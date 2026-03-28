@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Finalized Months Workspace</h3>
  <form id="finalizeForm">
    <input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}">
    <button type="button" id="finalizeBtn">Finalize</button>
  </form>
  <table width="100%" border="1" cellspacing="0" cellpadding="4">
    <tr><th>Month</th><th>State</th><th>Locked At</th><th>Finalized At</th></tr>
    @forelse($rows as $r)
      <tr><td>{{ $r->month_cycle ?? '' }}</td><td>{{ $r->state ?? '' }}</td><td>{{ $r->locked_at ?? '' }}</td><td>{{ $r->finalized_at ?? '' }}</td></tr>
    @empty
      <tr><td colspan="4">No rows</td></tr>
    @endforelse
  </table>
  <pre id="finalizeResult">Ready.</pre>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));document.getElementById('finalizeResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('finalizeBtn').onclick=()=>postJson('/api/billing/finalize',Object.fromEntries(new FormData(document.getElementById('finalizeForm'))));
</script>
@endsection