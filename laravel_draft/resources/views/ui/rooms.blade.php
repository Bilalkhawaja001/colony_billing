@extends('layouts.app')
@section('page_title','Housing & Occupancy · Rooms')
@section('page_subtitle','Room snapshot operations with CSV bulk upload, template download, list/filter and row delete controls.')
@section('content')
<style>.local-sticky{position:sticky;top:0;z-index:4;background:#fff;padding:10px;border:1px solid #e2e8f0;border-radius:10px}</style>
<div class="grid">
<div class="col-12 card soft"><div class="toolbar local-sticky"><span class="badge">Rooms Control</span><button class="btn" type="button" id="loadRoomsBtn">Reload</button></div></div>

<div class="col-7 card">
  <h3 class="section-title">Single Upsert</h3>
  <form id="roomUpsertForm" class="form-grid">
    <div class="field col-3"><label class="label">Month Cycle *</label><input name="month_cycle" placeholder="MM-YYYY"></div>
    <div class="field col-3"><label class="label">Unit ID *</label><input name="unit_id" placeholder="U-001"></div>
    <div class="field col-3"><label class="label">Category *</label><select name="category"><option>Family A+</option><option>Family A</option><option>Family B</option><option>Family C</option><option>Container</option><option>Hostel</option><option>Bachelor</option></select></div>
    <div class="field col-3"><label class="label">Room No *</label><input name="room_no" placeholder="R-01"></div>
    <div class="field col-6"><label class="label">Block Floor</label><input name="block_floor" placeholder="Block A / 1st"></div>
    <div class="col-12"><button class="btn btn-primary" type="submit">Upsert Room</button></div>
  </form>
</div>
<div class="col-5 card">
  <h3 class="section-title">CSV Bulk</h3>
  <div class="muted" style="margin-bottom:8px">Header: <code>month_cycle,unit_id,category,room_no,block_floor</code></div>
  <div class="toolbar">
    <button class="btn" type="button" id="downloadRoomsTemplate">Download Template</button>
    <input type="file" id="roomsCsvFile" accept=".csv,text/csv">
    <button class="btn btn-success" type="button" id="importRoomsCsv">Import Rooms CSV</button>
  </div>
</div>

<div class="col-12 card">
  <h3 class="section-title">Rooms Listing</h3>
  <div class="toolbar" style="margin-bottom:10px">
    <input id="roomsMonth" placeholder="month_cycle filter">
    <input id="roomsUnit" placeholder="unit_id filter">
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>ID</th><th>Month</th><th>Unit</th><th>Category</th><th>Room</th><th>Block</th><th>Action</th></tr></thead>
      <tbody id="roomsRows"><tr><td colspan="7"><div class="empty">No rows loaded.</div></td></tr></tbody>
    </table>
  </div>
</div>

<div class="col-12 card"><h3 class="section-title">Operation Status</h3><div id="roomStatus" class="banner">Ready.</div><details style="margin-top:8px"><summary class="muted">Technical response</summary><pre id="roomResult" style="margin-top:8px">{}</pre></details></div>
</div>
<script>
const csrf=@json(csrf_token());
const out=document.getElementById('roomResult');
const rowsEl=document.getElementById('roomsRows');
function setStatus(ok,text){const el=document.getElementById('roomStatus'); el.className=ok?'banner':'alert'; el.textContent=text;}
function show(v){out.textContent=JSON.stringify(v,null,2); const ok=(v?.status>=200&&v?.status<300)||v?.status==='done'; setStatus(ok, ok?'Completed successfully.':'Action failed.');}
function download(name,c){const b=new Blob([c],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(b);a.download=name;a.click();URL.revokeObjectURL(a.href);} 
function parseCsv(t){const l=t.split(/\r?\n/).map(s=>s.trim()).filter(Boolean); if(l.length<2)return []; const h=l[0].split(',').map(s=>s.trim()); return l.slice(1).map(x=>{const c=x.split(',').map(s=>s.trim()); return Object.fromEntries(h.map((k,i)=>[k,c[i]??'']));});}
async function req(url,method='GET',payload=null){const o={method,headers:{'X-CSRF-TOKEN':csrf}}; if(payload!==null){o.headers['Content-Type']='application/json';o.body=JSON.stringify(payload);} const r=await fetch(url,o);const j=await r.json().catch(()=>({raw:'non-json'}));const v={status:r.status,body:j};show(v);return v;}
function render(rows){ if(!Array.isArray(rows)||rows.length===0){rowsEl.innerHTML='<tr><td colspan="7"><div class="empty">No rows found.</div></td></tr>';return;} rowsEl.innerHTML=rows.map(r=>`<tr><td>${r.id??''}</td><td>${r.month_cycle??''}</td><td>${r.unit_id??''}</td><td>${r.category??''}</td><td>${r.room_no??''}</td><td>${r.block_floor??''}</td><td><button class="btn btn-danger" data-id="${r.id}">Delete</button></td></tr>`).join(''); }

document.getElementById('roomUpsertForm').addEventListener('submit',async e=>{e.preventDefault();await req('/rooms/upsert','POST',Object.fromEntries(new FormData(e.target))); document.getElementById('loadRoomsBtn').click();});
document.getElementById('downloadRoomsTemplate').onclick=()=>download('rooms_template.csv','month_cycle,unit_id,category,room_no,block_floor\n03-2027,U-001,Family A,R-01,Block A\n');
document.getElementById('importRoomsCsv').onclick=async()=>{const f=document.getElementById('roomsCsvFile').files[0]; if(!f)return show({status:400,error:'Select CSV file'}); const rows=parseCsv(await f.text()); if(rows.length===0)return show({status:400,error:'No data rows'}); let ok=0,fail=0,errors=[]; for(let i=0;i<rows.length;i++){const r=await req('/rooms/upsert','POST',rows[i]); if(r.status>=200&&r.status<300&&r.body?.status==='ok')ok++; else {fail++;errors.push({line:i+2,row:rows[i],response:r});}} show({status:'done',processed:rows.length,ok,fail,errors}); document.getElementById('loadRoomsBtn').click();};
document.getElementById('loadRoomsBtn').onclick=async()=>{const m=encodeURIComponent(document.getElementById('roomsMonth').value||''); const u=encodeURIComponent(document.getElementById('roomsUnit').value||''); const r=await req('/rooms?month_cycle='+m+'&unit_id='+u); render(r.body?.rows||[]);};
rowsEl.addEventListener('click',async e=>{const id=e.target?.dataset?.id; if(!id) return; await req('/rooms/'+encodeURIComponent(id),'DELETE'); document.getElementById('loadRoomsBtn').click();});
</script>
@endsection
