@extends('layouts.app')
@section('page_title','Employee Master')
@section('page_subtitle','Full employee operator console: add/upsert/search/get/patch/delete plus CSV bulk import.')
@section('content')
<div class="grid">
  <div class="col-7 card">
    <h3 class="section-title">Add / Upsert Employee</h3>
    <form id="empUpsertForm" class="form-grid">
      <div class="field col-3"><label class="label">Company ID *</label><input name="company_id" placeholder="E1001"></div>
      <div class="field col-5"><label class="label">Name *</label><input name="name" placeholder="Employee Name"></div>
      <div class="field col-4"><label class="label">Department</label><input name="department" placeholder="Ops"></div>
      <div class="field col-4"><label class="label">Designation</label><input name="designation" placeholder="Technician"></div>
      <div class="field col-4"><label class="label">Unit ID</label><input name="unit_id" placeholder="U-01"></div>
      <div class="field col-4"><label class="label">Active</label><select name="active"><option>Yes</option><option>No</option></select></div>
      <div class="toolbar col-12">
        <button class="btn btn-primary" type="submit">Upsert</button>
        <button class="btn" type="button" id="empAddBtn">Add (Conflict if exists)</button>
      </div>
    </form>
  </div>

  <div class="col-5 card">
    <h3 class="section-title">CSV Bulk Upload</h3>
    <div class="muted" style="margin-bottom:8px">Header required: <code>company_id,name,department,designation,unit_id,active</code></div>
    <div class="form-grid">
      <div class="field col-12"><label class="label">Upload CSV File</label><input type="file" id="empCsvFile" accept=".csv,text/csv"></div>
      <div class="field col-12"><label class="label">Or Paste CSV Text</label><textarea id="empCsvText" rows="6" placeholder="company_id,name,department,designation,unit_id,active"></textarea></div>
      <div class="toolbar col-12">
        <button class="btn btn-primary" type="button" id="empImportBtn">Import Employees CSV</button>
      </div>
    </div>
  </div>

  <div class="col-12 card">
    <h3 class="section-title">Search / Get / Patch / Delete</h3>
    <div class="form-grid" style="margin-bottom:10px">
      <div class="field col-4"><label class="label">Search q</label><input id="searchQ" placeholder="name / company id / dept"></div>
      <div class="field col-3"><label class="label">Department filter</label><input id="searchDept" placeholder="Ops"></div>
      <div class="field col-2"><label class="label">Active</label><select id="searchActive"><option value="">All</option><option>Yes</option><option>No</option></select></div>
      <div class="col-3" style="display:flex;align-items:flex-end"><div class="toolbar"><button class="btn" type="button" id="searchBtn">Search</button><button class="btn" type="button" id="listBtn">List</button></div></div>
    </div>

    <div class="form-grid" style="margin-bottom:10px">
      <div class="field col-3"><label class="label">Target Company ID</label><input id="targetCompanyId" placeholder="E1001"></div>
      <div class="field col-3"><label class="label">Patch Name</label><input id="patchName" placeholder="optional"></div>
      <div class="field col-3"><label class="label">Patch Department</label><input id="patchDepartment" placeholder="optional"></div>
      <div class="field col-3"><label class="label">Patch Active</label><select id="patchActive"><option value="">(no change)</option><option>Yes</option><option>No</option></select></div>
      <div class="toolbar col-12">
        <button class="btn" type="button" id="getBtn">Get</button>
        <button class="btn btn-success" type="button" id="patchBtn">Patch</button>
        <button class="btn btn-danger" type="button" id="deleteBtn">Soft Delete</button>
        <button class="btn" type="button" id="deptBtn">Departments</button>
      </div>
    </div>

    <h4 class="section-title" style="margin-top:8px">Employee List Preview</h4>
    <table>
      <thead><tr><th>Company ID</th><th>Name</th><th>Department</th><th>Designation</th><th>Unit</th><th>Active</th></tr></thead>
      <tbody id="empRows"><tr><td colspan="6"><div class="empty">Run search/list to load rows.</div></td></tr></tbody>
    </table>
  </div>

  <div class="col-12 card">
    <h3 class="section-title">API Result</h3>
    <pre id="empResult">Ready.</pre>
  </div>
</div>

<script>
const csrf=@json(csrf_token());
const resultEl=document.getElementById('empResult');
const rowsEl=document.getElementById('empRows');

function show(v){resultEl.textContent=JSON.stringify(v,null,2)}
function renderRows(rows){
  if(!Array.isArray(rows) || rows.length===0){ rowsEl.innerHTML='<tr><td colspan="6"><div class="empty">No rows found.</div></td></tr>'; return; }
  rowsEl.innerHTML=rows.map(r=>`<tr><td>${r.company_id??''}</td><td>${r.name??''}</td><td>${r.department??''}</td><td>${r.designation??''}</td><td>${r.unit_id??''}</td><td>${r.active??''}</td></tr>`).join('');
}
async function req(url,method='GET',payload=null){
  const opts={method,headers:{'X-CSRF-TOKEN':csrf}};
  if(payload!==null){opts.headers['Content-Type']='application/json';opts.body=JSON.stringify(payload)}
  const r=await fetch(url,opts);const j=await r.json().catch(()=>({raw:'non-json'}));
  const out={status:r.status,body:j}; show(out); return out;
}

function formObj(formId){return Object.fromEntries(new FormData(document.getElementById(formId)));}

document.getElementById('empUpsertForm').addEventListener('submit',async e=>{
  e.preventDefault();
  const out=await req('/employees/upsert','POST',formObj('empUpsertForm'));
  if(out.status<300){document.getElementById('targetCompanyId').value=(formObj('empUpsertForm').company_id||'');}
});

document.getElementById('empAddBtn').onclick=()=>req('/employees/add','POST',formObj('empUpsertForm'));

document.getElementById('empImportBtn').onclick=async()=>{
  const file=document.getElementById('empCsvFile').files[0];
  let csv=document.getElementById('empCsvText').value.trim();
  if(file){ csv=await file.text(); document.getElementById('empCsvText').value=csv; }
  if(!csv){ return show({status:400,body:{error:'Provide csv file or csv text'}}); }
  req('/employees/import','POST',{csv_text:csv});
};

document.getElementById('searchBtn').onclick=async()=>{
  const q=encodeURIComponent(document.getElementById('searchQ').value||'');
  const out=await req('/employees/search?q='+q);
  renderRows(out.body?.rows||[]);
};
document.getElementById('listBtn').onclick=async()=>{
  const q=encodeURIComponent(document.getElementById('searchQ').value||'');
  const d=encodeURIComponent(document.getElementById('searchDept').value||'');
  const a=encodeURIComponent(document.getElementById('searchActive').value||'');
  const out=await req('/employees?q='+q+'&department='+d+'&active='+a);
  renderRows(out.body?.rows||[]);
};

document.getElementById('getBtn').onclick=()=>req('/employees/'+encodeURIComponent(document.getElementById('targetCompanyId').value||''));

document.getElementById('patchBtn').onclick=()=>{
  const id=(document.getElementById('targetCompanyId').value||'').trim();
  const payload={};
  const n=document.getElementById('patchName').value.trim();
  const d=document.getElementById('patchDepartment').value.trim();
  const a=document.getElementById('patchActive').value;
  if(n) payload.name=n;
  if(d) payload.department=d;
  if(a) payload.active=a;
  req('/employees/'+encodeURIComponent(id),'PATCH',payload);
};

document.getElementById('deleteBtn').onclick=()=>req('/employees/'+encodeURIComponent(document.getElementById('targetCompanyId').value||''),'DELETE');
document.getElementById('deptBtn').onclick=()=>req('/employees/meta/departments');
</script>
@endsection