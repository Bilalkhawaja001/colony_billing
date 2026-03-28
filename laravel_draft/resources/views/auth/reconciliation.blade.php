@extends('layouts.app')
@section('page_title', 'Reconciliation')
@section('content')
<div class="grid">
    <div class="card col-8">
        <h3>Reconciliation Workspace</h3>
        <p class="muted">Validate financial and operational consistency before final closure.</p>
        <div class="actions" style="margin-top:10px">
            <a class="btn btn-primary" href="/reports/reconciliation">Open Reconciliation Report API Output</a>
            <a class="btn" href="/export/excel/reconciliation">Export Reconciliation Excel</a>
        </div>
    </div>
    <div class="card col-4">
        <h4>Control State</h4>
        <p><span class="badge">Ready for Review</span></p>
        <p class="muted">Use this page to centralize reconciliation actions before approval.</p>
    </div>

    <div class="card col-12">
        <h4>Validation Focus</h4>
        <table>
            <tr><th>Check</th><th>Purpose</th></tr>
            <tr><td>Unit vs Employee totals</td><td>Detect distribution mismatch</td></tr>
            <tr><td>Adjustment traceability</td><td>Validate approval chain</td></tr>
            <tr><td>Recovery alignment</td><td>Confirm payment coverage against billed units</td></tr>
        </table>
    </div>
</div>
@endsection
