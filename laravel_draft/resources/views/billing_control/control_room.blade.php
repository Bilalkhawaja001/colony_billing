@extends('billing_control.layout')

@section('content')
<section class="bc-panel">
    <form method="get" action="{{ route('billing.control.home') }}" class="bc-form">
        <input name="month_cycle" value="{{ request('month_cycle', $readiness['month'] ?? '') }}" placeholder="Month Cycle e.g. 2026-06">
        <button class="bc-btn" type="submit">Refresh Readiness</button>
    </form>
</section>

<section class="bc-grid">
    @include('billing_control.components.status-card', ['title' => 'Readiness', 'value' => ($readiness['isReady'] ?? false) ? 'Ready' : 'Blocked', 'note' => $readiness['mode'] ?? ''])
    @include('billing_control.components.status-card', ['title' => 'Month Cycle', 'value' => $readiness['stats']['month_cycle'] ?? '-', 'note' => ($readiness['stats']['cycle_start_date'] ?? '-') . ' to ' . ($readiness['stats']['cycle_end_date'] ?? '-')])
    @include('billing_control.components.status-card', ['title' => 'Active Employees', 'value' => $readiness['stats']['active_employees'] ?? '-', 'note' => 'employees_master'])
    @include('billing_control.components.status-card', ['title' => 'Active Meters', 'value' => $readiness['stats']['active_meters'] ?? '-', 'note' => 'util_meter_unit'])
</section>

<section class="bc-grid">
    @include('billing_control.components.status-card', ['title' => 'Readings', 'value' => $readiness['stats']['current_readings'] ?? '-', 'note' => 'cycle end date'])
    @include('billing_control.components.status-card', ['title' => 'Active Days', 'value' => $readiness['stats']['active_days_rows'] ?? '-', 'note' => 'attendance rows'])
    @include('billing_control.components.status-card', ['title' => 'Electric Rate', 'value' => $readiness['stats']['electric_rate'] ?? '-', 'note' => 'monthly rate'])
    @include('billing_control.components.status-card', ['title' => 'Latest Run', 'value' => $readiness['stats']['latest_run_status'] ?? '-', 'note' => $readiness['stats']['latest_run_id'] ?? '-'])
</section>

<section class="bc-panel">
    <h2>Readiness blockers</h2>
    <p class="bc-muted">Last checked: {{ $readiness['lastChecked'] ?? '-' }}</p>

    @forelse(($readiness['blockers'] ?? []) as $issue)
        @include('billing_control.components.issue-card', ['issue' => $issue])
    @empty
        <div class="bc-alert">No blockers found. Generate wiring is still separate Phase 1D.</div>
    @endforelse
</section>
@endsection
