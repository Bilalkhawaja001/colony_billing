@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Rooms Workspace</h3>
  <form id="roomUpsertForm">
    <input name="unit_id" placeholder="Unit ID">
    <input name="room_code" placeholder="Room Code">
    <input name="room_label" placeholder="Room Label">
    <button type="submit">Upsert Room</button>
  </form>
  <pre id="roomResult">Ready.</pre>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));document.getElementById('roomResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('roomUpsertForm').addEventListener('submit',e=>{e.preventDefault();postJson('/rooms/upsert',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection