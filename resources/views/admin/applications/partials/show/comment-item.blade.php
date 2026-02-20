{{-- resources/views/admin/applications/partials/show/comment-item.blade.php --}}
{{-- Rendered for initial page load AND returned as HTML fragment from the controller. --}}
{{-- Never include this directly in templates — use comment-history.blade.php instead. --}}
<div id="comment-{{ $comment->id }}"
     class="border-l-4 {{ $comment->is_pinned ? 'border-yellow-400 bg-yellow-50' : 'border-gray-200' }} pl-4 py-2 rounded-r-md">

    <div class="flex justify-between items-start gap-4">
        <div class="flex-1 min-w-0">

            {{-- Author + badges --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-medium text-gray-900 text-sm">{{ $comment->user->name }}</span>

                <span class="px-2 py-0.5 text-xs rounded-full
                    {{ $comment->type === 'internal' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}"
                      aria-label="Comment type: {{ $comment->type === 'internal' ? 'internal, staff only' : 'client visible' }}">
                    {{ ucfirst(str_replace('_', ' ', $comment->type)) }}
                </span>

                @if($comment->is_pinned)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800"
                          aria-label="Pinned comment">
                        📌 Pinned
                    </span>
                @endif
            </div>

            {{-- Body --}}
            <p class="mt-1 text-sm text-gray-600 whitespace-pre-line">{{ $comment->comment }}</p>

            {{-- Meta --}}
            <p class="mt-1 text-xs text-gray-500">
                <time datetime="{{ $comment->created_at->toIso8601String() }}">
                    {{ $comment->created_at->format('d M Y H:i') }}
                </time>
                @if($comment->ip_address)
                    &bull; IP: {{ $comment->ip_address }}
                @endif
            </p>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 flex-shrink-0">

            {{-- Pin / Unpin --}}
            <button type="button"
                    class="comment-pin-btn text-xs px-2 py-1 rounded border
                           {{ $comment->is_pinned
                               ? 'border-yellow-300 text-yellow-700 hover:bg-yellow-100'
                               : 'border-gray-300 text-gray-500 hover:bg-gray-100' }}
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition"
                    data-comment-id="{{ $comment->id }}"
                    data-pin-route="{{ route('admin.comments.togglePin', $comment) }}"
                    aria-label="{{ $comment->is_pinned ? 'Unpin this comment' : 'Pin this comment' }}"
                    aria-pressed="{{ $comment->is_pinned ? 'true' : 'false' }}">
                {{ $comment->is_pinned ? 'Unpin' : 'Pin' }}
            </button>

            {{-- Delete (soft) --}}
            <button type="button"
                    class="comment-delete-btn text-xs px-2 py-1 rounded border border-red-300 text-red-600
                           hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition"
                    data-comment-id="{{ $comment->id }}"
                    data-delete-route="{{ route('admin.comments.destroy', $comment) }}"
                    aria-label="Delete comment by {{ $comment->user->name }}">
                Delete
            </button>

        </div>
    </div>
</div>
