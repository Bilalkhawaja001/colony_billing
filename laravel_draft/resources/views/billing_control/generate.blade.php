@extends('billing_control.layout')

@section('content')
<section class="bc-panel">
    <h2>Generate Bill</h2>
    <p class="bc-muted">Real readiness data is wired. Actual generate job remains locked until Phase 1D approval.</p>

    <div class="bc-grid">
        @include('billing_control.components.status-card', ['title' => 'Readiness', 'value' => ($readiness['isReady'] ?? false) ? 'Ready' : 'Blocked', 'note' => $readiness['mode'] ?? ''])
        @include('billing_control.components.status-card', ['title' => 'Month', 'value' => $readiness['stats']['month_cycle'] ?? '-', 'note' => 'selected'])
        @include('billing_control.components.status-card', ['title' => 'Current Readings', 'value' => $readiness['stats']['current_readings'] ?? '-', 'note' => 'cycle end'])
        @include('billing_control.components.status-card', ['title' => 'Rate', 'value' => $readiness['stats']['electric_rate'] ?? '-', 'note' => 'electric'])
    </div>

    <form method="post" action="{{ route('billing.control.generate.store') }}">
        @csrf
        <button class="bc-btn" type="submit" disabled>Generate Bill Locked Until Phase 1D</button>
    </form>

    @forelse(($readiness['blockers'] ?? []) as $issue)
        @include('billing_control.components.issue-card', ['issue' => $issue])
    @empty
        <div class="bc-alert">Readiness data has no blockers. Generate job wiring still requires Phase 1D approval.</div>
    @endforelse
</section>
@endsection
