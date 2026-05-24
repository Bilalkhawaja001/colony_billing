@extends('layouts.app')
@section('page_title','Facilities Management - Service Requests')
@section('page_subtitle','Facilities-only complaint, repair and planned work requests. No billing or inventory linkage.')
@section('content')
@include('facilities._tabs')
@if(session('status'))<div class="card" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;margin-bottom:12px;">{{ session('status') }}</div>@endif
@if(isset($errors) && $errors->any())<div class="card" style="border-color:#fecaca;background:#fff7f7;color:#991b1b;margin-bottom:12px;">{{ $errors->first() }}</div>@endif
<div class="grid">
    <div class="col-12 card">
        <h3 class="section-title">New Service Request</h3>
        <form method="post" action="/facilities-management/service-requests" class="fm-form">
            @csrf
            <div class="field"><label class="label">Request Type</label><select name="request_type" required>@foreach($requestTypes as $type)<option>{{ $type }}</option>@endforeach</select></div>
            <div class="field"><label class="label">Priority</label><select name="priority" required>@foreach($priorities as $priority)<option>{{ $priority }}</option>@endforeach</select></div>
            <div class="field"><label class="label">Approval Level</label><select name="approval_required_level" required>@foreach($approvalLevels as $level)<option>{{ $level }}</option>@endforeach</select></div>
            <div class="field"><label class="label">Work Category</label><select name="work_category_id" required>@foreach($workCategories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
            <div class="field wide">
                <label class="label">Registered Facility</label>
                <select name="facility_registry_id">
                    <option value="">Unregistered / manual location</option>
                    @foreach($facilities as $facility)
                        <option value="{{ $facility->id }}" @selected((string) old('facility_registry_id') === (string) $facility->id)>{{ $facility->facility_code }} - {{ $facility->facility_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field wide">
                <label class="label">Affected Component / Item</label>
                <select name="facility_component_type_id" required>
                    <option value="">Select affected component / item</option>
                    @foreach($componentTypeRows as $componentType)
                        <option value="{{ $componentType->id }}" @selected((string) old('facility_component_type_id') === (string) $componentType->id)>{{ $componentType->name }}</option>
                    @endforeach
                </select>
                <span class="muted">Complete standard item list for washroom, toilet and bathroom work requests.</span>
            </div>
            <div class="field full"><label class="label">Location Text <span class="muted">required if no registered facility</span></label><input name="location_text" placeholder="Exact site/location"></div>
            <div class="field full"><label class="label">Problem Description</label><textarea name="problem_description" rows="3" required></textarea></div>
            <div class="field"><label class="label">Emergency?</label><select name="emergency_flag"><option value="0">No</option><option value="1">Yes</option></select></div>
            <div class="field wide"><label class="label">Emergency Reason</label><input name="emergency_reason" placeholder="Mandatory when emergency = yes"></div>
            <div class="field"><label class="label">Material Required?</label><select name="material_required"><option value="0">No</option><option value="1">Yes</option></select></div>
            <div class="field"><label class="label">Estimated Cost</label><input name="estimated_cost" type="number" step="0.01"></div>
            <div class="field full"><label class="label">Material Remarks</label><textarea name="material_remarks" rows="2" placeholder="Remarks only; no stock issue/return."></textarea></div>
            <div class="field full"><button class="btn btn-primary" type="submit">Submit Request</button></div>
        </form>
    </div>

    <div class="col-12 card">
        <h3 class="section-title">Service Requests</h3>
        <form method="get" class="fm-form" style="margin-bottom:12px;">
            <div class="field"><label class="label">Status</label><input name="status" value="{{ $filters['status'] ?? '' }}" placeholder="SUBMITTED"></div>
            <div class="field"><label class="label">Priority</label><input name="priority" value="{{ $filters['priority'] ?? '' }}" placeholder="HIGH"></div>
            <div class="field"><label class="label">Category ID</label><input name="work_category_id" value="{{ $filters['work_category_id'] ?? '' }}"></div>
            <div class="field"><label class="label">&nbsp;</label><button class="btn" type="submit">Filter</button></div>
        </form>
        <div class="fm-table-wrap"><table class="fm-table">
            <thead><tr><th>No</th><th>Type</th><th>Facility / Location</th><th>Category</th><th>Priority</th><th>Status</th><th>Description</th><th>Approval</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->request_no }}</td><td>{{ $row->request_type }}</td><td>{{ $row->facility_code ? $row->facility_code.' - '.$row->facility_name : $row->location_text }}</td><td>{{ $row->category_name }}</td><td>{{ $row->priority }}</td><td>{{ $row->status }}</td><td>{{ $row->problem_description }}</td><td>{{ $row->approval_required_level }}</td>
                    <td>
                        @if($row->status === 'APPROVED')<form method="post" action="/facilities-management/service-requests/{{ $row->id }}/convert-work-order">@csrf<button class="btn btn-primary" type="submit">Create Work Order</button></form>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="muted">No service requests.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
</div>
@endsection
