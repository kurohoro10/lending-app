{{-- resources/views/admin/applications/partials/show/comment-history.blade.php --}}
<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4" id="comments-history-heading">
            Comments History
        </h3>

        <div id="comments-list"
             class="space-y-4"
             aria-labelledby="comments-history-heading"
             aria-live="polite"
             aria-relevant="additions removals">

            @forelse($application->comments->sortByDesc('created_at') as $comment)
                @include('admin.applications.partials.show.comment-item', ['comment' => $comment])
            @empty
                <p id="no-comments-msg" class="text-sm text-gray-500 italic">No comments yet.</p>
            @endforelse

        </div>
    </div>
</div>
