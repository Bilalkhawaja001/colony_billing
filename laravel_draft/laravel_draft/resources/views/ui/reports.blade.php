@extends('layouts.app')
@section('page_title','Reporting Center')
@section('page_subtitle','Consolidated reporting center for monthly summary, recovery and employee bill analysis.')
@section('content')
<div class="grid">
    <div class="col-12 card">
        <form method="get" action="/reporting" class="form-grid" style="margin-bottom:10px">
            <div class="field col-4"><label class="label">Month Cycle</label><input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY"></div>
            <div class="col-8" style="display:flex;align-items:flex-end"><button class="btn btn-primary" type="submit">Reload</button></div>
        </form>
        <div class="toolbar sticky-actions">
            <a class="btn" href="/reports/monthly-summary?month_cycle={{ urlencode((string)$monthCycle) }}">Monthly Summary JSON</a>
            <a class="btn" href="/reports/employee-bill-summary?month_cycle={{ urlencode((string)$monthCycle) }}">Employee Bill Summary</a>
            <a class="btn" href="/reports/recovery?month_cycle={{ urlencode((string)$monthCycle) }}">Recovery JSON</a>
            <a class="btn" href="/export/excel/monthly-summary?month_cycle={{ urlencode((string)$monthCycle) }}">Export Excel</a>
            <a class="btn" href="/export/pdf/monthly-summary?month_cycle={{ urlencode((string)$monthCycle) }}">Export PDF</a>
        </div>
    </div>
    <div class="col-12 card">
        <h3 class="section-title">Utility Summary</h3>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Utility Type</th><th>Total Amount</th></tr></thead>
                <tbody>
                @forelse($rows as $row)
                    <tr><td>{{ $row->utility_type ?? '' }}</td><td>{{ number_format((float)($row->total_amount ?? 0), 2) }}</td></tr>
                @empty
                    <tr><td colspan="2"><div class="empty">No report rows found for selected month.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
