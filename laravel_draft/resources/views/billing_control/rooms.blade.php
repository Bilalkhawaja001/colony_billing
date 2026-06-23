@extends('billing_control.layout')

@section('content')
<section class="bc-panel">
    <h2>Fix Room Assignment</h2>
    <p class="bc-muted">Phase 1A scaffold only. One-row save gate exists but DB write disabled.</p>

    <form method="post" action="{{ route('billing.control.rooms.save') }}" class="bc-form">
        @csrf
        <input name="company_id" placeholder="Company ID">
        <input name="unit_id" placeholder="Unit ID">
        <input name="room_no" placeholder="Room No">
        <input name="start_date" type="date">
        <button class="bc-btn" type="submit">Test Save Gate</button>
    </form>
</section>
@endsection
