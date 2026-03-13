{{-- resources/views/applications/partials/edit/residential-addresses-form.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">
    <button type="button"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-left
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            id="residential-addresses-btn"
            aria-expanded="true"
            aria-controls="residential-addresses-content">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    Residential Address History
                </h3>
                <p class="text-indigo-100 text-sm mt-1">Provide your address history for the past 3 years</p>
            </div>
            <svg id="residential-addresses-chevron"
                 class="w-5 h-5 text-white transition-transform duration-200 transform rotate-180"
                 fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    <div id="residential-addresses-content"
         class="transition-all duration-300 ease-in-out"
         aria-labelledby="residential-addresses-btn">
        <div class="p-6">

            {{-- Messages --}}
            <div id="address-messages" tabindex="-1" class="mb-4"
                 role="status" aria-live="polite" aria-atomic="true"></div>

            {{-- Address list --}}
            <div id="address-list-container">
                @if($application->residentialAddresses->count() > 0)
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-semibold text-gray-900">Your Address History</h4>
                            <span id="address-count-badge"
                                  class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                {{ $application->residentialAddresses->count() }} Address(es)
                            </span>
                        </div>
                        <div id="address-list" class="space-y-3" data-addresses-section>
                            @include('applications/partials.edit.address-card-foreach')
                        </div>
                    </div>
                @endif
            </div>

            {{-- ── Add address form ──────────────────────────────────────── --}}
            <form id="residential-address-form"
                  method="POST"
                  action="{{ route('applications.residential-addresses.store', $application) }}">
                @csrf

                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-6 border-2 border-indigo-100 mb-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-1 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        Add New Address
                    </h4>
                    <p class="text-sm text-gray-600 mb-6">Fill in the details of your residential address</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Address Type --}}
                        <div>
                            <label for="address-type-select"
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                Address Type <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <select name="address_type" id="address-type-select" required
                                    aria-describedby="address_type-error"
                                    class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select address type…</option>
                                <option value="current">Current Address</option>
                                <option value="previous_1">Previous Address (Year 1)</option>
                                <option value="previous_2">Previous Address (Year 2)</option>
                                <option value="previous_3">Previous Address (Year 3)</option>
                            </select>
                            <p id="address_type-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- Residential Status --}}
                        <div>
                            <label for="residential-status-select"
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                Residential Status <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <select name="residential_status" id="residential-status-select" required
                                    aria-describedby="residential_status-error"
                                    class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select status…</option>
                                @foreach(\App\Helpers\AustralianSuburbs::getResidentialStatuses() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <p id="residential_status-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- Street Address --}}
                        <div class="md:col-span-2">
                            <label for="street-address-input"
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                Street Address <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <input type="text"
                                   name="street_address"
                                   id="street-address-input"
                                   required
                                   aria-describedby="street_address-error"
                                   placeholder="123 Main Street"
                                   class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p id="street_address-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- ── Suburb Typeahead ──────────────────────────── --}}
                        <div class="md:col-span-2">
                            <label for="suburb-typeahead"
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                Suburb <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <div class="relative">
                                <input type="text"
                                       id="suburb-typeahead"
                                       autocomplete="off"
                                       aria-autocomplete="list"
                                       aria-controls="suburb-suggestions"
                                       aria-describedby="suburb-typeahead-hint suburb-error"
                                       placeholder="Start typing a suburb…"
                                       class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">

                                {{-- Hidden inputs that carry the actual submitted values --}}
                                <input type="hidden" name="suburb"   id="suburb-hidden">
                                <input type="hidden" name="state"    id="state-hidden">

                                {{-- Suggestions dropdown --}}
                                <ul id="suburb-suggestions"
                                    role="listbox"
                                    aria-label="Suburb suggestions"
                                    class="hidden absolute z-30 w-full mt-1 bg-white border border-gray-200
                                           rounded-xl shadow-lg max-h-56 overflow-y-auto divide-y divide-gray-100">
                                </ul>
                            </div>
                            <p id="suburb-typeahead-hint" class="mt-1 text-xs text-gray-400">
                                Type at least 2 characters to see suggestions.
                            </p>
                            <p id="suburb-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- State (auto-filled, read-only display) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                State
                            </label>
                            <div id="state-display"
                                 class="block w-full py-3 px-4 border border-gray-200 bg-gray-50
                                        rounded-xl text-gray-500 text-sm select-none"
                                 aria-live="polite">
                                Auto-filled from suburb
                            </div>
                        </div>

                        {{-- Postcode --}}
                        <div>
                            <label for="postcode-input"
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                Postcode <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <input type="text"
                                   name="postcode"
                                   id="postcode-input"
                                   required
                                   pattern="[0-9]{4}"
                                   maxlength="4"
                                   inputmode="numeric"
                                   aria-describedby="postcode-hint postcode-error"
                                   placeholder="2000"
                                   class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p id="postcode-hint" class="mt-1 text-xs text-gray-400">
                                Auto-filled from suburb — edit if needed.
                            </p>
                            <p id="postcode-error" class="mt-1 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- Start Date --}}
                        <div>
                            <label for="start-date-input"
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                Start Date <span class="text-red-600" aria-hidden="true">*</span>
                            </label>
                            <input type="date"
                                   name="start_date"
                                   id="start-date-input"
                                   required
                                   aria-describedby="start_date-error"
                                   class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p id="start_date-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                        {{-- End Date --}}
                        <div>
                            <label for="end-date-input"
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                End Date
                                <span class="text-gray-400 font-normal text-xs ml-1">(leave blank if current)</span>
                            </label>
                            <input type="date"
                                   name="end_date"
                                   id="end-date-input"
                                   aria-describedby="end_date-error"
                                   class="block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p id="end_date-error" class="mt-2 text-sm text-red-600 hidden" role="alert"></p>
                        </div>

                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end">
                    <button type="submit"
                            id="submit-address-button"
                            class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600
                                   text-white rounded-xl font-bold text-sm uppercase tracking-wide
                                   hover:shadow-xl transition transform hover:scale-105
                                   disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg id="submit-address-spinner"
                             class="hidden animate-spin w-5 h-5 mr-2"
                             fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg id="submit-address-plus-icon"
                             class="w-5 h-5 mr-2"
                             fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        <span id="submit-address-text">Add Address</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
window.RESIDENTIAL_CONFIG = Object.assign(window.RESIDENTIAL_CONFIG ?? {}, {
    applicationId: @js($application->id),
    suburbSearchUrl: @js(route('api.suburbs.search')),
    deleteRoute: @js(route('applications.residential-addresses.destroy', [$application, ':id']))
});
</script>