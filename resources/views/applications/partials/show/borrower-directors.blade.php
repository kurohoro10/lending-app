{{-- resources/views/applications/partials/show/borrower-directors.blade.php --}}
@php
    $directors   = $application->borrowerDirectors;
    $borrowerType = $application->borrowerInformation->borrower_type;
    $label        = $borrowerType === 'trust' ? 'Trustees' : 'Directors';
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-1a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v1h-3zM4.75 14.094A5.973 5.973 0 004 17v1H1v-1a3 3 0 013.75-2.906z"/>
            </svg>
            {{ $label }}
        </h3>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto rounded-xl border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="{{ $label }}">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Ownership</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Guarantor</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($directors as $director)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $director->full_name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $director->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $director->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">
                                {{ $director->ownership_percentage !== null ? $director->ownership_percentage . '%' : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($director->is_guarantor)
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                                        Guarantor
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
