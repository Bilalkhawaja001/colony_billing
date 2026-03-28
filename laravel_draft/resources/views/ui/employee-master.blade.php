@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Employee Master Workspace</h3>
  <form id="empUpsertForm">
    <input name="company_id" placeholder="Company ID">
    <input name="employee_name" placeholder="Employee Name">
    <button type="submit">Upsert Employee</button>
  </form>
  <pre id="empResult">Ready.</pre>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));document.getElementById('empResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('empUpsertForm').addEventListener('submit',e=>{e.preventDefault();postJson('/employees/upsert',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection