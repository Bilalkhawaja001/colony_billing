@extends('layouts.app')
@section('page_title','Unit Directory')
@section('page_subtitle','Unit CRUD-lite operations with CSV bulk upsert and template download support.')
@section('content')
<div class="grid">
<div class="col-7 card">
  <h3 class="section-title">Single Upsert</h3>
  <form id="unitUpsertForm" class="form-grid">
    <div class="field col-6"><label class="label">Unit ID</label><input name="unit_id" placeholder="U-001"></div>
    <div class="field col-6"><label class="label">Unit Name</label><input name="unit_name" placeholder="Unit Name"></div>
    <div class="col-12"><button class="btn btn-primary" type="submit">Save Unit</button></div>
  </form>
</div>
<div class="col-5 card">
  <h3 class="section-title">CSV Bulk Upload</h3>
  <div class="muted" style="margin-bottom:8px">Header: <code>unit_id,unit_name</code></div>
  <div class="toolbar">
    <button class="btn" type="button" id="downloadUnitTemplate">Download Template</button>
    <input type="file" id="unitCsvFile" accept=".csv,text/csv">
    <button class="btn btn-primary" type="button" id="importUnitCsv">Import CSV</button>
  </div>
</div>

<div class="col-12 card">
  <h3 class="section-title">Unit Listing</h3>
  <div class="toolbar" style="margin-bottom:10px"><button class="btn" type="button" id="loadUnitsBtn">Reload Units</button></div>
  <table>
    <thead><tr><th>Unit ID</th><th>Name</th></tr></thead>
    <tbody id="unitRows"><tr><td colspan="2"><div class="empty">No rows loaded.</div></td></tr></tbody>
  </table>
</div>
<div class="col-12 card"><h3 class="section-title">API Result</h3><pre id="unitResult">Ready.</pre></div>
</div>
<script>
const csrf=@json(csrf_token());
const out=document.getElementById('unitResult');
const rowsEl=document.getElementById('unitRows');
function show(v){out.textContent=JSON.stringify(v,null,2)}
function parseCsv(t){const lines=t.split(/\r?\n/).map(s=>s.trim()).filter(Boolean); if(lines.length<2)return []; const h=lines[0].split(',').map(s=>s.trim()); return lines.slice(1).map(l=>{const c=l.split(',').map(s=>s.trim()); return Object.fromEntries(h.map((k,i)=>[k,c[i]??'']));});}
function download(name,c){const b=new Blob([c],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(b);a.download=name;a.click();URL.revokeObjectURL(a.href);} 
async function req(url,method='GET',payload=null){const o={method,headers:{'X-CSRF-TOKEN':csrf}};if(payload){o.headers['Content-Type']='application/json';o.body=JSON.stringify(payload);} const r=await fetch(url,o);const j=await r.json().catch(()=>({raw:'non-json'}));const v={status:r.status,body:j};show(v);return v;}
function render(rows){if(!Array.isArray(rows)||rows.length===0){rowsEl.innerHTML='<tr><td colspan="2"><div class="empty">No rows found.</div></td></tr>';return;} rowsEl.innerHTML=rows.map(r=>`<tr><td>${r.unit_id??''}</td><td>${r.unit_name??r.name??''}</td></tr>`).join('');}

document.getElementById('unitUpsertForm').addEventListener('submit',e=>{e.preventDefault();req('/units/upsert','POST',Object.fromEntries(new FormData(e.target)));});
document.getElementById('downloadUnitTemplate').onclick=()=>download('unit_master_template.csv','unit_id,unit_name\nU-001,Unit 1\n');
document.getElementById('importUnitCsv').onclick=async()=>{const f=document.getElementById('unitCsvFile').files[0]; if(!f)return show({status:400,error:'Select CSV file'}); const rows=parseCsv(await f.text()); if(rows.length===0)return show({status:400,error:'No data rows'}); let ok=0,fail=0,errors=[]; for(let i=0;i<rows.length;i++){const r=await req('/units/upsert','POST',{unit_id:rows[i].unit_id,unit_name:rows[i].unit_name}); if(r.status>=200&&r.status<300)ok++; else {fail++;errors.push({line:i+2,row:rows[i],response:r});}} show({status:'done',processed:rows.length,ok,fail,errors});};
document.getElementById('loadUnitsBtn').onclick=async()=>{const r=await req('/units'); render(r.body?.rows||[]);};
</script>
@endsection