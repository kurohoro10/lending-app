{{-- resources/views/applications/partials/edit/_company-assets-table.blade.php --}}
<div class="overflow-x-auto rounded-xl border border-gray-200">
    <table class="min-w-full divide-y divide-gray-200 text-sm" aria-label="Company assets">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Asset Name</th>
                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</th>
                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
                <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody id="cal-assets-tbody" class="bg-white divide-y divide-gray-100">
            @foreach($assets as $asset)
                <tr data-asset-id="{{ $asset->id }}">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $asset->asset_name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $asset->notes ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                        ${{ number_format($asset->value, 2) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button type="button"
                                data-asset-id="{{ $asset->id }}"
                                aria-label="Remove asset {{ $asset->asset_name }}"
                                class="cal-delete-asset-btn text-red-500 hover:text-red-700
                                       focus:outline-none focus:ring-2 focus:ring-red-500 rounded transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-50 border-t border-gray-200">
            <tr>
                <td colspan="2" class="px-4 py-3 text-sm font-semibold text-gray-700">Total Assets</td>
                <td id="cal-assets-total" class="px-4 py-3 text-right font-bold text-emerald-700">
                    ${{ number_format($assets->sum('value'), 2) }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
