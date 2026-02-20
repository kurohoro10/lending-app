(() => {
    const form = document.getElementById('employment-form');
    const messagesContainer = document.getElementById('employment-messages');
    const submitButton = document.getElementById('submit-employment-button');
    const submitButtonText = document.getElementById('submit-employment-text');
    const employmentDetailsAccordionBtn = document.getElementById('employment-details-btn');

    if (employmentDetailsAccordionBtn) {
        employmentDetailsAccordionBtn.addEventListener('click', () => {
            toggleAccordion('employment-details');
        });
    }

    // Helper functions
    function clearErrors() {
        const errorElements = form.querySelectorAll('[id$="-error"]');
        errorElements.forEach(element => {
            element.classList.add('hidden');
            element.textContent = '';
        });

        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.classList.remove('border-red-500');
            input.removeAttribute('aria-invalid');
        });

        messagesContainer.innerHTML = '';
    }

    function displayFieldError(fieldName, message) {
        const errorElement = document.getElementById(`${fieldName}-error`);
        const inputElement = document.getElementById(fieldName) ||
                            document.getElementById(fieldName.replace(/_/g, '-'));

        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }

        if (inputElement) {
            inputElement.classList.add('border-red-500');
            inputElement.setAttribute('aria-invalid', 'true');
        }
    }

    function displaySuccess(message) {
        messagesContainer.innerHTML = `
            <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-semibold text-green-800">${message}</p>
                    </div>
                </div>
            </div>
        `;
        messagesContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function displayError(message) {
        messagesContainer.innerHTML = `
            <div class="p-4 bg-red-50 border-l-4 border-red-400 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-semibold text-red-800">${message}</p>
                    </div>
                </div>
            </div>
        `;
        messagesContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Handle form submission with Fetch API
    if (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            // Clear previous errors
            clearErrors();

            // Disable submit button and show loading state
            submitButton.disabled = true;
            const originalText = submitButtonText.textContent;
            submitButtonText.textContent = 'Adding...';

            try {
                // Get form data
                const formData = new FormData(form);

                // Send fetch request
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || formData.get('_token'),
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    // Success
                    displaySuccess(data.message || 'Employment details added successfully.');

                    // Reset form
                    form.reset();

                    // Add new employment to the list
                    if (data.employment) {
                        addEmploymentToList(data.employment);
                        updateEmploymentCount();

                        document.dispatchEvent(new CustomEvent('ajaxSuccess', {
                            detail: { type: 'employment' }
                        }));
                    }
                } else {
                    // Validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(fieldName => {
                            const messages = data.errors[fieldName];
                            if (Array.isArray(messages) && messages.length > 0) {
                                displayFieldError(fieldName, messages[0]);
                            }
                        });
                        displayError('Please correct the errors above.');
                    } else {
                        displayError(data.message || 'An error occurred. Please try again.');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                displayError('A network error occurred. Please check your connection and try again.');
            } finally {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButtonText.textContent = originalText;
            }
        });
    }

    // Function to add employment to the list dynamically
    function addEmploymentToList(employment) {
        const employmentList = document.getElementById('employment-list');
        const listContainer = document.getElementById('employment-list-container');

        // Create container if it doesn't exist
        if (!employmentList) {
            listContainer.innerHTML = `
                <div class="mb-6 space-y-3">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-gray-700">Your Employment History</h4>
                        <span id="employment-count-badge" class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            1 Employment(s)
                        </span>
                    </div>
                    <div id="employment-list" data-employment-section></div>
                </div>
            `;
        }

        const employmentItem = createEmploymentElement(employment);
        document.getElementById('employment-list').insertAdjacentHTML('beforeend', employmentItem);
    }

    // Function to create employment HTML element
    function createEmploymentElement(employment) {
        const annualIncome = calculateAnnualIncome(employment.base_income, employment.additional_income || 0, employment.income_frequency);

        return `
            <div data-employment-card class="employment-item p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200 hover:shadow-md transition" data-employment-id="${employment.id}">
                <div class="flex justify-between items-start">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold uppercase">
                                    ${employment.employment_type.replace(/_/g, ' ')}
                                </span>
                            </div>
                            <div class="font-semibold text-gray-900">${employment.employer_business_name || 'N/A'}</div>
                            <div class="text-sm text-gray-600 mt-1">${employment.position || 'N/A'}</div>
                            <div class="text-sm font-bold text-green-600 mt-2">
                                Annual Income: $${annualIncome.toLocaleString('en-AU', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                            </div>
                        </div>
                    </div>
                    <button type="button"
                            data-employment-id="${employment.id}"
                            aria-label="Delete employment record ${employment.employment_type.replace(/_/g, ' ')}"
                            class="inline-flex items-center px-3 py-2 bg-red-50 text-red-700 rounded-lg text-sm font-medium hover:bg-red-100 transition delete-employment-btn">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        `;
    }

    // Calculate annual income based on frequency
    function calculateAnnualIncome(baseIncome, additionalIncome, frequency) {
        const totalIncome = parseFloat(baseIncome) + parseFloat(additionalIncome);

        switch(frequency) {
            case 'weekly':
                return totalIncome * 52;
            case 'fortnightly':
                return totalIncome * 26;
            case 'monthly':
                return totalIncome * 12;
            case 'annual':
                return totalIncome;
            default:
                return totalIncome;
        }
    }

    // Update employment count badge
    function updateEmploymentCount() {
        const badge = document.getElementById('employment-count-badge');
        const count = document.querySelectorAll('.employment-item').length;
        if (badge) {
            badge.textContent = `${count} Employment(s)`;
        }
    }

    // Global delete function
    async function deleteEmployment(applicationId, employmentId) {
        if (!confirm('Are you sure you want to delete this employment record?')) {
            return;
        }

        const messagesContainer = document.getElementById('employment-messages');
        const deleteUrl = EMPLOYMENT_CONFIG.deleteRoute.replace(':id', employmentId);

        try {
            const response = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Remove the employment from DOM
                const employmentElement = document.querySelector(`[data-employment-id="${employmentId}"]`);
                if (employmentElement) {
                    employmentElement.remove();
                }

                updateEmploymentCount();

                document.dispatchEvent(new CustomEvent('ajaxSuccess', {
                    detail: { type: 'employment' }
                }));

                // Update count
                const badge = document.getElementById('employment-count-badge');
                const count = document.querySelectorAll('.employment-item').length;
                if (badge) {
                    badge.textContent = `${count} Employment(s)`;
                }

                // Show success message
                messagesContainer.innerHTML = `
                    <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-semibold text-green-800">${data.message || 'Employment details deleted successfully.'}</p>
                            </div>
                        </div>
                    </div>
                `;
                messagesContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                throw new Error(data.message || 'Failed to delete employment record');
            }
        } catch (error) {
            console.error('Error:', error);
            messagesContainer.innerHTML = `
                <div class="p-4 bg-red-50 border-l-4 border-red-400 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-red-800">${error.message}</p>
                        </div>
                    </div>
                </div>
            `;
            messagesContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    document.addEventListener('click', e => {
        const btn = e.target.closest('.delete-employment-btn');
        if (!btn) return;

        const employment_id = btn.dataset.employmentId;

        deleteEmployment(EMPLOYMENT_CONFIG.applicationId, employment_id);
    });
})();
