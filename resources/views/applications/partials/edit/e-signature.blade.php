{{-- resources/views/applications/partials/edit/e-signature.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl mb-6 border-2 border-red-300">
    <button type="button"
            class="w-full bg-gradient-to-r from-red-600 to-pink-600 px-6 py-4 text-left focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
            id="e-signature-btn"
            aria-expanded="true"
            aria-controls="e-signature-content">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Declaration, Consent &amp; Electronic Signature
                </h3>
                <p class="text-red-100 text-sm mt-1">Read the full declaration below, then sign to submit</p>
            </div>
            <svg id="e-signature-chevron" class="w-5 h-5 text-white transition-transform duration-200 transform rotate-180" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    <div id="e-signature-content"
         class="transition-all duration-300 ease-in-out p-6"
         aria-labelledby="e-signature-header">
        <div class="p-6">

            @if(session('error') && str_contains(session('error'), 'signature'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-xl">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-semibold">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- FULL DECLARATION DOCUMENT                                   --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="mb-6">
                <div class="flex items-center mb-3">
                    <h4 class="text-sm font-bold text-gray-800 uppercase tracking-wide flex items-center">
                        <svg class="w-4 h-4 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                        </svg>
                        Commercial Loan Declaration, Consent and Security Authorisation
                    </h4>
                </div>

                {{-- Full declaration — fully expanded, no scroll --}}
                <div class="border-2 border-gray-200 rounded-xl bg-gray-50 p-6 space-y-6 text-sm text-gray-800 leading-relaxed font-['Georgia',serif]">

                    <div class="text-center pb-4 border-b-2 border-gray-300">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-1">Business Purpose Credit Application</p>
                        <h2 class="text-lg font-bold text-gray-900">Commercial Loan Declaration, Consent and Security Authorisation</h2>
                        <p class="text-sm text-gray-600 mt-1">I, the undersigned Applicant, hereby declare, acknowledge and agree as follows:</p>
                    </div>

                    {{-- Section 1 --}}
                    <div>
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">1</span>
                            Accuracy of Information
                        </h3>
                        <p class="mb-2">I declare that all information provided in my loan application and any supporting documentation is <strong>true, accurate, complete and not misleading in any material respect</strong>.</p>
                        <p class="mb-2">I confirm that I have disclosed all information relevant to my financial circumstances which may reasonably affect the assessment of this application.</p>
                        <p class="mb-2">I acknowledge that <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> ("ZYA Capital", "we", "our", or "us") will rely upon the information provided by me in assessing this application.</p>
                        <p>Providing false or misleading information may constitute <strong>misrepresentation, fraud or breach of contract</strong>, and may result in immediate enforcement of the loan agreement and legal action.</p>
                    </div>

                    {{-- Section 2 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">2</span>
                            Wholly or Predominantly Business Purpose Declaration
                        </h3>
                        <p class="mb-2">I and my associated business entities in this application expressly declare that the credit applied for from <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> is <strong>wholly or predominantly for business or investment purposes</strong> and <strong>not for personal, domestic or household purposes</strong>.</p>
                        <p class="mb-2">I and my associated business entities in this application acknowledge and agree that:</p>
                        <ol class="list-decimal list-inside space-y-1 pl-2 mb-2">
                            <li>The loan is intended solely for <strong>commercial or business activities</strong>.</li>
                            <li>The credit contract <strong>may not be regulated under the National Consumer Credit Protection Act 2009 (Cth)</strong>.</li>
                            <li>I may <strong>not receive consumer protections normally available to consumer borrowers</strong>.</li>
                            <li><strong>ZYA Capital Pty Ltd relies upon this declaration</strong> when deciding whether to provide the loan.</li>
                        </ol>
                        <p>This declaration is made pursuant to <strong>section 13(5) of the National Consumer Credit Protection Act 2009 (Cth)</strong>.</p>
                    </div>

                    {{-- Section 3 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">3</span>
                            Credit Information Consent and Credit Reporting Authorisation
                        </h3>
                        <p class="mb-2">I and my associated business entities in this application acknowledge and agree that <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> may conduct credit checks as part of assessing this application and administering any credit facility.</p>
                        <p class="mb-2">I hereby <strong>authorise and consent</strong> to ZYA Capital Pty Ltd obtaining, accessing, using and exchanging my personal and credit information with <strong>credit reporting bodies, financial institutions, credit providers and other relevant third parties</strong> for the purposes of:</p>
                        <ul class="list-disc list-inside space-y-1 pl-2 mb-2">
                            <li>assessing my credit application</li>
                            <li>verifying information provided</li>
                            <li>assessing my creditworthiness</li>
                            <li>administering or enforcing any loan agreement</li>
                            <li>recovering any outstanding amounts</li>
                        </ul>
                        <p class="mb-2">I understand that:</p>
                        <ol class="list-decimal list-inside space-y-1 pl-2 mb-2">
                            <li>A credit enquiry made by <strong>ZYA Capital Pty Ltd</strong> will be <strong>recorded on my credit file</strong>.</li>
                            <li>The enquiry <strong>may be visible to other lenders</strong>.</li>
                            <li>The enquiry <strong>may affect my credit score or credit rating</strong>.</li>
                        </ol>
                        <p>Credit reporting bodies may include <strong>Equifax Australia, Illion Australia, Experian Australia</strong> or other credit reporting agencies operating in Australia.</p>
                    </div>

                    {{-- Section 4 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">4</span>
                            Verification Authority
                        </h3>
                        <p>I authorise <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> to verify any information contained in my application and to contact employers, accountants, brokers, references, financial institutions or other relevant parties for verification purposes.</p>
                    </div>

                    {{-- Section 5 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">5</span>
                            Repayment Capacity
                        </h3>
                        <p>I confirm that, to the best of my knowledge and belief, there are <strong>no foreseeable circumstances</strong> that may materially affect my ability to meet my obligations under the proposed loan agreement.</p>
                    </div>

                    {{-- Section 6 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">6</span>
                            Insurance and Security Obligations
                        </h3>
                        <p class="mb-2">Where the loan is secured by collateral (including vehicles or other assets), I agree that:</p>
                        <ol class="list-decimal list-inside space-y-1 pl-2">
                            <li>The secured asset must remain <strong>fully insured under a comprehensive insurance policy</strong> until the loan is repaid in full.</li>
                            <li><strong>ZYA Capital Pty Ltd may be noted as an interested party</strong> on the insurance policy where applicable.</li>
                            <li>I must not sell, transfer, encumber or otherwise dispose of the secured asset <strong>without prior written consent of ZYA Capital Pty Ltd</strong>.</li>
                        </ol>
                    </div>

                    {{-- Section 7 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">7</span>
                            Payment Obligations
                        </h3>
                        <p class="mb-2">I undertake to:</p>
                        <ol class="list-decimal list-inside space-y-1 pl-2">
                            <li>ensure sufficient funds are available prior to each repayment date;</li>
                            <li>comply with all repayment obligations under the loan agreement;</li>
                            <li>pay any applicable fees, default interest, recovery costs or enforcement expenses arising from late or failed payments.</li>
                        </ol>
                    </div>

                    {{-- Section 8 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">8</span>
                            Notification of Changes
                        </h3>
                        <p class="mb-2">I agree to promptly notify <strong>ZYA Capital Pty Ltd</strong> of any material changes including:</p>
                        <ul class="list-disc list-inside space-y-1 pl-2">
                            <li>change of residential address</li>
                            <li>change of phone number or email</li>
                            <li>relocation of secured assets</li>
                            <li>circumstances affecting repayment ability</li>
                        </ul>
                    </div>

                    {{-- Section 9 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">9</span>
                            Privacy Consent
                        </h3>
                        <p class="mb-2">I acknowledge that <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> may collect, store, use and disclose my personal information for purposes including:</p>
                        <ul class="list-disc list-inside space-y-1 pl-2 mb-2">
                            <li>assessing my loan application</li>
                            <li>verifying identity and financial information</li>
                            <li>administering and enforcing the loan agreement</li>
                            <li>complying with legal obligations</li>
                            <li>engaging service providers including lawyers, accountants, funding partners or debt collection agencies</li>
                        </ul>
                        <p>Such handling of personal information will comply with the <strong>Privacy Act 1988 (Cth)</strong>.</p>
                    </div>

                    {{-- Section 10 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">10</span>
                            No Reliance
                        </h3>
                        <p class="mb-2">I acknowledge that I have <strong>not relied on any representation, advice or statement</strong> made by <strong>ZYA Capital Pty Ltd</strong>, its directors, employees or agents when deciding to apply for this loan.</p>
                        <p>I have relied solely on <strong>my own independent judgment and assessment</strong>.</p>
                    </div>

                    {{-- Section 11 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">11</span>
                            Independent Advice
                        </h3>
                        <p class="mb-2">I acknowledge that I have been given the opportunity to <strong>seek independent legal, financial or accounting advice</strong> before signing this declaration and entering into any loan agreement.</p>
                        <p>I confirm that I have either obtained such advice or voluntarily chosen not to do so.</p>
                    </div>

                    {{-- Section 12 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">12</span>
                            Fraud and Misrepresentation
                        </h3>
                        <p class="mb-2">I acknowledge that providing <strong>false, misleading or fraudulent information</strong> may result in:</p>
                        <ul class="list-disc list-inside space-y-1 pl-2">
                            <li>cancellation of loan approval</li>
                            <li>immediate repayment of the loan</li>
                            <li>enforcement of security</li>
                            <li>legal proceedings for recovery of losses</li>
                        </ul>
                    </div>

                    {{-- Section 13 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">13</span>
                            Security Interest and PPSR Authorisation
                        </h3>
                        <p class="mb-2">I acknowledge and agree that <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> may register a <strong>security interest</strong> over any collateral provided for the loan under the <strong>Personal Property Securities Act 2009 (Cth)</strong>.</p>
                        <p class="mb-2">I consent to <strong>ZYA Capital Pty Ltd registering a financing statement on the Personal Property Securities Register (PPSR)</strong>.</p>
                        <p>I agree to do all things reasonably required to allow ZYA Capital Pty Ltd to <strong>perfect, maintain or enforce its security interest</strong>.</p>
                    </div>

                    {{-- Section 14 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">14</span>
                            Authority to Register Caveat or Mortgage
                        </h3>
                        <p class="mb-2">Where the loan is secured by real property, I acknowledge and agree that:</p>
                        <ol class="list-decimal list-inside space-y-1 pl-2">
                            <li><strong>ZYA Capital Pty Ltd may lodge and maintain a caveat</strong> over the property.</li>
                            <li>I may be required to grant <strong>a first mortgage or second mortgage</strong> in favour of <strong>ZYA Capital Pty Ltd</strong> as security for the loan.</li>
                            <li>I consent to ZYA Capital Pty Ltd taking any steps necessary to <strong>register, maintain or enforce such security</strong>.</li>
                        </ol>
                    </div>

                    {{-- Section 15 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">15</span>
                            Irrevocable Power of Attorney
                        </h3>
                        <p class="mb-2">I irrevocably appoint <strong>ZYA Capital Pty Ltd (ACN 695 692 052)</strong> and its authorised representatives as my <strong>attorney</strong> for the limited purpose of protecting and enforcing any security granted in connection with this loan.</p>
                        <p class="mb-2">This authority includes the power to:</p>
                        <ul class="list-disc list-inside space-y-1 pl-2 mb-2">
                            <li>register or maintain <strong>PPSR security interests</strong></li>
                            <li>lodge <strong>caveats</strong></li>
                            <li>prepare and register <strong>first or second mortgages</strong></li>
                            <li>sign documents required to perfect or enforce security</li>
                            <li>recover, repossess or transfer secured assets where enforcement becomes necessary</li>
                        </ul>
                        <p>This <strong>Power of Attorney is given as security</strong> for the obligations owed under the loan and remains <strong>irrevocable until the loan and all related obligations are fully discharged</strong>.</p>
                    </div>

                    {{-- Section 16 --}}
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-bold text-gray-900 mb-2 flex items-center text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-700 font-bold text-xs mr-2 flex-shrink-0">16</span>
                            Legal Effect of Declaration
                        </h3>
                        <p class="mb-2">I acknowledge that:</p>
                        <ol class="list-decimal list-inside space-y-2 pl-2">
                            <li>This declaration forms part of my loan application and any credit agreement with <strong>ZYA Capital Pty Ltd</strong>.</li>
                            <li><strong>ZYA Capital Pty Ltd may rely upon this declaration</strong> when deciding whether to provide credit.</li>
                            <li>Any false statement may result in <strong>termination of the loan agreement and enforcement action</strong>.</li>
                            <li>Everything after <strong>"I"</strong> in this application shall be deemed to include <strong>"I and my associated business entities in the application"</strong>, and references to <strong>"my"</strong> shall be interpreted as <strong>"my and my associated business entities in the application"</strong>. By signing this application, <strong>I and my associated business entities in the application acknowledge, agree and confirm</strong> that all representations, declarations, authorisations, and obligations contained in this application apply jointly and severally to <strong>me and any business entities associated with me</strong>, including but not limited to companies, trusts, partnerships, or other entities in which I hold a direct or indirect ownership, control, or beneficial interest.</li>
                        </ol>
                    </div>

                    {{-- Agreement box at the bottom of the declaration --}}
                    <div class="border-t-2 border-gray-300 pt-6 mt-4 bg-gray-100 -mx-6 px-6 pb-4 rounded-b-xl">
                        <p class="font-semibold text-gray-900 text-sm mb-1">Agreement</p>
                        <p class="text-sm text-gray-700">By signing below, I and my associated business entities in the application confirm that:</p>
                        <ul class="list-disc list-inside space-y-1 pl-2 mt-2 text-sm text-gray-700">
                            <li>I have <strong>read and understood this declaration in full</strong>.</li>
                            <li>I understand its <strong>legal implications</strong>.</li>
                            <li>I voluntarily agree to be bound by its terms.</li>
                        </ul>
                    </div>

                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- AGREEMENT CHECKBOX                                          --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="bg-gray-50 rounded-xl p-6 border-2 border-gray-200 mb-6" id="agreement-checkbox-wrap">
                <label class="flex items-start cursor-pointer">
                    <div class="flex items-center h-5 mt-1">
                        <input id="signature-agreement" name="signature_agreement" type="checkbox" required
                            class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                            aria-required="true">
                    </div>
                    <div class="ml-4">
                        <span class="text-sm font-bold text-gray-900">
                            I have read, understood and agree to the Commercial Loan Declaration, Consent and Security Authorisation above
                            <span class="text-red-600">*</span>
                        </span>
                        <p class="text-sm text-gray-600 mt-1 leading-relaxed">
                            I acknowledge that this electronic signature has the same legal effect as a handwritten signature,
                            and that the signature drawn above is my own. I agree to all 16 clauses of the declaration as set out above.
                        </p>
                    </div>
                </label>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- DRAWN SIGNATURE                                             --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="mb-6" id="signature-section">
                <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Draw your signature below
                    <span class="text-red-500 ml-1" aria-label="required">*</span>
                </label>
                <div class="relative border-2 border-gray-300 rounded-xl bg-white shadow-inner">
                    <canvas id="signature-canvas" width="800" height="200"
                            class="cursor-crosshair"
                            style="display: block; width: 100%; touch-action: none;"
                            aria-label="Signature drawing area"></canvas>
                    {{-- Overlay blocks the canvas until checkbox is ticked --}}
                    <div id="signature-overlay"
                         class="absolute inset-0 bg-gray-100 bg-opacity-80 rounded-xl flex items-center justify-center cursor-not-allowed"
                         aria-hidden="true">
                        <div class="text-center px-4">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm font-semibold text-gray-500">Please agree to the declaration above first</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <button type="button"
                            id="clear-signature-btn"
                            class="inline-flex items-center px-4 py-2 text-sm font-semibold text-red-600 hover:text-red-800 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 rounded-lg">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                        </svg>
                        Clear &amp; Redraw
                    </button>
                    <p class="text-xs text-gray-500">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Use your mouse or touch screen to sign
                    </p>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- APPLICANT NAME + DATE                                       --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="mt-6 mb-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Applicant Name --}}
                <div>
                    <label for="signatory-name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Applicant Name
                        <span class="text-red-500 ml-1" aria-label="required">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input type="text"
                               name="signatory_name"
                               id="signatory-name"
                               required
                               aria-required="true"
                               value="{{ old('signatory_name', $application->borrowerInformation?->borrower_name) }}"
                               placeholder="Full legal name"
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-indigo-50">
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Auto-filled from Borrower Information — edit if different.</p>
                </div>

                {{-- Date --}}
                <div>
                    <label for="signatory-date" class="block text-sm font-semibold text-gray-700 mb-2">
                        Date
                        <span class="text-red-500 ml-1" aria-label="required">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input type="date"
                               name="signatory_date"
                               id="signatory-date"
                               required
                               readonly
                               aria-required="true"
                               value="{{ old('signatory_date', now()->format('Y-m-d')) }}"
                               class="w-full pl-12 pr-4 py-3 border border-gray-200 rounded-xl bg-gray-100 text-gray-600 cursor-not-allowed transition-all">
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Automatically set to today's date.</p>
                </div>

            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- POSITION / TITLE                                            --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="mt-6 mb-6">
                <label for="signatory-position" class="block text-sm font-semibold text-gray-700 mb-2">
                    Position/Title (Optional)
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <input type="text" name="signatory_position" id="signatory-position"
                        class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        placeholder="e.g., Director, Owner, Authorised Signatory">
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- AGREEMENT CONFIRMATION — shown only after checkbox ticked   --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div id="agreement-confirmation" class="hidden mb-6">
                <div class="flex items-start bg-green-50 rounded-xl p-4 border border-green-200 gap-3">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-green-900 mb-1">Secure &amp; Encrypted</p>
                        <p class="text-xs text-green-700 leading-relaxed">
                            Your signature has been recorded. It will be submitted with your IP address
                            (<span id="confirm-ip" class="font-mono font-semibold">resolving…</span>)
                            and timestamped at
                            <span id="confirm-time" class="font-mono font-semibold">—</span>.
                            All data is encrypted and stored securely for compliance purposes.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    #signature-canvas:active {
        cursor: grabbing;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkbox   = document.getElementById('signature-agreement');
    const confirmIp  = document.getElementById('confirm-ip');
    const confirmTime = document.getElementById('confirm-time');
    const confirmation = document.getElementById('agreement-confirmation');
    const sigOverlay = document.getElementById('signature-overlay');
    const sigCanvas  = document.getElementById('signature-canvas');

    // Resolve IP in the background as soon as the page loads
    // (display only — the real IP is always captured server-side via request()->ip())
    let resolvedIp = 'your IP address';
    fetch('https://api.ipify.org?format=json')
        .then(r => r.json())
        .then(data => {
            resolvedIp = data.ip;
            if (confirmIp) confirmIp.textContent = resolvedIp;
        })
        .catch(() => {
            resolvedIp = 'your IP address';
            if (confirmIp) confirmIp.textContent = resolvedIp;
        });

    function formatDateTime(date) {
        return date.toLocaleDateString('en-AU', {
            day: '2-digit', month: 'short', year: 'numeric'
        }) + ' at ' + date.toLocaleTimeString('en-AU', {
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
        });
    }

    function hasSignature() {
        if (!sigCanvas) return false;
        const pixels = sigCanvas.getContext('2d').getImageData(0, 0, sigCanvas.width, sigCanvas.height).data;
        for (let i = 3; i < pixels.length; i += 4) {
            if (pixels[i] > 0) return true;
        }
        return false;
    }

    let signedAt = null;

    // Checkbox ticked → unlock the signature canvas
    checkbox.addEventListener('change', function () {
        if (this.checked) {
            if (sigOverlay) sigOverlay.classList.add('hidden');
        } else {
            if (sigOverlay) sigOverlay.classList.remove('hidden');
            signedAt = null;
            if (confirmation) confirmation.classList.add('hidden');
        }
        updateSubmitButton();
    });

    // Signature drawn → show confirmation with locked timestamp
    function updateConfirmation() {
        if (!confirmation) return;
        if (hasSignature()) {
            if (confirmTime) confirmTime.textContent = formatDateTime(signedAt);
            if (confirmIp)   confirmIp.textContent   = resolvedIp;
            confirmation.classList.remove('hidden');
        } else {
            confirmation.classList.add('hidden');
        }
    }

    if (sigCanvas) {
        sigCanvas.addEventListener('mouseup', function () {
            if (hasSignature()) signedAt = new Date();
            updateConfirmation();
            updateSubmitButton();
        });
        sigCanvas.addEventListener('touchend', function () {
            if (hasSignature()) signedAt = new Date();
            updateConfirmation();
            updateSubmitButton();
        });
    }

    // Clear resets signature state and hides confirmation, but keeps checkbox
    const clearBtn = document.getElementById('clear-signature-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            signedAt = null;
            updateConfirmation();
            updateSubmitButton();
        });
    }

    // ── Live-sync borrower name → signatory name ──────────────────────
    const signatoryName = document.getElementById('signatory-name');

    function syncBorrowerName() {
        const borrowerInput = document.getElementById('borrower-name');
        if (borrowerInput && signatoryName && !signatoryName.dataset.manuallyEdited) {
            signatoryName.value = borrowerInput.value;
        }
    }

    syncBorrowerName();

    const borrowerNameEl = document.getElementById('borrower-name');
    if (borrowerNameEl) {
        borrowerNameEl.addEventListener('input', syncBorrowerName);
    }

    if (signatoryName) {
        signatoryName.addEventListener('input', function () {
            this.dataset.manuallyEdited = 'true';
        });
    }

    // ── Submit button: shown when checkbox ticked + signature drawn ───
    const submitContainer = document.getElementById('submit-application-container');

    function updateSubmitButton() {
        if (!submitContainer) return;
        const allDone = checkbox.checked && hasSignature();
        if (allDone) {
            submitContainer.innerHTML = `
                <div class="mt-6 fade-in">
                    <button id="submit-application-btn"
                            type="submit"
                            class="w-full flex items-center justify-center gap-3 px-8 py-4 rounded-2xl
                                   text-white font-bold text-base tracking-wide transition-all duration-200
                                   focus:outline-none focus:ring-4 focus:ring-green-400 focus:ring-offset-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Submit Application
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </button>
                    <p class="text-center text-xs text-gray-400 mt-2">
                        You will not be able to edit your application after submission.
                    </p>
                </div>
            `;
        } else {
            submitContainer.innerHTML = '';
        }
    }

    updateSubmitButton();
});
</script>