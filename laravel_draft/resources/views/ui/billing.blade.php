@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Billing Workspace</h3>
  <p>Run + lock + summary/export operator actions.</p>

  <form id="billingRunForm">
    <input name="month_cycle" placeholder="MM-YYYY or YYYY-MM" value="{{ $monthCycle }}">
    <input name="run_key" placeholder="run key" value="UI-RUN-1">
    <button type="submit">Run Billing</button>
  </form>
  <br>
  <form id="billingLockForm">
    <input name="run_id" placeholder="run id">
    <button type="submit">Lock Run</button>
  </form>

  <div style="margin-top:10px;">
    <a href="#" id="summaryLink">Monthly Summary</a> |
    <a href="#" id="excelLink">Export Excel</a> |
    <a href="#" id="pdfLink">Export PDF</a>
  </div>

  <pre id="billingResult">Ready.</pre>
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