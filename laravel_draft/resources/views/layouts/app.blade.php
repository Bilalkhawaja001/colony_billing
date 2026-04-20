<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page_title', 'Colony Billing Admin')</title>
    <style>
        :root{
            --bg:#eef4fb;
            --panel:rgba(255,255,255,.72);
            --panel-strong:rgba(255,255,255,.84);
            --panel-soft:rgba(248,251,255,.78);
            --line:rgba(191,208,230,.55);
            --line-strong:rgba(170,190,216,.72);
            --text:#16304a;
            --heading:#10263d;
            --muted:#647a92;
            --brand:#6d9fdb;
            --brand-strong:#4f84c7;
            --brand-soft:#e7f0fb;
            --success:#73ab99;
            --warn:#d4b07a;
            --danger:#cf929b;
            --shadow-soft:8px 8px 20px rgba(189,203,222,.34), -8px -8px 18px rgba(255,255,255,.9);
            --shadow-panel:16px 16px 34px rgba(188,201,220,.32), -12px -12px 28px rgba(255,255,255,.94);
            --shadow-inset:inset 2px 2px 4px rgba(255,255,255,.86), inset -3px -3px 7px rgba(196,208,226,.26);
            --radius-sm:12px;
            --radius-md:20px;
            --radius-lg:28px;
        }
        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{
            margin:0;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,.96), transparent 24%),
                radial-gradient(circle at top right, rgba(205,225,247,.46), transparent 26%),
                linear-gradient(180deg, #f8fbff 0%, var(--bg) 46%, #edf3fa 100%);
            background-attachment:fixed;
            color:var(--text);
            font-family:Inter,Segoe UI,Arial,sans-serif;
        }
        .app{display:flex;min-height:100vh}
        .sidebar{
            width:272px;
            background:linear-gradient(180deg, rgba(248,251,255,.74), rgba(238,245,252,.62));
            border-right:1px solid rgba(255,255,255,.82);
            padding:18px 14px;
            position:sticky;
            top:0;
            height:100vh;
            overflow:auto;
            box-shadow:var(--shadow-soft);
            backdrop-filter:blur(18px);
        }
        .brand{
            font-weight:900;
            font-size:17px;
            letter-spacing:.2px;
            padding:12px 12px 8px;
            color:var(--heading);
        }
        .sub{
            font-size:11px;
            color:var(--muted);
            padding:0 12px 14px;
            border-bottom:1px solid rgba(191,208,230,.5);
            letter-spacing:.8px;
            text-transform:uppercase;
        }
        .nav-section{margin-top:14px}
        .nav-head{font-size:11px;color:#7a92ad;text-transform:uppercase;letter-spacing:.9px;padding:8px 10px;font-weight:800}
        .nav a{
            display:flex;
            align-items:center;
            color:#3e5e7f;
            text-decoration:none;
            padding:10px 12px;
            border-radius:16px;
            margin:6px 0;
            font-size:13px;
            font-weight:700;
            border:1px solid transparent;
            background:linear-gradient(180deg, rgba(255,255,255,.72), rgba(239,245,252,.58));
            box-shadow:var(--shadow-soft);
            transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease;
        }
        .nav a:hover{
            transform:translateY(-1px);
            border-color:rgba(109,159,219,.28);
            background:linear-gradient(180deg, rgba(255,255,255,.88), rgba(232,242,252,.72));
            color:var(--heading);
        }
        .nav a.active{
            transform:translateY(-1px);
            border-color:rgba(109,159,219,.34);
            background:linear-gradient(90deg, rgba(231,240,251,.96), rgba(214,229,246,.84));
            color:var(--heading);
            box-shadow:var(--shadow-panel);
        }
        .nav-ico{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:24px;
            height:24px;
            margin-right:10px;
            border-radius:8px;
            background:linear-gradient(180deg, rgba(255,255,255,.9), rgba(228,238,248,.7));
            border:1px solid rgba(255,255,255,.82);
            box-shadow:var(--shadow-soft);
            font-size:13px;
            flex:none;
        }
        .main{flex:1;display:flex;flex-direction:column;min-width:0;position:relative}
        .top{
            background:linear-gradient(180deg, rgba(255,255,255,.84), rgba(241,247,253,.74));
            backdrop-filter:blur(18px);
            border-bottom:1px solid rgba(255,255,255,.82);
            padding:14px 24px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            box-shadow:var(--shadow-soft);
        }
        .top-title{font-weight:800;font-size:15px;letter-spacing:.3px;color:var(--heading)}
        .user{font-size:12px;color:var(--muted)}
        .container{padding:22px 24px;position:relative;z-index:1}
        .page-head{
            display:flex;
            justify-content:space-between;
            align-items:flex-end;
            gap:12px;
            margin-bottom:18px;
            padding:16px 18px;
            border:1px solid rgba(255,255,255,.82);
            border-radius:20px;
            background:linear-gradient(180deg, rgba(255,255,255,.8), rgba(240,246,252,.7));
            backdrop-filter:blur(14px);
            box-shadow:var(--shadow-panel);
        }
        .page-title{margin:0;font-size:22px;line-height:1.2;color:var(--heading)}
        .page-sub{margin:4px 0 0;color:var(--muted);font-size:13px}
        .crumb{font-size:12px;color:#7f96b1;text-transform:uppercase;letter-spacing:.7px}
        .grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px}
        .col-3{grid-column:span 3}.col-4{grid-column:span 4}.col-5{grid-column:span 5}.col-6{grid-column:span 6}.col-7{grid-column:span 7}.col-8{grid-column:span 8}.col-12{grid-column:span 12}
        .card{
            background:linear-gradient(180deg, rgba(255,255,255,.8), rgba(241,247,253,.68));
            border:1px solid rgba(255,255,255,.82);
            border-radius:18px;
            padding:15px;
            box-shadow:var(--shadow-panel);
            backdrop-filter:blur(12px);
            color:var(--text);
        }
        .card.soft{background:linear-gradient(180deg, rgba(249,252,255,.84), rgba(236,244,251,.7))}
        .section-title{margin:0 0 10px;font-size:16px;color:var(--heading)}
        .kpi{font-size:28px;font-weight:800;margin:8px 0 4px;color:var(--heading)}
        .muted{color:var(--muted);font-size:12px}
        .badge{display:inline-block;padding:3px 9px;border-radius:999px;font-size:11px;border:1px solid rgba(109,159,219,.24);background:rgba(231,240,251,.85);color:#456a8d;box-shadow:var(--shadow-soft)}
        .badge.success{border-color:rgba(115,171,153,.28);background:rgba(233,246,240,.92);color:#417061}
        .badge.warn{border-color:rgba(212,176,122,.28);background:rgba(255,247,234,.94);color:#8a6730}
        .banner{padding:10px 12px;border-radius:12px;background:linear-gradient(180deg, rgba(231,240,251,.95), rgba(221,234,248,.84));border:1px solid rgba(109,159,219,.22);color:#456a8d;font-size:13px;margin-bottom:12px;box-shadow:var(--shadow-soft)}
        .alert{padding:10px 12px;border:1px solid rgba(207,146,155,.28);background:linear-gradient(180deg, rgba(255,244,246,.96), rgba(246,226,230,.88));color:#8d5963;border-radius:12px;font-size:13px;box-shadow:var(--shadow-soft)}
        .toolbar{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
        .sticky-actions{position:sticky;top:0;z-index:5;background:linear-gradient(180deg,#ffffff 80%,rgba(255,255,255,.85) 100%);padding:8px;border:1px solid rgba(255,255,255,.82);border-radius:10px;box-shadow:var(--shadow-soft)}
        .table-wrap{overflow:auto;border:1px solid rgba(255,255,255,.82);border-radius:10px;box-shadow:var(--shadow-soft);background:rgba(255,255,255,.62)}
        .table-wrap table{min-width:760px}
        .stack{display:flex;flex-direction:column;gap:10px}
        .form-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:10px}
        .field{display:flex;flex-direction:column;gap:5px}
        .label{font-size:12px;color:#516884;font-weight:600}
        input,select,textarea{width:100%;padding:10px 11px;border:1px solid var(--panel-strong);border-radius:12px;background:linear-gradient(180deg, rgba(255,255,255,.95), rgba(239,245,252,.82));color:var(--text);font-size:13px;outline:none;backdrop-filter:blur(10px);box-shadow:var(--shadow-inset)}
        input::placeholder,textarea::placeholder{color:#91a7c1}
        input:focus,select:focus,textarea:focus{border-color:rgba(109,159,219,.52);box-shadow:0 0 0 3px rgba(109,159,219,.12), var(--shadow-inset)}
        pre{margin:0;padding:12px;border:1px solid rgba(255,255,255,.82);border-radius:14px;background:rgba(247,250,255,.92);color:#3f5e7f;font-size:12px;max-height:320px;overflow:auto;box-shadow:var(--shadow-soft)}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 12px;border:1px solid rgba(255,255,255,.84);border-radius:12px;background:linear-gradient(180deg, rgba(255,255,255,.88), rgba(236,243,251,.8));color:var(--text);text-decoration:none;cursor:pointer;font-size:13px;font-weight:700;box-shadow:var(--shadow-soft)}
        .btn:hover{background:linear-gradient(180deg, rgba(255,255,255,.95), rgba(228,238,248,.84));border-color:rgba(109,159,219,.26)}
        .btn-primary{background:linear-gradient(90deg, rgba(231,240,251,.98), rgba(204,222,244,.92));border-color:rgba(109,159,219,.30);color:#315579}.btn-primary:hover{background:linear-gradient(90deg, rgba(236,244,252,.98), rgba(214,229,246,.92))}
        .btn-success{background:linear-gradient(90deg, rgba(235,248,243,.98), rgba(219,238,229,.92));border-color:rgba(115,171,153,.30);color:#417061}
        .btn-warn{background:linear-gradient(90deg, rgba(255,248,238,.98), rgba(247,231,205,.92));border-color:rgba(212,176,122,.30);color:#8a6730}
        .btn-danger{background:linear-gradient(90deg, rgba(255,244,246,.98), rgba(246,226,230,.92));border-color:rgba(207,146,155,.30);color:#8d5963}
        table{width:100%;border-collapse:separate;border-spacing:0;font-size:13px}
        th,td{padding:10px 11px;text-align:left;border-bottom:1px solid rgba(191,208,230,.38)}
        th{font-size:12px;color:#627894;background:rgba(255,255,255,.46);font-weight:700}
        tr:hover td{background:rgba(255,255,255,.24)}
        .empty{padding:20px;border:1px dashed rgba(170,190,216,.52);border-radius:14px;background:rgba(250,252,255,.8);color:var(--muted);font-size:13px;text-align:center;box-shadow:var(--shadow-soft)}
        .split{display:flex;gap:10px;flex-wrap:wrap}
        @media (max-width:1200px){.sidebar{display:none}.col-3,.col-4,.col-5,.col-6,.col-7,.col-8,.col-12{grid-column:span 12}.container{padding:16px}.sticky-actions{position:static;padding:6px}}
    </style>
</head>
<body>
<div class="app">
    @if(session('user_id'))
    <aside class="sidebar">
        <div class="brand">Colony Billing</div>
        <div class="sub">Premium Light Workspace</div>
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
