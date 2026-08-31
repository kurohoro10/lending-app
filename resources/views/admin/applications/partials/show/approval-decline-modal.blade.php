{{-- =========================================================================
     Approval / Decline Letter Modal
     =========================================================================
     Triggered by #btn-approve and #btn-decline in quick-actions.blade.php.
     On confirm:
       1. PATCHes the new status via admin.applications.updateStatus
       2. POSTs the letter via admin.email.send
     Both requests use the existing routes — no new backend work required.
     ========================================================================= --}}

@php
    $clientName  = $application->personalDetails?->user?->first_name
                ?? $application->user->name;
    $appNumber   = $application->application_number;
    $loanAmount  = number_format($application->loan_amount, 2);
    $loanPurpose = \App\Support\LoanPurpose::label($application->loan_purpose);
    $termMonths  = $application->term_months;
    $fromName    = config('app.name', 'Our Team');
@endphp

{{-- ── Backdrop ─────────────────────────────────────────────────────────── --}}
<div id="adl-backdrop"
     class="hidden fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-40"
     aria-hidden="true"></div>

{{-- ── Modal ────────────────────────────────────────────────────────────── --}}
<div id="adl-modal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="adl-title">

    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl flex flex-col max-h-[90vh]">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <div class="flex items-center gap-3">
                {{-- Icon — swapped by JS --}}
                <div id="adl-icon"
                     class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center">
                </div>
                <div>
                    <h2 id="adl-title" class="text-base font-semibold text-gray-900"></h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $appNumber }} · {{ $clientName }}
                    </p>
                </div>
            </div>
            <button type="button"
                    id="adl-close"
                    class="text-gray-400 hover:text-gray-600 rounded-lg p-1.5 focus:outline-none
                           focus:ring-2 focus:ring-indigo-500 transition"
                    aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Notice banner --}}
        <div id="adl-notice"
             class="hidden mx-6 mt-4 rounded-lg px-4 py-3 text-sm font-medium"
             role="alert"
             aria-live="polite">
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">

            <p class="text-sm text-gray-600">
                Review and edit the letter below before sending. The status will be updated
                and the email dispatched when you click <strong>Send Letter</strong>.
            </p>

            {{-- Subject --}}
            <div>
                <label for="adl-subject"
                       class="block text-sm font-medium text-gray-700 mb-1">
                    Subject
                </label>
                <input type="text"
                       id="adl-subject"
                       maxlength="255"
                       class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm
                              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                       placeholder="Email subject…">
            </div>

            {{-- Body --}}
            <div>
                <label for="adl-body"
                       class="block text-sm font-medium text-gray-700 mb-1">
                    Message
                </label>
                <textarea id="adl-body"
                          rows="14"
                          maxlength="5000"
                          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm font-mono
                                 leading-relaxed focus:outline-none focus:ring-2 focus:ring-indigo-500
                                 focus:border-indigo-500 resize-y"
                          placeholder="Letter body…"></textarea>
                <p class="mt-1 text-xs text-gray-400 text-right">
                    <span id="adl-char-count">0</span> / 5000
                </p>
            </div>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-xl gap-3">
            <button type="button"
                    id="adl-cancel"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300
                           rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500
                           focus:ring-offset-2 transition">
                Cancel
            </button>

            <button type="button"
                    id="adl-send"
                    class="inline-flex items-center gap-2 px-5 py-2 border border-transparent rounded-md
                           font-semibold text-sm text-white focus:outline-none focus:ring-2
                           focus:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition">
                <svg id="adl-send-spinner"
                     class="hidden animate-spin h-4 w-4 text-white"
                     fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span id="adl-send-label">Send Letter</span>
            </button>
        </div>

    </div>
</div>

<script>
    (() => {
        // ── Config ────────────────────────────────────────────────────────────────
        const APP_ID     = @js($application->id);
        const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content;
        const CLIENT     = @js($clientName);
        const APP_NO     = @js($appNumber);
        const AMOUNT     = @js($loanAmount);
        const PURPOSE    = @js($loanPurpose);
        const TERM       = @js($termMonths);
        const FROM_NAME  = @js($fromName);

        const EMAIL_URL  = `/admin/applications/${APP_ID}/send-email`;
        const STATUS_URL = `/admin/applications/${APP_ID}/status`;

        // ── Date formatter ────────────────────────────────────────────────────────
        function getTodayDate() {
            const today = new Date();
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return today.toLocaleDateString('en-AU', options);
        }

        const TODAY = getTodayDate();

        // ── Letter templates ──────────────────────────────────────────────────────
        const TEMPLATES = {
            approve: {
                title:      'Send Approval Letter',
                newStatus:  'approved',
                iconBg:     'bg-green-100',
                iconColor:  'text-green-600',
                btnBg:      'bg-green-600 hover:bg-green-700 focus:ring-green-500',
                noticeBg:   'bg-green-50 text-green-800 border border-green-200',
                iconSvg: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>`,
                subject: `Loan Application ${APP_NO} — Conditional Approval`,
                body:
`ZYA Capital Pty Ltd
ABN: 55 695 692 052
E: hello@zyacapital.com.au
Date: ${TODAY}

Subject: Conditional Loan Approval

Dear ${CLIENT},

Re: Conditional Approval of Commercial Loan Application

We refer to your loan application submitted to ZYA Capital Pty Ltd ("Lender").

We are pleased to advise that, subject to the terms and conditions outlined in this letter, your application has received conditional approval.

This approval is granted based on the information currently available to the Lender and remains subject to the satisfactory completion of all pre-settlement requirements, including but not limited to the following:

1. Execution of Loan Documentation
   * The Borrower and any guarantor(s) must execute all loan, security and ancillary documentation required by the Lender.

2. Insurance Requirements
   * The Borrower must provide evidence of adequate insurance cover, including any property, asset, business, public liability or other insurance required by the Lender, noting the Lender's interest where applicable.

3. Title and Security Verification
   * Satisfactory title searches, PPSR searches, ASIC searches, valuation reports and any other investigations required by the Lender must be completed and approved.

4. Verification of Information
   * All information, documents, financial statements, declarations and representations provided by the Borrower must be true, complete, accurate and not misleading in any material respect.

5. Compliance Requirements
   * The Borrower must satisfy all applicable compliance, identification, anti-money laundering and verification requirements of the Lender.

6. No Material Adverse Change
   * There must be no material adverse change in the Borrower's financial position, business operations, credit profile or security position prior to settlement.

Reservation of Rights

This approval is conditional only and does not constitute a binding commitment by the Lender to advance funds until all conditions have been satisfied to the sole satisfaction of the Lender.

The Lender reserves the absolute right, at any time prior to settlement, to amend, suspend, withdraw or cancel this approval without further notice if:

* Any condition contained in this letter is not satisfied;
* Any information provided by the Borrower is found to be inaccurate, incomplete or misleading;
* The results of any search, valuation, compliance review or due diligence process are unsatisfactory to the Lender;
* The Borrower fails to execute the required documentation; or
* Any circumstance arises which, in the Lender's opinion, increases the risk associated with the proposed lending arrangement.

Nothing in this letter shall be construed as creating a legally binding obligation on the Lender to provide finance until all loan documentation has been executed and settlement has occurred.

We look forward to working with you towards settlement of this facility.

Yours faithfully,

______________________________
Authorised Officer
ZYA Capital Pty Ltd`,
            },

            decline: {
                title:      'Send Decline Letter',
                newStatus:  'declined',
                iconBg:     'bg-red-100',
                iconColor:  'text-red-600',
                btnBg:      'bg-red-600 hover:bg-red-700 focus:ring-red-500',
                noticeBg:   'bg-red-50 text-red-800 border border-red-200',
                iconSvg: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>`,
                subject: `Loan Application ${APP_NO} — Application Outcome`,
                body:
`ZYA Capital Pty Ltd
ABN: 55 695 692 052
Date: ${TODAY}
Private & Confidential

Borrower: ${CLIENT}
Application Reference: ${APP_NO}

Loan Application Outcome

Dear ${CLIENT},

Thank you for your application for finance with ZYA Capital Pty Ltd ("ZYA Capital").

Following our assessment of your application and the information provided, we regret to advise that ZYA Capital is unable to approve your application for credit at this time.

This decision has been made after consideration of the information available to us, including information provided by you and information obtained during our assessment process, and having regard to our lending policies, risk assessment framework, compliance obligations, and commercial lending criteria.

The decision may have been influenced by one or more factors, including but not limited to:

1. Information provided in the application and supporting documentation;
2. Verification and due diligence outcomes;
3. Financial position and repayment capacity;
4. Credit history and credit-related information;
5. Security and collateral considerations;
6. Risk assessment outcomes; and
7. ZYA Capital's lending policies and commercial requirements.

This decision relates solely to the current application and circumstances known to us at the time of assessment. Any future application will be assessed on its own merits based on the information available at that time.

Please note that the provision of finance is subject to ZYA Capital's discretion and lending criteria. The submission of an application does not create any obligation on ZYA Capital to provide credit or enter into any lending arrangement.

If you would like further information regarding this decision, you may contact us using the details below.

Complaints and Dispute Resolution

If you are dissatisfied with this decision or any aspect of our service, you may lodge a complaint with our Internal Dispute Resolution (IDR) team:

ZYA Capital Pty Ltd
Email: hello@zyacapital.com.au

We will acknowledge and respond to your complaint in accordance with our Internal Dispute Resolution procedures and applicable regulatory requirements.

Yours faithfully,

______________________________
Authorised Representative
ZYA Capital Pty Ltd`,
            },
        };

        // ── Element refs ──────────────────────────────────────────────────────────
        const backdrop    = document.getElementById('adl-backdrop');
        const modal       = document.getElementById('adl-modal');
        const iconEl      = document.getElementById('adl-icon');
        const titleEl     = document.getElementById('adl-title');
        const noticeEl    = document.getElementById('adl-notice');
        const subjectEl   = document.getElementById('adl-subject');
        const bodyEl      = document.getElementById('adl-body');
        const charCount   = document.getElementById('adl-char-count');
        const sendBtn     = document.getElementById('adl-send');
        const sendSpinner = document.getElementById('adl-send-spinner');
        const sendLabel   = document.getElementById('adl-send-label');
        const closeBtn    = document.getElementById('adl-close');
        const cancelBtn   = document.getElementById('adl-cancel');

        let currentType = null; // 'approve' | 'decline'

        // ── Open ──────────────────────────────────────────────────────────────────
        function open(type) {
            const cfg = TEMPLATES[type];
            if (! cfg) return;

            currentType = type;

            // Reset notice
            hideNotice();

            // Populate header
            titleEl.textContent = cfg.title;
            iconEl.className    = `flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center ${cfg.iconBg} ${cfg.iconColor}`;
            iconEl.innerHTML    = cfg.iconSvg;

            // Populate send button colour
            sendBtn.className = sendBtn.className.replace(
                /bg-\S+ hover:bg-\S+ focus:ring-\S+/g, ''
            );
            sendBtn.classList.add(...cfg.btnBg.split(' '));

            // Populate fields
            subjectEl.value = cfg.subject;
            bodyEl.value    = cfg.body;
            updateCharCount();

            // Show
            backdrop.classList.remove('hidden');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Focus subject so admin can tweak immediately
            subjectEl.focus();
        }

        // ── Close ─────────────────────────────────────────────────────────────────
        function close() {
            modal.classList.add('hidden');
            backdrop.classList.add('hidden');
            document.body.style.overflow = '';
            currentType = null;
            setSending(false);
        }

        // ── Char counter ──────────────────────────────────────────────────────────
        function updateCharCount() {
            charCount.textContent = bodyEl.value.length;
        }

        bodyEl.addEventListener('input', updateCharCount);

        // ── Notice helpers ────────────────────────────────────────────────────────
        function showNotice(msg, isError = false) {
            noticeEl.textContent = msg;
            noticeEl.className   = `mx-6 mt-4 rounded-lg px-4 py-3 text-sm font-medium ${
                isError
                    ? 'bg-red-50 text-red-800 border border-red-200'
                    : TEMPLATES[currentType]?.noticeBg ?? 'bg-green-50 text-green-800 border border-green-200'
            }`;
            noticeEl.classList.remove('hidden');
        }

        function hideNotice() {
            noticeEl.classList.add('hidden');
            noticeEl.textContent = '';
        }

        // ── Loading state ─────────────────────────────────────────────────────────
        function setSending(active) {
            sendBtn.disabled = active;
            sendSpinner.classList.toggle('hidden', ! active);
            sendLabel.textContent = active ? 'Sending…' : 'Send Letter';
        }

        // ── Send ──────────────────────────────────────────────────────────────────
        async function send() {
            const subject = subjectEl.value.trim();
            const message = bodyEl.value.trim();
            const cfg     = TEMPLATES[currentType];

            if (! subject) { showNotice('Please enter a subject.', true); subjectEl.focus(); return; }
            if (! message) { showNotice('Please enter a message body.', true); bodyEl.focus(); return; }

            hideNotice();
            setSending(true);

            // ── Step 1: Update status ─────────────────────────────────────────────
            try {
                const statusRes = await fetch(STATUS_URL, {
                    method: 'POST', // PATCH via _method spoofing
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: new URLSearchParams({
                        _method: 'PATCH',
                        status:  cfg.newStatus,
                    }),
                });

                // The controller returns a redirect (302) in non-JSON mode,
                // so we accept both 200 and 302 as success signals.
                // A JSON 422/500 means validation or lock failure.
                if (statusRes.headers.get('content-type')?.includes('application/json')) {
                    const json = await statusRes.json();
                    if (! statusRes.ok || json.errors) {
                        const msg = json.message ?? 'Could not update application status.';
                        showNotice(msg, true);
                        setSending(false);
                        return;
                    }
                }
                // Non-JSON (redirect) response is fine — the PATCH succeeded.

            } catch (err) {
                showNotice('Network error updating status. Please try again.', true);
                setSending(false);
                return;
            }

            // ── Step 2: Send email ────────────────────────────────────────────────
            try {
                const emailRes = await fetch(EMAIL_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ subject, message, letter_type: currentType === 'approve' ? 'approval_letter' : 'decline_letter' }),
                });

                const json = await emailRes.json();

                if (! emailRes.ok || ! json.success) {
                    // Status already changed — warn but don't block.
                    showNotice(
                        `Status updated to "${cfg.newStatus}", but the email could not be sent. ` +
                        `Please use Contact Client to send it manually.`,
                        true
                    );
                    setSending(false);
                    return;
                }

            } catch (err) {
                showNotice(
                    `Status updated to "${cfg.newStatus}", but a network error prevented the email. ` +
                    `Please send it manually via Contact Client.`,
                    true
                );
                setSending(false);
                return;
            }

            // ── All good — close and reload ───────────────────────────────────────
            close();
            // Reload so the status badge, dropdown, and locked-status UI all reflect the change.
            window.location.reload();
        }

        // ── Wire events ───────────────────────────────────────────────────────────
        sendBtn.addEventListener('click', send);
        closeBtn.addEventListener('click', close);
        cancelBtn.addEventListener('click', close);
        backdrop.addEventListener('click', close);

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && ! modal.classList.contains('hidden')) close();
        });

        // Trigger buttons (defined in quick-actions.blade.php)
        document.getElementById('btn-approve')?.addEventListener('click', () => open('approve'));
        document.getElementById('btn-decline')?.addEventListener('click', () => open('decline'));

        // Expose for any future programmatic use
        window.ApprovalDeclineModal = { open, close };
    })();
</script>