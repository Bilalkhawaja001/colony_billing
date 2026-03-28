@extends('layouts.app')
@section('page_title', 'Dashboard')
@section('content')
<div class="banner">Operational dashboard for colony electricity billing, governance, and reporting.</div>
<div class="grid" style="margin-top:14px">
    <div class="card col-6">
        <h3>Dashboard</h3>
        <p class="muted">Use sidebar navigation to access Billing, Month Cycle, Reports, and Reconciliation modules.</p>
    </div>
    <div class="card col-6">
        <h4>Quick Access</h4>
        <div class="actions">
            <a class="btn btn-primary" href="/ui/billing">Billing Workspace</a>
            <a class="btn" href="/ui/month-cycle">Month Cycle</a>
            <a class="btn" href="/ui/reports">Reports</a>
            <a class="btn" href="/ui/reconciliation">Reconciliation</a>
        </div>
    </div>
</div>
@endsection
