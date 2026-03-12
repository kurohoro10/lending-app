<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    /**
     * PATCH admin/documents/{document}/status
     *
     * Approve or reject a document uploaded by the client.
     * A review note is required when rejecting.
     */
    public function updateStatus(Request $request, Document $document): RedirectResponse|JsonResponse
    {
        $this->authorize('review', $document->application);

        if (in_array($document->application->status, ['approved', 'declined'])) {
            $error = 'Document status cannot be changed once the application has been approved or declined.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }

            return back()->with('error', $error);
        }

        $validated = $request->validate([
            'status'       => 'required|in:pending,approved,rejected',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['status'] === 'rejected' && blank($validated['review_notes'])) {
            $error = 'A review note is required when rejecting a document.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $error], 422);
            }

            return back()->withErrors(['review_notes' => $error]);
        }

        $oldStatus = $document->status;

        $document->update([
            'status'       => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
        ]);

        ActivityLog::logActivity(
            'document_reviewed',
            "Document '{$document->original_filename}' status changed from {$oldStatus} to {$validated['status']}",
            $document->application,
            ['status' => $oldStatus],
            ['status' => $validated['status'], 'review_notes' => $validated['review_notes'] ?? null],
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Document ' . $validated['status'] . ' successfully.',
                'document' => $document->fresh(),
            ]);
        }

        return back()->with('success', 'Document ' . ucfirst($validated['status']) . ' successfully.');
    }
}