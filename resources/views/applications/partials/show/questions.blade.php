{{-- resources/views/applications/partials/show/questions.blade.php --}}
@if($application->questions->count() > 0)
<div id="client-questions-section"
     class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200
            transition-all duration-500">

    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
            Questions from Assessment Team
        </h3>
    </div>

    <div class="p-6">

        {{-- Toast --}}
        <div id="client-qa-toast"
             class="hidden mb-4 p-3 rounded-xl text-sm"
             role="status"
             aria-live="polite"
             aria-atomic="true"></div>

        <ol class="space-y-3" aria-label="Assessment team questions">
            @foreach($application->questions as $question)
                <li class="question-card rounded-xl border p-4 transition-colors
                           {{ $question->status === 'pending'
                               ? 'bg-amber-50 border-amber-200'
                               : 'bg-gray-50 border-gray-200' }}"
                    data-question-id="{{ $question->id }}"
                    data-status="{{ $question->status }}">

                    <div class="flex items-start justify-between gap-3">

                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <div class="h-8 w-8 rounded-full flex items-center justify-center flex-shrink-0
                                        {{ $question->status === 'pending' ? 'bg-amber-100' : 'bg-gray-200' }}"
                                 aria-hidden="true">
                                <svg class="h-4 w-4 {{ $question->status === 'pending' ? 'text-amber-600' : 'text-gray-500' }}"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $question->question }}
                                    @if($question->is_mandatory)
                                        <span class="text-red-500 ml-0.5" aria-label="required">*</span>
                                    @endif
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    @if($question->asked_by_name){{ $question->asked_by_name }} · @endif
                                    {{ $question->created_at->format('d M Y') }}
                                    @if($question->status === 'answered')
                                        · Answered {{ $question->answered_at->format('d M Y') }}
                                    @endif
                                </p>

                                @if($question->status === 'pending')
                                    {{-- Answer form --}}
                                    <div class="answer-form mt-3">
                                        <label for="answer-input-{{ $question->id }}"
                                               class="sr-only">
                                            Your answer to: {{ $question->question }}
                                        </label>
                                        <textarea id="answer-input-{{ $question->id }}"
                                                  class="answer-input w-full text-sm border border-gray-300 rounded-xl
                                                         px-3 py-2 resize-none
                                                         focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                                  rows="3"
                                                  placeholder="Type your answer here… (Ctrl+Enter to submit)"
                                                  aria-required="{{ $question->is_mandatory ? 'true' : 'false' }}"
                                                  data-question-id="{{ $question->id }}"></textarea>
                                        <p class="answer-error hidden mt-1 text-xs text-red-600" role="alert"></p>
                                        <div class="mt-2 flex justify-end">
                                            <button type="button"
                                                    class="submit-answer-btn inline-flex items-center gap-2 px-4 py-2
                                                           bg-indigo-600 text-white text-xs font-semibold rounded-xl
                                                           hover:bg-indigo-700 transition
                                                           disabled:opacity-50 disabled:cursor-not-allowed
                                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                                                    data-question-id="{{ $question->id }}"
                                                    aria-label="Submit answer for question {{ $loop->iteration }}">
                                                <span class="btn-text">Submit Answer</span>
                                                <svg class="btn-spinner hidden animate-spin h-3 w-3"
                                                     fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                            stroke="currentColor" stroke-width="4"/>
                                                    <path class="opacity-75" fill="currentColor"
                                                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    {{-- Answered display --}}
                                    <div class="answer-display mt-3 p-3 bg-white rounded-xl border border-gray-200">
                                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $question->answer }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <span class="question-status flex-shrink-0 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                     {{ $question->status === 'pending'
                                         ? 'bg-amber-100 text-amber-700'
                                         : 'bg-green-100 text-green-700' }}">
                            {{ $question->status === 'pending' ? 'Pending' : 'Answered' }}
                        </span>

                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</div>

<script>
(() => {
    const section   = document.getElementById('client-questions-section');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!section) return;

    // ── Toast ─────────────────────────────────────────────────────────────────
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('client-qa-toast');
        if (!toast) return;
        const ok = type === 'success';
        toast.className = `mb-4 p-3 rounded-xl text-sm font-medium border ${
            ok ? 'bg-green-50 border-green-200 text-green-800'
               : 'bg-red-50 border-red-200 text-red-800'}`;
        toast.textContent = msg;
        toast.classList.remove('hidden');
        if (ok) setTimeout(() => toast.classList.add('hidden'), 4000);
    }

    // ── Update pending banner ─────────────────────────────────────────────────
    function updatePendingBanner() {
        const count    = section.querySelectorAll('.question-card[data-status="pending"]').length;
        const banner   = document.getElementById('pending-questions-warning');
        if (!banner) return;
        if (count === 0) {
            banner.style.opacity = '0';
            banner.style.transition = 'opacity 0.3s ease';
            setTimeout(() => banner.remove(), 300);
        } else {
            const countEl = document.getElementById('pending-count');
            const badgeEl = document.getElementById('pending-badge');
            if (countEl) countEl.textContent = count;
            if (badgeEl) badgeEl.textContent  = count;
        }
    }

    // ── Submit answer ─────────────────────────────────────────────────────────
    async function submitAnswer(questionId, btn) {
        const card      = section.querySelector(`.question-card[data-question-id="${questionId}"]`);
        if (!card) return;

        const textarea  = card.querySelector('.answer-input');
        const errorEl   = card.querySelector('.answer-error');
        const btnText   = btn.querySelector('.btn-text');
        const spinner   = btn.querySelector('.btn-spinner');

        if (!textarea.value.trim()) {
            errorEl.textContent = 'Please enter an answer.';
            errorEl.classList.remove('hidden');
            textarea.focus();
            return;
        }

        errorEl.classList.add('hidden');
        btn.disabled         = true;
        btnText.textContent  = 'Submitting…';
        spinner.classList.remove('hidden');

        try {
            const res  = await fetch(`/questions/${questionId}/answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ answer: textarea.value.trim() }),
            });

            const data = await res.json();

            if (data.success) {
                // Update card styling
                card.className = card.className
                    .replace('bg-amber-50 border-amber-200', 'bg-gray-50 border-gray-200');
                card.dataset.status = 'answered';

                // Update status badge
                const badge = card.querySelector('.question-status');
                if (badge) {
                    badge.className = badge.className
                        .replace('bg-amber-100 text-amber-700', 'bg-green-100 text-green-700');
                    badge.textContent = 'Answered';
                }

                // Update icon
                const iconWrap = card.querySelector('[aria-hidden="true"].rounded-full');
                if (iconWrap) {
                    iconWrap.className = iconWrap.className
                        .replace('bg-amber-100', 'bg-gray-200');
                    const svg = iconWrap.querySelector('svg');
                    if (svg) svg.className = svg.className.replace('text-amber-600', 'text-gray-500');
                }

                // Update meta line
                const meta = card.querySelector('.text-xs.text-gray-500');
                if (meta) {
                    const base = meta.textContent.split('·')[0].trim();
                    meta.textContent = `${base} · Answered ${data.answered_at}`;
                }

                // Swap form → answer display
                const form = card.querySelector('.answer-form');
                if (form) {
                    const div = document.createElement('div');
                    div.className = 'answer-display mt-3 p-3 bg-white rounded-xl border border-gray-200';
                    div.innerHTML = `<p class="text-sm text-gray-700 whitespace-pre-wrap">${escHtml(data.answer)}</p>`;
                    form.replaceWith(div);
                }

                updatePendingBanner();
                showToast(data.message ?? 'Answer submitted.', 'success');
            } else {
                showToast(data.message ?? 'Failed to submit answer.', 'error');
                btn.disabled        = false;
                btnText.textContent = 'Submit Answer';
                spinner.classList.add('hidden');
            }
        } catch {
            showToast('A network error occurred. Please try again.', 'error');
            btn.disabled        = false;
            btnText.textContent = 'Submit Answer';
            spinner.classList.add('hidden');
        }
    }

    // ── Event delegation: click ───────────────────────────────────────────────
    section.addEventListener('click', (e) => {
        const btn = e.target.closest('.submit-answer-btn');
        if (btn) submitAnswer(btn.dataset.questionId, btn);
    });

    // ── Event delegation: Ctrl/Cmd+Enter ─────────────────────────────────────
    section.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const textarea = e.target.closest('.answer-input');
            if (!textarea) return;
            const btn = section.querySelector(`.submit-answer-btn[data-question-id="${textarea.dataset.questionId}"]`);
            if (btn && !btn.disabled) submitAnswer(textarea.dataset.questionId, btn);
        }
    });

    function escHtml(str) {
        return String(str ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();

// Global: called from pending-questions.blade.php banner
function scrollToQuestions() {
    const section = document.getElementById('client-questions-section');
    if (!section) return;
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    section.style.boxShadow = '0 0 0 3px rgba(99, 102, 241, 0.3)';
    setTimeout(() => section.style.boxShadow = '', 1500);
    setTimeout(() => {
        const first = section.querySelector('.answer-input');
        if (first) first.focus();
    }, 500);
}
</script>
@endif
