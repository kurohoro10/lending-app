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

        <form method="POST" action="{{ route('applications.residential-addresses.store', $application) }}" class="mt-4">
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

                <div>
                    <label class="block text-sm font-medium text-gray-700">Suburb *</label>
                    <input type="text" name="suburb" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">State *</label>
                    <input type="text" name="state" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Postcode *</label>
                    <input type="text" name="postcode" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Country *</label>
                    <input type="text" name="country" value="Australia" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date *</label>
                    <input type="date" name="start_date" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date (leave blank if current)</label>
                    <input type="date" name="end_date" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
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
