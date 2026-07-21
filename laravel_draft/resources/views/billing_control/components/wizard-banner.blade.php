@php
    $wzStep = $wzStep ?? null;   // 'readings' | 'attendance' | 'allowances' | 'rate' | 'occupancy'
    $wzMonth = request('month_cycle', request('month'));
    $wzData = null;
    try {
        $rs = app(\App\Services\Billing\ControlRoom\ReadinessService::class);
        $ctx = $rs->resolveCycleContext($wzMonth);
        $cs = $ctx['cycle_start_date'] ?? null;
        $ce = $ctx['cycle_end_date'] ?? null;
        $mc = $ctx['month_cycle'] ?? $wzMonth;
        if ($cs && $ce) {
            $all = (new \App\Services\Billing\ControlRoom\WizardStepService())->steps($cs, $ce, $mc);
            foreach ($all as $i => $s) {
                if ($s['key'] === $wzStep) { $wzData = $s + ['index' => $i, 'next' => $all[$i+1] ?? null]; }
            }
        }
    } catch (\Throwable $e) { $wzData = null; }
@endphp

@if($wzData)
<div style="font-family:Inter,sans-serif;display:flex;align-items:center;gap:14px;flex-wrap:wrap;padding:12px 16px;margin-bottom:16px;border-radius:10px;border:1px solid {{ $wzData['ok'] ? '#a7f3d0' : '#fde68a' }};background:{{ $wzData['ok'] ? '#ecfdf5' : '#fffbeb' }}">
    <a href="{{ route('billing.control.wizard', ['month_cycle' => $mc]) }}"
       style="font-size:13px;font-weight:600;color:#2563eb;text-decoration:none;white-space:nowrap">&larr; Billing Control</a>

    <div style="flex-grow:1;min-width:200px">
        <div style="font-size:13px;font-weight:700;color:{{ $wzData['ok'] ? '#047857' : '#b45309' }}">
            {{ $wzData['title'] }} — {{ $wzData['ok'] ? 'Clear' : $wzData['issues'].' issue(s)' }}
        </div>
        <div style="font-size:12px;color:#64748b">{{ $wzData['detail'] }}</div>
    </div>

    @if($wzData['ok'])
        @if($wzData['next'])
            <a href="{{ $wzData['next']['fix_url'] }}?month_cycle={{ $mc }}"
               style="padding:7px 16px;background:#10b981;color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap">Next: {{ $wzData['next']['title'] }} &rarr;</a>
        @else
            <a href="{{ route('billing.control.wizard', ['month_cycle' => $mc]) }}"
               style="padding:7px 16px;background:#10b981;color:#fff;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap">Back to Wizard &rarr;</a>
        @endif
    @endif
</div>
@endif
