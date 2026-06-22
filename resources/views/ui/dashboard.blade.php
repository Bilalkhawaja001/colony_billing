@extends('layouts.app')
@section('page_title','Billing Command Dashboard')
@section('content')
@php
    $monthCycleRaw = trim((string)($monthCycle ?? request('month_cycle', '')));
    $hasPeriod = $monthCycleRaw !== '';
    $mc = urlencode($monthCycleRaw);
    $monthDate = null;
    $cycleStart = null;
    $cycleEnd = null;

    if (preg_match('/^(\d{2})-(\d{4})$/', $monthCycleRaw, $m)) {
        $monthDate = $m[2].'-'.$m[1].'-01';
    }

    $safeCount = function (string $table, ?callable $scope = null): int {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                return 0;
            }
            $query = \Illuminate\Support\Facades\DB::table($table);
            if ($scope) {
                $query = $scope($query);
            }
            return (int) $query->count();
        } catch (\Throwable $e) {
            return 0;
        }
    };

    try {
        if ($hasPeriod && \Illuminate\Support\Facades\Schema::hasTable('util_month_cycle')) {
            $cycle = \Illuminate\Support\Facades\DB::table('util_month_cycle')
                ->where('month_cycle', $monthCycleRaw)
                ->first();

            if ($cycle) {
                $cycleStart = $cycle->cycle_start_date ?? null;
                $cycleEnd = $cycle->cycle_end_date ?? null;
            }
        }

        if ((!$cycleStart || !$cycleEnd) && $monthDate) {
            $cycleStart = $monthDate;
            $cycleEnd = date('Y-m-t', strtotime($monthDate));
        }

        $readingsCount = 0;
        if ($cycleStart && $cycleEnd) {
            $readingsCount = $safeCount('electric_v1_readings', function ($q) use ($cycleStart, $cycleEnd) {
                return $q->whereDate('cycle_start_date', $cycleStart)
                    ->whereDate('cycle_end_date', $cycleEnd)
                    ->where(function ($qq) {
                        $qq->whereNull('reading_status')
                           ->orWhereNotIn('reading_status', ['INVALID', 'ERROR', 'REJECTED']);
                    });
            });
        }

        $attendanceCount = 0;
        if ($monthDate) {
            $attendanceCount = $safeCount('electric_active_days_monthly', function ($q) use ($monthDate) {
                return $q->whereDate('billing_month_date', $monthDate);
            });
        }
        if ($attendanceCount <= 0 && $cycleStart && $cycleEnd) {
            $attendanceCount = $safeCount('electric_v1_hr_attendance', function ($q) use ($cycleStart, $cycleEnd) {
                return $q->whereDate('cycle_start_date', $cycleStart)
                    ->whereDate('cycle_end_date', $cycleEnd);
            });
        }

        $allowanceCount = $safeCount('electric_v1_room_allowance', function ($q) {
            return $q->where('is_active', 1);
        });
        if ($allowanceCount <= 0) {
            $allowanceCount = $safeCount('electric_v1_allowance');
        }

        $ratesCount = 0;
        if ($hasPeriod) {
            $ratesCount = $safeCount('util_monthly_rates_config', function ($q) use ($monthCycleRaw) {
                return $q->where('month_cycle', $monthCycleRaw)->where('elec_rate', '>', 0);
            });
        }

        $reconciliationCount = 0;
        if ($hasPeriod) {
            $reconciliationCount = $safeCount('util_billing_line', function ($q) use ($monthCycleRaw) {
                return $q->where('month_cycle', $monthCycleRaw);
            });
            if ($reconciliationCount <= 0) {
                $reconciliationCount = $safeCount('electric_v1_output_employee_unit_drilldown', function ($q) use ($monthCycleRaw) {
                    return $q->where('month_cycle', $monthCycleRaw);
                });
            }
        }
        if ($reconciliationCount <= 0 && $cycleStart && $cycleEnd) {
            $reconciliationCount = $safeCount('electric_v1_output_employee_final', function ($q) use ($cycleStart, $cycleEnd) {
                return $q->whereDate('cycle_start_date', $cycleStart)
                    ->whereDate('cycle_end_date', $cycleEnd);
            });
        }

        $readySteps = [
            ['key' => 'reading', 'label' => 'Reading', 'count' => $readingsCount, 'href' => '/meters-readings/readings?month_cycle='.$mc],
            ['key' => 'attendance', 'label' => 'Attendance', 'count' => $attendanceCount, 'href' => '/active-days-monthly?month_cycle='.$mc],
            ['key' => 'allowance', 'label' => 'House allowance', 'count' => $allowanceCount, 'href' => '/imports-validation?month_cycle='.$mc],
            ['key' => 'rates', 'label' => 'Rates', 'count' => $ratesCount, 'href' => '/rates?month_cycle='.$mc],
            ['key' => 'reconciliation', 'label' => 'Reconciliation', 'count' => $reconciliationCount, 'href' => '/reporting?month_cycle='.$mc],
        ];

        foreach ($readySteps as $i => $step) {
            $readySteps[$i]['done'] = $hasPeriod && ((int) $step['count'] > 0);
        }

        $doneCount = count(array_filter($readySteps, fn($step) => $step['done']));
        $readyTotal = count($readySteps);
        $readyAll = $doneCount === $readyTotal;
        $firstPending = collect($readySteps)->first(fn($step) => !$step['done']);
        $readyProgress = $readyTotal > 1 ? max(0, min(100, (($doneCount - 1) / ($readyTotal - 1)) * 100)) : 0;
    } catch (\Throwable $e) {
        $readySteps = [
            ['label' => 'Reading', 'count' => 0, 'href' => '/meters-readings/readings?month_cycle='.$mc, 'done' => false],
            ['label' => 'Attendance', 'count' => 0, 'href' => '/active-days-monthly?month_cycle='.$mc, 'done' => false],
            ['label' => 'House allowance', 'count' => 0, 'href' => '/imports-validation?month_cycle='.$mc, 'done' => false],
            ['label' => 'Rates', 'count' => 0, 'href' => '/rates?month_cycle='.$mc, 'done' => false],
            ['label' => 'Reconciliation', 'count' => 0, 'href' => '/reporting?month_cycle='.$mc, 'done' => false],
        ];
        $doneCount = 0;
        $readyTotal = 5;
        $readyAll = false;
        $firstPending = ['label' => 'Readiness check', 'href' => '/ui/dashboard?month_cycle='.$mc];
        $readyProgress = 0;
    }

    $familyPreview = collect($familyRows ?? [])->take(8);
    $vanPreview = collect($vanRows ?? [])->take(8);
@endphp

<style>
    .dashboard-shell{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px}
    .dash-hero{grid-column:span 12;background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 58%,#0f766e 100%);color:#fff;border:0;border-radius:18px;padding:22px;box-shadow:0 18px 45px rgba(15,23,42,.22)}
    .dash-hero-top{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap}
    .dash-eyebrow{font-size:12px;text-transform:uppercase;letter-spacing:.08em;opacity:.78;font-weight:800}
    .dash-title{font-size:30px;line-height:1.05;font-weight:900;margin:6px 0 6px;letter-spacing:-.04em}
    .dash-subtitle{margin:0;color:#dbeafe;font-size:13px;font-weight:600}
    .dash-period{display:flex;flex-direction:column;align-items:flex-end;gap:4px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);border-radius:16px;padding:12px 14px;min-width:170px}
    .dash-period span{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#bfdbfe;font-weight:800}
    .dash-period strong{font-size:22px;letter-spacing:-.03em}
    .dash-form{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:18px}
    .dash-form input{height:38px;border-radius:10px;border:1px solid rgba(255,255,255,.24);background:rgba(255,255,255,.96);padding:0 12px;font-weight:800;min-width:160px}
    .dash-form .btn{height:38px;display:inline-flex;align-items:center}
    .progress-card{grid-column:span 12;border-radius:18px;padding:22px;border:1px solid rgba(15,23,42,.08);box-shadow:0 18px 45px rgba(15,23,42,.08);background:linear-gradient(180deg,#fff 0%,#fbfdff 100%)}
    .progress-head{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:18px;flex-wrap:wrap}
    .progress-head h3{font-size:22px;margin:3px 0 0;letter-spacing:-.03em}
    .status-pill{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:7px 10px;font-size:12px;font-weight:900;border:1px solid #fed7aa;background:#fff7ed;color:#c2410c}
    .status-pill.ready{border-color:#bbf7d0;background:#dcfce7;color:#166534}
    .status-pill.blocked{border-color:#fecaca;background:#fef2f2;color:#991b1b}
    .progress-track{position:relative;display:grid;grid-template-columns:repeat(5,minmax(110px,1fr));gap:8px;padding:22px 8px 14px;margin:6px 0 12px;--fill:0%}
    .progress-line,.progress-fill{position:absolute;top:42px;left:10%;right:10%;height:4px;border-radius:999px}
    .progress-line{background:#d8dee9}.progress-fill{right:auto;width:calc(var(--fill) * .8);background:linear-gradient(90deg,#16a34a,#22c55e);transition:width .25s ease}
    .progress-step{position:relative;z-index:2;min-width:0;display:flex;flex-direction:column;align-items:center;text-align:center;text-decoration:none;color:#0f172a}
    .step-node{width:44px;height:44px;border-radius:999px;display:flex;align-items:center;justify-content:center;background:#fff;border:3px solid #f97316;color:#ea580c;font-weight:900;font-size:18px;box-shadow:0 8px 22px rgba(15,23,42,.08)}
    .progress-step.done .step-node{background:#16a34a;border-color:#16a34a;color:#fff}.step-label{margin-top:12px;font-weight:900;font-size:14px;line-height:1.15;white-space:nowrap}.step-count{margin-top:5px;font-size:11px;font-weight:800;color:#64748b}
    .progress-footer{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 16px;border-radius:18px;background:#f8fafc;border:1px solid rgba(148,163,184,.22);flex-wrap:wrap}
    .progress-footer strong{display:block;font-size:14px;color:#0f172a}.progress-footer span{display:block;margin-top:3px;font-size:12px;color:#64748b;font-weight:700}
    .snapshot{grid-column:span 3}.panel{grid-column:span 6}.panel h4,.snapshot h4{margin:0 0 10px}.table-wrap{overflow:auto}.locked{opacity:.58;pointer-events:none}
    @media(max-width:1000px){.snapshot,.panel{grid-column:span 12}.progress-track{grid-template-columns:1fr;gap:12px;padding:8px 0}.progress-line,.progress-fill{display:none}.progress-step{flex-direction:row;justify-content:flex-start;text-align:left;gap:12px;padding:10px 12px;border-radius:16px;background:#f8fafc}.step-label,.step-count{margin-top:0}.dash-period{align-items:flex-start}}
</style>

<div class="dashboard-shell">
    <section class="dash-hero card">
        <div class="dash-hero-top">
            <div>
                <div class="dash-eyebrow">Billing Command Dashboard</div>
                <div class="dash-title">Month readiness console</div>
                <p class="dash-subtitle">Reading, attendance, allowance, rates aur reconciliation complete hotay hi tracker auto tick karega.</p>
            </div>
            <div class="dash-period">
                <span>Current Period</span>
                <strong>{{ $hasPeriod ? $monthCycleRaw : 'Not selected' }}</strong>
            </div>
        </div>
        <form method="get" action="/ui/dashboard" class="dash-form">
            <input name="month_cycle" value="{{ $monthCycleRaw }}" placeholder="MM-YYYY">
            <button class="btn btn-primary" type="submit">Reload Dashboard</button>
            @if($hasPeriod)
                <a class="btn" href="/ui/month-cycle?month_cycle={{ $mc }}">Month Cycle</a>
                <a class="btn" href="/ui/billing?month_cycle={{ $mc }}">Billing Workspace</a>
            @endif
        </form>
    </section>

    <section class="progress-card card">
        <div class="progress-head">
            <div>
                <div class="dash-eyebrow" style="color:#64748b">Auto Progress Tracker</div>
                <h3>Missing Data &amp; Blockers</h3>
            </div>
            @if($readyAll)
                <span class="status-pill ready">✓ Ready for Bill</span>
            @elseif(!$hasPeriod)
                <span class="status-pill blocked">No active period</span>
            @else
                <span class="status-pill">{{ $doneCount }}/{{ $readyTotal }} Complete</span>
            @endif
        </div>

        <div class="progress-track" style="--fill: {{ number_format($readyProgress, 2, '.', '') }}%;">
            <div class="progress-line"></div>
            <div class="progress-fill"></div>
            @foreach($readySteps as $step)
                <a class="progress-step {{ $step['done'] ? 'done' : 'pending' }}" href="{{ $step['href'] }}">
                    <span class="step-node">{{ $step['done'] ? '✓' : '!' }}</span>
                    <span class="step-label">{{ $step['label'] }}</span>
                    <span class="step-count">{{ (int) $step['count'] }} record{{ (int) $step['count'] === 1 ? '' : 's' }}</span>
                </a>
            @endforeach
        </div>

        <div class="progress-footer">
            @if(!$hasPeriod)
                <div><strong>Select month cycle</strong><span>Month cycle select karo taake readiness auto check ho.</span></div>
                <a class="btn btn-primary" href="/ui/month-cycle">Open Month Cycle</a>
            @elseif($readyAll)
                <div><strong>All checks complete</strong><span>Generate Bill button active hai.</span></div>
                <a class="btn btn-primary" href="/ui/billing?month_cycle={{ $mc }}">Generate / Review Bill</a>
            @else
                <div><strong>Next blocker: {{ $firstPending['label'] ?? 'Pending check' }}</strong><span>Ye step complete hotay hi circle auto fill + tick ho jayega.</span></div>
                <a class="btn btn-warning" href="{{ $firstPending['href'] ?? ('/ui/dashboard?month_cycle='.$mc) }}">Open Pending Step</a>
                <a class="btn locked" aria-disabled="true">Generate Bill Locked</a>
            @endif
        </div>
    </section>

    <section class="card snapshot">
        <div class="muted">Employees Billed</div>
        <div class="kpi">{{ $kpis['employees_billed'] ?? 0 }}</div>
        <span class="badge">Coverage</span>
    </section>
    <section class="card snapshot">
        <div class="muted">Total Billed</div>
        <div class="kpi">{{ number_format((float)($kpis['total_billed'] ?? 0), 2) }}</div>
        <span class="badge">Financial</span>
    </section>
    <section class="card snapshot">
        <div class="muted">Family Members</div>
        <div class="kpi">{{ $kpis['family_members'] ?? 0 }}</div>
        <span class="badge">Registry</span>
    </section>
    <section class="card snapshot">
        <div class="muted">Van Kids</div>
        <div class="kpi">{{ $kpis['van_kids'] ?? 0 }}</div>
        <span class="badge">Transport</span>
    </section>

    <section class="card panel">
        <h4>Family Members (Recent)</h4>
        <div class="table-wrap">
            <table>
                <tr><th>Employee ID</th><th>Name</th><th>Relation</th><th>Age</th></tr>
                @forelse($familyPreview as $row)
                    <tr>
                        <td>{{ $row->employee_id ?? '' }}</td>
                        <td>{{ $row->family_member_name ?? '' }}</td>
                        <td>{{ $row->relation ?? '' }}</td>
                        <td>{{ $row->age ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No family records found.</td></tr>
                @endforelse
            </table>
        </div>
    </section>

    <section class="card panel">
        <h4>Van Kids (Recent)</h4>
        <div class="table-wrap">
            <table>
                <tr><th>Employee ID</th><th>Child</th><th>School</th><th>Class</th><th>Amount</th></tr>
                @forelse($vanPreview as $row)
                    <tr>
                        <td>{{ $row->employee_id ?? '' }}</td>
                        <td>{{ $row->child_name ?? '' }}</td>
                        <td>{{ $row->school_name ?? '' }}</td>
                        <td>{{ $row->class_level ?? '' }}</td>
                        <td>{{ number_format((float)($row->amount ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">No van records found.</td></tr>
                @endforelse
            </table>
        </div>
    </section>
</div>
@endsection
