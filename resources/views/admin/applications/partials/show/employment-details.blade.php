@php
    $employments  = $application->employmentDetails;
    $verification = $application->assessorEmploymentVerification;
    $isUnlocked   = $verification !== null;
    $isStamped    = $isUnlocked && $verification->isStamped();

    $isAdmin            = auth()->user()->hasRole('admin');
    $isAssignedAssessor = auth()->user()->hasRole('assessor') && auth()->id() === $application->assigned_to;
    $canEdit            = $isUnlocked && ($isAdmin || $isAssignedAssessor);
    $hasAssignedUser    = $application->assigned_to !== null;

    $routes = [
        'unlock'     => route('admin.assessor-employment.unlock',              $application),
        'stamp'      => route('admin.assessor-employment.stamp',               $application),
        'store'      => route('admin.assessor-employment.store',               $application),
        'update'     => route('admin.assessor-employment.update',              [$application, ':id']),
        'destroy'    => route('admin.assessor-employment.destroy',             [$application, ':id']),
        'upload'     => route('admin.assessor-employment.upload',              [$application, ':id']),
        'docDestroy' => route('admin.assessor-employment.documents.destroy',   ':id'),
    ];
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z"/>
                </svg>
                Employment &amp; Income
            </h3>

            <div class="flex items-center gap-3">
                {{-- Stamp badge --}}
                <span id="emp-stamp-badge"
                      class="{{ $isStamped ? '' : 'hidden' }} inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span id="emp-stamp-text">
                        @if($isStamped)
                            Verified by {{ $verification->verifiedBy->first_name }} {{ $verification->verifiedBy->last_name }}
                            — {{ $verification->verified_at->format('d M Y, g:i a') }}
                        @endif
                    </span>
                </span>

                @if($isAdmin && !$isUnlocked && $hasAssignedUser)
                    <button type="button" id="emp-unlock-btn"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                                   text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                        </svg>
                        Allow Assessor to Edit
                    </button>
                @endif

                @if($isAdmin && !$isUnlocked && !$hasAssignedUser)
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-400">
                        Assign an assessor first
                    </span>
                @endif

                @if($canEdit)
                    <button type="button" id="emp-stamp-btn"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white
                                   text-sm font-semibold rounded-lg hover:bg-green-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $isStamped ? 'Re-verify' : 'Save & Verify' }}
                    </button>
                @endif
            </div>
        </div>

        {{-- Flash --}}
        <div id="emp-flash" class="hidden mb-4 px-4 py-2 rounded-lg text-sm font-medium" role="status" aria-live="polite"></div>

        {{-- Add button --}}
        @if($canEdit)
        <div class="flex justify-end mb-4">
            <button type="button" id="emp-add-btn"
                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white
                           text-xs font-semibold rounded-lg hover:bg-indigo-700 transition">
                + Add Employment
            </button>
        </div>
        @endif

        {{-- Employment records --}}
        <div id="emp-list" class="space-y-4">
            @foreach($employments as $employment)
            <div class="p-4 rounded-lg border {{ $employment->isAssessorAdded() ? 'bg-indigo-50 border-indigo-200' : 'bg-gray-50 border-gray-200' }}"
                 data-emp-id="{{ $employment->id }}">

                {{-- Assessor-added badge --}}
                @if($employment->isAssessorAdded())
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                        Assessor Added
                    </span>
                    <span class="text-xs text-gray-400">
                        by {{ optional($employment->addedBy)->first_name }} {{ optional($employment->addedBy)->last_name }}
                    </span>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <span class="text-xs font-medium text-gray-500">Type</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $employment->employment_type_label }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500">Employer</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $employment->employer_business_name ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500">Position</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $employment->position ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500">Base Income</span>
                        <p class="mt-1 text-sm text-gray-900">
                            ${{ number_format($employment->base_income, 2) }} / {{ $employment->income_frequency }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500">Base Income (After Tax)</span>
                        <p class="mt-1 text-sm text-gray-900">
                            @if($employment->after_tax_income !== null)
                                ${{ number_format($employment->after_tax_income, 2) }} / {{ ucfirst($employment->income_frequency) }}
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500">Annual Income</span>
                        <p class="mt-1 text-sm font-semibold text-indigo-600">${{ number_format($employment->getDisplayAnnualIncome(), 2) }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500">Monthly Income (Base)</span>
                        <p class="mt-1 text-sm font-semibold text-indigo-600">${{ number_format($employment->getMonthlyIncome(), 2) }}</p>
                    </div>

                    <div>
                        <span class="text-xs font-medium text-gray-500">Monthly Income / After Tax</span>
                        <p class="mt-1 text-sm font-semibold text-green-600">${{ number_format($employment->getMonthlyAfterTaxIncome(), 2) }}</p>
                    </div>
                    @if($employment->employment_start_date)
                    <div>
                        <span class="text-xs font-medium text-gray-500">Start Date</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $employment->employment_start_date->format('d M Y') }}</p>
                    </div>
                    @endif
                    @if($employment->abn)
                    <div>
                        <span class="text-xs font-medium text-gray-500">ABN</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $employment->abn }}</p>
                    </div>
                    @endif
                    @if($employment->employer_phone)
                    <div>
                        <span class="text-xs font-medium text-gray-500">Phone</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $employment->employer_phone }}</p>
                    </div>
                    @endif
                </div>

                {{-- Assessor comment --}}
                @if($employment->comment)
                <div class="mt-3 p-3 bg-yellow-50 rounded border border-yellow-100">
                    <span class="text-xs font-semibold text-yellow-700">Assessor Note:</span>
                    <p class="mt-1 text-sm text-gray-700">{{ $employment->comment }}</p>
                </div>
                @endif

                {{-- Documents --}}
                @if($employment->documents->count() > 0)
                <div class="mt-3" id="emp-docs-{{ $employment->id }}">
                    <span class="text-xs font-medium text-gray-500">Documents:</span>
                    <div class="mt-1 space-y-1">
                        @foreach($employment->documents as $doc)
                        <div class="flex items-center justify-between text-xs bg-white rounded px-3 py-1.5 border border-gray-200"
                             data-doc-id="{{ $doc->id }}">
                            <a href="{{ route('admin.assessor-employment.documents.download', $doc->id) }}"
                               class="text-indigo-600 hover:underline truncate max-w-xs">
                                {{ $doc->original_filename }}
                            </a>
                            <span class="text-gray-400 ml-2 shrink-0">{{ $doc->file_size_formatted }}</span>
                            @if($canEdit)
                            <button type="button" data-doc-id="{{ $doc->id }}"
                                    class="emp-doc-delete-btn ml-2 text-red-400 hover:text-red-600 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div id="emp-docs-{{ $employment->id }}" class="{{ $canEdit ? '' : 'hidden' }}"></div>
                @endif

                {{-- Actions --}}
                @if($canEdit)
                <div class="mt-3 flex items-center gap-2 flex-wrap">
                    {{-- History --}}
                    @if($employment->history->count() > 0)
                    <button type="button"
                            data-emp-history-id="{{ $employment->id }}"
                            class="emp-history-btn text-indigo-500 hover:text-indigo-700 text-xs underline">
                        {{ $employment->history->count() }} change(s)
                    </button>
                    @endif

                    {{-- Edit --}}
                    <button type="button"
                            data-emp-edit-id="{{ $employment->id }}"
                            data-emp-is-assessor="{{ $employment->isAssessorAdded() ? 'true' : 'false' }}"
                            class="emp-edit-btn inline-flex items-center gap-1 px-2 py-1 text-xs font-medium
                                   text-indigo-600 bg-indigo-50 rounded hover:bg-indigo-100 transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </button>

                    {{-- Upload --}}
                    <label class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium
                                  text-emerald-600 bg-emerald-50 rounded hover:bg-emerald-100 transition cursor-pointer">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Upload
                        <input type="file" class="hidden emp-upload-input"
                               data-emp-id="{{ $employment->id }}"
                               accept=".pdf,.jpg,.jpeg,.png">
                    </label>

                    {{-- Delete (assessor-added only) --}}
                    @if($employment->isAssessorAdded())
                    <button type="button"
                            data-emp-delete-id="{{ $employment->id }}"
                            class="emp-delete-btn inline-flex items-center gap-1 px-2 py-1 text-xs font-medium
                                   text-red-600 bg-red-50 rounded hover:bg-red-100 transition">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Remove
                    </button>
                    @endif
                </div>
                @elseif($employment->history->count() > 0)
                <div class="mt-3">
                    <button type="button"
                            data-emp-history-id="{{ $employment->id }}"
                            class="emp-history-btn text-indigo-500 hover:text-indigo-700 text-xs underline">
                        {{ $employment->history->count() }} change(s)
                    </button>
                </div>
                @endif
            </div>
            @endforeach
        </div>

    </div>
</div>

{{-- Edit Modal --}}
@if($canEdit)
<div id="emp-edit-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/40" id="emp-modal-backdrop"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
            <h4 id="emp-modal-title" class="text-base font-semibold text-gray-900 mb-4">Edit Employment</h4>
            <div id="emp-modal-body" class="space-y-4"></div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" id="emp-modal-cancel"
                        class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button" id="emp-modal-save"
                        class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div id="emp-add-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/40" id="emp-add-backdrop"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
            <h4 class="text-base font-semibold text-gray-900 mb-4">Add Employment Record</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Employment Type <span class="text-red-500">*</span></label>
                    <select id="add-emp-type" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select…</option>
                        <option value="payg">PAYG</option>
                        <option value="self_employed">Self Employed</option>
                        <option value="company_director">Company Director</option>
                        <option value="contract">Contract</option>
                        <option value="casual">Casual</option>
                        <option value="retired">Retired</option>
                        <option value="unemployed">Unemployed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Employer / Business Name</label>
                    <input type="text" id="add-emp-employer" placeholder="e.g. Acme Pty Ltd"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">ABN</label>
                    <input type="text" id="add-emp-abn" placeholder="e.g. 12 345 678 901"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Position</label>
                    <input type="text" id="add-emp-position" placeholder="e.g. Senior Developer"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Start Date</label>
                    <input type="date" id="add-emp-start"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Income Frequency <span class="text-red-500">*</span></label>
                    <select id="add-emp-frequency" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select…</option>
                        <option value="weekly">Weekly</option>
                        <option value="fortnightly">Fortnightly</option>
                        <option value="monthly">Monthly</option>
                        <option value="annual">Annual</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Base Income <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                        <input type="number" id="add-emp-base" min="0" step="0.01" placeholder="0.00"
                               class="block w-full py-2 pl-7 pr-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Additional Income</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                        <input type="number" id="add-emp-additional" min="0" step="0.01" placeholder="0.00"
                               class="block w-full py-2 pl-7 pr-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Employer Phone</label>
                    <input type="text" id="add-emp-phone" placeholder="e.g. 02 9000 0000"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Employer Address</label>
                    <input type="text" id="add-emp-address" placeholder="e.g. 1 Market St, Sydney"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Assessor Note / Comment</label>
                <textarea id="add-emp-comment" rows="3" placeholder="Reason for adding this record…"
                          class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" id="emp-add-cancel"
                        class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button" id="emp-add-save"
                        class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                    Add Record
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- History Modal --}}
<div id="emp-history-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/40" id="emp-history-backdrop"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl p-6 max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-900">Change History</h4>
                <button type="button" id="emp-history-close" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <div id="emp-history-body"></div>
        </div>
    </div>
</div>

{{-- Config --}}
<script>
window.EMP_ADMIN_CONFIG = {
    canEdit:   @js($canEdit),
    csrfToken: @js(csrf_token()),
    routes:    @js($routes),
    employments: @js(
        $employments->map(fn($e) => [
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
            'monthly_income_after_tax'    => $e->getMonthlyAfterTaxIncome(),
            'history'                     => $e->history->map(fn($h) => [
                'field_label' => $h->field_label,
                'old_value'   => $h->old_value,
                'new_value'   => $h->new_value,
                'changed_by'  => optional($h->changedBy)->first_name . ' ' . optional($h->changedBy)->last_name,
                'changed_at'  => $h->changed_at->format('d M Y, g:i a'),
            ])->values(),
            'documents' => $e->documents->map(fn($d) => [
                'id'                => $d->id,
                'original_filename' => $d->original_filename,
                'file_size'         => $d->file_size_formatted,
                'download_url'      => route('admin.assessor-employment.documents.download', $d->id),
            ])->values(),
        ])->values()
    ),
};
</script>
<script src="{{ asset('js/admin/assessorEmployment.js') }}"></script>