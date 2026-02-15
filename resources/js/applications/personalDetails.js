(() => {
    const personalDetailsAccordionBtn = document.getElementById('personal-details-btn');
    const form = document.getElementById('personal-details');
    const dobInput = document.getElementById('date_of_birth');
    const submitButton = document.getElementById('submit-button');
    const submitButtonText = document.getElementById('submit-button-text');
    const messagesContainer = document.getElementById('form-messages');

    personalDetailsAccordionBtn.addEventListener('click', () => {
        toggleAccordion('personal-details');
    });

    // Helper function to clear all error messages
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

    // Helper function to display field errors
    function displayFieldError(fieldName, message) {
        const errorElement = document.getElementById(`${fieldName}-error`);
        const inputElement = document.getElementById(fieldName);

        if (errorElement && inputElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
            inputElement.classList.add('border-red-500');
            inputElement.setAttribute('aria-invalid', 'true');
        }
    }

    // Helper function to display success message
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

    // Helper function to display general error message
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

    // Client-side age validation
    function validateAge() {
        if (!dobInput.value) {
            return true; // Let server handle if it's required
        }

        const birthDate = new Date(dobInput.value);
        const today = new Date();

        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        if (age < 18) {
            displayFieldError('date_of_birth', 'You must be at least 18 years old to apply.');
            dobInput.focus();
            return false;
        }

        return true;
    }

    // Clear errors when user types
    dobInput.addEventListener('input', function () {
        const errorElement = document.getElementById('date_of_birth-error');
        errorElement.classList.add('hidden');
        dobInput.classList.remove('border-red-500');
        dobInput.removeAttribute('aria-invalid');
    });

    // Handle form submission with Fetch API
    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        // Clear previous errors
        clearErrors();

        // Validate age
        if (!validateAge()) {
            return;
        }

        // Disable submit button and show loading state
        submitButton.disabled = true;
        const originalText = submitButtonText.textContent;
        submitButtonText.textContent = 'Saving...';

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
                displaySuccess(data.message || 'Personal details saved successfully.');

                // Optional: Update button text if it's a create -> update scenario
                if (originalText.includes('Save')) {
                    submitButtonText.textContent = 'Update Personal Details';
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
                    displayError('Please correct the errors below.');
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
})();
