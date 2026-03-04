{{-- resources/views/applications/partials/edit/bank-statements-ui.blade.php --}}
@php
    $activeProvider = \App\Models\Setting::where('key', 'active_bank_provider')->value('value') ?? 'basiq';
@endphp

@if($activeProvider === 'basiq')
    @include('applications.partials.edit.providers.basiq-ui', ['application' => $application])
@elseif($activeProvider === 'creditsense')
    @include('applications.partials.edit.providers.creditsense-ui', ['application' => $application])
@else
    @include('applications.partials.edit.providers.bank-api-ui')
@endif
