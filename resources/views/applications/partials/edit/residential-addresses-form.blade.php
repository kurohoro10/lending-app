<!-- Residential Addresses Section - Enhanced with Fetch API -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">
    <button type="button"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 text-left focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            id="residential-addresses-btn"
            aria-expanded="true"
            aria-controls="residential-addresses-content">
        <div class="flex items-center justify-between">
            <div>

                <h3 class="text-lg font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    Residential Address History
                </h3>
                <p class="text-indigo-100 text-sm mt-1">Provide your address history for the past 3 years</p>
            </div>
            <!-- Chevron Icon -->
            <svg id="residential-addresses-chevron" class="w-5 h-5 text-white transition-transform duration-200 transform rotate-180" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    <div id="residential-addresses-content"
         class="transition-all duration-300 ease-in-out p-6"
         aria-labelledby="residential-addresses-header">
        <div class="p-6">
            <!-- Success/Error Messages Container -->
            <div id="address-messages" tabindex="-1" class="mb-4" role="status" aria-live="polite" aria-atomic="true"></div>

            <!-- Address List Container -->
            <div id="address-list-container">
                @if($application->residentialAddresses->count() > 0)
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-gray-900">Your Address History</h4>
                        <span id="address-count-badge" class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            {{ $application->residentialAddresses->count() }} Address(es)
                        </span>
                    </div>

                    <div id="address-list" class="space-y-3" data-addresses-section>
                        @foreach($application->residentialAddresses->sortBy('address_type') as $address)
                        <div data-address-card class="address-item p-5 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border-2 border-gray-200 hover:shadow-lg hover:border-indigo-200 transition-all" data-address-id="{{ $address->id }}">
                            <div class="flex justify-between items-start">
                                <div class="flex items-start space-x-4 flex-1">
                                    <div class="flex-shrink-0">
                                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                                            <svg class="h-6 w-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center flex-wrap gap-2 mb-2">
                                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold">
                                                {{ ucwords(str_replace('_', ' ', $address->address_type)) }}
                                            </span>
                                            <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold">
                                                {{ ucwords(str_replace('_', ' ', $address->residential_status ?? 'N/A')) }}
                                            </span>
                                            <span class="text-xs text-gray-500 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $address->start_date ? $address->start_date->format('M Y') : 'N/A' }} - {{ $address->end_date ? $address->end_date->format('M Y') : 'Present' }}
                                            </span>
                                        </div>
                                        <div class="text-sm font-semibold text-gray-900 mb-1">
                                            {{ $address->street_address ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            {{ $address->suburb ?? 'N/A' }}, {{ $address->state ?? 'N/A' }} {{ $address->postcode ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                                <button type="button"
                                        data-address-id="{{ $address->id }}"
                                        aria-label="Delete address record {{ $address->address_type }}"
                                        class="ml-4 inline-flex items-center px-4 py-2 bg-red-50 text-red-700 rounded-xl text-sm font-semibold hover:bg-red-100 transition-all hover:shadow-md delete-address-btn">
                                    <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Add New Address Form -->
            <form id="residential-address-form" method="POST" action="{{ route('applications.residential-addresses.store', $application) }}">
                @csrf

                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-6 border-2 border-indigo-100 mb-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-1 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        Add New Address
                    </h4>
                    <p class="text-sm text-gray-600 mb-6">Fill in the details of your residential address</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Address Type -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Address Type <span class="text-red-600">*</span>
                            </label>
                            <select name="address_type" id="address-type-select" required
                                    class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select address type...</option>
                                <option value="current">Current Address</option>
                                <option value="previous_1">Previous Address (Year 1)</option>
                                <option value="previous_2">Previous Address (Year 2)</option>
                                <option value="previous_3">Previous Address (Year 3)</option>
                            </select>
                            <p id="address_type-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <!-- Residential Status -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Residential Status <span class="text-red-600">*</span>
                            </label>
                            <select name="residential_status" required
                                    class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select status...</option>
                                @foreach(\App\Helpers\AustralianSuburbs::getResidentialStatuses() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <p id="residential_status-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <!-- Street Address -->
                        <div class="col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Street Address <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="street_address" required
                                class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl"
                                placeholder="123 Main Street">
                            <p id="street_address-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <!-- State -->
                        <div>
                            <label for="state-selector" class="block text-sm font-semibold text-gray-700 mb-2">
                                State <span class="text-red-600">*</span>
                            </label>
                            <select name="state" id="state-selector" required
                                    class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select state...</option>
                                @if(class_exists('\App\Helpers\AustralianSuburbs'))
                                    @foreach(\App\Helpers\AustralianSuburbs::getAllStates() as $code => $name)
                                        <option value="{{ $code }}">{{ $name }}</option>
                                    @endforeach
                                @else
                                    <option value="NSW">New South Wales</option>
                                    <option value="VIC">Victoria</option>
                                    <option value="QLD">Queensland</option>
                                    <option value="SA">South Australia</option>
                                    <option value="WA">Western Australia</option>
                                    <option value="TAS">Tasmania</option>
                                    <option value="NT">Northern Territory</option>
                                    <option value="ACT">Australian Capital Territory</option>
                                @endif
                            </select>
                            <p id="state-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <!-- Suburb Dropdown -->
                        <div>
                            <label for="suburb-selector" class="block text-sm font-semibold text-gray-700 mb-2">
                                Suburb <span class="text-red-600">*</span>
                            </label>
                            <select name="suburb" id="suburb-selector" required disabled
                                    class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed">
                                <option value="">Select state first...</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Can't find your suburb? Type it below.</p>
                            <p id="suburb-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <!-- Postcode -->
                        <div>
                            <label for="postcode-input" class="block text-sm font-semibold text-gray-700 mb-2">
                                Postcode <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="postcode" id="postcode-input" required
                                pattern="[0-9]{4}"
                                maxlength="4"
                                class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl"
                                placeholder="2000">
                            <p class="mt-1 text-xs text-gray-500">4-digit Australian postcode</p>
                            <p id="postcode-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <!-- Manual Suburb Entry -->
                        <div>
                            <label for="suburb-manual" class="block text-sm font-semibold text-gray-700 mb-2">
                                Or Type Suburb Manually
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                                    </svg>
                                </div>
                                <input type="text" id="suburb-manual"
                                    class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-12 pr-4 py-3 shadow-sm border-gray-300 rounded-xl"
                                    placeholder="Type suburb name if not in list above">
                            </div>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label for="start-date-input" class="block text-sm font-semibold text-gray-700 mb-2">
                                Start Date <span class="text-red-600">*</span>
                            </label>
                            <input type="date" name="start_date" id="start-date-input" required
                                class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                            <p id="start_date-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>

                        <!-- End Date -->
                        <div>
                            <label for="end-date-input" class="block text-sm font-semibold text-gray-700 mb-2">
                                End Date <span class="text-gray-500 text-xs">(leave blank if current)</span>
                            </label>
                            <input type="date" name="end_date" id="end-date-input"
                                class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                            <p id="end_date-error" class="mt-2 text-sm text-red-600 hidden"></p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" id="submit-address-button"
                            class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-sm uppercase tracking-wide hover:shadow-xl transition transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        <span id="submit-address-text">Add Address</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $suburbsData = [];

    if (class_exists('\App\Helpers\AustralianSuburbs')) {
        foreach (\App\Helpers\AustralianSuburbs::getAllStates() as $code => $name) {
            $suburbsData[$code] = \App\Helpers\AustralianSuburbs::getSuburbsByState($code);
        }
    } else {
        $suburbsData = [
            'NSW' => ['Sydney', 'Newcastle', 'Wollongong', 'Central Coast'],
            'VIC' => ['Melbourne', 'Geelong', 'Ballarat', 'Bendigo'],
            'QLD' => ['Brisbane', 'Gold Coast', 'Townsville', 'Cairns'],
            'SA'  => ['Adelaide', 'Mount Gambier', 'Whyalla'],
            'WA'  => ['Perth', 'Bunbury', 'Geraldton'],
            'TAS' => ['Hobart', 'Launceston', 'Burnie'],
            'NT'  => ['Darwin', 'Alice Springs', 'Palmerston'],
            'ACT' => ['Canberra', 'Belconnen', 'Tuggeranong']
        ];
    }
@endphp

<script>
Object.assign(window.RESIDENTIAL_CONFIG  , {
    applicationId: @js($application->id),
    allSuburbs: @json($suburbsData),
    deleteRoute: @js(route('applications.residential-addresses.destroy', [$application, ':id']))
});
</script>
