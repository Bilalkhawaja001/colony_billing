@extends('layouts.app')
@section('page_title','Employee Helper')
@section('page_subtitle','Registry employee helper: upsert/get/import preview/commit/promote with CSV bulk flow.')
@section('content')
<div class="grid">
  <div class="col-7 card">
    <h3 class="section-title">Registry Upsert / Get</h3>
    <div class="form-grid">
      <div class="field col-4"><label class="label">Company ID</label><input id="regCompanyId" placeholder="E1001"></div>
      <div class="field col-4"><label class="label">Name</label><input id="regName" placeholder="Employee Name"></div>
      <div class="field col-4"><label class="label">Unit ID</label><input id="regUnit" placeholder="U-01"></div>
      <div class="toolbar col-12">
        <button class="btn btn-primary" type="button" id="regUpsertBtn">Registry Upsert</button>
        <button class="btn" type="button" id="regGetBtn">Registry Get</button>
      </div>
    </div>
  </div>

  <div class="col-5 card">
    <h3 class="section-title">Registry CSV Bulk</h3>
    <div class="muted" style="margin-bottom:8px">Paste or upload CSV text for preview/commit.</div>
    <div class="form-grid">
      <div class="field col-12"><label class="label">CSV File</label><input type="file" id="regCsvFile" accept=".csv,text/csv"></div>
      <div class="field col-12"><label class="label">CSV Text</label><textarea id="regCsvText" rows="6" placeholder="company_id,name,unit_id,..."></textarea></div>
      <div class="toolbar col-12">
        <button class="btn" type="button" id="regPreviewBtn">Import Preview</button>
        <button class="btn btn-success" type="button" id="regCommitBtn">Import Commit</button>
      </div>
    </div>
  </div>

  <div class="col-12 card soft">
    <h3 class="section-title">Promote Registry → Master</h3>
    <div class="toolbar">
      <button class="btn btn-warn" type="button" id="promoteBtn">Promote (Upsert=true)</button>
    </div>
  </div>

  <div class="col-12 card">
    <h3 class="section-title">API Result</h3>
    <pre id="helperResult">Ready.</pre>
  </div>
</div>

<script>
const csrf=@json(csrf_token());
const out=document.getElementById('helperResult');
function show(v){out.textContent=JSON.stringify(v,null,2)}
async function req(url,method='GET',payload=null){
  const opts={method,headers:{'X-CSRF-TOKEN':csrf}};
  if(payload!==null){opts.headers['Content-Type']='application/json';opts.body=JSON.stringify(payload)}
  const r=await fetch(url,opts);const j=await r.json().catch(()=>({raw:'non-json'}));
  const data={status:r.status,body:j};show(data);return data;
}
async function getCsv(){
  const f=document.getElementById('regCsvFile').files[0];
  let t=(document.getElementById('regCsvText').value||'').trim();
  if(f){t=await f.text();document.getElementById('regCsvText').value=t;}
  return t;
}

document.getElementById('regUpsertBtn').onclick=()=>req('/registry/employees/upsert','POST',{
  company_id:document.getElementById('regCompanyId').value,
  name:document.getElementById('regName').value,
  unit_id:document.getElementById('regUnit').value
});
document.getElementById('regGetBtn').onclick=()=>req('/registry/employees/'+encodeURIComponent(document.getElementById('regCompanyId').value||''));
document.getElementById('regPreviewBtn').onclick=async()=>{const t=await getCsv(); if(!t)return show({status:400,body:{error:'csv required'}}); req('/registry/employees/import-preview','POST',{csv_text:t});};
document.getElementById('regCommitBtn').onclick=async()=>{const t=await getCsv(); if(!t)return show({status:400,body:{error:'csv required'}}); req('/registry/employees/import-commit','POST',{csv_text:t});};
document.getElementById('promoteBtn').onclick=()=>req('/registry/employees/promote-to-master','POST',{upsert:true});
</script>
@endsection