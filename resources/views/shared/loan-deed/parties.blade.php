{{-- Loan Deed — parties block + intro (source PDF page 4). --}}
<div class="deed-page">
    <h1 class="deed-h1">Loan Deed</h1>

    <h2 class="deed-h2">Parties</h2>

    <p class="deed-p">
        <strong>ZYA Capital Pty Ltd ACN 695 692 052</strong><br>
        address: Unit 18, 6-8 Holden Street, Ashfield NSW 2131<br>
        email: legal@zyacapital.com.au<br>
        attention: Yuejia He<br>
        (<strong>Lender</strong>)
    </p>

    <p class="deed-p">and</p>

    <p class="deed-p">
        each person specified in the schedule as a Borrower<br>
        (each a <strong>Borrower</strong>)
    </p>

    <p class="deed-p">Name: {{ $d['borrower_name'] ?: '<>' }}</p>
    <p class="deed-p">Address: {{ $d['borrower_address'] ?: '<>' }}</p>
    <p class="deed-p">Electronic Address: {{ $d['borrower_email'] ?: '<>' }}</p>

    @if(!empty($d['guarantors']))
        <p class="deed-p">and</p>

        <p class="deed-p">
            each person specified in the schedule as a Guarantor<br>
            (each a <strong>Guarantor</strong>)
        </p>

        @foreach($d['guarantors'] as $guarantor)
            <p class="deed-p">Name: {{ $guarantor['full_name'] ?: '<>' }}</p>
            <p class="deed-p">Address: {{ $guarantor['address'] ?: '<>' }}</p>
            <p class="deed-p">Electronic Address: {{ $guarantor['email'] ?: '<>' }}</p>
        @endforeach
    @endif

    <p class="deed-p">
        We above enter into a loan contract as below on the term set out in the schedule below, along with the
        term and conditions in the memorandum of mortgage and general conditions in the document. This
        offer replaces any previous offer made following your credit application. Any such previous offer is
        canceller. The lender is treating this offer as commercial loan and no regulated by NCCP.
    </p>

    @include('shared.loan-deed.financial-table', ['d' => $d, 'mode' => $mode])
</div>
