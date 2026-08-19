<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Meter Readings | Colony Billing</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f7f9fb;color:#191c1e;font:14px Inter,Arial,sans-serif}.mr-top{background:#fff;border-bottom:1px solid #e2e8f0}.mr-topin,.mr-main,.mr-footin{max-width:1440px;margin:auto;padding:16px 24px}.mr-topin{display:flex;align-items:center;justify-content:space-between;gap:20px}.mr-brand{font-size:25px;font-weight:800;color:#0f172a}.mr-brand b{color:#0051d5}.mr-nav{display:flex;gap:8px}.mr-nav a{padding:9px 13px;border-radius:8px;color:#475569;text-decoration:none;font-weight:700}.mr-nav a.active{color:#0051d5;background:#eff6ff}.mr-main{display:grid;grid-template-columns:minmax(280px,1fr) minmax(560px,2fr);gap:24px;padding-top:24px;padding-bottom:32px}.mr-left{display:flex;flex-direction:column;gap:20px}.mr-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;box-shadow:0 4px 10px rgba(15,23,42,.04)}.mr-title{margin:0 0 22px;font-size:22px;color:#0f172a}.mr-title i{font-style:normal;color:#0051d5}.mr-field{margin-bottom:17px}.mr-field label{display:block;margin-bottom:6px;color:#475569;font-size:12px;font-weight:700}.mr-input,.mr-select{width:100%;height:40px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;padding:8px 11px;color:#0f172a;outline:0}.mr-input:focus,.mr-select:focus{border-color:#0051d5;box-shadow:0 0 0 3px rgba(0,81,213,.1)}.mr-mono{font-family:"Courier New",monospace}.mr-btn{height:40px;border:1px solid #d7dce3;border-radius:8px;background:#f2f4f6;color:#334155;padding:0 15px;font-weight:700;cursor:pointer}.mr-btn.primary{background:#0051d5;border-color:#0051d5;color:#fff}.mr-btn.full{width:100%}.mr-row{display:flex;gap:10px}.mr-result{margin-top:16px;padding:14px;border-radius:8px;background:#f2f4f6;border:1px solid #e2e8f0;white-space:pre-wrap;min-height:72px;max-height:220px;overflow:auto;font:12px/1.5 "Courier New",monospace}.mr-head{display:flex;justify-content:space-between;align-items:center;gap:18px;border-bottom:1px solid #e2e8f0;padding-bottom:16px;margin-bottom:22px}.mr-head h1{margin:0;font-size:29px}.mr-switch{display:flex;background:#f2f4f6;border:1px solid #e2e8f0;border-radius:9px;padding:5px}.mr-mode{border:0;border-radius:6px;padding:9px 16px;font-weight:700;color:#475569;background:transparent;cursor:pointer}.mr-mode.active{background:#0051d5;color:#fff}.mr-filters{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.mr-actions{display:flex;align-items:end;gap:8px}.mr-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:23px 0}.mr-kpi{background:#f2f4f6;border:1px solid #e2e8f0;border-radius:12px;padding:17px}.mr-kpi label{display:block;color:#64748b;font-size:12px;font-weight:700;margin-bottom:8px}.mr-kpi strong{display:block;font-size:24px;color:#0f172a;overflow-wrap:anywhere}.mr-kpi.warn strong{color:#d97706}.mr-tablewrap{overflow:auto;border:1px solid #e2e8f0;border-radius:12px}.mr-table{width:100%;border-collapse:collapse;min-width:790px}.mr-table th{background:#f2f4f6;color:#64748b;font-size:11px;text-transform:uppercase;text-align:left;padding:12px}.mr-table td{padding:12px;border-top:1px solid #e2e8f0}.mr-num{text-align:right}.mr-badge{display:inline-block;padding:4px 8px;border-radius:5px;background:#ecfdf5;color:#059669;font-size:10px;font-weight:800;text-transform:uppercase}.mr-status{color:#64748b;font-size:12px;margin:10px 0}.mr-hidden{display:none!important}.mr-employee-filters{display:grid;grid-template-columns:1fr 1fr auto;gap:16px;align-items:end}.mr-foot{background:#fff;border-top:1px solid #e2e8f0;color:#64748b}.mr-footin{display:flex;justify-content:space-between}.mr-links a{color:#64748b;text-decoration:none;margin-left:18px}.mr-back{color:#0051d5;text-decoration:none;font-weight:700}@media(max-width:950px){.mr-main{grid-template-columns:1fr}.mr-filters{grid-template-columns:repeat(2,1fr)}}@media(max-width:620px){.mr-nav{display:none}.mr-filters,.mr-kpis,.mr-employee-filters{grid-template-columns:1fr}.mr-head{align-items:flex-start;flex-direction:column}.mr-main{padding:16px}.mr-card{padding:17px}}
</style>
</head>
<body>
@include('partials.global-navbar')

<header class="mr-top"><div class="mr-topin"><div class="mr-brand"><b>⚡</b> Colony Billing</div><nav class="mr-nav"><a href="{{ url('/dashboard-v2') }}">Dashboard</a><a class="active" href="{{ url('/meters-readings') }}">Operations</a><a href="{{ url('/reports') }}">Reports</a></nav><a class="mr-back" href="{{ url('/meters-readings') }}">← Meters Hub</a></div></header>
<main class="mr-main" data-grid="meterReadings">
 <aside class="mr-left">
  <section class="mr-card"><h2 class="mr-title"><i>⚙</i> Quick Entry</h2><form id="quickReadingForm">
   <div class="mr-field"><label>Meter ID</label><input class="mr-input mr-mono" name="meter_id" placeholder="e.g. MTR-001" required></div>
   <div class="mr-field"><label>Unit ID</label><input class="mr-input mr-mono" name="unit_id" placeholder="e.g. U-001" required></div>
   <div class="mr-field"><label>Reading Value</label><input class="mr-input mr-mono" name="reading_value" type="number" step="0.0001" min="0" required></div>
   <div class="mr-field"><label>Reading Date</label><input class="mr-input" name="reading_date" type="date" required></div>
   <button class="mr-btn primary full" type="submit">Save Reading</button>
<div id="quickReadingMsg" style="display:none;margin-top:10px;padding:10px;border-radius:7px;font-weight:700"></div>
  </form></section>
  <section class="mr-card"><h2 class="mr-title"><i>⌕</i> Latest Lookup</h2><div class="mr-field"><label>Unit ID</label><div class="mr-row"><input class="mr-input mr-mono" id="latestUnit" placeholder="Search ID..."><button class="mr-btn" id="latestBtn" type="button">Search</button></div></div><pre class="mr-result" id="readingsResult">Ready.</pre></section>
  <section class="mr-card">
    <h2 class="mr-title"><i>⇪</i> Bulk Import (CSV)</h2>

    @if(session('status'))<div class="mr-result" style="color:#047857">{{ session('status') }}</div>@endif
    @if(session('error'))<div class="mr-result" style="color:#b91c1c">{{ session('error') }}</div>@endif

    <form method="post" action="{{ route('billing.readings.import.preview') }}" enctype="multipart/form-data">
      @csrf
      <div class="mr-field">
        <label>Month Cycle</label>
        <input class="mr-input" type="month" id="impMonth" value="{{ date('Y-m') }}" required>
        <input type="hidden" name="month_cycle" id="impMonthCycle" value="{{ date('m-Y') }}">
      </div>
      <div class="mr-field">
        <label>CSV File</label>
        <div class="mr-row">
          <input class="mr-input" type="file" name="csv_file" accept=".csv" required>
          <button class="mr-btn" type="submit">Preview</button>
        </div>
      </div>
      <div class="mr-field" style="font-size:12px;opacity:.75">Columns: unit_id, current_reading (previous_reading optional)</div>
    </form>

    @php($pv = session('reading_preview'))
    @if($pv)
      <pre class="mr-result">{{ count($pv['rows']) }} rows · {{ $pv['cycle_start'] }} → {{ $pv['cycle_end'] }}@if(!empty($pv['issues'])) · {{ count($pv['issues']) }} problem row(s)@endif</pre>

      @if(!empty($pv['issues']))
        <div class="mr-tablewrap" style="max-height:260px;overflow:auto">
          <table class="mr-table">
            <thead><tr><th>Line</th><th>Unit</th><th>Meter</th><th>Masla</th></tr></thead>
            <tbody>
            @foreach($pv['issues'] as $ix)
              <tr><td>{{ $ix['line'] }}</td><td><b>{{ $ix['unit_id'] }}</b></td><td>{{ $ix['meter_id'] }}</td><td style="color:#b91c1c">{{ $ix['message'] }}</td></tr>
            @endforeach
            </tbody>
          </table>
        </div>
      @endif

      @if(!empty($pv['missing']))
        <div class="mr-result" style="color:#92400e">CSV me nahi aaye ({{ count($pv['missing']) }}): {{ implode(', ', array_slice($pv['missing'], 0, 25)) }}@if(count($pv['missing']) > 25) ...@endif</div>
      @endif

      <form method="post" action="{{ route('billing.readings.import.commit') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="month_cycle" value="{{ $pv['month_cycle'] }}">
        @if(!empty($pv['issues']))
          <label style="display:block;margin:8px 0;font-size:13px">
            <input type="checkbox" name="skip_flagged" value="1"> Skip flagged rows and proceed ({{ count($pv['issues']) }} rows chhod di jayengi)
          </label>
        @endif
        <div class="mr-row">
          <input class="mr-input" type="file" name="csv_file" accept=".csv" required>
          <button class="mr-btn" type="submit" onclick="return confirm('Import these readings?')">Confirm Import</button>
        </div>
      </form>
    @endif
  </section>
 </aside>
 <section class="mr-card">
  <div class="mr-head"><h1><span style="color:#0051d5">▥</span> Consumption Analysis</h1><div class="mr-switch"><button class="mr-mode active" data-mode="meter" type="button">Meter / Unit</button><button class="mr-mode" data-mode="employee" type="button">Employee Alloc.</button></div></div>
  <div id="meterMode">
   <div class="mr-filters">
    <div class="mr-field"><label>From Date</label><input class="mr-input" id="mr_from" type="date"></div><div class="mr-field"><label>To Date</label><input class="mr-input" id="mr_to" type="date"></div>
    <div class="mr-field"><label>Department</label><select class="mr-select" id="mr_department"><option value="">All Departments</option><option>Weaving</option><option>Spinning</option><option>Centralized</option><option>Unmapped</option></select></div>
    <div class="mr-field"><label>Building / House Type</label><select class="mr-select" id="mr_building"><option value="">All Buildings</option></select></div><div class="mr-field"><label>Unit</label><select class="mr-select" id="mr_unit"><option value="">All Units</option></select></div><div class="mr-field"><label>Room</label><select class="mr-select" id="mr_room"><option value="">All Rooms</option></select></div>
    <div class="mr-actions"><button class="mr-btn primary" id="mr_run" type="button">▶ Run</button><button class="mr-btn" id="mr_reset" type="button">Reset</button></div>
   </div>
   <div class="mr-kpis"><div class="mr-kpi"><label>Meters / Rows</label><strong id="mr_kpi_meters">0</strong></div><div class="mr-kpi"><label>Total Consumption</label><strong id="mr_kpi_consumption">0</strong></div><div class="mr-kpi warn"><label>Unmapped</label><strong id="mr_kpi_unmapped">0</strong></div><div class="mr-kpi"><label>Data Source</label><strong id="mr_kpi_source" style="font-size:15px">-</strong></div></div>
   <div class="mr-status" id="mr_status">Ready.</div><div class="mr-tablewrap"><table class="mr-table"><thead><tr><th>Department</th><th>Building</th><th>Unit</th><th>Room</th><th>Meter</th><th>Opening</th><th>Closing</th><th>Consumption</th><th>Status</th></tr></thead><tbody id="mr_rows"><tr><td colspan="9">Run analysis to view rows.</td></tr></tbody></table></div>
  </div>
  <div class="mr-hidden" id="employeeMode">
   <div class="mr-employee-filters"><div class="mr-field"><label>Billing Month (MM-YYYY)</label><input class="mr-input mr-mono" id="emp_month" placeholder="MM-YYYY" value="{{ now()->format('m-Y') }}"></div><div class="mr-field"><label>Employee ID / Name</label><input class="mr-input" id="emp_search" placeholder="Search employee"></div><button class="mr-btn primary" id="emp_run" type="button">Load Allocation</button></div>
   <div class="mr-status" id="emp_status">Uses finalized employee-wise billing allocation.</div><div class="mr-tablewrap"><table class="mr-table"><thead><tr><th>Employee</th><th>Name</th><th>Unit</th><th>Room</th><th>Active Days</th><th>Used Units</th><th>Eligible Units</th><th>Billable Units</th><th>Amount</th></tr></thead><tbody id="emp_rows"><tr><td colspan="9">Load a billing month to view employee allocation.</td></tr></tbody></table></div>
  </div>
 </section>
</main>
<footer class="mr-foot"><div class="mr-footin"><b>Colony Billing Operations</b><span class="mr-links">Meter readings and employee allocation</span></div></footer>
<script>
const csrf=@json(csrf_token()), out=document.getElementById('readingsResult');

const quickReadingForm=document.getElementById('quickReadingForm');
const latestBtn=document.getElementById('latestBtn');
const latestUnit=document.getElementById('latestUnit');

const mr_department=document.getElementById('mr_department');
const mr_building=document.getElementById('mr_building');
const mr_unit=document.getElementById('mr_unit');
const mr_room=document.getElementById('mr_room');
const mr_from=document.getElementById('mr_from');
const mr_to=document.getElementById('mr_to');
const mr_run=document.getElementById('mr_run');
const mr_reset=document.getElementById('mr_reset');
const mr_status=document.getElementById('mr_status');
const mr_kpi_meters=document.getElementById('mr_kpi_meters');
const mr_kpi_consumption=document.getElementById('mr_kpi_consumption');
const mr_kpi_unmapped=document.getElementById('mr_kpi_unmapped');
const mr_kpi_source=document.getElementById('mr_kpi_source');
const mr_rows=document.getElementById('mr_rows');
const meterMode=document.getElementById('meterMode');
const employeeMode=document.getElementById('employeeMode');

const emp_month=document.getElementById('emp_month');
const emp_search=document.getElementById('emp_search');
const emp_run=document.getElementById('emp_run');
const emp_status=document.getElementById('emp_status');
const emp_rows=document.getElementById('emp_rows');

const esc=v=>String(v??'').replace(/[&<>"']/g,s=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
function show(v){out.textContent=JSON.stringify(v,null,2)}
async function req(url,method='GET',payload=null){const o={method,headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}};if(payload!==null){o.headers['Content-Type']='application/json';o.body=JSON.stringify(payload)}const r=await fetch(url,o),j=await r.json().catch(()=>({error:'Non-JSON response'}));show({status:r.status,body:j});return {r,j}}
latestBtn.onclick=()=>req('{{ url('/meter-reading/latest') }}/'+encodeURIComponent(latestUnit.value.trim()));
quickReadingForm.onsubmit=async e=>{
e.preventDefault();
const b=e.submitter,msg=document.getElementById('quickReadingMsg');
b.disabled=true;
msg.style.display='none';
try{
 const {r,j}=await req('{{ url('/meter-reading/upsert') }}','POST',Object.fromEntries(new FormData(e.target)));
 msg.style.display='block';
 if(r.ok){
   msg.style.background='#ecfdf5';
   msg.style.color='#047857';
   msg.textContent='✓ Reading saved successfully';
 }else{
   msg.style.background='#fef2f2';
   msg.style.color='#b91c1c';
   msg.textContent='✕ '+(j.error||j.message||'Reading could not be saved');
 }
}finally{
 b.disabled=false;
}
};
let mrCascadeRows=[],mrCascadeLoaded=false;
function uniq(rows,key){return [...new Set(rows.map(r=>String(r[key]??'').trim()).filter(Boolean))].sort((a,b)=>a.localeCompare(b))}
function options(el,values,label){const keep=el.value;el.innerHTML=`<option value="">${label}</option>`+values.map(v=>`<option value="${esc(v)}">${esc(v)}</option>`).join('');if(values.includes(keep))el.value=keep}
function cascade(level){const rows=mrCascadeRows.filter(r=>(!mr_department.value||r.department===mr_department.value)&&(!mr_building.value||level==='building'||r.building===mr_building.value)&&(!mr_unit.value||level!=='room'||r.unit_id===mr_unit.value));options(mr_building,uniq(rows,'building'),'All Buildings');options(mr_unit,uniq(rows,'unit_id'),'All Units');options(mr_room,uniq(rows,'room_no'),'All Rooms')}
async function meterData(p){const r=await fetch('{{ url('/meters-readings/readings/analysis-data') }}?'+p,{headers:{Accept:'application/json'}});if(!r.ok)throw new Error('Analysis request failed: '+r.status);return r.json()}
async function loadOptions(){mr_status.textContent='Loading options...';const p=new URLSearchParams({from:mr_from.value,to:mr_to.value,department:'',building:'',unit_id:'',room_no:''});const j=await meterData(p);mrCascadeRows=Array.isArray(j.options?.rows)?j.options.rows:(Array.isArray(j.rows)?j.rows:[]);mrCascadeLoaded=true;cascade('init');mr_status.textContent='Filters ready.'}
async function runMeter(){try{if(!mrCascadeLoaded)await loadOptions();mr_status.textContent='Loading...';const p=new URLSearchParams({from:mr_from.value,to:mr_to.value,department:mr_department.value,building:mr_building.value,unit_id:mr_unit.value,room_no:mr_room.value}),j=await meterData(p),rows=Array.isArray(j.rows)?j.rows:[];mr_kpi_meters.textContent=j.summary?.meters??0;mr_kpi_consumption.textContent=j.summary?.total_consumption??0;mr_kpi_unmapped.textContent=j.summary?.unmapped??0;mr_kpi_source.textContent=j.source||'-';mr_rows.innerHTML=rows.length?rows.map(x=>`<tr><td>${esc(x.department)}</td><td>${esc(x.building)}</td><td>${esc(x.unit_id)}</td><td>${esc(x.room_no)}</td><td class="mr-mono">${esc(x.meter_id)}</td><td>${esc(x.opening_date)} ${esc(x.opening_reading)}</td><td>${esc(x.closing_date)} ${esc(x.closing_reading)}</td><td class="mr-num"><b>${esc(x.consumption)}</b></td><td><span class="mr-badge">${esc(x.reading_status||'OK')}</span></td></tr>`).join(''):'<tr><td colspan="9">No rows found.</td></tr>';mr_status.textContent='Loaded '+rows.length+' rows'}catch(e){mr_status.textContent=e.message}}
mr_department.onchange=()=>cascade('department');mr_building.onchange=()=>cascade('building');mr_unit.onchange=()=>cascade('unit');mr_from.onchange=mr_to.onchange=()=>{mrCascadeLoaded=false};mr_run.onclick=runMeter;mr_reset.onclick=()=>{mr_from.value=mr_to.value=mr_department.value=mr_building.value=mr_unit.value=mr_room.value='';mrCascadeLoaded=false;runMeter()};
document.querySelectorAll('.mr-mode').forEach(b=>b.onclick=()=>{document.querySelectorAll('.mr-mode').forEach(x=>x.classList.toggle('active',x===b));meterMode.classList.toggle('mr-hidden',b.dataset.mode!=='meter');employeeMode.classList.toggle('mr-hidden',b.dataset.mode!=='employee')});
let employeeRows=[];function employeeArray(j){for(const v of [j?.rows,j?.data,j?.body?.rows,j?.body?.data,j?.results])if(Array.isArray(v))return v;return Array.isArray(j)?j:[]}
function renderEmployees(){const q=emp_search.value.trim().toLowerCase(),rows=employeeRows.filter(x=>!q||[x.employee_id,x.company_id,x.name,x.employee_name].some(v=>String(v??'').toLowerCase().includes(q)));emp_rows.innerHTML=rows.length?rows.map(x=>`<tr><td class="mr-mono">${esc(x.employee_id??x.company_id)}</td><td>${esc(x.employee_name??x.name)}</td><td>${esc(x.unit_id)}</td><td>${esc(x.room_no)}</td><td>${esc(x.active_days??x.attendance)}</td><td class="mr-num">${esc(x.emp_used_units??x.share_units)}</td><td class="mr-num">${esc(x.eligible_units??x.explain_free_share_units)}</td><td class="mr-num"><b>${esc(x.billable_units??x.explain_billable_units)}</b></td><td class="mr-num">${esc(x.amount??x.share_amount)}</td></tr>`).join(''):'<tr><td colspan="9">No employee allocation rows found.</td></tr>';emp_status.textContent='Loaded '+rows.length+' employee rows'}
emp_run.onclick=async()=>{emp_status.textContent='Loading...';try{const r=await fetch('{{ url('/api/results/employee-wise') }}?month_cycle='+encodeURIComponent(emp_month.value.trim()),{headers:{Accept:'application/json'}}),j=await r.json();if(!r.ok)throw new Error(j.message||'Employee allocation request failed: '+r.status);employeeRows=employeeArray(j);renderEmployees()}catch(e){emp_status.textContent=e.message}};emp_search.oninput=renderEmployees;
</script>
<script src="{{ url('/js/crud-grids.js') }}"></script>
</body></html>
<script>
(function(){
  var m=document.getElementById('impMonth'), h=document.getElementById('impMonthCycle');
  if(!m||!h) return;
  function sync(){ var v=m.value; if(!v) return; var p=v.split('-'); h.value=p[1]+'-'+p[0]; }
  m.addEventListener('change',sync); sync();
})();
</script>
