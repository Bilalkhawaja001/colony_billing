@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Colony Billing</h3>
    <p><strong>Status:</strong> Authenticated session active.</p>

    <p><strong>Current User</strong></p>
    <ul>
        <li>User ID: {{ session('user_id', 'N/A') }}</li>
        <li>Role: {{ session('role', 'N/A') }}</li>
        <li>Actor User ID: {{ session('actor_user_id', 'N/A') }}</li>
        <li>Admin User ID: {{ session('admin_user_id', 'N/A') }}</li>
        <li>Force Password Change: {{ (int) session('force_change_password', 0) === 1 ? 'Yes' : 'No' }}</li>
    </ul>
</div>

<div class="card">
    <h4>Quick Navigation</h4>
    <p>
        <a href="/ui/dashboard">Dashboard</a> |
        <a href="/ui/billing">Billing</a> |
        <a href="/ui/month-cycle">Month Cycle</a> |
        <a href="/logout">Logout</a>
    </p>
</div>

<div class="card">
    <h4>Scope Notes</h4>
    <ul>
        <li><strong>/ui/billing</strong> is currently guarded and may return blocked status (migration phase boundary).</li>
        <li><strong>/ui/month-cycle</strong> is currently guarded and may return blocked status (migration phase boundary).</li>
        <li>This dashboard is a minimal authenticated landing page so post-login does not appear blank/incomplete.</li>
    </ul>
</div>
@endsection
