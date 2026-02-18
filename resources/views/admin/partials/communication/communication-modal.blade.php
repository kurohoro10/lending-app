<!-- Reusable Communication Modal -->
<div x-data="communicationModal()" x-init="init()">
    <!-- Dropdown Button -->
    <div class="relative inline-block">
        <button @click="toggleDropdown"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Contact Client
            <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div x-show="dropdownOpen"
             x-cloak
             @click.away="dropdownOpen = false"
             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
            <button @click="openModal('email')"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 flex items-center">
                <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Send Email
            </button>
            <button @click="openModal('sms')"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-green-50 flex items-center border-t border-gray-100">
                <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-5 5v-5z"/>
                </svg>
                Send SMS/WhatsApp
            </button>
        </div>
    </div>

    <!-- Modal -->
    <div x-show="modalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
             @click="closeModal"></div>

        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-xl shadow-2xl max-w-2xl w-full"
                 @click.stop>

                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200"
                     :class="modalType === 'email' ? 'bg-indigo-50' : 'bg-green-50'">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center mr-3"
                                 :class="modalType === 'email' ? 'bg-indigo-100' : 'bg-green-100'">
                                <svg x-show="modalType === 'email'" class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <svg x-show="modalType === 'sms'" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-4l-5 5v-5z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900" x-text="modalType === 'email' ? 'Send Email' : 'Send SMS/WhatsApp'"></h3>
                                <p class="text-sm text-gray-600">{{ $application->user->name }} • {{ $application->application_number }}</p>
                            </div>
                        </div>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Toast -->
                <div x-show="toast.show"
                     x-transition
                     class="mx-6 mt-4 p-3 rounded-lg text-sm"
                     :class="toast.type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'"
                     x-text="toast.message"></div>

                <!-- Body -->
                <div class="p-6">
                    <!-- Template Selector -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Choose Template
                        </label>
                        <select x-model="selectedTemplate"
                                @change="loadTemplate"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Select a template --</option>
                            <template x-for="(template, key) in templates" :key="key">
                                <option :value="key" x-text="template.label"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Email Subject (Email only) -->
                    <div x-show="modalType === 'email'" class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Subject <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               x-model="formData.subject"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Enter email subject...">
                    </div>

                    <!-- Message Body -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Message <span class="text-red-500">*</span>
                            </label>
                            <span class="text-xs text-gray-500"
                                  x-text="formData.message.length + ' / ' + (modalType === 'email' ? '5000' : '1000')"></span>
                        </div>
                        <textarea x-model="formData.message"
                                  :rows="modalType === 'email' ? 12 : 6"
                                  :maxlength="modalType === 'email' ? 5000 : 1000"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono"
                                  :placeholder="modalType === 'email' ? 'Type your email message here...' : 'Type your SMS message here (max 1000 characters)...'"></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            <span x-show="modalType === 'email'">You can format with line breaks. Plain text only.</span>
                            <span x-show="modalType === 'sms'">Keep it concise for SMS delivery.</span>
                        </p>
                    </div>

                    <!-- Recipient Info -->
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-sm">
                        <div class="flex items-center text-gray-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-show="modalType === 'email'">Will be sent to: <strong>{{ $application->user->email }}</strong></span>
                            <span x-show="modalType === 'sms'">Will be sent to: <strong>{{ $application->personalDetails?->mobile_phone ?? 'No phone number' }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                    <button @click="closeModal"
                            type="button"
                            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button @click="sendCommunication"
                            type="button"
                            :disabled="sending || !isValid()"
                            :class="modalType === 'email' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-green-600 hover:bg-green-700'"
                            class="px-4 py-2 text-white rounded-md text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                        <span x-show="!sending" x-text="modalType === 'email' ? 'Send Email' : 'Send SMS'"></span>
                        <span x-show="sending" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Sending...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function communicationModal() {
        return {
            dropdownOpen: false,
            modalOpen: false,
            modalType: 'email',
            templates: {},
            selectedTemplate: '',
            formData: {
                subject: '',
                message: '',
            },
            sending: false,
            toast: {
                show: false,
                type: 'success',
                message: '',
            },

            init() {
                this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                this.applicationId = {{ $application->id }};
            },

            toggleDropdown() {
                this.dropdownOpen = !this.dropdownOpen;
            },

            async openModal(type) {
                this.modalType = type;
                this.dropdownOpen = false;
                this.resetForm();
                this.modalOpen = true;
                await this.fetchTemplates();
            },

            closeModal() {
                this.modalOpen = false;
                this.resetForm();
                this.selectedTemplate = '';
                this.templates = {};
            },

            resetForm() {
                this.formData = {
                    subject: '',
                    message: '',
                };
                this.toast.show = false;
            },

            async fetchTemplates() {
                const endpoint = this.modalType === 'email'
                    ? `/admin/applications/${this.applicationId}/email-templates`
                    : `/admin/applications/${this.applicationId}/sms-templates`;

                try {
                    console.log('Fetching templates from:', endpoint);
                    const response = await fetch(endpoint, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();
                    console.log('Templates loaded:', data);

                    if (data.success && data.templates) {
                        this.templates = data.templates;
                    } else {
                        console.error('Invalid template data:', data);
                    }
                } catch (error) {
                    console.error('Failed to fetch templates:', error);
                    this.showToast('Failed to load templates', 'error');
                }
            },

            loadTemplate() {
                console.log('Selected template:', this.selectedTemplate);
                console.log('Available templates:', this.templates);

                if (!this.selectedTemplate) {
                    this.resetForm();
                    return;
                }

                const template = this.templates[this.selectedTemplate];
                console.log('Loading template data:', template);

                if (template) {
                    if (this.modalType === 'email') {
                        this.formData.subject = template.subject || '';
                        this.formData.message = template.body || '';
                    } else {
                        this.formData.message = template.body || '';
                    }
                } else {
                    console.error('Template not found:', this.selectedTemplate);
                }
            },

            isValid() {
                if (this.modalType === 'email') {
                    return this.formData.subject.trim() && this.formData.message.trim();
                }
                return this.formData.message.trim();
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, type, message };
                setTimeout(() => {
                    this.toast.show = false;
                }, 4000);
            },

            async sendCommunication() {
                if (!this.isValid()) return;

                this.sending = true;

                const endpoint = this.modalType === 'email'
                    ? `/admin/applications/${this.applicationId}/send-email`
                    : `/admin/applications/${this.applicationId}/send-sms`;

                const payload = this.modalType === 'email'
                    ? { subject: this.formData.subject, message: this.formData.message }
                    : { message: this.formData.message };

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showToast(data.message, 'success');
                        setTimeout(() => {
                            this.closeModal();
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showToast(data.message, 'error');
                    }
                } catch (error) {
                    this.showToast('An error occurred. Please try again.', 'error');
                    console.error('Error:', error);
                } finally {
                    this.sending = false;
                }
            },
        };
    }
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
