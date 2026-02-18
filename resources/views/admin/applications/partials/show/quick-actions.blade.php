<div class="bg-white shadow-xl sm:rounded-lg">
    <div class="p-6">
        <div class="flex flex-wrap gap-4 items-end">
            <!-- Change Status -->
            <form method="POST" action="{{ route('admin.applications.updateStatus', $application) }}" class="flex items-end gap-2">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-gray-700">Change Status</label>
                    <select name="status" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="submitted" {{ $application->status == 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="under_review" {{ $application->status == 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="additional_info_required" {{ $application->status == 'additional_info_required' ? 'selected' : '' }}>Additional Info Required</option>
                        <option value="approved" {{ $application->status == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="declined" {{ $application->status == 'declined' ? 'selected' : '' }}>Declined</option>
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Update Status
                </button>
            </form>

            <!-- Assign To -->
            <form method="POST" action="{{ route('admin.applications.assign', $application) }}" class="flex items-end gap-2">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-sm font-medium text-gray-700">Assign To</label>
                    <select name="assigned_to" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Assessor</option>
                        @foreach(\App\Models\User::role(['admin', 'assessor'])->get() as $assessor)
                            <option value="{{ $assessor->id }}" {{ $application->assigned_to == $assessor->id ? 'selected' : '' }}>{{ $assessor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Assign
                </button>
            </form>

            <!-- Communication Dropdown -->
            @include('admin.partials.communication.communication-modal')

            @if(in_array($application->status, ['submitted', 'under_review']))
                <div x-data="{ showReturnModal: false }" class="contents">
                    <!-- Return Button -->
                    <button @click="showReturnModal = true"
                            class="inline-flex items-center px-4 py-2 bg-orange-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-600 transition"
                            aria-haspopup="dialog">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                        </svg>
                        Return to Client
                    </button>

                    <!-- Return Modal -->
                    <div x-show="showReturnModal"
                        x-cloak
                        class="fixed inset-0 z-50 overflow-y-auto"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="return-modal-title">

                        <!-- Backdrop -->
                        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                            @click="showReturnModal = false"></div>

                        <!-- Modal Content -->
                        <div class="relative min-h-screen flex items-center justify-center p-4">
                            <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full p-6"
                                @click.stop>

                                <!-- Header -->
                                <div class="flex items-center mb-5">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center mr-3">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 id="return-modal-title" class="text-lg font-semibold text-gray-900">
                                            Return Application to Client
                                        </h3>
                                        <p class="text-sm text-gray-500">{{ $application->application_number }}</p>
                                    </div>
                                </div>

                                <form method="POST"
                                    action="{{ route('admin.applications.returnToClient', $application) }}">
                                    @csrf

                                    <!-- Info Banner about notifications -->
                                    <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            <div class="text-sm text-blue-800">
                                                <strong class="font-semibold">Automatic Notifications:</strong>
                                                <p class="mt-1">When you return this application, the client will automatically receive:</p>
                                                <ul class="mt-1 ml-4 list-disc space-y-0.5">
                                                    <li>Email notification with your reason</li>
                                                    <li>SMS/WhatsApp notification (if enabled below)</li>
                                                    <li>Ability to edit and resubmit their application</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Return Reason -->
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

                                    <!-- SMS Option -->
                                    @if($application->personalDetails?->mobile_phone)
                                    <div class="mb-5">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox"
                                                name="notify_sms"
                                                value="1"
                                                checked
                                                class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                            <span class="ml-2 text-sm text-gray-700">
                                                Also send SMS/WhatsApp notification
                                                <span class="text-gray-500">({{ $application->personalDetails->mobile_phone }})</span>
                                            </span>
                                        </label>
                                    </div>
                                    @endif

                                    <!-- Info Banner -->
                                    <div class="mb-5 p-3 bg-orange-50 rounded-lg border border-orange-200">
                                        <p class="text-xs text-orange-800">
                                            <strong>What happens next:</strong> The application status will change to
                                            "Additional Info Required". The client will be notified by email
                                            and can edit and resubmit their application.
                                        </p>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex justify-end space-x-3">
                                        <button type="button"
                                                @click="showReturnModal = false"
                                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200 transition">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-2 bg-orange-500 text-white rounded-md text-sm font-medium hover:bg-orange-600 transition">
                                            Return to Client
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Export PDF -->
            {{-- <div class="flex flex-col justify-end"> --}}
                <a href="{{ route('admin.applications.exportPdf', $application) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                    Export PDF
                </a>
            {{-- </div> --}}
        </div>
    </div>
</div>
