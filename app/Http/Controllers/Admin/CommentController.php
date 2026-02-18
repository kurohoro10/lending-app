<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Comment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

/**
 * Class CommentController
 *
 * Manages the creation, modification, and visibility of comments
 * attached to loan applications, supporting both internal staff notes
 * and client-facing communications.
 *
 * @package App\Http\Controllers\Admin
 */
class CommentController extends Controller
{
    /**
     * Store a newly created comment in storage.
     *
     * @param Request     $request
     * @param Application $application
     * @return RedirectResponse
     */
    public function store(Request $request, Application $application): RedirectResponse
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

        ActivityLog::logActivity(
            'commented',
            "Added {$validated['type']} comment",
            $comment,
            null,
            ['comment_preview' => substr($validated['comment'], 0, 50)]
        );

        // Implementation Note: In the future, trigger a notification
        // if ($validated['type'] === 'client_visible') ...

        return back()->with('success', 'Comment added successfully.');
    }

    /**
     * Update the specified comment in storage.
     *
     * @param Request $request
     * @param Comment $comment
     * @return RedirectResponse
     */
    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $validated = $request->validate([
            'comment'   => 'required|string',
            'is_pinned' => 'boolean',
        ]);

        $oldValues = $comment->only(['comment', 'is_pinned']);
        $comment->update($validated);

        ActivityLog::logActivity(
            'updated',
            'Updated comment',
            $comment,
            $oldValues,
            $validated
        );

        return back()->with('success', 'Comment updated successfully.');
    }

    /**
     * Remove the specified comment from storage.
     *
     * @param Comment $comment
     * @return RedirectResponse
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $application = $comment->application;

        $comment->delete();

        ActivityLog::logActivity(
            'deleted',
            'Deleted comment',
            $application
        );

        return back()->with('success', 'Comment deleted successfully.');
    }

    /**
     * Toggle the pinned status of a comment.
     *
     * @param Comment $comment
     * @return RedirectResponse
     */
    public function togglePin(Comment $comment): RedirectResponse
    {
        $comment->update(['is_pinned' => !$comment->is_pinned]);

        $action = $comment->is_pinned ? 'Pinned' : 'Unpinned';

        ActivityLog::logActivity(
            'pinned',
            "{$action} comment",
            $comment
        );

        return back()->with('success', "Comment {$action} successfully.");
    }
}
