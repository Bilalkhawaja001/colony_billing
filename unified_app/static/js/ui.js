const v=id=>document.getElementById(id)?.value||'';
const n=id=>Number(v(id)||0);

async function post(path,payload){
  const r=await fetch(path,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
  const txt=await r.text();
  const out=document.getElementById('out');
  if(out) out.textContent=txt;
  try{return JSON.parse(txt);}catch{ return {status:r.ok?'ok':'error',raw:txt}; }
}

async function get(path){
  const r=await fetch(path);
  const txt=await r.text();
  const out=document.getElementById('out');
  if(out) out.textContent=txt;
  try{return JSON.parse(txt);}catch{ return {status:r.ok?'ok':'error',raw:txt}; }
}

function actorId(){ return Number(v('actor') || 1); }

async function runBilling(){
  await post('/billing/run',{month_cycle:v('bm'),run_key:v('rk'),actor_user_id:actorId()});
}

async function approveBilling(){
  await post('/billing/approve',{run_id:n('rid'),actor_user_id:actorId()});
}

async function lockBilling(){
  await post('/billing/lock',{run_id:n('rid'),actor_user_id:actorId()});
}

async function fingerprintBilling(){
  await get(`/billing/fingerprint?month_cycle=${encodeURIComponent(v('bm'))}`);
}

async function upsertMeterUnits(){
  await post('/meter-unit/upsert',{month_cycle:v('bm'),unit_id:v('mu_unit'),meter_units:Number(v('mu_units')||0)});
}

async function computeElecSplit(){
  await post('/billing/elec/compute',{month_cycle:v('bm'),zero_attendance_policy:'zero'});
}

async function createAdjustment(){
  const res = await post('/billing/adjustments/create',{
    month_cycle:v('bm'),
    employee_id:v('adj_employee'),
    utility_type:v('adj_utility'),
    amount_delta:Number(v('adj_delta')||0),
    reason:v('adj_reason'),
    actor_user_id:actorId(),
  });
  if(res?.adjustment_id){
    const el=document.getElementById('adj_id');
    if(el) el.value=res.adjustment_id;
  }
  await listAdjustments();
}

async function approveAdjustment(){
  await post('/billing/adjustments/approve',{adjustment_id:n('adj_id'),actor_user_id:actorId()});
  await listAdjustments();
}

function renderAdjustmentRows(rows){
  const body=document.getElementById('adj_rows');
  if(!body) return;
  if(!rows?.length){
    body.innerHTML = "<tr><td colspan='8' class='text-muted'>No rows found</td></tr>";
    return;
  }
  body.innerHTML = rows.map(r=>`<tr>
    <td>${r.id ?? ''}</td>
    <td>${r.month_cycle ?? ''}</td>
    <td>${r.employee_id ?? ''}</td>
    <td>${r.utility_type ?? ''}</td>
    <td>${r.amount_delta ?? ''}</td>
    <td><span class='chip ${r.status==='APPROVED'?'chip-ok':'chip-soft'}'>${r.status ?? ''}</span></td>
    <td>${r.created_by_user_id ?? ''}</td>
    <td>${r.approved_by_user_id ?? ''}</td>
  </tr>`).join('');
}

async function listAdjustments(){
  const month = encodeURIComponent(v('bm'));
  const emp = encodeURIComponent(v('adj_employee'));
  const res = await get(`/billing/adjustments/list?month_cycle=${month}&employee_id=${emp}`);
  renderAdjustmentRows(res?.rows || []);
}

(function initShell(){
  const key='ubp_sidebar_collapsed';
  const body=document.body;
  const btn=document.getElementById('sidebarToggle');
  if(localStorage.getItem(key)==='1') body.classList.add('sidebar-collapsed');

  if(btn){
    btn.addEventListener('click',()=>{
      if(window.innerWidth<=768){
        body.classList.toggle('sidebar-open');
        return;
      }
      body.classList.toggle('sidebar-collapsed');
      localStorage.setItem(key, body.classList.contains('sidebar-collapsed') ? '1' : '0');
    });
  }

  if(document.getElementById('adj_rows')){
    listAdjustments();
  }
})();
