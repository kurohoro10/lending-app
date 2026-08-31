<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ActivityLog;
use App\Support\LoanDeedData;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoanDeedController extends Controller
{
    /**
     * Show the loan deed to the client for review and signing.
     */
    public function show(Application $application): View
    {
        abort_if(
            $application->isLoanDeedSigned(),
            403,
            'This loan deed has already been signed.'
        );

        abort_if(
            ! $application->hasLoanDeedData(),
            403,
            'This document is not ready yet.'
        );

        $deedData = LoanDeedData::for($application);

        return view('applications.loan-deed', compact('application', 'deedData'));
    }

    /**
     * Handle client signing — store signature, stamp loan_deed_signed_at.
     */
    public function sign(Request $request, Application $application): RedirectResponse
    {
        abort_if(
            $application->isLoanDeedSigned(),
            403,
            'This loan deed has already been signed.'
        );

        abort_if(
            ! $application->hasLoanDeedData(),
            403,
            'This document is not ready yet.'
        );

        $validated = $request->validate([
            'signature'   => 'required|string',
            'declaration' => 'accepted',
        ]);

        $deedData = array_merge(
            $application->loan_deed_data,
            [
                'client_signature' => $validated['signature'],
                'signed_at'        => now()->toDateTimeString(),
                'signed_ip'        => $request->ip(),
            ]
        );

        $application->update([
            'loan_deed_data'      => $deedData,
            'loan_deed_signed_at' => now(),
        ]);

        ActivityLog::logActivity(
            'loan_deed_signed',
            'Loan deed signed by client',
            $application
        );

        ActivityLog::logActivity(
            'document_generated',
            'Loan deed signed by client',
            $application,
            null,
            [
                'doc_type'  => 'loan_deed',
                'doc_label' => 'Loan Deed PDF',
                'saved'     => null,
            ]
        );

        return redirect()
            ->route('applications.show', $application)
            ->with('success', 'Loan deed signed successfully. Thank you.');
    }
}
