@extends('layouts.app')
@section('page_title','Billing Command Dashboard')
@section('page_subtitle','Readiness console for the billing month — what is ready, what is missing, and the next action.')
@section('content')
@php
    // Honest, derived-only signals. No backend readiness feed is wired to this
    // view yet, so status tiles default to PENDING and no values are fabricated.
    $hasPeriod = trim((string)($monthCycle ?? '')) !== '';
    $mc = urlencode((string)($monthCycle ?? ''));
@endphp
<div class="grid dashboard-command-console">

    <!-- DASHBOARD_COMMAND_PILLS_START -->
    <div class="col-12 command-row" id="dashboardCommandRow">
        <div class="command-row-label">Commands</div>

        <a class="command-pill pill-blue" href="/unit-directory">
            <svg viewBox="0 0 24 24"><path d="M5 21V4h14v17M9 8h2m2 0h2M9 12h2m2 0h2M10 21v-5h4v5"/></svg>
            <span>Unit Directory</span>
        </a>

        <a class="command-pill pill-purple" href="/people-residency?mode=manage">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c1-3.6 3.2-5.3 7-5.3s6 1.7 7 5.3"/></svg>
            <span>Employee Profile</span>
        </a>

        <a class="command-pill pill-green primary" href="/people-residency?action=add&mode=quick">
            <svg viewBox="0 0 24 24"><circle cx="8" cy="8" r="3"/><path d="M3.5 19c.7-3 2.3-4.5 4.5-4.5M18 11v10m-5-5h10"/></svg>
            <span>Add Employee</span>
        </a>

        <a class="command-pill pill-orange" href="/reports/employee-statement?month_cycle={{ $mc }}">
            <svg viewBox="0 0 24 24"><path d="M6 3h9l3 3v15H6zM14 3v4h4M9 12h6m-6 4h6"/></svg>
            <span>Statement</span>
        </a>

        <a class="command-pill pill-cyan" href="/people-residency?tab=occupancy">
            <svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5M5.5 10.5V20h13v-9.5M10 20v-5h4v5"/></svg>
            <span>Residence</span>
        </a>

        <a class="command-pill pill-pink" href="/people-residency?tab=family">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><circle cx="16.5" cy="9" r="2.2"/><path d="M3.5 20c.7-3.5 2.4-5.2 5.5-5.2s4.8 1.7 5.5 5.2M15 15c2.5.2 4 1.8 4.5 5"/></svg>
            <span>Family</span>
        </a>

        <a class="command-pill pill-yellow" href="/transport?month_cycle={{ $mc }}">
            <svg viewBox="0 0 24 24"><path d="M4 16V8c0-2 1.5-3 3.5-3h7c2.3 0 4.2 2 5.5 5v6M4 13h16"/><circle cx="7" cy="17.5" r="1"/><circle cx="17" cy="17.5" r="1"/></svg>
            <span>School Van</span>
        </a>
    </div>
    <!-- DASHBOARD_COMMAND_PILLS_END -->

    <!-- 1. CURRENT RUN / CURRENT PERIOD -->
    <div class="col-12 cb-run-card">
        <div class="cb-run-top">
            <div>
                <div class="cb-run-eyebrow">Current Billing Period</div>
                <div class="cb-run-period">{{ $hasPeriod ? $monthCycle : 'No month selected' }}</div>
                <p class="cb-run-hint">
                    @if($hasPeriod)
                        Set the period below, then work through readiness before running billing.
                    @else
                        Enter a month cycle (MM-YYYY) to load this period's readiness.
                    @endif
                </p>
            </div>
            <div class="cb-run-side">
                @if($hasPeriod)
                    <span class="cb-status-pill is-pending">Readiness not yet checked</span>
                @else
                    <span class="cb-status-pill is-blocked">No active period</span>
                @endif
                <div class="cb-run-actions">
                    <a class="cb-btn-ghost" href="/month-lifecycle?month_cycle={{ $mc }}">Open Month Cycle</a>
                    <a class="cb-btn-ghost" href="/billing-run-lock?month_cycle={{ $mc }}">Open Billing</a>
                </div>
            </div>
        </div>

        <form method="get" action="/dashboard" class="cb-run-form">
            <div class="field">
                <label class="label">Month Cycle</label>
                <input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY">
            </div>
            <button class="btn btn-primary" type="submit">Reload Dashboard</button>
            <div class="cb-run-quicklinks">
                <a class="cb-btn-ghost" href="/reporting?month_cycle={{ $mc }}">Reports</a>
                <a class="cb-btn-ghost" href="/reporting?month_cycle={{ $mc }}">Reconciliation</a>
                <a class="cb-btn-ghost" href="/imports-validation?month_cycle={{ $mc }}">Imports</a>
                <a class="cb-btn-ghost" href="/rates?month_cycle={{ $mc }}">Rates</a>
            </div>
        </form>
    </div>

    <!-- 2. READINESS TILES -->
    <div class="col-12 card">
        <div class="cb-card-head">
            <h3 class="section-title">Readiness</h3>
            <span class="cb-status-pill is-pending">Live status not wired</span>
        </div>
        <div class="cb-readiness-grid">
            <div class="cb-tile is-pending">
                <div class="cb-tile-head">
                    <span class="cb-tile-name">Readings</span>
                    <span class="cb-status-pill is-pending">Pending</span>
                </div>
                <p class="cb-tile-desc">Meter readings for this period. Open to review and import.</p>
                <div class="cb-tile-foot">
                    <a class="cb-tile-link" href="/meters-readings/readings?month_cycle={{ $mc }}">Open readings →</a>
                </div>
            </div>

            <div class="cb-tile is-pending">
                <div class="cb-tile-head">
                    <span class="cb-tile-name">Attendance</span>
                    <span class="cb-status-pill is-pending">Pending</span>
                </div>
                <p class="cb-tile-desc">Active days / attendance import for the month.</p>
                <div class="cb-tile-foot">
                    <a class="cb-tile-link" href="/active-days-monthly?month_cycle={{ $mc }}">Open attendance →</a>
                </div>
            </div>

            <div class="cb-tile is-pending">
                <div class="cb-tile-head">
                    <span class="cb-tile-name">Allowance</span>
                    <span class="cb-status-pill is-pending">Pending</span>
                </div>
                <p class="cb-tile-desc">House allowance inputs &amp; corrections.</p>
                <div class="cb-tile-foot">
                    <a class="cb-tile-link" href="/imports-validation?month_cycle={{ $mc }}">Open imports →</a>
                </div>
            </div>

            <div class="cb-tile is-pending">
                <div class="cb-tile-head">
                    <span class="cb-tile-name">Rates</span>
                    <span class="cb-status-pill is-pending">Pending</span>
                </div>
                <p class="cb-tile-desc">Approved rate set must be in place before the run.</p>
                <div class="cb-tile-foot">
                    <a class="cb-tile-link" href="/rates?month_cycle={{ $mc }}">Open rates →</a>
                </div>
            </div>

            <div class="cb-tile is-pending">
                <div class="cb-tile-head">
                    <span class="cb-tile-name">Integrity</span>
                    <span class="cb-status-pill is-pending">Pending</span>
                </div>
                <p class="cb-tile-desc">Reconciliation &amp; integrity checks before locking.</p>
                <div class="cb-tile-foot">
                    <a class="cb-tile-link" href="/reporting?month_cycle={{ $mc }}">Open reconciliation →</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. MISSING DATA / BLOCKERS  +  4. NEXT ACTION -->
    <div class="col-7 card">
        <div class="cb-card-head">
            <h3 class="section-title">Missing Data &amp; Blockers</h3>
        </div>
        <div class="empty" style="margin-bottom:12px">
            No automated blocker feed is connected to this view yet — nothing is being flagged automatically.
            Use the manual pre-run checks below until live checks are wired in.
        </div>
        <div class="muted" style="margin-bottom:8px;font-weight:800">Manual pre-run checks</div>
        <ul class="cb-list">
            <li class="cb-list-row"><span class="cb-dot"></span>Readings imported and validated for this period.</li>
            <li class="cb-list-row"><span class="cb-dot"></span>Attendance / active days uploaded and reviewed.</li>
            <li class="cb-list-row"><span class="cb-dot"></span>House allowance inputs and corrections confirmed.</li>
            <li class="cb-list-row"><span class="cb-dot"></span>Rates approved before the billing run.</li>
            <li class="cb-list-row"><span class="cb-dot"></span>Reconciliation reviewed before lock.</li>
        </ul>
    </div>

    <div class="col-5 card soft">
        <div class="cb-card-head">
            <h3 class="section-title">Next Action</h3>
        </div>
        <div class="cb-next">
            @if(!$hasPeriod)
                <p class="cb-next-step">Select a billing month</p>
                <p class="cb-next-why">No active period is set. Enter a month cycle above and reload to begin.</p>
                <div class="split">
                    <a class="btn btn-primary" href="/month-lifecycle">Open Month Lifecycle</a>
                </div>
            @else
                <p class="cb-next-step">Review month inputs &amp; preview</p>
                <p class="cb-next-why">Confirm readings, attendance, allowance and rates for {{ $monthCycle }}, then preview before running billing.</p>
                <div class="split">
                    <a class="btn btn-primary" href="/month-lifecycle?month_cycle={{ $mc }}">Open Month Lifecycle</a>
                    <a class="btn" href="/imports-validation?month_cycle={{ $mc }}">Imports</a>
                    <a class="btn" href="/billing-run-lock?month_cycle={{ $mc }}">Billing Run</a>
                </div>
            @endif
        </div>
    </div>

    <!-- 5. SNAPSHOT (real KPI data) -->
    <div class="col-3 card">
        <div class="muted">Employees Billed</div>
        <div class="kpi">{{ $kpis['employees_billed'] ?? 0 }}</div>
        <span class="badge success">Billing Coverage</span>
    </div>
    <div class="col-3 card">
        <div class="muted">Total Billed</div>
        <div class="kpi">PKR {{ number_format((float)($kpis['total_billed'] ?? 0), 2) }}</div>
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

    <div class="col-12 card">
        <h3 class="section-title">Resident Type Overview</h3>
        <div class="grid" style="gap:10px">
            <a class="col-2 card soft kpi-link-card" href="/unit-directory">
                <div class="muted">Total Rooms</div>
                <div class="kpi">{{ $kpis['total_units'] ?? 0 }}</div>
                <span class="badge">Master</span>
            </a>
            <a class="col-2 card soft kpi-link-card" href="/unit-directory?res_type=house">
                <div class="muted">House Units</div>
                <div class="kpi">{{ $kpis['house_units'] ?? 0 }}</div>
                <span class="badge success">House</span>
            </a>
            <a class="col-2 card soft kpi-link-card" href="/unit-directory?res_type=bachelor">
                <div class="muted">Bachelor Units</div>
                <div class="kpi">{{ $kpis['bachelor_units'] ?? 0 }}</div>
                <span class="badge">Bachelor</span>
            </a>
            <a class="col-2 card soft kpi-link-card" href="/unit-directory?res_type=hostel">
                <div class="muted">Hostel</div>
                <div class="kpi">{{ $kpis['hostel_units'] ?? 0 }}</div>
                <span class="badge">Hostel</span>
            </a>
            <a class="col-2 card soft kpi-link-card" href="/unit-directory?res_type=containers">
                <div class="muted">Admin Colonies</div>
                <div class="kpi">{{ $kpis['container_units'] ?? 0 }}</div>
                <span class="badge warn">Admin Colonies</span>
            </a>
            <a class="col-2 card soft kpi-link-card" href="/unit-directory?res_type=uncategorized">
                <div class="muted">Uncategorized</div>
                <div class="kpi">{{ $kpis['uncategorized_units'] ?? 0 }}</div>
                <span class="badge warn">Review</span>
            </a>
        </div>
    </div>

    <!-- FAMILY & VAN OVERVIEW (real backend data: $familyRows / $vanRows) -->
    <div class="col-6 card">
        <div class="cb-card-head">
            <h3 class="section-title">Family Members</h3>
            <span class="badge">{{ count($familyRows ?? []) }} rows</span>
        </div>
        @if(!empty($familyRows))
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Employee</th><th>Member</th><th>Relation</th><th>Age</th></tr></thead>
                    <tbody>
                    @foreach($familyRows as $row)
                        <tr>
                            <td>{{ $row->employee_id ?? '' }}</td>
                            <td>{{ $row->family_member_name ?? '' }}</td>
                            <td>{{ $row->relation ?? '' }}</td>
                            <td>{{ $row->age ?? '' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty">No family member records for this period.</div>
        @endif
    </div>

    <div class="col-6 card">
        <div class="cb-card-head">
            <h3 class="section-title">School Van Kids</h3>
            <span class="badge warn">{{ count($vanRows ?? []) }} rows</span>
        </div>
        @if(!empty($vanRows))
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Employee</th><th>Child</th><th>School</th><th>Class</th><th>Amount</th></tr></thead>
                    <tbody>
                    @foreach($vanRows as $row)
                        <tr>
                            <td>{{ $row->employee_id ?? '' }}</td>
                            <td>{{ $row->child_name ?? '' }}</td>
                            <td>{{ $row->school_name ?? '' }}</td>
                            <td>{{ $row->class_level ?? '' }}</td>
                            <td>PKR {{ number_format((float)($row->amount ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty">No school van records for this period.</div>
        @endif
    </div>

    <!-- 6. LAST RUN / RECENT RUNS -->
    <div class="col-12 card">
        <div class="cb-card-head">
            <h3 class="section-title">Last Run &amp; Recent Runs</h3>
            <a class="cb-tile-link" href="/billing-run-lock?month_cycle={{ $mc }}">Open Billing Run &amp; Lock →</a>
        </div>
        <div class="empty">
            No run history is connected to this view yet. Previous runs and reprint actions appear here once a run feed is wired in.
        </div>
    </div>
</div>
<style>

/* Dashboard-only header replacement: hide default page-head and use fixed commands as the first rail. */
.main .container > .page-head{
    display:none;
}
.dashboard-command-console{
    margin-top:0;
    padding-top:48px;
}

.kpi-link-card{
    display:block;
    text-decoration:none;
    color:inherit;
    cursor:pointer;
    transition:transform .15s ease, box-shadow .15s ease;
}
.kpi-link-card:hover{
    transform:translateY(-2px);
    box-shadow:0 18px 36px rgba(15,23,42,.12);
}

/* DASHBOARD_COMPACT_CURRENT_PERIOD_START */
.cb-run-card{
    padding:12px 14px !important;
    border-radius:16px !important;
}
.cb-run-top{
    display:grid !important;
    grid-template-columns:minmax(0,1fr) auto;
    align-items:center !important;
    gap:12px !important;
    margin-bottom:8px !important;
}
.cb-run-eyebrow{
    font-size:10px !important;
    line-height:1 !important;
    letter-spacing:.14em !important;
    margin-bottom:4px !important;
}
.cb-run-period{
    font-size:24px !important;
    line-height:1.05 !important;
    margin:0 !important;
}
.cb-run-hint{
    margin:3px 0 0 !important;
    font-size:12px !important;
    line-height:1.35 !important;
    max-width:720px;
}
.cb-run-side{
    display:flex !important;
    flex-direction:column !important;
    align-items:flex-end !important;
    gap:8px !important;
}
.cb-run-actions,
.cb-run-quicklinks{
    display:flex !important;
    align-items:center !important;
    justify-content:flex-end !important;
    gap:7px !important;
    flex-wrap:wrap !important;
}
.cb-run-form{
    display:flex !important;
    align-items:flex-end !important;
    gap:9px !important;
    flex-wrap:wrap !important;
    margin-top:8px !important;
    padding-top:8px !important;
    border-top:1px solid rgba(148,163,184,.18) !important;
}
.cb-run-form .field{
    min-width:158px !important;
    max-width:190px !important;
}
.cb-run-form .label{
    font-size:10px !important;
    line-height:1 !important;
    margin-bottom:4px !important;
}
.cb-run-form input[name="month_cycle"]{
    min-height:34px !important;
    padding:6px 10px !important;
    font-weight:800 !important;
}
.cb-run-form .btn,
.cb-btn-ghost{
    min-height:34px !important;
    padding:7px 11px !important;
    font-size:12px !important;
    line-height:1 !important;
}
.cb-run-quicklinks{
    margin-left:auto !important;
}
@media(max-width:900px){
    .cb-run-top{
        grid-template-columns:1fr;
    }
    .cb-run-side,
    .cb-run-actions,
    .cb-run-quicklinks{
        align-items:flex-start !important;
        justify-content:flex-start !important;
        margin-left:0 !important;
    }
    .cb-run-form .field{
        max-width:none !important;
        width:100% !important;
    }
}
/* DASHBOARD_COMPACT_CURRENT_PERIOD_END */

/* DASHBOARD_EXECUTIVE_COMMAND_BUTTONS_START */
.command-row{
    grid-column:span 12;
    position:fixed;
    top:128px;
    left:50%;
    width:min(calc(100vw - 56px), 1424px);
    z-index:99;
    display:grid;
    grid-template-columns:84px repeat(7,minmax(0,1fr));
    align-items:center;
    gap:9px;
    min-height:64px;
    margin:0;
    padding:9px 11px;
    border:1px solid rgba(148,163,184,.28);
    border-radius:16px;
    background:rgba(248,250,252,.96);
    box-shadow:0 16px 38px rgba(15,23,42,.12);
    backdrop-filter:blur(14px);
    transform:translateX(-50%);
}
.command-row-label{
    height:46px;
    display:flex;
    align-items:center;
    padding:0 2px;
    color:#64748b;
    font-size:10px;
    font-weight:800;
    letter-spacing:.10em;
    text-transform:uppercase;
}
.command-pill{
    --surface:#edf4ff;
    --border:#bfd6fb;
    --depth:#a8c5ee;
    --ink:#1d4ed8;
    position:relative;
    width:100%;
    height:47px;
    padding:0 11px;
    display:flex;
    align-items:center;
    justify-content:flex-start;
    gap:8px;
    border:1px solid var(--border);
    border-radius:9px;
    background:var(--surface);
    color:var(--ink);
    font:inherit;
    font-size:13px;
    line-height:1;
    font-weight:750;
    white-space:nowrap;
    text-align:left;
    cursor:pointer;
    text-decoration:none;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.75),
        0 2px 0 var(--depth),
        0 5px 9px rgba(15,23,42,.06);
    transition:box-shadow .14s ease;
}
.command-pill::before,
.command-pill::after{
    display:none;
}
.command-pill:hover{
    text-decoration:none;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.82),
        0 3px 0 var(--depth),
        0 7px 13px rgba(15,23,42,.09);
}
.command-pill:focus-visible{
    outline:3px solid rgba(59,130,246,.22);
    outline-offset:2px;
}
.command-pill:active{
    box-shadow:
        inset 0 1px 3px rgba(15,23,42,.10),
        0 1px 0 var(--depth);
}
.command-pill svg{
    width:22px;
    height:22px;
    flex:0 0 22px;
    padding:0;
    border-radius:0;
    background:none;
    box-shadow:none;
    fill:none;
    stroke:currentColor;
    stroke-width:2;
    stroke-linecap:round;
    stroke-linejoin:round;
}
.command-pill > span:last-child{
    min-width:0;
    display:block;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    font-size:13px;
    line-height:1;
    font-weight:750;
    letter-spacing:0;
}
.pill-blue{--surface:#e9f2ff;--border:#bad2fb;--depth:#9dbbea;--ink:#1d4ed8;}
.pill-purple{--surface:#f2ecff;--border:#d4c4fa;--depth:#b7a1e0;--ink:#6d28d9;}
.pill-green{--surface:#e3f6eb;--border:#a8dcc0;--depth:#83bf9d;--ink:#047857;}
.pill-orange{--surface:#fff0e3;--border:#facaa6;--depth:#dfaa7d;--ink:#c2410c;}
.pill-cyan{--surface:#e4f5fb;--border:#acddec;--depth:#83bfd6;--ink:#0369a1;}
.pill-pink{--surface:#fbe8f2;--border:#eeb8d2;--depth:#d99ab9;--ink:#be185d;}
.pill-yellow{--surface:#fff5d4;--border:#ecd47a;--depth:#cfb550;--ink:#a16207;}
.command-pill.primary{
    height:47px;
    background:#07966c;
    border-color:#067b59;
    color:#fff;
    text-shadow:none;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.18),
        0 2px 0 #056448,
        0 6px 11px rgba(5,150,105,.14);
}
.command-pill.primary svg{
    stroke:#fff;
}
@media(max-width:1250px){
    .dashboard-command-console{
        padding-top:150px;
    }
    .command-row{
        grid-template-columns:repeat(4,minmax(0,1fr));
        top:116px;
    }
    .command-row-label{
        grid-column:1 / -1;
        height:22px;
    }
}
@media(max-width:720px){
    .dashboard-command-console{
        padding-top:0;
    }
    .command-row{
        position:static;
        width:auto;
        transform:none;
        grid-template-columns:1fr;
        max-height:none;
        overflow:visible;
        margin:0 0 14px;
    }
}
/* DASHBOARD_EXECUTIVE_COMMAND_BUTTONS_END */

</style>
@endsection