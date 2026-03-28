@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Results Unit-Wise</h3>
  <form id="resUnitForm"><input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY"><button type="button" id="resUnitLoad">Load</button></form>
  <pre id="resUnitResult">Ready.</pre>
</div>
<script>
async function getJson(url){const r=await fetch(url);const j=await r.json().catch(()=>({}));document.getElementById('resUnitResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('resUnitLoad').onclick=()=>{const m=encodeURIComponent(new FormData(document.getElementById('resUnitForm')).get('month_cycle')||'');getJson('/api/results/unit-wise?month_cycle='+m);};
</script>
@endsection