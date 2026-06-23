@extends('billing_control.layout')

@section('content')
<section class="bc-panel">
    <h2>Readiness Gate</h2>
    <p class="bc-muted">Real DB checks only. No data is modified.</p>

    <form method="get" action="{{ route('billing.control.readiness') }}" class="bc-form">
        <input name="month_cycle" value="{{ request('month_cycle', $readiness['month'] ?? '') }}" placeholder="Month Cycle e.g. 2026-06">
        <button class="bc-btn" type="submit">Run Check</button>
    </form>

    <div class="bc-grid">
        @include('billing_control.components.status-card', ['title' => 'Status', 'value' => ($readiness['isReady'] ?? false) ? 'Ready' : 'Blocked', 'note' => $readiness['mode'] ?? ''])
        @include('billing_control.components.status-card', ['title' => 'Cycle Days', 'value' => $readiness['stats']['cycle_days'] ?? '-', 'note' => ($readiness['stats']['cycle_start_date'] ?? '-') . ' to ' . ($readiness['stats']['cycle_end_date'] ?? '-')])
        @include('billing_control.components.status-card', ['title' => 'Rate', 'value' => $readiness['stats']['electric_rate'] ?? '-', 'note' => 'electric'])
        @include('billing_control.components.status-card', ['title' => 'Bill Runs', 'value' => $readiness['stats']['bill_runs'] ?? '-', 'note' => $readiness['stats']['latest_run_status'] ?? '-'])
    </div>
</section>

<section class="bc-panel">
    <h3>Blockers</h3>
    @forelse(($readiness['blockers'] ?? []) as $issue)
        @include('billing_control.components.issue-card', ['issue' => $issue])
    @empty
        <div class="bc-alert">No real-data blockers found.</div>
    @endforelse
</section>

<section class="bc-panel">
    <h3>Real data counts</h3>
    <div class="bc-table-wrap">
        <table class="bc-table">
            <thead><tr><th>Metric</th><th>Value</th></tr></thead>
            <tbody>
            @foreach(($readiness['stats'] ?? []) as $key => $value)
                <tr>
                    <td>{{ $key }}</td>
                    <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="bc-panel">
    <h3>Required table status</h3>
    <div class="bc-table-wrap">
        <table class="bc-table">
            <thead><tr><th>Table</th><th>Exists</th><th>Rows</th></tr></thead>
            <tbody>
            @foreach(($readiness['tables'] ?? []) as $table => $info)
                <tr>
                    <td>{{ $table }}</td>
                    <td>{{ !empty($info['exists']) ? 'YES' : 'NO' }}</td>
                    <td>{{ $info['count'] ?? '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
