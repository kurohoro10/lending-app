{{-- Loan Deed — full document aggregator. Include with $application, $d, $mode ('html'|'pdf'). --}}
@include('shared.loan-deed.cover', ['d' => $d, 'mode' => $mode])
@if($mode === 'pdf')<div style="page-break-before: always;"></div>@endif

@include('shared.loan-deed.toc', ['d' => $d, 'mode' => $mode])
@if($mode === 'pdf')<div style="page-break-before: always;"></div>@endif

@include('shared.loan-deed.parties', ['d' => $d, 'mode' => $mode])

@include('shared.loan-deed.recitals', ['d' => $d, 'mode' => $mode])

@include('shared.loan-deed.clauses', ['d' => $d, 'mode' => $mode])
@if($mode === 'pdf')<div style="page-break-before: always;"></div>@endif

@include('shared.loan-deed.schedule', ['d' => $d, 'mode' => $mode])
@if($mode === 'pdf')<div style="page-break-before: always;"></div>@endif

@include('shared.loan-deed.execution', ['d' => $d, 'mode' => $mode])
