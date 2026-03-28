@extends('layouts.app')
@section('page_title','Employee Master')
@section('page_subtitle','Manage employee identity records used by billing, registry, and reporting modules.')
@section('content')
<div class="grid">
<div class="col-8 card">
  <h3 class="section-title">Upsert Employee</h3>
  <form id="empUpsertForm" class="form-grid">
    <div class="field col-4"><label class="label">Company ID</label><input name="company_id" placeholder="Company ID"></div>
    <div class="field col-8"><label class="label">Employee Name</label><input name="employee_name" placeholder="Employee Name"></div>
    <div class="col-12"><button class="btn btn-primary" type="submit">Save Employee</button></div>
  </form>
</div>
<div class="col-4 card soft"><h3 class="section-title">Consistency</h3><div class="muted">Use canonical company IDs to avoid report join mismatches.</div></div>
<div class="col-12 card"><h3 class="section-title">API Result</h3><pre id="empResult">Ready.</pre></div>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));document.getElementById('empResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('empUpsertForm').addEventListener('submit',e=>{e.preventDefault();postJson('/employees/upsert',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection