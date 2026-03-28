@extends('layouts.app')
@section('page_title','Inputs Readings')
@section('page_subtitle','Operator reading console: latest lookup + quick upsert + meter master jump.')
@section('content')
<div class="grid">
<div class="col-6 card">
  <h3 class="section-title">Latest Reading Lookup</h3>
  <div class="form-grid">
    <div class="field col-8"><label class="label">Unit ID</label><input id="latestUnit" placeholder="U-001"></div>
    <div class="col-4" style="display:flex;align-items:flex-end"><button class="btn" type="button" id="latestBtn">Get Latest</button></div>
  </div>
</div>
<div class="col-6 card">
  <h3 class="section-title">Quick Upsert</h3>
  <form id="quickReadingForm" class="form-grid">
    <div class="field col-6"><label class="label">Meter ID</label><input name="meter_id"></div>
    <div class="field col-6"><label class="label">Unit ID</label><input name="unit_id"></div>
    <div class="field col-6"><label class="label">Reading Date</label><input name="reading_date" placeholder="YYYY-MM-DD"></div>
    <div class="field col-6"><label class="label">Reading Value</label><input name="reading_value"></div>
    <div class="col-12"><button class="btn btn-primary" type="submit">Upsert Reading</button></div>
  </form>
</div>
<div class="col-12 card soft"><a class="btn" href="/ui/meter-master">Open Full Meter Master Workspace</a></div>
<div class="col-12 card"><h3 class="section-title">Result</h3><pre id="readingsResult">Ready.</pre></div>
</div>
<script>
const csrf=@json(csrf_token());
const out=document.getElementById('readingsResult');
function show(v){out.textContent=JSON.stringify(v,null,2)}
async function req(url,method='GET',payload=null){const o={method,headers:{'X-CSRF-TOKEN':csrf}}; if(payload!==null){o.headers['Content-Type']='application/json';o.body=JSON.stringify(payload);} const r=await fetch(url,o); const j=await r.json().catch(()=>({raw:'non-json'})); show({status:r.status,body:j});}
document.getElementById('latestBtn').onclick=()=>req('/meter-reading/latest/'+encodeURIComponent(document.getElementById('latestUnit').value||''));
document.getElementById('quickReadingForm').addEventListener('submit',e=>{e.preventDefault();req('/meter-reading/upsert','POST',Object.fromEntries(new FormData(e.target)));});
</script>
@endsection