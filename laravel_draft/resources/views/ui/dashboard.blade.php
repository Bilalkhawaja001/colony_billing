@extends('layouts.app')
@section('page_title','Billing Command Dashboard')
@section('page_subtitle','Enterprise operational control center for month-cycle billing, transport data, reports and reconciliation health.')
@section('content')
<div class="grid">
    <!-- DASHBOARD_COMMAND_PILLS_START -->
    <div class="col-12 command-row" id="dashboardCommandRow">
        <div class="command-row-label">Commands</div>

        <button class="command-pill pill-blue" type="button">
            <svg viewBox="0 0 24 24"><path d="M5 21V4h14v17M9 8h2m2 0h2M9 12h2m2 0h2M10 21v-5h4v5"/></svg>
            <span>Unit Directory</span>
        </button>

        <button class="command-pill pill-purple" type="button">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c1-3.6 3.2-5.3 7-5.3s6 1.7 7 5.3"/></svg>
            <span>Employee Profile</span>
        </button>

        <button class="command-pill pill-green primary" type="button">
            <svg viewBox="0 0 24 24"><circle cx="8" cy="8" r="3"/><path d="M3.5 19c.7-3 2.3-4.5 4.5-4.5M18 11v10m-5-5h10"/></svg>
            <span>Add Employee</span>
        </button>

        <button class="command-pill pill-orange" type="button">
            <svg viewBox="0 0 24 24"><path d="M6 3h9l3 3v15H6zM14 3v4h4M9 12h6m-6 4h6"/></svg>
            <span>Statement</span>
        </button>

        <button class="command-pill pill-cyan" type="button">
            <svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5M5.5 10.5V20h13v-9.5M10 20v-5h4v5"/></svg>
            <span>Residence</span>
        </button>

        <button class="command-pill pill-pink" type="button">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><circle cx="16.5" cy="9" r="2.2"/><path d="M3.5 20c.7-3.5 2.4-5.2 5.5-5.2s4.8 1.7 5.5 5.2M15 15c2.5.2 4 1.8 4.5 5"/></svg>
            <span>Family</span>
        </button>

        <button class="command-pill pill-yellow" type="button">
            <svg viewBox="0 0 24 24"><path d="M4 16V8c0-2 1.5-3 3.5-3h7c2.3 0 4.2 2 5.5 5v6M4 13h16"/><circle cx="7" cy="17.5" r="1"/><circle cx="17" cy="17.5" r="1"/></svg>
            <span>School Van</span>
        </button>
    </div>
    <!-- DASHBOARD_COMMAND_PILLS_END -->

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
<style>
.command-row{
    grid-column:span 12;
    display:flex;
    align-items:center;
    gap:8px;
    min-height:50px;
    margin-bottom:2px;
}
.command-row-label{
    height:40px;
    display:flex;
    align-items:center;
    padding:0 11px 0 2px;
    color:#64748b;
    font-size:10px;
    font-weight:800;
    letter-spacing:.1em;
    text-transform:uppercase;
}
.command-pill{
    --pill-bg:#eaf3ff;
    --pill-border:#bfd8ff;
    --pill-text:#1754ac;
    height:40px;
    min-width:0;
    padding:0 12px 0 10px;
    display:inline-flex;
    align-items:center;
    gap:7px;
    border:1px solid var(--pill-border);
    border-radius:999px;
    background:var(--pill-bg);
    color:var(--pill-text);
    font:inherit;
    font-size:11.5px;
    font-weight:750;
    white-space:nowrap;
    cursor:default;
    transition:transform .14s ease, box-shadow .14s ease;
}
.command-pill svg{
    width:17px;
    height:17px;
    flex:0 0 17px;
    fill:none;
    stroke:currentColor;
    stroke-width:1.9;
    stroke-linecap:round;
    stroke-linejoin:round;
}
.command-pill:hover{
    transform:translateY(-1px);
    box-shadow:0 6px 14px rgba(15,23,42,.08);
}
.pill-blue{--pill-bg:#eaf3ff;--pill-border:#bfd8ff;--pill-text:#1d4ed8;}
.pill-purple{--pill-bg:#f2edff;--pill-border:#d6c7ff;--pill-text:#6d28d9;}
.pill-green{--pill-bg:#e5f8ee;--pill-border:#aee5c6;--pill-text:#047857;}
.pill-orange{--pill-bg:#fff0e4;--pill-border:#ffceab;--pill-text:#c2410c;}
.pill-cyan{--pill-bg:#e5f7fc;--pill-border:#b1e2f2;--pill-text:#0369a1;}
.pill-pink{--pill-bg:#fceaf3;--pill-border:#f3bed8;--pill-text:#be185d;}
.pill-yellow{--pill-bg:#fff7d8;--pill-border:#efd986;--pill-text:#a16207;}
.command-pill.primary{
    height:42px;
    padding-left:12px;
    background:#059669;
    border-color:#059669;
    color:#fff;
    box-shadow:0 7px 15px rgba(5,150,105,.18);
}
@media(max-width:1250px){
    .command-row{flex-wrap:wrap;}
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

/* DASHBOARD_COMMAND_PILLS_3D_START */
.command-pill{
    --pill-depth:#a8c9ef;
    position:relative;
    overflow:hidden;
    height:42px;
    padding:0 13px 0 11px;
    border-radius:12px;
    border-bottom-width:2px;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.90),
        0 3px 0 var(--pill-depth),
        0 7px 12px rgba(15,23,42,.10);
    transform:translateY(-1px);
}
.command-pill::before{
    content:'';
    position:absolute;
    left:1px;
    right:1px;
    top:1px;
    height:44%;
    border-radius:11px 11px 7px 7px;
    background:linear-gradient(180deg,rgba(255,255,255,.62),rgba(255,255,255,0));
    pointer-events:none;
}
.command-pill svg,
.command-pill span{
    position:relative;
    z-index:1;
}
.command-pill:hover{
    transform:translateY(-3px);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.95),
        0 5px 0 var(--pill-depth),
        0 12px 17px rgba(15,23,42,.14);
}
.command-pill:active{
    transform:translateY(2px);
    box-shadow:
        inset 0 2px 4px rgba(15,23,42,.12),
        0 1px 0 var(--pill-depth),
        0 3px 7px rgba(15,23,42,.10);
}
.pill-blue{
    --pill-depth:#9ebfe8;
    background:linear-gradient(180deg,#f7fbff 0%,#dcebff 100%);
}
.pill-purple{
    --pill-depth:#bba7e7;
    background:linear-gradient(180deg,#fcfaff 0%,#e8deff 100%);
}
.pill-green{
    --pill-depth:#8bc5a5;
    background:linear-gradient(180deg,#f1fff8 0%,#d7f3e4 100%);
}
.pill-orange{
    --pill-depth:#e5af87;
    background:linear-gradient(180deg,#fffaf5 0%,#ffe5d0 100%);
}
.pill-cyan{
    --pill-depth:#91c9dd;
    background:linear-gradient(180deg,#f3fcff 0%,#d8f0f9 100%);
}
.pill-pink{
    --pill-depth:#dda8c2;
    background:linear-gradient(180deg,#fff8fc 0%,#f7ddea 100%);
}
.pill-yellow{
    --pill-depth:#ddc36a;
    background:linear-gradient(180deg,#fffdf3 0%,#ffefbb 100%);
}
.command-pill.primary{
    --pill-depth:#04704f;
    height:43px;
    background:linear-gradient(180deg,#1bb985 0%,#059669 100%);
    border-color:#059669;
    color:#fff;
    text-shadow:0 1px 1px rgba(0,0,0,.16);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.30),
        0 3px 0 var(--pill-depth),
        0 9px 14px rgba(5,150,105,.22);
}
/* DASHBOARD_COMMAND_PILLS_3D_END */


/* DASHBOARD_EXECUTIVE_COMMAND_BUTTONS_START */
.command-row{
    display:grid;
    grid-template-columns:84px repeat(7,minmax(0,1fr));
    align-items:center;
    gap:9px;
    min-height:52px;
    margin:0 0 10px;
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
    cursor:default;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.75),
        0 2px 0 var(--depth),
        0 5px 9px rgba(15,23,42,.06);
    transform:translateY(-1px);
    transition:transform .14s ease, box-shadow .14s ease;
}
.command-pill::before,
.command-pill::after{
    display:none;
}
.command-pill:hover{
    transform:translateY(-2px);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.82),
        0 3px 0 var(--depth),
        0 7px 13px rgba(15,23,42,.09);
}
.command-pill:active{
    transform:translateY(1px);
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
    .command-row{
        grid-template-columns:repeat(4,minmax(0,1fr));
    }
    .command-row-label{
        grid-column:1 / -1;
        height:22px;
    }
}
/* DASHBOARD_EXECUTIVE_COMMAND_BUTTONS_END */

</style>
@endsection
