@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Login</h3>
    <form method="post" action="/login">
        @csrf
        <input name="username" placeholder="Username or Email" required><br>
        <input name="password" type="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <a href="/forgot-password">Forgot password?</a>
</div>
@endsection
