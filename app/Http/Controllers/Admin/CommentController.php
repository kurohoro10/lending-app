<?php

/**
 * @file    app/Http/Controllers/Admin/CommentController.php
 * @package App\Http\Controllers\Admin
 *
 * Manages the full lifecycle of comments on loan applications within the
 * commercial loan application system.
 *
 * All mutating methods support progressive enhancement:
 *  - JS present (`Accept: application/json`) → JsonResponse with re-rendered
 *    comment HTML for seamless in-page updates
 *  - JS absent  (plain form POST)            → RedirectResponse with flash message
 *
 * Comment types:
 *  - `internal`       — visible to admin and assessors only
 *  - `client_visible` — visible to the client in their portal
 *
 * Soft-delete flow:
 *  - `destroy()` soft-deletes and returns an undo toast snippet
 *  - `restore()` reverses the soft-delete within the undo window
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Blade partial used to render a single comment list item.
     *
     * Returned as rendered HTML in JSON responses so the front-end can
     * replace or insert the comment without a full page reload.
     *
     * @var string
     */
    private const VIEW_ITEM = 'admin.applications.partials.show.comment-item';

    /**
     * Blade partial used to render the undo toast after a soft-delete.
     *
     * Includes a restore button that posts to the restore route within
     * the undo window.
     *
     * @var string
     */
    private const VIEW_TOAST = 'admin.applications.partials.show.comment-undo-toast';

    // =========================================================================
    // Create
    // =========================================================================

    /**
     * Store a new comment against an application.
     *
     * Creates the comment, eager-loads the author, and logs the activity.
     * Returns rendered comment HTML for JSON callers, or redirects for
     * standard form submissions.
     *
     * Note: Client-visible comment notifications are stubbed for future
     * implementation (see inline TODO).
     *
     * @param  Request      $request      Incoming HTTP request with comment payload.
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse|RedirectResponse
     *
     * @bodyParam string  comment   required  The comment body text.
     * @bodyParam string  type      required  Comment visibility — `internal` or `client_visible`.
     * @bodyParam boolean is_pinned nullable  Whether to pin the comment. Defaults to false.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Comment added successfully.",
     *   "html": "<div class=\"comment-item\">...</div>"
     * }
     */
    public function store(Request $request, Application $application): JsonResponse|RedirectResponse
    {
        $validated = $this->validateCommentPayload($request);

        $comment = $this->createComment($request, $application, $validated);

        ActivityLog::logActivity(
            'commented',
            "Added {$validated['type']} comment",
            $comment,
            null,
            ['comment_preview' => substr($validated['comment'], 0, 50)]
        );

        // TODO: Notify client when type === 'client_visible'
        // if ($validated['type'] === 'client_visible') { notify client }

        return $this->jsonOrRedirect(
            $request,
            ['html' => $this->renderCommentItem($comment)],
            'Comment added successfully.'
        );
    }

    // =========================================================================
    // Update
    // =========================================================================

    /**
     * Update the body and/or pinned state of an existing comment.
     *
     * Captures the previous values for audit purposes before applying changes.
     * Returns rendered comment HTML for JSON callers, or redirects for
     * standard form submissions.
     *
     * @param  Request  $request  Incoming HTTP request with updated comment fields.
     * @param  Comment  $comment  The bound comment model instance.
     * @return JsonResponse|RedirectResponse
     *
     * @bodyParam string  comment   required  Updated comment body text.
     * @bodyParam boolean is_pinned nullable  Updated pinned state.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Comment updated successfully.",
     *   "html": "<div class=\"comment-item\">...</div>"
     * }
     */
    public function update(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        $validated = $this->validateCommentUpdatePayload($request);

        $oldValues = $comment->only(['comment', 'is_pinned']);

        $comment->update($validated);
        $comment->load('user');

        ActivityLog::logActivity('updated', 'Updated comment', $comment, $oldValues, $validated);

        return $this->jsonOrRedirect(
            $request,
            ['html' => $this->renderCommentItem($comment)],
            'Comment updated successfully.'
        );
    }

    // =========================================================================
    // Delete & Restore
    // =========================================================================

    /**
     * Soft-delete a comment and return an undo toast for JSON callers.
     *
     * Captures the comment ID before deletion so it can be included in the
     * JSON payload and embedded in the undo toast restore route. Logs the
     * activity against the parent application rather than the deleted comment.
     *
     * @param  Request  $request  Incoming HTTP request.
     * @param  Comment  $comment  The bound comment model instance.
     * @return JsonResponse|RedirectResponse
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Comment deleted.",
     *   "comment_id": 42,
     *   "undo_html": "<div class=\"toast\">...</div>"
     * }
     */
    public function destroy(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        $application = $comment->application;
        $commentId   = $comment->id;

        $comment->delete();

        ActivityLog::logActivity('deleted', 'Soft-deleted comment', $application);

        return $this->jsonOrRedirect(
            $request,
            [
                'comment_id' => $commentId,
                'undo_html'  => $this->renderUndoToast($commentId),
            ],
            'Comment deleted successfully.',
            'Comment deleted.'
        );
    }

    /**
     * Restore a soft-deleted comment within the undo window.
     *
     * Fetches the comment including trashed records and restores it.
     * Returns rendered comment HTML for JSON callers, or redirects for
     * standard form submissions.
     *
     * @param  Request  $request    Incoming HTTP request.
     * @param  int      $commentId  The ID of the soft-deleted comment to restore.
     * @return JsonResponse|RedirectResponse
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Comment restored.",
     *   "comment_id": 42,
     *   "html": "<div class=\"comment-item\">...</div>"
     * }
     */
    public function restore(Request $request, int $commentId): JsonResponse|RedirectResponse
    {
        $comment = Comment::withTrashed()->findOrFail($commentId);

        $comment->restore();
        $comment->load('user');

        ActivityLog::logActivity('restored', 'Restored comment', $comment);

        return $this->jsonOrRedirect(
            $request,
            [
                'comment_id' => $comment->id,
                'html'       => $this->renderCommentItem($comment),
            ],
            'Comment restored.'
        );
    }

    // =========================================================================
    // Pin Toggle
    // =========================================================================

    /**
     * Toggle the pinned state of a comment.
     *
     * Inverts the current `is_pinned` boolean, derives the action label
     * (`Pinned` / `Unpinned`) from the resulting state, and logs the activity.
     *
     * @param  Request  $request  Incoming HTTP request.
     * @param  Comment  $comment  The bound comment model instance.
     * @return JsonResponse|RedirectResponse
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Pinned comment successfully.",
     *   "html": "<div class=\"comment-item\">...</div>"
     * }
     */
    public function togglePin(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        $comment->update(['is_pinned' => ! $comment->is_pinned]);
        $comment->load('user');

        $action = $comment->is_pinned ? 'Pinned' : 'Unpinned';

        ActivityLog::logActivity('pinned', "{$action} comment", $comment);

        return $this->jsonOrRedirect(
            $request,
            ['html' => $this->renderCommentItem($comment)],
            "{$action} comment successfully."
        );
    }

    // =========================================================================
    // Private Helpers — Validation
    // =========================================================================

    /**
     * Validate the comment creation request payload.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated comment, type, and is_pinned fields.
     */
    private function validateCommentPayload(Request $request): array
    {
        return $request->validate([
            'comment'   => ['required', 'string'],
            'type'      => ['required', 'in:internal,client_visible'],
            'is_pinned' => ['boolean'],
        ]);
    }

    /**
     * Validate the comment update request payload.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated comment body and is_pinned fields.
     */
    private function validateCommentUpdatePayload(Request $request): array
    {
        return $request->validate([
            'comment'   => ['required', 'string'],
            'is_pinned' => ['boolean'],
        ]);
    }

    // =========================================================================
    // Private Helpers — Persistence
    // =========================================================================

    /**
     * Create a new Comment record and eager-load the author relationship.
     *
     * @param  Request      $request      The HTTP request (used for sender IP).
     * @param  Application  $application  The parent application.
     * @param  array        $validated    Validated comment creation payload.
     * @return Comment                    The newly created and loaded comment model.
     */
    private function createComment(Request $request, Application $application, array $validated): Comment
    {
        $comment = $application->comments()->create([
            'user_id'    => auth()->id(),
            'comment'    => $validated['comment'],
            'type'       => $validated['type'],
            'is_pinned'  => $validated['is_pinned'] ?? false,
            'ip_address' => $request->ip(),
        ]);

        $comment->load('user');

        return $comment;
    }

    // =========================================================================
    // Private Helpers — Rendering
    // =========================================================================

    /**
     * Render the comment item partial for a given comment model.
     *
     * @param  Comment  $comment  The comment to render (must have `user` loaded).
     * @return string             Rendered HTML string.
     */
    private function renderCommentItem(Comment $comment): string
    {
        return view(self::VIEW_ITEM, compact('comment'))->render();
    }

    /**
     * Render the undo toast partial for a soft-deleted comment.
     *
     * @param  int  $commentId  The ID of the deleted comment.
     * @return string           Rendered HTML string.
     */
    private function renderUndoToast(int $commentId): string
    {
        return view(self::VIEW_TOAST, [
            'commentId'    => $commentId,
            'restoreRoute' => route('admin.comments.restore', $commentId),
        ])->render();
    }

    // =========================================================================
    // Private Helpers — Progressive Enhancement
    // =========================================================================

    /**
     * Return a JSON response or a redirect based on the request type.
     *
     * Centralises the progressive enhancement fork used by every mutating
     * method. The `$jsonExtras` array is merged into the base JSON payload
     * alongside `success` and `message`. The redirect always flashes to the
     * session and returns to the previous URL.
     *
     * The optional `$jsonMessage` parameter allows JSON and redirect responses
     * to carry different message strings (e.g. "Comment deleted." vs
     * "Comment deleted successfully.") when the UX benefit justifies it.
     *
     * @param  Request  $request       The current HTTP request.
     * @param  array    $jsonExtras    Additional keys to merge into the JSON payload.
     * @param  string   $message       Flash message for the redirect (and JSON default).
     * @param  string|null $jsonMessage  Optional override message for the JSON response.
     * @return JsonResponse|RedirectResponse
     */
    private function jsonOrRedirect(
        Request $request,
        array $jsonExtras,
        string $message,
        ?string $jsonMessage = null
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json(array_merge([
                'success' => true,
                'message' => $jsonMessage ?? $message,
            ], $jsonExtras));
        }

        return back()->with('success', $message);
    }
}