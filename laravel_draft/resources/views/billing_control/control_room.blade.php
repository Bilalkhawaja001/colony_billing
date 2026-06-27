@extends('billing_control.layout')

@section('content')
@php
    $stats = data_get($readiness, 'stats', []);
    $month = request('month_cycle', data_get($readiness, 'month', data_get($stats, 'month_cycle', now()->format('m-Y'))));
    $isReady = (bool) data_get($readiness, 'isReady', false);
    $blockers = data_get($readiness, 'blockers', []);
    $meterCount = (int) data_get($stats, 'active_meters', 0);
    $readingCount = (int) data_get($stats, 'current_readings', 0);
    $pendingReadings = max($meterCount - $readingCount, 0);
@endphp

<div style="display:flex;justify-content:space-between;gap:18px;align-items:start;margin-bottom:28px">
    <div>
        <div class="eyebrow">Billing Center</div>
        <h1 class="page-title">Electricity · @include('billing_control.components.month-label', ['value' => $month])</h1>
    </div>
    <form method="get" action="{{ route('billing.control.home') }}" style="display:flex;gap:10px;align-items:center">
        @foreach(request()->except(['month_cycle', 'month']) as $key => $value)
            @if(is_scalar($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        <div data-month-picker-wrap>
        @include('billing_control.components.month-select', [
            'value' => $month,
            'id' => 'control-room-month-select',
        ])
        </div>

        <button class="btn btn-outline" type="submit">Refresh</button>
    </form>
</div>

<section class="hero {{ $isReady ? 'is-ok' : 'is-warn' }}">
    <div class="hero-icon">{{ $isReady ? '✓' : '⚠' }}</div>
    <div class="hero-body">
        <div class="hero-headline">{{ $isReady ? 'READY FOR PREVIEW' : 'MUST FIX ITEMS NEED ATTENTION FIRST' }}</div>
        <div class="hero-sub">
            @if($isReady)
                All required data is complete. You can preview bills.
            @else
                {{ count($blockers) }} Must Fix items need attention before bill preview.
            @endif
        </div>
    </div>
    <a class="btn {{ $isReady ? '' : 'btn-warn' }}" href="{{ $isReady ? route('billing.control.generate', ['month_cycle'=>$month]) : route('billing.control.readiness', ['month_cycle'=>$month]) }}">
        {{ $isReady ? 'Preview Bills' : "Check & Fix Data" }}
    </a>
</section>

<section class="stat-grid">
    @include('billing_control.components.status-card', ['value' => data_get($stats, 'active_employees', '-'), 'title' => 'Employees'])
    @include('billing_control.components.status-card', ['value' => data_get($stats, 'active_meters', '-'), 'title' => 'Meter Locations'])
    @include('billing_control.components.status-card', ['value' => data_get($stats, 'current_readings', '-'), 'title' => 'Readings In', 'warn' => !$isReady])
    @include('billing_control.components.status-card', ['value' => $pendingReadings, 'title' => 'Pending', 'warn' => $pendingReadings > 0])
</section>

<div style="margin-top:26px">
    @if($isReady)
        <a class="btn btn-cta" href="{{ route('billing.control.generate', ['month_cycle'=>$month]) }}">⚡ Preview & Generate</a>
        <div class="btn-hint">Preview mode only · final generation still locked</div>
    @else
        <button class="btn btn-cta btn-locked" disabled>⚡ Preview & Generate</button>
        <div class="btn-hint">🔒 Must Fix items need attention first</div>
    @endif
</div>
@endsection
