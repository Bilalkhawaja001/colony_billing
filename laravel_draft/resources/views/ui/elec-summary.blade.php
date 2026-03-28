@extends('layouts.app')
@section('page_title','Electric Summary')
@section('page_subtitle','Compute and inspect electric summary outputs with unit-level reloadable report context.')
@section('content')
<div class="grid">
<div class="col-12 card">
  <h3 class="section-title">Electric Summary Controls</h3>
  <form id="elecForm" class="form-grid">
    <div class="field col-4"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}"></div>
    <div class="field col-4"><label class="label">Unit ID</label><input name="unit_id" placeholder="Unit ID" value="{{ $unitId }}"></div>
    <div class="col-4" style="display:flex;align-items:flex-end;gap:8px"><button class="btn" type="button" id="elecLoad">Load Summary</button><button class="btn btn-primary" type="button" id="elecCompute">Compute Electric</button></div>
  </form>
</div>
<div class="col-12 card"><h3 class="section-title">Execution Result</h3><pre id="elecResult">Ready.</pre></div>
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