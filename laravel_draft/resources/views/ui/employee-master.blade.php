@extends('layouts.app')
@section('page_title','People & Residency')
@section('page_subtitle','Flask-parity Employee Master + Family + Occupancy in one workspace.')
@section('content')
<div class="card">
  <div class="toolbar sticky-actions" style="margin-bottom:12px">
    <div class="btn-group" role="group" aria-label="People Residency tabs">
      <button id="tab_btn_employee" class="btn btn-primary" type="button" onclick="setPeopleTab('employee')">Employee</button>
      <button id="tab_btn_family" class="btn" type="button" onclick="setPeopleTab('family')">Family</button>
      <button id="tab_btn_occupancy" class="btn" type="button" onclick="setPeopleTab('occupancy')">Occupancy</button>
    </div>
  </div>

  {{-- EMPLOYEE TAB --}}
  <div id="people_tab_employee" style="margin-top:8px">
    <div class="toolbar sticky-actions" style="margin-bottom:12px">
      <button id="mode_quick" class="btn btn-primary" type="button" onclick="setMode('quick')">Quick Add/Edit</button>
      <button id="mode_bulk" class="btn" type="button" onclick="setMode('bulk')">Bulk Upload</button>
      <button id="mode_manage" class="btn" type="button" onclick="setMode('manage')">Manage/List</button>
    </div>

    <div id="quick_panel" class="banner" style="margin-bottom:10px">
      <div class="toolbar">
        <input id="lookup_id" placeholder="CompanyID" style="max-width:220px">
        <button class="btn" type="button" onclick="fetchById()">Fetch by ID</button>
        <button class="btn" type="button" onclick="saveToRegistry()">Save Draft Registry</button>
        <span class="muted">Quick mode: required fields fill karo, then Add Employee.</span>
      </div>
    </div>

    <div id="bulk_panel" class="card soft" style="display:none;margin-bottom:10px">
      <div class="field"><label class="label">Employee Bulk Upload</label><input id="bulk_csv_file" type="file" accept=".csv,text/csv"></div>
      <div class="toolbar" style="margin-top:8px">
        <button class="btn" type="button" onclick="loadCsvFile()">Load Selected File</button>
        <button class="btn" type="button" onclick="previewBulk()">Import Preview</button>
        <button class="btn btn-success" type="button" onclick="commitBulk()">Commit Valid Rows</button>
      </div>

      <div class="grid" style="margin-top:12px">
        <div class="col-3 card">
          <div class="muted">Loaded Rows</div>
          <div class="kpi" id="bulk_total_rows">0</div>
        </div>
        <div class="col-3 card">
          <div class="muted">Valid Rows</div>
          <div class="kpi" id="bulk_valid_rows">0</div>
        </div>
        <div class="col-3 card">
          <div class="muted">Failed Rows</div>
          <div class="kpi" id="bulk_failed_rows">0</div>
        </div>
        <div class="col-3 card">
          <div class="muted">Committed Rows</div>
          <div class="kpi" id="bulk_commit_rows">0</div>
        </div>
      </div>

      <div class="banner" id="bulk_validation_summary" style="margin-top:10px">No preview yet.</div>

      <div class="grid" style="margin-top:10px">
        <div class="col-6">
          <h4 class="section-title">Valid Rows Preview</h4>
          <div class="table-wrap">
            <table>
              <thead>
                <tr><th>Row</th><th>CompanyID</th><th>Name</th><th>Department</th><th>Designation</th><th>Unit_ID</th></tr>
              </thead>
              <tbody id="bulk_valid_preview_rows">
                <tr><td colspan="6"><div class="empty">No valid rows preview yet.</div></td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="col-6">
          <h4 class="section-title">Failed Rows Summary</h4>
          <div class="table-wrap">
            <table>
              <thead>
                <tr><th>Row</th><th>Error Code</th><th>Error Message</th></tr>
              </thead>
              <tbody id="bulk_failed_preview_rows">
                <tr><td colspan="3"><div class="empty">No failed rows.</div></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <details style="margin-top:10px">
        <summary class="muted">Show raw preview response</summary>
        <pre id="bulk_preview" style="margin-top:8px">Ready.</pre>
      </details>
    </div>

    <div id="quick_form_panel">
      <div class="toolbar" style="margin-bottom:8px">
        <button class="btn" type="button" onclick="showTab('basic')">Basic Info</button>
        <button class="btn" type="button" onclick="showTab('res')">Residence</button>
        <button class="btn" type="button" onclick="showTab('assets')">Assets</button>
      </div>

      <div id="tab-basic" class="form-grid">
        <div class="field col-3"><label class="label">CompanyID*</label><input id="e_CompanyID"></div>
        <div class="field col-3"><label class="label">Name*</label><input id="e_Name"></div>
        <div class="field col-3"><label class="label">Father's Name</label><input id="e_Father"></div>
        <div class="field col-3"><label class="label">CNIC_No.*</label><input id="e_CNIC"></div>
        <div class="field col-3"><label class="label">Mobile_No.</label><input id="e_Mobile"></div>
        <div class="field col-3"><label class="label">Department*</label><input id="e_Department"></div>
        <div class="field col-3"><label class="label">Section</label><input id="e_Section"></div>
        <div class="field col-3"><label class="label">Sub Section</label><input id="e_SubSection"></div>
        <div class="field col-3"><label class="label">Designation*</label><input id="e_Designation"></div>
        <div class="field col-3"><label class="label">Employee Type</label><input id="e_EmployeeType"></div>
        <div class="field col-3"><label class="label">Join Date</label><input id="e_JoinDate" type="date"></div>
        <div class="field col-3"><label class="label">Leave Date</label><input id="e_LeaveDate" type="date"></div>
        <div class="field col-3"><label class="label">Active*</label><select id="e_Active"><option>Yes</option><option>No</option></select></div>
        <div class="field col-3"><label class="label">Remarks</label><input id="e_Remarks"></div>
      </div>

      <div id="tab-res" class="form-grid" style="display:none">
        <div class="field col-3"><label class="label">Colony Type</label><input id="e_ColonyType"></div>
        <div class="field col-3"><label class="label">Block Floor</label><input id="e_BlockFloor"></div>
        <div class="field col-3"><label class="label">Room No</label><input id="e_RoomNo"></div>
        <div class="field col-3"><label class="label">Shared Room</label><input id="e_SharedRoom"></div>
        <div class="field col-3"><label class="label">Unit_ID*</label><input id="e_UnitID"></div>
      </div>

      <div id="tab-assets" class="form-grid" style="display:none">
        @php
        $assets = ['Iron Cot','Single Bed','Double Bed','Mattress','Sofa Set','Bed Sheet','Wardrobe','Centre Table','Wooden Chair','Dinning Table','Dinning Chair','Side Table','Fridge','Water Dispenser','Washing Machine','Air Cooler','A/C','LED','Gyser','Electric Kettle','Wifi Rtr','Water Bottle','LPG cylinder','Gas Stove','Crockery','Kitchen Cabinet','Mug','Bucket','Mirror','Dustbin'];
        $ids = ['Iron Cot'=>'e_IronCot','Single Bed'=>'e_SingleBed','Double Bed'=>'e_DoubleBed','Mattress'=>'e_Mattress','Sofa Set'=>'e_SofaSet','Bed Sheet'=>'e_BedSheet','Wardrobe'=>'e_Wardrobe','Centre Table'=>'e_CentreTable','Wooden Chair'=>'e_WoodenChair','Dinning Table'=>'e_DinningTable','Dinning Chair'=>'e_DinningChair','Side Table'=>'e_SideTable','Fridge'=>'e_Fridge','Water Dispenser'=>'e_WaterDispenser','Washing Machine'=>'e_WashingMachine','Air Cooler'=>'e_AirCooler','A/C'=>'e_AC','LED'=>'e_LED','Gyser'=>'e_Gyser','Electric Kettle'=>'e_ElectricKettle','Wifi Rtr'=>'e_WifiRtr','Water Bottle'=>'e_WaterBottle','LPG cylinder'=>'e_LPG','Gas Stove'=>'e_GasStove','Crockery'=>'e_Crockery','Kitchen Cabinet'=>'e_KitchenCabinet','Mug'=>'e_Mug','Bucket'=>'e_Bucket','Mirror'=>'e_Mirror','Dustbin'=>'e_Dustbin'];
        @endphp
        @foreach($assets as $a)
        <div class="field col-3"><label class="label">{{ $a }}</label><input id="{{ $ids[$a] }}"></div>
        @endforeach
      </div>
    </div>

    <div id="quick_actions" class="toolbar" style="margin-top:10px">
      <button class="btn btn-primary" type="button" onclick="addEmployee()">Add Employee</button>
      <button class="btn" type="button" onclick="upsertEmployee()">Upsert</button>
      <button class="btn" type="button" onclick="saveToRegistry()">Save Draft Registry</button>
    </div>

    <div id="manage_panel" style="display:none;margin-top:12px">
      <div class="toolbar">
        <button class="btn" type="button" onclick="listEmployees(false)">List All</button>
        <button class="btn" type="button" onclick="listEmployees(true)">List Active</button>
        <button class="btn btn-danger" type="button" onclick="markLeft()">Mark Left</button>
        <button class="btn" type="button" onclick="setPeopleTab('family')">View Family</button>
        <button class="btn" type="button" onclick="setPeopleTab('occupancy')">View Occupancy</button>
      </div>
      <div class="form-grid" style="margin-top:8px">
        <div class="field col-3"><label class="label">Search (any)</label><input id="mf_q" oninput="applyFilters()"></div>
        <div class="field col-3"><label class="label">Department</label><input id="mf_dept" oninput="applyFilters()"></div>
        <div class="field col-3"><label class="label">Designation</label><input id="mf_desg" oninput="applyFilters()"></div>
        <div class="field col-3"><label class="label">Active</label><select id="mf_active" onchange="applyFilters()"><option value="">All</option><option>Yes</option><option>No</option></select></div>
      </div>
      <div id="emp_list" class="table-wrap" style="margin-top:10px"></div>
      <div class="toolbar" style="margin-top:8px">
        <span id="emp_page_info" class="muted"></span>
        <button class="btn" type="button" onclick="prevEmpPage()">Prev</button>
        <button class="btn" type="button" onclick="nextEmpPage()">Next</button>
      </div>
    </div>
  </div>

  <div id="actionStatus" class="banner" style="margin-top:10px">Ready.</div>
  <details style="margin-top:8px">
    <summary class="muted">Technical response</summary>
    <pre id="out" style="margin-top:8px">{}</pre>
  </details>

  {{-- FAMILY TAB --}}
  <div id="people_tab_family" style="display:none;margin-top:16px">
    <h4>Family</h4>
    <div class="banner small" id="family_header">Select an employee to load family details.</div>
    <div class="toolbar" style="margin:8px 0">
      <button class="btn" type="button" onclick="reloadFamily()">Reload Family</button>
      <button class="btn btn-primary" type="button" onclick="saveFamily()">Save Family</button>
    </div>
    <div class="form-grid" id="family_summary">
      <div class="field col-3"><label class="label">Month Cycle</label><input id="fam_month_cycle" placeholder="MM-YYYY"></div>
      <div class="field col-3"><label class="label">Spouse Name</label><input id="fam_spouse_name"></div>
      <div class="field col-3"><label class="label">Children Count</label><input id="fam_children_count" type="number" min="0"></div>
      <div class="field col-3"><label class="label">School Going Children</label><input id="fam_school_going_children" type="number" min="0" disabled></div>
      <div class="field col-3"><label class="label">Van Using Children</label><input id="fam_van_using_children" type="number" min="0" disabled></div>
      <div class="field col-3"><label class="label">Van Using Adults</label><input id="fam_van_using_adults" type="number" min="0"></div>
      <div class="field col-6"><label class="label">Remarks</label><input id="fam_remarks"></div>
    </div>

    <div class="toolbar" style="margin:12px 0 4px">
      <span class="muted">Children</span>
      <button class="btn" type="button" onclick="addFamilyChildRow()">Add Child</button>
    </div>
    <div class="table-wrap" id="family_children"></div>
  </div>

  {{-- OCCUPANCY TAB --}}
  <div id="people_tab_occupancy" style="display:none;margin-top:16px">
    <h4>Occupancy</h4>
    <div class="banner small" id="occupancy_header">Select an employee to load occupancy context.</div>
    <div class="toolbar" style="margin:8px 0">
      <button class="btn" type="button" onclick="reloadOccupancy()">Reload Occupancy</button>
      <a class="btn" href="/housing-occupancy" target="_blank">Open Full Occupancy Workspace</a>
    </div>
    <div class="form-grid" id="occupancy_summary"></div>
    <div class="table-wrap" style="margin-top:8px" id="occupancy_rows"></div>
  </div>
</div>

<script>
const csrf=@json(csrf_token());
let BULK_CSV_TEXT='';
let EMP_ROWS=[]; let EMP_FILTERED=[]; let EMP_PAGE=1; const PAGE_SIZE=25;

function v(id){ return (document.getElementById(id)?.value||'').trim(); }
let SELECTED_EMPLOYEE_STATE = null;
function normalizedRowId(source=null){
  return String(
    source?.CompanyID ??
    source?.company_id ??
    source?.employee_id ??
    source?.EmployeeID ??
    ''
  ).trim();
}
function monthFromCurrentDate(){
  const now=new Date();
  const mm=String(now.getMonth()+1).padStart(2,'0');
  const yyyy=String(now.getFullYear());
  return `${mm}-${yyyy}`;
}
function normalizeCompanyId(source=null){
  return String(
    normalizedRowId(source) ||
    normalizedRowId(SELECTED_EMPLOYEE_STATE) ||
    v('lookup_id') ||
    v('e_CompanyID') ||
    ''
  ).trim();
}
function currentCompanyId(source=null){ return normalizeCompanyId(source); }
function currentUiMonthCycle(){
  return document.getElementById('fam_month_cycle')?.value?.trim() ||
    document.querySelector('[name="month_cycle"]')?.value?.trim() ||
    document.querySelector('#month_cycle')?.value?.trim() ||
    document.querySelector('[data-month-cycle]')?.getAttribute('data-month-cycle')?.trim() ||
    document.querySelector('[data-current-month-cycle]')?.getAttribute('data-current-month-cycle')?.trim() ||
    '';
}
function normalizeMonthCycle(explicitMonth=''){
  return String(
    explicitMonth ||
    document.getElementById('fam_month_cycle')?.value ||
    document.querySelector('[name="month_cycle"]')?.value ||
    document.querySelector('#month_cycle')?.value ||
    currentUiMonthCycle() ||
    monthFromCurrentDate()
  ).trim();
}
function effectiveMonthCycle(explicitMonth=''){
  return normalizeMonthCycle(explicitMonth);
}
function setEmployeeContextFromForm(row=null){
  if(row){ SELECTED_EMPLOYEE_STATE=row; }
  const cid=normalizeCompanyId(row);
  if(cid){
    const lookup=document.getElementById('lookup_id');
    if(lookup) lookup.value=cid;
    const emp=document.getElementById('e_CompanyID');
    if(emp) emp.value=cid;
  }
  const normalizedResidence={
    unit_id: row?.unit_id ?? row?.Unit_ID ?? v('e_UnitID') ?? '',
    room_no: row?.room_no ?? row?.['Room No'] ?? v('e_RoomNo') ?? '',
    block_floor: row?.block_floor ?? row?.['Block Floor'] ?? v('e_BlockFloor') ?? '',
    colony_type: row?.colony_type ?? row?.['Colony Type'] ?? v('e_ColonyType') ?? '',
  };
  if(document.getElementById('e_UnitID') && normalizedResidence.unit_id) document.getElementById('e_UnitID').value=normalizedResidence.unit_id;
  if(document.getElementById('e_RoomNo') && normalizedResidence.room_no) document.getElementById('e_RoomNo').value=normalizedResidence.room_no;
  if(document.getElementById('e_BlockFloor') && normalizedResidence.block_floor) document.getElementById('e_BlockFloor').value=normalizedResidence.block_floor;
  if(document.getElementById('e_ColonyType') && normalizedResidence.colony_type) document.getElementById('e_ColonyType').value=normalizedResidence.colony_type;
  const month=normalizeMonthCycle(document.getElementById('fam_month_cycle')?.value || '');
  const famMonth=document.getElementById('fam_month_cycle');
  if(famMonth && month){ famMonth.value=month; }
  return { company_id: cid, month_cycle: month, ...normalizedResidence };
}
function buildOccupancyWorkspaceHref(){
  const link=document.querySelector('a[href="/housing-occupancy"], a[href^="/housing-occupancy?"]');
  if(!link) return;
  const ctx=setEmployeeContextFromForm();
  const params=new URLSearchParams();
  if(ctx.company_id) params.set('company_id', ctx.company_id);
  if(ctx.month_cycle) params.set('month_cycle', ctx.month_cycle);
  link.href = params.toString() ? `/housing-occupancy?${params.toString()}` : '/housing-occupancy';
}

function setPeopleTab(tab){
  ['employee','family','occupancy'].forEach(t=>{
    const pane=document.getElementById('people_tab_'+t);
    const btn=document.getElementById('tab_btn_'+t);
    if(pane){ pane.style.display=(t===tab?'':'none'); }
    if(btn){ btn.className = 'btn' + (t===tab ? ' btn-primary' : ''); }
  });
  if(tab==='family'){
    setEmployeeContextFromForm();
    buildOccupancyWorkspaceHref();
    reloadFamily();
  }
  if(tab==='occupancy'){
    setEmployeeContextFromForm();
    buildOccupancyWorkspaceHref();
    reloadOccupancy();
  }
}

async function reloadFamily(){
  const header=document.getElementById('family_header');
  const children=document.getElementById('family_children');
  const ctx=setEmployeeContextFromForm();
  const cid=ctx.company_id;
  const month=ctx.month_cycle;
  if(!cid){ header.textContent='Set CompanyID in Employee tab first.'; children.innerHTML='';
    document.getElementById('fam_month_cycle').value='';
    document.getElementById('fam_spouse_name').value='';
    document.getElementById('fam_children_count').value='';
    document.getElementById('fam_school_going_children').value='';
    document.getElementById('fam_van_using_children').value='';
    document.getElementById('fam_van_using_adults').value='';
    document.getElementById('fam_remarks').value='';
    return; }
  header.textContent='Loading family for '+cid+'...';
  const query=new URLSearchParams({company_id:cid, month_cycle:month});
  const r=await req('/family/details/context?'+query.toString());
  show(r);
  if(r.status!==200 || r.body?.status!=='ok'){ header.textContent='Failed to load family details.'; return; }
  const row=r.body?.row || {};
  header.textContent='Family for '+cid;
  document.getElementById('fam_month_cycle').value=month||'';
  buildOccupancyWorkspaceHref();
  document.getElementById('fam_spouse_name').value=row.spouse_name||'';
  document.getElementById('fam_children_count').value=row.children_count??'';
  document.getElementById('fam_school_going_children').value=row.school_going_children??'';
  document.getElementById('fam_van_using_children').value=row.van_using_children??'';
  document.getElementById('fam_van_using_adults').value=row.van_using_adults??'';
  document.getElementById('fam_remarks').value=row.remarks||'';
  const detail=await req('/family/details?'+query.toString());
  if(detail.status===200 && detail.body?.status==='ok'){
    const rows=detail.body.rows||[];
    const family=(rows.find(x=>String(x.month_cycle)===String(month) && String(x.company_id)===String(cid)))||null;
    if(family){
      document.getElementById('fam_spouse_name').value=family.spouse_name||'';
      document.getElementById('fam_children_count').value=family.children_count??'';
      document.getElementById('fam_school_going_children').value=family.school_going_children??'';
      document.getElementById('fam_van_using_children').value=family.van_using_children??'';
      document.getElementById('fam_van_using_adults').value=family.van_using_adults??'';
      document.getElementById('fam_remarks').value=family.remarks||'';
      children.innerHTML='';
      const childRows=family.children||[];
      if(childRows.length===0){
        children.innerHTML='<div class="empty">No child profiles yet.</div>';
      } else {
        childRows.forEach(c=>addFamilyChildRow(c));
      }
      return;
    }
  }
  children.innerHTML='<div class="empty">No child profiles yet.</div>';
}

function collectFamilyChildren(){
  const box=document.getElementById('family_children');
  const inputs=box.querySelectorAll('input[data-index],select[data-index]');
  const byIdx={};
  inputs.forEach(el=>{
    const idx=el.getAttribute('data-index');
    const field=el.getAttribute('data-field');
    if(!(idx in byIdx)) byIdx[idx]={};
    byIdx[idx][field]=el.value;
  });
  const rows=[];
  Object.keys(byIdx).sort((a,b)=>parseInt(a)-parseInt(b)).forEach((idx,i)=>{
    const r=byIdx[idx];
    rows.push({
      child_profile_id:r.child_profile_id||'',
      child_name:r.child_name||'',
      age:r.age||'',
      school_going:r.school_going||'0',
      school_name:r.school_name||'',
      class_name:r.class_name||'',
      van_using_child:r.van_using_child||'0',
      transport_join_date:r.transport_join_date||'',
      transport_leave_date:r.transport_leave_date||'',
      default_route_label:r.default_route_label||'',
      notes:r.notes||'',
    });
  });
  return rows;
}

function addFamilyChildRow(prefill={}){
  const box=document.getElementById('family_children');
  const existing=box.querySelectorAll('tr').length-1; // minus header
  const idx=existing>=0?existing:0;
  const row=`<tr>
    <td>${idx+1}<input type="hidden" data-field="child_profile_id" data-index="${idx}" value="${prefill.child_profile_id||''}"></td>
    <td><input data-field="child_name" data-index="${idx}" value="${prefill.child_name||''}"></td>
    <td><input type="number" min="0" data-field="age" data-index="${idx}" value="${prefill.age||''}"></td>
    <td><select data-field="school_going" data-index="${idx}"><option value="0" ${(String(prefill.school_going||'0')==='0'?'selected':'')}>No</option><option value="1" ${(String(prefill.school_going||'0')==='1'?'selected':'')}>Yes</option></select></td>
    <td><input data-field="school_name" data-index="${idx}" value="${prefill.school_name||''}"></td>
    <td><input data-field="class_name" data-index="${idx}" value="${prefill.class_name||''}"></td>
    <td><select data-field="van_using_child" data-index="${idx}"><option value="0" ${(String(prefill.van_using_child||'0')==='0'?'selected':'')}>No</option><option value="1" ${(String(prefill.van_using_child||'0')==='1'?'selected':'')}>Yes</option></select></td>
    <td><input type="date" data-field="transport_join_date" data-index="${idx}" value="${prefill.transport_join_date||''}"></td>
    <td><input type="date" data-field="transport_leave_date" data-index="${idx}" value="${prefill.transport_leave_date||''}"></td>
    <td><input data-field="default_route_label" data-index="${idx}" value="${prefill.default_route_label||''}"></td>
    <td><input data-field="notes" data-index="${idx}" value="${prefill.notes||''}"></td>
  </tr>`;
  if(existing<0){
    box.innerHTML=`<table><thead><tr><th>#</th><th>child_name</th><th>age</th><th>school_going</th><th>school_name</th><th>class_name</th><th>van_using_child</th><th>transport_join_date</th><th>transport_leave_date</th><th>default_route_label</th><th>notes</th></tr></thead><tbody>${row}</tbody></table>`;
  } else {
    box.querySelector('tbody').insertAdjacentHTML('beforeend',row);
  }
}

async function saveFamily(){
  const cid=currentCompanyId();
  const header=document.getElementById('family_header');
  if(!cid){ header.textContent='Set CompanyID in Employee tab first.'; return; }
  const month=effectiveMonthCycle(document.getElementById('fam_month_cycle').value);
  if(!month){ header.textContent='Month cycle required for family save.'; return; }
  const payload={
    month_cycle:month,
    company_id:cid,
    spouse_name:document.getElementById('fam_spouse_name').value.trim(),
    van_using_adults:document.getElementById('fam_van_using_adults').value.trim(),
    remarks:document.getElementById('fam_remarks').value.trim(),
    children:collectFamilyChildren(),
  };
  const r=await req('/family/details/upsert','POST',payload);
  show(r);
  if(r.status===200 && r.body?.status==='ok'){
    header.textContent='Family saved for '+cid;
    await reloadFamily();
  } else {
    header.textContent='Failed to save family.';
  }
}

async function reloadOccupancy(){
  const header=document.getElementById('occupancy_header');
  const sum=document.getElementById('occupancy_summary');
  const rowsBox=document.getElementById('occupancy_rows');
  const ctx=setEmployeeContextFromForm();
  const cid=ctx.company_id;
  const month=ctx.month_cycle;
  if(!cid){ header.textContent='Set CompanyID in Employee tab first.'; sum.innerHTML=''; rowsBox.innerHTML=''; return; }
  header.textContent='Loading occupancy for '+cid+'...';
  const query=new URLSearchParams({company_id:cid, month_cycle:month});
  const r=await req('/occupancy/context?'+query.toString());
  show(r);
  const occupancyMessage = String(r.body?.message || r.body?.error || r.body?.detail || '');
  const mappingRequired = occupancyMessage.includes('Unable to resolve occupancy category') || occupancyMessage.includes('complete room mapping first');
  const row=r.body?.row || {};
  const residenceSummary = `
    <div class="field col-3"><label class="label">Month</label><input disabled value="${month||''}"></div>
    <div class="field col-3"><label class="label">Unit_ID</label><input disabled value="${row.unit_id||ctx.unit_id||''}"></div>
    <div class="field col-3"><label class="label">Colony Type</label><input disabled value="${row.colony_type||ctx.colony_type||''}"></div>
    <div class="field col-3"><label class="label">Block Floor</label><input disabled value="${row.block_floor||ctx.block_floor||''}"></div>
    <div class="field col-3"><label class="label">Room No</label><input disabled value="${row.room_no||ctx.room_no||''}"></div>
    <div class="field col-3"><label class="label">Category</label><input disabled value="${row.category||''}"></div>
  `;
  buildOccupancyWorkspaceHref();
  if(r.status!==200 || r.body?.status!=='ok'){
    if(mappingRequired){
      header.textContent='Occupancy mapping required for '+cid;
      sum.innerHTML = residenceSummary;
      rowsBox.innerHTML = '<div class="banner">Occupancy mapping is incomplete for the selected employee/month. Open Full Occupancy Workspace to complete monthly occupancy mapping.</div>';
      return;
    }
    header.textContent='Occupancy status for '+cid;
    sum.innerHTML = residenceSummary;
    rowsBox.innerHTML = '<div class="banner">Occupancy status is temporarily unavailable. Use Full Occupancy Workspace for detail.</div>';
    return;
  }
  header.textContent='Occupancy status for '+cid;
  setEmployeeContextFromForm(row);
  buildOccupancyWorkspaceHref();
  sum.innerHTML = residenceSummary;
  rowsBox.innerHTML = '<div class="banner">Occupancy context is ready. Use full workspace for month-specific editing.</div>';
}

function show(o){
  document.getElementById('out').textContent = JSON.stringify(o,null,2);
  const ok=(o?.status>=200 && o?.status<300) || o?.status==='ok' || o?.body?.status==='ok';
  const msg=String(o?.body?.message || o?.body?.error || o?.error || '');
  const mappingRequired = msg.includes('Unable to resolve occupancy category') || msg.includes('complete room mapping first');
  const el=document.getElementById('actionStatus');
  if(mappingRequired){
    el.className='banner';
    el.textContent='Occupancy mapping is incomplete. Open Full Occupancy Workspace to complete monthly occupancy mapping.';
    return;
  }
  el.className=ok?'banner':'alert';
  el.textContent=ok?'Action completed successfully.':'Action failed. Check technical response.';
}
async function req(url, method='GET', payload=null){
  const opts={method,headers:{'X-CSRF-TOKEN':csrf}};
  if(payload!==null){opts.headers['Content-Type']='application/json';opts.body=JSON.stringify(payload);}
  const r=await fetch(url,opts); const j=await r.json().catch(()=>({raw:'non-json'}));
  return {status:r.status,body:j};
}

function payload(){
  return {
    CompanyID:v('e_CompanyID'), Name:v('e_Name'), "Father's Name":v('e_Father'), "CNIC_No.":v('e_CNIC'), "Mobile_No.":v('e_Mobile'),
    Department:v('e_Department'), Section:v('e_Section'), "Sub Section":v('e_SubSection'), Designation:v('e_Designation'), "Employee Type":v('e_EmployeeType'),
    "Colony Type":v('e_ColonyType'), "Block Floor":v('e_BlockFloor'), "Room No":v('e_RoomNo'), "Shared Room":v('e_SharedRoom'), Unit_ID:v('e_UnitID'),
    "Join Date":v('e_JoinDate'), "Leave Date":v('e_LeaveDate'), Active:v('e_Active'), Remarks:v('e_Remarks'),
    "Iron Cot":v('e_IronCot'), "Single Bed":v('e_SingleBed'), "Double Bed":v('e_DoubleBed'), "Mattress":v('e_Mattress'), "Sofa Set":v('e_SofaSet'),
    "Bed Sheet":v('e_BedSheet'), Wardrobe:v('e_Wardrobe'), "Centre Table":v('e_CentreTable'), "Wooden Chair":v('e_WoodenChair'),
    "Dinning Table":v('e_DinningTable'), "Dinning Chair":v('e_DinningChair'), "Side Table":v('e_SideTable'), Fridge:v('e_Fridge'),
    "Water Dispenser":v('e_WaterDispenser'), "Washing Machine":v('e_WashingMachine'), "Air Cooler":v('e_AirCooler'), "A/C":v('e_AC'), LED:v('e_LED'),
    Gyser:v('e_Gyser'), "Electric Kettle":v('e_ElectricKettle'), "Wifi Rtr":v('e_WifiRtr'), "Water Bottle":v('e_WaterBottle'),
    "LPG cylinder":v('e_LPG'), "Gas Stove":v('e_GasStove'), Crockery:v('e_Crockery'), "Kitchen Cabinet":v('e_KitchenCabinet'),
    Mug:v('e_Mug'), Bucket:v('e_Bucket'), Mirror:v('e_Mirror'), Dustbin:v('e_Dustbin')
  };
}

function fillForm(r){
  const map={e_CompanyID:'CompanyID',e_Name:'Name',e_Father:"Father's Name",e_CNIC:'CNIC_No.',e_Mobile:'Mobile_No.',e_Department:'Department',e_Section:'Section',e_SubSection:'Sub Section',e_Designation:'Designation',e_EmployeeType:'Employee Type',e_ColonyType:'Colony Type',e_BlockFloor:'Block Floor',e_RoomNo:'Room No',e_SharedRoom:'Shared Room',e_UnitID:'Unit_ID',e_JoinDate:'Join Date',e_LeaveDate:'Leave Date',e_Active:'Active',e_Remarks:'Remarks',e_IronCot:'Iron Cot',e_SingleBed:'Single Bed',e_DoubleBed:'Double Bed',e_Mattress:'Mattress',e_SofaSet:'Sofa Set',e_BedSheet:'Bed Sheet',e_Wardrobe:'Wardrobe',e_CentreTable:'Centre Table',e_WoodenChair:'Wooden Chair',e_DinningTable:'Dinning Table',e_DinningChair:'Dinning Chair',e_SideTable:'Side Table',e_Fridge:'Fridge',e_WaterDispenser:'Water Dispenser',e_WashingMachine:'Washing Machine',e_AirCooler:'Air Cooler',e_AC:'A/C',e_LED:'LED',e_Gyser:'Gyser',e_ElectricKettle:'Electric Kettle',e_WifiRtr:'Wifi Rtr',e_WaterBottle:'Water Bottle',e_LPG:'LPG cylinder',e_GasStove:'Gas Stove',e_Crockery:'Crockery',e_KitchenCabinet:'Kitchen Cabinet',e_Mug:'Mug',e_Bucket:'Bucket',e_Mirror:'Mirror',e_Dustbin:'Dustbin'};
  Object.keys(map).forEach(id=>{ const el=document.getElementById(id); if(el) el.value=(r[map[id]]??'');});
}

function showTab(tab){['basic','res','assets'].forEach(t=>document.getElementById('tab-'+t).style.display=(t===tab?'':'none'));}
function setMode(mode){
  const quick=mode==='quick', bulk=mode==='bulk', manage=mode==='manage';
  document.getElementById('quick_panel').style.display=quick?'':'none';
  document.getElementById('bulk_panel').style.display=bulk?'':'none';
  document.getElementById('quick_form_panel').style.display=quick?'':'none';
  document.getElementById('quick_actions').style.display=quick?'':'none';
  document.getElementById('manage_panel').style.display=manage?'':'none';
  if(manage && !EMP_ROWS.length) listEmployees(false);
}

async function fetchById(){
  const ctx=setEmployeeContextFromForm();
  const id=ctx.company_id;
  if(!id){show({status:'error',error:'CompanyID required'});return;}
  const r=await req('/employees/'+encodeURIComponent(id)); show(r);
  const row=r.body?.row || r.body?.employee || (r.body && typeof r.body === 'object' ? r.body : null);
  if(r.status===200 && row){
    SELECTED_EMPLOYEE_STATE=row;
    fillForm({
      ...row,
      Unit_ID: row.Unit_ID ?? row.unit_id ?? '',
      'Room No': row['Room No'] ?? row.room_no ?? '',
      'Block Floor': row['Block Floor'] ?? row.block_floor ?? '',
      'Colony Type': row['Colony Type'] ?? row.colony_type ?? '',
    });
    setEmployeeContextFromForm(row);
    buildOccupancyWorkspaceHref();
  }
}
async function prefillFromRegistry(){
  return fetchById();
}
async function saveToRegistry(){ const r=await req('/registry/employees/upsert','POST',payload()); show(r); }
async function addEmployee(){ const r=await req('/employees/add','POST',payload()); show(r); }
async function upsertEmployee(){ const r=await req('/employees/upsert','POST',payload()); show(r); }
async function markLeft(){ const id=v('e_CompanyID'); if(!id){show({status:'error',error:'CompanyID required'});return;} const r=await req('/employees/'+encodeURIComponent(id),'DELETE'); show(r); }

let BULK_PREVIEW_CACHE=null;
function renderBulkSummary(total, valid, failed, summary){
  document.getElementById('bulk_total_rows').textContent=String(total||0);
  document.getElementById('bulk_valid_rows').textContent=String(valid||0);
  document.getElementById('bulk_failed_rows').textContent=String(failed||0);
  document.getElementById('bulk_validation_summary').textContent=summary||'No preview yet.';
}
function renderBulkValidRows(rows){
  const tbody=document.getElementById('bulk_valid_preview_rows');
  if(!Array.isArray(rows)||rows.length===0){ tbody.innerHTML='<tr><td colspan="6"><div class="empty">No valid rows preview yet.</div></td></tr>'; return; }
  tbody.innerHTML=rows.map(item=>{
    const row=item.row||{};
    return `<tr><td>${item.row_no??''}</td><td>${item.CompanyID??row.CompanyID??''}</td><td>${row.Name??''}</td><td>${row.Department??''}</td><td>${row.Designation??''}</td><td>${row.Unit_ID??''}</td></tr>`;
  }).join('');
}
function renderBulkFailedRows(rows){
  const tbody=document.getElementById('bulk_failed_preview_rows');
  if(!Array.isArray(rows)||rows.length===0){ tbody.innerHTML='<tr><td colspan="3"><div class="empty">No failed rows.</div></td></tr>'; return; }
  tbody.innerHTML=rows.map(item=>`<tr><td>${item.row_no??''}</td><td>${item.error_code??'ERROR'}</td><td>${item.error_message??item.error??''}</td></tr>`).join('');
}
async function loadCsvFile(){
  const f=document.getElementById('bulk_csv_file').files?.[0];
  if(!f){show({status:'error',error:'Select CSV file'});return;}
  BULK_CSV_TEXT=await f.text();
  BULK_PREVIEW_CACHE=null;
  const total=Math.max(0,BULK_CSV_TEXT.split(/\r?\n/).filter(Boolean).length-1);
  document.getElementById('bulk_commit_rows').textContent='0';
  renderBulkSummary(total,0,0,`File loaded: ${f.name}. Run Import Preview to validate rows before commit.`);
  renderBulkValidRows([]);
  renderBulkFailedRows([]);
  document.getElementById('bulk_preview').textContent=JSON.stringify({status:'ok',loaded:f.name,bytes:BULK_CSV_TEXT.length,rows_detected:total},null,2);
  show({status:'ok',loaded:f.name,bytes:BULK_CSV_TEXT.length,rows_detected:total});
}
async function previewBulk(){
  if(!BULK_CSV_TEXT){show({status:'error',error:'Load CSV first'});return;}
  const r=await req('/registry/employees/import-preview','POST',{csv_text:BULK_CSV_TEXT});
  BULK_PREVIEW_CACHE=r.body||null;
  document.getElementById('bulk_preview').textContent=JSON.stringify(r.body,null,2);
  const total=r.body?.total_rows||0, valid=r.body?.accepted_rows||0, failed=r.body?.rejected_rows||0;
  renderBulkSummary(total,valid,failed,`Preview complete. ${valid} valid row(s), ${failed} failed row(s). Review failed rows before commit.`);
  renderBulkValidRows(r.body?.accepted_preview||[]);
  renderBulkFailedRows(r.body?.errors_preview||[]);
  show(r);
}
async function commitBulk(){
  if(!BULK_CSV_TEXT){show({status:'error',error:'Load CSV first'});return;}
  const r=await req('/registry/employees/import-commit','POST',{csv_text:BULK_CSV_TEXT});
  const committed=(r.body?.inserted||0)+(r.body?.updated||0);
  document.getElementById('bulk_commit_rows').textContent=String(committed);
  const total=BULK_PREVIEW_CACHE?.total_rows||0;
  const valid=BULK_PREVIEW_CACHE?.accepted_rows||0;
  const failed=r.body?.rejected??BULK_PREVIEW_CACHE?.rejected_rows??0;
  renderBulkSummary(total,valid,failed,`Commit finished. Inserted: ${r.body?.inserted||0}, Updated: ${r.body?.updated||0}, Rejected: ${failed}.`);
  show(r);
}

function applyFilters(){
  const q=v('mf_q').toLowerCase(), d=v('mf_dept').toLowerCase(), g=v('mf_desg').toLowerCase(), a=v('mf_active');
  EMP_FILTERED=EMP_ROWS.filter(r=>{
    const any=[r.CompanyID,r.Name,r['CNIC_No.'],r.Department,r.Designation,r.Unit_ID].join(' ').toLowerCase();
    if(q && !any.includes(q)) return false;
    if(d && !(r.Department||'').toLowerCase().includes(d)) return false;
    if(g && !(r.Designation||'').toLowerCase().includes(g)) return false;
    if(a && (r.Active||'')!==a) return false;
    return true;
  });
  EMP_PAGE=1; renderRows();
}

async function listEmployees(activeOnly){
  const r=await req('/employees?active_only='+(activeOnly?1:0)); show(r);
  EMP_ROWS=r.body?.rows||[]; EMP_FILTERED=[...EMP_ROWS]; EMP_PAGE=1; renderRows();
}

function renderRows(){
  const box=document.getElementById('emp_list');
  if(!EMP_FILTERED.length){ box.innerHTML='<div class="empty">No rows found.</div>'; document.getElementById('emp_page_info').textContent=''; return; }
  const total=EMP_FILTERED.length, pages=Math.max(1,Math.ceil(total/PAGE_SIZE)); if(EMP_PAGE>pages) EMP_PAGE=pages;
  const s=(EMP_PAGE-1)*PAGE_SIZE, e=Math.min(total,s+PAGE_SIZE), rows=EMP_FILTERED.slice(s,e);
  document.getElementById('emp_page_info').textContent=`Showing ${s+1}-${e} of ${total} (page ${EMP_PAGE}/${pages})`;
  box.innerHTML='<table><thead><tr><th>CompanyID</th><th>Name</th><th>Department</th><th>Designation</th><th>Unit_ID</th><th>Active</th><th>Action</th></tr></thead><tbody>'+rows.map(r=>`<tr><td>${r.CompanyID||''}</td><td>${r.Name||''}</td><td>${r.Department||''}</td><td>${r.Designation||''}</td><td>${r.Unit_ID||''}</td><td>${r.Active||''}</td><td><button class="btn" onclick='editRow(${JSON.stringify(r.CompanyID)})'>Edit</button></td></tr>`).join('')+'</tbody></table>';
}
async function editRow(id){
  const targetId=String(id ?? '').trim();
  const r=EMP_ROWS.find(x=>normalizedRowId(x)===targetId);
  if(!r) return;
  SELECTED_EMPLOYEE_STATE=r;
  fillForm(r);
  setEmployeeContextFromForm(r);
  buildOccupancyWorkspaceHref();
  setMode('quick');
  setPeopleTab('employee');
  await fetchById();
}
function prevEmpPage(){ if(EMP_PAGE>1){EMP_PAGE--; renderRows();} }
function nextEmpPage(){ const p=Math.max(1,Math.ceil(EMP_FILTERED.length/PAGE_SIZE)); if(EMP_PAGE<p){EMP_PAGE++; renderRows();} }

showTab('basic'); setMode('quick'); setPeopleTab('employee'); setEmployeeContextFromForm(); buildOccupancyWorkspaceHref();
</script>
@endsection
