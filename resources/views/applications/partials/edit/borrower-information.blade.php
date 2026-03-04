{{-- resources/views/applications/partials/edit/borrower-information.blade.php --}}
@php
    $borrower  = $application->borrowerInformation;
    $directors = $application->borrowerDirectors ?? collect();
    $showDirectors = in_array($borrower?->borrower_type, ['company', 'trust']);
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <button type="button"
            id="borrower-btn"
            aria-expanded="true"
            aria-controls="borrower-content"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-left
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    Borrower Information
                </h3>
                <p class="text-indigo-100 text-sm mt-1">Details about the loan applicant or entity</p>
            </div>
            <svg id="borrower-chevron"
                 class="w-5 h-5 text-white transition-transform duration-200 rotate-180"
                 fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    <div id="borrower-content" class="transition-all duration-300 ease-in-out">
        <div class="p-6">

            {{-- Toast ───────────────────────────────────────────────────── --}}
            <div id="borrower-messages"
                 class="mb-4 hidden"
                 role="status"
                 aria-live="polite"
                 aria-atomic="true"
                 tabindex="-1"></div>

            {{-- ── Borrower form ──────────────────────────────────────── --}}
            <form id="borrower-form" aria-label="Borrower information form" novalidate>
                @csrf

                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Borrower Name --}}
                        <div class="md:col-span-2">
                            <label for="borrower-name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Borrower Name <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input type="text"
                                   id="borrower-name"
                                   name="borrower_name"
                                   value="{{ old('borrower_name', $borrower?->borrower_name) }}"
                                   required
                                   aria-required="true"
                                   aria-describedby="borrower-name-error"
                                   placeholder="Full legal name or entity name"
                                   class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p id="borrower-name-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- Borrower Type --}}
                        <div>
                            <label for="borrower-type" class="block text-sm font-semibold text-gray-700 mb-2">
                                Borrower Type <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <select id="borrower-type"
                                    name="borrower_type"
                                    required
                                    aria-required="true"
                                    aria-controls="director-section"
                                    aria-describedby="borrower-type-hint borrower-type-error"
                                    class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select type…</option>
                                @foreach(['individual' => 'Individual', 'company' => 'Company', 'trust' => 'Trust', 'other' => 'Other'] as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('borrower_type', $borrower?->borrower_type) === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <p id="borrower-type-hint" class="mt-1 text-xs text-gray-400">
                                Company and Trust borrowers will be asked for director details.
                            </p>
                            <p id="borrower-type-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- ABN --}}
                        <div>
                            <label for="borrower-abn" class="block text-sm font-semibold text-gray-700 mb-2">
                                ABN
                                <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span>
                            </label>
                            <input type="text"
                                   id="borrower-abn"
                                   name="abn"
                                   value="{{ old('abn', $borrower?->abn) }}"
                                   inputmode="numeric"
                                   maxlength="14"
                                   aria-describedby="borrower-abn-hint borrower-abn-error"
                                   placeholder="XX XXX XXX XXX"
                                   class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p id="borrower-abn-hint" class="mt-1 text-xs text-gray-400">11-digit Australian Business Number.</p>
                            <p id="borrower-abn-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- Nature of Business --}}
                        <div>
                            <label for="borrower-nature" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nature of Business
                            </label>
                            <input type="text"
                                   id="borrower-nature"
                                   name="nature_of_business"
                                   value="{{ old('nature_of_business', $borrower?->nature_of_business) }}"
                                   aria-describedby="borrower-nature-error"
                                   placeholder="e.g. Retail, Construction, Healthcare"
                                   class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p id="borrower-nature-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- Years in Business --}}
                        <div>
                            <label for="borrower-years" class="block text-sm font-semibold text-gray-700 mb-2">
                                Years in Business
                            </label>
                            <input type="number"
                                   id="borrower-years"
                                   name="years_in_business"
                                   value="{{ old('years_in_business', $borrower?->years_in_business) }}"
                                   min="0"
                                   max="200"
                                   inputmode="numeric"
                                   aria-describedby="borrower-years-error"
                                   placeholder="e.g. 5"
                                   class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p id="borrower-years-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit"
                            id="borrower-submit-btn"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600
                                   text-white rounded-xl font-semibold text-sm uppercase tracking-wide
                                   hover:shadow-lg transition transform hover:scale-105
                                   disabled:opacity-50 disabled:cursor-not-allowed
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg id="borrower-spinner" class="hidden animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg id="borrower-save-icon" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293z"/>
                        </svg>
                        <span id="borrower-submit-label">Save Borrower Information</span>
                    </button>
                </div>
            </form>

            {{-- ══════════════════════════════════════════════════════════ --}}
            {{-- ── Director Section (Company / Trust only) ────────────── --}}
            {{-- ══════════════════════════════════════════════════════════ --}}
            <section id="director-section"
                     class="{{ $showDirectors ? '' : 'hidden' }} mt-8"
                     aria-label="Directors and trustees"
                     aria-live="polite">

                <div class="border-t border-gray-200 pt-6">

                    {{-- Section header --}}
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 id="director-section-heading"
                                class="text-base font-bold text-gray-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-1a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v1h-3zM4.75 14.094A5.973 5.973 0 004 17v1H1v-1a3 3 0 013.75-2.906z"/>
                                </svg>
                                <span id="director-section-title">Directors / Trustees</span>
                            </h4>
                            <p class="text-sm text-gray-500 mt-0.5" id="director-section-desc">
                                Add all directors or trustees associated with this borrower entity.
                            </p>
                        </div>
                        <button type="button"
                                id="add-director-btn"
                                aria-expanded="false"
                                aria-controls="director-form-panel"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                                       text-sm font-semibold rounded-xl hover:bg-indigo-700
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                       transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Director
                        </button>
                    </div>

                    {{-- Director toast --}}
                    <div id="director-messages"
                         class="mb-4 hidden"
                         role="status"
                         aria-live="polite"
                         aria-atomic="true"
                         tabindex="-1"></div>

                    {{-- ── Existing directors list ────────────────────── --}}
                    <div id="directors-list"
                         class="space-y-3 mb-4"
                         aria-label="Existing directors">
                        @forelse($directors as $director)
                            <div class="director-card flex items-center justify-between p-4
                                        bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl
                                        border border-gray-200 hover:shadow-md transition"
                                 data-director-id="{{ $director->id }}">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0" aria-hidden="true">
                                        <svg class="h-5 w-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $director->full_name }}</p>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            @if($director->email)
                                                <span class="text-xs text-gray-500">{{ $director->email }}</span>
                                            @endif
                                            @if($director->ownership_percentage !== null)
                                                <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">
                                                    {{ $director->ownership_percentage }}% ownership
                                                </span>
                                            @endif
                                            @if($director->is_guarantor)
                                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                                                    Guarantor
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <button type="button"
                                        data-director-id="{{ $director->id }}"
                                        aria-label="Remove director {{ $director->full_name }}"
                                        class="delete-director-btn inline-flex items-center px-3 py-2 bg-red-50
                                               text-red-700 rounded-lg text-sm font-medium hover:bg-red-100
                                               focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1
                                               transition flex-shrink-0">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Remove
                                </button>
                            </div>
                        @empty
                            <div id="directors-empty"
                                 class="text-center py-8 text-sm text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                No directors added yet. Click "Add Director" to begin.
                            </div>
                        @endforelse
                    </div>

                    {{-- ── Add director form (collapsed by default) ───── --}}
                    <div id="director-form-panel"
                         class="hidden bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl
                                p-6 border border-indigo-100"
                         aria-label="Add director form">

                        <h5 class="text-sm font-semibold text-gray-800 mb-4">New Director / Trustee</h5>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            {{-- Full Name --}}
                            <div class="md:col-span-2">
                                <label for="dir-name" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Full Name <span class="text-red-500" aria-hidden="true">*</span>
                                </label>
                                <input type="text"
                                       id="dir-name"
                                       name="dir_full_name"
                                       aria-required="true"
                                       aria-describedby="dir-name-error"
                                       placeholder="Legal full name"
                                       class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <p id="dir-name-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="dir-email" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Email
                                </label>
                                <input type="email"
                                       id="dir-email"
                                       name="dir_email"
                                       aria-describedby="dir-email-error"
                                       placeholder="director@example.com"
                                       class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <p id="dir-email-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                            </div>

                            {{-- Phone --}}
                            <div>
                                <label for="dir-phone" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Phone
                                </label>
                                <input type="tel"
                                       id="dir-phone"
                                       name="dir_phone"
                                       placeholder="04XX XXX XXX"
                                       class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            {{-- Date of Birth --}}
                            <div>
                                <label for="dir-dob" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Date of Birth
                                </label>
                                <input type="date"
                                       id="dir-dob"
                                       name="dir_date_of_birth"
                                       max="{{ now()->subYears(18)->format('Y-m-d') }}"
                                       class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            {{-- Ownership % --}}
                            <div>
                                <label for="dir-ownership" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Ownership %
                                </label>
                                <input type="number"
                                       id="dir-ownership"
                                       name="dir_ownership_percentage"
                                       min="0"
                                       max="100"
                                       step="0.01"
                                       inputmode="decimal"
                                       placeholder="e.g. 50"
                                       aria-describedby="dir-ownership-error"
                                       class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <p id="dir-ownership-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                            </div>

                            {{-- Guarantor --}}
                            <div class="md:col-span-2 flex items-center gap-3">
                                <input type="checkbox"
                                       id="dir-guarantor"
                                       name="dir_is_guarantor"
                                       value="1"
                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600
                                              focus:ring-2 focus:ring-indigo-500">
                                <label for="dir-guarantor" class="text-sm font-medium text-gray-700">
                                    This director / trustee is a guarantor for the loan
                                </label>
                            </div>

                        </div>

                        {{-- Form actions --}}
                        <div class="flex items-center justify-end gap-3 mt-5">
                            <button type="button"
                                    id="cancel-director-btn"
                                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300
                                           rounded-xl hover:bg-gray-50
                                           focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1
                                           transition">
                                Cancel
                            </button>
                            <button type="button"
                                    id="save-director-btn"
                                    class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white
                                           text-sm font-semibold rounded-xl hover:bg-indigo-700
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                           disabled:opacity-50 disabled:cursor-not-allowed transition">
                                <svg id="dir-spinner" class="hidden animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span id="dir-save-label">Add Director</span>
                            </button>
                        </div>
                    </div>

                </div>
            </section>

        </div>{{-- /p-6 --}}
    </div>{{-- /borrower-content --}}
</div>

<script>
Object.assign(window.BORROWER_CONFIG, {
    applicationId: @js($application->id),
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content,
    routes: {
        borrowerStore: @js(route('applications.borrower-information.store', $application)),
        directorStore: @js(route('applications.directors.store', $application)),
        directorDelete: @js(route('applications.directors.destroy', [$application, ':id']))
    }
});
</script>
