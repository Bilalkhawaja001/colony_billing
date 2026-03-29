<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page_title', 'Colony Billing Admin')</title>
    <style>
        :root{
            --bg:#f5f7fb;--panel:#ffffff;--panel-soft:#fafbff;--line:#e2e8f0;--line-strong:#cbd5e1;
            --text:#0f172a;--muted:#64748b;--brand:#3347a9;--brand-strong:#25388f;--success:#0f766e;--warn:#b45309;--danger:#b42318;
            --shadow:0 1px 2px rgba(15,23,42,.06),0 10px 24px rgba(15,23,42,.04);
        }
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,Segoe UI,Arial,sans-serif}
        .app{display:flex;min-height:100vh}
        .sidebar{width:272px;background:#fff;border-right:1px solid var(--line);padding:18px 14px;position:sticky;top:0;height:100vh;overflow:auto}
        .brand{font-weight:800;font-size:17px;letter-spacing:.2px;padding:8px 10px}
        .sub{font-size:12px;color:var(--muted);padding:0 10px 14px;border-bottom:1px solid var(--line)}
        .nav-section{margin-top:12px}
        .nav-head{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.6px;padding:6px 10px}
        .nav a{display:block;color:#1e293b;text-decoration:none;padding:9px 11px;border-radius:10px;margin:3px 0;font-size:13px;border:1px solid transparent}
        .nav a.active,.nav a:hover{background:#eef2ff;border-color:#dbe4ff;color:var(--brand-strong)}
        .main{flex:1;display:flex;flex-direction:column;min-width:0}
        .top{background:#fff;border-bottom:1px solid var(--line);padding:13px 24px;display:flex;justify-content:space-between;align-items:center}
        .top-title{font-weight:700;font-size:15px}
        .user{font-size:12px;color:var(--muted)}
        .container{padding:22px 24px}
        .page-head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;margin-bottom:16px}
        .page-title{margin:0;font-size:22px;line-height:1.2}
        .page-sub{margin:4px 0 0;color:var(--muted);font-size:13px}
        .crumb{font-size:12px;color:var(--muted)}
        .grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px}
        .col-3{grid-column:span 3}.col-4{grid-column:span 4}.col-5{grid-column:span 5}.col-6{grid-column:span 6}.col-7{grid-column:span 7}.col-8{grid-column:span 8}.col-12{grid-column:span 12}
        .card{background:var(--panel);border:1px solid var(--line);border-radius:12px;padding:15px;box-shadow:var(--shadow)}
        .card.soft{background:var(--panel-soft)}
        .section-title{margin:0 0 10px;font-size:16px}
        .kpi{font-size:28px;font-weight:800;margin:8px 0 4px}
        .muted{color:var(--muted);font-size:12px}
        .badge{display:inline-block;padding:3px 9px;border-radius:999px;font-size:11px;border:1px solid #dbeafe;background:#eff6ff;color:#1d4ed8}
        .badge.success{border-color:#99f6e4;background:#ecfeff;color:#0f766e}
        .badge.warn{border-color:#fed7aa;background:#fff7ed;color:#b45309}
        .banner{padding:10px 12px;border-radius:10px;background:#eef4ff;border:1px solid #cfddff;color:#1e3a8a;font-size:13px;margin-bottom:12px}
        .alert{padding:10px 12px;border:1px solid #fecaca;background:#fef2f2;color:#991b1b;border-radius:10px;font-size:13px}
        .toolbar{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
        .form-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:10px}
        .field{display:flex;flex-direction:column;gap:5px}
        .label{font-size:12px;color:#334155;font-weight:600}
        input,select,textarea{width:100%;padding:10px 11px;border:1px solid var(--line-strong);border-radius:9px;background:#fff;color:var(--text);font-size:13px;outline:none}
        input:focus,select:focus,textarea:focus{border-color:#9db4ff;box-shadow:0 0 0 3px rgba(51,71,169,.12)}
        pre{margin:0;padding:12px;border:1px solid var(--line);border-radius:10px;background:#0b1220;color:#cbd5e1;font-size:12px;max-height:320px;overflow:auto}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 12px;border:1px solid var(--line-strong);border-radius:9px;background:#fff;color:#0f172a;text-decoration:none;cursor:pointer;font-size:13px;font-weight:600}
        .btn:hover{background:#f8fafc}
        .btn-primary{background:var(--brand);border-color:var(--brand);color:#fff}.btn-primary:hover{background:var(--brand-strong)}
        .btn-success{background:var(--success);border-color:var(--success);color:#fff}
        .btn-warn{background:var(--warn);border-color:var(--warn);color:#fff}
        .btn-danger{background:#fff;border-color:#fecaca;color:#b42318}
        table{width:100%;border-collapse:separate;border-spacing:0;font-size:13px}
        th,td{padding:10px 11px;text-align:left;border-bottom:1px solid var(--line)}
        th{font-size:12px;color:#334155;background:#f8fafc;font-weight:700}
        tr:hover td{background:#fbfdff}
        .empty{padding:20px;border:1px dashed var(--line-strong);border-radius:10px;background:#fcfdff;color:var(--muted);font-size:13px;text-align:center}
        .split{display:flex;gap:10px;flex-wrap:wrap}
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
                <a class="{{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard">Dashboard</a>
                <a class="{{ request()->is('month-lifecycle') ? 'active' : '' }}" href="/month-lifecycle">Month Lifecycle</a>
                <a class="{{ request()->is('imports-validation') ? 'active' : '' }}" href="/imports-validation">Imports & Validation</a>
                <a class="{{ request()->is('billing-run-lock') ? 'active' : '' }}" href="/billing-run-lock">Billing Run & Lock</a>
                <a class="{{ request()->is('reporting') ? 'active' : '' }}" href="/reporting">Reporting Center</a>
            </div>
            <div class="nav-section">
                <div class="nav-head">Operations</div>
                <a class="{{ request()->is('people-residency') ? 'active' : '' }}" href="/people-residency">People & Residency</a>
                <a class="{{ request()->is('meters-readings') ? 'active' : '' }}" href="/meters-readings">Meters & Readings</a>
                <a class="{{ request()->is('unit-directory') ? 'active' : '' }}" href="/unit-directory">Unit Directory</a>
                <a class="{{ request()->is('housing-rooms') || request()->is('housing-occupancy') ? 'active' : '' }}" href="/housing-occupancy">Housing & Occupancy</a>
                <a class="{{ request()->is('electric-v1-lab') || request()->is('electric-v1-lab/*') ? 'active' : '' }}" href="/electric-v1-lab">Electric V1 Lab</a>
                <a class="{{ request()->is('rates') ? 'active' : '' }}" href="/rates">Rates</a>
            </div>
            <div class="nav-section">
                <div class="nav-head">Profile</div>
                <a href="/profile">My Profile</a>
                @if(in_array(session('role'), ['SUPER_ADMIN']))
                    <a class="{{ request()->is('ui/admin/users') ? 'active' : '' }}" href="/ui/admin/users">User Management</a>
                @endif
                <a href="/logout">Logout</a>
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
