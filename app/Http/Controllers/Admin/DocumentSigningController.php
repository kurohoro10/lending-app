<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Application\StampSignedDocument;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Notifications\Admin\DocumentSigningNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentSigningController extends Controller
{
    /**
     * Show the admin document-signing editor (upload / replace the source PDF).
     */
    public function show(Application $application): View
    {
        abort_if(
            $application->status !== Application::STATUS_APPROVED,
            403,
            'Document signing is only available for approved applications.'
        );

        return view('admin.applications.document-signing', compact('application'));
    }

    /**
     * Upload (or replace) the original PDF to be signed.
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        abort_if(
            $application->status !== Application::STATUS_APPROVED,
            403
        );

        abort_if($application->isDocumentSigningSigned(), 403, 'Document already signed.');

        $validated = $request->validate([
            'document' => 'required|file|mimes:pdf|max:20480',
        ]);

        // Remove the previous file if one exists, to avoid orphaned uploads.
        if ($application->document_signing_file_path && Storage::disk('local')->exists($application->document_signing_file_path)) {
            Storage::disk('local')->delete($application->document_signing_file_path);
        }

        $file = $validated['document'];
        $path = $file->storeAs(
            'document-signing/' . $application->id,
            Str::uuid() . '.pdf',
            'local'
        );

        $application->update([
            'document_signing_file_path' => $path,
            'document_signing_data' => array_merge($application->document_signing_data ?? [], [
                'original_filename' => $file->getClientOriginalName(),
                'requested_at'      => now()->toDateTimeString(),
            ]),
        ]);

        ActivityLog::logActivity(
            'document_signing_saved',
            'Document uploaded for signing by admin',
            $application
        );

        return back()->with('success', 'Document saved successfully.');
    }

    /**
     * Send a signed URL to the client to review and sign the document.
     */
    public function send(Application $application): RedirectResponse
    {
        abort_if(! $application->hasDocumentSigningFile(), 403, 'Save the document before sending.');
        abort_if($application->isDocumentSigningSigned(), 403, 'Document already signed.');
        abort_if(! $application->business_declaration_signed_at, 403, 'Business declaration must be signed first.');

        $signedUrl = URL::signedRoute(
            'applications.document-signing.client.show',
            ['application' => $application->id],
        );

        $application->update([
            'document_signing_data' => array_merge($application->document_signing_data ?? [], [
                'request_url' => $signedUrl,
                'sent_at'     => now()->toDateTimeString(),
            ]),
        ]);

        $application->user->notify(new DocumentSigningNotification($application, $signedUrl));

        ActivityLog::logActivity(
            'document_signing_sent',
            'Document signing link sent to client',
            $application
        );

        return back()->with('success', 'Document sent to client successfully.');
    }

    /**
     * View the signed document's metadata (no PDF re-render).
     */
    public function viewSigned(Application $application): View
    {
        abort_if(! $application->isDocumentSigningSigned(), 404);

        return view('admin.applications.document-signing-signed', compact('application'));
    }

    /**
     * Download the signed document — the original PDF with the client's
     * signature stamped on every page. Generated fresh on every request
     * from persisted data only; nothing is saved to disk.
     */
    public function downloadPdf(Application $application): Response
    {
        abort_if(! $application->isDocumentSigningSigned(), 404);
 
        $bytes = (new StampSignedDocument)->execute($application);
 
        $filename = 'signed-' . $application->application_number . '.pdf';
 
        ActivityLog::logActivity(
            'document_generated',
            'Signed document PDF downloaded',
            $application,
            null,
            ['doc_type' => 'signing', 'doc_label' => 'Signed Document PDF']
        );
 
        return response($bytes, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
