@extends('layouts.app')
@section('page_title','Meter Master')
@section('page_subtitle','Manage meter readings and meter-to-unit mapping from one compact workspace.')
@section('content')
<div class="grid">
  <div class="col-7 card">
    <h3 class="section-title">Upsert Meter Reading</h3>
    <form id="meterReadingForm" class="form-grid">
      <div class="field col-4"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY"></div>
      <div class="field col-4"><label class="label">Unit ID</label><input name="unit_id" placeholder="Unit ID"></div>
      <div class="field col-4"><label class="label">Meter ID</label><input name="meter_id" placeholder="Meter ID"></div>
      <div class="field col-6"><label class="label">Usage</label><input name="usage" placeholder="Usage"></div>
      <div class="field col-6"><label class="label">Amount</label><input name="amount" placeholder="Amount"></div>
      <div class="col-12"><button class="btn btn-primary" type="submit">Save Reading</button></div>
    </form>
  </div>
  <div class="col-5 card">
    <h3 class="section-title">Map Meter ↔ Unit</h3>
    <form id="meterUnitForm" class="form-grid">
      <div class="field col-6"><label class="label">Unit ID</label><input name="unit_id" placeholder="Unit ID"></div>
      <div class="field col-6"><label class="label">Meter ID</label><input name="meter_id" placeholder="Meter ID"></div>
      <div class="col-12"><button class="btn btn-success" type="submit">Save Mapping</button></div>
    </form>
  </div>
  <div class="col-12 card"><h3 class="section-title">API Result</h3><pre id="meterResult">Ready.</pre></div>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));document.getElementById('meterResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('meterReadingForm').addEventListener('submit',e=>{e.preventDefault();postJson('/meter-reading/upsert',Object.fromEntries(new FormData(e.target)));});
document.getElementById('meterUnitForm').addEventListener('submit',e=>{e.preventDefault();postJson('/meter-unit/upsert',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection