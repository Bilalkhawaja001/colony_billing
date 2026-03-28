@extends('layouts.app')
@section('page_title','Rooms')
@section('page_subtitle','Maintain room metadata used by occupancy and billing allocation workflows.')
@section('content')
<div class="grid">
<div class="col-8 card">
  <h3 class="section-title">Upsert Room</h3>
  <form id="roomUpsertForm" class="form-grid">
    <div class="field col-4"><label class="label">Unit ID</label><input name="unit_id" placeholder="Unit ID"></div>
    <div class="field col-4"><label class="label">Room Code</label><input name="room_code" placeholder="Room Code"></div>
    <div class="field col-4"><label class="label">Room Label</label><input name="room_label" placeholder="Room Label"></div>
    <div class="col-12"><button class="btn btn-primary" type="submit">Save Room</button></div>
  </form>
</div>
<div class="col-4 card soft"><h3 class="section-title">Operator Tip</h3><div class="muted">Use consistent room labels to keep unit cascade outputs readable.</div></div>
<div class="col-12 card"><h3 class="section-title">API Result</h3><pre id="roomResult">Ready.</pre></div>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));document.getElementById('roomResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('roomUpsertForm').addEventListener('submit',e=>{e.preventDefault();postJson('/rooms/upsert',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection