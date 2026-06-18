@extends('layouts.app')
@section('page_title','Residency Master')
@section('page_subtitle','Read-only residency unit, room and occupancy control surface. Write actions are disabled pending approval.')
@section('content')
<style>
.page-head{display:none!important}.residency-shell{border:1px solid rgba(109,159,219,.28);background:linear-gradient(180deg,rgba(255,255,255,.94),rgba(238,246,254,.78))}.module-intro{display:flex;justify-content:space-between;gap:16px;padding:4px 2px 14px;border-bottom:1px solid rgba(170,190,216,.45);margin-bottom:14px}.module-intro h3{margin:0 0 5px;color:#10263d}.module-intro p{margin:0;color:#647a92;font-size:13px}.filter-grid{display:grid;grid-template-columns:repeat(4,minmax(160px,1fr));gap:10px;margin-bottom:12px}.filter-grid input,.filter-grid select{width:100%}.status-pill{display:inline-flex;padding:3px 8px;border-radius:999px;font-size:12px;font-weight:800;background:#e8f2ff;color:#214d78}.status-Conflict{background:#ffe0e0;color:#8a1f1f}.status-Shared{background:#fff2cc;color:#765300}.status-Vacant{background:#e9f7ec;color:#236b32}.status-Occupied{background:#e8f2ff;color:#214d78}.table-wrap{overflow:auto;max-height:68vh;border:1px solid rgba(170,190,216,.45);border-radius:14px}.res-table{width:100%;border-collapse:collapse;font-size:12px}.res-table th,.res-table td{padding:8px;border-bottom:1px solid rgba(170,190,216,.35);vertical-align:top}.res-table th{position:sticky;top:0;background:#f6fbff;color:#315579;text-align:left;z-index:1}.muted-small{color:#647a92;font-size:12px}.disabled-action{opacity:.55;cursor:not-allowed}.warn{background:#fff8e1;border:1px solid #ffe08a;color:#6b5200;padding:8px 10px;border-radius:10px;margin-bottom:10px}
</style>
<div class="card residency-shell">
  <div class="module-intro">
    <div>
      <h3>Residency Master</h3>
      <p>Read-only master for residency units, floors, rooms, active status and current occupancy. No DB writes are performed from this page.</p>
    </div>
    <span class="badge">Read Only</span>
  </div>

  <div id="snapshotWarning" class="warn" style="display:none"></div>

  <div class="toolbar" style="margin-bottom:10px">
    <button class="btn disabled-action" type="button" disabled>Create Unit</button>
    <button class="btn disabled-action" type="button" disabled>Create Room</button>
    <button class="btn disabled-action" type="button" disabled>Edit</button>
    <button class="btn disabled-action" type="button" disabled>Deactivate</button>
    <span class="muted-small">Write phase requires separate approval.</span>
  </div>

  <div class="filter-grid">
    <input id="residence_type" placeholder="Residency Type">
    <input id="colony_type" placeholder="Colony Type">
    <input id="block_floor" placeholder="Block/Floor">
    <input id="unit_id" placeholder="Unit ID">
    <input id="room_no" placeholder="Room No">
    <select id="unit_active"><option value="">Active / Inactive</option><option>Active</option><option>Inactive</option></select>
    <select id="occupancy_status"><option value="">Occupied / Vacant / Shared / Conflict</option><option>Vacant</option><option>Occupied</option><option>Shared</option><option>Conflict</option></select>
    <button class="btn btn-primary" type="button" onclick="loadResidencyMaster()">Apply Filters</button>
  </div>

  <div class="toolbar" style="margin-bottom:10px">
    <span class="badge" id="rowCount">Loading...</span>
    <span class="muted-small" id="snapshotMeta"></span>
  </div>

  <div class="table-wrap">
    <table class="res-table">
      <thead><tr><th>Residency Type</th><th>Colony Type</th><th>Block/Floor</th><th>Unit ID</th><th>Room No</th><th>Unit Active</th><th>Occupancy</th><th>Count</th><th>CompanyIDs</th><th>Employee Names</th><th>Departments</th><th>Conflict Notes</th><th>Action</th></tr></thead>
      <tbody id="residencyRows"><tr><td colspan="13">Loading...</td></tr></tbody>
    </table>
  </div>
</div>
<script>
function esc(v){return String(v ?? '').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));}
function list(v){return Array.isArray(v) ? v.map(esc).join('<br>') : esc(v);}
async function loadResidencyMaster(){
  const params = new URLSearchParams();
  ['residence_type','colony_type','block_floor','unit_id','room_no','unit_active','occupancy_status'].forEach(id=>{const v=document.getElementById(id).value.trim(); if(v) params.set(id,v);});
  const res = await fetch('/residency-master/list?' + params.toString(), {headers:{'Accept':'application/json'}});
  const data = await res.json();
  const meta = data.metadata || {};
  document.getElementById('rowCount').textContent = 'Rows: ' + (meta.total_rows ?? 0);
  document.getElementById('snapshotMeta').textContent = 'Snapshot: ' + (meta.snapshot_month || '-') + ' / ' + (meta.snapshot_source || '-');
  const warn = document.getElementById('snapshotWarning');
  if(meta.snapshot_warning){ warn.style.display='block'; warn.textContent = meta.snapshot_warning + ' SNAPSHOT_SOURCE=' + meta.snapshot_source; } else { warn.style.display='none'; }
  const rows = data.rows || [];
  document.getElementById('residencyRows').innerHTML = rows.length ? rows.map(r => `<tr><td>${esc(r.residence_type)}</td><td>${esc(r.colony_type)}</td><td>${esc(r.block_floor)}</td><td>${esc(r.unit_id)}</td><td>${esc(r.room_no)}</td><td>${esc(r.unit_active)}</td><td><span class="status-pill status-${esc(r.occupancy_status)}">${esc(r.occupancy_status)}</span></td><td>${esc(r.occupant_count)}</td><td>${list(r.assigned_company_ids)}</td><td>${list(r.assigned_employee_names)}</td><td>${list(r.departments)}</td><td>${list(r.conflict_notes)}</td><td><button class="btn disabled-action" disabled>Disabled</button></td></tr>`).join('') : '<tr><td colspan="13">No rows found.</td></tr>';
}
document.addEventListener('DOMContentLoaded', loadResidencyMaster);
</script>
@endsection
