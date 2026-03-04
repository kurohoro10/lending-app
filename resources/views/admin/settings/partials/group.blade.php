{{-- resources/views/admin/settings/partials/group.blade.php --}}
@php
    $groupFields = collect($fields)->filter(fn($f) => $f['group'] === $groupKey);
    $headingId   = "settings-{$groupKey}-heading";
    $isBasiq       = $groupKey === 'basiq';
    $isCreditSense = $groupKey === 'creditsense';
    $borderless    = $borderless ?? false;
@endphp

@if(!$borderless)
<section aria-labelledby="{{ $headingId }}"
         class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
@endif

    {{-- Card header — hidden when borderless (parent card owns the chrome) --}}
    @if(!$borderless)
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center" aria-hidden="true">
            @include('admin.settings.partials.icon', ['icon' => $icon])
        </div>
        <div>
            <h3 id="{{ $headingId }}" class="text-sm font-semibold text-gray-900">{{ $groupLabel }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ $groupHint }}</p>
        </div>
    </div>
    @else
    {{-- Borderless: still need a visible heading for screen readers --}}
    <h3 id="{{ $headingId }}" class="sr-only">{{ $groupLabel }}</h3>
    @endif

    {{-- Form --}}
    <form method="POST"
          action="{{ route('admin.settings.update', $groupKey) }}"
          aria-labelledby="{{ $headingId }}"
          @if($isBasiq) id="form-basiq" @endif
          @if($isCreditSense) id="form-creditsense" @endif
          novalidate>
        @csrf
        @method('PATCH')

        @if($borderless)
        {{-- Borderless hint shown inside form instead of card header --}}
        <div class="px-6 pt-5 pb-1">
            <p class="text-xs text-gray-400">{{ $groupHint }}</p>
        </div>
        @endif

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
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
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
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
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

            {{-- ── Basiq: Test Connection ───────────────────────────────── --}}
            @if($isBasiq)
                <div class="pt-2 border-t border-gray-100">
                    <div class="flex items-center gap-3 flex-wrap">
                        <button type="button"
                                id="btn-basiq-test"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-md
                                       text-sm font-medium text-gray-700 hover:bg-gray-50
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                       transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="basiq-test-spinner" class="hidden animate-spin w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <svg id="basiq-test-icon" class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Test Connection
                        </button>
                        <span id="basiq-test-result" role="status" aria-live="polite"
                              class="hidden items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium"></span>
                    </div>
                    <p id="basiq-test-message" class="hidden mt-2 text-xs text-gray-500"></p>
                    <ol id="basiq-test-steps" class="hidden mt-3 space-y-1 text-xs text-gray-400" aria-label="Test steps">
                        <li id="step-token" class="flex items-center gap-1.5">
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

            {{-- ── CreditSense: Test Connection ─────────────────────────── --}}
            @if($isCreditSense)
                <div class="pt-2 border-t border-gray-100">
                    <div class="flex items-center gap-3 flex-wrap">
                        <button type="button"
                                id="btn-cs-test"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-md
                                       text-sm font-medium text-gray-700 hover:bg-gray-50
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                                       transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="cs-test-spinner" class="hidden animate-spin w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <svg id="cs-test-icon" class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Test Connection
                        </button>
                        <span id="cs-test-result" role="status" aria-live="polite"
                              class="hidden items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium"></span>
                    </div>
                    <p id="cs-test-message" class="hidden mt-2 text-xs text-gray-500"></p>
                    <ol id="cs-test-steps" class="hidden mt-3 space-y-1 text-xs text-gray-400" aria-label="CreditSense test steps">
                        <li id="cs-step-auth" class="flex items-center gap-1.5">
                            <span class="step-dot w-1.5 h-1.5 rounded-full bg-gray-300 flex-shrink-0"></span>
                            Authenticate with client code + API key
                        </li>
                        <li id="cs-step-api" class="flex items-center gap-1.5">
                            <span class="step-dot w-1.5 h-1.5 rounded-full bg-gray-300 flex-shrink-0"></span>
                            Verify /v2/applications endpoint
                        </li>
                    </ol>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 {{ $borderless ? 'border-t border-gray-100' : 'bg-gray-50 border-t border-gray-100' }} flex items-center justify-between">
            <p class="text-xs text-gray-400">Changes take effect immediately — no deploy required.</p>
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                           font-semibold text-xs text-white uppercase tracking-widest
                           hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                           transition">
                Save {{ $groupLabel }}
            </button>
        </div>
    </form>

@if(!$borderless)
</section>
@endif

@once
@push('scripts')
<script>
// ── JSON formatter ────────────────────────────────────────────────────────────
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-json-format]');
    if (!btn) return;
    const ta = document.getElementById(btn.dataset.jsonFormat);
    if (!ta) return;
    try {
        ta.value = JSON.stringify(JSON.parse(ta.value), null, 4);
        ta.classList.remove('border-red-300');
    } catch (err) {
        ta.classList.add('border-red-300');
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

// ── Shared test-connection helper ─────────────────────────────────────────────
function initTestConnection({ btnId, spinnerId, iconId, resultId, messageId, stepsId, steps, route, getPayload }) {
    const btn     = document.getElementById(btnId);
    if (!btn) return;
    const spinner = document.getElementById(spinnerId);
    const icon    = document.getElementById(iconId);
    const result  = document.getElementById(resultId);
    const message = document.getElementById(messageId);
    const stepsEl = document.getElementById(stepsId);

    function setStep(elId, state) {
        const el  = document.getElementById(elId);
        if (!el) return;
        const dot = el.querySelector('.step-dot');
        dot.className = 'step-dot w-1.5 h-1.5 rounded-full flex-shrink-0 ' + {
            pending: 'bg-gray-300',
            active:  'bg-indigo-400 animate-pulse',
            ok:      'bg-green-500',
            fail:    'bg-red-500',
        }[state];
    }

    btn.addEventListener('click', async function () {
        result.className  = 'hidden';
        message.className = 'hidden mt-2 text-xs text-gray-500';
        message.textContent = '';
        stepsEl.classList.remove('hidden');
        steps.forEach(s => setStep(s, 'pending'));

        btn.disabled = true;
        spinner.classList.remove('hidden');
        icon.classList.add('hidden');
        setStep(steps[0], 'active');

        try {
            const res  = await fetch(route, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':       'application/json',
                },
                body: JSON.stringify(getPayload()),
            });
            const data = await res.json();

            if (data.success) {
                steps.forEach(s => setStep(s, 'ok'));
                result.className  = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                result.innerHTML  = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Connected';
            } else {
                const firstFailed = data.message?.toLowerCase().includes('auth') ? 0 : 1;
                steps.forEach((s, i) => setStep(s, i < firstFailed ? 'ok' : i === firstFailed ? 'fail' : 'pending'));
                result.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
                result.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Failed';
            }
            if (data.message) {
                message.textContent = data.message;
                message.classList.remove('hidden');
            }
        } catch (err) {
            setStep(steps[0], 'fail');
            result.className    = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
            result.textContent  = 'Network error';
            message.textContent = err.message;
            message.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            spinner.classList.add('hidden');
            icon.classList.remove('hidden');
        }
    });
}

// ── Basiq test ────────────────────────────────────────────────────────────────
initTestConnection({
    btnId:     'btn-basiq-test',
    spinnerId: 'basiq-test-spinner',
    iconId:    'basiq-test-icon',
    resultId:  'basiq-test-result',
    messageId: 'basiq-test-message',
    stepsId:   'basiq-test-steps',
    steps:     ['step-token', 'step-institutions'],
    route:     '{{ route("admin.settings.basiq.test-connection") }}',
    getPayload: () => {
        const form = document.getElementById('form-basiq');
        return {
            api_key:  form?.querySelector('[name="basiq_api_key"]')?.value  ?? '',
            base_url: form?.querySelector('[name="basiq_base_url"]')?.value ?? '',
            env:      form?.querySelector('[name="basiq_env"]')?.value      ?? 'sandbox',
        };
    },
});

// ── CreditSense test ──────────────────────────────────────────────────────────
initTestConnection({
    btnId:     'btn-cs-test',
    spinnerId: 'cs-test-spinner',
    iconId:    'cs-test-icon',
    resultId:  'cs-test-result',
    messageId: 'cs-test-message',
    stepsId:   'cs-test-steps',
    steps:     ['cs-step-auth', 'cs-step-api'],
    route:     '{{ route("admin.settings.creditsense.test-connection") }}',
    getPayload: () => {
        const form = document.getElementById('form-creditsense');
        return {
            api_key:      form?.querySelector('[name="creditsense_api_key"]')?.value      ?? '',
            client_code:  form?.querySelector('[name="creditsense_client_code"]')?.value  ?? '',
            base_url:     form?.querySelector('[name="creditsense_base_url"]')?.value     ?? '',
            env:          form?.querySelector('[name="creditsense_env"]')?.value           ?? 'sandbox',
        };
    },
});
</script>
@endpush
@endonce
