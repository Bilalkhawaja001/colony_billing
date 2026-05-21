@extends('layouts.app')
@section('page_title','Billing Run & Lock')
@section('page_subtitle','Run billing cycles, lock approved runs, and open downstream report exports from one control surface.')
@section('content')

<div class="grid">
  <div class="col-12 card soft">
    <div class="toolbar sticky-actions">
      <span class="badge">Run + Lock Control</span>
      <a class="btn" href="/reporting?month_cycle={{ urlencode((string)$monthCycle) }}">Open Reporting Center</a>
    </div>
  </div>

  <div class="col-7 card">
    <h3 class="section-title">Run Billing</h3>
    <form id="billingRunForm" class="form-grid">
      <div class="field col-3"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY or YYYY-MM" value="{{ $monthCycle }}" required></div>
      <div class="field col-3"><label class="label">Cycle Start Date</label><input name="cycle_start_date" type="date" required></div>
      <div class="field col-3"><label class="label">Cycle End Date</label><input name="cycle_end_date" type="date" required></div>
      <div class="field col-3"><label class="label">Run Key</label><input name="run_key" placeholder="run key" value="UI-RUN-1"></div>
      <div class="col-12">
        <button class="btn btn-primary" type="submit">Run Billing</button>
        <span class="muted" style="margin-left:8px">Cycle dates are required before billing generation.</span>
      </div>
    </form>
  </div>

  <div class="col-5 card">
    <h3 class="section-title">Lock Run</h3>
    <form id="billingLockForm" class="form-grid">
      <div class="field col-12"><label class="label">Run ID</label><input name="run_id" placeholder="run id"></div>
      <div class="col-12"><button class="btn btn-warn" type="submit">Lock Approved Run</button></div>
    </form>
    <div class="alert" style="margin-top:10px">Use lock only after validation + approval checks.</div>
  </div>

  <div class="col-12 card">
    <h3 class="section-title">Report + Export Shortcuts</h3>
    <div class="toolbar">
      <a class="btn" href="#" id="summaryLink">Monthly Summary JSON</a>
      <a class="btn" href="#" id="excelLink">Export Excel</a>
      <a class="btn" href="#" id="pdfLink">Export PDF</a>
      <a class="btn" href="/reporting?month_cycle={{ urlencode((string)$monthCycle) }}">Open Reconciliation</a>
    </div>
  </div>

  <div class="col-12 card soft">
    <h3 class="section-title">Execution Status</h3>
    <div id="billingStatus" class="banner">Ready for controlled billing run.</div>
    <details style="margin-top:10px">
      <summary class="muted">Technical response</summary>
      <pre id="billingResult" style="margin-top:8px">{}</pre>
    </details>
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
function setStatus(ok,text){const el=document.getElementById('billingStatus'); el.className=ok?'banner':'alert'; el.textContent=text;}
async function postJson(url,payload){
 const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});
 const j=await r.json().catch(()=>({raw:'non-json'}));
 document.getElementById('billingResult').textContent=JSON.stringify({status:r.status,body:j},null,2);
 setStatus(r.ok, r.ok?`Completed: ${url}`:`Failed: ${url} (${r.status})`);
 if(url==='/billing/run' && j.run_id){
   document.querySelector('#billingLockForm input[name="run_id"]').value=j.run_id;
 }
}
document.getElementById('billingRunForm').addEventListener('submit',e=>{
  e.preventDefault();
  const payload=Object.fromEntries(new FormData(e.target));
  if(!payload.cycle_start_date || !payload.cycle_end_date){
    setStatus(false,'Cycle Start Date and Cycle End Date are required before billing generation.');
    return;
  }
  if(payload.cycle_end_date < payload.cycle_start_date){
    setStatus(false,'Cycle End Date cannot be before Cycle Start Date.');
    return;
  }
  postJson('/billing/run',payload);
});
document.getElementById('billingLockForm').addEventListener('submit',e=>{e.preventDefault();postJson('/billing/lock',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection
