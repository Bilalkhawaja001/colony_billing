<!DOCTYPE html>

<html class="light" lang="en"><head>
@include('partials.material-symbols-local')

<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Meter Management - UtilityFlow Enterprise</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;display=swap" rel="stylesheet"/>
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
                        "on-primary-fixed-variant": "#3f465c",
                        "on-primary-container": "#7c839b",
                        "error-container": "#ffdad6",
                        "inverse-on-surface": "#eaf1ff",
                        "on-error-container": "#93000a",
                        "outline": "#76777d",
                        "surface-container": "#e5eeff",
                        "on-secondary": "#ffffff",
                        "surface-dim": "#cbdbf5",
                        "surface": "#f8f9ff",
                        "on-tertiary": "#ffffff",
                        "tertiary-fixed-dim": "#c4c7c9",
                        "outline-variant": "#c6c6cd",
                        "on-secondary-fixed-variant": "#004c69",
                        "error": "#ba1a1a",
                        "on-secondary-container": "#004d6a",
                        "surface-variant": "#d3e4fe",
                        "on-tertiary-fixed-variant": "#444749",
                        "primary-fixed": "#dae2fd",
                        "primary": "#000000",
                        "inverse-surface": "#213145",
                        "secondary": "#00668a",
                        "on-tertiary-container": "#818486",
                        "on-primary-fixed": "#131b2e",
                        "primary-fixed-dim": "#bec6e0",
                        "surface-container-lowest": "#ffffff",
                        "on-background": "#0b1c30",
                        "on-secondary-fixed": "#001e2c",
                        "background": "#f8f9ff",
                        "primary-container": "#131b2e",
                        "tertiary": "#000000",
                        "tertiary-fixed": "#e0e3e5",
                        "surface-tint": "#565e74",
                        "inverse-primary": "#bec6e0",
                        "surface-container-highest": "#d3e4fe",
                        "surface-bright": "#f8f9ff",
                        "on-surface-variant": "#45464d",
                        "secondary-fixed": "#c4e7ff",
                        "secondary-fixed-dim": "#7bd0ff",
                        "on-tertiary-fixed": "#191c1e",
                        "on-error": "#ffffff",
                        "secondary-container": "#40c2fd",
                        "surface-container-high": "#dce9ff",
                        "on-primary": "#ffffff",
                        "surface-container-low": "#eff4ff",
                        "on-surface": "#0b1c30",
                        "tertiary-container": "#191c1e"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "gutter": "24px",
                        "container-max": "1440px",
                        "margin-desktop": "32px",
                        "unit": "4px",
                        "margin-mobile": "16px",
                        "stack-sm": "8px",
                        "stack-md": "16px",
                        "stack-lg": "24px"
                    },
                    "fontFamily": {
                        "body-lg": ["Inter"],
                        "headline-lg-mobile": ["Inter"],
                        "headline-md": ["Inter"],
                        "headline-sm": ["Inter"],
                        "body-md": ["Inter"],
                        "display-lg": ["Inter"],
                        "label-md": ["Inter"]
                    },
                    "fontSize": {
                        "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "headline-lg-mobile": ["28px", {"lineHeight": "36px", "fontWeight": "700"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                        "headline-sm": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "display-lg": ["36px", {"lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "label-md": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}]
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-background text-on-background font-body-lg min-h-screen flex flex-col">
@include('partials.global-navbar')

<!-- TopNavBar JSON Applied -->
<header class="bg-surface-container-lowest dark:bg-surface-container-lowest w-full top-0 border-b border-outline-variant dark:border-outline">
<div class="flex justify-between items-center w-full px-margin-desktop h-16 max-w-container-max mx-auto">
<!-- Brand -->
<div class="flex items-center gap-gutter">
<div class="text-headline-md font-headline-md font-bold text-primary dark:text-primary-fixed cursor-pointer active:opacity-80">
                    UtilityFlow Enterprise
                </div>
</div>
<!-- Navigation Links -->
<nav class="hidden md:flex items-center gap-stack-lg h-full">
<a class="h-full flex items-center text-secondary dark:text-secondary-fixed-dim border-b-2 border-secondary dark:border-secondary-fixed-dim pb-1 font-bold hover:bg-surface-container-low dark:hover:bg-surface-container-high transition-colors duration-200 cursor-pointer active:opacity-80 px-2" href="{{ url('/meters-readings/registry') }}">
                    Meter Registry
                </a>
<a class="h-full flex items-center text-on-surface-variant dark:text-on-surface-variant hover:text-primary dark:hover:text-primary-fixed hover:bg-surface-container-low dark:hover:bg-surface-container-high transition-colors duration-200 cursor-pointer active:opacity-80 px-2" href="{{ url('/meters-readings/readings') }}">
                    Readings
                </a>
<a class="h-full flex items-center text-on-surface-variant dark:text-on-surface-variant hover:text-primary dark:hover:text-primary-fixed hover:bg-surface-container-low dark:hover:bg-surface-container-high transition-colors duration-200 cursor-pointer active:opacity-80 px-2" href="{{ url('/meters-readings/water-tools') }}">
                    Water Tools
                </a>
</nav>
<!-- Trailing Actions -->
<div class="flex items-center gap-stack-md">
<button class="p-2 rounded-full hover:bg-surface-container-low dark:hover:bg-surface-container-high transition-colors duration-200 cursor-pointer active:opacity-80 text-on-surface-variant">
<span class="material-symbols-outlined" data-icon="notifications">notifications</span>
</button>
<button class="p-2 rounded-full hover:bg-surface-container-low dark:hover:bg-surface-container-high transition-colors duration-200 cursor-pointer active:opacity-80 text-on-surface-variant">
<span class="material-symbols-outlined" data-icon="settings">settings</span>
</button>
<div class="w-8 h-8 rounded-full bg-surface-variant overflow-hidden cursor-pointer">
<img alt="Administrator Profile" class="w-full h-full object-cover" data-alt="A small circular profile picture placeholder showing a generic silhouette or professional headshot for an administrator account. The lighting is clean and the background is a subtle grey. Corporate modern style." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBUgusEQB789SqtTvjUEmdvYuLoVymAwM4Pf3TwUihmiMq689rfG1i0V2o7ajCkb2ZoOGROccRLTtLvRoLc8DhBX9z0EPTGbDis-a9U0qnI8YeBduPZiSA0z8ZWYcSd17tkXyc8G-05QR5i_f0kgRvVKZFRTV190pUJadPYgkxU7tSHjC5AkoHCWImEGlBwW1PJdAWObJh9f3lYbL6Q0kKbCYGsB7UxqfDyLpyZ5ebtJrZP47OpL-NF"/>
</div>
</div>
</div>
</header>
<!-- Main Content Area -->
<main class="flex-grow px-margin-mobile md:px-margin-desktop py-stack-lg max-w-container-max mx-auto w-full flex flex-col gap-gutter">
<!-- Breadcrumbs & Header -->
<div class="flex flex-col gap-stack-sm mb-stack-md">
<nav class="flex items-center gap-2 text-body-md font-body-md text-on-surface-variant">
<a class="hover:text-secondary transition-colors" href="{{ url('/dashboard') }}">Dashboard</a>
<span class="material-symbols-outlined text-[16px]">chevron_right</span>
<span class="text-on-surface">Meter Management</span>
</nav>
<h1 class="text-display-lg font-display-lg text-on-surface">Utility Meter Management</h1>
</div>
<!-- Alert Banner (Laravel Blade Placeholder) -->
<!-- @if(session('success')) -->
<div class="hidden bg-surface-container-low border border-secondary text-on-surface px-stack-lg py-stack-md rounded-lg flex items-start gap-3">
<span class="material-symbols-outlined text-secondary mt-0.5">check_circle</span>
<div>
<h3 class="font-headline-sm text-headline-sm mb-1">Success</h3>
<p class="font-body-md text-body-md text-on-surface-variant">Operation completed successfully. (Blade placeholder)</p>
</div>
<button class="ml-auto text-on-surface-variant hover:text-on-surface"><span class="material-symbols-outlined">close</span></button>
</div>
<!-- @endif -->
<!-- Quick Stats Grid (Top Row) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
<!-- Stat Card 1 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-stack-lg flex flex-col gap-stack-sm hover:border-secondary transition-colors group cursor-default">
<div class="flex justify-between items-start">
<span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Active Meters</span>
<span class="material-symbols-outlined text-secondary bg-surface-container p-1.5 rounded-lg">speed</span>
</div>
<div class="flex items-baseline gap-2 mt-stack-sm">
<span class="text-display-lg font-display-lg text-on-surface">12,408</span>
<span class="font-body-md text-body-md text-secondary">+124 this month</span>
</div>
</div>
<!-- Stat Card 2 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-stack-lg flex flex-col gap-stack-sm hover:border-secondary transition-colors group cursor-default">
<div class="flex justify-between items-start">
<span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">Pending Readings</span>
<span class="material-symbols-outlined text-secondary bg-surface-container p-1.5 rounded-lg">assignment_late</span>
</div>
<div class="flex items-baseline gap-2 mt-stack-sm">
<span class="text-display-lg font-display-lg text-on-surface">342</span>
<span class="font-body-md text-body-md text-error bg-error-container px-2 py-0.5 rounded text-[12px] font-bold">Needs attention</span>
</div>
</div>
<!-- Stat Card 3 -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-stack-lg flex flex-col gap-stack-sm hover:border-secondary transition-colors group cursor-default">
<div class="flex justify-between items-start">
<span class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider">System Health</span>
<span class="material-symbols-outlined text-secondary bg-surface-container p-1.5 rounded-lg">health_and_safety</span>
</div>
<div class="flex items-baseline gap-2 mt-stack-sm">
<span class="text-display-lg font-display-lg text-on-surface">99.9%</span>
<span class="font-body-md text-body-md text-secondary bg-surface-container-low px-2 py-0.5 rounded text-[12px] font-bold">Optimal</span>
</div>
</div>
</div>
<!-- Section Cards Grid (Middle Row) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter mt-stack-md">
<!-- Meter Registry Card -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl flex flex-col overflow-hidden group hover:shadow-[0px_4px_12px_rgba(15,23,42,0.05)] transition-shadow">
<div class="p-stack-lg border-b border-outline-variant bg-surface-container-low flex items-center gap-3">
<span class="material-symbols-outlined text-secondary text-[28px]">inventory_2</span>
<h2 class="font-headline-md text-headline-md text-on-surface">Meter Registry</h2>
</div>
<div class="p-stack-lg flex-grow flex flex-col gap-stack-md">
<p class="font-body-lg text-body-lg text-on-surface-variant">
                        Comprehensive management of all physical utility endpoints. Track installation dates, maintenance cycles, and geolocation data for every meter in the network.
                    </p>
<div class="mt-auto pt-stack-md border-t border-outline-variant/50">
<button class="w-full bg-primary text-on-primary font-label-md text-label-md py-3 rounded-lg hover:bg-inverse-surface transition-colors focus:ring-2 focus:ring-secondary focus:ring-offset-2 focus:outline-none flex items-center justify-center gap-2">
                            Open Meter Registry <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
</button>
</div>
</div>
</div>
<!-- Readings Card -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl flex flex-col overflow-hidden group hover:shadow-[0px_4px_12px_rgba(15,23,42,0.05)] transition-shadow">
<div class="p-stack-lg border-b border-outline-variant bg-surface-container-low flex items-center gap-3">
<span class="material-symbols-outlined text-secondary text-[28px]">data_usage</span>
<h2 class="font-headline-md text-headline-md text-on-surface">Readings Console</h2>
</div>
<div class="p-stack-lg flex-grow flex flex-col gap-stack-md">
<p class="font-body-lg text-body-lg text-on-surface-variant">
                        Ingest, validate, and process consumption data. Access automated telemetry feeds or input manual readings. Includes anomaly detection and validation flags.
                    </p>
<div class="mt-auto pt-stack-md border-t border-outline-variant/50">
<button class="w-full bg-primary text-on-primary font-label-md text-label-md py-3 rounded-lg hover:bg-inverse-surface transition-colors focus:ring-2 focus:ring-secondary focus:ring-offset-2 focus:outline-none flex items-center justify-center gap-2">
                            Open Readings Console <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
</button>
</div>
</div>
</div>
<!-- Water Tools Card -->
<div class="bg-surface-container-lowest border border-outline-variant rounded-xl flex flex-col overflow-hidden group hover:shadow-[0px_4px_12px_rgba(15,23,42,0.05)] transition-shadow">
<div class="p-stack-lg border-b border-outline-variant bg-surface-container-low flex items-center gap-3">
<span class="material-symbols-outlined text-secondary text-[28px]">water_drop</span>
<h2 class="font-headline-md text-headline-md text-on-surface">Water Tools</h2>
</div>
<div class="p-stack-lg flex-grow flex flex-col gap-stack-md">
<p class="font-body-lg text-body-lg text-on-surface-variant">
                        Specialized calculators for complex water utility scenarios. Includes pressure loss estimation, leak detection algorithms, and seasonal variance modeling.
                    </p>
<div class="mt-auto pt-stack-md border-t border-outline-variant/50">
<button class="w-full bg-primary text-on-primary font-label-md text-label-md py-3 rounded-lg hover:bg-inverse-surface transition-colors focus:ring-2 focus:ring-secondary focus:ring-offset-2 focus:outline-none flex items-center justify-center gap-2">
                            Open Water Tools <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
</button>
</div>
</div>
</div>
</div>
</main>
<!-- Footer JSON Applied -->
<footer class="bg-surface dark:bg-surface-dim w-full py-stack-lg border-t border-outline-variant dark:border-outline mt-auto">
<div class="flex flex-col md:flex-row justify-between items-center px-margin-desktop w-full max-w-container-max mx-auto gap-stack-md">
<div class="font-label-md text-label-md font-bold text-on-surface">
                © 2024 UtilityFlow Systems. All rights reserved.
            </div>
<nav class="flex flex-wrap justify-center gap-stack-lg">
<a class="font-label-md text-label-md text-on-surface-variant dark:text-on-surface-variant hover:text-secondary dark:hover:text-secondary-fixed transition-colors" href="#">Support</a>
<a class="font-label-md text-label-md text-on-surface-variant dark:text-on-surface-variant hover:text-secondary dark:hover:text-secondary-fixed transition-colors" href="#">Privacy Policy</a>
<a class="font-label-md text-label-md text-on-surface-variant dark:text-on-surface-variant hover:text-secondary dark:hover:text-secondary-fixed transition-colors" href="#">Terms of Service</a>
<a class="font-label-md text-label-md text-on-surface-variant dark:text-on-surface-variant hover:text-secondary dark:hover:text-secondary-fixed transition-colors" href="#">API Documentation</a>
</nav>
</div>
</footer>
</body></html>