<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Colony Billing — Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/dashboard-v2.css') }}?v={{ filemtime(public_path('css/dashboard-v2.css')) }}">
</head>
<body class="dv2">
<div class="dv2-app">

  {{-- TOP NAVBAR --}}
  <header class="dv2-topbar">
    <div class="dv2-brand">
      <div class="dv2-logo"><svg viewBox="0 0 24 24"><path d="M3 11L12 3l9 8v9a1 1 0 01-1 1H4a1 1 0 01-1-1z"/><path d="M12 7l-2.5 5h3L10 17"/></svg></div>
      <div><div class="nm">Colony Billing</div><div class="sb">Enterprise Platform</div></div>
    </div>
    <nav class="dv2-nav">
      <a class="active" href="{{ url('/dashboard-v2') }}"><svg viewBox="0 0 20 20"><path d="M3 9l7-6 7 6v8H3z"/><path d="M8 17v-5h4v5"/></svg>Dashboard</a>
      <a href="{{ url('/control-room') }}"><svg viewBox="0 0 20 20"><rect x="4" y="2" width="12" height="16" rx="2"/><path d="M7 6h6M7 10h6M7 14h4"/></svg>Billing</a>
      <a href="{{ url('/people-residency') }}"><svg viewBox="0 0 20 20"><circle cx="7" cy="6" r="2.5"/><circle cx="14" cy="7" r="2"/><path d="M2 16c0-2.5 2-4 5-4s5 1.5 5 4M12 15c0-1.8 1.5-3 3.5-3"/></svg>People &amp; Housing</a>
      <a href="{{ url('/control-room/readings') }}"><svg viewBox="0 0 20 20"><rect x="3" y="4" width="14" height="13" rx="2"/><path d="M3 8h14M7 2v3M13 2v3"/></svg>Monthly Data</a>
      <a href="{{ url('/reporting') }}"><svg viewBox="0 0 20 20"><path d="M4 16V8M9 16V4M14 16v-6"/></svg>Reports</a>
      <a href="{{ url('/rates') }}"><svg viewBox="0 0 20 20"><circle cx="10" cy="10" r="3"/><path d="M10 1v3M10 16v3M1 10h3M16 10h3M3.5 3.5l2 2M14.5 14.5l2 2M16.5 3.5l-2 2M5.5 14.5l-2 2"/></svg>Setup</a>
    </nav>
    <div class="dv2-right">
      <div class="dv2-ic"><svg width="20" height="20"><circle cx="9" cy="9" r="6.5"/><path d="M19 19l-5-5"/></svg></div>
      <div class="dv2-ic"><svg width="20" height="20"><path d="M10 3a4.5 4.5 0 014.5 4.5v3.5l1.5 2H4l1.5-2V7.5A4.5 4.5 0 0110 3zM8 17a2 2 0 004 0"/></svg><span class="bdg">5</span></div>
      <div class="dv2-ic"><svg width="20" height="20"><circle cx="10" cy="10" r="3.8"/><path d="M10 1v2.5M10 16.5V19M1 10h2.5M16.5 10H19M3.6 3.6l1.8 1.8M14.6 14.6l1.8 1.8M16.4 3.6l-1.8 1.8M5.4 14.6l-1.8 1.8"/></svg></div>
      <div class="dv2-user">
        <div class="av">AD</div>
        <div><div class="un">Admin</div><div class="ur">Super Admin</div></div>
        <span class="c" style="color:var(--faint)"><svg width="16" height="16"><path d="M4 6l4 4 4-4"/></svg></span>
      </div>
    </div>
  </header>

  {{-- DASHBOARD --}}
  <main class="dv2-dash">

    {{-- ROW 1: header / month --}}
    <div class="dv2-phead">
      <div class="dv2-phl">
        <div class="dv2-phic"><svg viewBox="0 0 24 24"><path d="M4 19V11M9 19V5M14 19v-6M19 19V9"/></svg></div>
        <div>
          <div class="h">Dashboard</div>
          <div class="d">Operations home for this billing month</div>
          @if(!empty($kpis['missing_bill_months']))
            @php
              $mnames=[1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
              $miss=collect($kpis['missing_bill_months'])->map(function($mc) use($mnames){ [$m,$y]=explode('-',$mc); return ($mnames[(int)$m]??$m).' '.$y; })->implode(', ');
              $shown=$kpis['resolved_month']??null;
              $shownLbl=$shown? ($mnames[(int)explode('-',$shown)[0]]??'').' '.explode('-',$shown)[1] : null;
            @endphp
            <div class="dv2-bannote">
              <svg width="14" height="14" viewBox="0 0 16 16"><path fill="currentColor" d="M8 1l7 13H1z"/><path fill="#fff" d="M8 5.5v4M8 11.5h0"/></svg>
              @php
                $billWord = count($kpis['missing_bill_months']) > 1 ? 'bills' : 'bill';
                $noteText = $miss.' '.$billWord.' not created yet';
              @endphp
              <span>{{ $noteText }}</span>
            </div>
          @endif
        </div>
      </div>
      <div class="dv2-phr">
        <div class="dv2-msel">
          <div class="i"><svg width="20" height="20"><rect x="2" y="3" width="16" height="15" rx="2.5"/><path d="M2 8h16M6 1v4M14 1v4"/></svg></div>
          <div><div class="l">Billing Month</div><div class="v">{{ $monthCycle ?? now()->format('m-Y') }}</div></div>
          <span class="c"><svg width="16" height="16"><path d="M4 6l4 4 4-4"/></svg></span>
        </div>
        <div class="dv2-spill"><svg width="17" height="17"><circle cx="8.5" cy="8.5" r="7"/><path d="M5 8.5l2.5 2.5 4.5-5"/></svg> Open</div>
        <a class="dv2-bcta" href="{{ url('/control-room') }}">
          <div class="i"><svg width="20" height="20"><path d="M12 2L4 12h5l-1 6 9-11h-5z"/></svg></div>
          <div><div class="t1">Billing Center</div><div class="t2">Start your workflow</div></div>
          <svg width="18" height="18"><path d="M3 9h11M10 5l4 4-4 4"/></svg>
        </a>
      </div>
    </div>

    {{-- ROW 2: workflow attention --}}
    <div class="dv2-card dv2-wf">
      <div class="dv2-wftop">
        <div class="dv2-wft"><span class="i"><svg width="18" height="18"><path d="M9 1l7 3v5c0 4-3 6-7 8-4-2-7-4-7-8V4z"/><path d="M6.5 9l1.8 1.8L12 7"/></svg></span>Workflow Attention</div>
        <div class="dv2-wfa">
          <a class="dv2-wfbtn" href="{{ url('/month-lifecycle') }}"><svg viewBox="0 0 16 16"><rect x="2" y="3" width="12" height="11" rx="2"/><path d="M2 6h12M5 1v3M11 1v3"/></svg>Open Month Cycle</a>
          <a class="dv2-wfbtn" href="{{ url('/dashboard-v2') }}"><svg viewBox="0 0 16 16"><path d="M2 8a6 6 0 0110-4M14 8a6 6 0 01-10 4M11 4h2.5V1.5M5 12H2.5v2.5"/></svg>Reload Dashboard</a>
          <div class="dv2-wfnext"><svg width="17" height="17"><path d="M6 4l4 4-4 4"/></svg></div>
        </div>
      </div>
      <div class="dv2-wfgrid">
        <a class="dv2-wfc mustfix" href="{{ url('/control-room/readiness') }}"><div class="top"><span class="ci"><svg viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8 4a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 4m.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/></svg></span><div><div class="n">{{ $kpis['must_fix'] ?? '0' }}</div><div class="l">Must Fix</div></div></div><span class="lk">View Details →</span></a>
        <a class="dv2-wfc review" href="{{ url('/control-room/readiness') }}"><div class="top"><span class="ci"><svg viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/></svg></span><div><div class="n">{{ $kpis['please_review'] ?? '0' }}</div><div class="l">Please Review</div></div></div><span class="lk">View Details →</span></a>
        <a class="dv2-wfc ready" href="{{ url('/control-room/generate') }}"><div class="top"><span class="ci"><svg viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg></span><div><div class="n">{{ $kpis['ready'] ?? '0' }}</div><div class="l">Ready</div></div></div><span class="lk">View Details →</span></a>
        <a class="dv2-wfc read" href="{{ url('/control-room/readings') }}"><div class="top"><span class="ci"><svg viewBox="0 0 16 16"><path d="M8 16a6 6 0 0 0 6-6c0-1.655-1.122-2.904-2.176-4.077C10.75 4.74 9.762 3.65 8.39.823a.5.5 0 0 0-.78 0C6.24 3.65 5.25 4.74 4.176 5.923 3.122 7.096 2 8.345 2 10a6 6 0 0 0 6 6"/></svg></span><div><div class="n">{{ $kpis['missing_readings'] ?? '0' }}</div><div class="l">Missing Readings</div></div></div><span class="lk">View Details →</span></a>
        <a class="dv2-wfc rooms" href="{{ url('/control-room/rooms') }}"><div class="top"><span class="ci"><svg viewBox="0 0 16 16"><path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z"/></svg></span><div><div class="n">{{ $kpis['missing_rooms'] ?? '0' }}</div><div class="l">Missing Rooms</div></div></div><span class="lk">View Details →</span></a>
        <a class="dv2-wfc rate" href="{{ url('/rates') }}"><div class="top"><span class="ci"><span class="rs-glyph rs-small">Rs</span></span><div><div class="n">{{ $kpis['rate_missing'] ?? '0' }}</div><div class="l">Rate Missing</div></div></div><span class="lk">View Details →</span></a>
      </div>
    </div>

    {{-- ROW 3: KPI --}}
    <div class="dv2-kpi">
      <a class="dv2-kc" href="{{ url('/employees') }}"><div class="hh"><div class="ki b1"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg></div><div class="hr"><div class="ttl">Employees Billed</div><div class="num">{{ $kpis['employees_billed'] ?? '0' }}</div><div class="mt">This Month</div></div></div><svg class="spark" viewBox="0 0 240 44" preserveAspectRatio="none" aria-hidden="true"><defs><linearGradient id="sparkBlueFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#2563EB" stop-opacity=".30"/><stop offset="100%" stop-color="#2563EB" stop-opacity="0"/></linearGradient></defs><path d="M0,40 L0,25 L30,21 L60,23 L90,15 L120,17 L150,10 L180,13 L210,6 L240,9 L240,40 Z" fill="url(#sparkBlueFill)" stroke="none"/><polyline points="0,25 30,21 60,23 90,15 120,17 150,10 180,13 210,6 240,9" fill="none" stroke="#2563EB" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
      <a class="dv2-kc" href="{{ url('/control-room/export') }}"><div class="hh"><div class="ki b2"><span class="rs-glyph">Rs</span></div><div class="hr"><div class="ttl">Total Billed</div><div class="num sm">Rs. {{ number_format($kpis['total_billed'] ?? 0) }}</div><div class="mt">This Month</div></div></div><svg class="spark" viewBox="0 0 240 44" preserveAspectRatio="none" aria-hidden="true"><defs><linearGradient id="sparkGreenFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#16A34A" stop-opacity=".30"/><stop offset="100%" stop-color="#16A34A" stop-opacity="0"/></linearGradient></defs><path d="M0,40 L0,26 L30,22 L60,18 L90,20 L120,13 L150,15 L180,8 L210,10 L240,4 L240,40 Z" fill="url(#sparkGreenFill)" stroke="none"/><polyline points="0,26 30,22 60,18 90,20 120,13 150,15 180,8 210,10 240,4" fill="none" stroke="#16A34A" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
      <a class="dv2-kc" href="{{ url('/family/details') }}"><div class="hh"><div class="ki b3"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg></div><div class="hr"><div class="ttl">Family Members</div><div class="num">{{ count($familyRows ?? []) ?: ($kpis['family_members'] ?? '0') }}</div><div class="mt">Total Registered</div></div></div><svg class="spark" viewBox="0 0 240 44" preserveAspectRatio="none" aria-hidden="true"><defs><linearGradient id="sparkPurpleFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#7C3AED" stop-opacity=".30"/><stop offset="100%" stop-color="#7C3AED" stop-opacity="0"/></linearGradient></defs><path d="M0,40 L0,20 L30,18 L60,14 L90,16 L120,11 L150,13 L180,9 L210,11 L240,6 L240,40 Z" fill="url(#sparkPurpleFill)" stroke="none"/><polyline points="0,20 30,18 60,14 90,16 120,11 150,13 180,9 210,11 240,6" fill="none" stroke="#7C3AED" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
      <a class="dv2-kc" href="{{ url('/reports/van') }}"><div class="hh"><div class="ki b4"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2.2" y="9" width="1.8" height="3" rx=".6" fill="#1F2937"/><rect x="20" y="9" width="1.8" height="3" rx=".6" fill="#1F2937"/><rect x="4" y="3.5" width="16" height="15.5" rx="3" fill="#FACC15"/><rect x="4" y="3.5" width="16" height="3.4" rx="3" fill="#FDE047"/><rect x="6" y="7" width="12" height="4.3" rx="1.4" fill="#3B82F6"/><rect x="6" y="7" width="12" height="2" rx="1.2" fill="#60A5FA"/><rect x="7.5" y="13" width="9" height="2.4" rx="1" fill="#111827"/><line x1="9.2" y1="13" x2="9.2" y2="15.4" stroke="#374151" stroke-width=".6"/><line x1="12" y1="13" x2="12" y2="15.4" stroke="#374151" stroke-width=".6"/><line x1="14.8" y1="13" x2="14.8" y2="15.4" stroke="#374151" stroke-width=".6"/><circle cx="6.6" cy="17" r="1.15" fill="#FFFFFF"/><circle cx="17.4" cy="17" r="1.15" fill="#FFFFFF"/><circle cx="7.5" cy="19.3" r="1.7" fill="#1F2937"/><circle cx="16.5" cy="19.3" r="1.7" fill="#1F2937"/><circle cx="7.5" cy="19.3" r=".7" fill="#9CA3AF"/><circle cx="16.5" cy="19.3" r=".7" fill="#9CA3AF"/></svg></div><div class="hr"><div class="ttl">Van Kids</div><div class="num">{{ count($vanRows ?? []) ?: ($kpis['van_kids'] ?? '0') }}</div><div class="mt">School Van</div></div></div><svg class="spark" viewBox="0 0 240 44" preserveAspectRatio="none" aria-hidden="true"><defs><linearGradient id="sparkOrangeFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#EA580C" stop-opacity=".30"/><stop offset="100%" stop-color="#EA580C" stop-opacity="0"/></linearGradient></defs><path d="M0,40 L0,18 L30,22 L60,16 L90,19 L120,13 L150,16 L180,11 L210,14 L240,9 L240,40 Z" fill="url(#sparkOrangeFill)" stroke="none"/><polyline points="0,18 30,22 60,16 90,19 120,13 150,16 180,11 210,14 240,9" fill="none" stroke="#EA580C" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
      <a class="dv2-kc" href="{{ url('/housing-rooms') }}"><div class="hh"><div class="ki b5"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z"/><path d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293z"/></svg></div><div class="hr"><div class="ttl">Total Rooms</div><div class="num">{{ $kpis['total_units'] ?? '0' }}</div><div class="mt">All Rooms</div></div></div><svg class="spark" viewBox="0 0 240 44" preserveAspectRatio="none" aria-hidden="true"><defs><linearGradient id="sparkTealFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#0D9488" stop-opacity=".30"/><stop offset="100%" stop-color="#0D9488" stop-opacity="0"/></linearGradient></defs><path d="M0,40 L0,19 L30,17 L60,19 L90,15 L120,17 L150,13 L180,15 L210,11 L240,13 L240,40 Z" fill="url(#sparkTealFill)" stroke="none"/><polyline points="0,19 30,17 60,19 90,15 120,17 150,13 180,15 210,11 240,13" fill="none" stroke="#0D9488" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
      <a class="dv2-kc" href="{{ url('/housing-rooms') }}"><div class="hh"><div class="ki b6"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z"/><path d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293z"/></svg></div><div class="hr"><div class="ttl">House Units</div><div class="num">{{ $kpis['house_units'] ?? '0' }}</div><div class="mt">Residential</div></div></div><svg class="spark" viewBox="0 0 240 44" preserveAspectRatio="none" aria-hidden="true"><defs><linearGradient id="sparkHouseBlueFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#2563EB" stop-opacity=".30"/><stop offset="100%" stop-color="#2563EB" stop-opacity="0"/></linearGradient></defs><path d="M0,40 L0,20 L30,18 L60,20 L90,16 L120,18 L150,14 L180,16 L210,12 L240,14 L240,40 Z" fill="url(#sparkHouseBlueFill)" stroke="none"/><polyline points="0,20 30,18 60,20 90,16 120,18 150,14 180,16 210,12 240,14" fill="none" stroke="#2563EB" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
      <a class="dv2-kc" href="{{ url('/people-residency') }}"><div class="hh"><div class="ki b7"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg></div><div class="hr"><div class="ttl">Bachelor Units</div><div class="num">{{ $kpis['bachelor_units'] ?? '0' }}</div><div class="mt">Units</div></div></div><svg class="spark" viewBox="0 0 240 44" preserveAspectRatio="none" aria-hidden="true"><defs><linearGradient id="sparkAmberFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#D97706" stop-opacity=".30"/><stop offset="100%" stop-color="#D97706" stop-opacity="0"/></linearGradient></defs><path d="M0,40 L0,17 L30,20 L60,17 L90,20 L120,15 L150,18 L180,13 L210,16 L240,11 L240,40 Z" fill="url(#sparkAmberFill)" stroke="none"/><polyline points="0,17 30,20 60,17 90,20 120,15 150,18 180,13 210,16 240,11" fill="none" stroke="#D97706" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
      <a class="dv2-kc" href="{{ url('/people-residency') }}"><div class="hh"><div class="ki b8"><svg viewBox="0 0 16 16" aria-hidden="true"><path d="M3 0a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h3v-3.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V16h3a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1zm1 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM4.5 5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5M4 8.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm5.5-5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5M10 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm.5 2.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5"/></svg></div><div class="hr"><div class="ttl">Hostel Units</div><div class="num">{{ $kpis['hostel_units'] ?? '0' }}</div><div class="mt">Hostel</div></div></div><svg class="spark" viewBox="0 0 240 44" preserveAspectRatio="none" aria-hidden="true"><defs><linearGradient id="sparkPinkFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#DB2777" stop-opacity=".30"/><stop offset="100%" stop-color="#DB2777" stop-opacity="0"/></linearGradient></defs><path d="M0,40 L0,16 L30,19 L60,14 L90,17 L120,12 L150,15 L180,10 L210,13 L240,8 L240,40 Z" fill="url(#sparkPinkFill)" stroke="none"/><polyline points="0,16 30,19 60,14 90,17 120,12 150,15 180,10 210,13 240,8" fill="none" stroke="#DB2777" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
    </div>

    {{-- ROW 4: lower 3-col --}}
    <div class="dv2-lower">
      {{-- Quick Actions --}}
      <div class="dv2-card dv2-pnl">
        <div class="dv2-pnlh"><div class="t"><svg viewBox="0 0 18 18"><path d="M10 1L3 10h4l-1 7 8-10h-5z"/></svg>Quick Actions</div></div>
        <div class="dv2-qa">
          <a class="dv2-qt" href="{{ url('/unit-directory') }}"><div class="qi" style="background:linear-gradient(135deg,#3B82F6,#1E3A8A)"><svg viewBox="0 0 16 16"><path d="M3 0a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h3v-3.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5V16h3a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1zm1 2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zM4.5 5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5M4 8.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm5.5-5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5M10 5.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm.5 2.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5"/></svg></div><div class="qn">Unit Directory</div></a>
          <a class="dv2-qt" href="{{ url('/people-residency') }}"><div class="qi" style="background:linear-gradient(135deg,#22C55E,#16A34A)"><svg viewBox="0 0 16 16"><path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/></svg></div><div class="qn">Employee Profile</div></a>
          <a class="dv2-qt" href="{{ url('/people-residency') }}"><div class="qi" style="background:linear-gradient(135deg,#A855F7,#7C3AED)"><svg viewBox="0 0 16 16"><path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/><path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/></svg></div><div class="qn">Add Employee</div></a>
          <a class="dv2-qt" href="{{ url('/reports/employee-statement') }}"><div class="qi" style="background:linear-gradient(135deg,#FB923C,#EA580C)"><svg viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M4.5 9a.5.5 0 0 1 0-1h7a.5.5 0 0 1 0 1zM4 10.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m.5 2.5a.5.5 0 0 1 0-1h4a.5.5 0 0 1 0 1z"/></svg></div><div class="qn">Statement</div></a>
          <a class="dv2-qt" href="{{ url('/housing-occupancy') }}"><div class="qi" style="background:linear-gradient(135deg,#2DD4BF,#0D9488)"><svg viewBox="0 0 16 16"><path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z"/></svg></div><div class="qn">Residence</div></a>
          <a class="dv2-qt" href="{{ url('/reporting') }}"><div class="qi" style="background:linear-gradient(135deg,#F472B6,#DB2777)"><svg viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg></div><div class="qn">Family</div></a>
          <a class="dv2-qt" href="{{ url('/transport') }}"><div class="qi" style="background:linear-gradient(135deg,#60A5FA,#2563EB)"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2.2" y="9" width="1.8" height="3" rx=".6" fill="#1F2937"/><rect x="20" y="9" width="1.8" height="3" rx=".6" fill="#1F2937"/><rect x="4" y="3.5" width="16" height="15.5" rx="3" fill="#FACC15"/><rect x="4" y="3.5" width="16" height="3.4" rx="3" fill="#FDE047"/><rect x="6" y="7" width="12" height="4.3" rx="1.4" fill="#3B82F6"/><rect x="6" y="7" width="12" height="2" rx="1.2" fill="#60A5FA"/><rect x="7.5" y="13" width="9" height="2.4" rx="1" fill="#111827"/><line x1="9.2" y1="13" x2="9.2" y2="15.4" stroke="#374151" stroke-width=".6"/><line x1="12" y1="13" x2="12" y2="15.4" stroke="#374151" stroke-width=".6"/><line x1="14.8" y1="13" x2="14.8" y2="15.4" stroke="#374151" stroke-width=".6"/><circle cx="6.6" cy="17" r="1.15" fill="#FFFFFF"/><circle cx="17.4" cy="17" r="1.15" fill="#FFFFFF"/><circle cx="7.5" cy="19.3" r="1.7" fill="#1F2937"/><circle cx="16.5" cy="19.3" r="1.7" fill="#1F2937"/><circle cx="7.5" cy="19.3" r=".7" fill="#9CA3AF"/><circle cx="16.5" cy="19.3" r=".7" fill="#9CA3AF"/></svg></div><div class="qn">School Van</div></a>
          <a class="dv2-qt" href="{{ url('/meters-readings/readings') }}"><div class="qi" style="background:linear-gradient(135deg,#34D399,#059669)"><svg viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4M3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707M2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10m9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5m.754-4.246a.39.39 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.39.39 0 0 0-.029-.518z"/><path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.298-1.785 1.886-3.119 1.886H3.572c-1.334 0-2.677-.588-3.119-1.886A8 8 0 0 1 0 10m8-7a7 7 0 0 0-6.603 9.329c.203.59.62.871 1.18.871h10.846c.56 0 .977-.282 1.18-.872A7 7 0 0 0 8 3"/></svg></div><div class="qn">Meter Readings</div></a>
          <a class="dv2-qt" href="{{ url('/meters-readings/readings') }}"><div class="qi" style="background:linear-gradient(135deg,#FBBF24,#D97706)"><svg viewBox="0 0 16 16"><path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2m-5.146-5.146-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708.708"/></svg></div><div class="qn">Active Days</div></a>
          <a class="dv2-qt" href="{{ url('/rates') }}"><div class="qi" style="background:linear-gradient(135deg,#A855F7,#7C3AED)"><svg viewBox="0 0 16 16"><path d="M13.442 2.558a.625.625 0 0 1 0 .884l-10 10a.625.625 0 1 1-.884-.884l10-10a.625.625 0 0 1 .884 0M4.5 6a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m0-1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1m7 6a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m0-1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/></svg></div><div class="qn">Monthly Rates</div></a>
        </div>
        <div class="dv2-qafoot"><a href="{{ url('/dashboard-v2') }}">View All Actions →</a></div>
      </div>

      {{-- Resident Type --}}
      <div class="dv2-card dv2-pnl">
        <div class="dv2-pnlh"><div class="t"><svg viewBox="0 0 18 18"><circle cx="9" cy="9" r="7"/><path d="M9 2v7l5 3"/></svg>Resident Type Overview</div></div>
        <div class="dv2-donutw">
          <div class="dv2-donut">
            <svg viewBox="0 0 42 42" width="140" height="140">
              <circle cx="21" cy="21" r="15.9" fill="none" stroke="#EEF3FA" stroke-width="6.5"/>
              <circle cx="21" cy="21" r="15.9" fill="none" stroke="#2563EB" stroke-width="6.5" stroke-dasharray="43 57" transform="rotate(-90 21 21)"/>
              <circle cx="21" cy="21" r="15.9" fill="none" stroke="#F59E0B" stroke-width="6.5" stroke-dasharray="29 71" stroke-dashoffset="-43" transform="rotate(-90 21 21)"/>
              <circle cx="21" cy="21" r="15.9" fill="none" stroke="#DB2777" stroke-width="6.5" stroke-dasharray="21 79" stroke-dashoffset="-72" transform="rotate(-90 21 21)"/>
              <circle cx="21" cy="21" r="15.9" fill="none" stroke="#7C3AED" stroke-width="6.5" stroke-dasharray="5 95" stroke-dashoffset="-93" transform="rotate(-90 21 21)"/>
              <circle cx="21" cy="21" r="15.9" fill="none" stroke="#94A3B8" stroke-width="6.5" stroke-dasharray="2 98" stroke-dashoffset="-98" transform="rotate(-90 21 21)"/>
            </svg>
            <div class="ct"><div class="n">{{ $kpis['total_units'] ?? '0' }}</div><div class="l">Total Units</div></div>
          </div>
          <div class="dv2-lg">
            <div class="dv2-lgr"><span class="sw" style="background:#2563EB"></span><span class="nm">House Units</span><span class="vl">{{ $kpis['house_units'] ?? '0' }}</span></div>
            <div class="dv2-lgr"><span class="sw" style="background:#F59E0B"></span><span class="nm">Bachelor Units</span><span class="vl">{{ $kpis['bachelor_units'] ?? '0' }}</span></div>
            <div class="dv2-lgr"><span class="sw" style="background:#DB2777"></span><span class="nm">Hostel Units</span><span class="vl">{{ $kpis['hostel_units'] ?? '0' }}</span></div>
            <div class="dv2-lgr"><span class="sw" style="background:#7C3AED"></span><span class="nm">Admin Colonies</span><span class="vl">{{ $kpis['container_units'] ?? '0' }}</span></div>
            <div class="dv2-lgr"><span class="sw" style="background:#94A3B8"></span><span class="nm">Uncategorized</span><span class="vl">{{ $kpis['uncategorized_units'] ?? '0' }}</span></div>
          </div>
        </div>
        <div class="dv2-donutfoot"><a href="{{ url('/housing-rooms') }}">View All Units →</a></div>
      </div>

      {{-- Recent Activity --}}
      <div class="dv2-card dv2-pnl">
        <div class="dv2-pnlh"><div class="t"><svg viewBox="0 0 18 18"><rect x="2" y="3" width="14" height="13" rx="2"/><path d="M2 6h14M5 1v3M11 1v3"/></svg>Recent Activity</div><a class="va" href="{{ url('/control-room/export') }}">View All →</a></div>
        <div class="dv2-acts">
          <div class="dv2-act"><span class="ai" style="background:var(--bluec-bg);color:var(--blue)"><svg width="17" height="17"><path d="M2 4h13v9H2z"/><path d="M2 4l6.5 5L15 4"/></svg></span><div><div class="t1">Last Bill Reference</div><div class="t2">{{ $kpis['last_bill_ref'] ?? '—' }}</div></div><div class="rt"><span class="tm">{{ $kpis['last_bill_time'] ?? '—' }}</span><span class="st" style="background:#16A34A"><svg width="12" height="12"><path d="M2 6l3 3 5-6"/></svg></span></div></div>
          <div class="dv2-act"><span class="ai" style="background:var(--bluec-bg);color:var(--blue)"><svg width="17" height="17"><path d="M2.5 15V7l6.5-4 6.5 4v8M6 15v-4h6v4"/></svg></span><div><div class="t1">Last Generation Status</div><div class="t2">{{ $kpis['last_gen_status'] ?? '—' }}</div></div><div class="rt"><span class="tm">{{ $kpis['last_gen_time'] ?? '—' }}</span><span class="st" style="background:#2563EB"><svg width="12" height="12"><path d="M1 6s2-3 5-3 5 3 5 3-2 3-5 3-5-3-5-3z"/><circle cx="6" cy="6" r="1.4"/></svg></span></div></div>
          <div class="dv2-act"><span class="ai" style="background:var(--green-bg);color:var(--green)"><svg width="17" height="17"><circle cx="8.5" cy="8.5" r="7"/><path d="M8.5 4.5v4l2.5 1.5"/></svg></span><div><div class="t1">Last Reading Update</div><div class="t2">{{ $kpis['last_reading'] ?? '—' }}</div></div><div class="rt"><span class="tm">{{ $kpis['last_reading_time'] ?? '—' }}</span><span class="st" style="background:#16A34A"><svg width="12" height="12"><path d="M2 6l3 3 5-6"/></svg></span></div></div>
          <div class="dv2-act"><span class="ai" style="background:var(--purple-bg);color:var(--purple)"><svg width="17" height="17"><path d="M8.5 2v8M5 6.5l3.5 3 3.5-3M3 14h11"/></svg></span><div><div class="t1">Last Active-Days Upload</div><div class="t2">{{ $kpis['last_upload'] ?? '—' }}</div></div><div class="rt"><span class="tm">{{ $kpis['last_upload_time'] ?? '—' }}</span><span class="st" style="background:#7C3AED"><svg width="12" height="12"><path d="M6 2v6M3.5 5.5L6 8l2.5-2.5"/></svg></span></div></div>
          <div class="dv2-act"><span class="ai" style="background:var(--bluec-bg);color:var(--blue)"><svg width="17" height="17"><path d="M8.5 10V2M5 5.5L8.5 9 12 5.5M3 14h11"/></svg></span><div><div class="t1">Last Export</div><div class="t2">{{ $kpis['last_export'] ?? '—' }}</div></div><div class="rt"><span class="tm">{{ $kpis['last_export_time'] ?? '—' }}</span><span class="st" style="background:#2563EB"><svg width="12" height="12"><path d="M6 2v6M3.5 5.5L6 8l2.5-2.5"/></svg></span></div></div>
          <div class="dv2-act"><span class="ai" style="background:var(--green-bg);color:var(--green)"><svg width="17" height="17"><circle cx="8.5" cy="6" r="2.8"/><path d="M3 14c0-3 2.5-5 5.5-5s5.5 2 5.5 5"/></svg></span><div><div class="t1">Last Employee Update</div><div class="t2">{{ $kpis['last_emp_update'] ?? '—' }}</div></div><div class="rt"><span class="tm">{{ $kpis['last_emp_time'] ?? '—' }}</span><span class="st" style="background:#16A34A"><svg width="12" height="12"><path d="M2 6l3 3 5-6"/></svg></span></div></div>
        </div>
      </div>
    </div>

    {{-- ROW 5: reports --}}
    <div class="dv2-card dv2-reps">
      <div class="rh"><svg viewBox="0 0 18 18"><path d="M3 15V8M9 15V3M15 15v-5"/></svg>Reports Shortcuts</div>
      <div class="rg">
        <a class="dv2-rep r1" href="{{ url('/reports/monthly-summary') }}"><span class="rpi"><svg viewBox="0 0 16 16"><path d="M1 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm5-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1z"/></svg></span>Monthly Summary</a>
        <a class="dv2-rep r2" href="{{ url('/reports/employee-bill-summary') }}"><span class="rpi"><svg viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5"/></svg></span>Employee Bill Summary</a>
        <a class="dv2-rep r3" href="{{ url('/reports/reconciliation') }}"><span class="rpi"><svg viewBox="0 0 16 16"><path d="M6 1v3H1V1zM1 0a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1zm14 12v3h-5v-3zm-5-1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1zM6 8v7H1V8zM1 7a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1zm14-6v7h-5V1zm-5-1a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1z"/></svg></span>Reconciliation Report</a>
        <a class="dv2-rep r4" href="{{ url('/reports/recovery') }}"><span class="rpi"><span class="rs-glyph rs-report">Rs</span></span>Recovery Report</a>
        <a class="dv2-rep r5" href="{{ url('/reports/van') }}"><span class="rpi"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2.2" y="9" width="1.8" height="3" rx=".6" fill="#1F2937"/><rect x="20" y="9" width="1.8" height="3" rx=".6" fill="#1F2937"/><rect x="4" y="3.5" width="16" height="15.5" rx="3" fill="#FACC15"/><rect x="4" y="3.5" width="16" height="3.4" rx="3" fill="#FDE047"/><rect x="6" y="7" width="12" height="4.3" rx="1.4" fill="#3B82F6"/><rect x="6" y="7" width="12" height="2" rx="1.2" fill="#60A5FA"/><rect x="7.5" y="13" width="9" height="2.4" rx="1" fill="#111827"/><line x1="9.2" y1="13" x2="9.2" y2="15.4" stroke="#374151" stroke-width=".6"/><line x1="12" y1="13" x2="12" y2="15.4" stroke="#374151" stroke-width=".6"/><line x1="14.8" y1="13" x2="14.8" y2="15.4" stroke="#374151" stroke-width=".6"/><circle cx="6.6" cy="17" r="1.15" fill="#FFFFFF"/><circle cx="17.4" cy="17" r="1.15" fill="#FFFFFF"/><circle cx="7.5" cy="19.3" r="1.7" fill="#1F2937"/><circle cx="16.5" cy="19.3" r="1.7" fill="#1F2937"/><circle cx="7.5" cy="19.3" r=".7" fill="#9CA3AF"/><circle cx="16.5" cy="19.3" r=".7" fill="#9CA3AF"/></svg></span>Van Report</a>
        <a class="dv2-rep more" href="{{ url('/reporting') }}"><span class="rpi"><svg viewBox="0 0 16 16"><path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/></svg></span>More Reports ···</a>
      </div>
    </div>

  </main>
</div>
</body>
</html>
