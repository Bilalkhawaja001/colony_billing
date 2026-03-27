@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Admin Users</h3>
    <p>Role-gated user management page (Flask parity slice).</p>

    <table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Active</th>
                <th>Force Password Change</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>{{ $u->username }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->role }}</td>
                    <td>{{ (int) $u->is_active === 1 ? 'Yes' : 'No' }}</td>
                    <td>{{ (int) $u->force_change_password === 1 ? 'Yes' : 'No' }}</td>
                    <td>{{ $u->created_at }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
