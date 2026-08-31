{{-- Loan Deed — execution page (source PDF page 28/31).
     Renders captured signatures as <img> when present, blank lines otherwise. --}}
<div class="deed-page deed-execution-page">
    <p class="deed-execution-title">Executed as a deed:</p>

    <table class="deed-dated-table">
        <tr>
            <td class="deed-dated-label">Dated:</td>
            <td class="deed-dated-line">
                {{ !empty($d['signed_at']) ? \Illuminate\Support\Carbon::parse($d['signed_at'])->format('d F') : '' }}
            </td>
            <td class="deed-dated-year">
                {{ !empty($d['signed_at']) ? \Illuminate\Support\Carbon::parse($d['signed_at'])->format('Y') : date('Y') }}
            </td>
        </tr>
    </table>

    {{-- Lender execution --}}
    <table class="deed-execution-table">
        <tr>
            <td class="deed-execution-left">
                <strong>EXECUTED</strong> by <strong>ZYA Capital Pty Ltd ACN 695 692 052</strong>
                in accordance with Section 127 of the <em>Corporations Act 2001</em> (Cth):
            </td>
            <td class="deed-execution-gap"></td>
            <td class="deed-execution-right">
                <div class="deed-signature-block">
                    <div class="deed-signature-line"></div>
                    <div class="deed-name">Gang Chen</div>
                    <strong class="deed-role">Sole Director/Secretary</strong>
                </div>
            </td>
        </tr>
    </table>

    {{-- Borrower company execution — one block per director --}}
    <table class="deed-execution-table">
        <tr>
            <td class="deed-execution-left">
                <strong>EXECUTED</strong> by <strong>{{ $d['borrower_name'] ?: '[insert]' }}
                {{ $d['borrower_acn'] ? 'ACN ' . $d['borrower_acn'] : ($d['borrower_abn'] ? 'ABN ' . $d['borrower_abn'] : '') }}</strong>
                in accordance with Section 127 of the <em>Corporations Act 2001</em> (Cth):
            </td>
            <td class="deed-execution-gap"></td>
            <td class="deed-execution-right">
                @forelse($d['directors'] as $director)
                    <div class="deed-signature-block">
                        <div class="deed-signature-line"></div>
                        <div class="deed-name">{{ $director['full_name'] }}</div>
                        <strong class="deed-role">Director</strong>
                    </div>
                @empty
                    <div class="deed-signature-block">
                        <div class="deed-signature-line"></div>
                        <div class="deed-name">[insert]</div>
                        <strong class="deed-role">Director</strong>
                    </div>
                @endforelse
            </td>
        </tr>
    </table>

    {{-- Witness + client (borrower/guarantor individual) execution --}}
    <table class="deed-execution-table">
        <tr>
            <td class="deed-execution-left">
                <strong>SIGNED, SEALED AND DELIVERED</strong> by
                {{ $d['guarantor_name'] ?: $d['borrower_name'] ?: '[Insert]' }} in the presence of:

                <div class="deed-signature-block">
                    @if(!empty($d['witness_signature']))
                        <img src="{{ $d['witness_signature'] }}" alt="Witness signature" class="deed-signature-image">
                    @endif
                    <div class="deed-signature-line"></div>
                    <span class="deed-role">Witness signature</span>
                </div>

                <div class="deed-signature-block">
                    <div class="deed-print-line">{{ $d['witness_name'] ?: '' }}</div>
                    <span class="deed-role">Print name</span>
                </div>
            </td>
            <td class="deed-execution-gap"></td>
            <td class="deed-execution-right">
                <div class="deed-signature-block">
                    @if(!empty($d['client_signature']))
                        <img src="{{ $d['client_signature'] }}" alt="Signature" class="deed-signature-image">
                    @endif
                    <div class="deed-signature-line"></div>
                    <div class="deed-name">{{ $d['guarantor_name'] ?: $d['borrower_name'] ?: '[Insert]' }}</div>
                    <span class="deed-role">Signature</span>
                    @if(!empty($d['signed_at']))
                        <div class="deed-meta">Signed: {{ $d['signed_at'] }}</div>
                    @endif
                    @if(!empty($d['signed_ip']))
                        <div class="deed-meta">IP: {{ $d['signed_ip'] }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>
