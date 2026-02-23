<!-- Questions Section - Matching Documents Style Exactly -->
@if($application->questions->count() > 0)
<div id="client-questions-section" class="bg-white overflow-hidden shadow-xl sm:rounded-lg transition-all duration-500">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Questions from Assessment Team</h3>

        <!-- Toast -->
        <div id="client-qa-toast" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

        <!-- Questions List -->
        <div class="space-y-2">
            @foreach($application->questions as $question)
            <div class="question-card flex items-start justify-between p-3 rounded-lg transition-colors {{ $question->status === 'pending' ? 'bg-amber-50' : 'bg-gray-50' }}"
                 data-question-id="{{ $question->id }}"
                 data-status="{{ $question->status }}">
                <div class="flex items-start space-x-3 flex-1">
                    <svg class="h-8 w-8 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $question->question }}
                            @if($question->is_mandatory)
                                <span class="text-red-500">*</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500">
                            @if($question->asked_by_name){{ $question->asked_by_name }} • @endif
                            {{ $question->created_at->format('d M Y') }}
                            @if($question->status === 'answered')
                                • Answered {{ $question->answered_at->format('d M Y') }}
                            @endif
                        </div>

                        @if($question->status === 'pending')
                            <!-- Answer Form -->
                            <div class="answer-form mt-3">
                                <textarea class="answer-input w-full text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                                          rows="3"
                                          placeholder="Type your answer here..."
                                          data-question-id="{{ $question->id }}"></textarea>
                                <p class="answer-error hidden mt-1 text-xs text-red-600"></p>
                                <div class="mt-2 flex justify-end">
                                    <button class="submit-answer-btn inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            data-question-id="{{ $question->id }}">
                                        <span class="btn-text">Submit Answer</span>
                                        <svg class="btn-spinner hidden ml-2 h-3 w-3 animate-spin" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @else
                            <!-- Answered -->
                            <div class="answer-display mt-3 p-3 bg-white rounded-md border border-gray-200">
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $question->answer }}</p>
                            </div>
                        @endif
                    </div>
                </div>
                <span class="question-status flex-shrink-0 ml-3 text-xs {{ $question->status === 'pending' ? 'text-amber-600' : 'text-green-600' }}">
                    {{ $question->status === 'pending' ? 'Pending' : 'Answered' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const questionsSection = document.getElementById('client-questions-section');

    if (!questionsSection) return;

    // Toast notification
    function showToast(message, type = 'success') {
        const toast = document.getElementById('client-qa-toast');
        if (!toast) return;

        const bgColor = type === 'success'
            ? 'bg-green-50 border border-green-200 text-green-800'
            : 'bg-red-50 border border-red-200 text-red-800';

        toast.className = `mb-4 p-3 rounded-lg text-sm ${bgColor}`;
        toast.textContent = message;
        toast.classList.remove('hidden');

        setTimeout(() => toast.classList.add('hidden'), 4000);
    }

    // Update pending counts
    function updatePendingCounts() {
        const pendingQuestions = document.querySelectorAll('.question-card[data-status="pending"]');
        const pendingCount = pendingQuestions.length;

        // Update warning banner
        const warningBanner = document.getElementById('pending-questions-warning');
        if (warningBanner) {
            if (pendingCount === 0) {
                warningBanner.style.transition = 'opacity 0.3s ease';
                warningBanner.style.opacity = '0';
                setTimeout(() => warningBanner.remove(), 300);
            } else {
                const countEl = document.getElementById('pending-count');
                const badgeEl = document.getElementById('pending-badge');
                if (countEl) countEl.textContent = pendingCount;
                if (badgeEl) badgeEl.textContent = pendingCount;
            }
        }
    }

    // Submit answer
    async function submitAnswer(questionId, button) {
        const card = document.querySelector(`.question-card[data-question-id="${questionId}"]`);
        if (!card) return;

        const answerInput = card.querySelector('.answer-input');
        const errorEl = card.querySelector('.answer-error');
        const btnText = button.querySelector('.btn-text');
        const btnSpinner = button.querySelector('.btn-spinner');

        // Validate
        if (!answerInput.value.trim()) {
            errorEl.textContent = 'Please enter an answer.';
            errorEl.classList.remove('hidden');
            answerInput.focus();
            return;
        }

        errorEl.classList.add('hidden');

        // Loading state
        button.disabled = true;
        btnText.textContent = 'Submitting...';
        btnSpinner.classList.remove('hidden');

        try {
            const response = await fetch(`/questions/${questionId}/answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    answer: answerInput.value.trim(),
                }),
            });

            const data = await response.json();

            if (data.success) {
                // Update card
                card.className = 'question-card flex items-start justify-between p-3 rounded-lg transition-colors bg-gray-50';
                card.setAttribute('data-status', 'answered');

                // Update status
                const statusBadge = card.querySelector('.question-status');
                if (statusBadge) {
                    statusBadge.className = 'question-status flex-shrink-0 ml-3 text-xs text-green-600';
                    statusBadge.textContent = 'Answered';
                }

                // Update date info
                const dateInfo = card.querySelector('.text-xs.text-gray-500');
                if (dateInfo) {
                    const originalText = dateInfo.textContent.split('•')[0];
                    dateInfo.textContent = `${originalText}• Answered ${data.answered_at}`;
                }

                // Replace form with answer
                const formContainer = card.querySelector('.answer-form');
                if (formContainer) {
                    formContainer.outerHTML = `
                        <div class="answer-display mt-3 p-3 bg-white rounded-md border border-gray-200">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap">${data.answer}</p>
                        </div>
                    `;
                }

                updatePendingCounts();
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Failed to submit answer.', 'error');
                button.disabled = false;
                btnText.textContent = 'Submit Answer';
                btnSpinner.classList.add('hidden');
            }
        } catch (error) {
            showToast('An error occurred. Please try again.', 'error');
            button.disabled = false;
            btnText.textContent = 'Submit Answer';
            btnSpinner.classList.add('hidden');
            console.error('Error:', error);
        }
    }

    // Event delegation for submit buttons
    questionsSection.addEventListener('click', function(e) {
        const submitBtn = e.target.closest('.submit-answer-btn');
        if (submitBtn) {
            const questionId = submitBtn.dataset.questionId;
            if (questionId) {
                submitAnswer(questionId, submitBtn);
            }
        }
    });

    // Keyboard shortcut: Ctrl+Enter or Cmd+Enter to submit
    questionsSection.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const textarea = e.target.closest('.answer-input');
            if (textarea) {
                const questionId = textarea.dataset.questionId;
                const submitBtn = document.querySelector(`.submit-answer-btn[data-question-id="${questionId}"]`);
                if (submitBtn && !submitBtn.disabled) {
                    submitAnswer(questionId, submitBtn);
                }
            }
        }
    });
});

// Scroll to questions (called from warning banner)
function scrollToQuestions() {
    const questionsSection = document.getElementById('client-questions-section');
    if (questionsSection) {
        questionsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

        // Focus first textarea
        setTimeout(() => {
            const firstInput = questionsSection.querySelector('.answer-input');
            if (firstInput) {
                firstInput.focus();
            }
        }, 500);
    }
}
</script>
@endif
