@extends('layouts.app')
@section('page_title','Monthly Staff Check')
@section('page_subtitle','Reconcile your HR active list against the colony database — review differences and resolve them row by row.')
@section('content')
<style>
.sc-wrap{display:flex;flex-direction:column;gap:16px}
.sc-notice{display:flex;align-items:center;gap:10px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:12px 16px;font-size:13px;font-weight:600;color:#1e40af}
.sc-notice svg{flex-shrink:0}
.sc-card{border:1px solid #e2e8f0;border-radius:16px;background:#fff;padding:20px;box-shadow:0 1px 3px rgba(15,23,42,.04)}
.sc-card-head{display:flex;align-items:center;gap:10px;margin-bottom:14px}
.sc-card-head .ico{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#3b82f6,#2563eb);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sc-card-head h3{margin:0;font-size:16px;font-weight:800;color:#0f172a}
.sc-card-head p{margin:2px 0 0;font-size:12px;color:#64748b;font-weight:500}
.sc-upload{display:flex;flex-wrap:wrap;gap:12px;align-items:stretch}
.sc-filewrap{flex:1;min-width:240px;position:relative;display:flex;align-items:center;border:1.5px dashed #cbd5e1;border-radius:10px;padding:0 14px;background:#f8fafc;transition:border-color .15s}
.sc-filewrap:hover{border-color:#3b82f6}
.sc-file{flex:1;font-size:13px;padding:11px 0;background:transparent;border:none}
.sc-btn{height:44px;padding:0 24px;border-radius:10px;border:none;background:linear-gradient(180deg,#3b82f6,#2563eb);color:#fff;font-weight:700;font-size:14px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(37,99,235,.25);transition:transform .12s}
.sc-btn:hover{transform:translateY(-1px)}
.sc-hint{font-size:12px;color:#64748b;margin-top:12px;line-height:1.6;padding-top:12px;border-top:1px solid #f1f5f9}
.sc-hint b{color:#334155}
.sc-err{display:flex;gap:10px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:14px 16px;color:#b91c1c;font-weight:600;font-size:13px}
.sc-summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px}
.sc-stat{position:relative;border:1px solid #e2e8f0;border-radius:14px;padding:16px;background:#fff;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,.04)}
.sc-stat:before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px}
.sc-stat .v{font-size:30px;font-weight:900;line-height:1;letter-spacing:-.02em}
.sc-stat .l{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b;margin-top:8px}
.sc-stat.total:before{background:#2563eb} .sc-stat.total .v{color:#2563eb}
.sc-stat.dbtotal:before{background:#0891b2} .sc-stat.dbtotal .v{color:#0891b2}
.sc-stat.matched:before{background:#16a34a} .sc-stat.matched .v{color:#16a34a}
.sc-stat.dbmiss:before{background:#ea580c} .sc-stat.dbmiss .v{color:#ea580c}
.sc-stat.hrmiss:before{background:#7c3aed} .sc-stat.hrmiss .v{color:#7c3aed}
.sc-stat.invalid:before{background:#dc2626} .sc-stat.invalid .v{color:#dc2626}
.sc-table{width:100%;border-collapse:collapse;font-size:13px}
.sc-table thead th{background:#f8fafc;text-align:left;padding:11px 14px;font-weight:700;color:#475569;font-size:11px;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid #e2e8f0;position:sticky;top:0}
.sc-table tbody td{padding:11px 14px;border-bottom:1px solid #f1f5f9;color:#0f172a}
.sc-table tbody tr:last-child td{border-bottom:none}
.sc-table tbody tr:hover{background:#f8fbff}
.sc-mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-weight:700;color:#1e40af}
.sc-pill{display:inline-block;border-radius:6px;padding:3px 9px;font-size:11px;font-weight:700}
.sc-pill.warn{background:#fef2f2;color:#dc2626}
.sc-empty{padding:28px;text-align:center;color:#94a3b8;font-weight:600;font-size:13px}
details.sc-acc{border:1px solid #e2e8f0;border-radius:14px;background:#fff;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,.04)}
details.sc-acc summary{padding:14px 18px;font-weight:800;font-size:14px;color:#0f172a;list-style:none;display:flex;justify-content:space-between;align-items:center;cursor:pointer;transition:background .12s}
details.sc-acc summary:hover{background:#f8fafc}
details.sc-acc summary::-webkit-details-marker{display:none}
details.sc-acc summary:after{content:'▼';font-size:10px;color:#94a3b8;transition:transform .15s}
details.sc-acc[open] summary:after{transform:rotate(180deg)}
details.sc-acc[open] summary{border-bottom:1px solid #f1f5f9}
.sc-acc .body{max-height:440px;overflow:auto}
.sc-badge{border-radius:999px;padding:3px 11px;font-size:12px;font-weight:800;margin-left:10px}
.sc-badge.g{background:#f0fdf4;color:#16a34a} .sc-badge.o{background:#fff7ed;color:#ea580c}
.sc-badge.p{background:#f5f3ff;color:#7c3aed} .sc-badge.r{background:#fef2f2;color:#dc2626}
.sc-reconcile{font-size:12px;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 16px;line-height:1.7}
.sc-reconcile b{color:#334155}
</style>

<div class="sc-wrap">

  <div class="sc-notice">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    <span>Actions on this page (Mark Left, Mark Outside, Edit) update the employee master immediately when confirmed. Every change is recorded in the audit log.</span>
  </div>

  <div class="sc-card">
    <div class="sc-card-head">
      <div class="ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></div>
      <div><h3>Upload HR Active List</h3><p>CSV file with current month's active employees</p></div>
    </div>
    <form method="POST" action="{{ url('/staff-check/compare') }}" enctype="multipart/form-data" class="sc-upload">
      @csrf
      <div class="sc-filewrap"><input type="file" name="csv" accept=".csv,text/csv" required class="sc-file"></div>
      <button type="submit" class="sc-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Compare
      </button>
    </form>
    <div class="sc-hint">
      <b>Required columns:</b> Company ID, Employee Name &nbsp;·&nbsp; <b>Optional:</b> Department, Designation<br>
      Company ID is read as text so leading zeros are preserved. Database active employees are those with active = Yes or 1.
    </div>
  </div>

  @if($result)
    @if(!empty($result['error']))
      <div class="sc-err">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>{{ $result['error'] }}
        @if(!empty($result['found_headers']))
          <div style="margin-top:6px;font-weight:500;color:#7f1d1d">Found headers: {{ implode(', ', $result['found_headers']) }}</div>
        @endif
        </div>
      </div>
    @else
      @php $c = $result['counts']; @endphp
      <div class="sc-summary">
        <div class="sc-stat total"><div class="v">{{ $c['hr_csv_total'] }}</div><div class="l">HR CSV Total</div></div>
        <div class="sc-stat dbtotal"><div class="v">{{ $c['db_active_total'] }}</div><div class="l">DB Active Total</div></div>
        <div class="sc-stat matched"><div class="v">{{ $c['matched'] }}</div><div class="l">Matched</div></div>
        <div class="sc-stat dbmiss"><div class="v">{{ $c['db_missing_in_hr'] }}</div><div class="l">DB Active, Not in HR</div></div>
        <div class="sc-stat hrmiss"><div class="v">{{ $c['hr_missing_in_db'] }}</div><div class="l">HR Active, Not in DB</div></div>
        <div class="sc-stat invalid"><div class="v">{{ $c['invalid'] }}</div><div class="l">Invalid / Conflict</div></div>
      </div>

      <details class="sc-acc">
        <summary>Matched <span class="sc-badge">{{ count($result['matched']) }}</span></summary>
        <div class="body">
          <table class="sc-table">
            <thead><tr><th>Company ID</th><th>HR Name</th><th>DB Name</th><th>Unit</th><th>Flag</th><th>Action</th></tr></thead>
            <tbody>
              @forelse($result['matched'] as $m)
                <tr>
                  <td class="sc-mono">{{ $m['company_id'] }}</td>
                  <td>{{ $m['hr_name'] ?: '—' }}</td>
                  <td>{{ $m['db_name'] }}</td>
                  <td>{{ $m['unit_id'] ?: '—' }}</td>
                  <td>@if($m['name_mismatch'])<span class="sc-pill warn">Name mismatch</span>@else <span style="color:#16a34a;font-weight:800">OK</span> @endif</td>
                  <td><button type="button" class="sc-act" data-bucket="matched" data-cid="{{ $m['company_id'] }}" data-hrname="{{ $m['hr_name'] }}" data-dbname="{{ $m['db_name'] }}" data-unit="{{ $m['unit_id'] ?? '' }}">Action</button></td>
                </tr>
              @empty
                <tr><td colspan="6" class="sc-empty">No matched rows.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </details>

      <details class="sc-acc">
        <summary>DB Active but Missing in HR <span class="sc-badge">{{ count($result['db_missing_in_hr']) }}</span></summary>
        <div class="body">
          <table class="sc-table">
            <thead><tr><th>Company ID</th><th>DB Name</th><th>Unit</th><th>Action</th></tr></thead>
            <tbody>
              @forelse($result['db_missing_in_hr'] as $r)
                <tr><td class="sc-mono">{{ $r['company_id'] }}</td><td>{{ $r['name'] }}</td><td>{{ $r['unit_id'] ?: '—' }}</td><td><button type="button" class="sc-act" data-bucket="db_missing" data-cid="{{ $r['company_id'] }}" data-dbname="{{ $r['name'] }}" data-unit="{{ $r['unit_id'] ?? '' }}">Action</button></td></tr>
              @empty
                <tr><td colspan="4" class="sc-empty">None — every DB active employee is present in HR list.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </details>

      <details class="sc-acc">
        <summary>HR Active but Missing in DB <span class="sc-badge">{{ count($result['hr_missing_in_db']) }}</span></summary>
        <div class="body">
          <table class="sc-table">
            <thead><tr><th>Company ID</th><th>HR Name</th><th>Department</th><th>Designation</th><th>Action</th></tr></thead>
            <tbody>
              @forelse($result['hr_missing_in_db'] as $r)
                <tr><td class="sc-mono">{{ $r['company_id'] }}</td><td>{{ $r['name'] ?: '—' }}</td><td>{{ $r['department'] ?: '—' }}</td><td>{{ $r['designation'] ?: '—' }}</td><td><button type="button" class="sc-act" data-bucket="hr_missing" data-cid="{{ $r['company_id'] }}" data-hrname="{{ $r['name'] }}" data-dept="{{ $r['department'] ?? '' }}" data-desig="{{ $r['designation'] ?? '' }}">Action</button></td></tr>
              @empty
                <tr><td colspan="5" class="sc-empty">None — every HR active employee exists in DB.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </details>

      <details class="sc-acc">
        <summary>Invalid / Duplicate / Conflict <span class="sc-badge">{{ count($result['invalid']) }}</span></summary>
        <div class="body">
          <table class="sc-table">
            <thead><tr><th>CSV Line</th><th>Company ID</th><th>Name</th><th>Issue</th><th>Action</th></tr></thead>
            <tbody>
              @forelse($result['invalid'] as $r)
                <tr><td>{{ $r['line'] ?? '—' }}</td><td class="sc-mono">{{ $r['company_id'] ?: '—' }}</td><td>{{ $r['name'] ?: '—' }}</td><td><span class="sc-pill warn">{{ $r['issue'] }}</span></td><td><button type="button" class="sc-act" data-bucket="invalid" data-cid="{{ $r['company_id'] }}" data-hrname="{{ $r['name'] }}" data-issue="{{ $r['issue'] }}">Action</button></td></tr>
              @empty
                <tr><td colspan="5" class="sc-empty">No invalid, duplicate, or conflict rows.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </details>

      <div class="sc-hint">Counts reconcile as: HR valid rows ({{ $c['hr_valid_rows'] }}) = Matched ({{ $c['matched'] }}) + HR-missing-in-DB ({{ $c['hr_missing_in_db'] }}). DB active ({{ $c['db_active_total'] }}) = Matched ({{ $c['matched'] }}) + DB-missing-in-HR ({{ $c['db_missing_in_hr'] }}).</div>
    @endif
  @endif

</div>

{{-- Redesign: direct-action modal (writes to DB immediately, with confirm) --}}
<style>
.sc-act{height:30px;padding:0 14px;border-radius:8px;border:1px solid #2563eb;background:#eff6ff;color:#1d4ed8;font-weight:800;font-size:12px;cursor:pointer}
.sc-act:hover{background:#dbeafe}
.sc-act.done{background:#dcfce7;border-color:#16a34a;color:#15803d}
.sc-addlink{height:30px;padding:0 14px;border-radius:8px;border:1px solid #16a34a;background:#f0fdf4;color:#15803d;font-weight:800;font-size:12px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}
.sc-addlink:hover{background:#dcfce7}
.sc-row-badge{display:inline-block;margin-left:8px;font-size:11px;font-weight:800;color:#15803d}
.sc-modal-bd{display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:50;align-items:center;justify-content:center;padding:20px 16px;overflow-y:auto}
.sc-modal-bd.open{display:flex}
.sc-modal{background:#fff;border-radius:16px;width:100%;max-width:500px;box-shadow:0 24px 60px rgba(15,23,42,.3);overflow:hidden;max-height:90vh;display:flex;flex-direction:column;margin:auto}
.sc-modal-h{padding:16px 20px;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center}
.sc-modal-h h3{margin:0;font-size:16px;font-weight:900;color:#0f172a}
.sc-modal-x{border:none;background:#f1f5f9;width:32px;height:32px;border-radius:8px;font-size:18px;cursor:pointer;color:#475569}
.sc-modal-b{padding:18px 20px;display:flex;flex-direction:column;gap:12px;overflow:auto}
.sc-fld{display:flex;flex-direction:column;gap:4px}
.sc-fld label{font-size:12px;font-weight:800;color:#475569;text-transform:uppercase;letter-spacing:.03em}
.sc-fld input,.sc-fld select,.sc-fld textarea{height:38px;padding:0 10px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px}
.sc-fld textarea{height:auto;padding:8px 10px;resize:vertical;min-height:56px}
.sc-fld input[readonly]{background:#f8fafc;color:#64748b;font-weight:700}
.sc-modal-info{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px 12px;font-size:12px;color:#334155}
.sc-modal-info b{color:#0f172a}
.sc-note{font-size:11px;color:#dc2626;font-weight:700;display:none}
.sc-note.show{display:block}
.sc-modal-f{padding:14px 20px;border-top:1px solid #e2e8f0;display:flex;gap:10px;justify-content:flex-end}
.sc-mbtn{height:38px;padding:0 18px;border-radius:9px;font-weight:800;font-size:13px;cursor:pointer;border:1px solid}
.sc-mbtn.cancel{background:#fff;border-color:#cbd5e1;color:#475569}
.sc-mbtn.save{background:linear-gradient(180deg,#ef4444,#dc2626);border-color:#b91c1c;color:#fff}
.sc-warn{background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 12px;font-size:12px;font-weight:700;color:#92400e}
</style>

<div class="sc-modal-bd" id="scModalBd">
  <div class="sc-modal">
    <div class="sc-modal-h">
      <h3 id="scModalTitle">Action</h3>
      <button type="button" class="sc-modal-x" id="scModalClose">&times;</button>
    </div>
    <div class="sc-modal-b">
      <div class="sc-warn">This change is saved to the employee master immediately when you confirm.</div>
      <div class="sc-modal-info" id="scModalInfo"></div>

      <div class="sc-fld">
        <label>Company ID (not editable)</label>
        <input type="text" id="scfCid" readonly>
      </div>

      <div class="sc-fld">
        <label>Action</label>
        <select id="scfAction">
          <option value="">— Select action —</option>
          <option value="mark_left">Mark Left / Deactivate</option>
          <option value="mark_outside">Mark Outside (no colony billing)</option>
          <option value="edit">Edit Details</option>
        </select>
      </div>

      <div id="scLeftFields" style="display:none;flex-direction:column;gap:12px">
        <div class="sc-fld">
          <label>Last Working Date</label>
          <input type="date" id="scfLwd">
        </div>
        <div class="sc-fld">
          <label>Reason</label>
          <input type="text" id="scfReason" placeholder="Reason for leaving">
        </div>
      </div>

      <div id="scEditFields" style="display:none;flex-direction:column;gap:12px">
        <div class="sc-fld"><label>Name</label><input type="text" id="scfName"></div>
        <div class="sc-fld"><label>Department</label><input type="text" id="scfDept"></div>
        <div class="sc-fld"><label>Designation</label><input type="text" id="scfDesig"></div>
      </div>

      <div class="sc-fld">
        <label>Remarks (optional)</label>
        <textarea id="scfRemarks" placeholder="Notes..."></textarea>
      </div>

      <div class="sc-note" id="scNote"></div>
    </div>
    <div class="sc-modal-f">
      <button type="button" class="sc-mbtn cancel" id="scModalCancel">Cancel</button>
      <button type="button" class="sc-mbtn save" id="scModalSave">Confirm &amp; Save</button>
    </div>
  </div>
</div>

<script>
(function(){
  var BUCKET_TITLE = {matched:'Matched', db_missing:'DB Active but Missing in HR', invalid:'Invalid / Conflict'};
  var bd=document.getElementById('scModalBd');
  var elTitle=document.getElementById('scModalTitle');
  var elInfo=document.getElementById('scModalInfo');
  var elCid=document.getElementById('scfCid');
  var elAction=document.getElementById('scfAction');
  var elLeft=document.getElementById('scLeftFields');
  var elLwd=document.getElementById('scfLwd');
  var elReason=document.getElementById('scfReason');
  var elEdit=document.getElementById('scEditFields');
  var elName=document.getElementById('scfName');
  var elDept=document.getElementById('scfDept');
  var elDesig=document.getElementById('scfDesig');
  var elRemarks=document.getElementById('scfRemarks');
  var elNote=document.getElementById('scNote');
  var elSave=document.getElementById('scModalSave');
  var current=null;

  function esc(x){return (x==null?'':String(x));}
  function csrf(){var m=document.querySelector('input[name="_token"]');return m?m.value:'';}

  function toggleFields(){
    var a=elAction.value;
    elLeft.style.display = a==='mark_left' ? 'flex' : 'none';
    elEdit.style.display = a==='edit' ? 'flex' : 'none';
    elNote.classList.remove('show');
  }
  elAction.addEventListener('change', toggleFields);

  function openModal(btn){
    var b=btn.getAttribute('data-bucket');
    var cid=btn.getAttribute('data-cid');
    current={bucket:b,cid:cid,btn:btn};
    elTitle.textContent=BUCKET_TITLE[b]||'Action';
    elCid.value=cid;
    var nm=btn.getAttribute('data-dbname')||btn.getAttribute('data-hrname')||'';
    elName.value=nm;
    elDept.value=btn.getAttribute('data-dept')||'';
    elDesig.value=btn.getAttribute('data-desig')||'';
    var info='<b>Company ID:</b> '+esc(cid);
    if(btn.getAttribute('data-hrname')) info+='<br><b>HR Name:</b> '+esc(btn.getAttribute('data-hrname'));
    if(btn.getAttribute('data-dbname')) info+='<br><b>DB Name:</b> '+esc(btn.getAttribute('data-dbname'));
    if(btn.getAttribute('data-unit')) info+='<br><b>Unit:</b> '+esc(btn.getAttribute('data-unit'));
    if(btn.getAttribute('data-issue')) info+='<br><b>Issue:</b> '+esc(btn.getAttribute('data-issue'));
    elInfo.innerHTML=info;
    elAction.value=''; elLwd.value=''; elReason.value=''; elRemarks.value='';
    toggleFields(); elNote.classList.remove('show');
    bd.classList.add('open');
  }
  function closeModal(){bd.classList.remove('open');current=null;}

  function save(){
    if(!current) return;
    var a=elAction.value;
    if(!a){elNote.textContent='Please select an action.';elNote.classList.add('show');return;}
    var payload={cid:current.cid, action:a, remarks:elRemarks.value.trim()};
    if(a==='mark_left'){
      if(!elLwd.value||!elReason.value.trim()){elNote.textContent='Last Working Date and Reason are required.';elNote.classList.add('show');return;}
      payload.lwd=elLwd.value; payload.reason=elReason.value.trim();
      if(!confirm('Mark '+current.cid+' as LEFT? This updates the employee master now.')) return;
    } else if(a==='mark_outside'){
      if(!confirm('Mark '+current.cid+' as OUTSIDE (no colony billing)? This updates the master now.')) return;
    } else if(a==='edit'){
      if(!elName.value.trim()){elNote.textContent='Name cannot be empty.';elNote.classList.add('show');return;}
      payload.name=elName.value.trim(); payload.department=elDept.value.trim(); payload.designation=elDesig.value.trim();
      if(!confirm('Save details for '+current.cid+'?')) return;
    }
    elSave.disabled=true; elSave.textContent='Saving...';
    fetch('{{ url('/staff-check/action') }}',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf(),'Accept':'application/json'},
      body:JSON.stringify(payload)
    }).then(function(r){return r.json().then(function(j){return {ok:r.ok,j:j};});})
      .then(function(res){
        elSave.disabled=false; elSave.textContent='Confirm & Save';
        if(!res.ok||res.j.status!=='ok'){elNote.textContent='Error: '+(res.j.error||'failed');elNote.classList.add('show');return;}
        current.btn.classList.add('done'); current.btn.textContent='Done';
        var cell=current.btn.parentNode;
        var ex=cell.querySelector('.sc-row-badge'); if(ex) ex.remove();
        var bdg=document.createElement('span'); bdg.className='sc-row-badge'; bdg.textContent='✓ saved'; cell.appendChild(bdg);
        closeModal();
      })
      .catch(function(e){elSave.disabled=false;elSave.textContent='Confirm & Save';elNote.textContent='Network error: '+e;elNote.classList.add('show');});
  }

  document.querySelectorAll('.sc-act').forEach(function(btn){btn.addEventListener('click',function(){openModal(btn);});});
  document.getElementById('scModalClose').addEventListener('click', closeModal);
  document.getElementById('scModalCancel').addEventListener('click', closeModal);
  elSave.addEventListener('click', save);
  bd.addEventListener('click', function(e){if(e.target===bd) closeModal();});
})();
</script>

@endsection