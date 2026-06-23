@extends('billing_control.layout')

@section('content')
<section class="bc-panel">
    <h2>Generate Bill Dry Run</h2>
    <p class="bc-muted">Phase 1D dry run only. This checks the generate gate and writes no bill data.</p>

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
        <div class="bc-alert">Gate is ready for dry run. Real generate remains locked until next explicit approval.</div>
    @endforelse
</section>
@endsection
