@extends('layouts.app')
@section('page_title','Housing & Occupancy · Occupancy')
@section('page_subtitle','Month occupancy with CSV bulk upload, context autofill, listing filters and row delete controls.')
@section('content')
<style>.local-sticky{position:sticky;top:0;z-index:4;background:#fff;padding:10px;border:1px solid #e2e8f0;border-radius:10px}</style>
<div class="grid">
<div class="col-12 card soft"><div class="toolbar local-sticky"><span class="badge">Occupancy Control</span><button class="btn" type="button" id="loadOccBtn">Reload</button></div></div>

<div class="col-8 card">
  <h3 class="section-title">Single Upsert</h3>
  <form id="occUpsertForm" class="form-grid">
    <div class="field col-3"><label class="label">Month Cycle *</label><input name="month_cycle" placeholder="MM-YYYY"></div>
    <div class="field col-3"><label class="label">Category *</label><select name="category"><option>Family A+</option><option>Family A</option><option>Family B</option><option>Family C</option><option>Container</option><option>Hostel</option><option>Bachelor</option></select></div>
    <div class="field col-3"><label class="label">Unit ID *</label><input name="unit_id" placeholder="U-001"></div>
    <div class="field col-3"><label class="label">Room No *</label><input name="room_no" placeholder="R-01"></div>
    <div class="field col-4"><label class="label">Employee ID *</label><input name="employee_id" placeholder="E1001"></div>
    <div class="field col-4"><label class="label">Block Floor</label><input name="block_floor" placeholder="Block A"></div>
    <div class="field col-4"><label class="label">Active Days</label><input name="active_days" value="30"></div>
    <div class="col-12"><button class="btn btn-primary" type="submit">Upsert Occupancy</button></div>
  </form>
</div>
<div class="col-4 card">
  <h3 class="section-title">CSV Bulk + Helpers</h3>
  <div class="muted" style="margin-bottom:8px">Header: <code>month_cycle,category,unit_id,room_no,employee_id,block_floor,active_days</code></div>
  <div class="toolbar" style="margin-bottom:8px"><button class="btn" type="button" id="downloadOccTemplate">Download Template</button></div>
  <div class="toolbar" style="margin-bottom:8px"><input type="file" id="occCsvFile" accept=".csv,text/csv"><button class="btn btn-success" type="button" id="importOccCsv">Import Occupancy CSV</button></div>
  <div class="toolbar"><input id="autofillMonth" placeholder="MM-YYYY"><button class="btn" type="button" id="autofillBtn">Autofill Month</button></div>
</div>

<div class="col-12 card">
  <h3 class="section-title">Occupancy Listing</h3>
  <div class="toolbar" style="margin-bottom:10px"><input id="occMonth" placeholder="month_cycle filter"><input id="occUnit" placeholder="unit_id filter"></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>ID</th><th>Month</th><th>Unit</th><th>Room</th><th>Employee</th><th>Days</th><th>Action</th></tr></thead>
      <tbody id="occRows"><tr><td colspan="7"><div class="empty">No rows loaded.</div></td></tr></tbody>
    </table>
  </div>
</div>
<div class="col-12 card"><h3 class="section-title">Operation Status</h3><div id="occStatus" class="banner">Ready.</div><details style="margin-top:8px"><summary class="muted">Technical response</summary><pre id="occResult" style="margin-top:8px">{}</pre></details></div>
</div>
<script>
const csrf=@json(csrf_token());
const out=document.getElementById('occResult');
const rowsEl=document.getElementById('occRows');
function setStatus(ok,text){const el=document.getElementById('occStatus'); el.className=ok?'banner':'alert'; el.textContent=text;}
function show(v){out.textContent=JSON.stringify(v,null,2); const ok=(v?.status>=200&&v?.status<300)||v?.status==='done'; setStatus(ok, ok?'Completed successfully.':'Action failed.');}
function parseCsv(t){const l=t.split(/\r?\n/).map(s=>s.trim()).filter(Boolean); if(l.length<2)return []; const h=l[0].split(',').map(s=>s.trim()); return l.slice(1).map(x=>{const c=x.split(',').map(s=>s.trim()); return Object.fromEntries(h.map((k,i)=>[k,c[i]??'']));});}
function download(name,c){const b=new Blob([c],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(b);a.download=name;a.click();URL.revokeObjectURL(a.href);} 
async function req(url,method='GET',payload=null){const o={method,headers:{'X-CSRF-TOKEN':csrf}}; if(payload!==null){o.headers['Content-Type']='application/json';o.body=JSON.stringify(payload);} const r=await fetch(url,o); const j=await r.json().catch(()=>({raw:'non-json'})); const v={status:r.status,body:j}; show(v); return v;}
function render(rows){if(!Array.isArray(rows)||rows.length===0){rowsEl.innerHTML='<tr><td colspan="7"><div class="empty">No rows found.</div></td></tr>';return;} rowsEl.innerHTML=rows.map(r=>`<tr><td>${r.id??''}</td><td>${r.month_cycle??''}</td><td>${r.unit_id??''}</td><td>${r.room_no??''}</td><td>${r.employee_id??''}</td><td>${r.active_days??''}</td><td><button class="btn btn-danger" data-id="${r.id}">Delete</button></td></tr>`).join('');}

document.getElementById('occUpsertForm').addEventListener('submit',async e=>{e.preventDefault(); await req('/occupancy/upsert','POST',Object.fromEntries(new FormData(e.target))); document.getElementById('loadOccBtn').click();});
document.getElementById('downloadOccTemplate').onclick=()=>download('occupancy_template.csv','month_cycle,category,unit_id,room_no,employee_id,block_floor,active_days\n03-2027,Family A,U-001,R-01,E1001,Block A,30\n');
document.getElementById('importOccCsv').onclick=async()=>{const f=document.getElementById('occCsvFile').files[0]; if(!f)return show({status:400,error:'Select CSV file'}); const rows=parseCsv(await f.text()); if(rows.length===0)return show({status:400,error:'No data rows'}); let ok=0,fail=0,errors=[]; for(let i=0;i<rows.length;i++){const r=await req('/occupancy/upsert','POST',rows[i]); if(r.status>=200&&r.status<300&&r.body?.status==='ok')ok++; else {fail++;errors.push({line:i+2,row:rows[i],response:r});}} show({status:'done',processed:rows.length,ok,fail,errors}); document.getElementById('loadOccBtn').click();};
document.getElementById('autofillBtn').onclick=()=>req('/api/occupancy/autofill?month_cycle='+encodeURIComponent(document.getElementById('autofillMonth').value||''),'POST',{});
document.getElementById('loadOccBtn').onclick=async()=>{const m=encodeURIComponent(document.getElementById('occMonth').value||''); const u=encodeURIComponent(document.getElementById('occUnit').value||''); const r=await req('/occupancy?month_cycle='+m+'&unit_id='+u); render(r.body?.rows||[]);};
rowsEl.addEventListener('click',async e=>{const id=e.target?.dataset?.id; if(!id) return; await req('/occupancy/'+encodeURIComponent(id),'DELETE'); document.getElementById('loadOccBtn').click();});
</script>
@endsection
