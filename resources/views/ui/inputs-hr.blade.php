@extends('layouts.app')
@section('page_title','Inputs HR')
@section('page_subtitle','HR source ingestion controls with CSV upload/template download and employee listing feedback.')
@section('content')
<div class="grid">
  <div class="col-6 card">
    <h3 class="section-title">Active Days Monthly CSV Import</h3>
    <div class="muted" style="margin-bottom:8px">Header: <code>company_id,active_days</code></div>
    <div class="toolbar">
      <input type="text" id="hrMonthCycle" placeholder="MM-YYYY" value="{{ now()->format('m-Y') }}">
      <button class="btn" type="button" id="downloadHrTemplate">Download Template</button>
      <input type="file" id="hrCsvFile" accept=".csv,text/csv">
      <button class="btn" type="button" id="previewHrCsv">Preview CSV</button>
      <button class="btn btn-primary" type="button" id="importHrCsv">Commit Valid Rows</button>
    </div>
  </div>
  <div class="col-6 card">
    <h3 class="section-title">Quick Links</h3>
    <div class="toolbar">
      <a class="btn" href="/people-residency">Open Employee Master</a>
      <a class="btn" href="/people-residency">Open Employee Helper</a>
      <button class="btn" type="button" id="loadEmpRows">Reload Employees</button>
    </div>
  </div>
  <div class="col-12 card">
    <h3 class="section-title">Employee Rows (Post-Import Check)</h3>
    <table>
      <thead><tr><th>Company ID</th><th>Name</th><th>Department</th><th>Designation</th><th>Unit</th><th>Active</th></tr></thead>
      <tbody id="hrRows"><tr><td colspan="6"><div class="empty">No rows loaded.</div></td></tr></tbody>
    </table>
  </div>
  <div class="col-12 card"><h3 class="section-title">Result</h3><pre id="hrResult">Ready.</pre></div>
</div>
<script>
const csrf=@json(csrf_token());
const out=document.getElementById('hrResult');
const rowsEl=document.getElementById('hrRows');
function show(v){out.textContent=JSON.stringify(v,null,2)}
function download(name,c){const b=new Blob([c],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(b);a.download=name;a.click();URL.revokeObjectURL(a.href);} 
async function req(url,method='GET',payload=null){const o={method,headers:{'X-CSRF-TOKEN':csrf}}; if(payload!==null){o.headers['Content-Type']='application/json';o.body=JSON.stringify(payload);} const r=await fetch(url,o); const j=await r.json().catch(()=>({raw:'non-json'})); const v={status:r.status,body:j}; show(v); return v;}
function render(rows){if(!Array.isArray(rows)||rows.length===0){rowsEl.innerHTML='<tr><td colspan="6"><div class="empty">No rows found.</div></td></tr>';return;} rowsEl.innerHTML=rows.slice(0,200).map(r=>`<tr><td>${r.company_id??''}</td><td>${r.name??''}</td><td>${r.department??''}</td><td>${r.designation??''}</td><td>${r.unit_id??''}</td><td>${r.active??''}</td></tr>`).join('');}

document.getElementById('downloadHrTemplate').onclick=()=>download('active_days_import_template.csv','company_id,active_days\nE1001,30\nE1002,28\n');
document.getElementById('previewHrCsv').onclick=async()=>{
  const f=document.getElementById('hrCsvFile').files[0]; if(!f)return show({status:400,error:'Select HR CSV file'});
  const month_cycle=document.getElementById('hrMonthCycle').value.trim();
  const csv=await f.text(); await req('/imports/active-days/import','POST',{month_cycle,csv_text:csv,commit:false});
};
document.getElementById('importHrCsv').onclick=async()=>{
  const f=document.getElementById('hrCsvFile').files[0]; if(!f)return show({status:400,error:'Select HR CSV file'});
  const month_cycle=document.getElementById('hrMonthCycle').value.trim();
  const csv=await f.text(); const res=await req('/imports/active-days/import','POST',{month_cycle,csv_text:csv,commit:true});
  if(res.status>=200 && res.status<300) document.getElementById('loadEmpRows').click();
};
document.getElementById('loadEmpRows').onclick=async()=>{const r=await req('/employees'); render(r.body?.rows||[]);};
</script>
@endsection
