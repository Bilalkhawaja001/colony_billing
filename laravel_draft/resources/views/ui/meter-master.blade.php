@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Meter Workspace</h3>
  <form id="meterReadingForm">
    <input name="month_cycle" placeholder="MM-YYYY">
    <input name="unit_id" placeholder="Unit ID">
    <input name="meter_id" placeholder="Meter ID">
    <input name="usage" placeholder="Usage">
    <input name="amount" placeholder="Amount">
    <button type="submit">Upsert Reading</button>
  </form>
  <br>
  <form id="meterUnitForm">
    <input name="unit_id" placeholder="Unit ID">
    <input name="meter_id" placeholder="Meter ID">
    <button type="submit">Map Meter-Unit</button>
  </form>
  <pre id="meterResult">Ready.</pre>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));document.getElementById('meterResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('meterReadingForm').addEventListener('submit',e=>{e.preventDefault();postJson('/meter-reading/upsert',Object.fromEntries(new FormData(e.target)));});
document.getElementById('meterUnitForm').addEventListener('submit',e=>{e.preventDefault();postJson('/meter-unit/upsert',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection