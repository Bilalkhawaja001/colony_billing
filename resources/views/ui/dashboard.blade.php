@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Dashboard</h3>
    <p><strong>Month Cycle:</strong> {{ $monthCycle ?? 'N/A' }}</p>
    <p><strong>Employees Billed:</strong> {{ $kpis['employees_billed'] ?? 0 }}</p>
    <p><strong>Total Billed:</strong> {{ number_format((float)($kpis['total_billed'] ?? 0), 2) }}</p>
    <p><strong>Family Members:</strong> {{ $kpis['family_members'] ?? 0 }}</p>
    <p><strong>Van Kids:</strong> {{ $kpis['van_kids'] ?? 0 }}</p>
</div>

<div class="card">
    <h4>Family Members (Recent)</h4>
    <table width="100%" cellpadding="4" cellspacing="0" border="1">
        <tr><th>Employee ID</th><th>Name</th><th>Relation</th><th>Age</th></tr>
        @forelse(array_slice($familyRows, 0, 10) as $row)
            <tr>
                <td>{{ $row->employee_id ?? '' }}</td>
                <td>{{ $row->family_member_name ?? '' }}</td>
                <td>{{ $row->relation ?? '' }}</td>
                <td>{{ $row->age ?? '' }}</td>
            </tr>
        @empty
            <tr><td colspan="4">No family records found.</td></tr>
        @endforelse
    </table>
</div>

<div class="card">
    <h4>Van Kids (Recent)</h4>
    <table width="100%" cellpadding="4" cellspacing="0" border="1">
        <tr><th>Employee ID</th><th>Child</th><th>School</th><th>Class</th><th>Amount</th></tr>
        @forelse(array_slice($vanRows, 0, 10) as $row)
            <tr>
                <td>{{ $row->employee_id ?? '' }}</td>
                <td>{{ $row->child_name ?? '' }}</td>
                <td>{{ $row->school_name ?? '' }}</td>
                <td>{{ $row->class_level ?? '' }}</td>
                <td>{{ number_format((float)($row->amount ?? 0), 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="5">No van records found.</td></tr>
        @endforelse
    </table>
</div>
@endsection
