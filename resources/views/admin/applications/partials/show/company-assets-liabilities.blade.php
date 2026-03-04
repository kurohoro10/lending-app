{{-- resources/views/admin/applications/partials/show/company-assets-liabilities.blade.php --}}
@php
    $assets      = $application->companyAssets;
    $liabilities = $application->companyLiabilities;
    $net         = $assets->sum('value') - $liabilities->sum('value');
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm3 5a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"/>
            </svg>
            Company Assets &amp; Liabilities
        </h3>

        {{-- Assets --}}
        @if($assets->count() > 0)
            <h4 class="text-sm font-semibold text-gray-700 mb-2 mt-4">Assets</h4>
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Company assets">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asset Name</th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($assets as $asset)
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">{{ $asset->asset_name }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $asset->notes ?? '—' }}</td>
                                <td class="px-4 py-2 text-right font-medium text-gray-900">
                                    ${{ number_format($asset->value, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="2" class="px-4 py-2 text-sm font-semibold text-gray-700 text-right">Total</td>
                            <td class="px-4 py-2 text-right font-bold text-green-700">
                                ${{ number_format($assets->sum('value'), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        {{-- Liabilities --}}
        @if($liabilities->count() > 0)
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Liabilities</h4>
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Company liabilities">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Liability Name</th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($liabilities as $liability)
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">{{ $liability->liability_name }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $liability->notes ?? '—' }}</td>
                                <td class="px-4 py-2 text-right font-medium text-gray-900">
                                    ${{ number_format($liability->value, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="2" class="px-4 py-2 text-sm font-semibold text-gray-700 text-right">Total</td>
                            <td class="px-4 py-2 text-right font-bold text-red-600">
                                ${{ number_format($liabilities->sum('value'), 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        {{-- Net position --}}
        <div class="flex flex-wrap gap-6 justify-end text-sm font-semibold pt-2 border-t border-gray-100">
            <span>Assets: <span class="text-green-700">${{ number_format($assets->sum('value'), 2) }}</span></span>
            <span>Liabilities: <span class="text-red-600">${{ number_format($liabilities->sum('value'), 2) }}</span></span>
            <span>Net: <span class="{{ $net >= 0 ? 'text-green-700' : 'text-red-600' }}">${{ number_format($net, 2) }}</span></span>
        </div>

    </div>
</div>
