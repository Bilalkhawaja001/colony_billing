@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Forgot Password</h3>
    <form method="post" action="/forgot-password">
        @csrf
        <input name="identity" placeholder="Username or Email" required><br>
        <button type="submit">Send OTP</button>
    </form>
</div>
@endsection
