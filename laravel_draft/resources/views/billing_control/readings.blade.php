@extends('billing_control.layout')

@section('content')
<section class="bc-panel">
    <h2>Fix Meter Readings</h2>
    <p class="bc-muted">Phase 1A scaffold only. No real data and no DB write.</p>

    <form method="post" action="{{ route('billing.control.readings.save') }}" class="bc-form">
        @csrf
        <input name="meter_id" placeholder="Meter ID">
        <input name="unit_id" placeholder="Unit ID">
        <input name="reading_date" type="date">
        <input name="reading_value" type="number" step="0.001" placeholder="Reading">
        <button class="bc-btn" type="submit">Test Save Gate</button>
    </form>
</section>
@endsection
