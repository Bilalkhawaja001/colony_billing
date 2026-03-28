@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Family Details Workspace</h3>
  <form id="familyForm">
    <input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}">
    <input name="company_id" placeholder="Company ID" value="{{ $companyId }}">
    <input name="family_member_name" placeholder="Member Name" value="Ali">
    <input name="relation" placeholder="Relation" value="Son">
    <input name="age" placeholder="Age" value="8">
    <button type="button" id="familyLoad">Load</button>
    <button type="button" id="familyUpsert">Upsert</button>
  </form>
  <pre id="familyResult">Ready.</pre>
</div>
<script>
const csrf=@json(csrf_token());
const f=()=>Object.fromEntries(new FormData(document.getElementById('familyForm')));
async function getJson(url){const r=await fetch(url);const j=await r.json().catch(()=>({}));return {status:r.status,body:j};}
async function postJson(url,payload){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const j=await r.json().catch(()=>({}));return {status:r.status,body:j};}
function show(v){document.getElementById('familyResult').textContent=JSON.stringify(v,null,2)}
document.getElementById('familyLoad').onclick=async()=>{const p=f();show(await getJson('/family/details?month_cycle='+encodeURIComponent(p.month_cycle||'')+'&company_id='+encodeURIComponent(p.company_id||'')));};
document.getElementById('familyUpsert').onclick=async()=>{show(await postJson('/family/details/upsert',f()));};
</script>
@endsection