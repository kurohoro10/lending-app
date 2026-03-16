<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Document;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function store(Request $request, Application $application)
    {
        $this->authorize('uploadDocument', $application);

        // ── Log every inbound upload attempt for production debugging ─────────
        Log::info('[DocumentUpload] store() called', [
            'application_id'    => $application->id,
            'user_id'           => auth()->id(),
            'has_file'          => $request->hasFile('document'),
            'file_valid'        => $request->hasFile('document') && $request->file('document')?->isValid(),
            'file_error_code'   => $request->hasFile('document') ? $request->file('document')?->getError() : null,
            'document_category' => $request->input('document_category'),
            'content_type'      => $request->header('Content-Type'),
            'request_method'    => $request->method(),
        ]);

        try {
            $validated = $request->validate([
                'document'          => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
                'document_category' => 'required|string|in:id,income,bank,assets,liabilities,employment,other',
                'document_type'     => 'nullable|string|max:255',
                'description'       => 'nullable|string|max:500',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('[DocumentUpload] Validation failed', [
                'application_id' => $application->id,
                'errors'         => $e->errors(),
                'file_size'      => $request->hasFile('document') ? $request->file('document')?->getSize() : null,
                'mime_type'      => $request->hasFile('document') ? $request->file('document')?->getMimeType() : null,
                'client_mime'    => $request->hasFile('document') ? $request->file('document')?->getClientMimeType() : null,
            ]);
            throw $e;
        }

        try {
            $file             = $request->file('document');
            $originalFilename = $file->getClientOriginalName();

            // getClientOriginalExtension() can return an empty string in production
            // when the OS or browser doesn't send a file extension in the name.
            // Fall back to guessing from MIME type so storeAs() never receives an
            // empty filename segment (which throws ValueError: Path cannot be empty).
            $extension = $file->getClientOriginalExtension();
            if (empty($extension)) {
                $extension = $file->guessExtension() // uses finfo MIME → ext map
                    ?? $file->guessClientExtension() // uses client MIME type
                    ?? 'bin';                        // last resort

                Log::warning('[DocumentUpload] Empty extension from getClientOriginalExtension(), fell back to guess', [
                    'application_id' => $application->id,
                    'original_name'  => $originalFilename,
                    'guessed_ext'    => $extension,
                    'mime_type'      => $file->getMimeType(),
                    'client_mime'    => $file->getClientMimeType(),
                ]);
            }

            $storedFilename = Str::uuid() . '.' . $extension;
            $storagePath    = 'documents/' . $application->id;

            Log::info('[DocumentUpload] Attempting to store file', [
                'application_id'  => $application->id,
                'original_name'   => $originalFilename,
                'stored_filename' => $storedFilename,
                'storage_path'    => $storagePath,
                'file_size'       => $file->getSize(),
                'mime_type'       => $file->getMimeType(),
                'client_mime'     => $file->getClientMimeType(),
                'disk'            => 'local',
            ]);

            Log::info('[DocumentUpload] Disk root', [
                'disk_root' => config('filesystems.disks.local.root')
            ]);

            // AFTER — read file contents directly, bypassing getRealPath() issues on Windows:
            $fileContents = file_get_contents($file->getRealPath() ?: $file->getPathname());

            if ($fileContents === false) {
                throw new \RuntimeException('Could not read uploaded file from temporary path.');
            }

            $filePath = $storagePath . '/' . $storedFilename;
            $stored   = Storage::disk('local')->put($filePath, $fileContents);

            if (!$stored) {
                $filePath = false;
            }

            if ($filePath === false) {
                Log::error('[DocumentUpload] storeAs() returned false — check storage permissions', [
                    'application_id' => $application->id,
                    'storage_path'   => $storagePath,
                    'stored_filename'=> $storedFilename,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'File could not be saved. Please check storage permissions.',
                ], 500);
            }

            Log::info('[DocumentUpload] File stored successfully', [
                'application_id' => $application->id,
                'file_path'      => $filePath,
            ]);

            $document = $application->documents()->create([
                'uploaded_by'       => auth()->id(),
                'document_category' => $validated['document_category'],
                'document_type'     => $validated['document_type'] ?? null,
                'original_filename' => $originalFilename,
                'stored_filename'   => $storedFilename,
                'file_path'         => $filePath,
                'mime_type'         => $file->getMimeType(),
                'file_size'         => $file->getSize(),
                'description'       => $validated['description'] ?? null,
                'upload_ip'         => $request->ip(),
            ]);

            Log::info('[DocumentUpload] Document record created', [
                'document_id'    => $document->id,
                'application_id' => $application->id,
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

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Document uploaded successfully.',
                    'document' => $document,
                ], 201);
            }

            return back()->with('success', 'Document uploaded successfully.');

        } catch (\Throwable $e) {
            Log::error('[DocumentUpload] Unexpected error in store()', [
                'application_id' => $application->id,
                'user_id'        => auth()->id(),
                'exception'      => get_class($e),
                'message'        => $e->getMessage(),
                'file'           => $e->getFile(),
                'line'           => $e->getLine(),
                'trace'          => collect($e->getTrace())->take(5)->map(fn($f) => [
                    'file'     => $f['file'] ?? null,
                    'line'     => $f['line'] ?? null,
                    'function' => $f['function'] ?? null,
                ])->all(),
            ]);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while uploading. Please try again.',
                    // Only expose detail in debug mode — never in production
                    'detail'  => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }

            return back()->with('error', 'An error occurred while uploading. Please try again.');
        }
    }

    public function download(Document $document)
    {
        $this->authorize('view', $document->application);

        if (!Storage::exists($document->file_path)) {
            Log::warning('[DocumentDownload] File not found on disk', [
                'document_id' => $document->id,
                'file_path'   => $document->file_path,
            ]);
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

        $filename   = $document->original_filename;
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

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Document deleted successfully.',
                'deleted_id' => $documentId,
            ]);
        }

        return back()->with('success', 'Document deleted successfully.');
    }

    public function updateStatus(Request $request, Document $document)
    {
        $this->authorize('review', $document->application);

        $validated = $request->validate([
            'status'       => 'required|in:pending,approved,rejected',
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

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Document status updated successfully.',
                'document' => $document,
            ]);
        }

        return back()->with('success', 'Document status updated successfully.');
    }
}