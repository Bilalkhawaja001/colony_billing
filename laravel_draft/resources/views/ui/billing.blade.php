@extends('layouts.app')
@section('page_title','Billing Workspace')
@section('page_subtitle','Run billing cycles, lock approved runs, and open downstream report exports from one control surface.')
@section('content')
<div class="grid">
  <div class="col-7 card">
    <h3 class="section-title">Run Billing</h3>
    <form id="billingRunForm" class="form-grid">
      <div class="field col-6"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY or YYYY-MM" value="{{ $monthCycle }}"></div>
      <div class="field col-6"><label class="label">Run Key</label><input name="run_key" placeholder="run key" value="UI-RUN-1"></div>
      <div class="col-12"><button class="btn btn-primary" type="submit">Run Billing</button></div>
    </form>
  </div>

  <div class="col-5 card">
    <h3 class="section-title">Lock Run</h3>
    <form id="billingLockForm" class="form-grid">
      <div class="field col-12"><label class="label">Run ID</label><input name="run_id" placeholder="run id"></div>
      <div class="col-12"><button class="btn btn-warn" type="submit">Lock Approved Run</button></div>
    </form>
    <div class="muted" style="margin-top:10px">Use only after validation and approval checks.</div>
  </div>

  <div class="col-12 card soft">
    <h3 class="section-title">Report + Export Shortcuts</h3>
    <div class="toolbar">
      <a class="btn" href="#" id="summaryLink">Monthly Summary JSON</a>
      <a class="btn" href="#" id="excelLink">Export Excel</a>
      <a class="btn" href="#" id="pdfLink">Export PDF</a>
      <a class="btn" href="/ui/reconciliation?month_cycle={{ urlencode((string)$monthCycle) }}">Open Reconciliation</a>
    </div>
  </div>

  <div class="col-12 card">
    <h3 class="section-title">Execution Result</h3>
    <pre id="billingResult">Ready.</pre>
  </div>
</div>
<script>
const csrf=@json(csrf_token());
function mc(){return (new FormData(document.getElementById('billingRunForm')).get('month_cycle')||'').toString();}
function setLinks(){
 const m=encodeURIComponent(mc());
 document.getElementById('summaryLink').href='/reports/monthly-summary?month_cycle='+m;
 document.getElementById('excelLink').href='/export/excel/monthly-summary?month_cycle='+m;
 document.getElementById('pdfLink').href='/export/pdf/monthly-summary?month_cycle='+m;
}
setLinks();
document.getElementById('billingRunForm').addEventListener('input',setLinks);
async function postJson(url,payload){
 const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});
 const j=await r.json().catch(()=>({raw:'non-json'}));
 document.getElementById('billingResult').textContent=JSON.stringify({status:r.status,body:j},null,2);
 if(url==='/billing/run' && j.run_id){
   document.querySelector('#billingLockForm input[name="run_id"]').value=j.run_id;
 }
}
document.getElementById('billingRunForm').addEventListener('submit',e=>{e.preventDefault();postJson('/billing/run',Object.fromEntries(new FormData(e.target)));});
document.getElementById('billingLockForm').addEventListener('submit',e=>{e.preventDefault();postJson('/billing/lock',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection