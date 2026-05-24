@extends('layouts.app')
@section('page_title','Facilities Management - Approval Queue')
@section('page_subtitle','Facilities-specific approvals only. Billing and School Van approvals are untouched.')
@section('content')
@include('facilities._tabs')
@if(session('status'))<div class="card" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;margin-bottom:12px;">{{ session('status') }}</div>@endif
@if(isset($errors) && $errors->any())<div class="card" style="border-color:#fecaca;background:#fff7f7;color:#991b1b;margin-bottom:12px;">{{ $errors->first() }}</div>@endif
<div class="grid">
    <div class="col-12 card">
        <h3 class="section-title">Pending Requests</h3>
        <div class="fm-table-wrap"><table class="fm-table">
            <thead><tr><th>No</th><th>Facility / Location</th><th>Category</th><th>Priority</th><th>Level</th><th>Description</th><th>Approve</th><th>Reject</th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->request_no }}<br><span class="muted">{{ $row->request_type }}</span></td>
                    <td>{{ $row->facility_code ? $row->facility_code.' - '.$row->facility_name : $row->location_text }}</td>
                    <td>{{ $row->category_name }}</td><td>{{ $row->priority }}</td><td>{{ $row->approval_required_level }}</td><td>{{ $row->problem_description }}</td>
                    <td><form method="post" action="/facilities-management/service-requests/{{ $row->id }}/approve">@csrf<textarea name="approval_remarks" rows="2" placeholder="Remarks"></textarea><button class="btn btn-primary" type="submit">Approve</button></form></td>
                    <td><form method="post" action="/facilities-management/service-requests/{{ $row->id }}/reject">@csrf<textarea name="rejected_reason" rows="2" required placeholder="Required reason"></textarea><button class="btn" type="submit">Reject</button></form></td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">No pending approvals.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
    <div class="col-12 card">
        <h3 class="section-title">Recent Approval History</h3>
        <div class="fm-table-wrap"><table class="fm-table">
            <thead><tr><th>Request</th><th>Decision</th><th>Level</th><th>By</th><th>At</th><th>Remarks</th></tr></thead>
            <tbody>
            @forelse($history as $row)
                <tr><td>{{ $row->request_no }}</td><td>{{ $row->decision }}</td><td>{{ $row->decision_level }}</td><td>{{ $row->decided_by_user_id }}</td><td>{{ $row->decided_at }}</td><td>{{ $row->remarks }}</td></tr>
            @empty
                <tr><td colspan="6" class="muted">No approval history yet.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
</div>
@endsection
