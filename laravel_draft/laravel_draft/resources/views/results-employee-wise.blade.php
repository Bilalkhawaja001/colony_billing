@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Results Employee-Wise</h3>
  <form id="resEmpForm"><input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY"><button type="button" id="resEmpLoad">Load</button></form>
  <pre id="resEmpResult">Ready.</pre>
</div>
<script>
async function getJson(url){const r=await fetch(url);const j=await r.json().catch(()=>({}));document.getElementById('resEmpResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('resEmpLoad').onclick=()=>{const m=encodeURIComponent(new FormData(document.getElementById('resEmpForm')).get('month_cycle')||'');getJson('/api/results/employee-wise?month_cycle='+m);};
</script>
@endsection