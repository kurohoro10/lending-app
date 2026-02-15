/**
 * File: resources/js/applications/application.edit.js
 * Purpose: Application edit page behavior with client-side progress tracking
 */

(() => {
    const state = window.APP_STATE || {
        progress: {
            loanDetails: true,
            personalDetails: false,
            addresses: false,
            employment: false,
            expenses: false
        },
        legalAge: 18
    };

    const els = {
        submitBtn: document.getElementById('submit-application-btn'),
        dob: document.getElementById('date_of_birth'),
        employmentDates: document.querySelectorAll('input[name="employment_start_date"]'),
        signature: document.getElementById('signature-data'),
        agreement: document.getElementById('signature-agreement'),
        progressSteps: {
            loanDetails: document.getElementById('step-loan-details'),
            personal: document.getElementById('step-personal'),
            addresses: document.getElementById('step-addresses'),
            employment: document.getElementById('step-employment'),
            expenses: document.querySelector('[data-step="expenses"]')
        }
    };

    // Progress tracking functions
    function calculateProgress() {
        const p = state.progress;
        const completed = [
            p.loanDetails,
            p.personalDetails,
            p.addresses,
            p.employment,
            p.expenses
        ].filter(Boolean).length;

        return {
            completed,
            total: 5,
            percentage: Math.round((completed / 5) * 100)
        };
    }

    function updateStepVisual(stepName, isComplete) {
        const stepEl = els.progressSteps[stepName];
        if (!stepEl) return;

        const circle = stepEl.querySelector('div');
        const label = stepEl.querySelector('span');

        if (isComplete) {
            // Complete state
            circle.className = 'rounded-full h-14 w-14 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold border-4 border-white shadow-lg';
            circle.innerHTML = `
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            `;
            label.className = 'text-xs font-semibold text-indigo-600 mt-3 text-center';
        } else {
            // Incomplete state
            const stepNumbers = {
                loanDetails: '1',
                personal: '2',
                addresses: '3',
                employment: '4',
                expenses: '5'
            };
            circle.className = 'rounded-full h-14 w-14 flex items-center justify-center bg-white text-gray-400 font-bold border-4 border-gray-300 shadow';
            circle.textContent = stepNumbers[stepName] || '';
            label.className = 'text-xs font-medium text-gray-500 mt-3 text-center';
        }

        // Update accessibility
        stepEl.setAttribute('aria-current', isComplete ? 'step' : 'false');
    }

    function updateConnectorLine(index, isComplete) {
        const connectors = document.querySelectorAll('.flex-1.h-1.mx-2.rounded');
        if (connectors[index]) {
            if (isComplete) {
                connectors[index].className = 'flex-1 h-1 mx-2 rounded bg-gradient-to-r from-indigo-600 to-purple-600 transition-all duration-500';
            } else {
                connectors[index].className = 'flex-1 h-1 mx-2 rounded bg-gray-300 transition-all duration-500';
            }
        }
    }

    function renderProgress() {
        const progress = calculateProgress();

        // Update progress bar
        const bar = document.querySelector('.bg-gradient-to-r.from-indigo-600.to-purple-600.h-3');
        if (bar) {
            bar.style.width = progress.percentage + '%';
            bar.setAttribute('aria-valuenow', progress.percentage);
        }

        // Update progress text
        const completedText = document.querySelector('.text-sm.font-semibold.text-gray-700');
        const percentText = document.querySelector('.text-sm.font-bold.text-indigo-600');
        
        if (completedText) {
            completedText.textContent = `${progress.completed} of ${progress.total} sections completed`;
        }
        if (percentText) {
            percentText.textContent = `${progress.percentage}%`;
        }

        // Update step visuals
        updateStepVisual('loanDetails', state.progress.loanDetails);
        updateStepVisual('personal', state.progress.personalDetails);
        updateStepVisual('addresses', state.progress.addresses);
        updateStepVisual('employment', state.progress.employment);
        updateStepVisual('expenses', state.progress.expenses);

        // Update connector lines
        updateConnectorLine(0, state.progress.personalDetails);
        updateConnectorLine(1, state.progress.addresses);
        updateConnectorLine(2, state.progress.employment);
        updateConnectorLine(3, state.progress.expenses);

        // Announce to screen readers
        announceProgress(progress);
    }

    function announceProgress(progress) {
        const announcer = document.getElementById('progress-announcer') || createAnnouncer();
        announcer.textContent = `Application progress updated: ${progress.completed} of ${progress.total} sections completed, ${progress.percentage}% complete`;
    }

    function createAnnouncer() {
        const announcer = document.createElement('div');
        announcer.id = 'progress-announcer';
        announcer.className = 'sr-only';
        announcer.setAttribute('role', 'status');
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        document.body.appendChild(announcer);
        return announcer;
    }

    // Check progress state based on form data
    function checkPersonalDetailsComplete() {
        const fields = [
            'full_name', 'mobile_phone', 'email', 'marital_status',
            'number_of_dependants', 'date_of_birth', 'citizenship_status'
        ];
        return fields.every(field => {
            const input = document.getElementById(field) || document.querySelector(`[name="${field}"]`);
            return input && input.value.trim() !== '';
        });
    }

    function checkAddressesComplete() {
        const addressCards = document.querySelectorAll('[data-address-card]');
        return addressCards.length > 0;
    }

    function checkEmploymentComplete() {
        const employmentCards = document.querySelectorAll('[data-employment-card]');
        return employmentCards.length > 0;
    }

    function checkExpensesComplete() {
        const expenseRows = document.querySelectorAll('[data-expense-row]');
        return expenseRows.length > 0;
    }

    function updateProgressState() {
        state.progress.personalDetails = checkPersonalDetailsComplete();
        state.progress.addresses = checkAddressesComplete();
        state.progress.employment = checkEmploymentComplete();
        state.progress.expenses = checkExpensesComplete();

        renderProgress();
        updateSubmitState();
    }

    // Submit button state management
    function updateSubmitState() {
        if (!els.submitBtn) return;

        const allSectionsComplete = Object.values(state.progress).every(Boolean);
        const signatureValid = els.signature?.value?.trim() && els.agreement?.checked;

        const valid = allSectionsComplete && signatureValid;

        els.submitBtn.disabled = !valid;
        els.submitBtn.classList.toggle('opacity-50', !valid);
        els.submitBtn.classList.toggle('cursor-not-allowed', !valid);
        
        if (valid) {
            els.submitBtn.classList.add('hover:shadow-lg', 'hover:scale-105');
        } else {
            els.submitBtn.classList.remove('hover:shadow-lg', 'hover:scale-105');
        }
    }

    // Employment date validation
    function legalAgeDate(dob, years = 18) {
        const d = new Date(dob);
        d.setFullYear(d.getFullYear() + years);
        return d;
    }

    function attachEmploymentValidation() {
        if (!els.dob) return;

        els.employmentDates.forEach(input => {
            input.addEventListener('input', () => {
                if (!els.dob.value || !input.value) return;

                const legal = legalAgeDate(els.dob.value, state.legalAge);
                const start = new Date(input.value);
                const hint = getHint(input);

                if (start < legal) {
                    hint.textContent = `Employment history will be counted from ${legal.toLocaleDateString()}.`;
                    hint.hidden = false;
                } else {
                    hint.hidden = true;
                }
            });
        });
    }

    function getHint(input) {
        let hint = input.nextElementSibling;
        if (!hint || !hint.classList.contains('employment-hint')) {
            hint = document.createElement('p');
            hint.className = 'employment-hint text-xs text-red-600 mt-1';
            hint.setAttribute('aria-live', 'polite');
            input.after(hint);
        }
        return hint;
    }

    // Listen for dynamic content changes (addresses, employment, expenses)
    function observeFormChanges() {
        const observer = new MutationObserver(() => {
            debouncedUpdate();
        });

        const sectionsToObserve = [
            document.querySelector('[data-addresses-section]'),
            document.querySelector('[data-employment-section]'),
            document.querySelector('[data-expenses-section]')
        ];

        sectionsToObserve.forEach(section => {
            if (section) {
                observer.observe(section, {
                    childList: true,
                    subtree: true
                });
            }
        });

        return observer;
    }

    // Listen for form input changes in personal details
    function attachPersonalDetailsListeners() {
        const personalForm = document.querySelector('form[action*="personal-details"]');
        if (!personalForm) return;

        const inputs = personalForm.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', debouncedUpdate);
            input.addEventListener('change', debouncedUpdate);
        });
    }

    let updateTimeout;
    function debouncedUpdate() {
        clearTimeout(updateTimeout);
        updateTimeout = setTimeout(() => {
            updateProgressState();
        }, 100);
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        // Initial render
        updateProgressState();
        attachEmploymentValidation();
        attachPersonalDetailsListeners();
        
        // Watch for dynamic changes
        observeFormChanges();

        // Listen for signature changes
        els.signature?.addEventListener('input', updateSubmitState);
        els.agreement?.addEventListener('change', updateSubmitState);

        // Listen for successful AJAX form submissions
        document.addEventListener('ajaxSuccess', (e) => {
            if (e.detail?.type === 'address' || 
                e.detail?.type === 'employment' || 
                e.detail?.type === 'expense') {
                debouncedUpdate();
            }
        });
    });

    // Expose update function for external use
    window.updateApplicationProgress = updateProgressState;
})();