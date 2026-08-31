{{-- resources/views/applications/document-signing.blade.php — client review + sign --}}
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Document Signing — {{ $application->application_number }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Please review the document below carefully, then sign at the bottom of the page.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Original document, embedded for review --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Document</h3>
                </div>
                <iframe src="{{ $fileUrl }}" class="w-full" style="height: 70vh;" title="Document to sign"></iframe>
            </div>

            {{-- Signing zone --}}
            <form method="POST"
                  action="{{ route('applications.document-signing.sign', $application) }}"
                  id="document-signing-sign-form">
                @csrf

                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Your Signature <span class="text-red-500">*</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Sign in the box below using your mouse or finger. This signature will be applied to every page of the document.
                        </p>
                    </div>
                    <div class="p-6 space-y-5">

                        <div>
                            <canvas id="signature-pad"
                                    class="w-full border border-gray-300 rounded-lg bg-white touch-none"
                                    style="height:160px"></canvas>
                            <button type="button" id="clear-signature"
                                    class="mt-2 text-xs px-3 py-1.5 border border-gray-300 rounded-md text-gray-600 hover:bg-gray-50">
                                Clear
                            </button>
                            <input type="hidden" name="signature" id="signature-input">
                            @error('signature')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input type="text" readonly value="{{ now()->format('d M Y') }}"
                                   class="w-48 border-gray-200 bg-gray-50 rounded-md shadow-sm text-sm text-gray-500">
                        </div>

                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="declaration" value="1" required
                                   class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">
                                I confirm that I have read and reviewed the document above, and I agree to have
                                my signature applied to it. I acknowledge this constitutes electronic execution
                                of the document.
                            </span>
                        </label>
                        @error('declaration')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

                        <div class="flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white
                                           text-sm font-semibold rounded-md hover:bg-indigo-700 transition
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Sign Document
                            </button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <script>
        (function () {
            const canvas = document.getElementById('signature-pad');
            const input  = document.getElementById('signature-input');
            const form   = document.getElementById('document-signing-sign-form');

            const rect = canvas.getBoundingClientRect();
            const dpr  = window.devicePixelRatio || 1;
            canvas.width  = (rect.width || canvas.offsetWidth) * dpr;
            canvas.height = (rect.height || canvas.offsetHeight) * dpr;

            const ctx = canvas.getContext('2d');
            ctx.scale(dpr, dpr);
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
            }

            function stop() {
                if (!drawing) return;
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

            document.getElementById('clear-signature').addEventListener('click', () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                input.value = '';
            });

            form.addEventListener('submit', (e) => {
                if (!input.value) {
                    e.preventDefault();
                    alert('Please sign the document before submitting.');
                }
            });
        })();
    </script>
</x-app-layout>
