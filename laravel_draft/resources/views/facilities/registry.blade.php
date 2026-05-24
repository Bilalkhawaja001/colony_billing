@extends('layouts.app')
@section('page_title','Facilities Management - Facility Registry')
@section('page_subtitle','Permanent master registry for physical facilities and linked components/fixtures.')
@section('content')
@include('facilities._tabs')
@if(session('status'))<div class="card" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;margin-bottom:12px;">{{ session('status') }}</div>@endif
<div class="grid">
    <div class="col-12 card">
        <h3 class="section-title">Add / Update Facility</h3>
        <form method="post" action="/facilities-management/registry/facilities" class="fm-form">
            @csrf
            <div class="field"><label class="label">Facility Code</label><input name="facility_code" required placeholder="FM-001"></div>
            <div class="field"><label class="label">Facility Name</label><input name="facility_name" required placeholder="Bachelor Colony 1 Washroom 1"></div>
            <div class="field"><label class="label">Facility Type</label><select name="facility_type" required>@foreach($facilityTypes as $type)<option>{{ $type }}</option>@endforeach</select></div>
            <div class="field"><label class="label">Status</label><select name="status">@foreach($statuses as $status)<option>{{ $status }}</option>@endforeach</select></div>
            <div class="field"><label class="label">Section</label><input name="section" placeholder="Weaving"></div>
            <div class="field"><label class="label">Area</label><input name="area" placeholder="Bachelor Colony 1"></div>
            <div class="field wide"><label class="label">Specific Location</label><input name="specific_location" placeholder="Ground Floor / Room 1"></div>
            <div class="field"><label class="label">Condition</label><select name="condition"><option value="">Not assessed</option>@foreach($conditions as $condition)<option>{{ $condition }}</option>@endforeach</select></div>
            <div class="field"><label class="label">Active</label><select name="is_active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
            <div class="field full"><label class="label">Notes</label><textarea name="notes" rows="2" placeholder="Operational notes only; no billing linkage."></textarea></div>
            <div class="field full"><button class="btn btn-primary" type="submit">Save Facility</button></div>
        </form>
    </div>

    <div class="col-12 card">
        <h3 class="section-title">Facility Registry</h3>
        <div class="fm-table-wrap">
            <table class="fm-table">
                <thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Section</th><th>Area</th><th>Location</th><th>Status</th><th>Condition</th><th>Active</th></tr></thead>
                <tbody>
                @forelse($facilities as $facility)
                    <tr>
                        <td>{{ $facility->facility_code }}</td><td>{{ $facility->facility_name }}</td><td>{{ $facility->facility_type }}</td><td>{{ $facility->section }}</td><td>{{ $facility->area }}</td><td>{{ $facility->specific_location }}</td><td>{{ $facility->status }}</td><td>{{ $facility->condition }}</td><td>{{ $facility->is_active ? 'Yes' : 'No' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="muted">No registered facilities yet. Excel data has not been imported blindly.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12 card">
        <h3 class="section-title">Add Component / Fixture</h3>
        <form method="post" action="/facilities-management/registry/components" class="fm-form">
            @csrf
            <div class="field wide"><label class="label">Facility</label><select name="facility_id" required>@foreach($facilities as $facility)<option value="{{ $facility->id }}">{{ $facility->facility_code }} - {{ $facility->facility_name }}</option>@endforeach</select></div>
            <div class="field"><label class="label">Component Type</label><select name="component_type" required>@foreach($componentTypes as $type)<option>{{ $type }}</option>@endforeach</select></div>
            <div class="field"><label class="label">Quantity</label><input name="quantity" type="number" step="0.01" value="1"></div>
            <div class="field"><label class="label">Component Name</label><input name="component_name" placeholder="Optional label"></div>
            <div class="field"><label class="label">Condition</label><select name="condition"><option value="">Not assessed</option>@foreach($conditions as $condition)<option>{{ $condition }}</option>@endforeach</select></div>
            <div class="field"><label class="label">Status</label><select name="status"><option>ACTIVE</option><option>UNDER_REPAIR</option><option>INACTIVE</option></select></div>
            <div class="field"><label class="label">Active</label><select name="is_active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
            <div class="field full"><label class="label">Notes</label><textarea name="notes" rows="2"></textarea></div>
            <div class="field full"><button class="btn btn-primary" type="submit" @if($facilities->isEmpty()) disabled @endif>Add Component</button></div>
        </form>
    </div>

    <div class="col-12 card">
        <h3 class="section-title">Linked Components</h3>
        <div class="fm-table-wrap">
            <table class="fm-table">
                <thead><tr><th>Facility</th><th>Component Type</th><th>Name</th><th>Qty</th><th>Condition</th><th>Status</th><th>Active</th><th>Notes</th></tr></thead>
                <tbody>
                @forelse($components as $component)
                    <tr><td>{{ $component->facility_code }} - {{ $component->facility_name }}</td><td>{{ $component->component_type }}</td><td>{{ $component->component_name }}</td><td>{{ $component->quantity }}</td><td>{{ $component->condition }}</td><td>{{ $component->status }}</td><td>{{ $component->is_active ? 'Yes' : 'No' }}</td><td>{{ $component->notes }}</td></tr>
                @empty
                    <tr><td colspan="8" class="muted">No components yet. Components are separate from facility master to preserve future component history.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
