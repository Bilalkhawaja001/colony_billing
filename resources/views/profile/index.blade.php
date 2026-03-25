@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Profile / Force Password Change Screen</h3>
    <form method="post" action="/api/profile/change-password">
        @csrf
        <input name="old_password" type="password" placeholder="Old Password" required><br>
        <input name="new_password" type="password" placeholder="New Password (min 8)" required><br>
        <button type="submit">Change Password</button>
    </form>
</div>
@endsection
