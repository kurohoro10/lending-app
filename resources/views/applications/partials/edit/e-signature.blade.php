<!-- Electronic Signature Section - Required Before Submit -->
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
                    Electronic Signature Required
                </h3>
                <p class="text-red-100 text-sm mt-1">You must sign this application before submission</p>
            </div>
            <!-- Chevron Icon -->
            <svg id="e-signature-chevron" class="w-5 h-5 text-white transition-transform duration-200 transform rotate-180" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </div>
    </button>

    <div id="e-signature-content" 
         class="transition-all duration-300 ease-in-out p-6"
         aria-labelledby="e-signature-header">
        <div class="p-6">
            <!-- Warning if trying to submit without signature -->
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

            <!-- Declaration Text -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-6 mb-8 rounded-xl shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="h-5 w-5 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-bold text-blue-900 mb-2">Declaration</h4>
                        <p class="text-sm text-blue-800 leading-relaxed">
                            I declare that all information provided in this application is true and accurate to the best of my knowledge.
                            I understand that providing false or misleading information may result in rejection of this application or legal action.
                            I authorize the verification of this information and consent to credit checks as necessary.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Signature Type Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Signature type">
                        <button type="button" id="tab-typed"
                            class="signature-tab group inline-flex items-center border-b-2 border-indigo-500 py-4 px-1 text-sm font-semibold text-indigo-600 transition-all"
                            aria-current="page">
                            <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Type Your Name
                        </button>
                        <button type="button" id="tab-drawn"
                            class="signature-tab group inline-flex items-center border-b-2 border-transparent py-4 px-1 text-sm font-semibold text-gray-500 hover:text-indigo-600 hover:border-indigo-300 transition-all">
                            <svg class="mr-2 h-5 w-5 text-gray-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            Draw Signature
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Typed Signature -->
            <div id="signature-typed" class="signature-panel">
                <div class="mb-4">
                    <label for="typed-signature-input" class="block text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-indigo-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                        </svg>
                        Type your full name as signature
                        <span class="text-red-500 ml-1" aria-label="required">*</span>
                    </label>
                    <input type="text" id="typed-signature-input"
                        class="w-full px-5 py-4 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                        placeholder="John Smith"
                        aria-required="true"
                        aria-describedby="signature-hint"
                        style="font-family: 'Dancing Script', 'Brush Script MT', cursive; font-size: 24px;">
                    <p id="signature-hint" class="mt-2 text-xs text-gray-500">Enter your full legal name</p>
                </div>
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-dashed border-gray-300 rounded-xl p-10 text-center shadow-inner min-h-32 flex flex-col items-center justify-center">
                    <p class="text-xs uppercase tracking-wide text-gray-400 mb-3 font-semibold">Signature Preview</p>
                    <p id="typed-signature-preview" style="font-family: 'Dancing Script', 'Brush Script MT', cursive; font-size: 36px; color: #9CA3AF;">
                        Your signature will appear here
                    </p>
                    <div class="mt-4 pt-4 border-t border-gray-300 w-full">
                        <p class="text-xs text-gray-400">
                            Signed on: <span class="font-semibold">{{ date('F j, Y \a\t g:i A') }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Drawn Signature -->
            <div id="signature-drawn" class="signature-panel hidden">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Draw your signature below
                        <span class="text-red-500 ml-1" aria-label="required">*</span>
                    </label>
                    <div class="border-2 border-gray-300 rounded-xl bg-white shadow-inner">
                        <canvas id="signature-canvas" width="800" height="200" class="cursor-crosshair" style="display: block; width: 100%; touch-action: none;" aria-label="Signature drawing area"></canvas>
                    </div>
                    <div class="mt-3 flex items-center justify-between">
                        <button type="button"
                                id="clear-signature-btn"
                                class="inline-flex items-center px-4 py-2 text-sm font-semibold text-red-600 hover:text-red-800 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 rounded-lg">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                            </svg>
                            Clear & Redraw
                        </button>
                        <p class="text-xs text-gray-500">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Use your mouse or touch screen to sign
                        </p>
                    </div>
                </div>
            </div>

            <!-- Position/Title -->
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

            <!-- Agreement Checkbox -->
            <div class="bg-gray-50 rounded-xl p-6 border-2 border-gray-200 mb-6">
                <label class="flex items-start cursor-pointer">
                    <div class="flex items-center h-5 mt-1">
                        <input id="signature-agreement" name="signature_agreement" type="checkbox" required
                            class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                            aria-required="true">
                    </div>
                    <div class="ml-4">
                        <span class="text-sm font-bold text-gray-900">
                            I confirm that I am authorised to sign this application
                            <span class="text-red-600">*</span>
                        </span>
                        <p class="text-sm text-gray-600 mt-1 leading-relaxed">
                            I acknowledge that this electronic signature has the same legal effect as a handwritten signature,
                            and that the signature above is my own.
                        </p>
                    </div>
                </label>
            </div>

            <!-- Security Notice -->
            <div class="flex items-start bg-green-50 rounded-xl p-4 border border-green-200">
                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-green-900 mb-1">Secure & Encrypted</p>
                    <p class="text-xs text-green-700 leading-relaxed">
                        Your signature will be timestamped and recorded with your IP address (<span class="font-mono">{{ request()->ip() }}</span>) for security and compliance purposes. All data is encrypted and stored securely.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom signature fonts */
    @import url('https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&display=swap');

    #typed-signature-input,
    #typed-signature-preview {
        font-family: 'Dancing Script', 'Brush Script MT', cursive !important;
    }

    /* Canvas cursor */
    #signature-canvas:active {
        cursor: grabbing;
    }

    /* Smooth transitions */
    .signature-panel {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Tab hover effects */
    .signature-tab {
        position: relative;
    }

    .signature-tab::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(to right, #6366f1, #8b5cf6);
        transition: width 0.3s ease;
    }

    .signature-tab:hover::after {
        width: 100%;
    }
</style>

@push('scripts')
    @vite('resources/js/applications/e-signature.js')
@endpush
