@extends('layouts.app')
@section('page_title','People & Residency')
@section('page_subtitle','Employee master, room assignment, family details and CSV validation in one controlled workspace.')
@section('content')
<style>
.page-head{display:none!important}
.container{padding-top:18px!important}

.people-shell{border:1px solid rgba(109,159,219,.28);background:linear-gradient(180deg,rgba(255,255,255,.92),rgba(238,246,254,.78))}
.people-shell .module-intro{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;padding:4px 2px 14px;border-bottom:1px solid rgba(170,190,216,.45);margin-bottom:14px}
.people-shell .module-intro h3,.people-shell .module-intro h4{margin:0 0 5px;color:#10263d}
.people-shell .module-intro p{margin:0;color:#647a92;font-size:13px;max-width:760px}
.segmented{display:inline-flex;gap:6px;padding:6px;border:1px solid rgba(170,190,216,.45);border-radius:16px;background:rgba(247,251,255,.75)}
.people-panel-title{font-size:13px;font-weight:900;color:#315579;text-transform:uppercase;letter-spacing:.7px;margin:0 0 8px}
#quick_panel{background:linear-gradient(90deg,rgba(231,240,251,.95),rgba(247,251,255,.82));border:1px solid rgba(109,159,219,.22)}
#bulk_panel{border:1px solid rgba(109,159,219,.22)}
#actionStatus{font-weight:700}
.people-help{font-size:12px;color:#647a92;margin-top:6px}
.people-kpi-card{grid-column:span 3;text-align:left;cursor:pointer;border-color:rgba(109,159,219,.26)}
.people-kpi-card:hover{transform:translateY(-1px);border-color:rgba(109,159,219,.45)}
.people-kpi-card .kpi{margin:4px 0;font-size:26px}
</style>
<div class="card people-shell">
  <div class="module-intro">
    <div>
      <h3>Employee Master Control</h3>
      <p>Manage employee status, residency assignment, CSV validation and family links from one controlled workspace.</p>
    </div>
    <span class="badge">Employee Operations</span>
  </div>

  <div class="grid people-kpi-grid" style="margin-bottom:14px">
    <button type="button" class="card people-kpi-card" onclick="openEmployeeMetric('all')">
      <div class="muted">Total Employees</div>
      <div class="kpi" id="kpi_total_employees">Loading...</div>
      <div class="people-help">Click to view complete employee list</div>
    </button>
    <button type="button" class="card people-kpi-card" onclick="openEmployeeMetric('active')">
      <div class="muted">Active Employees</div>
      <div class="kpi" id="kpi_active_employees">Loading...</div>
      <div class="people-help">Active = Yes</div>
    </button>
    <button type="button" class="card people-kpi-card" onclick="openEmployeeMetric('deactive')">
      <div class="muted">Deactive Employees</div>
      <div class="kpi" id="kpi_deactive_employees">Loading...</div>
      <div class="people-help">Active = No</div>
    </button>
    <button type="button" class="card people-kpi-card" onclick="openEmployeeMetric('missing')">
      <div class="muted">Missing Status</div>
      <div class="kpi" id="kpi_missing_status">Loading...</div>
      <div class="people-help">Blank active status</div>
    </button>
  </div>

  <div class="toolbar sticky-actions" style="margin-bottom:12px">
    <div class="segmented" role="group" aria-label="People Residency tabs">
      <button id="tab_btn_employee" class="btn btn-primary" type="button" onclick="setPeopleTab('employee')">Employee Master</button>
      <button id="tab_btn_family" class="btn" type="button" onclick="setPeopleTab('family')">Family Details</button>
      <button id="tab_btn_occupancy" class="btn" type="button" onclick="setPeopleTab('occupancy')">Occupancy Status</button>
    </div>
  </div>

  {{-- EMPLOYEE TAB --}}
  <div id="people_tab_employee" style="margin-top:8px">
    <div class="toolbar sticky-actions" style="margin-bottom:12px">
      <div class="segmented">
        <button id="mode_quick" class="btn btn-primary" type="button" onclick="setMode('quick')">Search / Edit</button>
        <button id="mode_bulk" class="btn" type="button" onclick="setMode('bulk')">CSV Upload</button>
        <button id="mode_manage" class="btn" type="button" onclick="setMode('manage')">Employee List</button>
      </div>
    </div>


      <!-- HR_ACTIVE_WORKBOOK_UI_START -->
      <div class="card soft" id="hr_active_workbook_panel" style="margin-bottom:10px">
        <div class="toolbar" style="gap:10px;flex-wrap:wrap">
          <span class="badge">HR Workbook Reference</span>
          <input type="month" id="hrWorkbookMonth" style="max-width:160px">
          <input type="file" id="hrWorkbookFile" accept=".xlsx,.csv,.txt" style="max-width:320px">
          <button class="btn btn-primary" type="button" onclick="uploadHrWorkbook()">Upload Monthly HR Workbook</button>
          <button class="btn" type="button" onclick="hrReferenceFillForNewEmployee()">Fetch HR Reference</button>
          <span class="muted" id="hrWorkbookStatus">Reference only for new employees. Existing employees will not be overwritten.</span>
        </div>
      </div>
      <!-- HR_ACTIVE_WORKBOOK_UI_END -->

<div id="quick_panel" class="banner" style="margin-bottom:10px">
      <div class="toolbar">
        <span class="badge">Quick Mode</span>
        <input id="lookup_id" placeholder="CompanyID" style="max-width:220px">
        <button class="btn" type="button" onclick="fetchById()">Fetch by ID</button>
        <button class="btn btn-primary" type="button" onclick="openEmployeeProfile()">Open Employee Profile</button>
        <button class="btn" type="button" onclick="saveToRegistry()">Save to Registry</button>
        <span class="muted">Search by CompanyID, review details, then save changes only when needed.</span>
      </div>
    </div>

    <div id="bulk_panel" class="card soft" style="display:none;margin-bottom:10px">
      <div class="field"><label class="label">Employee CSV Upload & Validation</label><input id="bulk_csv_file" type="file" accept=".csv,text/csv"></div>
      <div class="toolbar" style="margin-top:8px">
        <button class="btn" type="button" onclick="loadCsvFile()">Load Selected File</button>
        <button class="btn" type="button" onclick="previewBulk()">Import Preview</button>
        <button class="btn btn-success" type="button" onclick="commitBulk()">Commit New Valid Rows</button>
        </div>
        <div class="banner small" style="margin-top:8px">
          <div><b>Expected header order</b></div>
          <code id="bulk_header_line"></code>
          <div style="margin-top:6px"><b>Sample row</b></div>
          <code id="bulk_sample_line"></code>
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
          <h4 class="section-title">Accepted Rows Preview</h4>
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
          <h4 class="section-title">Rejected Rows Summary</h4>
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
        <div class="segmented">
          <button class="btn" type="button" onclick="showTab('basic')">Basic Info</button>
          <button class="btn" type="button" onclick="showTab('res')">Residence</button>
          <button class="btn" type="button" onclick="showTab('assets')">Assets</button>
        </div>
      </div>

      <div id="tab-basic" class="form-grid">
        <div class="field col-3"><label class="label">CompanyID*</label><input id="e_CompanyID"></div>
        <div class="field col-3"><label class="label">Name*</label><input id="e_Name"></div>
        <div class="field col-3"><label class="label">Father's Name</label><input id="e_Father"></div>
        <div class="field col-3"><label class="label">CNIC_No.*</label><input id="e_CNIC"></div>
        <div class="field col-3"><label class="label">Mobile_No.</label><input id="e_Mobile"></div>
        <div class="field col-3"><label class="label">Department*</label><input id="e_Department" list="deptOptions" autocomplete="off"></div>
        <div class="field col-3"><label class="label">Section</label><input id="e_Section" list="sectionOptions" autocomplete="off"></div>
        <div class="field col-3"><label class="label">Sub Section</label><input id="e_SubSection" list="subSectionOptions" autocomplete="off"></div>
          <!-- PEOPLE_RESIDENCY_DEPT_CASCADE_DATALISTS_START -->
          <datalist id="deptOptions"></datalist>
          <datalist id="sectionOptions"></datalist>
          <datalist id="subSectionOptions"></datalist>
          <!-- PEOPLE_RESIDENCY_DEPT_CASCADE_DATALISTS_END -->
        <div class="field col-3"><label class="label">Designation*</label><input id="e_Designation"></div>
        <div class="field col-3"><label class="label">Employee Type</label><input id="e_EmployeeType"></div>
        <div class="field col-3"><label class="label">Join Date</label><input id="e_JoinDate" type="date"></div>
        <div class="field col-3"><label class="label">Leave Date</label><input id="e_LeaveDate" type="date"></div>
        <div class="field col-3"><label class="label">Active*</label><select id="e_Active"><option>Yes</option><option>No</option></select></div>
        <div class="field col-3"><label class="label">Remarks</label><input id="e_Remarks"></div>
      </div>

      <div id="tab-res" class="form-grid" style="display:none">
        <div class="field col-3"><label class="label">Residency Type</label><select id="e_ResidencyType"><option value="">Select</option></select></div>
        <div class="field col-3"><label class="label">Colony Type</label><select id="e_ColonyType"><option value="">Select</option></select></div>
        <div class="field col-3"><label class="label">Block Floor</label><select id="e_BlockFloor"><option value="">Select</option></select></div>
        <div class="field col-3"><label class="label">Room No</label><select id="e_RoomNo"><option value="">Select</option></select></div>
        <div class="field col-3"><label class="label">Shared Room</label><select id="e_SharedRoom"><option value="">No</option><option value="Yes">Yes</option></select></div>
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
      <button class="btn btn-primary" type="button" onclick="addEmployee()">Add New Employee</button>
      <button class="btn" type="button" onclick="upsertEmployee()">Update Existing</button>
      <button class="btn" type="button" onclick="saveToRegistry()">Save to Registry</button>
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
      <div id="emp_list_title" class="banner" style="margin-top:10px;display:none">Employee List</div><div id="emp_list" class="table-wrap" style="margin-top:10px"></div>
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
    <div class="module-intro">
      <div>
        <h4>Family</h4>
        <p>Permanent employee-linked family records and movement-ready master data workspace.</p>
      </div>
      <span class="badge success">Family Data</span>
    </div>
    <div id="legacy_family_monthly_controls" style="display:none" aria-hidden="true">
      <div class="banner small" id="family_header">Legacy monthly family controls disabled.</div>
      <div class="form-grid" id="family_summary">
        <div class="field col-3"><label class="label">Month Cycle</label><input id="fam_month_cycle" placeholder="MM-YYYY"></div>
        <div class="field col-3"><label class="label">Spouse Name</label><input id="fam_spouse_name"></div>
        <div class="field col-3"><label class="label">Children Count</label><input id="fam_children_count" type="number" min="0"></div>
        <div class="field col-3"><label class="label">School Going Children</label><input id="fam_school_going_children" type="number" min="0" disabled></div>
        <div class="field col-3"><label class="label">Van Using Children</label><input id="fam_van_using_children" type="number" min="0" disabled></div>
        <div class="field col-3"><label class="label">Van Using Adults</label><input id="fam_van_using_adults" type="number" min="0"></div>
        <div class="field col-6"><label class="label">Remarks</label><input id="fam_remarks"></div>
      </div>
      <div class="table-wrap" id="family_children"></div>
    </div>

    <div class="module-intro" style="margin-top:20px">
      <div>
        <h4>All Permanent Families Registry</h4>
        <p>Read-only permanent family member records for all employees. Search by Company ID, member, relation or source house.</p>
      </div>
      <span class="badge success">All Families</span>
    </div>
    <div class="toolbar" style="margin:8px 0">
      <input id="family_registry_search" placeholder="Search Company ID, member, relation or house..." oninput="filterFamilyRegistry()">
      <button class="btn" type="button" onclick="reloadFamilyRegistry()">Reload All Families</button>
    </div>
    <div class="banner small" id="family_registry_header">Loading permanent family registry...</div>
    <div class="table-wrap" id="family_registry_members">
      <div class="empty">Loading records...</div>
    </div>

    <div class="module-intro" style="margin-top:20px">
      <div>
        <h4>Permanent Family Master</h4>
        <p>Permanent employee-linked family records. This section is read-only at this stage; movement actions will be added separately.</p>
      </div>
      <span class="badge success">Master Records</span>
    </div>
    <div class="banner small" id="family_master_header">Select an employee to view permanent family master records.</div>
    <div class="table-wrap" id="family_master_members">
      <div class="empty">No employee selected.</div>
    </div>
  </div>

  {{-- OCCUPANCY TAB --}}
  <div id="people_tab_occupancy" style="display:none;margin-top:16px">
    <div class="module-intro">
      <div>
        <h4>Occupancy</h4>
        <p>Occupancy context, room linkage, and related workspace launch remain functionally unchanged.</p>
      </div>
      <span class="badge warn">Occupancy</span>
    </div>
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
const BULK_COLUMNS=['CompanyID','Name',"Father's Name",'CNIC_No.','Mobile_No.','Department','Section','Sub Section','Designation','Employee Type','Colony Type','Block Floor','Room No','Shared Room','Join Date','Unit_ID'];
const BULK_SAMPLE_ROW=['E1001','Ali Khan','Muhammad Ashraf','42101-1234567-1','03001234567','Admin','HR','Payroll','Officer','Staff','Family','Block A','R-12','No','2024-01-15','U-001'];

function v(id){ return (document.getElementById(id)?.value||'').trim(); }
function empVal(r,key){
 const map={
 'CompanyID':['CompanyID','company_id'],
 'Name':['Name','name'],
 "Father's Name":["Father's Name",'father_name'],
 'CNIC_No.':['CNIC_No.','cnic_no'],
 'Mobile_No.':['Mobile_No.','mobile_no'],
 'Department':['Department','department'],
 'Section':['Section','section'],
 'Sub Section':['Sub Section','sub_section'],
 'Designation':['Designation','designation'],
 'Employee Type':['Employee Type','employee_type'],
 'Colony Type':['Colony Type','colony_type'],
 'Block Floor':['Block Floor','block_floor'],
 'Room No':['Room No','room_no'],
 'Shared Room':['Shared Room','shared_room'],
 'Join Date':['Join Date','join_date'],
 'Unit_ID':['Unit_ID','unit_id'],
 'Active':['Active','active']
 };
 for(const k of (map[key]||[key])){ if(r && r[k]!==undefined && r[k]!==null) return r[k]; }
 return '';
}
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
  const empGrid=document.getElementById('employee_grid_wrap');
  const famGrid=document.getElementById('family_grid_wrap');
  if(empGrid) empGrid.style.display = tab==='employee' ? '' : 'none';
  if(famGrid) famGrid.style.display = tab==='family' ? '' : 'none';

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

let familyRegistryRows=[];

function renderFamilyRegistry(rows){
  const box=document.getElementById('family_registry_members');
  const header=document.getElementById('family_registry_header');

  header.textContent='All Permanent Families Registry — '+rows.length+' displayed / '+familyRegistryRows.length+' total member(s)';

  if(rows.length===0){
    box.innerHTML='<div class="empty">No matching permanent family records found.</div>';
    return;
  }

  const body=rows.map((x,i)=>`<tr>
    <td>${i+1}</td>
    <td>${familyMasterEsc(x.company_id)}</td>
    <td>${familyMasterEsc(x.member_name)}</td>
    <td>${familyMasterEsc(x.relation)}</td>
    <td>${familyMasterAge(x.age)}</td>
    <td>${String(x.school_going)==='1' ? 'Yes' : 'No'}</td>
    <td>${familyMasterEsc(x.school_name)}</td>
    <td>${familyMasterEsc(x.class_name)}</td>
    <td>${familyMasterEsc(x.current_status)}</td>
    <td>${familyMasterEsc(x.source_room_no)}</td>
  </tr>`).join('');

  box.innerHTML=`<table>
    <thead>
      <tr>
        <th>#</th>
        <th>Company ID</th>
        <th>Member Name</th>
        <th>Relation</th>
        <th>Age</th>
        <th>School Going</th>
        <th>School Name</th>
        <th>Class</th>
        <th>Status</th>
        <th>Source House</th>
      </tr>
    </thead>
    <tbody>${body}</tbody>
  </table>`;
}

function filterFamilyRegistry(){
  const q=String(document.getElementById('family_registry_search')?.value || '').trim().toLowerCase();

  if(!q){
    renderFamilyRegistry(familyRegistryRows);
    return;
  }

  const rows=familyRegistryRows.filter(x => [
    x.company_id,
    x.member_name,
    x.relation,
    x.source_room_no,
    x.source_colony_building_name,
    x.current_status
  ].some(v => String(v ?? '').toLowerCase().includes(q)));

  renderFamilyRegistry(rows);
}

async function reloadFamilyRegistry(){
  const header=document.getElementById('family_registry_header');
  const box=document.getElementById('family_registry_members');

  header.textContent='Loading permanent family registry...';

  const r=await req('/family/members/registry');

  if(r.status!==200 || r.body?.status!=='ok'){
    header.textContent='Failed to load permanent family registry.';
    box.innerHTML='<div class="empty">Unable to load records.</div>';
    return;
  }

  familyRegistryRows=r.body.rows || [];
  filterFamilyRegistry();
}

function familyMasterEsc(value){
  return String(value ?? '').replace(/[&<>"']/g, ch => ({
    '&':'&amp;',
    '<':'&lt;',
    '>':'&gt;',
    '"':'&quot;',
    "'":'&#039;'
  }[ch]));
}

function familyMasterAge(value){
  return value === null || value === undefined || value === '' ? '' : familyMasterEsc(value);
}

async function reloadFamilyMaster(cid){
  const header=document.getElementById('family_master_header');
  const box=document.getElementById('family_master_members');

  if(!cid){
    header.textContent='Select an employee to view permanent family master records.';
    box.innerHTML='<div class="empty">No employee selected.</div>';
    return;
  }

  header.textContent='Loading permanent family master for '+cid+'...';
  const r=await req('/family/members/master?'+new URLSearchParams({company_id:cid}).toString());

  if(r.status!==200 || r.body?.status!=='ok'){
    header.textContent='Failed to load permanent family master.';
    box.innerHTML='<div class="empty">Unable to load family master records.</div>';
    return;
  }

  const rows=r.body.rows||[];
  header.textContent='Permanent Family Master for '+cid+' — '+rows.length+' member(s)';

  if(rows.length===0){
    box.innerHTML='<div class="empty">No permanent family members found.</div>';
    return;
  }

  const body=rows.map((x,i)=>`<tr>
    <td>${i+1}</td>
    <td>${familyMasterEsc(x.member_name)}</td>
    <td>${familyMasterEsc(x.relation)}</td>
    <td>${familyMasterAge(x.age)}</td>
    <td>${String(x.school_going)==='1' ? 'Yes' : 'No'}</td>
    <td>${familyMasterEsc(x.school_name)}</td>
    <td>${familyMasterEsc(x.class_name)}</td>
    <td>${familyMasterEsc(x.current_status)}</td>
    <td>${familyMasterEsc(x.source_room_no)}</td>
  </tr>`).join('');

  box.innerHTML=`<table>
    <thead>
      <tr>
        <th>#</th>
        <th>Member Name</th>
        <th>Relation</th>
        <th>Age</th>
        <th>School Going</th>
        <th>School Name</th>
        <th>Class</th>
        <th>Current Status</th>
        <th>Source House</th>
      </tr>
    </thead>
    <tbody>${body}</tbody>
  </table>`;
}

async function reloadFamily(){
  const header=document.getElementById('family_header');
  const children=document.getElementById('family_children');
  await reloadFamilyRegistry();
  const ctx=setEmployeeContextFromForm();
  const cid=ctx.company_id;
  const month=ctx.month_cycle;
  if(!cid){ header.textContent='Set CompanyID in Employee tab first.'; children.innerHTML='';
    await reloadFamilyMaster('');
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
  await reloadFamilyMaster(cid);
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
    Department:v('e_Department'), department:v('e_Department'), dept:v('e_Department'), Section:v('e_Section'), "Sub Section":v('e_SubSection'), Designation:v('e_Designation'), designation:v('e_Designation'), desig:v('e_Designation'), "Employee Type":v('e_EmployeeType'),
      "Residency Type":v('e_ResidencyType'), residence_type:v('e_ResidencyType'),
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
  const residencyTypeEl=document.getElementById('e_ResidencyType');
  if(residencyTypeEl){
    const rt = r['Residency Type'] ?? r.residence_type ?? r.ResidencyType ?? '';
    if(rt) residencyTypeEl.value = rt;
  }
}

function showTab(tab){['basic','res','assets'].forEach(t=>document.getElementById('tab-'+t).style.display=(t===tab?'':'none'));}
function refreshEmployeeKpis(){
  const total=EMP_ROWS.length;
  const active=EMP_ROWS.filter(r=>String(empVal(r,'Active')||'').trim()==='Yes').length;
  const deactive=EMP_ROWS.filter(r=>String(empVal(r,'Active')||'').trim()==='No').length;
  const missing=EMP_ROWS.filter(r=>String(empVal(r,'Active')||'').trim()==='').length;
  const set=(id,val)=>{const el=document.getElementById(id); if(el) el.textContent=String(val);};
  set('kpi_total_employees',total);
  set('kpi_active_employees',active);
  set('kpi_deactive_employees',deactive);
  set('kpi_missing_status',missing);
}

async function ensureEmployeesLoaded(){
  const setLoading=(txt)=>['kpi_total_employees','kpi_active_employees','kpi_deactive_employees','kpi_missing_status'].forEach(id=>{const el=document.getElementById(id); if(el) el.textContent=txt;});
  if(!EMP_ROWS.length){
    setLoading('Loading...');
    const r=await req('/employees?active_only=0');
    show(r);
    EMP_ROWS=r.body?.rows||[];
    EMP_FILTERED=[...EMP_ROWS];
    EMP_PAGE=1;
    refreshEmployeeKpis();
  }
}

async function openEmployeeMetric(kind){
  await ensureEmployeesLoaded();
  setPeopleTab('employee');
  setMode('manage');
  document.getElementById('mf_q').value='';
  document.getElementById('mf_dept').value='';
  document.getElementById('mf_desg').value='';
  document.getElementById('mf_active').value = kind==='active' ? 'Yes' : (kind==='deactive' ? 'No' : '');
  const title=document.getElementById('emp_list_title');
  if(title){
    title.style.display='';
    title.textContent = kind==='active' ? `Active Employees — ${EMP_ROWS.filter(r=>String(empVal(r,'Active')||'').trim()==='Yes').length} records`
      : kind==='deactive' ? `Deactive Employees — ${EMP_ROWS.filter(r=>String(empVal(r,'Active')||'').trim()==='No').length} records`
      : kind==='missing' ? `Missing Status Employees — ${EMP_ROWS.filter(r=>String(empVal(r,'Active')||'').trim()==='').length} records`
      : `All Employees — ${EMP_ROWS.length} records`;
  }
  if(kind==='missing'){
    EMP_FILTERED=EMP_ROWS.filter(r=>String(empVal(r,'Active')||'').trim()==='');
    EMP_PAGE=1;
    renderRows();
    return;
  }
  applyFilters();
}

function setMode(mode){
  const quick=mode==='quick', bulk=mode==='bulk', manage=mode==='manage';
  document.getElementById('quick_panel').style.display=quick?'':'none';
  document.getElementById('bulk_panel').style.display=bulk?'':'none';
  document.getElementById('quick_form_panel').style.display=quick?'':'none';
  document.getElementById('quick_actions').style.display=quick?'':'none';
  document.getElementById('manage_panel').style.display=manage?'':'none';
  if(manage && !EMP_ROWS.length) listEmployees(false);
}

function openEmployeeProfile(){
  const companyId = String(
    document.getElementById('e_CompanyID')?.value ||
    document.getElementById('lookup_id')?.value ||
    ''
  ).trim();

  if(!companyId){
    setStatus(false, 'Enter or load CompanyID before opening Employee Profile.');
    return;
  }

  window.location.href = '/employee-profile/' + encodeURIComponent(companyId);
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
        'Residency Type': row['Residency Type'] ?? row.residence_type ?? '',
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
function peopleMiniPopup(message, ok=true){
    let box=document.getElementById('peopleMiniPopup');
    if(!box){
      box=document.createElement('div');
      box.id='peopleMiniPopup';
      box.style.position='fixed';
      box.style.top='18px';
      box.style.right='18px';
      box.style.zIndex='99999';
      box.style.maxWidth='360px';
      box.style.padding='14px 18px';
      box.style.borderRadius='14px';
      box.style.boxShadow='0 18px 45px rgba(15,23,42,.20)';
      box.style.fontWeight='800';
      box.style.fontSize='14px';
      box.style.lineHeight='1.4';
      box.style.transition='all .25s ease';
      document.body.appendChild(box);
    }

    box.textContent=message;
    box.style.background=ok?'#ecfdf5':'#fef2f2';
    box.style.color=ok?'#065f46':'#991b1b';
    box.style.border=ok?'1px solid #a7f3d0':'1px solid #fecaca';
    box.style.opacity='1';
    box.style.transform='translateY(0)';

    clearTimeout(window.__peopleMiniPopupTimer);
    window.__peopleMiniPopupTimer=setTimeout(()=>{
      box.style.opacity='0';
      box.style.transform='translateY(-8px)';
    },2600);
  }

  function clearEmployeeEntryFormAfterAdd(){
    const ids=[
      'lookup_id',
      'e_CompanyID','e_Name','e_Father','e_CNIC','e_Mobile',
      'e_Department','e_Section','e_SubSection','e_Designation','e_EmployeeType',
      'e_ColonyType','e_BlockFloor','e_RoomNo','e_SharedRoom','e_UnitID',
      'e_JoinDate','e_LeaveDate','e_Active','e_Remarks',
      'e_IronCot','e_SingleBed','e_DoubleBed','e_Mattress','e_SofaSet',
      'e_BedSheet','e_Wardrobe','e_CentreTable','e_WoodenChair',
      'e_DinningTable','e_DinningChair','e_SideTable','e_Fridge',
      'e_WaterDispenser','e_WashingMachine','e_AirCooler','e_AC','e_LED',
      'e_Gyser','e_ElectricKettle','e_WifiRtr','e_WaterBottle','e_LPG',
      'e_GasStove','e_Crockery','e_KitchenCabinet','e_Mug','e_Bucket',
      'e_Mirror','e_Dustbin'
    ];

    ids.forEach(id=>{
      const el=document.getElementById(id);
      if(el){
        el.value='';
        el.dispatchEvent(new Event('input',{bubbles:true}));
        el.dispatchEvent(new Event('change',{bubbles:true}));
      }
    });

    if(typeof SELECTED_EMPLOYEE_STATE !== 'undefined'){
      SELECTED_EMPLOYEE_STATE=null;
    }

    try{
      if(typeof setEmployeeContextFromForm==='function') setEmployeeContextFromForm(null);
      if(typeof buildOccupancyWorkspaceHref==='function') buildOccupancyWorkspaceHref();
      if(typeof showTab==='function') showTab('basic');
      if(typeof setPeopleTab==='function') setPeopleTab('employee');
    }catch(e){}
  }

  async function addEmployee(){
  const addPayload = (typeof peopleResidencyForceDeptDesigPayload === 'function') ? peopleResidencyForceDeptDesigPayload(payload()) : payload();
  const r = await req('/employees/add','POST',addPayload);

  const err = String(r?.body?.error || r?.body?.message || '').trim();
  const duplicateCompanyId = Number(r?.status) === 409 || err.toLowerCase().includes('companyid already exists');

  if(duplicateCompanyId){
    show(r);

    const msg = 'CompanyID already exists. Add New Employee nahi, Update Existing use karo.';
    const el = document.getElementById('actionStatus');
    if(el){
      el.className = 'alert';
      el.textContent = msg;
    }

    if(typeof peopleMiniPopup === 'function'){
      peopleMiniPopup(msg, false);
    }

    const out = document.getElementById('out');
    if(out){
      out.textContent = JSON.stringify({
        status: 409,
        button: 'Add New Employee',
        error: msg,
        original_response: r
      }, null, 2);
    }
    return;
  }

  show(r);

  const ok = (r?.status >= 200 && r?.status < 300) || r?.body?.status === 'ok';
  if(ok && typeof peopleMiniPopup === 'function'){
    peopleMiniPopup('New employee added successfully.', true);
  }
}
async function upsertEmployee(){ const p=(typeof peopleResidencyForceDeptDesigPayload === 'function') ? peopleResidencyForceDeptDesigPayload(payload()) : payload(); const r=await req('/employees/upsert','POST',p); show(r); }
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
    const any=[empVal(r,'CompanyID'),empVal(r,'Name'),empVal(r,'CNIC_No.'),empVal(r,'Department'),empVal(r,'Designation'),empVal(r,'Unit_ID')].join(' ').toLowerCase();
    if(q && !any.includes(q)) return false;
    if(d && !String(empVal(r,'Department')||'').toLowerCase().includes(d)) return false;
    if(g && !String(empVal(r,'Designation')||'').toLowerCase().includes(g)) return false;
    if(a && String(empVal(r,'Active')||'')!==a) return false;
    return true;
  });
  EMP_PAGE=1; renderRows();
}

async function listEmployees(activeOnly){
  const r=await req('/employees?active_only='+(activeOnly?1:0)); show(r);
  EMP_ROWS=r.body?.rows||[]; EMP_FILTERED=[...EMP_ROWS]; EMP_PAGE=1; refreshEmployeeKpis(); renderRows();
}

const EMP_LIST_COLUMNS=['CompanyID','Name','Department','Designation','Active','Leave Date','Unit_ID','Room No'];
function renderRows(){
  const box=document.getElementById('emp_list');
  if(!EMP_FILTERED.length){ box.innerHTML='<div class="empty">No rows found.</div>'; document.getElementById('emp_page_info').textContent=''; return; }
  const total=EMP_FILTERED.length, pages=Math.max(1,Math.ceil(total/PAGE_SIZE)); if(EMP_PAGE>pages) EMP_PAGE=pages;
  const s=(EMP_PAGE-1)*PAGE_SIZE, e=Math.min(total,s+PAGE_SIZE), rows=EMP_FILTERED.slice(s,e);
  document.getElementById('emp_page_info').textContent=`Showing ${s+1}-${e} of ${total} (page ${EMP_PAGE}/${pages})`;
  box.innerHTML='<table><thead><tr>'+EMP_LIST_COLUMNS.map(c=>`<th>${c}</th>`).join('')+'<th>Action</th></tr></thead><tbody>'+rows.map(r=>`<tr>${EMP_LIST_COLUMNS.map(c=>`<td>${empVal(r,c)||''}</td>`).join('')}<td><button class="btn btn-primary" onclick='editRow(${JSON.stringify(empVal(r,'CompanyID'))})'>Edit</button></td></tr>`).join('')+'</tbody></table>';
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


async function loadResidenceColonies(){
  const typeEl = document.getElementById('e_ResidencyType');
  const colonyEl = document.getElementById('e_ColonyType');
  const blockEl = document.getElementById('e_BlockFloor');
  const roomEl = document.getElementById('e_RoomNo');
  const unitEl = document.getElementById('e_UnitID');
  if(!typeEl || !colonyEl || !blockEl || !roomEl || !unitEl) return;

  function resetBelow(level){
    if(level <= 1) colonyEl.innerHTML = '<option value="">Select</option>';
    if(level <= 2) blockEl.innerHTML = '<option value="">Select</option>';
    if(level <= 3) roomEl.innerHTML = '<option value="">Select</option>';
    unitEl.value = '';
  }

  async function fillSelect(el, url){
    const res = await fetch(url, {headers:{'Accept':'application/json'}});
    const rows = await res.json();
    el.innerHTML = '<option value="">Select</option>';
    rows.forEach(x => {
      const opt = document.createElement('option');
      opt.value = x;
      opt.textContent = x === '__uncategorized' ? 'Uncategorized' : x;
      el.appendChild(opt);
    });
  }

  await fillSelect(typeEl, '/get-residence-types');

  typeEl.addEventListener('change', async () => {
    resetBelow(1);
    if(!typeEl.value) return;
    await fillSelect(colonyEl, '/get-colonies?residence_type=' + encodeURIComponent(typeEl.value));
  });

  colonyEl.addEventListener('change', async () => {
    resetBelow(2);
    if(!typeEl.value || !colonyEl.value) return;
    await fillSelect(blockEl, '/get-blocks/' + encodeURIComponent(colonyEl.value) + '?residence_type=' + encodeURIComponent(typeEl.value));
  });

  blockEl.addEventListener('change', async () => {
    resetBelow(3);
    if(!typeEl.value || !colonyEl.value || !blockEl.value) return;

    const r = await fetch('/get-rooms/' + encodeURIComponent(colonyEl.value) + '/' + encodeURIComponent(blockEl.value) + '?residence_type=' + encodeURIComponent(typeEl.value), {headers:{'Accept':'application/json'}});
    const rooms = await r.json();

    roomEl.innerHTML = '<option value="">Select</option>';
    rooms.forEach(x => {
      const opt = document.createElement('option');
      opt.value = x.room_no;
      opt.textContent = x.room_no;
      opt.dataset.unit = x.unit_id;
      roomEl.appendChild(opt);
    });
  });

  roomEl.addEventListener('change', () => {
    const opt = roomEl.options[roomEl.selectedIndex];
    unitEl.value = opt?.dataset?.unit || '';
  });
}

document.getElementById('bulk_header_line').textContent=BULK_COLUMNS.join(',');
document.getElementById('bulk_sample_line').textContent=BULK_SAMPLE_ROW.join(',');
showTab('basic'); setMode('quick'); setPeopleTab('employee'); setEmployeeContextFromForm(); buildOccupancyWorkspaceHref(); ensureEmployeesLoaded(); loadResidenceColonies();
</script>
{{-- Auto CRUD grids removed from People Residency to prevent double employee grid. --}}

<!-- PEOPLE_RESIDENCY_DEPT_CASCADE_SCRIPT_START -->
<script>
(function(){
  const deptEl=document.getElementById('e_Department');
  const sectionEl=document.getElementById('e_Section');
  const subEl=document.getElementById('e_SubSection');
  const deptList=document.getElementById('deptOptions');
  const sectionList=document.getElementById('sectionOptions');
  const subList=document.getElementById('subSectionOptions');

  if(!deptEl || !sectionEl || !subEl || !deptList || !sectionList || !subList){return;}

  let DEPT_TREE={};

  function esc(v){
    return String(v??'').replace(/[&<>"']/g,function(m){
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
    });
  }

  function setOptions(list, values){
    const clean=[...new Set((values||[]).map(v=>String(v||'').trim()).filter(Boolean))];
    list.innerHTML=clean.map(v=>`<option value="${esc(v)}"></option>`).join('');
  }

  function findKey(obj, value){
    const raw=String(value||'').trim();
    if(!raw)return '';
    if(Object.prototype.hasOwnProperty.call(obj, raw))return raw;
    const lower=raw.toLowerCase();
    return Object.keys(obj).find(k=>String(k).toLowerCase()===lower) || raw;
  }

  function deptKey(){
    return findKey(DEPT_TREE, deptEl.value);
  }

  function sectionKey(dKey){
    const sections=DEPT_TREE[dKey] || {};
    return findKey(sections, sectionEl.value);
  }

  function refreshSections(clearBad){
    const dKey=deptKey();
    const sectionsObj=DEPT_TREE[dKey] || {};
    const sections=Object.keys(sectionsObj).filter(Boolean);
    setOptions(sectionList, sections);

    if(clearBad && sectionEl.value && sections.length){
      const matched=sections.some(x=>x.toLowerCase()===sectionEl.value.trim().toLowerCase());
      if(!matched){
        sectionEl.value='';
        subEl.value='';
      }
    }

    refreshSubSections(clearBad);
  }

  function refreshSubSections(clearBad){
    const dKey=deptKey();
    const sKey=sectionKey(dKey);
    const subs=((DEPT_TREE[dKey]||{})[sKey] || []).filter(Boolean);
    setOptions(subList, subs);

    if(clearBad && subEl.value && subs.length){
      const matched=subs.some(x=>x.toLowerCase()===subEl.value.trim().toLowerCase());
      if(!matched){
        subEl.value='';
      }
    }
  }

  async function loadDeptCascade(){
    try{
      const r=await fetch('/people-residency/dept-cascade',{headers:{'Accept':'application/json'}});
      const j=await r.json();
      if(!r.ok || j.status!=='ok')return;
      DEPT_TREE=j.tree || {};
      setOptions(deptList, j.departments || Object.keys(DEPT_TREE));
      refreshSections(false);
    }catch(e){}
  }

  deptEl.addEventListener('input',()=>refreshSections(true));
  deptEl.addEventListener('change',()=>refreshSections(true));
  sectionEl.addEventListener('input',()=>refreshSubSections(true));
  sectionEl.addEventListener('change',()=>refreshSubSections(true));

  const oldFillForm=window.fillForm;
  if(typeof oldFillForm==='function' && !oldFillForm.__deptCascadeWrapped){
    const wrapped=function(){
      const out=oldFillForm.apply(this, arguments);
      setTimeout(()=>{refreshSections(false);refreshSubSections(false);},0);
      return out;
    };
    wrapped.__deptCascadeWrapped=true;
    window.fillForm=wrapped;
  }

  loadDeptCascade();
})();
</script>
<!-- PEOPLE_RESIDENCY_DEPT_CASCADE_SCRIPT_END -->



<script>
/* PEOPLE_RESIDENCY_DEPT_DESIG_USER_LOCK_START */
(function(){
  let hrApplying = false;
  const userEdited = { e_Department:false, e_Designation:false };
  const userValue = { e_Department:'', e_Designation:'' };

  function getEl(id){ return document.getElementById(id); }

  function restoreIfNeeded(id){
    const el = getEl(id);
    if(!el || !userEdited[id]) return;
    const wanted = userValue[id] ?? '';
    if(String(el.value || '') !== wanted){
      el.value = wanted;
    }
  }

  ['e_Department','e_Designation'].forEach(function(id){
    document.addEventListener('keydown', function(e){
      if(!e.target || e.target.id !== id) return;
      if(hrApplying) return;
      userEdited[id] = true;
      setTimeout(function(){
        const el=getEl(id);
        userValue[id]=el ? String(el.value || '') : '';
        restoreIfNeeded(id);
      }, 0);
    }, true);

    document.addEventListener('input', function(e){
      if(!e.target || e.target.id !== id) return;

      if(hrApplying){
        return;
      }

      if(e.isTrusted){
        userEdited[id] = true;
        userValue[id] = String(e.target.value || '');
        setTimeout(function(){ restoreIfNeeded(id); }, 30);
        setTimeout(function(){ restoreIfNeeded(id); }, 120);
        return;
      }

      if(userEdited[id]){
        restoreIfNeeded(id);
      }
    }, true);

    document.addEventListener('change', function(e){
      if(!e.target || e.target.id !== id) return;
      if(hrApplying) return;

      if(e.isTrusted){
        userEdited[id] = true;
        userValue[id] = String(e.target.value || '');
      } else if(userEdited[id]){
        restoreIfNeeded(id);
      }
    }, true);
  });

  window.peopleResidencyHrApplyStart = function(){
    hrApplying = true;
    userEdited.e_Department = false;
    userEdited.e_Designation = false;
    userValue.e_Department = '';
    userValue.e_Designation = '';
  };

  window.peopleResidencyHrApplyEnd = function(){
    setTimeout(function(){ hrApplying = false; }, 200);
  };

  window.peopleResidencyCanAutoSetField = function(id){
    return !userEdited[id];
  };
})();
/* PEOPLE_RESIDENCY_DEPT_DESIG_USER_LOCK_END */
</script>

<!-- HR_ACTIVE_WORKBOOK_SCRIPT_START -->
<script>
(function(){
  const statusEl = () => document.getElementById('hrWorkbookStatus');

  function setHrStatus(msg, ok=true){
    const el=statusEl();
    if(!el)return;
    el.textContent=msg;
    el.style.color=ok?'#475569':'#b91c1c';
  }

  function val(id){
    return (document.getElementById(id)?.value||'').trim();
  }

  function setIfEmpty(id, value){
    const el=document.getElementById(id);
    if(!el)return;
    const next=String(value??'').trim();
    if(next!=='' && String(el.value||'').trim()===''){
      el.value=next;
      el.dispatchEvent(new Event('input',{bubbles:true}));
      el.dispatchEvent(new Event('change',{bubbles:true}));
    }
  }

  function setHrReferenceValue(id, value){
      const el=document.getElementById(id);
      if(!el)return;

      if((id==='e_Department' || id==='e_Designation') &&
        typeof window.peopleResidencyCanAutoSetField === 'function' &&
        !window.peopleResidencyCanAutoSetField(id)){
        return;
      }

      const next=String(value??'').trim();
      el.value=next;
      el.dispatchEvent(new Event('input',{bubbles:true}));
      el.dispatchEvent(new Event('change',{bubbles:true}));
    }

  function clearHrReferenceEmployeeFields(){
    const ids=[
      'e_CompanyID','e_Name','e_Father','e_CNIC','e_Mobile',
      'e_Department','e_Section','e_SubSection','e_Designation','e_EmployeeType',
      'e_ColonyType','e_BlockFloor','e_RoomNo','e_SharedRoom','e_UnitID',
      'e_JoinDate','e_LeaveDate','e_Active','e_Remarks'
    ];

    ids.forEach(id=>{
      const el=document.getElementById(id);
      if(el){
        el.value='';
        el.dispatchEvent(new Event('input',{bubbles:true}));
        el.dispatchEvent(new Event('change',{bubbles:true}));
      }
    });

    if(typeof SELECTED_EMPLOYEE_STATE !== 'undefined'){
      SELECTED_EMPLOYEE_STATE=null;
    }
  }

  function applyHrReference(row){
    if(!row)return false;
      var __hrCid = String((row && row.CompanyID) || '').trim();
      if(__hrCid && window.__lastHrAppliedCompanyId === __hrCid){ return false; }
      window.__lastHrAppliedCompanyId = __hrCid;
      if(typeof window.peopleResidencyHrApplyStart === 'function') window.peopleResidencyHrApplyStart();

    const map={
      e_CompanyID:'CompanyID',
      e_Name:'Name',
      e_Father:"Father's Name",
      e_CNIC:'CNIC_No.',
      e_Mobile:'Mobile_No.',
      e_Department:'Department',
      e_Section:'Section',
      e_SubSection:'Sub Section',
      e_Designation:'Designation',
      e_EmployeeType:'Employee Type',
      e_ColonyType:'Colony Type',
      e_BlockFloor:'Block Floor',
      e_RoomNo:'Room No',
      e_SharedRoom:'Shared Room',
      e_UnitID:'Unit_ID',
      e_JoinDate:'Join Date'
    };

    clearHrReferenceEmployeeFields();
    Object.keys(map).forEach(id=>setHrReferenceValue(id,row[map[id]]));

    if(row.CompanyID){
      const lookup=document.getElementById('lookup_id');
      if(lookup && !lookup.value)lookup.value=row.CompanyID;
    }

    setTimeout(()=>{
      ['e_Department','e_Section','e_SubSection'].forEach(id=>{
        const el=document.getElementById(id);
        if(el) el.dispatchEvent(new Event('change',{bubbles:true}));
      });
    },80);

    setHrStatus(`HR reference applied for NEW employee ${row.CompanyID || ''} (${row._hr_month_cycle || ''}). Now press Add New Employee to save.`);
    if(typeof window.peopleResidencyHrApplyEnd === 'function') window.peopleResidencyHrApplyEnd();
      return true;
  }

  async function getHrReference(companyId){
    companyId=String(companyId||'').trim();
    if(!companyId)return null;

    const r=await fetch('/hr-active-workbook/reference?company_id='+encodeURIComponent(companyId)+'&_='+Date.now(), {
      headers:{'Accept':'application/json'}
    });

    const j=await r.json().catch(()=>({status:'error',error:'non-json'}));

    if(j.employee_exists){
      setHrStatus('Employee already exists in master. HR workbook reference not applied.', false);
      return null;
    }

    if(!r.ok || j.status!=='ok' || !j.reference_allowed){
      setHrStatus(j.message || ('No HR reference found for '+companyId), false);
      return null;
    }

    return j.row || null;
  }

  window.hrReferenceFillForNewEmployee = async function(){
    const cid = val('e_CompanyID') || val('lookup_id');

    if(!cid){
      setHrStatus('CompanyID required for HR reference.', false);
      return;
    }

    setHrStatus('Checking employee master and HR workbook reference...');

    const row=await getHrReference(cid);

    if(row){
      applyHrReference(row);
    }
  };

  window.uploadHrWorkbook = async function(){
    const month=(document.getElementById('hrWorkbookMonth')?.value||'').trim();
    const fileEl=document.getElementById('hrWorkbookFile');
    const file=fileEl?.files?.[0]||null;

    if(!month){
      setHrStatus('Month required.', false);
      return;
    }

    if(!file){
      setHrStatus('Select HR workbook first. XLSX/CSV allowed.', false);
      return;
    }

    const fd=new FormData();
    fd.append('month_cycle', month);
    fd.append('upload_file', file);

    setHrStatus('Uploading HR workbook reference...');

    const r=await fetch('/hr-active-workbook/upload',{
      method:'POST',
      headers:{'X-CSRF-TOKEN':csrf},
      body:fd
    });

    const j=await r.json().catch(()=>({status:'error',error:'non-json response'}));

    if(!r.ok || j.status!=='ok'){
      setHrStatus(j.error || `Upload failed (${r.status}).`, false);
      return;
    }

    setHrStatus(`HR workbook reference uploaded. Sheets=${j.sheet_count}, imported rows=${j.imported_rows}.`);
  };

  const oldFetchById=window.fetchById;
  if(typeof oldFetchById==='function' && !oldFetchById.__hrReferenceOnlyWrapped){
    const wrapped=async function(){
      const before=(val('lookup_id') || val('e_CompanyID'));
      const out=await oldFetchById.apply(this, arguments);
      const cid=(val('lookup_id') || val('e_CompanyID') || before);
      if(cid){
        const row=await getHrReference(cid);
        if(row)applyHrReference(row);
      }
      return out;
    };
    wrapped.__hrReferenceOnlyWrapped=true;
    window.fetchById=wrapped;
  }

  let timer=null;
  function scheduleReferenceLookup(){
    clearTimeout(timer);
    timer=setTimeout(()=>{
      const cid=val('e_CompanyID') || val('lookup_id');
      if(cid && cid.length>=4){
        getHrReference(cid).then(row=>{ if(row)applyHrReference(row); });
      }
    },800);
  }

  ['e_CompanyID','lookup_id'].forEach(id=>{
    const el=document.getElementById(id);
    if(el){
      el.addEventListener('change', scheduleReferenceLookup);
      el.addEventListener('blur', scheduleReferenceLookup);
    }
  });

  const monthEl=document.getElementById('hrWorkbookMonth');
  if(monthEl && !monthEl.value){
    const d=new Date();
    monthEl.value=d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0');
  }

  async function loadLatestHrWorkbookStatus(){
    try{
      const r=await fetch('/hr-active-workbook/recent', {headers:{'Accept':'application/json'}});
      const j=await r.json().catch(()=>({status:'error'}));
      const latest=(j.uploads||[])[0] || null;

      if(latest){
        const monthEl=document.getElementById('hrWorkbookMonth');
        if(monthEl && latest.month_cycle){
          monthEl.value=String(latest.month_cycle).slice(0,7);
        }

        setHrStatus(
          `Latest HR workbook already loaded: ${latest.original_file_name || 'uploaded file'} · ${latest.month_cycle || ''} · rows ${latest.imported_rows || 0}. File select box refresh par blank rahega, lekin reference data saved hai.`,
          true
        );
      }else{
        setHrStatus('No HR workbook uploaded yet. Upload monthly HR workbook first.', false);
      }
    }catch(e){
      setHrStatus('Could not check latest HR workbook status.', false);
    }
  }

  loadLatestHrWorkbookStatus();
})();
</script>
<!-- HR_ACTIVE_WORKBOOK_SCRIPT_END -->


<script>
/* PEOPLE_RESIDENCY_FETCHED_EDITABLE_PATCH_START */
(function(){
  function enableFetchedEmployeeFields(){
    document.querySelectorAll('[id^="e_"], #quickCompanyId').forEach(function(el){
      if(!el) return;
      if(el.type === 'hidden') return;

      el.removeAttribute('readonly');
      el.readOnly = false;

      el.removeAttribute('disabled');
      el.disabled = false;

      el.classList.remove('readonly');
      el.classList.remove('is-readonly');
      el.classList.remove('disabled');
      el.classList.remove('is-disabled');
    });

    const msg = document.getElementById('actionStatus');
    if(msg && /HR reference applied|Fetch/i.test(String(msg.textContent || ''))){
      msg.textContent = msg.textContent + ' Fields are editable before save.';
    }
  }

  let timer = null;
  function scheduleEnable(){
    clearTimeout(timer);
    timer = setTimeout(enableFetchedEmployeeFields, 80);
  }

  document.addEventListener('DOMContentLoaded', function(){
    enableFetchedEmployeeFields();

    document.addEventListener('click', function(e){
      const t = e.target;
      if(!t) return;
      const text = String(t.textContent || '').trim().toLowerCase();
      if(text.includes('fetch by id') || text.includes('add new employee') || text.includes('update existing')){
        setTimeout(enableFetchedEmployeeFields, 250);
        setTimeout(enableFetchedEmployeeFields, 700);
      }
    });

    const obs = new MutationObserver(scheduleEnable);
    obs.observe(document.body, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['disabled','readonly','value']
    });
  });

  window.enableFetchedEmployeeFields = enableFetchedEmployeeFields;
})();
/* PEOPLE_RESIDENCY_FETCHED_EDITABLE_PATCH_END */
</script>


<script>
/* PEOPLE_RESIDENCY_FORCE_DEPT_DESIG_PATCH_START */
(function(){
  function currentVal(id){
    const el = document.getElementById(id);
    return el ? String(el.value || '').trim() : '';
  }

  window.peopleResidencyForceDeptDesigPayload = function(p){
    p = p || {};
    const dept = currentVal('e_Department');
    const desig = currentVal('e_Designation');

    if(dept){
      p.Department = dept;
      p.department = dept;
      p.dept = dept;
    }

    if(desig){
      p.Designation = desig;
      p.designation = desig;
      p.desig = desig;
    }

    return p;
  };
})();
/* PEOPLE_RESIDENCY_FORCE_DEPT_DESIG_PATCH_END */
</script>

@endsection


<!-- EMPLOYEE_MASTER_DASHBOARD_QUERY_START -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qs = new URLSearchParams(window.location.search);

    function clean(v) {
        return (v || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function clickByText(patterns) {
        const els = document.querySelectorAll('button, a, [role="button"], .btn, .nav-link, [class*="tab"]');
        for (const el of els) {
            const t = clean(el.innerText || el.textContent || el.getAttribute('aria-label'));
            if (patterns.some(p => t.includes(p))) {
                el.click();
                return true;
            }
        }
        return false;
    }

    setTimeout(function () {
        if (qs.get('mode') === 'add') {
            clickByText(['add employee', 'new employee', 'create employee']);
        }

        if (qs.get('open') === 'residence') {
            clickByText(['residence', 'assign residence', 'room', 'unit']);
        }
    }, 500);
});
</script>
<!-- EMPLOYEE_MASTER_DASHBOARD_QUERY_END -->
