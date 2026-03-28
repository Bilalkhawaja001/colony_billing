@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Reports</h3>
    <form method="get" action="/ui/reports">
      <input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY">
      <button type="submit">Reload</button>
    </form>
    <p><strong>Month Cycle:</strong> {{ $monthCycle ?? 'N/A' }}</p>
    <p>
      <a href="/reports/monthly-summary?month_cycle={{ urlencode((string)$monthCycle) }}">Monthly Summary JSON</a> |
      <a href="/reports/employee-bill-summary?month_cycle={{ urlencode((string)$monthCycle) }}">Employee Bill Summary JSON</a> |
      <a href="/reports/recovery?month_cycle={{ urlencode((string)$monthCycle) }}">Recovery JSON</a> |
      <a href="/export/excel/monthly-summary?month_cycle={{ urlencode((string)$monthCycle) }}">Export Excel</a> |
      <a href="/export/pdf/monthly-summary?month_cycle={{ urlencode((string)$monthCycle) }}">Export PDF</a>
    </p>
</div>
<div class="card">
    <h4>Utility Summary</h4>
    <table width="100%" cellpadding="4" cellspacing="0" border="1">
        <tr><th>Utility Type</th><th>Total Amount</th></tr>
        @forelse($rows as $row)
            <tr><td>{{ $row->utility_type ?? '' }}</td><td>{{ number_format((float)($row->total_amount ?? 0), 2) }}</td></tr>
        @empty
            <tr><td colspan="2">No report rows found.</td></tr>
        @endforelse
    </table>
</div>
@endsection