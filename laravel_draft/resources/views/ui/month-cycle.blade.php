<!DOCTYPE html><html lang="en" class="light"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=block" rel="stylesheet"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=JetBrains+Mono:wght@400&amp;display=swap" rel="stylesheet"><script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script><script id="tailwind-config">try{
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-secondary-fixed-variant": "#003ea8",
                        "tertiary-fixed-dim": "#b7c8e1",
                        "inverse-primary": "#bec6e0",
                        "on-secondary": "#ffffff",
                        "on-error-container": "#93000a",
                        "outline-variant": "#c6c6cd",
                        "on-background": "#191c1e",
                        "on-tertiary-fixed": "#0b1c30",
                        "surface-dim": "#d8dadc",
                        "surface-variant": "#e0e3e5",
                        "error": "#ba1a1a",
                        "primary-fixed-dim": "#bec6e0",
                        "on-tertiary": "#ffffff",
                        "on-primary-fixed": "#131b2e",
                        "tertiary": "#000000",
                        "surface-container-highest": "#e0e3e5",
                        "on-primary-container": "#7c839b",
                        "on-surface-variant": "#45464d",
                        "surface-container": "#eceef0",
                        "surface-container-high": "#e6e8ea",
                        "inverse-on-surface": "#eff1f3",
                        "on-tertiary-fixed-variant": "#38485d",
                        "on-primary": "#ffffff",
                        "secondary-container": "#316bf3",
                        "primary-fixed": "#dae2fd",
                        "on-tertiary-container": "#75859d",
                        "error-container": "#ffdad6",
                        "background": "#f7f9fb",
                        "surface-tint": "#565e74",
                        "outline": "#76777d",
                        "surface-bright": "#f7f9fb",
                        "on-secondary-fixed": "#00174b",
                        "surface-container-lowest": "#ffffff",
                        "on-secondary-container": "#fefcff",
                        "surface": "#f7f9fb",
                        "inverse-surface": "#2d3133",
                        "primary": "#000000",
                        "primary-container": "#131b2e",
                        "surface-container-low": "#f2f4f6",
                        "tertiary-fixed": "#d3e4fe",
                        "on-error": "#ffffff",
                        "on-primary-fixed-variant": "#3f465c",
                        "secondary": "#0051d5",
                        "secondary-fixed": "#dbe1ff",
                        "on-surface": "#191c1e",
                        "secondary-fixed-dim": "#b4c5ff",
                        "tertiary-container": "#0b1c30"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "sm": "8px",
                        "xs": "4px",
                        "base": "4px",
                        "md": "16px",
                        "xl": "32px",
                        "container-max": "1440px",
                        "2xl": "48px",
                        "lg": "24px",
                        "gutter": "20px"
                    },
                    "fontFamily": {
                        "body-sm": ["Inter"],
                        "mono-data": ["JetBrains Mono"],
                        "display-lg": ["Inter"],
                        "headline-md": ["Inter"],
                        "body-lg": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-sm": ["Inter"],
                        "label-md": ["Inter"]
                    },
                    "fontSize": {
                        "body-sm": ["13px", { "lineHeight": "18px", "fontWeight": "400" }],
                        "mono-data": ["13px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "display-lg": ["36px", { "lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "body-lg": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "body-md": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "headline-sm": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "label-md": ["12px", { "lineHeight": "16px", "fontWeight": "600" }]
                    }
                },
            },
        }
    }catch(_e){}</script><meta charset="utf-8"></head><body class="bg-background text-on-surface font-body-md min-h-screen flex flex-col">
@include('partials.global-navbar')

<!-- TopNavBar -->
<nav class="sticky top-0 w-full z-50 flex justify-between items-center px-lg py-sm bg-surface-container-lowest border-b border-outline-variant">
<div class="flex items-center gap-xl">
<span class="font-display-lg text-display-lg font-bold text-primary">NodeSky Billing</span>
<div class="hidden md:flex gap-md">
<a href="#" class="text-secondary border-b-2 border-secondary pb-1 font-bold hover:text-secondary-dim transition-colors duration-200">Billing</a>
<a href="#" class="text-on-surface-variant font-medium hover:text-secondary transition-colors duration-200">Admin</a>
<a href="#" class="text-on-surface-variant font-medium hover:text-secondary transition-colors duration-200">Office Staff</a>
</div>
</div>
<div class="flex flex-1 justify-center px-lg">
<div class="relative w-full max-w-md hidden md:block">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
<input type="text" placeholder="Search resources..." class="w-full pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant rounded focus:border-secondary focus:ring-2 focus:ring-secondary/20 outline-none transition-all text-body-sm font-body-sm">
</div>
</div>
<div class="flex items-center gap-sm">
<button class="p-2 text-on-surface-variant hover:text-secondary transition-colors duration-200">
<span class="material-symbols-outlined" data-original-icon="notifications">notifications</span>
</button>
<button class="p-2 text-on-surface-variant hover:text-secondary transition-colors duration-200">
<span class="material-symbols-outlined">help</span>
</button>
<button class="p-2 text-on-surface-variant hover:text-secondary transition-colors duration-200">
<span class="material-symbols-outlined">settings</span>
</button>
<img alt="User profile avatar" class="w-8 h-8 rounded-full border border-outline-variant ml-2 object-cover" data-alt="A small, professional user avatar portrait of a person wearing business casual attire, set against a clean, light studio background, suitable for a corporate dashboard profile icon." src="https://lh3.googleusercontent.com/aida-public/AB6AXuA-9O5dsslNWf1p1hCMUdrjkttotZYGNv-mgWDRyUei1fCGHNOfuL8zCHSW1-vh3_0HMlL4pYPdfyjAIFec6FZwaJbdkalfRpktlp_rAMONd5FuiP9uPn8vFaUmcK7e8Ot9kimImJb-5P9X_zPEP-fALGREpt-xWL9JiGw8N0NzXs-17rTwMNoUzDfFZfTRvAmupjkFVp9Dg3Jc3h-e9TVdUqizX3mitfM27fnspUBHLGyAECJ6KjXV">
</div>
</nav>
<!-- Main Layout -->
<div class="flex flex-1 overflow-hidden">
<!-- Sidebar (Placeholder for context) -->

<!-- Main Content -->
<main class="flex-1 overflow-y-auto p-lg md:p-xl bg-background">
<div class="max-w-container-max mx-auto space-y-xl">
<!-- Page Header & Alert -->
<div>
<h1 class="font-headline-md text-headline-md text-primary mb-sm">Billing Month Setup</h1>
<div class="bg-[#fef3c7] border border-[#f59e0b] text-[#92400e] px-md py-sm rounded flex items-start gap-sm">
<span class="material-symbols-outlined text-[#f59e0b] mt-[2px]">warning</span>
<div>
<p class="font-bold text-body-md">Warning: Month ending soon</p>
<p class="text-body-sm mt-1">The current billing month (October 2023) will automatically close in 2 days. Ensure all pending manual adjustments are verified.</p>
</div>
</div>
</div>
<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter">
<!-- Card 1 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded p-md flex flex-col justify-between h-[100px]">
<p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wide">Active Month</p>
<p class="font-headline-sm text-headline-sm text-primary">Oct 2023</p>
</div>
<!-- Card 2 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded p-md flex flex-col justify-between h-[100px]">
<p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wide">Pending Transactions</p>
<p class="font-headline-sm text-headline-sm text-primary">1,245</p>
</div>
<!-- Card 3 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded p-md flex flex-col justify-between h-[100px]">
<p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wide">Processed Invoices</p>
<div class="flex items-end gap-sm">
<p class="font-headline-sm text-headline-sm text-primary">89.4%</p>
<div class="w-full bg-surface-container-high h-1 rounded-full mb-2 flex-1">
<div class="bg-secondary h-1 rounded-full w-[89.4%]"></div>
</div>
</div>
</div>
<!-- Card 4 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded p-md flex flex-col justify-between h-[100px]">
<p class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wide">Days to Close</p>
<p class="font-headline-sm text-headline-sm text-error font-bold">02</p>
</div>
</div>
<!-- Tabbed Interface -->
<div class="border-b border-outline-variant flex gap-lg">
<button class="pb-sm border-b-2 border-secondary text-secondary font-bold font-body-md px-xs">Current Setup</button>
<button class="pb-sm border-b-2 border-transparent text-on-surface-variant hover:text-primary font-medium font-body-md px-xs transition-colors">History</button>
<button class="pb-sm border-b-2 border-transparent text-on-surface-variant hover:text-primary font-medium font-body-md px-xs transition-colors">Logs</button>
</div>
<!-- Forms Grid Layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-xl">
<!-- Open Month Config -->
<section class="bg-surface-container-lowest border border-outline-variant rounded flex flex-col h-full">
<div class="p-md border-b border-outline-variant">
<h2 class="font-headline-sm text-headline-sm text-primary">Open Month</h2>
</div>
<div class="p-md flex-1 space-y-md">
<div class="grid grid-cols-2 gap-md">
<div class="space-y-sm">
<label class="block font-label-md text-label-md text-on-surface">Target Year</label>
<select class="w-full border border-outline-variant rounded px-sm py-2 bg-white focus:border-secondary focus:ring-2 focus:ring-secondary/20 outline-none text-body-md">
<option>2023</option>
<option>2024</option>
</select>
</div>
<div class="space-y-sm">
<label class="block font-label-md text-label-md text-on-surface">Target Month</label>
<select class="w-full border border-outline-variant rounded px-sm py-2 bg-white focus:border-secondary focus:ring-2 focus:ring-secondary/20 outline-none text-body-md">
<option>November</option>
<option>December</option>
</select>
</div>
</div>
<div class="space-y-sm">
<label class="block font-label-md text-label-md text-on-surface">Proration Ruleset</label>
<select class="w-full border border-error rounded px-sm py-2 bg-white focus:border-error focus:ring-2 focus:ring-error/20 outline-none text-body-md">
<option>Select Ruleset...</option>
<option>Standard Corporate</option>
</select>
<p class="text-error font-body-sm text-body-sm mt-1">Please select a valid ruleset for the upcoming month.</p>
</div>
<div class="space-y-sm">
<label class="block font-label-md text-label-md text-on-surface">Cutoff Date Override</label>
<input class="w-full border border-outline-variant rounded px-sm py-2 bg-white focus:border-secondary focus:ring-2 focus:ring-secondary/20 outline-none text-body-md" type="date">
</div>
<div class="pt-sm">
<button class="w-full bg-primary text-on-primary py-2 rounded font-body-md font-medium hover:bg-surface-tint transition-colors">Initialize Month</button>
</div>
</div>
</section>
<!-- Transition State Config -->
<section class="bg-surface-container-lowest border border-outline-variant rounded flex flex-col h-full">
<div class="p-md border-b border-outline-variant flex justify-between items-center">
<h2 class="font-headline-sm text-headline-sm text-primary">Transition State</h2>
<span class="bg-[#dcfce7] text-[#166534] px-2 py-1 rounded font-label-md text-[10px] uppercase font-bold">Ready</span>
</div>
<div class="p-md flex-1 space-y-md">
<p class="font-body-md text-body-md text-on-surface-variant mb-md">Configure policies for transitioning active billing cycles.</p>
<div class="flex items-center justify-between py-2 border-b border-surface-variant">
<div>
<p class="font-body-md text-body-md font-medium text-on-surface">Auto-Close Previous Month</p>
<p class="font-body-sm text-body-sm text-on-surface-variant">Automatically finalize when new month opens</p>
</div>
<div class="relative inline-block w-10 mr-2 align-middle select-none">
<input checked="" class="checked:bg-secondary outline-none focus:outline-none right-4 checked:right-0 duration-200 ease-in absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" id="toggle1" name="toggle" type="checkbox">
<label class="block overflow-hidden h-6 rounded-full bg-surface-dim cursor-pointer" for="toggle1"></label>
</div>
</div>
<div class="flex items-center justify-between py-2 border-b border-surface-variant">
<div>
<p class="font-body-md text-body-md font-medium text-on-surface">Strict Ledger Locking</p>
<p class="font-body-sm text-body-sm text-on-surface-variant">Prevent backdating entries past 48 hours</p>
</div>
<div class="relative inline-block w-10 mr-2 align-middle select-none">
<input class="checked:bg-secondary outline-none focus:outline-none right-4 checked:right-0 duration-200 ease-in absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" id="toggle2" name="toggle" type="checkbox">
<label class="block overflow-hidden h-6 rounded-full bg-surface-dim cursor-pointer" for="toggle2"></label>
</div>
</div>
<div class="space-y-sm mt-md">
<label class="block font-label-md text-label-md text-on-surface">Grace Period (Days)</label>
<div class="flex gap-sm">
<input class="w-24 border border-outline-variant rounded px-sm py-2 bg-white focus:border-secondary focus:ring-2 focus:ring-secondary/20 outline-none text-body-md" type="number" value="3">
<button class="bg-white border border-outline-variant text-primary px-md py-2 rounded font-body-md font-medium hover:bg-surface-container-low transition-colors">Update Policy</button>
</div>
</div>
</div>
</section>
</div>
<!-- Month State Register Table -->
<section class="bg-surface-container-lowest border border-outline-variant rounded overflow-hidden">
<div class="p-md border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
<h2 class="font-headline-sm text-headline-sm text-primary">Month State Register</h2>
<button class="text-secondary font-medium font-body-sm flex items-center gap-xs hover:underline">
                            View Full Ledger <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</button>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-container-lowest border-b border-outline-variant">
<th class="p-sm font-label-md text-label-md text-outline uppercase font-semibold">Period</th>
<th class="p-sm font-label-md text-label-md text-outline uppercase font-semibold">Status</th>
<th class="p-sm font-label-md text-label-md text-outline uppercase font-semibold">Processed Date</th>
<th class="p-sm font-label-md text-label-md text-outline uppercase font-semibold">Total Billed</th>
<th class="p-sm font-label-md text-label-md text-outline uppercase font-semibold text-right">Actions</th>
</tr>
</thead>
<tbody class="font-body-md text-body-md">
<tr class="border-b border-surface-variant hover:bg-surface-bright transition-colors">
<td class="p-sm font-medium">Oct 2023</td>
<td class="p-sm">
<span class="bg-[#dbeafe] text-[#1e40af] px-2 py-1 rounded font-label-md text-[10px] uppercase font-bold">Active</span>
</td>
<td class="p-sm text-on-surface-variant">-</td>
<td class="p-sm font-mono-data text-mono-data">$142,500.00</td>
<td class="p-sm text-right">
<button class="text-secondary hover:text-secondary-dim font-medium px-2">Force Close</button>
</td>
</tr>
<tr class="border-b border-surface-variant hover:bg-surface-bright transition-colors">
<td class="p-sm font-medium">Sep 2023</td>
<td class="p-sm">
<span class="bg-[#f3f4f6] text-[#374151] px-2 py-1 rounded font-label-md text-[10px] uppercase font-bold">Closed</span>
</td>
<td class="p-sm text-on-surface-variant">Oct 02, 2023</td>
<td class="p-sm font-mono-data text-mono-data">$138,210.50</td>
<td class="p-sm text-right">
<button class="text-secondary hover:text-secondary-dim font-medium px-2">Audit</button>
</td>
</tr>
<tr class="hover:bg-surface-bright transition-colors">
<td class="p-sm font-medium">Aug 2023</td>
<td class="p-sm">
<span class="bg-[#f3f4f6] text-[#374151] px-2 py-1 rounded font-label-md text-[10px] uppercase font-bold">Closed</span>
</td>
<td class="p-sm text-on-surface-variant">Sep 01, 2023</td>
<td class="p-sm font-mono-data text-mono-data">$135,900.00</td>
<td class="p-sm text-right">
<button class="text-secondary hover:text-secondary-dim font-medium px-2">Audit</button>
</td>
</tr>
</tbody>
</table>
</div>
</section>
<!-- Execution Status (Empty State Example) -->
<section class="bg-surface-container-lowest border border-outline-variant rounded overflow-hidden">
<div class="p-md border-b border-outline-variant flex justify-between items-center bg-surface-container-low">
<h2 class="font-headline-sm text-headline-sm text-primary">Execution Status (Background Jobs)</h2>
<button class="p-1 hover:bg-surface-variant rounded text-on-surface-variant transition-colors"><span class="material-symbols-outlined text-[20px]">refresh</span></button>
</div>
<div class="p-xl flex flex-col items-center justify-center text-center">
<span class="material-symbols-outlined text-[48px] text-outline-variant mb-sm">task</span>
<p class="font-headline-sm text-headline-sm text-on-surface mb-xs">No Active Jobs</p>
<p class="font-body-md text-body-md text-on-surface-variant max-w-md">There are currently no background billing executions running. Trigger a manual sync or wait for the scheduled cycle.</p>
</div>
</section>
</div>
</main>
</div>

<div style="display:none" data-backend-contract="month-lifecycle"><form method="post" action="{{ url('month/open') }}">@csrf<input name="month_cycle" value="{{ $monthCycle }}"></form><form method="post" action="{{ url('month/transition') }}">@csrf<input name="month_cycle" value="{{ $monthCycle }}"></form></div></body></html>