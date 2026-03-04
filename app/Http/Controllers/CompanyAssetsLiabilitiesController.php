<?php
// app/Http/Controllers/CompanyAssetsLiabilitiesController.php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\CompanyAsset;
use App\Models\CompanyLiability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyAssetsLiabilitiesController extends Controller
{
    // ── Assets ────────────────────────────────────────────────────────────────

    public function storeAsset(Request $request, Application $application): JsonResponse
    {
        $this->authorizeCompany($application);

        $validated = $request->validate([
            'asset_name' => 'required|string|max:255',
            'notes'      => 'nullable|string|max:500',
            'value'      => 'required|numeric|min:0',
        ]);

        $validated['application_id'] = $application->id;
        $asset = CompanyAsset::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Asset added.',
            'asset'   => $this->formatAsset($asset),
        ]);
    }

    public function destroyAsset(Application $application, CompanyAsset $asset): JsonResponse
    {
        $this->authorizeCompany($application);
        abort_if($asset->application_id !== $application->id, 403);
        $asset->delete();

        return response()->json(['success' => true, 'message' => 'Asset removed.']);
    }

    // ── Liabilities ───────────────────────────────────────────────────────────

    public function storeLiability(Request $request, Application $application): JsonResponse
    {
        $this->authorizeCompany($application);

        $validated = $request->validate([
            'liability_name' => 'required|string|max:255',
            'notes'          => 'nullable|string|max:500',
            'value'          => 'required|numeric|min:0',
        ]);

        $validated['application_id'] = $application->id;
        $liability = CompanyLiability::create($validated);

        return response()->json([
            'success'   => true,
            'message'   => 'Liability added.',
            'liability' => $this->formatLiability($liability),
        ]);
    }

    public function destroyLiability(Application $application, CompanyLiability $liability): JsonResponse
    {
        $this->authorizeCompany($application);
        abort_if($liability->application_id !== $application->id, 403);
        $liability->delete();

        return response()->json(['success' => true, 'message' => 'Liability removed.']);
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
