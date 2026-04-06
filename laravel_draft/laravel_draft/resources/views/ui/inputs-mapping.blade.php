@extends('layouts.app')
@section('page_title','Inputs Mapping')
@section('page_subtitle','Operator-ready mapping console: room cascade actions + context checks for month/unit chains.')
@section('content')
<div class="grid">
<div class="col-6 card">
  <h3 class="section-title">Rooms Cascade Cleanup</h3>
  <form id="cascadeForm" class="form-grid">
    <div class="field col-6"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY"></div>
    <div class="field col-6"><label class="label">Unit ID</label><input name="unit_id" placeholder="U-001"></div>
    <div class="col-12"><button class="btn btn-warn" type="submit">Run Cascade (cleanup occupancy+rooms)</button></div>
  </form>
</div>
<div class="col-6 card">
  <h3 class="section-title">Occupancy Context Check</h3>
  <div class="form-grid">
    <div class="field col-6"><label class="label">Month Cycle</label><input id="ctxMonth" placeholder="MM-YYYY"></div>
    <div class="field col-6"><label class="label">Company/Employee ID</label><input id="ctxEmployee" placeholder="E1001"></div>
    <div class="col-12"><button class="btn" type="button" id="ctxBtn">Load Context</button></div>
  </div>
</div>
<div class="col-12 card"><h3 class="section-title">Result</h3><pre id="mapResult">Ready.</pre></div>
</div>
<script>
const csrf=@json(csrf_token());
const out=document.getElementById('mapResult');
function show(v){out.textContent=JSON.stringify(v,null,2)}
async function req(url,method='GET',payload=null){const o={method,headers:{'X-CSRF-TOKEN':csrf}}; if(payload!==null){o.headers['Content-Type']='application/json';o.body=JSON.stringify(payload);} const r=await fetch(url,o);const j=await r.json().catch(()=>({raw:'non-json'}));show({status:r.status,body:j});}
document.getElementById('cascadeForm').addEventListener('submit',e=>{e.preventDefault();req('/api/rooms/cascade','POST',Object.fromEntries(new FormData(e.target)));});
document.getElementById('ctxBtn').onclick=()=>{const m=encodeURIComponent(document.getElementById('ctxMonth').value||'');const e=encodeURIComponent(document.getElementById('ctxEmployee').value||'');req('/occupancy/context?month_cycle='+m+'&employee_id='+e);};
</script>
@endsection