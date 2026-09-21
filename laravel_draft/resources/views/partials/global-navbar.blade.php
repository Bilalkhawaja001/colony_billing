@php
    $navGroups = [
        ['key'=>'dashboard','label'=>'Dashboard','href'=>url('dashboard'),'patterns'=>['dashboard','dashboard-v2','/'],'children'=>[]],
        ['key'=>'billing','label'=>'Billing','patterns'=>['control-room*','allowances*','meters-readings*','rates*','active-days-monthly*','control-room/wizard*','control-room/readiness*'], 'children'=>[
            ['label'=>'Bill Wizard','href'=>url('control-room/wizard'),'patterns'=>['control-room/wizard*']],
            ['label'=>'Check & Fix Data','href'=>url('control-room/readiness'),'patterns'=>['control-room/readiness*']],
            ['label'=>'Bill Generation','href'=>url('control-room'),'patterns'=>['control-room','control-room/generate*','control-room/runs*']],
            ['label'=>'Free Allowances','href'=>url('allowances'),'patterns'=>['allowances*']],
            ['label'=>'Meter Readings','href'=>url('meters-readings'),'patterns'=>['meters-readings*','control-room/readings*']],
            ['label'=>'Rates & Charges','href'=>url('rates'),'patterns'=>['rates*','monthly-rates*']],
            ['label'=>'Attendance Days','href'=>url('active-days-monthly'),'patterns'=>['active-days-monthly*']],
        ]],
        ['key'=>'residents','label'=>'Residents','patterns'=>['people-residency*','family-list*','housing-occupancy*','unit-directory*'], 'children'=>[
            ['label'=>'Employees','href'=>url('people-residency'),'patterns'=>['people-residency*']],
            ['label'=>'Families','href'=>url('family-list'),'patterns'=>['family-list*']],
            ['label'=>'Unit Directory','href'=>url('unit-directory'),'patterns'=>['unit-directory*']],
        ]],
        ['key'=>'reports','label'=>'Reports','patterns'=>['reporting*','reports*','control-room/export*'], 'children'=>[
            ['label'=>'Employee Statement','href'=>url('reports/employee-statement'),'patterns'=>['reports/employee-statement*']],
            ['label'=>'Monthly Summary','href'=>url('reports/monthly-summary'),'patterns'=>['reports/monthly-summary*']],
            ['label'=>'Recovery Report','href'=>url('reports/recovery'),'patterns'=>['reports/recovery*']],
            ['label'=>'Reconciliation','href'=>url('reports/reconciliation'),'patterns'=>['reports/reconciliation*']],
            ['label'=>'Downloads','href'=>url('control-room/export'),'patterns'=>['control-room/export*']],
        ]],
        ['key'=>'transport','label'=>'Transport','patterns'=>['transport*'], 'children'=>[
            ['label'=>'Van Service','href'=>url('transport'),'patterns'=>['transport*','reports/van*']],
        ]],
        ['key'=>'settings','label'=>'Settings','patterns'=>['ui/admin/users*','month-lifecycle*','imports-validation*'], 'children'=>[
            ['label'=>'Users','href'=>url('ui/admin/users'),'patterns'=>['ui/admin/users*']],
            ['label'=>'Colony Setup','href'=>url('month-lifecycle'),'patterns'=>['month-lifecycle*']],
            ['label'=>'Import Data','href'=>url('imports-validation'),'patterns'=>['imports-validation*']],
        ]],
    ];
    $isActive = function(array $patterns): bool {
        foreach ($patterns as $pattern) {
            if ($pattern === '/' && request()->path() === '/') return true;
            if (request()->is($pattern)) return true;
        }
        return false;
    };
@endphp
<style>
{!! file_get_contents(public_path('css/fonts-local.css')) !!}
</style>
<style>
{!! file_get_contents(public_path('css/global-navbar.css')) !!}
</style>
<nav class="ns-global-navbar" data-global-navbar="1" aria-label="Global navigation">
    <div class="ns-nav-inner">
        <div class="ns-nav-left">
            <a class="ns-brand" href="{{ url('dashboard') }}" aria-label="NodeSky Billing Dashboard">
                <img class="ns-brand-logo" src="/assets/images/nodesky-logo.webp" alt="NodeSky">
                <span class="ns-brand-text">Billing</span>
            </a>
            <div class="ns-desktop-menu" role="menubar" aria-label="Primary navigation">
                @foreach($navGroups as $group)
                    @php $groupActive = $isActive($group['patterns']); @endphp
                    @if(empty($group['children']))
                        <a class="ns-nav-item {{ $groupActive ? 'is-active' : '' }}" href="{{ $group['href'] }}" role="menuitem" aria-current="{{ $groupActive ? 'page' : 'false' }}">{{ $group['label'] }}</a>
                    @else
                        <div class="ns-nav-group {{ $groupActive ? 'is-active' : '' }}" data-nav-group>
                            <button type="button" class="ns-nav-item ns-nav-trigger {{ $groupActive ? 'is-active' : '' }}" aria-haspopup="true" aria-expanded="false" data-nav-trigger>
                                <span>{{ $group['label'] }}</span>
                                <span class="material-symbols-outlined ns-chevron" aria-hidden="true">expand_more</span>
                            </button>
                            <div class="ns-dropdown" role="menu">
                                @foreach($group['children'] as $child)
                                    @php $childActive = $isActive($child['patterns']); @endphp
                                    <a class="ns-dropdown-link {{ $childActive ? 'is-active' : '' }}" href="{{ $child['href'] }}" role="menuitem" aria-current="{{ $childActive ? 'page' : 'false' }}">{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="ns-nav-actions">
            <label class="ns-search"
                   aria-label="Global search"
                   data-global-search-url="{{ url('global-search') }}">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
                <input type="text" inputmode="search" placeholder="Search..." autocomplete="off" aria-label="Search">
            </label>
            <button type="button" class="ns-icon-btn" aria-label="Notifications" data-visual-only="notifications"><span class="material-symbols-outlined">notifications</span></button>
            <a class="ns-icon-btn" href="{{ url('ui/admin/users') }}" aria-label="Settings"><span class="material-symbols-outlined">settings</span></a>
            <a class="ns-avatar" href="{{ url('ui/profile') }}" aria-label="Profile"><span>{{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email ?? 'A', 0, 1)) }}</span></a>
            <button type="button" class="ns-mobile-toggle" aria-label="Open menu" aria-expanded="false" data-mobile-toggle><span class="material-symbols-outlined">menu</span></button>
        </div>
    </div>
    <div class="ns-mobile-panel" data-mobile-panel hidden>
        <div class="ns-mobile-search"><span class="material-symbols-outlined">search</span><input type="text" inputmode="search" placeholder="Search..." aria-label="Mobile search"></div>
        @foreach($navGroups as $group)
            @php $groupActive = $isActive($group['patterns']); @endphp
            @if(empty($group['children']))
                <a class="ns-mobile-link {{ $groupActive ? 'is-active' : '' }}" href="{{ $group['href'] }}">{{ $group['label'] }}</a>
            @else
                <div class="ns-mobile-group {{ $groupActive ? 'is-active' : '' }}" data-mobile-group>
                    <button type="button" class="ns-mobile-link ns-mobile-trigger {{ $groupActive ? 'is-active' : '' }}" data-mobile-trigger aria-expanded="{{ $groupActive ? 'true' : 'false' }}"><span>{{ $group['label'] }}</span><span class="material-symbols-outlined">expand_more</span></button>
                    <div class="ns-mobile-submenu" {{ $groupActive ? '' : 'hidden' }}>
                        @foreach($group['children'] as $child)
                            @php $childActive = $isActive($child['patterns']); @endphp
                            <a class="ns-mobile-sublink {{ $childActive ? 'is-active' : '' }}" href="{{ $child['href'] }}">{{ $child['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</nav>
<script>
{!! file_get_contents(public_path('js/global-navbar.js')) !!}
</script>
