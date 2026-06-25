@extends('billing_control.layout')

@section('content')
<div class="eyebrow">Fix Data</div>
<h1 class="page-title">Room Assignment</h1>

<section class="card" style="margin-top:22px">
    <div class="pin-card">
        <div class="pin-key">Purpose</div><div class="pin-val">Review room assignments and allowance-related issues.</div>
        <div class="pin-key">Status</div><div class="pin-val">Safe gate only. DB write remains disabled until approved.</div>
    </div>

    <form method="post" action="{{ route('billing.control.rooms.save') }}" class="form-stack">
        @csrf
        <button class="btn btn-outline" type="submit">Test Save Gate</button>
    </form>
</section>
@endsection
