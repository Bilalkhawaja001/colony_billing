<!DOCTYPE html>

<html class="light" lang="en"><head>
@include('partials.material-symbols-local')
<meta charset="utf-8">

<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Admin Users - Enterprise Dashboard</title>
<!-- Material Symbols -->
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Design System Configuration -->
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-primary-fixed-variant": "#0040a2",
                        "on-primary-container": "#c4d2ff",
                        "inverse-on-surface": "#edf0ff",
                        "error-container": "#ffdad6",
                        "outline": "#737685",
                        "on-error-container": "#93000a",
                        "on-tertiary": "#ffffff",
                        "surface": "#faf9ff",
                        "surface-container": "#e9edff",
                        "on-secondary": "#ffffff",
                        "surface-dim": "#ccdaff",
                        "on-secondary-fixed-variant": "#3b475b",
                        "error": "#ba1a1a",
                        "on-secondary-container": "#576377",
                        "tertiary-fixed-dim": "#ffb59b",
                        "outline-variant": "#c3c6d6",
                        "surface-variant": "#d8e2ff",
                        "primary": "#003d9b",
                        "on-tertiary-fixed-variant": "#812800",
                        "primary-fixed": "#dae2ff",
                        "inverse-surface": "#1d3054",
                        "on-primary-fixed": "#001848",
                        "secondary": "#535f73",
                        "on-tertiary-container": "#ffc6b2",
                        "surface-container-lowest": "#ffffff",
                        "on-background": "#051a3e",
                        "on-secondary-fixed": "#101c2d",
                        "primary-fixed-dim": "#b2c5ff",
                        "tertiary-fixed": "#ffdbcf",
                        "background": "#faf9ff",
                        "primary-container": "#0052cc",
                        "tertiary": "#7b2600",
                        "surface-tint": "#0c56d0",
                        "inverse-primary": "#b2c5ff",
                        "surface-container-highest": "#d8e2ff",
                        "surface-bright": "#faf9ff",
                        "on-surface-variant": "#434654",
                        "on-tertiary-fixed": "#380d00",
                        "secondary-fixed": "#d7e3fb",
                        "secondary-fixed-dim": "#bbc7de",
                        "secondary-container": "#d4e0f8",
                        "surface-container-high": "#e1e8ff",
                        "on-primary": "#ffffff",
                        "surface-container-low": "#f1f3ff",
                        "on-surface": "#051a3e",
                        "tertiary-container": "#a33500",
                        "on-error": "#ffffff"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "md": "16px",
                        "sm": "8px",
                        "xs": "4px",
                        "xl": "32px",
                        "gutter": "16px",
                        "base": "4px",
                        "container-max": "1440px",
                        "lg": "24px"
                    },
                    "fontFamily": {
                        "body-lg": ["Inter", "sans-serif"],
                        "headline-lg-mobile": ["Work Sans", "sans-serif"],
                        "code-sm": ["JetBrains Mono", "monospace"],
                        "headline-md": ["Work Sans", "sans-serif"],
                        "body-md": ["Inter", "sans-serif"],
                        "body-sm": ["Inter", "sans-serif"],
                        "headline-lg": ["Work Sans", "sans-serif"],
                        "label-md": ["Inter", "sans-serif"]
                    },
                    "fontSize": {
                        "body-lg": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "headline-lg-mobile": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                        "code-sm": ["12px", { "lineHeight": "16px", "fontWeight": "400" }],
                        "headline-md": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "body-md": ["14px", { "lineHeight": "20px", "fontWeight": "400" }],
                        "body-sm": ["12px", { "lineHeight": "18px", "fontWeight": "400" }],
                        "headline-lg": ["28px", { "lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "label-md": ["12px", { "lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600" }]
                    }
                }
            }
        }
    </script>
<style>
        /* Utility for hiding scrollbar but keeping functionality */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Font Variation Settings for Material Symbols */
        .material-symbols-outlined[data-weight="fill"] { font-variation-settings: 'FILL' 1; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md text-body-md antialiased min-h-screen flex flex-col relative overflow-x-hidden">
@include('partials.global-navbar')

<!-- Top Navigation Placeholder -->
    <nav class="bg-primary text-on-primary border-b border-outline-variant sticky top-0 z-40"><div class="max-w-[1440px] mx-auto px-md md:px-lg h-16 flex items-center justify-between"><a class="font-headline-md text-headline-md font-semibold" href="{{ url('dashboard') }}">NodeSky Billing</a><div class="hidden md:flex items-center gap-sm"><a class="px-sm py-xs rounded-lg hover:bg-primary-container" href="{{ url('dashboard') }}">Dashboard</a><a class="px-sm py-xs rounded-lg hover:bg-primary-container" href="{{ url('reporting') }}">Reports</a><a class="px-sm py-xs rounded-lg bg-primary-container text-on-primary-container" href="{{ url('ui/admin/users') }}">Admin Users</a></div><a class="text-body-sm" href="{{ url('logout') }}">Logout</a></div></nav>

    <!-- Global Toast Container (Fixed Top Right) -->
<div aria-live="assertive" class="fixed top-20 right-lg z-50 flex flex-col gap-sm pointer-events-none">
<!-- Success Toast Example (Hidden by default, shown via JS in real app) -->
<div class="pointer-events-auto flex items-start gap-md p-md bg-surface border border-outline-variant shadow-lg rounded-lg max-w-sm transform transition-all duration-300 opacity-0 translate-y-[-10px] hidden" id="toast-success">
<span class="material-symbols-outlined text-primary" data-weight="fill">check_circle</span>
<div class="flex-1">
<p class="font-label-md text-label-md text-on-surface">Success</p>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">User profile updated successfully.</p>
</div>
<button class="text-on-surface-variant hover:text-on-surface transition-colors" onclick="this.parentElement.classList.add('hidden')">
<span class="material-symbols-outlined">close</span>
</button>
</div>
<!-- Error Toast Example -->
<div class="pointer-events-auto flex items-start gap-md p-md bg-error-container border border-error shadow-lg rounded-lg max-w-sm transform transition-all duration-300 opacity-0 translate-y-[-10px] hidden" id="toast-error">
<span class="material-symbols-outlined text-on-error-container" data-weight="fill">error</span>
<div class="flex-1">
<p class="font-label-md text-label-md text-on-error-container">Action Failed</p>
<p class="font-body-sm text-body-sm text-on-error-container mt-1">Unable to delete administrator account.</p>
</div>
<button class="text-on-error-container hover:opacity-70 transition-colors" onclick="this.parentElement.classList.add('hidden')">
<span class="material-symbols-outlined">close</span>
</button>
</div>
</div>
<!-- Main Content Canvas -->
<main class="flex-1 w-full max-w-[1440px] mx-auto px-md md:px-lg py-lg md:py-xl flex flex-col gap-lg">
<!-- Header Section -->
<header class="flex flex-col md:flex-row md:items-center justify-between gap-md">
<div>
<nav aria-label="Breadcrumb" class="mb-sm hidden md:block">
<ol class="flex items-center gap-xs text-body-sm font-body-sm text-on-surface-variant">
<li><a class="hover:text-primary transition-colors" href="#">Dashboard</a></li>
<li><span class="material-symbols-outlined text-[16px]">chevron_right</span></li>
<li aria-current="page" class="text-on-surface">Users</li>
</ol>
</nav>
<h1 class="font-headline-lg-mobile text-headline-lg-mobile md:font-headline-lg md:text-headline-lg text-on-background">Admin Users</h1>
<p class="font-body-md text-body-md text-on-surface-variant mt-1">Manage user access, roles, and status across the organization.</p>
</div>
<button class="inline-flex items-center justify-center gap-sm bg-primary text-on-primary hover:bg-primary-container hover:text-on-primary-container px-md py-sm rounded-lg font-label-md text-label-md transition-all focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-background h-10 w-full md:w-auto shadow-sm hover:shadow-md" type="button">
<span class="material-symbols-outlined">person_add</span>
                Add New User
            </button>
</header>
<!-- Central Content Card -->
<section class="bg-surface border border-outline-variant rounded-xl flex flex-col overflow-hidden shadow-sm">
<!-- Contextual Tabs -->
<div class="border-b border-outline-variant px-md md:px-lg overflow-x-auto no-scrollbar bg-surface-container-lowest">
<nav aria-label="User Views" class="flex gap-lg">
<a aria-current="page" class="py-md font-label-md text-label-md text-primary border-b-2 border-primary whitespace-nowrap active" href="#">
                        All Users
                    </a>
<a class="py-md font-label-md text-label-md text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low px-sm -mx-sm rounded-t-sm transition-colors whitespace-nowrap" href="#">
                        Billing Team
                    </a>
<a class="py-md font-label-md text-label-md text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low px-sm -mx-sm rounded-t-sm transition-colors whitespace-nowrap" href="#">
                        Administrators
                    </a>
<a class="py-md font-label-md text-label-md text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low px-sm -mx-sm rounded-t-sm transition-colors whitespace-nowrap" href="#">
                        Office Staff
                    </a>
<a class="py-md font-label-md text-label-md text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low px-sm -mx-sm rounded-t-sm transition-colors whitespace-nowrap" href="#">
                        Pending Invites <span class="ml-xs bg-error text-on-error rounded-full px-2 py-0.5 text-[10px]">3</span>
</a>
</nav>
</div>
<!-- Toolbar / Filters -->
<form action="{{ url('ui/admin/users') }}" class="p-md md:p-lg flex flex-col lg:flex-row gap-md justify-between items-start lg:items-center bg-surface border-b border-outline-variant" method="GET">
<!-- Search -->
<div class="relative w-full lg:max-w-md group">
<span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors pointer-events-none">search</span>
<input class="w-full pl-10 pr-md py-sm h-10 bg-surface-container-lowest border border-outline-variant rounded-lg text-body-md font-body-md text-on-surface placeholder:text-outline focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all" id="search" name="search" placeholder="Search by name or email..." type="text"/>
</div>
<!-- Dropdown Filters -->
<div class="flex flex-col sm:flex-row gap-sm w-full lg:w-auto">
<div class="relative w-full sm:w-40">
<select aria-label="Filter by Role" class="w-full appearance-none bg-surface-container-lowest border border-outline-variant rounded-lg pl-md pr-10 py-sm h-10 text-body-md font-body-md text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all cursor-pointer" name="role">
<option value="">All Roles</option>
<option value="admin">Admin</option>
<option value="editor">Editor</option>
<option value="viewer">Viewer</option>
</select>
<span class="material-symbols-outlined absolute right-sm top-1/2 -translate-y-1/2 text-outline pointer-events-none">arrow_drop_down</span>
</div>
<div class="relative w-full sm:w-40">
<select aria-label="Filter by Status" class="w-full appearance-none bg-surface-container-lowest border border-outline-variant rounded-lg pl-md pr-10 py-sm h-10 text-body-md font-body-md text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all cursor-pointer" name="status">
<option value="">All Statuses</option>
<option value="active">Active</option>
<option value="pending">Pending</option>
<option value="inactive">Inactive</option>
</select>
<span class="material-symbols-outlined absolute right-sm top-1/2 -translate-y-1/2 text-outline pointer-events-none">arrow_drop_down</span>
</div>
<button class="inline-flex items-center justify-center h-10 px-md border border-outline-variant rounded-lg text-label-md font-label-md text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface transition-colors focus:outline-none focus:ring-2 focus:ring-primary w-full sm:w-auto hidden lg:inline-flex" type="button">
<span class="material-symbols-outlined mr-xs text-[18px]">filter_list</span>
                        More
                    </button>
</div>
</form>
<!-- Data Table Container -->
<div class="overflow-x-auto w-full relative">
<!-- Main Table (Populated State) -->
<table class="w-full text-left border-collapse min-w-[900px]" id="users-table">
<thead class="bg-surface-container-low border-b border-outline-variant">
<tr>
<th class="py-sm px-md font-label-md text-label-md text-on-surface-variant uppercase tracking-wider w-12 text-center" scope="col">
<input aria-label="Select all rows" class="rounded border-outline-variant text-primary focus:ring-primary h-4 w-4 bg-surface cursor-pointer" type="checkbox"/>
</th>
<th class="py-sm px-md font-label-md text-label-md text-on-surface-variant uppercase tracking-wider cursor-pointer hover:text-on-surface transition-colors group" scope="col">
<div class="flex items-center gap-xs">
                                    Name
                                    <span class="material-symbols-outlined text-[16px] opacity-0 group-hover:opacity-100 transition-opacity">arrow_downward</span>
</div>
</th>
<th class="py-sm px-md font-label-md text-label-md text-on-surface-variant uppercase tracking-wider" scope="col">Contact Info</th>
<th class="py-sm px-md font-label-md text-label-md text-on-surface-variant uppercase tracking-wider" scope="col">Role</th>
<th class="py-sm px-md font-label-md text-label-md text-on-surface-variant uppercase tracking-wider" scope="col">Status</th>
<th class="py-sm px-md font-label-md text-label-md text-on-surface-variant uppercase tracking-wider text-right" scope="col">Actions</th>
</tr>
</thead>
<tbody>@forelse(($users ?? collect()) as $u)<tr class="border-b border-outline-variant hover:bg-surface-container-low"><td class="py-sm px-md text-center"><input type="checkbox" class="rounded border-outline-variant"></td><td class="py-sm px-md">{{ $u->username ?? ($u->name ?? '') }}<div class="text-on-surface-variant text-body-sm">ID {{ $u->id ?? '' }}</div></td><td class="py-sm px-md">{{ $u->email ?? '' }}</td><td class="py-sm px-md">{{ $u->role ?? '' }}</td><td class="py-sm px-md">{{ (int) ($u->is_active ?? 0) === 1 ? 'Active' : 'Inactive' }}</td><td class="py-sm px-md text-right"><a href="#" class="text-primary">View</a></td></tr>@empty<tr><td colspan="6" class="p-6 text-center">No users found.</td></tr>@endforelse</tbody>
</table>
<!-- Loading State Skeleton (Hidden by default) -->
<div class="hidden w-full bg-surface" id="loading-skeleton">
<div class="animate-pulse flex flex-col divide-y divide-outline-variant">
<!-- Skeleton Row 1 -->
<div class="flex items-center gap-md py-md px-md">
<div class="h-4 w-4 bg-surface-variant rounded"></div>
<div class="flex items-center gap-sm flex-1 max-w-[250px]">
<div class="h-10 w-10 rounded-full bg-surface-variant shrink-0"></div>
<div class="space-y-2 w-full">
<div class="h-4 bg-surface-variant rounded w-3/4"></div>
<div class="h-3 bg-surface-variant rounded w-1/2"></div>
</div>
</div>
<div class="space-y-2 flex-1 max-w-[200px]">
<div class="h-4 bg-surface-variant rounded w-full"></div>
<div class="h-3 bg-surface-variant rounded w-2/3"></div>
</div>
<div class="h-4 bg-surface-variant rounded w-24 mx-md"></div>
<div class="h-6 bg-surface-variant rounded-md w-16 mx-md"></div>
<div class="flex-1 flex justify-end gap-2 pr-md">
<div class="h-8 w-8 bg-surface-variant rounded-md"></div>
<div class="h-8 w-8 bg-surface-variant rounded-md"></div>
</div>
</div>
<!-- Skeleton Row 2 -->
<div class="flex items-center gap-md py-md px-md">
<div class="h-4 w-4 bg-surface-variant rounded"></div>
<div class="flex items-center gap-sm flex-1 max-w-[250px]">
<div class="h-10 w-10 rounded-full bg-surface-variant shrink-0"></div>
<div class="space-y-2 w-full">
<div class="h-4 bg-surface-variant rounded w-2/3"></div>
<div class="h-3 bg-surface-variant rounded w-1/3"></div>
</div>
</div>
<div class="space-y-2 flex-1 max-w-[200px]">
<div class="h-4 bg-surface-variant rounded w-5/6"></div>
<div class="h-3 bg-surface-variant rounded w-1/2"></div>
</div>
<div class="h-4 bg-surface-variant rounded w-20 mx-md"></div>
<div class="h-6 bg-surface-variant rounded-md w-20 mx-md"></div>
<div class="flex-1 flex justify-end gap-2 pr-md">
<div class="h-8 w-8 bg-surface-variant rounded-md"></div>
<div class="h-8 w-8 bg-surface-variant rounded-md"></div>
</div>
</div>
</div>
</div>
<!-- Empty State (Hidden by default) -->
<div class="hidden flex flex-col items-center justify-center py-xl px-lg text-center bg-surface border-t border-outline-variant" id="empty-state">
<div class="w-48 h-48 mb-md bg-contain bg-center bg-no-repeat opacity-80" data-alt="A clean, minimalist vector illustration of an empty file cabinet or open folder floating in a bright, modern corporate setting. The style is flat and conceptual, using a precise palette of light grays and subtle primary blue accents to match the enterprise light-mode aesthetic. The mood is calm, organized, and devoid of clutter, indicating a clear, zero-inbox or 'no results found' state." style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuD8QQGk4uy7_gtbUJseLsu_Vyf-hi2GgYLOQr4IbYeaLJORI_YmBqZ9nxCi6N7UIYLOWGvNTAQK9HBFucOBA5i1sMgQRvv8bpCCNUu38WYvL2A3sWknNWhIvLMRfkwOzn8NSFUytDwNyqG4pZ4Swmuk2U5GPRs0VqKX63hzZ0FC2IyZckLw23OMBAz7_jAANlWAOs82wBmOPTN0laRqvhUQ441cLirJ7v5fkDu1Z4SaFsFLJ360puNj')"></div>
<h3 class="font-headline-md text-headline-md text-on-surface mb-sm">No users found</h3>
<p class="font-body-md text-body-md text-on-surface-variant max-w-sm mb-lg">We couldn't find any users matching your current filter criteria. Try adjusting your search or add a new user.</p>
<button class="inline-flex items-center justify-center gap-sm border border-outline-variant text-on-surface hover:bg-surface-container-low px-md py-sm rounded-lg font-label-md text-label-md transition-colors focus:outline-none focus:ring-2 focus:ring-primary" type="button">
                        Clear Filters
                    </button>
</div>
</div>
<!-- Pagination Footer -->
<div class="p-md md:p-lg border-t border-outline-variant bg-surface-container-lowest flex flex-col sm:flex-row items-center justify-between gap-md">
<p class="font-body-sm text-body-sm text-on-surface-variant">
                    Showing <span class="font-bold text-on-surface">1</span> to <span class="font-bold text-on-surface">4</span> of <span class="font-bold text-on-surface">128</span> users
                </p>
<nav aria-label="Pagination" class="flex items-center gap-xs">
<button class="p-sm inline-flex items-center justify-center border border-outline-variant rounded-md text-on-surface-variant opacity-50 cursor-not-allowed bg-surface" disabled="" type="button">
<span class="material-symbols-outlined text-[18px]">chevron_left</span>
<span class="sr-only">Previous Page</span>
</button>
<div class="hidden sm:flex items-center gap-xs">
<button aria-current="page" class="w-8 h-8 inline-flex items-center justify-center rounded-md bg-primary-container text-on-primary-container font-label-md text-label-md transition-colors focus:outline-none focus:ring-2 focus:ring-primary" type="button">1</button>
<button class="w-8 h-8 inline-flex items-center justify-center rounded-md border border-outline-variant text-on-surface hover:bg-surface-container-low font-label-md text-label-md transition-colors focus:outline-none focus:ring-2 focus:ring-primary" type="button">2</button>
<button class="w-8 h-8 inline-flex items-center justify-center rounded-md border border-outline-variant text-on-surface hover:bg-surface-container-low font-label-md text-label-md transition-colors focus:outline-none focus:ring-2 focus:ring-primary" type="button">3</button>
<span class="w-8 h-8 inline-flex items-center justify-center text-on-surface-variant font-body-md text-body-md">...</span>
<button class="w-8 h-8 inline-flex items-center justify-center rounded-md border border-outline-variant text-on-surface hover:bg-surface-container-low font-label-md text-label-md transition-colors focus:outline-none focus:ring-2 focus:ring-primary" type="button">12</button>
</div>
<button class="p-sm inline-flex items-center justify-center border border-outline-variant rounded-md text-on-surface hover:bg-surface-container-low transition-colors focus:outline-none focus:ring-2 focus:ring-primary bg-surface" type="button">
<span class="material-symbols-outlined text-[18px]">chevron_right</span>
<span class="sr-only">Next Page</span>
</button>
</nav>
</div>
</section>
</main>
<!-- Basic script to demonstrate toast toggle (for reviewer context) -->
<script>
        // Example: setTimeout to show a toast purely for visual validation of the layout if run.
        // In reality, Blade/Livewire would handle this state.
        /*
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const toast = document.getElementById('toast-success');
                if(toast) {
                    toast.classList.remove('hidden', 'opacity-0', 'translate-y-[-10px]');
                    toast.classList.add('opacity-100', 'translate-y-0');

                    setTimeout(() => {
                        toast.classList.add('opacity-0', 'translate-y-[-10px]');
                        setTimeout(() => toast.classList.add('hidden'), 300);
                    }, 4000);
                }
            }, 1000);
        });
        */
    </script>
</body></html>