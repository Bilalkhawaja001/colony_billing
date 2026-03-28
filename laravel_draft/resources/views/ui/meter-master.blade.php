@extends('layouts.app')
@section('page_title','Meter Master')
@section('page_subtitle','Meter readings + meter-unit mapping with CSV bulk tools and operator feedback.')
@section('content')
<div class="grid">
  <div class="col-7 card">
    <h3 class="section-title">Single Entry · Meter Reading</h3>
    <form id="meterReadingForm" class="form-grid">
      <div class="field col-4"><label class="label">Month Cycle</label><input name="month_cycle" placeholder="MM-YYYY"></div>
      <div class="field col-4"><label class="label">Unit ID</label><input name="unit_id" placeholder="U-001"></div>
      <div class="field col-4"><label class="label">Meter ID</label><input name="meter_id" placeholder="M-001"></div>
      <div class="field col-6"><label class="label">Reading Date</label><input name="reading_date" placeholder="YYYY-MM-DD"></div>
      <div class="field col-6"><label class="label">Reading Value</label><input name="reading_value" placeholder="123.45"></div>
      <div class="col-12"><button class="btn btn-primary" type="submit">Upsert Reading</button></div>
    </form>
  </div>

  <div class="col-5 card">
    <h3 class="section-title">Single Entry · Meter ↔ Unit Mapping</h3>
    <form id="meterUnitForm" class="form-grid">
      <div class="field col-6"><label class="label">Meter ID</label><input name="meter_id" placeholder="M-001"></div>
      <div class="field col-6"><label class="label">Unit ID</label><input name="unit_id" placeholder="U-001"></div>
      <div class="field col-6"><label class="label">Meter Type</label><input name="meter_type" placeholder="ELEC"></div>
      <div class="field col-6"><label class="label">Is Active (1/0)</label><input name="is_active" value="1"></div>
      <div class="col-12"><button class="btn btn-success" type="submit">Upsert Mapping</button></div>
    </form>
  </div>

  <div class="col-12 card soft">
    <h3 class="section-title">CSV Bulk Tools</h3>
    <div class="grid">
      <div class="col-6">
        <div class="muted" style="margin-bottom:8px">Readings CSV header:</div>
        <code>meter_id,unit_id,reading_date,reading_value</code>
        <div class="toolbar" style="margin-top:10px">
          <button class="btn" type="button" id="downloadReadingsTemplate">Download Readings Template</button>
          <input type="file" id="readingsCsvFile" accept=".csv,text/csv">
          <button class="btn btn-primary" type="button" id="importReadingsCsv">Import Readings CSV</button>
        </div>
      </div>
      <div class="col-6">
        <div class="muted" style="margin-bottom:8px">Mappings CSV header:</div>
        <code>meter_id,unit_id,meter_type,is_active</code>
        <div class="toolbar" style="margin-top:10px">
          <button class="btn" type="button" id="downloadMappingsTemplate">Download Mappings Template</button>
          <input type="file" id="mappingsCsvFile" accept=".csv,text/csv">
          <button class="btn btn-success" type="button" id="importMappingsCsv">Import Mappings CSV</button>
        </div>
      </div>
    </div>
    <div class="alert" style="margin-top:10px">Bulk import uses existing upsert APIs row-by-row; failures are reported with line numbers.</div>
  </div>

  <div class="col-12 card">
    <h3 class="section-title">Mapping List</h3>
    <div class="toolbar" style="margin-bottom:10px">
      <input id="meterListQ" placeholder="filter by meter/unit/type">
      <button class="btn" type="button" id="loadMeterMappings">Reload Mappings</button>
    </div>
    <table>
      <thead><tr><th>Meter ID</th><th>Unit ID</th><th>Meter Type</th><th>Active</th></tr></thead>
      <tbody id="meterRows"><tr><td colspan="4"><div class="empty">No mapping rows loaded.</div></td></tr></tbody>
    </table>
  </div>

  <div class="col-12 card"><h3 class="section-title">Result / Errors</h3><pre id="meterResult">Ready.</pre></div>
</div>

<script>
const csrf=@json(csrf_token());
const result=document.getElementById('meterResult');
const meterRows=document.getElementById('meterRows');

function show(v){result.textContent=JSON.stringify(v,null,2)}
function parseCsv(text){
  const lines=text.split(/\r?\n/).map(s=>s.trim()).filter(Boolean);
  if(lines.length<2) return {header:[],rows:[]};
  const header=lines[0].split(',').map(s=>s.trim());
  const rows=lines.slice(1).map(l=>l.split(',').map(s=>s.trim()));
  return {header,rows};
}
function toObjects({header,rows}){ return rows.map(r=>Object.fromEntries(header.map((h,i)=>[h,r[i] ?? '']))); }
function download(name,content){ const b=new Blob([content],{type:'text/csv'}); const a=document.createElement('a'); a.href=URL.createObjectURL(b); a.download=name; a.click(); URL.revokeObjectURL(a.href); }

async function req(url,method='GET',payload=null){
  const opts={method,headers:{'X-CSRF-TOKEN':csrf}};
  if(payload!==null){opts.headers['Content-Type']='application/json';opts.body=JSON.stringify(payload)}
  const r=await fetch(url,opts); const j=await r.json().catch(()=>({raw:'non-json'}));
  return {status:r.status,body:j};
}

async function importRows(objects,endpoint,mapFn){
  let ok=0, fail=0, errors=[];
  for(let i=0;i<objects.length;i++){
    const payload=mapFn(objects[i]);
    const res=await req(endpoint,'POST',payload);
    if(res.status>=200 && res.status<300 && (res.body?.status==='ok' || res.body?.status===undefined)) ok++;
    else { fail++; errors.push({line:i+2,payload,response:res}); }
  }
  show({status:'done',endpoint,processed:objects.length,ok,fail,errors});
}

function renderMappings(rows){
  if(!Array.isArray(rows)||rows.length===0){ meterRows.innerHTML='<tr><td colspan="4"><div class="empty">No rows found.</div></td></tr>'; return; }
  meterRows.innerHTML=rows.map(r=>`<tr><td>${r.meter_id??''}</td><td>${r.unit_id??''}</td><td>${r.meter_type??''}</td><td>${r.is_active??''}</td></tr>`).join('');
}

// Single entry handlers
document.getElementById('meterReadingForm').addEventListener('submit',async e=>{
  e.preventDefault();
  const payload=Object.fromEntries(new FormData(e.target));
  show(await req('/meter-reading/upsert','POST',payload));
});
document.getElementById('meterUnitForm').addEventListener('submit',async e=>{
  e.preventDefault();
  const payload=Object.fromEntries(new FormData(e.target));
  show(await req('/meter-unit/upsert','POST',payload));
});

// Templates
document.getElementById('downloadReadingsTemplate').onclick=()=>download('meter_readings_template.csv','meter_id,unit_id,reading_date,reading_value\nM-001,U-001,2026-03-01,123.45\n');
document.getElementById('downloadMappingsTemplate').onclick=()=>download('meter_mappings_template.csv','meter_id,unit_id,meter_type,is_active\nM-001,U-001,ELEC,1\n');

// CSV imports
document.getElementById('importReadingsCsv').onclick=async()=>{
  const file=document.getElementById('readingsCsvFile').files[0]; if(!file) return show({status:400,error:'Select readings CSV file'});
  const text=await file.text(); const objects=toObjects(parseCsv(text));
  if(objects.length===0) return show({status:400,error:'CSV has no data rows'});
  importRows(objects,'/meter-reading/upsert',r=>({meter_id:r.meter_id,unit_id:r.unit_id,reading_date:r.reading_date,reading_value:r.reading_value}));
};
document.getElementById('importMappingsCsv').onclick=async()=>{
  const file=document.getElementById('mappingsCsvFile').files[0]; if(!file) return show({status:400,error:'Select mappings CSV file'});
  const text=await file.text(); const objects=toObjects(parseCsv(text));
  if(objects.length===0) return show({status:400,error:'CSV has no data rows'});
  importRows(objects,'/meter-unit/upsert',r=>({meter_id:r.meter_id,unit_id:r.unit_id,meter_type:r.meter_type,is_active:r.is_active||1}));
};

// List mappings
document.getElementById('loadMeterMappings').onclick=async()=>{
  const q=encodeURIComponent(document.getElementById('meterListQ').value||'');
  const res=await req('/meter-unit?q='+q);
  show(res);
  renderMappings(res.body?.rows||[]);
};
</script>
@endsection