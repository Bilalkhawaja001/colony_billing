@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Occupancy Workspace</h3>
  <form id="occUpsertForm">
    <input name="month_cycle" placeholder="MM-YYYY">
    <input name="unit_id" placeholder="Unit ID">
    <input name="employee_id" placeholder="Employee ID">
    <input name="persons" placeholder="Persons">
    <button type="submit">Upsert Occupancy</button>
  </form>
  <pre id="occResult">Ready.</pre>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));document.getElementById('occResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('occUpsertForm').addEventListener('submit',e=>{e.preventDefault();postJson('/occupancy/upsert',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection