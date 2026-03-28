@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Electric Summary Workspace</h3>
  <form id="elecForm">
    <input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}">
    <input name="unit_id" placeholder="Unit ID" value="{{ $unitId }}">
    <button type="button" id="elecLoad">Load Summary</button>
    <button type="button" id="elecCompute">Compute Electric</button>
  </form>
  <pre id="elecResult">Ready.</pre>
</div>
<script>
const csrf=@json(csrf_token());
const f=()=>Object.fromEntries(new FormData(document.getElementById('elecForm')));
async function getJson(url){const r=await fetch(url);const j=await r.json().catch(()=>({}));return {status:r.status,body:j};}
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));return {status:r.status,body:j};}
function show(v){document.getElementById('elecResult').textContent=JSON.stringify(v,null,2)}
document.getElementById('elecLoad').onclick=async()=>{const p=f(); show(await getJson('/reports/elec-summary?month_cycle='+encodeURIComponent(p.month_cycle||'')+'&unit_id='+encodeURIComponent(p.unit_id||'')));};
document.getElementById('elecCompute').onclick=async()=>{show(await postJson('/billing/elec/compute',f()));};
</script>
@endsection