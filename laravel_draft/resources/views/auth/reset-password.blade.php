@extends('layouts.app')
@section('page_title','Reset Password')
@section('page_subtitle','Complete OTP verification and set a new password using the existing backend flow with improved visual polish.')
@section('content')
<div class="grid">
    <div class="col-4"></div>
    <div class="col-4 card soft">
        <h3 class="section-title">Set a new password</h3>
        <p class="muted" style="margin-bottom:12px">Use the OTP you received and choose your new password below.</p>
        <form method="post" action="/reset-password" class="form-grid">
            @csrf
            <div class="field col-12"><label class="label">Username or Email</label><input name="identity" placeholder="Username or Email" required></div>
            <div class="field col-12"><label class="label">6-digit OTP</label><input name="otp" placeholder="6-digit OTP" required></div>
            <div class="field col-12"><label class="label">New Password</label><input name="new_password" type="password" placeholder="New Password" required></div>
            <div class="col-12 toolbar">
                <button class="btn btn-primary" type="submit">Reset</button>
            </div>
        </form>
    </div>
    <div class="col-4"></div>
</div>
@endsection