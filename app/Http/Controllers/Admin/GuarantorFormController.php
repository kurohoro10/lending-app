<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Notifications\Admin\GuarantorFormNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class GuarantorFormController extends Controller
{
    /**
     * Show the admin guarantor form page.
     * Pre-fills from existing application data where possible.
     */
    public function show(Application $application): View
    {
        abort_if(
            $application->status !== Application::STATUS_APPROVED,
            403,
            'Guarantor form is only available for approved applications.'
        );

        // Pre-fill guarantor_data defaults from application if not yet saved
        $guarantorData = $application->guarantor_data ?? $this->buildDefaults($application);

        return view('admin.applications.guarantor-form', compact('application', 'guarantorData'));
    }

    /**
     * Save the guarantor form data and stamp guarantor_form_requested_at.
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        abort_if(
            $application->status !== Application::STATUS_APPROVED,
            403
        );

        $validated = $request->validate([
            // Section 1 — Guarantor Details
            'guarantor_full_name'    => 'required|string|max:255',
            'guarantor_dob'          => 'required|date',
            'guarantor_id_number'    => 'required|string|max:255',
            'guarantor_residential'  => 'required|string|max:500',
            'guarantor_postal'       => 'nullable|string|max:500',
            'guarantor_phone'        => 'required|string|max:50',
            'guarantor_email'        => 'required|email|max:255',
            'guarantor_occupation'   => 'required|string|max:255',
            'guarantor_employer'     => 'required|string|max:255',
            'guarantor_abn'          => 'nullable|string|max:50',

            // Guarantor signature — required
            'guarantor_signature'    => 'required|string',

            // Section 2 — Borrower Details
            'borrower_name'          => 'required|string|max:255',
            'borrower_abn'           => 'nullable|string|max:50',
            'borrower_address'       => 'required|string|max:500',
            'facility_type'          => 'required|string|max:255',
            'loan_amount'            => 'required|string|max:50',

            // Witness — optional fields, optional signature
            'witness_full_name'      => 'nullable|string|max:255',
            'witness_occupation'     => 'nullable|string|max:255',
            'witness_signature'      => 'nullable|string',

            // Solicitor — all optional
            'solicitor_name'         => 'nullable|string|max:255',
            'solicitor_firm'         => 'nullable|string|max:255',
            'solicitor_signature'    => 'nullable|string',
        ]);

        $application->update([
            'guarantor_data'              => $validated,
            'guarantor_form_requested_at' => $application->guarantor_form_requested_at ?? now(),
        ]);

        ActivityLog::logActivity(
            'guarantor_form_saved',
            'Guarantor form filled by admin',
            $application
        );

        return back()->with('success', 'Guarantor form saved successfully.');
    }

    /**
     * Send the guarantor form link to the client and stamp guarantor_form_request_url.
     */
    public function send(Application $application): RedirectResponse
    {
        abort_if(! $application->hasGuarantorData(), 403, 'Save the form before sending.');
        abort_if($application->isGuarantorFormSigned(), 403, 'Guarantor form already signed.');

        $signedUrl = URL::signedRoute(
            'applications.guarantor-form.client.show',
            ['application' => $application->id],
        );

        $application->update([
            'guarantor_form_request_url' => $signedUrl,
        ]);

        $application->user->notify(new GuarantorFormNotification($application, $signedUrl));

        ActivityLog::logActivity(
            'guarantor_form_sent',
            'Guarantor form link sent to client',
            $application
        );

        return back()->with('success', 'Guarantor form sent to client successfully.');
    }

    /**
     * Build default values pre-filled from application data.
     */
    private function buildDefaults(Application $application): array
    {
        $borrower = $application->borrowerInformation;

        return [
            'guarantor_full_name'   => '',
            'guarantor_dob'         => '',
            'guarantor_id_number'   => '',
            'guarantor_residential' => '',
            'guarantor_postal'      => '',
            'guarantor_phone'       => '',
            'guarantor_email'       => '',
            'guarantor_occupation'  => '',
            'guarantor_employer'    => '',
            'guarantor_abn'         => '',
            'guarantor_signature'   => '',  // new

            'borrower_name'         => $borrower?->company_name ?? $application->user->name,
            'borrower_abn'          => $borrower?->abn ?? '',
            'borrower_address'      => $borrower?->registered_address ?? '',
            'facility_type'         => \App\Support\LoanPurpose::label($application->loan_purpose),
            'loan_amount'           => '$' . number_format($application->loan_amount, 2),

            'witness_full_name'     => '',
            'witness_occupation'    => '',
            'witness_signature'     => '',  // new

            'solicitor_name'        => '',
            'solicitor_firm'        => '',
            'solicitor_signature'   => '',  // new
        ];
    }

    public function viewSigned(Application $application): View
    {
        abort_if(! $application->isGuarantorFormSigned(), 404);

        $guarantorData = $application->guarantor_data;

        return view('admin.applications.guarantor-form-signed', compact('application', 'guarantorData'));
    }

    /**
     * Toggle whether a guarantor is required for this application.
     */
    public function toggleGuarantorRequired(Request $request, Application $application): RedirectResponse
    {
        abort_if(
            $application->status !== Application::STATUS_APPROVED,
            403
        );

        $required = $request->boolean('guarantor_required');

        $application->update(['guarantor_required' => $required]);

        ActivityLog::logActivity(
            'guarantor_requirement_updated',
            $required ? 'Guarantor marked as required' : 'Guarantor marked as not required',
            $application
        );

        return back()->with('success', $required
            ? 'Guarantor marked as required.'
            : 'Guarantor requirement removed.'
        );
    }
}