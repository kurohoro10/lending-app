{{-- resources/views/applications/partials/show/comments.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-200">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
        <h3 class="text-lg font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
            </svg>
            Comments History
        </h3>
    </div>

    <div class="p-6">
        <ol class="space-y-4" aria-label="Comment history">
            @foreach($application->comments->sortByDesc('created_at') as $comment)
                @if($comment->type !== 'internal')
                    <li class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center
                                        text-xs font-bold text-indigo-700 flex-shrink-0" aria-hidden="true">
                                {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                            </div>
                            @if(!$loop->last)
                                <div class="w-px flex-1 bg-gray-200 mt-1" aria-hidden="true"></div>
                            @endif
                        </div>
                        <div class="pb-4 flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-sm font-semibold text-gray-900">{{ $comment->user->name }}</span>
                                @if($comment->is_pinned)
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">
                                        Pinned
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400">
                                    {{ $comment->created_at->format('d M Y H:i') }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->comment }}</p>
                        </div>
                    </li>
                @endif
            @endforeach
        </ol>
    </div>
</div>
