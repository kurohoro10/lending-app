<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Document;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function store(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'document' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            'document_category' => 'required|string|in:id,income,bank,assets,liabilities,employment,other',
            'document_type' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('document');
        $originalFilename = $file->getClientOriginalName();
        $storedFilename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs(
            'documents/' . $application->id,
            $storedFilename,
            'local'
        );

        $document = $application->documents()->create([
            'uploaded_by' => auth()->id(),
            'document_category' => $validated['document_category'],
            'document_type' => $validated['document_type'] ?? null,
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'file_path' => $filePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'description' => $validated['description'] ?? null,
            'upload_ip' => $request->ip(),
        ]);

        ActivityLog::logActivity(
            'uploaded',
            "Uploaded document: {$originalFilename}",
            $document,
            null,
            [
                'category' => $validated['document_category'],
                'filename' => $originalFilename,
            ]
        );

        // Check if the request expects JSON (AJAX request)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'document' => $document
            ], 201);
        }

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function download(Document $document)
    {
        $this->authorize('view', $document->application);

        if (!Storage::exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        ActivityLog::logActivity(
            'downloaded',
            "Downloaded document: {$document->original_filename}",
            $document
        );

        return Storage::download($document->file_path, $document->original_filename);
    }

    public function destroy(Request $request, Application $application, Document $document)
    {
        $this->authorize('update', $application);

        $filename = $document->original_filename;
        $documentId = $document->id;

        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        $document->delete();

        ActivityLog::logActivity(
            'deleted',
            "Deleted document: {$filename}",
            $application
        );

        // Check if the request expects JSON (AJAX request)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully.',
                'deleted_id' => $documentId
            ], 200);
        }

        return back()->with('success', 'Document deleted successfully.');
    }

    public function updateStatus(Request $request, Document $document)
    {
        $this->authorize('review', $document->application);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'review_notes' => 'nullable|string',
        ]);

        $oldStatus = $document->status;

        $document->update(array_merge($validated, [
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]));

        ActivityLog::logActivity(
            'reviewed',
            "Document {$document->original_filename} status changed from {$oldStatus} to {$validated['status']}",
            $document,
            ['status' => $oldStatus],
            $validated
        );

        // Check if the request expects JSON (AJAX request)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Document status updated successfully.',
                'document' => $document
            ], 200);
        }

        return back()->with('success', 'Document status updated successfully.');
    }
}
