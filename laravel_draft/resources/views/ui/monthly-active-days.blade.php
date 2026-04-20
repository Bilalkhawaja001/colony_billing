@extends('layouts.app')
@section('page_title','Monthly Active Days Import')
@section('page_subtitle','Upload monthly active days, preview validation, replace selected month data when needed, and keep billing inputs traceable.')
@section('content')
<div class="grid">
  <div class="col-12 card soft">
    <div class="toolbar">
      <span class="badge">Monthly Active Days</span>
      <a class="btn" href="/active-days-monthly/template">Download Template</a>
    </div>
  </div>

  <div class="col-6 card">
    <h3 class="section-title">Upload & Preview</h3>
    <form id="activeDaysForm" class="form-grid">
      <div class="field col-6"><label class="label">Billing Month</label><input type="month" name="billing_month_date" value="{{ substr((string)$billingMonthDate, 0, 7) }}"></div>
      <div class="field col-6"><label class="label">CSV File</label><input type="file" name="upload_file" accept=".csv,.txt"></div>
      <div class="field col-12"><label><input type="checkbox" name="replace_existing" value="1"> Replace existing selected month data</label></div>
      <div class="col-12">
        <button class="btn btn-primary" type="submit">Preview Import</button>
        <button class="btn" type="button" id="finalImportBtn" disabled>Finalize Import</button>
      </div>
    </form>
  </div>

  <div class="col-6 card">
    <h3 class="section-title">Result Summary</h3>
    <pre id="summaryBox">No preview yet.</pre>
  </div>

  <div class="col-12 card">
    <h3 class="section-title">Preview</h3>
    <div id="previewStatus" class="banner">Ready.</div>
    <pre id="previewBox" style="margin-top:10px">[]</pre>
  </div>

  <div class="col-12 card">
    <h3 class="section-title">Current Imported Rows</h3>
    <pre id="existingRowsBox">@json($rows, JSON_PRETTY_PRINT)</pre>
  </div>
</div>
<script>
const csrf=@json(csrf_token());
let previewToken='';
let lastMonth='{{ (string) $billingMonthDate }}';
let replaceExisting=false;
const summaryBox=document.getElementById('summaryBox');
const previewBox=document.getElementById('previewBox');
const existingRowsBox=document.getElementById('existingRowsBox');
const finalImportBtn=document.getElementById('finalImportBtn');
const statusBox=document.getElementById('previewStatus');

function setStatus(ok, text){statusBox.className=ok?'banner':'alert';statusBox.textContent=text;}
function payloadMonth(v){return /^\d{4}-\d{2}$/.test(v)?`${v}-01`:v;}
async function refreshRows(month){
  const r=await fetch(`/active-days-monthly/rows?billing_month_date=${encodeURIComponent(month)}`);
  const j=await r.json().catch(()=>({}));
  existingRowsBox.textContent=JSON.stringify(j.rows||j,null,2);
}

document.getElementById('activeDaysForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const form=e.target;
  const fd=new FormData(form);
  const month=payloadMonth((fd.get('billing_month_date')||'').toString());
  fd.set('billing_month_date', month);
  replaceExisting=fd.get('replace_existing')==='1';
  lastMonth=month;
  const r=await fetch('/active-days-monthly/preview',{method:'POST',headers:{'X-CSRF-TOKEN':csrf},body:fd});
  const j=await r.json().catch(()=>({status:'error',error:'non-json response'}));
  summaryBox.textContent=JSON.stringify(j.summary??j,null,2);
  previewBox.textContent=JSON.stringify({valid_rows:j.valid_rows,invalid_rows:j.invalid_rows},null,2);
  previewToken=j.preview_token||'';
  finalImportBtn.disabled=!(r.ok && previewToken && (j.summary?.valid_rows??0)>0);
  setStatus(r.ok, r.ok ? 'Preview ready.' : `Preview failed (${r.status}).`);
});

finalImportBtn.addEventListener('click', async ()=>{
  if(!previewToken){return;}
  const payload={billing_month_date:lastMonth,preview_token:previewToken,replace_existing:replaceExisting};
  const r=await fetch('/active-days-monthly/import',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});
  const j=await r.json().catch(()=>({status:'error',error:'non-json response'}));
  summaryBox.textContent=JSON.stringify(j.summary??j,null,2);
  previewBox.textContent=JSON.stringify(j.rows??j,null,2);
  setStatus(r.ok, r.ok ? 'Import completed.' : `Import failed (${r.status}).`);
  if(r.ok){previewToken='';finalImportBtn.disabled=true;await refreshRows(lastMonth);}
});
</script>
@endsection
