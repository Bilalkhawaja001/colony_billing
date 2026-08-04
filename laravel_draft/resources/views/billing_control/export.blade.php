@extends('billing_control.layout')

@section('content')
@php($month = request('month_cycle', request('month', now()->format('m-Y'))))

<div class="eyebrow">Download &amp; Records</div>
<h1 class="page-title">Download & Records &middot; @include('billing_control.components.month-label', ['value' => $month])</h1>

<section class="form-card" style="margin-top:24px">
    <form method="post" action="{{ route('billing.control.export.download') }}" class="form-stack">
        @csrf

        <div>
            <label class="form-label">Billing Month</label>
            <div data-month-picker-wrap>
        @include('billing_control.components.month-select', [
                'value' => $month,
                'id' => 'export-month-select',
            ])
            </div>
        </div>

        <div>
            <label class="form-label">Bill Type</label>
            <select class="form-select" name="bill_type">
                <option value="electric_v1">Electricity</option>
            </select>
        </div>

        <div class="dl-filters">
            <div>
                <label class="form-label">Scope</label>
                <select class="form-select" name="scope">
                    <option value="all">All</option>
                    <option value="SPINNING">SPINNING</option>
                    <option value="WEAVING">WEAVING</option>
                    <option value="CENTRALIZED">CENTRALIZED</option>
                </select>
            </div>
            <div>
                <label class="form-label">Unit Type</label>
                <select class="form-select" name="unit_type" id="export-unit-type">
                    <option value="">All</option>
                    <option value="House">House</option>
                    <option value="Bachelor">Bachelor</option>
                    <option value="Hostel - Guest House">Hostel - Guest House</option>
                </select>
            </div>
            <div>
                <label class="form-label">Room Type</label>
                <select class="form-select" name="room_type" id="export-room-type"><option value="">All</option></select>
            </div>
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center">
            <button class="btn btn-cta" type="submit">Download & Records</button>
            <button class="btn btn-cta" type="submit" formaction="{{ route('billing.control.export.detailed-electric-breakdown') }}">Detailed Electric Breakdown (Excel)</button>
        </div>
        <div class="col-caption">Filtered download will follow approved billing records and Excel format.</div>
    </form>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roomTypesByUnitType = {
        'House': ['HOUSE_A', 'HOUSE_B', 'HOUSE_C'],
        'Bachelor': ['ROOM'],
        'Hostel - Guest House': ['CONTAINER', 'HOUSE_A+', 'ROOM']
    };

    const unitTypeSelect = document.getElementById('export-unit-type');
    const roomTypeSelect = document.getElementById('export-room-type');

    function populateRoomTypes() {
        if (!unitTypeSelect || !roomTypeSelect) return;

        const options = roomTypesByUnitType[unitTypeSelect.value] || [];
        roomTypeSelect.innerHTML = '';

        const allOption = document.createElement('option');
        allOption.value = '';
        allOption.textContent = 'All';
        roomTypeSelect.appendChild(allOption);

        options.forEach(function (roomType) {
            const option = document.createElement('option');
            option.value = roomType;
            option.textContent = roomType;
            roomTypeSelect.appendChild(option);
        });

        roomTypeSelect.value = '';
    }

    populateRoomTypes();
    if (unitTypeSelect) unitTypeSelect.addEventListener('change', populateRoomTypes);
});
</script>
@endpush

