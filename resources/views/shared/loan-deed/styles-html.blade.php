{{-- Loan Deed — shared document styles for HTML renderers (client review page, admin signed view).
     Same class names as the DomPDF template's inline CSS so content partials never change. --}}
<style>
    .deed-document { font-family: Georgia, 'Times New Roman', serif; font-size: 14px; color: #1a1a2e; line-height: 1.55; }
    .deed-document .deed-page { background: #fff; padding: 2.5rem 3rem; margin-bottom: 1.5rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; }

    /* Cover */
    .deed-document .deed-cover-spacer { height: 6rem; }
    .deed-document .deed-cover-title { font-size: 2.25rem; font-weight: 700; color: #1e2a78; border-bottom: 2px solid #6b7280; padding-bottom: 0.5rem; margin-bottom: 2rem; }
    .deed-document .deed-cover-party { display: table; margin-bottom: 0.75rem; }
    .deed-document .deed-cover-dash { display: table-cell; width: 2.5rem; }
    .deed-document .deed-cover-party > div { display: table-cell; }
    .deed-document .deed-cover-and { margin: 0.75rem 0 0.75rem 2.5rem; }
    .deed-document .deed-cover-firm { margin-top: 8rem; text-align: right; }
    .deed-document .deed-firm-wordmark { font-size: 1.15rem; color: #1e2a78; }
    .deed-document .deed-firm-light { color: #9ca3af; font-weight: 300; }
    .deed-document .deed-firm-address { font-size: 0.8rem; color: #4b5563; margin-top: 0.5rem; }

    /* TOC — mirrors the DomPDF template: three zones (number / title+leader / page), widths in
       percent. The leader lives inside the title cell (not a separate column) as a border-bottom
       on a nested 2-cell table, using the width:1%+white-space:nowrap shrink-to-fit trick on the
       text cell so the sibling absorbs remaining width and carries the border — a genuinely
       proportional leader, not a fixed dot run. See toc-entries.blade.php for how this was
       verified against DomPDF's actual rendered output. */
    .deed-document .deed-toc-heading { font-size: 1.6rem; font-weight: 700; color: #1e2a78; margin-bottom: 1.1rem; }
    .deed-document .deed-toc-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .deed-document .deed-toc-table td { padding: 0.2rem 0; vertical-align: bottom; }
    .deed-document .deed-toc-num { width: 8%; text-align: left; }
    .deed-document .deed-toc-titlewrap { width: 87%; padding-right: 0.4rem; }
    .deed-document .deed-toc-leader-table { width: 100%; border-collapse: collapse; }
    .deed-document .deed-toc-leader-table td { padding: 0; vertical-align: bottom; }
    .deed-document .deed-toc-text { width: 1%; white-space: nowrap; padding-right: 0.3rem; text-align: left; }
    .deed-document .deed-toc-leader { border-bottom: 1px dotted #9ca3af; }
    .deed-document .deed-toc-page { width: 5%; text-align: right; white-space: nowrap; }
    .deed-document .deed-toc-main td { font-weight: 700; color: #1e2a78; padding-top: 1rem; }
    .deed-document .deed-toc-main:first-child td { padding-top: 0; }
    .deed-document .deed-toc-main .deed-toc-leader-table td { padding-top: 0; }
    .deed-document .deed-toc-sub td { font-weight: 400; color: #1a1a2e; }
    .deed-document .deed-toc-sub .deed-toc-num { padding-left: 1.1rem; }

    /* Headings and text */
    .deed-document .deed-h1 { font-size: 1.9rem; font-weight: 700; color: #1e2a78; margin-bottom: 1.25rem; }
    .deed-document .deed-h2 { font-size: 1.35rem; font-weight: 700; color: #1e2a78; margin: 1.5rem 0 0.75rem; }
    .deed-document .deed-h3 { font-size: 1.05rem; font-weight: 700; color: #1e2a78; border-bottom: 1px solid #6b7280; padding-bottom: 0.25rem; margin: 1.75rem 0 0.75rem; }
    .deed-document .deed-h4 { font-size: 0.95rem; font-weight: 700; margin: 1.1rem 0 0.4rem; }
    .deed-document .deed-p { margin-bottom: 0.6rem; text-align: justify; }
    .deed-document .deed-muted { color: #6b7280; font-size: 0.85em; }

    /* Lists */
    .deed-document .deed-ol { margin: 0.35rem 0 0.75rem 2.25rem; }
    .deed-document .deed-ol li { margin-bottom: 0.35rem; text-align: justify; }

    /* Financial / fee tables */
    .deed-document .deed-fin-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    .deed-document .deed-fin-table td { border: 1px solid #374151; padding: 0.4rem 0.6rem; vertical-align: top; }
    .deed-document .deed-fin-table td:first-child { width: 34%; }
    .deed-document .deed-fin-table td:last-child { width: 16%; }
    .deed-document .deed-fin-header td { font-weight: 700; }

    /* Recitals / lettered clause tables */
    .deed-document .deed-clause-table { width: 100%; border-collapse: collapse; margin-bottom: 0.75rem; }
    .deed-document .deed-clause-table td { padding: 0.25rem 0; vertical-align: top; text-align: justify; }
    .deed-document .deed-clause-num { width: 3rem; }

    /* Schedule */
    .deed-document .deed-schedule-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    .deed-document .deed-schedule-table td { padding: 0.5rem 0.75rem; vertical-align: top; }
    .deed-document .deed-sched-label { width: 27%; background: #bfd0e4; font-weight: 700; }
    .deed-document .deed-schedule-table tr td:last-child { background: #eceff4; }

    /* Execution — plain black, no theme colour, tables only (no flex/grid) */
    .deed-document .deed-execution-page { color: #000; }
    .deed-document .deed-execution-title { font-size: 1rem; font-weight: 700; margin-bottom: 2rem; }

    .deed-document .deed-dated-table { width: 100%; border-collapse: collapse; margin: 0 0 2.75rem; }
    .deed-document .deed-dated-table td { padding: 0; vertical-align: bottom; }
    .deed-document .deed-dated-label { width: 3rem; font-weight: 700; white-space: nowrap; }
    .deed-document .deed-dated-line { border-bottom: 1px solid #000; }
    .deed-document .deed-dated-year { width: 2.5rem; text-align: right; white-space: nowrap; padding-left: 0.4rem; }

    .deed-document .deed-execution-table { width: 100%; border-collapse: collapse; margin: 0 0 2.5rem; }
    .deed-document .deed-execution-table td { padding: 0; vertical-align: top; }
    .deed-document .deed-execution-left { width: 52%; text-align: justify; }
    .deed-document .deed-execution-gap { width: 6%; }
    .deed-document .deed-execution-right { width: 42%; vertical-align: top; }

    .deed-document .deed-signature-block { margin-top: 0.75rem; margin-bottom: 1.4rem; }
    .deed-document .deed-signature-block:first-child { margin-top: 0; }
    .deed-document .deed-signature-block:last-child { margin-bottom: 0; }

    .deed-document .deed-signature-line { border-bottom: 1px solid #000; height: 2.25rem; width: 85%; margin-bottom: 0.25rem; }
    .deed-document .deed-signature-image { display: block; max-height: 3.5rem; max-width: 85%; margin-bottom: 0.25rem; }
    .deed-document .deed-print-line { border-bottom: 1px solid #000; min-height: 1rem; width: 85%; margin-bottom: 0.25rem; }

    .deed-document .deed-name { margin-bottom: 0.15rem; }
    .deed-document .deed-meta { font-size: 0.85em; margin-top: 0.25rem; }
</style>
