{{-- Declined Tab Content --}}

<div class="text-center py-8">
    <svg class="mx-auto h-12 w-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <h3 class="mt-2 text-lg font-medium text-gray-900">Application Declined</h3>
    <p class="mt-2 text-sm text-gray-500">
        This application has been declined and is not eligible for approval.
    </p>
    
    @if($application->return_reason)
        <div class="mt-4 p-4 bg-red-50 rounded-lg border border-red-200">
            <p class="text-sm font-medium text-red-900">Decline Reason</p>
            <p class="text-sm text-red-800 mt-1">{{ $application->return_reason }}</p>
        </div>
    @endif
</div>