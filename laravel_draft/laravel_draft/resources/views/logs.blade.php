@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Logs Workspace</h3>
  <form id="logsForm"><input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY"><button type="button" id="logsLoad">Load</button></form>
  <pre id="logsResult">Ready.</pre>
</div>
<script>
async function getJson(url){const r=await fetch(url);const j=await r.json().catch(()=>({}));document.getElementById('logsResult').textContent=JSON.stringify({status:r.status,body:j},null,2);} 
document.getElementById('logsLoad').onclick=()=>{const m=encodeURIComponent(new FormData(document.getElementById('logsForm')).get('month_cycle')||'');getJson('/api/logs?month_cycle='+m);};
</script>
@endsection