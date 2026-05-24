@extends('layouts.app')
@section('page_title','Facilities Management - Overview')
@section('page_subtitle','Independent physical/site operations module. No billing formulas or generated statements are touched.')
@section('content')
@include('facilities._tabs')
<div class="grid">
    <div class="col-12 card">
        <h3 class="section-title">Overview</h3>
        <p class="muted">KPIs are driven only from Facilities Management tables. Empty figures mean registry/work-order/service data has not been imported or entered yet.</p>
    </div>
    <div class="col-12">
        <div class="fm-kpi-grid">
            <div class="card soft"><div class="muted">Total Registered Facilities</div><div class="kpi">{{ $kpis['total_registered_facilities'] }}</div></div>
            <div class="card soft"><div class="muted">Open Work Orders</div><div class="kpi">{{ $kpis['open_work_orders'] }}</div></div>
            <div class="card soft"><div class="muted">Critical Pending Works</div><div class="kpi">{{ $kpis['critical_pending_works'] }}</div></div>
            <div class="card soft"><div class="muted">Completed & Verified This Month</div><div class="kpi">{{ $kpis['completed_verified_this_month'] }}</div></div>
            <div class="card soft"><div class="muted">Today Daily Services Status</div><div class="kpi">{{ $kpis['today_daily_services_status'] }}</div></div>
            <div class="card soft"><div class="muted">Pest Control Follow-Ups Due</div><div class="kpi">{{ $kpis['pest_control_followups_due'] }}</div></div>
        </div>
    </div>
    <div class="col-6 card">
        <h3 class="section-title">Controlled Work Categories</h3>
        <div class="fm-table-wrap">
            <table class="fm-table" style="min-width:520px;">
                <thead><tr><th>Category</th><th>Group</th></tr></thead>
                <tbody>
                @forelse($workCategories as $category)
                    <tr><td>{{ $category->name }}</td><td>{{ $category->category_group }}</td></tr>
                @empty
                    <tr><td colspan="2" class="muted">No category master rows yet. Run migrations.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-6 card">
        <h3 class="section-title">Phase 1 Boundary</h3>
        <div class="fm-note">
            Built for Maintenance and Routine Services only: plumbing, electrical, civil, HVAC, RO/geyser, sanitation, market/masjid/colony/common-area works, cleaning and pest-control follow-ups. Inventory Control, stock issue/return and duplicate stock masters are explicitly deferred.
        </div>
    </div>
</div>
@endsection
