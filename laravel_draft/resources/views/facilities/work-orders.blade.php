@extends('layouts.app')
@section('page_title','Facilities Management - Work Orders')
@section('page_subtitle','Phase 1 read-only foundation table for future work-order workflow.')
@section('content')
@include('facilities._tabs')
<div class="grid">
    <div class="col-12 card">
        <h3 class="section-title">Work Orders Foundation</h3>
        <div class="fm-note">Work-order storage is present for Overview counts and future Phase 2 workflow. Material Required, Material Remarks, Estimated Cost and Actual Cost exist as future-ready fields only; no Inventory Control integration is present.</div>
    </div>
    <div class="col-12 card">
        <h3 class="section-title">Current Work Orders</h3>
        <div class="fm-table-wrap">
            <table class="fm-table">
                <thead><tr><th>No</th><th>Facility</th><th>Category</th><th>Title</th><th>Priority</th><th>Status</th><th>Reported</th><th>Verified</th><th>Estimated</th><th>Actual</th></tr></thead>
                <tbody>
                @forelse($rows as $row)
                    <tr><td>{{ $row->work_order_no }}</td><td>{{ $row->facility_code }} - {{ $row->facility_name }}</td><td>{{ $row->category_name }}</td><td>{{ $row->title }}</td><td>{{ $row->priority }}</td><td>{{ $row->status }}</td><td>{{ $row->reported_on }}</td><td>{{ $row->verified_on }}</td><td>{{ $row->estimated_cost }}</td><td>{{ $row->actual_cost }}</td></tr>
                @empty
                    <tr><td colspan="10" class="muted">No work orders yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
