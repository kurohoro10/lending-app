<!-- Questions Section -->
@if($application->questions->count() > 0)
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Questions from Assessment Team</h3>

        <!-- Toast -->
        <div id="client-qa-toast" class="hidden mb-4 p-4 rounded-lg text-sm font-medium"></div>

        <div id="client-questions-list" class="space-y-4">
            @foreach($application->questions as $question)
            <div id="client-question-{{ $question->id }}"
                 class="border rounded-lg p-4 {{ $question->status === 'pending' ? 'border-yellow-300 bg-yellow-50' : 'border-gray-200' }}">
                <div class="flex justify-between items-start mb-2">
                    <div class="text-sm font-semibold text-gray-900">
                        {{ $question->question }}
                        @if($question->is_mandatory)
                            <span class="ml-1 text-red-500">*</span>
                        @endif
                    </div>
                    <span class="ml-4 flex-shrink-0 px-2 py-1 text-xs rounded-full {{ $question->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                        {{ ucfirst($question->status) }}
                    </span>
                </div>

                @if($question->status === 'pending')
                    <!-- Answer Form -->
                    <div class="mt-3" id="answer-form-{{ $question->id }}">
                        <textarea id="answer-input-{{ $question->id }}" rows="3"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                  placeholder="Type your answer here..."></textarea>
                        <p id="answer-error-{{ $question->id }}" class="hidden mt-1 text-sm text-red-600"></p>
                        <div class="mt-2 flex justify-end">
                            <button onclick="submitAnswer({{ $question->id }})"
                                    id="answer-btn-{{ $question->id }}"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                <span id="answer-btn-text-{{ $question->id }}">Submit Answer</span>
                                <svg id="answer-spinner-{{ $question->id }}" class="hidden ml-2 h-4 w-4 animate-spin" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Answered -->
                    <div id="answer-display-{{ $question->id }}" class="mt-2 p-3 bg-gray-50 rounded">
                        <div class="text-sm text-gray-700">{{ $question->answer }}</div>
                        <div class="mt-1 text-xs text-gray-500">
                            Answered on {{ $question->answered_at->format('d M Y H:i') }}
                        </div>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    const clientCsrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    function showClientToast(message, type = 'success') {
        const toast = document.getElementById('client-qa-toast');
        toast.className = `mb-4 p-4 rounded-lg text-sm font-medium ${
            type === 'success'
                ? 'bg-green-100 text-green-800 border border-green-200'
                : 'bg-red-100 text-red-800 border border-red-200'
        }`;
        toast.textContent = message;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 4000);
    }

    async function submitAnswer(questionId) {
        const answerInput = document.getElementById(`answer-input-${questionId}`);
        const btn = document.getElementById(`answer-btn-${questionId}`);
        const errorEl = document.getElementById(`answer-error-${questionId}`);

        if (!answerInput.value.trim()) {
            errorEl.textContent = 'Please enter an answer.';
            errorEl.classList.remove('hidden');
            answerInput.focus();
            return;
        }

        errorEl.classList.add('hidden');

        // Loading state
        btn.disabled = true;
        document.getElementById(`answer-btn-text-${questionId}`).textContent = 'Submitting...';
        document.getElementById(`answer-spinner-${questionId}`).classList.remove('hidden');

        try {
            const response = await fetch(`/questions/${questionId}/answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': clientCsrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    answer: answerInput.value.trim(),
                }),
            });

            const data = await response.json();

            if (data.success) {
                // Update the question card to show answered state
                const card = document.getElementById(`client-question-${questionId}`);

                // Change card styling
                card.className = 'border rounded-lg p-4 border-gray-200 transition-all';

                // Update status badge
                card.querySelector('.px-2.py-1.text-xs.rounded-full').className =
                    'ml-4 flex-shrink-0 px-2 py-1 text-xs rounded-full bg-green-100 text-green-800';
                card.querySelector('.px-2.py-1.text-xs.rounded-full').textContent = 'Answered';

                // Replace form with answer display
                document.getElementById(`answer-form-${questionId}`).outerHTML = `
                    <div class="mt-2 p-3 bg-gray-50 rounded">
                        <div class="text-sm text-gray-700">${data.answer}</div>
                        <div class="mt-1 text-xs text-gray-500">Answered on ${data.answered_at}</div>
                    </div>`;

                // Update pending questions warning if exists
                updatePendingWarning();
                showClientToast(data.message, 'success');
            } else {
                showClientToast(data.message || 'Failed to submit answer.', 'error');
                btn.disabled = false;
                document.getElementById(`answer-btn-text-${questionId}`).textContent = 'Submit Answer';
                document.getElementById(`answer-spinner-${questionId}`).classList.add('hidden');
            }
        } catch (error) {
            showClientToast('An error occurred. Please try again.', 'error');
            btn.disabled = false;
            document.getElementById(`answer-btn-text-${questionId}`).textContent = 'Submit Answer';
            document.getElementById(`answer-spinner-${questionId}`).classList.add('hidden');
            console.error('Error:', error);
        }
    }

    function updatePendingWarning() {
        // Count remaining pending questions
        const pendingCards = document.querySelectorAll('[id^="client-question-"] .bg-yellow-100');
        const warningBanner = document.getElementById('pending-questions-warning');

        if (warningBanner && pendingCards.length === 0) {
            warningBanner.remove();
        } else if (warningBanner) {
            const countEl = warningBanner.querySelector('h3');
            if (countEl) {
                countEl.textContent = `You have ${pendingCards.length} pending question(s)`;
            }
        }
    }
</script>
@endif
