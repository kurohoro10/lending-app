{{-- resources/views/applications/business-declaration.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Business Purpose Declaration
        </h2>
        <p class="text-sm text-gray-500 mt-1">Application {{ $application->application_number }}</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Declaration Document --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 text-center">
                    <h1 class="text-lg font-bold text-gray-900 uppercase">Business Purpose Declaration</h1>
                    <p class="text-xs text-gray-500 mt-1">(Individual borrowers only)</p>
                </div>

                <div class="p-6 space-y-5 text-sm text-gray-700">

                    <p class="italic text-gray-600">
                        <strong>Instructions to Borrower:</strong> Only sign this declaration if the loan funds
                        will be used wholly or predominantly for business and/or investment purposes which is
                        not investment in residential property.
                    </p>

                    <p>This declaration applies to the following loan advance:</p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Borrower Name</p>
                            <p class="text-sm text-gray-900 mt-0.5">
                                {{ $declarationData['borrower_name'] }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Loan Purpose</p>
                            <p class="text-sm text-gray-900 mt-0.5">
                                {{ $declarationData['loan_purpose'] ?: '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Amount</p>
                            <p class="text-sm text-gray-900 mt-0.5">
                                {{ $declarationData['loan_amount_display'] }}
                            </p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-5">
                        <h2 class="text-sm font-bold text-gray-900 mb-3">Declaration of Purpose</h2>
                        <p>
                            I/We declare that the credit to be provided to me/us by <strong>AHA Money</strong>
                            is to be applied wholly or predominantly for:
                        </p>
                        <ul class="mt-2 ml-6 list-disc space-y-1">
                            <li>business purposes; or</li>
                            <li>investment purposes other than investment in residential property.</li>
                        </ul>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <p class="text-xs text-amber-800 font-semibold uppercase mb-1">Important Notice</p>
                        <p class="text-xs text-amber-700">
                            You should only sign this declaration if this loan is wholly or predominantly for
                            business purposes or investment purposes other than investment in residential property.
                            By signing this declaration you may lose your protection under the
                            <strong>National Credit Code</strong>.
                        </p>
                    </div>

                </div>
            </div>

            {{-- Signature Form --}}
            <form method="POST"
                  action="{{ route('applications.business-declaration.sign', $application) }}"
                  id="declaration-form">
                @csrf

                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900">Sign the Declaration</h3>
                    </div>
                    <div class="p-6 space-y-6">

                        {{-- Date --}}
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Date</p>
                            <p class="text-sm text-gray-900">{{ now()->format('d/m/Y') }}</p>
                        </div>

                        {{-- Signature Canvas --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Signature <span class="text-red-500">*</span>
                            </label>
                            <input type="hidden" name="signature" id="signature_input">
                            <div class="space-y-3">
                                <canvas id="sig-canvas"
                                        class="w-full border border-gray-300 rounded-lg bg-white touch-none"
                                        style="height: 160px"></canvas>
                                <button type="button" onclick="clearSig()"
                                        class="text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                    Clear
                                </button>
                            </div>
                            @error('signature')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Declaration Checkbox --}}
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="declaration" id="declaration" value="1"
                                   class="mt-0.5 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                   required>
                            <label for="declaration" class="text-sm text-gray-700">
                                I/We confirm that the information above is true and correct, and I/we understand
                                that by signing this declaration I/we may lose protection under the National Credit Code.
                            </label>
                        </div>
                        @error('declaration')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                        {{-- Submit --}}
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white
                                           text-sm font-semibold rounded-md hover:bg-indigo-700 transition
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Sign & Submit Declaration
                            </button>
                        </div>

                    </div>
                </div>

            </form>

        </div>
    </div>

    <script>
        const canvas = document.getElementById('sig-canvas');
        const ctx = canvas.getContext('2d');

        // Fit canvas to rendered size
        const rect = canvas.getBoundingClientRect();
        canvas.width  = rect.width  || canvas.offsetWidth;
        canvas.height = rect.height || canvas.offsetHeight;

        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth   = 2;
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';

        let drawing = false, lastX = 0, lastY = 0;

        function pos(e) {
            const r = canvas.getBoundingClientRect();
            const s = e.touches ? e.touches[0] : e;
            return [s.clientX - r.left, s.clientY - r.top];
        }

        canvas.addEventListener('mousedown',  e => { drawing = true; [lastX, lastY] = pos(e); });
        canvas.addEventListener('mousemove',  e => {
            if (!drawing) return;
            const [x, y] = pos(e);
            ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(x, y); ctx.stroke();
            [lastX, lastY] = [x, y];
            document.getElementById('signature_input').value = canvas.toDataURL();
        });
        canvas.addEventListener('mouseup',    () => drawing = false);
        canvas.addEventListener('mouseleave', () => drawing = false);
        canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing = true; [lastX, lastY] = pos(e); }, { passive: false });
        canvas.addEventListener('touchmove',  e => {
            e.preventDefault();
            if (!drawing) return;
            const [x, y] = pos(e);
            ctx.beginPath(); ctx.moveTo(lastX, lastY); ctx.lineTo(x, y); ctx.stroke();
            [lastX, lastY] = [x, y];
            document.getElementById('signature_input').value = canvas.toDataURL();
        }, { passive: false });
        canvas.addEventListener('touchend', () => drawing = false);

        function clearSig() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            document.getElementById('signature_input').value = '';
        }
    </script>
</x-app-layout>