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
            <div class="field"><label class="label">Work Category</label><select id="fm-work-category-id" name="work_category_id" required><option value="">Select work category</option>@foreach($workCategories as $category)<option value="{{ $category->id }}" @selected((string) old('work_category_id') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></div>
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
                        <option value="{{ $facility->facility_code }} | {{ $facility->facility_name }} | {{ $facility->specific_location }} | {{ $facility->facility_type }}"></option>
                    @endforeach
                </datalist>

                <span class="muted" id="fm-facility-help">Search and select a registered facility, or leave blank for manual location.</span>
            </div>

            <div id="fm-facility-source" hidden>
                @foreach($facilities as $facility)
                    <span
                        data-id="{{ $facility->id }}"
                        data-label="{{ $facility->facility_code }} | {{ $facility->facility_name }} | {{ $facility->specific_location }} | {{ $facility->facility_type }}"
                    ></span>
                @endforeach
            </div>
            <div class="field full">
                <label class="label">Affected Items / Required Work</label>
                <div id="fm-request-items" class="fm-request-items"></div>
                <button id="fm-add-request-item" class="btn" type="button" style="margin-top:10px;">+ Add Another Item</button>
                <span class="muted" style="display:block;margin-top:8px;">Add all affected items for this location and selected work category. Different work categories require separate requests.</span>
            </div>

            <div id="fm-category-component-source" hidden>
                @foreach($categoryComponentRows as $mapping)
                    <span
                        data-category-id="{{ $mapping->work_category_id }}"
                        data-component-id="{{ $mapping->component_type_id }}"
                        data-component-name="{{ $mapping->component_name }}"
                    ></span>
                @endforeach
            </div>
            <div class="field full"><label class="label">Location Text <span class="muted">required if no registered facility</span></label><input name="location_text" placeholder="Exact site/location"></div>
            <div class="field full"><label class="label">General Request Description</label><textarea name="problem_description" rows="3" required>{{ old('problem_description') }}</textarea></div>
            <div class="field"><label class="label">Emergency?</label><select name="emergency_flag"><option value="0">No</option><option value="1">Yes</option></select></div>
            <div class="field wide"><label class="label">Emergency Reason</label><input name="emergency_reason" placeholder="Mandatory when emergency = yes"></div>
            <div class="field full"><span class="muted">Material, cost and procurement source will be recorded item-wise above. No inventory transaction is created.</span></div>
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
                    <td>{{ $row->request_no }}</td><td>{{ $row->requested_at ? \Carbon\Carbon::parse($row->requested_at)->format('d/m/Y') : '-' }}</td><td>{{ $row->completion_date ? \Carbon\Carbon::parse($row->completion_date)->format('d/m/Y') : 'Pending' }}</td><td>{{ $row->requester_employee_id }} - {{ $row->requester_name_snapshot }}</td><td>{{ $row->request_type }}</td><td>{{ $row->facility_code ? $row->facility_code.' - '.$row->facility_name : $row->location_text }}</td><td>@foreach(($requestItems[$row->id] ?? collect()) as $item)<div><strong>{{ $item->component_name }}</strong> — {{ str_replace('_', ' ', $item->work_action) }}<br><span class="muted">{{ $item->problem_detail }} | Cost: {{ number_format($item->total_cost, 2) }} | {{ str_replace('_', ' ', $item->material_source) }}</span></div>@endforeach</td><td>{{ $row->category_name }}</td><td>{{ $row->priority }}</td><td>{{ $row->status }}</td><td>{{ $row->problem_description }}</td><td>{{ $row->approval_required_level }}</td>
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
    const categorySelect = document.getElementById('fm-work-category-id');
    const itemsContainer = document.getElementById('fm-request-items');
    const addItemButton = document.getElementById('fm-add-request-item');
    const workActions = @json($itemWorkActions);
    const materialSources = @json($itemMaterialSources);
    @php
        $defaultRequestItems = [[
            'facility_component_type_id' => '',
            'work_action' => 'SERVICE',
            'problem_detail' => '',
            'part_material_used' => '',
            'quantity' => '1',
            'unit' => '',
            'unit_cost' => '0',
            'material_source' => 'NOT_REQUIRED',
            'remarks' => ''
        ]];
    @endphp
    const oldItems = @json(old('items', $defaultRequestItems));

    if (!categorySelect || !itemsContainer || !addItemButton) {
        return;
    }

    const componentsByCategory = {};

    document.querySelectorAll('#fm-category-component-source [data-category-id]').forEach(function (node) {
        const categoryId = node.dataset.categoryId;

        if (!componentsByCategory[categoryId]) {
            componentsByCategory[categoryId] = [];
        }

        componentsByCategory[categoryId].push({
            id: node.dataset.componentId,
            name: node.dataset.componentName
        });
    });

    function label(value) {
        return value.replaceAll('_', ' ').replace(/\b\w/g, function (letter) {
            return letter.toUpperCase();
        });
    }

    function populateComponents(select, selectedValue) {
        const options = componentsByCategory[categorySelect.value] || [];
        select.innerHTML = '<option value="">Select affected item</option>';

        options.forEach(function (item) {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            option.selected = String(selectedValue || '') === String(item.id);
            select.appendChild(option);
        });

        if (!options.some(function (item) { return String(item.id) === String(selectedValue || ''); })) {
            select.value = '';
        }
    }

    function createSelect(name, values, selectedValue) {
        const select = document.createElement('select');
        select.name = name;
        select.required = true;

        values.forEach(function (value) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label(value);
            option.selected = String(value) === String(selectedValue || '');
            select.appendChild(option);
        });

        return select;
    }

    function reindexRows() {
        itemsContainer.querySelectorAll('.fm-request-item-row').forEach(function (row, index) {
            row.querySelector('.fm-item-title').textContent = 'Item ' + (index + 1);
            row.querySelectorAll('[data-field]').forEach(function (field) {
                field.name = 'items[' + index + '][' + field.dataset.field + ']';
            });
        });
    }

    function syncMaterialFields(row, clearWhenHidden) {
        const actionSelect = row.querySelector('[data-field="work_action"]');
        const materialFields = row.querySelector('.fm-material-fields');
        const showMaterialFields = ['PART_CHANGE', 'INSTALLATION', 'REFILL'].includes(actionSelect.value);

        materialFields.style.display = showMaterialFields ? 'block' : 'none';

        if (!showMaterialFields && clearWhenHidden) {
            row.querySelector('[data-field="part_material_used"]').value = '';
            row.querySelector('[data-field="quantity"]').value = '1';
            row.querySelector('[data-field="unit"]').value = '';
            row.querySelector('[data-field="unit_cost"]').value = '0';
            row.querySelector('[data-field="material_source"]').value = 'NOT_REQUIRED';
            row.querySelector('[data-field="remarks"]').value = '';
        }
    }

    function addItem(data) {
        const row = document.createElement('div');
        row.className = 'fm-request-item-row card soft';
        row.style.marginBottom = '10px';
        row.innerHTML = ''
            + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">'
            + '<strong class="fm-item-title"></strong>'
            + '<button type="button" class="btn fm-remove-item">Remove</button>'
            + '</div>'
            + '<div class="fm-form fm-item-grid">'
            + '<div class="field wide"><label class="label">Affected Component / Item</label><select data-field="facility_component_type_id" required></select></div>'
            + '<div class="field"><label class="label">Work Action</label><span class="fm-action-holder"></span></div>'
            + '<div class="field full"><label class="label">Issue / Service / Part Change Detail</label><textarea data-field="problem_detail" rows="2" required></textarea></div>'
            + '<div class="fm-material-fields field full" style="display:none;">'
            + '<div class="fm-form">'
            + '<div class="field wide"><label class="label">Part / Material Used</label><input data-field="part_material_used" placeholder="Part or material used"></div>'
            + '<div class="field"><label class="label">Quantity</label><input data-field="quantity" type="number" step="0.01" min="0.01" required></div>'
            + '<div class="field"><label class="label">Unit</label><input data-field="unit" placeholder="Nos / Kg / Ltr"></div>'
            + '<div class="field"><label class="label">Unit Cost</label><input data-field="unit_cost" type="number" step="0.01" min="0"></div>'
            + '<div class="field"><label class="label">Source</label><span class="fm-source-holder"></span></div>'
            + '<div class="field full"><label class="label">Remarks</label><input data-field="remarks" placeholder="Optional"></div>'
            + '</div>'
            + '</div>'
            + '</div>';

        const componentSelect = row.querySelector('[data-field="facility_component_type_id"]');
        populateComponents(componentSelect, data.facility_component_type_id || '');

        const actionSelect = createSelect('', workActions, data.work_action || 'SERVICE');
        actionSelect.dataset.field = 'work_action';
        row.querySelector('.fm-action-holder').appendChild(actionSelect);

        const sourceSelect = createSelect('', materialSources, data.material_source || 'NOT_REQUIRED');
        sourceSelect.dataset.field = 'material_source';
        row.querySelector('.fm-source-holder').appendChild(sourceSelect);

        row.querySelector('[data-field="problem_detail"]').value = data.problem_detail || '';
        row.querySelector('[data-field="part_material_used"]').value = data.part_material_used || '';
        row.querySelector('[data-field="quantity"]').value = data.quantity || '1';
        row.querySelector('[data-field="unit"]').value = data.unit || '';
        row.querySelector('[data-field="unit_cost"]').value = data.unit_cost || '0';
        row.querySelector('[data-field="remarks"]').value = data.remarks || '';

        actionSelect.addEventListener('change', function () {
            syncMaterialFields(row, true);
        });
        syncMaterialFields(row, false);

        row.querySelector('.fm-remove-item').addEventListener('click', function () {
            if (itemsContainer.querySelectorAll('.fm-request-item-row').length > 1) {
                row.remove();
                reindexRows();
            }
        });

        itemsContainer.appendChild(row);
        reindexRows();
    }

    categorySelect.addEventListener('change', function () {
        itemsContainer.querySelectorAll('[data-field="facility_component_type_id"]').forEach(function (select) {
            populateComponents(select, select.value);
        });
    });

    addItemButton.addEventListener('click', function () {
        addItem({
            facility_component_type_id: '',
            work_action: 'SERVICE',
            problem_detail: '',
            part_material_used: '',
            quantity: '1',
            unit: '',
            unit_cost: '0',
            material_source: 'NOT_REQUIRED',
            remarks: ''
        });
    });

    oldItems.forEach(function (item) {
        addItem(item);
    });
});
</script>


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
