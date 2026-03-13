/**
 * File: resources/js/applications/show/application.edit.js
 * Purpose: Application edit page behavior with client-side progress tracking
 *          and dynamic submit section rendering - ENHANCED BUTTON VERSION
 */

(() => {
    const state = window.APP_STATE ?? {};

    state.progress = state.progress ?? {
        loanDetails: true,
        personalDetails: false,
        addresses: false,
        employment: false,
        expenses: false
    };

    state.legalAge = state.legalAge ?? 18;

    const els = {
        submitBtn: document.getElementById('submit-application-btn'),
        dob: document.getElementById('date_of_birth'),
        employmentDates: document.querySelectorAll('input[name="employment_start_date"]'),
        signature: document.getElementById('signature-data'),
        agreement: document.getElementById('signature-agreement'),
        submitContainer: document.getElementById('submit-application-container'),
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
            circle.className = 'rounded-full h-14 w-14 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold border-4 border-white shadow-lg';
            circle.innerHTML = `
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            `;
            label.className = 'text-xs font-semibold text-indigo-600 mt-3 text-center';
        } else {
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

        const bar = document.querySelector('.bg-gradient-to-r.from-indigo-600.to-purple-600.h-3');
        if (bar) {
            bar.style.width = progress.percentage + '%';
            bar.setAttribute('aria-valuenow', progress.percentage);
        }

        // Use specific class instead of generic Tailwind classes
        const completedText = document.querySelector('.progress-steps-label');
        const percentText = document.querySelector('.progress-steps-percent');

        if (completedText) {
            completedText.textContent = `${progress.completed} of ${progress.total} sections completed`;
        }
        if (percentText) {
            percentText.textContent = `${progress.percentage}%`;
        }

        updateStepVisual('loanDetails', state.progress.loanDetails);
        updateStepVisual('personal', state.progress.personalDetails);
        updateStepVisual('addresses', state.progress.addresses);
        updateStepVisual('employment', state.progress.employment);
        updateStepVisual('expenses', state.progress.expenses);

        updateConnectorLine(0, state.progress.personalDetails);
        updateConnectorLine(1, state.progress.addresses);
        updateConnectorLine(2, state.progress.employment);
        updateConnectorLine(3, state.progress.expenses);

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

    function checkPersonalDetailsComplete() {
        const fields = [
            'mobile_phone', 'marital_status',
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
        const expenseRows = document.querySelectorAll('[data-expense-row], .expense-input-row');

        let hasSavedExpenses = false;

        expenseRows.forEach(row => {
            const amountInput = row.querySelector('.expense-amount-input');
            if (amountInput && parseFloat(amountInput.value || 0) > 0) {
                hasSavedExpenses = true;
            }
        });

        const otherRows = document.querySelectorAll('[data-other-row]');
        otherRows.forEach(row => {
            const nameInput = row.querySelector('input[type="text"]');
            const amountInput = row.querySelector('.expense-amount-input');
            if (nameInput?.value.trim() && amountInput && parseFloat(amountInput.value || 0) > 0) {
                hasSavedExpenses = true;
            }
        });

        return hasSavedExpenses;
    }

    // Add a flag so we only re-check expenses after a save, not on DOM init
    let expensesInitialized = false;

    function updateProgressState() {
        state.progress.personalDetails = checkPersonalDetailsComplete();
        state.progress.addresses       = checkAddressesComplete();
        state.progress.employment      = checkEmploymentComplete();

        // Only re-evaluate expenses after an AJAX save — on initial load,
        // trust the server-provided value in APP_STATE
        if (expensesInitialized) {
            state.progress.expenses = checkExpensesComplete();
        }

        renderProgress();
        renderSubmitSection();
    }

    function canBeSubmitted() {
        return Object.values(state.progress).every(Boolean);
    }

    function getMissingRequirements() {
        const missing = [];

        if (!state.progress.personalDetails) {
            missing.push({
                text: 'Personal Details (Complete all required fields)',
                icon: 'cross'
            });
        }
        if (!state.progress.addresses) {
            missing.push({
                text: 'At least one Residential Address',
                icon: 'cross'
            });
        }
        if (!state.progress.employment) {
            missing.push({
                text: 'Employment Details',
                icon: 'cross'
            });
        }
        if (!state.progress.expenses) {
            missing.push({
                text: 'Living Expenses (Enter at least one expense)',
                icon: 'cross'
            });
        }

        return missing;
    }

    function renderSubmitSection() {
        if (!els.submitContainer) return;

        const canSubmit = canBeSubmitted();
        const existingContent = els.submitContainer.firstElementChild;

        if (existingContent) {
            existingContent.classList.add('fade-out');
            setTimeout(() => {
                els.submitContainer.innerHTML = canSubmit
                    ? renderReadyToSubmit()
                    : renderIncomplete();

                const newContent = els.submitContainer.firstElementChild;
                if (newContent) {
                    newContent.classList.add('fade-in');
                }

                attachSubmitButtonListener();
                announceSubmitStatus(canSubmit);
            }, 300);

        } else {
            els.submitContainer.innerHTML = canSubmit ?
                renderReadyToSubmit() :
                renderIncomplete();

            attachSubmitButtonListener();
        }
    }

    function renderReadyToSubmit() {
        return `
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 overflow-hidden shadow-xl sm:rounded-2xl border-2 border-green-200" role="region" aria-label="Submit application">
                <div class="p-8">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-16 w-16 rounded-full bg-green-100" aria-hidden="true">
                                <svg class="h-8 w-8 text-green-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-6 flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Ready to Submit!</h3>
                            <p class="text-gray-700 mb-6">
                                Your application is complete and ready to submit. Once submitted, our team will review your application and contact you if additional information is needed.
                            </p>
                            <div class="bg-white rounded-xl p-4 mb-8 border border-green-200">
                                <h4 class="font-semibold text-gray-900 mb-3">What happens next?</h4>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>Our team will review your application within 24-48 hours</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>You'll receive an email confirmation immediately</span>
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span>We'll contact you if we need any additional information</span>
                                    </li>
                                </ul>
                            </div>
                            <button type="submit"
                                    id="submit-application-btn"
                                    class="inline-flex items-center justify-center px-10 py-5 text-white rounded-xl font-bold text-lg uppercase tracking-wide transition-all duration-300 transform focus:outline-none focus:ring-4 focus:ring-green-400 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed w-full"
                                    disabled
                                    aria-label="Submit application for review">
                                <!-- Spinner: shown while submitting -->
                                <svg id="submit-application-spinner"
                                    class="hidden animate-spin w-6 h-6 mr-3 flex-shrink-0"
                                    fill="none" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <!-- Arrow icon: hidden while submitting -->
                                <svg id="submit-application-arrow"
                                    class="w-6 h-6 mr-3 flex-shrink-0"
                                    fill="currentColor" viewBox="0 0 20 20"
                                    aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                                </svg>
                                <span id="submit-application-label">Submit Application for Review</span>
                            </button>
                            <p class="mt-4 text-sm text-center" id="submit-status-text">
                                <span class="font-semibold text-yellow-700">⚠️ Complete signature below to enable submit button</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function renderIncomplete() {
        const missing = getMissingRequirements();
        const missingHTML = missing.map(item => `
            <li class="flex items-center text-gray-700">
                <svg class="w-5 h-5 text-yellow-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>${item.text}</span>
            </li>
        `).join('');

        return `
            <div class="bg-gradient-to-br from-yellow-50 to-amber-50 border-l-4 border-yellow-400 rounded-xl p-6 shadow-lg" role="region" aria-label="Application incomplete">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-bold text-yellow-800 mb-2">Complete Required Sections</h3>
                        <p class="text-sm text-yellow-700 mb-4">
                            Please complete all required sections before submitting your application.
                        </p>
                        <div class="bg-white rounded-lg p-4 border border-yellow-200">
                            <h4 class="font-semibold text-gray-900 mb-3 text-sm">Still needed:</h4>
                            <ul class="space-y-2 text-sm">
                                ${missingHTML}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function announceSubmitStatus(canSubmit) {
        const announcer = document.getElementById('submit-announcer') || createSubmitAnnouncer();
        if (canSubmit) {
            announcer.textContent = 'Application is now ready to submit. All required sections are complete.';
        } else {
            const missing = getMissingRequirements();
            announcer.textContent = `Application cannot be submitted yet. Please complete: ${missing.map(m => m.text).join(', ')}`;
        }
    }

    function createSubmitAnnouncer() {
        const announcer = document.createElement('div');
        announcer.id = 'submit-announcer';
        announcer.className = 'sr-only';
        announcer.setAttribute('role', 'status');
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        document.body.appendChild(announcer);
        return announcer;
    }

    function updateSubmitState() {
        const submitBtn = document.getElementById('submit-application-btn');
        const statusText = document.getElementById('submit-status-text');
        if (!submitBtn) return;

        const signatureValid = els.signature?.value?.trim() && els.agreement?.checked;
        const valid = canBeSubmitted() && signatureValid;

        submitBtn.disabled = !valid;

        if (valid) {
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            if (statusText) {
                statusText.innerHTML = `<span class="font-bold text-green-700">✓ Ready to submit!</span>`;
            }
        } else {
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            if (statusText) {
                statusText.innerHTML = `<span class="font-semibold text-yellow-700">⚠️ Complete signature below to enable submit button</span>`;
            }
        }
    }

    function attachSubmitButtonListener() {
        const signatureInput    = document.getElementById('signature-data');
        const agreementCheckbox = document.getElementById('signature-agreement');

        if (signatureInput) {
            signatureInput.removeEventListener('input', updateSubmitState);
            signatureInput.addEventListener('input', updateSubmitState);
        }

        if (agreementCheckbox) {
            agreementCheckbox.removeEventListener('change', updateSubmitState);
            agreementCheckbox.addEventListener('change', updateSubmitState);
        }

        // ── Loading state on final submit ─────────────────────────────────
        const form = document.querySelector('form[action*="submit"]');
        if (form) {
            form.removeEventListener('submit', handleFinalSubmit);
            form.addEventListener('submit', handleFinalSubmit);
        }

        updateSubmitState();
    }

    function handleFinalSubmit() {
        const btn     = document.getElementById('submit-application-btn');
        const spinner = document.getElementById('submit-application-spinner');
        const arrow   = document.getElementById('submit-application-arrow');
        const label   = document.getElementById('submit-application-label');

        if (!btn) return;

        btn.disabled = true;
        btn.setAttribute('aria-disabled', 'true');
        spinner?.classList.remove('hidden');
        arrow?.classList.add('hidden');
        if (label) label.textContent = 'Submitting…';
    }

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

    document.addEventListener('DOMContentLoaded', () => {
        renderProgress();           // First render uses server APP_STATE as-is
        renderSubmitSection();

        // Now safe to start watching personal details fields
        attachEmploymentValidation();
        attachPersonalDetailsListeners();
        attachSubmitButtonListener();
        observeFormChanges();

        // Mark expenses as "live" only after a successful expense save
        document.addEventListener('ajaxSuccess', (e) => {
            if (e.detail?.type === 'expense') {
                expensesInitialized = true;
            }
            if (e.detail?.type === 'address'    ||
                e.detail?.type === 'employment' ||
                e.detail?.type === 'expense') {
                debouncedUpdate();
            }
        });

        // Delayed flag: after a short tick let the currency inputs hydrate,
        // then allow client-side expense checks on subsequent updates
        setTimeout(() => { expensesInitialized = true; }, 200);
    });

    window.updateApplicationProgress = updateProgressState;
})();
