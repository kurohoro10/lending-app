// resources/js/admin/comments.js
//
// Handles four AJAX interactions with progressive enhancement throughout:
//   1. Add comment    (store)
//   2. Toggle pin     (togglePin)
//   3. Soft delete    (destroy) — shows 10-second undo toast
//   4. Restore        (restore) — triggered from undo toast
//
// All mutating requests send `Accept: application/json` so the controller
// knows to return JSON. Without JS the form falls back to a plain POST/redirect.
//
// Uses a single delegated listener on #comments-list for pin/delete/undo
// so dynamically injected comments are automatically covered.

(() => {
    // ── Element references ──────────────────────────────────────────────────
    const form       = document.getElementById('comment-form');
    const list       = document.getElementById('comments-list');
    const submitBtn  = document.getElementById('comment-submit-btn');
    const spinner    = document.getElementById('comment-spinner');
    const statusMsg  = document.getElementById('comment-submit-status');
    const fieldError = document.getElementById('comment-field-error');
    const textarea   = document.getElementById('comment-text');

    // Exit silently if comments section isn't on this page
    if (!form || !list) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    // Tracks active countdown timers keyed by comment ID so they can be
    // cleared immediately if the user hits Undo before the timer expires.
    const undoTimers = {};

    // ── Shared fetch helper ──────────────────────────────────────────────────

    async function ajaxRequest(url, method = 'POST', body = null) {
        const options = {
            method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept':       'application/json',
            },
        };

        if (body) options.body = body;

        const res  = await fetch(url, options);
        const data = await res.json();

        if (!res.ok || !data.success) {
            throw new Error(data.message ?? 'Request failed.');
        }

        return data;
    }

    // ── DOM helpers ──────────────────────────────────────────────────────────

    function setLoading(loading) {
        if (submitBtn) submitBtn.disabled = loading;
        spinner?.classList.toggle('hidden', !loading);
    }

    function setStatus(message, isError = false) {
        if (!statusMsg) return;
        statusMsg.textContent = message;
        statusMsg.className   = `text-sm ${isError ? 'text-red-600' : 'text-green-600'}`;
    }

    function clearStatus() {
        if (statusMsg) {
            statusMsg.textContent = '';
            statusMsg.className   = 'text-sm';
        }
    }

    function showFieldError(message) {
        if (!fieldError) return;
        fieldError.textContent = message;
        fieldError.classList.remove('hidden');
        textarea?.setAttribute('aria-invalid', 'true');
        textarea?.focus();
    }

    function clearFieldError() {
        if (!fieldError) return;
        fieldError.textContent = '';
        fieldError.classList.add('hidden');
        textarea?.removeAttribute('aria-invalid');
    }

    /** Prepend a rendered HTML string to the top of the comments list */
    function prependComment(html) {
        document.getElementById('no-comments-msg')?.remove();

        const node = htmlToElement(html);
        if (!node) return;

        list.prepend(node);
        flashHighlight(node);
    }

    /** Replace an existing comment element with new HTML */
    function replaceComment(commentId, html) {
        const existing = document.getElementById(`comment-${commentId}`);
        const node     = htmlToElement(html);
        if (!existing || !node) return;
        existing.replaceWith(node);
        flashHighlight(node);
    }

    function htmlToElement(html) {
        const temp = document.createElement('div');
        temp.innerHTML = html.trim();
        return temp.firstElementChild ?? null;
    }

    function flashHighlight(node) {
        node.classList.add('ring-2', 'ring-indigo-300', 'transition-shadow');
        setTimeout(() => node.classList.remove('ring-2', 'ring-indigo-300'), 1500);
    }

    // ── 1. Add comment ───────────────────────────────────────────────────────

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearStatus();
        clearFieldError();

        if (!textarea?.value.trim()) {
            showFieldError('Please enter a comment before submitting.');
            return;
        }

        setLoading(true);
        setStatus('Saving…');

        try {
            const data = await ajaxRequest(form.dataset.action, 'POST', new FormData(form));
            prependComment(data.html);
            form.reset();
            setStatus('Comment added successfully.');
            setTimeout(clearStatus, 3000);
        } catch (err) {
            setStatus(err.message || 'Something went wrong. Please try again.', true);
        } finally {
            setLoading(false);
        }
    });

    // ── Delegated listener for pin, delete, undo ─────────────────────────────
    // Covers both server-rendered and dynamically injected comment elements.

    list.addEventListener('click', (e) => {
        const pinBtn    = e.target.closest('.comment-pin-btn');
        const deleteBtn = e.target.closest('.comment-delete-btn');
        const undoBtn   = e.target.closest('.comment-undo-btn');

        if (pinBtn)    handlePin(pinBtn);
        if (deleteBtn) handleDelete(deleteBtn);
        if (undoBtn)   handleUndo(undoBtn);
    });

    // ── 2. Toggle pin ────────────────────────────────────────────────────────

    async function handlePin(btn) {
        const { commentId, pinRoute } = btn.dataset;
        if (!commentId || !pinRoute) return;

        btn.disabled = true;

        try {
            const data = await ajaxRequest(pinRoute, 'POST');
            replaceComment(commentId, data.html);
        } catch (err) {
            alert(err.message || 'Failed to update pin.');
        } finally {
            // Re-enable only if the button still exists (replaceWith may have removed it)
            btn.disabled = false;
        }
    }

    // ── 3. Soft delete ───────────────────────────────────────────────────────

    async function handleDelete(btn) {
        const { commentId, deleteRoute } = btn.dataset;
        const commentEl = document.getElementById(`comment-${commentId}`);
        if (!commentId || !deleteRoute || !commentEl) return;

        btn.disabled = true;

        try {
            const data = await ajaxRequest(deleteRoute, 'DELETE');

            // Swap the comment out for the undo toast
            const toastNode = htmlToElement(data.undo_html);
            if (!toastNode) { commentEl.remove(); return; }

            commentEl.replaceWith(toastNode);
            startUndoCountdown(commentId, toastNode);

        } catch (err) {
            alert(err.message || 'Failed to delete comment.');
            btn.disabled = false;
        }
    }

    /**
     * Start the 10-second countdown on the undo toast.
     * When it expires the toast fades out and is removed.
     */
    function startUndoCountdown(commentId, toastNode) {
        const countdownEl = document.getElementById(`comment-undo-countdown-${commentId}`);
        let remaining = 10;

        const interval = setInterval(() => {
            remaining--;

            if (countdownEl) countdownEl.textContent = `${remaining}s`;

            if (remaining <= 0) {
                clearInterval(interval);
                delete undoTimers[commentId];
                toastNode.style.transition = 'opacity 0.4s ease';
                toastNode.style.opacity    = '0';
                setTimeout(() => {
                    toastNode.remove();
                    if (list.children.length === 0) showNoCommentsPlaceholder();
                }, 400);
            }
        }, 1000);

        undoTimers[commentId] = interval;
    }

    // ── 4. Restore (undo) ────────────────────────────────────────────────────

    async function handleUndo(btn) {
        const { commentId, restoreRoute } = btn.dataset;
        const toastEl = document.getElementById(`comment-undo-${commentId}`);
        if (!commentId || !restoreRoute) return;

        // Clear the countdown immediately so it doesn't fire while we wait
        if (undoTimers[commentId]) {
            clearInterval(undoTimers[commentId]);
            delete undoTimers[commentId];
        }

        btn.disabled = true;

        try {
            const data = await ajaxRequest(restoreRoute, 'PATCH');

            // Replace the toast with the restored comment
            if (toastEl) {
                const node = htmlToElement(data.html);
                if (node) {
                    toastEl.replaceWith(node);
                    flashHighlight(node);
                }
            }
        } catch (err) {
            alert(err.message || 'Failed to restore comment.');
            btn.disabled = false;
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    function showNoCommentsPlaceholder() {
        if (document.getElementById('no-comments-msg')) return;
        const p = document.createElement('p');
        p.id        = 'no-comments-msg';
        p.className = 'text-sm text-gray-500 italic';
        p.textContent = 'No comments yet.';
        list.appendChild(p);
    }

})();
