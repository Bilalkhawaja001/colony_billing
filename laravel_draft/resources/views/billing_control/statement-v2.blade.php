@extends('billing_control.layout')

@section('content')

<style>
.statement-wrap{max-width:1120px;margin:0 auto}
.statement-card{background:#fff;border:1px solid #dce3ee;border-radius:14px;padding:20px;margin-bottom:18px}
.statement-title{font-size:26px;font-weight:800;margin:0 0 18px}
.statement-form{display:flex;gap:12px;align-items:end;flex-wrap:wrap}
.statement-form label{font-size:12px;font-weight:700;display:block;margin-bottom:6px}
.statement-form input,.statement-form select{height:42px;border:1px solid #ccd5e2;border-radius:8px;padding:0 12px;min-width:220px}
.statement-btn{height:42px;border:0;border-radius:8px;background:#2563eb;color:#fff;font-weight:700;padding:0 24px;cursor:pointer}
.statement-table{width:100%;border-collapse:collapse;font-size:13px}
.statement-table th,.statement-table td{padding:10px 8px;border-bottom:1px solid #e7ebf1;text-align:right;vertical-align:top}
.statement-table th{font-weight:800;background:#f8fafc}
.statement-table th:first-child,.statement-table td:first-child{text-align:left}
.statement-table td:nth-child(2),.statement-table th:nth-child(2){text-align:left}
.emp-name{font-size:21px;font-weight:800;margin-bottom:6px}
.emp-meta{font-size:12px;color:#75829a;line-height:1.7}
.summary{text-align:right;margin-top:14px;line-height:1.8}
.summary strong{font-size:18px}
.history-title{font-size:20px;font-weight:800;margin-bottom:14px}
.grand{background:#f8fafc;font-weight:800}
.detail-box{padding:12px;background:#f8fafc;border-radius:9px;margin:4px 0}
.detail-box summary{cursor:pointer;font-weight:700;color:#2563eb}
.error-box{padding:14px;background:#fff1f2;color:#be123c;border-radius:9px;margin-top:15px}
@media print{.no-print{display:none!important}.statement-card{box-shadow:none}.statement-wrap{max-width:none}}
</style>

<div class="statement-wrap">

    <h1 class="statement-title">
        Employee Statement{{ $data ? ' — '.$data['selected_month'] : '' }}
    </h1>

    <div class="statement-card no-print">
        <form method="get" action="{{ url('/statement-v2') }}" class="statement-form">
            <div>
                <label>Employee / Company ID</label>
                <input type="text" name="company_id" value="{{ $companyId }}" placeholder="320494" required>
            </div>

            @if(!empty($cycles))
            <div>
                <label>Billing Month</label>
                <select name="cycle">
                    @foreach($cycles as $cycle)
                        <option value="{{ $cycle['key'] }}"
                            @selected(($data['selected_cycle_key'] ?? '') === $cycle['key'])>
                            {{ $cycle['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <button type="submit" class="statement-btn">View Statement</button>

            @if($data)
                <button type="button" onclick="window.print()" class="statement-btn">Print</button>
            @endif
        </form>
    </div>

    @if($error)
        <div class="error-box">{{ $error }}</div>
    @endif

    @if($data)
        @php($e = $data['employee'])
        @php($s = $data['summary'])

        <div class="statement-card">
            <div class="emp-name">{{ $e['name'] }}</div>

            <div class="emp-meta">
                ID: {{ $e['company_id'] }}
                @if($e['father_name']) · S/O {{ $e['father_name'] }} @endif
                @if($e['department']) · {{ $e['department'] }} @endif
                @if($e['designation']) · {{ $e['designation'] }} @endif
                @if($e['colony_type']) · {{ $e['colony_type'] }} @endif
                @if($e['block_floor']) · {{ $e['block_floor'] }} @endif
                <br>
                Billing Cycle: {{ $data['cycle_start'] }} to {{ $data['cycle_end'] }}
                · Ref: {{ $s['bill_reference'] }}
            </div>

            <div style="overflow-x:auto;margin-top:18px">
                <table class="statement-table">
                    <thead>
                    <tr>
                        <th>Unit / Room</th>
                        <th>Type</th>
                        <th>Attendance</th>
                        <th>Used Units</th>
                        <th>Free Units</th>
                        <th>Billable Units</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($data['lines'] as $line)
                        <tr>
                            <td>
                                {{ $line['unit_id'] }}
                                {{ $line['room_no'] ? ' / '.$line['room_no'] : '' }}
                            </td>
                            <td>{{ $line['residence_type'] }}</td>
                            <td>{{ number_format($line['attendance'],2) }}</td>
                            <td>{{ number_format($line['gross_units'],2) }}</td>
                            <td>{{ number_format($line['free_units'],2) }}</td>
                            <td>{{ number_format($line['billable_units'],2) }}</td>
                            <td>{{ number_format($line['rate'],2) }}</td>
                            <td>{{ number_format($line['amount'],2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No detail rows found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="summary">
                Total Billable Units:
                <strong>{{ number_format($s['total_billable_units'],2) }}</strong><br>
                Rate: {{ number_format($s['rate'],2) }} / unit<br>
                Total Amount:
                <strong>PKR {{ number_format($s['total_amount'],2) }}</strong>
            </div>
        </div>

        <div class="statement-card">
            <div class="history-title">Overall Statement / Billing History</div>

            <div style="overflow-x:auto">
                <table class="statement-table">
                    <thead>
                    <tr>
                        <th>Month</th>
                        <th>Unit / Room</th>
                        <th>Attendance</th>
                        <th>Used Units</th>
                        <th>Free Units</th>
                        <th>Billable Units</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($history as $row)
                        <tr>
                            <td>
                                <a href="{{ url('/statement-v2') }}?company_id={{ urlencode($companyId) }}&cycle={{ urlencode($row['cycle_key']) }}">
                                    <strong>{{ $row['month'] }}</strong>
                                </a>
                            </td>
                            <td>{{ $row['unit_room'] }}</td>
                            <td>{{ number_format($row['attendance'],2) }}</td>
                            <td>{{ number_format($row['used_units'],2) }}</td>
                            <td>{{ number_format($row['free_units'],2) }}</td>
                            <td>{{ number_format($row['billable_units'],2) }}</td>
                            <td>{{ number_format($row['rate'],2) }}</td>
                            <td><strong>{{ number_format($row['amount'],2) }}</strong></td>
                        </tr>

                        @if(count($row['lines']) > 1)
                        <tr>
                            <td colspan="8">
                                <details class="detail-box">
                                    <summary>View {{ $row['month'] }} unit-wise detail</summary>
                                    <table class="statement-table" style="margin-top:10px">
                                        <tbody>
                                        @foreach($row['lines'] as $line)
                                            <tr>
                                                <td>{{ $line['unit_id'] }}{{ $line['room_no'] ? ' / '.$line['room_no'] : '' }}</td>
                                                <td>{{ $line['residence_type'] }}</td>
                                                <td>{{ number_format($line['attendance'],2) }}</td>
                                                <td>{{ number_format($line['gross_units'],2) }}</td>
                                                <td>{{ number_format($line['free_units'],2) }}</td>
                                                <td>{{ number_format($line['billable_units'],2) }}</td>
                                                <td>{{ number_format($line['rate'],2) }}</td>
                                                <td>{{ number_format($line['amount'],2) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </details>
                            </td>
                        </tr>
                        @endif
                    @endforeach

                    <tr class="grand">
                        <td colspan="5">GRAND TOTAL</td>
                        <td>{{ number_format($overall['total_billable_units'],2) }}</td>
                        <td>-</td>
                        <td>PKR {{ number_format($overall['total_amount'],2) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

@endsection
