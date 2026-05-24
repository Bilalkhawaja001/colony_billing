@extends('layouts.app')
@section('page_title','Facilities Management - Verification & Closure')
@section('page_subtitle','Completed work verification, rework decision and closure controls.')
@section('content')
@include('facilities._tabs')
@if(session('status'))<div class="card" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;margin-bottom:12px;">{{ session('status') }}</div>@endif
@if(isset($errors) && $errors->any())<div class="card" style="border-color:#fecaca;background:#fff7f7;color:#991b1b;margin-bottom:12px;">{{ $errors->first() }}</div>@endif
<div class="grid">
    <div class="col-12 card">
        <h3 class="section-title">Pending Verification</h3>
        <div class="fm-table-wrap"><table class="fm-table">
            <thead><tr><th>Work Order</th><th>Facility</th><th>Status</th><th>Completion Remarks</th><th>Accept</th><th>Require Rework</th></tr></thead>
            <tbody>
            @forelse($completedRows as $row)
                <tr><td>{{ $row->work_order_no }}</td><td>{{ $row->facility_code }} - {{ $row->facility_name }}</td><td>{{ $row->status }}</td><td>{{ $row->completion_remarks }}</td>
                    <td><form method="post" action="/facilities-management/work-orders/{{ $row->id }}/verify">@csrf<input type="hidden" name="verification_result" value="ACCEPTED"><textarea name="verification_remarks" rows="2" placeholder="Verification remarks"></textarea><button class="btn btn-primary" type="submit">Accept</button></form></td>
                    <td><form method="post" action="/facilities-management/work-orders/{{ $row->id }}/verify">@csrf<input type="hidden" name="verification_result" value="REWORK_REQUIRED"><textarea name="verification_remarks" rows="2" required placeholder="Required rework reason"></textarea><button class="btn" type="submit">Rework</button></form></td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No completed work awaiting verification.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
    <div class="col-12 card">
        <h3 class="section-title">Verified Pending Closure</h3>
        <div class="fm-table-wrap"><table class="fm-table">
            <thead><tr><th>Work Order</th><th>Facility</th><th>Verified By</th><th>Verified At</th><th>Remarks</th><th>Close</th></tr></thead>
            <tbody>
            @forelse($verifiedRows as $row)
                <tr><td>{{ $row->work_order_no }}</td><td>{{ $row->facility_code }} - {{ $row->facility_name }}</td><td>{{ $row->verified_by }}</td><td>{{ $row->verified_at }}</td><td>{{ $row->verification_remarks }}</td><td><form method="post" action="/facilities-management/work-orders/{{ $row->id }}/close">@csrf<textarea name="remarks" rows="2" placeholder="Closure remarks"></textarea><button class="btn btn-primary" type="submit">Close</button></form></td></tr>
            @empty
                <tr><td colspan="6" class="muted">No verified work awaiting closure.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
    <div class="col-12 card">
        <h3 class="section-title">Rework Required</h3>
        <div class="fm-table-wrap"><table class="fm-table"><thead><tr><th>Work Order</th><th>Facility</th><th>Reason</th><th>Action</th></tr></thead><tbody>
            @forelse($reworkRows as $row)
                <tr><td>{{ $row->work_order_no }}</td><td>{{ $row->facility_code }} - {{ $row->facility_name }}</td><td>{{ $row->verification_remarks }}</td><td><a class="btn" href="/facilities-management/work-orders">Return to Work Orders</a></td></tr>
            @empty
                <tr><td colspan="4" class="muted">No rework queue.</td></tr>
            @endforelse
        </tbody></table></div>
    </div>
</div>
@endsection
