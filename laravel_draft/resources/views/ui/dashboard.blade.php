@extends('layouts.app')
@section('page_title','Dashboard')
@section('page_subtitle','Operational control center for month-cycle billing, reporting and reconciliation health.')
@section('content')
<div class="grid">
    <div class="col-3 card">
        <div class="muted">Employees Billed</div>
        <div class="kpi">{{ $kpis['employees_billed'] ?? 0 }}</div>
        <span class="badge success">Billing Coverage</span>
    </div>
    <div class="col-3 card">
        <div class="muted">Total Billed</div>
        <div class="kpi">{{ number_format((float)($kpis['total_billed'] ?? 0), 2) }}</div>
        <span class="badge">Financial</span>
    </div>
    <div class="col-3 card">
        <div class="muted">Family Members</div>
        <div class="kpi">{{ $kpis['family_members'] ?? 0 }}</div>
        <span class="badge">Registry</span>
    </div>
    <div class="col-3 card">
        <div class="muted">Van Kids</div>
        <div class="kpi">{{ $kpis['van_kids'] ?? 0 }}</div>
        <span class="badge warn">Transport</span>
    </div>

    <div class="col-8 card">
        <h3 class="section-title">Month Control + Quick Actions</h3>
        <form method="get" action="/dashboard" class="form-grid" style="margin-bottom:12px;">
            <div class="field col-4">
                <label class="label">Month Cycle</label>
                <input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY">
            </div>
            <div class="field col-8" style="justify-content:flex-end;display:flex;align-items:flex-end;">
                <div class="split">
                    <button class="btn btn-primary" type="submit">Reload Dashboard</button>
                    <a class="btn" href="/month-lifecycle?month_cycle={{ urlencode((string)$monthCycle) }}">Open Month Cycle</a>
                    <a class="btn" href="/billing-run-lock?month_cycle={{ urlencode((string)$monthCycle) }}">Open Billing</a>
                </div>
            </div>
        </form>
        <div class="split">
            <a class="btn" href="/reporting?month_cycle={{ urlencode((string)$monthCycle) }}">Reports</a>
            <a class="btn" href="/reporting?month_cycle={{ urlencode((string)$monthCycle) }}">Reconciliation</a>
            <a class="btn" href="/imports-validation?month_cycle={{ urlencode((string)$monthCycle) }}">Imports</a>
            <a class="btn" href="/rates?month_cycle={{ urlencode((string)$monthCycle) }}">Rates</a>
        </div>
    </div>

    <div class="col-4 card soft">
        <h3 class="section-title">Workflow Attention</h3>
        <div class="muted" style="margin-bottom:8px">Month Context</div>
        <div><strong>{{ $monthCycle ?? 'N/A' }}</strong></div>
        <div class="muted" style="margin-top:10px">Operator Checks</div>
        <ul style="margin:8px 0 0 18px;padding:0;color:#334155;font-size:13px;line-height:1.6">
            <li>Rates approved before billing run</li>
            <li>Imports validated and errors reviewed</li>
            <li>Reports and reconciliation reviewed</li>
        </ul>
    </div>

    <div class="col-12 card">
        <h3 class="section-title">Recent Workflow Summary</h3>
        <div class="grid" style="gap:10px">
            <div class="col-3 card soft"><div class="muted">Billing</div><div style="font-weight:700">Run + lock control ready</div></div>
            <div class="col-3 card soft"><div class="muted">Month Cycle</div><div style="font-weight:700">State transitions available</div></div>
            <div class="col-3 card soft"><div class="muted">Reports</div><div style="font-weight:700">JSON + export endpoints linked</div></div>
            <div class="col-3 card soft"><div class="muted">Data Inputs</div><div style="font-weight:700">Mapping / HR / Readings / RO access</div></div>
        </div>
    </div>
</div>
@endsection
