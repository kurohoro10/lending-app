{{-- resources/views/admin/settings/partials/group.blade.php --}}
@php
    $groupFields = collect($fields)->filter(fn($f) => $f['group'] === $groupKey);
    $headingId   = "settings-{$groupKey}-heading";
    $isBasiq     = $groupKey === 'basiq';
@endphp

<section aria-labelledby="{{ $headingId }}"
         class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">

    {{-- Card header --}}
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center" aria-hidden="true">
            @if($icon === 'phone')
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            @elseif($icon === 'mail')
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            @elseif($icon === 'basiq')
                {{-- Bank-link / connection icon representing Basiq --}}
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10.172 13.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
            @elseif($icon === 'bank')
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
            @else
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            @endif
        </div>
        <div>
            <h3 id="{{ $headingId }}" class="text-sm font-semibold text-gray-900">{{ $groupLabel }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ $groupHint }}</p>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST"
          action="{{ route('admin.settings.update', $groupKey) }}"
          aria-labelledby="{{ $headingId }}"
          @if($isBasiq) id="form-basiq" @endif
          novalidate>
        @csrf
        @method('PATCH')

        <div class="px-6 py-5 space-y-5">
            @foreach($groupFields as $key => $field)
                @php $fieldId = "field-{$key}"; $hintId = "hint-{$key}"; @endphp

                <div>
                    <label for="{{ $fieldId }}"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $field['label'] }}
                        @if($field['is_secret'])
                            <span class="ml-1 text-xs font-normal text-gray-400" aria-label="sensitive value">(secret)</span>
                        @endif
                    </label>

                    @if($field['type'] === 'select')
                        <select id="{{ $fieldId }}"
                                name="{{ $key }}"
                                class="block w-full sm:max-w-xs rounded-md border-gray-300 shadow-sm text-sm
                                       focus:ring-indigo-500 focus:border-indigo-500"
                                aria-describedby="{{ $hintId }}">
                            @foreach($field['options'] as $val => $label)
                                <option value="{{ $val }}"
                                    {{ ($settings[$key] ?? '') === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                    @elseif($field['type'] === 'textarea')
                        <div class="relative">
                            <textarea id="{{ $fieldId }}"
                                      name="{{ $key }}"
                                      rows="10"
                                      spellcheck="false"
                                      autocomplete="off"
                                      class="block w-full rounded-md border-gray-300 shadow-sm text-sm font-mono
                                             focus:ring-indigo-500 focus:border-indigo-500
                                             @error($key) border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                      aria-describedby="{{ $hintId }}{{ $errors->has($key) ? " error-{$key}" : '' }}"
                                      placeholder='{ "internal_field": "provider.response.path" }'>{{ $settings[$key] ?? '' }}</textarea>

                            <button type="button"
                                    data-json-format="{{ $fieldId }}"
                                    class="mt-1.5 inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800
                                           focus:outline-none focus:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 6h16M4 12h8m-8 6h16"/>
                                </svg>
                                Format JSON
                            </button>
                        </div>

                    @else
                        <div class="relative sm:max-w-md">
                            <input type="{{ $field['type'] === 'password' ? 'password' : $field['type'] }}"
                                   id="{{ $fieldId }}"
                                   name="{{ $key }}"
                                   value="{{ $field['is_secret'] ? '' : ($settings[$key] ?? '') }}"
                                   placeholder="{{ $field['is_secret'] && !empty($settings[$key]) ? '••••••••  (saved)' : '' }}"
                                   autocomplete="{{ $field['type'] === 'password' ? 'new-password' : 'off' }}"
                                   spellcheck="false"
                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                          focus:ring-indigo-500 focus:border-indigo-500
                                          @error($key) border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                   aria-describedby="{{ $hintId }}{{ $errors->has($key) ? " error-{$key}" : '' }}"
                                   @if($field['type'] === 'password') aria-label="{{ $field['label'] }} — leave blank to keep existing value" @endif>

                            @if($field['type'] === 'password')
                                <button type="button"
                                        class="settings-toggle-secret absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-r-md"
                                        data-target="{{ $fieldId }}"
                                        aria-label="Toggle {{ $field['label'] }} visibility"
                                        aria-pressed="false">
                                    <svg class="icon-eye w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg class="icon-eye-off hidden w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @endif

                    @error($key)
                        <p id="error-{{ $key }}" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                    @enderror

                    <p id="{{ $hintId }}" class="mt-1 text-xs text-gray-400">{{ $field['hint'] }}</p>
                </div>
            @endforeach

            {{-- ── Basiq: Test Connection ───────────────────────────────────── --}}
            @if($isBasiq)
                <div class="pt-2 border-t border-gray-100">
                    <div class="flex items-center gap-3 flex-wrap">
                        <button type="button"
                                id="btn-basiq-test"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-md
                                       text-sm font-medium text-gray-700 hover:bg-gray-50
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                       transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="basiq-test-spinner"
                                 class="hidden animate-spin w-4 h-4 text-indigo-600"
                                 fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <svg id="basiq-test-icon"
                                 class="w-4 h-4 text-gray-500"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Test Connection
                        </button>

                        <span id="basiq-test-result"
                              role="status"
                              aria-live="polite"
                              class="hidden items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium">
                        </span>
                    </div>

                    <p id="basiq-test-message" class="hidden mt-2 text-xs text-gray-500"></p>

                    {{-- Two-step progress indicator --}}
                    <ol id="basiq-test-steps"
                        class="hidden mt-3 space-y-1 text-xs text-gray-400"
                        aria-label="Test steps">
                        <li id="step-token"     class="flex items-center gap-1.5">
                            <span class="step-dot w-1.5 h-1.5 rounded-full bg-gray-300 flex-shrink-0"></span>
                            Obtain access token from /token
                        </li>
                        <li id="step-institutions" class="flex items-center gap-1.5">
                            <span class="step-dot w-1.5 h-1.5 rounded-full bg-gray-300 flex-shrink-0"></span>
                            Verify AU institutions endpoint
                        </li>
                    </ol>
                </div>
            @endif
        </div>

        {{-- Footer / save --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                Changes take effect immediately — no deploy required.
            </p>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                           font-semibold text-xs text-white uppercase tracking-widest
                           hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                           transition">
                Save {{ $groupLabel }}
            </button>
        </div>

    </form>
</section>

@once
@push('scripts')
<script>
// ── JSON textarea formatter ───────────────────────────────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-json-format]');
    if (!btn) return;
    const textarea = document.getElementById(btn.dataset.jsonFormat);
    if (!textarea) return;
    try {
        textarea.value = JSON.stringify(JSON.parse(textarea.value), null, 4);
        textarea.classList.remove('border-red-300');
    } catch (err) {
        textarea.classList.add('border-red-300');
        alert('Invalid JSON: ' + err.message);
    }
});

// ── Password visibility toggle ────────────────────────────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.settings-toggle-secret');
    if (!btn) return;
    const input  = document.getElementById(btn.dataset.target);
    const eyeOn  = btn.querySelector('.icon-eye');
    const eyeOff = btn.querySelector('.icon-eye-off');
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    eyeOn.classList.toggle('hidden', isHidden);
    eyeOff.classList.toggle('hidden', !isHidden);
    btn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
});

// ── Basiq test connection ─────────────────────────────────────────────────────
(function () {
    const btn = document.getElementById('btn-basiq-test');
    if (!btn) return;

    const spinner      = document.getElementById('basiq-test-spinner');
    const icon         = document.getElementById('basiq-test-icon');
    const resultBadge  = document.getElementById('basiq-test-result');
    const messageLine  = document.getElementById('basiq-test-message');
    const stepsEl      = document.getElementById('basiq-test-steps');
    const stepToken    = document.getElementById('step-token');
    const stepInst     = document.getElementById('step-institutions');

    function setStep(el, state) {
        // state: 'pending' | 'active' | 'ok' | 'fail'
        const dot = el.querySelector('.step-dot');
        dot.className = 'step-dot w-1.5 h-1.5 rounded-full flex-shrink-0 ' + {
            pending : 'bg-gray-300',
            active  : 'bg-indigo-400 animate-pulse',
            ok      : 'bg-green-500',
            fail    : 'bg-red-500',
        }[state];
    }

    btn.addEventListener('click', async function () {
        const form    = document.getElementById('form-basiq');
        const apiKey  = form.querySelector('[name="settings[basiq_api_key]"]');
        const baseUrl = form.querySelector('[name="basiq_base_url"]')?.value ?? '';
        const env     = form.querySelector('[name="basiq_env"]')?.value       ?? 'sandbox';

        // Reset
        resultBadge.className = 'hidden';
        messageLine.className = 'hidden mt-2 text-xs text-gray-500';
        messageLine.textContent = '';
        stepsEl.classList.remove('hidden');
        setStep(stepToken, 'pending');
        setStep(stepInst,  'pending');

        btn.disabled = true;
        spinner.classList.remove('hidden');
        icon.classList.add('hidden');

        // Animate step 1 active
        setStep(stepToken, 'active');

        try {
            const res = await fetch('{{ route("admin.settings.basiq.test-connection") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ api_key: apiKey, base_url: baseUrl, env }),
            });

            const data = await res.json();

            if (data.success) {
                // Both steps passed (server does them sequentially)
                setStep(stepToken, 'ok');
                setStep(stepInst,  'ok');

                resultBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                resultBadge.innerHTML = `
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Connected`;
            } else {
                // Determine which step failed based on the error message content
                const tokenFailed = data.message && data.message.toLowerCase().includes('auth');
                setStep(stepToken, tokenFailed ? 'fail' : 'ok');
                setStep(stepInst,  tokenFailed ? 'pending' : 'fail');

                resultBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
                resultBadge.innerHTML = `
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Failed`;
            }

            if (data.message) {
                messageLine.textContent = data.message;
                messageLine.classList.remove('hidden');
            }

        } catch (err) {
            setStep(stepToken, 'fail');
            setStep(stepInst,  'pending');
            resultBadge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
            resultBadge.textContent = 'Network error';
            messageLine.textContent = err.message;
            messageLine.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            spinner.classList.add('hidden');
            icon.classList.remove('hidden');
        }
    });
})();
</script>
@endpush
@endonce
