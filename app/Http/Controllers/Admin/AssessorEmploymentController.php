<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\EmploymentDetail;
use App\Models\AssessorEmploymentDocument;
use App\Models\AssessorEmploymentVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssessorEmploymentController extends Controller
{
    // ── Unlock ────────────────────────────────────────────────────────────────

    public function unlock(Request $request, Application $application)
    {
        abort_if(!auth()->user()->hasRole('admin'), 403, 'Only admins can unlock.');
        $this->authorise($application);

        if (!$application->assessorEmploymentVerification) {
            AssessorEmploymentVerification::create([
                'application_id' => $application->id,
                'initiated_by'   => auth()->id(),
                'verified_by'    => null,
                'verified_at'    => null,
            ]);
        }

        return response()->json(['success' => true]);
    }

    // ── Stamp ─────────────────────────────────────────────────────────────────

    public function stamp(Request $request, Application $application)
    {
        $this->authorise($application);

        $verification = $application->assessorEmploymentVerification;
        abort_if(!$verification, 422, 'Must unlock before verifying.');

        $verification->update([
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'stamp'   => [
                'verified_by_name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
                'verified_at'      => now()->format('d M Y, g:i a'),
            ],
        ]);
    }

    // ── Edit any employment record (client or assessor-added) ─────────────────

    public function update(Request $request, Application $application, EmploymentDetail $employment)
    {
        $this->authorise($application);
        $this->requireUnlocked($application);
        abort_if($employment->application_id !== $application->id, 403);

        $validated = $request->validate([
            'employment_type'             => 'required|in:payg,self_employed,company_director,contract,casual,retired,unemployed',
            'employer_business_name'      => 'nullable|string|max:255',
            'abn'                         => 'nullable|string|max:20',
            'employment_role'             => 'nullable|string|max:255',
            'position'                    => 'nullable|string|max:255',
            'employment_start_date'       => 'nullable|date|before_or_equal:today',
            'base_income'                 => 'required|numeric|min:0',
            'additional_income'           => 'nullable|numeric|min:0',
            'income_frequency'            => 'required|in:weekly,fortnightly,monthly,annual',
            'employer_phone'              => 'nullable|string|max:20',
            'employer_address'            => 'nullable|string',
            'comment'                     => 'nullable|string|max:1000',
        ]);

        $employment->update($validated);

        if ($employment->employment_start_date) {
            $employment->calculateEmploymentLength();
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Employment updated.',
            'employment' => $this->formatEmployment($employment->fresh()->load('history.changedBy', 'documents.uploadedBy', 'addedBy')),
        ]);
    }

    // ── Add new employment record (assessor-added) ────────────────────────────

    public function store(Request $request, Application $application)
    {
        $this->authorise($application);
        $this->requireUnlocked($application);

        $validated = $request->validate([
            'employment_type'             => 'required|in:payg,self_employed,company_director,contract,casual,retired,unemployed',
            'employer_business_name'      => 'nullable|string|max:255',
            'abn'                         => 'nullable|string|max:20',
            'employment_role'             => 'nullable|string|max:255',
            'position'                    => 'nullable|string|max:255',
            'employment_start_date'       => 'nullable|date|before_or_equal:today',
            'base_income'                 => 'required|numeric|min:0',
            'additional_income'           => 'nullable|numeric|min:0',
            'income_frequency'            => 'required|in:weekly,fortnightly,monthly,annual',
            'employer_phone'              => 'nullable|string|max:20',
            'employer_address'            => 'nullable|string',
            'comment'                     => 'nullable|string|max:1000',
        ]);

        $validated['application_id'] = $application->id;
        $validated['added_by']       = auth()->id();

        $employment = EmploymentDetail::create($validated);

        if ($employment->employment_start_date) {
            $employment->calculateEmploymentLength();
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Employment record added.',
            'employment' => $this->formatEmployment($employment->fresh()->load('history.changedBy', 'documents.uploadedBy', 'addedBy')),
        ], 201);
    }

    // ── Delete employment record ───────────────────────────────────────────────

    public function destroy(Application $application, EmploymentDetail $employment)
    {
        $this->authorise($application);
        $this->requireUnlocked($application);
        abort_if($employment->application_id !== $application->id, 403);

        // Only assessor-added records can be deleted; client originals are preserved
        abort_if(!$employment->isAssessorAdded(), 403, 'Client-submitted records cannot be deleted.');

        foreach ($employment->documents as $doc) {
            Storage::disk('local')->delete('assessor-employment/' . $doc->stored_filename);
        }

        $employment->delete();

        return response()->json(['success' => true, 'message' => 'Employment record removed.']);
    }

    // ── Upload document ───────────────────────────────────────────────────────

    public function uploadDocument(Request $request, Application $application, EmploymentDetail $employment)
    {
        $this->authorise($application);
        $this->requireUnlocked($application);
        abort_if($employment->application_id !== $application->id, 403);

        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $file   = $request->file('file');
        $stored = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('assessor-employment', $stored, 'local');

        $doc = AssessorEmploymentDocument::create([
            'employment_detail_id' => $employment->id,
            'uploaded_by'          => auth()->id(),
            'original_filename'    => $file->getClientOriginalName(),
            'stored_filename'      => $stored,
            'mime_type'            => $file->getMimeType(),
            'file_size'            => $file->getSize(),
        ]);

        return response()->json([
            'success'  => true,
            'document' => $this->formatDocument($doc->load('uploadedBy')),
        ]);
    }

    // ── Download document ─────────────────────────────────────────────────────

    public function downloadDocument(AssessorEmploymentDocument $document)
    {
        $this->authorise($document->employmentDetail->application);
        $path = storage_path('app/assessor-employment/' . $document->stored_filename);
        abort_if(!file_exists($path), 404);
        return response()->download($path, $document->original_filename);
    }

    // ── Delete document ───────────────────────────────────────────────────────

    public function destroyDocument(AssessorEmploymentDocument $document)
    {
        $this->authorise($document->employmentDetail->application);
        Storage::disk('local')->delete('assessor-employment/' . $document->stored_filename);
        $document->delete();
        return response()->json(['success' => true]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function authorise(Application $application): void
    {
        $user               = auth()->user();
        $isAdmin            = $user->hasRole('admin');
        $isAssignedAssessor = $user->hasRole('assessor') && $user->id === $application->assigned_to;
        abort_if(!$isAdmin && !$isAssignedAssessor, 403);
    }

    private function requireUnlocked(Application $application): void
    {
        abort_if(!$application->assessorEmploymentVerification, 403, 'Section not unlocked.');
    }

    private function formatEmployment(EmploymentDetail $e): array
    {
        return [
            'id'                          => $e->id,
            'is_assessor_added'           => $e->isAssessorAdded(),
            'added_by_name'               => $e->addedBy ? $e->addedBy->first_name . ' ' . $e->addedBy->last_name : null,
            'employment_type'             => $e->employment_type,
            'employment_type_label'       => $e->employment_type_label,
            'employer_business_name'      => $e->employer_business_name,
            'abn'                         => $e->abn,
            'employment_role'             => $e->employment_role,
            'position'                    => $e->position,
            'employment_start_date'       => $e->employment_start_date?->format('Y-m-d'),
            'length_of_employment_months' => $e->length_of_employment_months,
            'base_income'                 => (float) $e->base_income,
            'additional_income'           => (float) $e->additional_income,
            'income_frequency'            => $e->income_frequency,
            'employer_phone'              => $e->employer_phone,
            'employer_address'            => $e->employer_address,
            'comment'                     => $e->comment,
            'annual_income'               => $e->getAnnualIncome(),
            'monthly_income'              => $e->getMonthlyIncome(),
            'history'                     => $e->history->map(fn($h) => [
                'field_label' => $h->field_label,
                'old_value'   => $h->old_value,
                'new_value'   => $h->new_value,
                'changed_by'  => optional($h->changedBy)->first_name . ' ' . optional($h->changedBy)->last_name,
                'changed_at'  => $h->changed_at->format('d M Y, g:i a'),
            ])->values()->toArray(),
            'documents'                   => $e->documents->map(fn($d) => $this->formatDocument($d))->values()->toArray(),
        ];
    }

    private function formatDocument(AssessorEmploymentDocument $d): array
    {
        return [
            'id'                => $d->id,
            'original_filename' => $d->original_filename,
            'file_size'         => $d->file_size_formatted,
            'mime_type'         => $d->mime_type,
            'download_url'      => route('admin.assessor-employment.documents.download', $d->id),
        ];
    }
}