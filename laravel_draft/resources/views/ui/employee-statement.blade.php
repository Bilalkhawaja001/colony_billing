@extends('layouts.app')

@section('page_title','Employee Statement')
@section('page_subtitle','Employee-wise electric billing statement for selected month.')

@section('content')
@php
    $monthCycle = $month_cycle ?? request('month_cycle', '05-2026');
    $companyId = $company_id ?? request('company_id', '');
    $q = request('q', '');
    $status = request('status', '');
    $rows = $statement ?? [];
@endphp

<div class="grid">
    <div class="col-12 card">
        <form method="get" action="/reports/employee-statement" class="form-grid">
            <div class="field col-3">
                <label class="label">Month Cycle</label>
                <input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY">
            </div>
            <div class="field col-3">
                <label class="label">Company ID</label>
                <input name="company_id" value="{{ $companyId }}" placeholder="Employee ID">
            </div>
            <div class="field col-3">
                <label class="label">Search</label>
                <input name="q" value="{{ $q }}" placeholder="Name / Unit / Room">
            </div>
            <div class="field col-2">
                <label class="label">Status</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="positive" @selected($status === 'positive')>Positive Bill</option>
                    <option value="zero" @selected($status === 'zero')>Zero Bill</option>
                </select>
            </div>
            <div class="col-1" style="display:flex;align-items:flex-end">
                <button class="btn btn-primary" type="submit">Load</button>
            </div>
        </form>
    </div>

    <div class="col-12 card">
        <div class="toolbar sticky-actions" style="justify-content:space-between">
            <div>
                <strong>Total Amount:</strong> {{ number_format((float)($total_amount ?? 0), 2) }}
                <span style="margin-left:14px"><strong>Rows:</strong> {{ count($rows) }}</span>
            </div>
            <div>
                <a class="btn" href="/reports/employee-statement?{{ http_build_query(array_merge(request()->query(), ['format'=>'json'])) }}">JSON</a>
                <a class="btn" href="/reports/employee-statement/export?{{ http_build_query(request()->query()) }}">Export CSV</a>
                <a class="btn" href="/reports/employee-statement/export?{{ http_build_query(array_merge(request()->query(), ['format'=>'pdf'])) }}">Export PDF</a>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Company ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Unit</th>
                        <th>Room</th>
                        <th>Active Days</th>
                        <th>Used Units</th>
                        <th>Eligible Units</th>
                        <th>Billable Units</th>
                        <th>Rate</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['month_cycle'] ?? '' }}</td>
                        <td>{{ $row['company_id'] ?? '' }}</td>
                        <td>{{ $row['name'] ?? '' }}</td>
                        <td>{{ $row['department'] ?? '' }}</td>
                        <td>{{ $row['unit_id'] ?? '' }}</td>
                        <td>{{ $row['room_no'] ?? '' }}</td>
                        <td>{{ $row['active_days'] ?? '' }}</td>
                        <td>{{ $row['emp_used_units'] ?? '' }}</td>
                        <td>{{ $row['eligible_units'] ?? '' }}</td>
                        <td>{{ $row['billable_units'] ?? '' }}</td>
                        <td>{{ $row['rate'] ?? '' }}</td>
                        <td>{{ number_format((float)($row['electric_amount'] ?? 0), 2) }}</td>
                        <td>{{ $row['billing_status'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13"><div class="empty">No employee statement rows found.</div></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(!empty($note))
            <div class="empty" style="margin-top:12px;text-align:left">{{ $note }}</div>
        @endif
    </div>
</div>
@endsection
