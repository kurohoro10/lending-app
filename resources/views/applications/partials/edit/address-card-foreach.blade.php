{{--
    Replace the existing address card @foreach in residential-addresses.blade.php.
    The data-start-date / data-end-date attributes are read by the JS coverage calculator on page load.
--}}
@foreach($application->residentialAddresses->sortBy('address_type') as $address)
<div data-address-card
     class="address-item p-5 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border-2 border-gray-200 hover:shadow-lg hover:border-indigo-200 transition-all"
     data-address-id="{{ $address->id }}"
     data-start-date="{{ $address->start_date?->toDateString() }}"
     data-end-date="{{ $address->end_date?->toDateString() }}">
    <div class="flex justify-between items-start">
        <div class="flex items-start space-x-4 flex-1">
            <div class="flex-shrink-0">
                <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
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
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <time datetime="{{ $address->start_date?->toDateString() }}">
                            {{ $address->start_date?->format('M Y') ?? 'N/A' }}
                        </time>
                        –
                        <time datetime="{{ $address->end_date?->toDateString() ?? 'present' }}">
                            {{ $address->end_date?->format('M Y') ?? 'Present' }}
                        </time>
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
                aria-label="Delete {{ ucwords(str_replace('_', ' ', $address->address_type)) }} address"
                class="ml-4 inline-flex items-center px-4 py-2 bg-red-50 text-red-700 rounded-xl text-sm font-semibold hover:bg-red-100 transition-all hover:shadow-md delete-address-btn">
            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            Delete
        </button>
    </div>
</div>
@endforeach
