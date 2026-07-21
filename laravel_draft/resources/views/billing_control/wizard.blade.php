@extends('billing_control.layout')
@section('content')
@include('billing_control.components.tw-head')

<div style="font-family:Inter,sans-serif">

  <div class="flex justify-between items-center mb-6" style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
    <div>
      <div class="text-xs font-semibold text-blue uppercase tracking-wider mb-1">Billing Center</div>
      <h1 class="text-3xl font-bold text-ink">Bill Generation Wizard</h1>
      <div class="text-sm text-muted mt-1">Cycle: {{ $cycleStart ?? '-' }} to {{ $cycleEnd ?? '-' }}</div>
    </div>
    <form method="post" action="{{ route('billing.control.wizard.cycle') }}" style="display:flex;gap:8px;align-items:flex-end;flex-wrap:wrap">
      @csrf
      <div>
        <label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:3px">Month</label>
        <input type="month" id="wzMonth" value="{{ $month ? substr($month,3,4).'-'.substr($month,0,2) : '' }}"
               style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 10px;font-size:14px">
        <input type="hidden" name="month_cycle" id="wzMonthCycle" value="{{ $month }}">
      </div>
      <div>
        <label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:3px">Cycle Start</label>
        <input type="date" name="cycle_start_date" id="wzStart" value="{{ $cycleStart }}"
               style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 10px;font-size:14px">
      </div>
      <div>
        <label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:3px">Cycle End</label>
        <input type="date" name="cycle_end_date" id="wzEnd" value="{{ $cycleEnd }}"
               style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 10px;font-size:14px">
      </div>
      <button type="submit" style="padding:8px 16px;background:#2563eb;color:#fff;border:0;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">Load / Save Cycle</button>
    </form>
  </div>

  @if(session('status'))<div class="bg-ok/10 border border-ok/30 text-ok rounded-lg px-4 py-2 mb-4 text-sm font-semibold">{{ session('status') }}</div>@endif
  @if(session('error'))<div class="bg-bad/10 border border-bad/30 text-bad rounded-lg px-4 py-2 mb-4 text-sm font-semibold">{{ session('error') }}</div>@endif

  <div style="display:flex;flex-direction:column;gap:12px">
    @foreach($steps as $i => $s)
      <div class="bg-surface border rounded-xl p-4 {{ $s['ok'] ? 'border-ok/30' : 'border-warn/40' }}" style="display:flex;gap:14px;align-items:flex-start">
        <div style="width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;{{ $s['ok'] ? 'background:#d1fae5;color:#047857' : 'background:#fef3c7;color:#b45309' }}">
          {{ $s['ok'] ? '✓' : $i+1 }}
        </div>
        <div style="flex-grow:1">
          <div style="font-weight:700;color:#0f172a">{{ $s['title'] }}</div>
          <div style="font-size:13px;color:#64748b;margin-top:2px">{{ $s['detail'] }}</div>
        </div>
        <div style="flex-shrink:0;text-align:right">
          @if($s['ok'])
            <span style="font-size:12px;font-weight:700;color:#047857">READY</span>
          @else
            <a href="{{ $s['fix_url'] }}" style="display:inline-block;padding:6px 14px;background:#f59e0b;color:#fff;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none">Fix Now</a>
          @endif
        </div>
      </div>
    @endforeach
  </div>

  <div class="bg-surface border border-border rounded-xl p-5" style="margin-top:20px">
    <div style="font-weight:700;color:#0f172a;margin-bottom:12px">Final Step — Preview &amp; Generate</div>

    @if(!$allOk)
      <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;font-size:13px;color:#92400e;margin-bottom:14px">
        Some steps still have issues. Fix them above, or continue and record a decision for each issue on the
        <a href="{{ route('billing.control.readiness', ['month_cycle'=>$month]) }}" style="color:#2563eb;font-weight:600">Readiness</a> page.
      </div>
    @endif

    <form method="post" action="{{ route('billing.control.generate.store') }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      @csrf
      <input type="hidden" name="month_cycle" value="{{ $month }}">
      <select name="method_code" class="border border-border rounded-lg px-3 py-2 text-sm" required>
        @foreach($methods as $mc => $ml)
          <option value="{{ $mc }}">{{ $ml }}</option>
        @endforeach
      </select>
      <button type="submit" class="px-5 py-2 bg-blue text-white rounded-lg text-sm font-semibold hover:bg-blue-h">Run Preview</button>
      <a href="{{ route('billing.control.generate', ['month_cycle'=>$month]) }}" style="padding:8px 16px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;font-weight:600;color:#45464d;text-decoration:none">Go to Official Generate</a>
    </form>
  </div>

</div>
@endsection

<script>
(function(){
  var m = document.getElementById('wzMonth');
  var mc = document.getElementById('wzMonthCycle');
  var s = document.getElementById('wzStart');
  var e = document.getElementById('wzEnd');
  if(!m) return;
  m.addEventListener('change', function(){
    var v = m.value; // YYYY-MM
    if(!v) return;
    var y = parseInt(v.split('-')[0],10), mo = parseInt(v.split('-')[1],10);
    mc.value = String(mo).padStart(2,'0') + '-' + y;
    // default cycle: 16th of previous month to 15th of selected month
    var pm = mo - 1, py = y;
    if(pm === 0){ pm = 12; py = y - 1; }
    s.value = py + '-' + String(pm).padStart(2,'0') + '-16';
    e.value = y + '-' + String(mo).padStart(2,'0') + '-15';
  });
})();
</script>
