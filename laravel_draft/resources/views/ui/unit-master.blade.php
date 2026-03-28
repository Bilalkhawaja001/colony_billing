@extends('layouts.app')
@section('page_title','Unit Master')
@section('page_subtitle','Manage unit references with a compact operator-friendly admin form.')
@section('content')
<div class="grid">
<div class="col-7 card">
  <h3 class="section-title">Upsert Unit</h3>
  <form id="unitUpsertForm" class="form-grid">
    <div class="field col-6"><label class="label">Unit ID</label><input name="unit_id" placeholder="Unit ID"></div>
    <div class="field col-6"><label class="label">Unit Name</label><input name="unit_name" placeholder="Unit Name"></div>
    <div class="col-12"><button class="btn btn-primary" type="submit">Save Unit</button></div>
  </form>
</div>
<div class="col-5 card soft"><h3 class="section-title">Notes</h3><div class="muted">Keep unit IDs stable; downstream occupancy/meter mappings depend on these keys.</div></div>
<div class="col-12 card"><h3 class="section-title">API Result</h3><pre id="unitResult">Ready.</pre></div>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));document.getElementById('unitResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('unitUpsertForm').addEventListener('submit',e=>{e.preventDefault();postJson('/units/upsert',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection