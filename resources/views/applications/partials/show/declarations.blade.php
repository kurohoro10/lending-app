<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Declarations</h3>
        <div class="space-y-3">
            @foreach($application->declarations as $declaration)
            <div class="flex items-start space-x-3">
                <svg class="h-5 w-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <div class="text-sm font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $declaration->declaration_type)) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Agreed on {{ $declaration->agreed_at->format('d M Y H:i') }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
