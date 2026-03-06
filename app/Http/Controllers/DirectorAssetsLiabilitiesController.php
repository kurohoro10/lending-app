<?php
/**
 * File: app/Http/Controllers/DirectorAssetsLiabilitiesController.php
 *
 * Handles storing and deleting director assets and liabilities.
 * Supports both JSON (AJAX/API) and standard PHP requests.
 */

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\DirectorAsset;
use App\Models\DirectorLiability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DirectorAssetsLiabilitiesController extends Controller
{
    // ── Assets ────────────────────────────────────────────────────────────────

    public function storeAsset(Request $request, Application $application)
    {
        try {

            $validated = $request->validate([
                'asset_type'      => 'required|in:house,bank,super,vehicle,other',
                'description'     => 'nullable|string|max:255',
                'property_use'    => 'nullable|in:main_residence,rental,na',
                'estimated_value' => 'required|numeric|min:0',
            ]);

            $validated['application_id'] = $application->id;
            $validated['property_use']   = $validated['property_use'] ?? 'na';

            $asset = DirectorAsset::create($validated);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Asset added.',
                    'asset'   => $this->formatAsset($asset),
                ]);
            }

            return back()->with('success', 'Asset added successfully.');

        } catch (\Throwable $e) {

            Log::error('Failed to store director asset', [
                'application_id' => $application->id ?? null,
                'error' => $e->getMessage(),
            ]);

            $message = 'Unable to add asset. Please try again.';

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return back()->withErrors(['asset' => $message]);
        }
    }

    public function destroyAsset(Application $application, DirectorAsset $asset)
    {
        try {

            abort_if($asset->application_id !== $application->id, 403);

            $asset->delete();

            return response()->json([
                'success' => true,
                'message' => 'Asset removed.',
            ]);

        } catch (\Throwable $e) {

            Log::error('Failed to delete director asset', [
                'asset_id' => $asset->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to remove asset.',
            ], 500);
        }
    }

    // ── Liabilities ───────────────────────────────────────────────────────────

    public function storeLiability(Request $request, Application $application)
    {
        try {

            $validated = $request->validate([
                'liability_type'      => 'required|in:credit_card,home_loan,car_loan,other',
                'lender_name'         => 'nullable|string|max:255',
                'credit_limit'        => 'nullable|required_if:liability_type,credit_card|numeric|min:0',
                'outstanding_balance' => 'required|numeric|min:0',
            ], [
                'credit_limit.required_if' => 'Credit limit is required for credit cards.',
            ]);

            $validated['application_id'] = $application->id;

            $liability = DirectorLiability::create($validated);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success'   => true,
                    'message'   => 'Liability added.',
                    'liability' => $this->formatLiability($liability),
                ]);
            }

            return back()->with('success', 'Liability added successfully.');

        } catch (\Throwable $e) {

            Log::error('Failed to store director liability', [
                'application_id' => $application->id ?? null,
                'error' => $e->getMessage(),
            ]);

            $message = 'Unable to add liability. Please try again.';

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 500);
            }

            return back()->withErrors(['liability' => $message]);
        }
    }

    public function destroyLiability(Application $application, DirectorLiability $liability)
    {
        try {

            abort_if($liability->application_id !== $application->id, 403);

            $liability->delete();

            return response()->json([
                'success' => true,
                'message' => 'Liability removed.',
            ]);

        } catch (\Throwable $e) {

            Log::error('Failed to delete director liability', [
                'liability_id' => $liability->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to remove liability.',
            ], 500);
        }
    }

    // ── Formatters ────────────────────────────────────────────────────────────

    private function formatAsset(DirectorAsset $a): array
    {
        return [
            'id'               => $a->id,
            'asset_type'       => $a->asset_type,
            'asset_type_label' => $a->asset_type_label,
            'description'      => $a->description,
            'property_use'     => $a->property_use,
            'estimated_value'  => $a->estimated_value,
        ];
    }

    private function formatLiability(DirectorLiability $l): array
    {
        return [
            'id'                    => $l->id,
            'liability_type'        => $l->liability_type,
            'liability_type_label'  => $l->liability_type_label,
            'lender_name'           => $l->lender_name,
            'credit_limit'          => $l->credit_limit,
            'outstanding_balance'   => $l->outstanding_balance,
        ];
    }
}