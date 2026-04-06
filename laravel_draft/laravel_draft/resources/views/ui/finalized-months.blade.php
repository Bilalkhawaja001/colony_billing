@extends('layouts.app')
@section('page_title','Finalized Months')
@section('page_subtitle','Finalize month outputs and review lock/finalization state history for audit trace.')
@section('content')
<div class="grid">
<div class="col-12 card">
  <h3 class="section-title">Finalize Month</h3>
  <form id="finalizeForm" class="form-grid">
    <div class="field col-4"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}"></div>
    <div class="col-8" style="display:flex;align-items:flex-end"><button class="btn btn-primary" type="button" id="finalizeBtn">Finalize</button></div>
  </form>
</div>
<div class="col-12 card">
  <h3 class="section-title">Finalization Register</h3>
  <table>
    <thead><tr><th>Month</th><th>State</th><th>Locked At</th><th>Finalized At</th></tr></thead>
    <tbody>
    @forelse($rows as $r)
      <tr><td>{{ $r->month_cycle ?? '' }}</td><td><span class="badge">{{ $r->state ?? '' }}</span></td><td>{{ $r->locked_at ?? '—' }}</td><td>{{ $r->finalized_at ?? '—' }}</td></tr>
    @empty
      <tr><td colspan="4"><div class="empty">No finalized month rows available.</div></td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div class="col-12 card"><h3 class="section-title">API Result</h3><pre id="finalizeResult">Ready.</pre></div>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));document.getElementById('finalizeResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('finalizeBtn').onclick=()=>postJson('/api/billing/finalize',Object.fromEntries(new FormData(document.getElementById('finalizeForm'))));
</script>
@endsection