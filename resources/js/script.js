// resources/js/application/script.js
// Global scripts

export function toggleAccordion(id) {
    const content = document.getElementById(id + '-content');
    const button = content.previousElementSibling;
    const chevron = document.getElementById(id + '-chevron');

    const isExpanded = button.getAttribute('aria-expanded') === 'true';

    if (isExpanded) {
        content.classList.add('hidden');
        button.setAttribute('aria-expanded', 'false');
        chevron.classList.remove('rotate-180');
    } else {
        content.classList.remove('hidden');
        button.setAttribute('aria-expanded', 'true');
        chevron.classList.add('rotate-180');
    }
}

/**
 * Strip everything except digits and a single decimal point.
 * Returns a plain numeric string suitable for a hidden input or server submission.
 *
 * e.g. "$1,234.50" -> "1234.50"
 */
export function toRawNumber(value) {
    const stripped = value.replace(/[^0-9.]/g, '');
    const parts = stripped.split('.');
    if (parts.length > 2) {
        return parts[0] + '.' + parts.slice(1).join('');
    }
    return stripped;
}

/**
 * Format a raw numeric string with thousand-separator commas.
 * Preserves a trailing decimal point and up to 2 decimal places while typing.
 *
 * e.g. "1234567.5" -> "1,234,567.5"
 */
export function formatWithCommas(raw) {
    if (!raw) return '';

    const parts = raw.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    if (parts.length === 2) {
        return parts[0] + '.' + parts[1].slice(0, 2);
    }
    return parts[0];
}

/**
 * Wire up a visible text input + its paired hidden input for live currency formatting.
 *
 * The display input shows the comma-formatted value for readability.
 * The hidden input holds the raw numeric value for form submission.
 *
 * Usage:
 *   window.initCurrencyInput('base-income-display', 'base-income');
 *   window.initCurrencyInput('base-income-display', 'base-income', { min: 1, max: 9_000_000_000 });
 *
 * Options:
 *   min      {number}  Minimum allowed value (inclusive). Enforced on blur. Default: 0.
 *   max      {number}  Maximum allowed value (inclusive). Enforced on input + blur. Default: 9,000,000,000.
 *   errorId  {string}  ID of an element to show inline error messages in. Optional.
 *                      If omitted, errors are shown via a temporary tooltip on the input itself.
 *
 * @param {string} displayId
 * @param {string} hiddenId
 * @param {object} [options]
 */
export function initCurrencyInput(displayId, hiddenId, options = {}) {
    const display = document.getElementById(displayId);
    const hidden  = document.getElementById(hiddenId);

    if (!display || !hidden) return;

    const min = options.min ?? 0;
    const max = options.max ?? 9_000_000_000; // safe default — fits decimal(12,2)

    // ── Error display ─────────────────────────────────────────────────────────

    function showError(msg) {
        if (options.errorId) {
            const el = document.getElementById(options.errorId);
            if (el) {
                el.textContent = msg;
                el.classList.remove('hidden');
            }
            return;
        }

        // Fallback: inline tooltip pinned below the input
        let tooltip = display.parentElement.querySelector('.currency-error-tooltip');
        if (!tooltip) {
            tooltip = document.createElement('p');
            tooltip.className = 'currency-error-tooltip mt-1 text-xs text-red-600';
            display.parentElement.appendChild(tooltip);
        }
        tooltip.textContent = msg;
        tooltip.classList.remove('hidden');
        display.classList.add('border-red-500');
    }

    function clearError() {
        if (options.errorId) {
            const el = document.getElementById(options.errorId);
            if (el) {
                el.textContent = '';
                el.classList.add('hidden');
            }
        }
        const tooltip = display.parentElement.querySelector('.currency-error-tooltip');
        if (tooltip) tooltip.classList.add('hidden');
        display.classList.remove('border-red-500');
    }

    // ── Input handler ─────────────────────────────────────────────────────────

    display.addEventListener('input', () => {
        const selectionStart = display.selectionStart;
        const prevLength     = display.value.length;

        let raw = toRawNumber(display.value);
        let overMax = false;

        if (raw !== '' && parseFloat(raw) > max) {
            raw      = String(max);
            overMax  = true;
        }

        const formatted = formatWithCommas(raw);
        display.value   = formatted;
        hidden.value    = raw || '0';

        if (overMax) {
            showError(`Maximum amount is $${formatWithCommas(String(max))}.`);
        } else {
            clearError();
        }

        const diff = formatted.length - prevLength;
        display.setSelectionRange(selectionStart + diff, selectionStart + diff);
    });

    // ── Blur handler ──────────────────────────────────────────────────────────

    display.addEventListener('blur', () => {
        let raw = toRawNumber(display.value);
        raw = raw.replace(/\.$/, '');

        const num = parseFloat(raw);

        if (!isNaN(num)) {
            if (num > max) {
                showError(`Maximum amount is $${formatWithCommas(String(max))}.`);
                raw = String(max);
            } else if (num < min && display.value !== '') {
                showError(`Minimum amount is $${formatWithCommas(String(min))}.`);
                raw = String(min);
            } else {
                clearError();
            }
        } else {
            clearError();
        }

        display.value = formatWithCommas(raw);
        hidden.value  = raw || '0';
    });

    // Clear error as soon as the user starts correcting their input
    display.addEventListener('focus', clearError);
}