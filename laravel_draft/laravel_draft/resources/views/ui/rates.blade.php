@extends('layouts.app')
@section('page_title','Rates Workspace')
@section('page_subtitle','Configure monthly utility rates, then approve for controlled billing execution.')
@section('content')
<div class="grid">
  <div class="col-8 card">
    <h3 class="section-title">Monthly Rate Configuration</h3>
    <form id="ratesUpsertForm" class="form-grid">
      <div class="field col-4"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}"></div>
      <div class="field col-4"><label class="label">Electric Rate</label><input name="elec_rate" placeholder="Elec Rate" value="50"></div>
      <div class="field col-4"><label class="label">Water General Rate</label><input name="water_general_rate" placeholder="Water General" value="0.2"></div>
      <div class="field col-4"><label class="label">Water Drinking Rate</label><input name="water_drinking_rate" placeholder="Water Drinking" value="0.5"></div>
      <div class="field col-4"><label class="label">School Van Rate</label><input name="school_van_rate" placeholder="Van Rate" value="4500"></div>
      <div class="col-12"><button class="btn btn-primary" type="submit">Save / Update Rates</button></div>
    </form>
  </div>
  <div class="col-4 card soft">
    <h3 class="section-title">Approval Step</h3>
    <form id="ratesApproveForm" class="form-grid">
      <div class="field col-12"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}"></div>
      <div class="col-12"><button class="btn btn-success" type="submit">Approve Rates</button></div>
    </form>
    <div class="alert" style="margin-top:10px">Approve only after verification; this affects downstream billing outputs.</div>
  </div>
  <div class="col-12 card">
    <h3 class="section-title">Rates API Result</h3>
    <pre id="ratesResult">Ready.</pre>
  </div>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){
 const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});
 let body; let ok=true;
 try { body=await r.json(); }
 catch (e) { ok=false; body={status:'error',message:'Non-JSON response from server'}; }
 const result= ok && body && typeof body==='object' ? body : {status: r.status, body};
 document.getElementById('ratesResult').textContent=JSON.stringify(result,null,2);
}
document.getElementById('ratesUpsertForm').addEventListener('submit',e=>{e.preventDefault();postJson('/rates/upsert',Object.fromEntries(new FormData(e.target)));});
document.getElementById('ratesApproveForm').addEventListener('submit',e=>{e.preventDefault();postJson('/rates/approve',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection