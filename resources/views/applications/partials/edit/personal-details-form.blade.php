{{-- resources/views/applications/partials/edit/personal-details-form.blade.php --}}
@php
    $pd             = $application->personalDetails;
    $maritalStatus  = old('marital_status', $pd?->marital_status ?? '');
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">

    {{-- ── Header ──────────────────────────────────────────────────────── --}}
    <button type="button"
            id="personal-details-btn"
            aria-expanded="true"
            aria-controls="personal-details-content"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-left
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    Personal Details
                </h3>
                <p class="text-indigo-100 text-sm mt-1">Tell us about yourself</p>
            </div>
            <svg id="personal-details-chevron"
                 class="w-5 h-5 text-white transition-transform duration-200 rotate-180"
                 fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    <div id="personal-details-content" class="transition-all duration-300 ease-in-out">
        <div class="p-6">

            {{-- Completion badge --}}
            @if($application->hasCompletePersonalDetails())
                <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 rounded-lg"
                     role="status">
                    <div class="flex">
                        <svg class="h-6 w-6 text-green-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-green-800">Personal details completed</p>
                            <p class="text-xs text-green-700 mt-1">You can update your information below if needed.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Messages --}}
            <div id="form-messages"
                 class="mb-4"
                 role="status"
                 aria-live="polite"
                 aria-atomic="true"></div>

            <form id="personal-details"
                  method="POST"
                  action="{{ route('applications.personal-details.store', $application) }}"
                  novalidate>
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- ── Name fields (read-only) ──────────────────────── --}}
                    <div class="md:col-span-2">
                        <div class="flex flex-col gap-6 lg:flex-row">
                            @foreach([
                                ['first_name',      'First Name'],
                                ['middle_name',     'Middle Name'],
                                ['last_name',       'Last Name'],
                                ['name_extension',  'Name Extension'],
                            ] as [$field, $label])
                                <div class="{{ $field === 'name_extension' ? 'w-full lg:w-32 shrink-0' : 'flex-1 min-w-0' }}">
                                    <label for="{{ $field }}"
                                           class="block text-sm font-semibold text-gray-700 mb-2">
                                        {{ $label }}
                                    </label>
                                    <input type="text"
                                           id="{{ $field }}"
                                           name="{{ $field }}"
                                           value="{{ $application->user->{$field} }}"
                                           readonly
                                           aria-readonly="true"
                                           title="{{ $label }} is linked to your account and cannot be changed here."
                                           class="block w-full py-3 px-4 border-gray-300 bg-gray-100 text-gray-500
                                                  cursor-not-allowed rounded-xl shadow-sm focus:ring-0 focus:border-gray-300">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Email (read-only) --}}
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Email Address
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ $application->user?->email }}"
                               readonly
                               aria-readonly="true"
                               title="Email is linked to your account and cannot be changed here."
                               class="block w-full py-3 px-4 border-gray-300 bg-gray-100 text-gray-500
                                      cursor-not-allowed rounded-xl shadow-sm focus:ring-0 focus:border-gray-300">
                    </div>

                    {{-- Mobile --}}
                    <div>
                        <label for="mobile_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                            Mobile Phone <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input type="tel"
                               id="mobile_phone"
                               name="mobile_phone"
                               value="{{ old('mobile_phone', $pd?->mobile_phone) }}"
                               required
                               aria-required="true"
                               aria-describedby="mobile_phone-error"
                               class="mt-1 block w-full py-3 px-4 border border-gray-300 rounded-xl shadow-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p id="mobile_phone-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                    </div>

                    {{-- Date of Birth --}}
                    <div>
                        <label for="date_of_birth" class="block text-sm font-semibold text-gray-700 mb-2">
                            Date of Birth <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input type="date"
                               id="date_of_birth"
                               name="date_of_birth"
                               value="{{ old('date_of_birth', $pd?->date_of_birth?->format('Y-m-d')) }}"
                               required
                               aria-required="true"
                               aria-describedby="date_of_birth-error"
                               max="{{ now()->subYears(18)->format('Y-m-d') }}"
                               class="mt-1 block w-full py-3 px-4 border border-gray-300 rounded-xl shadow-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p id="date_of_birth-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                    </div>

                    {{-- Gender --}}
                    <div>
                        <label for="gender" class="block text-sm font-semibold text-gray-700 mb-2">
                            Gender
                        </label>
                        <select id="gender"
                                name="gender"
                                aria-describedby="gender-error"
                                class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select…</option>
                            @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $val => $lbl)
                                <option value="{{ $val }}" {{ old('gender', $pd?->gender) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        <p id="gender-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                    </div>

                    {{-- Contact Role --}}
                    <div>
                        <label for="contact_role" class="block text-sm font-semibold text-gray-700 mb-2">
                            Contact Role
                        </label>
                        <select id="contact_role"
                                name="contact_role"
                                aria-describedby="contact_role-hint contact_role-error"
                                class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select…</option>
                            @foreach(['director' => 'Director', 'sole_trader' => 'Sole Trader', 'partner' => 'Partner', 'other' => 'Other'] as $val => $lbl)
                                <option value="{{ $val }}" {{ old('contact_role', $pd?->contact_role) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        <p id="contact_role-hint" class="mt-1 text-xs text-gray-400">Your role in relation to this loan application.</p>
                        <p id="contact_role-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                    </div>

                    {{-- Citizenship Status --}}
                    <div>
                        <label for="citizenship_status" class="block text-sm font-semibold text-gray-700 mb-2">
                            Citizenship Status <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <select id="citizenship_status"
                                name="citizenship_status"
                                required
                                aria-required="true"
                                aria-describedby="citizenship_status-error"
                                class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select…</option>
                            @foreach([
                                'australian_citizen' => 'Australian Citizen',
                                'permanent_resident' => 'Permanent Resident',
                                'temporary_resident' => 'Temporary Resident',
                                'nz_citizen'         => 'NZ Citizen',
                            ] as $val => $lbl)
                                <option value="{{ $val }}" {{ old('citizenship_status', $pd?->citizenship_status) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        <p id="citizenship_status-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                    </div>

                    {{-- Marital Status --}}
                    <div>
                        <label for="marital_status" class="block text-sm font-semibold text-gray-700 mb-2">
                            Marital Status <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <select id="marital_status"
                                name="marital_status"
                                required
                                aria-required="true"
                                aria-controls="spouse-fields"
                                aria-describedby="marital_status-error"
                                class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select…</option>
                            @foreach(['single' => 'Single', 'married' => 'Married', 'defacto' => 'De Facto', 'divorced' => 'Divorced', 'widowed' => 'Widowed'] as $val => $lbl)
                                <option value="{{ $val }}" {{ $maritalStatus === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        <p id="marital_status-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                    </div>

                    {{-- Number of Dependants --}}
                    <div>
                        <label for="number_of_dependants" class="block text-sm font-semibold text-gray-700 mb-2">
                            Number of Dependants <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input type="number"
                               id="number_of_dependants"
                               name="number_of_dependants"
                               value="{{ old('number_of_dependants', $pd?->number_of_dependants ?? 0) }}"
                               min="0"
                               required
                               aria-required="true"
                               aria-describedby="number_of_dependants-error"
                               inputmode="numeric"
                               class="mt-1 block w-full py-3 px-4 border border-gray-300 rounded-xl shadow-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p id="number_of_dependants-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                    </div>

                    {{-- ── Spouse fields (conditional: married / defacto) ── --}}
                    <fieldset id="spouse-fields"
                              class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6
                                     {{ in_array($maritalStatus, ['married', 'defacto']) ? '' : 'hidden' }}"
                              aria-label="Spouse or partner details">

                        {{-- Spouse Name --}}
                        <div>
                            <label for="spouse_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Spouse / Partner Name
                                <span id="spouse-name-required" class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input type="text"
                                   id="spouse_name"
                                   name="spouse_name"
                                   value="{{ old('spouse_name', $pd?->spouse_name) }}"
                                   aria-describedby="spouse_name-error"
                                   placeholder="Full legal name"
                                   class="mt-1 block w-full py-3 px-4 border border-gray-300 rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p id="spouse_name-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- Spouse Income — display input (text + commas) + hidden raw value for submission --}}
                        <div>
                            <label for="spouse_income_display" class="block text-sm font-semibold text-gray-700 mb-2">
                                Spouse / Partner Annual Income
                                <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none"
                                      aria-hidden="true">$</span>
                                <input type="text"
                                       id="spouse_income_display"
                                       inputmode="decimal"
                                       placeholder="0.00"
                                       autocomplete="off"
                                       aria-describedby="spouse_income-hint spouse_income-error"
                                       class="block w-full py-3 pl-8 pr-4 border border-gray-300 rounded-xl shadow-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <input type="hidden"
                                       id="spouse_income"
                                       name="spouse_income"
                                       value="{{ old('spouse_income', $pd?->spouse_income) }}">
                            </div>
                            <p id="spouse_income-hint" class="mt-1 text-xs text-gray-400">Before tax, per year.</p>
                            <p id="spouse_income-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                    </fieldset>

                </div>{{-- /grid --}}

                {{-- Submit --}}
                <div class="mt-6 flex justify-end">
                    <button type="submit"
                            id="submit-button"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600
                                text-white rounded-xl font-semibold text-sm uppercase tracking-wide
                                hover:shadow-lg transition transform hover:scale-105
                                disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none
                                focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg id="submit-spinner"
                            class="hidden animate-spin w-4 h-4 mr-2"
                            fill="none" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg id="submit-check-icon"
                            class="w-4 h-4 mr-2"
                            fill="currentColor" viewBox="0 0 20 20"
                            aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span id="submit-button-text">
                            {{ $pd ? 'Update' : 'Save' }} Personal Details
                        </span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>