@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Month Cycle</h3>
    <p><strong>Status:</strong> Foundation page active.</p>
    <p>Cycle governance UI shell is available. Final cycle orchestration behavior remains controlled by backend guard logic.</p>
</div>

<div class="card">
    <h4>Current Cycle (Placeholder)</h4>
    <ul>
        <li>Cycle State: Not wired to live source yet</li>
        <li>Guard Middleware: Enabled on protected routes</li>
        <li>Target: connect cycle status + timeline in next patch</li>
    </ul>
</div>

<div class="card">
    <h4>Actions (Informational)</h4>
    <p><button type="button" disabled>Open Month (Pending backend wiring)</button></p>
    <p><button type="button" disabled>Transition Month (Pending backend wiring)</button></p>
</div>

<div class="card">
    <a href="/ui/dashboard">&larr; Back to Dashboard</a>
</div>
@endsection
