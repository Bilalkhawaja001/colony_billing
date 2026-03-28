@extends('layouts.app')
@section('page_title','Occupancy')
@section('page_subtitle','Capture month-aware occupancy records feeding water and shared utility calculations.')
@section('content')
<div class="grid">
<div class="col-8 card">
  <h3 class="section-title">Upsert Occupancy</h3>
  <form id="occUpsertForm" class="form-grid">
    <div class="field col-3"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY"></div>
    <div class="field col-3"><label class="label">Unit ID</label><input name="unit_id" placeholder="Unit ID"></div>
    <div class="field col-3"><label class="label">Employee ID</label><input name="employee_id" placeholder="Employee ID"></div>
    <div class="field col-3"><label class="label">Persons</label><input name="persons" placeholder="Persons"></div>
    <div class="col-12"><button class="btn btn-primary" type="submit">Save Occupancy</button></div>
  </form>
</div>
<div class="col-4 card soft"><h3 class="section-title">Context</h3><div class="muted">Month + unit + employee mapping must stay clean for downstream summary accuracy.</div></div>
<div class="col-12 card"><h3 class="section-title">API Result</h3><pre id="occResult">Ready.</pre></div>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));document.getElementById('occResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('occUpsertForm').addEventListener('submit',e=>{e.preventDefault();postJson('/occupancy/upsert',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection