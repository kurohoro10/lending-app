<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ActivityLog;
use App\Support\BusinessDeclarationData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessDeclarationController extends Controller
{
    /**
     * Show the business declaration form to the client.
     */
    public function show(Application $application): View
    {
        abort_if(
            $application->business_declaration_signed_at,
            403,
            'This declaration has already been signed.'
        );

        abort_if(
            ! $application->business_declaration_sent_at,
            403,
            'This declaration is not ready yet.'
        );

        $declarationData = BusinessDeclarationData::for($application);

        return view('applications.business-declaration', compact('application', 'declarationData'));
    }

    /**
     * Handle client signature submission.
     */
    public function sign(Request $request, Application $application): RedirectResponse
    {
        abort_if(
            $application->business_declaration_signed_at,
            403,
            'This declaration has already been signed.'
        );

        $validated = $request->validate([
            'signature'   => 'required|string',
            'declaration' => 'accepted',
        ]);

        $application->update([
            'business_declaration_signed_at' => now(),
        ]);

        // Store signature data in a dedicated JSON column or reuse guarantor_data
        // We'll piggyback on guarantor_data since it's already a JSON column
        $guarantorData = $application->guarantor_data ?? [];
        $guarantorData['business_declaration'] = [
            'signature'  => $validated['signature'],
            'signed_at'  => now()->toDateTimeString(),
            'signed_ip'  => $request->ip(),
        ];

        $application->update(['guarantor_data' => $guarantorData]);

        ActivityLog::logActivity(
            'business_declaration_signed',
            'Business declaration signed by client',
            $application
        );

        ActivityLog::logActivity(
            'document_generated',
            'Business declaration signed by client',
            $application,
            null,
            [
                'doc_type'  => 'declaration',
                'doc_label' => 'Business Declaration PDF',
                'saved'     => null,
            ]
        );

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Business declaration signed successfully. Thank you.');
    }
}