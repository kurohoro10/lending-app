{{-- resources/views/admin/applications/partials/show/quick-actions.blade.php --}}

<div class="bg-white shadow-xl sm:rounded-lg">
    <div class="p-6">
        <div class="flex flex-wrap gap-4 items-end">

            {{-- ── Change Status ──────────────────────────────────────────── --}}
            <form method="POST"
                  action="{{ route('admin.applications.updateStatus', $application) }}"
                  class="flex items-end gap-2"
                  data-loading-form>
                @csrf
                @method('PATCH')
                <div>
                    <label for="status-select" class="block text-sm font-medium text-gray-700">
                        Change Status
                    </label>
                    <select id="status-select"
                            name="status"
                            required
                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm
                                   focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="submitted"                {{ $application->status === 'submitted'                ? 'selected' : '' }}>Submitted</option>
                        <option value="under_review"             {{ $application->status === 'under_review'             ? 'selected' : '' }}>Under Review</option>
                        <option value="additional_info_required" {{ $application->status === 'additional_info_required' ? 'selected' : '' }}>Additional Info Required</option>
                        <option value="approved"                 {{ $application->status === 'approved'                 ? 'selected' : '' }}>Approved</option>
                        <option value="declined"                 {{ $application->status === 'declined'                 ? 'selected' : '' }}>Declined</option>
                    </select>
                </div>
                <button type="submit"
                        class="loading-btn inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent
                               rounded-md font-semibold text-xs text-white uppercase tracking-widest
                               hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                               disabled:opacity-60 disabled:cursor-not-allowed transition">
                    <svg class="btn-spinner hidden animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span class="btn-label">Update Status</span>
                </button>
            </form>

            {{-- ── Assign To ──────────────────────────────────────────────── --}}
            @if(auth()->user()->hasRole('admin'))
                @if(in_array($application->status, ['approved', 'declined']))
                    {{-- Show read-only for approved/declined --}}
                    <div class="flex items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Assigned To
                            </label>
                            <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 border border-gray-300 rounded-md">
                                <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-semibold text-gray-700">
                                    {{ $application->assignedTo->name ?? 'Unassigned' }}
                                </span>
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true" title="Assignment locked">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Cannot reassign {{ $application->status }} applications
                            </p>
                        </div>
                    </div>
                @else
                    {{-- Admins can assign for other statuses --}}
                    <form method="POST"
                        action="{{ route('admin.applications.assign', $application) }}"
                        class="flex items-end gap-2"
                        data-loading-form>
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="assigned-select" class="block text-sm font-medium text-gray-700">
                                Assign To
                                @if($application->status === 'submitted')
                                    <span class="text-xs text-indigo-600 font-normal">(will change status to "Under Review")</span>
                                @endif
                            </label>
                            <select id="assigned-select"
                                    name="assigned_to"
                                    required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm
                                        focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select Assessor</option>
                                @foreach(\App\Models\User::role(['admin', 'assessor'])->get() as $assessor)
                                    <option value="{{ $assessor->id }}"
                                            {{ $application->assigned_to == $assessor->id ? 'selected' : '' }}>
                                        {{ $assessor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                                class="loading-btn inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent
                                    rounded-md font-semibold text-xs text-white uppercase tracking-widest
                                    hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                    disabled:opacity-60 disabled:cursor-not-allowed transition">
                            <svg class="btn-spinner hidden animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span class="btn-label">Assign</span>
                        </button>
                    </form>
                @endif
            @else
                {{-- Assessors see read-only assignment info --}}
                <div class="flex items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Assigned To
                        </label>
                        @if($application->assigned_to === auth()->id())
                            <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-50 border border-green-200 rounded-md">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-semibold text-green-900">
                                    You ({{ auth()->user()->name }})
                                </span>
                            </div>
                        @else
                            <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-200 rounded-md">
                                <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-semibold text-indigo-900">
                                    {{ $application->assignedTo->name ?? 'Unassigned' }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ── Contact Client (Communication modal) ──────────────────── --}}
            @include('admin.partials.communication.communication-modal')

            {{-- ── Return to Client ───────────────────────────────────────── --}}
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

            {{-- ── Expense Calculator ─────────────────────────────────────── --}}
            <button type="button"
                    id="open-expense-calculator"
                    data-data-route="{{ route('admin.expenses.data', $application) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-indigo-300 text-indigo-700 text-sm
                           font-medium rounded-md hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500
                           focus:ring-offset-2 transition"
                    aria-haspopup="dialog"
                    aria-controls="expense-calculator-modal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Expense Calculator
            </button>

            {{-- ── Export PDF ─────────────────────────────────────────────── --}}
            <a href="{{ route('admin.applications.exportPdf', $application) }}"
               id="export-pdf-btn"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 border border-transparent rounded-md
                      font-semibold text-xs text-white uppercase tracking-widest
                      hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2
                      transition"
               aria-label="Export application as PDF">
                <svg id="export-pdf-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <svg id="export-pdf-spinner" class="hidden animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span id="export-pdf-label">Export PDF</span>
            </a>

        </div>
    </div>
</div>
