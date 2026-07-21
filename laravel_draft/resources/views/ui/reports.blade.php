<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>NodeSky - Reports Center</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=JetBrains+Mono:wght@400&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "surface-container-highest": "#e4e2e4",
                      "tertiary-fixed": "#fcdeb5",
                      "secondary-fixed-dim": "#b9c7e0",
                      "surface-tint": "#565e74",
                      "tertiary-fixed-dim": "#dec29a",
                      "on-tertiary-fixed": "#271901",
                      "on-primary": "#ffffff",
                      "error": "#ba1a1a",
                      "on-tertiary": "#ffffff",
                      "surface-container": "#f0edef",
                      "secondary": "#515f74",
                      "on-primary-fixed": "#131b2e",
                      "inverse-surface": "#303032",
                      "primary-container": "#131b2e",
                      "inverse-on-surface": "#f3f0f2",
                      "on-secondary": "#ffffff",
                      "on-primary-fixed-variant": "#3f465c",
                      "on-surface-variant": "#45464d",
                      "surface-dim": "#dcd9db",
                      "outline": "#76777d",
                      "primary": "#000000",
                      "on-tertiary-container": "#98805d",
                      "tertiary-container": "#271901",
                      "outline-variant": "#c6c6cd",
                      "inverse-primary": "#bec6e0",
                      "secondary-container": "#d5e3fd",
                      "on-primary-container": "#7c839b",
                      "primary-fixed-dim": "#bec6e0",
                      "secondary-fixed": "#d5e3fd",
                      "on-error": "#ffffff",
                      "on-error-container": "#93000a",
                      "on-surface": "#1b1b1d",
                      "error-container": "#ffdad6",
                      "surface-container-lowest": "#ffffff",
                      "surface-container-high": "#eae7e9",
                      "surface-container-low": "#f6f3f5",
                      "on-tertiary-fixed-variant": "#574425",
                      "surface-variant": "#e4e2e4",
                      "background": "#fcf8fa",
                      "on-background": "#1b1b1d",
                      "on-secondary-fixed": "#0d1c2f",
                      "primary-fixed": "#dae2fd",
                      "on-secondary-container": "#57657b",
                      "on-secondary-fixed-variant": "#3a485c",
                      "tertiary": "#000000",
                      "surface": "#fcf8fa",
                      "surface-bright": "#fcf8fa"
              },
              "borderRadius": {
                      "DEFAULT": "0.125rem",
                      "lg": "0.25rem",
                      "xl": "0.5rem",
                      "full": "0.75rem"
              },
              "spacing": {
                      "stack-compact": "0.5rem",
                      "stack-default": "1rem",
                      "gutter": "1.5rem",
                      "section-gap": "2rem",
                      "container-max": "1440px"
              },
              "fontFamily": {
                      "mono-data": [
                              "JetBrains Mono"
                      ],
                      "body-md": [
                              "Inter"
                      ],
                      "label-md": [
                              "Inter"
                      ],
                      "display-lg": [
                              "Inter"
                      ],
                      "headline-md": [
                              "Inter"
                      ],
                      "body-lg": [
                              "Inter"
                      ],
                      "headline-sm": [
                              "Inter"
                      ],
                      "body-sm": [
                              "Inter"
                      ]
              },
              "fontSize": {
                      "mono-data": [
                              "13px",
                              {
                                      "lineHeight": "20px",
                                      "fontWeight": "400"
                              }
                      ],
                      "body-md": [
                              "14px",
                              {
                                      "lineHeight": "20px",
                                      "fontWeight": "400"
                              }
                      ],
                      "label-md": [
                              "12px",
                              {
                                      "lineHeight": "16px",
                                      "letterSpacing": "0.05em",
                                      "fontWeight": "600"
                              }
                      ],
                      "display-lg": [
                              "36px",
                              {
                                      "lineHeight": "44px",
                                      "letterSpacing": "-0.02em",
                                      "fontWeight": "700"
                              }
                      ],
                      "headline-md": [
                              "24px",
                              {
                                      "lineHeight": "32px",
                                      "letterSpacing": "-0.01em",
                                      "fontWeight": "600"
                              }
                      ],
                      "body-lg": [
                              "16px",
                              {
                                      "lineHeight": "24px",
                                      "fontWeight": "400"
                              }
                      ],
                      "headline-sm": [
                              "18px",
                              {
                                      "lineHeight": "28px",
                                      "fontWeight": "600"
                              }
                      ],
                      "body-sm": [
                              "12px",
                              {
                                      "lineHeight": "18px",
                                      "fontWeight": "400"
                              }
                      ]
              }
      },
          },
        }
      </script>
<style>
        .material-symbols-outlined {
          font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.fill {
            font-variation-settings: 'FILL' 1;
        }
      </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col">
@include('partials.global-navbar')

<!-- TopNavBar -->
<header class="bg-surface-container-lowest dark:bg-surface-container-lowest flex justify-between items-center w-full px-gutter max-w-container-max mx-auto h-16 border-b border-outline-variant dark:border-outline-variant flat no shadows sticky top-0 z-50">
<div class="flex items-center gap-6">
<span class="font-headline-md text-headline-md font-black text-primary dark:text-on-primary-fixed tracking-tight">NodeSky</span>
<nav class="hidden md:flex gap-6 h-full items-center">
<a class="text-on-surface-variant dark:text-on-surface-variant hover:text-primary dark:hover:text-primary-fixed transition-colors active:scale-95 transition-transform font-body-md text-body-md" href="#">Billing</a>
<a class="text-on-surface-variant dark:text-on-surface-variant hover:text-primary dark:hover:text-primary-fixed transition-colors active:scale-95 transition-transform font-body-md text-body-md" href="#">Infrastructure</a>
<a class="text-on-surface-variant dark:text-on-surface-variant hover:text-primary dark:hover:text-primary-fixed transition-colors active:scale-95 transition-transform font-body-md text-body-md" href="#">Usage</a>
<a class="text-primary dark:text-primary-fixed font-bold border-b-2 border-primary dark:border-primary-fixed pb-2 hover:text-primary dark:hover:text-primary-fixed transition-colors active:scale-95 transition-transform font-body-md text-body-md flex items-center h-full pt-2" href="#">Reports</a>
</nav>
</div>
<div class="flex items-center gap-4">
<div class="hidden md:flex relative text-on-surface-variant">
<span class="material-symbols-outlined absolute left-2.5 top-2 text-[20px]">search</span>
<input class="pl-9 pr-4 py-1.5 bg-surface-container-low border border-outline-variant rounded text-body-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors w-48 text-on-surface" placeholder="Search..." type="text"/>
</div>
<div class="flex items-center gap-2">
<button aria-label="notifications" class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded transition-colors">
<span class="material-symbols-outlined" data-icon="notifications">notifications</span>
</button>
<button aria-label="help" class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded transition-colors">
<span class="material-symbols-outlined" data-icon="help">help</span>
</button>
<div class="w-8 h-8 rounded-full bg-secondary-container flex items-center justify-center overflow-hidden cursor-pointer hover:ring-2 hover:ring-primary transition-all">
<span class="font-label-md text-on-secondary-container">U</span>
</div>
</div>
</div>
</header>
<!-- TopAppBar -->
<div class="bg-surface dark:bg-background flex items-center justify-between px-gutter py-stack-default max-w-container-max mx-auto border-b border-outline-variant w-full">
<div class="flex items-center gap-3">
<button class="p-1 -ml-1 text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-surface-container-highest rounded cursor-pointer">
<span class="material-symbols-outlined" data-icon="arrow_back">arrow_back</span>
</button>
<div class="flex flex-col">
<h1 class="font-headline-sm text-headline-sm font-bold text-primary dark:text-on-surface">Reports Center</h1>
<span class="font-body-sm text-body-sm text-on-surface-variant">Utility Summary</span>
</div>
</div>
<div class="flex items-center gap-2">
<div class="hidden sm:flex gap-2 mr-4">
<button class="px-4 py-2 bg-primary text-on-primary font-label-md text-label-md rounded hover:bg-on-primary-fixed-variant transition-colors shadow-sm">Monthly Summary JSON</button>
<button class="px-4 py-2 bg-primary text-on-primary font-label-md text-label-md rounded hover:bg-on-primary-fixed-variant transition-colors shadow-sm">Employee Bill Summary</button>
<button class="px-4 py-2 bg-primary text-on-primary font-label-md text-label-md rounded hover:bg-on-primary-fixed-variant transition-colors shadow-sm">Recovery JSON</button>
</div>
<button class="p-2 text-on-surface-variant border border-outline-variant rounded hover:bg-surface-container-highest dark:hover:bg-surface-container-highest transition-colors flex items-center justify-center" title="Export Excel">
<span class="material-symbols-outlined text-[20px]" data-icon="download">download</span>
</button>
<button class="p-2 text-on-surface-variant border border-outline-variant rounded hover:bg-surface-container-highest dark:hover:bg-surface-container-highest transition-colors flex items-center justify-center" title="Export PDF">
<span class="material-symbols-outlined text-[20px]" data-icon="share">share</span>
</button>
<button class="p-2 text-on-surface-variant hover:bg-surface-container-highest dark:hover:bg-surface-container-highest rounded transition-colors cursor-pointer">
<span class="material-symbols-outlined" data-icon="more_vert">more_vert</span>
</button>
</div>
</div>
<!-- Main Canvas -->
<main class="flex-grow w-full max-w-container-max mx-auto px-gutter py-section-gap flex flex-col gap-section-gap">
<!-- Tabs -->
<div class="border-b border-outline-variant flex gap-6">
<button class="px-1 py-3 border-b-2 border-primary text-primary font-label-md text-label-md cursor-pointer transition-colors" onclick="switchTab('usage')">Usage Statistics</button>
<button class="px-1 py-3 border-b-2 border-transparent text-on-surface-variant hover:text-primary font-label-md text-label-md cursor-pointer transition-colors" onclick="switchTab('employee')">Employee Billing</button>
<button class="px-1 py-3 border-b-2 border-transparent text-on-surface-variant hover:text-primary font-label-md text-label-md cursor-pointer transition-colors" onclick="switchTab('recovery')">Recovery Analysis</button>
</div>
<!-- Tab 1: Usage Statistics -->
<div class="flex flex-col gap-stack-default animate-[fadeIn_0.2s_ease-in-out]" id="tab-usage">
<!-- Filters -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-4 flex flex-wrap gap-4 items-end">
<div class="flex flex-col gap-1.5 flex-grow sm:flex-grow-0 sm:w-64">
<label class="font-label-md text-label-md text-on-surface">Date Range</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-2.5 text-[18px] text-on-surface-variant">calendar_today</span>
<input class="w-full pl-10 pr-3 py-2 bg-surface border border-outline-variant rounded font-body-sm text-body-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none text-on-surface transition-colors" placeholder="Oct 01 - Oct 31, 2024" type="text"/>
</div>
</div>
<div class="flex flex-col gap-1.5 flex-grow sm:flex-grow-0 sm:w-64">
<label class="font-label-md text-label-md text-on-surface">Service Category</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-2.5 text-[18px] text-on-surface-variant">filter_list</span>
<select class="w-full pl-10 pr-8 py-2 bg-surface border border-outline-variant rounded font-body-sm text-body-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none text-on-surface transition-colors appearance-none">
<option>All Categories</option>
<option>Compute</option>
<option>Storage</option>
<option>Network</option>
</select>
<span class="material-symbols-outlined absolute right-2 top-2.5 text-[20px] text-on-surface-variant pointer-events-none">arrow_drop_down</span>
</div>
</div>
<button class="px-4 py-2 bg-surface-container-high text-on-surface border border-outline-variant font-label-md text-label-md rounded hover:bg-surface-container-highest transition-colors sm:ml-auto">
                    Apply Filters
                </button>
</div>
<!-- KPIs -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-5 flex flex-col gap-2">
<div class="flex items-center gap-2 text-on-surface-variant">
<span class="material-symbols-outlined text-[20px]">electric_bolt</span>
<h3 class="font-label-md text-label-md">Total Utility Usage</h3>
</div>
<div class="flex items-baseline gap-2">
<span class="font-display-lg text-display-lg text-on-surface">45.2 TB</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">+12% from last month</span>
</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-5 flex flex-col gap-2">
<div class="flex items-center gap-2 text-on-surface-variant">
<span class="material-symbols-outlined text-[20px]">dns</span>
<h3 class="font-label-md text-label-md">Active Services</h3>
</div>
<div class="flex items-baseline gap-2">
<span class="font-display-lg text-display-lg text-on-surface">1,204</span>
<span class="font-body-sm text-body-sm text-on-surface-variant pl-2 border-l border-outline-variant">99.9% Uptime</span>
</div>
</div>
</div>
<!-- Data Table -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low border-b border-outline-variant">
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant whitespace-nowrap">Service Name</th>
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant whitespace-nowrap">Provider</th>
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant whitespace-nowrap">Usage</th>
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant whitespace-nowrap">Cost</th>
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant whitespace-nowrap">Status</th>
</tr>
</thead>
<tbody class="font-body-sm text-body-sm text-on-surface divide-y divide-outline-variant">
<tr class="hover:bg-surface-container-low transition-colors group">
<td class="py-3 px-4 flex items-center gap-2">
<span class="material-symbols-outlined text-[16px] text-on-surface-variant">database</span>
                                    NodeSky RDS
                                </td>
<td class="py-3 px-4">Internal</td>
<td class="py-3 px-4 font-mono-data text-mono-data">12.5 TB</td>
<td class="py-3 px-4 font-mono-data text-mono-data">$4,250.00</td>
<td class="py-3 px-4">
<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#E6F4EA] text-[#137333] font-label-md text-[10px]">Active</span>
</td>
</tr>
<tr class="hover:bg-surface-container-low transition-colors group">
<td class="py-3 px-4 flex items-center gap-2">
<span class="material-symbols-outlined text-[16px] text-on-surface-variant">cloud</span>
                                    Object Store S3
                                </td>
<td class="py-3 px-4">External AWS</td>
<td class="py-3 px-4 font-mono-data text-mono-data">30.1 TB</td>
<td class="py-3 px-4 font-mono-data text-mono-data">$1,850.50</td>
<td class="py-3 px-4">
<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#E6F4EA] text-[#137333] font-label-md text-[10px]">Active</span>
</td>
</tr>
<tr class="hover:bg-surface-container-low transition-colors group">
<td class="py-3 px-4 flex items-center gap-2">
<span class="material-symbols-outlined text-[16px] text-on-surface-variant">memory</span>
                                    Compute Node Alpha
                                </td>
<td class="py-3 px-4">Internal</td>
<td class="py-3 px-4 font-mono-data text-mono-data">2.6 TB</td>
<td class="py-3 px-4 font-mono-data text-mono-data">$950.00</td>
<td class="py-3 px-4">
<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#FEF7E0] text-[#B06000] font-label-md text-[10px]">Pending Review</span>
</td>
</tr>
</tbody>
</table>
</div>
</div>
</div>
<!-- Tab 2: Employee Billing -->
<div class="hidden flex-col gap-stack-default animate-[fadeIn_0.2s_ease-in-out]" id="tab-employee">
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-5">
<h3 class="font-headline-sm text-headline-sm text-on-surface mb-4 border-b border-outline-variant pb-2">Generate Report</h3>
<form method="get" action="{{ url('reporting') }}" class="flex flex-wrap gap-4 items-end">
<!-- Laravel CSRF placeholder -->
<input name="_token" type="hidden" value="csrf_placeholder"/>
<div class="flex flex-col gap-1.5 flex-grow sm:flex-grow-0 sm:w-64">
<label class="font-label-md text-label-md text-on-surface">Department</label>
<div class="relative">
<select class="w-full pl-3 pr-8 py-2 bg-surface border border-outline-variant rounded font-body-sm text-body-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none text-on-surface transition-colors appearance-none">
<option>Engineering</option>
<option>Sales</option>
<option>Marketing</option>
</select>
<span class="material-symbols-outlined absolute right-2 top-2.5 text-[20px] text-on-surface-variant pointer-events-none">arrow_drop_down</span>
</div>
</div>
<div class="flex flex-col gap-1.5 flex-grow sm:flex-grow-0 sm:w-64">
<label class="font-label-md text-label-md text-on-surface">Billing Period</label>
<div class="relative">
<input class="w-full pl-3 pr-3 py-2 bg-surface border border-outline-variant rounded font-body-sm text-body-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none text-on-surface transition-colors" type="month"/>
</div>
</div>
<button class="px-4 py-2 bg-primary text-on-primary font-label-md text-label-md rounded hover:bg-on-primary-fixed-variant transition-colors shadow-sm sm:ml-auto" type="submit">
                        Generate
                    </button>
</form>
</div>
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-low border-b border-outline-variant">
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant">Employee Name</th>
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant">Department</th>
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant">Billable Hours</th>
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant">Total Cost</th>
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant text-right">Actions</th>
</tr>
</thead>
<tbody class="font-body-sm text-body-sm text-on-surface divide-y divide-outline-variant">
<tr class="hover:bg-surface-container-low transition-colors group">
<td class="py-3 px-4 flex items-center gap-3">
<div class="w-6 h-6 rounded-full bg-secondary-container flex items-center justify-center text-[10px] font-bold text-on-secondary-container">JD</div>
                                    John Doe
                                </td>
<td class="py-3 px-4">Engineering</td>
<td class="py-3 px-4 font-mono-data text-mono-data">160h</td>
<td class="py-3 px-4 font-mono-data text-mono-data">$12,800.00</td>
<td class="py-3 px-4 text-right">
<button class="text-on-surface-variant hover:text-primary transition-colors opacity-0 group-hover:opacity-100"><span class="material-symbols-outlined text-[18px]">visibility</span></button>
</td>
</tr>
<tr class="hover:bg-surface-container-low transition-colors group">
<td class="py-3 px-4 flex items-center gap-3">
<div class="w-6 h-6 rounded-full bg-tertiary-container flex items-center justify-center text-[10px] font-bold text-on-tertiary-container">JS</div>
                                    Jane Smith
                                </td>
<td class="py-3 px-4">Sales</td>
<td class="py-3 px-4 font-mono-data text-mono-data">145h</td>
<td class="py-3 px-4 font-mono-data text-mono-data">$9,425.00</td>
<td class="py-3 px-4 text-right">
<button class="text-on-surface-variant hover:text-primary transition-colors opacity-0 group-hover:opacity-100"><span class="material-symbols-outlined text-[18px]">visibility</span></button>
</td>
</tr>
</tbody>
</table>
</div>
</div>
</div>
<!-- Tab 3: Recovery Analysis -->
<div class="hidden flex-col gap-stack-default animate-[fadeIn_0.2s_ease-in-out]" id="tab-recovery">
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-4 flex gap-4 items-center">
<div class="relative flex-grow max-w-md">
<span class="material-symbols-outlined absolute left-3 top-2 text-[20px] text-on-surface-variant">search</span>
<input class="w-full pl-10 pr-4 py-2 bg-surface border border-outline-variant rounded font-body-sm text-body-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none text-on-surface transition-colors" placeholder="Search Recovery ID or Client..." type="text"/>
</div>
<button class="p-2 text-on-surface-variant border border-outline-variant rounded hover:bg-surface-container-high transition-colors">
<span class="material-symbols-outlined text-[20px]">filter_list</span>
</button>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<!-- Empty State 1 -->
<div class="bg-surface-container-lowest border border-outline-variant border-dashed rounded-lg p-8 flex flex-col items-center justify-center text-center gap-3">
<div class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center">
<span class="material-symbols-outlined text-[24px] text-on-surface-variant">hourglass_empty</span>
</div>
<div>
<h4 class="font-headline-sm text-headline-sm text-on-surface">No Pending Recoveries</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">All recovery processes are currently up to date.</p>
</div>
</div>
<!-- Empty State 2 -->
<div class="bg-surface-container-lowest border border-outline-variant border-dashed rounded-lg p-8 flex flex-col items-center justify-center text-center gap-3">
<div class="w-12 h-12 rounded-full bg-surface-container-high flex items-center justify-center">
<span class="material-symbols-outlined text-[24px] text-on-surface-variant">inventory_2</span>
</div>
<div>
<h4 class="font-headline-sm text-headline-sm text-on-surface">Archived Reports Empty</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Older reports will be moved here automatically.</p>
</div>
</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden mt-4">
<div class="px-4 py-3 border-b border-outline-variant bg-surface-container-low flex justify-between items-center">
<h3 class="font-label-md text-label-md text-on-surface">Recent Recoveries</h3>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="border-b border-outline-variant">
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant">Client</th>
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant">Recovery ID</th>
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant">Amount</th>
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant">Date</th>
<th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant">Status</th>
</tr>
</thead>
<tbody class="font-body-sm text-body-sm text-on-surface divide-y divide-outline-variant">
<tr class="hover:bg-surface-container-low transition-colors">
<td class="py-3 px-4 font-medium">Acme Corp</td>
<td class="py-3 px-4 font-mono-data text-mono-data text-on-surface-variant">REC-2024-889</td>
<td class="py-3 px-4 font-mono-data text-mono-data">$45,200.00</td>
<td class="py-3 px-4 text-on-surface-variant">Oct 12, 2024</td>
<td class="py-3 px-4">
<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#E8F0FE] text-[#1967D2] font-label-md text-[10px]">Completed</span>
</td>
</tr>
</tbody>
</table>
</div>
</div>
</div>
</main>
<!-- Footer -->
<footer class="bg-surface-container-high dark:bg-surface-container-high w-full border-t border-outline-variant mt-auto">
<div class="flex flex-col md:flex-row justify-between items-center px-gutter py-section-gap max-w-container-max mx-auto gap-4">
<span class="font-label-md text-label-md font-bold text-on-surface">© 2024 NodeSky Infrastructure. All rights reserved.</span>
<div class="flex gap-4">
<a class="text-on-surface-variant dark:text-on-surface-variant font-body-sm text-body-sm hover:text-primary dark:hover:text-primary-fixed underline transition-all" href="#">Privacy Policy</a>
<a class="text-on-surface-variant dark:text-on-surface-variant font-body-sm text-body-sm hover:text-primary dark:hover:text-primary-fixed underline transition-all" href="#">Terms of Service</a>
<a class="text-on-surface-variant dark:text-on-surface-variant font-body-sm text-body-sm hover:text-primary dark:hover:text-primary-fixed underline transition-all" href="#">API Documentation</a>
<a class="text-on-surface-variant dark:text-on-surface-variant font-body-sm text-body-sm hover:text-primary dark:hover:text-primary-fixed underline transition-all" href="#">Support</a>
</div>
</div>
</footer>
<style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
<script>
        function switchTab(tabId) {
            // Hide all tabs
            document.getElementById('tab-usage').classList.add('hidden');
            document.getElementById('tab-usage').classList.remove('flex');
            document.getElementById('tab-employee').classList.add('hidden');
            document.getElementById('tab-employee').classList.remove('flex');
            document.getElementById('tab-recovery').classList.add('hidden');
            document.getElementById('tab-recovery').classList.remove('flex');

            // Reset all buttons
            const buttons = document.querySelectorAll('button[onclick^="switchTab"]');
            buttons.forEach(btn => {
                btn.classList.remove('border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-on-surface-variant');
            });

            // Show selected tab and highlight button
            document.getElementById('tab-' + tabId).classList.remove('hidden');
            document.getElementById('tab-' + tabId).classList.add('flex');

            const activeBtn = document.querySelector(`button[onclick="switchTab('${tabId}')"]`);
            activeBtn.classList.remove('border-transparent', 'text-on-surface-variant');
            activeBtn.classList.add('border-primary', 'text-primary');
        }
    </script>
<script>window.__backendReady=true;</script>
<div style="display:none" data-backend-contract="reporting"><form method="get" action="{{ url('reporting') }}"><input name="month_cycle" value="{{ $monthCycle }}"></form><a href="{{ url('reports/monthly-summary') }}?month_cycle={{ urlencode((string)$monthCycle) }}">Monthly Summary JSON</a><a href="{{ url('reports/employee-bill-summary') }}?month_cycle={{ urlencode((string)$monthCycle) }}">Employee Bill Summary</a><a href="{{ url('reports/recovery') }}?month_cycle={{ urlencode((string)$monthCycle) }}">Recovery JSON</a></div></body></html>