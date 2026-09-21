<!doctype html><html><head>
@include('partials.material-symbols-local')
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Active Days Import</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#fcf8ff;color:#1b1b24;font:13px/1.5 Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;-webkit-font-smoothing:antialiased}
a{color:#4f46e5}
.wrap{max-width:1600px;margin:auto;padding:16px 24px}
header{background:#fff;border-bottom:1px solid #c7c4d8}
.top{display:flex;justify-content:space-between;align-items:center;height:48px}
.brand{font-size:16px;font-weight:700;color:#3525cd;letter-spacing:-.2px}
.nav a{margin-left:22px;color:#464555;text-decoration:none;font-size:13px;font-weight:500}
.nav .on{color:#3525cd;border-bottom:2px solid #3525cd;padding-bottom:14px}
.hero{display:flex;justify-content:space-between;gap:24px;align-items:flex-end;border-bottom:1px solid #c7c4d8;padding-bottom:16px;margin-bottom:20px}
.hero h1{font-size:24px;font-weight:600;letter-spacing:-.02em;margin:0 0 3px}
.muted{color:#464555}.hint{font-size:11px;color:#585f6c}
.steps{display:flex;align-items:center;gap:8px;font-size:12px;white-space:nowrap}
.step{display:flex;align-items:center;gap:5px;color:#585f6c}
.dot{width:17px;height:17px;border-radius:50%;display:grid;place-items:center;background:#e4e1ee;color:#585f6c;font-size:10px;font-weight:700}
.done{color:#3525cd}.done .dot{background:#e2dfff;color:#3525cd}
.active{color:#1b1b24;font-weight:600}.active .dot{background:#4f46e5;color:#fff}
.line{width:30px;height:1px;background:#c7c4d8}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.card{background:#fff;border:1px solid #c7c4d8;border-radius:6px;padding:16px}
.card h2{margin:0;font-size:16px;font-weight:600;letter-spacing:-.01em}
.chead{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px}
.rhead{display:flex;justify-content:space-between;align-items:center;gap:14px}
.actions{display:flex;gap:6px;align-items:center}
.fields{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.field{margin-bottom:10px}
.field label,.lbl{display:block;font-size:11px;font-weight:600;color:#464555;margin-bottom:4px}
.input{width:100%;height:30px;border:1px solid #c7c4d8;border-radius:3px;padding:0 8px;font-size:13px;font-family:inherit;color:#1b1b24;background:#fff}
.input:focus{outline:0;border-color:#4f46e5;box-shadow:0 0 0 1px #4f46e5}
input[type=file].input{padding:3px 8px;font-size:12px;height:auto}
select.input{cursor:pointer}
.btn{height:30px;padding:0 14px;border:1px solid #c7c4d8;border-radius:3px;background:#fff;color:#1b1b24;font-weight:500;font-size:13px;font-family:inherit;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:.15s}
.btn:hover:not(:disabled){background:#eae6f4}
.btn:disabled{opacity:.5;cursor:not-allowed}
.primary{background:#4f46e5;border-color:#4f46e5;color:#fff}
.primary:hover:not(:disabled){background:#3525cd;border-color:#3525cd}
.mini{height:25px;padding:0 9px;font-size:12px}
.mini.del{color:#ba1a1a;border-color:#ffdad6}
.mini.del:hover{background:#ffdad6}
.warning{display:flex;gap:8px;align-items:flex-start;font-size:12px;color:#464555;margin-top:10px}
.results{margin-top:20px}
.kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px}
.kpi{background:#fff;border:1px solid #c7c4d8;border-radius:6px;padding:12px 14px}
.kpi label{font-size:11px;font-weight:600;color:#585f6c}
.kpi strong{display:block;font-size:24px;font-weight:700;margin-top:2px;letter-spacing:-.02em}
.good{border-left:3px solid #10b981}.good label{color:#047857}.good strong{color:#047857}
.bad{border-left:3px solid #ba1a1a}.bad label,.bad strong{color:#ba1a1a}
.tablewrap{overflow:auto;border:1px solid #c7c4d8;border-radius:4px;margin-top:12px;background:#fff}
.tbl{width:100%;border-collapse:collapse;text-align:left;font-size:13px}
.tbl th{background:#f5f2ff;color:#464555;font-size:12px;font-weight:600;padding:8px 14px;border-bottom:1px solid #c7c4d8;white-space:nowrap}
.tbl td{padding:7px 14px;border-bottom:1px solid #e4e1ee;vertical-align:middle}
.tbl tbody tr:last-child td{border-bottom:0}
.tbl tbody tr:hover{background:#faf8ff}
.status{background:#f5f2ff;border:1px solid #e4e1ee;border-radius:4px;padding:10px;margin-top:12px;white-space:pre-wrap;font:12px/1.5 monospace;color:#464555;max-height:190px;overflow:auto}
.hide{display:none}
.current{margin-top:20px}
.mrow{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap}
.mrow .field{flex:1;min-width:130px;margin-bottom:0}
.srch{position:relative}
.drop{display:none;position:absolute;z-index:60;top:100%;left:0;right:0;margin-top:4px;max-height:220px;overflow:auto;background:#fff;border:1px solid #c7c4d8;border-radius:4px;box-shadow:0 4px 10px rgba(27,27,36,.10)}
.opt{padding:8px 12px;cursor:pointer;border-bottom:1px solid #e4e1ee;font-size:13px}
.opt:last-child{border-bottom:0}.opt:hover{background:#f0ecf9}
.opt b{font-family:monospace;font-size:12px}
.opt span{display:block;font-size:11px;color:#585f6c;margin-top:1px}
.chips{display:none;flex-wrap:wrap;gap:5px;margin:12px 0 2px}
.chip{display:inline-flex;align-items:center;gap:5px;background:#f0ecf9;border:1px solid #c7c4d8;border-radius:12px;padding:3px 10px;font-size:12px}
.chip i{font-style:normal;color:#585f6c;font-size:10px;font-weight:600;letter-spacing:.3px}
.chip b{font-weight:600}
.pill{display:inline-flex;align-items:center;padding:2px 7px;border-radius:3px;font-size:11px;font-weight:600;white-space:nowrap}
.pill.ok{background:#d1fae5;color:#047857}
.pill.no{background:#ffdad6;color:#93000a}
.pill.man{background:#e2dfff;color:#3323cc}
.pill.csv{background:#e4e1ee;color:#464555}
.filters{display:grid;grid-template-columns:repeat(6,1fr);gap:8px;align-items:end;margin-top:14px}
#gridRows .input{height:26px;padding:0 6px;font-size:12px}
#gridRows .id{font-family:monospace;font-size:12px;font-weight:600}
#gridRows .sub{display:block;font-size:11px;color:#585f6c;margin-top:1px}
tr.out td{background:#f7f5fb;color:#777587}
tr.out .id{color:#777587}
#gridWrap.min{display:none}
footer{border-top:1px solid #c7c4d8;background:#fff;color:#585f6c;font-size:12px;margin-top:24px}
@media(max-width:1200px){.filters{grid-template-columns:repeat(3,1fr)}}
@media(max-width:900px){.grid2{grid-template-columns:1fr}.hero{display:block}.steps{margin-top:14px;overflow:auto}}
@media(max-width:650px){.fields,.kpis,.filters{grid-template-columns:1fr}.nav{display:none}.wrap{padding:14px}.rhead,.chead{display:block}.actions{margin-top:10px}}
</style></head>
<body>
@include('partials.global-navbar')
<header><div class="wrap top"><div class="brand">Monthly Data</div><nav class="nav"><a href="{{ url('/dashboard-v2') }}">Dashboard</a><a class="on" href="{{ url('/active-days-monthly') }}">Operations</a><a href="{{ url('/reports') }}">Reports</a></nav></div></header>
<main class="wrap">

<div class="hero">
  <div><h1>Active Days Import</h1><div class="muted">Upload and validate resident attendance records for billing cycles.</div></div>
  <div class="steps">
    <span class="step done"><b class="dot">&#10003;</b>Select Period</span><i class="line"></i>
    <span class="step done"><b class="dot">&#10003;</b>Upload File</span><i class="line"></i>
    <span class="step active"><b class="dot">3</b>Preview &amp; Validate</span><i class="line"></i>
    <span class="step"><b class="dot">4</b>Finalize</span>
  </div>
</div>

<div class="grid2">
  <section class="card">
    <div class="chead"><h2>Import Details</h2><a href="{{ url('/active-days-monthly/template') }}" style="font-size:12px;text-decoration:none">&#8595; CSV Template</a></div>
    <form id="activeDaysForm">
      <div class="field"><label>BILLING MONTH</label><input class="input" type="month" name="billing_month_date" value="{{ substr((string)$billingMonthDate,0,7) }}" required></div>
      <div class="fields">
        <div class="field"><label>CYCLE START</label><input class="input" type="date" name="cycle_start_date" required></div>
        <div class="field"><label>CYCLE END</label><input class="input" type="date" name="cycle_end_date" required></div>
      </div>
      <div class="field"><label>CSV FILE</label><input class="input" type="file" name="upload_file" accept=".csv,.txt" required></div>
      <label class="warning"><input id="replaceExisting" type="checkbox" name="replace_existing" value="1"><span>Replace existing manual entries for this period. CSV rows will overwrite manual inputs.</span></label>
      <p style="margin:14px 0 0;padding-top:12px;border-top:1px solid #e4e1ee"><button class="btn primary" type="submit" style="width:100%">Preview &amp; Validate</button></p>
    </form>
  </section>

  <section class="card">
    <div class="chead"><h2>Manual Entry / Override</h2></div>
    <div class="field srch"><label>SEARCH EMPLOYEE (ID / NAME / CNIC)</label><input class="input" id="empSearch" type="text" autocomplete="off" placeholder="Type at least 2 characters..."><div class="drop" id="empDrop"></div></div>
    <div class="chips" id="empDetail"></div>
    <div class="mrow" style="margin-top:10px">
      <div class="field" style="max-width:130px"><label>ACTIVE DAYS</label><input class="input" id="mActiveDays" type="number" step="0.0001" min="0" placeholder="e.g. 15"></div>
      <div class="field" style="flex:2"><label>REMARKS (OPTIONAL)</label><input class="input" id="mRemarks" type="text" placeholder="Reason for override..."></div>
    </div>
    <div class="rhead" style="margin-top:14px;padding-top:12px;border-top:1px solid #e4e1ee"><span class="hint" id="mMsg">Ready.</span><button class="btn primary" id="mSaveBtn" type="button" disabled>Save Entry</button></div>
  </section>
</div>

<section class="results">
<div class="kpis">
  <div class="kpi"><label>TOTAL ROWS</label><strong id="kTotal">0</strong></div>
  <div class="kpi good"><label>VALID ROWS</label><strong id="kValid">0</strong></div>
  <div class="kpi bad"><label>INVALID ROWS</label><strong id="kInvalid">0</strong></div>
  <div class="kpi"><label>EXISTING OVERLAP</label><strong id="kExisting">0</strong></div>
</div>

<div class="card">
  <div class="rhead"><div><h2>Validation Results</h2><span class="hint">Review parsed data before finalizing the import.</span></div><div class="actions"><button class="btn mini" id="reloadRows" type="button">Current Rows</button><button class="btn primary" id="finalImportBtn" type="button" disabled>Finalize Import</button></div></div>
  <div id="invalidBlock" class="hide"><div class="tablewrap"><table class="tbl"><thead><tr><th>Row</th><th>Company ID</th><th>Active Days</th><th>Error Reason</th></tr></thead><tbody id="invalidRows"></tbody></table></div></div>
  <div class="tablewrap"><table class="tbl"><thead><tr><th>Company ID</th><th>Active Days</th><th>Remarks</th><th>Status</th></tr></thead><tbody id="validRows"><tr><td colspan="4">Upload a CSV and run preview.</td></tr></tbody></table></div>
  <label class="warning" style="margin-top:14px;padding-top:12px;border-top:1px solid #e4e1ee"><input id="ackOverwrite" type="checkbox"><span id="overwriteText">No preview completed yet.</span></label>
  <pre class="status" id="summaryBox">Ready.</pre>
</div>
</section>

<section class="card" style="margin-top:20px">
  <div class="rhead"><div><h2>Attendance Grid</h2><span class="hint">Inline edit active days for any employee. Rows without an entry show blank.</span></div><div class="actions"><span class="hint" id="gMsg"></span><button class="btn mini" id="gToggle" type="button" style="display:none">Minimize</button><button class="btn primary" id="gLoadBtn" type="button">Load Grid</button></div></div>
  <div class="filters">
    <div class="field" style="margin:0"><label>BILLING MONTH</label><input class="input" id="fMonth" type="month" value="{{ substr((string)$billingMonthDate,0,7) }}"></div>
    <div class="field" style="margin:0"><label>SEARCH</label><input class="input" id="fQ" type="text" placeholder="ID / Name / CNIC"></div>
    <div class="field" style="margin:0"><label>DEPARTMENT</label><select class="input" id="fDept"><option value="">All</option></select></div>
    <div class="field" style="margin:0"><label>STATUS</label><select class="input" id="fStatus"><option value="ALL">All</option><option value="ACTIVE">Active</option><option value="LEFT">Left</option></select></div>
    <div class="field" style="margin:0"><label>ENTRY</label><select class="input" id="fEntry"><option value="ALL">All</option><option value="HAS">Has Entry</option><option value="MISSING">Missing</option></select></div>
    <div class="field" style="margin:0"><label>SOURCE</label><select class="input" id="fSource"><option value="">All</option><option value="MANUAL">Manual</option><option value="CSV">CSV</option></select></div>
  </div>
  <div id="gridWrap"><div class="tablewrap"><table class="tbl"><thead><tr><th>Emp ID</th><th>Name</th><th>Department</th><th>Unit / Room</th><th>Status</th><th>Left Date</th><th>Residence</th><th>Active Days</th><th>Remarks</th><th>Source</th><th>Action</th></tr></thead><tbody id="gridRows"><tr><td colspan="11">Select a billing month, then click Load Grid.</td></tr></tbody></table></div></div>
</section>

<section class="card current">
  <div class="rhead"><div><h2>Current Imported Rows</h2></div><div class="actions"><span class="hint" id="curMsg"></span><button class="btn mini" id="curToggle" type="button" style="display:none">Minimize</button><button class="btn primary" id="reloadRows2" type="button">Load Rows</button></div></div>
  <div id="curWrap" style="display:none"><div class="tablewrap"><table class="tbl"><thead><tr><th>Company ID</th><th>Active Days</th><th>Remarks</th></tr></thead><tbody id="existingRows"></tbody></table></div></div>
</section>

</main>
<footer><div class="wrap top"><b>&copy; 2026 NodeSky Technologies.</b><span>Monthly billing operations</span></div></footer>
<script>const csrf=@json(csrf_token());let token='';const f=document.getElementById('activeDaysForm'),box=document.getElementById('summaryBox');const esc=v=>String(v??'').replace(/[&<>"']/g,s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));function ary(o,ks){for(const k of ks){const v=k.split('.').reduce((a,x)=>a?.[x],o);if(Array.isArray(v))return v}return[]}function n(o,ks,d=0){for(const k of ks){const v=k.split('.').reduce((a,x)=>a?.[x],o);if(v!==undefined&&!isNaN(Number(v)))return Number(v)}return d}function preview(j){token=j.preview_token||'';const ok=ary(j,['valid_rows','accepted_rows','rows.valid']),bad=ary(j,['invalid_rows','rejected_rows','errors','rows.invalid']),total=n(j,['summary.total_rows','total_rows'],ok.length+bad.length),existing=n(j,['summary.existing_updates','summary.existing_rows','existing_updates']);kTotal.textContent=total;kValid.textContent=n(j,['summary.valid_rows','summary.accepted_rows'],ok.length);kInvalid.textContent=n(j,['summary.invalid_rows','summary.rejected_rows'],bad.length);kExisting.textContent=existing;validRows.innerHTML=ok.length?ok.slice(0,100).map(x=>`<tr><td>${esc(x.company_id)}</td><td>${esc(x.active_days)}</td><td>${esc(x.remarks)}</td><td>${esc(x.status||'READY')}</td></tr>`).join(''):'<tr><td colspan="4">No valid rows.</td></tr>';invalidRows.innerHTML=bad.map((x,i)=>`<tr><td>${esc(x.row??x.row_number??i+1)}</td><td>${esc(x.company_id)}</td><td>${esc(x.active_days)}</td><td>${esc(x.error??x.reason??x.errors)}</td></tr>`).join('');invalidBlock.classList.toggle('hide',!bad.length);overwriteText.textContent=existing?existing+' existing records may be updated.':'No existing updates reported.';finalImportBtn.disabled=!token;box.textContent=JSON.stringify(j.summary??j,null,2)}f.onsubmit=async e=>{e.preventDefault();const b=e.submitter;b.disabled=true;box.textContent='Validating...';try{const r=await fetch('{{ url('/active-days-monthly/preview') }}',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,Accept:'application/json'},body:new FormData(f)}),j=await r.json();if(!r.ok)throw Error(JSON.stringify(j));preview(j)}catch(x){box.textContent=x.message}finally{b.disabled=false}};async function rows(){const r=await fetch('{{ url('/active-days-monthly/rows') }}?billing_month_date='+encodeURIComponent(f.billing_month_date.value),{headers:{Accept:'application/json'}}),j=await r.json(),a=Array.isArray(j.rows)?j.rows:[];existingRows.innerHTML=a.length?a.map(x=>`<tr><td>${esc(x.company_id)}</td><td>${esc(x.active_days)}</td><td>${esc(x.remarks)}</td></tr>`).join(''):'<tr><td colspan="3">No rows for selected month.</td></tr>'}reloadRows.onclick=rows;f.billing_month_date.onchange=rows;finalImportBtn.onclick=async()=>{if(!token)return;if(replaceExisting.checked&&!ackOverwrite.checked){box.textContent='Acknowledge overwrite before finalizing.';return}const p={billing_month_date:f.billing_month_date.value,cycle_start_date:f.cycle_start_date.value,cycle_end_date:f.cycle_end_date.value,replace_existing:replaceExisting.checked,preview_token:token};finalImportBtn.disabled=true;try{const r=await fetch('{{ url('/active-days-monthly/import') }}',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,Accept:'application/json'},body:JSON.stringify(p)}),j=await r.json();if(!r.ok)throw Error(JSON.stringify(j));box.textContent=JSON.stringify(j,null,2);token='';rows()}catch(x){box.textContent=x.message;finalImportBtn.disabled=false}};rows();</script>
<script>
(function(){
const CSRF=@json(csrf_token()), U=@json(url('/active-days-monthly'));
const esc=v=>String(v??'').replace(/[&<>"']/g,s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
const MON=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const fdate=v=>{if(!v)return '-';const t=String(v).slice(0,10).split('-');if(t.length!==3)return String(v);const d=+t[2],mo=+t[1];if(!mo||!d)return String(v);return String(d).padStart(2,'0')+'-'+MON[mo-1]+'-'+t[0]};
const F=document.getElementById('activeDaysForm');
const month=()=>F.billing_month_date.value;
const gMonth=()=>(document.getElementById('fMonth').value||F.billing_month_date.value);
let picked=null,tmr=null;

async function api(url,opt){const r=await fetch(url,opt);const j=await r.json();if(!r.ok)throw new Error(j.error||JSON.stringify(j));return j}

empSearch.oninput=()=>{clearTimeout(tmr);const q=empSearch.value.trim();picked=null;mSaveBtn.disabled=true;empDetail.style.display='none';
 if(q.length<2){empDrop.style.display='none';return}
 tmr=setTimeout(async()=>{try{const j=await api(U+'/employees?q='+encodeURIComponent(q),{headers:{Accept:'application/json'}});
  const a=j.employees||[];
  empDrop.innerHTML=a.length?a.map((e,i)=>`<div class="opt empOpt" data-i="${i}"><b>${esc(e.company_id)}</b> — ${esc(e.name)}<span>${esc(e.department||'-')} · ${esc(e.unit_id||'-')} · ${e.active==='Yes'?'Active':'Left'}</span></div>`).join(''):'<div class="opt muted">No match.</div>';
  empDrop.style.display='block';
  empDrop.querySelectorAll('.empOpt').forEach(d=>d.onclick=()=>pick(a[+d.dataset.i]));
 }catch(x){empDrop.innerHTML='<div class="opt">'+esc(x.message)+'</div>';empDrop.style.display='block'}},250)};

function pick(e){picked=e;empDrop.style.display='none';empSearch.value=e.company_id+' — '+e.name;
 empDetail.innerHTML=`<div class="chip"><i>EMP ID</i><b>${esc(e.company_id)}</b></div><div class="chip"><i>NAME</i><b>${esc(e.name)}</b></div><div class="chip"><i>DEPARTMENT</i><b>${esc(e.department||'-')}${e.section?' · '+esc(e.section):''}</b></div><div class="chip"><i>UNIT / ROOM</i><b>${esc(e.unit_id||'-')} · ${esc(e.block_floor||'-')} / ${esc(e.room_no||'-')}</b></div><div class="chip"><i>STATUS</i><span class="pill ${e.active==='Yes'?'ok':'no'}">${e.active==='Yes'?'ACTIVE':'LEFT'}</span></div><div class="chip"><i>JOIN</i><b>${fdate(e.join_date)}</b></div><div class="chip"><i>LEFT DATE</i><b>${fdate(e.leave_date)}</b></div>`;
 empDetail.style.display='flex';mSaveBtn.disabled=false}

document.addEventListener('click',e=>{if(!empDrop.contains(e.target)&&e.target!==empSearch)empDrop.style.display='none'});

mSaveBtn.onclick=async()=>{if(!picked)return;if(!month()){mMsg.textContent='Select billing month above.';return}
 mSaveBtn.disabled=true;mMsg.textContent='Saving...';
 try{const j=await api(U+'/row',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,Accept:'application/json'},body:JSON.stringify({billing_month_date:month(),company_id:picked.company_id,active_days:mActiveDays.value,remarks:mRemarks.value,cycle_start_date:F.cycle_start_date.value||null,cycle_end_date:F.cycle_end_date.value||null})});
  mMsg.textContent=j.action.toUpperCase()+' — '+j.company_id;mActiveDays.value='';mRemarks.value='';loadGrid()}
 catch(x){mMsg.textContent='Error: '+x.message}finally{mSaveBtn.disabled=false}};

async function loadGrid(){if(!gMonth()){gMsg.textContent='Select a billing month.';return}
 gMsg.textContent='Loading...';
 const p=new URLSearchParams({billing_month_date:gMonth(),q:fQ.value,department:fDept.value,status:fStatus.value,entry:fEntry.value,source:fSource.value,limit:500});
 try{const j=await api(U+'/grid?'+p,{headers:{Accept:'application/json'}}),a=j.rows||[];
  if(!fDept.value){const ds=[...new Set(a.map(r=>r.department).filter(Boolean))].sort();fDept.innerHTML='<option value="">All</option>'+ds.map(d=>`<option>${esc(d)}</option>`).join('')}
  gridRows.innerHTML=a.length?a.map(r=>`<tr data-id="${esc(r.company_id)}" class="${r.residence_status==='OUTSIDE'?'out':''}">
   <td class="id">${esc(r.company_id)}</td><td>${esc(r.name)}</td><td>${esc(r.department||'-')}${r.section?'<span class="sub">'+esc(r.section)+'</span>':''}</td>
   <td>${esc(r.unit_id||'-')}<span class="sub">${esc(r.block_floor||'-')} / ${esc(r.room_no||'-')}</span></td>
   <td><span class="pill ${r.active==='Yes'?'ok':'no'}">${r.active==='Yes'?'ACTIVE':'LEFT'}</span></td><td style="white-space:nowrap">${fdate(r.leave_date)}</td><td>${r.residence_status==='OUTSIDE'?'<span class="pill no">OUTSIDE</span>':(r.residence_status?'<span class="pill ok">'+esc(r.residence_status)+'</span>':'<span class="muted">—</span>')}</td>
   ${r.residence_status==='OUTSIDE'?'<td class="muted">\u2014</td><td class="muted">Not billed (outside colony)</td>':`<td><input class="input gD" style="width:96px" type="number" step="0.0001" min="0" value="${r.entry_id?esc(r.active_days):''}"></td><td><input class="input gR" style="min-width:160px" type="text" value="${esc(r.remarks||'')}"></td>`}
   <td>${r.entry_id?(r.source_file==='MANUAL'?'<span class="pill man">MANUAL</span>':'<span class="pill csv">CSV</span>'):'<span class="muted">—</span>'}</td>
   <td style="white-space:nowrap">${r.residence_status==='OUTSIDE'?'<span class="muted">N/A</span>':'<button class="btn mini gS" type="button">Save</button> '+(r.entry_id?'<button class="btn mini del gX" type="button">Delete</button>':'')}</td></tr>`).join(''):'<tr><td colspan="11">No rows match these filters.</td></tr>';
  gMsg.textContent=a.length+' row(s).';gToggle.style.display='';gridWrap.classList.remove('min');gToggle.textContent='Minimize';

  gridRows.querySelectorAll('tr[data-id]').forEach(tr=>{
   const id=tr.dataset.id;
   const s=tr.querySelector('.gS'),x=tr.querySelector('.gX');
   if(s)s.onclick=async()=>{s.disabled=true;try{await api(U+'/row',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,Accept:'application/json'},body:JSON.stringify({billing_month_date:month(),company_id:id,active_days:tr.querySelector('.gD').value,remarks:tr.querySelector('.gR').value,cycle_start_date:F.cycle_start_date.value||null,cycle_end_date:F.cycle_end_date.value||null})});gMsg.textContent='Saved '+id;loadGrid()}catch(e){gMsg.textContent='Error ('+id+'): '+e.message;s.disabled=false}};
   if(x)x.onclick=async()=>{if(!confirm('Delete entry for '+id+'?'))return;x.disabled=true;try{await api(U+'/row/delete',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,Accept:'application/json'},body:JSON.stringify({billing_month_date:month(),company_id:id})});gMsg.textContent='Deleted '+id;loadGrid()}catch(e){gMsg.textContent='Error: '+e.message;x.disabled=false}};
  });
 }catch(x){gMsg.textContent='Error: '+x.message}}

gLoadBtn.onclick=loadGrid;
gToggle.onclick=()=>{const m=gridWrap.classList.toggle('min');gToggle.textContent=m?'Expand':'Minimize'};
const curWrap=document.getElementById('curWrap'),curToggle=document.getElementById('curToggle'),curMsg=document.getElementById('curMsg');
document.getElementById('reloadRows2').onclick=async()=>{curMsg.textContent='Loading...';
 try{const j=await api(U+'/rows?billing_month_date='+encodeURIComponent(gMonth()),{headers:{Accept:'application/json'}}),a=Array.isArray(j.rows)?j.rows:[];
  document.getElementById('existingRows').innerHTML=a.length?a.map(x=>`<tr><td>${esc(x.company_id)}</td><td>${esc(x.active_days)}</td><td>${esc(x.remarks||'')}</td></tr>`).join(''):'<tr><td colspan="3">No rows for selected month.</td></tr>';
  curWrap.style.display='block';curToggle.style.display='';curToggle.textContent='Minimize';curMsg.textContent=a.length+' row(s).'}
 catch(x){curMsg.textContent='Error: '+x.message}};
curToggle.onclick=()=>{const h=curWrap.style.display==='none';curWrap.style.display=h?'block':'none';curToggle.textContent=h?'Minimize':'Expand'};
[fMonth,fQ,fDept,fStatus,fEntry,fSource].forEach(el=>el.onchange=loadGrid);
fQ.onkeydown=e=>{if(e.key==='Enter'){e.preventDefault();loadGrid()}};
})();
</script>
</body></html>
