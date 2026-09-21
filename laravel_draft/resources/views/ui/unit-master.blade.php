<!DOCTYPE html>

<html class="light" lang="en"><head>
@include('partials.material-symbols-local')
<meta charset="utf-8">

<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>NodeSky Billing - Housing Unit Directory</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=JetBrains+Mono:wght@400&amp;display=swap" rel="stylesheet"/>
<style>
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
        }
    </style>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface": "#f7f9fb",
                        "on-primary-fixed-variant": "#003ea8",
                        "surface-dim": "#d8dadc",
                        "on-error-container": "#93000a",
                        "on-surface-variant": "#434655",
                        "tertiary": "#4d556b",
                        "outline": "#737686",
                        "error": "#ba1a1a",
                        "on-tertiary-fixed": "#131b2e",
                        "surface-variant": "#e0e3e5",
                        "on-tertiary-fixed-variant": "#3f465c",
                        "secondary-fixed-dim": "#b9c7df",
                        "error-container": "#ffdad6",
                        "on-primary": "#ffffff",
                        "on-secondary-fixed-variant": "#3a485b",
                        "secondary-container": "#d5e3fc",
                        "on-error": "#ffffff",
                        "surface-container-low": "#f2f4f6",
                        "on-tertiary": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "on-secondary-container": "#57657a",
                        "on-primary-fixed": "#00174b",
                        "primary-fixed": "#dbe1ff",
                        "surface-container": "#eceef0",
                        "surface-container-highest": "#e0e3e5",
                        "inverse-on-surface": "#eff1f3",
                        "on-background": "#191c1e",
                        "tertiary-container": "#656d84",
                        "primary-container": "#2563eb",
                        "tertiary-fixed": "#dae2fd",
                        "outline-variant": "#c3c6d7",
                        "on-primary-container": "#eeefff",
                        "surface-tint": "#0053db",
                        "surface-bright": "#f7f9fb",
                        "secondary": "#515f74",
                        "inverse-primary": "#b4c5ff",
                        "tertiary-fixed-dim": "#bec6e0",
                        "on-tertiary-container": "#eef0ff",
                        "inverse-surface": "#2d3133",
                        "on-secondary": "#ffffff",
                        "on-surface": "#191c1e",
                        "primary-fixed-dim": "#b4c5ff",
                        "on-secondary-fixed": "#0d1c2e",
                        "primary": "#004ac6",
                        "background": "#f7f9fb",
                        "secondary-fixed": "#d5e3fc",
                        "surface-container-high": "#e6e8ea"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "xs": "4px",
                        "lg": "24px",
                        "base": "4px",
                        "xl": "32px",
                        "gutter": "20px",
                        "sm": "8px",
                        "md": "16px",
                        "container-max": "1440px"
                    },
                    "fontFamily": {
                        "label-sm": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-md": ["Inter"],
                        "headline-sm": ["Inter"],
                        "body-md": ["Inter"],
                        "label-md": ["Inter"],
                        "body-sm": ["Inter"],
                        "mono-sm": ["JetBrains Mono"],
                        "headline-lg": ["Inter"]
                    },
                    "fontSize": {
                        "label-sm": ["11px", { "lineHeight": "14px", "fontWeight": "500" }],
                        "body-lg": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "headline-sm": ["18px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "body-md": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "label-md": ["12px", { "lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600" }],
                        "body-sm": ["13px", { "lineHeight": "18px", "fontWeight": "400" }],
                        "mono-sm": ["12px", { "lineHeight": "16px", "fontWeight": "400" }],
                        "headline-lg": ["30px", { "lineHeight": "38px", "letterSpacing": "-0.02em", "fontWeight": "600" }]
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-surface text-on-surface font-body-md text-body-md antialiased min-h-screen flex flex-col">
@include('partials.global-navbar')

<!-- TopNavBar Component -->
<!-- Main Content -->
<main class="flex-grow w-full max-w-container-max mx-auto px-md md:px-lg py-lg space-y-xl">
<div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 border-b border-outline-variant pb-sm">
<div>
<h1 class="font-headline-lg text-headline-lg text-on-surface">Unit Directory</h1>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Manage housing categories, sub-categories, rooms, and resident allocations.</p>
</div>
<div class="flex gap-2">
<button type="button" onclick="window.location.href=window.location.pathname+'/export'+window.location.search" class="bg-surface-container-lowest border border-outline-variant text-on-surface font-label-md text-label-md px-3 py-1.5 rounded flex items-center gap-1 hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined" style="font-size: 16px;">download</span> Export
                </button>
</div>
</div>
<!-- 1. Unit Category Overview -->
<section class="space-y-4">
<h2 class="font-headline-sm text-headline-sm text-on-surface">Category Overview</h2>
@php
  $tsTotal = collect($typeStats)->sum('total');
  $tsVacant = collect($typeStats)->sum('vacant');
  $icons = ['HOUSE'=>'home','HOSTEL'=>'apartment','BACHELOR'=>'single_bed','CONTAINER'=>'inventory_2','COMMON'=>'domain_disabled','UNSET'=>'help'];
  $residenceTypes = ['ROOM', 'CONTAINER', 'HOUSE_A+', 'HOUSE_A', 'HOUSE_B', 'HOUSE_C', 'COMMON'];
  $occupantGrades = ['BACHELOR', 'SENIOR_STAFF', 'FAMILY', 'COMMON'];
  $departments = ['Weaving', 'Spinning', 'Centralized', 'External'];
  $floors = ['Ground', '1st', '2nd', '3rd', '4th'];
@endphp
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
  <a href="{{ url('unit-directory') }}" class="block bg-surface-container-lowest border rounded p-3 hover:shadow-md transition {{ $type === '' ? 'border-primary ring-2 ring-primary/30' : 'border-outline-variant' }}">
    <div class="flex justify-between items-center mb-1">
      <span class="font-label-md text-label-md text-on-surface-variant text-xs">All Units</span>
      <span class="material-symbols-outlined text-primary" style="font-size:18px">domain</span>
    </div>
    <div class="text-2xl font-bold text-on-surface leading-tight">{{ $tsTotal }}</div>
    <div class="text-sm font-bold text-green-700 mt-1">{{ $tsVacant }} vacant</div>
  </a>

  @foreach($typeStats as $t)
  <a href="{{ url('unit-directory') }}?type={{ $t->type }}" class="block rounded p-3 border hover:shadow-md transition {{ $type === $t->type ? 'border-primary ring-2 ring-primary/30 bg-primary/5' : ($t->vacant > 0 ? 'border-green-300 bg-green-50/40' : 'border-outline-variant bg-surface-container-lowest') }}">
    <div class="flex justify-between items-center mb-1">
      <span class="font-label-md text-label-md text-on-surface-variant text-xs">{{ ucfirst(strtolower($t->type)) }}</span>
      <span class="material-symbols-outlined text-primary" style="font-size:18px">{{ $icons[$t->type] ?? 'home' }}</span>
    </div>
    <div class="text-2xl font-bold text-on-surface leading-tight">{{ $t->total }}</div>
    <div class="text-sm font-bold mt-1 {{ $t->vacant > 0 ? 'text-green-700' : 'text-on-surface-variant' }}">{{ $t->vacant }} vacant</div>
  </a>
  @endforeach
</div>
</section>
<!-- 2. Sub Categories & Rooms -->
<section class="space-y-4">
<div class="flex justify-between items-center">
<h2 class="font-headline-sm text-headline-sm text-on-surface">Sub Categories &amp; Rooms</h2>
<div class="flex gap-2">
<div class="relative">
<span class="material-symbols-outlined absolute left-2 top-1.5 text-outline" style="font-size: 16px;">search</span>
<input class="pl-8 pr-3 py-1 border border-outline-variant rounded bg-surface-container-lowest text-body-sm focus:border-primary focus:ring-1 focus:ring-primary w-48" placeholder="Filter rooms..." type="text"/>
</div>
<button class="bg-surface-container-lowest border border-outline-variant p-1 rounded hover:bg-surface-container-low">
<span class="material-symbols-outlined text-on-surface-variant" style="font-size: 20px;">filter_list</span>
</button>
</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant rounded shadow-[0_2px_4px_rgba(0,0,0,0.02)] overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse min-w-[800px]">
<thead>
<tr class="bg-surface-container-low border-b border-outline-variant">
<th class="py-2 px-4 font-label-md text-label-md text-on-surface-variant uppercase">Unit ID</th>
<th class="py-2 px-4 font-label-md text-label-md text-on-surface-variant uppercase">Room No</th>
<th class="py-2 px-4 font-label-md text-label-md text-on-surface-variant uppercase">Location</th>
<th class="py-2 px-4 font-label-md text-label-md text-on-surface-variant uppercase">Block / Floor</th>
<th class="py-2 px-4 font-label-md text-label-md text-on-surface-variant uppercase">Department</th>
<th class="py-2 px-4 font-label-md text-label-md text-on-surface-variant uppercase">Occupants</th>
<th class="py-2 px-4 font-label-md text-label-md text-on-surface-variant uppercase">Status</th>
<th class="py-2 px-4 font-label-md text-label-md text-on-surface-variant uppercase text-right">Actions</th>
</tr>
</thead>
<tbody class="font-body-sm text-body-sm divide-y divide-outline-variant">
@forelse($units as $u)
@php($n = (int) ($occ[$u->unit_id] ?? 0))
<tr class="hover:bg-surface-container-low transition-colors {{ $u->is_active ? '' : 'opacity-50' }}">
<form method="post" action="{{ route('billing.units.update', $u->unit_id) }}" id="uf-{{ $loop->index }}">@csrf @method('PUT')</form>
<td class="px-4 py-2 font-mono font-semibold text-on-surface">{{ $u->unit_id }}</td>
<td class="px-4 py-2"><input form="uf-{{ $loop->index }}" name="room_no" value="{{ $u->room_no }}" class="w-24 border border-outline-variant rounded px-2 py-1 text-sm" placeholder="—"></td>
<td class="px-4 py-2"><input form="uf-{{ $loop->index }}" name="colony_type" value="{{ $u->colony_type }}" class="w-52 border border-outline-variant rounded px-2 py-1 text-sm" placeholder="e.g. Weaving Bachelor Colony"></td>
<td class="px-4 py-2"><input form="uf-{{ $loop->index }}" name="block_name" value="{{ $u->block_name }}" class="w-36 border border-outline-variant rounded px-2 py-1 text-sm" placeholder="—"></td>
<td class="px-4 py-2 font-mono text-center">{{ $n }}</td>
<td class="px-4 py-2">
  <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $u->is_active ? ($n > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600') : 'bg-red-100 text-red-700' }}">
    {{ !$u->is_active ? 'Inactive' : ($n > 0 ? 'Occupied' : 'Vacant') }}
  </span>
</td>
<td class="px-4 py-2 text-right whitespace-nowrap">
  <button form="uf-{{ $loop->index }}" type="submit" class="text-primary text-xs font-semibold hover:underline mr-3">Save</button>
  <form method="post" action="{{ route('billing.units.toggle', $u->unit_id) }}" class="inline">@csrf @method('PATCH')
    <button type="submit" class="text-xs font-semibold hover:underline {{ $u->is_active ? 'text-red-600' : 'text-green-700' }}">{{ $u->is_active ? 'Deactivate' : 'Activate' }}</button>
  </form>
</td>
</tr>
<tr class="bg-surface-container-low/40">
<td colspan="8" class="px-4 pb-3 pt-0">
  <details>
    <summary class="cursor-pointer text-xs font-semibold text-primary select-none">Rooms ({{ count($rooms[$u->unit_id] ?? []) }})</summary>
    <div class="mt-2 flex flex-wrap gap-2 items-center">
      @foreach($rooms[$u->unit_id] ?? [] as $rm)
        @php($rc = (int) ($roomOcc[$u->unit_id.'|'.$rm->room_no] ?? 0))
        @php($emps = $roomEmployees[$u->unit_id.'|'.$rm->room_no] ?? collect())
        <details class="inline-block align-top">
          <summary class="inline-flex items-center gap-2 border rounded px-2 py-1 text-xs cursor-pointer select-none {{ !$rm->is_active ? 'opacity-50 border-outline-variant' : ($rc > 0 ? 'border-outline-variant bg-surface hover:border-primary' : 'border-green-300 bg-green-50') }}">
            <span class="font-mono font-semibold">{{ $rm->room_no }}</span>
            <span class="text-on-surface-variant">{{ $rc > 0 ? $rc.' person' : 'vacant' }}</span>
          </summary>
          <div class="mt-1 border border-outline-variant rounded bg-surface p-2 min-w-[420px]">
            @if(count($emps))
              <table class="w-full text-xs">
                <thead><tr class="text-on-surface-variant">
                  <th class="text-left py-1 pr-3">Company ID</th>
                  <th class="text-left py-1 pr-3">Name</th>
                  <th class="text-left py-1 pr-3">Department</th>
                  <th class="text-left py-1 pr-3">Designation</th>
                  <th class="text-left py-1">Mobile</th>
                </tr></thead>
                <tbody>
                @foreach($emps as $e)
                  <tr class="border-t border-outline-variant">
                    <td class="py-1 pr-3 font-mono"><a href="{{ url('employee-profile') }}/{{ $e->company_id }}" class="text-primary hover:underline">{{ $e->company_id }}</a></td>
                    <td class="py-1 pr-3 font-semibold">{{ $e->name ?: '—' }}</td>
                    <td class="py-1 pr-3">{{ $e->department ?: '—' }}</td>
                    <td class="py-1 pr-3">{{ $e->designation ?: '—' }}</td>
                    <td class="py-1">{{ $e->mobile_no ?: '—' }}</td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            @else
              <div class="text-xs text-on-surface-variant">No residents in this room.</div>
            @endif
            <form method="post" action="{{ route('billing.units.room.store') }}" class="mt-2 grid grid-cols-2 gap-2 items-end border-t border-outline-variant pt-2">
              @csrf
              <input type="hidden" name="unit_id" value="{{ $u->unit_id }}">
              <input type="hidden" name="room_no" value="{{ $rm->room_no }}">
              <label class="text-xs text-on-surface-variant">Residence Type
                <select name="residence_type" class="mt-1 w-full border border-outline-variant rounded px-2 py-1 text-xs bg-surface-container-lowest">
                  <option value="">â€”</option>
                  @foreach($residenceTypes as $rt)
                    <option value="{{ $rt }}" @selected($rm->residence_type === $rt)>{{ $rt }}</option>
                  @endforeach
                </select>
              </label>
              <label class="text-xs text-on-surface-variant">Occupant Grade
                <select name="occupant_grade" class="mt-1 w-full border border-outline-variant rounded px-2 py-1 text-xs bg-surface-container-lowest">
                  <option value="">â€”</option>
                  @foreach($occupantGrades as $og)
                    <option value="{{ $og }}" @selected($rm->occupant_grade === $og)>{{ $og }}</option>
                  @endforeach
                </select>
              </label>
              <label class="text-xs text-on-surface-variant">Floor
                <select name="floor" class="mt-1 w-full border border-outline-variant rounded px-2 py-1 text-xs bg-surface-container-lowest">
                  @foreach($floors as $fl)
                    <option value="{{ $fl }}" @selected(($rm->floor ?: 'Ground') === $fl)>{{ $fl }}</option>
                  @endforeach
                </select>
              </label>
              <button type="submit" class="text-primary text-xs font-semibold hover:underline text-left">Save room</button>
            </form>
            <form method="post" action="{{ route('billing.units.room.toggle', $rm->id) }}" class="mt-2">@csrf @method('PATCH')
              <button type="submit" class="text-xs font-semibold {{ $rm->is_active ? 'text-red-600' : 'text-green-700' }} hover:underline">{{ $rm->is_active ? 'Deactivate room' : 'Activate room' }}</button>
            </form>
          </div>
        </details>
      @endforeach
      <form method="post" action="{{ route('billing.units.room.store') }}" class="inline-flex gap-1 items-center">
        @csrf
        <input type="hidden" name="unit_id" value="{{ $u->unit_id }}">
        <input name="room_no" required placeholder="{{ $u->unit_id }}-1" class="border border-outline-variant rounded px-2 py-1 text-xs w-32">
        <select name="residence_type" class="border border-outline-variant rounded px-2 py-1 text-xs bg-surface-container-lowest">
          <option value="">Type</option>
          @foreach($residenceTypes as $rt)<option value="{{ $rt }}">{{ $rt }}</option>@endforeach
        </select>
        <select name="occupant_grade" class="border border-outline-variant rounded px-2 py-1 text-xs bg-surface-container-lowest">
          <option value="">Grade</option>
          @foreach($occupantGrades as $og)<option value="{{ $og }}">{{ $og }}</option>@endforeach
        </select>
        <select name="floor" class="border border-outline-variant rounded px-2 py-1 text-xs bg-surface-container-lowest">
          @foreach($floors as $fl)<option value="{{ $fl }}" @selected($fl === 'Ground')>{{ $fl }}</option>@endforeach
        </select>
        <button type="submit" class="bg-primary text-white rounded px-2 py-1 text-xs font-semibold">+ Room</button>
      </form>
    </div>
  </details>
</td>
</tr>
@empty
<tr><td colspan="8" class="px-4 py-8 text-center text-on-surface-variant">No units found.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
</section>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-lg">
<!-- 3. Residents List View -->
<section class="lg:col-span-2 space-y-4">
<h2 class="font-headline-sm text-headline-sm text-on-surface">Recent Residents</h2>
<div class="bg-surface-container-lowest border border-outline-variant rounded shadow-[0_2px_4px_rgba(0,0,0,0.02)] overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse min-w-[500px]">
<thead>
<tr class="bg-surface-container-low border-b border-outline-variant">
<th class="py-2 px-4 font-label-md text-label-md text-on-surface-variant uppercase">Name</th>
<th class="py-2 px-4 font-label-md text-label-md text-on-surface-variant uppercase">Room</th>
<th class="py-2 px-4 font-label-md text-label-md text-on-surface-variant uppercase">Move-in Date</th>
<th class="py-2 px-4 font-label-md text-label-md text-on-surface-variant uppercase text-right">Billing</th>
</tr>
</thead>
<tbody class="font-body-sm text-body-sm divide-y divide-outline-variant">
<tr class="hover:bg-surface-container-low transition-colors">
<td class="py-2 px-4 font-medium text-on-surface">Sarah Jenkins</td>
<td class="py-2 px-4 font-mono-sm text-mono-sm text-on-surface-variant">RM-101A</td>
<td class="py-2 px-4 text-on-surface-variant">Oct 12, 2023</td>
<td class="py-2 px-4 text-right text-primary font-medium">Active</td>
</tr>
<tr class="hover:bg-surface-container-low transition-colors">
<td class="py-2 px-4 font-medium text-on-surface">Michael Chang</td>
<td class="py-2 px-4 font-mono-sm text-mono-sm text-on-surface-variant">RM-101A</td>
<td class="py-2 px-4 text-on-surface-variant">Oct 15, 2023</td>
<td class="py-2 px-4 text-right text-primary font-medium">Active</td>
</tr>
<tr class="hover:bg-surface-container-low transition-colors">
<td class="py-2 px-4 font-medium text-on-surface">Elena Rodriguez</td>
<td class="py-2 px-4 font-mono-sm text-mono-sm text-on-surface-variant">RM-103A</td>
<td class="py-2 px-4 text-on-surface-variant">Nov 01, 2023</td>
<td class="py-2 px-4 text-right text-primary font-medium">Active</td>
</tr>
<tr class="hover:bg-surface-container-low transition-colors">
<td class="py-2 px-4 font-medium text-on-surface">David Smith</td>
<td class="py-2 px-4 font-mono-sm text-mono-sm text-on-surface-variant">EX-201</td>
<td class="py-2 px-4 text-on-surface-variant">Dec 05, 2023</td>
<td class="py-2 px-4 text-right text-on-surface-variant">Pending</td>
</tr>
</tbody>
</table>
</div>
<div class="px-4 py-2 border-t border-outline-variant bg-surface-bright text-center">
<a class="font-label-md text-label-md text-primary hover:underline" href="#">View All Residents</a>
</div>
</div>
</section>
<!-- 4. Data Management -->
<section class="space-y-4">
<h2 class="font-headline-sm text-headline-sm text-on-surface">Data Management</h2>
<!-- Upsert Form -->
<div class="bg-surface-container-lowest border border-outline-variant rounded p-4 shadow-[0_2px_4px_rgba(0,0,0,0.02)]">
<h3 class="font-label-md text-label-md text-on-surface-variant mb-3 uppercase">Quick Add Room</h3>
<form action="{{ url('unit-directory') }}" class="space-y-3" method="POST">
<!-- CSRF Token Placeholder for Laravel -->
<input name="_token" type="hidden" value="csrf_placeholder"/>
<div>
<label class="block font-label-md text-label-md text-on-surface mb-1">Room ID</label>
<input class="w-full border border-outline-variant rounded px-3 py-1.5 text-body-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" name="room_id" placeholder="e.g. RM-104C" type="text"/>
</div>
<div>
<label class="block font-label-md text-label-md text-on-surface mb-1">Category</label>
<select class="w-full border border-outline-variant rounded px-3 py-1.5 text-body-sm bg-surface-container-lowest focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" name="category_id">
<option value="">Select Category</option>
<option value="1">Standard Housing</option>
<option value="2">Executive Suites</option>
<option value="3">Temporary Lodging</option>
</select>
</div>
<div>
<label class="block font-label-md text-label-md text-on-surface mb-1">Capacity</label>
<input class="w-full border border-outline-variant rounded px-3 py-1.5 text-body-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" name="capacity" placeholder="Max residents" type="number"/>
</div>
<button class="w-full bg-surface-container-lowest border border-outline-variant text-on-surface font-label-md text-label-md py-1.5 rounded hover:bg-surface-container-low transition-colors mt-2" type="submit">Add Room</button>
</form>
</div>
<!-- CSV Upload -->
<div class="bg-surface-container-lowest border border-outline-variant rounded p-4 shadow-[0_2px_4px_rgba(0,0,0,0.02)]">
<h3 class="font-label-md text-label-md text-on-surface-variant mb-3 uppercase">Bulk Upload</h3>
<div class="border-2 border-dashed border-outline-variant rounded-lg p-6 text-center hover:border-primary hover:bg-primary-fixed/10 transition-colors cursor-pointer group">
<span class="material-symbols-outlined text-outline group-hover:text-primary mb-2" style="font-size: 32px;">upload_file</span>
<p class="font-body-sm text-body-sm text-on-surface">Drag &amp; drop CSV file or <span class="text-primary font-medium">browse</span></p>
<p class="font-label-sm text-label-sm text-on-surface-variant mt-1">Format: room_id, category, capacity</p>
</div>
</div>
</section>
</div>
<!-- 5. Operation Status (Feedback Area) -->
<section class="border-t border-outline-variant pt-4 mt-8 pb-lg">
<h2 class="font-label-md text-label-md text-on-surface-variant uppercase mb-3">System Status</h2>
<div class="flex flex-wrap gap-4">
<!-- Success Toast -->
<div class="flex items-center gap-2 bg-surface-container-lowest border border-outline-variant rounded p-3 shadow-sm min-w-[250px]">
<span class="material-symbols-outlined text-primary" style="font-size: 20px;">check_circle</span>
<div class="font-body-sm text-body-sm text-on-surface">Data synced successfully</div>
</div>
<!-- Loading State -->
<div class="flex items-center gap-2 bg-surface-container-lowest border border-outline-variant rounded p-3 shadow-sm min-w-[250px] opacity-70">
<span class="material-symbols-outlined text-outline animate-spin" style="font-size: 20px;">sync</span>
<div class="font-body-sm text-body-sm text-on-surface-variant">Processing batch upload...</div>
</div>
<!-- Error State -->
<div class="flex items-center gap-2 bg-error-container/20 border border-error-container rounded p-3 shadow-sm min-w-[250px]">
<span class="material-symbols-outlined text-error" style="font-size: 20px;">error</span>
<div class="font-body-sm text-body-sm text-on-surface">Validation failed for RM-104C</div>
</div>
</div>
</section>
</main>
<!-- BottomNavBar Component (Mobile Only) -->
<div style="display:none" data-backend-contract="unit-directory"><form id="unitUpsertForm"><input name="unit_id"><input name="unit_name"></form><button id="loadUnitsBtn"></button><button id="downloadUnitTemplate"></button><input id="unitCsvFile" type="file"><button id="importUnitCsv"></button><tbody id="unitRows"></tbody><pre id="unitResult"></pre><div id="unitStatus"></div></div>
<script>const csrf=@json(csrf_token());const appBase=@json(url(''));function appUrl(p){return appBase+'/'+String(p).replace(/^\/+/, '')}</script></body></html>