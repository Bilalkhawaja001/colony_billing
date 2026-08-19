<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Free Allowances' }} | Billing Management</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'surface-container-low': '#f5f3f4',
                        'on-secondary': '#ffffff',
                        'on-surface-variant': '#45474c',
                        'surface-container-high': '#eae7e9',
                        'surface-tint': '#545f73',
                        'secondary-fixed': '#dbe1ff',
                        'on-primary': '#ffffff',
                        'primary-container': '#1e293b',
                        'error': '#ba1a1a',
                        'on-surface': '#1b1b1d',
                        'surface-container-lowest': '#ffffff',
                        'secondary': '#0051d5',
                        'surface': '#fbf8fa',
                        'primary': '#091426',
                        'background': '#fbf8fa',
                        'primary-fixed': '#d8e3fb',
                        'outline': '#75777d',
                        'surface-variant': '#e4e2e3',
                        'outline-variant': '#c5c6cd',
                        'secondary-container': '#316bf3'
                    },
                    borderRadius: {
                        DEFAULT: '0.125rem',
                        lg: '0.25rem',
                        xl: '0.5rem',
                        full: '0.75rem'
                    },
                    spacing: {
                        'stack-lg': '24px',
                        'stack-md': '16px',
                        'stack-sm': '8px',
                        'margin-page': '32px',
                        'gutter': '24px',
                        'container-max': '1440px'
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        'headline-xl': ['Inter'],
                        'headline-lg': ['Inter'],
                        'body-lg': ['Inter'],
                        'label-md': ['Inter'],
                        'label-sm': ['Inter'],
                        'body-md': ['Inter']
                    },
                    fontSize: {
                        'headline-xl': ['30px', {lineHeight: '36px', letterSpacing: '-0.02em', fontWeight: '700'}],
                        'headline-lg': ['24px', {lineHeight: '32px', letterSpacing: '-0.01em', fontWeight: '600'}],
                        'body-lg': ['16px', {lineHeight: '24px', fontWeight: '400'}],
                        'label-md': ['13px', {lineHeight: '18px', letterSpacing: '0.05em', fontWeight: '600'}],
                        'label-sm': ['12px', {lineHeight: '16px', fontWeight: '500'}],
                        'body-md': ['14px', {lineHeight: '20px', fontWeight: '400'}]
                    }
                }
            }
        };
    </script>
    <style>
        html, body { min-height: 100%; }
        body { margin: 0; background: #f8fafc; color: #1b1b1d; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .material-symbols-outlined.filled { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans text-body-lg antialiased overflow-x-hidden min-h-screen">
<div style="max-width:1280px;margin:0 auto;padding:16px 24px 0">
@include('billing_control.components.wizard-banner', ['wzStep' => 'allowances'])
</div>
@include('partials.global-navbar')

<header class="w-full sticky top-0 z-40 bg-surface border-b border-outline-variant shadow-sm flex justify-between items-center h-16 px-margin-page">
    <div class="flex items-center gap-4">
        <span class="font-headline-lg text-headline-lg font-black text-primary">Billing Management</span>
    </div>
    <div class="flex items-center gap-stack-md">
        <form method="get" action="{{ route('billing.allowances') }}" class="relative hidden lg:block w-64">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
            <input name="q" value="{{ $q }}" class="w-full pl-10 pr-4 py-2 bg-surface-container-low border-none rounded-full font-label-md text-label-md text-primary focus:ring-2 focus:ring-secondary focus:ring-offset-2 transition-all outline-none" placeholder="Search..." type="search">
            @if($type !== '')<input type="hidden" name="type" value="{{ $type }}">@endif
        </form>
        <button type="button" class="text-on-surface-variant hover:text-secondary transition-all rounded-full p-2" aria-label="Notifications">
            <span class="material-symbols-outlined">notifications</span>
        </button>
        <button type="button" class="text-on-surface-variant hover:text-secondary transition-all rounded-full p-2" aria-label="Help">
            <span class="material-symbols-outlined">help_outline</span>
        </button>
    </div>
</header>

<main class="w-full p-margin-page max-w-container-max mx-auto">
    @if(session('status'))
        <div class="mb-stack-md bg-[#dcfce7] border-l-4 border-[#166534] p-4 rounded-r-lg shadow-sm flex items-center justify-between" id="session-message">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-[#166534]">check_circle</span>
                <p class="font-body-md text-body-md text-[#166534]">{{ session('status') }}</p>
            </div>
            <button type="button" class="text-[#166534] hover:opacity-80" onclick="document.getElementById('session-message').remove()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-stack-md bg-[#fff1f0] border-l-4 border-error p-4 rounded-r-lg shadow-sm">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined filled text-error">error</span>
                <div class="font-body-md text-body-md text-error">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-stack-lg gap-stack-md">
        <div>
            <h1 class="font-headline-xl text-headline-xl text-primary mb-1">Free Allowances</h1>
            <p class="font-body-md text-body-md text-on-surface-variant">Manage complimentary electric units for residential units.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            <form method="post" action="{{ route('billing.allowances.import.preview') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 bg-white border border-[#e2e8f0] rounded-lg p-2 shadow-sm">
                @csrf
                <label class="flex items-center gap-2 px-3 py-2 rounded-md bg-surface-container-low cursor-pointer font-label-md text-label-md text-primary hover:bg-surface-variant transition-colors">
                    <span class="material-symbols-outlined text-[18px]">upload_file</span>
                    <span id="roomCsvFilename">Upload Room Allowances CSV</span>
                    <input id="roomAllowanceCsvInput" name="room_allowance_csv" type="file" accept=".csv,text/csv" required class="sr-only" onchange="document.getElementById('roomCsvFilename').textContent = this.files[0] ? this.files[0].name : 'Upload Room Allowances CSV'">
                </label>
                <button type="submit" class="flex items-center justify-center gap-2 bg-[#0f766e] text-white px-4 py-2 rounded-lg font-label-md text-label-md hover:bg-[#115e59] transition-all h-[40px] shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">preview</span>
                    Preview
                </button>
            </form>
            <button type="button" onclick="openAllowanceModal()" class="flex items-center justify-center gap-2 bg-[#2563eb] text-white px-4 py-2 rounded-lg font-label-md text-label-md hover:bg-[#1d4ed8] transition-all h-[40px] shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Add Allowance
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-stack-lg">
        <div class="bg-white border border-[#e2e8f0] rounded-xl p-stack-md shadow-[0_1px_3px_rgba(0,0,0,0.05)] flex items-center gap-stack-md relative overflow-hidden hover:border-[#cbd5e1] transition-colors">
            <div class="absolute right-0 top-0 w-24 h-full bg-gradient-to-l from-primary-fixed to-transparent opacity-20"></div>
            <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-on-primary shrink-0 z-10">
                <span class="material-symbols-outlined">group</span>
            </div>
            <div class="z-10">
                <p class="font-label-sm text-label-sm text-on-surface-variant mb-1 uppercase tracking-wider">Total Occupancy</p>
                <p class="font-headline-lg text-headline-lg text-primary">{{ number_format($totalOccupancy) }}</p>
            </div>
        </div>
        <div class="bg-white border border-[#e2e8f0] rounded-xl p-stack-md shadow-[0_1px_3px_rgba(0,0,0,0.05)] flex items-center gap-stack-md relative overflow-hidden hover:border-[#cbd5e1] transition-colors">
            <div class="absolute right-0 top-0 w-24 h-full bg-gradient-to-l from-secondary-fixed to-transparent opacity-30"></div>
            <div class="w-12 h-12 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary shrink-0 z-10">
                <span class="material-symbols-outlined">flash_on</span>
            </div>
            <div class="z-10">
                <p class="font-label-sm text-label-sm text-on-surface-variant mb-1 uppercase tracking-wider">Total Units Allocated</p>
                <p class="font-headline-lg text-headline-lg text-primary">{{ rtrim(rtrim(number_format($totalAllocatedUnits, 4, '.', ','), '0'), '.') }} kWh</p>
            </div>
        </div>
    </div>



    @if($roomImportPreview)
        <section class="mb-stack-lg bg-white border border-[#e2e8f0] rounded-xl shadow-[0_1px_3px_rgba(0,0,0,0.05)] overflow-hidden">
            <div class="p-stack-md border-b border-[#e2e8f0] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <h2 class="font-headline-lg text-headline-lg text-primary">Room Allowance CSV Preview</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant">File: <span class="font-medium text-primary">{{ $roomImportPreview['filename'] }}</span></p>
                    <p class="font-label-sm text-label-sm text-on-surface-variant">Preview expires at {{ $roomImportPreview['expires_at'] }}. Commit inserts NEW rows only.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <form method="post" action="{{ route('billing.allowances.import.commit') }}">
                        @csrf
                        <input type="hidden" name="import_token" value="{{ $roomImportPreview['token'] }}">
                        <button type="submit" @disabled(($roomImportPreview['summary']['new'] ?? 0) < 1 || ($roomImportPreview['summary']['invalid'] ?? 0) > 0) class="w-full sm:w-auto px-4 py-2 rounded-lg font-label-md text-label-md text-white bg-[#16a34a] hover:bg-[#15803d] disabled:bg-slate-300 disabled:cursor-not-allowed shadow-sm">
                            Commit NEW Rows
                        </button>
                    </form>
                    <form method="post" action="{{ route('billing.allowances.import.cancel') }}">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-lg font-label-md text-label-md text-primary border border-[#cbd5e1] hover:bg-surface-container-low">
                            Cancel Preview
                        </button>
                    </form>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 p-stack-md border-b border-[#e2e8f0]">
                @foreach([
                    'Total data rows' => $roomImportPreview['summary']['total'] ?? 0,
                    'New' => $roomImportPreview['summary']['new'] ?? 0,
                    'Existing skipped' => $roomImportPreview['summary']['existing_skipped'] ?? 0,
                    'Conflict skipped' => $roomImportPreview['summary']['conflict_skipped'] ?? 0,
                    'Invalid' => $roomImportPreview['summary']['invalid'] ?? 0,
                ] as $label => $value)
                    <div class="rounded-lg border border-[#e2e8f0] p-3 bg-surface-container-low">
                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">{{ $label }}</p>
                        <p class="font-headline-md text-headline-md text-primary">{{ number_format($value) }}</p>
                    </div>
                @endforeach
            </div>
            <div class="overflow-x-auto max-h-[520px]">
                <table class="w-full text-left border-collapse min-w-[980px]">
                    <thead class="sticky top-0 bg-[#f8fafc] z-10">
                    <tr>
                        <th class="p-3 text-xs font-bold text-slate-500 uppercase">CSV Row</th>
                        <th class="p-3 text-xs font-bold text-slate-500 uppercase">Unit ID</th>
                        <th class="p-3 text-xs font-bold text-slate-500 uppercase">Room No</th>
                        <th class="p-3 text-xs font-bold text-slate-500 uppercase text-right">Allowance</th>
                        <th class="p-3 text-xs font-bold text-slate-500 uppercase">Status</th>
                        <th class="p-3 text-xs font-bold text-slate-500 uppercase">Result / Reason</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-[#edf2f7]">
                    @foreach($roomImportPreview['rows'] as $previewRow)
                        <tr>
                            <td class="p-3 font-mono text-xs text-slate-500">{{ $previewRow['csv_row'] }}</td>
                            <td class="p-3 font-body-md text-body-md text-primary">{{ $previewRow['unit_id'] }}</td>
                            <td class="p-3 font-body-md text-body-md text-primary">{{ $previewRow['room_no'] }}</td>
                            <td class="p-3 font-body-md text-body-md text-primary text-right">{{ $previewRow['room_free_allowance'] }}</td>
                            <td class="p-3 font-body-md text-body-md text-primary">{{ $previewRow['is_active'] }}</td>
                            <td class="p-3 font-body-md text-body-md">
                                <span class="font-bold {{ $previewRow['result'] === 'NEW' ? 'text-[#166534]' : ($previewRow['result'] === 'INVALID' ? 'text-error' : 'text-slate-600') }}">{{ $previewRow['result'] }}</span>
                                <span class="text-on-surface-variant">— {{ $previewRow['reason'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <form method="get" action="{{ route('billing.allowances') }}" class="bg-white border border-[#e2e8f0] rounded-t-xl p-stack-md shadow-[0_1px_3px_rgba(0,0,0,0.05)] flex flex-col sm:flex-row items-center justify-between gap-stack-md border-b-0">
        <div class="relative w-full sm:w-[320px]">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
            <input name="q" value="{{ $q }}" class="w-full pl-10 pr-4 py-2 bg-white border border-[#d1d5db] rounded-md font-body-md text-body-md text-primary focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] transition-all outline-none" placeholder="Search Unit ID, Room No, or Name..." type="search">
        </div>
        <div class="w-full sm:w-auto flex items-center gap-stack-sm">
            <div class="relative w-full sm:w-48">
                <select name="type" class="w-full appearance-none pl-4 pr-10 py-2 bg-white border border-[#d1d5db] rounded-md font-body-md text-body-md text-primary focus:ring-2 focus:ring-[#2563eb] focus:border-[#2563eb] outline-none cursor-pointer">
                    <option value="">Filter by Type</option>
                    @foreach($allowanceTypes as $allowanceType)
                        <option value="{{ $allowanceType }}" @selected($type === $allowanceType)>{{ ucfirst(strtolower($allowanceType)) }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none">expand_more</span>
            </div>
            <button type="submit" class="w-10 h-10 border border-[#d1d5db] rounded-md flex items-center justify-center text-on-surface-variant hover:bg-surface-container-low transition-colors shrink-0" title="Apply filters">
                <span class="material-symbols-outlined">filter_list</span>
            </button>
        </div>
    </form>

    <div class="bg-white border border-[#e2e8f0] rounded-b-xl shadow-[0_1px_3px_rgba(0,0,0,0.05)] overflow-x-auto">
        @if($rows->count())
            <table class="w-full text-left border-collapse min-w-[1120px]">
                <thead>
                <tr class="bg-[#f1f5f9] border-b border-[#e2e8f0]">
                    <th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider whitespace-nowrap">Unit ID</th>
                    <th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider whitespace-nowrap">Room No.</th>
                    <th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider whitespace-nowrap">Free Allowance (Unit)</th>
                    <th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider whitespace-nowrap">Unit Name</th>
                    <th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider whitespace-nowrap">Floor</th>
                    <th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider whitespace-nowrap">Type</th>
                    <th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider whitespace-nowrap text-center">Occupants</th>
                    <th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider whitespace-nowrap">Status</th>
                    <th class="py-3 px-4 font-label-md text-label-md text-on-surface-variant uppercase tracking-wider whitespace-nowrap text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="font-body-md text-body-md text-primary">
                @foreach($rows as $row)
                    @php
                        $inactive = !$row['is_active'];
                        $typeStyles = match($row['allowance_type']) {
                            'BACHELOR' => 'bg-[#e0e7ff] text-[#3730a3]',
                            'FAMILY' => 'bg-[#fef3c7] text-[#92400e]',
                            'SENIOR_STAFF' => 'bg-[#f3f4f6] text-[#4b5563]',
                            'COMMON' => 'bg-[#e5e7eb] text-[#1f2937]',
                            default => 'bg-[#fff4e5] text-[#b54708]'
                        };
                    @endphp
                    <tr class="border-b border-[#e2e8f0] hover:bg-slate-50 transition-colors h-[52px] {{ $inactive ? 'text-on-surface-variant' : '' }}">
                        <td class="py-2 px-4 whitespace-nowrap font-medium">{{ $row['unit_id'] }}</td>
                        <td class="py-2 px-4 whitespace-nowrap">{{ $row['room_no'] ?: '—' }}</td>
                        <td class="py-2 px-4 whitespace-nowrap"><span class="font-semibold {{ $inactive ? '' : 'text-[#2563eb]' }}">{{ rtrim(rtrim(number_format($row['free_electric'], 4, '.', ','), '0'), '.') }}</span> kWh</td>
                        <td class="py-2 px-4 whitespace-nowrap">{{ $row['unit_name'] ?: '—' }}</td>
                        <td class="py-2 px-4 whitespace-nowrap">{{ $row['floor'] ?: '—' }}</td>
                        <td class="py-2 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-semibold {{ $typeStyles }}">{{ $row['allowance_type'] === 'UNCLASSIFIED' ? 'Unclassified' : ucfirst(strtolower($row['allowance_type'])) }}</span>
                        </td>
                        <td class="py-2 px-4 whitespace-nowrap text-center text-on-surface-variant">{{ number_format($row['occupancy']) }}</td>
                        <td class="py-2 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium gap-1 {{ $row['is_active'] ? 'bg-[#dcfce7] text-[#166534]' : 'bg-[#f1f5f9] text-[#64748b]' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $row['is_active'] ? 'bg-[#166534]' : 'bg-[#64748b]' }}"></span>
                                {{ $row['is_active'] ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-2 px-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-3 {{ $inactive ? 'opacity-60 hover:opacity-100' : '' }}">
                                <button type="button" class="p-1 text-on-surface-variant hover:text-[#2563eb] transition-colors rounded" title="Edit" data-allowance="{{ base64_encode(json_encode($row)) }}" onclick="openAllowanceModal(JSON.parse(atob(this.dataset.allowance)))">
                                    <span class="material-symbols-outlined text-[20px]">edit</span>
                                </button>
                                <form method="post" action="{{ $row['source'] === 'room' ? route('billing.allowances.rooms.status', $row['id']) : route('billing.allowances.status', $row['id']) }}" onsubmit="return confirm('{{ $row['is_active'] ? 'Is allowance ko deactivate karna hai?' : 'Is allowance ko activate karna hai?' }}')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="relative inline-flex items-center w-9 h-5 rounded-full transition-colors {{ $row['is_active'] ? 'bg-[#2563eb]' : 'bg-gray-200' }}" aria-label="{{ $row['is_active'] ? 'Deactivate' : 'Activate' }} {{ $row['unit_id'] }}">
                                        <span class="absolute top-[2px] w-4 h-4 bg-white border border-gray-300 rounded-full transition-all {{ $row['is_active'] ? 'left-[18px]' : 'left-[2px]' }}"></span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="py-16 px-6 text-center text-on-surface-variant">No allowance records found.</div>
        @endif

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 border-t border-[#e2e8f0] bg-white">
            <p class="font-body-md text-body-md text-on-surface-variant">
                @if($rows->count()) Showing <span class="font-medium text-primary">{{ number_format($rows->firstItem()) }}</span> to <span class="font-medium text-primary">{{ number_format($rows->lastItem()) }}</span> of <span class="font-medium text-primary">{{ number_format($rows->total()) }}</span> results @else Showing 0 results @endif
            </p>
            @if($rows->hasPages())
                <div class="flex items-center border border-outline-variant rounded-lg overflow-hidden shadow-sm">
                    <a class="w-10 h-10 flex items-center justify-center border-r border-outline-variant hover:bg-surface-container-low {{ $rows->onFirstPage() ? 'pointer-events-none text-slate-300' : 'text-primary' }}" href="{{ $rows->previousPageUrl() ?: '#' }}"><span class="material-symbols-outlined text-[18px]">chevron_left</span></a>
                    @php($startPage = max(1, $rows->currentPage() - 2))
                    @php($endPage = min($rows->lastPage(), $rows->currentPage() + 2))
                    @foreach($rows->getUrlRange($startPage, $endPage) as $page => $url)
                        <a class="w-10 h-10 flex items-center justify-center border-r border-outline-variant font-label-md text-label-md {{ $page === $rows->currentPage() ? 'bg-surface-container-low text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}" href="{{ $url }}">{{ $page }}</a>
                    @endforeach
                    @if($endPage < $rows->lastPage())<span class="w-10 h-10 flex items-center justify-center border-r border-outline-variant text-on-surface-variant">…</span>@endif
                    <a class="w-10 h-10 flex items-center justify-center hover:bg-surface-container-low {{ $rows->hasMorePages() ? 'text-primary' : 'pointer-events-none text-slate-300' }}" href="{{ $rows->nextPageUrl() ?: '#' }}"><span class="material-symbols-outlined text-[18px]">chevron_right</span></a>
                </div>
            @endif
        </div>
    </div>
</main>

<div id="allowanceModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="allowanceModalTitle" onclick="if(event.target === this) closeAllowanceModal()">
    <div class="absolute inset-0 bg-[#545f73]/40 backdrop-blur-sm"></div>
    <div class="relative z-10 w-full max-w-2xl bg-white rounded-xl shadow-[0_10px_15px_-3px_rgba(0,0,0,0.1)] flex flex-col max-h-[calc(100vh-32px)] overflow-hidden border border-surface-variant">
        <div class="flex items-start justify-between px-6 py-5 border-b border-surface-variant bg-white shrink-0">
            <div>
                <h2 id="allowanceModalTitle" class="font-headline-lg text-headline-lg text-on-surface">Add New Allowance</h2>
                <p id="allowanceModalSubtitle" class="font-body-md text-body-md text-on-surface-variant mt-1">Create a new free electric allowance record for a residential unit.</p>
            </div>
            <button type="button" aria-label="Close" onclick="closeAllowanceModal()" class="text-on-surface-variant hover:text-on-surface transition-colors rounded-full p-1 hover:bg-surface-container-low">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="allowanceForm" method="post" action="{{ route('billing.allowances.store') }}" class="contents">
            @csrf
            <div id="methodField"></div>
            <input type="hidden" name="form_mode" id="form_mode" value="add">
            <input type="hidden" name="allowance_id" id="allowance_id" value="">
            <input type="hidden" name="record_source" id="record_source" value="unit">

            <div class="px-6 py-6 overflow-y-auto grow space-y-stack-lg">
                <section class="bg-surface-container-low p-5 rounded-lg border border-outline-variant/30">
                    <h3 class="font-label-md text-label-md text-on-surface-variant mb-4 uppercase">Unit Identification</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface" for="f_unit_id">Unit ID <span class="text-error">*</span></label>
                            <input id="f_unit_id" name="unit_id" maxlength="255" required class="w-full h-10 px-3 bg-white border {{ $errors->has('unit_id') ? 'border-2 border-error' : 'border-outline-variant' }} text-on-surface font-body-md text-body-md rounded-DEFAULT focus:ring-2 focus:ring-secondary focus:border-secondary focus:outline-none" placeholder="e.g. U-1023">
                            <p id="unitIdEditNote" class="hidden font-label-sm text-label-sm text-on-surface-variant">Unit ID is locked while editing.</p>
                            @error('unit_id')<p class="font-label-sm text-label-sm text-error flex items-center gap-1"><span class="material-symbols-outlined filled text-[14px]">error</span>{{ $message }}</p>@enderror
                        </div>
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface" for="f_unit_name">Unit Name <span class="text-on-surface-variant font-normal normal-case text-[11px]">(Optional)</span></label>
                            <input id="f_unit_name" name="unit_name" maxlength="255" class="w-full h-10 px-3 bg-white border border-outline-variant text-on-surface font-body-md text-body-md rounded-DEFAULT focus:ring-2 focus:ring-secondary focus:border-secondary focus:outline-none" placeholder="e.g. Main Residence">
                        </div>
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface" for="f_room_no">Room No. <span class="text-on-surface-variant font-normal normal-case text-[11px]">(Optional)</span></label>
                            <input id="f_room_no" name="room_no" maxlength="64" class="w-full h-10 px-3 bg-white border border-outline-variant text-on-surface font-body-md text-body-md rounded-DEFAULT focus:ring-2 focus:ring-secondary focus:border-secondary focus:outline-none" placeholder="e.g. 101">
                        </div>
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface" for="f_floor">Floor <span class="text-on-surface-variant font-normal normal-case text-[11px]">(Optional)</span></label>
                            <input id="f_floor" name="floor" maxlength="64" class="w-full h-10 px-3 bg-white border border-outline-variant text-on-surface font-body-md text-body-md rounded-DEFAULT focus:ring-2 focus:ring-secondary focus:border-secondary focus:outline-none" placeholder="e.g. Ground">
                        </div>
                    </div>
                </section>

                <section class="bg-surface-container-low p-5 rounded-lg border border-outline-variant/30">
                    <h3 class="font-label-md text-label-md text-on-surface-variant mb-4 uppercase">Allowance Configuration</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface" for="f_allowance_type">Allowance Type <span class="text-error">*</span></label>
                            <div class="relative">
                                <select id="f_allowance_type" name="allowance_type" required onchange="updateAllowanceMapping()" class="w-full h-10 pl-3 pr-10 appearance-none bg-white border border-outline-variant text-on-surface font-body-md text-body-md rounded-DEFAULT focus:ring-2 focus:ring-secondary focus:border-secondary focus:outline-none cursor-pointer">
                                    <option value="">Select classification...</option>
                                    @foreach($allowanceTypes as $allowanceType)<option value="{{ $allowanceType }}">{{ ucfirst(strtolower($allowanceType)) }}</option>@endforeach
                                </select>
                                <span class="material-symbols-outlined pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-on-surface-variant">expand_more</span>
                            </div>
                            <p class="font-label-sm text-label-sm text-on-surface-variant">Determines the tier classification for free quota allocation.</p>
                            <div id="mappingNote" class="hidden mt-2 p-2 bg-surface-variant rounded border border-outline-variant/50 items-start gap-2">
                                <span class="material-symbols-outlined text-[16px] text-secondary mt-0.5">info</span>
                                <div><span class="font-label-sm text-label-sm text-on-surface block">Residence Type</span><span id="mappingText" class="font-body-md text-on-surface-variant text-[12px]">Internal Mapping: --</span></div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface" for="f_free_electric">Free Allowance <span class="text-error">*</span></label>
                            <div class="relative">
                                <input id="f_free_electric" name="free_electric" type="number" min="0" max="9999999999.9999" step="0.0001" required class="w-full h-10 pl-3 pr-12 bg-white border border-outline-variant text-on-surface font-body-md text-body-md rounded-DEFAULT focus:ring-2 focus:ring-secondary focus:border-secondary focus:outline-none" placeholder="0">
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 font-body-md text-body-md text-on-surface-variant">kWh</span>
                            </div>
                            @error('free_electric')<p class="font-label-sm text-label-sm text-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-surface-variant bg-white shrink-0">
                <button type="button" onclick="closeAllowanceModal()" class="h-10 px-4 font-label-md text-label-md text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low rounded-DEFAULT transition-colors">Cancel</button>
                <button id="allowanceSubmitButton" type="submit" class="h-10 px-6 font-label-md text-label-md text-white bg-[#0051d5] hover:bg-[#0046b8] rounded-DEFAULT shadow-sm transition-colors focus:ring-2 focus:ring-secondary focus:ring-offset-2">Save Allowance</button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('allowanceModal');
    const form = document.getElementById('allowanceForm');
    const storeUrl = @json(route('billing.allowances.store'));
    const unitUpdateUrl = @json(route('billing.allowances.update', ['allowance' => '__ID__']));
    const roomUpdateUrl = @json(route('billing.allowances.rooms.update', ['roomAllowance' => '__ID__']));

    window.openAllowanceModal = function(row = null) {
        const isEdit = Boolean(row && row.id);
        const source = row && row.source === 'room' ? 'room' : 'unit';
        form.reset();
        form.action = storeUrl;
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('form_mode').value = isEdit ? 'edit' : 'add';
        document.getElementById('allowance_id').value = isEdit ? row.id : '';
        document.getElementById('record_source').value = source;
        document.getElementById('allowanceModalTitle').textContent = isEdit ? 'Edit Allowance' : 'Add New Allowance';
        document.getElementById('allowanceModalSubtitle').textContent = isEdit ? 'Update the selected free electric allowance record.' : 'Create a new free electric allowance record for a residential unit.';
        document.getElementById('allowanceSubmitButton').textContent = isEdit ? 'Save Changes' : 'Save Allowance';

        if (isEdit) {
            form.action = (source === 'room' ? roomUpdateUrl : unitUpdateUrl).replace('__ID__', encodeURIComponent(row.id));
            document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        }

        const unitInput = document.getElementById('f_unit_id');
        unitInput.readOnly = isEdit;
        document.getElementById('unitIdEditNote').classList.toggle('hidden', !isEdit);

        if (row) {
            unitInput.value = row.unit_id || '';
            document.getElementById('f_unit_name').value = row.unit_name || '';
            document.getElementById('f_room_no').value = row.room_no || '';
            document.getElementById('f_floor').value = row.floor || '';
            document.getElementById('f_allowance_type').value = row.allowance_type === 'UNCLASSIFIED' ? '' : (row.allowance_type || '');
            document.getElementById('f_free_electric').value = row.free_electric ?? '';
        } else {
            document.getElementById('f_free_electric').value = '0';
        }

        updateAllowanceMapping();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.documentElement.style.overflow = 'hidden';
        setTimeout(() => unitInput.focus(), 20);
    };

    window.closeAllowanceModal = function() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.documentElement.style.overflow = '';
    };

    window.updateAllowanceMapping = function() {
        const type = document.getElementById('f_allowance_type').value;
        const note = document.getElementById('mappingNote');
        if (!type) {
            note.classList.add('hidden');
            note.classList.remove('flex');
            return;
        }
        document.getElementById('mappingText').textContent = 'Internal Mapping: ' + (type === 'FAMILY' ? 'HOUSE' : 'ROOM');
        note.classList.remove('hidden');
        note.classList.add('flex');
    };

    document.addEventListener('keydown', event => { if (event.key === 'Escape') closeAllowanceModal(); });

    @if($errors->any())
    openAllowanceModal({
        id: @json(old('form_mode') === 'edit' ? old('allowance_id') : null),
        source: @json(old('record_source', 'unit')),
        unit_id: @json(old('unit_id')),
        unit_name: @json(old('unit_name')),
        room_no: @json(old('room_no')),
        floor: @json(old('floor')),
        allowance_type: @json(old('allowance_type')),
        free_electric: @json(old('free_electric'))
    });
    @endif
})();
</script>
</body>
</html>
