<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Comment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Application $application)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
            'type' => 'required|in:internal,client_visible',
            'is_pinned' => 'boolean',
        ]);

        $comment = $application->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
            'type' => $validated['type'],
            'is_pinned' => $validated['is_pinned'] ?? false,
            'ip_address' => $request->ip(),
        ]);

        ActivityLog::logActivity(
            'commented',
            "Added {$validated['type']} comment",
            $comment,
            null,
            ['comment_preview' => substr($validated['comment'], 0, 50)]
        );

        // TODO: Send notification if client_visible

        return back()->with('success', 'Comment added successfully.');
    }

    public function update(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
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

    public function destroy(Comment $comment)
    {
        $comment->delete();

        ActivityLog::logActivity(
            'deleted',
            'Deleted comment',
            $comment->application
        );

        return back()->with('success', 'Comment deleted successfully.');
    }

    public function togglePin(Comment $comment)
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
