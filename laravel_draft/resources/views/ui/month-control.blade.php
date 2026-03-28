@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Month Control</h3>
    <p>Operational month states sourced from <code>util_month_cycle</code>.</p>
</div>

<div class="card">
    <h4>Month States</h4>
    <table width="100%" cellpadding="4" cellspacing="0" border="1">
        <tr><th>Month Cycle</th><th>State</th><th>Locked At</th><th>Finalized At</th></tr>
        @forelse($rows as $row)
            <tr>
                <td>{{ $row->month_cycle ?? '' }}</td>
                <td>{{ $row->state ?? '' }}</td>
                <td>{{ $row->locked_at ?? '' }}</td>
                <td>{{ $row->finalized_at ?? '' }}</td>
            </tr>
        @empty
            <tr><td colspan="4">No month state rows found.</td></tr>
        @endforelse
    </table>
</div>
@endsection
