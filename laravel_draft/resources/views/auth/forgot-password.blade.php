@extends('layouts.app')
@section('page_title','Forgot Password')
@section('page_subtitle','Request an OTP through the unchanged recovery workflow, now styled with the shared frosted design system.')
@section('content')
<div class="grid">
    <div class="col-4"></div>
    <div class="col-4 card soft">
        <h3 class="section-title">Reset request</h3>
        <p class="muted" style="margin-bottom:12px">Enter your username or email to receive the existing password recovery OTP.</p>
        <form method="post" action="/forgot-password" class="form-grid">
            @csrf
            <div class="field col-12">
                <label class="label">Username or Email</label>
                <input name="identity" placeholder="Username or Email" required>
            </div>
            <div class="col-12 toolbar">
                <button class="btn btn-primary" type="submit">Send OTP</button>
            </div>
        </form>
    </div>
    <div class="col-4"></div>
</div>
@endsection