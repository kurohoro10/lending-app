{{-- Loan Deed — financial table + fee tables (source PDF pages 4-7). --}}
<table class="deed-fin-table">
    <tr class="deed-fin-header">
        <td>Financial Table</td>
        <td>Description</td>
        <td>Amount</td>
    </tr>
    <tr><td>Amount of capital provided</td><td></td><td>{{ $d['principal_sum'] ?: '<>' }}</td></tr>
    <tr><td>Annual Percentage Rate</td><td></td><td>{{ $d['annual_percentage_rate'] ?: '<>' }}</td></tr>
    <tr><td>Total amount of interest payable</td><td></td><td>{{ $d['total_interest'] ?: '<>' }}</td></tr>
    <tr><td><strong>Repayments</strong></td><td></td><td></td></tr>
    <tr><td>Repayment cycle (Weekly/Fortnightly/Monthly)</td><td></td><td>{{ $d['repayment_cycle'] ?: '<>' }}</td></tr>
    <tr><td>Total Number of Repayments</td><td></td><td>{{ $d['total_repayments'] ?: '<>' }}</td></tr>
    <tr><td>Amount of Per Repayment</td><td></td><td>{{ $d['amount_per_repayment'] ?: '<>' }}</td></tr>
    <tr><td>Total Amount of Repayment</td><td></td><td>{{ $d['total_repayment_amount'] ?: '<>' }}</td></tr>
    <tr><td>Date of First Repayment</td><td></td><td>{{ $d['first_repayment_date'] ?: '<>' }}</td></tr>
    <tr><td><strong>Credit fee and charges due immediately we executive the document</strong></td><td></td><td></td></tr>
    <tr><td>Application Fee</td><td></td><td>{{ $d['application_fee'] ?: '<>' }}</td></tr>
    <tr><td>Security Interest Search Fee</td><td></td><td>{{ $d['security_search_fee'] ?: '<>' }}</td></tr>
    <tr><td>Legal Fee</td><td></td><td>{{ $d['legal_fee'] ?: '<>' }}</td></tr>
    <tr><td>Security Registration Fee</td><td></td><td>{{ $d['security_registration_fee'] ?: '<>' }}</td></tr>
    <tr><td>Valuation Fee</td><td></td><td>{{ $d['valuation_fee'] ?: '<>' }}</td></tr>
    <tr><td>Manual Allocation Fee</td><td>Charged each time an allocation is made manually.</td><td>$15 per time</td></tr>
    <tr><td><strong>Fees and charges which ARE payable AFTER the settlement date:</strong></td><td></td><td></td></tr>
    <tr><td>Monthly Account Fee</td><td></td><td>{{ $d['monthly_account_fee'] ?: '<>' }}</td></tr>
    <tr>
        <td>Direct Debit Fee</td>
        <td>Payable to Ezidebit Pty Ltd, this $0.78 fee or the current rate if your payments are made by
            direct debit, this fee will apply on a per transaction basis and be added to the amount due and
            the total will be direct debited from your nominated bank account. You can avoid this fee by
            paying the payment due in cash no later than on the day prior to the due date.</td>
        <td>0.78</td>
    </tr>
    <tr><td>Annual Review Fee</td><td></td><td>{{ $d['annual_review_fee'] ?: '<>' }}</td></tr>
    <tr><td><strong>Fees and charges which MAY become payable under your loan contract:</strong></td><td></td><td></td></tr>
    <tr>
        <td>Dishonoured or Missed Payment Fee</td>
        <td>Payable to Us if you miss or fail to make a payment in accordance with the repayment schedule
            or when we are notified of a dishonour by our bankers for a payment you made, this fee may be
            debited to your account and form part of the balance on which interest (if any) is charged.</td>
        <td>20</td>
    </tr>
    <tr>
        <td>Security Interest Modification Fee</td>
        <td>Payable to AFSA this fee or such higher amount as may be applicable at the time may be payable
            if we are required to amend a financing statement in a minor way</td>
        <td>30</td>
    </tr>
    <tr>
        <td>Contract Variation Fee</td>
        <td>Payable to us when you request us to change the feature of the loan for example, payment
            holiday, payment term change, minimum loan repayment change.</td>
        <td>100</td>
    </tr>
    <tr>
        <td>Arrears Notice Fee</td>
        <td>Payable to Us if your loan is in arrears and we find it necessary to write to you, this fee may be
            debited to your account and will form part of the balance on which interest (if any) is charged.</td>
        <td>50</td>
    </tr>
    <tr>
        <td>Default Notice Fee</td>
        <td>Payable to Us if your loan is in arrears and we find it necessary to issue a default notice, this fee
            may be debited to your account and will form part of the balance on which interest (if any) is
            charged.</td>
        <td>50</td>
    </tr>
    <tr>
        <td>Security Release Letter</td>
        <td>Payable to Us when you have repaid your loan in full and we are required to release any security,
            this fee will be payable.</td>
        <td>100</td>
    </tr>
    <tr>
        <td>Enforcement Expenses</td>
        <td>Payable to Us these may be payable when, following an unremedied default, we have cause to
            recover any reasonable expense we incur in enforcing our rights under the contract.</td>
        <td>At cost</td>
    </tr>
    <tr>
        <td>Security Interest Discharge Fee</td>
        <td>Payable to AFSA or land title when you have repaid your loan in full and we are required to
            release any security that has been registered with the registar of encumbered vehicles this fee
            maybe payable.</td>
        <td>At cost</td>
    </tr>
</table>

<p class="deed-p">Disclosure Date: {{ $d['disclosure_date'] ?: '' }}</p>

<p class="deed-p">Note: Under this Contract we may vary any of the following without your consent:</p>
<p class="deed-p">• Amount or frequency of the repayments;</p>
<p class="deed-p">• How interest is calculated or applied;</p>
<p class="deed-p">• Amount and frequency of credit fees and charges or other fees and charges (including by imposing
    new fees and charges or other fees and charges changing their method of calculation); and</p>
<p class="deed-p">• The method of publication of any applicable reference rate(s)</p>
