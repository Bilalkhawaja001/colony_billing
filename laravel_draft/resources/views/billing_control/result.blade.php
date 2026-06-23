@extends('billing_control.layout')

@section('content')
<section class="bc-panel">
    <h2>{{ $pageTitle ?? 'Bill Result' }}</h2>
    <p class="bc-muted">Run: {{ $run }}</p>

    @if(!empty($dryRun))
        <div class="bc-alert">{{ $dryRun['message'] ?? 'Dry run completed.' }}</div>

        <div class="bc-grid">
            @include('billing_control.components.status-card', ['title' => 'Dry Run Status', 'value' => $dryRun['status'] ?? '-', 'note' => 'no DB write'])
            @include('billing_control.components.status-card', ['title' => 'Month Cycle', 'value' => $dryRun['month_cycle'] ?? '-', 'note' => ($dryRun['cycle_start_date'] ?? '-') . ' to ' . ($dryRun['cycle_end_date'] ?? '-')])
            @include('billing_control.components.status-card', ['title' => 'Employees', 'value' => $dryRun['active_employees'] ?? '-', 'note' => 'active'])
            @include('billing_control.components.status-card', ['title' => 'Meters', 'value' => $dryRun['active_meters'] ?? '-', 'note' => 'active electric'])
        </div>

        <section class="bc-panel">
            <h3>Dry run safety proof</h3>
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <thead><tr><th>Action</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach(($dryRun['safety'] ?? []) as $action => $status)
                        <tr><td>{{ $action }}</td><td>{{ $status }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bc-panel">
            <h3>Dry run counts</h3>
            <div class="bc-table-wrap">
                <table class="bc-table">
                    <tbody>
                        <tr><td>current_readings</td><td>{{ $dryRun['current_readings'] ?? '-' }}</td></tr>
                        <tr><td>active_days_rows</td><td>{{ $dryRun['active_days_rows'] ?? '-' }}</td></tr>
                        <tr><td>electric_rate</td><td>{{ $dryRun['electric_rate'] ?? '-' }}</td></tr>
                        <tr><td>blocker_count</td><td>{{ $dryRun['blocker_count'] ?? '-' }}</td></tr>
                        <tr><td>checked_at</td><td>{{ $dryRun['checked_at'] ?? '-' }}</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <p>Phase 1D dry run only. Real bill rows are not generated yet.</p>
    @endif
</section>
@endsection
