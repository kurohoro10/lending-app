{{-- resources/views/admin/applications/partials/show/comment-undo-toast.blade.php --}}
{{-- Swapped in by JS after a soft delete. Removed after 10 seconds or on Undo click. --}}
<div id="comment-undo-{{ $commentId }}"
     class="flex items-center justify-between gap-4 px-4 py-3 rounded-r-md border-l-4 border-l-red-300 border border-gray-200 bg-gray-50"
     role="status"
     aria-live="polite"
     aria-label="Comment deleted. Undo available for 10 seconds.">

    <div class="flex items-center gap-3 min-w-0">
        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
        <span class="text-sm text-gray-500">
            Comment deleted.
            <span id="comment-undo-countdown-{{ $commentId }}"
                  class="font-medium text-gray-600"
                  aria-live="off">10s</span>
            remaining to undo.
        </span>
    </div>

    <button type="button"
            class="comment-undo-btn flex-shrink-0 text-sm font-medium text-indigo-600 hover:text-indigo-800
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 rounded transition"
            data-comment-id="{{ $commentId }}"
            data-restore-route="{{ $restoreRoute }}"
            aria-label="Undo deletion — restore this comment">
        Undo
    </button>
</div>
