<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\DirectorAsset;
use App\Models\DirectorLiability;
use App\Models\AssessorDalVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssessorDirectorAssetsLiabilitiesController extends Controller
{
    // ── Unlock — admin clicks "Allow Assessor to Edit" ────────────────────────

    public function unlock(Request $request, Application $application)
    {
        $this->authorise($application);

        // Only admin can unlock
        abort_if(!auth()->user()->hasRole('admin'), 403, 'Only admins can unlock.');

        if (!$application->assessorDalVerification) {
            AssessorDalVerification::create([
                'application_id' => $application->id,
                'initiated_by'   => auth()->id(),
                'verified_by'    => null,
                'verified_at'    => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Editing unlocked for assessor.',
        ]);
    }

    // ── Stamp — admin or assessor clicks "Save & Verify" ─────────────────────

    public function stamp(Request $request, Application $application)
    {
        $this->authorise($application);

        $verification = $application->assessorDalVerification;

        abort_if(!$verification, 422, 'Must unlock before verifying.');

        $verification->update([
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Verification stamped.',
            'stamp'   => [
                'verified_by_name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
                'verified_at'      => now()->format('d M Y, g:i a'),
            ],
        ]);
    }

    // ── Assets ────────────────────────────────────────────────────────────────

    public function storeAsset(Request $request, Application $application)
    {
        $this->authorise($application);
        $this->requireUnlocked($application);

        $validated = $request->validate([
            'asset_type'           => 'required|in:house,bank,super,vehicle,other',
            'description'          => 'nullable|string|max:255',
            'property_use'         => 'nullable|in:main_residence,rental,na',
            'estimated_value'      => 'required|numeric|min:0',
            'is_owned'             => 'required|boolean',
            'ownership_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['application_id'] = $application->id;
        $validated['property_use']   = $validated['property_use'] ?? 'na';

        $asset = DirectorAsset::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Asset added.',
            'asset'   => $this->formatAsset($asset->load('history.changedBy')),
        ]);
    }

    public function updateAsset(Request $request, DirectorAsset $asset)
    {
        $this->authorise($asset->application);
        $this->requireUnlocked($asset->application);

        $validated = $request->validate([
            'asset_type'           => 'required|in:house,bank,super,vehicle,other',
            'description'          => 'nullable|string|max:255',
            'property_use'         => 'nullable|in:main_residence,rental,na',
            'estimated_value'      => 'required|numeric|min:0',
            'is_owned'             => 'required|boolean',
            'ownership_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $asset->update($validated); // history auto-logged via model event

        return response()->json([
            'success' => true,
            'message' => 'Asset updated.',
            'asset'   => $this->formatAsset($asset->fresh()->load('history.changedBy')),
        ]);
    }

    public function destroyAsset(DirectorAsset $asset)
    {
        $this->authorise($asset->application);
        $this->requireUnlocked($asset->application);

        $asset->delete();

        return response()->json(['success' => true, 'message' => 'Asset removed.']);
    }

    // ── Liabilities ───────────────────────────────────────────────────────────

    public function storeLiability(Request $request, Application $application)
    {
        $this->authorise($application);
        $this->requireUnlocked($application);

        $validated = $request->validate([
            'liability_type'      => 'required|in:credit_card,home_loan,car_loan,other',
            'lender_name'         => 'nullable|string|max:255',
            'credit_limit'        => 'nullable|required_if:liability_type,credit_card|numeric|min:0',
            'outstanding_balance' => 'required|numeric|min:0',
        ]);

        $validated['application_id'] = $application->id;

        $liability = DirectorLiability::create($validated);

        return response()->json([
            'success'   => true,
            'message'   => 'Liability added.',
            'liability' => $this->formatLiability($liability->load('history.changedBy')),
        ]);
    }

    public function updateLiability(Request $request, DirectorLiability $liability)
    {
        $this->authorise($liability->application);
        $this->requireUnlocked($liability->application);

        $validated = $request->validate([
            'liability_type'      => 'required|in:credit_card,home_loan,car_loan,other',
            'lender_name'         => 'nullable|string|max:255',
            'credit_limit'        => 'nullable|required_if:liability_type,credit_card|numeric|min:0',
            'outstanding_balance' => 'required|numeric|min:0',
        ]);

        $liability->update($validated);

        return response()->json([
            'success'   => true,
            'message'   => 'Liability updated.',
            'liability' => $this->formatLiability($liability->fresh()->load('history.changedBy')),
        ]);
    }

    public function destroyLiability(DirectorLiability $liability)
    {
        $this->authorise($liability->application);
        $this->requireUnlocked($liability->application);

        $liability->delete();

        return response()->json(['success' => true, 'message' => 'Liability removed.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function authorise(Application $application): void
    {
        $user = auth()->user();

        $isAdmin           = $user->hasRole('admin');
        $isAssignedAssessor = $user->hasRole('assessor') && $user->id === $application->assigned_to;

        abort_if(!$isAdmin && !$isAssignedAssessor, 403, 'Unauthorised.');
    }

    private function requireUnlocked(Application $application): void
    {
        abort_if(
            !$application->assessorDalVerification,
            403,
            'This section has not been unlocked yet.'
        );
    }

    private function formatAsset(DirectorAsset $a): array
    {
        return [
            'id'                   => $a->id,
            'asset_type'           => $a->asset_type,
            'asset_type_label'     => $a->asset_type_label,
            'description'          => $a->description,
            'property_use'         => $a->property_use,
            'estimated_value'      => (float) $a->estimated_value,
            'is_owned'             => $a->is_owned,
            'ownership_percentage' => $a->ownership_percentage ? (float) $a->ownership_percentage : null,
            'history'              => $a->history->map(fn($h) => [
                'field_label' => $h->field_label,
                'old_value'   => $h->old_value,
                'new_value'   => $h->new_value,
                'changed_by'  => optional($h->changedBy)->first_name . ' ' . optional($h->changedBy)->last_name,
                'changed_at'  => $h->changed_at->format('d M Y, g:i a'),
            ])->values()->toArray(),
        ];
    }

    private function formatLiability(DirectorLiability $l): array
    {
        return [
            'id'                   => $l->id,
            'liability_type'       => $l->liability_type,
            'liability_type_label' => $l->liability_type_label,
            'lender_name'          => $l->lender_name,
            'credit_limit'         => $l->credit_limit ? (float) $l->credit_limit : null,
            'outstanding_balance'  => (float) $l->outstanding_balance,
            'history'              => $l->history->map(fn($h) => [
                'field_label' => $h->field_label,
                'old_value'   => $h->old_value,
                'new_value'   => $h->new_value,
                'changed_by'  => optional($h->changedBy)->first_name . ' ' . optional($h->changedBy)->last_name,
                'changed_at'  => $h->changed_at->format('d M Y, g:i a'),
            ])->values()->toArray(),
        ];
    }
}