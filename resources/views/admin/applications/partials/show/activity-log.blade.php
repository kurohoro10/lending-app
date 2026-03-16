{{-- resources/views/admin/applications/partials/show/activity-log.blade.php --}}
@php
    $logs = \App\Helpers\ActivityLogFormatter::forApplication($application);
@endphp

<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4" id="activity-log-heading">
            Activity Log
        </h3>

        @if($logs->isEmpty())
            <p class="text-sm text-gray-500 italic">No activity recorded yet.</p>
        @else
            <ol class="space-y-2" aria-labelledby="activity-log-heading">
                @foreach($logs as $log)
                    <li class="flex items-start space-x-3 text-sm">
                        <time datetime="{{ $log['iso'] }}"
                              class="flex-shrink-0 text-gray-500 w-44">
                            {{ $log['datetime'] }}
                        </time>
                        <span class="font-medium text-gray-900 flex-shrink-0">
                            {{ $log['user'] }}
                        </span>
                        <span class="text-gray-600">
                            {{ $log['description'] }}
                        </span>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</div>