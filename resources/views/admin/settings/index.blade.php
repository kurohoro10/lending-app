{{-- resources/views/admin/settings/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    System Settings
                </h2>
                <p class="text-sm text-gray-500 mt-1">Manage third-party credentials and integrations.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if(session('success'))
                <div class="rounded-lg bg-green-50 border border-green-200 p-4 flex items-center gap-3"
                     role="status" aria-live="polite">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-lg bg-red-50 border border-red-200 p-4" role="alert">
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Twilio ─────────────────────────────────────────────────── --}}
            @include('admin.settings.partials.group', [
                'groupKey'   => 'twilio',
                'groupLabel' => 'Twilio (SMS & WhatsApp)',
                'groupHint'  => 'Used for sending SMS and WhatsApp notifications to applicants.',
                'icon'       => 'phone',
                'fields'     => $fields,
                'settings'   => $settings,
            ])

            {{-- ── Mail / SMTP ─────────────────────────────────────────────── --}}
            @include('admin.settings.partials.group', [
                'groupKey'   => 'mail',
                'groupLabel' => 'Email / SMTP',
                'groupHint'  => 'Outgoing email configuration for all system notifications.',
                'icon'       => 'mail',
                'fields'     => $fields,
                'settings'   => $settings,
            ])

            {{-- ── CreditSense ─────────────────────────────────────────────── --}}
            @include('admin.settings.partials.group', [
                'groupKey'   => 'creditsense',
                'groupLabel' => 'CreditSense',
                'groupHint'  => 'Bank statement analysis integration credentials.',
                'icon'       => 'document',
                'fields'     => $fields,
                'settings'   => $settings,
            ])

        </div>
    </div>
</x-app-layout>
