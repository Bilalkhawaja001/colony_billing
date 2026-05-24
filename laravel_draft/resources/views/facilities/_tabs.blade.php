@php
$facilityTabs = [
    '/facilities-management' => 'Overview',
    '/facilities-management/registry' => 'Facility Registry',
    '/facilities-management/inspections' => 'Inspections',
    '/facilities-management/service-requests' => 'Service Requests',
    '/facilities-management/approval-queue' => 'Approval Queue',
    '/facilities-management/work-orders' => 'Work Orders',
    '/facilities-management/daily-services' => 'Daily Services',
    '/facilities-management/verification-closure' => 'Verification & Closure',
    '/facilities-management/reports' => 'Reports',
];
@endphp
<style>
.fm-tabs{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 14px;}
.fm-tabs a{padding:9px 12px;border:1px solid #dbe6f3;border-radius:999px;background:#fff;color:#334155;text-decoration:none;font-size:13px;font-weight:700;}
.fm-tabs a.active{background:#0f172a;color:#fff;border-color:#0f172a;}
.fm-kpi-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:14px;}
.fm-table-wrap{overflow:auto;border:1px solid #e6edf6;border-radius:14px;}
.fm-table{width:100%;border-collapse:collapse;background:#fff;min-width:920px;}
.fm-table th,.fm-table td{padding:10px 11px;border-bottom:1px solid #edf2f7;text-align:left;font-size:13px;vertical-align:top;}
.fm-table th{background:#f8fbff;color:#334155;font-weight:800;}
.fm-form{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;}
.fm-form .wide{grid-column:span 2;}
.fm-form .full{grid-column:1 / -1;}
.fm-note{border:1px solid #dbeafe;background:#f8fbff;color:#334155;border-radius:12px;padding:10px 12px;font-size:13px;line-height:1.45;}
@media(max-width:1000px){.fm-kpi-grid,.fm-form{grid-template-columns:1fr}.fm-form .wide{grid-column:auto}}
</style>
<div class="fm-tabs">
@foreach($facilityTabs as $href => $label)
    <a href="{{ $href }}" class="{{ request()->path() === ltrim($href, '/') ? 'active' : '' }}">{{ $label }}</a>
@endforeach
</div>
