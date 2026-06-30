@extends('layouts.app')
@section('page_title','Billing Command <span class="premium-icon-tile premium-dashboard-icon premium-icon-blue premium-inline-title"><svg viewBox="0 0 24 24"><path d="M4 13h4l2-6 4 12 2-6h4"></path><path d="M4 20h16"></path></svg></span>Dashboard')
@section('page_subtitle','Enterprise operational control center for month-cycle billing, transport data, reports and reconciliation health.')
@section('content')
<div class="grid">
    <!-- DASHBOARD_COMMAND_PILLS_START -->
    <div class="col-12 command-row" id="dashboardCommandRow">
        <div class="command-row-label">Commands</div>

        <button class="command-pill pill-blue" type="button">
            <svg viewBox="0 0 24 24"><path d="M5 21V4h14v17M9 8h2m2 0h2M9 12h2m2 0h2M10 21v-5h4v5"/></svg>
            <span class="premium-icon-tile premium-action-icon premium-icon-blue premium-inline-title"><svg viewBox="0 0 24 24"><rect x="6" y="3" width="12" height="18" rx="2"></rect><path d="M10 7h.01M14 7h.01M10 11h.01M14 11h.01M10 15h.01M14 15h.01"></path></svg></span><span>Unit Directory</span>
        </button>

        <button class="command-pill pill-purple" type="button">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c1-3.6 3.2-5.3 7-5.3s6 1.7 7 5.3"/></svg>
            <span class="premium-icon-tile premium-action-icon premium-icon-green premium-inline-title"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"></circle><path d="M5 20c0-4 2.8-6.5 7-6.5s7 2.5 7 6.5"></path></svg></span><span>Employee Profile</span>
        </button>

        <button class="command-pill pill-green primary" type="button">
            <svg viewBox="0 0 24 24"><circle cx="8" cy="8" r="3"/><path d="M3.5 19c.7-3 2.3-4.5 4.5-4.5M18 11v10m-5-5h10"/></svg>
            <span class="premium-icon-tile premium-action-icon premium-icon-purple premium-inline-title"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 20c0-4 2.4-6 6-6"></path><path d="M17 10v8M13 14h8"></path></svg></span><span>Add Employee</span>
        </button>

        <button class="command-pill pill-orange" type="button">
            <svg viewBox="0 0 24 24"><path d="M6 3h9l3 3v15H6zM14 3v4h4M9 12h6m-6 4h6"/></svg>
            <span class="premium-icon-tile premium-action-icon premium-icon-orange premium-inline-title"><svg viewBox="0 0 24 24"><path d="M7 3h8l4 4v14H7z"></path><path d="M15 3v5h4M10 12h6M10 16h4"></path></svg></span><span>Statement</span>
        </button>

        <button class="command-pill pill-cyan" type="button">
            <svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5M5.5 10.5V20h13v-9.5M10 20v-5h4v5"/></svg>
            <span class="premium-icon-tile premium-action-icon premium-icon-teal premium-inline-title"><svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5V21h-6v-6H9v6H3v-9.5Z"></path></svg></span><span>Residence</span>
        </button>

        <button class="command-pill pill-pink" type="button">
            <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><circle cx="16.5" cy="9" r="2.2"/><path d="M3.5 20c.7-3.5 2.4-5.2 5.5-5.2s4.8 1.7 5.5 5.2M15 15c2.5.2 4 1.8 4.5 5"/></svg>
            <span class="premium-icon-tile premium-action-icon premium-icon-pink premium-inline-title"><svg viewBox="0 0 24 24"><circle cx="8" cy="8" r="3"></circle><circle cx="16" cy="9" r="2.5"></circle><path d="M3 20c0-3.6 2.2-5.6 5.4-5.6M13 19c0-2.5 1.7-4.1 4.1-4.1"></path></svg></span><span>Family</span>
        </button>

        <button class="command-pill pill-yellow" type="button">
            <svg viewBox="0 0 24 24"><path d="M4 16V8c0-2 1.5-3 3.5-3h7c2.3 0 4.2 2 5.5 5v6M4 13h16"/><circle cx="7" cy="17.5" r="1"/><circle cx="17" cy="17.5" r="1"/></svg>
            <span>School Van</span>
        </button>
    </div>
    <!-- DASHBOARD_COMMAND_PILLS_END -->

    <div class="col-3 card">
        <div class="muted premium-inline-title"><span class="premium-icon-tile premium-kpi-icon premium-icon-blue"><svg viewBox="0 0 24 24"><circle cx="8" cy="8" r="3"></circle><circle cx="16" cy="9" r="2.5"></circle><path d="M3 20c0-3.6 2.2-5.6 5.4-5.6M13 19c0-2.5 1.7-4.1 4.1-4.1"></path></svg></span>Employees Billed</div>
        <div class="kpi">{{ $kpis['employees_billed'] ?? 0 }}</div>
        <span class="badge success">Billing Coverage</span>
    </div>
    <div class="col-3 card">
        <div class="muted premium-inline-title"><span class="premium-icon-tile premium-kpi-icon premium-icon-green"><svg viewBox="0 0 24 24"><path d="M15 5H8l2 5h5a3 3 0 0 1 0 6H8"></path><path d="M8 10h9"></path></svg></span>Total Billed</div>
        <div class="kpi">PKR {{ number_format((float)($kpis['total_billed'] ?? 0), 2) }}</div>
        <span class="badge">Financial</span>
    </div>
    <div class="col-3 card">
        <div class="muted premium-inline-title"><span class="premium-icon-tile premium-kpi-icon premium-icon-purple"><svg viewBox="0 0 24 24"><circle cx="8" cy="8" r="3"></circle><circle cx="16" cy="9" r="2.5"></circle><path d="M3 20c0-3.6 2.2-5.6 5.4-5.6M13 19c0-2.5 1.7-4.1 4.1-4.1"></path></svg></span>Family Members</div>
        <div class="kpi">{{ $kpis['family_members'] ?? 0 }}</div>
        <span class="badge">Registry</span>
    </div>
    <div class="col-3 card">
        <div class="muted premium-inline-title"><span class="premium-icon-tile premium-kpi-icon premium-icon-orange"><svg viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="9" rx="2.5"></rect><path d="M6 7l1-3h10l1 3"></path><circle cx="8" cy="18" r="1.7"></circle><circle cx="16" cy="18" r="1.7"></circle></svg></span>Van Kids</div>
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

/* DASHBOARD_EXECUTIVE_COMMAND_BUTTONS_START */
.command-row{
    grid-column:span 12;
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



.premium-icon-tile{
  display:grid;
  place-items:center;
  flex-shrink:0;
  color:#fff;
  box-shadow:
    0 6px 14px rgba(15,27,51,.14),
    inset 0 1px 0 rgba(255,255,255,.22);
  position:relative;
  overflow:hidden;
}
.premium-icon-tile::after{
  content:"";
  position:absolute;
  top:-35%;
  right:-25%;
  width:70%;
  height:70%;
  border-radius:50%;
  background:rgba(255,255,255,.20);
}
.premium-icon-tile svg{
  position:relative;
  z-index:2;
  stroke-width:2.3;
  stroke-linecap:round;
  stroke-linejoin:round;
  fill:none;
}
.premium-icon-blue{background:linear-gradient(135deg,#3B82F6,#1E40AF);}
.premium-icon-green{background:linear-gradient(135deg,#22C55E,#15803D);}
.premium-icon-purple{background:linear-gradient(135deg,#A855F7,#7C3AED);}
.premium-icon-orange{background:linear-gradient(135deg,#FB923C,#EA580C);}
.premium-icon-teal{background:linear-gradient(135deg,#2DD4BF,#0D9488);}
.premium-icon-amber{background:linear-gradient(135deg,#FBBF24,#D97706);}
.premium-icon-pink{background:linear-gradient(135deg,#F472B6,#DB2777);}
.premium-icon-red{background:linear-gradient(135deg,#FB7185,#DC2626);}

.premium-dashboard-icon{
  width:38px;
  height:38px;
  border-radius:12px;
  margin-right:10px;
}
.premium-dashboard-icon svg{
  width:21px;
  height:21px;
}
.premium-kpi-icon{
  width:42px;
  height:42px;
  border-radius:14px;
  margin-bottom:8px;
}
.premium-kpi-icon svg{
  width:22px;
  height:22px;
}
.premium-action-icon{
  width:36px;
  height:36px;
  border-radius:12px;
  margin-bottom:7px;
}
.premium-action-icon svg{
  width:18px;
  height:18px;
}
.premium-inline-title{
  display:flex;
  align-items:center;
  min-width:0;
}


</style>
@endsection
