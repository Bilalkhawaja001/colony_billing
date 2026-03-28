@extends('layouts.app')
@section('page_title','Water Meters')
@section('page_subtitle','Water occupancy snapshot, zone adjustments and allocation preview in one workspace.')
@section('content')
<div class="grid">
<div class="col-12 card">
  <h3 class="section-title">Water Controls</h3>
  <form id="waterForm" class="form-grid">
    <div class="field col-3"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}"></div>
    <div class="field col-3"><label class="label">Zone</label><input name="zone" placeholder="Zone" value="A"></div>
    <div class="field col-3"><label class="label">Liters</label><input name="liters" placeholder="Liters" value="100"></div>
    <div class="col-3" style="display:flex;align-items:flex-end;gap:8px"><button class="btn" type="button" id="waterLoad">Load Snapshot</button><button class="btn btn-primary" type="button" id="waterUpsert">Upsert Adjustment</button></div>
  </form>
</div>
<div class="col-12 card"><h3 class="section-title">Result</h3><pre id="waterResult">Ready.</pre></div>
</div>
<script>
const csrf=@json(csrf_token());
const f=()=>Object.fromEntries(new FormData(document.getElementById('waterForm')));
async function getJson(url){const r=await fetch(url);const j=await r.json().catch(()=>({}));return {status:r.status,body:j};}
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));return {status:r.status,body:j};}
function show(v){document.getElementById('waterResult').textContent=JSON.stringify(v,null,2)}
document.getElementById('waterLoad').onclick=async()=>{
 const m=encodeURIComponent(f().month_cycle||'');
 const a=await getJson('/api/water/occupancy-snapshot?month_cycle='+m);
 const b=await getJson('/api/water/zone-adjustments?month_cycle='+m);
 const c=await getJson('/api/water/allocation-preview?month_cycle='+m);
 show({snapshot:a,adjustments:b,allocation:c});
};
document.getElementById('waterUpsert').onclick=async()=>{show(await postJson('/api/water/zone-adjustments',f()));};
</script>
@endsection