<?php
/**
 * File: app/Http/Controllers/CompanyAssetsLiabilitiesController.php
 *
 * Handles storing company assets and liabilities with
 * safe error handling and JSON / standard request support.
 */

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\CompanyAsset;
use App\Models\CompanyLiability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CompanyAssetsLiabilitiesController extends Controller
{
    // ── Assets ────────────────────────────────────────────────────────────────

    public function storeAsset(Request $request, Application $application)
    {
        try {
            $this->authorizeCompany($application);

            $validated = $request->validate([
                'asset_name' => 'required|string|max:255',
                'notes'      => 'nullable|string|max:500',
                'value'      => 'required|numeric|min:0',
            ]);

            $validated['application_id'] = $application->id;

            $asset = CompanyAsset::create($validated);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Asset added.',
                    'asset'   => $this->formatAsset($asset),
                ]);
            }

            return back()->with('success', 'Asset added successfully.');

        } catch (\Throwable $e) {

            Log::error('Failed to store company asset', [
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

    // ── Liabilities ───────────────────────────────────────────────────────────

    public function storeLiability(Request $request, Application $application)
    {
        try {
            $this->authorizeCompany($application);

            $validated = $request->validate([
                'liability_name' => 'required|string|max:255',
                'notes'          => 'nullable|string|max:500',
                'value'          => 'required|numeric|min:0',
            ]);

            $validated['application_id'] = $application->id;

            $liability = CompanyLiability::create($validated);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success'   => true,
                    'message'   => 'Liability added.',
                    'liability' => $this->formatLiability($liability),
                ]);
            }

            return back()->with('success', 'Liability added successfully.');

        } catch (\Throwable $e) {

            Log::error('Failed to store company liability', [
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

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function authorizeCompany(Application $application): void
    {
        abort_if(
            $application->borrowerInformation?->borrower_type !== 'company',
            422,
            'Company assets & liabilities only apply to Company borrowers.'
        );
    }

    private function formatAsset(CompanyAsset $a): array
    {
        return [
            'id'         => $a->id,
            'asset_name' => $a->asset_name,
            'notes'      => $a->notes,
            'value'      => $a->value,
        ];
    }

    private function formatLiability(CompanyLiability $l): array
    {
        return [
            'id'             => $l->id,
            'liability_name' => $l->liability_name,
            'notes'          => $l->notes,
            'value'          => $l->value,
        ];
    }
}