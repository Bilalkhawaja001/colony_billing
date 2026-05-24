@extends('layouts.app')
@section('page_title','Facilities Management - Work Orders')
@section('page_subtitle','Linked work orders with controlled lifecycle and status history.')
@section('content')
@include('facilities._tabs')
@if(session('status'))<div class="card" style="border-color:#bbf7d0;background:#f0fdf4;color:#166534;margin-bottom:12px;">{{ session('status') }}</div>@endif
<div class="grid">
    <div class="col-12 card">
        <h3 class="section-title">Work Orders</h3>
        <div class="fm-note">Allowed sequence: OPEN → ASSIGNED → IN_PROGRESS → COMPLETED → VERIFIED → CLOSED. Rework returns to assignment/progress. Cancellation requires reason.</div>
    </div>
    <div class="col-12 card">
        <div class="fm-table-wrap"><table class="fm-table">
            <thead><tr><th>No</th><th>Request</th><th>Facility</th><th>Category</th><th>Priority</th><th>Status</th><th>Description</th><th>Next Action</th><th>History</th></tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->work_order_no }}</td><td>{{ $row->request_no }}</td><td>{{ $row->facility_code }} - {{ $row->facility_name }}</td><td>{{ $row->category_name }}</td><td>{{ $row->priority }}</td><td><strong>{{ $row->status }}</strong></td><td>{{ $row->description ?? $row->title }}</td>
                    <td>
                        @if(in_array($row->status, ['OPEN','REWORK_REQUIRED']))
                            <form method="post" action="/facilities-management/work-orders/{{ $row->id }}/transition">@csrf<input type="hidden" name="to_status" value="ASSIGNED"><input name="assigned_to" placeholder="Assign to"><button class="btn" type="submit">Assign</button></form>
                        @elseif($row->status === 'ASSIGNED')
                            <form method="post" action="/facilities-management/work-orders/{{ $row->id }}/transition">@csrf<input type="hidden" name="to_status" value="IN_PROGRESS"><button class="btn" type="submit">Start</button></form>
                        @elseif($row->status === 'IN_PROGRESS')
                            <form method="post" action="/facilities-management/work-orders/{{ $row->id }}/transition">@csrf<input type="hidden" name="to_status" value="COMPLETED"><label class="label">Completion Date</label><div style="display:flex;gap:8px;align-items:center;"><input id="fm-completion-display-{{ $row->id }}" value="{{ now()->format('d/m/Y') }}" readonly><input class="fm-completion-picker" data-display-id="fm-completion-display-{{ $row->id }}" name="completion_date" type="date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required title="Select completion date" style="width:48px;min-width:48px;padding:8px;"></div><textarea name="remarks" rows="2" placeholder="Completion remarks"></textarea><input name="actual_cost" type="number" step="0.01" placeholder="Actual cost"><button class="btn btn-primary" type="submit">Complete</button></form>
                        @elseif($row->status === 'COMPLETED')
                            <span class="muted">Waiting verification</span>
                        @elseif($row->status === 'VERIFIED')
                            <form method="post" action="/facilities-management/work-orders/{{ $row->id }}/close">@csrf<textarea name="remarks" rows="2" placeholder="Closure remarks"></textarea><button class="btn btn-primary" type="submit">Close</button></form>
                        @else
                            <span class="muted">No action</span>
                        @endif
                    </td>
                    <td>
                        @foreach(($histories[$row->id] ?? collect()) as $history)
                            <div class="muted">{{ $history->from_status ?: 'NEW' }} → {{ $history->to_status }}<br>{{ $history->action_at }}</div>
                        @endforeach
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="muted">No work orders yet. Approve and convert a Service Request first.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function fmFormatDate(value) {
        if (!value) return '';
        const parts = value.split('-');
        return parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : '';
    }

    document.querySelectorAll('.fm-completion-picker').forEach(function (picker) {
        const display = document.getElementById(picker.dataset.displayId);
        if (!display) return;

        display.value = fmFormatDate(picker.value);
        picker.addEventListener('change', function () {
            display.value = fmFormatDate(picker.value);
        });
    });
});
</script>

@endsection
