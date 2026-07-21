<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Profile - NodeSky Billing</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "error": "#ba1a1a",
                      "surface-container-lowest": "#ffffff",
                      "surface-container-highest": "#e0e3e5",
                      "inverse-on-surface": "#eff1f3",
                      "error-container": "#ffdad6",
                      "surface-container-high": "#e6e8ea",
                      "secondary": "#00668a",
                      "primary-fixed-dim": "#bec6e0",
                      "on-primary": "#ffffff",
                      "on-surface": "#191c1e",
                      "on-surface-variant": "#45464d",
                      "background": "#f7f9fb",
                      "surface-container-low": "#f2f4f6",
                      "tertiary-fixed-dim": "#b7c8e1",
                      "on-tertiary-fixed-variant": "#38485d",
                      "secondary-fixed-dim": "#7bd0ff",
                      "surface-bright": "#f7f9fb",
                      "secondary-container": "#40c2fd",
                      "on-background": "#191c1e",
                      "inverse-surface": "#2d3133",
                      "surface-container": "#eceef0",
                      "outline": "#76777d",
                      "inverse-primary": "#bec6e0",
                      "on-secondary-container": "#004d6a",
                      "tertiary": "#000000",
                      "on-primary-fixed": "#131b2e",
                      "surface-tint": "#565e74",
                      "on-primary-container": "#7c839b",
                      "on-tertiary": "#ffffff",
                      "on-tertiary-container": "#75859d",
                      "on-error-container": "#93000a",
                      "surface": "#f7f9fb",
                      "surface-dim": "#d8dadc",
                      "on-secondary-fixed": "#001e2c",
                      "primary-container": "#131b2e",
                      "tertiary-container": "#0b1c30",
                      "primary-fixed": "#dae2fd",
                      "tertiary-fixed": "#d3e4fe",
                      "surface-variant": "#e0e3e5",
                      "on-error": "#ffffff",
                      "outline-variant": "#c6c6cd",
                      "secondary-fixed": "#c4e7ff",
                      "on-secondary": "#ffffff",
                      "on-secondary-fixed-variant": "#004c69",
                      "primary": "#000000",
                      "on-tertiary-fixed": "#0b1c30",
                      "on-primary-fixed-variant": "#3f465c"
              },
              "borderRadius": {
                      "DEFAULT": "0.125rem",
                      "lg": "0.25rem",
                      "xl": "0.5rem",
                      "full": "0.75rem"
              },
              "spacing": {
                      "container-padding": "24px",
                      "gutter": "16px",
                      "stack-sm": "8px",
                      "stack-md": "16px",
                      "stack-lg": "32px",
                      "unit": "4px"
              },
              "fontFamily": {
                      "body-lg": ["Inter"],
                      "body-md": ["Inter"],
                      "label-md": ["Inter"],
                      "display": ["Inter"],
                      "headline-md": ["Inter"],
                      "headline-lg": ["Inter"],
                      "body-sm": ["Inter"],
                      "mono-md": ["jetbrainsMono"]
              },
              "fontSize": {
                      "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                      "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                      "label-md": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}],
                      "display": ["36px", {"lineHeight": "44px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                      "headline-md": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                      "headline-lg": ["28px", {"lineHeight": "36px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                      "body-sm": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                      "mono-md": ["13px", {"lineHeight": "20px", "fontWeight": "400"}]
              }
      },
          },
        }
    </script>
<style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');

        .blade-alert {
            display: none; /* Placeholder for Laravel session alerts */
        }

        /* Custom form ring for focus state */
        .form-input:focus {
            --tw-ring-color: #00668a;
            border-color: #00668a;
        }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md min-h-screen flex flex-col">
@include('partials.global-navbar')

<!-- TopNavBar -->
<nav class="bg-surface-container-lowest border-b border-surface-container-highest shadow-sm docked full-width top-0 w-full z-50">
<div class="flex justify-between items-center w-full px-container-padding max-w-[1200px] mx-auto h-16">
<!-- Brand -->
<div class="flex items-center gap-stack-md">
<span class="font-headline-md text-headline-md font-bold text-primary">NodeSky Billing</span>
<!-- Desktop Nav Links -->
<div class="hidden md:flex items-center gap-stack-md ml-stack-lg h-full pt-[2px]">
<a class="text-on-surface-variant hover:text-secondary hover:bg-surface-container-low transition-all duration-200 px-3 py-2 rounded-md h-full flex items-center" href="#">Dashboard</a>
<a class="text-on-surface-variant hover:text-secondary hover:bg-surface-container-low transition-all duration-200 px-3 py-2 rounded-md h-full flex items-center" href="#">Invoices</a>
<a class="text-on-surface-variant hover:text-secondary hover:bg-surface-container-low transition-all duration-200 px-3 py-2 rounded-md h-full flex items-center" href="#">Customers</a>
<a class="text-primary border-b-2 border-secondary pb-1 font-bold h-full flex items-center mt-[14px]" href="#">Profile</a>
</div>
</div>
<!-- Actions -->
<div class="flex items-center gap-stack-md">
<!-- Search Placeholder (on_left in JSON) -->
<div class="hidden lg:flex relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 transform -translate-y-1/2 text-on-surface-variant pointer-events-none" style="font-size: 18px;">search</span>
<input class="pl-9 pr-4 py-1.5 bg-surface-container-low border border-surface-container-highest rounded-lg text-body-sm focus:outline-none focus:ring-2 focus:ring-secondary/50 focus:border-secondary transition-colors w-64" placeholder="Search..." type="text"/>
</div>
<button class="text-on-surface-variant hover:bg-surface-container-low transition-all duration-200 p-2 rounded-full hidden md:block">
<span class="font-body-sm text-body-sm">Support</span>
</button>
<button class="text-on-surface-variant hover:text-secondary hover:bg-surface-container-low transition-all duration-200 p-2 rounded-full active:opacity-80 transition-opacity">
<span class="material-symbols-outlined">notifications</span>
</button>
<button class="text-on-surface-variant hover:text-secondary hover:bg-surface-container-low transition-all duration-200 p-2 rounded-full active:opacity-80 transition-opacity">
<span class="material-symbols-outlined">settings</span>
</button>
<div class="h-8 w-8 rounded-full overflow-hidden border border-surface-container-highest ml-stack-sm cursor-pointer hover:opacity-80 transition-opacity">
<img alt="User account avatar" class="w-full h-full object-cover" data-alt="A professional headshot of a corporate administrator in a modern, well-lit office setting. The lighting is soft and neutral, emphasizing a professional corporate modern aesthetic. The individual appears confident and competent." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAjgN0_EBrPLqzSmL3xJGdPcv6Pj9F4oggZnYOLKcWHi9fHr6PXXuI7p0j7v7F4pnBe8VSmM2H4GUjeKTaFwuI-VkuDbLx_vUc-keakJ5d6Ug5qkwzNX8c_9cuJwynPJbK-gFttpAcSy4gdGBAevWB2BpYui_zycP-NyILf1UC0eScteCPVJiyWLfo6JXoeZkfEY6sbhZpzblV29q2C2eXtSdjmSZVeTqUYgmXiOJdwJZALLH48OdYo"/>
</div>
</div>
</div>
</nav>
<!-- TopAppBar -->
<header class="bg-background w-full">
<div class="flex flex-col w-full px-container-padding pt-stack-lg pb-stack-md max-w-[1200px] mx-auto">
<div class="flex justify-between items-center w-full">
<div class="flex items-center gap-stack-md">
<button class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-full transition-colors hidden md:block">
<span class="material-symbols-outlined text-primary">arrow_back</span>
</button>
<div>
<h1 class="font-headline-lg text-headline-lg font-bold text-primary">Profile Settings</h1>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Manage your account details and billing preferences.</p>
</div>
</div>
<div class="flex gap-stack-sm">
<!-- Actions from JSON injected style -->
<button class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg border border-surface-container-highest bg-surface-container-lowest transition-colors flex items-center justify-center">
<span class="material-symbols-outlined">filter_list</span>
</button>
<button class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg border border-surface-container-highest bg-surface-container-lowest transition-colors flex items-center justify-center">
<span class="material-symbols-outlined">download</span>
</button>
</div>
</div>
</div>
</header>
<!-- Main Content Canvas -->
<main class="flex-grow w-full px-container-padding pb-stack-lg max-w-[1200px] mx-auto flex flex-col gap-stack-lg">
<!-- Laravel Alert Placeholders -->
<div class="blade-alert bg-surface-container-lowest border border-[#10b981] rounded-lg p-stack-md flex items-center gap-stack-sm shadow-sm" style="display: flex;">
<span class="material-symbols-outlined text-[#10b981]">check_circle</span>
<div class="flex-grow">
<p class="font-body-md text-body-md text-on-surface font-semibold">Profile Updated</p>
<p class="font-body-sm text-body-sm text-on-surface-variant">Your account settings have been successfully saved.</p>
</div>
<button class="text-on-surface-variant hover:text-on-surface">
<span class="material-symbols-outlined">close</span>
</button>
</div>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-stack-lg items-start">
<!-- Left Column: Overview & Access -->
<div class="lg:col-span-4 flex flex-col gap-stack-lg">
<!-- Profile Overview Card -->
<div class="bg-surface-container-lowest border border-surface-variant rounded-xl shadow-sm p-stack-lg flex flex-col items-center text-center">
<div class="relative mb-stack-md">
<div class="w-24 h-24 rounded-full overflow-hidden border-4 border-surface-container-lowest shadow-sm">
<img class="w-full h-full object-cover" data-alt="A high-quality, professional avatar image showing a corporate executive in a crisp shirt. The background is a subtle, out-of-focus modern office, utilizing cool slate and white tones for a clean enterprise look." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAt34LkSnEjK4wqtLYoDEZeZvHZ3zr9Jt_eYaDPlOMc40ORtdyuyrE-nNozkvkvr0J_SubKLlBWLARrqdeT8sZmmCmbSkLiJvuTGqlMCIYYkalTUSyICL98SPeva64qPOc-Ol5qWbWieGkz6ynDqnarEH4U51XllxVqruPq_Pin1eLWTFGfxFLfongVML9hj1Q9swAAT9q67BrH6b1mUdIZSQAmgqBeyeF9gUEQIF6DT6CGwnMxvx5B"/>
</div>
<button class="absolute bottom-0 right-0 bg-surface-container-lowest border border-surface-variant p-1.5 rounded-full text-on-surface-variant hover:text-secondary shadow-sm transition-colors">
<span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
</button>
</div>
<h2 class="font-headline-md text-headline-md text-on-surface mb-1">Sarah Jenkins</h2>
<p class="font-body-sm text-body-sm text-on-surface-variant mb-stack-sm">Billing Administrator</p>
<div class="flex items-center gap-2 mb-stack-md">
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#f0fdf4] text-[#166534] border border-[#dcfce7]">
<span class="w-1.5 h-1.5 rounded-full bg-[#166534] mr-1.5"></span>
                            Active
                        </span>
</div>
<div class="w-full border-t border-surface-variant pt-stack-md mt-stack-sm flex flex-col gap-stack-sm text-left">
<div class="flex justify-between items-center">
<span class="font-label-md text-label-md text-on-surface-variant uppercase">Department</span>
<span class="font-body-sm text-body-sm text-on-surface font-medium">Finance &amp; Ops</span>
</div>
<div class="flex justify-between items-center">
<span class="font-label-md text-label-md text-on-surface-variant uppercase">Timezone</span>
<span class="font-body-sm text-body-sm text-on-surface font-medium">America/New_York</span>
</div>
</div>
</div>
<!-- Billing Access Level Card -->
<div class="bg-surface-container-lowest border border-surface-variant rounded-xl shadow-sm p-stack-lg">
<h3 class="font-headline-md text-headline-md text-on-surface mb-stack-md flex items-center gap-2">
<span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">shield_person</span>
                        Access Level
                    </h3>
<div class="bg-surface-container-low rounded-lg p-stack-md border border-surface-variant mb-stack-md">
<div class="flex items-start gap-stack-md">
<span class="material-symbols-outlined text-secondary mt-0.5">verified_user</span>
<div>
<p class="font-body-md text-body-md font-semibold text-on-surface">Full Administrator</p>
<p class="font-body-sm text-body-sm text-on-surface-variant mt-1">You have unrestricted access to all billing operations, invoice management, and user permissions.</p>
</div>
</div>
</div>
<ul class="flex flex-col gap-stack-sm">
<li class="flex items-center gap-stack-sm">
<span class="material-symbols-outlined text-[#10b981]" style="font-size: 18px;">check</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">View &amp; Edit Invoices</span>
</li>
<li class="flex items-center gap-stack-sm">
<span class="material-symbols-outlined text-[#10b981]" style="font-size: 18px;">check</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">Manage Payment Methods</span>
</li>
<li class="flex items-center gap-stack-sm">
<span class="material-symbols-outlined text-[#10b981]" style="font-size: 18px;">check</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">Configure Billing Settings</span>
</li>
</ul>
</div>
</div>
<!-- Right Column: Forms -->
<div class="lg:col-span-8 flex flex-col gap-stack-lg">
<!-- Account Settings Form -->
<div class="bg-surface-container-lowest border border-surface-variant rounded-xl shadow-sm overflow-hidden">
<div class="border-b border-surface-variant bg-surface-bright px-stack-lg py-stack-md">
<h3 class="font-headline-md text-headline-md text-on-surface">Account Information</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">Update your basic profile details.</p>
</div>
<form action="{{ url('ui/profile') }}" class="p-stack-lg flex flex-col gap-stack-md" method="POST">
<!-- CSRF Placeholder -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-stack-md">
<div class="flex flex-col gap-stack-sm">
<label class="font-label-md text-label-md text-on-surface-variant" for="first_name">First Name</label>
<input class="form-input bg-surface-container-lowest border border-outline-variant rounded-md px-3 py-2 text-body-md text-on-surface focus:outline-none transition-shadow shadow-sm" id="first_name" name="first_name" required="" type="text" value="Sarah"/>
</div>
<div class="flex flex-col gap-stack-sm">
<label class="font-label-md text-label-md text-on-surface-variant" for="last_name">Last Name</label>
<input class="form-input bg-surface-container-lowest border border-outline-variant rounded-md px-3 py-2 text-body-md text-on-surface focus:outline-none transition-shadow shadow-sm" id="last_name" name="last_name" required="" type="text" value="Jenkins"/>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-stack-md">
<div class="flex flex-col gap-stack-sm">
<label class="font-label-md text-label-md text-on-surface-variant" for="email">Email Address</label>
<input class="form-input bg-surface-container-lowest border border-outline-variant rounded-md px-3 py-2 text-body-md text-on-surface focus:outline-none transition-shadow shadow-sm" id="email" name="email" required="" type="email" value="sarah.jenkins@nodesky.internal"/>
</div>
<div class="flex flex-col gap-stack-sm">
<label class="font-label-md text-label-md text-on-surface-variant" for="phone">Phone Number</label>
<input class="form-input bg-surface-container-lowest border border-outline-variant rounded-md px-3 py-2 text-body-md text-on-surface focus:outline-none transition-shadow shadow-sm" id="phone" name="phone" type="tel" value="+1 (555) 019-2834"/>
</div>
</div>
<div class="flex justify-end mt-stack-sm pt-stack-md border-t border-surface-variant">
<button class="bg-secondary hover:bg-secondary-container text-on-primary font-label-md text-label-md py-2 px-4 rounded-lg transition-colors shadow-sm flex items-center gap-2" type="submit">
                                Save Changes
                            </button>
</div>
</form>
</div>
<!-- Security Section -->
<div class="bg-surface-container-lowest border border-surface-variant rounded-xl shadow-sm overflow-hidden">
<div class="border-b border-surface-variant bg-surface-bright px-stack-lg py-stack-md">
<h3 class="font-headline-md text-headline-md text-on-surface">Security</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">Ensure your account is using a long, random password to stay secure.</p>
</div>
<form action="/ui/profile/password" class="p-stack-lg flex flex-col gap-stack-md" method="POST">
<div class="flex flex-col gap-stack-sm max-w-md">
<label class="font-label-md text-label-md text-on-surface-variant" for="current_password">Current Password</label>
<input class="form-input bg-surface-container-lowest border border-outline-variant rounded-md px-3 py-2 text-body-md text-on-surface focus:outline-none transition-shadow shadow-sm" id="current_password" name="current_password" required="" type="password"/>
</div>
<div class="flex flex-col gap-stack-sm max-w-md">
<label class="font-label-md text-label-md text-on-surface-variant" for="password">New Password</label>
<input class="form-input bg-surface-container-lowest border border-outline-variant rounded-md px-3 py-2 text-body-md text-on-surface focus:outline-none transition-shadow shadow-sm" id="password" name="password" required="" type="password"/>
</div>
<div class="flex flex-col gap-stack-sm max-w-md">
<label class="font-label-md text-label-md text-on-surface-variant" for="password_confirmation">Confirm New Password</label>
<input class="form-input bg-surface-container-lowest border border-outline-variant rounded-md px-3 py-2 text-body-md text-on-surface focus:outline-none transition-shadow shadow-sm" id="password_confirmation" name="password_confirmation" required="" type="password"/>
</div>
<div class="flex justify-end mt-stack-sm pt-stack-md border-t border-surface-variant">
<button class="bg-surface-container-lowest border border-surface-variant hover:bg-surface-container-low text-on-surface font-label-md text-label-md py-2 px-4 rounded-lg transition-colors shadow-sm" type="submit">
                                Update Password
                            </button>
</div>
</form>
</div>
<!-- Notification Preferences -->
<div class="bg-surface-container-lowest border border-surface-variant rounded-xl shadow-sm overflow-hidden">
<div class="border-b border-surface-variant bg-surface-bright px-stack-lg py-stack-md">
<h3 class="font-headline-md text-headline-md text-on-surface">Notification Preferences</h3>
<p class="font-body-sm text-body-sm text-on-surface-variant">Manage how we contact you regarding billing events.</p>
</div>
<div class="p-stack-lg flex flex-col gap-stack-md">
<!-- Toggle Item -->
<div class="flex items-center justify-between py-stack-sm">
<div class="flex flex-col">
<span class="font-body-md text-body-md font-semibold text-on-surface">Email Alerts</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">Receive daily summaries and critical invoice alerts via email.</span>
</div>
<label class="relative inline-flex items-center cursor-pointer">
<input checked="" class="sr-only peer" type="checkbox" value=""/>
<div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-secondary"></div>
</label>
</div>
<!-- Toggle Item -->
<div class="flex items-center justify-between py-stack-sm border-t border-surface-container-high">
<div class="flex flex-col">
<span class="font-body-md text-body-md font-semibold text-on-surface">SMS Notifications</span>
<span class="font-body-sm text-body-sm text-on-surface-variant">Receive text messages for failed payments or urgent account issues.</span>
</div>
<label class="relative inline-flex items-center cursor-pointer">
<input class="sr-only peer" type="checkbox" value=""/>
<div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-secondary"></div>
</label>
</div>
</div>
</div>
</div>
</div>
</main>
<div style="display:none" data-backend-contract="profile"><form method="post" action="{{ url('api/profile/change-password') }}">@csrf<label>Current Password</label><input name="old_password" type="password" required><label>New Password</label><input name="new_password" type="password" required><button type="submit">Change Password</button></form></div></body></html>