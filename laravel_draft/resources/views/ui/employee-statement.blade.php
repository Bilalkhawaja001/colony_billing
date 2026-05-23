@extends('layouts.app')

@section('page_title','Employee Statement')
@section('page_subtitle','Month-wise electric bill statement by employee, house/unit, or room.')

@section('content')
@php
    $fromMonth = $from_month ?? request('from_month', request('month_cycle', '05-2026'));
    $toMonth = $to_month ?? request('to_month', request('month_cycle', $fromMonth));
    $companyId = $company_id ?? request('company_id', '');
    $unitId = $unit_id ?? request('unit_id', '');
    $roomNo = $room_no ?? request('room_no', '');
    $q = request('q', '');
    $status = request('status', '');
    $rows = $statement ?? [];
    $sum = $summary ?? [];
    $schoolVan = $school_van ?? [];
    $schoolVanRows = $schoolVan['rows'] ?? [];
    $schoolVanBlocked = (bool)($schoolVan['blocked'] ?? false);
    $schoolVanGenerated = (bool)($schoolVan['generated'] ?? false);
    $first = $rows[0] ?? [];

    $empName = $first['name'] ?? '';
    $empId = $first['company_id'] ?? $companyId;
    $empDept = $first['department'] ?? '';
    $empDesignation = $first['designation'] ?? '';
    $empUnit = $first['unit_id'] ?? $unitId;
    $empRoom = $first['room_no'] ?? $roomNo;
    $empColonyType = $first['colony_type'] ?? '';
    $empFloor = $first['block_floor'] ?? '';

    $periodLabel = ($fromMonth === $toMonth) ? $fromMonth : ($fromMonth . ' to ' . $toMonth);

    $rateValues = collect($rows)->pluck('rate')->filter(fn($v) => $v !== null && $v !== '')->map(fn($v) => (float)$v)->unique()->values();
    $rateLabel = $rateValues->count() === 1 ? number_format((float)$rateValues->first(), 2) : 'Multiple';

    $statusLabel = ((float)($sum['total_amount'] ?? 0) > 0) ? 'POSITIVE BILL' : 'ZERO BILL';
    $statusClass = ((float)($sum['total_amount'] ?? 0) > 0) ? 'is-positive' : 'is-zero';

    $monthBox = ($fromMonth === $toMonth) ? $fromMonth : (($sum['months_count'] ?? 0) . ' Months');

    $monthInput = function ($value) {
        try { return \Carbon\Carbon::createFromFormat('m-Y', (string)$value)->format('Y-m'); } catch (\Throwable $e) {}
        return $value;
    };
@endphp

<div class="statement-page">
    <div class="statement-toolbar card no-print">
        <form method="get" action="/reports/employee-statement" class="statement-filter-grid">
            <div class="field">
                <label class="label">From Month</label>
                <input type="month" name="from_month" value="{{ $monthInput($fromMonth) }}">
            </div>
            <div class="field">
                <label class="label">To Month</label>
                <input type="month" name="to_month" value="{{ $monthInput($toMonth) }}">
            </div>
            <div class="field">
                <label class="label">Employee ID</label>
                <input name="company_id" value="{{ $companyId }}" placeholder="240105">
            </div>
            <div class="field">
                <label class="label">House / Unit</label>
                <input name="unit_id" value="{{ $unitId }}" placeholder="WB-105">
            </div>
            <div class="field">
                <label class="label">Room No.</label>
                <input name="room_no" value="{{ $roomNo }}" placeholder="WB-105">
            </div>
            <div class="field">
                <label class="label">Quick Search</label>
                <input name="q" value="{{ $q }}" placeholder="Name / ID / House">
            </div>
            <div class="field">
                <label class="label">Status</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="positive" @selected($status === 'positive')>Positive Bill</option>
                    <option value="zero" @selected($status === 'zero')>Zero Bill</option>
                </select>
            </div>
            <div class="field statement-load">
                <label class="label">&nbsp;</label>
                <button class="btn btn-primary" type="submit">Load Statement</button>
            </div>
        </form>

        <div class="statement-toolbar-actions">
            <button class="btn" type="button" onclick="window.print()">Print</button>
            <a class="btn" target="_blank" href="/reports/employee-statement/print?{{ http_build_query(request()->query()) }}">Printable Page</a>
            <a class="btn" href="/reports/employee-statement/export?{{ http_build_query(request()->query()) }}">Download CSV</a>
            <a class="btn" href="/reports/employee-statement/export?{{ http_build_query(array_merge(request()->query(), ['format'=>'pdf'])) }}">Download PDF</a>
        </div>
    </div>

    <div class="statement-preview">
        <div class="statement-hero">
            <div class="brand-panel">
                <div class="brand-mark">
                    <div>
                        <div class="brand-title">COLONY BILLING</div>
                        <div class="brand-sub">UTILITY BILLING</div>
                    </div>
                </div>
                <div class="brand-skyline"></div>
            </div>

            <div class="hero-copy">
                <h1><span>Electric</span> Bill Statement</h1>
                <p>
                    This statement is generated from electric billing records for the selected billing period.
                    Calculation is based on attendance days, consumed units, eligible units, billable units,
                    and applicable monthly electric rate.
                </p>
            </div>

            <div class="hero-meta">
                <div class="meta-box">
                    <div class="meta-icon blue">📅</div>
                    <div>
                        <span>Generated On</span>
                        <strong>{{ now()->format('d-M-Y h:i A') }}</strong>
                    </div>
                </div>
                <div class="meta-box">
                    <div class="meta-icon green">🗓</div>
                    <div>
                        <span>Billing Period</span>
                        <strong>{{ $periodLabel }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="statement-main-grid">
            <section class="panel employee-panel">
                <div class="panel-head">Employee Information</div>
                <div class="panel-body">
                    <div class="info-row"><span>Employee Name</span><strong>{{ $empName ?: 'All Employees' }}</strong></div>
                    <div class="info-row"><span>Employee ID</span><strong>{{ $empId ?: 'All' }}</strong></div>
                    <div class="info-row"><span>Designation</span><strong>{{ $empDesignation ?: '-' }}</strong></div>
                    <div class="info-row"><span>Department</span><strong>{{ $empDept ?: '-' }}</strong></div>
                    <div class="info-row"><span>Colony Type</span><strong>{{ $empColonyType ?: '-' }}</strong></div>
                    <div class="info-row"><span>Floor / Block</span><strong>{{ $empFloor ?: '-' }}</strong></div>
                    <div class="info-row"><span>House / Unit</span><strong>{{ $empUnit ?: 'All' }}</strong></div>
                    <div class="info-row"><span>Room Address</span><strong>{{ $empRoom ?: 'All' }}</strong></div>
                </div>
            </section>

            <section class="panel summary-panel">
                <div class="panel-head">Bill Summary</div>
                <div class="panel-body summary-layout">
                    <div class="summary-stat">
                        <span>Month</span>
                        <strong>{{ $monthBox }}</strong>
                    </div>
                    <div class="summary-stat">
                        <span>Attendance (Days)</span>
                        <strong>{{ number_format((float)($sum['total_active_days'] ?? 0), 2) }}</strong>
                    </div>
                    <div class="summary-stat">
                        <span>Used Units</span>
                        <strong>{{ number_format((float)($sum['total_used_units'] ?? 0), 4) }}</strong>
                    </div>
                    <div class="summary-stat">
                        <span>Eligible Units</span>
                        <strong>{{ number_format((float)($sum['total_eligible_units'] ?? 0), 4) }}</strong>
                    </div>
                    <div class="summary-stat">
                        <span>Billable Units</span>
                        <strong>{{ number_format((float)($sum['total_billable_units'] ?? 0), 4) }}</strong>
                    </div>
                    <div class="summary-stat">
                        <span>Rate (Rs.)</span>
                        <strong>{{ $rateLabel }}</strong>
                    </div>

                    <div class="amount-card {{ $statusClass }}">
                        <span>Total Amount</span>
                        <div class="amount-value">Rs. {{ number_format((float)($sum['total_amount'] ?? 0), 2) }}</div>
                        <div class="amount-status">{{ $statusLabel }}</div>
                    </div>
                </div>
            </section>
        </div>

        <section class="panel details-panel">
            <div class="panel-head">Billing Details</div>
            <div class="panel-body">
                <div class="table-wrap">
                    <table class="billing-table">
                        <thead>
                            <tr>
                                <th>Attendance<br>(Days)</th>
                                <th>Used Units</th>
                                <th>Eligible Units</th>
                                <th>Billable Units</th>
                                <th>Rate (Rs.)</th>
                                <th>Electric Bill (Rs.)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td class="num">{{ number_format((float)($row['active_days'] ?? 0), 2) }}</td>
                                <td class="num">{{ number_format((float)($row['emp_used_units'] ?? 0), 4) }}</td>
                                <td class="num">{{ number_format((float)($row['eligible_units'] ?? 0), 4) }}</td>
                                <td class="num">{{ number_format((float)($row['billable_units'] ?? 0), 4) }}</td>
                                <td class="num">{{ number_format((float)($row['rate'] ?? 0), 2) }}</td>
                                <td class="num bill">{{ number_format((float)($row['electric_amount'] ?? 0), 2) }}</td>
                                <td>
                                    <span class="status-pill {{ ((float)($row['electric_amount'] ?? 0) > 0) ? 'ok' : 'zero' }}">
                                        {{ $row['billing_status'] ?? '' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">No employee statement rows found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="panel details-panel school-van-charge-panel">
            <div class="panel-head">
                School Van Charge
                <span class="sv-separate-pill">Separate Charge</span>
            </div>
            <div class="panel-body">
                @if($schoolVanBlocked)
                    <div class="sv-statement-alert">
                        <strong>Blocked - Expense Correction Required</strong>
                        <span>School van amount cannot be finalized until the invalid transport expense entry is corrected.</span>
                    </div>
                @endif

                @if(count($schoolVanRows))
                    <div class="table-wrap">
                        <table class="billing-table sv-statement-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Employee ID</th>
                                    <th>Children</th>
                                    <th>Chargeable Units</th>
                                    <th>School Van Charge (Rs.)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($schoolVanRows as $svRow)
                                <tr>
                                    <td>{{ $svRow['month_cycle'] ?? '' }}</td>
                                    <td>{{ $svRow['company_id'] ?? '' }}</td>
                                    <td class="num">{{ number_format((float)($svRow['children_count'] ?? 0)) }}</td>
                                    <td class="num">{{ number_format((float)($svRow['chargeable_units'] ?? 0), 2) }}</td>
                                    <td class="num bill">
                                        {{ $schoolVanBlocked ? 'Pending Correction' : number_format((float)($svRow['payable_amount'] ?? 0), 2) }}
                                    </td>
                                    <td>
                                        <span class="status-pill {{ $schoolVanBlocked ? 'blocked' : 'ok' }}">
                                            {{ $schoolVanBlocked ? 'BLOCKED' : ($schoolVanGenerated ? 'GENERATED' : 'PREVIEW') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="sv-statement-total">
                        <span>School Van Charge Total</span>
                        <strong>
                            {{ $schoolVanBlocked ? 'Pending Expense Correction' : 'Rs. '.number_format((float)($schoolVan['total_amount'] ?? 0), 2) }}
                        </strong>
                    </div>
                @else
                    <div class="sv-statement-empty">
                        No school van charge found for the selected employee and billing period.
                    </div>
                @endif

                @if($schoolVanBlocked && count($schoolVan['blockers'] ?? []))
                    @foreach($schoolVan['blockers'] as $blocker)
                        <div class="sv-statement-blocker">
                            Correction required: {{ $blocker['vehicle_code'] ?? 'School Van' }} fuel entry dated
                            {{ $blocker['entry_date'] ?? '-' }} is outside billing cycle {{ $blocker['month_cycle'] ?? '' }}.
                        </div>
                    @endforeach
                @endif

                <div class="sv-statement-note">
                    {{ $schoolVanGenerated ? 'Official generated charge.' : 'Calculated preview only - generate School Van Bill to make it official.' }} This charge is shown separately and is not merged into the Electric Bill Total.
                </div>
            </div>
        </section>
    </div>
</div>
            </section>

            <section class="footer-box footer-compact">
                <div class="qr-placeholder"></div>
                <div class="thank-text">Thank you for your timely payment.</div>
            </section>
        </div>
    </div>
</div>

<style>
.statement-page{display:grid;gap:18px}
.statement-toolbar{padding:18px 20px}
.statement-filter-grid{display:grid;grid-template-columns:repeat(4,minmax(180px,1fr));gap:14px}
.statement-filter-grid .field input,
.statement-filter-grid .field select{height:42px}
.statement-load .btn{width:100%;height:42px}
.statement-toolbar-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:14px;flex-wrap:wrap}

.statement-preview{
    background:#fff;
    border:1px solid #d8e2ef;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 20px 50px rgba(15,23,42,.08)
}

.statement-hero{
    display:grid;
    grid-template-columns:340px 1fr 260px;
    align-items:stretch;
    border-bottom:1px solid #e6edf7
}

.brand-panel{
    background:linear-gradient(135deg,#07235f 0%,#0b2e76 64%,#0d347f 100%);
    color:#fff;
    padding:24px 22px 18px;
    position:relative;
    overflow:hidden;
    min-height:220px
}
.brand-panel::after{
    content:"";
    position:absolute;
    top:-18%;
    right:-22%;
    width:62%;
    height:150%;
    background:#fff;
    border-left:8px solid #67cc3b;
    border-radius:55% 0 0 55%;
    transform:rotate(10deg)
}
.brand-mark{position:relative;z-index:2;display:flex;gap:0;align-items:flex-start;max-width:235px}
/* electric icon removed */
.brand-title{
    font-size:26px;
    font-weight:900;
    line-height:1.05;
    letter-spacing:.01em;
    max-width:180px;
    white-space:normal
}
.brand-sub{
    margin-top:8px;
    color:#78d741;
    font-size:10px;
    font-weight:800;
    letter-spacing:.05em;
    text-transform:uppercase;
    white-space:nowrap
}
.brand-skyline{
    position:absolute;left:0;right:0;bottom:0;height:110px;z-index:1;opacity:.28;
    background:
      linear-gradient(transparent 78%, rgba(103,204,59,.9) 78%, rgba(103,204,59,.9) 80%, transparent 80%) bottom/100% 100% no-repeat,
      linear-gradient(90deg, transparent 6%, rgba(255,255,255,.85) 6%, rgba(255,255,255,.85) 7%, transparent 7%) 18px 42px/90px 60px no-repeat,
      linear-gradient(90deg, transparent 50%, rgba(255,255,255,.8) 50%, rgba(255,255,255,.8) 53%, transparent 53%) 120px 26px/80px 76px no-repeat,
      linear-gradient(90deg, transparent 58%, rgba(255,255,255,.8) 58%, rgba(255,255,255,.8) 60%, transparent 60%) 210px 40px/95px 62px no-repeat;
}

.hero-copy{padding:34px 34px 24px}
.hero-copy h1{
    margin:0 0 18px;
    font-size:52px;
    line-height:1;
    text-transform:uppercase;
    letter-spacing:.02em;
    color:#123a83
}
.hero-copy h1 span{color:#67cc3b}
.hero-copy p{
    max-width:680px;
    margin:0;
    font-size:17px;
    line-height:1.7;
    color:#2f3c53
}

.hero-meta{
    padding:38px 26px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    gap:22px;
    border-left:1px solid #e6edf7
}
.meta-box{display:flex;gap:14px;align-items:center}
.meta-icon{
    width:54px;height:54px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-size:24px;font-weight:900;
    background:#edf3ff;color:#1940a0
}
.meta-icon.green{background:#edf9ef;color:#45aa2a}
.meta-box span{
    display:block;
    font-size:14px;
    color:#37517a
}
.meta-box strong{
    display:block;
    margin-top:4px;
    font-size:16px;
    color:#0f172a
}

.statement-main-grid{
    display:grid;
    grid-template-columns:405px 1fr;
    gap:22px;
    padding:18px
}
.panel{
    border:1px solid #d8e2ef;
    border-radius:16px;
    overflow:hidden;
    background:#fff
}
.panel-head{
    background:linear-gradient(135deg,#072a72 0%,#0c357f 80%);
    color:#fff;
    padding:14px 20px;
    font-size:16px;
    font-weight:900;
    text-transform:uppercase;
    letter-spacing:.03em;
    position:relative
}
.panel-head::after{
    content:"";
    position:absolute;
    right:-18px;top:0;
    width:52px;height:100%;
    background:#fff;
    transform:skewX(-28deg);
    opacity:.12
}
.panel-body{padding:16px 18px}

.employee-panel .info-row{
    display:grid;
    grid-template-columns:135px 8px 1fr;
    gap:8px;
    align-items:center;
    padding:8px 4px;
    border-bottom:1px solid #edf2f8
}
.employee-panel .info-row:last-child{border-bottom:0}
.employee-panel .info-row span{
    color:#31425e;
    font-size:12px;
    line-height:1.2;
    white-space:nowrap
}
.employee-panel .info-row span::before{
    content:"";
    display:inline-block;
    width:6px;
    height:6px;
    margin-right:7px;
    border-radius:50%;
    background:#1740a3;
    vertical-align:middle
}
.employee-panel .info-row strong{
    color:#111827;
    font-size:13px;
    font-weight:800;
    line-height:1.25;
    word-break:normal
}
.employee-panel .info-row::after{
    content:":";
    grid-column:2;
    grid-row:1;
    color:#43536d;
    font-weight:900
}

.summary-layout{
    display:grid;
    grid-template-columns:repeat(6,minmax(92px,1fr)) 190px;
    gap:0;
    align-items:stretch
}
.summary-stat{
    padding:12px 10px;
    border-right:1px solid #e7eef7;
    display:flex;
    flex-direction:column;
    justify-content:center;
    min-height:150px
}
.summary-stat span{
    color:#20409a;
    font-size:10.5px;
    font-weight:800;
    text-transform:uppercase;
    line-height:1.2;
    min-height:26px
}
.summary-stat strong{
    margin-top:14px;
    font-size:14px;
    color:#111827;
    font-weight:900;
    white-space:nowrap
}
.amount-card{
    margin:6px 0 6px 14px;
    border-radius:16px;
    padding:18px 14px;
    color:#fff;
    display:flex;
    flex-direction:column;
    justify-content:center;
    min-height:150px;
    background:linear-gradient(180deg,#072a72 0%,#0c357f 100%)
}
.amount-card span{
    font-size:14px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.03em;
    opacity:.95
}
.amount-value{
    margin-top:14px;
    font-size:18px;
    font-weight:900;
    line-height:1.15;
    white-space:nowrap
}
.amount-status{
    margin-top:12px;
    background:#f7fff6;
    color:#2d9b22;
    border-radius:10px;
    padding:9px 8px;
    text-align:center;
    font-weight:900;
    font-size:11px;
    white-space:nowrap
}
.amount-card.is-zero .amount-status{
    color:#6b7280;
    background:#f3f4f6
}

.details-panel{margin:0 18px 18px}
.billing-table{
    width:100%;
    table-layout:fixed;
    border-collapse:collapse;
    font-size:12px
}
.billing-table th{
    background:linear-gradient(180deg,#082d79 0%,#0c357f 100%);
    color:#fff;
    padding:10px 6px;
    border:1px solid rgba(255,255,255,.18);
    text-align:center;
    font-size:10.5px;
    line-height:1.25;
    text-transform:uppercase
}
.billing-table td{
    padding:10px 6px;
    border:1px solid #dfe7f1;
    background:#fff;
    text-align:center!important;
    vertical-align:middle!important;
    white-space:nowrap
}
.billing-table tbody tr:nth-child(even) td{background:#fbfdff}
.billing-table .num{
    text-align:center!important;
    vertical-align:middle!important
}
.billing-table .bill{
    color:#153b97;
    font-size:16px;
    font-weight:900
}
.status-pill{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:105px;
    margin:0 auto;
    padding:9px 12px;
    border-radius:12px;
    font-size:12px;
    font-weight:900;
    text-transform:uppercase
}
.status-pill.ok{background:#edf7ea;color:#319325}
.status-pill.zero{background:#f3f4f6;color:#6b7280}


.school-van-charge-panel{margin-top:0}
.sv-separate-pill{
    float:right;
    padding:4px 10px;
    border-radius:999px;
    background:#eaf3ff;
    color:#2764c6;
    font-size:11px;
    font-weight:700;
    letter-spacing:.04em;
}
.sv-statement-alert{
    display:flex;
    flex-direction:column;
    gap:4px;
    margin-bottom:14px;
    padding:12px 14px;
    border:1px solid #fdba74;
    border-radius:12px;
    background:#fff7ed;
    color:#9a3412;
}
.sv-statement-alert span{font-size:13px}
.sv-statement-table{margin-bottom:14px}
.status-pill.blocked{background:#fff1f2;color:#be123c}
.sv-statement-total{
    display:flex;
    justify-content:flex-end;
    align-items:center;
    gap:18px;
    padding-top:13px;
    border-top:1px solid #e5edf7;
    font-size:14px;
}
.sv-statement-total strong{font-size:17px;color:#12315f}
.sv-statement-empty{
    padding:14px;
    border:1px dashed #d8e2ef;
    border-radius:12px;
    color:#64748b;
}
.sv-statement-blocker{
    margin-top:12px;
    padding:10px 12px;
    border-radius:10px;
    background:#fff7ed;
    color:#9a3412;
    font-size:13px;
}
.sv-statement-note{
    margin-top:12px;
    color:#64748b;
    font-size:12px;
}

@media (max-width: 1400px){
    .statement-filter-grid{grid-template-columns:repeat(2,minmax(180px,1fr))}
    .statement-hero{grid-template-columns:1fr}
    .brand-panel::after{display:none}
    .hero-meta{border-left:0;border-top:1px solid #e6edf7;flex-direction:row;flex-wrap:wrap}
    .statement-main-grid{grid-template-columns:1fr}
    .summary-layout{grid-template-columns:repeat(2,minmax(120px,1fr))}
    .amount-card{margin:18px 0 0}
    }

@media print{
    .no-print,
    .cb-topbar,
    .cb-sidebar,
    .sidebar,
    .topbar,
    nav,
    header,
    footer{
        display:none !important
    }
    body{background:#fff}
    .statement-preview{
        border:0;
        border-radius:0;
        box-shadow:none;
        width:100%;
        overflow:hidden
    }
    .statement-hero{
        grid-template-columns:300px 1fr 220px
    }
    .statement-main-grid{
        grid-template-columns:320px 1fr
    }
    .summary-layout{
        grid-template-columns:repeat(6,minmax(90px,1fr)) 180px
    }
        @page{
        size:A4 landscape;
        margin:8mm
    }
}

.table-wrap{overflow:hidden}
</style>
@endsection
