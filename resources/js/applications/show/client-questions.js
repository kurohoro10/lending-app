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
            markCardAnswered(card, answerText, answerData.answered_at);
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

    function markCardAnswered(card, answerText, answeredAt) {
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

        // Replace form with answer display
        const form = card.querySelector('.answer-form');
        if (form) {
            const div = document.createElement('div');
            div.className = 'answer-display mt-1 p-3 bg-white rounded-xl border border-gray-200';
            div.innerHTML = `<p class="text-sm text-gray-700 whitespace-pre-wrap">${esc(answerText)}</p>`;
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