@extends('layouts.app')
@section('page_title', 'Month Cycle Management')
@section('content')
<div class="grid">
    <div class="card col-8">
        <h3>Month Cycle Management</h3>
        <p class="muted">Govern cycle state transitions under guarded middleware.</p>
        <div class="actions" style="margin-top:10px">
            <form method="POST" action="/month/open">@csrf<button class="btn btn-primary" type="submit">Open Month</button></form>
            <form method="POST" action="/month/transition">@csrf<button class="btn btn-warning" type="submit">Transition Month</button></form>
        </div>
    </div>
    <div class="card col-4">
        <h4>Governance</h4>
        <p class="muted">Only SUPER_ADMIN and BILLING_ADMIN can execute month transition endpoints.</p>
    </div>

    <div class="card col-12">
        <h4>Control Checklist</h4>
        <table>
            <tr><th>Step</th><th>Owner</th><th>Status</th></tr>
            <tr><td>Attendance & meter intake verified</td><td>Billing Team</td><td>In Progress</td></tr>
            <tr><td>Pre-bill reconciliation reviewed</td><td>Admin</td><td>Queued</td></tr>
            <tr><td>Cycle closure authorization</td><td>SUPER_ADMIN</td><td>Awaiting Approval</td></tr>
        </table>
    </div>
</div>
@endsection
