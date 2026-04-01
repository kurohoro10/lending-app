<?php

/**
 * @file    app/Http/Controllers/Admin/DocumentController.php
 * @package App\Http\Controllers\Admin
 *
 * Manages admin review actions on client-uploaded documents within the
 * commercial loan application system.
 *
 * Responsibilities:
 *  - Approving or rejecting documents uploaded by applicants
 *  - Enforcing business rules around terminal application statuses
 *  - Logging document review activity for audit purposes
 *
 * Progressive enhancement:
 *  - JS present (`Accept: application/json`) → JsonResponse with fresh document model
 *  - JS absent  (plain form POST)            → RedirectResponse with flash message
 *
 * Business rules:
 *  - Document status cannot be changed once the parent application is `approved` or `declined`
 *  - A `review_notes` value is mandatory when setting status to `rejected`
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * Terminal application statuses that prevent document review.
     *
     * Once a parent application reaches one of these statuses, no further
     * document status changes are permitted.
     *
     * @var string[]
     */
    private const LOCKED_APPLICATION_STATUSES = ['approved', 'declined'];

    // =========================================================================
    // Status Update
    // =========================================================================

    /**
     * Approve or reject a document uploaded by the client.
     *
     * Enforces two business rules before persisting:
     *  1. The parent application must not be in a terminal status.
     *  2. A review note is required when the new status is `rejected`.
     *
     * On success, updates the document with the new status, reviewer identity,
     * and timestamp, then logs the status transition for audit. Returns a fresh
     * document model in JSON responses so the front-end can re-render without
     * a separate fetch.
     *
     * @param  Request   $request   Incoming HTTP request with status and optional review notes.
     * @param  Document  $document  The bound document model instance.
     * @return RedirectResponse|JsonResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException  If the admin lacks `review` policy on the application.
     *
     * @bodyParam string status       required  New document status — `pending`, `approved`, or `rejected`.
     * @bodyParam string review_notes nullable  Reviewer notes; required when status is `rejected` (max 1000 chars).
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Document approved successfully.",
     *   "document": { "id": 1, "status": "approved", ... }
     * }
     * @response 422 { "success": false, "message": "..." }
     */
    public function updateStatus(Request $request, Document $document): RedirectResponse|JsonResponse
    {
        $this->authorize('review', $document->application);

        if ($this->isApplicationLocked($document)) {
            $error = 'Document status cannot be changed once the application has been approved or declined.';
            return $this->errorResponse($request, $error);
        }

        $validated = $this->validateStatusUpdate($request);

        if ($this->isRejectionMissingNote($validated)) {
            $error = 'A review note is required when rejecting a document.';
            return $this->rejectionNoteErrorResponse($request, $error);
        }

        $oldStatus = $document->status;

        $this->applyStatusUpdate($document, $validated);

        $this->logReviewActivity($document, $oldStatus, $validated);

        return $this->successResponse($request, $document, $validated['status']);
    }

    // =========================================================================
    // Private Helpers — Guards
    // =========================================================================

    /**
     * Determine whether the document's parent application is in a locked status.
     *
     * @param  Document  $document  The document whose parent application is checked.
     * @return bool                 True if the application status prevents further review.
     */
    private function isApplicationLocked(Document $document): bool
    {
        return in_array($document->application->status, self::LOCKED_APPLICATION_STATUSES);
    }

    /**
     * Determine whether a rejection is missing its mandatory review note.
     *
     * @param  array  $validated  The validated request payload.
     * @return bool               True if the status is `rejected` and no note was provided.
     */
    private function isRejectionMissingNote(array $validated): bool
    {
        return $validated['status'] === 'rejected' && blank($validated['review_notes'] ?? null);
    }

    // =========================================================================
    // Private Helpers — Validation & Persistence
    // =========================================================================

    /**
     * Validate the document status update request payload.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated `status` and optional `review_notes` fields.
     */
    private function validateStatusUpdate(Request $request): array
    {
        return $request->validate([
            'status'       => ['required', 'in:pending,approved,rejected'],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    /**
     * Apply the validated status, review notes, reviewer identity, and timestamp to the document.
     *
     * @param  Document  $document   The document to update.
     * @param  array     $validated  Validated status update payload.
     * @return void
     */
    private function applyStatusUpdate(Document $document, array $validated): void
    {
        $document->update([
            'status'       => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
        ]);
    }

    /**
     * Log the document review status transition to the activity log.
     *
     * @param  Document  $document   The reviewed document.
     * @param  string    $oldStatus  The status before the update.
     * @param  array     $validated  The validated payload containing the new status and notes.
     * @return void
     */
    private function logReviewActivity(Document $document, string $oldStatus, array $validated): void
    {
        ActivityLog::logActivity(
            'document_reviewed',
            "Document '{$document->original_filename}' status changed from {$oldStatus} to {$validated['status']}",
            $document->application,
            ['status' => $oldStatus],
            ['status' => $validated['status'], 'review_notes' => $validated['review_notes'] ?? null],
        );
    }

    // =========================================================================
    // Private Helpers — Progressive Enhancement Responses
    // =========================================================================

    /**
     * Return a generic error response for the locked-application guard.
     *
     * @param  Request  $request  The current HTTP request.
     * @param  string   $error    The error message to return or flash.
     * @return RedirectResponse|JsonResponse
     */
    private function errorResponse(Request $request, string $error): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        return back()->with('error', $error);
    }

    /**
     * Return a rejection-note validation error response.
     *
     * JSON callers receive the error as a message field; form callers receive
     * a validation error on the `review_notes` input so the field is highlighted.
     *
     * @param  Request  $request  The current HTTP request.
     * @param  string   $error    The error message to return or flash.
     * @return RedirectResponse|JsonResponse
     */
    private function rejectionNoteErrorResponse(Request $request, string $error): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $error], 422);
        }

        return back()->withErrors(['review_notes' => $error]);
    }

    /**
     * Return a success response after a document review has been persisted.
     *
     * JSON callers receive a fresh document model. Form callers receive a
     * redirect with a ucfirst-capitalised flash message.
     *
     * @param  Request   $request   The current HTTP request.
     * @param  Document  $document  The updated document model.
     * @param  string    $status    The new status string used to build the message.
     * @return RedirectResponse|JsonResponse
     */
    private function successResponse(Request $request, Document $document, string $status): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => "Document {$status} successfully.",
                'document' => $document->fresh(),
            ]);
        }

        return back()->with('success', 'Document ' . ucfirst($status) . ' successfully.');
    }
}