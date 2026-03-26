<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Colony Billing Admin</title>
    <style>
        :root{--bg:#f4f6fb;--panel:#ffffff;--line:#d9e0ee;--text:#1b2430;--muted:#667085;--brand:#1e3a8a;--brand2:#0f766e;--warn:#b45309}
        *{box-sizing:border-box} body{margin:0;font-family:Segoe UI,Arial,sans-serif;background:var(--bg);color:var(--text)}
        .app{display:flex;min-height:100vh}
        .sidebar{width:250px;background:#0f172a;color:#e2e8f0;padding:18px 14px}
        .brand{font-weight:700;font-size:18px;margin:4px 8px 18px}
        .sub{font-size:12px;color:#94a3b8;margin:0 8px 16px}
        .nav a{display:block;color:#cbd5e1;text-decoration:none;padding:10px 12px;border-radius:8px;margin:4px 0;font-size:14px}
        .nav a.active,.nav a:hover{background:#1e293b;color:#fff}
        .main{flex:1;display:flex;flex-direction:column}
        .top{background:var(--panel);border-bottom:1px solid var(--line);padding:14px 22px;display:flex;justify-content:space-between;align-items:center}
        .user{font-size:13px;color:var(--muted)}
        .container{padding:22px}
        .grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px}
        .card{background:var(--panel);border:1px solid var(--line);border-radius:10px;padding:16px}
        .kpi{font-size:26px;font-weight:700;margin:8px 0}
        .muted{color:var(--muted);font-size:13px}
        .col-3{grid-column:span 3}.col-4{grid-column:span 4}.col-6{grid-column:span 6}.col-8{grid-column:span 8}.col-12{grid-column:span 12}
        table{width:100%;border-collapse:collapse;font-size:13px} th,td{border-bottom:1px solid var(--line);padding:9px;text-align:left} th{background:#eef2ff}
        .btn{display:inline-block;padding:8px 12px;border:1px solid var(--line);border-radius:8px;background:#fff;color:#111827;text-decoration:none;cursor:pointer}
        .btn-primary{background:var(--brand);border-color:var(--brand);color:#fff}
        .btn-success{background:var(--brand2);border-color:var(--brand2);color:#fff}
        .btn-warning{background:var(--warn);border-color:var(--warn);color:#fff}
        .actions{display:flex;gap:8px;flex-wrap:wrap}
        .banner{padding:10px 12px;border-radius:8px;background:#e0ecff;border:1px solid #c5d9ff;font-size:13px}
        .badge{display:inline-block;padding:3px 8px;border-radius:999px;font-size:11px;background:#ecfeff;border:1px solid #a5f3fc;color:#155e75}
        @media (max-width:1000px){.sidebar{display:none}.col-3,.col-4,.col-6,.col-8,.col-12{grid-column:span 12}}
    </style>
</head>
<body>
<div class="app">
    @if(session('user_id'))
    <aside class="sidebar">
        <div class="brand">Colony Billing</div>
        <div class="sub">Enterprise Admin Console</div>
        <nav class="nav">
            <a class="{{ request()->is('ui/dashboard') ? 'active' : '' }}" href="/ui/dashboard">Dashboard</a>
            <a class="{{ request()->is('ui/billing') ? 'active' : '' }}" href="/ui/billing">Billing Workspace</a>
            <a class="{{ request()->is('ui/month-cycle') ? 'active' : '' }}" href="/ui/month-cycle">Month Cycle</a>
            <a class="{{ request()->is('ui/reports') ? 'active' : '' }}" href="/ui/reports">Reports</a>
            <a class="{{ request()->is('ui/reconciliation') ? 'active' : '' }}" href="/ui/reconciliation">Reconciliation</a>
            @if(in_array(session('role'), ['SUPER_ADMIN']))
                <a class="{{ request()->is('ui/admin/users') ? 'active' : '' }}" href="/ui/admin/users">User Management</a>
            @endif
            <a href="/ui/profile">My Profile</a>
            <a href="/logout">Logout</a>
        </nav>
    </aside>
    @endif

    <main class="main">
        <header class="top">
            <div><strong>@yield('page_title', 'Colony Billing')</strong></div>
            <div class="user">User #{{ session('user_id', 'N/A') }} | Role: {{ session('role', 'N/A') }}</div>
        </header>
        <section class="container">
            @if(session('status'))<div class="banner" style="margin-bottom:12px">{{ session('status') }}</div>@endif
            @yield('content')
        </section>
    </main>
</div>
</body>
</html>
