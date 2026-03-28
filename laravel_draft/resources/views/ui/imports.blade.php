@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Imports Workspace</h3>
  <p>Ingest preview + validation token flow.</p>
  <form id="ingestPreviewForm">
    <input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}">
    <input name="rows_received" placeholder="Rows Received" value="10">
    <button type="submit">Ingest Preview</button>
  </form>
  <br>
  <form id="markValidatedForm">
    <input name="token" placeholder="Validation Token">
    <button type="submit">Mark Validated</button>
  </form>
  <p>Download error report via: <code>/imports/error-report/{token}</code></p>
  <pre id="importsResult">Ready.</pre>
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