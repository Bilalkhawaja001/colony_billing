@extends('layouts.app')
@section('page_title','Inputs RO')
@section('page_subtitle','RO + water allocation operator console with preview and adjustment write actions.')
@section('content')
<div class="grid">
<div class="col-6 card">
  <h3 class="section-title">Allocation Preview</h3>
  <div class="form-grid">
    <div class="field col-8"><label class="label">Month Cycle</label><input id="roMonth" placeholder="MM-YYYY"></div>
    <div class="col-4" style="display:flex;align-items:flex-end"><button class="btn" type="button" id="roPreviewBtn">Load Preview</button></div>
  </div>
</div>
<div class="col-6 card">
  <h3 class="section-title">Zone Adjustment Upsert</h3>
  <form id="roAdjustForm" class="form-grid">
    <div class="field col-6"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY"></div>
    <div class="field col-6"><label class="label">Zone Code</label><input name="water_zone" placeholder="FAMILY_METER"></div>
    <div class="field col-6"><label class="label">Raw Liters</label><input name="raw_liters" value="0"></div>
    <div class="field col-6"><label class="label">Common Use Liters</label><input name="common_use_liters" value="0"></div>
    <div class="col-12"><button class="btn btn-primary" type="submit">Upsert Adjustment</button></div>
  </form>
</div>
<div class="col-12 card soft"><a class="btn" href="/ui/water-meters">Open Full Water Meters Workspace</a></div>
<div class="col-12 card"><h3 class="section-title">Result</h3><pre id="roResult">Ready.</pre></div>
</div>
<script>
const csrf=@json(csrf_token());
const out=document.getElementById('roResult');
function show(v){out.textContent=JSON.stringify(v,null,2)}
async function req(url,method='GET',payload=null){const o={method,headers:{'X-CSRF-TOKEN':csrf}}; if(payload!==null){o.headers['Content-Type']='application/json';o.body=JSON.stringify(payload);} const r=await fetch(url,o); const j=await r.json().catch(()=>({raw:'non-json'})); show({status:r.status,body:j});}
document.getElementById('roPreviewBtn').onclick=()=>req('/api/water/allocation-preview?month_cycle='+encodeURIComponent(document.getElementById('roMonth').value||''));
document.getElementById('roAdjustForm').addEventListener('submit',e=>{e.preventDefault();const x=Object.fromEntries(new FormData(e.target)); req('/api/water/zone-adjustments','POST',{month_cycle:x.month_cycle,rows:[{water_zone:x.water_zone,raw_liters:Number(x.raw_liters||0),common_use_liters:Number(x.common_use_liters||0)]});});
</script>
@endsection