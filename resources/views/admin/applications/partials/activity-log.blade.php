<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Activity Log</h3>
        <div class="space-y-2">
            @foreach($application->activityLogs->sortByDesc('created_at')->take(10) as $log)
            <div class="flex items-start space-x-3 text-sm">
                <span class="text-gray-500">{{ $log->created_at->format('d M Y H:i') }}</span>
                <span class="font-medium text-gray-900">{{ $log->user->name ?? 'System' }}</span>
                <span class="text-gray-600">{{ $log->description }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
