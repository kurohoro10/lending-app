// resources/js/admin/questions.js

(() => {
    const applicationId = window.APP_QUESTION?.applicationId;
    const csrfToken     = document.querySelector('meta[name="csrf-token"]')?.content;
    const list          = document.getElementById('questions-list');

    if (!list || !applicationId) return;

    // ── Toggle ask form ───────────────────────────────────────────────────────

    function toggleAskForm() {
        const form = document.getElementById('ask-question-form');
        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            document.getElementById('question-input')?.focus();
        }
    }

    document.getElementById('toggle-ask-form-btn')?.addEventListener('click', toggleAskForm);
    document.getElementById('cancel-btn')?.addEventListener('click', toggleAskForm);

    // ── Submit question ───────────────────────────────────────────────────────

    document.getElementById('submit-question-btn')?.addEventListener('click', submitQuestion);

    async function submitQuestion() {
        const questionInput = document.getElementById('question-input');
        const isMandatory   = document.getElementById('is-mandatory').checked;
        const btn           = document.getElementById('submit-question-btn');
        const errorEl       = document.getElementById('question-error');

        if (!questionInput.value.trim()) {
            errorEl.textContent = 'Please enter a question.';
            errorEl.classList.remove('hidden');
            questionInput.focus();
            return;
        }

        errorEl.classList.add('hidden');
        setSubmitLoading(true);

        try {
            const res  = await fetch(`/admin/applications/${applicationId}/questions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({
                    question:     questionInput.value.trim(),
                    is_mandatory: isMandatory,
                }),
            });

            const data = await res.json();

            if (data.success) {
                document.getElementById('no-questions-message')?.remove();
                list.insertAdjacentHTML('afterbegin', buildQuestionCard(data.question));
                questionInput.value = '';
                document.getElementById('is-mandatory').checked = false;
                document.getElementById('ask-question-form').classList.add('hidden');
                updateCount(1);
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Failed to send question.', 'error');
            }
        } catch {
            showToast('An error occurred. Please try again.', 'error');
        } finally {
            setSubmitLoading(false);
        }
    }

    function setSubmitLoading(loading) {
        const btn = document.getElementById('submit-question-btn');
        if (btn) btn.disabled = loading;
        const text = document.getElementById('submit-question-text');
        if (text) text.textContent = loading ? 'Sending...' : 'Send Question';
        document.getElementById('submit-question-spinner')?.classList.toggle('hidden', !loading);
    }

    // ── Delegated listener — delete + mark as read ────────────────────────────
    // Covers both server-rendered (blade) and dynamically injected cards.

    list.addEventListener('click', (e) => {
        const deleteBtn     = e.target.closest('[data-action="delete-question"]');
        const markAsReadBtn = e.target.closest('[data-action="mark-as-read"]');

        if (deleteBtn)     deleteQuestion(deleteBtn.dataset.questionId);
        if (markAsReadBtn) markQuestionAsRead(markAsReadBtn.dataset.questionId);
    });

    // ── Delete question ───────────────────────────────────────────────────────

    async function deleteQuestion(questionId) {
        if (!confirm('Are you sure you want to delete this question?')) return;

        const card = document.getElementById(`question-card-${questionId}`);
        if (!card) return;

        card.style.opacity = '0.5';

        try {
            const res  = await fetch(`/admin/questions/${questionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept':       'application/json',
                },
            });

            const data = await res.json();

            if (data.success) {
                card.remove();
                updateCount(-1);
                showToast(data.message, 'success');

                if (list.children.length === 0) {
                    list.innerHTML = emptyState();
                }
            } else {
                card.style.opacity = '1';
                showToast(data.message || 'Failed to delete question.', 'error');
            }
        } catch {
            card.style.opacity = '1';
            showToast('An error occurred. Please try again.', 'error');
        }
    }

    // ── Mark as read ──────────────────────────────────────────────────────────

    async function markQuestionAsRead(questionId) {
        try {
            const res  = await fetch(`/admin/questions/${questionId}/mark-read`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept':       'application/json',
                },
            });

            const data = await res.json();

            if (data.success) {
                const card = document.getElementById(`question-card-${questionId}`);
                if (card) {
                    // Remove NEW badges and mark-as-read button
                    card.querySelectorAll('[data-new-badge], [data-action="mark-as-read"]').forEach(el => el.remove());
                    // Swap border/bg from green to neutral
                    card.classList.replace('border-green-300', 'border-gray-200');
                    card.classList.replace('bg-green-50', 'bg-white');
                    // Swap answer box border
                    card.querySelector('.border-green-300')?.classList.replace('border-green-300', 'border-gray-200');
                }
                showToast('Marked as read.', 'success');
            }
        } catch {
            showToast('Failed to mark as read.', 'error');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    function showToast(message, type = 'success') {
        const toast = document.getElementById('qa-toast');
        if (!toast) return;
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
        if (countEl) countEl.textContent = parseInt(countEl.textContent) + delta;
    }

    function emptyState() {
        return `
        <div id="no-questions-message" class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500">No questions yet. Click "Ask Question" to start.</p>
        </div>`;
    }

    function buildQuestionCard(q) {
        return `
        <div id="question-card-${q.id}"
             class="border rounded-lg p-4 border-yellow-300 bg-yellow-50 transition-all">
            <div class="flex justify-between items-start mb-3">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        ${q.is_mandatory ? '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Mandatory</span>' : ''}
                        <span class="text-xs text-gray-500">Asked by ${escHtml(q.asked_by)}</span>
                    </div>
                    <div class="text-sm font-semibold text-gray-900 mb-1">Q: ${escHtml(q.question)}</div>
                    <div class="text-xs text-gray-500">${escHtml(q.asked_at)}</div>
                </div>
                <button type="button"
                        data-action="delete-question"
                        data-question-id="${q.id}"
                        class="text-red-600 hover:text-red-800 ml-4 flex-shrink-0 focus:outline-none focus:ring-2 focus:ring-red-500 rounded"
                        aria-label="Delete question">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <div class="mt-3 p-3 bg-white rounded border border-yellow-200">
                <p class="text-sm text-gray-600 italic">Waiting for client's response...</p>
            </div>
        </div>`;
    }

    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

})();
