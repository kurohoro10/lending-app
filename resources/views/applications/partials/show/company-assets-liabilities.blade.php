{{-- resources/views/applications/partials/show/company-assets-liabilities.blade.php --}}
@php
    $assets      = $application->companyAssets;
    $liabilities = $application->companyLiabilities;
    $net         = $assets->sum('value') - $liabilities->sum('value');
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm3 5a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"/>
            </svg>
            Company Assets &amp; Liabilities
        </h3>
    </div>

    <div class="p-6 space-y-8">

        {{-- Assets --}}
        @if($assets->count() > 0)
            <section aria-labelledby="show-cal-assets-heading">
                <h4 id="show-cal-assets-heading"
                    class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Assets</h4>
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Company assets">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Asset Name</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($assets as $asset)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $asset->asset_name }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $asset->notes ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                        ${{ number_format($asset->value, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-sm font-semibold text-gray-700">Total</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-700">
                                    ${{ number_format($assets->sum('value'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        @endif

        {{-- Liabilities --}}
        @if($liabilities->count() > 0)
            <section aria-labelledby="show-cal-liabilities-heading">
                <h4 id="show-cal-liabilities-heading"
                    class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Liabilities</h4>
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Company liabilities">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Liability Name</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($liabilities as $liability)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $liability->liability_name }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $liability->notes ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                        ${{ number_format($liability->value, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-sm font-semibold text-gray-700">Total</td>
                                <td class="px-4 py-3 text-right font-bold text-red-600">
                                    ${{ number_format($liabilities->sum('value'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        @endif

        {{-- Net position --}}
        <div class="rounded-xl bg-gray-50 border border-gray-200 p-4 flex flex-wrap gap-4 justify-between items-center"
             aria-label="Company net position">
            <span class="text-sm text-gray-500 font-medium">Net Position (Assets − Liabilities)</span>
            <div class="flex flex-wrap gap-6 text-sm font-semibold">
                <span>Assets: <span class="text-emerald-700">${{ number_format($assets->sum('value'), 2) }}</span></span>
                <span>Liabilities: <span class="text-red-600">${{ number_format($liabilities->sum('value'), 2) }}</span></span>
                <span>Net: <span class="font-bold {{ $net >= 0 ? 'text-emerald-700' : 'text-red-600' }}">${{ number_format($net, 2) }}</span></span>
            </div>
        </div>

    </div>
</div>
