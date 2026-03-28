@extends('layouts.app')
@section('page_title', 'Reports Center')
@section('content')
<div class="grid">
    <div class="card col-12">
        <h3>Reports Center</h3>
        <p class="muted">Generate and export analytical reports from secured report endpoints.</p>
    </div>

    <div class="card col-6">
        <h4>Operational Reports</h4>
        <div class="actions">
            <a class="btn" href="/reports/monthly-summary">Monthly Summary</a>
            <a class="btn" href="/reports/recovery">Recovery Report</a>
            <a class="btn" href="/reports/employee-bill-summary">Employee Bill Summary</a>
        </div>
    </div>

    <div class="card col-6">
        <h4>Technical Reports</h4>
        <div class="actions">
            <a class="btn" href="/reports/reconciliation">Reconciliation</a>
            <a class="btn" href="/reports/van">VAN Report</a>
            <a class="btn" href="/reports/elec-summary">Electricity Summary</a>
            <a class="btn btn-primary" href="/export/excel/reconciliation">Export Reconciliation Excel</a>
        </div>
    </div>
</div>
@endsection
