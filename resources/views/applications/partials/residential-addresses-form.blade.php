<!-- Residential Addresses Section - Enhanced -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
            </svg>
            Residential Address History
        </h3>
        <p class="text-indigo-100 text-sm mt-1">Provide your address history for the past 3 years</p>
    </div>
    <div class="p-6">
        @if($application->residentialAddresses->count() > 0)
        <div class="mb-6 space-y-3">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Your Address History</h4>
            @foreach($application->residentialAddresses->sortBy('address_type') as $address)
            <div class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200 hover:shadow-md transition">
                <div class="flex justify-between items-start">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold">
                                    {{ ucwords(str_replace('_', ' ', $address->address_type)) }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $address->start_date->format('M Y') }} - {{ $address->end_date ? $address->end_date->format('M Y') : 'Present' }}
                                </span>
                            </div>
                            <div class="text-sm font-medium text-gray-900 mt-1">{{ $address->full_address }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('applications.residential-addresses.destroy', [$application, $address]) }}" onsubmit="return confirm('Are you sure you want to delete this address?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-50 text-red-700 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <form id="residential-address" method="POST" action="{{ route('applications.residential-addresses.store', $application) }}" class="mt-6">
            @csrf

            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border border-indigo-100 mb-6">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Add New Address</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Address Type *</label>
                        <select name="address_type" required class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select address type...</option>
                            <option value="current">Current Address</option>
                            <option value="previous_1">Previous Address (Year 1)</option>
                            <option value="previous_2">Previous Address (Year 2)</option>
                            <option value="previous_3">Previous Address (Year 3)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Residential Status</label>
                        <select name="residential_status" class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="own">Own</option>
                            <option value="rent">Rent</option>
                            <option value="boarding">Boarding</option>
                            <option value="living_with_parents">Living with Parents</option>
                        </select>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Street Address *</label>
                        <input type="text" name="street_address" required class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                    </div>

                    <div>
                        <label for="country" class="block text-sm font-semibold text-gray-700 mb-2">Country *</label>
                        <select name="country" id="country" required
                                class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-white rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="AU" selected>Australia</option>
                            <option value="NZ">New Zealand</option>
                            <option value="US">United States</option>
                            <option value="GB">United Kingdom</option>
                            <option value="CA">Canada</option>
                        </select>
                    </div>

                    <div>
                        <label for="postcode" class="block text-sm font-semibold text-gray-700 mb-2">Postcode *</label>
                        <input type="text" name="postcode" id="postcode" required
                            class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                        <span id="postcode-status" class="sr-only" role="status"></span>
                    </div>

                    <div>
                        <label for="suburb" class="block text-sm font-semibold text-gray-700 mb-2">Suburb *</label>
                        <input type="text" name="suburb" id="suburb" required
                            class="mt-1 bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                    </div>

                    <div>
                        <label for="state" class="block text-sm font-semibold text-gray-700 mb-2">State *</label>
                        <input type="text" name="state" id="state" required
                            class="mt-1 bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Start Date *</label>
                        <input type="date" name="start_date" required class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">End Date (leave blank if current)</label>
                        <input type="date" name="end_date" class="mt-1 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full py-3 px-4 shadow-sm border-gray-300 rounded-xl">
                    </div>
                </div>
            </div>

            <div id="address-error-container" class="mb-6 hidden" role="alert">
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p id="address-error-text" class="text-sm text-red-700 font-medium"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold text-sm uppercase tracking-wide hover:shadow-lg transition transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Add Address
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('residential-address');
    const typeSelect = document.querySelector('select[name="address_type"]');
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    const errorContainer = document.getElementById('address-error-container');
    const errorText = document.getElementById('address-error-text');

    form.addEventListener('submit', function (event) {
        const type = typeSelect.value;
        const start = new Date(startDateInput.value);
        // Use end_date if provided, otherwise use today's date
        const end = endDateInput.value ? new Date(endDateInput.value) : new Date();

        // Define requirements
        const requirements = {
            'previous_1': 1,
            'previous_2': 2,
            'previous_3': 3,
            'current': 0 // Usually no minimum for current, adjust if needed
        };

        const requiredYears = requirements[type] || 0;

        if (requiredYears > 0) {
            // Calculate difference in years
            let diffInMs = end - start;
            let diffInYears = diffInMs / (1000 * 60 * 60 * 24 * 365.25);

            if (diffInYears < requiredYears) {
                event.preventDefault(); // Stop submission

                // Show Error
                const message = `For ${type.replace('_', ' ')}, the duration must be at least ${requiredYears} year(s). Current: ${diffInYears.toFixed(1)} years.`;
                errorText.textContent = message;
                errorContainer.classList.remove('hidden');

                // Accessibility: Scroll to error and focus for screen readers
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                errorContainer.classList.add('hidden');
            }
        }
    });

    // Reset error message when user changes inputs
    [typeSelect, startDateInput, endDateInput].forEach(el => {
        el.addEventListener('change', () => errorContainer.classList.add('hidden'));
    });

    const countrySelect = document.getElementById('country');
    const postcodeInp = document.getElementById('postcode');
    const suburbInp = document.getElementById('suburb');
    const stateInp = document.getElementById('state');
    const statusRegion = document.getElementById('postcode-status');

    // Function to fetch address data
    const fetchAddress = () => {
        const country = countrySelect.value;
        const postcode = postcodeInp.value.trim();

        // Validation: Australia (4 digits), US (5 digits), etc.
        if ((country === 'AU' && postcode.length === 4) || (country === 'US' && postcode.length === 5)) {
            statusRegion.textContent = "Fetching address details...";

            fetch(`https://api.zippopotam.is/${country.toLowerCase()}/${postcode}`)
                .then(response => {
                    if (!response.ok) throw new Error('Location not found');
                    return response.json();
                })
                .then(data => {
                    const place = data.places[0];
                    suburbInp.value = place['place name'];
                    stateInp.value = place['state abbreviation'];

                    statusRegion.textContent = `Success. Found ${place['place name']}, ${place['state abbreviation']}.`;
                    postcodeInp.classList.remove('border-red-500');
                })
                .catch(err => {
                    statusRegion.textContent = "Postcode not found for this country.";
                    postcodeInp.classList.add('border-red-500');
                });
        }
    };

    // Listen for changes
    postcodeInp.addEventListener('input', fetchAddress);
    countrySelect.addEventListener('change', () => {
        // Clear fields when country changes to avoid mismatched data
        postcodeInp.value = '';
        suburbInp.value = '';
        stateInp.value = '';
        statusRegion.textContent = "Country changed. Please enter the new postcode.";
    });
});
</script>
