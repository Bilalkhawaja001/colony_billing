<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Imports &amp; Validation | NodeSky Billing</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=JetBrains+Mono:wght@500&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                            "secondary-fixed": "#d5e3fd",
                            "background": "#f7f9fb",
                            "surface-container-high": "#e6e8ea",
                            "on-secondary-fixed": "#0d1c2f",
                            "primary": "#000000",
                            "inverse-surface": "#2d3133",
                            "primary-fixed-dim": "#bec6e0",
                            "on-surface": "#191c1e",
                            "on-secondary": "#ffffff",
                            "inverse-primary": "#bec6e0",
                            "tertiary-fixed-dim": "#66d8d2",
                            "on-tertiary-container": "#00938e",
                            "surface-bright": "#f7f9fb",
                            "secondary": "#515f74",
                            "surface-tint": "#565e74",
                            "on-primary-container": "#7c839b",
                            "tertiary-fixed": "#84f5ee",
                            "primary-container": "#131b2e",
                            "tertiary-container": "#00201e",
                            "outline-variant": "#c6c6cd",
                            "on-background": "#191c1e",
                            "inverse-on-surface": "#eff1f3",
                            "surface-container-highest": "#e0e3e5",
                            "surface-container": "#eceef0",
                            "primary-fixed": "#dae2fd",
                            "on-primary-fixed": "#131b2e",
                            "on-secondary-container": "#57657b",
                            "surface-container-lowest": "#ffffff",
                            "on-tertiary": "#ffffff",
                            "surface-container-low": "#f2f4f6",
                            "on-error": "#ffffff",
                            "secondary-container": "#d5e3fd",
                            "on-secondary-fixed-variant": "#3a485c",
                            "secondary-fixed-dim": "#b9c7e0",
                            "on-tertiary-fixed-variant": "#00504d",
                            "on-primary": "#ffffff",
                            "error-container": "#ffdad6",
                            "on-tertiary-fixed": "#00201e",
                            "surface-variant": "#e0e3e5",
                            "error": "#ba1a1a",
                            "tertiary": "#000000",
                            "outline": "#76777d",
                            "on-surface-variant": "#45464d",
                            "on-error-container": "#93000a",
                            "on-primary-fixed-variant": "#3f465c",
                            "surface": "#f7f9fb",
                            "surface-dim": "#d8dadc"
                    },
                    "borderRadius": {
                            "DEFAULT": "0.125rem",
                            "lg": "0.25rem",
                            "xl": "0.5rem",
                            "full": "0.75rem"
                    },
                    "spacing": {
                            "container-max": "1440px",
                            "gutter": "24px",
                            "stack-lg": "32px",
                            "stack-md": "16px",
                            "base": "4px",
                            "margin-desktop": "32px",
                            "margin-mobile": "16px",
                            "stack-sm": "8px"
                    },
                    "fontFamily": {
                            "body-md": ["Inter"],
                            "label-mono": ["JetBrains Mono"],
                            "headline-md": ["Inter"],
                            "headline-lg-mobile": ["Inter"],
                            "headline-lg": ["Inter"],
                            "body-lg": ["Inter"],
                            "body-sm": ["Inter"],
                            "headline-xl": ["Inter"]
                    },
                    "fontSize": {
                            "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                            "label-mono": ["12px", {"lineHeight": "16px", "letterSpacing": "0.02em", "fontWeight": "500"}],
                            "headline-md": ["18px", {"lineHeight": "28px", "fontWeight": "600"}],
                            "headline-lg-mobile": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                            "headline-lg": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                            "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                            "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                            "headline-xl": ["36px", {"lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700"}]
                    }
                }
            }
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md antialiased min-h-screen flex flex-col">
@include('partials.global-navbar')

<!-- TopNavBar -->
<header class="bg-surface-container-lowest text-primary font-body-md docked full-width top-0 border-b border-outline-variant flat no shadows">
<div class="flex justify-between items-center w-full px-margin-desktop h-16 max-w-container-max mx-auto">
<div class="flex items-center gap-gutter">
<span class="text-headline-md font-headline-md font-bold text-primary">NodeSky Billing</span>
<nav class="hidden md:flex items-center gap-stack-md h-full">
<a class="text-secondary hover:text-primary transition-colors duration-200 h-16 flex items-center px-2 cursor-pointer active:opacity-80" href="#">Dashboard</a>
<a class="text-secondary hover:text-primary transition-colors duration-200 h-16 flex items-center px-2 cursor-pointer active:opacity-80" href="#">Invoices</a>
<a class="text-secondary hover:text-primary transition-colors duration-200 h-16 flex items-center px-2 cursor-pointer active:opacity-80" href="#">Customers</a>
<a class="text-primary border-b-2 border-primary pb-1 h-16 flex items-center px-2 cursor-pointer active:opacity-80" href="#">Imports &amp; Validation</a>
</nav>
</div>
<div class="flex items-center gap-stack-md">
<span class="text-body-sm text-secondary cursor-pointer hover:bg-surface-container-low transition-colors duration-200 px-2 py-1 rounded">Help</span>
<button class="text-secondary hover:text-primary transition-colors cursor-pointer active:opacity-80">
<span class="material-symbols-outlined">notifications</span>
</button>
<button class="text-secondary hover:text-primary transition-colors cursor-pointer active:opacity-80">
<span class="material-symbols-outlined">settings</span>
</button>
<img alt="User Profile" class="w-8 h-8 rounded-full border border-outline-variant ml-2 object-cover" data-alt="A clean, minimalist abstract geometric pattern representing a generic user avatar. Professional blue and gray tones, flat vector style, perfectly circular composition on a stark white background." src="https://lh3.googleusercontent.com/aida-public/AB6AXuCs8CMUi5d6-xpJZdtdLs_l0IEG_PuPaA-36fJnrY8ilHQKgNR9U_7gVLG9pgy2v0bpMtmQMgkIBcNr74wibf3FwCQvIe2oEBT1PdvF6RNQlDsvKqLWKFfHbkV-JyPgBGTO7aboTkpl-tAqRNekQRKH2ryJn3_BS0Q_lHmDxeEQ2lICrSB1g4CAwkuzVd50jbctzInid9POGany06ylj_42aqezp3iWmEKBQW_HCXYKf3NbUiBx9qq5"/>
</div>
</div>
</header>
<!-- Main Content Canvas -->
<main class="flex-1 w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-stack-lg">
<!-- Page Header -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-stack-md mb-stack-lg">
<div>
<h1 class="font-headline-xl text-headline-xl text-primary mb-2">Data Imports &amp; Validation</h1>
<p class="font-body-lg text-body-lg text-secondary">Manage billing data ingestion, review staging batches, and confirm validation rules.</p>
</div>
<div class="flex items-center gap-stack-sm">
<button class="bg-surface-container-lowest text-secondary border border-outline-variant font-body-sm text-body-sm px-4 py-2 rounded flex items-center gap-2 hover:bg-surface-container-low transition-colors">
<span class="material-symbols-outlined text-base">download</span>
                    Export Log
                </button>
<button class="bg-primary text-on-primary font-body-sm text-body-sm px-4 py-2 rounded flex items-center gap-2 hover:opacity-90 transition-opacity">
<span class="material-symbols-outlined text-base">upload</span>
                    New Import
                </button>
</div>
</div>
<!-- Alert Banner (Error Example) -->
<div class="mb-stack-lg bg-surface-container-lowest border-l-4 border-error p-stack-md rounded shadow-sm flex items-start gap-3">
<span class="material-symbols-outlined text-error mt-0.5">error</span>
<div>
<h4 class="font-headline-md text-body-md text-on-surface mb-1 font-semibold">Validation Failure in Batch #8902</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant">32 records failed schema validation due to missing 'Tax_ID' fields. Action required before processing can continue.</p>
</div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
<!-- Left Column: Ingest Preview & Validation -->
<div class="lg:col-span-8 flex flex-col gap-gutter">
<!-- Section 1: Ingest Preview (Data Table) -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg shadow-sm overflow-hidden">
<div class="px-stack-md py-stack-md border-b border-outline-variant flex justify-between items-center bg-surface-bright">
<h2 class="font-headline-md text-headline-md text-primary">Ingest Preview</h2>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-secondary text-sm">search</span>
<input class="pl-9 pr-3 py-1.5 border border-outline-variant rounded font-body-sm text-body-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary w-64 bg-surface-container-lowest" placeholder="Search filenames..." type="text"/>
</div>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="border-b border-outline-variant bg-surface-container-low">
<th class="px-4 py-3 font-label-mono text-label-mono text-secondary uppercase tracking-wider w-12">
<input class="rounded border-outline-variant text-primary focus:ring-primary" type="checkbox"/>
</th>
<th class="px-4 py-3 font-label-mono text-label-mono text-secondary uppercase tracking-wider">Filename</th>
<th class="px-4 py-3 font-label-mono text-label-mono text-secondary uppercase tracking-wider">Ingest Date</th>
<th class="px-4 py-3 font-label-mono text-label-mono text-secondary uppercase tracking-wider text-right">Records</th>
<th class="px-4 py-3 font-label-mono text-label-mono text-secondary uppercase tracking-wider">Status</th>
<th class="px-4 py-3 font-label-mono text-label-mono text-secondary uppercase tracking-wider text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant font-body-sm text-body-sm bg-surface-container-lowest">
<tr class="hover:bg-surface-container-low transition-colors group">
<td class="px-4 py-3">
<input class="rounded border-outline-variant text-primary focus:ring-primary" type="checkbox"/>
</td>
<td class="px-4 py-3 font-medium text-primary">Q3_EU_Billing_Run.csv</td>
<td class="px-4 py-3 text-secondary">Oct 12, 14:30</td>
<td class="px-4 py-3 text-right tabular-nums">12,450</td>
<td class="px-4 py-3">
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-secondary-container text-on-secondary-container">
<span class="w-1.5 h-1.5 rounded-full bg-primary-container"></span>
                                            Pending Review
                                        </span>
</td>
<td class="px-4 py-3 text-right">
<button class="text-secondary hover:text-primary transition-colors p-1">
<span class="material-symbols-outlined text-[18px]">more_vert</span>
</button>
</td>
</tr>
<tr class="hover:bg-surface-container-low transition-colors group">
<td class="px-4 py-3">
<input class="rounded border-outline-variant text-primary focus:ring-primary" type="checkbox"/>
</td>
<td class="px-4 py-3 font-medium text-primary">US_Tax_Adjustments_Sep.xlsx</td>
<td class="px-4 py-3 text-secondary">Oct 12, 11:15</td>
<td class="px-4 py-3 text-right tabular-nums">3,201</td>
<td class="px-4 py-3">
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-error-container text-on-error-container">
<span class="w-1.5 h-1.5 rounded-full bg-error"></span>
                                            Errors Found
                                        </span>
</td>
<td class="px-4 py-3 text-right">
<button class="text-secondary hover:text-primary transition-colors p-1">
<span class="material-symbols-outlined text-[18px]">more_vert</span>
</button>
</td>
</tr>
<tr class="hover:bg-surface-container-low transition-colors group">
<td class="px-4 py-3">
<input class="rounded border-outline-variant text-primary focus:ring-primary" type="checkbox"/>
</td>
<td class="px-4 py-3 font-medium text-primary">APAC_Usage_Logs_W40.csv</td>
<td class="px-4 py-3 text-secondary">Oct 11, 09:00</td>
<td class="px-4 py-3 text-right tabular-nums">45,992</td>
<td class="px-4 py-3">
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-tertiary-fixed-dim text-on-tertiary-fixed-variant">
<span class="w-1.5 h-1.5 rounded-full bg-tertiary-container"></span>
                                            Validated
                                        </span>
</td>
<td class="px-4 py-3 text-right">
<button class="text-secondary hover:text-primary transition-colors p-1">
<span class="material-symbols-outlined text-[18px]">more_vert</span>
</button>
</td>
</tr>
</tbody>
</table>
</div>
<div class="px-stack-md py-3 border-t border-outline-variant bg-surface-bright flex items-center justify-between font-body-sm text-body-sm text-secondary">
<span>Showing 1 to 3 of 24 entries</span>
<div class="flex gap-2">
<button class="px-2 py-1 border border-outline-variant rounded hover:bg-surface-container-low disabled:opacity-50" disabled="">Prev</button>
<button class="px-2 py-1 border border-outline-variant rounded hover:bg-surface-container-low">Next</button>
</div>
</div>
</div>
<!-- Section 2: Mark Validated (Action Area) -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg shadow-sm p-stack-md">
<h3 class="font-headline-md text-headline-md text-primary mb-1">Batch Actions</h3>
<p class="font-body-sm text-body-sm text-secondary mb-stack-md">Apply validation rules or reject selected ingestion batches.</p>
<form action="/imports/validate" class="flex flex-col sm:flex-row gap-4 items-end" method="POST">
<input name="_token" type="hidden" value="csrf_placeholder_token"/>
<div class="flex-1 w-full">
<label class="block font-body-sm font-semibold text-primary mb-1">Validation Profile</label>
<select class="w-full border border-outline-variant rounded font-body-sm text-body-sm py-2 px-3 bg-surface-container-lowest focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary" name="profile">
<option>Standard Billing Ruleset</option>
<option>Strict Tax Compliance (EU)</option>
<option>Legacy System Migration</option>
</select>
</div>
<div class="flex gap-3 w-full sm:w-auto">
<button class="flex-1 sm:flex-none bg-surface-container-lowest text-error border border-error font-body-sm text-body-sm px-4 py-2 rounded hover:bg-error-container transition-colors" name="action" type="submit" value="reject">
                                Reject Selected
                            </button>
<button class="flex-1 sm:flex-none bg-primary text-on-primary font-body-sm text-body-sm px-6 py-2 rounded hover:opacity-90 transition-opacity" name="action" type="submit" value="validate">
                                Mark Validated
                            </button>
</div>
</form>
</div>
</div>
<!-- Right Column: Status Tracker & Settings -->
<div class="lg:col-span-4 flex flex-col gap-gutter">
<!-- Section 3: Import Status (Visual Tracker) -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-lg shadow-sm p-stack-md">
<div class="flex justify-between items-center mb-stack-md">
<h3 class="font-headline-md text-headline-md text-primary">System Activity</h3>
<span class="text-xs font-semibold text-tertiary-container bg-tertiary-fixed px-2 py-1 rounded">Live</span>
</div>
<div class="space-y-4">
<!-- Progress Item -->
<div class="relative pl-6 border-l-2 border-primary pb-4">
<span class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-primary ring-4 ring-surface-container-lowest flex items-center justify-center">
<span class="w-1.5 h-1.5 bg-on-primary rounded-full animate-pulse"></span>
</span>
<p class="font-body-sm text-body-sm font-semibold text-primary">Processing Q3_EU_Billing_Run.csv</p>
<p class="font-body-sm text-body-sm text-secondary mb-2">Validating schema and data types...</p>
<div class="w-full bg-surface-container-highest rounded-full h-1.5">
<div class="bg-primary h-1.5 rounded-full" style="width: 45%"></div>
</div>
<p class="text-xs text-right text-secondary mt-1">45%</p>
</div>
<!-- Completed Item -->
<div class="relative pl-6 border-l-2 border-outline-variant pb-4">
<span class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-tertiary-fixed ring-4 ring-surface-container-lowest flex items-center justify-center">
<span class="material-symbols-outlined text-[12px] text-tertiary-container">check</span>
</span>
<p class="font-body-sm text-body-sm font-semibold text-primary">APAC_Usage_Logs_W40.csv</p>
<p class="font-body-sm text-body-sm text-secondary">Validated successfully. 45,992 records.</p>
<p class="text-xs text-secondary mt-1">2 hours ago</p>
</div>
<!-- Failed Item -->
<div class="relative pl-6">
<span class="absolute -left-[9px] top-0 w-4 h-4 rounded-full bg-error ring-4 ring-surface-container-lowest flex items-center justify-center">
<span class="material-symbols-outlined text-[12px] text-on-error">close</span>
</span>
<p class="font-body-sm text-body-sm font-semibold text-error">US_Tax_Adjustments_Sep.xlsx</p>
<p class="font-body-sm text-body-sm text-secondary">Halted due to critical errors.</p>
<p class="text-xs text-secondary mt-1">4 hours ago</p>
</div>
</div>
</div>
<!-- Settings/Upload Form Widget -->
<div class="bg-surface-container-low border border-outline-variant rounded-lg shadow-sm p-stack-md">
<h3 class="font-headline-md text-headline-md text-primary mb-4">Quick Upload</h3>
<form action="/imports/upload" class="space-y-4" enctype="multipart/form-data" method="POST">
<input name="_token" type="hidden" value="csrf_placeholder_token"/>
<div class="border-2 border-dashed border-outline-variant rounded-lg p-6 flex flex-col items-center justify-center text-center bg-surface-container-lowest hover:bg-surface-bright transition-colors cursor-pointer">
<span class="material-symbols-outlined text-secondary text-3xl mb-2">cloud_upload</span>
<p class="font-body-sm text-body-sm text-primary font-semibold">Drag &amp; drop files here</p>
<p class="font-body-sm text-body-sm text-secondary text-xs mt-1">CSV, XLSX up to 50MB</p>
<input class="hidden" type="file"/>
</div>
<div class="flex items-center gap-2">
<input class="rounded border-outline-variant text-primary focus:ring-primary" id="auto_validate" name="auto_validate" type="checkbox"/>
<label class="font-body-sm text-body-sm text-secondary cursor-pointer" for="auto_validate">Run default validation rules immediately</label>
</div>
<button class="w-full bg-surface-container-lowest text-primary border border-outline-variant font-body-sm text-body-sm px-4 py-2 rounded hover:bg-surface-container-low transition-colors font-semibold" type="button">
                            Browse Files
                        </button>
</form>
</div>
</div>
</div>
</main>
<!-- Footer -->
<footer class="bg-surface-container-low text-on-surface-variant font-body-sm full-width bottom-0 border-t border-outline-variant flat no shadows mt-auto">
<div class="flex flex-col md:flex-row justify-between items-center py-stack-md px-margin-desktop w-full max-w-container-max mx-auto gap-4">
<span class="font-bold text-on-surface">© 2024 NodeSky Billing. All rights reserved.</span>
<div class="flex gap-stack-md">
<a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Security</a>
<a class="text-on-surface-variant hover:text-primary transition-colors" href="#">Terms of Service</a>
<a class="text-on-surface-variant hover:text-primary transition-colors" href="#">API Documentation</a>
</div>
</div>
</footer>
<div style="display:none" data-backend-contract="imports-validation"><form method="post" action="{{ url('imports/meter-register/ingest-preview') }}" enctype="multipart/form-data">@csrf<input name="month_cycle" value="{{ $monthCycle }}"><input type="file" name="file"></form><form method="post" action="{{ url('imports/mark-validated') }}">@csrf<input name="month_cycle" value="{{ $monthCycle }}"></form></div></body></html>