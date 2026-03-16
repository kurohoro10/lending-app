// resources/js/admin/questions.js
// Handles: template picker, ask-form toggle, submit question, delete, mark-as-read

(() => {
    'use strict';

    const applicationId = window.APP_QUESTION?.applicationId;
    const csrf          = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const list          = document.getElementById('questions-list');

    if (!list || !applicationId) return;

    // ── Element refs ──────────────────────────────────────────────────────────

    const toggleBtn        = document.getElementById('toggle-ask-form-btn');
    const askForm          = document.getElementById('ask-question-form');
    const cancelBtn        = document.getElementById('cancel-btn');
    const templateSelect   = document.getElementById('question-template-select');
    const docIndicator     = document.getElementById('doc-required-indicator');
    const docCategoryLabel = document.getElementById('doc-required-category-label');
    const docCategoryValue = document.getElementById('doc-category-hint-value');
    const removeDocBtn     = document.getElementById('remove-doc-requirement-btn');
    const questionInput    = document.getElementById('question-input');
    const charCount        = document.getElementById('question-charcount');
    const mandatoryCheck   = document.getElementById('is-mandatory');
    const questionError    = document.getElementById('question-error');
    const submitBtn        = document.getElementById('submit-question-btn');
    const submitText       = document.getElementById('submit-question-text');
    const submitSpinner    = document.getElementById('submit-question-spinner');
    const toastEl          = document.getElementById('qa-toast');
    const announcer        = document.getElementById('qa-announcer');

    const DOC_LABELS = {
        id:          'Identification',
        income:      'Income Documentation',
        bank:        'Bank Statements',
        assets:      'Asset Documentation',
        liabilities: 'Liability Documentation',
        employment:  'Employment / Business Verification',
        other:       'Other Documents',
    };

    // ── Ask-form toggle ───────────────────────────────────────────────────────

    function openForm() {
        askForm.classList.remove('hidden');
        toggleBtn.setAttribute('aria-expanded', 'true');
        templateSelect?.focus();
    }

    function closeForm() {
        askForm.classList.add('hidden');
        toggleBtn.setAttribute('aria-expanded', 'false');
        resetForm();
        toggleBtn.focus();
    }

    toggleBtn?.addEventListener('click', () =>
        askForm.classList.contains('hidden') ? openForm() : closeForm()
    );
    cancelBtn?.addEventListener('click', closeForm);
    askForm?.addEventListener('keydown', e => { if (e.key === 'Escape') closeForm(); });

    // ── Character counter ─────────────────────────────────────────────────────

    questionInput?.addEventListener('input', updateCharCount);

    function updateCharCount() {
        const len = questionInput.value.length;
        charCount.textContent = `${len} / 1000`;
        charCount.classList.toggle('text-red-500', len > 900);
        charCount.classList.toggle('text-gray-400', len <= 900);
    }

    // ── Template picker ───────────────────────────────────────────────────────
    // Value format: "docCategory|questionText|isMandatory|requiresDoc"

    templateSelect?.addEventListener('change', () => {
        const raw = templateSelect.value;
        if (!raw) return;

        const [category, questionText, mandatoryStr, requiresDocStr] = raw.split('|');

        questionInput.value    = questionText;
        mandatoryCheck.checked = mandatoryStr === 'true';
        updateCharCount();

        if (requiresDocStr === 'true' && category && DOC_LABELS[category]) {
            docCategoryValue.value       = category;
            docCategoryLabel.textContent = DOC_LABELS[category];
            docIndicator.classList.remove('hidden');
        } else {
            clearDocRequirement();
        }

        templateSelect.value = ''; // allow reselection of same template
        announce('Template applied. Review the question text before sending.');
        questionInput.focus();
        questionInput.setSelectionRange(questionInput.value.length, questionInput.value.length);
    });

    removeDocBtn?.addEventListener('click', () => {
        clearDocRequirement();
        announce('Document upload requirement removed.');
    });

    function clearDocRequirement() {
        if (docCategoryValue) docCategoryValue.value = '';
        docIndicator?.classList.add('hidden');
    }

    // ── Submit question ───────────────────────────────────────────────────────

    submitBtn?.addEventListener('click', submitQuestion);
    questionInput?.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); submitQuestion(); }
    });

    async function submitQuestion() {
        const text = questionInput.value.trim();
        if (!text) {
            showFieldError(questionError, questionInput, 'Please enter a question.');
            return;
        }

        clearFieldError(questionError, questionInput);
        setLoading(true);

        try {
            const res = await fetch(`/admin/applications/${applicationId}/questions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'Accept':       'application/json',
                },
                body: JSON.stringify({
                    question:          text,
                    is_mandatory:      mandatoryCheck.checked,
                    doc_category_hint: docCategoryValue?.value || null,
                }),
            });

            const data = await res.json();

            if (data.success) {
                document.getElementById('no-questions-message')?.remove();
                list.insertAdjacentHTML('afterbegin', buildQuestionCard(data.question));
                closeForm();
                updateCount(1);
                showToast(data.message, 'success');
                announce(data.message);
            } else {
                showToast(data.message || 'Failed to send question.', 'error');
            }
        } catch {
            showToast('A network error occurred. Please try again.', 'error');
        } finally {
            setLoading(false);
        }
    }

    function setLoading(on) {
        submitBtn.disabled     = on;
        submitText.textContent = on ? 'Sending…' : 'Send Question';
        submitSpinner?.classList.toggle('hidden', !on);
    }

    function resetForm() {
        questionInput.value    = '';
        updateCharCount();
        mandatoryCheck.checked = false;
        templateSelect.value   = '';
        clearDocRequirement();
        clearFieldError(questionError, questionInput);
    }

    // ── Delegated: delete + mark-as-read ─────────────────────────────────────

    list.addEventListener('click', e => {
        const del  = e.target.closest('[data-action="delete-question"]');
        const read = e.target.closest('[data-action="mark-as-read"]');
        if (del)  deleteQuestion(del.dataset.questionId);
        if (read) markAsRead(read.dataset.questionId);
    });

    // ── Delete ────────────────────────────────────────────────────────────────

    async function deleteQuestion(id) {
        if (!confirm('Are you sure you want to delete this question?')) return;

        const card = document.getElementById(`question-card-${id}`);
        if (!card) return;

        card.style.opacity = '0.5';
        card.setAttribute('aria-busy', 'true');

        try {
            const res  = await fetch(`/admin/questions/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (data.success) {
                card.remove();
                updateCount(-1);
                showToast(data.message, 'success');
                announce(data.message);
                if (!list.querySelector('[id^="question-card-"]')) {
                    list.innerHTML = emptyState();
                }
            } else {
                card.style.opacity = '1';
                card.removeAttribute('aria-busy');
                showToast(data.message || 'Failed to delete.', 'error');
            }
        } catch {
            card.style.opacity = '1';
            card.removeAttribute('aria-busy');
            showToast('A network error occurred. Please try again.', 'error');
        }
    }

    // ── Mark as read ──────────────────────────────────────────────────────────

    async function markAsRead(id) {
        try {
            const res  = await fetch(`/admin/questions/${id}/mark-read`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (data.success) {
                const card = document.getElementById(`question-card-${id}`);
                if (card) {
                    card.querySelectorAll('[data-new-badge],[data-action="mark-as-read"]')
                        .forEach(el => el.remove());
                    card.classList.replace('border-green-300', 'border-gray-200');
                    card.classList.replace('bg-green-50', 'bg-white');
                    card.querySelector('.border-green-200')
                        ?.classList.replace('border-green-200', 'border-gray-200');
                }
                showToast('Marked as read.', 'success');
                announce('Question marked as read.');
            }
        } catch {
            showToast('Failed to mark as read.', 'error');
        }
    }

    // ── Card builder (optimistic insert) ─────────────────────────────────────

    function buildQuestionCard(q) {
        const docBadge = q.doc_category_hint && DOC_LABELS[q.doc_category_hint]
            ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full
                            bg-indigo-100 text-indigo-700 font-medium"
                     aria-label="Document requested: ${esc(DOC_LABELS[q.doc_category_hint])}">
                   <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                       <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                   </svg>
                   ${esc(DOC_LABELS[q.doc_category_hint])}
               </span>`
            : '';

        return `
        <div id="question-card-${q.id}"
             class="border rounded-lg p-4 transition-all border-yellow-300 bg-yellow-50"
             role="listitem"
             aria-label="Question: ${esc(q.question.slice(0, 80))}">
            <div class="flex justify-between items-start">
                <div class="flex-1 min-w-0 pr-4">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800 font-medium">Pending</span>
                        ${q.is_mandatory ? '<span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700 font-medium">Mandatory</span>' : ''}
                        ${docBadge}
                        <span class="text-xs text-gray-500">Asked by ${esc(q.asked_by)}</span>
                    </div>
                    <p class="text-sm font-semibold text-gray-900 mb-1">Q: ${esc(q.question)}</p>
                    <p class="text-xs text-gray-500"><time>${esc(q.asked_at)}</time></p>
                </div>
                <button type="button"
                        data-action="delete-question"
                        data-question-id="${q.id}"
                        class="text-red-500 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 rounded p-1"
                        aria-label="Delete question: ${esc(q.question.slice(0, 60))}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <div class="mt-3 p-3 bg-white rounded border border-yellow-200">
                <p class="text-sm text-gray-500 italic">Waiting for client's response…</p>
            </div>
        </div>`;
    }

    // ── Utilities ─────────────────────────────────────────────────────────────

    let toastTimer = null;

    function showToast(message, type = 'success') {
        if (!toastEl) return;
        const ok = type === 'success';
        toastEl.className = `mb-4 p-4 rounded-lg text-sm font-medium border ${
            ok ? 'bg-green-50 border-green-200 text-green-800'
               : 'bg-red-50 border-red-200 text-red-800'}`;
        toastEl.textContent = message;
        toastEl.classList.remove('hidden');
        toastEl.focus();
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toastEl.classList.add('hidden'), 5000);
    }

    function announce(msg) {
        if (!announcer) return;
        announcer.textContent = '';
        requestAnimationFrame(() => { announcer.textContent = msg; });
    }

    function updateCount(delta) {
        const el = document.getElementById('qa-count');
        if (!el) return;
        const next = (parseInt(el.textContent) || 0) + delta;
        el.textContent = next;
        el.setAttribute('aria-label', `${next} questions total`);
    }

    function showFieldError(errorEl, inputEl, msg) {
        if (errorEl) { errorEl.textContent = msg; errorEl.classList.remove('hidden'); }
        if (inputEl) { inputEl.setAttribute('aria-invalid', 'true'); inputEl.focus(); }
    }

    function clearFieldError(errorEl, inputEl) {
        if (errorEl) { errorEl.textContent = ''; errorEl.classList.add('hidden'); }
        if (inputEl) inputEl.removeAttribute('aria-invalid');
    }

    function emptyState() {
        return `<div id="no-questions-message" class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500">No questions yet. Click "Ask Question" to start.</p>
        </div>`;
    }

    function esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

})();