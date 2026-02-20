<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Comment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

/**
 * Class CommentController
 *
 * All mutating methods support progressive enhancement:
 *  - JS present (Accept: application/json) → JsonResponse with rendered HTML
 *  - JS absent  (plain form POST)          → RedirectResponse with flash message
 *
 * @package App\Http\Controllers\Admin
 */
class CommentController extends Controller
{
    private const VIEW_ITEM  = 'admin.applications.partials.show.comment-item';
    private const VIEW_TOAST = 'admin.applications.partials.show.comment-undo-toast';

    /**
     * Store a new comment.
     */
    public function store(Request $request, Application $application): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'comment'   => 'required|string',
            'type'      => 'required|in:internal,client_visible',
            'is_pinned' => 'boolean',
        ]);

        $comment = $application->comments()->create([
            'user_id'    => auth()->id(),
            'comment'    => $validated['comment'],
            'type'       => $validated['type'],
            'is_pinned'  => $validated['is_pinned'] ?? false,
            'ip_address' => $request->ip(),
        ]);

        $comment->load('user');

        ActivityLog::logActivity(
            'commented',
            "Added {$validated['type']} comment",
            $comment,
            null,
            ['comment_preview' => substr($validated['comment'], 0, 50)]
        );

        // if ($validated['type'] === 'client_visible') { notify client }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully.',
                'html'    => view(self::VIEW_ITEM, compact('comment'))->render(),
            ]);
        }

        return back()->with('success', 'Comment added successfully.');
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'comment'   => 'required|string',
            'is_pinned' => 'boolean',
        ]);

        $oldValues = $comment->only(['comment', 'is_pinned']);
        $comment->update($validated);
        $comment->load('user');

        ActivityLog::logActivity('updated', 'Updated comment', $comment, $oldValues, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Comment updated successfully.',
                'html'    => view(self::VIEW_ITEM, compact('comment'))->render(),
            ]);
        }

        return back()->with('success', 'Comment updated successfully.');
    }

    /**
     * Soft-delete a comment and return an undo toast.
     */
    public function destroy(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        $application = $comment->application;
        $commentId   = $comment->id;

        $comment->delete();

        ActivityLog::logActivity('deleted', 'Soft-deleted comment', $application);

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Comment deleted.',
                'comment_id' => $commentId,
                'undo_html'  => view(self::VIEW_TOAST, [
                    'commentId'    => $commentId,
                    'restoreRoute' => route('admin.comments.restore', $commentId),
                ])->render(),
            ]);
        }

        return back()->with('success', 'Comment deleted successfully.');
    }

    /**
     * Restore a soft-deleted comment.
     */
    public function restore(Request $request, int $commentId): JsonResponse|RedirectResponse
    {
        $comment = Comment::withTrashed()->findOrFail($commentId);
        $comment->restore();
        $comment->load('user');

        ActivityLog::logActivity('restored', 'Restored comment', $comment);

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Comment restored.',
                'comment_id' => $comment->id,
                'html'       => view(self::VIEW_ITEM, compact('comment'))->render(),
            ]);
        }

        return back()->with('success', 'Comment restored.');
    }

    /**
     * Toggle the pinned status of a comment.
     */
    public function togglePin(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        $comment->update(['is_pinned' => !$comment->is_pinned]);
        $comment->load('user');

        $action = $comment->is_pinned ? 'Pinned' : 'Unpinned';

        ActivityLog::logActivity('pinned', "{$action} comment", $comment);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$action} comment successfully.",
                'html'    => view(self::VIEW_ITEM, compact('comment'))->render(),
            ]);
        }

        return back()->with('success', "Comment {$action} successfully.");
    }
}
