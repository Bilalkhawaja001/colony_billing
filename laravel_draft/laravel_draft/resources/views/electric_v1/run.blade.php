@extends('layouts.app')
@section('page_title', 'Electric V1 Lab · Run')
@section('page_subtitle','Run isolated Electric V1 with clear input grouping and execution status.')
@section('content')
<style>.local-sticky{position:sticky;top:0;z-index:4;background:#fff;padding:10px;border:1px solid #e2e8f0;border-radius:10px}</style>
<div class="grid">
  <div class="col-12 card soft"><div class="toolbar local-sticky"><span class="badge">Electric V1 Execution</span></div></div>
  <div class="col-8 card">
    <h3 class="section-title">Run Parameters</h3>
    <form id="ev1RunForm" class="form-grid">
      <div class="field col-4"><label class="label">Cycle Start</label><input id="cycle_start" type="date" required></div>
      <div class="field col-4"><label class="label">Cycle End</label><input id="cycle_end" type="date" required></div>
      <div class="field col-4"><label class="label">Flat Rate</label><input id="flat_rate" type="number" step="any" placeholder="Flat Rate" required></div>
      <div class="col-12"><button class="btn btn-primary" type="submit">Run V1</button></div>
    </form>
  </div>
  <div class="col-4 card">
    <h3 class="section-title">Execution Notes</h3>
    <div class="muted">Isolated V1 execution endpoint. Legacy billing routes are not used.</div>
  </div>
  <div class="col-12 card">
    <h3 class="section-title">Execution Status</h3>
    <div id="ev1Status" class="banner">Ready.</div>
    <details style="margin-top:8px"><summary class="muted">Technical response</summary><pre id="out" style="margin-top:8px">{}</pre></details>
  </div>
</div>
<script>
function setStatus(ok,text){const el=document.getElementById('ev1Status'); el.className=ok?'banner':'alert'; el.textContent=text;}
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
  setStatus(r.ok, r.ok?'Run completed.':'Run failed.');
});
</script>
@endsection
