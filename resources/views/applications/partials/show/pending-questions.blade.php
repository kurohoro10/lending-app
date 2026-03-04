{{-- resources/views/applications/partials/show/pending-questions.blade.php --}}
@php $pendingCount = $application->questions->where('status', 'pending')->count(); @endphp

@if($pendingCount > 0)
    <div id="pending-questions-warning"
         class="bg-amber-50 border border-amber-200 rounded-2xl p-4 transition-all duration-300"
         role="alert"
         aria-live="polite">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-900">
                    <span id="pending-count">{{ $pendingCount }}</span>
                    {{ $pendingCount === 1 ? 'question needs' : 'questions need' }} your answer
                </p>
                <button type="button"
                        onclick="scrollToQuestions()"
                        class="mt-1 text-sm text-gray-600 hover:text-gray-900 underline decoration-1
                               underline-offset-2 hover:decoration-2 transition-all
                               focus:outline-none focus:ring-2 focus:ring-amber-400 rounded">
                    View questions
                </button>
            </div>
            <span id="pending-badge"
                  class="flex-shrink-0 flex items-center justify-center h-6 w-6 rounded-full
                         bg-amber-100 text-amber-700 text-xs font-semibold"
                  aria-label="{{ $pendingCount }} pending">
                {{ $pendingCount }}
            </span>
        </div>
    </div>
@endif

<script>
function scrollToQuestions() {
    const questionsSection = document.getElementById('client-questions-section');

    if (questionsSection) {
        // Smooth scroll
        questionsSection.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        // Subtle highlight
        questionsSection.style.boxShadow = '0 0 0 2px rgba(251, 191, 36, 0.3)';
        setTimeout(() => {
            questionsSection.style.boxShadow = '';
        }, 1500);

        // Focus first pending textarea
        setTimeout(() => {
            const firstInput = questionsSection.querySelector('textarea[id^="answer-input-"]');
            if (firstInput) {
                firstInput.focus();
                firstInput.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }, 500);
    }
}
</script>
