@extends('layouts.app')
@section('page_title','Secure Access')
@section('page_subtitle','Protected access for colony billing operations, month lifecycle, reports and reconciliation.')
@section('content')
<div class="grid" style="min-height:calc(100vh - 210px);align-items:center">
    <div class="col-6 card" style="padding:28px">
        <span class="badge">Enterprise Billing Platform</span>
        <h3 class="section-title" style="font-size:30px;margin-top:16px">Colony Billing Control Center</h3>
        <p class="muted">
            Secure workspace for electricity billing, monthly active days, transport billing, meter readings, rates, reports and administrative control.
        </p>
        <div class="grid" style="margin-top:18px">
            <div class="col-6 card soft">
                <div class="muted">System Scope</div>
                <strong>Billing Operations</strong>
            </div>
            <div class="col-6 card soft">
                <div class="muted">Access Layer</div>
                <strong>Role Protected</strong>
            </div>
        </div>
    </div>

    <div class="col-6 card" style="max-width:460px;margin-left:auto;padding:28px">
        <h3 class="section-title">Login</h3>
        <p class="muted" style="margin-bottom:14px">Enter authorized credentials to continue.</p>

        <form method="post" action="/login" class="form-grid">
            @csrf
            <div class="field col-12">
                <label class="label">Username or Email</label>
                <input name="username" placeholder="Enter username or email" required autocomplete="username">
            </div>
            <div class="field col-12">
                <label class="label">Password</label>
                <input name="password" type="password" placeholder="Enter password" required autocomplete="current-password">
            </div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit" style="width:100%">Login to Dashboard</button>
            </div>
            <div class="col-12">
                <a class="muted" href="/forgot-password">Forgot password?</a>
            </div>
        </form>
    </div>
</div>
@endsection
