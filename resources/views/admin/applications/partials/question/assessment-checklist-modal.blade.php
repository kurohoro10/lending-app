{{-- =========================================================================
     Assessment Checklist Request Modal
     =========================================================================
     Triggered by #open-assessment-checklist-btn in questions.blade.php.
     Clones the approval-decline-modal.blade.php pattern: editable subject
     + editable body textarea + Send. Unlike that modal, there's no status
     update step — Send does one thing: idempotently create any missing
     checklist Question rows, then dispatch one email via the existing
     assessmentChecklist.send route.
     ========================================================================= --}}

{{-- ── Backdrop ─────────────────────────────────────────────────────────── --}}
<div id="ac-backdrop"
     class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-40"
     aria-hidden="true"></div>

{{-- ── Modal ────────────────────────────────────────────────────────────── --}}
<div id="ac-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="ac-title">

    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl flex flex-col max-h-[90vh]">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center bg-indigo-100 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 id="ac-title" class="text-base font-semibold text-gray-900">Request Assessment Documents</h2>
                    <p id="ac-subtitle" class="text-xs text-gray-500 mt-0.5">
                        {{ $application->application_number }} · {{ $application->user->name }}
                    </p>
                </div>
            </div>
            <button type="button"
                    id="ac-close"
                    class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5 focus:outline-none
                           focus:ring-2 focus:ring-indigo-500 transition"
                    aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Notice banner --}}
        <div id="ac-notice"
             class="hidden mx-6 mt-4 rounded-lg px-4 py-3 text-sm font-medium"
             role="alert"
             aria-live="polite">
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">

            <p class="text-sm text-gray-600">
                Review and edit the email below before sending. Any checklist items not already
                requested on this application will appear in the client's portal when you click
                <strong>Send</strong>. Items already requested are never duplicated.
            </p>

            <p id="ac-status-line" class="hidden text-xs font-medium px-3 py-2 rounded-lg" role="status" aria-live="polite"></p>

            {{-- Subject --}}
            <div>
                <label for="ac-subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <input type="text"
                       id="ac-subject"
                       maxlength="255"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Email subject…">
            </div>

            {{-- Body --}}
            <div>
                <label for="ac-body" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea id="ac-body"
                          rows="16"
                          maxlength="5000"
                          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm font-mono
                                 leading-relaxed focus:outline-none focus:ring-2 focus:ring-indigo-500
                                 focus:border-indigo-500 resize-y"
                          placeholder="Email body…"></textarea>
                <p class="mt-1 text-xs text-gray-400 text-right">
                    <span id="ac-char-count">0</span> / 5000
                </p>
            </div>

            <p class="text-xs text-gray-400">
                An "Open Client Portal" button linking the client straight to their assessment
                checklist is added automatically after this message — it isn't part of the editable text above.
            </p>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl gap-3">
            <button type="button"
                    id="ac-cancel"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300
                           rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500
                           focus:ring-offset-2 transition">
                Cancel
            </button>

            <button type="button"
                    id="ac-send"
                    class="inline-flex items-center gap-2 px-5 py-2 border border-transparent rounded-md
                           font-semibold text-sm text-white bg-indigo-600 hover:bg-indigo-700
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                           disabled:opacity-60 disabled:cursor-not-allowed transition">
                <svg id="ac-send-spinner"
                     class="hidden animate-spin h-4 w-4 text-white"
                     fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span id="ac-send-label">Send</span>
            </button>
        </div>

    </div>
</div>

<script>
    (() => {
        const APP_ID = @js($application->id);
        const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content;

        const PREVIEW_URL = `/admin/applications/${APP_ID}/assessment-checklist/preview`;
        const SEND_URL     = `/admin/applications/${APP_ID}/assessment-checklist/send`;

        const backdrop    = document.getElementById('ac-backdrop');
        const modal       = document.getElementById('ac-modal');
        const noticeEl     = document.getElementById('ac-notice');
        const statusLine   = document.getElementById('ac-status-line');
        const subjectEl    = document.getElementById('ac-subject');
        const bodyEl        = document.getElementById('ac-body');
        const charCount     = document.getElementById('ac-char-count');
        const sendBtn        = document.getElementById('ac-send');
        const sendSpinner    = document.getElementById('ac-send-spinner');
        const sendLabel      = document.getElementById('ac-send-label');
        const closeBtn       = document.getElementById('ac-close');
        const cancelBtn      = document.getElementById('ac-cancel');
        const triggerBtn     = document.getElementById('open-assessment-checklist-btn');

        function updateCharCount() {
            charCount.textContent = bodyEl.value.length;
        }
        bodyEl?.addEventListener('input', updateCharCount);

        function showNotice(msg, isError = false) {
            noticeEl.textContent = msg;
            noticeEl.className = `mx-6 mt-4 rounded-lg px-4 py-3 text-sm font-medium ${
                isError ? 'bg-red-50 text-red-800 border border-red-200'
                        : 'bg-green-50 text-green-800 border border-green-200'
            }`;
            noticeEl.classList.remove('hidden');
        }
        function hideNotice() {
            noticeEl.classList.add('hidden');
            noticeEl.textContent = '';
        }

        function setSending(active) {
            sendBtn.disabled = active;
            sendSpinner.classList.toggle('hidden', !active);
            sendLabel.textContent = active ? 'Sending…' : 'Send';
        }

        async function open() {
            hideNotice();
            statusLine.classList.add('hidden');
            backdrop.classList.remove('hidden');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            subjectEl.value = '';
            bodyEl.value    = 'Loading…';
            updateCharCount();

            try {
                const res  = await fetch(PREVIEW_URL, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF } });
                const data = await res.json();

                subjectEl.value = data.subject;
                bodyEl.value    = data.body;
                updateCharCount();

                if (data.items_missing > 0) {
                    statusLine.textContent = `${data.items_missing} new checklist item(s) will be created for the client when you send.`;
                    statusLine.className = 'text-xs font-medium px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700';
                } else {
                    statusLine.textContent = 'All checklist items already exist — sending will just resend this email as a reminder.';
                    statusLine.className = 'text-xs font-medium px-3 py-2 rounded-lg bg-gray-50 text-gray-600';
                }
                statusLine.classList.remove('hidden');

            } catch (err) {
                bodyEl.value = '';
                showNotice('Could not load the checklist preview. Please close and try again.', true);
            }

            subjectEl.focus();
        }

        function close() {
            modal.classList.add('hidden');
            backdrop.classList.add('hidden');
            document.body.style.overflow = '';
            setSending(false);
        }

        async function send() {
            const subject = subjectEl.value.trim();
            const message = bodyEl.value.trim();

            if (!subject) { showNotice('Please enter a subject.', true); subjectEl.focus(); return; }
            if (!message) { showNotice('Please enter a message body.', true); bodyEl.focus(); return; }

            hideNotice();
            setSending(true);

            try {
                const res  = await fetch(SEND_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ subject, message }),
                });
                const data = await res.json();

                if (!res.ok || !data.success) {
                    showNotice(data.message ?? 'Failed to send the assessment request.', true);
                    setSending(false);
                    return;
                }

            } catch (err) {
                showNotice('Network error while sending. Please try again.', true);
                setSending(false);
                return;
            }

            close();
            window.location.reload();
        }

        sendBtn?.addEventListener('click', send);
        closeBtn?.addEventListener('click', close);
        cancelBtn?.addEventListener('click', close);
        backdrop?.addEventListener('click', close);
        triggerBtn?.addEventListener('click', open);

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
        });
    })();
</script>
