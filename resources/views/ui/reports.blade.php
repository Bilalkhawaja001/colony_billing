@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Reports</h3>
    <p><strong>Month Cycle:</strong> {{ $monthCycle ?? 'N/A' }}</p>
</div>

<div class="card">
    <h4>Utility Summary</h4>
    <table width="100%" cellpadding="4" cellspacing="0" border="1">
        <tr><th>Utility Type</th><th>Total Amount</th></tr>
        @forelse($rows as $row)
            <tr>
                <td>{{ $row->utility_type ?? '' }}</td>
                <td>{{ number_format((float)($row->total_amount ?? 0), 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="2">No report rows found.</td></tr>
        @endforelse
    </table>
</div>
@endsection
