<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Residential Address History (3 Years)</h3>

        @if($application->residentialAddresses->count() > 0)
        <div class="mb-4 space-y-3">
            @foreach($application->residentialAddresses->sortBy('address_type') as $address)
            <div class="border rounded-lg p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $address->address_type)) }}</div>
                        <div class="text-sm text-gray-600 mt-1">{{ $address->full_address }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $address->start_date->format('M Y') }} - {{ $address->end_date ? $address->end_date->format('M Y') : 'Present' }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('applications.residential-addresses.destroy', [$application, $address]) }}" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <form id="residential-address" method="POST" action="{{ route('applications.residential-addresses.store', $application) }}" class="mt-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Address Type *</label>
                    <select name="address_type" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="current">Current Address</option>
                        <option value="previous_1">Previous Address (Year 1)</option>
                        <option value="previous_2">Previous Address (Year 2)</option>
                        <option value="previous_3">Previous Address (Year 3)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Residential Status</label>
                    <select name="residential_status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="own">Own</option>
                        <option value="rent">Rent</option>
                        <option value="boarding">Boarding</option>
                        <option value="living_with_parents">Living with Parents</option>
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Street Address *</label>
                    <input type="text" name="street_address" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700">Country *</label>
                        <select name="country" id="country" required
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="AU" selected>Australia</option>
                            <option value="NZ">New Zealand</option>
                            <option value="US">United States</option>
                            <option value="GB">United Kingdom</option>
                            <option value="CA">Canada</option>
                            </select>
                    </div>

                    <div>
                        <label for="postcode" class="block text-sm font-medium text-gray-700">Postcode *</label>
                        <input type="text" name="postcode" id="postcode" required
                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <span id="postcode-status" class="sr-only" role="status"></span>
                    </div>

                    <div>
                        <label for="suburb" class="block text-sm font-medium text-gray-700">Suburb *</label>
                        <input type="text" name="suburb" id="suburb" required
                            class="mt-1 bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700">State *</label>
                        <input type="text" name="state" id="state" required
                            class="mt-1 bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date *</label>
                        <input type="date" name="start_date" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Date (leave blank if current)</label>
                        <input type="date" name="end_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>
                </div>

            </div>

            <div id="address-error-container" class="mt-4 hidden" role="alert">
                <div class="bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p id="address-error-text" class="text-sm text-red-700"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
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
