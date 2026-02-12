<!-- Electronic Signature Section - Enhanced LoanFlow Design -->
<div class="bg-white rounded-2xl shadow-xl p-8 mb-6 border border-gray-200">
    <!-- Header -->
    <div class="flex items-center mb-6">
        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mr-4">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-2xl font-bold text-gray-900">Electronic Signature Required</h3>
            <p class="text-sm text-gray-600 mt-1">Sign your application to finalize submission</p>
        </div>
    </div>

    <!-- Declaration Box -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-l-4 border-indigo-500 rounded-xl p-6 mb-8 shadow-sm">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                    <svg class="h-5 w-5 text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h4 class="text-sm font-bold text-indigo-900 mb-2">Declaration</h4>
                <p class="text-sm text-indigo-800 leading-relaxed">
                    I declare that all information provided in this application is true and accurate to the best of my knowledge. I understand that providing false or misleading information may result in rejection or legal action.
                </p>
            </div>
        </div>
    </div>

    <!-- Signature Type Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Signature type">
                <button type="button" onclick="showSignatureType('typed')" id="tab-typed"
                    class="signature-tab group inline-flex items-center border-b-2 border-indigo-500 py-4 px-1 text-sm font-semibold text-indigo-600 transition-all"
                    aria-current="page">
                    <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Type Your Name
                </button>
                <button type="button" onclick="showSignatureType('drawn')" id="tab-drawn"
                    class="signature-tab group inline-flex items-center border-b-2 border-transparent py-4 px-1 text-sm font-semibold text-gray-500 hover:text-indigo-600 hover:border-indigo-300 transition-all">
                    <svg class="mr-2 h-5 w-5 text-gray-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Draw Signature
                </button>
            </nav>
        </div>
    </div>

    <!-- Typed Signature Panel -->
    <div id="signature-typed" class="signature-panel">
        <div class="mb-6">
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
                   style="font-family: 'Brush Script MT', cursive; font-size: 24px;">
            <p id="signature-hint" class="mt-2 text-xs text-gray-500">Enter your full legal name</p>
        </div>
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-dashed border-gray-300 rounded-xl p-10 text-center shadow-inner">
            <p class="text-xs uppercase tracking-wide text-gray-400 mb-3 font-semibold">Signature Preview</p>
            <p id="typed-signature-preview" style="font-family: 'Brush Script MT', cursive; font-size: 36px; color: #9CA3AF;">
                Your signature will appear here
            </p>
            <div class="mt-4 pt-4 border-t border-gray-300">
                <p class="text-xs text-gray-400" id="signature-date">
                    Signed on: <span class="font-semibold">{{ date('F j, Y \a\t g:i A') }}</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Drawn Signature Panel -->
    <div id="signature-drawn" class="signature-panel hidden">
        <div class="mb-6">
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
                <button type="button" onclick="clearCanvas()"
                        class="inline-flex items-center px-4 py-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
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
    <div class="mt-6">
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

    <!-- Hidden inputs -->
    <input type="hidden" name="signature" id="signature-data">
    <input type="hidden" name="signature_type" id="signature-type" value="typed">

    <!-- Agreement Checkbox -->
    <div class="mt-8 bg-gray-50 rounded-xl p-6 border-2 border-gray-200">
        <div class="flex items-start">
            <div class="flex items-center h-5 mt-1">
                <input id="signature-agreement" name="signature_agreement" type="checkbox" required
                       class="h-5 w-5 text-indigo-600 border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                       aria-required="true">
            </div>
            <label for="signature-agreement" class="ml-4 text-sm text-gray-700 cursor-pointer select-none">
                <span class="font-semibold text-gray-900">I confirm that I am authorised to sign this application</span> and that the signature above is my own.
                This electronic signature has the same legal effect as a handwritten signature.
                <span class="text-red-600 font-bold" aria-label="required">*</span>
            </label>
        </div>
    </div>

    <!-- Security Notice -->
    <div class="mt-6 flex items-start bg-green-50 rounded-xl p-4 border border-green-200">
        <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-green-900 mb-1">Secure & Encrypted</p>
            <p class="text-xs text-green-700">
                Your signature will be timestamped and recorded with your IP address (<span class="font-mono">{{ request()->ip() }}</span>) for security and compliance purposes. All data is encrypted and stored securely.
            </p>
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

<script>
    // Signature Type Switching
    function showSignatureType(type) {
        // Hide all panels
        document.querySelectorAll('.signature-panel').forEach(panel => panel.classList.add('hidden'));

        // Show selected panel
        document.getElementById('signature-' + type).classList.remove('hidden');

        // Update tabs
        document.querySelectorAll('.signature-tab').forEach(tab => {
            tab.classList.remove('border-indigo-500', 'text-indigo-600');
            tab.classList.add('border-transparent', 'text-gray-500');
            tab.removeAttribute('aria-current');
        });

        const activeTab = document.getElementById('tab-' + type);
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        activeTab.classList.add('border-indigo-500', 'text-indigo-600');
        activeTab.setAttribute('aria-current', 'page');

        // Set signature type
        document.getElementById('signature-type').value = type;

        // Clear signature data when switching
        document.getElementById('signature-data').value = '';
    }

    // Typed Signature
    const typedInput = document.getElementById('typed-signature-input');
    const typedPreview = document.getElementById('typed-signature-preview');

    typedInput?.addEventListener('input', function(e) {
        if (e.target.value.trim()) {
            typedPreview.textContent = e.target.value;
            typedPreview.style.color = '#1F2937';
            document.getElementById('signature-data').value = e.target.value;
        } else {
            typedPreview.textContent = 'Your signature will appear here';
            typedPreview.style.color = '#9CA3AF';
            document.getElementById('signature-data').value = '';
        }
    });

    // Drawn Signature
    const canvas = document.getElementById('signature-canvas');
    const ctx = canvas?.getContext('2d');
    let isDrawing = false;

    if (canvas && ctx) {
        // Initialize canvas properly
        ctx.strokeStyle = '#1F2937';
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        // Mouse events
        canvas.addEventListener('mousedown', function(e) {
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const x = (e.clientX - rect.left) * scaleX;
            const y = (e.clientY - rect.top) * scaleY;

            ctx.beginPath();
            ctx.moveTo(x, y);
        });

        canvas.addEventListener('mousemove', function(e) {
            if (!isDrawing) return;

            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const x = (e.clientX - rect.left) * scaleX;
            const y = (e.clientY - rect.top) * scaleY;

            ctx.lineTo(x, y);
            ctx.stroke();

            // Save after each stroke
            saveCanvasData();
        });

        canvas.addEventListener('mouseup', function() {
            if (isDrawing) {
                isDrawing = false;
                ctx.closePath();
            }
        });

        canvas.addEventListener('mouseleave', function() {
            if (isDrawing) {
                isDrawing = false;
                ctx.closePath();
            }
        });

        // Touch events
        canvas.addEventListener('touchstart', function(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const x = (touch.clientX - rect.left) * scaleX;
            const y = (touch.clientY - rect.top) * scaleY;

            isDrawing = true;
            ctx.beginPath();
            ctx.moveTo(x, y);
        });

        canvas.addEventListener('touchmove', function(e) {
            if (!isDrawing) return;
            e.preventDefault();

            const touch = e.touches[0];
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const x = (touch.clientX - rect.left) * scaleX;
            const y = (touch.clientY - rect.top) * scaleY;

            ctx.lineTo(x, y);
            ctx.stroke();

            // Save after each stroke
            saveCanvasData();
        });

        canvas.addEventListener('touchend', function() {
            if (isDrawing) {
                isDrawing = false;
                ctx.closePath();
            }
        });
    }

    function saveCanvasData() {
        if (canvas) {
            const imageData = canvas.toDataURL('image/png');
            document.getElementById('signature-data').value = imageData;
            console.log('Signature saved:', imageData.substring(0, 50) + '...');
        }
    }

    function clearCanvas() {
        if (ctx && canvas) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('signature-data').value = '';
            console.log('Canvas cleared');

            // Show feedback
            const clearBtn = event.target;
            const originalText = clearBtn.innerHTML;
            clearBtn.innerHTML = '<svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Cleared!';
            setTimeout(() => {
                clearBtn.innerHTML = originalText;
            }, 1500);
        }
    }

    // Form submission validation
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const signatureData = document.getElementById('signature-data').value;
                const agreementChecked = document.getElementById('signature-agreement').checked;

                if (!signatureData || signatureData.trim() === '') {
                    e.preventDefault();
                    alert('Please provide your signature before submitting.');

                    // Scroll to signature section
                    document.querySelector('#signature-typed, #signature-drawn').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return false;
                }

                if (!agreementChecked) {
                    e.preventDefault();
                    alert('Please confirm your signature agreement before submitting.');

                    // Scroll to checkbox
                    document.getElementById('signature-agreement').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    document.getElementById('signature-agreement').focus();
                    return false;
                }
            });
        }
    });
</script>
