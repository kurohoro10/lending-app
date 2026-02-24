/**
 * documents.js
 * Handles document upload, validation, listing, and deletion
 */

(() => {
    const form = document.getElementById('document-form');
    if (!form) return;
    const fileInput = document.getElementById('file-upload');
    const fileUploadLabel = document.getElementById('file-upload-label');
    const uploadIcon = document.getElementById('upload-icon');
    const uploadText = document.getElementById('upload-text');
    const fileInfo = document.getElementById('file-info');
    const selectedFileInfo = document.getElementById('selected-file-info');
    const selectedFilename = document.getElementById('selected-filename');
    const selectedFilesize = document.getElementById('selected-filesize');
    const clearFileBtn = document.getElementById('clear-file-btn');
    const messagesContainer = document.getElementById('document-messages');
    const submitButton = document.getElementById('submit-document-button');
    const submitButtonText = document.getElementById('submit-document-text');
    const documentsAccordionBtn = document.getElementById('documents-btn');

    if (documentsAccordionBtn) {
        documentsAccordionBtn.addEventListener('click', () => {
            console.log('clicked');
            toggleAccordion('documents');
        });
    }

    const allowedTypes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    // Helper functions
    function clearErrors() {
        const errorElements = form.querySelectorAll('[id$="-error"]');
        errorElements.forEach(element => {
            element.classList.add('hidden');
            element.textContent = '';
        });

        const inputs = form.querySelectorAll('input, select, textarea');
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
            inputElement.setAttribute('aria-describedby', `${fieldName}-error`);
        }
    }

    function announceMessage(html) {
        messagesContainer.innerHTML = html;
        messagesContainer.focus();
        messagesContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }


    function displaySuccess(message) {
        announceMessage(`
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
        `);
    }

    function displayError(message) {
        announceMessage(`
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
        `);
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // File input change handler
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        submitButton.disabled = true;

        if (!file) return;

        if (!allowedTypes.includes(file.type)) {
            displayFieldError('document', 'Unsupported file type.');
            fileInput.value = '';
            submitButton.disabled = true;
            return;
        }

        // Validate file size (10MB max)
        if (file.size > APP_STATE.maxFileSize) {
            displayFieldError('document', 'File size must not exceed 10MB');
            fileInput.value = '';
            return;
        }

        submitButton.disabled = false;
        selectedFilename.textContent = file.name;
        selectedFilesize.textContent = formatFileSize(file.size);

        uploadIcon.classList.add('hidden');
        uploadText.classList.add('hidden');
        fileInfo.classList.add('hidden');
        selectedFileInfo.classList.remove('hidden');

        fileUploadLabel.classList.add('border-indigo-500', 'bg-indigo-50');
    });

    // Clear file button
    clearFileBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        fileInput.value = '';
        submitButton.disabled = true;

        uploadIcon.classList.remove('hidden');
        uploadText.classList.remove('hidden');
        fileInfo.classList.remove('hidden');
        selectedFileInfo.classList.add('hidden');

        fileUploadLabel.classList.remove('border-indigo-500', 'bg-indigo-50');
    });

    // Drag and drop handlers
    fileUploadLabel.addEventListener('dragover', function(e) {
        e.preventDefault();
        fileUploadLabel.classList.add('border-indigo-500', 'bg-indigo-50');
    });

    fileUploadLabel.addEventListener('dragleave', function(e) {
        e.preventDefault();
        if (!fileInput.files.length) {
            fileUploadLabel.classList.remove('border-indigo-500', 'bg-indigo-50');
        }
    });

    fileUploadLabel.addEventListener('drop', function(e) {
        e.preventDefault();
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });

    // Handle form submission with Fetch API
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        form.setAttribute('aria-busy', 'true');

        // Clear previous errors
        clearErrors();

        // Validate file is selected
        if (!fileInput.files.length) {
            displayFieldError('document', 'Please select a file to upload');
            form.removeAttribute('aria-busy');
            submitButton.disabled = false;
            return;
        }

        // Disable submit button and show loading state
        submitButton.disabled = true;
        const originalText = submitButtonText.textContent;
        submitButtonText.innerHTML = `
            <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Uploading...</span>
        `;

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
                displaySuccess(data.message || 'Document uploaded successfully.');

                // Reset form
                form.reset();

                // Reset file upload display
                uploadIcon.classList.remove('hidden');
                uploadText.classList.remove('hidden');
                fileInfo.classList.remove('hidden');
                selectedFileInfo.classList.add('hidden');
                fileUploadLabel.classList.remove('border-indigo-500', 'bg-indigo-50');

                // Add new document to the list
                if (data.document) {
                    addDocumentToList(data.document);
                    updateDocumentCount();
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
            form.removeAttribute('aria-busy');
        }
    });

    // Function to add document to the list dynamically
    function addDocumentToList(doc) {
        const documentsList = document.getElementById('documents-list');
        const listContainer = document.getElementById('documents-list-container');

        // Create container if it doesn't exist
        if (!documentsList) {
            listContainer.innerHTML = `
                <div class="mb-6 space-y-3">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-gray-700">Uploaded Documents</h4>
                        <span id="document-count-badge" class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            1 Document(s)
                        </span>
                    </div>
                    <div id="documents-list"></div>
                </div>
            `;
        }

        const documentItem = createDocumentElement(doc);
        document.getElementById('documents-list').insertAdjacentHTML('afterbegin', documentItem);
        updateDocumentCount();
    }

    // Function to create document HTML element
    function createDocumentElement(doc) {
        const date = new Date(doc.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        const downloadUrl = APP_STATE.routes.download.replace(':id', doc.id);

        return `
            <div class="document-item flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200 hover:shadow-md transition" data-document-id="${doc.id}">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-900">${doc.original_filename}</div>
                        <div class="flex items-center space-x-2 text-xs text-gray-500 mt-1">
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-full font-medium">${doc.document_category.charAt(0).toUpperCase() + doc.document_category.slice(1)}</span>
                            <span>•</span>
                            <span>${formatFileSize(doc.file_size)}</span>
                            <span>•</span>
                            <span>${date}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="${downloadUrl}" class="inline-flex items-center px-3 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        Download
                    </a>
                    <button type="button"
                            data-document-id="${doc.id}"
                            aria-label="Delete document ${doc.original_filename}"
                            class="inline-flex items-center px-3 py-2 bg-red-50 text-red-700 rounded-lg text-sm font-medium hover:bg-red-100 transition delete-document-btn">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        `;
    }

    // Update document count badge
    function updateDocumentCount() {
        const badge = document.getElementById('document-count-badge');
        const count = document.querySelectorAll('.document-item').length;
        if (badge) {
            badge.textContent = `${count} Document(s)`;
        }
    }

    // Global delete function
    async function deleteDocument(applicationId, documentId) {
        if (!window.confirm('Are you sure you want to delete this document?')) {
            return;
        }

        announceMessage('<p class="sr-only">Deleting document</p>');

        const messagesContainer = document.getElementById('document-messages');
        const deleteUrl = APP_STATE.routes['delete'].replace(':id', documentId);

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
                // Remove the document from DOM
                const documentElement = document.querySelector(`[data-document-id="${documentId}"]`);
                if (documentElement) {
                    documentElement.remove();
                }

                // Update count
                const badge = document.getElementById('document-count-badge');
                const count = document.querySelectorAll('.document-item').length;
                if (badge) {
                    badge.textContent = `${count} Document(s)`;
                }

                // Show success message
                announceMessage(`
                    <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-semibold text-green-800">${data.message || 'Document deleted successfully.'}</p>
                            </div>
                        </div>
                    </div>
                `);
            } else {
                throw new Error(data.message || 'Failed to delete document');
            }
        } catch (error) {
            console.error('Error:', error);
            announceMessage(`
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
            `);
        }
    }

    document.addEventListener('click', e => {
        const btn = e.target.closest('.delete-document-btn');
        if (!btn) return;

        const documentId = btn.dataset.documentId;
        deleteDocument(APP_STATE.applicationId, documentId);
    });
})();
