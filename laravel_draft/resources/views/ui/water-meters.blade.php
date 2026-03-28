@extends('layouts.app')
@section('page_title','Water Meters')
@section('page_subtitle','Operational water console with snapshot, adjustments, allocation preview and standardized zone payload tools.')
@section('content')
<div class="grid">
<div class="col-12 card">
  <h3 class="section-title">Water Controls</h3>
  <form id="waterForm" class="form-grid">
    <div class="field col-3"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}"></div>
    <div class="field col-3"><label class="label">Zone</label><select name="zone"><option>FAMILY_METER</option><option>BACHELOR_METER</option><option>ADMIN_METER</option><option>TANKER_ZONE</option></select></div>
    <div class="field col-3"><label class="label">Raw Liters</label><input name="raw_liters" placeholder="0" value="100"></div>
    <div class="field col-3"><label class="label">Common Use Liters</label><input name="common_use_liters" placeholder="0" value="10"></div>
    <div class="col-12 toolbar"><button class="btn" type="button" id="waterLoad">Load Snapshot+Adjustments+Allocation</button><button class="btn btn-primary" type="button" id="waterUpsert">Upsert One Zone</button><button class="btn" type="button" id="waterPreset">Send 4-Zone Preset</button></div>
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
document.getElementById('waterUpsert').onclick=async()=>{
 const p=f();
 show(await postJson('/api/water/zone-adjustments',{month_cycle:p.month_cycle,rows:[{water_zone:p.zone,raw_liters:Number(p.raw_liters||0),common_use_liters:Number(p.common_use_liters||0)]}));
};
document.getElementById('waterPreset').onclick=async()=>{
 const p=f();
 show(await postJson('/api/water/zone-adjustments',{month_cycle:p.month_cycle,rows:[
   {water_zone:'FAMILY_METER',raw_liters:1000,common_use_liters:100},
   {water_zone:'BACHELOR_METER',raw_liters:600,common_use_liters:60},
   {water_zone:'ADMIN_METER',raw_liters:300,common_use_liters:30},
   {water_zone:'TANKER_ZONE',raw_liters:200,common_use_liters:20}
 ]}));
};
</script>
@endsection