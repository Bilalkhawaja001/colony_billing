@extends('billing_control.layout')

@section('content')
@php
    $data = $dryRun ?? [];
    $runLabel = $run ?? data_get($data, 'run_id', '-');
@endphp

<div class="eyebrow">Generated Bills / Billing Record</div>
<h1 class="page-title">Generated Bills / Billing Record</h1>

<section class="panel-center" style="margin-top:24px">
    <div class="gen-tick">✓</div>
    <h2 class="headline">{{ data_get($data, 'status', 'Preview completed') }}</h2>
    <p class="hero-sub" style="margin:8px auto 22px">{{ data_get($data, 'message', 'Preview Bills completed.') }}</p>
</section>

<section class="card" style="margin-top:24px">
    <div class="eyebrow">Run Summary</div>
    <div class="run-summary">
        <div class="run-row"><span class="k">Bill Reference</span><span class="v">{{ $runLabel }}</span></div>
        <div class="run-row"><span class="k">Method</span><span class="v">{{ data_get($data, 'method_label', '-') }}</span></div>
        <div class="run-row"><span class="k">Cycle Start</span><span class="v">{{ data_get($data, 'cycle_start', data_get($data, 'cycle_start_date', '-')) }}</span></div>
        <div class="run-row"><span class="k">Cycle End</span><span class="v">{{ data_get($data, 'cycle_end', data_get($data, 'cycle_end_date', '-')) }}</span></div>
        <div class="run-row"><span class="k">Employees</span><span class="v">{{ data_get($data, 'summary.employees', data_get($data, 'active_employees', '-')) }}</span></div>
        <div class="run-row"><span class="k">Total Amount</span><span class="v">{{ number_format((float) data_get($data, 'summary.total_amount', 0), 2) }}</span></div>
        <div class="run-row"><span class="k">Rate</span><span class="v">{{ data_get($data, 'rate', data_get($data, 'electric_rate', '-')) }}</span></div>
        <div class="run-row"><span class="k">Issues</span><span class="v">{{ count(data_get($data, 'issues', [])) }} / snapshot: {{ count(data_get($data, 'snapshot_issues', [])) }}</span></div>
    </div>
</section>
@endsection
