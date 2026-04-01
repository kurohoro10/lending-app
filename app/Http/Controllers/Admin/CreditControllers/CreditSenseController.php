<?php

/**
 * @file    app/Http/Controllers/Admin/CreditControllers/CreditSenseController.php
 * @package App\Http\Controllers\Admin\CreditControllers
 *
 * Thin HTTP layer for the CreditSense open-banking integration within the
 * commercial loan application system.
 *
 * All CreditSense API logic, credential resolution, URL construction, and
 * error normalisation is delegated to CreditSenseService. This controller is
 * responsible only for:
 *  - Authorisation checks (via Laravel Policies)
 *  - Input validation
 *  - Translating CreditSenseService result arrays into JSON responses
 *  - Persisting relevant data back onto Application models
 *  - Writing ActivityLog entries
 *
 * Route group: admin/applications/{application}/creditsense/...
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin\CreditControllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Services\CreditSenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreditSenseController extends Controller
{
    /**
     * The display name stored against application records for audit purposes.
     *
     * @var string
     */
    private const PROVIDER_NAME = 'CreditSense';

    /**
     * CreditSense error code returned when credentials are invalid.
     *
     * Maps to an HTTP 401 Unauthorized response rather than the default 502.
     *
     * @var int
     */
    private const ERROR_CODE_INVALID_CREDENTIALS = 102;

    /**
     * Inject the CreditSense service layer.
     *
     * @param  CreditSenseService  $creditSense  Service handling all CreditSense API communication.
     */
    public function __construct(private readonly CreditSenseService $creditSense) {}

    // =========================================================================
    // Report Retrieval
    // =========================================================================

    /**
     * Manually pull the CreditSense report for an application.
     *
     * Used when the automated webhook has not fired or needs to be re-triggered.
     * Resolves the CreditSense App ID from the application record, delegates the
     * API call to the service layer, and persists the result on success.
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               Success confirmation or service error response.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the admin lacks `update` policy.
     *
     * @response 200 { "success": true, "message": "Report retrieved and saved successfully." }
     * @response 404 { "error": "No CreditSense App ID is associated with this application." }
     * @response 401 { "error": "..." }  Invalid credentials (CreditSense error code 102).
     * @response 502 { "error": "..." }  Upstream API failure.
     */
    public function fetchReport(Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $csAppId = $application->credit_sense_app_id ?? $application->application_number;

        if (blank($csAppId)) {
            return response()->json([
                'error' => 'No CreditSense App ID is associated with this application. '
                         . 'The customer may not have completed the bank connection yet.',
            ], 404);
        }

        $result = $this->creditSense->fetchReport($csAppId);

        if (! $result['success']) {
            return $this->serviceError($result);
        }

        $this->persistReport($application, $result['data']);

        ActivityLog::logActivity(
            'credit_sense_report_fetched',
            'CreditSense report manually fetched via REST API',
            $application,
        );

        return response()->json([
            'success' => true,
            'message' => 'Report retrieved and saved successfully.',
        ]);
    }

    // =========================================================================
    // Quicklink Management
    // =========================================================================

    /**
     * Create a CreditSense quicklink token for customer self-service completion.
     *
     * The returned token and URL can be delivered to the customer via SMS or
     * email so they can complete the bank connection on their own device.
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               Token and URL on success, or service error response.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the admin lacks `update` policy.
     *
     * @response 200 { "success": true, "token": "...", "url": "https://..." }
     * @response 401 { "error": "..." }
     * @response 502 { "error": "..." }
     */
    public function createQuicklink(Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $result = $this->creditSense->createQuicklink(
            appReference: (string) $application->application_number,
        );

        if (! $result['success']) {
            return $this->serviceError($result);
        }

        ActivityLog::logActivity(
            'credit_sense_quicklink_created',
            'CreditSense quicklink created for customer delivery',
            $application,
        );

        return response()->json([
            'success' => true,
            'token'   => $result['data']['token'],
            'url'     => $result['data']['url'],
        ]);
    }

    /**
     * Send a CreditSense quicklink to the customer via SMS.
     *
     * Requires a previously generated quicklink token. The token, recipient
     * name, and mobile number must be supplied in the request body.
     *
     * @param  Request      $request      Incoming HTTP request with token, name, and mobile.
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               Success acknowledgement or service error response.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the admin lacks `update` policy.
     *
     * @bodyParam string token  required  The quicklink token from createQuicklink().
     * @bodyParam string name   required  The customer's display name for the SMS greeting.
     * @bodyParam string mobile required  The recipient mobile number in any accepted format.
     *
     * @response 200 { "success": true }
     * @response 401 { "error": "..." }
     * @response 502 { "error": "..." }
     */
    public function sendQuicklinkSms(Request $request, Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'token'  => ['required', 'string'],
            'name'   => ['required', 'string'],
            'mobile' => ['required', 'string'],
        ]);

        $result = $this->creditSense->sendQuicklinkSms(
            token:  $validated['token'],
            name:   $validated['name'],
            mobile: $validated['mobile'],
        );

        if (! $result['success']) {
            return $this->serviceError($result);
        }

        ActivityLog::logActivity(
            'credit_sense_quicklink_sms_sent',
            'CreditSense quicklink sent to customer via SMS',
            $application,
        );

        return response()->json(['success' => true]);
    }

    /**
     * Send a CreditSense quicklink to the customer via email.
     *
     * Requires a previously generated quicklink token. The token, recipient
     * name, and email address must be supplied in the request body.
     *
     * @param  Request      $request      Incoming HTTP request with token, name, and email.
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               Success acknowledgement or service error response.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the admin lacks `update` policy.
     *
     * @bodyParam string token required  The quicklink token from createQuicklink().
     * @bodyParam string name  required  The customer's display name for the email greeting.
     * @bodyParam string email required  A valid email address for the recipient.
     *
     * @response 200 { "success": true }
     * @response 401 { "error": "..." }
     * @response 502 { "error": "..." }
     */
    public function sendQuicklinkEmail(Request $request, Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'token' => ['required', 'string'],
            'name'  => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $result = $this->creditSense->sendQuicklinkEmail(
            token: $validated['token'],
            name:  $validated['name'],
            email: $validated['email'],
        );

        if (! $result['success']) {
            return $this->serviceError($result);
        }

        ActivityLog::logActivity(
            'credit_sense_quicklink_email_sent',
            'CreditSense quicklink sent to customer via email',
            $application,
        );

        return response()->json(['success' => true]);
    }

    // =========================================================================
    // Application Status
    // =========================================================================

    /**
     * Poll CreditSense for the current processing status of an application.
     *
     * Used by the admin UI to check whether the customer has completed the
     * CreditSense flow and whether a report is available for retrieval.
     *
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               Status data with application list and count,
     *                                    or service error response.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the admin lacks `view` policy.
     *
     * @response 200 {
     *   "success": true,
     *   "applications": [...],
     *   "count": 1
     * }
     * @response 401 { "error": "..." }
     * @response 502 { "error": "..." }
     */
    public function getApplicationStatus(Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        $result = $this->creditSense->getApplicationStatus(
            appRef: (string) $application->application_number,
        );

        if (! $result['success']) {
            return $this->serviceError($result);
        }

        return response()->json([
            'success'      => true,
            'applications' => $result['data']['applications'],
            'count'        => $result['data']['count'],
        ]);
    }

    // =========================================================================
    // Settings — Connection Test
    // =========================================================================

    /**
     * Validate CreditSense credentials before saving them to the Settings table.
     *
     * Accepts optional unsaved credential overrides in the request body so that
     * admins can test a new configuration without first committing it to the
     * database. Falls back to the currently stored settings when fields are omitted.
     *
     * @param  Request  $request  Incoming HTTP request with optional credential overrides.
     * @return JsonResponse       Success confirmation with environment label, or 422 on failure.
     *
     * @bodyParam string api_key   nullable  CreditSense API key override.
     * @bodyParam string api_token nullable  CreditSense API token override.
     * @bodyParam string base_url  nullable  Base URL override (must be a valid URL).
     * @bodyParam string env       nullable  Environment label — one of: sandbox, production.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Connected successfully (sandbox). API Key and Token are valid.",
     *   "env": "sandbox"
     * }
     * @response 422 { "success": false, "message": "..." }
     */
    public function testConnection(Request $request): JsonResponse
    {
        $request->validate([
            'api_key'   => ['nullable', 'string'],
            'api_token' => ['nullable', 'string'],
            'base_url'  => ['nullable', 'url'],
            'env'       => ['nullable', 'string', 'in:sandbox,production'],
        ]);

        $result = $this->creditSense->testConnection(
            apiKey:   $request->input('api_key'),
            apiToken: $request->input('api_token'),
            baseUrl:  $request->input('base_url'),
        );

        if (! $result['success']) {
            return response()->json(['success' => false, 'message' => $result['error']], 422);
        }

        $env = $request->input('env') ?: 'sandbox';

        ActivityLog::logActivity(
            'creditsense_test_connection',
            "CreditSense test connection succeeded ({$env})",
        );

        return response()->json([
            'success' => true,
            'message' => "Connected successfully ({$env}). API Key and Token are valid.",
            'env'     => $env,
        ]);
    }

    // =========================================================================
    // Private Helpers
    // =========================================================================

    /**
     * Persist a successfully fetched CreditSense report onto the application record.
     *
     * @param  Application  $application  The application to update.
     * @param  mixed        $reportData   The raw report payload from the service layer.
     * @return void
     */
    private function persistReport(Application $application, mixed $reportData): void
    {
        $application->update([
            'credit_sense_report'             => $reportData,
            'credit_sense_report_received_at' => now(),
            'bank_api_provider_name'          => self::PROVIDER_NAME,
        ]);
    }

    /**
     * Map a CreditSenseService failure result array to a JSON error response.
     *
     * Error code {@see self::ERROR_CODE_INVALID_CREDENTIALS} maps to HTTP 401
     * Unauthorized. All other error codes map to HTTP 502 Bad Gateway, indicating
     * a failure in the upstream CreditSense API.
     *
     * @param  array  $result  The failure result array returned by CreditSenseService.
     * @return JsonResponse    JSON error response with the appropriate HTTP status code.
     */
    private function serviceError(array $result): JsonResponse
    {
        $status = $result['code'] === self::ERROR_CODE_INVALID_CREDENTIALS ? 401 : 502;

        return response()->json(['error' => $result['error']], $status);
    }
}