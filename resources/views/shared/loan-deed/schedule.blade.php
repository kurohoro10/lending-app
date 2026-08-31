{{-- Loan Deed — Schedule (source PDF page 28): key/value grid, shaded label column. --}}
<div class="deed-page">
    <h2 class="deed-h2">Schedule</h2>

    <table class="deed-schedule-table">
        <tr>
            <td class="deed-sched-label">Borrower</td>
            <td>
                {{ $d['borrower_name'] ?: '[insert]' }}<br>
                address: {{ $d['borrower_address'] ?: '[insert]' }}<br>
                email: {{ $d['borrower_email'] ?: '[insert]' }}
            </td>
        </tr>
        <tr>
            <td class="deed-sched-label">Default Rate</td>
            <td>{{ $d['default_rate'] ? $d['default_rate'] . ' per annum' : 'xx% per annum' }}</td>
        </tr>
        @if(!empty($d['guarantors']))
            <tr>
                <td class="deed-sched-label">Guarantor</td>
                <td>
                    @foreach($d['guarantors'] as $guarantor)
                        {{ $guarantor['full_name'] ?: '[insert]' }}<br>
                        email: {{ $guarantor['email'] ?: '[insert]' }}<br>
                        (<strong>Guarantor</strong>)
                        @if(!$loop->last)<br><br>@endif
                    @endforeach
                </td>
            </tr>
        @endif
        <tr>
            <td class="deed-sched-label">Legal Fees</td>
            <td>Professional fees, disbursements and associated taxes as per invoice of solicitors for the Lender</td>
        </tr>
        <tr>
            <td class="deed-sched-label">Exit Fee</td>
            <td>{{ $d['exit_fee'] ?: '' }}</td>
        </tr>
        <tr>
            <td class="deed-sched-label">Break Cost</td>
            <td>{{ $d['break_cost'] ?: '' }}</td>
        </tr>
        <tr>
            <td class="deed-sched-label">Permitted Encumbrance</td>
            <td>{{ $d['permitted_encumbrance'] ?: '' }}</td>
        </tr>
        <tr>
            <td class="deed-sched-label">Principal Sum</td>
            <td>{{ $d['principal_sum'] ?: '$[insert]' }}</td>
        </tr>
        <tr>
            <td class="deed-sched-label">Security</td>
            <td>
                @php
                    $securityProperties = $d['security']['properties'] ?? [];
                    $securityVehicles   = $d['security']['vehicles'] ?? [];
                @endphp
                @if(empty($securityProperties) && empty($securityVehicles))
                    [insert title details and address]
                @else
                    @foreach($securityProperties as $property)
                        Property Address {{ $property['address'] ?: '[insert]' }} with Volume and Folio Number {{ $property['volume_folio'] ?: '[insert]' }} will be taken as security.<br>
                    @endforeach
                    @foreach($securityVehicles as $vehicle)
                        Brand {{ $vehicle['brand'] ?: '[insert]' }} Model {{ $vehicle['model'] ?: '[insert]' }} VIN {{ $vehicle['vin'] ?: '[insert]' }} will be taken as security.<br>
                    @endforeach
                @endif
            </td>
        </tr>
    </table>
</div>

@if($mode === 'pdf')<div style="page-break-before: always;"></div>@endif
<div class="deed-page">
    <h2 class="deed-h2">Schedule 2</h2>
    <p class="deed-p"><strong>Loan Repayment Schedule</strong></p>

    @if(!empty($d['repayment_schedule']))
        <table class="deed-fin-table">
            <tr class="deed-fin-header">
                <td>#</td>
                <td>Repayment Date</td>
                <td>Amount</td>
            </tr>
            @foreach($d['repayment_schedule'] as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row['date'] ?? '' }}</td>
                    <td>{{ $row['amount'] ?? '' }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <p class="deed-p deed-muted">[if applicable]</p>
    @endif
</div>

@if($mode === 'pdf')<div style="page-break-before: always;"></div>@endif
<div class="deed-page">
    <h2 class="deed-h2">Schedule 3</h2>
    <p class="deed-p"><strong>Business Purpose Declaration</strong></p>
    <p class="deed-p deed-muted">[to be used when the borrower is an individual]</p>
</div>
