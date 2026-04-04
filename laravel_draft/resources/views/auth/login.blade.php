@extends('layouts.app')
@section('page_title','Login')
@section('page_subtitle','Access the existing billing workspace and protected tools using the shared premium frosted visual language.')
@section('content')
<div class="grid">
    <div class="col-4"></div>
    <div class="col-4 card soft">
        <h3 class="section-title">Welcome back</h3>
        <p class="muted" style="margin-bottom:12px">Sign in with your existing credentials. Authentication workflow remains unchanged.</p>
        <form method="post" action="/login" class="form-grid">
            @csrf
            <div class="field col-12">
                <label class="label">Username or Email</label>
                <input name="username" placeholder="Username or Email" required>
            </div>
            <div class="field col-12">
                <label class="label">Password</label>
                <input name="password" type="password" placeholder="Password" required>
            </div>
            <div class="col-12 toolbar">
                <button class="btn btn-primary" type="submit">Login</button>
                <a class="btn" href="/forgot-password">Forgot password?</a>
            </div>
        </form>
    </div>
    <div class="col-4"></div>
</div>
@endsection