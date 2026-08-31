<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ActivityLog;
use App\Notifications\Admin\LoanDeedNotification;
use App\Support\LoanDeedData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class LoanDeedController extends Controller
{
    /**
     * Show the admin loan deed editor.
     * Pre-fills from application data via LoanDeedData.
     */
    public function show(Application $application): View
    {
        abort_if(
            $application->status !== Application::STATUS_APPROVED,
            403,
            'Loan deed is only available for approved applications.'
        );

        $deedData = LoanDeedData::for($application);

        return view('admin.applications.loan-deed', compact('application', 'deedData'));
    }

    /**
     * Save the loan deed data and stamp loan_deed_requested_at.
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        abort_if(
            $application->status !== Application::STATUS_APPROVED,
            403
        );

        abort_if($application->isLoanDeedSigned(), 403, 'Loan deed already signed.');

        $validated = $request->validate([
            // Parties
            'borrower_name'       => 'required|string|max:255',
            'borrower_abn'        => 'nullable|string|max:50',
            'borrower_acn'        => 'nullable|string|max:50',
            'borrower_address'    => 'required|string|max:500',
            'borrower_email'      => 'required|email|max:255',
            'borrower_phone'      => 'nullable|string|max:50',
            'guarantor_name'      => 'nullable|string|max:255',
            'guarantor_email'     => 'nullable|email|max:255',
            'guarantor_address'   => 'nullable|string|max:500',

            // Financial table (Loan Detail) — total_repayments/total_repayment_amount/total_interest
            // are computed below, never accepted from the request
            'principal_sum'          => 'required|string|max:50',
            'annual_percentage_rate' => 'required|string|max:50',
            'repayment_cycle'        => 'required|string|max:50',
            'loan_term_weeks'        => 'nullable|numeric|max:9999',
            'amount_per_repayment'   => 'nullable|string|max:50',
            'first_repayment_date'   => 'nullable|string|max:50',

            // Fees
            'application_fee'            => 'nullable|string|max:50',
            'security_search_fee'        => 'nullable|string|max:50',
            'legal_fee'                  => 'nullable|string|max:50',
            'security_registration_fee'  => 'nullable|string|max:50',
            'valuation_fee'              => 'nullable|string|max:50',
            'monthly_account_fee'        => 'nullable|string|max:50',
            'annual_review_fee'          => 'nullable|string|max:50',
            'exit_fee'                   => 'nullable|string|max:100',
            'break_cost'                 => 'nullable|string|max:100',
            'include_upfront_fees_in_loan_amount'    => 'sometimes|boolean',
            'include_monthly_fee_in_first_repayment' => 'sometimes|boolean',

            // Schedule values
            'disclosure_date'       => 'nullable|string|max:50',
            'default_rate'          => 'nullable|string|max:50',
            'permitted_encumbrance' => 'nullable|string|max:500',

            // Security
            'security.properties'                              => 'nullable|array',
            'security.properties.*.address'                    => 'nullable|string|max:500',
            'security.properties.*.owners'                     => 'nullable|array',
            'security.properties.*.owners.*'                   => 'nullable|string|max:255',
            'security.properties.*.owners_are_guarantors'      => 'sometimes|boolean',
            'security.properties.*.valuation'                  => 'nullable|string|max:100',
            'security.properties.*.volume_folio'               => 'nullable|string|max:255',
            'security.properties.*.council_rate_notice_sighted' => 'sometimes|boolean',
            'security.vehicles'                                => 'nullable|array',
            'security.vehicles.*.brand'                        => 'nullable|string|max:255',
            'security.vehicles.*.model'                        => 'nullable|string|max:255',
            'security.vehicles.*.vin'                          => 'nullable|string|max:100',
            'security.vehicles.*.price'                        => 'nullable|string|max:50',
            'security.vehicles.*.km_travelled'                 => 'nullable|string|max:50',

            // Witness (optional)
            'witness_name'       => 'nullable|string|max:255',
            'witness_occupation' => 'nullable|string|max:255',
            'witness_signature'  => 'nullable|string',
        ]);

        // Checkboxes are absent from the request entirely when unchecked
        $validated['include_upfront_fees_in_loan_amount']    = $request->boolean('include_upfront_fees_in_loan_amount');
        $validated['include_monthly_fee_in_first_repayment'] = $request->boolean('include_monthly_fee_in_first_repayment');

        // Security — normalize per-card checkboxes, drop blank owner rows, drop entirely-blank cards
        $validated['security']['properties'] = collect($validated['security']['properties'] ?? [])
            ->map(function ($property, $i) use ($request) {
                $property['owners'] = array_values(array_filter(
                    $property['owners'] ?? [],
                    fn ($owner) => trim((string) $owner) !== ''
                ));
                $property['owners_are_guarantors']       = $request->boolean("security.properties.$i.owners_are_guarantors");
                $property['council_rate_notice_sighted'] = $request->boolean("security.properties.$i.council_rate_notice_sighted");

                return $property;
            })
            ->filter(fn ($property) => !empty($property['address']) || !empty($property['owners'])
                || !empty($property['valuation']) || !empty($property['volume_folio']))
            ->values()
            ->all();

        $validated['security']['vehicles'] = collect($validated['security']['vehicles'] ?? [])
            ->filter(fn ($vehicle) => !empty($vehicle['brand']) || !empty($vehicle['model'])
                || !empty($vehicle['vin']) || !empty($vehicle['price']) || !empty($vehicle['km_travelled']))
            ->values()
            ->all();

        // Total Number of Repayments — computed from Loan Term (Weeks) + Repayment Frequency,
        // then fed into the existing repayment-schedule generator exactly as before (that method
        // itself is unchanged — it already reads total_repayments from $data).
        $validated['total_repayments'] = $this->calculateTotalRepayments(
            $validated['loan_term_weeks'] ?? '',
            $validated['repayment_cycle'] ?? ''
        );

        // Schedule 2 — always regenerated from the current repayment settings above, never manually entered
        $validated['repayment_schedule'] = $this->generateRepaymentSchedule($validated);

        // Total Repayment Amount — summed from the schedule just generated (which already applies
        // the monthly-fee-in-first-repayment rule), not recalculated separately.
        $validated['total_repayment_amount'] = $this->sumRepaymentAmounts($validated['repayment_schedule']);

        // Loan Lent Including Fee — Loan Amount plus the settlement-time fees that are actually
        // capitalized into the loan (Application/Legal/Valuation/Security Search/Security
        // Registration), only when the "Include Upfront Fees in Loan Amount" checkbox is set.
        // Exit Fee, Break Cost, and the fixed Manual Allocation Fee are deliberately excluded —
        // they're contingent/later charges, not money advanced at drawdown (mirrors how the deed's
        // own financial-table.blade.php already keeps Exit Fee/Break Cost out of its "due
        // immediately" fee group).
        $validated['loan_lent_including_fee'] = $this->calculateLoanLentIncludingFee($validated);

        // Total Interest Payable — Total Repayment Amount minus Loan Lent Including Fee.
        $validated['total_interest'] = $this->calculateTotalInterest(
            $validated['total_repayment_amount'],
            $validated['loan_lent_including_fee']
        );

        // Preserve non-form keys already persisted (directors snapshot, signatures)
        $existing = $application->loan_deed_data ?? [];

        $application->update([
            'loan_deed_data'         => array_merge($existing, $validated),
            'loan_deed_requested_at' => $application->loan_deed_requested_at ?? now(),
        ]);

        ActivityLog::logActivity(
            'loan_deed_saved',
            'Loan deed prepared by admin',
            $application
        );

        return back()->with('success', 'Loan deed saved successfully.');
    }

    /**
     * Send the loan deed link to the client and stamp loan_deed_request_url.
     */
    public function send(Application $application): RedirectResponse
    {
        abort_if(! $application->hasLoanDeedData(), 403, 'Save the loan deed before sending.');
        abort_if($application->isLoanDeedSigned(), 403, 'Loan deed already signed.');
        abort_if(
            $application->requiresGuarantor() && ! $application->isGuarantorFormSigned(),
            403,
            'The guarantor form must be signed before the loan deed can be sent.'
        );

        $signedUrl = URL::signedRoute(
            'applications.loan-deed.client.show',
            ['application' => $application->id],
        );

        $application->update([
            'loan_deed_request_url' => $signedUrl,
        ]);

        $application->user->notify(new LoanDeedNotification($application, $signedUrl));

        ActivityLog::logActivity(
            'loan_deed_sent',
            'Loan deed link sent to client',
            $application
        );

        return back()->with('success', 'Loan deed sent to client successfully.');
    }

    /**
     * View the signed loan deed (read-only HTML render).
     */
    public function viewSigned(Application $application): View
    {
        abort_if(! $application->isLoanDeedSigned(), 404);

        $deedData = LoanDeedData::for($application);

        return view('admin.applications.loan-deed-signed', compact('application', 'deedData'));
    }

    /**
     * Download the signed loan deed as a PDF.
     * Rendered on demand from persisted data — never from request input.
     */
    public function downloadPdf(Application $application): Response
    {
        abort_if(! $application->isLoanDeedSigned(), 404);
 
        $deedData = LoanDeedData::for($application);
 
        $pdf = Pdf::loadView('admin.applications.pdf.loan-deed', [
            'application' => $application,
            'deedData'    => $deedData,
            'generatedAt' => now(),
        ]);
 
        $pdf->setPaper('a4', 'portrait');
 
        ActivityLog::logActivity(
            'document_generated',
            'Loan deed PDF downloaded',
            $application,
            null,
            ['doc_type' => 'loan_deed', 'doc_label' => 'Loan Deed PDF']
        );
 
        return $pdf->download('loan-deed-' . $application->application_number . '.pdf');
    }

    /**
     * Auto-generate the repayment schedule (Schedule 2) from the Financial Table's repayment
     * settings — first repayment date, repayment cycle, total number of repayments, and amount
     * per repayment — instead of the admin typing each row manually.
     *
     * Returns [] when there isn't enough information to generate from yet (e.g. the admin hasn't
     * filled in the date/cycle/count), leaving Schedule 2 blank rather than erroring the save.
     */
    private function generateRepaymentSchedule(array $data): array
    {
        $firstDate = $data['first_repayment_date'] ?? null;
        $cycle     = $data['repayment_cycle'] ?? null;
        $count     = (int) ($data['total_repayments'] ?? 0);

        if (empty($firstDate) || empty($cycle) || $count < 1) {
            return [];
        }

        $amount = $data['amount_per_repayment'] ?? '';
        $schedule = [];

        for ($i = 0; $i < $count; $i++) {
            $date = \Illuminate\Support\Carbon::parse($firstDate);
            $date = $cycle === 'Monthly'
                ? $date->addMonths($i)
                : $date->addDays(($cycle === 'Fortnightly' ? 14 : 7) * $i);

            $rowAmount = $amount;
            if ($i === 0 && ($data['include_monthly_fee_in_first_repayment'] ?? false)) {
                $rowAmount = $this->addAmountStrings($amount, $data['monthly_account_fee'] ?? '');
            }

            $schedule[] = [
                'date'   => $date->format('d/m/Y'),
                'amount' => $rowAmount,
            ];
        }

        return $schedule;
    }

    /**
     * Add two free-text currency strings (e.g. "$120.00" + "$15") numerically.
     */
    private function addAmountStrings(string $amount, string $fee): string
    {
        $amountNum = $this->parseAmountString($amount);
        $feeNum    = $this->parseAmountString($fee);

        if ($amountNum === 0.0 && $feeNum === 0.0) {
            return $amount;
        }

        return '$' . number_format($amountNum + $feeNum, 2);
    }

    /**
     * Total Number of Repayments — Loan Term (Weeks) ÷ weeks-per-repayment for the selected
     * Repayment Frequency (Weekly = 1, Fortnightly = 2, Monthly = 4), rounded up to the next
     * whole repayment. Returns '' when there isn't enough information yet (mirrors the same
     * "leave blank rather than error" behaviour generateRepaymentSchedule() already uses).
     */
    private function calculateTotalRepayments(string $loanTermWeeks, string $cycle): string
    {
        $weeks = (float) $loanTermWeeks;

        if ($weeks <= 0 || empty($cycle)) {
            return '';
        }

        $weeksPerRepayment = match ($cycle) {
            'Fortnightly' => 2,
            'Monthly'     => 4,
            default       => 1, // Weekly
        };

        return (string) (int) ceil($weeks / $weeksPerRepayment);
    }

    /**
     * Total Repayment Amount — summed from the already-generated repayment schedule, so the
     * monthly-fee-in-first-repayment rule (already applied per row by generateRepaymentSchedule())
     * is never recalculated a second time here.
     */
    private function sumRepaymentAmounts(array $schedule): string
    {
        if (empty($schedule)) {
            return '';
        }

        $total = array_sum(array_map(
            fn ($row) => $this->parseAmountString($row['amount'] ?? ''),
            $schedule
        ));

        return '$' . number_format($total, 2);
    }

    /**
     * Loan Lent Including Fee = Loan Amount + the settlement-time fees that are actually
     * capitalized into the loan (Application/Legal/Valuation/Security Search/Security
     * Registration), only when "Include Upfront Fees in Loan Amount" is checked. Exit Fee, Break
     * Cost, and the fixed Manual Allocation Fee are deliberately excluded.
     */
    private function calculateLoanLentIncludingFee(array $data): string
    {
        $principalSum = $data['principal_sum'] ?? '';

        if ($principalSum === '') {
            return '';
        }

        if (empty($data['include_upfront_fees_in_loan_amount'])) {
            return $principalSum;
        }

        $total = $this->parseAmountString($principalSum)
            + $this->parseAmountString($data['application_fee'] ?? '')
            + $this->parseAmountString($data['legal_fee'] ?? '')
            + $this->parseAmountString($data['valuation_fee'] ?? '')
            + $this->parseAmountString($data['security_search_fee'] ?? '')
            + $this->parseAmountString($data['security_registration_fee'] ?? '');

        return '$' . number_format($total, 2);
    }

    /**
     * Total Interest Payable = Total Repayment Amount − Loan Lent Including Fee.
     */
    private function calculateTotalInterest(string $totalRepaymentAmount, string $loanLentIncludingFee): string
    {
        if ($totalRepaymentAmount === '' || $loanLentIncludingFee === '') {
            return '';
        }

        $total = $this->parseAmountString($totalRepaymentAmount) - $this->parseAmountString($loanLentIncludingFee);

        return '$' . number_format($total, 2);
    }

    /**
     * Extract a numeric value from a free-text currency string (e.g. "$1,200.50" -> 1200.5).
     */
    private function parseAmountString(string $amount): float
    {
        return (float) preg_replace('/[^0-9.\-]/', '', $amount);
    }
}
