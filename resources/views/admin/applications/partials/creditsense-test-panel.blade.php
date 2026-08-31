{{--
    resources/views/admin/applications/partials/creditsense-test-panel.blade.php
    
    Drop this inside your admin application show view, wrapped in @if(config('app.debug'))
    so it never shows in production.
    
    Usage: @include('admin.applications.partials.creditsense-test-panel', ['application' => $application])
--}}

@if(config('app.debug'))
<div class="mt-6 border-2 border-dashed border-amber-300 rounded-xl overflow-hidden"
     x-data="csTestPanel()" x-init="init()">

    {{-- Header --}}
    <div class="bg-amber-50 px-5 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span class="text-xs font-bold text-amber-800 uppercase tracking-wide">CreditSense Debug Panel</span>
            <span class="text-xs text-amber-600">(debug mode only — never shown in production)</span>
        </div>
        <button @click="open = !open" class="text-xs text-amber-700 underline focus:outline-none">
            <span x-text="open ? 'Collapse' : 'Expand'">Expand</span>
        </button>
    </div>

    <div x-show="open" x-cloak class="bg-white p-5 space-y-5">

        {{-- Current DB state --}}
        <div>
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Current database state</h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach([
                    ['label' => 'credit_sense_app_id',            'value' => $application->credit_sense_app_id],
                    ['label' => 'credit_sense_completed_at',       'value' => $application->credit_sense_completed_at?->format('d M Y H:i')],
                    ['label' => 'credit_sense_report_received_at', 'value' => $application->credit_sense_report_received_at?->format('d M Y H:i')],
                    ['label' => 'bank_api_provider_name',          'value' => $application->bank_api_provider_name],
                ] as $field)
                <div class="rounded-lg border border-gray-200 p-3">
                    <p class="text-xs text-gray-400 truncate mb-1">{{ $field['label'] }}</p>
                    <p class="text-xs font-mono font-semibold {{ $field['value'] ? 'text-green-700' : 'text-gray-400' }}">
                        {{ $field['value'] ?? 'null' }}
                    </p>
                </div>
                @endforeach
            </div>

            {{-- Report preview --}}
            @if($application->credit_sense_report)
            <div class="mt-3 rounded-lg border border-green-200 bg-green-50 p-3">
                <p class="text-xs font-semibold text-green-800 mb-1">✓ Report data saved</p>
                <pre class="text-xs text-green-700 overflow-x-auto max-h-32">{{ json_encode($application->credit_sense_report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
            @else
            <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3">
                <p class="text-xs font-semibold text-red-800">✗ No report data — credit_sense_report is null</p>
            </div>
            @endif
        </div>

        <hr class="border-gray-100">

        {{-- Step 1: Simulate complete() --}}
        <div>
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Step 1 — Simulate iframe completion</h4>
            <p class="text-xs text-gray-400 mb-3">Fires POST <code class="bg-gray-100 px-1 rounded">{{ route('creditsense.complete', $application) }}</code></p>
            <div class="flex items-center gap-3 flex-wrap">
                <input type="text"
                       x-model="fakeAppId"
                       placeholder="Fake CS App ID e.g. 99999"
                       class="border border-gray-300 rounded-lg text-xs px-3 py-2 w-48 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <button @click="simulateComplete()"
                        :disabled="loading.complete"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 disabled:opacity-50 transition focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <svg x-show="loading.complete" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Simulate complete()
                </button>
                <span x-show="results.complete"
                      :class="results.complete?.ok ? 'text-green-700 bg-green-50 border-green-200' : 'text-red-700 bg-red-50 border-red-200'"
                      class="text-xs px-3 py-1.5 rounded-full border font-medium"
                      x-text="results.complete?.msg"></span>
            </div>
        </div>

        {{-- Step 2: Simulate webhook --}}
        <div>
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Step 2 — Simulate CreditSense webhook</h4>
            <p class="text-xs text-gray-400 mb-3">Fires POST <code class="bg-gray-100 px-1 rounded">/api/webhooks/creditsense</code> — mimics what CreditSense sends after bank connection.</p>
            <div class="flex items-center gap-3 flex-wrap">
                <button @click="simulateWebhook()"
                        :disabled="loading.webhook"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg x-show="loading.webhook" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Fire fake webhook
                </button>
                <span x-show="results.webhook"
                      :class="results.webhook?.ok ? 'text-green-700 bg-green-50 border-green-200' : 'text-red-700 bg-red-50 border-red-200'"
                      class="text-xs px-3 py-1.5 rounded-full border font-medium"
                      x-text="results.webhook?.msg"></span>
            </div>
            <div x-show="results.webhook?.body" class="mt-2">
                <pre class="text-xs bg-gray-50 border border-gray-200 rounded p-2 overflow-x-auto max-h-24" x-text="results.webhook?.body"></pre>
            </div>
        </div>

        {{-- Step 3: Fetch report manually --}}
        <div>
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Step 3 — Manual fetchReport()</h4>
            <p class="text-xs text-gray-400 mb-3">
                Fires POST <code class="bg-gray-100 px-1 rounded">{{ route('admin.creditsense.fetchReport', $application) }}</code>
                using <code class="bg-gray-100 px-1 rounded">credit_sense_app_id = {{ $application->credit_sense_app_id ?? 'null (will fail)' }}</code>
            </p>
            <div class="flex items-center gap-3 flex-wrap">
                <button @click="fetchReport()"
                        :disabled="loading.fetch"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 text-white text-xs font-semibold rounded-lg hover:bg-gray-800 disabled:opacity-50 transition focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <svg x-show="loading.fetch" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Run fetchReport()
                </button>
                <span x-show="results.fetch"
                      :class="results.fetch?.ok ? 'text-green-700 bg-green-50 border-green-200' : 'text-red-700 bg-red-50 border-red-200'"
                      class="text-xs px-3 py-1.5 rounded-full border font-medium"
                      x-text="results.fetch?.msg"></span>
            </div>
        </div>

        {{-- Reset --}}
        <div class="pt-2 border-t border-gray-100 flex items-center gap-3">
            <button @click="resetApplication()"
                    :disabled="loading.reset"
                    class="text-xs text-red-600 hover:text-red-800 underline focus:outline-none">
                Reset all CreditSense fields on this application
            </button>
            <span x-show="results.reset"
                  :class="results.reset?.ok ? 'text-green-700' : 'text-red-700'"
                  class="text-xs font-medium"
                  x-text="results.reset?.msg"></span>
        </div>

    </div>
</div>

<script>
function csTestPanel() {
    return {
        open: true,
        fakeAppId: '{{ $application->credit_sense_app_id ?? "99999" }}',
        loading: { complete: false, webhook: false, fetch: false, reset: false },
        results: { complete: null, webhook: null, fetch: null, reset: null },

        init() {},

        csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        },

        async post(url, body, opts = {}) {
            const headers = {
                'X-CSRF-TOKEN': this.csrf(),
                'Accept': 'application/json',
                ...opts.headers,
            };
            if (!(body instanceof FormData)) {
                headers['Content-Type'] = 'application/json';
            }
            const res = await fetch(url, {
                method: 'POST',
                headers,
                body: body instanceof FormData ? body : JSON.stringify(body),
            });
            const text = await res.text();
            let data;
            try { data = JSON.parse(text); } catch { data = { error: text.slice(0, 200) }; }
            return { ok: res.ok, status: res.status, data };
        },

        async simulateComplete() {
            this.loading.complete = true;
            this.results.complete = null;
            try {
                // Also save the fake app ID first
                if (this.fakeAppId) {
                    await this.post('{{ route("creditsense.saveAppId", $application) }}', {
                        app_id: this.fakeAppId,
                    });
                }
                const r = await this.post('{{ route("creditsense.complete", $application) }}', {});
                this.results.complete = {
                    ok: r.ok,
                    msg: r.ok ? `✓ ${r.data.message}` : `✗ ${r.data.error ?? r.data.message ?? 'Failed'}`,
                };
                if (r.ok) setTimeout(() => location.reload(), 1500);
            } catch (e) {
                this.results.complete = { ok: false, msg: '✗ Network error: ' + e.message };
            } finally {
                this.loading.complete = false;
            }
        },

        async simulateWebhook() {
            this.loading.webhook = true;
            this.results.webhook = null;

            const fakePayload = {
                appRef: '{{ $application->application_number }}',
                app_ref: '{{ $application->application_number }}',
                App_ID: parseInt(this.fakeAppId) || 99999,
                Success: true,
                Response: {
                    App_ID: parseInt(this.fakeAppId) || 99999,
                    App_Ref: '{{ $application->application_number }}',
                    Status: 'Complete',
                    Completed_Dt: new Date().toISOString(),
                    Bank_Accounts: [
                        {
                            Account_Name: 'Test Account (Simulated)',
                            BSB: '062-000',
                            Account_Number: '12345678',
                            Balance: 5000.00,
                            Transactions: [
                                { Date: '2026-04-01', Description: 'Simulated salary', Amount: 5000 },
                                { Date: '2026-04-10', Description: 'Simulated rent',   Amount: -2000 },
                            ],
                        }
                    ],
                },
                _simulated: true,
                _simulated_at: new Date().toISOString(),
            };

            try {
                const r = await this.post('/api/webhooks/creditsense', fakePayload);
                this.results.webhook = {
                    ok: r.ok,
                    msg: r.ok
                        ? `✓ Webhook accepted (HTTP ${r.status}) — report saved`
                        : `✗ HTTP ${r.status}: ${r.data.error ?? 'Rejected'}`,
                    body: JSON.stringify(r.data, null, 2),
                };
                if (r.ok) setTimeout(() => location.reload(), 1500);
            } catch (e) {
                this.results.webhook = { ok: false, msg: '✗ Network error: ' + e.message };
            } finally {
                this.loading.webhook = false;
            }
        },

        async fetchReport() {
            this.loading.fetch = true;
            this.results.fetch = null;
            try {
                const r = await this.post('{{ route("admin.creditsense.fetchReport", $application) }}', {});
                this.results.fetch = {
                    ok: r.ok,
                    msg: r.ok
                        ? `✓ ${r.data.message}`
                        : `✗ ${r.data.error ?? r.data.message ?? 'Failed'}`,
                };
                if (r.ok) setTimeout(() => location.reload(), 1500);
            } catch (e) {
                this.results.fetch = { ok: false, msg: '✗ Network error: ' + e.message };
            } finally {
                this.loading.fetch = false;
            }
        },

        async resetApplication() {
            if (!confirm('This will clear all CreditSense fields on this application. Continue?')) return;
            this.loading.reset = true;
            this.results.reset = null;
            try {
                const r = await this.post('{{ route("admin.creditsense.debugReset", $application) }}', {});
                this.results.reset = {
                    ok: r.ok,
                    msg: r.ok ? '✓ Reset complete' : `✗ ${r.data.error ?? 'Failed'}`,
                };
                if (r.ok) setTimeout(() => location.reload(), 1000);
            } catch (e) {
                this.results.reset = { ok: false, msg: '✗ ' + e.message };
            } finally {
                this.loading.reset = false;
            }
        },
    };
}
</script>
@endif