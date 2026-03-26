@extends('layouts.app')
@section('page_title', 'Electric V1 Run')
@section('content')
<div class="card">
    <h3>ElectricBillingV1 Run</h3>
    <p class="muted">Isolated V1 execution endpoint. Legacy billing routes are not used.</p>
    <form id="ev1RunForm" class="actions">
        <input id="cycle_start" type="date" class="btn" required>
        <input id="cycle_end" type="date" class="btn" required>
        <input id="flat_rate" type="number" step="any" class="btn" placeholder="Flat Rate" required>
        <button class="btn btn-primary" type="submit">Run V1</button>
    </form>
    <pre id="out" class="muted" style="margin-top:12px"></pre>
</div>
<script>
document.getElementById('ev1RunForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const payload={
    cycle_start:document.getElementById('cycle_start').value,
    cycle_end:document.getElementById('cycle_end').value,
    flat_rate:document.getElementById('flat_rate').value
  };
  const r=await fetch('/api/electric-v1/run',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
  const j=await r.json();
  document.getElementById('out').textContent=JSON.stringify(j,null,2);
});
</script>
@endsection
