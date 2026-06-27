@extends('billing_control.layout')

@section('content')
@php($month = request('month_cycle', request('month', now()->format('m-Y'))))

<div class="eyebrow">Download &amp; Records</div>
<h1 class="page-title">Download & Records · @include('billing_control.components.month-label', ['value' => $month])</h1>

<section class="form-card" style="margin-top:24px">
    <form method="post" action="{{ route('billing.control.export.download') }}" class="form-stack">
        @csrf

        <div>
            <label class="form-label">Billing Month</label>
            <div data-month-picker-wrap>
        @include('billing_control.components.month-select', [
                'value' => $month,
                'id' => 'export-month-select',
            ])
            </div>
        </div>

        <div>
            <label class="form-label">Bill Type</label>
            <select class="form-select" name="bill_type">
                <option value="electric_v1">Electricity</option>
            </select>
        </div>

        <div class="dl-filters">
            <div>
                <label class="form-label">Scope</label>
                <select class="form-select" name="scope"><option value="all">All</option></select>
            </div>
            <div>
                <label class="form-label">Unit Type</label>
                <select class="form-select" name="unit_type"><option value="">All</option></select>
            </div>
            <div>
                <label class="form-label">Room Type</label>
                <select class="form-select" name="room_type"><option value="">All</option></select>
            </div>
        </div>

        <button class="btn btn-cta" type="submit">Download & Records</button>
        <div class="col-caption">Filtered download will follow approved billing records and Excel format.</div>
    </form>
</section>
@endsection
