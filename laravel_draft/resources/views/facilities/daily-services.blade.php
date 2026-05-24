@extends('layouts.app')
@section('page_title','Facilities Management - Daily Services')
@section('page_subtitle','Routine cleaning and pest-control service foundation.')
@section('content')
@include('facilities._tabs')
<div class="grid">
    <div class="col-12 card">
        <h3 class="section-title">Routine Services Scope</h3>
        <div class="fm-note">Supported service types: {{ implode(', ', $routineServices) }}.</div>
    </div>
    <div class="col-12 card">
        <h3 class="section-title">Service Records</h3>
        <div class="fm-table-wrap">
            <table class="fm-table">
                <thead><tr><th>Date</th><th>Facility</th><th>Service Type</th><th>Status</th><th>Performed By</th><th>Verified By</th><th>Remarks</th></tr></thead>
                <tbody>
                @forelse($rows as $row)
                    <tr><td>{{ $row->service_date }}</td><td>{{ $row->facility_code }} - {{ $row->facility_name }}</td><td>{{ $row->service_type }}</td><td>{{ $row->status }}</td><td>{{ $row->performed_by }}</td><td>{{ $row->verified_by }}</td><td>{{ $row->remarks }}</td></tr>
                @empty
                    <tr><td colspan="7" class="muted">No daily service records yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
