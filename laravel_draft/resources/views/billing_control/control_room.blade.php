@extends('billing_control.layout')

@section('content')
<section class="bc-grid">
    @include('billing_control.components.status-card', ['title' => 'Readiness', 'value' => ($readiness['isReady'] ?? false) ? 'Ready' : 'Locked', 'note' => $readiness['mode'] ?? ''])
    @include('billing_control.components.status-card', ['title' => 'Employees', 'value' => $readiness['stats']['employees'] ?? '-', 'note' => 'TODO real data'])
    @include('billing_control.components.status-card', ['title' => 'Meters', 'value' => $readiness['stats']['meters'] ?? '-', 'note' => 'TODO real data'])
    @include('billing_control.components.status-card', ['title' => 'Bill Runs', 'value' => $readiness['stats']['bill_runs'] ?? '-', 'note' => 'TODO real data'])
</section>

<section class="bc-panel">
    <h2>Safe scaffold added</h2>
    <p>Phase 1A only. Real generation disabled until readiness logic is wired.</p>
    <div class="bc-actions">
        <a class="bc-btn-link" href="{{ route('billing.control.readiness') }}">Check Readiness</a>
        <a class="bc-btn-link" href="{{ route('billing.control.generate') }}">Generate Gate</a>
    </div>
</section>
@endsection
