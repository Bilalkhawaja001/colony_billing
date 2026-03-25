@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Reset Password</h3>
    <form method="post" action="/reset-password">
        @csrf
        <input name="identity" placeholder="Username or Email" required><br>
        <input name="otp" placeholder="6-digit OTP" required><br>
        <input name="new_password" type="password" placeholder="New Password" required><br>
        <button type="submit">Reset</button>
    </form>
</div>
@endsection
