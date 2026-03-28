@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Reconciliation</h3>
    <form method="get" action="/ui/reconciliation">
      <input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY">
      <button type="submit">Reload</button>
    </form>
    <p><strong>Month Cycle:</strong> {{ $monthCycle ?? 'N/A' }}</p>
    <p>
      <a href="/reports/reconciliation?month_cycle={{ urlencode((string)$monthCycle) }}">Reconciliation JSON</a> |
      <a href="/export/excel/reconciliation?month_cycle={{ urlencode((string)$monthCycle) }}">Export Reconciliation Excel</a>
    </p>
</div>
<div class="card">
    <h4>Employee Reconciliation</h4>
    <table width="100%" cellpadding="4" cellspacing="0" border="1">
        <tr><th>Employee ID</th><th>Billed</th><th>Recovered</th><th>Outstanding</th></tr>
        @forelse($rows as $row)
            <tr>
                <td>{{ $row->employee_id ?? '' }}</td>
                <td>{{ number_format((float)($row->billed ?? 0), 2) }}</td>
                <td>{{ number_format((float)($row->recovered ?? 0), 2) }}</td>
                <td>{{ number_format((float)($row->outstanding ?? 0), 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="4">No reconciliation rows found.</td></tr>
        @endforelse
    </table>
</div>
@endsection