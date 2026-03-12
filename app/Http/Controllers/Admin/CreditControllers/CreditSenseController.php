<?php

namespace App\Http\Controllers\Admin\CreditControllers;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Services\CreditSenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin CreditSenseController
 *
 * Thin HTTP layer. All CreditSense API logic, credential resolution, URL
 * construction, and error normalisation lives in CreditSenseService.
 *
 * Responsibilities of this controller:
 *   - Authorisation checks
 *   - Input validation
 *   - Translating CreditSenseService result arrays into JSON responses
 *   - Persisting relevant data back onto Application models
 *   - Writing ActivityLog entries
 */
class CreditSenseController extends Controller
{
    private const PROVIDER_NAME = 'CreditSense';

    public function __construct(private readonly CreditSenseService $creditSense) {}

    // ── Fetch report ──────────────────────────────────────────────────────

    /**
     * POST admin/applications/{application}/creditsense/fetch-report
     *
     * Manually pull the CreditSense report when the webhook hasn't fired.
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

        $application->update([
            'credit_sense_report'             => $result['data'],
            'credit_sense_report_received_at' => now(),
            'bank_api_provider_name'          => self::PROVIDER_NAME,
        ]);

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

    // ── Create quicklink ──────────────────────────────────────────────────

    /**
     * POST admin/applications/{application}/creditsense/quicklink
     *
     * Create a quicklink token the customer can use to complete CreditSense
     * on their own device.
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

    // ── Send quicklink via SMS ────────────────────────────────────────────

    /**
     * POST admin/applications/{application}/creditsense/quicklink/sms
     */
    public function sendQuicklinkSms(Request $request, Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'token'  => 'required|string',
            'name'   => 'required|string',
            'mobile' => 'required|string',
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

    // ── Send quicklink via email ──────────────────────────────────────────

    /**
     * POST admin/applications/{application}/creditsense/quicklink/email
     */
    public function sendQuicklinkEmail(Request $request, Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'token' => 'required|string',
            'name'  => 'required|string',
            'email' => 'required|email',
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

    // ── Application status polling ────────────────────────────────────────

    /**
     * GET admin/applications/{application}/creditsense/status
     *
     * Poll CreditSense for the current status of an application.
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

    // ── Test connection (Settings UI) ─────────────────────────────────────

    /**
     * POST /admin/settings/creditsense/test-connection
     *
     * Validates credentials before saving. Accepts unsaved overrides from the
     * request body so the admin can test before committing to the database.
     */
    public function testConnection(Request $request): JsonResponse
    {
        $request->validate([
            'api_key'   => 'nullable|string',
            'api_token' => 'nullable|string',
            'base_url'  => 'nullable|url',
            'env'       => 'nullable|string|in:sandbox,production',
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

        ActivityLog::logActivity('creditsense_test_connection', "CreditSense test connection succeeded ({$env})");

        return response()->json([
            'success' => true,
            'message' => "Connected successfully ({$env}). API Key and Token are valid.",
            'env'     => $env,
        ]);
    }

    // ── Internal helpers ──────────────────────────────────────────────────

    /**
     * Map a CreditSenseService failure result to a JSON error response.
     *
     * Error code 102 = invalid credentials → 401.
     * Everything else → 502 (bad gateway from upstream).
     */
    private function serviceError(array $result): JsonResponse
    {
        $status = $result['code'] === 102 ? 401 : 502;

        return response()->json(['error' => $result['error']], $status);
    }
}