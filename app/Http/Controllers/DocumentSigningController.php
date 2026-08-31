<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentSigningController extends Controller
{
    /**
     * Show the document to the client for review and signing.
     */
    public function show(Application $application): View
    {
        abort_if(
            $application->isDocumentSigningSigned(),
            403,
            'This document has already been signed.'
        );

        abort_if(
            ! $application->hasDocumentSigningFile(),
            403,
            'This document is not ready yet.'
        );

        $fileUrl = URL::signedRoute(
            'applications.document-signing.client.file',
            ['application' => $application->id],
        );

        return view('applications.document-signing', compact('application', 'fileUrl'));
    }

    /**
     * Stream the original, unmodified PDF inline for client review.
     * Nothing is stamped at this point — this is the pre-signature document.
     */
    public function streamFile(Application $application): StreamedResponse
    {
        abort_if(
            $application->isDocumentSigningSigned(),
            403,
            'This document has already been signed.'
        );

        abort_if(
            ! $application->hasDocumentSigningFile(),
            403,
            'This document is not ready yet.'
        );

        return Storage::disk('local')->response(
            $application->document_signing_file_path,
            $application->document_signing_data['original_filename'] ?? null
        );
    }

    /**
     * Handle client signing — store signature once for the whole document.
     */
    public function sign(Request $request, Application $application): RedirectResponse
    {
        abort_if(
            $application->isDocumentSigningSigned(),
            403,
            'This document has already been signed.'
        );

        abort_if(
            ! $application->hasDocumentSigningFile(),
            403,
            'This document is not ready yet.'
        );

        $validated = $request->validate([
            'signature'   => 'required|string',
            'declaration' => 'accepted',
        ]);

        $application->update([
            'document_signing_data' => array_merge($application->document_signing_data ?? [], [
                'signature' => $validated['signature'],
                'signed_at' => now()->toDateTimeString(),
                'signed_ip' => $request->ip(),
            ]),
        ]);

        ActivityLog::logActivity(
            'document_signing_signed',
            'Document signed by client',
            $application
        );

        ActivityLog::logActivity(
            'document_generated',
            'Document signed by client',
            $application,
            null,
            [
                'doc_type'  => 'signing',
                'doc_label' => 'Signed Document PDF',
                'saved'     => null,
            ]
        );

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Document signed successfully. Thank you.');
    }
}
