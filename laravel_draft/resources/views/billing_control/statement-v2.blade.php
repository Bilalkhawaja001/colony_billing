@extends('billing_control.layout')
@section('content')

<div class="eyebrow">Reports · Electric V2</div>
<h1 class="page-title">Employee Statement — June 2026</h1>

{{-- Search form: company_id daalo --}}
<section class="form-card" style="margin-top:16px">
    <form method="get" action="" class="form-stack">
        <label class="form-label">Employee / Company ID</label>
        <div style="display:flex; gap:8px; flex-wrap:wrap">
            <input class="form-select" style="max-width:260px" type="text" name="company_id"
                   value="{{ $companyId }}" placeholder="e.g. 81001">
            <button class="btn btn-cta" type="submit">View Statement</button>
        </div>
        <div class="col-caption">Cycle: {{ $cycleStart }} to {{ $cycleEnd }}</div>
    </form>
</section>

@if($companyId !== '' && $data === null)
    <section class="form-card" style="margin-top:16px">
        <div class="col-caption">Company ID <strong>{{ $companyId }}</strong> ka June bill nahi mila. ID check karein.</div>
    </section>
@endif

@if($data !== null)
    @php($e = $data['employee'])
    @php($s = $data['summary'])

    <section class="form-card" style="margin-top:16px" id="printArea">
        {{-- Print button (screen pe dikhega, print me chhup jayega) --}}
        <div style="text-align:right; margin-bottom:12px" class="no-print">
            <button class="btn btn-cta" type="button" onclick="window.print()">🖨 Print</button>
        </div>

        {{-- Header --}}
        <h2 style="margin:0 0 4px">{{ $e['name'] }}</h2>
        <div class="col-caption" style="margin-bottom:12px">
            ID: {{ $e['company_id'] }}
            @if($e['father_name']) · S/O {{ $e['father_name'] }} @endif
            @if($e['department']) · {{ $e['department'] }} @endif
            @if($e['designation']) · {{ $e['designation'] }} @endif
            @if($e['colony_type']) · {{ $e['colony_type'] }} @endif
            @if($e['block_floor']) · {{ $e['block_floor'] }} @endif
        </div>
        <div class="col-caption" style="margin-bottom:16px">
            Billing Cycle: {{ $cycleStart }} to {{ $cycleEnd }}
            @if($s['bill_reference']) · Ref: {{ $s['bill_reference'] }} @endif
        </div>

        {{-- Detail table --}}
        <table style="width:100%; border-collapse:collapse; margin-bottom:16px">
            <thead>
                <tr style="border-bottom:2px solid #ccc; text-align:left">
                    <th style="padding:8px">Unit</th>
                    <th style="padding:8px">Type</th>
                    <th style="padding:8px; text-align:right">Attendance</th>
                    <th style="padding:8px; text-align:right">Used Units</th>
                    <th style="padding:8px; text-align:right">Free Units</th>
                    <th style="padding:8px; text-align:right">Billable Units</th>
                    <th style="padding:8px; text-align:right">Rate</th>
                    <th style="padding:8px; text-align:right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['lines'] as $line)
                    <tr style="border-bottom:1px solid #eee">
                        <td style="padding:8px">{{ $line['unit_id'] }}</td>
                        <td style="padding:8px">{{ $line['residence_type'] }}</td>
                        <td style="padding:8px; text-align:right">{{ number_format($line['attendance'], 2) }}</td>
                        <td style="padding:8px; text-align:right">{{ number_format($line['gross_units'], 2) }}</td>
                        <td style="padding:8px; text-align:right">{{ number_format($line['free_units'], 2) }}</td>
                        <td style="padding:8px; text-align:right">{{ number_format($line['billable_units'], 2) }}</td>
                        <td style="padding:8px; text-align:right">{{ number_format($line['rate'], 2) }}</td>
                        <td style="padding:8px; text-align:right">{{ number_format($line['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary --}}
        <div style="text-align:right; border-top:2px solid #ccc; padding-top:12px">
            <div>Total Billable Units: <strong>{{ number_format($s['total_billable_units'], 2) }}</strong></div>
            <div>Rate: <strong>{{ number_format($s['rate'], 2) }}</strong> / unit</div>
            <div style="font-size:1.3em; margin-top:8px">
                Total Amount: <strong>PKR {{ number_format($s['total_amount'], 2) }}</strong>
            </div>
            @if($s['is_estimated'])
                <div class="col-caption" style="margin-top:8px">⚠ Includes estimated readings</div>
            @endif
        </div>
    </section>

    <style>
        @media print {
            .no-print { display: none !important; }
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
@endif

@endsection
