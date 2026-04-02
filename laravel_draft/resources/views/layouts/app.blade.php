<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page_title', 'Colony Billing Admin')</title>
    <style>
        :root{
            --bg:radial-gradient(circle at top, #243a74 0%, #12203f 36%, #0d1830 68%, #0a1327 100%);
            --panel:rgba(20,31,58,.72);--panel-soft:rgba(24,37,68,.66);--line:rgba(148,177,255,.18);--line-strong:rgba(160,192,255,.30);
            --text:#eef4ff;--muted:#a8bbdf;--brand:#78d9ff;--brand-strong:#6f8cff;--success:#22c7a8;--warn:#ffb84d;--danger:#ff768f;
            --shadow:0 14px 34px rgba(6,12,28,.28),0 4px 14px rgba(14,30,68,.22);
            --side-bg:linear-gradient(180deg,rgba(18,30,58,.94) 0%,rgba(14,24,48,.96) 100%);
            --side-line:rgba(148,177,255,.18);
            --side-text:#edf3ff;
            --side-muted:#9fb4df;
            --side-glow:#78d9ff;
            --side-glow-2:#6f8cff;
        }
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg);background-attachment:fixed;color:var(--text);font-family:Inter,Segoe UI,Arial,sans-serif}
        .app{display:flex;min-height:100vh}
        .sidebar{width:272px;background:var(--side-bg);border-right:1px solid var(--side-line);padding:18px 14px;position:sticky;top:0;height:100vh;overflow:auto;box-shadow:inset -1px 0 0 rgba(255,255,255,.05),0 14px 36px rgba(10,18,38,.20);backdrop-filter:blur(18px)}
        .brand{font-weight:900;font-size:17px;letter-spacing:.5px;padding:10px 12px;color:var(--side-text);text-shadow:0 0 18px rgba(120,217,255,.12)}
        .sub{font-size:11px;color:var(--side-muted);padding:0 12px 14px;border-bottom:1px solid rgba(120,146,255,.14);letter-spacing:.8px;text-transform:uppercase}
        .nav-section{margin-top:14px}
        .nav-head{font-size:11px;color:var(--side-muted);text-transform:uppercase;letter-spacing:.9px;padding:8px 10px}
        .nav a{display:flex;align-items:center;color:var(--side-text);text-decoration:none;padding:10px 12px;border-radius:16px;margin:6px 0;font-size:13px;border:1px solid rgba(148,177,255,.14);background:linear-gradient(180deg,rgba(255,255,255,.06),rgba(255,255,255,.025));box-shadow:inset 0 1px 0 rgba(255,255,255,.05),0 8px 18px rgba(10,18,38,.14);transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease}
        .nav a:hover{transform:translateY(-1px);border-color:rgba(120,217,255,.28);box-shadow:inset 0 1px 0 rgba(255,255,255,.08),0 0 0 1px rgba(120,217,255,.06),0 10px 22px rgba(10,18,38,.18),0 0 14px rgba(120,217,255,.08);background:linear-gradient(180deg,rgba(120,217,255,.08),rgba(111,140,255,.07));color:#ffffff}
        .nav a.active{transform:translateY(-1px);border-color:rgba(120,217,255,.34);background:linear-gradient(90deg,rgba(120,217,255,.14),rgba(111,140,255,.12));color:#ffffff;box-shadow:inset 0 1px 0 rgba(255,255,255,.10),0 0 0 1px rgba(120,217,255,.08),0 12px 24px rgba(10,18,38,.20),0 0 18px rgba(120,217,255,.10)}
        .nav-ico{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;margin-right:10px;border-radius:8px;background:linear-gradient(180deg,rgba(255,255,255,.16),rgba(255,255,255,.05));border:1px solid rgba(255,255,255,.10);box-shadow:inset 0 1px 0 rgba(255,255,255,.12),0 4px 10px rgba(10,18,38,.16);font-size:13px;flex:none}
        .main{flex:1;display:flex;flex-direction:column;min-width:0;position:relative}
        .main::before{content:'';position:fixed;inset:0;pointer-events:none;background:radial-gradient(circle at top right, rgba(77,226,255,.08), transparent 28%),radial-gradient(circle at left center, rgba(139,92,246,.09), transparent 26%);}
        .top{background:linear-gradient(180deg,rgba(23,36,68,.72),rgba(17,28,54,.66));backdrop-filter:blur(18px);border-bottom:1px solid rgba(148,177,255,.16);padding:14px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:inset 0 1px 0 rgba(255,255,255,.05),0 12px 28px rgba(10,18,38,.14)}
        .top-title{font-weight:800;font-size:15px;letter-spacing:.3px;color:#f3f7ff}
        .user{font-size:12px;color:var(--muted)}
        .container{padding:22px 24px;position:relative;z-index:1}
        .page-head{display:flex;justify-content:space-between;align-items:flex-end;gap:12px;margin-bottom:18px;padding:16px 18px;border:1px solid rgba(148,177,255,.16);border-radius:20px;background:linear-gradient(180deg,rgba(30,44,82,.56),rgba(20,32,62,.48));backdrop-filter:blur(14px);box-shadow:inset 0 1px 0 rgba(255,255,255,.06),0 12px 28px rgba(10,18,38,.14)}
        .page-title{margin:0;font-size:22px;line-height:1.2}
        .page-sub{margin:4px 0 0;color:var(--muted);font-size:13px}
        .crumb{font-size:12px;color:#84a0d9;text-transform:uppercase;letter-spacing:.7px}
        .grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px}
        .col-3{grid-column:span 3}.col-4{grid-column:span 4}.col-5{grid-column:span 5}.col-6{grid-column:span 6}.col-7{grid-column:span 7}.col-8{grid-column:span 8}.col-12{grid-column:span 12}
        .card{background:linear-gradient(180deg,rgba(27,41,77,.56),rgba(22,34,66,.48));border:1px solid var(--line);border-radius:18px;padding:15px;box-shadow:var(--shadow);backdrop-filter:blur(12px);color:var(--text)}
        .card.soft{background:linear-gradient(180deg,rgba(30,46,86,.52),rgba(22,34,66,.42))}
        .section-title{margin:0 0 10px;font-size:16px}
        .kpi{font-size:28px;font-weight:800;margin:8px 0 4px}
        .muted{color:var(--muted);font-size:12px}
        .badge{display:inline-block;padding:3px 9px;border-radius:999px;font-size:11px;border:1px solid rgba(77,226,255,.24);background:rgba(77,226,255,.08);color:#9cecff}
        .badge.success{border-color:rgba(25,195,163,.30);background:rgba(25,195,163,.10);color:#8df3df}
        .badge.warn{border-color:rgba(245,158,11,.28);background:rgba(245,158,11,.10);color:#ffd08a}
        .banner{padding:10px 12px;border-radius:12px;background:linear-gradient(180deg,rgba(77,226,255,.12),rgba(77,226,255,.07));border:1px solid rgba(77,226,255,.22);color:#d7f7ff;font-size:13px;margin-bottom:12px}
        .alert{padding:10px 12px;border:1px solid rgba(255,107,129,.28);background:linear-gradient(180deg,rgba(255,107,129,.14),rgba(255,107,129,.08));color:#ffd7dd;border-radius:12px;font-size:13px}
        .toolbar{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
        .sticky-actions{position:sticky;top:0;z-index:5;background:linear-gradient(180deg,#ffffff 80%,rgba(255,255,255,.85) 100%);padding:8px;border:1px solid var(--line);border-radius:10px}
        .table-wrap{overflow:auto;border:1px solid var(--line);border-radius:10px}
        .table-wrap table{min-width:760px}
        .stack{display:flex;flex-direction:column;gap:10px}
        .form-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:10px}
        .field{display:flex;flex-direction:column;gap:5px}
        .label{font-size:12px;color:#334155;font-weight:600}
        input,select,textarea{width:100%;padding:10px 11px;border:1px solid rgba(170,195,255,.34);border-radius:12px;background:rgba(42,60,102,.72);color:var(--text);font-size:13px;outline:none;backdrop-filter:blur(10px)}
        input::placeholder,textarea::placeholder{color:#bdd0f3}
        input:focus,select:focus,textarea:focus{border-color:rgba(120,217,255,.52);box-shadow:0 0 0 3px rgba(120,217,255,.12),0 0 14px rgba(120,217,255,.08)}
        pre{margin:0;padding:12px;border:1px solid rgba(120,146,255,.16);border-radius:14px;background:rgba(7,11,22,.88);color:#cbd5e1;font-size:12px;max-height:320px;overflow:auto}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 12px;border:1px solid rgba(170,195,255,.26);border-radius:12px;background:linear-gradient(180deg,rgba(255,255,255,.07),rgba(255,255,255,.03));color:var(--text);text-decoration:none;cursor:pointer;font-size:13px;font-weight:700;box-shadow:inset 0 1px 0 rgba(255,255,255,.06),0 8px 18px rgba(10,18,38,.12)}
        .btn:hover{background:linear-gradient(180deg,rgba(120,217,255,.10),rgba(111,140,255,.08));border-color:rgba(120,217,255,.26)}
        .btn-primary{background:linear-gradient(90deg,rgba(120,217,255,.26),rgba(111,140,255,.24));border-color:rgba(120,217,255,.30);color:#fff}.btn-primary:hover{background:linear-gradient(90deg,rgba(120,217,255,.32),rgba(111,140,255,.28))}
        .btn-success{background:var(--success);border-color:var(--success);color:#fff}
        .btn-warn{background:var(--warn);border-color:var(--warn);color:#fff}
        .btn-danger{background:#fff;border-color:#fecaca;color:#b42318}
        table{width:100%;border-collapse:separate;border-spacing:0;font-size:13px}
        th,td{padding:10px 11px;text-align:left;border-bottom:1px solid rgba(122,146,255,.10)}
        th{font-size:12px;color:#cfe1ff;background:rgba(255,255,255,.04);font-weight:700}
        tr:hover td{background:rgba(255,255,255,.03)}
        .empty{padding:20px;border:1px dashed rgba(122,146,255,.24);border-radius:14px;background:rgba(18,25,46,.50);color:var(--muted);font-size:13px;text-align:center}
        .split{display:flex;gap:10px;flex-wrap:wrap}
        @media (max-width:1200px){.sidebar{display:none}.col-3,.col-4,.col-5,.col-6,.col-7,.col-8,.col-12{grid-column:span 12}.container{padding:16px}.sticky-actions{position:static;padding:6px}}
    </style>
</head>
<body>
<div class="app">
    @if(session('user_id'))
    <aside class="sidebar">
        <div class="brand">🚀 Colony Billing</div>
        <div class="sub">Control Grid // Enterprise Console</div>
        <nav class="nav">
            <div class="nav-section">
                <div class="nav-head">🧭 Core</div>
                <a class="{{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard"><span class="nav-ico">📊</span>Dashboard</a>
                <a class="{{ request()->is('month-lifecycle') ? 'active' : '' }}" href="/month-lifecycle"><span class="nav-ico">🗓️</span>Month Lifecycle</a>
                <a class="{{ request()->is('imports-validation') ? 'active' : '' }}" href="/imports-validation"><span class="nav-ico">📥</span>Imports & Validation</a>
                <a class="{{ request()->is('billing-run-lock') ? 'active' : '' }}" href="/billing-run-lock"><span class="nav-ico">🔒</span>Billing Run & Lock</a>
                <a class="{{ request()->is('reporting') ? 'active' : '' }}" href="/reporting"><span class="nav-ico">📑</span>Reporting Center</a>
            </div>
            <div class="nav-section">
                <div class="nav-head">⚙️ Operations</div>
                <a class="{{ request()->is('people-residency') ? 'active' : '' }}" href="/people-residency"><span class="nav-ico">🏠</span>People & Residency</a>
                <a class="{{ request()->is('transport') ? 'active' : '' }}" href="/transport"><span class="nav-ico">🚌</span>Transport</a>
                <a class="{{ request()->is('meters-readings') ? 'active' : '' }}" href="/meters-readings"><span class="nav-ico">📏</span>Meters & Readings</a>
                <a class="{{ request()->is('unit-directory') ? 'active' : '' }}" href="/unit-directory"><span class="nav-ico">🧱</span>Unit Directory</a>
                <a class="{{ request()->is('housing-rooms') || request()->is('housing-occupancy') ? 'active' : '' }}" href="/housing-occupancy"><span class="nav-ico">🛏️</span>Housing & Occupancy</a>
                <a class="{{ request()->is('electric-v1-lab') || request()->is('electric-v1-lab/*') ? 'active' : '' }}" href="/electric-v1-lab"><span class="nav-ico">⚡</span>Electric V1 Lab</a>
                <a class="{{ request()->is('rates') ? 'active' : '' }}" href="/rates"><span class="nav-ico">💲</span>Rates</a>
            </div>
            <div class="nav-section">
                <div class="nav-head">👤 Profile</div>
                <a href="/profile"><span class="nav-ico">🪪</span>My Profile</a>
                @if(in_array(session('role'), ['SUPER_ADMIN']))
                    <a class="{{ request()->is('ui/admin/users') ? 'active' : '' }}" href="/ui/admin/users"><span class="nav-ico">🛡️</span>User Management</a>
                @endif
                <a href="/logout"><span class="nav-ico">⏻</span>Logout</a>
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
