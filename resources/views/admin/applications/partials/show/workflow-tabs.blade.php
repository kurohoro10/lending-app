@php use App\Models\Application; @endphp

@php
    $tabs = [
        'in_progress' => [
            'label' => 'In Progress',
            'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
            'color' => 'indigo',
            'description' => 'Processing application',
        ],
        'approve' => [
            'label' => 'Approve',
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'color' => 'green',
            'description' => 'Ready for approval',
        ],
        'deferred' => [
            'label' => 'Deferred',
            'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'color' => 'gray',
            'description' => 'Deferred decision',
        ],
        'declined' => [
            'label' => 'Declined',
            'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
            'color' => 'red',
            'description' => 'Application declined',
        ],
    ];

    $currentTab = $application->getTabForStatus($application->status);
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
    <div class="border-b border-gray-200">
        <nav class="flex flex-wrap sm:flex-nowrap" aria-label="Workflow tabs">
            @foreach ($tabs as $tabKey => $tab)
                @php
                    $isActive = $currentTab === $tabKey;
                    $bgColor = $isActive ? "bg-{$tab['color']}-50" : 'bg-white';
                    $borderColor = $isActive ? "border-{$tab['color']}-500" : 'border-transparent';
                    $textColor = $isActive ? "text-{$tab['color']}-700" : 'text-gray-500';
                    $iconColor = $isActive ? "text-{$tab['color']}-600" : 'text-gray-400';
                @endphp

                <div class="flex-1 sm:flex-none">
                    <div class="px-4 sm:px-6 py-4 border-b-2 {{ $borderColor }} {{ $bgColor }} transition-colors">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="{{ $tab['icon'] }}" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold {{ $textColor }}">{{ $tab['label'] }}</p>
                                <p class="text-xs text-gray-500">{{ $tab['description'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </nav>
    </div>

    {{-- Tab Content Section --}}
    <div class="p-6">
        @if($currentTab === 'in_progress')
            @include('admin.applications.partials.show.workflow-progress-tab')
        @elseif($currentTab === 'approve')
            @include('admin.applications.partials.show.workflow-approve-tab')
        @elseif($currentTab === 'deferred')
            @include('admin.applications.partials.show.workflow-deferred-tab')
        @elseif($currentTab === 'declined')
            @include('admin.applications.partials.show.workflow-declined-tab')
        @endif
    </div>
</div>