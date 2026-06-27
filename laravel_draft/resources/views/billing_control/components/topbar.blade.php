@php
    $monthLabel = request('month_cycle', request('month', data_get($readiness ?? [], 'stats.month_cycle', now()->format('m-Y'))));
    $monthPretty = $monthLabel;

    if (preg_match('/^(\d{2})-(\d{4})$/', (string) $monthLabel, $m)) {
        $dt = \DateTime::createFromFormat('!m-Y', $m[1].'-'.$m[2]);
        if ($dt) {
            $monthPretty = $dt->format('M Y');
        }
    } elseif (preg_match('/^(\d{4})-(\d{2})/', (string) $monthLabel, $m)) {
        $dt = \DateTime::createFromFormat('!Y-m', $m[1].'-'.$m[2]);
        if ($dt) {
            $monthPretty = $dt->format('M Y');
        }
    }

    $lastChecked = data_get($readiness ?? [], 'lastChecked', '—');
@endphp

<header class="topbar">
    <div class="brand">Colony Billing</div>
    <span class="badge-staging">SAFE MODE</span>
    <span class="month-pill">{{ $monthPretty }}</span>
    <div class="topbar-spacer"></div>
    <span class="topbar-muted">Last checked {{ $lastChecked }}</span>
</header>
