<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Comments History</h3>
        <div class="space-y-4">
            @foreach($application->comments->sortByDesc('created_at') as $comment)
            <div class="border-l-4 {{ $comment->is_pinned ? 'border-yellow-400' : 'border-gray-200' }} pl-4 py-2">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <span class="font-medium text-gray-900">{{ $comment->user->name }}</span>
                            <span class="px-2 py-1 text-xs rounded-full {{ $comment->type == 'internal' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst(str_replace('_', ' ', $comment->type)) }}
                            </span>
                            @if($comment->is_pinned)
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pinned</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-gray-600">{{ $comment->comment }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $comment->created_at->format('d M Y H:i') }} • IP: {{ $comment->ip_address }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
