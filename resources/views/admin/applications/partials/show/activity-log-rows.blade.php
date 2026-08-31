{{-- resources/views/admin/applications/partials/show/activity-log-rows.blade.php --}}
{{-- Renders one day-grouped batch of activity log rows. Deliberately
     self-contained (defines its own color lookup tables, doesn't assume
     anything about a surrounding page) because it's rendered from two
     places: the initial load inside activity-log.blade.php, and each
     "Load More" batch from Admin\ApplicationController::activityLog(). --}}
@php
    // Tailwind's content scanner (tailwind.config.js) only reads
    // resources/views/**/*.blade.php — it cannot see class names built by
    // interpolating a PHP variable (e.g. "bg-{{ $color }}-50"). Presenter
    // only ever returns bare color words, so the literal class strings for
    // every possible word live here, where the build can actually find them.
    $categoryClasses = [
        'indigo'  => 'bg-indigo-50 text-indigo-600',
        'sky'     => 'bg-sky-50 text-sky-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber'   => 'bg-amber-50 text-amber-600',
        'teal'    => 'bg-teal-50 text-teal-600',
        'violet'  => 'bg-violet-50 text-violet-600',
        'fuchsia' => 'bg-fuchsia-50 text-fuchsia-600',
        'orange'  => 'bg-orange-50 text-orange-600',
        'slate'   => 'bg-slate-50 text-slate-600',
        'gray'    => 'bg-gray-50 text-gray-500',
    ];

    // Application::statusBadgeColor() word => pill classes.
    $statusPillClasses = [
        'blue'   => 'bg-blue-100 text-blue-700',
        'yellow' => 'bg-yellow-100 text-yellow-700',
        'orange' => 'bg-orange-100 text-orange-700',
        'purple' => 'bg-purple-100 text-purple-700',
        'red'    => 'bg-red-100 text-red-700',
        'gray'   => 'bg-gray-100 text-gray-600',
        'green'  => 'bg-green-100 text-green-700',
    ];

    $grouped = $logs->groupBy(function ($log) {
        $date = $log['created_at'];

        if ($date->isToday()) {
            return 'Today';
        }

        if ($date->isYesterday()) {
            return 'Yesterday';
        }

        return $date->format('d M Y');
    });

    $continueGroup ??= null;
@endphp

@if($logs->isEmpty())
    {{-- Only reachable via a filtered/searched AJAX batch — the initial,
         unfiltered load is guarded before this partial is ever included. --}}
    <p class="text-sm text-gray-500 italic py-2">No matching activity found.</p>
@endif

@foreach($grouped as $dayLabel => $dayLogs)
    {{-- data-day-label lets the "Load More" JS read off the last group
         currently shown, so the next batch knows whether to suppress its
         first header (see $continueGroup below). --}}
    <div class="activity-log-day-group mb-4 last:mb-0" data-day-label="{{ $dayLabel }}">
        @unless($loop->first && $continueGroup === $dayLabel)
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 mb-1.5">
                {{ $dayLabel }}
            </p>
        @endunless

        <ol class="space-y-1" role="list" aria-label="Activity on {{ $dayLabel }}">
            @foreach($dayLogs as $log)
                @php
                    $categoryClass = $categoryClasses[$log['color']] ?? $categoryClasses['gray'];
                    $isCommunication = $log['category'] === 'communication';
                    $hasPreview = $isCommunication
                        && (
                            filled(data_get($log, 'new_values.subject'))
                            || filled(data_get($log, 'new_values.excerpt'))
                        );
                @endphp

                <li class="flex items-start gap-3 text-sm py-1">
                    {{-- Category icon --}}
                    <span class="mt-0.5 flex-shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full {{ $categoryClass }}"
                          title="{{ $log['category_label'] }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $log['icon_path'] }}" />
                        </svg>
                    </span>

                    {{-- Relative time, full timestamp on hover --}}
                    <time datetime="{{ $log['iso'] }}"
                          title="{{ $log['datetime'] }}"
                          class="flex-shrink-0 text-gray-400 w-24 pt-0.5 tabular-nums">
                        {{ $log['created_at']->diffForHumans() }}
                    </time>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-baseline gap-x-1.5 gap-y-0.5">
                            {{-- Actor --}}
                            @if($log['actor']['is_system'])
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide bg-gray-100 text-gray-500">
                                    {{ $log['actor']['label'] }}
                                </span>
                            @else
                                <span class="font-medium text-gray-900">{{ $log['user'] }}</span>
                                <span class="text-[10px] text-gray-400 uppercase tracking-wide">{{ $log['actor']['label'] }}</span>
                            @endif

                            {{-- Status transition pills, when resolvable — otherwise plain description --}}
                            @if($log['status_transition'])
                                @php
                                    $oldPill = $statusPillClasses[$log['status_transition']['old']['color']] ?? $statusPillClasses['gray'];
                                    $newPill = $statusPillClasses[$log['status_transition']['new']['color']] ?? $statusPillClasses['gray'];
                                @endphp
                                <span class="inline-flex items-center gap-1">
                                    <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $oldPill }}">
                                        {{ $log['status_transition']['old']['label'] }}
                                    </span>
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                    <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $newPill }}">
                                        {{ $log['status_transition']['new']['label'] }}
                                    </span>
                                </span>
                            @elseif(! $hasPreview)
                                <span class="text-gray-600">{{ $log['description'] }}</span>
                            @endif
                        </div>

                        {{-- Communication preview card — only renders once new_values actually
                             carries a subject or excerpt. Every field inside is independently
                             optional, so a row with only some metadata still renders cleanly. --}}
                        @if($hasPreview)
                            @php
                                $direction = data_get($log, 'new_values.direction');
                                $counterparty = $direction === 'inbound'
                                    ? data_get($log, 'new_values.from')
                                    : data_get($log, 'new_values.to');
                                $counterpartyLabel = $direction === 'inbound' ? 'From' : 'To';
                            @endphp
                            <div class="mt-1 rounded-md border border-gray-100 bg-gray-50 px-2.5 py-1.5">
                                @if(data_get($log, 'new_values.subject'))
                                    <p class="text-xs font-medium text-gray-700 truncate">
                                        {{ data_get($log, 'new_values.subject') }}
                                    </p>
                                @endif

                                @if($counterparty || data_get($log, 'new_values.is_ad_hoc') || data_get($log, 'new_values.status'))
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        @if($counterparty)
                                            {{ $counterpartyLabel }}: {{ $counterparty }}
                                        @endif
                                        @if(data_get($log, 'new_values.is_ad_hoc'))
                                            <span class="ml-1 inline-flex items-center px-1 rounded bg-gray-200 text-gray-600 font-medium">Ad-hoc</span>
                                        @endif
                                        @if(data_get($log, 'new_values.status'))
                                            <span class="ml-1 inline-flex items-center px-1 rounded bg-gray-200 text-gray-600 font-medium">{{ ucfirst(data_get($log, 'new_values.status')) }}</span>
                                        @endif
                                    </p>
                                @endif

                                @if(data_get($log, 'new_values.excerpt'))
                                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">
                                        {{ data_get($log, 'new_values.excerpt') }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
@endforeach
