@extends('layouts.app')
@section('page_title','Van Module')
@section('page_subtitle','Inspect van report outputs with month-aware controls and downstream billing visibility.')
@section('content')
<div class="grid">
<div class="col-12 card">
  <h3 class="section-title">Van Report Loader</h3>
  <form id="vanForm" class="form-grid">
    <div class="field col-4"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}"></div>
    <div class="col-8" style="display:flex;align-items:flex-end"><button class="btn btn-primary" type="button" id="vanLoad">Load Van Report</button></div>
  </form>
</div>
<div class="col-12 card"><h3 class="section-title">Result</h3><pre id="vanResult">Ready.</pre></div>
</div>
<script>
const f=()=>Object.fromEntries(new FormData(document.getElementById('vanForm')));
async function getJson(url){const r=await fetch(url);const j=await r.json().catch(()=>({}));return {status:r.status,body:j};}
function show(v){document.getElementById('vanResult').textContent=JSON.stringify(v,null,2)}
document.getElementById('vanLoad').onclick=async()=>{const m=encodeURIComponent(f().month_cycle||''); show(await getJson('/reports/van?month_cycle='+m));};
</script>
@endsection