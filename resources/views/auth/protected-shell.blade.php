@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Dashboard</h3>
    <p><strong>Status:</strong> Authenticated session active.</p>
    <p>Welcome to Colony Billing shell. Core billing domain logic is being migrated, but navigation and base pages are live.</p>
</div>

<div class="card">
    <h4>Current Session</h4>
    <ul>
        <li>User ID: {{ session('user_id', 'N/A') }}</li>
        <li>Role: {{ session('role', 'N/A') }}</li>
        <li>Actor User ID: {{ session('actor_user_id', 'N/A') }}</li>
        <li>Admin User ID: {{ session('admin_user_id', 'N/A') }}</li>
        <li>Force Password Change: {{ (int) session('force_change_password', 0) === 1 ? 'Yes' : 'No' }}</li>
    </ul>
</div>

<div class="card">
    <h4>App Navigation</h4>
    <ul>
        <li><a href="/ui/dashboard">Dashboard Home</a></li>
        <li><a href="/ui/billing">Billing</a> <small>- foundation page</small></li>
        <li><a href="/ui/month-cycle">Month Cycle</a> <small>- foundation page</small></li>
        <li><a href="/ui/reports">Reports Shell</a></li>
        <li><a href="/ui/reconciliation">Reconciliation Shell</a></li>
        <li><a href="/logout">Logout</a></li>
    </ul>
</div>

<div class="card">
    <h4>Roadmap Note</h4>
    <p>Billing and month-cycle now have visible minimal pages for navigation + validation while backend workflows are completed.</p>
</div>
@endsection
