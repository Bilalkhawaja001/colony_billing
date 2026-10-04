@extends('billing_control.layout')
@section('content')
@php
    $stats = data_get($readiness, 'stats', []);
    $month = request('month_cycle', data_get($readiness, 'month', data_get($stats, 'month_cycle', now()->format('m-Y'))));
    $isReady = (bool) data_get($readiness, 'isReady', false);
    $blockers = data_get($readiness, 'blockers', []);
    $meterCount = (int) data_get($stats, 'active_meters', 0);
    $readingCount = (int) data_get($stats, 'current_readings', 0);
    $pendingReadings = max($meterCount - $readingCount, 0);
@endphp

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
<script>
tailwind.config = { theme: { extend: {
  colors: {
    'ink':'#0f172a','muted':'#45464d','surface':'#ffffff','bg':'#f7f9fb',
    'sub':'#f2f4f6','border':'#e2e8f0','blue':'#2563eb','blue-h':'#1d4ed8',
    'ok':'#10b981','warn':'#f59e0b','bad':'#ef4444'
  },
  fontFamily: { sans:['Inter','sans-serif'], mono:['JetBrains Mono','monospace'] }
}}}
</script>

<div class="font-sans" style="font-family:Inter,sans-serif">

  {{-- Header --}}
  <div class="flex flex-col md:flex-row justify-between md:items-center gap-4 mb-6">
    <div>
      <div class="text-xs font-semibold text-blue uppercase tracking-wider mb-1">Billing Center</div>
      <h1 class="text-3xl font-bold text-ink">Electricity · @include('billing_control.components.month-label', ['value' => $month])</h1>
    </div>
    <form method="get" action="{{ route('billing.control.home') }}" class="flex gap-2 items-center">
      @foreach(request()->except(['month_cycle', 'month']) as $key => $value)
        @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
      @endforeach
      <div data-month-picker-wrap>
        @include('billing_control.components.month-select', ['value' => $month, 'id' => 'control-room-month-select'])
      </div>
      <button class="px-4 py-2 border border-border rounded-lg text-sm font-semibold text-muted hover:bg-sub transition" type="submit">Refresh</button>
    </form>
  </div>

  {{-- Hero status banner --}}
  <div class="rounded-xl border p-5 mb-6 flex items-center gap-4 {{ $isReady ? 'bg-ok/5 border-ok/30' : 'bg-warn/5 border-warn/30' }}">
    <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl flex-shrink-0 {{ $isReady ? 'bg-ok/10 text-ok' : 'bg-warn/10 text-warn' }}">
      {{ $isReady ? '✓' : '⚠' }}
    </div>
    <div class="flex-grow">
      <div class="font-bold text-ink">{{ $isReady ? 'READY FOR PREVIEW' : 'MUST FIX ITEMS NEED ATTENTION FIRST' }}</div>
      <div class="text-sm text-muted">
        {{ $isReady ? 'All required data is complete. You can preview bills.' : count($blockers).' Must Fix items need attention before bill preview.' }}
      </div>
    </div>
    <a class="px-4 py-2 rounded-lg font-semibold text-sm text-white {{ $isReady ? 'bg-blue hover:bg-blue-h' : 'bg-warn hover:opacity-90' }} transition flex-shrink-0"
       href="{{ $isReady ? route('billing.control.generate', ['month_cycle'=>$month]) : route('billing.control.readiness', ['month_cycle'=>$month]) }}">
      {{ $isReady ? 'Preview Bills' : 'Check & Fix Data' }}
    </a>
  </div>

  {{-- Stat cards --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
      $cards = [
        ['Employees', data_get($stats,'active_employees','-'), false],
        ['Meter Locations', data_get($stats,'active_meters','-'), false],
        ['Readings In', data_get($stats,'current_readings','-'), !$isReady],
        ['Pending', $pendingReadings, $pendingReadings > 0],
      ];
    @endphp
    @foreach($cards as [$title,$value,$warn])
      <div class="bg-surface border rounded-xl p-4 {{ $warn ? 'border-warn/30' : 'border-border' }}">
        <div class="text-xs font-semibold uppercase tracking-wider mb-2 {{ $warn ? 'text-warn' : 'text-muted' }}">{{ $title }}</div>
        <div class="text-3xl font-bold text-ink font-mono">{{ $value }}</div>
      </div>
    @endforeach
  </div>

  {{-- Generate button --}}
  <div>
    @if($isReady)
      <a class="inline-flex items-center gap-2 px-5 py-3 bg-blue text-white rounded-lg font-semibold hover:bg-blue-h transition shadow-sm"
         href="{{ route('billing.control.generate', ['month_cycle'=>$month]) }}">⚡ Preview & Generate</a>
      <div class="text-sm text-muted mt-2">Preview mode only · final generation still locked</div>
    @else
      <button class="inline-flex items-center gap-2 px-5 py-3 bg-sub text-muted rounded-lg font-semibold cursor-not-allowed" disabled>⚡ Preview & Generate</button>
      <div class="text-sm text-muted mt-2">🔒 Must Fix items need attention first</div>
    @endif
  </div>

</div>
@endsection
