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
            <div class="field">
                <label class="label">Request Date</label>
                <div class="fm-single-date-control fm-request-date-control" style="position:relative;width:100%;">
                    <input id="fm-request-date-display" value="{{ old('request_date') ? \Carbon\Carbon::parse(old('request_date'))->format('d/m/Y') : now()->format('d/m/Y') }}" placeholder="dd/mm/yyyy" readonly style="width:100%;padding-right:44px;">
                    <span aria-hidden="true" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:16px;pointer-events:none;">📅</span>
                    <input id="fm-request-date-picker" name="request_date" type="date" value="{{ old('request_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required aria-label="Select request date" style="position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer;">
                </div>
                <span class="muted">Format: dd/mm/yyyy</span>
            </div>
            <div class="field wide">
                <label class="label">Requester Employee ID</label>
                <input id="fm-requester-employee-id" name="requester_employee_id" list="fm-requester-list" value="{{ old('requester_employee_id') }}" placeholder="Search / enter Employee ID" required autocomplete="off">
                <datalist id="fm-requester-list">
                    @foreach($requesterEmployees as $employee)
                        <option value="{{ $employee->company_id }}">{{ $employee->name }} - {{ $employee->designation }}</option>
                    @endforeach
                </datalist>
            </div>
            <div class="field"><label class="label">Requester Name</label><input id="fm-requester-name" readonly></div>
            <div class="field"><label class="label">Designation</label><input id="fm-requester-designation" readonly></div>
            <div class="field"><label class="label">Department</label><input id="fm-requester-department" readonly></div>
            <div class="field"><label class="label">Section</label><input id="fm-requester-section" readonly></div>
            <div class="field"><label class="label">Sub Section</label><input id="fm-requester-sub-section" readonly></div>
            <div class="field"><label class="label">Mobile No.</label><input id="fm-requester-mobile" readonly></div>

            <div id="fm-requester-source" hidden>
                @foreach($requesterEmployees as $employee)
                    <span
                        data-company-id="{{ $employee->company_id }}"
                        data-name="{{ $employee->name }}"
                        data-designation="{{ $employee->designation }}"
                        data-department="{{ $employee->department }}"
                        data-section="{{ $employee->section }}"
                        data-sub-section="{{ $employee->sub_section }}"
                        data-mobile="{{ $employee->mobile_no }}"
                    ></span>
                @endforeach
            </div>

            <div class="field"><label class="label">Request Type</label><select name="request_type" required>@foreach($requestTypes as $type)<option>{{ $type }}</option>@endforeach</select></div>
            <div class="field"><label class="label">Priority</label><select name="priority" required>@foreach($priorities as $priority)<option>{{ $priority }}</option>@endforeach</select></div>
            <div class="field"><label class="label">Approval Level</label><select name="approval_required_level" required>@foreach($approvalLevels as $level)<option>{{ $level }}</option>@endforeach</select></div>
            <div class="field"><label class="label">Work Category</label><select name="work_category_id" required>@foreach($workCategories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
            <div class="field wide">
                <label class="label">Registered Facility</label>
                <input
                    id="fm-facility-search"
                    list="fm-facility-list"
                    placeholder="Search facility code, department or area"
                    autocomplete="off"
                >
                <input id="fm-facility-id" type="hidden" name="facility_registry_id" value="{{ old('facility_registry_id') }}">

                <datalist id="fm-facility-list">
                    @foreach($facilities as $facility)
                        <option value="{{ $facility->facility_code }} - {{ $facility->facility_name }}"></option>
                    @endforeach
                </datalist>

                <span class="muted" id="fm-facility-help">Search and select a registered facility, or leave blank for manual location.</span>
            </div>

            <div id="fm-facility-source" hidden>
                @foreach($facilities as $facility)
                    <span
                        data-id="{{ $facility->id }}"
                        data-label="{{ $facility->facility_code }} - {{ $facility->facility_name }}"
                    ></span>
                @endforeach
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
            <thead><tr><th>No</th><th>Request Date</th><th>Completion Date</th><th>Requester</th><th>Type</th><th>Facility / Location</th><th>Affected Item</th><th>Category</th><th>Priority</th><th>Status</th><th>Description</th><th>Approval</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->request_no }}</td><td>{{ $row->requested_at ? \Carbon\Carbon::parse($row->requested_at)->format('d/m/Y') : '-' }}</td><td>{{ $row->completion_date ? \Carbon\Carbon::parse($row->completion_date)->format('d/m/Y') : 'Pending' }}</td><td>{{ $row->requester_employee_id }} - {{ $row->requester_name_snapshot }}</td><td>{{ $row->request_type }}</td><td>{{ $row->facility_code ? $row->facility_code.' - '.$row->facility_name : $row->location_text }}</td><td>{{ $row->affected_component_name }}</td><td>{{ $row->category_name }}</td><td>{{ $row->priority }}</td><td>{{ $row->status }}</td><td>{{ $row->problem_description }}</td><td>{{ $row->approval_required_level }}</td>
                    <td>
                        @if($row->status === 'APPROVED')<form method="post" action="/facilities-management/service-requests/{{ $row->id }}/convert-work-order">@csrf<button class="btn btn-primary" type="submit">Create Work Order</button></form>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="13" class="muted">No service requests.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const employeeInput = document.getElementById('fm-requester-employee-id');
    const fields = {
        name: document.getElementById('fm-requester-name'),
        designation: document.getElementById('fm-requester-designation'),
        department: document.getElementById('fm-requester-department'),
        section: document.getElementById('fm-requester-section'),
        subSection: document.getElementById('fm-requester-sub-section'),
        mobile: document.getElementById('fm-requester-mobile')
    };

    const employees = {};
    document.querySelectorAll('#fm-requester-source [data-company-id]').forEach(function (node) {
        employees[node.dataset.companyId] = {
            name: node.dataset.name || '',
            designation: node.dataset.designation || '',
            department: node.dataset.department || '',
            section: node.dataset.section || '',
            subSection: node.dataset.subSection || '',
            mobile: node.dataset.mobile || ''
        };
    });

    function loadRequester() {
        const employee = employees[employeeInput.value.trim()] || {
            name: '', designation: '', department: '', section: '', subSection: '', mobile: ''
        };

        fields.name.value = employee.name;
        fields.designation.value = employee.designation;
        fields.department.value = employee.department;
        fields.section.value = employee.section;
        fields.subSection.value = employee.subSection;
        fields.mobile.value = employee.mobile;
    }

    employeeInput.addEventListener('input', loadRequester);
    employeeInput.addEventListener('change', loadRequester);
    loadRequester();
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    function fmFormatDate(value) {
        if (!value) return '';
        const parts = value.split('-');
        return parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : '';
    }

    const requestPicker = document.getElementById('fm-request-date-picker');
    const requestDisplay = document.getElementById('fm-request-date-display');

    if (requestPicker && requestDisplay) {
        requestDisplay.value = fmFormatDate(requestPicker.value);
        requestPicker.addEventListener('change', function () {
            requestDisplay.value = fmFormatDate(requestPicker.value);
        });
    }
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const facilitySearch = document.getElementById('fm-facility-search');
    const facilityId = document.getElementById('fm-facility-id');
    const facilityHelp = document.getElementById('fm-facility-help');

    if (!facilitySearch || !facilityId) {
        return;
    }

    const facilitiesByLabel = {};
    let initialLabel = '';

    document.querySelectorAll('#fm-facility-source [data-id]').forEach(function (node) {
        facilitiesByLabel[node.dataset.label] = node.dataset.id;

        if (node.dataset.id === facilityId.value) {
            initialLabel = node.dataset.label;
        }
    });

    if (initialLabel) {
        facilitySearch.value = initialLabel;
    }

    function syncFacilitySelection() {
        const selectedId = facilitiesByLabel[facilitySearch.value] || '';
        facilityId.value = selectedId;

        if (!facilitySearch.value) {
            facilityHelp.textContent = 'Manual location mode: enter exact location below.';
        } else if (selectedId) {
            facilityHelp.textContent = 'Registered facility selected.';
        } else {
            facilityHelp.textContent = 'Select a facility from the search results, or clear the field for manual location.';
        }
    }

    facilitySearch.addEventListener('input', syncFacilitySelection);
    facilitySearch.addEventListener('change', syncFacilitySelection);
    syncFacilitySelection();
});
</script>

@endsection
