<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page_title', 'Colony Billing Admin')</title>
    <style>
        :root{
            --shell:#07111f;
            --shell2:#0b1b31;
            --panel:#ffffff;
            --panel2:#f8fafc;
            --line:#d8e0ec;
            --line2:#e7edf5;
            --text:#142033;
            --heading:#0f172a;
            --muted:#64748b;
            --brand:#1d4ed8;
            --brand2:#0ea5e9;
            --success:#0f766e;
            --warn:#b45309;
            --danger:#b91c1c;
            --shadow:0 18px 42px rgba(15,23,42,.10);
        }
        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{
            margin:0;
            background:
                radial-gradient(circle at top left,rgba(37,99,235,.16),transparent 32%),
                linear-gradient(135deg,#eef4fb 0%,#f8fafc 48%,#edf4ff 100%);
            color:var(--text);
            font-family:Inter,Segoe UI,Arial,sans-serif;
            -webkit-font-smoothing:antialiased;
        }
        .app{display:flex;min-height:100vh}
        .sidebar{
            width:286px;
            background:linear-gradient(180deg,#081426 0%,#0b1729 52%,#07111f 100%);
            border-right:1px solid rgba(148,163,184,.18);
            padding:20px 16px;
            position:sticky;
            top:0;
            height:100vh;
            overflow:auto;
            box-shadow:14px 0 34px rgba(2,8,23,.22);
        }
        .brand{
            font-weight:900;
            font-size:18px;
            letter-spacing:.2px;
            padding:12px 12px 6px;
            color:#fff;
        }
        .sub{
            font-size:11px;
            color:#94a3b8;
            padding:0 12px 16px;
            border-bottom:1px solid rgba(148,163,184,.18);
            letter-spacing:.12em;
            text-transform:uppercase;
        }
        .nav-section{margin-top:14px}
        .nav-head{
            font-size:11px;
            color:#718096;
            text-transform:uppercase;
            letter-spacing:.12em;
            padding:8px 10px;
            font-weight:900;
        }
        .nav a{
            display:flex;
            align-items:center;
            color:#cbd7e6;
            text-decoration:none;
            padding:10px 12px;
            border-radius:13px;
            margin:5px 0;
            font-size:13px;
            font-weight:750;
            border:1px solid transparent;
            background:transparent;
            transition:.16s ease;
        }
        .nav a:hover{
            background:rgba(37,99,235,.14);
            color:#fff;
            border-color:rgba(147,197,253,.18);
        }
        .nav a.active{
            background:linear-gradient(135deg,rgba(37,99,235,.98),rgba(14,165,233,.74));
            color:#fff;
            border-color:rgba(191,219,254,.35);
            box-shadow:0 12px 24px rgba(37,99,235,.28);
        }
        .nav-ico{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:26px;
            height:26px;
            margin-right:10px;
            border-radius:9px;
            background:rgba(255,255,255,.08);
            border:1px solid rgba(255,255,255,.10);
            color:#dbeafe;
            font-size:11px;
            font-weight:900;
            flex:none;
        }
        .main{flex:1;display:flex;flex-direction:column;min-width:0}
        .top{
            min-height:68px;
            background:rgba(255,255,255,.88);
            backdrop-filter:blur(14px);
            border-bottom:1px solid var(--line);
            padding:14px 26px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            position:sticky;
            top:0;
            z-index:20;
        }
        .top-title{font-weight:900;font-size:16px;color:#0f172a;letter-spacing:-.01em}
        .user{
            font-size:12px;
            color:#475569;
            background:#f1f5f9;
            border:1px solid #dbe4ef;
            border-radius:999px;
            padding:8px 12px;
            font-weight:800;
        }
        .container{width:100%;max-width:1440px;padding:26px;position:relative;z-index:1}
        .page-head{
            display:flex;
            justify-content:space-between;
            align-items:flex-end;
            gap:12px;
            margin-bottom:18px;
            padding:18px 20px;
            border:1px solid var(--line2);
            border-radius:20px;
            background:rgba(255,255,255,.94);
            box-shadow:var(--shadow);
        }
        .page-title{margin:0;font-size:24px;line-height:1.2;color:#0f172a;letter-spacing:-.03em}
        .page-sub{margin:5px 0 0;color:var(--muted);font-size:13px}
        .crumb{font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.12em;font-weight:900}
        .grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:16px}
        .col-3{grid-column:span 3}.col-4{grid-column:span 4}.col-5{grid-column:span 5}.col-6{grid-column:span 6}.col-7{grid-column:span 7}.col-8{grid-column:span 8}.col-12{grid-column:span 12}
        .card{
            background:rgba(255,255,255,.96);
            border:1px solid var(--line2);
            border-radius:18px;
            padding:18px;
            box-shadow:var(--shadow);
            color:var(--text);
        }
        .card.soft{background:#f8fbff;box-shadow:0 10px 28px rgba(15,23,42,.06)}
        .section-title{margin:0 0 10px;font-size:16px;color:#0f172a;letter-spacing:-.02em}
        .kpi{font-size:30px;font-weight:900;margin:8px 0 4px;color:#0f172a;letter-spacing:-.03em}
        .muted{color:var(--muted);font-size:12px;line-height:1.55}
        .badge{
            display:inline-flex;
            align-items:center;
            padding:4px 9px;
            border-radius:999px;
            font-size:11px;
            border:1px solid #bfdbfe;
            background:#eff6ff;
            color:#1e40af;
            font-weight:900;
        }
        .badge.success{border-color:#99f6e4;background:#ecfdf5;color:#0f766e}
        .badge.warn{border-color:#fde68a;background:#fffbeb;color:#92400e}
        .banner{
            padding:12px 14px;
            border-radius:14px;
            background:#eaf3ff;
            border:1px solid #c7dcff;
            color:#1e3a8a;
            font-size:13px;
            margin-bottom:12px;
            font-weight:800;
        }
        .alert{padding:10px 12px;border:1px solid #fecdd3;background:#fff1f2;color:#9f1239;border-radius:12px;font-size:13px}
        .toolbar,.split{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
        .module-intro{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:14px;
            margin-bottom:16px;
            padding:16px 18px;
            border:1px solid var(--line2);
            border-radius:18px;
            background:#f8fbff;
        }
        .module-intro h3,.module-intro h4{margin:0 0 6px;color:#0f172a;letter-spacing:-.02em}
        .module-intro p{margin:0;color:var(--muted);font-size:13px;line-height:1.55}
        .segmented{
            display:inline-flex;
            gap:6px;
            flex-wrap:wrap;
            padding:5px;
            border:1px solid var(--line2);
            border-radius:15px;
            background:#f8fafc;
        }
        .small{font-size:12px}
        code{
            display:inline-block;
            max-width:100%;
            padding:4px 7px;
            border-radius:8px;
            background:#eef4ff;
            color:#1e3a8a;
            border:1px solid #dbeafe;
            overflow:auto;
        }
        .sticky-actions{
            position:sticky;
            top:82px;
            z-index:10;
            background:rgba(255,255,255,.92);
            backdrop-filter:blur(12px);
            border:1px solid var(--line2);
            border-radius:16px;
            padding:10px;
            box-shadow:0 14px 30px rgba(15,23,42,.08);
        }
        
        .table-wrap{overflow:auto;border:1px solid var(--line2);border-radius:14px;background:#fff}
        .table-wrap table{min-width:760px}
        .stack{display:flex;flex-direction:column;gap:10px}
        .form-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:12px}
        .field{display:flex;flex-direction:column;gap:6px}
        .label{font-size:12px;color:#475569;font-weight:800}
        input,select,textarea{
            width:100%;
            padding:11px 12px;
            border:1px solid #cbd5e1;
            border-radius:12px;
            background:#fff;
            color:#0f172a;
            font-size:13px;
            outline:none;
        }
        input::placeholder,textarea::placeholder{color:#94a3b8}
        input:focus,select:focus,textarea:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.12)}
        pre{margin:0;padding:12px;border:1px solid var(--line2);border-radius:14px;background:#f8fafc;color:#334155;font-size:12px;max-height:320px;overflow:auto}
        button,.btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:7px;
            padding:10px 14px;
            border:1px solid #d7e0ec;
            border-radius:12px;
            background:#fff;
            color:#0f172a;
            text-decoration:none;
            cursor:pointer;
            font-size:13px;
            font-weight:900;
            transition:.16s ease;
        }
        button:hover,.btn:hover{transform:translateY(-1px);box-shadow:0 10px 24px rgba(15,23,42,.10)}
        .btn-primary{background:linear-gradient(135deg,#1d4ed8,#2563eb);border-color:#1d4ed8;color:#fff}
        .btn-success{background:linear-gradient(135deg,#0f766e,#14b8a6);border-color:#0f766e;color:#fff}
        .btn-warn,.btn-warning{background:linear-gradient(135deg,#b45309,#f59e0b);border-color:#b45309;color:#fff}
        .btn-danger{background:linear-gradient(135deg,#b91c1c,#ef4444);border-color:#b91c1c;color:#fff}
        table{width:100%;border-collapse:separate;border-spacing:0;font-size:13px;border:1px solid var(--line2);border-radius:14px;overflow:hidden;background:#fff}
        th,td{padding:11px 12px;text-align:left;border-bottom:1px solid var(--line2)}
        th{font-size:12px;color:#334155;background:#f1f6ff;font-weight:900;text-transform:uppercase;letter-spacing:.045em}
        tr:last-child td{border-bottom:0}
        tr:hover td{background:#f8fbff}
        .empty{padding:20px;border:1px dashed #cbd5e1;border-radius:14px;background:#f8fafc;color:var(--muted);font-size:13px;text-align:center}
        @media (max-width:1200px){.sidebar{display:none}.col-3,.col-4,.col-5,.col-6,.col-7,.col-8,.col-12{grid-column:span 12}.container{padding:16px}}
    </style>
</head>
<body>
<div class="app">
    @if(session('user_id'))
    <aside class="sidebar">
        <div class="brand">Colony Billing</div>
        <div class="sub">Enterprise Admin Console</div>
        <nav class="nav">
            <div class="nav-section">
                <div class="nav-head">Core</div>
                <a class="{{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard"><span class="nav-ico">D</span>Dashboard</a>
                <a class="{{ request()->is('month-lifecycle') ? 'active' : '' }}" href="/month-lifecycle"><span class="nav-ico">M</span>Month Lifecycle</a>
                <a class="{{ request()->is('imports-validation') ? 'active' : '' }}" href="/imports-validation"><span class="nav-ico">I</span>Imports & Validation</a>
                <a class="{{ request()->is('billing-run-lock') ? 'active' : '' }}" href="/billing-run-lock"><span class="nav-ico">B</span>Billing Run & Lock</a>
                <a class="{{ request()->is('reporting') ? 'active' : '' }}" href="/reporting"><span class="nav-ico">R</span>Reporting Center</a>
            </div>
            <div class="nav-section">
                <div class="nav-head">Operations</div>
                <a class="{{ request()->is('people-residency') ? 'active' : '' }}" href="/people-residency"><span class="nav-ico">P</span>People & Residency</a>
                <a class="{{ request()->is('active-days-monthly') || request()->is('ui/monthly-active-days') ? 'active' : '' }}" href="/active-days-monthly"><span class="nav-ico">AD</span>Active Days Monthly</a>
                <a class="{{ request()->is('transport') ? 'active' : '' }}" href="/transport"><span class="nav-ico">T</span>Transport</a>
                <a class="{{ request()->is('meters-readings') ? 'active' : '' }}" href="/meters-readings"><span class="nav-ico">MR</span>Meters & Readings</a>
                <a class="{{ request()->is('unit-directory') ? 'active' : '' }}" href="/unit-directory"><span class="nav-ico">U</span>Unit Directory</a>
                <a class="{{ request()->is('housing-rooms') || request()->is('housing-occupancy') ? 'active' : '' }}" href="/housing-occupancy"><span class="nav-ico">H</span>Housing & Occupancy</a>
                <a class="{{ request()->is('electric-v1-lab') || request()->is('electric-v1-lab/*') ? 'active' : '' }}" href="/electric-v1-lab"><span class="nav-ico">E</span>Electric V1 Lab</a>
                <a class="{{ request()->is('rates') ? 'active' : '' }}" href="/rates"><span class="nav-ico">$</span>Rates</a>
            </div>
            <div class="nav-section">
                <div class="nav-head">Profile</div>
                <a href="/profile"><span class="nav-ico">MP</span>My Profile</a>
                @if(in_array(session('role'), ['SUPER_ADMIN']))
                    <a class="{{ request()->is('ui/admin/users') ? 'active' : '' }}" href="/ui/admin/users"><span class="nav-ico">UM</span>User Management</a>
                @endif
                <a href="/logout"><span class="nav-ico">X</span>Logout</a>
            </div>
        </nav>
    </aside>
    @endif

    <main class="main">
        <header class="top">
            <div class="top-title">@yield('page_title', 'Colony Billing')</div>
            <div class="user">User #{{ session('user_id', 'N/A') }} · Role: {{ session('role', 'N/A') }}</div>
        </header>
        <section class="container">
            <div class="page-head">
                <div>
                    <div class="crumb">Admin / Workspace</div>
                    <h1 class="page-title">@yield('page_title', 'Colony Billing')</h1>
                    @hasSection('page_subtitle')<p class="page-sub">@yield('page_subtitle')</p>@endif
                </div>
                @hasSection('page_actions')<div class="toolbar">@yield('page_actions')</div>@endif
            </div>
            @if(session('status'))<div class="banner">{{ session('status') }}</div>@endif
            @yield('content')
        </section>
    </main>
</div>
</body>
</html>
