@php
    $q = request()->query();
    $monthLabel = request('month_cycle', request('month', data_get($readiness ?? [], 'stats.month_cycle', now()->format('m-Y'))));
    $isReady = (bool) data_get($readiness ?? [], 'isReady', false);
    $blockerCount = count(data_get($readiness ?? [], 'blockers', []));
@endphp

<aside class="sidebar billing-center-nav premium-billing-nav" aria-label="Billing Center navigation">
    <div class="sidebar-label">Billing Center</div>

    <div class="nav-section-title">Active Month</div>
    <a class="nav-card nav-card-month {{ request()->routeIs('billing.control.home') ? 'is-active' : '' }}" href="{{ route('billing.control.home', $q) }}">
        <span class="nav-card-title">Billing Month</span>
        <span class="nav-card-status">@include('billing_control.components.month-label', ['value' => $monthLabel])</span>
    </a>

    <div class="nav-section-title">Workflow</div>

    <a class="nav-card {{ request()->routeIs('billing.control.readiness') ? 'is-active' : '' }}" href="{{ route('billing.control.readiness', $q) }}">
        <span class="nav-card-title">Check &amp; Fix Data</span>
        <span class="nav-card-status {{ $isReady ? 'is-ok' : 'is-warn' }}">{{ $isReady ? 'Ready for preview' : 'Must Fix: '.$blockerCount }}</span>
    </a>

    <a class="nav-card {{ request()->routeIs('billing.control.readings') || request()->routeIs('billing.control.rooms') ? 'is-active' : '' }}" href="{{ route('billing.control.readings', $q) }}">
        <span class="nav-card-title">Review Readings &amp; Rooms</span>
        <span class="nav-card-status">{{ $blockerCount ? $blockerCount.' Must Fix' : 'Data checked' }}</span>
    </a>

    <a class="nav-card {{ request()->routeIs('billing.control.generate') ? 'is-active' : '' }}" href="{{ route('billing.control.generate', $q) }}">
        <span class="nav-card-title">Preview &amp; Generate</span>
        <span class="nav-card-status {{ $isReady ? 'is-ok' : 'is-warn' }}">{{ $isReady ? 'Preview available' : 'Must Fix items first' }}</span>
    </a>

    <a class="nav-card {{ request()->routeIs('billing.control.export') || request()->routeIs('billing.control.runs.*') ? 'is-active' : '' }}" href="{{ route('billing.control.export', $q) }}">
        <span class="nav-card-title">Download &amp; Records</span>
        <span class="nav-card-status">Generated Bills / Billing Record</span>
    </a>
</aside>
