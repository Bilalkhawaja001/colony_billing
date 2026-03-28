@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Rates Workspace</h3>
  <p>Monthly rates upsert/approve controls.</p>
  <form id="ratesUpsertForm">
    <input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}">
    <input name="elec_rate" placeholder="Elec Rate" value="50">
    <input name="water_general_rate" placeholder="Water General" value="0.2">
    <input name="water_drinking_rate" placeholder="Water Drinking" value="0.5">
    <input name="school_van_rate" placeholder="Van Rate" value="4500">
    <button type="submit">Upsert Rates</button>
  </form>
  <br>
  <form id="ratesApproveForm">
    <input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}">
    <button type="submit">Approve Rates</button>
  </form>
  <pre id="ratesResult">Ready.</pre>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){
 const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});
 const j=await r.json().catch(()=>({raw:'non-json'}));
 document.getElementById('ratesResult').textContent=JSON.stringify({status:r.status,body:j},null,2);
}
document.getElementById('ratesUpsertForm').addEventListener('submit',e=>{e.preventDefault();postJson('/rates/upsert',Object.fromEntries(new FormData(e.target)));});
document.getElementById('ratesApproveForm').addEventListener('submit',e=>{e.preventDefault();postJson('/rates/approve',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection