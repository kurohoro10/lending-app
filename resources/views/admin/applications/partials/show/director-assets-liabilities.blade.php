@php
    $assets       = $application->directorAssets;
    $liabilities  = $application->directorLiabilities;
    $net          = $assets->sum('estimated_value') - $liabilities->sum('outstanding_balance');

    $verification = $application->assessorDalVerification;
    $isUnlocked   = $verification !== null;
    $isStamped    = $isUnlocked && $verification->isStamped();

    $isAdmin           = auth()->user()->hasRole('admin');
    $isAssignedAssessor = auth()->user()->hasRole('assessor') && auth()->id() === $application->assigned_to;
    $canEdit           = $isUnlocked && ($isAdmin || $isAssignedAssessor);
    $hasAssignedUser   = $application->assigned_to !== null;

    $routes = [
        'unlock'           => route('admin.assessor-dal.unlock',            $application),
        'stamp'            => route('admin.assessor-dal.stamp',             $application),
        'assetStore'       => route('admin.assessor-dal.assets.store',      $application),
        'assetUpdate'      => route('admin.assessor-dal.assets.update',     ':id'),
        'assetDestroy'     => route('admin.assessor-dal.assets.destroy',    ':id'),
        'liabilityStore'   => route('admin.assessor-dal.liabilities.store', $application),
        'liabilityUpdate'  => route('admin.assessor-dal.liabilities.update',  ':id'),
        'liabilityDestroy' => route('admin.assessor-dal.liabilities.destroy', ':id'),
    ];
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">

        {{-- ── Header ──────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM2 9v7a2 2 0 002 2h12a2 2 0 002-2V9H2zm4 2h8a1 1 0 010 2H6a1 1 0 010-2z"/>
                </svg>
                Director Assets &amp; Liabilities
            </h3>

            <div class="flex items-center gap-3">
                {{-- Stamp badge --}}
                <span id="dal-stamp-badge"
                      class="{{ $isStamped ? '' : 'hidden' }} inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span id="dal-stamp-text">
                        @if($isStamped)
                            Verified by {{ $verification->verifiedBy->first_name }} {{ $verification->verifiedBy->last_name }}
                            — {{ $verification->verified_at->format('d M Y, g:i a') }}
                        @endif
                    </span>
                </span>

                {{-- Admin: unlock button (only shows if not yet unlocked) --}}
                @if($isAdmin && !$isUnlocked && $hasAssignedUser)
                    <button type="button" id="dal-unlock-btn"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                                   text-sm font-semibold rounded-lg hover:bg-indigo-700
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                        </svg>
                        Allow Assessor to Edit
                    </button>
                @endif

                @if($isAdmin && !$isUnlocked && !$hasAssignedUser)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-400"
                          title="Assign an assessor first">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        Assign an assessor first
                    </span>
                @endif

                {{-- Save & Verify / Re-verify (shows when unlocked, for both admin and assessor) --}}
                @if($canEdit)
                    <button type="button" id="dal-stamp-btn"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white
                                   text-sm font-semibold rounded-lg hover:bg-green-700
                                   focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $isStamped ? 'Re-verify' : 'Save & Verify' }}
                    </button>
                @endif
            </div>
        </div>

        {{-- Flash --}}
        <div id="dal-flash" class="hidden mb-4 px-4 py-2 rounded-lg text-sm font-medium" role="status" aria-live="polite"></div>

        {{-- ── Add buttons (only when unlocked and can edit) ───────────────── --}}
        @if($canEdit)
        <div class="flex gap-2 mb-4">
            <button type="button" id="dal-add-asset-btn"
                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 text-white
                           text-xs font-semibold rounded-lg hover:bg-emerald-700 transition">
                + Add Asset
            </button>
            <button type="button" id="dal-add-liability-btn"
                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-500 text-white
                           text-xs font-semibold rounded-lg hover:bg-red-600 transition">
                + Add Liability
            </button>
        </div>
        @endif

        {{-- ── Assets ───────────────────────────────────────────────────────── --}}
        @if($assets->count() > 0 || $canEdit)
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Assets</h4>
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property Use</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owned</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ownership %</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">History</th>
                            @if($canEdit)<th class="px-4 py-2"></th>@endif
                        </tr>
                    </thead>
                    <tbody id="dal-assets-tbody" class="bg-white divide-y divide-gray-100">
                        @foreach($assets as $asset)
                        <tr data-asset-id="{{ $asset->id }}">
                            <td class="px-4 py-2 text-gray-900">{{ $asset->asset_type_label }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $asset->description ?? '—' }}</td>
                            <td class="px-4 py-2 text-gray-600">
                                @if($asset->asset_type === 'house')
                                    {{ $asset->property_use === 'main_residence' ? 'Main Residence' : 'Rental' }}
                                @else —
                                @endif
                            </td>
                            <td class="px-4 py-2 text-gray-600">{{ $asset->is_owned ? 'Yes' : 'No' }}</td>
                            <td class="px-4 py-2 text-right text-gray-600">
                                {{ $asset->ownership_percentage !== null ? $asset->ownership_percentage . '%' : '100%' }}
                            </td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900">
                                ${{ number_format($asset->estimated_value, 2) }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                @if($asset->history->count() > 0)
                                    <button type="button"
                                            data-history-type="asset"
                                            data-entry-id="{{ $asset->id }}"
                                            class="dal-history-btn text-indigo-500 hover:text-indigo-700 text-xs underline">
                                        {{ $asset->history->count() }} change(s)
                                    </button>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            @if($canEdit)
                            <td class="px-4 py-2 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" data-edit-type="asset" data-entry-id="{{ $asset->id }}"
                                            class="dal-edit-btn text-indigo-500 hover:text-indigo-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button type="button" data-delete-type="asset" data-entry-id="{{ $asset->id }}"
                                            class="dal-delete-btn text-red-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="{{ $canEdit ? 6 : 6 }}" class="px-4 py-2 text-sm font-semibold text-gray-700 text-right">Total</td>
                            <td id="dal-assets-total" class="px-4 py-2 text-right font-bold text-green-700">
                                ${{ number_format($assets->sum('estimated_value'), 2) }}
                            </td>
                            <td @if($canEdit) colspan="2" @endif></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        {{-- ── Add Asset Form ───────────────────────────────────────────────── --}}
        @if($canEdit)
        <div id="dal-asset-form" class="hidden mb-6 bg-emerald-50 rounded-lg p-5 border border-emerald-100">
            <h5 class="text-sm font-semibold text-gray-800 mb-4">New Asset</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Asset Type <span class="text-red-500">*</span></label>
                    <select id="dal-af-type" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                        <option value="">Select…</option>
                        <option value="house">House / Property</option>
                        <option value="bank">Bank Account</option>
                        <option value="super">Superannuation</option>
                        <option value="vehicle">Vehicle</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div id="dal-af-propuse-wrap" class="hidden">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Property Use <span class="text-red-500">*</span></label>
                    <select id="dal-af-propuse" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                        <option value="">Select…</option>
                        <option value="main_residence">Main Residence</option>
                        <option value="rental">Rental / Investment</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Description</label>
                    <input type="text" id="dal-af-desc" placeholder="e.g. 12 Example St"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Owned <span class="text-red-500">*</span></label>
                    <select id="dal-af-owned" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Ownership %</label>
                    <input type="number" id="dal-af-pct" min="0" max="100" placeholder="e.g. 50"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Estimated Value <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                        <input type="number" id="dal-af-value" min="0" step="0.01" placeholder="0.00"
                               class="block w-full py-2 pl-7 pr-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" id="dal-af-cancel"
                        class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button" id="dal-af-save"
                        class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition">
                    Add Asset
                </button>
            </div>
        </div>
        @endif

        {{-- ── Liabilities ──────────────────────────────────────────────────── --}}
        @if($liabilities->count() > 0 || $canEdit)
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Liabilities</h4>
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lender</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Limit</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">History</th>
                            @if($canEdit)<th class="px-4 py-2"></th>@endif
                        </tr>
                    </thead>
                    <tbody id="dal-liabilities-tbody" class="bg-white divide-y divide-gray-100">
                        @foreach($liabilities as $liability)
                        <tr data-liability-id="{{ $liability->id }}">
                            <td class="px-4 py-2 text-gray-900">{{ $liability->liability_type_label }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $liability->lender_name ?? '—' }}</td>
                            <td class="px-4 py-2 text-right text-gray-600">
                                {{ $liability->credit_limit !== null ? '$' . number_format($liability->credit_limit, 2) : '—' }}
                            </td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900">
                                ${{ number_format($liability->outstanding_balance, 2) }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                @if($liability->history->count() > 0)
                                    <button type="button"
                                            data-history-type="liability"
                                            data-entry-id="{{ $liability->id }}"
                                            class="dal-history-btn text-indigo-500 hover:text-indigo-700 text-xs underline">
                                        {{ $liability->history->count() }} change(s)
                                    </button>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                            @if($canEdit)
                            <td class="px-4 py-2 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" data-edit-type="liability" data-entry-id="{{ $liability->id }}"
                                            class="dal-edit-btn text-indigo-500 hover:text-indigo-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button type="button" data-delete-type="liability" data-entry-id="{{ $liability->id }}"
                                            class="dal-delete-btn text-red-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-sm font-semibold text-gray-700 text-right">Total</td>
                            <td id="dal-liabilities-total" class="px-4 py-2 text-right font-bold text-red-600">
                                ${{ number_format($liabilities->sum('outstanding_balance'), 2) }}
                            </td>
                            <td @if($canEdit) colspan="2" @endif></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        {{-- ── Add Liability Form ───────────────────────────────────────────── --}}
        @if($canEdit)
        <div id="dal-liability-form" class="hidden mb-6 bg-red-50 rounded-lg p-5 border border-red-100">
            <h5 class="text-sm font-semibold text-gray-800 mb-4">New Liability</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Liability Type <span class="text-red-500">*</span></label>
                    <select id="dal-lf-type" class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-red-400">
                        <option value="">Select…</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="home_loan">Home Loan</option>
                        <option value="car_loan">Car Loan</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Lender Name</label>
                    <input type="text" id="dal-lf-lender" placeholder="e.g. Commonwealth Bank"
                           class="block w-full py-2 px-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-red-400">
                </div>
                <div id="dal-lf-limit-wrap" class="hidden">
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Credit Limit <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                        <input type="number" id="dal-lf-limit" min="0" step="0.01" placeholder="0.00"
                               class="block w-full py-2 pl-7 pr-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-red-400">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Outstanding Balance <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                        <input type="number" id="dal-lf-balance" min="0" step="0.01" placeholder="0.00"
                               class="block w-full py-2 pl-7 pr-3 border border-gray-300 bg-white rounded-lg text-sm focus:ring-2 focus:ring-red-400">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" id="dal-lf-cancel"
                        class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button" id="dal-lf-save"
                        class="px-4 py-2 text-sm font-semibold text-white bg-red-500 rounded-lg hover:bg-red-600 transition">
                    Add Liability
                </button>
            </div>
        </div>
        @endif

        {{-- ── Net Position ─────────────────────────────────────────────────── --}}
        <div class="flex flex-wrap gap-6 justify-end text-sm font-semibold pt-2 border-t border-gray-100">
            <span>Assets: <span id="dal-net-assets" class="text-green-700">${{ number_format($assets->sum('estimated_value'), 2) }}</span></span>
            <span>Liabilities: <span id="dal-net-liabilities" class="text-red-600">${{ number_format($liabilities->sum('outstanding_balance'), 2) }}</span></span>
            <span>Net: <span id="dal-net-total" class="{{ $net >= 0 ? 'text-green-700' : 'text-red-600' }}">${{ number_format($net, 2) }}</span></span>
        </div>

    </div>
</div>

{{-- ── Edit Modal ────────────────────────────────────────────────────────── --}}
@if($canEdit)
<div id="dal-edit-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/40" id="dal-modal-backdrop"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6">
            <h4 class="text-base font-semibold text-gray-900 mb-4">Edit Entry</h4>
            <div id="dal-modal-body" class="space-y-4"></div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" id="dal-modal-cancel"
                        class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button" id="dal-modal-save"
                        class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── History Modal ─────────────────────────────────────────────────────── --}}
<div id="dal-history-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/40" id="dal-history-backdrop"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl p-6 max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-base font-semibold text-gray-900">Change History</h4>
                <button type="button" id="dal-history-close" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <div id="dal-history-body"></div>
        </div>
    </div>
</div>

{{-- ── Config ────────────────────────────────────────────────────────────── --}}
<script>
window.DAL_ADMIN_CONFIG = {
    canEdit:    @js($canEdit),
    isUnlocked: @js($isUnlocked),
    csrfToken:  @js(csrf_token()),
    routes:     @js($routes),
    assets:     @js(
        $assets->map(fn($a) => [
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
            ])->values(),
        ])->values()
    ),
    liabilities: @js(
        $liabilities->map(fn($l) => [
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
            ])->values(),
        ])->values()
    ),
};
</script>
<script src="{{ asset('js/admin/assessorDal.js') }}"></script>