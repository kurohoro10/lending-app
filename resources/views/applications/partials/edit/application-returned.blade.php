@if($application->isReturned())
<div class="mb-6 bg-orange-50 border-l-4 border-orange-400 rounded-lg p-5"
     role="alert"
     aria-live="polite">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-orange-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-sm font-semibold text-orange-800">
                Your Application Requires Amendments
            </h3>
            <div class="mt-2 text-sm text-orange-700">
                <p class="font-medium">Reason from assessor:</p>
                <p class="mt-1 p-2 bg-orange-100 rounded">{{ $application->return_reason }}</p>
            </div>
            <div class="mt-2 text-xs text-orange-600">
                Returned on {{ $application->returned_at->format('d M Y \a\t H:i') }}
                @if($application->returnedBy)
                    by {{ $application->returnedBy->name }}
                @endif
            </div>
            <p class="mt-2 text-sm text-orange-700">
                Please make the necessary changes below and resubmit your application.
            </p>
        </div>
    </div>
</div>
@endif
