@extends('billing_control.layout')

@section('content')
@php
    $stats = data_get($readiness, 'stats', []);
    $month = request('month_cycle', data_get($readiness, 'month', data_get($stats, 'month_cycle', now()->format('m-Y'))));
    $isReady = (bool) data_get($readiness, 'isReady', false);
    $blockers = data_get($readiness, 'blockers', []);
@endphp

<div class="eyebrow">Check &amp; Fix Data</div>
<h1 class="page-title">Check &amp; Fix Data · @include('billing_control.components.month-label', ['value' => $month])</h1>

<div class="pin-card" style="margin-top:22px">
    <div class="pin-key">Purpose</div><div class="pin-val">Check required monthly data before bill preview.</div>
    <div class="pin-key">Issue</div><div class="pin-val">{{ $isReady ? 'No Must Fix items found.' : count($blockers).' Must Fix items found.' }}</div>
    <div class="pin-key">Next</div><div class="pin-val">{{ $isReady ? 'Go to Preview & Generate.' : 'Fix listed Must Fix items first.' }}</div>
</div>

<section class="issue-list">
    @forelse($blockers as $issue)
        @include('billing_control.components.issue-card', ['issue' => $issue])
    @empty
        <div class="allgood-toggle">
            <span class="allgood-check">✓</span>
            <div>
                <b>All good</b>
                <div class="issue-desc">No Must Fix items found. You can generate preview.</div>
            </div>
        </div>
    @endforelse
</section>

<div style="margin-top:24px">
    @if($isReady)
        <a class="btn btn-cta" href="{{ route('billing.control.generate', ['month_cycle'=>$month]) }}">⚡ Preview & Generate</a>
    @else
        <button class="btn btn-cta btn-locked" disabled>⚡ Preview & Generate — 🔒 Fix {{ count($blockers) }} Must Fix items</button>
    @endif
</div>

<section class="card" style="margin-top:24px">
    <div class="eyebrow">Real data counts</div>
    <div class="grid-wrap">
        <div class="grid-scroll">
            <table class="grid">
                <thead><tr><th>Metric</th><th class="num">Value</th></tr></thead>
                <tbody>
                @foreach($stats as $key => $value)
                    @php
                        $displayKey = $key === 'month_cycle' ? 'Billing Month' : ucwords(str_replace('_',' ', $key));
                        $isMonthLikeValue = is_scalar($value) && preg_match('/^\d{2}-\d{4}$/', (string) $value);
                    @endphp
                    <tr>
                        <td>{{ $displayKey }}</td>
                        <td class="num">
                            @if($isMonthLikeValue)
                                @include('billing_control.components.month-label', ['value' => $value])
                            @else
                                {{ is_scalar($value) ? $value : json_encode($value) }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
