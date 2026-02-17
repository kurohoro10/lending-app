<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Electronic Signature</h3>

    <div class="border-2 border-gray-300 rounded-lg p-6 bg-gray-50">
        @if($finalSignature->signature_type === 'typed')
            <p class="text-3xl mb-4" style="font-family: 'Brush Script MT', cursive; color: #1F2937;">
                {{ $finalSignature->signature_data }}
            </p>
        @else
            <img src="{{ $finalSignature->signature_data }}" alt="Signature" class="max-w-md">
        @endif

        <div class="mt-4 pt-4 border-t border-gray-300 grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-600">Signed by:</p>
                <p class="font-semibold">{{ $finalSignature->signatory_name }}</p>
            </div>
            @if($finalSignature->signatory_position)
            <div>
                <p class="text-gray-600">Position:</p>
                <p class="font-semibold">{{ $finalSignature->signatory_position }}</p>
            </div>
            @endif
            <div>
                <p class="text-gray-600">Date & Time:</p>
                <p class="font-semibold">{{ $finalSignature->signature_timestamp->format('d/m/Y h:i A') }}</p>
            </div>
            <div>
                <p class="text-gray-600">IP Address:</p>
                <p class="font-semibold">{{ $finalSignature->agreement_ip }}</p>
            </div>
        </div>
    </div>
</div>
