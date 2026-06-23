@extends('billing_control.layout')

@section('content')
<section class="bc-panel">
    <h2>Generate Bill Dry Run</h2>
    <p class="bc-muted">Phase 1E keeps final generation locked. Dry run and audit write no bill data.</p>

    <div class="bc-grid">
        @include('billing_control.components.status-card', ['title' => 'Readiness', 'value' => ($readiness['isReady'] ?? false) ? 'Ready' : 'Blocked', 'note' => $readiness['mode'] ?? ''])
        @include('billing_control.components.status-card', ['title' => 'Month', 'value' => $readiness['stats']['month_cycle'] ?? '-', 'note' => 'selected'])
        @include('billing_control.components.status-card', ['title' => 'Current Readings', 'value' => $readiness['stats']['current_readings'] ?? '-', 'note' => 'cycle end'])
        @include('billing_control.components.status-card', ['title' => 'Rate', 'value' => $readiness['stats']['electric_rate'] ?? '-', 'note' => 'electric'])
    </div>

    <form method="post" action="{{ route('billing.control.generate.store') }}" class="bc-form">
        @csrf
        <input type="hidden" name="month_cycle" value="{{ request('month_cycle', $readiness['month'] ?? '') }}">
        <button class="bc-btn" type="submit" @disabled(!($readiness['isReady'] ?? false))>Run Generate Dry Run</button>
        <span class="bc-muted">DB write: NO | Queue dispatch: NO | Bill insert: NO</span>
    </form>

    @forelse(($readiness['blockers'] ?? []) as $issue)
        @include('billing_control.components.issue-card', ['issue' => $issue])
    @empty
        <div class="bc-alert">Gate is ready for dry run. Final generation remains locked until explicit next approval.</div>
    @endforelse
</section>

<section class="bc-panel">
    <h2>Final Generation Safety Audit</h2>
    <p class="bc-muted">Confirmation gate only. Final generation is intentionally disabled.</p>

    <div class="bc-grid">
        @include('billing_control.components.status-card', ['title' => 'Audit Status', 'value' => $safetyAudit['status'] ?? '-', 'note' => 'locked'])
        @include('billing_control.components.status-card', ['title' => 'Final Generation', 'value' => !empty($safetyAudit['real_generate_enabled']) ? 'Enabled' : 'Locked', 'note' => $safetyAudit['reason'] ?? ''])
        @include('billing_control.components.status-card', ['title' => 'Ready Blockers', 'value' => $safetyAudit['readiness']['blocker_count'] ?? '-', 'note' => $safetyAudit['readiness']['mode'] ?? '-'])
        @include('billing_control.components.status-card', ['title' => 'Next Approval', 'value' => 'Phase 1F', 'note' => 'required'])
    </div>

    <div class="bc-table-wrap">
        <table class="bc-table">
            <thead><tr><th>Safety item</th><th>Status</th></tr></thead>
            <tbody>
            @foreach(($safetyAudit['phase1e_safety'] ?? []) as $key => $value)
                <tr><td>{{ $key }}</td><td>{{ $value }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="bc-panel">
    <h3>Current output counts</h3>
    <div class="bc-table-wrap">
        <table class="bc-table">
            <thead><tr><th>Table</th><th>Current rows</th></tr></thead>
            <tbody>
            @foreach(($safetyAudit['current_output_counts'] ?? []) as $table => $count)
                <tr><td>{{ $table }}</td><td>{{ $count }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="bc-panel">
    <h3>Confirmation gate</h3>
    <p class="bc-muted">Required phrase for later final execution approval:</p>
    <code>{{ $safetyAudit['required_phrase'] ?? 'CONFIRM FINAL GENERATION' }}</code>

    <form class="bc-form">
        <input type="text" placeholder="Locked until Phase 1F" disabled>
        <button class="bc-btn" type="button" disabled>Final Generation Locked</button>
    </form>

    <div class="bc-alert bc-alert-danger">Phase 1E does not queue a job and does not write or delete billing rows.</div>
</section>
@endsection
