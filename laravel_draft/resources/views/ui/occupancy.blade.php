<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>NodeSky Billing - Occupancy</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=JetBrains+Mono:wght@400&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                            "surface-variant": "#e0e3e5",
                            "on-tertiary-fixed": "#131b2e",
                            "error": "#ba1a1a",
                            "tertiary": "#4d556b",
                            "outline": "#737686",
                            "on-surface-variant": "#434655",
                            "on-error-container": "#93000a",
                            "on-primary-fixed-variant": "#003ea8",
                            "surface": "#f7f9fb",
                            "surface-dim": "#d8dadc",
                            "surface-container": "#eceef0",
                            "primary-fixed": "#dbe1ff",
                            "on-primary-fixed": "#00174b",
                            "surface-container-lowest": "#ffffff",
                            "on-tertiary": "#ffffff",
                            "on-secondary-container": "#54647a",
                            "surface-container-low": "#f2f4f6",
                            "on-error": "#ffffff",
                            "secondary-container": "#d0e1fb",
                            "on-secondary-fixed-variant": "#38485d",
                            "secondary-fixed-dim": "#b7c8e1",
                            "on-tertiary-fixed-variant": "#3f465c",
                            "on-primary": "#ffffff",
                            "error-container": "#ffdad6",
                            "surface-bright": "#f7f9fb",
                            "secondary": "#505f76",
                            "surface-tint": "#0053db",
                            "on-primary-container": "#eeefff",
                            "tertiary-fixed": "#dae2fd",
                            "primary-container": "#2563eb",
                            "tertiary-container": "#656d84",
                            "outline-variant": "#c3c6d7",
                            "surface-container-highest": "#e0e3e5",
                            "on-background": "#191c1e",
                            "inverse-on-surface": "#eff1f3",
                            "secondary-fixed": "#d3e4fe",
                            "background": "#f7f9fb",
                            "surface-container-high": "#e6e8ea",
                            "on-secondary-fixed": "#0b1c30",
                            "primary": "#004ac6",
                            "inverse-surface": "#2d3133",
                            "primary-fixed-dim": "#b4c5ff",
                            "on-surface": "#191c1e",
                            "on-secondary": "#ffffff",
                            "tertiary-fixed-dim": "#bec6e0",
                            "inverse-primary": "#b4c5ff",
                            "on-tertiary-container": "#eef0ff"
                    },
                    "borderRadius": {
                            "DEFAULT": "0.25rem",
                            "lg": "0.5rem",
                            "xl": "0.75rem",
                            "full": "9999px"
                    },
                    "spacing": {
                            "xl": "40px",
                            "gutter": "24px",
                            "sm": "8px",
                            "md": "16px",
                            "container-max": "1440px",
                            "xs": "4px",
                            "lg": "24px",
                            "base": "4px"
                    },
                    "fontFamily": {
                            "display": ["Inter"],
                            "label-md": ["Inter"],
                            "code": ["jetbrainsMono"],
                            "headline-md": ["Inter"],
                            "headline-lg-mobile": ["Inter"],
                            "headline-lg": ["Inter"],
                            "body-md": ["Inter"],
                            "body-lg": ["Inter"]
                    },
                    "fontSize": {
                            "display": ["36px", {"lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                            "label-md": ["12px", {"lineHeight": "16px", "letterSpacing": "0.01em", "fontWeight": "500"}],
                            "code": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                            "headline-md": ["18px", {"lineHeight": "26px", "fontWeight": "600"}],
                            "headline-lg-mobile": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                            "headline-lg": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                            "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                            "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}]
                    }
                }
            }
        }
    </script>
<style>
        body { background-color: #F8FAFC; }
        .input-focus:focus { box-shadow: 0px 0px 0px 4px rgba(37, 99, 235, 0.15); border-color: #2563eb; outline: none; }
    </style>
</head>
<body class="text-on-background min-h-screen flex flex-col font-body-md text-body-md">
@include('partials.global-navbar')

<!-- TopNavBar -->
<header class="bg-surface-container-lowest dark:bg-inverse-surface w-full h-16 border-b border-outline-variant dark:border-outline flat no shadows sticky top-0 z-50">
<div class="flex justify-between items-center px-lg h-16 max-w-container-max mx-auto">
<div class="flex items-center gap-lg h-full">
<span class="font-display text-headline-md font-bold text-primary dark:text-primary-fixed-dim">NodeSky Billing</span>
<nav class="hidden md:flex items-center gap-md h-full ml-xl">
<a class="text-secondary dark:text-secondary-fixed-dim h-full flex items-center px-1 hover:text-primary dark:hover:text-primary-fixed-dim transition-colors cursor-pointer active:opacity-80 transition-opacity" href="#">Dashboard</a>
<a class="text-primary dark:text-primary-fixed-dim border-b-2 border-primary dark:border-primary-fixed-dim h-full flex items-center px-1 hover:text-primary dark:hover:text-primary-fixed-dim transition-colors cursor-pointer active:opacity-80 transition-opacity" href="#">Occupancy</a>
<a class="text-secondary dark:text-secondary-fixed-dim h-full flex items-center px-1 hover:text-primary dark:hover:text-primary-fixed-dim transition-colors cursor-pointer active:opacity-80 transition-opacity" href="#">Billing</a>
<a class="text-secondary dark:text-secondary-fixed-dim h-full flex items-center px-1 hover:text-primary dark:hover:text-primary-fixed-dim transition-colors cursor-pointer active:opacity-80 transition-opacity" href="#">Reports</a>
<a class="text-secondary dark:text-secondary-fixed-dim h-full flex items-center px-1 hover:text-primary dark:hover:text-primary-fixed-dim transition-colors cursor-pointer active:opacity-80 transition-opacity" href="#">Staff</a>
<a class="text-secondary dark:text-secondary-fixed-dim h-full flex items-center px-1 hover:text-primary dark:hover:text-primary-fixed-dim transition-colors cursor-pointer active:opacity-80 transition-opacity" href="#">Settings</a>
</nav>
</div>
<div class="flex items-center gap-md">
<button class="hidden md:flex bg-primary-container text-on-primary font-label-md text-label-md px-4 py-2 rounded font-medium hover:bg-primary transition-colors cursor-pointer active:opacity-80 transition-opacity">New Entry</button>
<button class="text-secondary dark:text-secondary-fixed-dim hover:text-primary dark:hover:text-primary-fixed-dim transition-colors cursor-pointer active:opacity-80 transition-opacity p-2"><span class="material-symbols-outlined">notifications</span></button>
<button class="text-secondary dark:text-secondary-fixed-dim hover:text-primary dark:hover:text-primary-fixed-dim transition-colors cursor-pointer active:opacity-80 transition-opacity p-2"><span class="material-symbols-outlined">help_outline</span></button>
<div class="w-8 h-8 rounded-full bg-surface-variant overflow-hidden cursor-pointer active:opacity-80 transition-opacity border border-outline-variant ml-2">
<img alt="User profile" class="w-full h-full object-cover" data-alt="A professional headshot of a person, set against a clean, light grey studio background. The lighting is soft and even, typical of corporate portrait photography. The subject is well-lit, appearing approachable and professional, fitting the modern enterprise aesthetic." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAvcPqUaxaKRhFZum3ofB0qqYF7elISe9lv6HzTXl3M_sZr0DBHSV69LyImhVcf7kclhw4vbgdteV8BJdJCrhCyqWWYQj672Q54eH2-cITFqHA4D56OXnu_-z2JrGP8nsFTfCstS9lZXmS1r8sR9qgJl7Y0WaVUloJ7iE0NIXvptJ1lpPYcZ-9H3RIegM4yOT5Y3RjHdRL2s2Ws11HTeZv44T9N8VnMK0Gspcg9ZAv_EvRuNlSrm1Qs"/>
</div>
</div>
</div>
</header>
<main class="flex-grow w-full max-w-container-max mx-auto px-md md:px-lg py-xl flex flex-col gap-xl">
<!-- 1. Operation Status -->
<section aria-label="Alerts" id="alerts-section">
<div class="bg-surface-container-lowest border-l-4 border-primary-container p-md rounded-r-lg shadow-sm flex items-start gap-md">
<span class="material-symbols-outlined text-primary-container" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<div class="flex-grow">
<h3 class="font-label-md text-label-md text-on-surface font-semibold mb-1">Operation Successful</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Occupancy records synchronized with billing module.</p>
</div>
<button class="text-outline hover:text-on-surface"><span class="material-symbols-outlined text-sm">close</span></button>
</div>
</section>
<!-- 2. Overview Cards -->
<section aria-label="Key Metrics">
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-md">
<!-- Card 1 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-lg">
<div class="flex justify-between items-start mb-sm">
<span class="font-label-md text-label-md text-on-surface-variant">Occupancy Rate</span>
<span class="material-symbols-outlined text-outline">pie_chart</span>
</div>
<div class="font-headline-lg text-headline-lg text-on-surface mb-2">92.4%</div>
<div class="font-label-md text-label-md text-[#059669] flex items-center gap-1">
<span class="material-symbols-outlined text-[16px]">trending_up</span> +1.2% from last month
                    </div>
</div>
<!-- Card 2 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-lg">
<div class="flex justify-between items-start mb-sm">
<span class="font-label-md text-label-md text-on-surface-variant">Pending Billing</span>
<span class="material-symbols-outlined text-outline">receipt_long</span>
</div>
<div class="font-headline-lg text-headline-lg text-on-surface mb-2">$42,500</div>
<div class="font-label-md text-label-md text-error flex items-center gap-1">
                        14 units pending
                    </div>
</div>
<!-- Card 3 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-lg">
<div class="flex justify-between items-start mb-sm">
<span class="font-label-md text-label-md text-on-surface-variant">Total Units</span>
<span class="material-symbols-outlined text-outline">apartment</span>
</div>
<div class="font-headline-lg text-headline-lg text-on-surface mb-2">450</div>
<div class="font-label-md text-label-md text-on-surface-variant flex items-center gap-1">
                        Across 3 properties
                    </div>
</div>
<!-- Card 4 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-lg">
<div class="flex justify-between items-start mb-sm">
<span class="font-label-md text-label-md text-on-surface-variant">Active Tenants</span>
<span class="material-symbols-outlined text-outline">group</span>
</div>
<div class="font-headline-lg text-headline-lg text-on-surface mb-2">416</div>
<div class="font-label-md text-label-md text-on-surface-variant flex items-center gap-1">
                        8 new this week
                    </div>
</div>
<!-- Card 5 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-lg">
<div class="flex justify-between items-start mb-sm">
<span class="font-label-md text-label-md text-on-surface-variant">Maintenance Alerts</span>
<span class="material-symbols-outlined text-outline">build</span>
</div>
<div class="font-headline-lg text-headline-lg text-on-surface mb-2">7</div>
<div class="font-label-md text-label-md text-error flex items-center gap-1">
                        3 high priority
                    </div>
</div>
</div>
</section>
<!-- 3. Single Upsert & Tabs -->
<section aria-label="Data Entry" class="bg-surface-container-lowest border border-outline-variant rounded-lg overflow-hidden shadow-sm">
<div class="border-b border-outline-variant flex px-md bg-surface-container-low">
<button class="px-md py-md font-label-md text-label-md text-primary border-b-2 border-primary font-semibold">Single Upsert</button>
<button class="px-md py-md font-label-md text-label-md text-on-surface-variant hover:text-on-surface">Bulk Tools</button>
<button class="px-md py-md font-label-md text-label-md text-on-surface-variant hover:text-on-surface">History</button>
</div>
<div class="p-lg">
<h2 class="font-headline-md text-headline-md mb-lg text-on-surface">Add or Update Record</h2>
<form action="/occupancy/upsert" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-gutter" method="POST">
<!-- CSRF Placeholder -->
<input name="_token" type="hidden" value="csrf_placeholder"/>
<div class="flex flex-col gap-1">
<label class="font-label-md text-label-md text-on-surface-variant" for="tenant_name">Tenant Name</label>
<input class="h-10 px-3 border border-outline-variant rounded input-focus text-body-md" id="tenant_name" name="tenant_name" placeholder="John Doe" type="text"/>
</div>
<div class="flex flex-col gap-1">
<label class="font-label-md text-label-md text-on-surface-variant" for="unit_number">Unit Number</label>
<input class="h-10 px-3 border border-outline-variant rounded input-focus text-body-md" id="unit_number" name="unit_number" placeholder="A-101" type="text"/>
</div>
<div class="flex flex-col gap-1">
<label class="font-label-md text-label-md text-on-surface-variant" for="property_id">Property</label>
<select class="h-10 px-3 border border-outline-variant rounded input-focus text-body-md bg-white" id="property_id" name="property_id">
<option value="">Select Property...</option>
<option value="1">NodeSky Tower</option>
<option value="2">Skyline Heights</option>
</select>
</div>
<div class="flex flex-col gap-1">
<label class="font-label-md text-label-md text-on-surface-variant" for="occupancy_date">Occupancy Date</label>
<input class="h-10 px-3 border border-outline-variant rounded input-focus text-body-md text-on-surface-variant" id="occupancy_date" name="occupancy_date" type="date"/>
</div>
<div class="flex flex-col gap-1">
<label class="font-label-md text-label-md text-on-surface-variant" for="lease_end_date">Lease End Date</label>
<input class="h-10 px-3 border border-outline-variant rounded input-focus text-body-md text-on-surface-variant" id="lease_end_date" name="lease_end_date" type="date"/>
</div>
<div class="flex flex-col gap-1">
<label class="font-label-md text-label-md text-on-surface-variant" for="status">Status</label>
<select class="h-10 px-3 border border-outline-variant rounded input-focus text-body-md bg-white" id="status" name="status">
<option value="active">Active</option>
<option value="pending">Pending</option>
<option value="vacated">Vacated</option>
</select>
</div>
<div class="flex flex-col gap-1">
<label class="font-label-md text-label-md text-on-surface-variant" for="monthly_rent">Monthly Rent ($)</label>
<input class="h-10 px-3 border border-outline-variant rounded input-focus text-body-md" id="monthly_rent" name="monthly_rent" placeholder="1500.00" type="number"/>
</div>
<div class="flex flex-col gap-1">
<label class="font-label-md text-label-md text-on-surface-variant" for="billing_cycle">Billing Cycle</label>
<select class="h-10 px-3 border border-outline-variant rounded input-focus text-body-md bg-white" id="billing_cycle" name="billing_cycle">
<option value="monthly">Monthly</option>
<option value="quarterly">Quarterly</option>
<option value="annually">Annually</option>
</select>
</div>
<div class="flex flex-col gap-1">
<label class="font-label-md text-label-md text-on-surface-variant" for="deposit_amount">Security Deposit ($)</label>
<input class="h-10 px-3 border border-outline-variant rounded input-focus text-body-md" id="deposit_amount" name="deposit_amount" placeholder="1500.00" type="number"/>
</div>
<div class="flex flex-col gap-1">
<label class="font-label-md text-label-md text-on-surface-variant" for="contact_email">Contact Email</label>
<input class="h-10 px-3 border border-outline-variant rounded input-focus text-body-md" id="contact_email" name="contact_email" placeholder="tenant@example.com" type="email"/>
</div>
<div class="flex flex-col gap-1">
<label class="font-label-md text-label-md text-on-surface-variant" for="contact_phone">Contact Phone</label>
<input class="h-10 px-3 border border-outline-variant rounded input-focus text-body-md" id="contact_phone" name="contact_phone" placeholder="(555) 123-4567" type="tel"/>
</div>
<div class="flex flex-col gap-1">
<label class="font-label-md text-label-md text-on-surface-variant" for="emergency_contact">Emergency Contact</label>
<input class="bg-surface-container-low cursor-not-allowed" disabled="" id="emergency_contact" name="emergency_contact" placeholder="Jane Doe" type="text"/>
<span class="text-xs text-outline mt-1">Requires supervisor approval</span>
</div>
<div class="col-span-full flex justify-end gap-sm mt-md border-t border-outline-variant pt-md">
<button class="bg-surface-container-lowest text-secondary border border-outline-variant font-label-md text-label-md px-4 py-2 rounded hover:bg-surface-container-low transition-colors" type="button">Cancel</button>
<button class="bg-primary-container text-on-primary font-label-md text-label-md px-4 py-2 rounded hover:bg-primary transition-colors" type="submit">Save Record</button>
</div>
</form>
</div>
</section>
</main>
<!-- Footer -->
<footer class="bg-surface-container-low dark:bg-surface-container-highest w-full py-md border-t border-outline-variant dark:border-outline flat no shadows mt-auto">
<div class="flex flex-col md:flex-row justify-between items-center px-lg max-w-container-max mx-auto gap-md">
<span class="font-display text-label-md font-bold text-secondary">NodeSky Billing</span>
<span class="font-label-md text-label-md text-on-surface-variant dark:text-on-surface">© 2024 NodeSky Billing Housing &amp; Occupancy System</span>
<nav class="flex gap-md font-label-md text-label-md">
<a class="text-on-surface-variant hover:underline transition-all duration-200" href="#">Documentation</a>
<a class="text-on-surface-variant hover:underline transition-all duration-200" href="#">Support</a>
<a class="text-on-surface-variant hover:underline transition-all duration-200" href="#">Privacy Policy</a>
<a class="text-on-surface-variant hover:underline transition-all duration-200" href="#">System Status</a>
</nav>
</div>
</footer>
<div style="display:none" data-backend-contract="housing-occupancy"><form id="occUpsertForm"><input name="month_cycle"><select name="category"></select><input name="unit_id"><input name="room_no"><input name="employee_id"><input name="block_floor"><input name="active_days"></form><button id="loadOccBtn"></button><button id="downloadOccTemplate"></button><input id="occCsvFile" type="file"><button id="importOccCsv"></button><input id="autofillMonth"><button id="autofillBtn"></button><input id="occMonth"><input id="occUnit"><tbody id="occRows"></tbody><pre id="occResult"></pre><div id="occStatus"></div></div>
<script>const csrf=@json(csrf_token());const appBase=@json(url(''));function appUrl(p){return appBase+'/'+String(p).replace(/^\/+/, '')}</script></body></html>