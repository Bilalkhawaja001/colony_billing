@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Unit Master Workspace</h3>
  <form id="unitUpsertForm">
    <input name="unit_id" placeholder="Unit ID">
    <input name="unit_name" placeholder="Unit Name">
    <button type="submit">Upsert Unit</button>
  </form>
  <pre id="unitResult">Ready.</pre>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));document.getElementById('unitResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('unitUpsertForm').addEventListener('submit',e=>{e.preventDefault();postJson('/units/upsert',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection