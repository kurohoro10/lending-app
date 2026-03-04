{{-- resources/views/applications/partials/show/director-assets-liabilities.blade.php --}}
@php
    $assets      = $application->directorAssets;
    $liabilities = $application->directorLiabilities;
    $net         = $assets->sum('estimated_value') - $liabilities->sum('outstanding_balance');
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM2 9v7a2 2 0 002 2h12a2 2 0 002-2V9H2zm4 2h8a1 1 0 010 2H6a1 1 0 010-2z"/>
            </svg>
            Director Assets &amp; Liabilities
        </h3>
    </div>

    <div class="p-6 space-y-8">

        {{-- Assets --}}
        @if($assets->count() > 0)
            <section aria-labelledby="show-dal-assets-heading">
                <h4 id="show-dal-assets-heading"
                    class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Assets</h4>
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Director assets">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Property Use</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($assets as $asset)
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-medium">
                                            {{ $asset->asset_type_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $asset->description ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        @if($asset->asset_type === 'house')
                                            {{ $asset->property_use === 'main_residence' ? 'Main Residence' : 'Rental' }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                        ${{ number_format($asset->estimated_value, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-700">Total</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                    ${{ number_format($assets->sum('estimated_value'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        @endif

        {{-- Liabilities --}}
        @if($liabilities->count() > 0)
            <section aria-labelledby="show-dal-liabilities-heading">
                <h4 id="show-dal-liabilities-heading"
                    class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Liabilities</h4>
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Director liabilities">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lender</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Limit</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($liabilities as $liability)
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                            {{ $liability->liability_type_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $liability->lender_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600">
                                        {{ $liability->credit_limit !== null ? '$' . number_format($liability->credit_limit, 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                        ${{ number_format($liability->outstanding_balance, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="3" class="px-4 py-3 text-sm font-semibold text-gray-700">Total</td>
                                <td class="px-4 py-3 text-right font-bold text-red-600">
                                    ${{ number_format($liabilities->sum('outstanding_balance'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        @endif

        {{-- Net position --}}
        <div class="rounded-xl bg-gray-50 border border-gray-200 p-4 flex flex-wrap gap-4 justify-between items-center"
             aria-label="Net position">
            <span class="text-sm text-gray-500 font-medium">Net Position (Assets − Liabilities)</span>
            <div class="flex flex-wrap gap-6 text-sm font-semibold">
                <span>Assets: <span class="text-emerald-700">${{ number_format($assets->sum('estimated_value'), 2) }}</span></span>
                <span>Liabilities: <span class="text-red-600">${{ number_format($liabilities->sum('outstanding_balance'), 2) }}</span></span>
                <span>Net: <span class="font-bold {{ $net >= 0 ? 'text-emerald-700' : 'text-red-600' }}">${{ number_format($net, 2) }}</span></span>
            </div>
        </div>

    </div>
</div>
