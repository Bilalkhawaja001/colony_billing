@extends('billing_control.layout')
@section('content')
@include('billing_control.components.tw-head')
<style>
  .sidebar, .billing-center-nav { display:none !important; }
  .main, .main-inner { max-width:100% !important; width:100% !important; padding-left:24px !important; padding-right:24px !important; }
</style>

<div style="font-family:Inter,sans-serif">
  <div style="margin-bottom:18px">
    <div class="text-xs font-semibold text-blue uppercase tracking-wider mb-1">HR Import</div>
    <h1 class="text-3xl font-bold text-ink">Pending Employees</h1>
    <div class="text-sm text-muted mt-1">New employees from the HR upload. Assign a residence, then add them to the master.</div>
  </div>

  @if(session('status'))<div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:14px;font-weight:600">{{ session('status') }}</div>@endif
  @if(session('error'))<div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:14px;font-weight:600">{{ session('error') }}</div>@endif

  <div class="bg-surface border border-border rounded-xl p-4">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px">
      <div style="font-weight:700;color:#0f172a">{{ count($pending) }} employee(s) waiting</div>
      <form method="get" style="display:flex;gap:6px;align-items:center">
        <input name="q" value="{{ $q }}" placeholder="Search ID, name, department…" style="border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;font-size:13px;width:240px">
        <button type="submit" style="padding:6px 14px;background:#2563eb;color:#fff;border:0;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Search</button>
        @if($q !== '')<a href="{{ url('pending-employees') }}" style="font-size:13px;color:#64748b;text-decoration:none">Clear</a>@endif
      </form>
    </div>

    <div style="overflow:auto">
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <thead style="background:#f8f9fb">
        <tr>
          <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb">Company ID</th>
          <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb">Name</th>
          <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb">Department</th>
          <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb">Colony</th>
          <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb">Floor</th>
          <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #e5e7eb">Room</th>
          <th style="text-align:right;padding:8px 10px;border-bottom:1px solid #e5e7eb">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($pending as $p)
          <tr>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f2f4;font-family:monospace;font-weight:600">{{ $p->company_id }}</td>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f2f4;font-weight:600">{{ $p->name }}</td>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f2f4">{{ $p->department ?: '—' }}</td>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f2f4">
              <input form="pe-{{ $p->company_id }}" name="colony_type" class="pe-colony" data-id="{{ $p->company_id }}" list="colonyList" autocomplete="off" placeholder="type to search…" style="border:1px solid #e2e8f0;border-radius:6px;padding:4px 8px;font-size:12px;width:180px">
            </td>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f2f4">
              <input form="pe-{{ $p->company_id }}" name="block_floor" class="pe-floor" data-id="{{ $p->company_id }}" list="floorList-{{ $p->company_id }}" autocomplete="off" placeholder="floor…" style="border:1px solid #e2e8f0;border-radius:6px;padding:4px 8px;font-size:12px;width:120px">
              <datalist id="floorList-{{ $p->company_id }}"></datalist>
            </td>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f2f4">
              <input class="pe-room" data-id="{{ $p->company_id }}" list="roomList-{{ $p->company_id }}" autocomplete="off" placeholder="room…" style="border:1px solid #e2e8f0;border-radius:6px;padding:4px 8px;font-size:12px;width:170px">
              <datalist id="roomList-{{ $p->company_id }}"></datalist>
              <input type="hidden" form="pe-{{ $p->company_id }}" name="unit_id" class="pe-unit" data-id="{{ $p->company_id }}">
              <input type="hidden" form="pe-{{ $p->company_id }}" name="room_no" class="pe-roomno" data-id="{{ $p->company_id }}">
            </td>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f2f4;text-align:right;white-space:nowrap">
              <form method="post" action="{{ route('billing.pending.approve', $p->company_id) }}" id="pe-{{ $p->company_id }}" style="display:inline">@csrf</form>
              <button form="pe-{{ $p->company_id }}" type="submit" style="padding:4px 12px;background:#10b981;color:#fff;border:0;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer">Add</button>
              <form method="post" action="{{ route('billing.pending.reject', $p->company_id) }}" style="display:inline" onsubmit="return confirm('Remove {{ $p->company_id }} from pending?')">
                @csrf @method('DELETE')
                <button type="submit" style="background:none;border:0;color:#ef4444;font-size:12px;font-weight:600;cursor:pointer;margin-left:6px">Skip</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" style="padding:24px;text-align:center;color:#94a3b8">No pending employees.</td></tr>
        @endforelse
      </tbody>
    </table>
    </div>

    <datalist id="colonyList">@foreach(array_keys($tree) as $cn)<option value="{{ $cn }}">@endforeach</datalist>
    <datalist id="unitList">
      @foreach($units as $u)<option value="{{ $u }}">@endforeach
    </datalist>
  </div>
</div>

<script>
window.PE_TREE = @json($tree);
(function(){
  function setList(id, items, isRoom){
    var dl = document.getElementById(id);
    if(!dl) return;
    dl.innerHTML = '';
    items.forEach(function(it){
      var o = document.createElement('option');
      if(isRoom){ o.value = it.room; o.label = it.n > 0 ? (it.n + ' person') : 'vacant'; }
      else { o.value = it; }
      dl.appendChild(o);
    });
  }

  document.querySelectorAll('.pe-colony').forEach(function(ci){
    ci.addEventListener('input', function(){
      var id = ci.dataset.id;
      var fi = document.querySelector('.pe-floor[data-id="'+id+'"]');
      var ri = document.querySelector('.pe-room[data-id="'+id+'"]');
      var floors = PE_TREE[ci.value] ? Object.keys(PE_TREE[ci.value]) : [];
      setList('floorList-'+id, floors, false);
      setList('roomList-'+id, [], true);
      if(fi) fi.value = '';
      if(ri) ri.value = '';
      var u = document.querySelector('.pe-unit[data-id="'+id+'"]');
      var r = document.querySelector('.pe-roomno[data-id="'+id+'"]');
      if(u) u.value = '';
      if(r) r.value = '';
    });
  });

  document.querySelectorAll('.pe-floor').forEach(function(fi){
    fi.addEventListener('input', function(){
      var id = fi.dataset.id;
      var ci = document.querySelector('.pe-colony[data-id="'+id+'"]');
      var ri = document.querySelector('.pe-room[data-id="'+id+'"]');
      var list = (PE_TREE[ci.value] && PE_TREE[ci.value][fi.value]) ? PE_TREE[ci.value][fi.value] : [];
      setList('roomList-'+id, list, true);
      if(ri) ri.value = '';
    });
  });

  document.querySelectorAll('.pe-room').forEach(function(ri){
    ri.addEventListener('input', function(){
      var id = ri.dataset.id;
      var ci = document.querySelector('.pe-colony[data-id="'+id+'"]');
      var fi = document.querySelector('.pe-floor[data-id="'+id+'"]');
      var list = (PE_TREE[ci.value] && PE_TREE[ci.value][fi.value]) ? PE_TREE[ci.value][fi.value] : [];
      var hit = list.filter(function(x){ return x.room === ri.value; })[0];
      var u = document.querySelector('.pe-unit[data-id="'+id+'"]');
      var r = document.querySelector('.pe-roomno[data-id="'+id+'"]');
      if(u) u.value = hit ? hit.unit : '';
      if(r) r.value = hit ? hit.room : '';
    });
  });
})();
</script>
@endsection
