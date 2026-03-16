// resources/js/applications/client-questions.js
// Handles: answer submission + inline document upload per question card

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const section   = document.getElementById('client-questions-section');
    const csrf      = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const announcer = document.getElementById('client-qa-announcer');
    const toastEl   = document.getElementById('client-qa-toast');

    if (!section) return;

    const ALLOWED_TYPES = [
        'application/pdf', 'image/jpeg', 'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];
    const MAX_SIZE = 10 * 1024 * 1024; // 10 MB

    // ── File input wiring (one per card) ─────────────────────────────────────
    // Run on page load for server-rendered cards; called again after answer to
    // handle any future dynamic renders if needed.

    function wireCard(card) {
        const fileInput = card.querySelector('.doc-file-input');
        if (!fileInput || fileInput.dataset.wired) return;
        fileInput.dataset.wired = '1';

        const preview     = card.querySelector('.doc-file-preview');
        const previewName = card.querySelector('.doc-preview-name');
        const previewSize = card.querySelector('.doc-preview-size');
        const clearBtn    = card.querySelector('.doc-clear-btn');
        const uploadError = card.querySelector('.doc-upload-error');

        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (!file) return;

            uploadError?.classList.add('hidden');

            if (!ALLOWED_TYPES.includes(file.type)) {
                showUploadError(uploadError, 'Unsupported file type. Please upload a PDF, image, Word, or Excel file.');
                fileInput.value = '';
                return;
            }
            if (file.size > MAX_SIZE) {
                showUploadError(uploadError, 'File size must not exceed 10 MB.');
                fileInput.value = '';
                return;
            }

            // Show inline file preview
            if (previewName) previewName.textContent = file.name;
            if (previewSize) previewSize.textContent = formatBytes(file.size);
            preview?.classList.remove('hidden');
        });

        clearBtn?.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            fileInput.value = '';
            preview?.classList.add('hidden');
            uploadError?.classList.add('hidden');
            // Return focus to the upload trigger label
            card.querySelector('.doc-upload-trigger')?.focus();
        });
    }

    // Wire all cards present on load
    section.querySelectorAll('.question-card').forEach(wireCard);

    // ── CreditSense bank-connect wiring ───────────────────────────────────────

    function wireBankConnectCard(card) {
        const connectBtn = card.querySelector('.cs-connect-btn');
        if (!connectBtn || connectBtn.dataset.wired) return;
        connectBtn.dataset.wired = '1';

        connectBtn.addEventListener('click', () => launchCreditSense(card));
    }

    section.querySelectorAll('.question-card[data-bank-connect="true"]').forEach(wireBankConnectCard);

    async function launchCreditSense(card) {
        const questionId   = card.dataset.questionId;
        const configRoute  = card.querySelector('.cs-bank-panel')?.dataset.configRoute;
        const completeRoute = card.querySelector('.cs-bank-panel')?.dataset.completeRoute;
        const connectBtn   = card.querySelector('.cs-connect-btn');
        const connectIcon  = card.querySelector('.cs-connect-icon');
        const connectSpinner = card.querySelector('.cs-connect-spinner');
        const connectLabel = card.querySelector('.cs-connect-label');
        const iframeContainer = card.querySelector('.cs-iframe-container');
        const iframeLoading   = card.querySelector('.cs-iframe-loading');
        const iframeEl        = card.querySelector('.cs-iframe');
        const iframeError     = card.querySelector('.cs-iframe-error');
        const iframeErrorMsg  = card.querySelector('.cs-iframe-error-msg');

        if (!configRoute) return;

        // Loading state on button
        connectBtn.disabled = true;
        connectIcon?.classList.add('hidden');
        connectSpinner?.classList.remove('hidden');
        if (connectLabel) connectLabel.textContent = 'Connecting…';

        try {
            const res = await fetch(configRoute, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            });

            // Read the JSON body regardless of status so we can surface the server message
            const config = await res.json().catch(() => null);

            if (!res.ok) {
                // 500 from iframeConfig means CreditSense is not configured in Settings
                const serverMsg = config?.error ?? null;
                const isNotConfigured = res.status === 500 || (serverMsg && serverMsg.toLowerCase().includes('not configured'));

                throw new Error(
                    isNotConfigured
                        ? 'Bank connection is not available right now. Please contact support.'
                        : (serverMsg ?? 'Failed to load bank connection config.')
                );
            }

            if (config.already_completed) {
                await autoAnswerBankConnect(questionId, completeRoute, card);
                return;
            }

            // Show iframe container
            iframeContainer?.classList.remove('hidden');

            // Load SDK
            const cdnUrl = config.cdn_url
                ?? 'https://6dadc58e31982fd9f0be-d4a1ccb0c1936ef2a5b7f304db75b8a4.ssl.cf4.rackcdn.com/CS-Integrated-Iframe-v1.min.js';
            await loadCsSdk(cdnUrl);

            // Init SDK pointing at this card's iframe
            if (typeof jQuery === 'undefined' || !jQuery.CreditSense?.Iframe) {
                throw new Error('CreditSense SDK did not load correctly.');
            }

            jQuery.CreditSense.Iframe({
                client:              config.client_code,
                elementSelector:     `#creditSenseIFrame-${questionId}`,
                enableDynamicHeight: true,
                params: { appRef: config.app_ref, centrelink: true },
                callback: (code) => handleCsCallback(code, questionId, completeRoute, card),
            });

        } catch (err) {
            // Restore the connect button
            connectBtn.disabled = false;
            connectIcon?.classList.remove('hidden');
            connectSpinner?.classList.add('hidden');
            if (connectLabel) connectLabel.textContent = 'Try Again';

            // If the iframe container was never shown (config failed before we got there),
            // show the error inline below the connect button row instead
            const containerVisible = iframeContainer && !iframeContainer.classList.contains('hidden');

            if (containerVisible) {
                iframeLoading?.classList.add('hidden');
                if (iframeErrorMsg) iframeErrorMsg.textContent = err.message;
                iframeError?.classList.remove('hidden');
            } else {
                // Show error inside the panel without opening the iframe container
                let errEl = card.querySelector('.cs-config-error');
                if (!errEl) {
                    errEl = document.createElement('p');
                    errEl.className = 'cs-config-error mt-2 text-xs text-red-600 flex items-center gap-1';
                    errEl.setAttribute('role', 'alert');
                    errEl.setAttribute('aria-live', 'polite');
                    card.querySelector('.cs-bank-panel')?.appendChild(errEl);
                }
                errEl.innerHTML = `<svg class="w-3.5 h-3.5 flex-shrink-0 text-red-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM9 9a1 1 0 000 2v3a1 1 0 102 0v-3a1 1 0 000-2H9zm0-4a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/></svg> ${esc(err.message)}`;
                errEl.classList.remove('hidden');
                errEl.focus();
            }
        }
    }

    function handleCsCallback(code, questionId, completeRoute, card) {
        const iframeLoading = card.querySelector('.cs-iframe-loading');
        const iframeEl      = card.querySelector('.cs-iframe');
        const iframeError   = card.querySelector('.cs-iframe-error');
        const iframeErrorMsg = card.querySelector('.cs-iframe-error-msg');

        switch (String(code)) {
            case '0':
                // Iframe ready — show it
                iframeLoading?.classList.add('hidden');
                if (iframeEl) {
                    iframeEl.classList.remove('hidden');
                    iframeEl.setAttribute('aria-busy', 'false');
                }
                break;

            case '99':
            case '100':
                // Success — mark complete on server, then auto-answer the question
                autoAnswerBankConnect(questionId, completeRoute, card);
                break;

            case '-1':
                // Cancelled — restore connect button
                card.querySelector('.cs-iframe-container')?.classList.add('hidden');
                const btn = card.querySelector('.cs-connect-btn');
                if (btn) {
                    btn.disabled = false;
                    card.querySelector('.cs-connect-icon')?.classList.remove('hidden');
                    card.querySelector('.cs-connect-spinner')?.classList.add('hidden');
                    const lbl = card.querySelector('.cs-connect-label');
                    if (lbl) lbl.textContent = 'Connect My Bank';
                    btn.focus();
                }
                break;

            case '-2':
                // Timed out
                iframeLoading?.classList.add('hidden');
                if (iframeErrorMsg) iframeErrorMsg.textContent = 'Connection timed out. Please refresh the page and try again.';
                iframeError?.classList.remove('hidden');
                break;
        }
    }

    async function autoAnswerBankConnect(questionId, completeRoute, card) {
        try {
            // Step 1: mark CreditSense complete on server
            if (completeRoute) {
                await fetch(completeRoute, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                });
            }

            // Step 2: submit a system answer — stored server-side, not shown to client
            const answerRes = await fetch(card.dataset.answerRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ answer: 'Bank account connected via CreditSense.' }),
            });
            const answerData = await answerRes.json();

            if (answerData.success) {
                // No text to display — markCardAnswered detects bank_connect and shows the pill
                markCardAnswered(card, answerData.answered_at);
                updatePendingBanner();
                showToast('Bank account connected successfully.', 'success');
                announce('Bank account connected. Question marked as answered.');
            }
        } catch {
            showToast('Bank connected but failed to update question. Please refresh.', 'error');
        }
    }

    function loadCsSdk(cdnUrl) {
        return new Promise((resolve, reject) => {
            if (typeof jQuery !== 'undefined' && jQuery.CreditSense) { resolve(); return; }
            if (document.getElementById('cs-sdk-script')) {
                document.getElementById('cs-sdk-script').addEventListener('load', resolve);
                return;
            }
            if (typeof jQuery === 'undefined') { reject(new Error('jQuery is required by the CreditSense SDK.')); return; }
            const s = document.createElement('script');
            s.id = 'cs-sdk-script';
            s.src = cdnUrl;
            s.onload = resolve;
            s.onerror = () => reject(new Error('Failed to load CreditSense SDK.'));
            document.head.appendChild(s);
        });
    }

    // ── Delegated: submit answer button ──────────────────────────────────────

    section.addEventListener('click', e => {
        const btn = e.target.closest('.submit-answer-btn');
        if (btn) handleSubmit(btn);
    });

    // Ctrl/Cmd+Enter inside textarea
    section.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const ta = e.target.closest('.answer-input');
            if (!ta) return;
            const qId = ta.dataset.questionId;
            const btn = section.querySelector(`.submit-answer-btn[data-question-id="${qId}"]`);
            if (btn && !btn.disabled) handleSubmit(btn);
        }
    });

    // ── Submit handler ────────────────────────────────────────────────────────

    async function handleSubmit(btn) {
        const questionId  = btn.dataset.questionId;
        const requiresDoc = btn.dataset.requiresDoc === 'true';
        const docCategory = btn.dataset.docCategory;

        const card      = section.querySelector(`#client-question-card-${questionId}`);
        const textarea  = card?.querySelector('.answer-input');
        const answerErr = card?.querySelector('.answer-error');

        if (!card || !textarea) return;

        const answerText = textarea.value.trim();

        // Validate text answer
        if (!answerText) {
            showUploadError(answerErr, 'Please enter an answer.');
            textarea.setAttribute('aria-invalid', 'true');
            textarea.focus();
            return;
        }

        answerErr?.classList.add('hidden');
        textarea.removeAttribute('aria-invalid');

        btn.disabled = true;
        btn.querySelector('.btn-text').textContent = 'Submitting…';
        btn.querySelector('.btn-spinner').classList.remove('hidden');

        try {
            // Step 1 — submit the text answer
            const answerRes = await fetch(`/questions/${questionId}/answer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ answer: answerText }),
            });

            const answerData = await answerRes.json();

            if (!answerData.success) {
                throw new Error(answerData.message || 'Failed to submit answer.');
            }

            // Step 2 — upload document if one was selected
            if (requiresDoc) {
                const fileInput = card.querySelector('.doc-file-input');
                const file = fileInput?.files[0];

                if (file) {
                    await uploadDocument(card, file, docCategory, questionId);
                }
                // If no file chosen, that's OK — upload was optional
            }

            // Step 3 — update DOM to answered state
            card._pendingAnswerText = answerText;
            markCardAnswered(card, answerData.answered_at);
            updatePendingBanner();
            showToast(answerData.message ?? 'Answer submitted successfully.', 'success');
            announce(answerData.message ?? 'Answer submitted.');

        } catch (err) {
            showToast(err.message || 'A network error occurred. Please try again.', 'error');
            btn.disabled = false;
            btn.querySelector('.btn-text').textContent = 'Submit Answer';
            btn.querySelector('.btn-spinner').classList.add('hidden');
        }
    }

    // ── Document upload (XHR for progress) ───────────────────────────────────

    function uploadDocument(card, file, docCategory, questionId) {
        const uploadRoute = card.dataset.uploadRoute;
        const progressWrap = card.querySelector('.doc-upload-progress');
        const progressBar  = card.querySelector('.doc-progress-bar');
        const progressPct  = card.querySelector('.doc-progress-pct');
        const successWrap  = card.querySelector('.doc-upload-success');
        const successName  = card.querySelector('.doc-upload-success-name');
        const uploadError  = card.querySelector('.doc-upload-error');

        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('document', file);
            formData.append('document_category', docCategory);
            formData.append('description', `Uploaded in response to question #${questionId}`);
            formData.append('_token', csrf());

            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', e => {
                if (!e.lengthComputable) return;
                const pct = Math.round((e.loaded / e.total) * 100);
                progressWrap?.classList.remove('hidden');
                if (progressBar)  { progressBar.style.width = `${pct}%`; }
                if (progressPct)  { progressPct.textContent  = `${pct}%`; }
                const pb = progressWrap?.querySelector('[role="progressbar"]');
                if (pb) pb.setAttribute('aria-valuenow', pct);
            });

            xhr.addEventListener('load', () => {
                progressWrap?.classList.add('hidden');
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (xhr.status >= 200 && xhr.status < 300 && data.success) {
                        successName.textContent = file.name;
                        successWrap?.classList.remove('hidden');
                        announce(`Document "${file.name}" uploaded successfully.`);
                        resolve(data);
                    } else {
                        showUploadError(uploadError, data.message || 'Document upload failed.');
                        resolve(null); // non-blocking — answer was already saved
                    }
                } catch {
                    showUploadError(uploadError, 'Unexpected response from server.');
                    resolve(null);
                }
            });

            xhr.addEventListener('error', () => {
                progressWrap?.classList.add('hidden');
                showUploadError(uploadError, 'Network error during upload. Please upload the document separately.');
                resolve(null); // non-blocking
            });

            xhr.open('POST', uploadRoute);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrf());
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.send(formData);
        });
    }

    // ── DOM: mark a card as answered ─────────────────────────────────────────

    function markCardAnswered(card, answeredAt) {
        const isBankConnect = card.dataset.bankConnect === 'true';

        // Swap background
        card.classList.replace('bg-amber-50', 'bg-gray-50');
        card.classList.replace('border-amber-200', 'border-gray-200');
        card.dataset.status = 'answered';

        // Status badge
        const badge = card.querySelector('.question-status');
        if (badge) {
            badge.className = badge.className
                .replace('bg-amber-100 text-amber-700', 'bg-green-100 text-green-700');
            badge.textContent = 'Answered';
            badge.setAttribute('aria-label', 'Status: Answered');
        }

        // Icon
        const iconWrap = card.querySelector('[aria-hidden="true"].rounded-full');
        if (iconWrap) {
            iconWrap.classList.replace('bg-amber-100', 'bg-gray-200');
            iconWrap.querySelector('svg')?.classList.replace('text-amber-600', 'text-gray-500');
        }

        // Meta line — append answered date
        if (answeredAt) {
            const meta = card.querySelector('.text-xs.text-gray-500');
            if (meta) {
                const base = meta.textContent.trim().split('·')[0].trim();
                meta.textContent = `${base} · Answered ${answeredAt}`;
            }
        }

        // Replace form with appropriate answered display
        const form = card.querySelector('.answer-form');
        if (form) {
            const div = document.createElement('div');

            if (isBankConnect) {
                div.className = 'answer-display mt-1 flex items-center gap-2.5 px-3 py-2.5 rounded-xl border border-green-200 bg-green-50';
                div.setAttribute('role', 'status');
                div.innerHTML = `
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-green-800">Bank account connected</p>
                        ${answeredAt ? `<p class="text-xs text-green-600">Completed ${esc(answeredAt)}</p>` : ''}
                    </div>`;
            } else {
                div.className = 'answer-display mt-1 p-3 bg-white rounded-xl border border-gray-200';
                div.innerHTML = `<p class="text-sm text-gray-700 whitespace-pre-wrap">${esc(card._pendingAnswerText ?? '')}</p>`;
            }

            form.replaceWith(div);
        }
    }

    // ── Pending banner update ─────────────────────────────────────────────────

    function updatePendingBanner() {
        const remaining = section.querySelectorAll('.question-card[data-status="pending"]').length;
        const banner    = document.getElementById('pending-questions-warning');
        if (!banner) return;

        if (remaining === 0) {
            banner.style.opacity = '0';
            banner.style.transition = 'opacity 0.3s ease';
            setTimeout(() => banner.remove(), 300);
        } else {
            const countEl = document.getElementById('pending-count');
            const badgeEl = document.getElementById('pending-badge');
            if (countEl) countEl.textContent = remaining;
            if (badgeEl) badgeEl.textContent  = remaining;
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    let toastTimer = null;

    function showToast(message, type = 'success') {
        if (!toastEl) return;
        const ok = type === 'success';
        toastEl.className = `mb-4 p-3 rounded-xl text-sm font-medium border ${
            ok ? 'bg-green-50 border-green-200 text-green-800'
               : 'bg-red-50 border-red-200 text-red-800'}`;
        toastEl.textContent = message;
        toastEl.classList.remove('hidden');
        toastEl.focus();
        clearTimeout(toastTimer);
        if (ok) toastTimer = setTimeout(() => toastEl.classList.add('hidden'), 4000);
    }

    function announce(msg) {
        if (!announcer) return;
        announcer.textContent = '';
        requestAnimationFrame(() => { announcer.textContent = msg; });
    }

    function showUploadError(el, msg) {
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function formatBytes(bytes) {
        if (!bytes) return '0 B';
        const k = 1024, sizes = ['B','KB','MB','GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${(bytes / Math.pow(k, i)).toFixed(1)} ${sizes[i]}`;
    }

    function esc(str) {
        return String(str ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Global: scroll to questions (called from pending banner) ─────────────
    window.scrollToQuestions = function () {
        if (!section) return;
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        section.style.boxShadow = '0 0 0 3px rgba(99,102,241,0.3)';
        setTimeout(() => section.style.boxShadow = '', 1500);
        setTimeout(() => {
            section.querySelector('.answer-input')?.focus();
        }, 500);
    };

});