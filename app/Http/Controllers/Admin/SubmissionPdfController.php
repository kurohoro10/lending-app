<?php
// NEW FILE: app/Http/Controllers/Admin/SubmissionPdfController.php
// After creating this, update the route in routes/admin/adminRoutes.php
// (see the route snippet at the bottom of this file).

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SubmissionPdfController extends Controller
{
    /**
     * Download the pre-generated submission PDF for an application.
     *
     * Security: only filenames ending in .pdf are served.
     * Logs a document_generated event to the document timeline so every
     * download is visible in the quick-actions sidebar history.
     *
     * @param  string  $filename  e.g. loan-application-APP-2024-000123.pdf
     * @return BinaryFileResponse
     */
    public function download(string $filename): Response
    {
        if (! str_ends_with($filename, '.pdf')) {
            abort(404);
        }
 
        $storagePath = GenerateSubmissionPdf::FOLDER . '/' . $filename;
 
        if (! Storage::disk(GenerateSubmissionPdf::DISK)->exists($storagePath)) {
            abort(404);
        }
 
        $bytes = Storage::disk(GenerateSubmissionPdf::DISK)->get($storagePath);
 
        return response($bytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}