<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Notifications\Admin\BusinessDeclarationNotification;
use App\Support\BusinessDeclarationData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class BusinessDeclarationController extends Controller
{
    /**
     * Show the admin business declaration editor.
     * Pre-fills from application data via BusinessDeclarationData.
     */
    public function show(Application $application): View
    {
        abort_if(
            $application->status !== Application::STATUS_APPROVED,
            403,
            'Business declaration is only available for approved applications.'
        );

        $declarationData = BusinessDeclarationData::for($application);

        return view('admin.applications.business-declaration', compact('application', 'declarationData'));
    }

    /**
     * Save the business declaration field overrides and stamp
     * business_declaration_requested_at.
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        abort_if(
            $application->status !== Application::STATUS_APPROVED,
            403
        );

        abort_if($application->business_declaration_signed_at, 403, 'Declaration already signed.');

        $validated = $request->validate([
            'borrower_name'       => 'required|string|max:255',
            'loan_purpose'        => 'required|string|max:255',
            'loan_amount_display' => 'required|string|max:50',
        ]);

        $application->update([
            'business_declaration_data'        => $validated,
            'business_declaration_requested_at' => $application->business_declaration_requested_at ?? now(),
        ]);

        ActivityLog::logActivity(
            'business_declaration_saved',
            'Business declaration prepared by admin',
            $application
        );

        return back()->with('success', 'Business declaration saved successfully.');
    }

    /**
     * Send a signed URL to the client to complete the business declaration.
     */
    public function send(Application $application): RedirectResponse
    {
        abort_if($application->status !== Application::STATUS_APPROVED, 403);
        abort_if(! $application->isLoanDeedSigned(), 403, 'Loan deed must be signed first.');
        abort_if(! $application->hasBusinessDeclarationData(), 403, 'Save the declaration before sending.');
        abort_if($application->business_declaration_signed_at, 403, 'Declaration already signed.');

        $signedUrl = URL::signedRoute(
            'applications.business-declaration.show',
            ['application' => $application->id],
        );

        $application->update([
            'business_declaration_sent_at' => now(),
        ]);

        $application->user->notify(new BusinessDeclarationNotification($application, $signedUrl));

        ActivityLog::logActivity(
            'business_declaration_sent',
            'Business declaration sent to client',
            $application
        );

        return back()->with('success', 'Business declaration sent to client successfully.');
    }

    /**
     * Show the signed declaration to the admin.
     */
    public function view(Application $application): View
    {
        abort_if(! $application->business_declaration_signed_at, 404);

        $declarationData = BusinessDeclarationData::for($application);

        return view('admin.applications.business-declaration-signed', compact('application', 'declarationData'));
    }

    /**
     * Download the signed business purpose declaration as a PDF.
     * Rendered on demand from persisted data — never from request input.
     */
    public function downloadPdf(Application $application): Response
    {
        abort_if(! $application->business_declaration_signed_at, 404);
 
        $declarationData = BusinessDeclarationData::for($application);
 
        $pdf = Pdf::loadView('admin.applications.pdf.business-declaration', [
            'application'     => $application,
            'declarationData' => $declarationData,
            'generatedAt'     => now(),
        ]);
 
        $pdf->setPaper('a4', 'portrait');
 
        ActivityLog::logActivity(
            'document_generated',
            'Business declaration PDF downloaded',
            $application,
            null,
            ['doc_type' => 'declaration', 'doc_label' => 'Business Declaration PDF']
        );
 
        return $pdf->download('business-declaration-' . $application->application_number . '.pdf');
    }
}
