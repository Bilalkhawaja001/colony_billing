@extends('layouts.app')
@section('page_title','Reconciliation')
@section('page_subtitle','Track billed, recovered and outstanding positions with export-ready reconciliation controls.')
@section('content')
<div class="grid">
    <div class="col-12 card">
        <form method="get" action="/reporting" class="form-grid" style="margin-bottom:10px">
            <div class="field col-4"><label class="label">Month Cycle</label><input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY"></div>
            <div class="col-8" style="display:flex;align-items:flex-end"><button class="btn btn-primary" type="submit">Reload</button></div>
        </form>
        <div class="toolbar">
            <a class="btn" href="/reports/reconciliation?month_cycle={{ urlencode((string)$monthCycle) }}">Reconciliation JSON</a>
            <a class="btn" href="/export/excel/reconciliation?month_cycle={{ urlencode((string)$monthCycle) }}">Export Reconciliation Excel</a>
        </div>
    </div>
    <div class="col-12 card">
        <h3 class="section-title">Employee Reconciliation</h3>
        <table>
            <thead><tr><th>Employee ID</th><th>Billed</th><th>Recovered</th><th>Outstanding</th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->employee_id ?? '' }}</td>
                    <td>{{ number_format((float)($row->billed ?? 0), 2) }}</td>
                    <td>{{ number_format((float)($row->recovered ?? 0), 2) }}</td>
                    <td><span class="badge warn">{{ number_format((float)($row->outstanding ?? 0), 2) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4"><div class="empty">No reconciliation rows found.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
