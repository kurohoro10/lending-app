{{-- resources/views/admin/applications/guarantor-form.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Guarantor Form — {{ $application->application_number }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Fill in all details and capture signatures, then send to client for acknowledgement.
                </p>
            </div>
            <a href="{{ route('admin.applications.show', $application) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300
                      rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                ← Back to Application
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Status Banner --}}
            @if($application->isGuarantorFormSigned())
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-sm font-medium text-green-800">
                        This form was signed by the client on
                        {{ $application->guarantor_form_signed_at->format('d M Y \a\t g:i A') }}.
                    </p>
                </div>
            @elseif($application->guarantor_form_request_url)
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-medium text-blue-800">
                        Form sent to client and awaiting signature.
                    </p>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.applications.guarantor-form.store', $application) }}"
                  id="guarantor-form">
                @csrf

                {{-- Section 1: Guarantor Details --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Section 1 — Guarantor Details
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Legal Name <span class="text-red-500">*</span></label>
                            <input type="text" name="guarantor_full_name"
                                   value="{{ old('guarantor_full_name', $guarantorData['guarantor_full_name'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('guarantor_full_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth <span class="text-red-500">*</span></label>
                            <input type="date" name="guarantor_dob"
                                   value="{{ old('guarantor_dob', $guarantorData['guarantor_dob'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('guarantor_dob')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Driver Licence / Passport Number <span class="text-red-500">*</span></label>
                            <input type="text" name="guarantor_id_number"
                                   value="{{ old('guarantor_id_number', $guarantorData['guarantor_id_number'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('guarantor_id_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Residential Address <span class="text-red-500">*</span></label>
                            <input type="text" name="guarantor_residential"
                                   value="{{ old('guarantor_residential', $guarantorData['guarantor_residential'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('guarantor_residential')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Postal Address</label>
                            <input type="text" name="guarantor_postal"
                                   value="{{ old('guarantor_postal', $guarantorData['guarantor_postal'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Telephone <span class="text-red-500">*</span></label>
                            <input type="text" name="guarantor_phone"
                                   value="{{ old('guarantor_phone', $guarantorData['guarantor_phone'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('guarantor_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                            <input type="email" name="guarantor_email"
                                   value="{{ old('guarantor_email', $guarantorData['guarantor_email'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('guarantor_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Occupation <span class="text-red-500">*</span></label>
                            <input type="text" name="guarantor_occupation"
                                   value="{{ old('guarantor_occupation', $guarantorData['guarantor_occupation'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('guarantor_occupation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Employer / Business Name <span class="text-red-500">*</span></label>
                            <input type="text" name="guarantor_employer"
                                   value="{{ old('guarantor_employer', $guarantorData['guarantor_employer'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('guarantor_employer')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">ABN / ACN (if applicable)</label>
                            <input type="text" name="guarantor_abn"
                                   value="{{ old('guarantor_abn', $guarantorData['guarantor_abn'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                    </div>
                </div>

                {{-- Section 2: Borrower Details --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Section 2 — Borrower Details
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Borrower Name <span class="text-red-500">*</span></label>
                            <input type="text" name="borrower_name"
                                   value="{{ old('borrower_name', $guarantorData['borrower_name'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('borrower_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ABN / ACN</label>
                            <input type="text" name="borrower_abn"
                                   value="{{ old('borrower_abn', $guarantorData['borrower_abn'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Facility Type <span class="text-red-500">*</span></label>
                            <input type="text" name="facility_type"
                                   value="{{ old('facility_type', $guarantorData['facility_type'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('facility_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Registered Address <span class="text-red-500">*</span></label>
                            <input type="text" name="borrower_address"
                                   value="{{ old('borrower_address', $guarantorData['borrower_address'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('borrower_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Loan / Facility Amount <span class="text-red-500">*</span></label>
                            <input type="text" name="loan_amount"
                                   value="{{ old('loan_amount', $guarantorData['loan_amount'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('loan_amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                    </div>
                </div>

                {{-- Guarantor Signature --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Guarantor Signature <span class="text-red-500">*</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">The guarantor signs here in person with the admin present.</p>
                    </div>
                    <div class="p-6">
                        <input type="hidden" name="guarantor_signature" id="guarantor_signature_input"
                               value="{{ old('guarantor_signature', $guarantorData['guarantor_signature'] ?? '') }}">

                        @if(!empty($guarantorData['guarantor_signature']) && !old('guarantor_signature'))
                            {{-- Show existing signature with option to re-sign --}}
                            <div id="guarantor-existing-sig" class="space-y-3">
                                <p class="text-xs text-gray-500">Current signature on file:</p>
                                <div class="border border-gray-200 rounded-lg bg-gray-50 p-2 inline-block">
                                    <img src="{{ $guarantorData['guarantor_signature'] }}"
                                         alt="Guarantor Signature" class="h-24 object-contain">
                                </div>
                                <div>
                                    <button type="button"
                                            onclick="showCanvas('guarantor')"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 underline">
                                        Re-sign
                                    </button>
                                </div>
                            </div>
                            <div id="guarantor-canvas-wrap" class="hidden space-y-3">
                                <canvas id="guarantor-sig-canvas"
                                        class="w-full border border-gray-300 rounded-lg bg-white touch-none"
                                        style="height:160px"></canvas>
                                <div class="flex gap-3">
                                    <button type="button" onclick="clearCanvas('guarantor')"
                                            class="text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                        Clear
                                    </button>
                                    <button type="button" onclick="hideCanvas('guarantor')"
                                            class="text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @else
                            {{-- No existing signature — show canvas immediately --}}
                            <div id="guarantor-canvas-wrap" class="space-y-3">
                                <canvas id="guarantor-sig-canvas"
                                        class="w-full border border-gray-300 rounded-lg bg-white touch-none"
                                        style="height:160px"></canvas>
                                <button type="button" onclick="clearCanvas('guarantor')"
                                        class="text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                    Clear
                                </button>
                            </div>
                        @endif
                        @error('guarantor_signature')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Witness --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Witness
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" name="witness_full_name"
                                   value="{{ old('witness_full_name', $guarantorData['witness_full_name'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Occupation</label>
                            <input type="text" name="witness_occupation"
                                   value="{{ old('witness_occupation', $guarantorData['witness_occupation'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        {{-- Witness Signature --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Witness Signature</label>
                            <input type="hidden" name="witness_signature" id="witness_signature_input"
                                   value="{{ old('witness_signature', $guarantorData['witness_signature'] ?? '') }}">

                            @if(!empty($guarantorData['witness_signature']) && !old('witness_signature'))
                                <div id="witness-existing-sig" class="space-y-3">
                                    <p class="text-xs text-gray-500">Current signature on file:</p>
                                    <div class="border border-gray-200 rounded-lg bg-gray-50 p-2 inline-block">
                                        <img src="{{ $guarantorData['witness_signature'] }}"
                                             alt="Witness Signature" class="h-24 object-contain">
                                    </div>
                                    <div>
                                        <button type="button"
                                                onclick="showCanvas('witness')"
                                                class="text-xs text-indigo-600 hover:text-indigo-800 underline">
                                            Re-sign
                                        </button>
                                    </div>
                                </div>
                                <div id="witness-canvas-wrap" class="hidden space-y-3">
                                    <canvas id="witness-sig-canvas"
                                            class="w-full border border-gray-300 rounded-lg bg-white touch-none"
                                            style="height:160px"></canvas>
                                    <div class="flex gap-3">
                                        <button type="button" onclick="clearCanvas('witness')"
                                                class="text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                            Clear
                                        </button>
                                        <button type="button" onclick="hideCanvas('witness')"
                                                class="text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div id="witness-canvas-wrap" class="space-y-3">
                                    <canvas id="witness-sig-canvas"
                                            class="w-full border border-gray-300 rounded-lg bg-white touch-none"
                                            style="height:160px"></canvas>
                                    <button type="button" onclick="clearCanvas('witness')"
                                            class="text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                        Clear
                                    </button>
                                </div>
                            @endif
                            @error('witness_signature')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                    </div>
                </div>

                {{-- Solicitor --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Solicitor's Certificate
                            <span class="ml-2 text-xs font-normal text-gray-400 normal-case">(optional)</span>
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Solicitor Name</label>
                            <input type="text" name="solicitor_name"
                                   value="{{ old('solicitor_name', $guarantorData['solicitor_name'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Law Firm</label>
                            <input type="text" name="solicitor_firm"
                                   value="{{ old('solicitor_firm', $guarantorData['solicitor_firm'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        {{-- Solicitor Signature --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Solicitor Signature</label>
                            <input type="hidden" name="solicitor_signature" id="solicitor_signature_input"
                                   value="{{ old('solicitor_signature', $guarantorData['solicitor_signature'] ?? '') }}">

                            @if(!empty($guarantorData['solicitor_signature']) && !old('solicitor_signature'))
                                <div id="solicitor-existing-sig" class="space-y-3">
                                    <p class="text-xs text-gray-500">Current signature on file:</p>
                                    <div class="border border-gray-200 rounded-lg bg-gray-50 p-2 inline-block">
                                        <img src="{{ $guarantorData['solicitor_signature'] }}"
                                             alt="Solicitor Signature" class="h-24 object-contain">
                                    </div>
                                    <div>
                                        <button type="button"
                                                onclick="showCanvas('solicitor')"
                                                class="text-xs text-indigo-600 hover:text-indigo-800 underline">
                                            Re-sign
                                        </button>
                                    </div>
                                </div>
                                <div id="solicitor-canvas-wrap" class="hidden space-y-3">
                                    <canvas id="solicitor-sig-canvas"
                                            class="w-full border border-gray-300 rounded-lg bg-white touch-none"
                                            style="height:160px"></canvas>
                                    <div class="flex gap-3">
                                        <button type="button" onclick="clearCanvas('solicitor')"
                                                class="text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                            Clear
                                        </button>
                                        <button type="button" onclick="hideCanvas('solicitor')"
                                                class="text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div id="solicitor-canvas-wrap" class="space-y-3">
                                    <canvas id="solicitor-sig-canvas"
                                            class="w-full border border-gray-300 rounded-lg bg-white touch-none"
                                            style="height:160px"></canvas>
                                    <button type="button" onclick="clearCanvas('solicitor')"
                                            class="text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                        Clear
                                    </button>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between bg-white shadow-sm rounded-lg border border-gray-200 px-6 py-4">
                    <p class="text-xs text-gray-500">
                        Save first, then send to client once you are satisfied with the details.
                    </p>
                    @if(! $application->isGuarantorFormSigned())
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                                       text-sm font-semibold rounded-md hover:bg-indigo-700 transition
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Save Form
                        </button>
                    @endif
                </div>

            </form>

            {{-- Send to Client --}}
            @if($application->hasGuarantorData() && ! $application->isGuarantorFormSigned())
                <form method="POST"
                      action="{{ route('admin.applications.guarantor-form.send', $application) }}"
                      onsubmit="return confirm('Send the guarantor form link to the client?')">
                    @csrf
                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-green-600 text-white
                                       text-sm font-semibold rounded-md hover:bg-green-700 transition
                                       focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Send to Client
                        </button>
                    </div>
                </form>
            @endif

        </div>
    </div>

    <script>
        // ── Canvas state ──────────────────────────────────────────────────────────
        const canvases = {};

        function initCanvas(name) {
            const canvas = document.getElementById(`${name}-sig-canvas`);
            if (!canvas) return;

            // Fit canvas resolution to its rendered size
            const rect = canvas.getBoundingClientRect();
            canvas.width  = rect.width  || canvas.offsetWidth;
            canvas.height = rect.height || canvas.offsetHeight;

            const ctx = canvas.getContext('2d');
            ctx.strokeStyle = '#1e293b';
            ctx.lineWidth   = 2;
            ctx.lineCap     = 'round';
            ctx.lineJoin    = 'round';

            let drawing = false;
            let lastX = 0, lastY = 0;

            function pos(e) {
                const r = canvas.getBoundingClientRect();
                const src = e.touches ? e.touches[0] : e;
                return [src.clientX - r.left, src.clientY - r.top];
            }

            function start(e) {
                e.preventDefault();
                drawing = true;
                [lastX, lastY] = pos(e);
            }

            function draw(e) {
                if (!drawing) return;
                e.preventDefault();
                const [x, y] = pos(e);
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(x, y);
                ctx.stroke();
                [lastX, lastY] = [x, y];
                // Push to hidden input live
                document.getElementById(`${name}_signature_input`).value = canvas.toDataURL();
            }

            function stop() { drawing = false; }

            canvas.addEventListener('mousedown',  start);
            canvas.addEventListener('mousemove',  draw);
            canvas.addEventListener('mouseup',    stop);
            canvas.addEventListener('mouseleave', stop);
            canvas.addEventListener('touchstart', start, { passive: false });
            canvas.addEventListener('touchmove',  draw,  { passive: false });
            canvas.addEventListener('touchend',   stop);

            canvases[name] = canvas;
        }

        function clearCanvas(name) {
            const canvas = canvases[name];
            if (!canvas) return;
            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById(`${name}_signature_input`).value = '';
        }

        function showCanvas(name) {
            document.getElementById(`${name}-existing-sig`).classList.add('hidden');
            document.getElementById(`${name}-canvas-wrap`).classList.remove('hidden');
            // Clear the old stored value so a fresh draw is required
            document.getElementById(`${name}_signature_input`).value = '';
            initCanvas(name);
        }

        function hideCanvas(name) {
            document.getElementById(`${name}-canvas-wrap`).classList.add('hidden');
            document.getElementById(`${name}-existing-sig`).classList.remove('hidden');
            // Restore original value
            // (the hidden input was cleared by showCanvas — re-populate from the img src)
            const img = document.querySelector(`#${name}-existing-sig img`);
            if (img) document.getElementById(`${name}_signature_input`).value = img.src;
        }

        // ── Init on load ──────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            ['guarantor', 'witness', 'solicitor'].forEach(name => {
                const wrap = document.getElementById(`${name}-canvas-wrap`);
                if (wrap && !wrap.classList.contains('hidden')) {
                    initCanvas(name);
                }
            });
        });
    </script>
</x-app-layout>