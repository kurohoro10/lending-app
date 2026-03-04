<?php
// app/Http/Controllers/DirectorAssetsLiabilitiesController.php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\DirectorAsset;
use App\Models\DirectorLiability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DirectorAssetsLiabilitiesController extends Controller
{
    // ── Assets ────────────────────────────────────────────────────────────────

    public function storeAsset(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'asset_type'      => 'required|in:house,bank,super,vehicle,other',
            'description'     => 'nullable|string|max:255',
            'property_use'    => 'nullable|in:main_residence,rental,na',
            'estimated_value' => 'required|numeric|min:0',
        ]);

        $validated['application_id'] = $application->id;
        $validated['property_use']   = $validated['property_use'] ?? 'na';

        $asset = DirectorAsset::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Asset added.',
            'asset'   => $this->formatAsset($asset),
        ]);
    }

    public function destroyAsset(Application $application, DirectorAsset $asset): JsonResponse
    {
        abort_if($asset->application_id !== $application->id, 403);
        $asset->delete();

        return response()->json(['success' => true, 'message' => 'Asset removed.']);
    }

    // ── Liabilities ───────────────────────────────────────────────────────────

    public function storeLiability(Request $request, Application $application): JsonResponse
    {
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

        return response()->json([
            'success'   => true,
            'message'   => 'Liability added.',
            'liability' => $this->formatLiability($liability),
        ]);
    }

    public function destroyLiability(Application $application, DirectorLiability $liability): JsonResponse
    {
        abort_if($liability->application_id !== $application->id, 403);
        $liability->delete();

        return response()->json(['success' => true, 'message' => 'Liability removed.']);
    }

    // ── Formatters ────────────────────────────────────────────────────────────

    private function formatAsset(DirectorAsset $a): array
    {
        return [
            'id'              => $a->id,
            'asset_type'      => $a->asset_type,
            'asset_type_label'=> $a->asset_type_label,
            'description'     => $a->description,
            'property_use'    => $a->property_use,
            'estimated_value' => $a->estimated_value,
        ];
    }

    private function formatLiability(DirectorLiability $l): array
    {
        return [
            'id'                  => $l->id,
            'liability_type'      => $l->liability_type,
            'liability_type_label'=> $l->liability_type_label,
            'lender_name'         => $l->lender_name,
            'credit_limit'        => $l->credit_limit,
            'outstanding_balance' => $l->outstanding_balance,
        ];
    }
}
