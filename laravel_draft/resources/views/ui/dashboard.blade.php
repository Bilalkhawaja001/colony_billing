@extends('layouts.app')
@section('content')
<div class="card">
    <h3>Dashboard</h3>
    <form method="get" action="/ui/dashboard">
      <input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY">
      <button type="submit">Reload</button>
    </form>
    <p><strong>Month Cycle:</strong> {{ $monthCycle ?? 'N/A' }}</p>
    <p><strong>Employees Billed:</strong> {{ $kpis['employees_billed'] ?? 0 }}</p>
    <p><strong>Total Billed:</strong> {{ number_format((float)($kpis['total_billed'] ?? 0), 2) }}</p>
    <p><strong>Family Members:</strong> {{ $kpis['family_members'] ?? 0 }}</p>
    <p><strong>Van Kids:</strong> {{ $kpis['van_kids'] ?? 0 }}</p>
    <p><a href="/ui/reports?month_cycle={{ urlencode((string)$monthCycle) }}">Open Reports</a> | <a href="/ui/reconciliation?month_cycle={{ urlencode((string)$monthCycle) }}">Open Reconciliation</a></p>
</div>
@endsection