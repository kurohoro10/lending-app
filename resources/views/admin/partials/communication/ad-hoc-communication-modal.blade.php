<div id="adhoc-modal-root">

    {{-- ── Trigger Button ───────────────────────────────── --}}
    <button id="adhoc-open-btn"
            type="button"
            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md
                   font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
            aria-haspopup="dialog"
            aria-controls="adhoc-offcanvas"
            aria-label="Send message to third party">
        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Contact Third Party
    </button>

    {{-- ── Backdrop ───────────────────────────────────────── --}}
    <div id="adhoc-backdrop"
         class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-40"
         aria-hidden="true"></div>

    {{-- ── Off-canvas Panel ──────────────────────────────── --}}
    <div id="adhoc-offcanvas"
         class="hidden fixed inset-y-0 right-0 z-50 w-full max-w-2xl flex flex-col bg-white shadow-2xl
                transform translate-x-full transition-transform duration-300 ease-in-out"
         role="dialog"
         aria-modal="true"
         aria-labelledby="adhoc-panel-title">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-amber-50">
            <div>
                <h2 id="adhoc-panel-title" class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                        Third Party
                    </span>
                    Contact Third Party
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ $application->application_number }} — message will be logged to this application</p>
            </div>

            <button type="button"
                    id="adhoc-close-btn"
                    class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg p-1.5 transition"
                    aria-label="Close third party communication panel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="border-b border-gray-200 bg-white">
            <nav class="-mb-px flex" role="tablist" aria-label="Communication channels">

                <button type="button"
                        role="tab"
                        id="adhoc-tab-email"
                        data-adhoc-tab="email"
                        aria-selected="true"
                        aria-controls="adhoc-panel-email"
                        class="adhoc-tab flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium
                               border-b-2 border-indigo-600 text-indigo-600
                               focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    Email
                </button>

                <button type="button"
                        role="tab"
                        id="adhoc-tab-sms"
                        data-adhoc-tab="sms"
                        aria-selected="false"
                        aria-controls="adhoc-panel-sms"
                        class="adhoc-tab flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium
                               border-b-2 border-transparent text-gray-500
                               hover:text-gray-700 hover:border-gray-300
                               focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    SMS / WhatsApp
                </button>

            </nav>
        </div>

        {{-- ── Email Panel ──────────────────────────────────────────────────── --}}
        <div id="adhoc-panel-email"
             role="tabpanel"
             aria-labelledby="adhoc-tab-email"
             class="adhoc-tab-panel flex-1 flex flex-col min-h-0 overflow-y-auto">

            <div class="flex-1 px-5 py-4 space-y-4">

                {{-- Info banner --}}
                <div class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    This email will be sent to a third party and logged against application
                    <strong>{{ $application->application_number }}</strong>.
                </div>

                {{-- Email toast --}}
                <div id="adhoc-email-toast"
                     class="hidden p-2.5 rounded-lg text-xs"
                     role="status"
                     aria-live="polite"
                     aria-atomic="true"></div>

                {{-- Recipient name --}}
                <div>
                    <label for="adhoc-email-recipient-name" class="block text-xs font-medium text-gray-600 mb-1">
                        Recipient Name <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="text"
                           id="adhoc-email-recipient-name"
                           autocomplete="off"
                           placeholder="e.g. John Smith"
                           aria-required="true"
                           class="w-full text-sm border-gray-300 rounded-md shadow-sm
                                  focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- Recipient email --}}
                <div>
                    <label for="adhoc-email-recipient-email" class="block text-xs font-medium text-gray-600 mb-1">
                        Recipient Email <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="email"
                           id="adhoc-email-recipient-email"
                           autocomplete="off"
                           placeholder="e.g. john@example.com"
                           aria-required="true"
                           class="w-full text-sm border-gray-300 rounded-md shadow-sm
                                  focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- Template --}}
                <div>
                    <label for="adhoc-email-template" class="block text-xs font-medium text-gray-600 mb-1">
                        Template
                    </label>
                    <select id="adhoc-email-template"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm
                                   focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">— Select a template —</option>
                    </select>
                </div>

                {{-- Subject --}}
                <div>
                    <label for="adhoc-email-subject" class="block text-xs font-medium text-gray-600 mb-1">
                        Subject <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="text"
                           id="adhoc-email-subject"
                           autocomplete="off"
                           placeholder="Email subject…"
                           aria-required="true"
                           class="w-full text-sm border-gray-300 rounded-md shadow-sm
                                  focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- Message --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="adhoc-email-message" class="block text-xs font-medium text-gray-600">
                            Message <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <span id="adhoc-email-char-count" class="text-xs text-gray-400" aria-live="polite">0 / 5000</span>
                    </div>
                    <textarea id="adhoc-email-message"
                              rows="8"
                              maxlength="5000"
                              placeholder="Type your message…"
                              aria-required="true"
                              class="w-full text-sm border-gray-300 rounded-md shadow-sm resize-none
                                     focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                {{-- Send --}}
                <div class="flex justify-end pt-1">
                    <button type="button"
                            id="adhoc-email-send-btn"
                            disabled
                            class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white text-xs
                                   font-semibold rounded-md hover:bg-indigo-700 focus:outline-none
                                   focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                   disabled:opacity-50 disabled:cursor-not-allowed transition">
                        <svg id="adhoc-email-send-spinner" class="hidden animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg id="adhoc-email-send-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span id="adhoc-email-send-label">Send Email</span>
                    </button>
                </div>

            </div>
        </div>

        {{-- ── SMS Panel ────────────────────────────────────────────────────── --}}
        <div id="adhoc-panel-sms" role="tabpanel" aria-labelledby="adhoc-tab-sms" class="adhoc-tab-panel hidden flex-1 flex flex-col min-h-0 overflow-y-auto">

            <div class="flex-1 px-5 py-4 space-y-4">

                {{-- Info banner --}}
                <div class="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    This SMS will be sent to a third party and logged against application
                    <strong>{{ $application->application_number }}</strong>.
                </div>

                {{-- SMS toast --}}
                <div id="adhoc-sms-toast"
                     class="hidden p-2.5 rounded-lg text-xs"
                     role="status"
                     aria-live="polite"
                     aria-atomic="true"></div>

                {{-- Recipient name --}}
                <div>
                    <label for="adhoc-sms-recipient-name" class="block text-xs font-medium text-gray-600 mb-1">
                        Recipient Name <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="text" id="adhoc-sms-recipient-name" autocomplete="off" placeholder="e.g. John Smith" aria-required="true" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>

                {{-- Recipient phone --}}
                <div>
                    <label for="adhoc-sms-recipient-phone" class="block text-xs font-medium text-gray-600 mb-1">
                        Mobile Number <span class="text-red-500" aria-hidden="true">*</span>
                    </label>
                    <input type="tel" id="adhoc-sms-recipient-phone" autocomplete="off" placeholder="e.g. +61400000000" aria-required="true" class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                    <p class="mt-1 text-xs text-gray-400">Include country code (e.g. +61 for Australia).</p>
                </div>

                {{-- Template --}}
                <div>
                    <label for="adhoc-sms-template" class="block text-xs font-medium text-gray-600 mb-1">
                        Template
                    </label>
                    <select id="adhoc-sms-template"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm
                                   focus:ring-green-500 focus:border-green-500">
                        <option value="">— Select a template —</option>
                    </select>
                </div>

                {{-- Message --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="adhoc-sms-message" class="block text-xs font-medium text-gray-600">
                            Message <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <span id="adhoc-sms-char-count" class="text-xs text-gray-400" aria-live="polite">0 / 1000</span>
                    </div>
                    <textarea id="adhoc-sms-message"
                              rows="5"
                              maxlength="1000"
                              placeholder="Type your message…"
                              aria-required="true"
                              class="w-full text-sm border-gray-300 rounded-md shadow-sm resize-none
                                     focus:ring-green-500 focus:border-green-500"></textarea>
                    <p class="mt-1 text-xs text-gray-400">Keep concise for best SMS delivery.</p>
                </div>

                {{-- Send --}}
                <div class="flex justify-end pt-1">
                    <button type="button"
                            id="adhoc-sms-send-btn"
                            disabled
                            class="inline-flex items-center gap-2 px-5 py-2 bg-green-600 text-white text-xs
                                   font-semibold rounded-md hover:bg-green-700 focus:outline-none
                                   focus:ring-2 focus:ring-green-500 focus:ring-offset-2
                                   disabled:opacity-50 disabled:cursor-not-allowed transition">
                        <svg id="adhoc-sms-send-spinner" class="hidden animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg id="adhoc-sms-send-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span id="adhoc-sms-send-label">Send SMS</span>
                    </button>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
(() => {
    const APP_ID = @js($application->id);
    const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content;

    const openBtn   = document.getElementById('adhoc-open-btn');
    const backdrop  = document.getElementById('adhoc-backdrop');
    const offcanvas = document.getElementById('adhoc-offcanvas');
    const closeBtn  = document.getElementById('adhoc-close-btn');
    const tabs      = document.querySelectorAll('.adhoc-tab');
    const panels    = document.querySelectorAll('.adhoc-tab-panel');

    // ── Email elements ────────────────────────────────────────────────────────
    const emailRecipientName  = document.getElementById('adhoc-email-recipient-name');
    const emailRecipientEmail = document.getElementById('adhoc-email-recipient-email');
    const emailTemplate       = document.getElementById('adhoc-email-template');
    const emailSubject        = document.getElementById('adhoc-email-subject');
    const emailMessage        = document.getElementById('adhoc-email-message');
    const emailCharCount      = document.getElementById('adhoc-email-char-count');
    const emailSendBtn        = document.getElementById('adhoc-email-send-btn');
    const emailSendSpinner    = document.getElementById('adhoc-email-send-spinner');
    const emailSendIcon       = document.getElementById('adhoc-email-send-icon');
    const emailSendLabel      = document.getElementById('adhoc-email-send-label');
    const emailToast          = document.getElementById('adhoc-email-toast');

    // ── SMS elements ──────────────────────────────────────────────────────────
    const smsRecipientName  = document.getElementById('adhoc-sms-recipient-name');
    const smsRecipientPhone = document.getElementById('adhoc-sms-recipient-phone');
    const smsTemplate       = document.getElementById('adhoc-sms-template');
    const smsMessage        = document.getElementById('adhoc-sms-message');
    const smsCharCount      = document.getElementById('adhoc-sms-char-count');
    const smsSendBtn        = document.getElementById('adhoc-sms-send-btn');
    const smsSendSpinner    = document.getElementById('adhoc-sms-send-spinner');
    const smsSendIcon       = document.getElementById('adhoc-sms-send-icon');
    const smsSendLabel      = document.getElementById('adhoc-sms-send-label');
    const smsToast          = document.getElementById('adhoc-sms-toast');

    // ── Template caches ───────────────────────────────────────────────────────
    let emailTemplates = {};
    let smsTemplates   = {};
    let emailTemplatesLoaded = false;
    let smsTemplatesLoaded   = false;

    // ── Open / close ──────────────────────────────────────────────────────────
    function openPanel() {
        backdrop.classList.remove('hidden');
        offcanvas.classList.remove('hidden');
        requestAnimationFrame(() => {
            offcanvas.classList.remove('translate-x-full');
            closeBtn.focus();
        });
        document.body.style.overflow = 'hidden';
        loadEmailTemplates();
    }

    function closePanel() {
        offcanvas.classList.add('translate-x-full');
        offcanvas.addEventListener('transitionend', function handler() {
            offcanvas.classList.add('hidden');
            backdrop.classList.add('hidden');
            offcanvas.removeEventListener('transitionend', handler);
        });
        document.body.style.overflow = '';
        openBtn.focus();
    }

    openBtn.addEventListener('click', openPanel);
    closeBtn.addEventListener('click', closePanel);
    backdrop.addEventListener('click', closePanel);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !offcanvas.classList.contains('hidden')) closePanel();
    });

    // ── Tab switching ─────────────────────────────────────────────────────────
    function activateTab(key) {
        tabs.forEach(t => {
            const active = t.dataset.adhocTab === key;
            t.setAttribute('aria-selected', active ? 'true' : 'false');
            t.classList.toggle('border-indigo-600',  active);
            t.classList.toggle('text-indigo-600',    active);
            t.classList.toggle('border-transparent', !active);
            t.classList.toggle('text-gray-500',      !active);
        });
        panels.forEach(p => p.classList.toggle('hidden', p.id !== `adhoc-panel-${key}`));

        if (key === 'sms') loadSmsTemplates();
    }

    tabs.forEach(t => t.addEventListener('click', () => activateTab(t.dataset.adhocTab)));

    // Arrow key navigation
    const tabList = Array.from(tabs);
    tabList.forEach((tab, idx) => {
        tab.addEventListener('keydown', e => {
            let target = null;
            if (e.key === 'ArrowRight') target = tabList[(idx + 1) % tabList.length];
            if (e.key === 'ArrowLeft')  target = tabList[(idx - 1 + tabList.length) % tabList.length];
            if (e.key === 'Home')       target = tabList[0];
            if (e.key === 'End')        target = tabList[tabList.length - 1];
            if (target) { e.preventDefault(); target.click(); target.focus(); }
        });
    });

    // ── Template loading ──────────────────────────────────────────────────────
    async function loadEmailTemplates() {
        if (emailTemplatesLoaded) return;
        try {
            const res  = await fetch(`/admin/applications/${APP_ID}/ad-hoc/email-templates`, {
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            const data = await res.json();
            if (data.success && data.templates) {
                emailTemplates = data.templates;
                Object.entries(emailTemplates).forEach(([key, tpl]) => {
                    emailTemplate.appendChild(
                        Object.assign(document.createElement('option'), { value: key, textContent: tpl.label })
                    );
                });
                emailTemplatesLoaded = true;
            }
        } catch { /* silently fail */ }
    }

    async function loadSmsTemplates() {
        if (smsTemplatesLoaded) return;
        try {
            const res  = await fetch(`/admin/applications/${APP_ID}/ad-hoc/sms-templates`, {
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            const data = await res.json();
            if (data.success && data.templates) {
                smsTemplates = data.templates;
                Object.entries(smsTemplates).forEach(([key, tpl]) => {
                    smsTemplate.appendChild(
                        Object.assign(document.createElement('option'), { value: key, textContent: tpl.label })
                    );
                });
                smsTemplatesLoaded = true;
            }
        } catch { /* silently fail */ }
    }

    emailTemplate.addEventListener('change', () => {
        const tpl = emailTemplates[emailTemplate.value];
        if (!tpl) return;
        emailSubject.value = tpl.subject ?? '';
        emailMessage.value = tpl.body ?? '';
        emailCharCount.textContent = `${emailMessage.value.length} / 5000`;
        validateEmail();
    });

    smsTemplate.addEventListener('change', () => {
        const tpl = smsTemplates[smsTemplate.value];
        if (!tpl) return;
        smsMessage.value = tpl.body ?? '';
        smsCharCount.textContent = `${smsMessage.value.length} / 1000`;
        validateSms();
    });

    // ── Validation ────────────────────────────────────────────────────────────
    function validateEmail() {
        emailSendBtn.disabled = !(
            emailRecipientName.value.trim() &&
            emailRecipientEmail.value.trim() &&
            emailSubject.value.trim() &&
            emailMessage.value.trim()
        );
    }

    function validateSms() {
        smsSendBtn.disabled = !(
            smsRecipientName.value.trim() &&
            smsRecipientPhone.value.trim() &&
            smsMessage.value.trim()
        );
    }

    [emailRecipientName, emailRecipientEmail, emailSubject].forEach(el =>
        el.addEventListener('input', validateEmail)
    );
    emailMessage.addEventListener('input', () => {
        emailCharCount.textContent = `${emailMessage.value.length} / 5000`;
        validateEmail();
    });

    [smsRecipientName, smsRecipientPhone].forEach(el =>
        el.addEventListener('input', validateSms)
    );
    smsMessage.addEventListener('input', () => {
        smsCharCount.textContent = `${smsMessage.value.length} / 1000`;
        validateSms();
    });

    // ── Send email ────────────────────────────────────────────────────────────
    emailSendBtn.addEventListener('click', async () => {
        if (emailSendBtn.disabled) return;
        setSending('email', true);
        try {
            const res  = await fetch(`/admin/applications/${APP_ID}/ad-hoc/send-email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    recipient_name:  emailRecipientName.value,
                    recipient_email: emailRecipientEmail.value,
                    subject:         emailSubject.value,
                    message:         emailMessage.value,
                }),
            });
            const data = await res.json();
            if (data.success) {
                showToast('email', data.message ?? 'Email sent.', 'success');
                resetEmailForm();
            } else {
                showToast('email', data.message ?? 'Failed to send.', 'error');
                setSending('email', false);
            }
        } catch {
            showToast('email', 'An error occurred. Please try again.', 'error');
            setSending('email', false);
        }
    });

    // ── Send SMS ──────────────────────────────────────────────────────────────
    smsSendBtn.addEventListener('click', async () => {
        if (smsSendBtn.disabled) return;
        setSending('sms', true);
        try {
            const res  = await fetch(`/admin/applications/${APP_ID}/ad-hoc/send-sms`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    recipient_name:  smsRecipientName.value,
                    recipient_phone: smsRecipientPhone.value,
                    message:         smsMessage.value,
                }),
            });
            const data = await res.json();
            if (data.success) {
                showToast('sms', data.message ?? 'SMS sent.', 'success');
                resetSmsForm();
            } else {
                showToast('sms', data.message ?? 'Failed to send.', 'error');
                setSending('sms', false);
            }
        } catch {
            showToast('sms', 'An error occurred. Please try again.', 'error');
            setSending('sms', false);
        }
    });

    // ── Helpers ───────────────────────────────────────────────────────────────
    function setSending(channel, on) {
        if (channel === 'email') {
            emailSendBtn.disabled = on;
            emailSendSpinner.classList.toggle('hidden', !on);
            emailSendIcon.classList.toggle('hidden', on);
            emailSendLabel.textContent = on ? 'Sending…' : 'Send Email';
        } else {
            smsSendBtn.disabled = on;
            smsSendSpinner.classList.toggle('hidden', !on);
            smsSendIcon.classList.toggle('hidden', on);
            smsSendLabel.textContent = on ? 'Sending…' : 'Send SMS';
        }
    }

    function resetEmailForm() {
        emailRecipientName.value  = '';
        emailRecipientEmail.value = '';
        emailSubject.value        = '';
        emailMessage.value        = '';
        emailTemplate.value       = '';
        emailCharCount.textContent = '0 / 5000';
        setSending('email', false);
        validateEmail();
    }

    function resetSmsForm() {
        smsRecipientName.value  = '';
        smsRecipientPhone.value = '';
        smsMessage.value        = '';
        smsTemplate.value       = '';
        smsCharCount.textContent = '0 / 1000';
        setSending('sms', false);
        validateSms();
    }

    const toastTimers = {};
    function showToast(channel, msg, type) {
        const toast = channel === 'email' ? emailToast : smsToast;
        clearTimeout(toastTimers[channel]);
        const ok = type === 'success';
        toast.className = `p-2.5 rounded-lg text-xs ${ok
            ? 'bg-green-50 border border-green-200 text-green-800'
            : 'bg-red-50 border border-red-200 text-red-800'}`;
        toast.textContent = msg;
        toast.classList.remove('hidden');
        toastTimers[channel] = setTimeout(() => toast.classList.add('hidden'), 4000);
    }

    window.AdHocPanel = { open: openPanel, close: closePanel };
})();
</script>