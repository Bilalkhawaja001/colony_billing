@extends('layouts.app')
@section('page_title', 'Billing Workspace')
@section('content')
<div class="grid">
    <div class="card col-8">
        <h3>Billing Workspace</h3>
        <p class="muted">Execute billing pipeline controls with authenticated + RBAC guarded actions.</p>
        <div class="actions" style="margin-top:10px">
            <form method="POST" action="/api/billing/precheck">@csrf<button class="btn btn-primary" type="submit">Run Precheck</button></form>
            <form method="POST" action="/api/billing/finalize">@csrf<button class="btn btn-success" type="submit">Finalize Billing</button></form>
            <form method="POST" action="/billing/lock">@csrf<button class="btn" type="submit">Lock Billing</button></form>
            <form method="POST" action="/billing/approve">@csrf<button class="btn btn-warning" type="submit">Approve Billing</button></form>
        </div>
    </div>

    <div class="card col-4">
        <h4>Pipeline Status</h4>
        <p><span class="badge">Guarded Route Access</span></p>
        <p class="muted">Use action buttons to trigger backend workflow endpoints. Response handling follows controller behavior.</p>
    </div>

    <div class="card col-6">
        <h4>Adjustments</h4>
        <div class="actions">
            <form method="POST" action="/billing/adjustments/create">@csrf<button class="btn" type="submit">Create Adjustment</button></form>
            <form method="POST" action="/billing/adjustments/approve">@csrf<button class="btn" type="submit">Approve Adjustment</button></form>
        </div>
    </div>

    <div class="card col-6">
        <h4>Recovery</h4>
        <form method="POST" action="/recovery/payment">@csrf<button class="btn" type="submit">Post Recovery Payment</button></form>
    </div>

    <div class="card col-12">
        <h4>Workspace Notes</h4>
        <p class="muted">UI is production-facing and operator-oriented. Deep transaction grids can be extended here without changing route contracts.</p>
    </div>
</div>
@endsection
