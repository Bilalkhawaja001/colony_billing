<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>NodeSky Billing - Rates</title>
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
                        "on-secondary-fixed-variant": "#34476a",
                        "secondary-container": "#bfd2fd",
                        "error-container": "#ffdad6",
                        "on-primary": "#ffffff",
                        "on-tertiary-fixed-variant": "#374763",
                        "secondary-fixed-dim": "#b4c7f1",
                        "on-secondary-container": "#475a7e",
                        "on-tertiary": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "surface-container": "#edeef0",
                        "primary-fixed": "#dae2ff",
                        "on-primary-fixed": "#001848",
                        "on-error": "#ffffff",
                        "surface-container-low": "#f3f4f6",
                        "on-error-container": "#93000a",
                        "on-surface-variant": "#434654",
                        "surface-dim": "#d9dadc",
                        "surface": "#f8f9fb",
                        "on-primary-fixed-variant": "#0040a2",
                        "error": "#ba1a1a",
                        "surface-variant": "#e1e2e4",
                        "on-tertiary-fixed": "#091c35",
                        "outline": "#737685",
                        "tertiary": "#34445f",
                        "on-secondary": "#ffffff",
                        "on-surface": "#191c1e",
                        "primary-fixed-dim": "#b2c5ff",
                        "inverse-surface": "#2e3132",
                        "on-tertiary-container": "#c3d3f5",
                        "inverse-primary": "#b2c5ff",
                        "tertiary-fixed-dim": "#b7c7e8",
                        "surface-container-high": "#e7e8ea",
                        "background": "#f8f9fb",
                        "secondary-fixed": "#d7e2ff",
                        "primary": "#003d9b",
                        "on-secondary-fixed": "#041b3c",
                        "outline-variant": "#c3c6d6",
                        "tertiary-container": "#4b5b78",
                        "primary-container": "#0052cc",
                        "tertiary-fixed": "#d6e3ff",
                        "surface-container-highest": "#e1e2e4",
                        "inverse-on-surface": "#f0f1f3",
                        "on-background": "#191c1e",
                        "secondary": "#4c5e83",
                        "surface-bright": "#f8f9fb",
                        "on-primary-container": "#c4d2ff",
                        "surface-tint": "#0c56d0"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "sm": "8px",
                        "gutter": "20px",
                        "xl": "32px",
                        "md": "16px",
                        "container-max": "1440px",
                        "xs": "4px",
                        "base": "4px",
                        "lg": "24px"
                    },
                    "fontFamily": {
                        "body-lg": ["Inter"],
                        "mono-md": ["JetBrains Mono"],
                        "display-lg": ["Inter"],
                        "headline-sm": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-md": ["Inter"],
                        "headline-lg": ["Inter"],
                        "label-md": ["Inter"],
                        "body-sm": ["Inter"],
                        "display-md": ["Inter"]
                    },
                    "fontSize": {
                        "body-lg": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "mono-md": ["13px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "display-lg": ["36px", { "lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "headline-sm": ["16px", { "lineHeight": "24px", "fontWeight": "600" }],
                        "body-md": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "headline-md": ["20px", { "lineHeight": "28px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "headline-lg": ["24px", { "lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "label-md": ["12px", { "lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600" }],
                        "body-sm": ["12px", { "lineHeight": "18px", "fontWeight": "400" }],
                        "display-md": ["30px", { "lineHeight": "38px", "letterSpacing": "-0.02em", "fontWeight": "700" }]
                    }
                }
            }
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined[data-weight="fill"] {
            font-variation-settings: 'FILL' 1;
        }

        /* Custom scrollbar for raw data preview */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f3f4f6;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c3c6d6;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #737685;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col">
@include('partials.global-navbar')

<!-- TopNavBar -->
<header class="bg-surface border-b border-outline-variant w-full sticky top-0 z-50">
<div class="flex justify-between items-center w-full px-lg py-sm max-w-container-max mx-auto">
<!-- Brand -->
<div class="flex items-center gap-md">
<div class="text-headline-md font-headline-md font-bold text-primary">NodeSky Billing</div>
</div>
<!-- Navigation Links (Web) -->
<nav class="hidden md:flex gap-lg h-full items-end pt-2">
<a class="text-on-surface-variant hover:text-primary transition-colors pb-4 font-body-md" href="#">Dashboard</a>
<a class="text-primary border-b-2 border-primary pb-4 font-body-md font-bold" href="#">Rates</a>
<a class="text-on-surface-variant hover:text-primary transition-colors pb-4 font-body-md" href="#">Invoices</a>
<a class="text-on-surface-variant hover:text-primary transition-colors pb-4 font-body-md" href="#">Clients</a>
<a class="text-on-surface-variant hover:text-primary transition-colors pb-4 font-body-md" href="#">Reports</a>
<a class="text-on-surface-variant hover:text-primary transition-colors pb-4 font-body-md" href="#">Settings</a>
</nav>
<!-- Trailing Actions -->
<div class="flex items-center gap-md">
<div class="hidden sm:flex gap-sm items-center mr-sm">
<button class="p-2 text-on-surface-variant hover:text-primary transition-colors rounded-full hover:bg-surface-variant">
<span class="material-symbols-outlined" data-icon="notifications">notifications</span>
</button>
<button class="p-2 text-on-surface-variant hover:text-primary transition-colors rounded-full hover:bg-surface-variant">
<span class="material-symbols-outlined" data-icon="help_outline">help_outline</span>
</button>
</div>
<button class="bg-primary text-on-primary px-4 py-2 rounded-lg font-label-md hover:bg-primary-container transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Create Rate
                </button>
<div class="h-8 w-8 rounded-full bg-surface-container-high border border-outline-variant overflow-hidden flex-shrink-0 cursor-pointer ml-sm">
<img alt="Admin User Profile" class="w-full h-full object-cover" data-alt="A professional headshot of an administrative user in a modern corporate setting, neutral lighting, professional attire." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDVeYuvBBSK7w6bSquiXmhEoQruBD55UDdMIV_w-EN47Rn-HKBDzrRh5lDONW-BqJDuELDfuXW6BrVM4w_wOYN1zLKzsuxypao6uRtQ7Q1DYqcaySHJUIVFKRA8fKOujBgBJXb_spCWInHPPdqtXRuhE-rCuW30dASIDvDEQnEnWeN3uI-LIbW3uUBuLQ0kr7MELxv0wkzx2yIVCRW-f6bNEQHzZ2GJVV6CwfP1d5sc5jdu15lOtemHWg"/>
</div>
<!-- Mobile Menu Toggle -->
<button class="md:hidden p-2 text-on-surface-variant ml-xs">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
</div>
</header>
<!-- Main Content Canvas -->
<main class="flex-1 w-full max-w-container-max mx-auto px-md md:px-lg py-lg md:py-xl grid grid-cols-1 md:grid-cols-12 gap-gutter">
<!-- Header & Summary Metrics -->
<div class="col-span-1 md:col-span-12 mb-md">
<h1 class="text-display-md font-display-md text-on-surface mb-lg">Monthly Rate Configuration</h1>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-md">
<!-- Metric Card 1 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-md flex flex-col gap-xs relative overflow-hidden group">
<div class="absolute -right-4 -top-4 w-16 h-16 bg-primary/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
<span class="text-label-md font-label-md text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[16px]">monitoring</span> Active Rates
                    </span>
<span class="text-headline-lg font-headline-lg text-on-surface">1,248</span>
<span class="text-body-sm font-body-sm text-primary">+12% vs last month</span>
</div>
<!-- Metric Card 2 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-md flex flex-col gap-xs relative overflow-hidden group">
<div class="absolute -right-4 -top-4 w-16 h-16 bg-error/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
<span class="text-label-md font-label-md text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[16px]">pending_actions</span> Pending Approvals
                    </span>
<span class="text-headline-lg font-headline-lg text-on-surface">34</span>
<span class="text-body-sm font-body-sm text-error">Requires attention</span>
</div>
<!-- Metric Card 3 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-md flex flex-col gap-xs relative overflow-hidden group">
<div class="absolute -right-4 -top-4 w-16 h-16 bg-secondary/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
<span class="text-label-md font-label-md text-on-surface-variant flex items-center gap-1">
<span class="material-symbols-outlined text-[16px]">update</span> Last Updated
                    </span>
<span class="text-headline-lg font-headline-lg text-on-surface">2h ago</span>
<span class="text-body-sm font-body-sm text-on-surface-variant">System auto-sync</span>
</div>
</div>
</div>
<!-- Left Column: Forms -->
<div class="col-span-1 md:col-span-8 flex flex-col gap-lg">
<!-- Rate Configuration Form -->
<section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-lg">
<div class="flex items-center gap-2 mb-md border-b border-outline-variant pb-sm">
<span class="material-symbols-outlined text-primary">tune</span>
<h2 class="text-headline-md font-headline-md text-on-surface">Configuration Details</h2>
</div>
<form action="{{ url('rates') }}" class="space-y-md" method="POST">
<!-- Fake CSRF -->
<input name="_token" type="hidden" value="dummy_csrf_token_string"/>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
<!-- Rate Name -->
<div class="flex flex-col gap-xs">
<label class="text-label-md font-label-md text-on-surface" for="rate_name">Rate Name</label>
<input class="bg-surface border border-outline-variant rounded-lg px-3 py-2 text-body-md font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" id="rate_name" name="rate_name" placeholder="e.g. Enterprise Tier A" type="text"/>
</div>
<!-- Effective Date -->
<div class="flex flex-col gap-xs">
<label class="text-label-md font-label-md text-on-surface" for="effective_date">Effective Date</label>
<input class="bg-surface border border-outline-variant rounded-lg px-3 py-2 text-body-md font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" id="effective_date" name="effective_date" type="date" value="2024-05-01"/>
</div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-md">
<!-- Currency -->
<div class="flex flex-col gap-xs">
<label class="text-label-md font-label-md text-on-surface" for="currency">Currency</label>
<select class="bg-surface border border-outline-variant rounded-lg px-3 py-2 text-body-md font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors appearance-none" id="currency" name="currency">
<option value="USD">USD ($)</option>
<option value="EUR">EUR (€)</option>
<option value="GBP">GBP (£)</option>
</select>
</div>
<!-- Base Amount (Error State Example) -->
<div class="flex flex-col gap-xs">
<label class="text-label-md font-label-md text-on-surface" for="base_amount">Base Amount</label>
<div class="relative">
<span class="absolute left-3 top-2.5 text-on-surface-variant font-body-md">$</span>
<input class="w-full bg-surface border-2 border-error rounded-lg pl-8 pr-3 py-2 text-body-md font-body-md text-on-surface focus:outline-none focus:ring-0" id="base_amount" name="base_amount" placeholder="0.00" type="number" value="-500"/>
<span class="absolute right-3 top-2.5 text-error material-symbols-outlined text-[18px]">error</span>
</div>
<span class="text-label-md font-label-md text-error">Base amount cannot be negative.</span>
</div>
<!-- Overload Factor -->
<div class="flex flex-col gap-xs">
<label class="text-label-md font-label-md text-on-surface" for="overload_factor">Overload Factor</label>
<div class="relative">
<input class="w-full bg-surface border border-outline-variant rounded-lg px-3 py-2 text-body-md font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" id="overload_factor" name="overload_factor" step="0.1" type="number" value="1.5"/>
<span class="absolute right-3 top-2.5 text-on-surface-variant font-body-md">x</span>
</div>
</div>
</div>
<!-- Billing Cycle Types -->
<div class="flex flex-col gap-xs pt-sm border-t border-outline-variant">
<label class="text-label-md font-label-md text-on-surface">Billing Cycle</label>
<div class="flex flex-wrap gap-md mt-1">
<label class="flex items-center gap-2 cursor-pointer">
<input checked="" class="text-primary focus:ring-primary border-outline-variant w-4 h-4" name="cycle" type="radio" value="monthly"/>
<span class="text-body-md font-body-md text-on-surface">Monthly</span>
</label>
<label class="flex items-center gap-2 cursor-pointer">
<input class="text-primary focus:ring-primary border-outline-variant w-4 h-4" name="cycle" type="radio" value="quarterly"/>
<span class="text-body-md font-body-md text-on-surface">Quarterly</span>
</label>
<label class="flex items-center gap-2 cursor-pointer">
<input class="text-primary focus:ring-primary border-outline-variant w-4 h-4" name="cycle" type="radio" value="annually"/>
<span class="text-body-md font-body-md text-on-surface">Annually</span>
</label>
</div>
</div>
</form>
</section>
<!-- Approval Step Section -->
<section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-lg relative overflow-hidden">
<!-- Loading State Overlay Example (Hidden by default for visual, uncomment flex to see) -->
<div class="hidden absolute inset-0 bg-surface/80 backdrop-blur-sm z-10 flex-col items-center justify-center gap-2">
<span class="material-symbols-outlined animate-spin text-primary text-[32px]">progress_activity</span>
<span class="text-label-md font-label-md text-on-surface">Processing Assignment...</span>
</div>
<div class="flex items-center justify-between mb-md border-b border-outline-variant pb-sm">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-secondary">assignment_ind</span>
<h2 class="text-headline-md font-headline-md text-on-surface">Approval Assignment</h2>
</div>
<span class="bg-secondary-container text-on-secondary-container px-2 py-1 rounded text-label-md font-label-md uppercase tracking-wider">Draft</span>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-md">
<div class="flex flex-col gap-xs">
<label class="text-label-md font-label-md text-on-surface" for="reviewer">Assign Reviewer</label>
<select class="bg-surface border border-outline-variant rounded-lg px-3 py-2 text-body-md font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors appearance-none" id="reviewer" name="reviewer">
<option value="">Select a compliance officer...</option>
<option value="1">Sarah Jenkins (Tier 1)</option>
<option value="2">Michael Chang (Tier 2)</option>
<option value="3">Elena Rossi (Director)</option>
</select>
</div>
<div class="flex flex-col gap-xs md:col-span-2">
<label class="text-label-md font-label-md text-on-surface" for="comments">Submission Comments</label>
<textarea class="bg-surface border border-outline-variant rounded-lg px-3 py-2 text-body-md font-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors resize-none" id="comments" name="comments" placeholder="Add any context for the reviewer..." rows="3"></textarea>
</div>
</div>
<div class="flex justify-end gap-md mt-lg pt-md border-t border-outline-variant">
<button class="px-4 py-2 border border-outline-variant text-on-surface rounded-lg font-label-md hover:bg-surface-variant transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-outline-variant" type="button">
                        Save Draft
                    </button>
<button class="bg-primary text-on-primary px-4 py-2 rounded-lg font-label-md hover:bg-primary-container transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-primary flex items-center gap-2" type="submit">
<span class="material-symbols-outlined text-[18px]">send</span> Submit for Review
                    </button>
</div>
</section>
</div>
<!-- Right Column: API Results & Status -->
<div class="col-span-1 md:col-span-4 flex flex-col gap-lg">
<!-- System Alerts -->
<div class="flex flex-col gap-sm">
<!-- Success Alert -->
<div class="bg-[#E8F5E9] border-l-4 border-[#2E7D32] p-md rounded-r-lg flex items-start gap-3 shadow-sm">
<span class="material-symbols-outlined text-[#2E7D32]">check_circle</span>
<div>
<h4 class="text-headline-sm font-headline-sm text-[#1B5E20]">Connection Established</h4>
<p class="text-body-sm font-body-sm text-[#2E7D32] mt-1">Pricing engine API is responding normally (24ms ping).</p>
</div>
</div>
</div>
<!-- API Result Card -->
<section class="bg-inverse-surface border border-outline-variant rounded-xl p-0 flex flex-col h-full min-h-[400px] shadow-sm overflow-hidden">
<div class="p-md border-b border-outline-variant/30 flex justify-between items-center bg-inverse-surface">
<h3 class="text-headline-sm font-headline-sm text-inverse-primary flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">terminal</span> API Dry-Run
                    </h3>
<div class="flex items-center gap-2">
<span class="relative flex h-3 w-3">
<span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
<span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
</span>
<span class="text-label-md font-label-md text-on-surface-variant text-gray-400">Live</span>
</div>
</div>
<div class="p-md flex-1 bg-[#1E1E1E] overflow-auto custom-scrollbar">
<pre class="font-mono-md text-mono-md text-gray-300 whitespace-pre-wrap break-all"><code>{
  <span class="text-blue-400">"status"</span>: <span class="text-green-400">"success"</span>,
  <span class="text-blue-400">"timestamp"</span>: <span class="text-orange-300">"2024-05-24T10:30:15Z"</span>,
  <span class="text-blue-400">"data"</span>: {
    <span class="text-blue-400">"rate_id"</span>: <span class="text-orange-300">"rt_9f8e7d6c"</span>,
    <span class="text-blue-400">"computation_hash"</span>: <span class="text-green-400">"a1b2c3d4..."</span>,
    <span class="text-blue-400">"projected_mrr"</span>: <span class="text-yellow-300">12500.00</span>,
    <span class="text-blue-400">"factors_applied"</span>: [
      <span class="text-orange-300">"base_tier"</span>,
      <span class="text-orange-300">"volume_discount"</span>
    ],
    <span class="text-blue-400">"warnings"</span>: [
      <span class="text-red-400">"Base amount parameter anomalous (-500)"</span>
    ]
  }
}</code></pre>
</div>
<div class="p-sm bg-inverse-surface border-t border-outline-variant/30 flex justify-between items-center text-gray-400">
<span class="text-label-md font-label-md text-xs">Response Time: 112ms</span>
<button class="text-inverse-primary hover:text-primary-fixed-dim text-label-md font-label-md flex items-center gap-1 transition-colors">
<span class="material-symbols-outlined text-[16px]">content_copy</span> Copy JSON
                    </button>
</div>
</section>
</div>
</main>
<!-- Footer -->
<footer class="bg-surface-container-low border-t border-outline-variant mt-auto">
<div class="w-full py-lg px-xl flex flex-col md:flex-row justify-between items-center max-w-container-max mx-auto gap-md">
<div class="font-headline-sm text-headline-sm text-on-surface">
                NodeSky Billing
            </div>
<nav class="flex flex-wrap justify-center gap-lg">
<a class="text-on-surface-variant text-label-md font-label-md opacity-80 hover:opacity-100 hover:underline transition-opacity" href="#">Documentation</a>
<a class="text-on-surface-variant text-label-md font-label-md opacity-80 hover:opacity-100 hover:underline transition-opacity" href="#">API Reference</a>
<a class="text-on-surface-variant text-label-md font-label-md opacity-80 hover:opacity-100 hover:underline transition-opacity" href="#">Support</a>
<a class="text-on-surface-variant text-label-md font-label-md opacity-80 hover:opacity-100 hover:underline transition-opacity" href="#">System Status</a>
</nav>
<div class="text-secondary text-label-md font-label-md">
                © 2024 NodeSky Billing Infrastructure. All rights reserved.
            </div>
</div>
</footer>
<div style="display:none" data-backend-contract="rates"><form id="ratesUpsertForm"><input name="month_cycle" value="{{ $monthCycle }}"><input name="elec_rate"><input name="water_general_rate"><input name="water_drinking_rate"><input name="school_van_rate"></form><form id="ratesApproveForm"><input name="month_cycle" value="{{ $monthCycle }}"></form><pre id="ratesResult"></pre></div>
<script>const csrf=@json(csrf_token());const appBase=@json(url(''));function appUrl(p){return appBase+'/'+String(p).replace(/^\/+/, '')}</script></body></html>