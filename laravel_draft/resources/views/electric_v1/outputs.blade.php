@extends('layouts.app')
@section('page_title', 'Electric V1 Lab · Outputs')
@section('page_subtitle','Fetch output bundle with cleaner controls and status feedback.')
@section('content')
<style>.local-sticky{position:sticky;top:0;z-index:4;background:#fff;padding:10px;border:1px solid #e2e8f0;border-radius:10px}</style>
<div class="grid">
  <div class="col-12 card soft"><div class="toolbar local-sticky"><span class="badge">Output Retrieval</span></div></div>
  <div class="col-8 card">
    <h3 class="section-title">Bundle Filters</h3>
    <form id="ev1OutForm" class="form-grid">
      <div class="field col-4"><label class="label">Cycle Start</label><input id="o_cycle_start" type="date" required></div>
      <div class="field col-4"><label class="label">Cycle End</label><input id="o_cycle_end" type="date" required></div>
      <div class="field col-4"><label class="label">Run ID (optional)</label><input id="o_run_id" placeholder="Run ID"></div>
      <div class="col-12"><button class="btn btn-primary" type="submit">Fetch Bundle</button></div>
    </form>
  </div>
  <div class="col-4 card">
    <h3 class="section-title">Tip</h3>
    <div class="muted">Use cycle dates first, then optional run_id for exact run bundle.</div>
  </div>
  <div class="col-12 card">
    <h3 class="section-title">Fetch Status</h3>
    <div id="ev1OutStatus" class="banner">Ready.</div>
    <details style="margin-top:8px"><summary class="muted">Technical response</summary><pre id="bundle" style="margin-top:8px">{}</pre></details>
  </div>
</div>
<script>
function setStatus(ok,text){const el=document.getElementById('ev1OutStatus'); el.className=ok?'banner':'alert'; el.textContent=text;}
document.getElementById('ev1OutForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const q=new URLSearchParams({cycle_start:document.getElementById('o_cycle_start').value,cycle_end:document.getElementById('o_cycle_end').value});
  const rid=document.getElementById('o_run_id').value.trim();
  if(rid) q.set('run_id',rid);
  const r=await fetch('/api/electric-v1/outputs?'+q.toString());
  const j=await r.json();
  document.getElementById('bundle').textContent=JSON.stringify(j,null,2);
  setStatus(r.ok, r.ok?'Bundle fetched.':'Fetch failed.');
});
</script>
@endsection
