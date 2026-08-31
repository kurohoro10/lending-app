<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GuarantorFormController extends Controller
{
    /**
     * Show the guarantor form to the client.
     * Zone 1 is read-only, Zone 2 is editable.
     */
    public function show(Application $application): View
    {
        abort_if(
            $application->isGuarantorFormSigned(),
            403,
            'This guarantor form has already been signed.'
        );

        abort_if(
            ! $application->hasGuarantorData(),
            403,
            'This form is not ready yet.'
        );

        $guarantorData = $application->guarantor_data;

        return view('applications.guarantor-form', compact('application', 'guarantorData'));
    }

    /**
     * Handle client submission — save editable fields,
     * store signature, stamp guarantor_form_signed_at.
     */
    public function sign(Request $request, Application $application): RedirectResponse
    {
        abort_if(
            $application->isGuarantorFormSigned(),
            403,
            'This guarantor form has already been signed.'
        );

        abort_if(
            ! $application->hasGuarantorData(),
            403,
            'This form is not ready yet.'
        );

        $validated = $request->validate([
            // Zone 2 — client editable (borrower details)
            'borrower_name'    => 'required|string|max:255',
            'borrower_abn'     => 'nullable|string|max:50',
            'borrower_address' => 'required|string|max:500',
            'facility_type'    => 'required|string|max:255',
            'loan_amount'      => 'required|string|max:50',

            // Zone 2 — solicitor (client editable)
            'solicitor_name'   => 'nullable|string|max:255',
            'solicitor_firm'   => 'nullable|string|max:255',

            // Zone 3 — signature
            'signature'        => 'required|string',
            'declaration'      => 'accepted',
        ]);

        // Merge client editable fields back into existing guarantor_data
        $guarantorData = array_merge(
            $application->guarantor_data,
            [
                'borrower_name'    => $validated['borrower_name'],
                'borrower_abn'     => $validated['borrower_abn'] ?? '',
                'borrower_address' => $validated['borrower_address'],
                'facility_type'    => $validated['facility_type'],
                'loan_amount'      => $validated['loan_amount'],
                'solicitor_name'   => $validated['solicitor_name'] ?? '',
                'solicitor_firm'   => $validated['solicitor_firm'] ?? '',
                'signature'        => $validated['signature'],
                'signed_at'        => now()->toDateTimeString(),
                'signed_ip'        => $request->ip(),
            ]
        );

        $application->update([
            'guarantor_data'         => $guarantorData,
            'guarantor_form_signed_at' => now(),
        ]);

        ActivityLog::logActivity(
            'guarantor_form_signed',
            'Guarantor form signed by client',
            $application
        );

        ActivityLog::logActivity(
            'document_generated',
            'Guarantor form signed by client',
            $application,
            null,
            [
                'doc_type'  => 'guarantor',
                'doc_label' => 'Guarantor Form PDF',
                'saved'     => null,
            ]
        );

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Guarantor form signed successfully. Thank you.');
    }
}