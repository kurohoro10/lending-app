{{-- resources/views/admin/applications/partials/show/returnedToClient.blade.php --}}
@if(in_array($application->status, ['submitted', 'under_review']))
    <div x-data="{ showReturnModal: false }" class="contents">

        <button @click="showReturnModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 border border-transparent
                        rounded-md font-semibold text-xs text-white uppercase tracking-widest
                        hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2
                        transition"
                aria-haspopup="dialog"
                aria-controls="return-modal">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
            </svg>
            Return to Client
        </button>

        {{-- Return Modal --}}
        <div x-show="showReturnModal"
                x-cloak
                id="return-modal"
                class="fixed inset-0 z-50 overflow-y-auto"
                role="dialog"
                aria-modal="true"
                aria-labelledby="return-modal-title">

            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                    @click="showReturnModal = false"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full p-6" @click.stop>

                    <div class="flex items-center mb-5">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                        </div>
                        <div>
                            <h3 id="return-modal-title" class="text-lg font-semibold text-gray-900">
                                Return Application to Client
                            </h3>
                            <p class="text-sm text-gray-500">{{ $application->application_number }}</p>
                        </div>
                        <button @click="showReturnModal = false"
                                class="ml-auto text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 rounded"
                                aria-label="Close modal">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form method="POST"
                            action="{{ route('admin.applications.returnToClient', $application) }}"
                            data-loading-form>
                        @csrf

                        <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="text-sm text-blue-800">
                                    <strong class="font-semibold">Automatic Notifications:</strong>
                                    <p class="mt-1">The client will automatically receive an email notification with your reason and an SMS/WhatsApp if enabled below.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="return_reason"
                                    class="block text-sm font-medium text-gray-700 mb-2">
                                Reason for Return
                                <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <textarea id="return_reason"
                                        name="return_reason"
                                        rows="4"
                                        required
                                        minlength="10"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 text-sm"
                                        placeholder="Explain what the client needs to update or provide...">{{ old('return_reason') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                This message will be visible to the client and sent via email.
                            </p>
                            @error('return_reason')
                                <p class="mt-1 text-sm text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($application->personalDetails?->mobile_phone)
                            <div class="mb-5">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox"
                                            name="notify_sms"
                                            value="1"
                                            checked
                                            class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                    <span class="text-sm text-gray-700">
                                        Also send SMS/WhatsApp notification
                                        <span class="text-gray-500">({{ $application->personalDetails->mobile_phone }})</span>
                                    </span>
                                </label>
                            </div>
                        @endif

                        <div class="mb-5 p-3 bg-orange-50 rounded-lg border border-orange-200">
                            <p class="text-xs text-orange-800">
                                <strong>What happens next:</strong> The application status will change to
                                "Additional Info Required". The client will be notified and can edit and resubmit.
                            </p>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button"
                                    @click="showReturnModal = false"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium
                                            hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400 transition">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="loading-btn inline-flex items-center gap-2 px-4 py-2 bg-orange-500 text-white
                                            rounded-md text-sm font-medium hover:bg-orange-600
                                            focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2
                                            disabled:opacity-60 disabled:cursor-not-allowed transition">
                                <svg class="btn-spinner hidden animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span class="btn-label">Return to Client</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endif
