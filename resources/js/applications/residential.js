(() => {
    const form = document.getElementById('residential-address-form');
    const typeSelect = document.getElementById('address-type-select');
    const startDateInput = document.getElementById('start-date-input');
    const endDateInput = document.getElementById('end-date-input');
    const messagesContainer = document.getElementById('address-messages');
    const submitButton = document.getElementById('submit-address-button');
    const submitButtonText = document.getElementById('submit-address-text');
    const suburbSelect = document.getElementById('suburb-selector');
    const manualInput = document.getElementById('suburb-manual');

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
        const inputElement = document.getElementById(`${fieldName}-input`) ||
                            document.getElementById(`${fieldName}-selector`) ||
                            document.getElementById(`${fieldName}-select`);

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

    // Client-side validation
    function validateDateRange() {
        if (!startDateInput.value) return true;

        const type = typeSelect.value;
        const start = new Date(startDateInput.value);
        const end = endDateInput.value ? new Date(endDateInput.value) : new Date();

        const requirements = {
            'previous_1': 1,
            'previous_2': 2,
            'previous_3': 3,
            'current': 0
        };

        const requiredYears = requirements[type] || 0;

        if (requiredYears > 0) {
            let diffInMs = end - start;
            let diffInYears = diffInMs / (1000 * 60 * 60 * 24 * 365.25);

            if (diffInYears < requiredYears) {
                const message = `For ${type.replace(/_/g, ' ')}, the duration must be at least ${requiredYears} year(s). Current: ${diffInYears.toFixed(1)} years.`;
                displayError(message);
                return false;
            }
        }

        return true;
    }

    // Suburb dropdown functionality
    window.updateSuburbs = function(state) {
        if (!state) {
            suburbSelect.disabled = true;
            suburbSelect.innerHTML = '<option value="">Select state first...</option>';
            return;
        }

        suburbSelect.disabled = false;
        suburbSelect.innerHTML = '<option value="">Select suburb...</option>';

        const suburbs = RESIDENTIAL_CONFIG.allSuburbs[state] || [];
        suburbs.forEach(suburb => {
            const option = document.createElement('option');
            option.value = suburb;
            option.textContent = suburb;
            suburbSelect.appendChild(option);
        });
    };

    // Manual suburb entry logic
    manualInput?.addEventListener('input', function(e) {
        if (e.target.value) {
            suburbSelect.value = '';
            let hiddenInput = document.querySelector('input[name="suburb"][type="hidden"]');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'suburb';
                e.target.parentNode.appendChild(hiddenInput);
            }
            hiddenInput.value = e.target.value;
            suburbSelect.removeAttribute('required');
        } else {
            suburbSelect.setAttribute('required', 'required');
            document.querySelector('input[name="suburb"][type="hidden"]')?.remove();
        }
    });

    // Clear manual when dropdown selected
    suburbSelect?.addEventListener('change', function(e) {
        if (e.target.value) {
            manualInput.value = '';
            document.querySelector('input[name="suburb"][type="hidden"]')?.remove();
        }
    });

    // Handle form submission with Fetch API
    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        // Clear previous errors
        clearErrors();

        // Validate date range
        if (!validateDateRange()) {
            return;
        }

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
                displaySuccess(data.message || 'Address added successfully.');

                // Reset form
                form.reset();
                suburbSelect.disabled = true;
                suburbSelect.innerHTML = '<option value="">Select state first...</option>';

                // Add new address to the list
                if (data.address) {
                    addAddressToList(data.address);
                    updateAddressCount();
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

    // Function to add address to the list dynamically
    function addAddressToList(address) {
        const addressList = document.getElementById('address-list');
        const listContainer = document.getElementById('address-list-container');

        // Create container if it doesn't exist
        if (!addressList) {
            listContainer.innerHTML = `
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-gray-900">Your Address History</h4>
                        <span id="address-count-badge" class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            1 Address(es)
                        </span>
                    </div>
                    <div id="address-list" class="space-y-3"></div>
                </div>
            `;
        }

        const addressItem = createAddressElement(address);
        document.getElementById('address-list').insertAdjacentHTML('beforeend', addressItem);
    }

    // Function to create address HTML element
    function createAddressElement(address) {
        const startDate = address.start_date ? new Date(address.start_date).toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) : 'N/A';
        const endDate = address.end_date ? new Date(address.end_date).toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) : 'Present';

        return `
            <div class="address-item p-5 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border-2 border-gray-200 hover:shadow-lg hover:border-indigo-200 transition-all" data-address-id="${address.id}">
                <div class="flex justify-between items-start">
                    <div class="flex items-start space-x-4 flex-1">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center flex-wrap gap-2 mb-2">
                                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold">
                                    ${address.address_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </span>
                                <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold">
                                    ${(address.residential_status || 'N/A').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </span>
                                <span class="text-xs text-gray-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    ${startDate} - ${endDate}
                                </span>
                            </div>
                            <div class="text-sm font-semibold text-gray-900 mb-1">
                                ${address.street_address || 'N/A'}
                            </div>
                            <div class="text-sm text-gray-600">
                                ${address.suburb || 'N/A'}, ${address.state || 'N/A'} ${address.postcode || 'N/A'}
                            </div>
                        </div>
                    </div>
                    <button type="button"
                            data-address-id="${address.id}"
                            class="ml-4 inline-flex items-center px-4 py-2 bg-red-50 text-red-700 rounded-xl text-sm font-semibold hover:bg-red-100 transition-all hover:shadow-md delete-address-btn">
                        <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        `;
    }

    // Update address count badge
    function updateAddressCount() {
        const badge = document.getElementById('address-count-badge');
        const count = document.querySelectorAll('.address-item').length;
        if (badge) {
            badge.textContent = `${count} Address(es)`;
        }
    }

    // Global delete function
    async function deleteAddress(applicationId, addressId) {
        if (!confirm('Are you sure you want to delete this address?')) {
            return;
        }

        const messagesContainer = document.getElementById('address-messages');
        const deleteUrl = RESIDENTIAL_CONFIG.deleteRoute.replace(':id', addressId);

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
                // Remove the address from DOM
                const addressElement = document.querySelector(`[data-address-id="${addressId}"]`);
                if (addressElement) {
                    addressElement.remove();
                }

                // Update count
                const badge = document.getElementById('address-count-badge');
                const count = document.querySelectorAll('.address-item').length;
                if (badge) {
                    badge.textContent = `${count} Address(es)`;
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
                                <p class="text-sm font-semibold text-green-800">${data.message || 'Address deleted successfully.'}</p>
                            </div>
                        </div>
                    </div>
                `;
                messagesContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                throw new Error(data.message || 'Failed to delete address');
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
        const btn = e.target.closest('.delete-address-btn');
        if (!btn) return;

        const addressId = btn.dataset.addressId;
        deleteAddress(RESIDENTIAL_CONFIG.applicationId, addressId);
    });
})();
