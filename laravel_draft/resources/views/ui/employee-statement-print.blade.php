<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Bill Statement</title>
    <style>
        *{box-sizing:border-box}
        body{
            margin:0;
            background:#d9e1ea;
            color:#111;
            font-family:Arial, Helvetica, sans-serif;
            font-size:12px;
        }
        .print-actions{
            width:1050px;
            margin:18px auto 10px;
            text-align:right;
        }
        .print-actions button{
            background:#111827;
            color:#fff;
            border:0;
            padding:9px 16px;
            border-radius:6px;
            font-weight:700;
            cursor:pointer;
        }
        .sheet{
            width:1050px;
            min-height:742px;
            margin:0 auto 24px;
            background:#fff;
            padding:26px 30px;
            border:1px solid #999;
        }
        .letterhead{
            text-align:center;
            border-bottom:2px solid #111;
            padding-bottom:10px;
            margin-bottom:14px;
        }
        .letterhead h1{
            margin:0;
            font-size:24px;
            letter-spacing:.03em;
            text-transform:uppercase;
        }
        .letterhead .sub{
            margin-top:4px;
            font-size:12px;
            font-weight:700;
            letter-spacing:.08em;
            text-transform:uppercase;
        }
        .doc-meta{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:8px;
            margin-bottom:10px;
            font-size:12px;
        }
        .doc-meta div:nth-child(even){text-align:right}
        .title{
            text-align:center;
            border:1px solid #111;
            padding:8px;
            font-size:17px;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.04em;
            margin:12px 0;
        }
        .section-title{
            background:#f1f1f1;
            border:1px solid #111;
            border-bottom:0;
            padding:6px 8px;
            font-weight:800;
            text-transform:uppercase;
            font-size:12px;
        }
        .details{
            width:100%;
            border-collapse:collapse;
            margin-bottom:12px;
        }
        .details td{
            border:1px solid #111;
            padding:7px 8px;
            vertical-align:top;
        }
        .details .label{
            width:15%;
            background:#fafafa;
            font-weight:800;
            color:#222;
        }
        .details .value{
            width:35%;
            font-weight:700;
        }
        .billing-table{
            width:100%;
            border-collapse:collapse;
            margin-top:0;
            font-size:11px;
        }
        .billing-table th{
            border:1px solid #111;
            background:#e9ecef;
            padding:6px 5px;
            text-align:center;
            font-weight:800;
            text-transform:uppercase;
        }
        .billing-table td{
            border:1px solid #111;
            padding:6px 5px;
        }
        .num{text-align:right}
        .center{text-align:center}
        .total-row td{
            font-weight:800;
            background:#f7f7f7;
        }
        .school-van-blocked{
            border:1px solid #d78c2f;
            background:#fff8ee;
            color:#874c00;
            padding:8px 10px;
            margin:6px 0 9px;
            font-size:10px;
        }
        .school-van-note{
            font-size:9px;
            color:#555;
            margin:7px 0 10px;
        }
        .school-van-table td,
        .school-van-table th{
            font-size:9px;
        }

        .summary-wrap{
            display:grid;
            grid-template-columns:1fr 330px;
            gap:18px;
            margin-top:12px;
        }
        .cert{
            border:1px solid #111;
            padding:9px 10px;
            line-height:1.55;
            min-height:92px;
        }
        .amount-table{
            width:100%;
            border-collapse:collapse;
        }
        .amount-table td{
            border:1px solid #111;
            padding:7px 8px;
        }
        .amount-table .label{
            font-weight:800;
            background:#fafafa;
        }
        .amount-table .amount{
            text-align:right;
            font-weight:800;
        }
        .grand td{
            background:#eef4ff;
            color:#111;
            font-size:13px;
        }
        .signatures{
            display:grid;
            grid-template-columns:repeat(4,1fr);
            gap:22px;
            margin-top:46px;
        }
        .sig{
            text-align:center;
            border-top:1px solid #111;
            padding-top:7px;
            font-weight:800;
        }
        .footer{
            margin-top:14px;
            font-size:10px;
            color:#333;
            display:flex;
            justify-content:space-between;
            border-top:1px solid #bbb;
            padding-top:6px;
        }
        @media print{
            body{background:#fff}
            .print-actions{display:none}
            .sheet{
                width:auto;
                min-height:auto;
                margin:0;
                border:0;
                padding:8mm;
            }
            @page{size:A4 landscape;margin:8mm}
        }
    
        .charge-summary-table{
            width:100%;
            border-collapse:collapse;
            margin-bottom:7px;
            font-size:11px;
        }
        .charge-summary-table th{
            background:#e9ecef;
            border:1px solid #111;
            padding:6px 7px;
            text-transform:uppercase;
            font-weight:800;
        }
        .charge-summary-table td{
            border:1px solid #111;
            padding:7px 8px;
        }
        .charge-summary-table .payable-row td{
            background:#eef4ff;
            color:#111;
            font-size:13px;
        }
        .charge-summary-table .payable-row span{
            display:block;
            margin-top:3px;
            font-size:9px;
            font-weight:400;
            color:#475569;
        }

    
        .school-van-detail-print{
            margin-bottom:6px;
        }
        .compact-charge-summary{
            margin-bottom:8px;
        }
        .compact-charge-summary th:nth-child(1){width:60%}
        .compact-charge-summary th:nth-child(2){width:18%}
        .compact-charge-summary th:nth-child(3){width:22%}

    </style>
</head>
<body>
@php
    $rows = $statement ?? [];
    $sum = $summary ?? [];
    $schoolVan = $school_van ?? [];
    $schoolVanRows = $schoolVan['rows'] ?? [];
    $schoolVanBlocked = (bool)($schoolVan['blocked'] ?? false);
    $schoolVanGenerated = (bool)($schoolVan['generated'] ?? false);
    $electricTotal = round((float)($sum['total_amount'] ?? 0), 2);
    $schoolVanTotal = $schoolVanBlocked ? null : round((float)($schoolVan['total_amount'] ?? 0), 2);
    $schoolVanChildren = (float)collect($schoolVanRows)->sum('children_count');
    $schoolVanChargeableUnits = (float)collect($schoolVanRows)->sum('chargeable_units');
    $schoolVanPerKidRate = (!$schoolVanBlocked && $schoolVanChargeableUnits > 0)
        ? round(((float)$schoolVanTotal / $schoolVanChargeableUnits), 2)
        : null;
    $totalPayable = $schoolVanBlocked ? null : round($electricTotal + $schoolVanTotal, 2);
    $first = $rows[0] ?? [];
    $empName = $first['name'] ?? '';
    $empId = $first['company_id'] ?? ($company_id ?? '');
    $empDept = $first['department'] ?? '';
    $empDesignation = $first['designation'] ?? '';
    $empUnit = $first['unit_id'] ?? ($unit_id ?? '');
    $empRoom = $first['room_no'] ?? ($room_no ?? '');
    $docNo = 'EES/'.str_replace('-', '', (string)($from_month ?? '')).'/'.(($empId ?: $empUnit ?: $empRoom) ?: 'ALL');
@endphp

<div class="print-actions">
    <button onclick="window.print()">Print Statement</button>
</div>

<div class="sheet">
    <div class="letterhead">
        <h1>Colony Billing</h1>
        <div class="sub">Utility Billing Department</div>
    </div>

    <div class="doc-meta">
        <div><strong>Document No:</strong> {{ $docNo }}</div>
        <div><strong>Generated On:</strong> {{ now()->format('d-M-Y h:i A') }}</div>
        <div><strong>Billing Period:</strong> {{ $from_month ?? '' }} to {{ $to_month ?? '' }}</div>
        <div><strong>Report Type:</strong> Employee Bill Statement</div>
    </div>

    <div class="title">Employee Bill Statement</div>

    <div class="section-title">Employee / Residence Information</div>
    <table class="details">
        <tr>
            <td class="label">Employee Name</td>
            <td class="value">{{ $empName ?: 'All Employees' }}</td>
            <td class="label">Employee ID</td>
            <td class="value">{{ $empId ?: 'All' }}</td>
        </tr>
        <tr>
            <td class="label">Department</td>
            <td class="value">{{ $empDept ?: '-' }}</td>
            <td class="label">Designation</td>
            <td class="value">{{ $empDesignation ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">House / Unit</td>
            <td class="value">{{ $empUnit ?: 'All' }}</td>
            <td class="label">Room Address</td>
            <td class="value">{{ $empRoom ?: 'All' }}</td>
        </tr>
    </table>

    <div class="section-title">Electric Billing Calculation</div>
    <table class="billing-table electric-calculation-print">
        <thead>
            <tr>
                <th>Month</th>
                <th>Attendance</th>
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
                <td class="center">{{ $row['month_cycle'] ?? '' }}</td>
                <td class="num">{{ number_format((float)($row['active_days'] ?? 0), 2) }}</td>
                <td class="num">{{ number_format((float)($row['emp_used_units'] ?? 0), 4) }}</td>
                <td class="num">{{ number_format((float)($row['eligible_units'] ?? 0), 4) }}</td>
                <td class="num">{{ number_format((float)($row['billable_units'] ?? 0), 4) }}</td>
                <td class="num">{{ number_format((float)($row['rate'] ?? 0), 2) }}</td>
                <td class="num"><strong>{{ number_format((float)($row['electric_amount'] ?? 0), 2) }}</strong></td>
                <td class="center">{{ $row['billing_status'] ?? '' }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="center">No employee statement rows found.</td></tr>
        @endforelse

            <tr class="total-row">
                <td class="center"><strong>Total</strong></td>
                <td class="num">{{ number_format((float)($sum['total_active_days'] ?? 0), 2) }}</td>
                <td class="num">{{ number_format((float)($sum['total_used_units'] ?? 0), 4) }}</td>
                <td class="num">{{ number_format((float)($sum['total_eligible_units'] ?? 0), 4) }}</td>
                <td class="num">{{ number_format((float)($sum['total_billable_units'] ?? 0), 4) }}</td>
                <td></td>
                <td class="num"><strong>{{ number_format((float)($sum['total_amount'] ?? 0), 2) }}</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">School Van Billing Calculation</div>
    <table class="billing-table school-van-detail-print">
        <thead>
            <tr>
                <th>Month</th>
                <th>Children</th>
                <th>Per Kid Rate (Rs.)</th>
                <th>Chargeable Units</th>
                <th>School Van Charge (Rs.)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($schoolVanRows as $svRow)
            <tr>
                <td class="center">{{ $svRow['month_cycle'] ?? '' }}</td>
                <td class="num">{{ number_format((float)($svRow['children_count'] ?? 0)) }}</td>
                <td class="num">
                    {{ ((float)($svRow['chargeable_units'] ?? 0) > 0 && !$schoolVanBlocked) ? number_format(((float)($svRow['payable_amount'] ?? 0) / (float)$svRow['chargeable_units']), 2) : 'Pending' }}
                </td>
                <td class="num">{{ number_format((float)($svRow['chargeable_units'] ?? 0), 2) }}</td>
                <td class="num"><strong>{{ $schoolVanBlocked ? 'Pending Correction' : number_format((float)($svRow['payable_amount'] ?? 0), 2) }}</strong></td>
                <td class="center">{{ $schoolVanBlocked ? 'BLOCKED' : ($schoolVanGenerated ? 'GENERATED' : 'PREVIEW') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="center">No school van charge found for selected employee and period.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    @if($schoolVanBlocked)
        <div class="school-van-blocked">
            <strong>School Van Charge Pending Correction.</strong>
            Total bill amount cannot be finalized until the blocked transport expense is corrected.
        </div>
    @endif

    <div class="school-van-note">
        {{ $schoolVanGenerated ? 'Official generated school van charge included in Net Bill Amount.' : 'Calculated preview school van charge; not officially generated.' }}
    </div>

    <div class="section-title">Bill Amount Summary</div>
    <table class="charge-summary-table compact-charge-summary">
        <thead>
            <tr>
                <th>Charge Component</th>
                <th>Status</th>
                <th>Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Electric Bill</strong></td>
                <td class="center">{{ $electricTotal > 0 ? 'POSITIVE BILL' : 'ZERO BILL' }}</td>
                <td class="num"><strong>{{ number_format($electricTotal, 2) }}</strong></td>
            </tr>
            <tr>
                <td><strong>School Van Charge</strong></td>
                <td class="center">{{ $schoolVanBlocked ? 'BLOCKED' : ($schoolVanGenerated ? 'GENERATED' : 'PREVIEW') }}</td>
                <td class="num"><strong>{{ $schoolVanBlocked ? 'Pending' : number_format((float)$schoolVanTotal, 2) }}</strong></td>
            </tr>
            <tr class="payable-row">
                <td colspan="2"><strong>Net Bill Amount</strong><span>Electric Bill + School Van Charge</span></td>
                <td class="num"><strong>{{ $totalPayable === null ? 'Pending Correction' : number_format($totalPayable, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="summary-wrap">
        <div class="cert">
            <strong>Certification:</strong><br>
            This Colony Utility Bill has been generated for the selected billing period from billing records.
        </div>

        <table class="amount-table">
            <tr>
                <td class="label">Electric Bill</td>
                <td class="amount">{{ number_format($electricTotal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">School Van Charge</td>
                <td class="amount">{{ $schoolVanBlocked ? 'Pending' : number_format((float)$schoolVanTotal, 2) }}</td>
            </tr>
            <tr class="grand">
                <td class="label">Net Bill Amount</td>
                <td class="amount">{{ $totalPayable === null ? 'Pending Correction' : number_format($totalPayable, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="signatures">
        <div class="sig">Prepared By</div>
        <div class="sig">Checked By</div>
        <div class="sig">Accounts Verified</div>
        <div class="sig">Approved By</div>
    </div>

    <div class="footer">
        <span>System Generated Statement</span>
        <span>Colony Billing - Administration</span>
    </div>
</div>
</body>
</html>
