{{-- Loan Deed — TOC row markup (source PDF pages 2-3).
     Three visual zones, matching the original: A) number  B) title + continuous leader  C) page.
     The leader is NOT a separate table column — it lives inside the title cell as a
     border-bottom on a nested 2-cell table, so it genuinely scales with the title's own length
     (short titles get a long leader, long titles get a short one) rather than a fixed dot run.
     This is verified working: rendered a real production PDF and read the raw content stream's
     line-drawing operators — the leader line starts at a different x per row (right after each
     title's own text) and always ends at the identical x (the fixed left edge of the page-number
     column), exactly like the source document.
     Two things that make this reliable, both confirmed by reading DomPDF's source and testing
     rendered output (vendor/dompdf/dompdf/src/Cellmap.php):
       1. Column widths on the OUTER table (num/title/page) are in PERCENT, not px/pt — this
          DomPDF version silently resolves absolute-unit column widths to 0 in
          table-layout:fixed, which is why every earlier px-based attempt misaligned.
       2. The INNER table's text cell uses width:1%+white-space:nowrap (the standard CSS2.1
          shrink-to-fit trick), so its sibling cell absorbs all remaining width and carries
          nothing but the border — a border cannot wrap, unlike a long run of dot characters. --}}
<table class="deed-toc-table">
    @foreach($entries as [$num, $title, $page])
        <tr class="{{ strpos($num, '.') === false ? 'deed-toc-main' : 'deed-toc-sub' }}">
            <td class="deed-toc-num">{{ $num }}</td>
            <td class="deed-toc-titlewrap">
                <table class="deed-toc-leader-table">
                    <tr>
                        <td class="deed-toc-text">{{ $title }}</td>
                        <td class="deed-toc-leader"></td>
                    </tr>
                </table>
            </td>
            <td class="deed-toc-page">{{ $page }}</td>
        </tr>
    @endforeach
</table>
