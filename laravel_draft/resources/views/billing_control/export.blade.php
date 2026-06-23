@extends('billing_control.layout')

@section('content')
<section class="bc-panel">
    <h2>Download Excel</h2>
    <p class="bc-muted">6-filter scaffold. Real 17-column export pending Phase 2.</p>

    <form method="post" action="{{ route('billing.control.export.download') }}" class="bc-form">
        @csrf
        <input name="billing_month" placeholder="Month e.g. 2026-06">
        <select name="bill_type">
            <option value="electric_v1">Electric V1</option>
            <option value="water">Water</option>
            <option value="school_van">School Van</option>
            <option value="all">All</option>
        </select>
        <select name="scope">
            <option value="all">All</option>
            <option value="colony">Colony</option>
            <option value="unit">Unit</option>
            <option value="room">Room</option>
        </select>
        <input name="colony_type" placeholder="Colony Type">
        <input name="unit_type" placeholder="Unit Type">
        <input name="room_type" placeholder="Room Type">
        <button class="bc-btn" type="submit">Test Export Gate</button>
    </form>
</section>
@endsection
