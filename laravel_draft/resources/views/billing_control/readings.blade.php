@extends('billing_control.layout')

@section('content')
<div class="eyebrow">Fix Data</div>
<h1 class="page-title">Meter Readings</h1>

<section class="card" style="margin-top:22px">
    <div class="pin-card">
        <div class="pin-key">Purpose</div><div class="pin-val">Review or fix missing meter readings before bill preview.</div>
        <div class="pin-key">Status</div><div class="pin-val">Safe gate only. No real billing rows are generated from this page.</div>
    </div>

    <form method="post" action="{{ route('billing.control.readings.save') }}" class="form-stack">
        @csrf
        <button class="btn btn-outline" type="submit">Test Save Gate</button>
    </form>
</section>
@endsection
