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

@php
    $dataIssues = data_get($readiness, 'dataIssues', []);
    $issueSummary = data_get($readiness, 'dataIssueSummary', []);
    $decisions = data_get($readiness, 'decisions', []);
    $pending = data_get($readiness, 'pendingDecisions', 0);
    $cyStart = data_get($readiness, 'cycle.cycle_start_date', '');
    $cyEnd = data_get($readiness, 'cycle.cycle_end_date', '');
@endphp
@if(count($dataIssues) > 0)
<section class="card" style="margin-top:24px">
    <div class="eyebrow">Data Issues — Records Affected</div>
    <h2 style="margin:6px 0 14px;font-size:18px">
        {{ count($dataIssues) }} records need attention
        @if($pending > 0)<span style="font-size:13px;color:#b45309;font-weight:600">· {{ $pending }} pending decision</span>@endif
    </h2>

    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px">
        @foreach($issueSummary as $code => $cnt)
            <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:6px;padding:8px 14px">
                <div style="font-size:11px;color:#9a3412;font-weight:700">{{ str_replace('_',' ',$code) }}</div>
                <div style="font-size:20px;font-weight:700;color:#7c2d12">{{ $cnt }}</div>
            </div>
        @endforeach
    </div>

    <div style="max-height:460px;overflow:auto;border:1px solid #e5e7eb;border-radius:6px">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
        <thead style="position:sticky;top:0;background:#f8f9fb">
            <tr>
                <th style="text-align:left;padding:9px 12px;border-bottom:1px solid #e5e7eb">Issue</th>
                <th style="text-align:left;padding:9px 12px;border-bottom:1px solid #e5e7eb">Unit</th>
                <th style="text-align:left;padding:9px 12px;border-bottom:1px solid #e5e7eb">Room</th>
                <th style="text-align:left;padding:9px 12px;border-bottom:1px solid #e5e7eb">Decision</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataIssues as $di)
                @php
                    $iCode = $di['code'] ?? '';
                    $iUnit = $di['unit'] ?? '';
                    $iRoom = $di['room'] ?? '';
                    $dkey  = $iCode.'|'.$iUnit.'|'.$iRoom;
                    $dec   = $decisions[$dkey] ?? null;
                @endphp
                <tr>
                    <td style="padding:8px 12px;border-bottom:1px solid #f1f2f4;color:#b45309;font-weight:600">{{ str_replace('_',' ', $iCode) }}</td>
                    <td style="padding:8px 12px;border-bottom:1px solid #f1f2f4;font-family:monospace">{{ $iUnit ?: '-' }}</td>
                    <td style="padding:8px 12px;border-bottom:1px solid #f1f2f4;font-family:monospace">{{ $iRoom ?: '-' }}</td>
                    <td style="padding:8px 12px;border-bottom:1px solid #f1f2f4">
                        @if($dec)
                            <span style="font-size:12px;font-weight:700;color:#047857">{{ $dec['decision'] }}</span>
                            @if(!empty($dec['reason']))
                                <div style="font-size:11px;color:#6b7280">{{ $dec['reason'] }}</div>
                            @endif
                        @else
                            <form method="post" action="{{ route('billing.control.readiness.decision') }}" style="display:flex;gap:5px;align-items:center">
                                @csrf
                                <input type="hidden" name="cycle_start_date" value="{{ $cyStart }}">
                                <input type="hidden" name="cycle_end_date" value="{{ $cyEnd }}">
                                <input type="hidden" name="issue_code" value="{{ $iCode }}">
                                <input type="hidden" name="unit_id" value="{{ $iUnit }}">
                                <input type="hidden" name="room_no" value="{{ $iRoom }}">
                                <select name="decision" style="font-size:12px;padding:3px 6px;border:1px solid #d1d5db;border-radius:4px">
                                    <option value="EXCLUDE">Exclude</option>
                                    <option value="USE_ZERO">Use Zero</option>
                                    <option value="USE_PREVIOUS">Use Previous</option>
                                    <option value="FIX">Fixed</option>
                                    <option value="WAIVE">Waive</option>
                                </select>
                                <input name="reason" placeholder="reason" style="font-size:12px;padding:3px 6px;width:110px;border:1px solid #d1d5db;border-radius:4px">
                                <button type="submit" style="font-size:12px;padding:3px 10px;background:#2563eb;color:#fff;border:0;border-radius:4px;cursor:pointer">Save</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <div style="margin-top:12px;font-size:13px;color:#6b7280">
        These records will be skipped during billing. To fix them, go to the <a href="{{ url('allowances') }}" style="color:#2563eb;font-weight:600">Free Allowances</a> page.
    </div>
</section>
@endif
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
