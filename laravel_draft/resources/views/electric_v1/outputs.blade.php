@extends('layouts.app')
@section('page_title', 'Electric V1 Lab · Outputs')
@section('content')
<div class="card">
    <h3>ElectricBillingV1 Outputs</h3>
    <form id="ev1OutForm" class="actions">
        <input id="o_cycle_start" type="date" class="btn" required>
        <input id="o_cycle_end" type="date" class="btn" required>
        <input id="o_run_id" class="btn" placeholder="Run ID (optional)">
        <button class="btn btn-primary" type="submit">Fetch Bundle</button>
    </form>
    <pre id="bundle" class="muted" style="margin-top:12px"></pre>
</div>
<script>
document.getElementById('ev1OutForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const q=new URLSearchParams({cycle_start:document.getElementById('o_cycle_start').value,cycle_end:document.getElementById('o_cycle_end').value});
  const rid=document.getElementById('o_run_id').value.trim();
  if(rid) q.set('run_id',rid);
  const r=await fetch('/api/electric-v1/outputs?'+q.toString());
  const j=await r.json();
  document.getElementById('bundle').textContent=JSON.stringify(j,null,2);
});
</script>
@endsection
