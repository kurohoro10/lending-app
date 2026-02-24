{{-- resources/views/admin/partials/communication/communication-modal.blade.php --}}

<div id="comm-modal-root">

    {{-- ── Trigger button + dropdown ──────────────────────────────────────── --}}
    <div class="relative inline-block">
        <button id="comm-dropdown-btn"
                type="button"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="comm-dropdown-menu"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Contact Client
            <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>

        <div id="comm-dropdown-menu"
             role="menu"
             aria-label="Contact options"
             class="hidden absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
            <button id="comm-open-email"
                    type="button"
                    role="menuitem"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50
                           flex items-center focus:outline-none focus:bg-indigo-50">
                <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Send Email
            </button>
            <button id="comm-open-sms"
                    type="button"
                    role="menuitem"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-green-50
                           flex items-center border-t border-gray-100 focus:outline-none focus:bg-green-50">
                <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-5 5v-5z"/>
                </svg>
                Send SMS/WhatsApp
            </button>
        </div>
    </div>

    {{-- ── Modal ───────────────────────────────────────────────────────────── --}}
    <div id="comm-modal"
         class="hidden fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true"
         aria-labelledby="comm-modal-title">

        {{-- Backdrop --}}
        <div id="comm-backdrop"
             class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div id="comm-modal-panel"
                 class="relative bg-white rounded-xl shadow-2xl max-w-2xl w-full">

                {{-- Header --}}
                <div id="comm-modal-header"
                     class="px-6 py-4 border-b border-gray-200 bg-indigo-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div id="comm-header-icon"
                                 class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                {{-- swapped by JS --}}
                                <svg id="comm-icon-email" class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <svg id="comm-icon-sms" class="hidden w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-5 5v-5z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 id="comm-modal-title"
                                    class="text-lg font-semibold text-gray-900">
                                    Send Email
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ $application->user->name }} • {{ $application->application_number }}
                                </p>
                            </div>
                        </div>
                        <button id="comm-close-btn"
                                type="button"
                                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 rounded"
                                aria-label="Close modal">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Toast --}}
                <div id="comm-toast"
                     class="hidden mx-6 mt-4 p-3 rounded-lg text-sm"
                     role="status"
                     aria-live="polite"
                     aria-atomic="true"></div>

                {{-- Body --}}
                <div class="p-6">

                    {{-- Template selector --}}
                    <div class="mb-4">
                        <label for="comm-template-select"
                               class="block text-sm font-medium text-gray-700 mb-2">
                            Choose Template
                        </label>
                        <select id="comm-template-select"
                                class="w-full border-gray-300 rounded-md shadow-sm
                                       focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Select a template --</option>
                        </select>
                    </div>

                    {{-- Subject (email only) --}}
                    <div id="comm-subject-wrap" class="mb-4">
                        <label for="comm-subject"
                               class="block text-sm font-medium text-gray-700 mb-2">
                            Subject <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input type="text"
                               id="comm-subject"
                               placeholder="Enter email subject..."
                               class="w-full border-gray-300 rounded-md shadow-sm
                                      focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    {{-- Message --}}
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <label for="comm-message"
                                   class="block text-sm font-medium text-gray-700">
                                Message <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <span id="comm-char-count"
                                  class="text-xs text-gray-500"
                                  aria-live="polite">
                                0 / 5000
                            </span>
                        </div>
                        <textarea id="comm-message"
                                  rows="12"
                                  maxlength="5000"
                                  placeholder="Type your email message here..."
                                  class="w-full border-gray-300 rounded-md shadow-sm
                                         focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono"></textarea>
                        <p id="comm-message-hint" class="mt-1 text-xs text-gray-500">
                            You can format with line breaks. Plain text only.
                        </p>
                    </div>

                    {{-- Recipient --}}
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-sm">
                        <div class="flex items-center text-gray-700 gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span id="comm-recipient-text">
                                Will be sent to: <strong>{{ $application->user->email }}</strong>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl flex justify-end gap-3">
                    <button id="comm-cancel-btn"
                            type="button"
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md text-sm
                                   font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 transition">
                        Cancel
                    </button>
                    <button id="comm-send-btn"
                            type="button"
                            disabled
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-md
                                   text-sm font-medium hover:bg-indigo-700
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                   disabled:opacity-50 disabled:cursor-not-allowed transition">
                        <svg id="comm-send-spinner"
                             class="hidden animate-spin h-3.5 w-3.5 text-white flex-shrink-0"
                             fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span id="comm-send-label">Send Email</span>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const APPLICATION_ID = @js($application->id);
    const EMAIL_RECIPIENT = @js($application->user->email);
    const SMS_RECIPIENT   = @js($application->personalDetails?->mobile_phone ?? 'No phone number');
    const CSRF            = document.querySelector('meta[name="csrf-token"]')?.content;

    // ── Element refs ──────────────────────────────────────────────────────────

    const dropdownBtn     = document.getElementById('comm-dropdown-btn');
    const dropdownMenu    = document.getElementById('comm-dropdown-menu');
    const openEmailBtn    = document.getElementById('comm-open-email');
    const openSmsBtn      = document.getElementById('comm-open-sms');

    const modal           = document.getElementById('comm-modal');
    const backdrop        = document.getElementById('comm-backdrop');
    const modalHeader     = document.getElementById('comm-modal-header');
    const headerIcon      = document.getElementById('comm-header-icon');
    const iconEmail       = document.getElementById('comm-icon-email');
    const iconSms         = document.getElementById('comm-icon-sms');
    const modalTitle      = document.getElementById('comm-modal-title');
    const closeBtn        = document.getElementById('comm-close-btn');
    const cancelBtn       = document.getElementById('comm-cancel-btn');
    const toast           = document.getElementById('comm-toast');

    const templateSelect  = document.getElementById('comm-template-select');
    const subjectWrap     = document.getElementById('comm-subject-wrap');
    const subjectInput    = document.getElementById('comm-subject');
    const messageInput    = document.getElementById('comm-message');
    const charCount       = document.getElementById('comm-char-count');
    const messageHint     = document.getElementById('comm-message-hint');
    const recipientText   = document.getElementById('comm-recipient-text');

    const sendBtn         = document.getElementById('comm-send-btn');
    const sendSpinner     = document.getElementById('comm-send-spinner');
    const sendLabel       = document.getElementById('comm-send-label');

    // ── State ─────────────────────────────────────────────────────────────────

    let modalType   = 'email';
    let templates   = {};
    let toastTimer  = null;

    // ── Dropdown ──────────────────────────────────────────────────────────────

    dropdownBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const open = dropdownMenu.classList.toggle('hidden');
        dropdownBtn.setAttribute('aria-expanded', String(!open));
    });

    document.addEventListener('click', (e) => {
        if (!dropdownMenu.contains(e.target) && e.target !== dropdownBtn) {
            dropdownMenu.classList.add('hidden');
            dropdownBtn.setAttribute('aria-expanded', 'false');
        }
    });

    // Close dropdown on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            dropdownMenu.classList.add('hidden');
            dropdownBtn.setAttribute('aria-expanded', 'false');
        }
    });

    openEmailBtn.addEventListener('click', () => openModal('email'));
    openSmsBtn.addEventListener('click',   () => openModal('sms'));

    // ── Modal open / close ────────────────────────────────────────────────────

    function openModal(type) {
        modalType = type;
        dropdownMenu.classList.add('hidden');
        dropdownBtn.setAttribute('aria-expanded', 'false');

        applyModalType();
        resetForm();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Return focus to close btn once visible
        requestAnimationFrame(() => closeBtn.focus());

        fetchTemplates();
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        resetForm();
        templates = {};
        dropdownBtn.focus(); // return focus to trigger
    }

    closeBtn.addEventListener('click',   closeModal);
    cancelBtn.addEventListener('click',  closeModal);
    backdrop.addEventListener('click',   closeModal);

    // Trap Escape inside modal
    modal.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });

    // Prevent clicks inside panel from closing modal
    document.getElementById('comm-modal-panel').addEventListener('click', (e) => e.stopPropagation());

    // ── Apply email vs SMS UI ─────────────────────────────────────────────────

    function applyModalType() {
        const isEmail = modalType === 'email';

        // Header bg + icon
        modalHeader.classList.toggle('bg-indigo-50', isEmail);
        modalHeader.classList.toggle('bg-green-50',  !isEmail);
        headerIcon.classList.toggle('bg-indigo-100', isEmail);
        headerIcon.classList.toggle('bg-green-100',  !isEmail);
        iconEmail.classList.toggle('hidden', !isEmail);
        iconSms.classList.toggle('hidden',    isEmail);

        // Title
        modalTitle.textContent = isEmail ? 'Send Email' : 'Send SMS/WhatsApp';

        // Subject field
        subjectWrap.classList.toggle('hidden', !isEmail);
        subjectInput.required = isEmail;

        // Textarea
        messageInput.rows       = isEmail ? 12 : 6;
        messageInput.maxLength  = isEmail ? 5000 : 1000;
        messageInput.placeholder = isEmail
            ? 'Type your email message here...'
            : 'Type your SMS message here (max 1000 characters)...';
        messageHint.textContent = isEmail
            ? 'You can format with line breaks. Plain text only.'
            : 'Keep it concise for SMS delivery.';

        // Char counter
        charCount.textContent = `0 / ${isEmail ? '5000' : '1000'}`;

        // Recipient
        recipientText.innerHTML = isEmail
            ? `Will be sent to: <strong>${EMAIL_RECIPIENT}</strong>`
            : `Will be sent to: <strong>${SMS_RECIPIENT}</strong>`;

        // Send button colour
        sendBtn.classList.toggle('bg-indigo-600',   isEmail);
        sendBtn.classList.toggle('hover:bg-indigo-700', isEmail);
        sendBtn.classList.toggle('focus:ring-indigo-500', isEmail);
        sendBtn.classList.toggle('bg-green-600',    !isEmail);
        sendBtn.classList.toggle('hover:bg-green-700',  !isEmail);
        sendBtn.classList.toggle('focus:ring-green-500',  !isEmail);
        sendBtn.setAttribute('aria-label', isEmail ? 'Send email' : 'Send SMS');
        sendLabel.textContent = isEmail ? 'Send Email' : 'Send SMS';
    }

    // ── Form helpers ──────────────────────────────────────────────────────────

    function resetForm() {
        subjectInput.value  = '';
        messageInput.value  = '';
        templateSelect.value = '';
        charCount.textContent = `0 / ${modalType === 'email' ? '5000' : '1000'}`;
        hideToast();
        validateForm();
    }

    function validateForm() {
        const hasSubject = modalType === 'sms' || subjectInput.value.trim().length > 0;
        const hasMessage = messageInput.value.trim().length > 0;
        sendBtn.disabled = !(hasSubject && hasMessage);
    }

    subjectInput.addEventListener('input', validateForm);
    messageInput.addEventListener('input', () => {
        validateForm();
        charCount.textContent = `${messageInput.value.length} / ${modalType === 'email' ? '5000' : '1000'}`;
    });

    // ── Templates ─────────────────────────────────────────────────────────────

    async function fetchTemplates() {
        const endpoint = modalType === 'email'
            ? `/admin/applications/${APPLICATION_ID}/email-templates`
            : `/admin/applications/${APPLICATION_ID}/sms-templates`;

        try {
            const res  = await fetch(endpoint, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const data = await res.json();
            if (data.success && data.templates) {
                templates = data.templates;
                populateTemplateSelect();
            }
        } catch {
            showToast('Failed to load templates.', 'error');
        }
    }

    function populateTemplateSelect() {
        // Remove all options except the first placeholder
        while (templateSelect.options.length > 1) {
            templateSelect.remove(1);
        }
        Object.entries(templates).forEach(([key, tpl]) => {
            const opt  = document.createElement('option');
            opt.value  = key;
            opt.textContent = tpl.label;
            templateSelect.appendChild(opt);
        });
    }

    templateSelect.addEventListener('change', () => {
        const key = templateSelect.value;
        if (!key) { resetForm(); return; }

        const tpl = templates[key];
        if (!tpl) return;

        if (modalType === 'email') {
            subjectInput.value = tpl.subject ?? '';
        }
        messageInput.value = tpl.body ?? '';
        charCount.textContent = `${messageInput.value.length} / ${modalType === 'email' ? '5000' : '1000'}`;
        validateForm();
    });

    // ── Send ──────────────────────────────────────────────────────────────────

    sendBtn.addEventListener('click', sendCommunication);

    async function sendCommunication() {
        if (sendBtn.disabled) return;

        setSending(true);

        const endpoint = modalType === 'email'
            ? `/admin/applications/${APPLICATION_ID}/send-email`
            : `/admin/applications/${APPLICATION_ID}/send-sms`;

        const payload = modalType === 'email'
            ? { subject: subjectInput.value, message: messageInput.value }
            : { message: messageInput.value };

        try {
            const res  = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type':  'application/json',
                    'X-CSRF-TOKEN':  CSRF,
                    'Accept':        'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => { closeModal(); window.location.reload(); }, 1500);
            } else {
                showToast(data.message || 'Something went wrong.', 'error');
                setSending(false);
            }
        } catch {
            showToast('An error occurred. Please try again.', 'error');
            setSending(false);
        }
    }

    function setSending(on) {
        sendBtn.disabled = on;
        sendSpinner.classList.toggle('hidden', !on);
        sendLabel.textContent = on
            ? 'Sending…'
            : (modalType === 'email' ? 'Send Email' : 'Send SMS');
    }

    // ── Toast ─────────────────────────────────────────────────────────────────

    function showToast(message, type = 'success') {
        clearTimeout(toastTimer);
        const isSuccess = type === 'success';
        toast.className = `mx-6 mt-4 p-3 rounded-lg text-sm ${
            isSuccess
                ? 'bg-green-100 text-green-800 border border-green-200'
                : 'bg-red-100 text-red-800 border border-red-200'
        }`;
        toast.textContent = message;
        toast.classList.remove('hidden');
        toastTimer = setTimeout(hideToast, 4000);
    }

    function hideToast() {
        toast.classList.add('hidden');
        toast.textContent = '';
    }

})();
</script>
