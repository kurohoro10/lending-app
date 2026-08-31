{{-- resources/views/applications/guarantor-form.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Guarantor Application Form
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $application->application_number }} — Please review, complete and sign below.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Section 1: Guarantor Details (read-only) --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                        Section 1 — Guarantor Details
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">Pre-filled by our team. Contact us if any details are incorrect.</p>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @foreach([
                        'Full Legal Name'                => $guarantorData['guarantor_full_name'] ?? '—',
                        'Date of Birth'                  => $guarantorData['guarantor_dob'] ?? '—',
                        'Driver Licence / Passport'      => $guarantorData['guarantor_id_number'] ?? '—',
                        'Residential Address'            => $guarantorData['guarantor_residential'] ?? '—',
                        'Postal Address'                 => $guarantorData['guarantor_postal'] ?: '—',
                        'Telephone'                      => $guarantorData['guarantor_phone'] ?? '—',
                        'Email Address'                  => $guarantorData['guarantor_email'] ?? '—',
                        'Occupation'                     => $guarantorData['guarantor_occupation'] ?? '—',
                        'Employer / Business Name'       => $guarantorData['guarantor_employer'] ?? '—',
                        'ABN / ACN'                      => $guarantorData['guarantor_abn'] ?: '—',
                    ] as $label => $value)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $label }}</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Legal Sections (read-only) --}}
            @foreach([
                '3. Guarantee Acknowledgement' => 'The Guarantor acknowledges and agrees that they have voluntarily offered to guarantee and indemnify the obligations of the Borrower. The guarantee and indemnity may be unlimited in amount unless expressly limited in writing by the Lender. The Lender may enforce the guarantee against the Guarantor without first taking action against the Borrower, any co-guarantor, or any security property. The Guarantor\'s obligations are continuing, irrevocable, and remain in force notwithstanding any variation, extension, renewal, restructuring, or replacement of any facility.',
                '4. Privacy Acknowledgement and Consent' => 'The Guarantor authorises and consents to the Lender collecting, using, verifying, disclosing, and exchanging personal, financial, commercial, and credit-related information for the purposes of assessing this application, managing the facility, recovering monies, and complying with legal and regulatory obligations including the Privacy Act 1988 (Cth), AML/CTF Act 2006 (Cth), and other applicable laws.',
                '5. Credit Enquiry Consent' => 'The Guarantor expressly consents to the Lender making consumer and commercial credit enquiries, obtaining credit reports, and exchanging information with credit reporting bodies and credit providers. The Guarantor acknowledges that credit enquiries may impact their credit score.',
                '6. Enforcement, Indemnity and Recovery Costs' => 'The Guarantor indemnifies the Lender against all loss, liability, damages, costs, charges, interest, default interest, legal costs on a full indemnity basis, and all other amounts suffered or incurred by the Lender arising from any default by the Borrower or Guarantor.',
                '7. Bankruptcy and Solvency Declaration' => 'The Guarantor declares and warrants that they are not bankrupt, insolvent, under administration, or subject to any insolvency proceeding, and that no application has been made or threatened for bankruptcy, winding up, or liquidation.',
                '8. Acknowledgement of Reliance' => 'The Guarantor acknowledges that the Lender will rely upon the information contained in this application when assessing the Borrower\'s application and the Guarantor\'s suitability. This form does not constitute an approval, offer, or acceptance of credit.',
                '9. Declaration' => 'I/We declare that all information provided in this application is true, correct, and complete; no material fact or circumstance has been omitted; I/we understand the nature and effect of the obligations undertaken; and I/we freely and voluntarily provide this declaration and consent.',
            ] as $title => $text)
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">{{ $title }}</h3>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $text }}</p>
                    </div>
                </div>
            @endforeach

            {{-- Form: Zone 2 (editable) + Zone 3 (signature) --}}
            <form method="POST"
                    action="{{ route('applications.guarantor-form.sign', ['application' => $application->id]) }}"
                    id="guarantor-sign-form">
                @csrf

                {{-- Section 2: Borrower Details (editable) --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Section 2 — Borrower Details
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Please review and correct if needed.</p>
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Registered Address <span class="text-red-500">*</span></label>
                            <input type="text" name="borrower_address"
                                   value="{{ old('borrower_address', $guarantorData['borrower_address'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('borrower_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Facility Type <span class="text-red-500">*</span></label>
                            <input type="text" name="facility_type"
                                   value="{{ old('facility_type', $guarantorData['facility_type'] ?? '') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            @error('facility_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
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

                {{-- Solicitor Certificate (editable) --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Solicitor's Certificate <span class="text-gray-400 font-normal normal-case">(optional)</span>
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

                    </div>
                </div>

                {{-- Zone 3: Signature & Declaration --}}
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Signature & Declaration
                        </h3>
                    </div>
                    <div class="p-6 space-y-5">

                        {{-- Signature Pad --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Your Signature <span class="text-red-500">*</span>
                            </label>
                            <div class="border-2 border-gray-300 rounded-lg overflow-hidden bg-gray-50"
                                 style="touch-action: none;">
                                <canvas id="signature-pad"
                                        class="w-full"
                                        width="700"
                                        height="180">
                                </canvas>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-xs text-gray-500">Draw your signature above using your mouse or finger.</p>
                                <button type="button"
                                        id="clear-signature"
                                        class="text-xs text-red-600 hover:text-red-800 underline">
                                    Clear
                                </button>
                            </div>
                            <input type="hidden" name="signature" id="signature-input">
                            @error('signature')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Date (auto) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input type="text"
                                   id="signed-date"
                                   class="w-48 border-gray-300 rounded-md shadow-sm text-sm bg-gray-100 text-gray-600"
                                   readonly>
                        </div>

                        {{-- Declaration Checkbox --}}
                        <div class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                            <input type="checkbox"
                                   name="declaration"
                                   id="declaration"
                                   value="1"
                                   class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                   required>
                            <label for="declaration" class="text-sm text-amber-900 leading-relaxed">
                                I declare that all information provided in this application is true, correct, and complete.
                                I understand the nature and effect of the guarantee and indemnity obligations, and I freely
                                and voluntarily provide this declaration and consent. I understand that providing false or
                                misleading information may constitute fraud.
                            </label>
                        </div>
                        @error('declaration')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                        {{-- Submit --}}
                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                    id="submit-btn"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white
                                           text-sm font-semibold rounded-md hover:bg-indigo-700 transition
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                           disabled:opacity-60 disabled:cursor-not-allowed">
                                Submit & Sign
                            </button>
                        </div>

                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Signature Pad JS (vanilla, no library needed) --}}
    <script>
    (() => {
        const canvas  = document.getElementById('signature-pad');
        const ctx     = canvas.getContext('2d');
        const input   = document.getElementById('signature-input');
        const clearBtn = document.getElementById('clear-signature');
        const form    = document.getElementById('guarantor-sign-form');

        // Auto date
        const dateEl = document.getElementById('signed-date');
        dateEl.value = new Date().toLocaleDateString('en-AU', {
            day: '2-digit', month: 'long', year: 'numeric'
        });

        // HiDPI scaling
        const ratio = window.devicePixelRatio || 1;
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        ctx.scale(ratio, ratio);

        ctx.strokeStyle = '#1e1e1e';
        ctx.lineWidth   = 2;
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';

        let drawing = false;
        let empty   = true;

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const src  = e.touches ? e.touches[0] : e;
            return {
                x: (src.clientX - rect.left),
                y: (src.clientY - rect.top),
            };
        }

        function start(e) {
            e.preventDefault();
            drawing = true;
            const p = getPos(e);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        }

        function draw(e) {
            if (! drawing) return;
            e.preventDefault();
            empty = false;
            const p = getPos(e);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
        }

        function stop(e) {
            if (! drawing) return;
            drawing = false;
            input.value = canvas.toDataURL('image/png');
        }

        canvas.addEventListener('mousedown',  start);
        canvas.addEventListener('mousemove',  draw);
        canvas.addEventListener('mouseup',    stop);
        canvas.addEventListener('mouseleave', stop);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove',  draw,  { passive: false });
        canvas.addEventListener('touchend',   stop);

        clearBtn.addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            input.value = '';
            empty = true;
        });

        form.addEventListener('submit', e => {
            if (empty || ! input.value) {
                e.preventDefault();
                alert('Please draw your signature before submitting.');
                canvas.focus();
            }
        });
    })();
    </script>

</x-app-layout>