<!-- Questions & Answers Section -->
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg" id="qa-section">
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">
                Questions & Answers
                <span id="qa-count" class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700">
                    {{ $application->questions->count() }}
                </span>
            </h3>
            <button onclick="toggleAskForm()"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Ask Question
            </button>
        </div>

        <!-- Toast Notification -->
        <div id="qa-toast" class="hidden mb-4 p-4 rounded-lg text-sm font-medium"></div>

        <!-- Ask Question Form -->
        <div id="ask-question-form" class="hidden mb-6 p-4 bg-indigo-50 rounded-lg border-2 border-indigo-200">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Your Question</label>
                <textarea id="question-input" rows="3"
                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                          placeholder="Type your question to the client..."></textarea>
                <p id="question-error" class="hidden mt-1 text-sm text-red-600"></p>
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="is-mandatory"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Mark as mandatory</span>
                </label>
                <div class="flex space-x-2">
                    <button type="button" onclick="toggleAskForm()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="button" onclick="submitQuestion()" id="submit-question-btn"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 flex items-center">
                        <span id="submit-question-text">Send Question</span>
                        <svg id="submit-question-spinner" class="hidden ml-2 h-4 w-4 animate-spin" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Questions List -->
        <div id="questions-list" class="space-y-4">
            @forelse($application->questions->sortByDesc('created_at') as $question)
                @include('admin.applications.partials.question.question-item', ['question' => $question])
            @empty
                <div id="no-questions-message" class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">No questions yet. Click "Ask Question" to start.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    const applicationId = {{ $application->id }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    function toggleAskForm() {
        const form = document.getElementById('ask-question-form');
        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            document.getElementById('question-input').focus();
        }
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('qa-toast');
        toast.className = `mb-4 p-4 rounded-lg text-sm font-medium ${
            type === 'success'
                ? 'bg-green-100 text-green-800 border border-green-200'
                : 'bg-red-100 text-red-800 border border-red-200'
        }`;
        toast.textContent = message;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 4000);
    }

    function updateCount(delta) {
        const countEl = document.getElementById('qa-count');
        countEl.textContent = parseInt(countEl.textContent) + delta;
    }

    async function submitQuestion() {
        const questionInput = document.getElementById('question-input');
        const isMandatory = document.getElementById('is-mandatory').checked;
        const btn = document.getElementById('submit-question-btn');
        const errorEl = document.getElementById('question-error');

        if (!questionInput.value.trim()) {
            errorEl.textContent = 'Please enter a question.';
            errorEl.classList.remove('hidden');
            questionInput.focus();
            return;
        }

        errorEl.classList.add('hidden');

        // Loading state
        btn.disabled = true;
        document.getElementById('submit-question-text').textContent = 'Sending...';
        document.getElementById('submit-question-spinner').classList.remove('hidden');

        try {
            const response = await fetch(`/admin/applications/${applicationId}/questions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    question: questionInput.value.trim(),
                    is_mandatory: isMandatory,
                }),
            });

            const data = await response.json();

            if (data.success) {
                // Remove "no questions" message if exists
                document.getElementById('no-questions-message')?.remove();

                // Prepend new question card to list
                const list = document.getElementById('questions-list');
                list.insertAdjacentHTML('afterbegin', buildQuestionCard(data.question));

                // Reset form
                questionInput.value = '';
                document.getElementById('is-mandatory').checked = false;
                document.getElementById('ask-question-form').classList.add('hidden');

                updateCount(1);
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Failed to send question.', 'error');
            }
        } catch (error) {
            showToast('An error occurred. Please try again.', 'error');
            console.error('Error:', error);
        } finally {
            btn.disabled = false;
            document.getElementById('submit-question-text').textContent = 'Send Question';
            document.getElementById('submit-question-spinner').classList.add('hidden');
        }
    }

    async function deleteQuestion(questionId) {
        if (!confirm('Are you sure you want to delete this question?')) return;

        const card = document.getElementById(`question-card-${questionId}`);
        card.style.opacity = '0.5';

        try {
            const response = await fetch(`/admin/questions/${questionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                card.remove();
                updateCount(-1);
                showToast(data.message, 'success');

                // Show "no questions" if list is empty
                if (document.getElementById('questions-list').children.length === 0) {
                    document.getElementById('questions-list').innerHTML = `
                        <div id="no-questions-message" class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No questions yet. Click "Ask Question" to start.</p>
                        </div>`;
                }
            } else {
                card.style.opacity = '1';
                showToast(data.message || 'Failed to delete question.', 'error');
            }
        } catch (error) {
            card.style.opacity = '1';
            showToast('An error occurred. Please try again.', 'error');
        }
    }

    function buildQuestionCard(q) {
        return `
        <div id="question-card-${q.id}" class="border rounded-lg p-4 border-yellow-300 bg-yellow-50 transition-all">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        ${q.is_mandatory ? '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Mandatory</span>' : ''}
                        <span class="text-xs text-gray-500">Asked by ${q.asked_by}</span>
                    </div>
                    <div class="text-sm font-semibold text-gray-900 mb-1">Q: ${q.question}</div>
                    <div class="text-xs text-gray-500">${q.asked_at}</div>
                </div>
                <button onclick="deleteQuestion(${q.id})" class="text-red-600 hover:text-red-800 ml-4">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <div class="mt-3 p-3 bg-white rounded border border-yellow-200">
                <p class="text-sm text-gray-600 italic">Waiting for client's response...</p>
            </div>
        </div>`;
    }
</script>
