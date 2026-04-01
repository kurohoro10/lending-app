<?php

/**
 * @file    app/Http/Controllers/AccountantDetailController.php
 * @package App\Http\Controllers
 *
 * Manages the accountant detail record associated with a loan application
 * within the commercial loan application system.
 *
 * Responsibilities:
 *  - Creating or updating the accountant detail for an application (upsert)
 *  - Validating and returning the persisted accountant data
 *
 * Upsert behaviour:
 *  - If an `accountantDetail` record already exists for the application,
 *    it is updated in place.
 *  - Otherwise a new record is created and associated with the application.
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountantDetailController extends Controller
{
    // =========================================================================
    // Upsert
    // =========================================================================

    /**
     * Create or update the accountant detail for a loan application.
     *
     * Acts as an upsert — updates the existing record when one is present,
     * or creates a new one when the application has no accountant detail yet.
     *
     * @param  Request      $request      Incoming HTTP request with accountant fields.
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               The persisted accountant detail payload.
     *
     * @bodyParam string  accountant_name       required  Accountant's full name (max 255 chars).
     * @bodyParam string  accountant_email      nullable  Accountant's email address (max 255 chars).
     * @bodyParam string  accountant_phone      nullable  Accountant's phone number (max 20 chars).
     * @bodyParam integer years_with_accountant nullable  Years the client has used this accountant (0–100).
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Accountant details saved.",
     *   "accountant": {
     *     "accountant_name": "Jane Smith",
     *     "accountant_email": "jane@example.com",
     *     "accountant_phone": "+61400000000",
     *     "years_with_accountant": 5
     *   }
     * }
     */
    public function store(Request $request, Application $application): JsonResponse
    {
        $validated = $this->validateAccountantPayload($request);

        $validated['application_id'] = $application->id;

        $accountant = $this->upsertAccountantDetail($application, $validated);

        return response()->json([
            'success'    => true,
            'message'    => 'Accountant details saved.',
            'accountant' => $this->formatAccountantDetail($accountant),
        ]);
    }

    // =========================================================================
    // Private Helpers — Validation
    // =========================================================================

    /**
     * Validate the accountant detail request payload.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated accountant fields.
     */
    private function validateAccountantPayload(Request $request): array
    {
        return $request->validate([
            'accountant_name'       => ['required', 'string', 'max:255'],
            'accountant_email'      => ['nullable', 'email', 'max:255'],
            'accountant_phone'      => ['nullable', 'string', 'max:20'],
            'years_with_accountant' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);
    }

    // =========================================================================
    // Private Helpers — Persistence
    // =========================================================================

    /**
     * Update the existing accountant detail or create a new one for the application.
     *
     * Uses `tap()` to update in place and return the model when a record already
     * exists, preserving the same return type in both branches.
     *
     * @param  Application  $application  The parent application.
     * @param  array        $validated    Validated payload including `application_id`.
     * @return \App\Models\AccountantDetail  The updated or newly created model.
     */
    private function upsertAccountantDetail(Application $application, array $validated): \App\Models\AccountantDetail
    {
        return $application->accountantDetail
            ? tap($application->accountantDetail)->update($validated)
            : $application->accountantDetail()->create($validated);
    }

    // =========================================================================
    // Private Helpers — Formatting
    // =========================================================================

    /**
     * Map an AccountantDetail model to the array shape used in JSON responses.
     *
     * @param  \App\Models\AccountantDetail  $accountant  The model to format.
     * @return array                                       Associative array for JSON serialisation.
     */
    private function formatAccountantDetail(\App\Models\AccountantDetail $accountant): array
    {
        return [
            'accountant_name'       => $accountant->accountant_name,
            'accountant_email'      => $accountant->accountant_email,
            'accountant_phone'      => $accountant->accountant_phone,
            'years_with_accountant' => $accountant->years_with_accountant,
        ];
    }
}