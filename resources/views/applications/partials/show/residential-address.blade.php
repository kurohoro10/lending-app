{{-- resources/views/applications/partials/show/residential-address.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h3a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h3a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
            </svg>
            Residential History
        </h3>
    </div>

    <div class="p-6">
        <ol class="space-y-4" aria-label="Residential address history">
            @foreach($application->residentialAddresses->sortBy('address_type') as $address)
                <li class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="h-3 w-3 rounded-full bg-indigo-500 mt-1 flex-shrink-0" aria-hidden="true"></div>
                        @if(!$loop->last)
                            <div class="w-px flex-1 bg-indigo-200 mt-1" aria-hidden="true"></div>
                        @endif
                    </div>
                    <div class="pb-4 flex-1">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ ucwords(str_replace('_', ' ', $address->address_type)) }}
                        </p>
                        <p class="mt-0.5 text-sm text-gray-600">{{ $address->full_address }}</p>
                        <p class="mt-0.5 text-xs text-gray-400">
                            {{ $address->start_date->format('M Y') }}
                            –
                            {{ $address->end_date ? $address->end_date->format('M Y') : 'Present' }}
                            <span class="ml-1">({{ $address->months_at_address }} months)</span>
                        </p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</div>
