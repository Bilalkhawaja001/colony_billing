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
            <a class="btn btn-primary" href="/billing-run-lock">Billing Workspace</a>
            <a class="btn" href="/month-lifecycle">Month Cycle</a>
            <a class="btn" href="/reporting">Reports</a>
            <a class="btn" href="/reporting">Reconciliation</a>
        </div>
    </div>
</div>
@endsection

