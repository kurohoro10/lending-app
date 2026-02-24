<div class="mb-6 bg-green-50 border-l-4 border-green-400 rounded-lg p-4 shadow-sm animate-pulse-subtle"
        role="alert"
        aria-live="polite">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-sm font-semibold text-green-800">
                {{ $totalAnsweredQuestions }} Client Response{{ $totalAnsweredQuestions > 1 ? 's' : '' }} Available
            </h3>
            <p class="mt-1 text-sm text-green-700">
                Clients have answered questions on their applications. Review responses below to continue assessment.
            </p>
        </div>
        <div class="ml-3 flex-shrink-0">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold bg-green-200 text-green-900">
                {{ $totalAnsweredQuestions }}
            </span>
        </div>
    </div>
</div>
