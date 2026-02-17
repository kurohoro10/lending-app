<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Residential History</h3>
        <div class="space-y-4">
            @foreach($application->residentialAddresses->sortBy('address_type') as $address)
            <div class="border-l-4 border-indigo-400 pl-4">
                <div class="text-sm font-medium text-gray-900">
                    {{ ucwords(str_replace('_', ' ', $address->address_type)) }}
                </div>
                <div class="mt-1 text-sm text-gray-600">
                    {{ $address->full_address }}
                </div>
                <div class="mt-1 text-xs text-gray-500">
                    {{ $address->start_date->format('M Y') }} - {{ $address->end_date ? $address->end_date->format('M Y') : 'Present' }}
                    ({{ $address->months_at_address }} months)
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
