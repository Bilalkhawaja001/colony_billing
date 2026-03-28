@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Van Module Workspace</h3>
  <form id="vanForm">
    <input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}">
    <button type="button" id="vanLoad">Load Van Report</button>
  </form>
  <p>Equivalent operator actions: report load + billing run dependency visibility.</p>
  <pre id="vanResult">Ready.</pre>
</div>
<script>
const f=()=>Object.fromEntries(new FormData(document.getElementById('vanForm')));
async function getJson(url){const r=await fetch(url);const j=await r.json().catch(()=>({}));return {status:r.status,body:j};}
function show(v){document.getElementById('vanResult').textContent=JSON.stringify(v,null,2)}
document.getElementById('vanLoad').onclick=async()=>{const m=encodeURIComponent(f().month_cycle||''); show(await getJson('/reports/van?month_cycle='+m));};
</script>
@endsection