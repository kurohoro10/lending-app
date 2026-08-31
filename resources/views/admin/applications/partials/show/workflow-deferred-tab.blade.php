{{-- Deferred Tab Content --}}

<div class="text-center py-8">
    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <h3 class="mt-2 text-lg font-medium text-gray-900">Application Deferred</h3>
    <p class="mt-2 text-sm text-gray-500">
        This application is awaiting further decision. Additional information or time may be required before proceeding.
    </p>
    
    @if($application->return_reason)
        <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-sm font-medium text-blue-900">Deferral Reason</p>
            <p class="text-sm text-blue-800 mt-1">{{ $application->return_reason }}</p>
        </div>
    @endif
</div>