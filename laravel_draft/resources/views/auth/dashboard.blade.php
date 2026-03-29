@extends('layouts.app')
@section('page_title', 'Dashboard')
@section('content')
<div class="banner">Operational dashboard for colony electricity billing, governance, and reporting.</div>

<div class="grid" style="margin-top:14px">
    <div class="card col-3"><div class="muted">Billing Cycles</div><div class="kpi">Active</div><div class="muted">Current cycle monitoring enabled</div></div>
    <div class="card col-3"><div class="muted">Billing Actions</div><div class="kpi">Ready</div><div class="muted">Precheck / finalize / lock / approve available</div></div>
    <div class="card col-3"><div class="muted">Reports</div><div class="kpi">6</div><div class="muted">Reconciliation, summary, recovery, VAN, electricity</div></div>
    <div class="card col-3"><div class="muted">Access Role</div><div class="kpi">{{ session('role', 'N/A') }}</div><div class="muted">Session-scoped RBAC controls in effect</div></div>

    <div class="card col-6">
        <h4>Core Workspaces</h4>
        <div class="actions">
            <a class="btn btn-primary" href="/billing-run-lock">Open Billing Workspace</a>
            <a class="btn" href="/month-lifecycle">Open Month Cycle</a>
            <a class="btn" href="/reporting">Open Reports</a>
            <a class="btn" href="/reporting">Open Reconciliation</a>
        </div>
    </div>

    <div class="card col-6">
        <h4>Session Information</h4>
        <table>
            <tr><th>User ID</th><td>{{ session('user_id', 'N/A') }}</td></tr>
            <tr><th>Role</th><td>{{ session('role', 'N/A') }}</td></tr>
            <tr><th>Actor User ID</th><td>{{ session('actor_user_id', 'N/A') }}</td></tr>
            <tr><th>Admin User ID</th><td>{{ session('admin_user_id', 'N/A') }}</td></tr>
        </table>
    </div>
</div>
@endsection

