@extends('layouts.app')
@section('page_title','Residence Manager')
@section('page_subtitle','Employee, family, occupancy and data operations workspace.')
@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
body{background:#f8f9fb!important;font-family:Inter,Arial,sans-serif!important}
body .sidebar,body .top,body .page-head,body .cb-shell{display:none!important}
body .app,body .main{display:block!important;min-height:100vh!important;padding:0!important;margin:0!important}body .sidebar,body .top,body .page-head,body .cb-shell{display:none!important;height:0!important;min-height:0!important;padding:0!important;margin:0!important;border:0!important;overflow:hidden!important}body .content,body .page,body .wrap,body .main-inner{padding-top:0!important;margin-top:0!important}
body .container{padding:0!important;margin:0!important;max-width:none!important;width:100%!important}body .app{padding:0!important;margin:0!important;display:block!important}body main.main{padding:0!important;margin:0!important}body header.top{display:none!important;height:0!important}body .page-head{display:none!important;height:0!important;margin:0!important;padding:0!important}#residenceManagerApp{padding-top:0!important;margin-top:0!important}
#residenceManagerApp{--rm-primary:#003d9b;--rm-primary-container:#0052cc;--rm-on-primary:#fff;--rm-bg:#f8f9fb;--rm-surface:#fff;--rm-low:#f3f4f6;--rm-high:#e7e8ea;--rm-line:#c3c6d6;--rm-text:#191c1e;--rm-muted:#434654;--rm-error:#ba1a1a;--rm-success:#08724d;--rm-warning:#8b5a00;min-height:100vh;background:var(--rm-bg);color:var(--rm-text);font-size:14px;line-height:20px}
#residenceManagerApp *{box-sizing:border-box}
#residenceManagerApp button,#residenceManagerApp input,#residenceManagerApp select,#residenceManagerApp textarea{font:inherit}
#residenceManagerApp .material-symbols-outlined{font-family:'Material Symbols Outlined';font-weight:normal;font-style:normal;font-size:20px;line-height:1;letter-spacing:normal;text-transform:none;display:inline-block;white-space:nowrap;word-wrap:normal;direction:ltr;-webkit-font-feature-settings:'liga';-webkit-font-smoothing:antialiased}
.rm-topbar{height:64px;background:#fff;border-bottom:1px solid var(--rm-line);position:sticky;top:0;z-index:50}
.rm-topbar-inner{height:100%;max-width:1440px;margin:auto;padding:0 32px;display:flex;align-items:center;justify-content:space-between;gap:20px}
.rm-brand-group{display:flex;align-items:center;gap:32px;height:100%}.rm-brand{font-size:18px;line-height:24px;font-weight:700;color:var(--rm-primary);white-space:nowrap}
.rm-nav{display:flex;align-items:center;height:100%;gap:24px}.rm-nav button{height:100%;border:0;border-bottom:2px solid transparent;background:transparent;padding:0 2px;color:var(--rm-muted);font-size:12px;font-weight:600;letter-spacing:.04em;cursor:pointer}.rm-nav button:hover,.rm-nav button.is-active{color:var(--rm-primary)}.rm-nav button.is-active{border-color:var(--rm-primary)}
.rm-top-actions{display:flex;align-items:center;gap:8px}.rm-global-search{width:230px;height:36px;position:relative}.rm-global-search .material-symbols-outlined{position:absolute;left:10px;top:8px;color:#737685}.rm-global-search input{width:100%;height:100%;padding:7px 10px 7px 36px;border:1px solid var(--rm-line);border-radius:4px;background:#f8f9fb;color:var(--rm-text);outline:none}.rm-icon-btn{width:36px;height:36px;border:0;border-radius:50%;background:transparent;color:var(--rm-muted);display:grid;place-items:center;cursor:pointer}.rm-icon-btn:hover{background:#edeef0}.rm-avatar{width:32px;height:32px;border-radius:50%;background:var(--rm-primary-container);color:#fff;display:grid;place-items:center;font-size:12px;font-weight:700;border:1px solid var(--rm-line)}
.rm-main{max-width:1440px;margin:auto;padding:24px 32px 40px}.rm-view{display:none}.rm-view.is-active{display:block}.rm-page-head{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:24px}.rm-page-head h1{margin:0;font-size:24px;line-height:32px;font-weight:600;letter-spacing:-.01em}.rm-page-head p{margin:2px 0 0;color:var(--rm-muted)}
.rm-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.rm-btn{height:40px;border:1px solid var(--rm-line);border-radius:4px;background:#fff;color:#2f3440;padding:0 16px;font-size:12px;font-weight:600;letter-spacing:.03em;display:inline-flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;text-decoration:none}.rm-btn:hover{background:#f3f4f6}.rm-btn-primary{background:var(--rm-primary);border-color:var(--rm-primary);color:#fff}.rm-btn-primary:hover{background:#00327f}.rm-btn-danger{background:var(--rm-error);border-color:var(--rm-error);color:#fff}.rm-btn-danger:hover{background:#991313}.rm-btn-soft-danger{background:#ffdad6;border-color:#ffb4ab;color:#93000a}.rm-btn-sm{height:32px;padding:0 10px;font-size:11px}.rm-btn:disabled{opacity:.5;cursor:not-allowed}
.rm-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:16px}.rm-kpis.rm-kpis-5{grid-template-columns:repeat(5,minmax(0,1fr))}.rm-kpi{background:#fff;border:1px solid var(--rm-line);border-radius:4px;padding:16px;min-height:116px}.rm-kpi.alert{border-left:4px solid var(--rm-error)}.rm-kpi-label{text-transform:uppercase;color:var(--rm-muted);font-size:12px;line-height:16px;font-weight:600;letter-spacing:.04em}.rm-kpi-value{margin-top:4px;font-size:32px;line-height:40px;font-weight:700;letter-spacing:-.02em}.rm-kpi-value.primary{color:var(--rm-primary)}.rm-kpi-value.error{color:var(--rm-error)}.rm-kpi-meta{margin-top:8px;color:#737685;font-size:13px;display:flex;align-items:center;gap:5px}.rm-kpi-meta.error{color:var(--rm-error)}
.rm-card{background:#fff;border:1px solid var(--rm-line);border-radius:4px}.rm-filter-card{padding:16px;margin-bottom:16px}.rm-filter-grid{display:grid;grid-template-columns:minmax(280px,1.8fr) repeat(3,minmax(150px,.7fr)) auto;gap:12px;align-items:center}.rm-field{display:flex;flex-direction:column;gap:6px}.rm-label{font-size:11px;line-height:16px;font-weight:600;color:#555b68;text-transform:uppercase;letter-spacing:.04em}.rm-input,.rm-select,.rm-textarea{width:100%;border:1px solid var(--rm-line);border-radius:4px;background:#fff;color:var(--rm-text);padding:9px 11px;outline:none}.rm-input,.rm-select{height:40px}.rm-input:focus,.rm-select:focus,.rm-textarea:focus{border-color:var(--rm-primary);box-shadow:0 0 0 1px var(--rm-primary)}.rm-search-wrap{position:relative}.rm-search-wrap .material-symbols-outlined{position:absolute;left:11px;top:10px;color:#737685}.rm-search-wrap .rm-input{padding-left:39px;background:#f8f9fb}
.rm-table-card{overflow:hidden}.rm-table-scroll{overflow:auto}.rm-table{width:100%;border-collapse:collapse;min-width:980px}.rm-table thead{background:#f3f4f6}.rm-table th{padding:13px 18px;text-align:left;font-size:11px;line-height:16px;font-weight:600;color:#555b68;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--rm-line);white-space:nowrap}.rm-table td{padding:12px 18px;border-bottom:1px solid #e1e2e4;vertical-align:middle}.rm-table tbody tr:hover td{background:#f8f9fb}.rm-table tbody tr:last-child td{border-bottom:0}.rm-id-link{color:var(--rm-primary);font-weight:600}.rm-person{display:flex;align-items:center;gap:10px}.rm-person-avatar{width:32px;height:32px;border-radius:50%;background:#dae2ff;color:#003d9b;display:grid;place-items:center;font-weight:700}.rm-person-name{font-weight:600}.rm-subtext{font-size:11px;color:#737685}.rm-status{display:inline-flex;align-items:center;padding:3px 8px;border-radius:999px;font-size:11px;line-height:16px;font-weight:600}.rm-status.active,.rm-status.occupied,.rm-status.full{background:#dff4e9;color:#08724d}.rm-status.inactive,.rm-status.vacant{background:#edeef0;color:#555b68}.rm-status.missing,.rm-status.conflict,.rm-status.over{background:#ffdad6;color:#93000a}.rm-status.shared,.rm-status.partial{background:#fff1d6;color:#8b5a00}.rm-row-actions{display:flex;align-items:center;justify-content:flex-end;gap:2px}.rm-row-actions button{width:30px;height:30px;border:0;background:transparent;color:#555b68;display:grid;place-items:center;border-radius:3px;cursor:pointer}.rm-row-actions button:hover{background:#edeef0;color:var(--rm-primary)}.rm-row-actions button.danger:hover{background:#ffdad6;color:#93000a}
.rm-table-footer{min-height:52px;border-top:1px solid var(--rm-line);padding:10px 16px;display:flex;align-items:center;justify-content:space-between;color:#555b68;font-size:12px}.rm-pagination{display:flex;align-items:center;gap:4px}.rm-page-btn{min-width:32px;height:32px;border:1px solid var(--rm-line);background:#fff;border-radius:3px;color:#434654;cursor:pointer}.rm-page-btn.is-active{background:var(--rm-primary);border-color:var(--rm-primary);color:#fff}
.rm-two-col{display:grid;grid-template-columns:minmax(0,2fr) minmax(300px,1fr);gap:16px}.rm-panel-title{padding:14px 16px;border-bottom:1px solid var(--rm-line);display:flex;align-items:center;justify-content:space-between;font-size:14px;font-weight:600}.rm-panel-body{padding:16px}.rm-tile-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.rm-tile{border:1px solid var(--rm-line);border-radius:4px;padding:16px;min-height:160px;display:flex;flex-direction:column}.rm-tile-icon{width:36px;height:36px;border:1px solid #b2c5ff;background:#dae2ff;color:var(--rm-primary);display:grid;place-items:center;border-radius:3px}.rm-tile h3{font-size:14px;margin:12px 0 4px}.rm-tile p{font-size:12px;color:#555b68;margin:0 0 14px}.rm-tile .rm-link-action{margin-top:auto;border:0;background:transparent;padding:0;color:var(--rm-primary);font-weight:600;font-size:12px;display:flex;align-items:center;gap:4px;cursor:pointer}.rm-blue-note{background:#064aa8;color:#fff;padding:16px;min-height:110px}.rm-blue-note small{display:block;opacity:.8;text-transform:uppercase;letter-spacing:.06em;font-weight:600}.rm-blue-note strong{display:block;margin:8px 0}.rm-upload-area{border:1px dashed #aeb4c1;border-radius:4px;padding:26px;text-align:center;background:#fafbfc}.rm-upload-area .material-symbols-outlined{font-size:36px;color:#737685}.rm-danger-note{border:1px solid #ffb4ab;background:#fff1ef;color:#93000a;padding:11px;border-radius:4px;font-size:12px;display:flex;gap:8px;align-items:flex-start}
.rm-family-lookup{padding:16px;margin-bottom:16px}.rm-family-employee{display:grid;grid-template-columns:1.2fr .8fr .8fr;gap:10px;margin-top:12px}.rm-mini-card{border:1px solid var(--rm-line);background:#f8f9fb;border-radius:4px;padding:12px}.rm-mini-card label{display:block;font-size:10px;text-transform:uppercase;color:#737685;margin-bottom:4px}.rm-family-layout{display:grid;grid-template-columns:minmax(0,1fr);gap:16px}
.rm-overlay{display:none;position:fixed;inset:0;background:rgba(25,28,30,.56);z-index:80}.rm-overlay.is-open{display:block}.rm-drawer{position:fixed;right:0;top:0;width:min(460px,100vw);height:100vh;background:#fff;z-index:90;transform:translateX(102%);transition:transform .2s ease;display:flex;flex-direction:column;box-shadow:-12px 0 32px rgba(0,0,0,.18)}.rm-drawer.wide{width:min(640px,100vw)}.rm-drawer.is-open{transform:translateX(0)}.rm-drawer-head{min-height:62px;padding:16px 20px;border-bottom:1px solid var(--rm-line);display:flex;align-items:center;justify-content:space-between}.rm-drawer-title{font-size:16px;font-weight:600}.rm-close{width:32px;height:32px;border:0;background:transparent;border-radius:3px;display:grid;place-items:center;cursor:pointer}.rm-close:hover{background:#edeef0}.rm-drawer-body{padding:18px 20px;overflow:auto;flex:1}.rm-drawer-foot{padding:14px 20px;border-top:1px solid var(--rm-line);display:flex;justify-content:flex-end;gap:8px}.rm-form-section{margin-bottom:20px}.rm-form-section-title{font-size:11px;font-weight:700;color:var(--rm-primary);text-transform:uppercase;letter-spacing:.05em;padding-bottom:8px;border-bottom:1px solid var(--rm-line);margin-bottom:12px}.rm-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.rm-form-grid .wide{grid-column:1/-1}.rm-required{color:var(--rm-error)}.rm-radio-row{height:40px;display:flex;align-items:center;gap:18px}.rm-check-label{display:flex;align-items:center;gap:6px}.rm-message{margin-bottom:12px}.rm-error-box{border:1px solid #ffb4ab;background:#fff1ef;color:#93000a;padding:10px;border-radius:4px}
.rm-profile-head{display:flex;gap:12px;align-items:center}.rm-profile-avatar{width:48px;height:48px;border-radius:4px;background:#dae2ff;color:var(--rm-primary);display:grid;place-items:center;font-size:18px;font-weight:700}.rm-profile-title{font-size:16px;font-weight:600}.rm-profile-actions{display:flex;gap:8px;margin-bottom:12px}.rm-profile-tabs{display:flex;gap:18px;border-bottom:1px solid var(--rm-line);margin-bottom:16px}.rm-profile-tabs button{border:0;border-bottom:2px solid transparent;background:transparent;padding:10px 0;color:#555b68;font-size:11px;font-weight:600;cursor:pointer}.rm-profile-tabs button.is-active{color:var(--rm-primary);border-color:var(--rm-primary)}.rm-profile-pane{display:none}.rm-profile-pane.is-active{display:block}.rm-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.rm-info-box{border:1px solid var(--rm-line);background:#f8f9fb;border-radius:4px;padding:12px}.rm-info-box.wide{grid-column:1/-1}.rm-info-box label{display:block;font-size:10px;color:#737685;text-transform:uppercase;margin-bottom:3px}.rm-info-box strong{font-size:13px}
.rm-modal{display:none;position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);z-index:100;width:min(460px,94vw);background:#fff;border:1px solid var(--rm-line);box-shadow:0 24px 72px rgba(0,0,0,.3)}.rm-modal.is-open{display:block}.rm-modal-head{padding:16px 18px;border-bottom:1px solid var(--rm-line);display:flex;align-items:center;justify-content:space-between}.rm-modal-body{padding:18px}.rm-modal-foot{padding:12px 18px;border-top:1px solid var(--rm-line);display:flex;justify-content:flex-end;gap:8px}.rm-modal-summary{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}.rm-modal-summary div{background:#f8f9fb;border:1px solid var(--rm-line);padding:10px}.rm-modal-summary label{display:block;font-size:10px;color:#737685;text-transform:uppercase}.rm-toast{display:none;position:fixed;right:20px;bottom:20px;z-index:120;background:#2e3132;color:#fff;padding:12px 16px;border-radius:4px;box-shadow:0 8px 30px rgba(0,0,0,.2)}.rm-toast.is-open{display:block}.rm-empty{padding:36px;text-align:center;color:#737685}.rm-hidden{display:none!important}
@media(max-width:1100px){.rm-topbar-inner,.rm-main{padding-left:24px;padding-right:24px}.rm-nav{gap:14px}.rm-global-search{display:none}.rm-kpis.rm-kpis-5{grid-template-columns:repeat(3,1fr)}.rm-filter-grid{grid-template-columns:1fr 1fr}.rm-two-col{grid-template-columns:1fr}.rm-tile-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:760px){.rm-topbar-inner,.rm-main{padding-left:16px;padding-right:16px}.rm-brand{display:none}.rm-nav{gap:8px;overflow:auto}.rm-nav button{white-space:nowrap;font-size:11px}.rm-top-actions .rm-icon-btn{display:none}.rm-page-head{align-items:flex-start;flex-direction:column}.rm-kpis,.rm-kpis.rm-kpis-5{grid-template-columns:1fr 1fr}.rm-filter-grid{grid-template-columns:1fr}.rm-tile-grid,.rm-family-employee,.rm-form-grid,.rm-info-grid{grid-template-columns:1fr}.rm-form-grid .wide,.rm-info-box.wide{grid-column:auto}.rm-table-footer{align-items:flex-start;gap:10px;flex-direction:column}}
.rm-nav{display:flex;gap:4px;flex-wrap:wrap;margin:0 0 24px;border-bottom:1px solid var(--rm-line)} .rm-nav button{background:none;border:none;border-bottom:2px solid transparent;padding:10px 16px;font-size:14px;font-weight:500;color:var(--rm-muted);cursor:pointer;font-family:inherit;transition:.15s} .rm-nav button:hover{color:var(--rm-primary);background:#f8f9fb} .rm-nav button.is-active{color:var(--rm-primary);border-bottom-color:var(--rm-primary);font-weight:600}.rm-nav{margin:0 !important;padding-bottom:0 !important}.rm-main{padding-top:16px !important}.rm-page-head{margin-bottom:16px !important}</style>
<style>#residenceManagerApp .rm-nav{margin-bottom:0!important}#residenceManagerApp .rm-main{padding-top:14px!important}#residenceManagerApp .rm-page-head{margin-bottom:14px!important}</style>
<style>#residenceManagerApp .rm-nav{height:48px!important;max-height:48px!important;overflow:hidden!important;align-items:center!important;flex-wrap:nowrap!important;margin:0!important}</style>
<div id="residenceManagerApp">
  <div class="rm-nav">
    <button data-view="directory" class="is-active">Directory</button>
    <button data-view="family">Family</button>
    <button data-view="occupancy">Occupancy</button>
    <button data-view="outside">Outside Colony</button>
    <button data-view="formpending">Form Not Received</button>
    <button data-view="operations">Import / Operations</button>
    <button data-view="workbook">Workbook</button>
    <button data-view="unassigned">No Residence</button>
    <button data-view="crowded">Crowded Rooms</button>
  </div>
<main class="rm-main">
  <section id="rmViewDirectory" class="rm-view is-active">
    <div class="rm-page-head">
      <div><h1>Employee Directory</h1><p>Manage and monitor organizational personnel records</p></div>
      <button id="rmAddEmployee" class="rm-btn rm-btn-primary" type="button"><span class="material-symbols-outlined">person_add</span>Add New Employee</button>
    </div>
    <div class="rm-kpis rm-kpis-5">
      <div class="rm-kpi"><div class="rm-kpi-label">Total Employees</div><div id="rmKpiTotal" class="rm-kpi-value">{{ number_format($empTotal ?? 0) }}</div><div class="rm-kpi-meta"><span class="material-symbols-outlined">groups</span>Global workforce</div></div>
      <div class="rm-kpi"><div class="rm-kpi-label">Active</div><div id="rmKpiActive" class="rm-kpi-value primary">{{ number_format($empActive ?? 0) }}</div><div class="rm-kpi-meta"><span class="material-symbols-outlined">check_circle</span>Currently active</div></div>
      <div class="rm-kpi"><div class="rm-kpi-label">Inactive</div><div id="rmKpiInactive" class="rm-kpi-value">{{ number_format($empInactive ?? 0) }}</div><div class="rm-kpi-meta"><span class="material-symbols-outlined">pause_circle</span>Left / inactive</div></div>
      <div class="rm-kpi alert"><div class="rm-kpi-label">Missing Status</div><div id="rmKpiMissing" class="rm-kpi-value error">{{ number_format($empMissing ?? 0) }}</div><div class="rm-kpi-meta error"><span class="material-symbols-outlined">warning</span>Requires attention</div></div><div class="rm-kpi alert" data-view="unassigned" style="cursor:pointer"><div class="rm-kpi-label">No Residence</div><div class="rm-kpi-value error">{{ count($noResidence ?? []) }}</div><div class="rm-kpi-meta error"><span class="material-symbols-outlined">home_work</span>Active, unassigned</div></div><div class="rm-kpi" data-view="outside" style="cursor:pointer"><div class="rm-kpi-label">Outside Colony</div><div class="rm-kpi-value">{{ count($outsideEmployees ?? []) }}</div><div class="rm-kpi-meta"><span class="material-symbols-outlined">location_off</span>Living outside</div></div><div class="rm-kpi" data-view="formpending" style="cursor:pointer"><div class="rm-kpi-label">Form Not Received</div><div class="rm-kpi-value">{{ count($formPending ?? []) }}</div><div class="rm-kpi-meta"><span class="material-symbols-outlined">description</span>Awaiting form</div></div>
    </div>
    <div class="rm-card rm-filter-card">
      <div class="rm-filter-grid">
        <div class="rm-search-wrap"><span class="material-symbols-outlined">search</span><input id="rmEmployeeSearch" class="rm-input" placeholder="Search by Company ID or employee name"></div>
        <select id="rmDepartmentFilter" class="rm-select"><option value="">Department</option></select>
        <select id="rmDesignationFilter" class="rm-select"><option value="">Designation</option></select>
        <select id="rmStatusFilter" class="rm-select"><option value="">Status</option><option value="Yes">Active</option><option value="No">Inactive</option><option value="MISSING">Missing Status</option></select>
        <button id="rmClearFilters" class="rm-btn" type="button"><span class="material-symbols-outlined">filter_list</span>Clear Filters</button>
      </div>
    </div>
    <div class="rm-card rm-table-card">
      <div class="rm-table-scroll"><table class="rm-table"><thead><tr><th>Company ID</th><th>Name</th><th>Department</th><th>Designation</th><th>Status</th><th>Join Date</th><th style="text-align:right">Actions</th></tr></thead><tbody id="rmEmployeeRows"><tr><td colspan="7" class="rm-empty">Loading employee records...</td></tr></tbody></table></div>
      <div class="rm-table-footer"><span id="rmEmployeeCount">Showing 0 records</span><div id="rmPager" style="display:flex;gap:6px;align-items:center"></div><div class="rm-pagination"><button id="rmPrevPage" class="rm-page-btn">‹</button><button id="rmCurrentPage" class="rm-page-btn is-active">1</button><button id="rmNextPage" class="rm-page-btn">›</button></div></div>
    </div>
  </section>

  <section id="rmViewFamily" class="rm-view">
    <div class="rm-page-head"><div><h1>Family Workspace</h1><p>Manage permanent family members linked to colony residents</p></div><button id="rmAddFamily" class="rm-btn rm-btn-primary"><span class="material-symbols-outlined">group_add</span>Add Family Member</button></div>
    <div class="rm-card rm-family-lookup">
      <div style="display:flex;gap:10px"><div class="rm-search-wrap" style="flex:1"><span class="material-symbols-outlined">search</span><input id="rmFamilySearch" class="rm-input" placeholder="Search Company ID, member name or relation"></div><select id="rmFamilyStatus" class="rm-select" style="max-width:190px"><option value="">All Statuses</option><option>PRESENT</option><option>MOVED_OUT</option></select><button id="rmFamilyRefresh" class="rm-btn"><span class="material-symbols-outlined">refresh</span>Refresh</button></div>
      <div class="rm-family-employee"><div class="rm-mini-card"><label>Registry</label><strong id="rmFamilyRecordCount">0 family records</strong></div><div class="rm-mini-card"><label>Employees represented</label><strong id="rmFamilyEmployeeCount">0</strong></div><div class="rm-mini-card"><label>Data source</label><strong>Permanent Family Master</strong></div></div>
    </div>
    <div class="rm-card rm-table-card"><div class="rm-panel-title"><span>Family Registry</span><span class="rm-subtext">Live records</span></div><div class="rm-table-scroll"><table class="rm-table"><thead><tr><th>Company ID</th><th>Member</th><th>Relation</th><th>Age</th><th>School</th><th>Residence</th><th>Status</th><th style="text-align:right">Action</th></tr></thead><tbody id="rmFamilyRows"><tr><td colspan="8" class="rm-empty">Open this workspace to load family records.</td></tr></tbody></table></div></div>
  </section>

  <section id="rmViewOccupancy" class="rm-view">
    <div class="rm-page-head"><div><h1>Occupancy Workspace</h1><p>Real-time oversight of unit utilization and resident distribution across all colonies.</p></div><div class="rm-actions"><button id="rmExportOccupancy" class="rm-btn"><span class="material-symbols-outlined">download</span>Export Report</button><button id="rmNewAllocation" class="rm-btn rm-btn-primary"><span class="material-symbols-outlined">add</span>New Allocation</button></div></div>
    <div class="rm-kpis rm-kpis-5">
      <div class="rm-kpi"><div class="rm-kpi-label">Total Units</div><div id="rmOccTotal" class="rm-kpi-value">0</div><div class="rm-kpi-meta">All residential units</div></div>
      <div class="rm-kpi"><div class="rm-kpi-label">Occupied</div><div id="rmOccOccupied" class="rm-kpi-value primary">0</div><div class="rm-kpi-meta">Assigned units</div></div>
      <div class="rm-kpi"><div class="rm-kpi-label">Vacant</div><div id="rmOccVacant" class="rm-kpi-value">0</div><div class="rm-kpi-meta">Available now</div></div>
      <div class="rm-kpi alert"><div class="rm-kpi-label">Over Capacity</div><div id="rmOccConflict" class="rm-kpi-value error">0</div><div class="rm-kpi-meta error">Requires action</div></div>
      <div class="rm-kpi"><div class="rm-kpi-label">Total Residents</div><div id="rmOccResidents" class="rm-kpi-value">0</div><div class="rm-kpi-meta">Active assignments</div></div>
    </div>
    <div class="rm-card rm-filter-card"><div class="rm-filter-grid" style="grid-template-columns:repeat(5,minmax(130px,1fr)) auto"><select id="rmOccColony" class="rm-select"><option value="">All Colonies</option></select><select id="rmOccType" class="rm-select"><option value="">All Types</option></select><input id="rmOccUnit" class="rm-input" placeholder="Unit ID"><input id="rmOccRoom" class="rm-input" placeholder="Room No"><select id="rmOccStatus" class="rm-select"><option value="">All Status</option><option>Vacant</option><option>Occupied</option><option>Shared</option><option>Conflict</option></select><button id="rmOccClear" class="rm-btn">Clear All Filters</button></div></div>
    <div class="rm-card rm-table-card"><div class="rm-table-scroll"><table class="rm-table"><thead><tr><th>Unit ID</th><th>Colony / Block</th><th>Room No.</th><th>Room Type</th><th>Occupants</th><th>Available</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead><tbody id="rmOccupancyRows"><tr><td colspan="8" class="rm-empty">Open this workspace to load occupancy.</td></tr></tbody></table></div></div>
  </section>

  <section id="rmViewOperations" class="rm-view">
    <div class="rm-page-head"><div><h1>Data Operations Control Center</h1><p>Manage, audit, and synchronize enterprise residency data across systems.</p></div><div class="rm-actions"><button class="rm-btn"><span class="material-symbols-outlined">settings</span>System Logs</button><button id="rmChooseCsvTop" class="rm-btn rm-btn-primary"><span class="material-symbols-outlined">add</span>New Bulk Action</button></div></div>
    <div class="rm-two-col">
      <div>
        <div class="rm-card"><div class="rm-panel-title"><span><span class="material-symbols-outlined" style="color:var(--rm-primary)">badge</span> Employee Data Management</span><span class="rm-status active">Active Modules</span></div><div class="rm-panel-body"><div class="rm-tile-grid">
          <div class="rm-tile"><div class="rm-tile-icon"><span class="material-symbols-outlined">download</span></div><h3>Download CSV Template</h3><p>Get the latest standardized employee data schema for bulk imports.</p><button id="rmDownloadTemplate" class="rm-link-action">Download <span class="material-symbols-outlined">arrow_forward</span></button></div>
          <div class="rm-tile"><div class="rm-tile-icon"><span class="material-symbols-outlined">upload_file</span></div><h3>Upload Employees CSV</h3><p>Bulk create or update employee records via secure file processing.</p><button id="rmChooseCsv" class="rm-link-action">Upload <span class="material-symbols-outlined">arrow_forward</span></button><input id="rmCsvFile" type="file" accept=".csv,text/csv" hidden></div>
          <div class="rm-tile"><div class="rm-tile-icon"><span class="material-symbols-outlined">table_view</span></div><h3>Export All Employees</h3><p>Generate a full snapshot of current employee master data.</p><button id="rmExportEmployees" class="rm-link-action">Export <span class="material-symbols-outlined">arrow_forward</span></button></div>
        </div></div></div>
        <div class="rm-card" style="margin-top:16px;background:#ecfdf5;border-color:#a7f3d0"><div class="rm-panel-body" style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap"><div><b>Pending Employees</b><div style="font-size:13px;color:#475569">New employees from your upload — assign a residence and add them to the master.</div></div><a href="{{ url('pending-employees') }}" class="rm-btn rm-btn-primary" style="text-decoration:none">Open Pending Employees</a></div></div>
        <div class="rm-card" style="margin-top:16px;background:#fef2f2;border-color:#fecaca"><div class="rm-panel-body" style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap"><div><b>Bulk Mark as Left</b><div style="font-size:13px;color:#475569">Upload a CSV of employees who have left (CompanyID, leave_date).</div></div><a href="{{ url('bulk-leave') }}" class="rm-btn" style="text-decoration:none;border-color:#ef4444;color:#ef4444">Open Bulk Leave</a></div></div>
        <div id="rmCsvPreview" class="rm-card" style="display:none;margin-top:16px"><div class="rm-panel-title"><span>CSV Preview Results</span></div><div class="rm-panel-body" id="rmCsvPreviewBody"></div></div>
        <div class="rm-card" style="margin-top:16px"><div class="rm-panel-title"><span><span class="material-symbols-outlined">history</span> Import History</span></div><div id="rmOperationHistory" class="rm-panel-body rm-empty">No operations in this browser session.</div></div>
      </div>
      <div>
        <div class="rm-card"><div class="rm-panel-title"><span><span class="material-symbols-outlined">home_work</span> Residency Data</span></div><div class="rm-panel-body" style="display:grid;gap:10px"><button id="rmExportResidency" class="rm-btn" style="justify-content:flex-start"><span class="material-symbols-outlined">radio_button_unchecked</span>Export Residency Data</button><button id="rmSyncOccupancy" class="rm-btn" style="justify-content:flex-start"><span class="material-symbols-outlined">sync</span>Sync Occupancy</button></div></div>
        <div class="rm-card" style="margin-top:16px"><div class="rm-panel-title"><span><span class="material-symbols-outlined">folder</span> Export Center</span></div><div class="rm-panel-body"><div class="rm-subtext">Files generated during this session appear in Import History.</div></div></div>
        <div class="rm-blue-note" style="margin-top:16px"><small>Data Integrity</small><strong>Real-time validation is now active.</strong><span style="font-size:12px;opacity:.85">All imports use the live backend validation workflow.</span></div>
      </div>
    </div>
  </section>

  <section id="rmViewUnassigned" class="rm-view">
    <div class="rm-page-head"><div><h1>Employees Without Residence</h1><p>Active employees who have no room assigned.</p></div><div style="display:flex;gap:8px;align-items:center"><input id="rmSearchNoRes" class="rm-input" placeholder="Search ID, name, department…" style="min-width:260px"><button id="rmExportNoResidence" class="rm-btn">Export CSV</button></div></div>
    <div class="rm-card rm-table-card"><div class="rm-table-scroll"><table class="rm-table">
      <thead><tr><th>Company ID</th><th>Name</th><th>Department</th><th>Designation</th><th style="text-align:right">Action</th></tr></thead>
      <tbody data-searchbody="nores">
      @forelse($noResidence ?? [] as $nr)
        <tr>
          <td style="font-family:monospace;font-weight:600">{{ $nr->company_id }}</td>
          <td style="font-weight:600">{{ $nr->name }}</td>
          <td>{{ $nr->department ?: '—' }}</td>
          <td>{{ $nr->designation ?: '—' }}</td>
          <td style="text-align:right;white-space:nowrap">
            <button class="rm-btn rm-btn-primary" data-emp-row="{{ $nr->company_id }}" data-res-assign="{{ $nr->company_id }}" data-name="{{ $nr->name }}" style="font-size:11px;padding:4px 8px">Assign</button>
            <button class="rm-btn" data-res-status="{{ $nr->company_id }}" data-to="OUTSIDE" data-name="{{ $nr->name }}" style="font-size:11px;padding:4px 8px">Outside</button>
            <button class="rm-btn" data-res-status="{{ $nr->company_id }}" data-to="FORM_PENDING" data-name="{{ $nr->name }}" style="font-size:11px;padding:4px 8px">No Form</button>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="rm-empty">All active employees have a residence.</td></tr>
      @endforelse
      </tbody></table></div></div>
  </section>

  <section id="rmViewOutside" class="rm-view">
    <div class="rm-page-head"><div><h1>Living Outside Colony</h1><p>Active employees living outside the colony. They are not billed for utilities.</p></div><div style="display:flex;gap:8px;align-items:center"><input id="rmSearchOutside" class="rm-input" placeholder="Search ID, name, department…" style="min-width:260px"><button id="rmExportOutside" class="rm-btn">Export CSV</button></div></div>
    <div class="rm-card rm-table-card"><div class="rm-table-scroll"><table class="rm-table">
      <thead><tr><th>Company ID</th><th>Name</th><th>Department</th><th>Designation</th><th>Marked On</th><th style="text-align:right">Action</th></tr></thead>
      <tbody data-searchbody="outside">
      @forelse($outsideEmployees ?? [] as $oe)
        <tr>
          <td style="font-family:monospace;font-weight:600">{{ $oe->company_id }}</td>
          <td style="font-weight:600">{{ $oe->name }}</td>
          <td>{{ $oe->department ?: '—' }}</td>
          <td>{{ $oe->designation ?: '—' }}</td>
          <td class="rm-subtext">{{ $oe->marked_on ?: '—' }}</td>
          <td style="text-align:right"><button class="rm-btn rm-btn-primary" data-emp-row="{{ $oe->company_id }}" data-outside-assign="{{ $oe->company_id }}" data-name="{{ $oe->name }}" style="font-size:12px;padding:5px 10px">Assign Residence</button></td>
        </tr>
      @empty
        <tr><td colspan="6" class="rm-empty">No employees are marked as living outside the colony.</td></tr>
      @endforelse
      </tbody></table></div></div>
  </section>

  <section id="rmViewFormpending" class="rm-view">
    <div class="rm-page-head"><div><h1>Form Not Received</h1><p>Active employees whose residence form has not been submitted.</p></div><div style="display:flex;gap:8px;align-items:center"><input id="rmSearchFormPending" class="rm-input" placeholder="Search ID, name, department…" style="min-width:260px"><button id="rmExportFormPending" class="rm-btn">Export CSV</button></div></div>
    <div class="rm-card rm-table-card"><div class="rm-table-scroll"><table class="rm-table">
      <thead><tr><th>Company ID</th><th>Name</th><th>Department</th><th>Designation</th><th style="text-align:right">Action</th></tr></thead>
      <tbody data-searchbody="formpending">
      @forelse($formPending ?? [] as $fp)
        <tr>
          <td style="font-family:monospace;font-weight:600">{{ $fp->company_id }}</td>
          <td style="font-weight:600">{{ $fp->name }}</td>
          <td>{{ $fp->department ?: '—' }}</td>
          <td>{{ $fp->designation ?: '—' }}</td>
          <td style="text-align:right;white-space:nowrap">
            <button class="rm-btn rm-btn-primary" data-emp-row="{{ $fp->company_id }}" data-res-assign="{{ $fp->company_id }}" data-name="{{ $fp->name }}" style="font-size:11px;padding:4px 8px">Assign</button>
            <button class="rm-btn" data-res-status="{{ $fp->company_id }}" data-to="OUTSIDE" data-name="{{ $fp->name }}" style="font-size:11px;padding:4px 8px">Outside</button>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="rm-empty">No pending forms.</td></tr>
      @endforelse
      </tbody></table></div></div>
  </section>

  <section id="rmViewCrowded" class="rm-view">
    <div class="rm-page-head"><div><h1>Crowded Rooms</h1><p>Rooms sorted by number of residents.</p></div></div>
    <div class="rm-card rm-table-card"><div class="rm-table-scroll"><table class="rm-table">
      <thead><tr><th>Unit ID</th><th>Room No</th><th style="text-align:right">Residents</th></tr></thead>
      <tbody>
      @forelse($crowded ?? [] as $cr)
        <tr>
          <td style="font-family:monospace;font-weight:600">{{ $cr->unit_id }}</td>
          <td style="font-family:monospace">{{ $cr->room_id }}</td>
          <td style="text-align:right"><span style="font-weight:700;color:{{ $cr->people >= 10 ? '#dc2626' : ($cr->people >= 5 ? '#d97706' : '#059669') }}">{{ $cr->people }}</span></td>
        </tr>
      @empty
        <tr><td colspan="3" class="rm-empty">No occupancy data.</td></tr>
      @endforelse
      </tbody></table></div></div>
  </section>

  <section id="rmViewWorkbook" class="rm-view">
    <div class="rm-page-head"><div><h1>HR Workbook Management</h1><p>Validate employee reference workbooks before controlled registry updates.</p></div></div>
    <div class="rm-two-col" style="grid-template-columns:360px minmax(0,1fr)">
      <div style="display:grid;gap:16px;align-content:start">
        <div class="rm-card"><div class="rm-panel-title"><span>Upload Workbook</span></div><div class="rm-panel-body"><div class="rm-field"><label class="rm-label">Target Period</label><input class="rm-input" type="month" value="2026-07"></div><div class="rm-upload-area" style="margin-top:12px"><span class="material-symbols-outlined">lock</span><p style="margin:8px 0 2px;font-weight:600">Permission-controlled upload</p><p class="rm-subtext">Upload uses the permanent employee registry validation flow.</p><button id="rmWorkbookChoose" class="rm-btn rm-btn-primary" style="margin-top:10px"><span class="material-symbols-outlined">upload</span>Upload & Process Workbook</button><input id="rmWorkbookFile" type="file" accept=".csv,text/csv" hidden></div><div id="rmWorkbookPreview" style="margin-top:12px"></div></div></div>
        <div class="rm-card"><div class="rm-panel-title"><span>Employee Reference Lookup</span></div><div class="rm-panel-body"><div class="rm-danger-note"><span class="material-symbols-outlined">warning</span>HR reference data is for new employees only. Do not use for existing record updates.</div><div class="rm-field" style="margin-top:12px"><label class="rm-label">Employee Company ID</label><div style="display:flex;gap:8px"><input id="rmWorkbookLookupId" class="rm-input" placeholder="RM-2024-0001"><button id="rmWorkbookLookup" class="rm-btn">Fetch</button></div></div><div id="rmWorkbookLookupResult" style="margin-top:12px"></div></div></div>
      </div>
      <div class="rm-card"><div class="rm-panel-title"><span>Recent Uploads</span><button class="rm-icon-btn"><span class="material-symbols-outlined">refresh</span></button></div><div class="rm-table-scroll"><table class="rm-table"><thead><tr><th>Filename</th><th>Target Period</th><th>Uploaded By</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead><tbody><tr><td colspan="5" class="rm-empty">Uploads completed in this browser session will appear here.</td></tr></tbody></table></div></div>
    </div>
  </section>
</main>

<div id="rmOverlay" class="rm-overlay"></div>

<aside id="rmEmployeeDrawer" class="rm-drawer">
  <div class="rm-drawer-head"><div id="rmEmployeeDrawerTitle" class="rm-drawer-title">Add New Employee</div><button class="rm-close" data-close="rmEmployeeDrawer"><span class="material-symbols-outlined">close</span></button></div>
  <div class="rm-drawer-body"><div id="rmEmployeeMessage" class="rm-message"></div>
    <div class="rm-form-section"><div class="rm-form-section-title">Basic Information</div><div class="rm-form-grid">
      <div class="rm-field"><label class="rm-label">Company ID <span class="rm-required">*</span></label><input id="rmEmpId" class="rm-input" placeholder="RM-"></div>
      <div class="rm-field"><label class="rm-label">Full Name <span class="rm-required">*</span></label><input id="rmEmpName" class="rm-input"></div>
      <div class="rm-field"><label class="rm-label">Father's Name</label><input id="rmEmpFather" class="rm-input"></div>
      <div class="rm-field"><label class="rm-label">CNIC <span class="rm-required">*</span></label><input id="rmEmpCnic" class="rm-input" placeholder="XXXXX-XXXXXXX-X"></div>
      <div class="rm-field"><label class="rm-label">Mobile Number</label><input id="rmEmpMobile" class="rm-input"></div>
      <div class="rm-field"><label class="rm-label">Department <span class="rm-required">*</span></label><input id="rmEmpDepartment" class="rm-input"></div>
      <div class="rm-field"><label class="rm-label">Section</label><input id="rmEmpSection" class="rm-input"></div>
      <div class="rm-field"><label class="rm-label">Sub Section</label><input id="rmEmpSubSection" class="rm-input"></div>
      <div class="rm-field"><label class="rm-label">Designation <span class="rm-required">*</span></label><input id="rmEmpDesignation" class="rm-input"></div>
      <div class="rm-field"><label class="rm-label">Employee Type</label><input id="rmEmpType" class="rm-input"></div>
      <div class="rm-field"><label class="rm-label">Join Date</label><input id="rmEmpJoinDate" type="date" class="rm-input"></div>
      <div class="rm-field"><label class="rm-label">Employment Status</label><select id="rmEmpActive" class="rm-select"><option value="Yes">Active</option><option value="No">Inactive</option></select></div>
      <div class="rm-field wide"><label class="rm-label">Remarks</label><textarea id="rmEmpRemarks" class="rm-textarea" rows="3"></textarea></div>
    </div></div>
    <div class="rm-form-section"><div class="rm-form-section-title">Residence Assignment</div><div class="rm-form-grid">
      <div class="rm-field"><label class="rm-label">Residence Type</label><select id="rmEmpResidenceType" class="rm-select" disabled><option value="">Select</option></select></div>
      <div class="rm-field"><label class="rm-label">Colony Type</label><select id="rmEmpColony" class="rm-select" disabled><option value="">Select</option></select></div>
      <div class="rm-field"><label class="rm-label">Block / Floor</label><select id="rmEmpBlock" class="rm-select" disabled><option value="">Select</option></select></div>
      <div class="rm-field"><label class="rm-label">Room No</label><select id="rmEmpRoom" class="rm-select" disabled><option value="">Select</option></select></div>
      <div class="rm-field"><label class="rm-label">Shared Room</label><select id="rmEmpShared" class="rm-select" disabled><option>No</option><option>Yes</option></select></div>
      <div class="rm-field"><label class="rm-label">Unit ID</label><input id="rmEmpUnit" class="rm-input" readonly></div><div class="rm-field wide" id="rmResidenceLockNote" style="display:none"><div class="rm-subtext" style="color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:8px 10px">Residence fields are read-only when editing. Use <strong>Transfer Residence</strong> to shift a room.</div></div>
    </div></div>
  </div>
  <div class="rm-drawer-foot"><button data-close="rmEmployeeDrawer" class="rm-btn">Cancel</button><button id="rmSaveRegistry" class="rm-btn"><span class="material-symbols-outlined">save</span>Save to Registry</button><button id="rmSaveEmployee" class="rm-btn rm-btn-primary">Add Employee</button></div>
</aside>

<aside id="rmFamilyDrawer" class="rm-drawer">
  <div class="rm-drawer-head"><div class="rm-drawer-title">Add Family Member</div><button class="rm-close" data-close="rmFamilyDrawer"><span class="material-symbols-outlined">close</span></button></div>
  <div class="rm-drawer-body"><div class="rm-form-section"><div class="rm-form-section-title">Identity</div><div class="rm-form-grid">
    <div class="rm-field wide"><label class="rm-label">Employee <span class="rm-required">*</span></label><select id="rmFamilyEmployee" class="rm-select"><option value="">Select</option></select></div>
    <div class="rm-field"><label class="rm-label">Full Name <span class="rm-required">*</span></label><input id="rmFamilyName" class="rm-input"></div><div class="rm-field"><label class="rm-label">Relation <span class="rm-required">*</span></label><select id="rmFamilyRelation" class="rm-select"><option value="">Select</option><option>Spouse</option><option>Son</option><option>Daughter</option><option>Parent</option><option>Other</option></select></div>
    <div class="rm-field"><label class="rm-label">Age</label><input id="rmFamilyAge" type="number" min="0" class="rm-input"></div><div class="rm-field"><label class="rm-label">School Going</label><select id="rmFamilySchoolGoing" class="rm-select"><option value="0">No</option><option value="1">Yes</option></select></div>
    <div class="rm-field"><label class="rm-label">School Name</label><input id="rmFamilySchool" class="rm-input"></div><div class="rm-field"><label class="rm-label">Class</label><input id="rmFamilyClass" class="rm-input"></div>
    <div class="rm-field wide"><label class="rm-label">Remarks / Notes</label><textarea id="rmFamilyRemarks" class="rm-textarea" rows="4"></textarea></div>
  </div></div></div>
  <div class="rm-drawer-foot"><button data-close="rmFamilyDrawer" class="rm-btn">Cancel</button><button id="rmSaveFamily" class="rm-btn rm-btn-primary">Save Member</button></div>
</aside>

<aside id="rmProfileDrawer" class="rm-drawer wide">
  <div class="rm-drawer-head"><div id="rmProfileHeader" class="rm-profile-head"><div class="rm-profile-avatar">?</div><div><div class="rm-profile-title">Employee Profile</div><div class="rm-subtext">Loading...</div></div></div><button class="rm-close" data-close="rmProfileDrawer"><span class="material-symbols-outlined">close</span></button></div>
  <div id="rmProfileBody" class="rm-drawer-body"><div class="rm-empty">Loading profile...</div></div>
  <div class="rm-drawer-foot"><button data-close="rmProfileDrawer" class="rm-btn">Close View</button></div>
</aside>

<aside id="rmResidenceDrawer" class="rm-drawer">
  <div class="rm-drawer-head"><div><div id="rmResidenceTitle" class="rm-drawer-title">Assign / Transfer Residence</div><div class="rm-subtext">Operational workflow</div></div><button class="rm-close" data-close="rmResidenceDrawer"><span class="material-symbols-outlined">close</span></button></div>
  <div class="rm-drawer-body"><input id="rmResidenceCompanyId" type="hidden"><div class="rm-form-section"><div class="rm-form-section-title">Employee Information</div><div class="rm-mini-card"><strong id="rmResidenceEmployee">—</strong></div></div><div class="rm-form-section"><div class="rm-form-section-title">New Residence Selection</div><div class="rm-form-grid"><div class="rm-field"><label class="rm-label">Colony <span class="rm-required">*</span></label><input id="rmResColony" class="rm-input" list="rmColonyList" autocomplete="off" placeholder="type to search…"><datalist id="rmColonyList">@foreach(array_keys($tree ?? []) as $cn)<option value="{{ $cn }}">@endforeach</datalist></div><div class="rm-field"><label class="rm-label">Floor</label><input id="rmResFloor" class="rm-input" list="rmFloorList" autocomplete="off" placeholder="floor…"><datalist id="rmFloorList"></datalist></div><div class="rm-field"><label class="rm-label">Room No <span class="rm-required">*</span></label><input id="rmResRoomPick" class="rm-input" list="rmRoomList" autocomplete="off" placeholder="room…"><datalist id="rmRoomList"></datalist></div><input id="rmResidenceUnit" type="hidden"><input id="rmResidenceRoom" type="hidden"><div class="rm-field"><label class="rm-label">Effective Date</label><input id="rmResidenceDate" type="date" class="rm-input"></div><div class="rm-field wide"><label class="rm-label">Remarks</label><textarea id="rmResidenceRemarks" class="rm-textarea" rows="4"></textarea></div></div></div></div>
  <div class="rm-drawer-foot"><button data-close="rmResidenceDrawer" class="rm-btn">Cancel</button><button id="rmSaveResidence" class="rm-btn rm-btn-primary">Confirm Transfer <span class="material-symbols-outlined">arrow_forward</span></button></div>
</aside>

<div id="rmConfirmModal" class="rm-modal"><div class="rm-modal-head"><strong id="rmConfirmTitle">Confirm Action</strong><button class="rm-close" data-close="rmConfirmModal"><span class="material-symbols-outlined">close</span></button></div><div class="rm-modal-body"><div id="rmConfirmSummary" class="rm-modal-summary"></div><div class="rm-danger-note"><span class="material-symbols-outlined">warning</span><span id="rmConfirmWarning">This action will update the live record.</span></div><div class="rm-form-grid" style="margin-top:14px"><div class="rm-field wide"><label class="rm-label">Effective Date</label><input id="rmConfirmDate" type="date" class="rm-input"></div><div class="rm-field wide"><label class="rm-label">Reason / Remarks</label><textarea id="rmConfirmRemarks" class="rm-textarea" rows="3"></textarea></div></div></div><div class="rm-modal-foot"><button data-close="rmConfirmModal" class="rm-btn">Cancel</button><button id="rmConfirmAction" class="rm-btn rm-btn-danger">Confirm</button></div></div>
<div id="rmToast" class="rm-toast"></div>
</div>
<script>
window.RM_TREE = @json($tree ?? []);
document.addEventListener('DOMContentLoaded',function(){
 function rmSetList(id,items,isRoom){var dl=document.getElementById(id);if(!dl)return;dl.innerHTML='';
  items.forEach(function(it){var o=document.createElement('option');
   if(isRoom){o.value=it.room;o.label=it.n>0?(it.n+' person'):'vacant';}else{o.value=it;}
   dl.appendChild(o);});}
 var ci=document.getElementById('rmResColony'),fi=document.getElementById('rmResFloor'),ri=document.getElementById('rmResRoomPick'),
     hu=document.getElementById('rmResidenceUnit'),hr=document.getElementById('rmResidenceRoom');
 if(!ci||!fi||!ri||!hu||!hr)return;
 ci.addEventListener('input',function(){rmSetList('rmFloorList',RM_TREE[ci.value]?Object.keys(RM_TREE[ci.value]):[],false);rmSetList('rmRoomList',[],true);fi.value='';ri.value='';hu.value='';hr.value='';});
 fi.addEventListener('input',function(){var l=(RM_TREE[ci.value]&&RM_TREE[ci.value][fi.value])?RM_TREE[ci.value][fi.value]:[];rmSetList('rmRoomList',l,true);ri.value='';hu.value='';hr.value='';});
 ri.addEventListener('input',function(){var l=(RM_TREE[ci.value]&&RM_TREE[ci.value][fi.value])?RM_TREE[ci.value][fi.value]:[];var h=l.filter(function(x){return x.room===ri.value;})[0];hu.value=h?h.unit:'';hr.value=h?h.room:'';});
});

(()=>{
const $=id=>document.getElementById(id), all=s=>[...document.querySelectorAll(s)], csrf=@json(csrf_token());
const bind=(id,event,handler)=>{const el=$(id); if(el) el.addEventListener(event,handler);};
const bindAll=(ids,event,handler)=>ids.forEach(id=>bind(id,event,handler));
const V2_BASE=@json(url('/api/v2/people-residency'));
const URLS={employees:V2_BASE+'/employees',add:V2_BASE+'/employees',import:V2_BASE+'/employees/import',profile:V2_BASE+'/profiles',family:V2_BASE+'/families',occupancy:V2_BASE+'/occupancy',registry:V2_BASE+'/registry/upsert',registryPreview:V2_BASE+'/registry/import-preview',registryCommit:V2_BASE+'/registry/import-commit',registryGet:V2_BASE+'/registry'};
const state={employees:[],filtered:[],families:[],occupancy:[],editing:null,profile:null,confirm:null,csv:'',page:1,pageSize:25};
const esc=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const pick=(r,...keys)=>{for(const key of keys){if(r&&r[key]!==undefined&&r[key]!==null)return r[key]}return''};
const empId=r=>String(pick(r,'company_id','CompanyID')).trim(), empName=r=>String(pick(r,'name','Name')).trim();
const route=(base,id)=>base+'/'+encodeURIComponent(id);
function toast(message,error=false){const el=$('rmToast');el.textContent=message;el.style.background=error?'#93000a':'#2e3132';el.classList.add('is-open');setTimeout(()=>el.classList.remove('is-open'),2800)}
async function api(url,options={}){const method=options.method||'GET';const headers={Accept:'application/json',...(method!=='GET'?{'Content-Type':'application/json','X-CSRF-TOKEN':csrf}:{})};const init={...options,method,headers:{...headers,...(options.headers||{})}};if(init.body&&typeof init.body!=='string')init.body=JSON.stringify(init.body);const response=await fetch(url,init);const data=await response.json().catch(()=>({}));if(!response.ok||data.status==='error')throw new Error(data.error||data.message||`Request failed (${response.status})`);return data}
function openLayer(id){$('rmOverlay').classList.add('is-open');$(id).classList.add('is-open')}
function closeLayer(id){$(id).classList.remove('is-open');if(!all('.rm-drawer.is-open,.rm-modal.is-open').length)$('rmOverlay').classList.remove('is-open')}
all('[data-close]').forEach(btn=>btn.addEventListener('click',()=>closeLayer(btn.dataset.close)));bind('rmOverlay','click',()=>all('.rm-drawer.is-open,.rm-modal.is-open').forEach(el=>closeLayer(el.id)));
function showView(name){all('[data-view]').forEach(btn=>btn.classList.toggle('is-active',btn.dataset.view===name));all('.rm-view').forEach(view=>view.classList.toggle('is-active',view.id==='rmView'+name[0].toUpperCase()+name.slice(1)));if(name==='family'&&!state.families.length)loadFamilies();if(name==='occupancy'&&!state.occupancy.length)loadOccupancy()}
all('[data-view]').forEach(btn=>btn.addEventListener('click',()=>showView(btn.dataset.view)));
function setOptions(select,values,placeholder){const current=select.value;select.innerHTML=`<option value="">${esc(placeholder)}</option>`+[...new Set(values.filter(Boolean).map(String))].sort().map(v=>`<option value="${esc(v)}">${esc(v)}</option>`).join('');select.value=current}
async function loadEmployees(){try{const p=new URLSearchParams();p.set('per_page',100);p.set('page',state.page||1);var qEl=$('rmEmployeeSearch'),dEl=$('rmDepartmentFilter'),sEl=$('rmStatusFilter');if(qEl&&qEl.value.trim())p.set('q',qEl.value.trim());if(dEl&&dEl.value)p.set('department',dEl.value);if(sEl&&sEl.value)p.set('active',sEl.value);const data=await api(URLS.employees+'?'+p.toString());state.employees=data.rows||[];state.total=data.total||0;state.page=data.page||1;fillEmployeeSelect();applyEmployeeFilters();renderPager()}catch(error){$('rmEmployeeRows').innerHTML=`<tr><td colspan="7" class="rm-empty"><div class="rm-error-box">${esc(error.message)}</div></td></tr>`}}
function renderPager(){var el=document.getElementById('rmPager');if(!el)return;var per=100,total=state.total||0,page=state.page||1,pages=Math.max(1,Math.ceil(total/per));el.innerHTML='<span style="font-size:12px;color:#737685">Page '+page+' of '+pages+' · '+total+' employees</span>'+' <button class="rm-btn" '+(page<=1?'disabled':'')+' onclick="gotoPage('+(page-1)+')">Prev</button>'+' <button class="rm-btn" '+(page>=pages?'disabled':'')+' onclick="gotoPage('+(page+1)+')">Next</button>';}window.gotoPage=function(p){state.page=p;loadEmployees()};var _reloadT=null;window.reloadFiltered=function(){clearTimeout(_reloadT);_reloadT=setTimeout(function(){state.page=1;loadEmployees()},350)};function applyEmployeeFilters(){const q=$('rmEmployeeSearch').value.trim().toLowerCase(),dept=$('rmDepartmentFilter').value,designation=$('rmDesignationFilter').value,status=$('rmStatusFilter').value;state.filtered=state.employees.filter(row=>{const active=String(pick(row,'active','Active')).trim();return(!q||JSON.stringify(row).toLowerCase().includes(q))&&(!dept||pick(row,'department','Department')===dept)&&(!designation||pick(row,'designation','Designation')===designation)&&(!status||(status==='MISSING'?active==='':active===status))});state.page=1;renderEmployees()}
function renderEmployees(){setOptions($('rmDepartmentFilter'),state.employees.map(r=>pick(r,'department','Department')),'Department');setOptions($('rmDesignationFilter'),state.employees.map(r=>pick(r,'designation','Designation')),'Designation');const total=state.employees.length,active=state.employees.filter(r=>String(pick(r,'active','Active')).trim()==='Yes').length,inactive=state.employees.filter(r=>String(pick(r,'active','Active')).trim()==='No').length,missing=state.employees.filter(r=>String(pick(r,'active','Active')).trim()==='').length;/*server KPI*/ if(false) void 0;void 0;void 0;void 0;const pages=Math.max(1,Math.ceil(state.filtered.length/state.pageSize));if(state.page>pages)state.page=pages;const start=(state.page-1)*state.pageSize,rows=state.filtered.slice(start,start+state.pageSize);$('rmCurrentPage').textContent=state.page;$('rmEmployeeCount').textContent=state.filtered.length?`Showing ${start+1} to ${Math.min(start+state.pageSize,state.filtered.length)} of ${state.filtered.length} entries`:'Showing 0 entries';$('rmPrevPage').disabled=state.page<=1;$('rmNextPage').disabled=state.page>=pages;$('rmEmployeeRows').innerHTML=rows.length?rows.map(row=>{const active=String(pick(row,'active','Active')).trim(),status=active==='Yes'?'Active':active==='No'?'Inactive':'Missing Status',statusClass=active==='Yes'?'active':active==='No'?'inactive':'missing';return `<tr><td><span class="rm-id-link">${esc(empId(row))}</span></td><td><div class="rm-person"><div class="rm-person-avatar">${esc((empName(row)||'?').slice(0,1).toUpperCase())}</div><div><div class="rm-person-name">${esc(empName(row))}</div><div class="rm-subtext">${esc(pick(row,'Employee Type','employee_type'))}</div></div></div></td><td>${esc(pick(row,'department','Department')||'—')}</td><td>${esc(pick(row,'designation','Designation')||'—')}</td><td><span class="rm-status ${statusClass}">${esc(status)}</span></td><td>${esc(pick(row,'Join Date','join_date')||'—')}</td><td><div class="rm-row-actions"><button title="View" data-action="view" data-id="${esc(empId(row))}"><span class="material-symbols-outlined">visibility</span></button><button title="Edit" data-action="edit" data-id="${esc(empId(row))}"><span class="material-symbols-outlined">edit</span></button><button title="Residence" data-action="residence" data-id="${esc(empId(row))}"><span class="material-symbols-outlined">home_work</span></button><button class="danger" title="Mark Left" data-action="left" data-id="${esc(empId(row))}"><span class="material-symbols-outlined">person_remove</span></button></div></td></tr>`}).join(''):`<tr><td colspan="7" class="rm-empty">No matching employee records.</td></tr>`}
bind('rmEmployeeSearch','input',applyEmployeeFilters);bind('rmGlobalSearch','input',e=>{if($('rmEmployeeSearch')) $('rmEmployeeSearch').value=e.target.value;showView('directory');applyEmployeeFilters()});bindAll(['rmDepartmentFilter','rmDesignationFilter','rmStatusFilter'],'change',applyEmployeeFilters);bind('rmClearFilters','click',()=>{['rmEmployeeSearch','rmDepartmentFilter','rmDesignationFilter','rmStatusFilter','rmGlobalSearch'].forEach(id=>{if($(id)) $(id).value=''});applyEmployeeFilters()});bind('rmPrevPage','click',()=>{if(state.page>1){state.page--;renderEmployees()}});bind('rmNextPage','click',()=>{if(state.page<Math.ceil(state.filtered.length/state.pageSize)){state.page++;renderEmployees()}});
bind('rmEmployeeRows','click',event=>{const btn=event.target.closest('[data-action]');if(!btn)return;const employee=state.employees.find(r=>empId(r)===btn.dataset.id);if(btn.dataset.action==='view')openProfile(btn.dataset.id);if(btn.dataset.action==='edit')openEmployee(employee);if(btn.dataset.action==='residence')openResidence(employee);if(btn.dataset.action==='left')openConfirm('left',employee)});
const employeeFields={rmEmpId:['CompanyID','company_id'],rmEmpName:['Name','name'],rmEmpFather:["Father's Name",'father_name'],rmEmpCnic:['CNIC_No.','cnic_no'],rmEmpMobile:['Mobile_No.','mobile_no'],rmEmpDepartment:['Department','department'],rmEmpSection:['Section','section'],rmEmpSubSection:['Sub Section','sub_section'],rmEmpDesignation:['Designation','designation'],rmEmpType:['Employee Type','employee_type'],rmEmpJoinDate:['Join Date','join_date'],rmEmpActive:['Active','active'],rmEmpColony:['Colony Type','colony_type'],rmEmpBlock:['Block Floor','block_floor'],rmEmpRoom:['Room No','room_no'],rmEmpShared:['Shared Room','shared_room'],rmEmpUnit:['Unit_ID','unit_id'],rmEmpRemarks:['Remarks','remarks']};
function employeePayload(){const payload={};for(const [id,keys] of Object.entries(employeeFields))payload[keys[0]]=$(id).value.trim();return payload}
function openEmployee(employee=null){state.editing=employee;$('rmEmployeeDrawerTitle').textContent=employee?'Edit Employee':'Add New Employee';$('rmSaveEmployee').textContent=employee?'Save Changes':'Add Employee';for(const [id,keys] of Object.entries(employeeFields))$(id).value=employee?pick(employee,...keys):'';$('rmEmpActive').value=employee?(pick(employee,'Active','active')||'Yes'):'Yes';$('rmEmpId').disabled=!!employee;const lockRes=!!employee;['rmEmpResidenceType','rmEmpColony','rmEmpBlock','rmEmpRoom','rmEmpShared'].forEach(i=>{const el=$(i);if(el)el.disabled=lockRes});const uEl=$('rmEmpUnit');if(uEl)uEl.readOnly=lockRes;const nEl=$('rmResidenceLockNote');if(nEl)nEl.style.display=lockRes?'block':'none';$('rmEmployeeMessage').innerHTML='';openLayer('rmEmployeeDrawer')}
bind('rmAddEmployee','click',()=>openEmployee());bind('rmSaveEmployee','click',async()=>{try{const body=employeePayload();const url=state.editing?route(URLS.employees,empId(state.editing)):URLS.add;await api(url,{method:state.editing?'PATCH':'POST',body});closeLayer('rmEmployeeDrawer');toast('Employee saved successfully.');await loadEmployees()}catch(error){$('rmEmployeeMessage').innerHTML=`<div class="rm-error-box">${esc(error.message)}</div>`}});bind('rmSaveRegistry','click',async()=>{try{await api(URLS.registry,{method:'POST',body:employeePayload()});toast('Employee saved to registry.')}catch(error){toast(error.message,true)}});
async function loadResidenceCascades(){try{const data=await api(V2_BASE+'/residence-types');const types=data.rows||[];$('rmEmpResidenceType').innerHTML='<option value="">Select</option>'+types.map(v=>`<option>${esc(v)}</option>`).join('')+'<option value="OUTSIDE">Outside Colony</option>'}catch(_){}}
bind('rmEmpResidenceType','change',async()=>{if($('rmEmpResidenceType').value==='OUTSIDE'){$('rmEmpColony').innerHTML='<option value="OUTSIDE">Outside Colony</option>';$('rmEmpBlock').innerHTML='<option value="">—</option>';$('rmEmpRoom').innerHTML='<option value="">—</option>';$('rmEmpUnit').value='OUTSIDE';return}try{const data=await api(V2_BASE+'/colonies?residence_type='+encodeURIComponent($('rmEmpResidenceType').value));const rows=data.rows||[];$('rmEmpColony').innerHTML='<option value="">Select</option>'+rows.map(v=>`<option value="${esc(v)}">${esc(v==='__uncategorized'?'Uncategorized':v)}</option>`).join('')}catch(_){}});bind('rmEmpColony','change',async()=>{try{const url=V2_BASE+'/blocks/'+encodeURIComponent($('rmEmpColony').value)+'?residence_type='+encodeURIComponent($('rmEmpResidenceType').value);const data=await api(url);const rows=data.rows||[];$('rmEmpBlock').innerHTML='<option value="">Select</option>'+rows.map(v=>`<option>${esc(v)}</option>`).join('')}catch(_){}});bind('rmEmpBlock','change',async()=>{try{const url=V2_BASE+'/rooms/'+encodeURIComponent($('rmEmpColony').value)+'/'+encodeURIComponent($('rmEmpBlock').value)+'?residence_type='+encodeURIComponent($('rmEmpResidenceType').value);const data=await api(url);const rows=data.rows||[];$('rmEmpRoom').innerHTML='<option value="">Select</option>'+rows.map(v=>`<option value="${esc(v.room_no)}" data-unit="${esc(v.unit_id)}">${esc(v.room_no)}</option>`).join('')}catch(_){}});bind('rmEmpRoom','change',()=>{$('rmEmpUnit').value=$('rmEmpRoom').selectedOptions[0]?.dataset.unit||$('rmEmpUnit').value});
async function openProfile(companyId){openLayer('rmProfileDrawer');$('rmProfileBody').innerHTML='<div class="rm-empty">Loading profile...</div>';try{const profile=await api(route(URLS.profile,companyId),{headers:{Accept:'application/json'}});state.profile=profile;const employee=profile.employee||{},residence=profile.residence||{};$('rmProfileHeader').innerHTML=`<div class="rm-profile-avatar">${esc((employee.name||'?').slice(0,1).toUpperCase())}</div><div><div class="rm-profile-title">Employee Profile: ${esc(employee.name)}</div><div class="rm-subtext">${esc(employee.company_id)} · ${esc(employee.department||'—')}</div></div>`;$('rmProfileBody').innerHTML=`<div class="rm-profile-actions"><button class="rm-btn rm-btn-primary" data-profile-action="edit"><span class="material-symbols-outlined">edit</span>Edit Employee</button><button class="rm-btn" data-profile-action="residence"><span class="material-symbols-outlined">home_work</span>Transfer Residence</button><button class="rm-btn rm-btn-soft-danger" data-profile-action="left"><span class="material-symbols-outlined">person_remove</span>Mark Left</button></div><div class="rm-profile-tabs"><button class="is-active" data-profile-tab="basic">Basic Info</button><button data-profile-tab="employment">Employment</button><button data-profile-tab="residency">Residency</button><button data-profile-tab="family">Family (${esc(profile.kpis?.linked_family_members||0)})</button><button data-profile-tab="resources">Resources</button></div><div class="rm-profile-pane is-active" data-profile-pane="basic"><div class="rm-info-grid"><div class="rm-info-box"><label>Full Name</label><strong>${esc(employee.name||'—')}</strong></div><div class="rm-info-box"><label>Father's Name</label><strong>${esc(employee.father_name||'—')}</strong></div><div class="rm-info-box"><label>CNIC / ID Number</label><strong>${esc(employee.cnic_no||'—')}</strong></div><div class="rm-info-box"><label>Mobile Number</label><strong>${esc(employee.mobile_no||'—')}</strong></div><div class="rm-info-box wide"><label>Residential Access (Permanent)</label><strong>${esc([residence.unit_id,residence.block_floor,residence.room_no].filter(Boolean).join(', ')||'Unassigned')}</strong></div></div></div><div class="rm-profile-pane" data-profile-pane="employment"><div class="rm-info-grid"><div class="rm-info-box"><label>Department</label><strong>${esc(employee.department||'—')}</strong></div><div class="rm-info-box"><label>Designation</label><strong>${esc(employee.designation||'—')}</strong></div><div class="rm-info-box"><label>Employee Type</label><strong>${esc(employee.employee_type||'—')}</strong></div><div class="rm-info-box"><label>Status</label><strong>${esc(employee.active_label||'—')}</strong></div><div class="rm-info-box"><label>Join Date</label><strong>${esc(employee.join_date||'—')}</strong></div></div></div><div class="rm-profile-pane" data-profile-pane="residency"><div class="rm-info-grid"><div class="rm-info-box"><label>Unit ID</label><strong>${esc(residence.unit_id||'—')}</strong></div><div class="rm-info-box"><label>Room No</label><strong>${esc(residence.room_no||'—')}</strong></div><div class="rm-info-box"><label>Residence Type</label><strong>${esc(residence.residence_type||'—')}</strong></div><div class="rm-info-box"><label>Occupancy Mode</label><strong>${esc(residence.occupancy_mode||'—')}</strong></div><div class="rm-info-box wide"><label>Status</label><strong>${esc(residence.status||'UNASSIGNED')}</strong></div></div></div><div class="rm-profile-pane" data-profile-pane="family"><div class="rm-table-scroll"><table class="rm-table"><thead><tr><th>Member</th><th>Relation</th><th>Status</th></tr></thead><tbody>${(profile.family_rows||[]).map(row=>`<tr><td>${esc(row.member_name)}</td><td>${esc(row.relation)}</td><td><span class="rm-status ${row.current_status==='PRESENT'||row.current_status==='ACTIVE'?'active':'inactive'}">${esc(row.current_status)}</span></td></tr>`).join('')||'<tr><td colspan="3" class="rm-empty">No family records.</td></tr>'}</tbody></table></div></div><div class="rm-profile-pane" data-profile-pane="resources"><div class="rm-info-grid">${(profile.assets||[]).map(asset=>`<div class="rm-info-box"><label>${esc(asset.label)}</label><strong>${esc(asset.quantity)}</strong></div>`).join('')||'<div class="rm-info-box wide"><strong>No issued resources.</strong></div>'}</div></div>`}catch(error){$('rmProfileBody').innerHTML=`<div class="rm-error-box">${esc(error.message)}</div>`}}
bind('rmProfileBody','click',event=>{const tab=event.target.closest('[data-profile-tab]');if(tab){all('[data-profile-tab]').forEach(b=>b.classList.toggle('is-active',b===tab));all('[data-profile-pane]').forEach(p=>p.classList.toggle('is-active',p.dataset.profilePane===tab.dataset.profileTab));return}const action=event.target.closest('[data-profile-action]');if(!action)return;const employee=state.employees.find(r=>empId(r)===state.profile?.employee?.company_id);closeLayer('rmProfileDrawer');if(action.dataset.profileAction==='edit')openEmployee(employee);if(action.dataset.profileAction==='residence')openResidence(employee);if(action.dataset.profileAction==='left')openConfirm('left',employee)});
const EXPORT_ROWS=@json($exportRows ?? ['no_residence'=>[],'outside'=>[]]);
bind('rmExportNoResidence','click',()=>downloadCsv('Employees_Without_Residence.csv',['Company ID','Name','Department','Designation'],EXPORT_ROWS.no_residence||[]));
function wireSearch(inputId, bodyKey){
  const inp=document.getElementById(inputId);
  if(!inp)return;
  inp.addEventListener('input',()=>{
    const q=inp.value.trim().toLowerCase();
    const body=document.querySelector('[data-searchbody="'+bodyKey+'"]');
    if(!body)return;
    let shown=0;
    body.querySelectorAll('tr').forEach(tr=>{
      if(tr.querySelector('.rm-empty')&&!tr.dataset.noMatch)return;
      const hit=!q||tr.textContent.toLowerCase().includes(q);
      tr.style.display=hit?'':'none';
      if(hit)shown++;
    });
    let msg=body.querySelector('[data-no-match]');
    if(!msg){msg=document.createElement('tr');msg.setAttribute('data-no-match','1');msg.innerHTML='<td colspan="6" class="rm-empty">No matching records.</td>';body.appendChild(msg)}
    msg.style.display=(q&&shown===0)?'':'none';
  });
}
wireSearch('rmSearchNoRes','nores');
wireSearch('rmSearchOutside','outside');
wireSearch('rmSearchFormPending','formpending');
bind('rmExportFormPending','click',()=>downloadCsv('Employees_Form_Not_Received.csv',['Company ID','Name','Department','Designation'],EXPORT_ROWS.form_pending||[]));
document.addEventListener('click',function(ev){
  const a=ev.target.closest('[data-res-assign]');
  if(a){openResidence({company_id:a.dataset.resAssign,name:a.dataset.name});return}
  const b=ev.target.closest('[data-res-status]');
  if(!b)return;
  const label={OUTSIDE:'Outside Colony',FORM_PENDING:'Form Not Received'}[b.dataset.to];
  if(!confirm('Mark '+b.dataset.name+' as '+label+'?'))return;
  const f=document.createElement('form');f.method='POST';f.action=@json(url('people-residency/residence-status'));
  f.innerHTML='<input type="hidden" name="_token" value="'+@json(csrf_token())+'"><input type="hidden" name="company_id" value="'+b.dataset.resStatus+'"><input type="hidden" name="status" value="'+b.dataset.to+'">';
  document.body.appendChild(f);f.submit();
});
bind('rmExportOutside','click',()=>downloadCsv('Employees_Outside_Colony.csv',['Company ID','Name','Department','Designation','Marked On'],EXPORT_ROWS.outside||[]));
document.addEventListener('click',function(ev){const b=ev.target.closest('[data-outside-assign]');if(!b)return;openResidence({company_id:b.dataset.outsideAssign,name:b.dataset.name})});
function openResidence(employee){state.editing=employee;$('rmResidenceCompanyId').value=empId(employee);$('rmResidenceEmployee').textContent=`${empName(employee)} (${empId(employee)})`;$('rmResidenceUnit').value=pick(employee,'Unit_ID','unit_id');$('rmResidenceRoom').value=pick(employee,'Room No','room_no');$('rmResidenceDate').value=new Date().toISOString().slice(0,10);$('rmResidenceRemarks').value='';const has=!!pick(employee,'Unit_ID','unit_id');$('rmResidenceTitle').textContent=has?'Assign / Transfer Residence':'Assign New Residence';$('rmSaveResidence').innerHTML=has?'Confirm Transfer <span class="material-symbols-outlined">arrow_forward</span>':'Assign Residence <span class="material-symbols-outlined">arrow_forward</span>';openLayer('rmResidenceDrawer')}
bind('rmSaveResidence','click',async()=>{const employee=state.editing,has=!!pick(employee,'Unit_ID','unit_id');const payload={unit_id:$('rmResidenceUnit').value,room_no:$('rmResidenceRoom').value,effective_date:$('rmResidenceDate').value,remarks:$('rmResidenceRemarks').value};try{try{await api(route(URLS.profile,empId(employee))+'/residence/'+(has?'shift':'assign'),{method:'POST',body:payload})}catch(err){if(has&&/no active residence/i.test(err.message||'')){await api(route(URLS.profile,empId(employee))+'/residence/assign',{method:'POST',body:payload})}else{throw err}}closeLayer('rmResidenceDrawer');toast('Residence updated successfully.');
  (function(){
    const cid=empId(employee);
    const btn=document.querySelector('[data-emp-row="'+cid+'"]');
    if(!btn)return;
    const tr=btn.closest('tr'), body=tr&&tr.closest('tbody');
    if(tr)tr.remove();
    if(body){
      const key=body.dataset.searchbody;
      const left=body.querySelectorAll('tr:not([data-no-match])').length;
      const kpi=document.querySelector('[data-view="'+(key==='nores'?'unassigned':key)+'"] .rm-kpi-value');
      if(kpi)kpi.textContent=left;
      if(left===0){const e=document.createElement('tr');e.innerHTML='<td colspan="6" class="rm-empty">Nothing left in this list.</td>';body.appendChild(e)}
    }
  })();state.occupancy=[];await loadEmployees()}catch(error){toast(error.message,true)}});
function openConfirm(mode,employee){state.confirm={mode,employee};const isLeft=mode==='left';$('rmConfirmTitle').textContent=isLeft?'Mark Employee as Left':'Confirm Vacate Residence';$('rmConfirmSummary').innerHTML=`<div><label>Employee ID</label><strong>${esc(empId(employee))}</strong></div><div><label>Name</label><strong>${esc(empName(employee))}</strong></div>`;$('rmConfirmWarning').textContent=isLeft?'This employee will be marked inactive.':'Proceeding will close all active residency assignments for this employee.';$('rmConfirmDate').value=new Date().toISOString().slice(0,10);$('rmConfirmRemarks').value='';$('rmConfirmAction').textContent=isLeft?'Confirm Mark Left':'Confirm Vacate';openLayer('rmConfirmModal')}
bind('rmConfirmAction','click',async()=>{const {mode,employee}=state.confirm||{};try{if(mode==='left')await api(route(URLS.employees,empId(employee)),{method:'PATCH',body:{Active:'No','Leave Date':$('rmConfirmDate').value,Remarks:$('rmConfirmRemarks').value}});else await api(route(URLS.profile,empId(employee))+'/residence/vacate',{method:'POST',body:{effective_date:$('rmConfirmDate').value,remarks:$('rmConfirmRemarks').value}});closeLayer('rmConfirmModal');toast('Action completed successfully.');state.occupancy=[];await loadEmployees()}catch(error){toast(error.message,true)}});
function fillEmployeeSelect(){$('rmFamilyEmployee').innerHTML='<option value="">Select employee</option>'+state.employees.map(row=>`<option value="${esc(empId(row))}">${esc(empId(row)+' — '+empName(row))}</option>`).join('')}
async function loadFamilies(){try{const data=await api(URLS.family);state.families=data.rows||[];$('rmFamilyRecordCount').textContent=`${state.families.length} family records`;$('rmFamilyEmployeeCount').textContent=new Set(state.families.map(r=>r.company_id)).size;renderFamilies()}catch(error){$('rmFamilyRows').innerHTML=`<tr><td colspan="8"><div class="rm-error-box">${esc(error.message)}</div></td></tr>`}}
function renderFamilies(){const q=$('rmFamilySearch').value.trim().toLowerCase(),status=$('rmFamilyStatus').value;const rows=state.families.filter(row=>(!q||JSON.stringify(row).toLowerCase().includes(q))&&(!status||row.current_status===status));$('rmFamilyRows').innerHTML=rows.length?rows.map(row=>`<tr><td><span class="rm-id-link">${esc(row.company_id)}</span></td><td><div class="rm-person-name">${esc(row.member_name)}</div></td><td>${esc(row.relation||'—')}</td><td>${esc(row.age??'—')}</td><td>${row.school_going?'Yes':'No'}<div class="rm-subtext">${esc(row.school_name||'')}</div></td><td>${esc(row.source_colony_building_name||row.source_room_no||'—')}</td><td><span class="rm-status ${row.current_status==='PRESENT'?'active':'inactive'}">${esc(row.current_status||'—')}</span></td><td><div class="rm-row-actions"><button data-family-profile="${esc(row.company_id)}"><span class="material-symbols-outlined">visibility</span></button></div></td></tr>`).join(''):`<tr><td colspan="8" class="rm-empty">No matching family records.</td></tr>`}
bind('rmFamilySearch','input',renderFamilies);bind('rmFamilyStatus','change',renderFamilies);bind('rmFamilyRefresh','click',loadFamilies);bind('rmAddFamily','click',()=>openLayer('rmFamilyDrawer'));bind('rmFamilyRows','click',event=>{const btn=event.target.closest('[data-family-profile]');if(btn)openProfile(btn.dataset.familyProfile)});bind('rmSaveFamily','click',async()=>{const companyId=$('rmFamilyEmployee').value;if(!companyId)return toast('Select an employee.',true);try{await api(route(URLS.profile,companyId)+'/family-members',{method:'POST',body:{member_name:$('rmFamilyName').value,relation:$('rmFamilyRelation').value,age:$('rmFamilyAge').value,school_going:$('rmFamilySchoolGoing').value==='1',school_name:$('rmFamilySchool').value,class_name:$('rmFamilyClass').value,remarks:$('rmFamilyRemarks').value}});closeLayer('rmFamilyDrawer');toast('Family member added successfully.');state.families=[];await loadFamilies()}catch(error){toast(error.message,true)}});
async function loadOccupancy(){try{const data=await api(URLS.occupancy);state.occupancy=data.rows||[];renderOccupancy()}catch(error){$('rmOccupancyRows').innerHTML=`<tr><td colspan="8"><div class="rm-error-box">${esc(error.message)}</div></td></tr>`}}
function renderOccupancy(){setOptions($('rmOccColony'),state.occupancy.map(r=>r.colony_type),'All Colonies');setOptions($('rmOccType'),state.occupancy.map(r=>r.residence_type),'All Types');const colony=$('rmOccColony').value,type=$('rmOccType').value,unit=$('rmOccUnit').value.trim().toLowerCase(),room=$('rmOccRoom').value.trim().toLowerCase(),status=$('rmOccStatus').value;const rows=state.occupancy.filter(row=>(!colony||row.colony_type===colony)&&(!type||row.residence_type===type)&&(!unit||String(row.unit_id).toLowerCase().includes(unit))&&(!room||String(row.room_no).toLowerCase().includes(room))&&(!status||row.occupancy_status===status));$('rmOccTotal').textContent=state.occupancy.length.toLocaleString();$('rmOccOccupied').textContent=state.occupancy.filter(r=>r.occupancy_status==='Occupied'||r.occupancy_status==='Shared').length.toLocaleString();$('rmOccVacant').textContent=state.occupancy.filter(r=>r.occupancy_status==='Vacant').length.toLocaleString();$('rmOccConflict').textContent=state.occupancy.filter(r=>r.occupancy_status==='Conflict').length.toLocaleString();$('rmOccResidents').textContent=state.occupancy.reduce((sum,r)=>sum+Number(r.occupant_count||0),0).toLocaleString();$('rmOccupancyRows').innerHTML=rows.length?rows.map(row=>{const statusClass=row.occupancy_status==='Occupied'?'occupied':row.occupancy_status==='Vacant'?'vacant':row.occupancy_status==='Shared'?'partial':'over';const available=row.occupancy_status==='Vacant'?1:row.occupancy_status==='Shared'?0:'—';return `<tr><td><span class="rm-id-link">${esc(row.unit_id)}</span></td><td>${esc(row.colony_type||'—')}<div class="rm-subtext">${esc(row.block_floor||'')}</div></td><td>${esc(row.room_no||'—')}</td><td>${esc(row.residence_type||'—')}</td><td>${esc(row.occupant_count||0)}<div class="rm-subtext">${esc((row.assigned_employee_names||[]).join(', '))}</div></td><td>${esc(available)}</td><td><span class="rm-status ${statusClass}">${esc(row.occupancy_status)}</span></td><td><div class="rm-row-actions">${(row.assigned_company_ids||[]).length?`<button data-occ-profile="${esc(row.assigned_company_ids[0])}"><span class="material-symbols-outlined">visibility</span></button>`:`<button data-occ-assign><span class="material-symbols-outlined">person_add</span></button>`}</div></td></tr>`}).join(''):`<tr><td colspan="8" class="rm-empty">No matching occupancy rows.</td></tr>`}
bindAll(['rmOccColony','rmOccType','rmOccStatus'],'change',renderOccupancy);bindAll(['rmOccUnit','rmOccRoom'],'input',renderOccupancy);bind('rmOccClear','click',()=>{['rmOccColony','rmOccType','rmOccUnit','rmOccRoom','rmOccStatus'].forEach(id=>$(id).value='');renderOccupancy()});bind('rmOccupancyRows','click',event=>{const profile=event.target.closest('[data-occ-profile]');if(profile)openProfile(profile.dataset.occProfile);if(event.target.closest('[data-occ-assign]')){showView('directory');toast('Select an employee and use the residence action.')}});bind('rmNewAllocation','click',()=>{showView('directory');toast('Select an employee and click the residence action.')});
const csvColumns=['CompanyID','Name',"Father's Name",'CNIC_No.','Mobile_No.','Department','Section','Sub Section','Designation','Employee Type','Colony Type','Block Floor','Room No','Shared Room','Join Date','Unit_ID'];function downloadCsv(filename,headers,rows){const csv=[headers,...rows].map(row=>row.map(value=>`"${String(value??'').replaceAll('"','""')}"`).join(',')).join('\r\n');const link=document.createElement('a');link.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'}));link.download=filename;link.click();URL.revokeObjectURL(link.href);addHistory(filename,rows.length)}function addHistory(filename,count){const box=$('rmOperationHistory');if(box.classList.contains('rm-empty')){box.classList.remove('rm-empty');box.innerHTML=''}box.insertAdjacentHTML('afterbegin',`<div style="padding:10px 0;border-bottom:1px solid var(--rm-line)"><strong>${esc(filename)}</strong><div class="rm-subtext">${count} rows · ${new Date().toLocaleString()}</div></div>`)}
bind('rmDownloadTemplate','click',()=>downloadCsv('Residence_Manager_Employee_Template.csv',csvColumns,[['RM-0001','Employee Name','Father Name','42101-0000000-0','03000000000','Department','Section','Sub Section','Designation','Staff','Family','Block A','R-01','No',new Date().toISOString().slice(0,10),'UNIT-001']]));function chooseCsv(){$('rmCsvFile').click()}bind('rmChooseCsv','click',chooseCsv);bind('rmChooseCsvTop','click',chooseCsv);bind('rmCsvFile','change',async event=>{const file=event.target.files[0];if(!file)return;state.csv=await file.text();const lines=state.csv.trim().split(/\r?\n/),headers=(lines[0]||'').split(',').map(v=>v.replace(/^"|"$/g,'').trim()),missing=['CompanyID','Name'].filter(c=>!headers.includes(c));$('rmCsvPreview').style.display='block';$('rmCsvPreviewBody').innerHTML=`<div class="rm-info-grid"><div class="rm-info-box"><label>Total Rows</label><strong>${Math.max(0,lines.length-1)}</strong></div><div class="rm-info-box"><label>Header Validation</label><strong>${missing.length?'Failed':'Passed'}</strong></div></div>${missing.length?`<div class="rm-error-box" style="margin-top:12px">Missing columns: ${esc(missing.join(', '))}</div>`:`<button id="rmCommitCsv" class="rm-btn rm-btn-primary" style="margin-top:12px">Commit Valid Rows</button>`}`;const commit=$('rmCommitCsv');if(commit)commit.addEventListener('click',async()=>{if(commit.disabled)return;commit.disabled=true;commit.textContent='Importing...';$('rmCsvPreviewBody').insertAdjacentHTML('beforeend','<div id="rmImportStatus" style="margin-top:12px;padding:10px;border:1px solid var(--rm-line);background:#eff6ff;border-radius:6px">Importing 1607 rows... please wait, do not refresh.</div>');try{const result=await api(URLS.import,{method:'POST',body:{csv_text:state.csv}});commit.remove();document.getElementById('rmImportStatus')?.remove();$('rmCsvPreviewBody').insertAdjacentHTML('beforeend',`<div class="rm-info-grid" style="margin-top:12px"><div class="rm-info-box"><label>Inserted</label><strong>${esc(result.inserted||0)}</strong></div><div class="rm-info-box"><label>Rejected</label><strong>${esc(result.rejected||0)}</strong></div></div>`);addHistory('Employee CSV Import',result.inserted||0);toast('CSV import completed.');await loadEmployees()}catch(error){commit.disabled=false;commit.textContent='Commit Valid Rows';toast(error.message,true)}})});bind('rmExportEmployees','click',()=>downloadCsv('Residence_Manager_Employees.csv',csvColumns,state.filtered.map(row=>csvColumns.map(column=>pick(row,column,column==='CompanyID'?'company_id':column==='Name'?'name':'')))));
function exportOccupancy(){if(!state.occupancy.length)return loadOccupancy().then(exportOccupancy);const headers=['Residence Type','Colony','Block','Unit','Room','Status','Occupants','Employee Names'];downloadCsv('Residence_Manager_Occupancy.csv',headers,state.occupancy.map(row=>[row.residence_type,row.colony_type,row.block_floor,row.unit_id,row.room_no,row.occupancy_status,row.occupant_count,(row.assigned_employee_names||[]).join('|')]))}bind('rmExportResidency','click',exportOccupancy);bind('rmExportOccupancy','click',exportOccupancy);bind('rmSyncOccupancy','click',loadOccupancy);
bind('rmWorkbookChoose','click',()=>$('rmWorkbookFile')?.click());bind('rmWorkbookFile','change',async event=>{const file=event.target.files[0];if(!file)return;state.csv=await file.text();$('rmWorkbookPreview').innerHTML='<button id="rmWorkbookValidate" class="rm-btn rm-btn-primary">Validate Workbook</button>';$('rmWorkbookValidate').addEventListener('click',async()=>{try{const preview=await api(URLS.registryPreview,{method:'POST',body:{csv_text:state.csv}});$('rmWorkbookPreview').innerHTML=`<pre style="white-space:pre-wrap;background:#f3f4f6;border:1px solid var(--rm-line);padding:10px;max-height:260px;overflow:auto">${esc(JSON.stringify(preview,null,2))}</pre><button id="rmWorkbookCommit" class="rm-btn rm-btn-primary" style="margin-top:8px">Commit Valid Rows</button>`;$('rmWorkbookCommit').addEventListener('click',async()=>{try{const result=await api(URLS.registryCommit,{method:'POST',body:{csv_text:state.csv}});$('rmWorkbookPreview').innerHTML=`<pre style="white-space:pre-wrap;background:#f3f4f6;border:1px solid var(--rm-line);padding:10px">${esc(JSON.stringify(result,null,2))}</pre>`;toast('Registry workbook committed.')}catch(error){toast(error.message,true)}})}catch(error){toast(error.message,true)}})});bind('rmWorkbookLookup','click',async()=>{const companyId=$('rmWorkbookLookupId').value.trim();if(!companyId)return;try{const data=await api(URLS.registryGet+'/'+encodeURIComponent(companyId));const row=data.row||data;$('rmWorkbookLookupResult').innerHTML=`<div class="rm-mini-card"><strong>${esc(pick(row,'Name','name')||'Record found')}</strong><div class="rm-subtext">${esc(pick(row,'CompanyID','company_id')||companyId)} · ${esc(pick(row,'Department','department')||'—')}</div></div>`}catch(error){$('rmWorkbookLookupResult').innerHTML=`<div class="rm-error-box">${esc(error.message)}</div>`}});
loadEmployees();loadResidenceCascades();
})();
</script>

<style id="nodesky-svg-icon-fallback">
/*
 * Do not allow Material ligature words to affect layout.
 * SVG is rendered inside the same span.
 */
.material-symbols-outlined {
    font-size: 0 !important;
    line-height: 1 !important;
    overflow: visible;
    vertical-align: middle;
}

.material-symbols-outlined > svg {
    width: 20px;
    height: 20px;
    display: inline-block;
    vertical-align: middle;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
    pointer-events: none;
}

.material-symbols-outlined[data-svg-size="16"] > svg {
    width: 16px;
    height: 16px;
}

.material-symbols-outlined[data-svg-size="18"] > svg {
    width: 18px;
    height: 18px;
}

.material-symbols-outlined[data-svg-size="24"] > svg {
    width: 24px;
    height: 24px;
}
</style>

<script id="nodesky-svg-material-converter">
(function () {
    'use strict';

    const icons = {
        search:
            '<circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path>',

        expand_more:
            '<path d="m6 9 6 6 6-6"></path>',

        expand_less:
            '<path d="m6 15 6-6 6 6"></path>',

        chevron_right:
            '<path d="m9 18 6-6-6-6"></path>',

        chevron_left:
            '<path d="m15 18-6-6 6-6"></path>',

        arrow_forward:
            '<path d="M5 12h14"></path><path d="m13 6 6 6-6 6"></path>',

        arrow_back:
            '<path d="M19 12H5"></path><path d="m11 18-6-6 6-6"></path>',

        add:
            '<path d="M12 5v14"></path><path d="M5 12h14"></path>',

        close:
            '<path d="M18 6 6 18"></path><path d="m6 6 12 12"></path>',

        visibility:
            '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12"></path><circle cx="12" cy="12" r="2.5"></circle>',

        edit:
            '<path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"></path>',

        person_add:
            '<circle cx="9" cy="7" r="4"></circle><path d="M3 21v-2a6 6 0 0 1 6-6h2"></path><path d="M19 8v6"></path><path d="M16 11h6"></path>',

        person_remove:
            '<circle cx="9" cy="7" r="4"></circle><path d="M3 21v-2a6 6 0 0 1 6-6h2"></path><path d="M16 11h6"></path>',

        group_add:
            '<circle cx="8" cy="8" r="3"></circle><path d="M2 20v-2a5 5 0 0 1 5-5h2"></path><path d="M16 8v6"></path><path d="M13 11h6"></path>',

        groups:
            '<circle cx="9" cy="8" r="3"></circle><circle cx="17" cy="9" r="2.5"></circle><path d="M3 20v-2a5 5 0 0 1 5-5h2a5 5 0 0 1 5 5v2"></path><path d="M15 14h2a4 4 0 0 1 4 4v2"></path>',

        check_circle:
            '<circle cx="12" cy="12" r="9"></circle><path d="m8 12 3 3 5-6"></path>',

        pause_circle:
            '<circle cx="12" cy="12" r="9"></circle><path d="M10 9v6"></path><path d="M14 9v6"></path>',

        warning:
            '<path d="M10.3 3.6 2.4 18a2 2 0 0 0 1.8 3h15.6a2 2 0 0 0 1.8-3L13.7 3.6a2 2 0 0 0-3.4 0Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path>',

        home_work:
            '<path d="M3 21V9l9-6 9 6v12"></path><path d="M9 21v-6h6v6"></path><path d="M6 12h2"></path><path d="M16 12h2"></path>',

        location_off:
            '<path d="M9.5 5.2A7 7 0 0 1 19 12c0 3-3 6.5-7 10"></path><path d="M8 20C5 16.5 3 14 3 12a8.5 8.5 0 0 1 .7-3.4"></path><path d="m3 3 18 18"></path>',

        description:
            '<path d="M6 2h8l4 4v16H6Z"></path><path d="M14 2v5h5"></path><path d="M9 13h6"></path><path d="M9 17h6"></path>',

        filter_list:
            '<path d="M4 6h16"></path><path d="M7 12h10"></path><path d="M10 18h4"></path>',

        refresh:
            '<path d="M20 6v5h-5"></path><path d="M4 18v-5h5"></path><path d="M18 11a7 7 0 0 0-12-4L4 11"></path><path d="M6 13a7 7 0 0 0 12 4l2-4"></path>',

        sync:
            '<path d="M20 7h-5V2"></path><path d="M4 17h5v5"></path><path d="M18 11a7 7 0 0 0-12-5L4 8"></path><path d="M6 13a7 7 0 0 0 12 5l2-2"></path>',

        download:
            '<path d="M12 3v12"></path><path d="m7 10 5 5 5-5"></path><path d="M5 21h14"></path>',

        upload:
            '<path d="M12 15V3"></path><path d="m7 8 5-5 5 5"></path><path d="M5 21h14"></path>',

        upload_file:
            '<path d="M6 2h8l4 4v16H6Z"></path><path d="M14 2v5h5"></path><path d="M12 18v-7"></path><path d="m9 14 3-3 3 3"></path>',

        table_view:
            '<rect x="3" y="4" width="18" height="16" rx="1"></rect><path d="M3 9h18"></path><path d="M9 9v11"></path><path d="M15 9v11"></path>',

        history:
            '<path d="M3 12a9 9 0 1 0 3-6.7"></path><path d="M3 4v6h6"></path><path d="M12 7v5l3 2"></path>',

        folder:
            '<path d="M3 6h6l2 2h10v11H3Z"></path>',

        lock:
            '<rect x="5" y="10" width="14" height="11" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path>',

        save:
            '<path d="M5 3h12l3 3v15H4V3Z"></path><path d="M8 3v6h8V3"></path><path d="M8 21v-7h8v7"></path>',

        badge:
            '<rect x="3" y="5" width="18" height="16" rx="2"></rect><circle cx="9" cy="11" r="2.5"></circle><path d="M5.5 18a4 4 0 0 1 7 0"></path><path d="M15 10h3"></path><path d="M15 14h3"></path>',

        radio_button_unchecked:
            '<circle cx="12" cy="12" r="9"></circle>',

        settings:
            '<circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1A1.7 1.7 0 0 0 9 4.6 1.7 1.7 0 0 0 10 3V2.8h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1Z"></path>',

        notifications:
            '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M10 21h4"></path>',

        help:
            '<circle cx="12" cy="12" r="9"></circle><path d="M9.5 9a2.7 2.7 0 1 1 4.5 2c-1.3 1-2 1.4-2 3"></path><path d="M12 18h.01"></path>',

        help_outline:
            '<circle cx="12" cy="12" r="9"></circle><path d="M9.5 9a2.7 2.7 0 1 1 4.5 2c-1.3 1-2 1.4-2 3"></path><path d="M12 18h.01"></path>',

        more_vert:
            '<circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="19" r="1"></circle>'
    };

    const fallback =
        '<circle cx="12" cy="12" r="9"></circle>' +
        '<circle cx="8" cy="12" r=".7" fill="currentColor" stroke="none"></circle>' +
        '<circle cx="12" cy="12" r=".7" fill="currentColor" stroke="none"></circle>' +
        '<circle cx="16" cy="12" r=".7" fill="currentColor" stroke="none"></circle>';

    function convert(el) {
        if (!el || el.dataset.svgConverted === '1') return;

        const name = (el.textContent || '').trim();

        if (!name) return;

        el.dataset.materialName = name;
        el.dataset.svgConverted = '1';

        el.innerHTML =
            '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' +
            (icons[name] || fallback) +
            '</svg>';
    }

    function scan(root) {
        if (!root) return;

        if (
            root.nodeType === 1 &&
            root.classList &&
            root.classList.contains('material-symbols-outlined')
        ) {
            convert(root);
        }

        if (root.querySelectorAll) {
            root.querySelectorAll('.material-symbols-outlined')
                .forEach(convert);
        }
    }

    /*
     * Convert existing page.
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            scan(document);
        }, { once: true });
    } else {
        scan(document);
    }

    /*
     * Convert icons inserted later by renderEmployees(),
     * drawers, occupancy, family table, etc.
     */
    const observer = new MutationObserver(function (mutations) {
        for (const mutation of mutations) {
            for (const node of mutation.addedNodes) {
                if (node.nodeType === 1) {
                    scan(node);
                }
            }
        }
    });

    observer.observe(document.documentElement, {
        childList: true,
        subtree: true
    });

})();
</script>


@endsection

<script>document.addEventListener('DOMContentLoaded',function(){['rmEmployeeSearch','rmDepartmentFilter','rmStatusFilter'].forEach(function(id){var el=document.getElementById(id);if(!el)return;el.addEventListener(id==='rmEmployeeSearch'?'input':'change',function(){if(window.reloadFiltered)window.reloadFiltered()});});});</script>
