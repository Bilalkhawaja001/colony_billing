<header class="bc-topbar">
    <div>
        <div class="bc-kicker">Colony Billing</div>
        <h1>{{ $pageTitle ?? 'Control Room' }}</h1>
    </div>
    <div class="bc-top-actions">
        <a href="{{ route('billing.control.home') }}">Control Room</a>
        <a href="{{ route('billing.control.readiness') }}">Readiness</a>
        <a href="{{ route('billing.control.export') }}">Export</a>
    </div>
</header>
