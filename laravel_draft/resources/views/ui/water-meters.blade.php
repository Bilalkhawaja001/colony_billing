@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Water Module Workspace</h3>
  <form id="waterForm">
    <input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}">
    <input name="zone" placeholder="Zone" value="A">
    <input name="liters" placeholder="Liters" value="100">
    <button type="button" id="waterLoad">Load Snapshot/Adjustments</button>
    <button type="button" id="waterUpsert">Upsert Adjustment</button>
  </form>
  <pre id="waterResult">Ready.</pre>
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
 show(await postJson('/api/water/zone-adjustments',p));
};
</script>
@endsection