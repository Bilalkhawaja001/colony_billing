@extends('layouts.app')
@section('page_title','Dashboard')
@section('page_subtitle','Operations home for this billing month')
@section('hide_page_head', '1')

@section('content')
@php
    $rawMonth = trim((string)($monthCycle ?? ''));
    $hasPeriod = $rawMonth !== '';
    $mc = urlencode($rawMonth);
    $monthLabel = $hasPeriod ? $rawMonth : 'No month selected';

    $kpis = $kpis ?? [];
    $employeesBilled = (int)($kpis['employees_billed'] ?? 0);
    $totalBilled = (float)($kpis['total_billed'] ?? 0);
    $familyCount = (int)($kpis['family_members'] ?? 0);
    $vanKids = (int)($kpis['van_kids'] ?? 0);
    $totalUnits = (int)($kpis['total_units'] ?? 0);
    $houseUnits = (int)($kpis['house_units'] ?? 0);
    $bachelorUnits = (int)($kpis['bachelor_units'] ?? 0);
    $hostelUnits = (int)($kpis['hostel_units'] ?? 0);
    $adminColonies = (int)($kpis['container_units'] ?? 0);
    $uncategorizedUnits = (int)($kpis['uncategorized_units'] ?? 0);

    $familyRows = $familyRows ?? [];
    $vanRows = $vanRows ?? [];

    $monthQuery = $hasPeriod ? ('?month_cycle=' . $mc) : '';
    $routeWithMonth = fn (string $path) => $path . $monthQuery;
@endphp

<div class="dash-wrap">
    <section class="dash-head">
        <div class="dash-head-left">
            <div class="dash-head-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M4 20V10M10 20V4M16 20v-8M22 20v-6"/></svg>
            </div>
            <div>
                <h1>Dashboard</h1>
                <p>Operations home for this billing month</p>
            </div>
        </div>

        <div class="dash-head-actions">
            <form method="get" action="/dashboard" class="month-card">
                <span class="month-icon" aria-hidden="true"><svg viewBox="0 0 20 20"><rect x="3" y="4" width="14" height="13" rx="2"/><path d="M3 8h14M7 2v3M13 2v3"/></svg></span>
                <label>
                    <span>Billing Month</span>
                    <input name="month_cycle" value="{{ $rawMonth }}" placeholder="MM-YYYY" aria-label="Billing month">
                </label>
                <button type="submit" title="Reload Dashboard"><svg viewBox="0 0 20 20"><path d="M3 10a7 7 0 0 1 12-5M17 10a7 7 0 0 1-12 5M13 5h3V2M7 15H4v3"/></svg></button>
            </form>

            <span class="state-pill {{ $hasPeriod ? 'is-open' : 'is-muted' }}">
                <svg viewBox="0 0 18 18"><circle cx="9" cy="9" r="7"/><path d="M6 9l2 2 4-4"/></svg>
                {{ $hasPeriod ? 'Open' : 'Select Month' }}
            </span>

            <a class="billing-cta" href="{{ $routeWithMonth('/billing-run-lock') }}">
                <span><svg viewBox="0 0 20 20"><path d="M11 1 3 11h5l-1 7 9-11h-5z"/></svg></span>
                <strong>Billing Center</strong>
                <small>Start your workflow</small>
                <svg viewBox="0 0 20 20"><path d="M4 10h10M10 6l4 4-4 4"/></svg>
            </a>
        </div>
    </section>

    <section class="dash-card workflow-card">
        <div class="section-row">
            <div class="section-title"><span class="danger-icon"><svg viewBox="0 0 20 20"><path d="M10 2 19 18H1z"/><path d="M10 7v5M10 15h0"/></svg></span>Workflow Attention</div>
            <div class="section-actions">
                <a href="{{ $routeWithMonth('/month-lifecycle') }}"><svg viewBox="0 0 18 18"><rect x="2" y="3" width="14" height="13" rx="2"/><path d="M2 7h14M6 1v3M12 1v3"/></svg>Open Month Cycle</a>
                <a href="/dashboard{{ $monthQuery }}"><svg viewBox="0 0 18 18"><path d="M2 9a7 7 0 0 1 12-5M16 9a7 7 0 0 1-12 5M12 4h3V1M6 14H3v3"/></svg>Reload Dashboard</a>
            </div>
        </div>

        <div class="workflow-grid">
            <a class="wf-tile must" href="{{ $routeWithMonth('/imports-validation') }}">
                <span class="tile-icon"><svg viewBox="0 0 20 20"><circle cx="10" cy="10" r="8"/><path d="M10 6v5M10 14h0"/></svg></span>
                <span><strong>{{ $hasPeriod ? '0' : '1' }}</strong><em>Must Fix</em><small>Check data →</small></span>
            </a>
            <a class="wf-tile review" href="{{ $routeWithMonth('/reporting') }}">
                <span class="tile-icon"><svg viewBox="0 0 20 20"><path d="M1 10s3-6 9-6 9 6 9 6-3 6-9 6-9-6-9-6z"/><circle cx="10" cy="10" r="2.5"/></svg></span>
                <span><strong>{{ $hasPeriod ? '0' : '—' }}</strong><em>Please Review</em><small>Open reports →</small></span>
            </a>
            <a class="wf-tile ready" href="{{ $routeWithMonth('/billing-run-lock') }}">
                <span class="tile-icon"><svg viewBox="0 0 20 20"><circle cx="10" cy="10" r="8"/><path d="M6 10l3 3 5-6"/></svg></span>
                <span><strong>{{ $hasPeriod ? 'Ready' : 'Wait' }}</strong><em>Billing Center</em><small>Start workflow →</small></span>
            </a>
            <a class="wf-tile read" href="{{ $routeWithMonth('/meters-readings/readings') }}">
                <span class="tile-icon"><svg viewBox="0 0 20 20"><path d="M10 2c3 4 5 6 5 9a5 5 0 0 1-10 0c0-3 2-5 5-9z"/></svg></span>
                <span><strong>Open</strong><em>Readings</em><small>Meter data →</small></span>
            </a>
            <a class="wf-tile rooms" href="/housing-occupancy{{ $monthQuery }}">
                <span class="tile-icon"><svg viewBox="0 0 20 20"><path d="M3 17V8l7-5 7 5v9M8 17v-5h4v5"/></svg></span>
                <span><strong>Open</strong><em>Rooms</em><small>Housing data →</small></span>
            </a>
            <a class="wf-tile rate" href="{{ $routeWithMonth('/rates') }}">
                <span class="tile-icon"><svg viewBox="0 0 20 20"><path d="M11 3H6l1 5h4a2 2 0 0 1 0 4H6M6 8h7"/></svg></span>
                <span><strong>Open</strong><em>Rates</em><small>Review rates →</small></span>
            </a>
        </div>
    </section>

    <section class="kpi-grid">
        <a class="dash-card kpi-card blue" href="{{ $routeWithMonth('/reporting') }}">
            <span class="kpi-head"><span>Employees Billed</span><i><svg viewBox="0 0 22 22"><circle cx="11" cy="7" r="3.3"/><path d="M4 19c0-3.6 3-6 7-6s7 2.4 7 6"/></svg></i></span>
            <strong>{{ number_format($employeesBilled) }}</strong><small>This Month</small>
            <svg class="spark" viewBox="0 0 200 34" preserveAspectRatio="none"><polyline points="0,28 25,24 50,26 75,18 100,20 125,12 150,15 175,8 200,10"/></svg>
        </a>
        <a class="dash-card kpi-card green" href="{{ $routeWithMonth('/reports/monthly-summary') }}">
            <span class="kpi-head"><span>Total Billed</span><i><svg viewBox="0 0 22 22"><path d="M12 3H7l1 6h4a2.2 2.2 0 0 1 0 4.4H7M7 9h8"/></svg></i></span>
            <strong class="money">PKR {{ number_format($totalBilled, 2) }}</strong><small>This Month</small>
            <svg class="spark" viewBox="0 0 200 34" preserveAspectRatio="none"><polyline points="0,30 25,26 50,22 75,24 100,16 125,18 150,10 175,12 200,6"/></svg>
        </a>
        <a class="dash-card kpi-card purple" href="{{ $routeWithMonth('/family/details') }}">
            <span class="kpi-head"><span>Family Members</span><i><svg viewBox="0 0 22 22"><circle cx="7" cy="7" r="2.8"/><circle cx="15" cy="8" r="2.3"/><path d="M2 18c0-3 2.2-5 5-5M11 17c0-2.2 1.8-4 4-4"/></svg></i></span>
            <strong>{{ number_format($familyCount) }}</strong><small>Total Registered</small>
            <svg class="spark" viewBox="0 0 200 34" preserveAspectRatio="none"><polyline points="0,24 25,22 50,18 75,20 100,14 125,16 150,12 175,14 200,9"/></svg>
        </a>
        <a class="dash-card kpi-card orange" href="{{ $routeWithMonth('/transport') }}">
            <span class="kpi-head"><span>Van Kids</span><i><svg viewBox="0 0 22 22"><rect x="3" y="6" width="16" height="9" rx="2"/><circle cx="8" cy="17" r="1.6"/><circle cx="14" cy="17" r="1.6"/></svg></i></span>
            <strong>{{ number_format($vanKids) }}</strong><small>School Van</small>
            <svg class="spark" viewBox="0 0 200 34" preserveAspectRatio="none"><polyline points="0,20 25,24 50,18 75,22 100,16 125,20 150,14 175,18 200,12"/></svg>
        </a>
    </section>

    <section class="kpi-grid units-row">
        <a class="dash-card kpi-card teal" href="/unit-directory"><span class="kpi-head"><span>Total Rooms</span><i><svg viewBox="0 0 22 22"><path d="M3 17V8l8-5 8 5v9M8 17v-5h6v5"/></svg></i></span><strong>{{ number_format($totalUnits) }}</strong><small>All Rooms</small></a>
        <a class="dash-card kpi-card blue" href="/unit-directory?res_type=house"><span class="kpi-head"><span>House Units</span><i><svg viewBox="0 0 22 22"><path d="M3 17V8l8-5 8 5v9M8 17v-5h6v5"/></svg></i></span><strong>{{ number_format($houseUnits) }}</strong><small>Residential</small></a>
        <a class="dash-card kpi-card amber" href="/unit-directory?res_type=bachelor"><span class="kpi-head"><span>Bachelor Units</span><i><svg viewBox="0 0 22 22"><circle cx="11" cy="7" r="3.3"/><path d="M4 19c0-3.6 3-6 7-6s7 2.4 7 6"/></svg></i></span><strong>{{ number_format($bachelorUnits) }}</strong><small>Units</small></a>
        <a class="dash-card kpi-card pink" href="/unit-directory?res_type=hostel"><span class="kpi-head"><span>Hostel Units</span><i><svg viewBox="0 0 22 22"><rect x="4" y="3" width="12" height="14" rx="1.5"/><path d="M8 3v14M12 3v14M4 8h12"/></svg></i></span><strong>{{ number_format($hostelUnits) }}</strong><small>Hostel</small></a>
    </section>

    <section class="lower-grid">
        <div class="dash-card panel-card">
            <div class="panel-title"><svg viewBox="0 0 18 18"><path d="M10 1 3 10h5l-1 7 8-10h-5z"/></svg>Quick Actions</div>
            <div class="quick-grid">
                <a href="/unit-directory"><i class="blue"><svg viewBox="0 0 20 20"><rect x="4" y="3" width="12" height="14" rx="1.5"/><path d="M8 3v14M12 3v14M4 8h12"/></svg></i><span>Unit Directory</span></a>
                <a href="/people-residency"><i class="green"><svg viewBox="0 0 20 20"><circle cx="10" cy="7" r="3"/><path d="M4 17c0-3 2.7-5 6-5s6 2 6 5"/></svg></i><span>Employee Profile</span></a>
                <a href="/people-residency"><i class="purple"><svg viewBox="0 0 20 20"><circle cx="10" cy="10" r="8"/><path d="M10 6v8M6 10h8"/></svg></i><span>Add Employee</span></a>
                <a href="{{ $routeWithMonth('/reports/employee-statement') }}"><i class="orange"><svg viewBox="0 0 20 20"><path d="M5 2h7l3 3v13H5z"/><path d="M8 8h4M8 11h4M8 14h3"/></svg></i><span>Statement</span></a>
                <a href="/housing-occupancy{{ $monthQuery }}"><i class="teal"><svg viewBox="0 0 20 20"><path d="M3 17V8l7-5 7 5v9M8 17v-5h4v5"/></svg></i><span>Residence</span></a>
                <a href="{{ $routeWithMonth('/family/details') }}"><i class="pink"><svg viewBox="0 0 20 20"><circle cx="7" cy="7" r="2.5"/><circle cx="14" cy="8" r="2"/><path d="M2 17c0-2.6 2-4.5 5-4.5M11 16c0-2 1.6-3.5 3.5-3.5"/></svg></i><span>Family</span></a>
                <a href="{{ $routeWithMonth('/transport') }}"><i class="blue"><svg viewBox="0 0 20 20"><rect x="3" y="6" width="14" height="8" rx="2"/><circle cx="7" cy="16" r="1.5"/><circle cx="13" cy="16" r="1.5"/></svg></i><span>School Van</span></a>
                <a href="{{ $routeWithMonth('/meters-readings/readings') }}"><i class="green"><svg viewBox="0 0 20 20"><circle cx="10" cy="10" r="8"/><path d="M10 5v5l3 2"/></svg></i><span>Meter Readings</span></a>
                <a href="{{ $routeWithMonth('/active-days-monthly') }}"><i class="amber"><svg viewBox="0 0 20 20"><rect x="2" y="4" width="16" height="13" rx="2"/><path d="M2 8h16M6 2v3M14 2v3"/></svg></i><span>Active Days</span></a>
                <a href="{{ $routeWithMonth('/rates') }}"><i class="purple"><svg viewBox="0 0 20 20"><circle cx="6" cy="6" r="2.5"/><circle cx="14" cy="14" r="2.5"/><path d="M5 15 15 5"/></svg></i><span>Monthly Rates</span></a>
            </div>
        </div>

        <div class="dash-card panel-card">
            <div class="panel-title"><svg viewBox="0 0 18 18"><circle cx="9" cy="9" r="7"/><path d="M9 2v7l5 3"/></svg>Resident Type Overview</div>
            <a class="donut-link" href="/unit-directory">
                <div class="donut">
                    <svg viewBox="0 0 42 42" width="140" height="140">
                        <circle cx="21" cy="21" r="15.9" fill="none" stroke="#EEF2F8" stroke-width="6"/>
                        <circle cx="21" cy="21" r="15.9" fill="none" stroke="#2563EB" stroke-width="6" stroke-dasharray="43 57" stroke-dashoffset="0" transform="rotate(-90 21 21)"/>
                        <circle cx="21" cy="21" r="15.9" fill="none" stroke="#F59E0B" stroke-width="6" stroke-dasharray="29 71" stroke-dashoffset="-43" transform="rotate(-90 21 21)"/>
                        <circle cx="21" cy="21" r="15.9" fill="none" stroke="#DB2777" stroke-width="6" stroke-dasharray="21 79" stroke-dashoffset="-72" transform="rotate(-90 21 21)"/>
                        <circle cx="21" cy="21" r="15.9" fill="none" stroke="#7C3AED" stroke-width="6" stroke-dasharray="5 95" stroke-dashoffset="-93" transform="rotate(-90 21 21)"/>
                    </svg>
                    <span><strong>{{ number_format($totalUnits) }}</strong><em>Total Units</em></span>
                </div>
                <div class="legend-list">
                    <span><i style="background:#2563EB"></i>House Units <b>{{ number_format($houseUnits) }}</b></span>
                    <span><i style="background:#F59E0B"></i>Bachelor Units <b>{{ number_format($bachelorUnits) }}</b></span>
                    <span><i style="background:#DB2777"></i>Hostel Units <b>{{ number_format($hostelUnits) }}</b></span>
                    <span><i style="background:#7C3AED"></i>Admin Colonies <b>{{ number_format($adminColonies) }}</b></span>
                    <span><i style="background:#94A3B8"></i>Uncategorized <b>{{ number_format($uncategorizedUnits) }}</b></span>
                </div>
            </a>
        </div>

        <div class="dash-card panel-card">
            <div class="panel-title"><svg viewBox="0 0 18 18"><rect x="2" y="3" width="14" height="13" rx="2"/><path d="M2 7h14M6 1v3M12 1v3"/></svg>Recent Activity</div>
            <div class="activity-empty">
                <strong>No recent activity connected yet.</strong>
                <span>Last bill, upload, reading update and export records will appear here when the activity feed is available.</span>
                <a href="{{ $routeWithMonth('/billing-run-lock') }}">Open Billing Center →</a>
            </div>
        </div>
    </section>

    <section class="dash-card reports-card">
        <div class="panel-title"><svg viewBox="0 0 18 18"><path d="M4 16V8M9 16V4M14 16v-6"/></svg>Reports Shortcuts</div>
        <div class="reports-grid">
            <a href="{{ $routeWithMonth('/reports/monthly-summary') }}">Monthly Summary</a>
            <a href="{{ $routeWithMonth('/reports/employee-bill-summary') }}">Employee Bill Summary</a>
            <a href="{{ $routeWithMonth('/reports/reconciliation') }}">Reconciliation Report</a>
            <a href="{{ $routeWithMonth('/reports/recovery') }}">Recovery Report</a>
            <a href="{{ $routeWithMonth('/reports/van') }}">Van Report</a>
            <a href="{{ $routeWithMonth('/reporting') }}">More Reports ···</a>
        </div>
    </section>

    <footer class="dash-foot">Colony Billing · Enterprise Platform · nodesky.pk/billing</footer>
</div>

<style>
:root{--db-bg:#eef2f8;--db-surface:#fff;--db-ink:#0f172a;--db-muted:#64748b;--db-faint:#94a3b8;--db-line:#e8ecf3;--db-blue:#2563eb;--db-navy:#0b1c3a;--db-shadow:0 1px 3px rgba(15,23,42,.05),0 8px 24px rgba(15,23,42,.06)}
.dash-wrap{max-width:1320px;margin:0 auto 28px;padding:0 0 4px;color:var(--db-ink)}
.dash-wrap svg{stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
.dash-head{display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;margin:4px 0 22px}
.dash-head-left{display:flex;align-items:center;gap:14px}.dash-head-icon{width:54px;height:54px;border-radius:14px;background:linear-gradient(135deg,#3b82f6,#1e3a8a);display:grid;place-items:center;color:#fff;box-shadow:0 6px 16px rgba(37,99,235,.3)}.dash-head-icon svg{width:27px;height:27px}.dash-head h1{margin:0;font-size:25px;font-weight:900;letter-spacing:-.03em}.dash-head p{margin:2px 0 0;color:var(--db-muted);font-size:13.5px}
.dash-head-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.month-card{display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--db-line);border-radius:13px;padding:8px 10px 8px 14px;box-shadow:var(--db-shadow)}.month-icon{width:34px;height:34px;border-radius:9px;background:#eff4ff;color:#2563eb;display:grid;place-items:center}.month-icon svg{width:18px;height:18px}.month-card label{display:flex;flex-direction:column;gap:1px}.month-card label span{font-size:10.5px;color:var(--db-muted);font-weight:800;text-transform:uppercase;letter-spacing:.05em}.month-card input{width:112px;min-height:auto;border:0;background:transparent;box-shadow:none;padding:0;color:var(--db-ink);font-size:15px;font-weight:900;letter-spacing:-.01em}.month-card button{width:32px;height:32px;border:0;border-radius:9px;background:#eff4ff;color:#2563eb;display:grid;place-items:center;cursor:pointer}.month-card button svg{width:17px;height:17px}.state-pill{display:inline-flex;align-items:center;gap:7px;border-radius:11px;padding:11px 16px;font-weight:800;font-size:14px}.state-pill svg{width:16px;height:16px}.state-pill.is-open{background:#ecfdf3;color:#16a34a;border:1px solid #bbf7d0}.state-pill.is-muted{background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1}.billing-cta{display:grid;grid-template-columns:34px auto 18px;align-items:center;column-gap:12px;background:linear-gradient(135deg,#2563eb,#1e3a8a);color:#fff;border-radius:13px;padding:10px 18px;box-shadow:0 8px 20px rgba(37,99,235,.35);text-decoration:none}.billing-cta:hover{transform:translateY(-1px);box-shadow:0 10px 26px rgba(37,99,235,.45)}.billing-cta span{width:34px;height:34px;border-radius:9px;background:rgba(255,255,255,.18);display:grid;place-items:center}.billing-cta strong{display:block;font-size:14.5px;line-height:1.1}.billing-cta small{grid-column:2;font-size:11.5px;opacity:.86}
.dash-card{background:#fff;border:1px solid var(--db-line);border-radius:16px;box-shadow:var(--db-shadow)}.workflow-card{padding:20px}.section-row{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px}.section-title{display:flex;align-items:center;gap:10px;font-size:16px;font-weight:900}.danger-icon{width:34px;height:34px;border-radius:9px;background:#fee2e2;color:#dc2626;display:grid;place-items:center}.danger-icon svg{width:18px;height:18px}.section-actions{display:flex;gap:10px;flex-wrap:wrap}.section-actions a{display:flex;align-items:center;gap:8px;border:1px solid var(--db-line);background:#fff;border-radius:11px;padding:9px 15px;font-size:13.5px;font-weight:800;color:var(--db-ink);text-decoration:none}.section-actions a:hover{border-color:#2563eb;color:#2563eb}.section-actions svg{width:17px;height:17px;color:#2563eb}
.workflow-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:13px}.wf-tile{display:flex;align-items:center;gap:11px;border:1px solid var(--db-line);border-radius:13px;padding:15px;text-decoration:none;transition:.15s;min-width:0}.wf-tile:hover{transform:translateY(-2px);box-shadow:var(--db-shadow)}.wf-tile .tile-icon{width:42px;height:42px;border-radius:11px;display:grid;place-items:center;flex:0 0 42px}.wf-tile svg{width:20px;height:20px}.wf-tile strong{display:block;font-size:21px;font-weight:900;line-height:1;letter-spacing:-.03em}.wf-tile em{display:block;font-style:normal;font-size:12.5px;color:#64748b;font-weight:800;margin-top:3px}.wf-tile small{display:block;font-size:12px;font-weight:850;margin-top:7px}.wf-tile.must{background:#fff4ed}.wf-tile.must .tile-icon{background:#fecaca;color:#dc2626}.wf-tile.must small{color:#dc2626}.wf-tile.review,.wf-tile.rate{background:#fffbeb}.wf-tile.review .tile-icon{background:#fed7aa;color:#ea580c}.wf-tile.review small{color:#ea580c}.wf-tile.ready{background:#ecfdf3}.wf-tile.ready .tile-icon{background:#bbf7d0;color:#16a34a}.wf-tile.ready small{color:#16a34a}.wf-tile.read{background:#eff4ff}.wf-tile.read .tile-icon{background:#dbeafe;color:#2563eb}.wf-tile.read small{color:#2563eb}.wf-tile.rooms{background:#f5f0ff}.wf-tile.rooms .tile-icon{background:#e9d5ff;color:#7c3aed}.wf-tile.rooms small{color:#7c3aed}.wf-tile.rate .tile-icon{background:#fde68a;color:#d97706}.wf-tile.rate small{color:#d97706}
.kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-top:18px}.kpi-card{display:block;padding:20px;position:relative;overflow:hidden;text-decoration:none;color:var(--db-ink);transition:.16s}.kpi-card:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(15,23,42,.1)}.kpi-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:7px;color:#64748b;font-weight:800}.kpi-head i{width:48px;height:48px;border-radius:13px;display:grid;place-items:center;color:#fff;box-shadow:0 6px 14px rgba(0,0,0,.12)}.kpi-head svg{width:22px;height:22px}.kpi-card strong{display:block;font-size:28px;font-weight:900;letter-spacing:-.03em}.kpi-card strong.money{font-size:23px}.kpi-card small{display:block;font-size:12.5px;color:#94a3b8;margin-top:2px}.kpi-card .spark{width:100%;height:34px;margin-top:12px;display:block}.kpi-card .spark polyline{stroke:currentColor;stroke-width:2.2;fill:none}.kpi-card.blue{color:#2563eb}.kpi-card.green{color:#16a34a}.kpi-card.purple{color:#7c3aed}.kpi-card.orange{color:#ea580c}.kpi-card.teal{color:#0d9488}.kpi-card.amber{color:#d97706}.kpi-card.pink{color:#db2777}.kpi-card.blue .kpi-head i{background:linear-gradient(135deg,#3b82f6,#2563eb)}.kpi-card.green .kpi-head i{background:linear-gradient(135deg,#22c55e,#16a34a)}.kpi-card.purple .kpi-head i{background:linear-gradient(135deg,#a855f7,#7c3aed)}.kpi-card.orange .kpi-head i{background:linear-gradient(135deg,#fb923c,#ea580c)}.kpi-card.teal .kpi-head i{background:linear-gradient(135deg,#2dd4bf,#0d9488)}.kpi-card.amber .kpi-head i{background:linear-gradient(135deg,#fbbf24,#d97706)}.kpi-card.pink .kpi-head i{background:linear-gradient(135deg,#f472b6,#db2777)}.units-row .kpi-card .spark{display:none}
.lower-grid{display:grid;grid-template-columns:1.15fr 1fr 1.15fr;gap:16px;margin-top:18px}.panel-card{padding:20px}.panel-title{display:flex;align-items:center;gap:9px;font-size:15.5px;font-weight:900;margin-bottom:16px}.panel-title svg{width:18px;height:18px;color:#2563eb}.quick-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:11px}.quick-grid a{text-align:center;padding:13px 6px;border-radius:12px;text-decoration:none;color:#0f172a}.quick-grid a:hover{background:#eef2f8}.quick-grid i{width:46px;height:46px;border-radius:13px;display:grid;place-items:center;color:#fff;margin:0 auto 8px;box-shadow:0 4px 10px rgba(0,0,0,.1)}.quick-grid i svg{width:20px;height:20px}.quick-grid span{display:block;font-size:11.5px;font-weight:850}.quick-grid .blue{background:linear-gradient(135deg,#3b82f6,#1e3a8a)}.quick-grid .green{background:linear-gradient(135deg,#22c55e,#16a34a)}.quick-grid .purple{background:linear-gradient(135deg,#a855f7,#7c3aed)}.quick-grid .orange{background:linear-gradient(135deg,#fb923c,#ea580c)}.quick-grid .teal{background:linear-gradient(135deg,#2dd4bf,#0d9488)}.quick-grid .pink{background:linear-gradient(135deg,#f472b6,#db2777)}.quick-grid .amber{background:linear-gradient(135deg,#fbbf24,#d97706)}
.donut-link{display:flex;align-items:center;gap:18px;text-decoration:none;color:#0f172a}.donut{position:relative;width:140px;height:140px;flex:0 0 140px}.donut > span{position:absolute;inset:0;display:grid;place-items:center;text-align:center}.donut strong{font-size:24px;font-weight:900;line-height:1}.donut em{display:block;font-style:normal;font-size:11px;color:#64748b}.legend-list{flex:1;display:flex;flex-direction:column;gap:9px}.legend-list span{display:flex;align-items:center;gap:9px;font-size:13px;font-weight:750}.legend-list i{width:11px;height:11px;border-radius:3px;flex:0 0 11px}.legend-list b{margin-left:auto;color:#64748b;font-size:12.5px}.activity-empty{min-height:230px;display:flex;flex-direction:column;align-items:flex-start;justify-content:center;gap:8px;color:#64748b}.activity-empty strong{font-size:15px;color:#0f172a}.activity-empty a{font-weight:900;color:#2563eb;text-decoration:none}.reports-card{padding:18px 20px;margin-top:16px}.reports-grid{display:flex;gap:12px;flex-wrap:wrap}.reports-grid a{flex:1;min-width:160px;display:flex;align-items:center;justify-content:center;border:1px solid var(--db-line);border-radius:12px;padding:13px 15px;font-size:13.5px;font-weight:850;color:#2563eb;text-decoration:none}.reports-grid a:hover{border-color:#2563eb;background:#eff4ff}.dash-foot{margin:30px 0 10px;text-align:center;font-size:12px;color:#94a3b8}
@media(max-width:1100px){.workflow-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.lower-grid{grid-template-columns:1fr}.quick-grid{grid-template-columns:repeat(5,1fr)}}
@media(max-width:720px){.dash-wrap{padding:0}.dash-head-actions{width:100%}.month-card,.billing-cta{width:100%}.workflow-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.kpi-grid{grid-template-columns:1fr}.quick-grid{grid-template-columns:repeat(3,1fr)}.donut-link{flex-direction:column;align-items:flex-start}.state-pill{width:100%;justify-content:center}.dash-head-left{align-items:flex-start}}
</style>
@endsection
