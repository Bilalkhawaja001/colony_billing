@extends('billing_control.layout')

@section('content')
@php
    $stats = data_get($readiness, 'stats', []);
    $month = request('month_cycle', data_get($readiness, 'month', data_get($stats, 'month_cycle', now()->format('m-Y'))));
    $isReady = (bool) data_get($readiness, 'isReady', false);
    $blockers = data_get($readiness, 'blockers', []);
@endphp

<div class="eyebrow">Preview &amp; Generate</div>
<h1 class="page-title">Preview & Generate · @include('billing_control.components.month-label', ['value' => $month])</h1>

@if(!$isReady)
    <section class="panel-center is-locked" style="margin-top:24px">
        <div class="hero-icon" style="margin:0 auto 16px;background:var(--warn-bg);color:var(--warn)">🔒</div>
        <h2 class="headline">Must Fix items need attention first</h2>
        <p class="hero-sub" style="margin:8px auto 22px">Fix {{ count($blockers) }} Must Fix item(s) before previewing bills.</p>
        <a class="btn btn-warn" href="{{ route('billing.control.readiness', ['month_cycle'=>$month]) }}">Go to Check & Fix Data</a>
    </section>
@else
    <section class="panel-center" style="margin-top:24px">
        <div class="gen-tick">✓</div>
        <h2 class="headline">Ready for Preview</h2>
        <p class="hero-sub" style="margin:8px auto 22px">Preview Bills will run checks only. Official final generation remains locked.</p>

        <form method="post" action="{{ route('billing.control.generate.store') }}">
            @csrf
            <input type="hidden" name="month_cycle" value="{{ $month }}">
            <button class="btn btn-cta" type="submit">⚡ Preview Bills</button>
        </form>
        <div class="btn-hint">DB write: NO · Bill insert: NO</div>
    </section>
@endif

<section class="stat-grid" style="margin-top:24px">
    @include('billing_control.components.status-card', ['value' => data_get($stats, 'active_employees', '-'), 'title' => 'Employees'])
    @include('billing_control.components.status-card', ['value' => data_get($stats, 'current_readings', '-'), 'title' => 'Readings In'])
    @include('billing_control.components.status-card', ['value' => data_get($stats, 'electric_rate', '-'), 'title' => 'Rate'])
    @include('billing_control.components.status-card', ['value' => data_get($safetyAudit ?? [], 'status', 'Locked'), 'title' => 'Safety Audit'])
</section>
@endsection
