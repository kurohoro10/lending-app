{{-- Loan Deed — cover page (source PDF page 1). Shared by HTML views and DomPDF. --}}
<div class="deed-page deed-cover">
    <div class="deed-cover-spacer"></div>

    <h1 class="deed-cover-title">Loan Deed</h1>

    <div class="deed-cover-parties">
        <div class="deed-cover-party">
            <span class="deed-cover-dash">-</span>
            <div>
                ZYA Capital Pty Ltd (ACN: 695 692 052)<br>
                <strong>(Lender)</strong>
            </div>
        </div>

        <p class="deed-cover-and">and</p>

        <div class="deed-cover-party">
            <span class="deed-cover-dash">-</span>
            <div>
                {{ $d['borrower_name'] ?: '[insert]' }}<br>
                <strong>(Borrowers)</strong>
            </div>
        </div>

        @if(!empty($d['guarantors']))
            <p class="deed-cover-and">and</p>

            <div class="deed-cover-party">
                <span class="deed-cover-dash">-</span>
                <div>
                    @foreach($d['guarantors'] as $guarantor)
                        {{ $guarantor['full_name'] ?: '[insert]' }}<br>
                    @endforeach
                    <strong>(Director Guarantors)</strong>
                </div>
            </div>
        @endif
    </div>

    <div class="deed-cover-firm">
        <p class="deed-firm-wordmark"><strong>danaher</strong><span class="deed-firm-light">moulton</span></p>
        <p class="deed-firm-address">
            Level 1, 276 High Street<br>
            Kew VIC 3101<br>
            Ref: AH: 260358
        </p>
    </div>
</div>
