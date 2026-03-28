@extends('layouts.app')
@section('page_title','Imports Workspace')
@section('page_subtitle','Ingest preview, validation-token control, and error-loop handling for meter import readiness.')
@section('content')
<div class="grid">
  <div class="col-7 card">
    <h3 class="section-title">Ingest Preview</h3>
    <form id="ingestPreviewForm" class="form-grid">
      <div class="field col-6"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}"></div>
      <div class="field col-6"><label class="label">Rows Received</label><input name="rows_received" placeholder="Rows Received" value="10"></div>
      <div class="col-12"><button class="btn btn-primary" type="submit">Generate Preview Token</button></div>
    </form>
  </div>
  <div class="col-5 card">
    <h3 class="section-title">Mark Validated</h3>
    <form id="markValidatedForm" class="form-grid">
      <div class="field col-12"><label class="label">Validation Token</label><input name="token" placeholder="Validation Token"></div>
      <div class="col-12"><button class="btn btn-success" type="submit">Mark Validated</button></div>
    </form>
    <div class="muted" style="margin-top:10px">Error report endpoint: <code>/imports/error-report/{token}</code></div>
  </div>
  <div class="col-12 card">
    <h3 class="section-title">Import Operation Log</h3>
    <pre id="importsResult">Ready.</pre>
  </div>
</div>
<script>
const csrf=@json(csrf_token());
async function postJson(url,payload){
 const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});
 const j=await r.json().catch(()=>({raw:'non-json'}));
 document.getElementById('importsResult').textContent=JSON.stringify({status:r.status,body:j},null,2);
}
document.getElementById('ingestPreviewForm').addEventListener('submit',e=>{e.preventDefault();postJson('/imports/meter-register/ingest-preview',Object.fromEntries(new FormData(e.target)));});
document.getElementById('markValidatedForm').addEventListener('submit',e=>{e.preventDefault();postJson('/imports/mark-validated',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection