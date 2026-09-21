<!DOCTYPE html><html lang="en" style=""><head>
@include('partials.material-symbols-local')
<meta charset="utf-8"><meta content="width=device-width, initial-scale=1.0" name="viewport"><script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script><script id="tailwind-config">try{
  tailwind.config = {
    darkMode: "class",
    theme: {
      extend: {
        "colors": {
                "inverse-on-surface": "#eef0ff",
                "error-container": "#ffdad6",
                "outline": "#737686",
                "on-error-container": "#93000a",
                "on-primary-fixed-variant": "#003ea8",
                "on-primary-container": "#eeefff",
                "surface-variant": "#dae2fd",
                "primary": "#004ac6",
                "primary-fixed": "#dbe1ff",
                "on-tertiary-fixed-variant": "#38485d",
                "surface": "#faf8ff",
                "on-tertiary": "#ffffff",
                "surface-container": "#eaedff",
                "surface-dim": "#d2d9f4",
                "on-secondary": "#ffffff",
                "on-secondary-fixed-variant": "#43474b",
                "error": "#ba1a1a",
                "on-secondary-container": "#5e6367",
                "tertiary-fixed-dim": "#b7c8e1",
                "outline-variant": "#c3c6d7",
                "on-background": "#131b2e",
                "surface-container-lowest": "#ffffff",
                "on-secondary-fixed": "#171c1f",
                "primary-fixed-dim": "#b4c5ff",
                "tertiary-fixed": "#d3e4fe",
                "background": "#faf8ff",
                "primary-container": "#2563eb",
                "tertiary": "#46566c",
                "inverse-surface": "#283044",
                "on-primary-fixed": "#00174b",
                "secondary": "#5a5f62",
                "on-tertiary-container": "#e9f0ff",
                "on-tertiary-fixed": "#0b1c30",
                "secondary-fixed": "#dfe3e7",
                "secondary-fixed-dim": "#c3c7cb",
                "secondary-container": "#dce0e4",
                "surface-container-high": "#e2e7ff",
                "on-primary": "#ffffff",
                "surface-container-low": "#f2f3ff",
                "on-surface": "#131b2e",
                "tertiary-container": "#5e6e85",
                "on-error": "#ffffff",
                "surface-tint": "#0053db",
                "inverse-primary": "#b4c5ff",
                "surface-container-highest": "#dae2fd",
                "surface-bright": "#faf8ff",
                "on-surface-variant": "#434655"
        },
        "borderRadius": {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "full": "9999px"
        },
        "spacing": {
                "base": "4px",
                "lg": "24px",
                "xl": "32px",
                "gutter": "20px",
                "xs": "4px",
                "md": "16px",
                "sm": "8px",
                "margin": "40px",
                "stack-sm": "0.5rem",
                "stack-md": "1rem",
                "stack-xs": "0.25rem",
                "stack-lg": "1.5rem",
                "margin-page": "2rem",
                "container-max": "1440px"
        },
        "fontFamily": {
                "label-md": [
                        "Geist"
                ],
                "label-bold": [
                        "Geist"
                ],
                "display-lg": [
                        "Geist"
                ],
                "body-md": [
                        "Geist"
                ],
                "body-sm": [
                        "Geist"
                ],
                "headline-md": [
                        "Geist"
                ],
                "title-sm": [
                        "Geist"
                ],
                "headline-xl": ["Geist"],
                "body-lg": ["Geist"],
                "headline-sm": ["Geist"],
                "mono-data": ["Geist"]
        },
        "fontSize": {
                "label-md": [
                        "12px",
                        {
                                "lineHeight": "16px",
                                "fontWeight": "500"
                        }
                ],
                "label-bold": [
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
                                "fontWeight": "600"
                        }
                ],
                "body-md": [
                        "14px",
                        {
                                "lineHeight": "20px",
                                "fontWeight": "400"
                        }
                ],
                "body-sm": [
                        "13px",
                        {
                                "lineHeight": "18px",
                                "fontWeight": "400"
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
                "title-sm": [
                        "18px",
                        {
                                "lineHeight": "28px",
                                "fontWeight": "500"
                        }
                ],
                "headline-xl": ["30px", {"lineHeight": "38px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                "headline-sm": ["18px", {"lineHeight": "26px", "fontWeight": "600"}],
                "mono-data": ["13px", {"lineHeight": "18px", "fontWeight": "500"}]
        }
},
    },
  }
}catch(_e){}</script><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&amp;display=swap" data-snapdom="injected-import"></head><body class="bg-background text-on-surface font-body-md antialiased selection:bg-primary-fixed selection:text-on-primary-fixed min-h-screen flex flex-col">
@include('partials.global-navbar')

<!-- TopNavBar -->
<nav class="bg-primary dark:bg-primary fixed top-0 w-full z-50 border-b border-outline-variant flat no shadows">
<div class="flex items-center justify-between px-margin-page h-16 max-w-container-max mx-auto">
<div class="flex items-center gap-6">
<div class="flex items-center gap-3">
<img alt="NodeSky Billing Logo" class="h-8 w-8 rounded-DEFAULT" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBYTGgXayh_zKBFSSGCeaOXdLkGMWwZM0TG_gTjaHHo114RPtxUNc42fuPpROTRPpKXfFks4hsPtp54ko2BO4_iJon2qsFgaOB12HTujWq5TIzuXfLQiB2I10GJRHqtPIsCEKhGbdPniULs3C-SS-llKwIQnG0jLkfnxTukuCZLZV3DjCkGPZZvB8ePMxCSe3_2zcixvV27pZzsHzACLSScHHZ2GpRysXfb0fiDu15t_JH2Yfk5ogPRWQ">
<span class="font-headline-sm text-headline-sm font-bold text-on-primary">NodeSky Billing</span>
</div>
<div class="hidden md:flex gap-1 ml-4 h-16">
<a class="flex items-center px-3 h-full font-body-md text-body-md text-on-primary-container hover:text-on-primary transition-colors hover:bg-primary-container transition-all cursor-pointer active:opacity-80" href="#">Dashboard</a>
<a class="flex items-center px-3 h-full font-body-md text-body-md text-on-primary-container hover:text-on-primary transition-colors hover:bg-primary-container transition-all cursor-pointer active:opacity-80" href="#">Invoices</a>
<a class="flex items-center px-3 h-full font-body-md text-body-md text-on-primary border-b-2 border-inverse-primary pb-1 hover:bg-primary-container transition-all cursor-pointer active:opacity-80" href="#">Statements</a>
<a class="flex items-center px-3 h-full font-body-md text-body-md text-on-primary-container hover:text-on-primary transition-colors hover:bg-primary-container transition-all cursor-pointer active:opacity-80" href="#">Reports</a>
<a class="flex items-center px-3 h-full font-body-md text-body-md text-on-primary-container hover:text-on-primary transition-colors hover:bg-primary-container transition-all cursor-pointer active:opacity-80" href="#">Settings</a>
</div>
</div>
<div class="flex items-center gap-4">
<button class="text-on-primary-container hover:text-on-primary transition-colors cursor-pointer p-2 rounded-full hover:bg-primary-container flex items-center justify-center">
<span class="material-symbols-outlined" data-icon="notifications">notifications</span>
</button>
<button class="text-on-primary-container hover:text-on-primary transition-colors cursor-pointer p-2 rounded-full hover:bg-primary-container flex items-center justify-center">
<span class="material-symbols-outlined" data-icon="help">help</span>
</button>
<button class="flex items-center gap-2 pl-2 cursor-pointer hover:opacity-80 transition-opacity">
<img alt="Administrator profile picture" class="w-8 h-8 rounded-full object-cover border border-outline-variant" data-alt="Professional headshot of a corporate administrator in a modern office setting. Sharp focus, high contrast, clean white background, conveying authority and competence." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDvniKJkmVwRddl0vZ7nIN9r7dt-JI73m8FDjKkIHT0WUA2RE5MbgQ-CRo1PWPQFS3pRqTqsxA1f9l9wIyVcXgSEj4T1Yyt-yInkaaqjinv8d33B90BRP4wcMQWeGpe8EL3sEc6p531Ds8xVjdlmfIyKCqpCSpmE9kwM3JLx0X3PQhuG6Yx1wtXU-fmOBLHOFViCiPPLc8P3YwFrOg-HeBAk0O8hzScpJtJRoVHQZkE6jeXBsE9kxW1Yg">
</button>
</div>
</div>
</nav>
<!-- Main Content Canvas -->
<main class="flex-grow pt-24 pb-12 px-margin-page max-w-container-max mx-auto w-full">
<!-- Success Banner Example -->
<div class="bg-surface-container-low border border-outline-variant rounded-DEFAULT p-4 mb-6 flex items-start gap-3">
<span class="material-symbols-outlined text-[#166534]" data-icon="check_circle">check_circle</span>
<div>
<h4 class="font-label-md text-label-md text-on-surface">Statement Generated</h4>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">The requested employee statement has been successfully processed and is ready for review.</p>
</div>
<button class="ml-auto text-on-surface-variant hover:text-on-surface">
<span class="material-symbols-outlined text-[18px]" data-icon="close">close</span>
</button>
</div>
<!-- Page Header -->
<header class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
<div>
<h1 class="font-headline-xl text-headline-xl text-on-surface">Employee Bill Statement</h1>
<p class="font-body-md text-body-md text-on-surface-variant mt-2">View and export detailed financial statements for individual personnel.</p>
</div>
<div class="flex flex-wrap items-center gap-3">
<button class="bg-surface-container-lowest border border-outline-variant text-on-surface hover:bg-surface-container-low transition-colors px-4 py-2 rounded-DEFAULT font-label-md text-label-md flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]" data-icon="print">print</span>
                    Printable Page
                </button>
<button class="bg-surface-container-lowest border border-outline-variant text-on-surface hover:bg-surface-container-low transition-colors px-4 py-2 rounded-DEFAULT font-label-md text-label-md flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]" data-icon="csv">csv</span>
                    Download CSV
                </button>
<button class="bg-primary text-on-primary hover:bg-primary-container hover:text-on-primary-container transition-colors px-4 py-2 rounded-DEFAULT font-label-md text-label-md flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]" data-icon="picture_as_pdf">picture_as_pdf</span>
                    Download PDF
                </button>
</div>
</header>
<!-- Filter Form -->
<section class="bg-surface-container-lowest border border-outline-variant rounded-DEFAULT p-6 mb-8">
<form action="/statements/filter" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end" method="POST">
<!-- Laravel Blade CSRF Token Placeholder -->
<!-- @csrf -->
<div class="flex flex-col gap-1.5 lg:col-span-1">
<label class="font-label-md text-label-md text-on-surface" for="employee_id">Employee</label>
<select class="bg-surface-container-lowest border border-outline-variant text-on-surface text-body-sm rounded-DEFAULT focus:ring-2 focus:ring-primary focus:border-primary block w-full p-2.5" id="employee_id" name="employee_id">
<option value="">Select Employee...</option>
<option value="1">Sarah Jenkins (ENG-442)</option>
<option selected="" value="2">Marcus Chen (OPS-109)</option>
<option value="3">Elena Rodriguez (DES-991)</option>
</select>
</div>
<div class="flex flex-col gap-1.5 lg:col-span-1">
<label class="font-label-md text-label-md text-on-surface" for="date_from">Date From</label>
<input class="bg-surface-container-lowest border border-outline-variant text-on-surface text-body-sm rounded-DEFAULT focus:ring-2 focus:ring-primary focus:border-primary block w-full p-2.5" id="date_from" name="date_from" type="date" value="2023-10-01">
</div>
<div class="flex flex-col gap-1.5 lg:col-span-1">
<label class="font-label-md text-label-md text-on-surface" for="date_to">Date To</label>
<input class="bg-surface-container-lowest border border-outline-variant text-on-surface text-body-sm rounded-DEFAULT focus:ring-2 focus:ring-primary focus:border-primary block w-full p-2.5" id="date_to" name="date_to" type="date" value="2023-10-31">
</div>
<div class="flex flex-col gap-1.5 lg:col-span-1">
<label class="font-label-md text-label-md text-on-surface" for="status">Status</label>
<select class="bg-surface-container-lowest border border-outline-variant text-on-surface text-body-sm rounded-DEFAULT focus:ring-2 focus:ring-primary focus:border-primary block w-full p-2.5" id="status" name="status">
<option value="all">All Statuses</option>
<option selected="" value="paid">Paid</option>
<option value="pending">Pending</option>
</select>
</div>
<div class="lg:col-span-1">
<button class="w-full bg-primary text-on-primary hover:bg-primary-container hover:text-on-primary-container transition-colors p-2.5 rounded-DEFAULT font-label-md text-label-md flex items-center justify-center gap-2 h-[42px]" type="submit">
<span class="material-symbols-outlined text-[18px]" data-icon="search">search</span>
                        Apply Filters
                    </button>
</div>
</form>
</section>
<!-- Summary Cards Bento -->
<section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
<div class="bg-surface-container-lowest border border-outline-variant rounded-DEFAULT p-6 flex flex-col justify-between">
<div class="flex items-center gap-2 mb-4 text-on-surface-variant">
<span class="material-symbols-outlined text-[20px]" data-icon="payments">payments</span>
<h3 class="font-label-md text-label-md uppercase">Total Gross Earnings</h3>
</div>
<div>
<span class="font-mono-data text-headline-xl text-on-surface block">12,450.00</span>
<span class="font-body-sm text-body-sm text-on-surface-variant mt-1 block">Period: Oct 1 - Oct 31, 2023</span>
</div>
</div>
<div class="bg-surface-container-lowest border border-outline-variant rounded-DEFAULT p-6 flex flex-col justify-between">
<div class="flex items-center gap-2 mb-4 text-on-surface-variant">
<span class="material-symbols-outlined text-[20px]" data-icon="money_off">money_off</span>
<h3 class="font-label-md text-label-md uppercase">Total Deductions</h3>
</div>
<div>
<span class="font-mono-data text-headline-xl text-on-surface block">3,185.50</span>
<span class="font-body-sm text-body-sm text-on-surface-variant mt-1 block">Taxes &amp; Benefits combined</span>
</div>
</div>
<div class="bg-surface-container-lowest border border-primary-fixed-dim bg-surface-container-low rounded-DEFAULT p-6 flex flex-col justify-between">
<div class="flex items-center justify-between mb-4 text-on-surface">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[20px]" data-icon="account_balance">account_balance</span>
<h3 class="font-label-md text-label-md uppercase font-bold">Net Payout</h3>
</div>
<span class="bg-[#dcfce7] text-[#166534] px-2 py-0.5 rounded-sm font-label-md text-[10px] uppercase">Paid</span>
</div>
<div>
<span class="font-mono-data text-headline-xl text-primary block">9,264.50</span>
<span class="font-body-sm text-body-sm text-on-surface-variant mt-1 block">Direct Deposit ending in *4492</span>
</div>
</div>
</section>
<!-- Detailed Tables Layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
<!-- Earnings Table -->
<section class="bg-surface-container-lowest border border-outline-variant rounded-DEFAULT overflow-hidden flex flex-col">
<div class="p-4 border-b border-outline-variant bg-surface-bright flex justify-between items-center">
<h3 class="font-headline-sm text-headline-sm text-on-surface">Earnings Breakdown</h3>
<button class="text-on-surface-variant hover:text-on-surface transition-colors p-1" title="Export Earnings">
<span class="material-symbols-outlined text-[20px]" data-icon="download">download</span>
</button>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-primary text-on-primary">
<th class="p-3 font-label-md text-label-md font-semibold whitespace-nowrap">Description</th>
<th class="p-3 font-label-md text-label-md font-semibold text-right whitespace-nowrap">Hours/Qty</th>
<th class="p-3 font-label-md text-label-md font-semibold text-right whitespace-nowrap">Rate</th>
<th class="p-3 font-label-md text-label-md font-semibold text-right whitespace-nowrap">Amount</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant text-body-sm">
<tr class="hover:bg-surface-container-high transition-colors">
<td class="p-3 text-on-surface">Regular Salary</td>
<td class="p-3 font-mono-data text-on-surface-variant text-right">160.00</td>
<td class="p-3 font-mono-data text-on-surface-variant text-right">65.00</td>
<td class="p-3 font-mono-data text-on-surface text-right font-medium">10,400.00</td>
</tr>
<tr class="hover:bg-surface-container-high transition-colors">
<td class="p-3 text-on-surface">Overtime</td>
<td class="p-3 font-mono-data text-on-surface-variant text-right">10.00</td>
<td class="p-3 font-mono-data text-on-surface-variant text-right">97.50</td>
<td class="p-3 font-mono-data text-on-surface text-right font-medium">975.00</td>
</tr>
<tr class="hover:bg-surface-container-high transition-colors">
<td class="p-3 text-on-surface">Quarterly Bonus</td>
<td class="p-3 font-mono-data text-on-surface-variant text-right">1.00</td>
<td class="p-3 font-mono-data text-on-surface-variant text-right">-</td>
<td class="p-3 font-mono-data text-on-surface text-right font-medium">1,000.00</td>
</tr>
<tr class="hover:bg-surface-container-high transition-colors">
<td class="p-3 text-on-surface">Expense Reimbursement</td>
<td class="p-3 font-mono-data text-on-surface-variant text-right">-</td>
<td class="p-3 font-mono-data text-on-surface-variant text-right">-</td>
<td class="p-3 font-mono-data text-on-surface text-right font-medium">75.00</td>
</tr>
</tbody>
<tfoot>
<tr class="bg-surface-bright border-t-2 border-outline-variant">
<td class="p-3 font-label-md text-label-md text-right uppercase text-on-surface-variant" colspan="3">Total Gross</td>
<td class="p-3 font-mono-data text-body-md text-on-surface text-right font-bold">12,450.00</td>
</tr>
</tfoot>
</table>
</div>
</section>
<!-- Deductions Table -->
<section class="bg-surface-container-lowest border border-outline-variant rounded-DEFAULT overflow-hidden flex flex-col">
<div class="p-4 border-b border-outline-variant bg-surface-bright flex justify-between items-center">
<h3 class="font-headline-sm text-headline-sm text-on-surface">Deductions</h3>
<button class="text-on-surface-variant hover:text-on-surface transition-colors p-1" title="Export Deductions">
<span class="material-symbols-outlined text-[20px]" data-icon="download">download</span>
</button>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-primary text-on-primary">
<th class="p-3 font-label-md text-label-md font-semibold whitespace-nowrap">Description</th>
<th class="p-3 font-label-md text-label-md font-semibold text-right whitespace-nowrap">Type</th>
<th class="p-3 font-label-md text-label-md font-semibold text-right whitespace-nowrap">Amount</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant text-body-sm">
<tr class="hover:bg-surface-container-high transition-colors">
<td class="p-3 text-on-surface">Federal Income Tax</td>
<td class="p-3 text-on-surface-variant text-right">Statutory</td>
<td class="p-3 font-mono-data text-error text-right font-medium">-1,850.00</td>
</tr>
<tr class="hover:bg-surface-container-high transition-colors">
<td class="p-3 text-on-surface">State Income Tax (CA)</td>
<td class="p-3 text-on-surface-variant text-right">Statutory</td>
<td class="p-3 font-mono-data text-error text-right font-medium">-750.00</td>
</tr>
<tr class="hover:bg-surface-container-high transition-colors">
<td class="p-3 text-on-surface">Medicare &amp; Social Security</td>
<td class="p-3 text-on-surface-variant text-right">Statutory</td>
<td class="p-3 font-mono-data text-error text-right font-medium">-345.50</td>
</tr>
<tr class="hover:bg-surface-container-high transition-colors">
<td class="p-3 text-on-surface">401(k) Contribution</td>
<td class="p-3 text-on-surface-variant text-right">Pre-Tax Voluntary</td>
<td class="p-3 font-mono-data text-error text-right font-medium">-150.00</td>
</tr>
<tr class="hover:bg-surface-container-high transition-colors">
<td class="p-3 text-on-surface">Health Insurance Premium</td>
<td class="p-3 text-on-surface-variant text-right">Post-Tax Voluntary</td>
<td class="p-3 font-mono-data text-error text-right font-medium">-90.00</td>
</tr>
</tbody>
<tfoot>
<tr class="bg-surface-bright border-t-2 border-outline-variant">
<td class="p-3 font-label-md text-label-md text-right uppercase text-on-surface-variant" colspan="2">Total Deductions</td>
<td class="p-3 font-mono-data text-body-md text-error text-right font-bold">-3,185.50</td>
</tr>
</tfoot>
</table>
</div>
</section>
</div>
<!-- Full Width Transaction History Table -->
<section class="bg-surface-container-lowest border border-outline-variant rounded-DEFAULT overflow-hidden">
<div class="p-4 border-b border-outline-variant bg-surface-bright flex justify-between items-center">
<div>
<h3 class="font-headline-sm text-headline-sm text-on-surface">Recent Disbursements</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Transaction history for this statement period.</p>
</div>
<div class="flex items-center gap-2">
<button class="bg-surface-container-lowest border border-outline-variant text-on-surface hover:bg-surface-container-low transition-colors px-3 py-1.5 rounded-DEFAULT font-label-md text-label-md disabled:opacity-50 disabled:cursor-not-allowed" disabled="">
                        Previous
                    </button>
<button class="bg-surface-container-lowest border border-outline-variant text-on-surface hover:bg-surface-container-low transition-colors px-3 py-1.5 rounded-DEFAULT font-label-md text-label-md">
                        Next
                    </button>
</div>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-surface-bright text-on-surface-variant border-b border-outline-variant">
<th class="p-3 font-label-md text-label-md font-semibold whitespace-nowrap">Date</th>
<th class="p-3 font-label-md text-label-md font-semibold whitespace-nowrap">Transaction ID</th>
<th class="p-3 font-label-md text-label-md font-semibold whitespace-nowrap">Method</th>
<th class="p-3 font-label-md text-label-md font-semibold whitespace-nowrap">Status</th>
<th class="p-3 font-label-md text-label-md font-semibold text-right whitespace-nowrap">Amount</th>
<th class="p-3 font-label-md text-label-md font-semibold text-center whitespace-nowrap">Action</th>
</tr>
</thead>
<tbody class="divide-y divide-outline-variant text-body-sm">
<tr class="hover:bg-surface-container-high transition-colors">
<td class="p-3 text-on-surface">Oct 31, 2023</td>
<td class="p-3 font-mono-data text-on-surface-variant">TXN-9982-A</td>
<td class="p-3 text-on-surface">ACH Direct Deposit</td>
<td class="p-3">
<span class="bg-[#dcfce7] text-[#166534] px-2 py-0.5 rounded-sm font-label-md text-[10px] uppercase">Cleared</span>
</td>
<td class="p-3 font-mono-data text-on-surface text-right font-medium">9,264.50</td>
<td class="p-3 text-center">
<button class="text-on-surface-variant hover:text-primary transition-colors" title="View Receipt">
<span class="material-symbols-outlined text-[18px]" data-icon="receipt_long">receipt_long</span>
</button>
</td>
</tr>
<tr class="hover:bg-surface-container-high transition-colors">
<td class="p-3 text-on-surface">Oct 15, 2023</td>
<td class="p-3 font-mono-data text-on-surface-variant">TXN-8871-C</td>
<td class="p-3 text-on-surface">Expense Check</td>
<td class="p-3">
<span class="bg-[#fef3c7] text-[#92400e] px-2 py-0.5 rounded-sm font-label-md text-[10px] uppercase">Pending</span>
</td>
<td class="p-3 font-mono-data text-on-surface text-right font-medium">75.00</td>
<td class="p-3 text-center">
<button class="text-on-surface-variant hover:text-primary transition-colors" title="View Receipt">
<span class="material-symbols-outlined text-[18px]" data-icon="receipt_long">receipt_long</span>
</button>
</td>
</tr>
</tbody>
</table>
</div>
</section>
</main>
<!-- Footer -->
<footer class="bg-surface-container-low dark:bg-surface-container-lowest border-t border-outline-variant flat no shadows mt-auto w-full">
<div class="flex flex-col md:flex-row justify-between items-center px-margin-page py-stack-lg max-w-container-max mx-auto w-full">
<div class="font-label-md text-label-md font-bold text-on-surface mb-4 md:mb-0">
                © 2026 NodeSky Billing. All rights reserved.
            </div>
<div class="flex flex-wrap gap-4 font-body-sm text-body-sm text-on-surface-variant dark:text-on-surface-variant">
<a class="hover:text-primary hover:underline transition-all cursor-pointer" href="#">Privacy Policy</a>
</div></div></footer><div style="display:none" data-backend-contract="employee-statement"><form method="get" action="{{ url('reports/employee-statement') }}"><input name="from_month" value="{{ $fromMonth ?? request('from_month', request('month_cycle', '')) }}"><input name="to_month" value="{{ $toMonth ?? request('to_month', request('month_cycle', '')) }}"><input name="company_id" value="{{ $companyId ?? request('company_id', '') }}"><input name="unit_id" value="{{ $unitId ?? request('unit_id', '') }}"><input name="room_no" value="{{ $roomNo ?? request('room_no', '') }}"><input name="q" value="{{ $q ?? request('q', '') }}"><select name="status"><option value="">All</option></select></form><a href="{{ url('reports/employee-statement/print') }}?{{ http_build_query(request()->query()) }}">Printable Page</a><a href="{{ url('reports/employee-statement/export') }}?{{ http_build_query(request()->query()) }}">Download CSV</a><a href="{{ url('reports/employee-statement/export') }}?{{ http_build_query(array_merge(request()->query(), ['format'=>'pdf'])) }}">Download PDF</a></div></body></html>