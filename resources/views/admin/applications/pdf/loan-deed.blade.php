{{-- DomPDF template — Loan Deed. Thin shell over the shared partials ($mode = 'pdf').
     Inline styles only, no flexbox/grid. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Loan Deed — {{ $application->application_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9.5pt; color: #1a1a2e; line-height: 1.5; margin: 0; }
        /* Original document sits closer to the binding edge: left margin noticeably wider than right. */
        .deed-page { padding: 10px 28px 10px 54px; }

        /* Cover */
        .deed-cover-spacer { height: 180px; }
        .deed-cover-title { font-size: 26pt; font-weight: bold; color: #1e2a78; border-bottom: 1.5px solid #6b7280; padding-bottom: 6px; margin: 0 0 30px 0; }
        .deed-cover-party { margin-bottom: 10px; }
        .deed-cover-dash { display: inline-block; width: 30px; vertical-align: top; }
        .deed-cover-party > div { display: inline-block; width: 80%; vertical-align: top; }
        .deed-cover-and { margin: 10px 0 10px 30px; }
        .deed-cover-firm { margin-top: 220px; text-align: right; }
        .deed-firm-wordmark { font-size: 12pt; color: #1e2a78; margin: 0; }
        .deed-firm-light { color: #9ca3af; font-weight: normal; }
        .deed-firm-address { font-size: 8pt; color: #4b5563; margin-top: 6px; }

        /* TOC — three zones (number / title+leader / page), widths in PERCENT. Confirmed by
           reading vendor/dompdf/dompdf/src/Cellmap.php: this DomPDF version discards pixel/point
           widths on table-layout:fixed columns (resolves them to 0 — a real bug in the
           absolute-width code path) while percentage widths resolve correctly; every earlier
           px-based attempt was therefore never actually constraining the columns at all.
           The leader lives INSIDE the title cell (not a separate column) as a border-bottom on a
           nested 2-cell table: the text cell uses width:1%+white-space:nowrap (the CSS2.1
           shrink-to-fit trick) so its sibling absorbs all remaining width and carries the border —
           verified by decompressing the rendered PDF's content stream and confirming the drawn
           line starts at a different x per row (right after each title) and always ends at the
           same x (the page-number column), i.e. a genuinely proportional leader. */
        .deed-toc-heading { font-size: 16pt; font-weight: bold; color: #1e2a78; margin: 0 0 16px 0; }
        .deed-toc-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .deed-toc-table td { padding: 3px 0; vertical-align: bottom; font-size: 9pt; }
        .deed-toc-num { width: 8%; text-align: left; }
        .deed-toc-titlewrap { width: 87%; padding-right: 6px; }
        .deed-toc-leader-table { width: 100%; border-collapse: collapse; }
        .deed-toc-leader-table td { padding: 0; vertical-align: bottom; font-size: 9pt; }
        .deed-toc-text { width: 1%; white-space: nowrap; padding-right: 4px; text-align: left; }
        .deed-toc-leader { border-bottom: 0.75px dotted #9ca3af; }
        .deed-toc-page { width: 5%; text-align: right; white-space: nowrap; }
        .deed-toc-main td { font-weight: bold; color: #1e2a78; padding-top: 14px; }
        .deed-toc-main:first-child td { padding-top: 0; }
        .deed-toc-main .deed-toc-leader-table td { padding-top: 0; }
        .deed-toc-sub td { font-weight: normal; color: #1a1a2e; }
        .deed-toc-sub .deed-toc-num { padding-left: 16px; }

        /* Headings and text */
        .deed-h1 { font-size: 20pt; font-weight: bold; color: #1e2a78; margin: 0 0 16px 0; }
        .deed-h2 { font-size: 14pt; font-weight: bold; color: #1e2a78; margin: 18px 0 8px 0; }
        .deed-h3 { font-size: 11pt; font-weight: bold; color: #1e2a78; border-bottom: 0.75px solid #6b7280; padding-bottom: 3px; margin: 18px 0 8px 0; }
        .deed-h4 { font-size: 9.5pt; font-weight: bold; margin: 12px 0 4px 0; }
        .deed-p { margin: 0 0 6px 0; text-align: justify; }
        .deed-muted { color: #6b7280; font-size: 8pt; }

        /* Lists */
        .deed-ol { margin: 4px 0 8px 26px; padding: 0; }
        .deed-ol li { margin-bottom: 4px; text-align: justify; }

        /* Financial / fee tables */
        .deed-fin-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .deed-fin-table td { border: 0.75px solid #374151; padding: 4px 6px; vertical-align: top; font-size: 8.5pt; }
        .deed-fin-table td:first-child { width: 34%; }
        .deed-fin-table td:last-child { width: 16%; }
        .deed-fin-header td { font-weight: bold; }

        /* Recitals / lettered clause tables */
        .deed-clause-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .deed-clause-table td { padding: 2px 0; vertical-align: top; text-align: justify; }
        .deed-clause-num { width: 28px; }

        /* Schedule */
        .deed-schedule-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .deed-schedule-table td { padding: 6px 8px; vertical-align: top; }
        .deed-sched-label { width: 27%; background-color: #bfd0e4; font-weight: bold; }
        .deed-schedule-table tr td { background-color: #eceff4; }
        .deed-schedule-table tr td.deed-sched-label { background-color: #bfd0e4; }

        /* Execution — plain black, no theme colour, tables only (no flex/grid) */
        .deed-execution-page { color: #000; }
        .deed-execution-title { font-size: 9.5pt; font-weight: bold; margin: 0 0 34px 0; }

        .deed-dated-table { width: 100%; border-collapse: collapse; margin: 0 0 46px 0; }
        .deed-dated-table td { padding: 0; vertical-align: bottom; font-size: 9.5pt; }
        .deed-dated-label { width: 42px; font-weight: bold; white-space: nowrap; }
        .deed-dated-line { border-bottom: 0.75px solid #000; }
        .deed-dated-year { width: 34px; text-align: right; white-space: nowrap; padding-left: 6px; }

        .deed-execution-table { width: 100%; border-collapse: collapse; margin: 0 0 42px 0; }
        .deed-execution-table td { padding: 0; vertical-align: top; }
        .deed-execution-left { width: 52%; text-align: justify; }
        .deed-execution-gap { width: 6%; }
        .deed-execution-right { width: 42%; vertical-align: top; }

        .deed-signature-block { margin-top: 12px; margin-bottom: 22px; }
        .deed-signature-block:first-child { margin-top: 0; }
        .deed-signature-block:last-child { margin-bottom: 0; }

        .deed-signature-line { border-bottom: 0.75px solid #000; height: 34px; width: 85%; margin-bottom: 4px; }
        .deed-signature-image { display: block; max-height: 50px; max-width: 85%; margin-bottom: 4px; }
        .deed-print-line { border-bottom: 0.75px solid #000; min-height: 14px; width: 85%; margin-bottom: 4px; }

        .deed-name { font-size: 9.5pt; margin-bottom: 2px; }
        .deed-role { font-size: 9.5pt; }
        .deed-meta { font-size: 8pt; margin-top: 4px; }

        /* Fixed footer on every page */
        .pdf-footer {
            position: fixed;
            bottom: 8px;
            left: 20px;
            right: 20px;
            font-size: 7pt;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="pdf-footer">
        Loan Deed — {{ $application->application_number }} &nbsp;|&nbsp; Generated {{ $generatedAt->format('d M Y g:i A') }}
    </div>

    @include('shared.loan-deed.document', ['application' => $application, 'd' => $deedData, 'mode' => 'pdf'])
</body>
</html>
