@extends('layouts.app')
@section('page_title','Unit Directory')
@section('page_subtitle','Unit master, residence categories, colony drilldowns and resident details.')
@section('content')

<style>
.local-sticky{position:sticky;top:0;z-index:4;background:#fff;padding:10px;border:1px solid #e2e8f0;border-radius:10px}
.unit-main-card-grid,.unit-sub-card-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px}
.unit-main-card,.unit-sub-card{border:1px solid var(--border,#dbeafe);border-radius:15px;background:linear-gradient(135deg,var(--bg1,#fff),var(--bg2,#f8fbff));padding:13px 14px;text-align:left;cursor:pointer;box-shadow:0 8px 18px rgba(15,23,42,.055);transition:transform .15s ease,box-shadow .15s ease;position:relative;overflow:hidden}
.unit-main-card:hover,.unit-sub-card:hover{transform:translateY(-2px);box-shadow:0 14px 26px rgba(15,23,42,.12)}
.unit-main-card::before,.unit-sub-card::before{content:"";position:absolute;left:0;top:0;bottom:0;width:5px;background:var(--accent,#2563eb)}
.um-title,.us-title{font-size:13px;line-height:1.25;font-weight:900;color:#0f172a;margin-bottom:8px}
.um-value{font-size:30px;line-height:1;font-weight:950;color:var(--accent,#2563eb)}
.um-meta,.us-meta{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px}
.um-meta span,.us-meta span{border-radius:999px;background:rgba(255,255,255,.76);border:1px solid rgba(255,255,255,.86);padding:5px 8px;font-size:11px;font-weight:800;color:#475569}
.um-meta b,.us-meta b{color:#0f172a}
.us-row{display:flex;align-items:baseline;gap:7px;margin-top:4px}
.us-row strong{font-size:26px;line-height:1;color:var(--accent,#2563eb);font-weight:950}
.us-row span{font-size:10px;font-weight:800;text-transform:uppercase;color:#64748b;letter-spacing:.06em}
.tone-house{--accent:#16a34a;--border:#bbf7d0;--bg1:#f0fdf4;--bg2:#fff}
.tone-bachelor{--accent:#7c3aed;--border:#ddd6fe;--bg1:#f5f3ff;--bg2:#fff}
.tone-hostel{--accent:#ea580c;--border:#fed7aa;--bg1:#fff7ed;--bg2:#fff}
.tone-container{--accent:#0f766e;--border:#99f6e4;--bg1:#f0fdfa;--bg2:#fff}
.tone-uncategorized{--accent:#6b7280;--border:#e5e7eb;--bg1:#f9fafb;--bg2:#fff}
.tone-default{--accent:#334155;--border:#cbd5e1;--bg1:#f8fafc;--bg2:#fff}
.room-link-btn{
    padding:6px 10px;
    border-radius:10px;
    font-weight:900;
    color:#1d4ed8;
}
</style>

<div class="grid">
    <div class="col-12 card soft">
        <div class="toolbar local-sticky">
            <span class="badge">Directory Control</span>
            <button class="btn" type="button" id="loadUnitsBtn">Reload Units</button>
        </div>
    </div>

    <div class="col-12 card" id="residentGroupCard">
        <h3 class="section-title">Unit Category Overview</h3>
        <div id="residentGroupCards"></div>
    </div>

    <div class="col-12 card" id="residentSubGroupCard" style="display:none">
        <h3 class="section-title" id="residentSubGroupTitle">Sub Categories</h3>
        <div id="residentSubGroupCards"></div>
    </div>

    <div class="col-12 card" id="residentRoomCard" style="display:none">
        <h3 class="section-title" id="residentRoomTitle">Rooms</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Room No</th>
                        <th>Unit ID</th>
                        <th>Floor</th>
                        <th>Employees</th>
                    </tr>
                </thead>
                <tbody id="residentRoomRows">
                    <tr><td colspan="4"><div class="empty">Select a bachelor / hostel colony.</div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12 card" id="residentDetailsCard" style="display:none">
        <h3 class="section-title" id="residentDetailsTitle">Residents</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Company ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Family Members</th>
                        <th>Colony</th>
                        <th>Unit</th>
                        <th>Floor</th>
                        <th>Room</th>
                        <th>Active Days</th>
                    </tr>
                </thead>
                <tbody id="residentRows">
                    <tr><td colspan="10"><div class="empty">Select a category.</div></td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-7 card">
        <h3 class="section-title">Single Upsert</h3>
        <form id="unitUpsertForm" class="form-grid">
            <div class="field col-6"><label class="label">Unit ID</label><input name="unit_id" placeholder="U-001"></div>
            <div class="field col-6"><label class="label">Unit Name</label><input name="unit_name" placeholder="Unit Name"></div>
            <div class="col-12"><button class="btn btn-primary" type="submit">Save Unit</button></div>
        </form>
    </div>

    <div class="col-5 card">
        <h3 class="section-title">CSV Bulk Upload</h3>
        <div class="muted" style="margin-bottom:8px">Header: <code>unit_id,unit_name</code></div>
        <div class="toolbar">
            <button class="btn" type="button" id="downloadUnitTemplate">Download Template</button>
            <input type="file" id="unitCsvFile" accept=".csv,text/csv">
            <button class="btn btn-primary" type="button" id="importUnitCsv">Import CSV</button>
        </div>
    </div>

    <div class="col-12 card">
        <h3 class="section-title">Unit Listing</h3>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Unit ID</th><th>Colony Type</th><th>Block/Floor</th><th>Room No</th><th>Active</th></tr></thead>
                <tbody id="unitRows"><tr><td colspan="5"><div class="empty">No rows loaded.</div></td></tr></tbody>
            </table>
        </div>
    </div>

    <div class="col-12 card">
        <h3 class="section-title">Operation Status</h3>
        <div id="unitStatus" class="banner">Ready.</div>
        <details style="margin-top:8px">
            <summary class="muted">Technical response</summary>
            <pre id="unitResult" style="margin-top:8px">{}</pre>
        </details>
    </div>
</div>

<script>
const csrf=@json(csrf_token());
const out=document.getElementById('unitResult');
const rowsEl=document.getElementById('unitRows');

function setStatus(ok,text){const el=document.getElementById('unitStatus'); el.className=ok?'banner':'alert'; el.textContent=text;}
function show(v){out.textContent=JSON.stringify(v,null,2); const ok=(v?.status>=200&&v?.status<300)||v?.status==='done'; setStatus(ok, ok?'Completed successfully.':'Action failed.');}
function parseCsv(t){const lines=t.split(/\r?\n/).map(s=>s.trim()).filter(Boolean); if(lines.length<2)return []; const h=lines[0].split(',').map(s=>s.trim()); return lines.slice(1).map(l=>{const c=l.split(',').map(s=>s.trim()); return Object.fromEntries(h.map((k,i)=>[k,c[i]??'']));});}
function download(name,c){const b=new Blob([c],{type:'text/csv'});const a=document.createElement('a');a.href=URL.createObjectURL(b);a.download=name;a.click();URL.revokeObjectURL(a.href);}
async function req(url,method='GET',payload=null){const o={method,headers:{'X-CSRF-TOKEN':csrf}};if(payload){o.headers['Content-Type']='application/json';o.body=JSON.stringify(payload);} const r=await fetch(url,o);const j=await r.json().catch(()=>({raw:'non-json'}));const v={status:r.status,body:j};show(v);return v;}

function render(rows){
    if(!Array.isArray(rows)||rows.length===0){
        rowsEl.innerHTML='<tr><td colspan="5"><div class="empty">No rows found.</div></td></tr>';
        return;
    }
    rowsEl.innerHTML=rows.map(r=>`<tr>
        <td>${r.unit_id??''}</td>
        <td>${r.colony_type??''}</td>
        <td>${r.block_name??''}</td>
        <td>${r.room_no??''}</td>
        <td>${r.is_active??''}</td>
    </tr>`).join('');
}

document.getElementById('unitUpsertForm').addEventListener('submit',e=>{e.preventDefault();req('/units/upsert','POST',Object.fromEntries(new FormData(e.target)));});
document.getElementById('downloadUnitTemplate').onclick=()=>download('unit_master_template.csv','unit_id,unit_name\nU-001,Unit 1\n');
document.getElementById('importUnitCsv').onclick=async()=>{const f=document.getElementById('unitCsvFile').files[0]; if(!f)return show({status:400,error:'Select CSV file'}); const rows=parseCsv(await f.text()); if(rows.length===0)return show({status:400,error:'No data rows'}); let ok=0,fail=0,errors=[]; for(let i=0;i<rows.length;i++){const r=await req('/units/upsert','POST',{unit_id:rows[i].unit_id,unit_name:rows[i].unit_name}); if(r.status>=200&&r.status<300)ok++; else {fail++;errors.push({line:i+2,row:rows[i],response:r});}} show({status:'done',processed:rows.length,ok,fail,errors});};

async function loadFilteredUnits(){
    const params = new URLSearchParams(window.location.search || '');
    params.set('page', '1');
    params.set('per_page', '100');
    const r = await req('/api/grids/units?' + params.toString());
    render(r.body?.rows || []);
    const label = params.get('res_type') || params.get('colony_type') || 'all';
    setStatus(true, 'Loaded units filter: ' + label);
}
document.getElementById('loadUnitsBtn').onclick=loadFilteredUnits;

function detectGroup(label){
    const v = String(label || '').toLowerCase();

    if(v.includes('hod') || v.includes('hostel') || v.includes('guest')) return 'hostel';
    if(v.includes('palidar') || v.includes('bachelor')) return 'bachelor';
    if(v.includes('old abaseen') || v.includes('admin block') || v.includes('container')) return 'containers';
    if(v.includes('family') || v.includes('house')) return 'house';
    if(!v || v.includes('null') || v.includes('uncategorized')) return 'uncategorized';

    return 'uncategorized';
}


function groupTone(key){
    return {house:'house',bachelor:'bachelor',hostel:'hostel',containers:'container',uncategorized:'uncategorized',other:'default'}[key] || 'default';
}

function groupLabel(key){
    return {house:'House Units',bachelor:'Bachelor Units',hostel:'Hostel Units',containers:'Admin Block',uncategorized:'Uncategorized',other:'Other'}[key] || key;
}

function sumRows(list, key){return list.reduce((a,x)=>a+Number(x[key]||0),0);}

async function loadResidentGroups(){
    const params = new URLSearchParams(window.location.search || '');
    const forcedType = params.get('res_type') || '';

    const r = await req('/api/units/resident-groups?' + params.toString());
    const rows = r.body?.rows || [];
    const host = document.getElementById('residentGroupCards');

    if(!rows.length){
        host.innerHTML='<div class="empty">No category data found.</div>';
        return;
    }

    const grouped = {};
    rows.forEach(row => {
        const key = detectGroup(row.colony_type || '');
        if(!grouped[key]) grouped[key]=[];
        grouped[key].push(row);
    });

    if(forcedType && grouped[forcedType]){
        renderMainCards({[forcedType]: grouped[forcedType]}, host);
        renderSubCards(groupLabel(forcedType), grouped[forcedType]);
        return;
    }

    renderMainCards(grouped, host);
}

function renderMainCards(grouped, host){
    const order=['house','bachelor','hostel','containers','uncategorized','other'];
    host.innerHTML=`<div class="unit-main-card-grid">${
        order.filter(k=>grouped[k]?.length).map(k=>{
            const list=grouped[k];
            const tone=groupTone(k);
            return `<button type="button" class="unit-main-card tone-${tone}" data-main="${k}">
                <div class="um-title">${groupLabel(k)}</div>
                <div class="um-value">${sumRows(list,'resident_count')}</div>
                <div class="um-meta">
                    <span>Categories <b>${list.length}</b></span>
                    <span>Units <b>${sumRows(list,'unit_count')}</b></span>
                    <span>Rooms <b>${sumRows(list,'room_count')}</b></span>
                </div>
            </button>`;
        }).join('')
    }</div>`;

    host.querySelectorAll('[data-main]').forEach(btn=>{
        const key=btn.dataset.main;
        btn.onclick=()=>renderSubCards(groupLabel(key), grouped[key] || []);
    });
}

function renderSubCards(title, rows){
    const card=document.getElementById('residentSubGroupCard');
    const host=document.getElementById('residentSubGroupCards');
    const heading=document.getElementById('residentSubGroupTitle');

    card.style.display='block';
    heading.textContent=title+' - Categories';

    host.innerHTML=`<div class="unit-sub-card-grid">${
        rows.map(row=>{
            const colony=row.colony_type || '__uncategorized';
            const label=row.colony_type || 'Uncategorized';
            const tone=groupTone(detectGroup(label));
            return `<button type="button" class="unit-sub-card tone-${tone}" data-colony="${String(colony).replaceAll('"','&quot;')}">
                <div class="us-title">${label}</div>
                <div class="us-row"><strong>${row.resident_count ?? 0}</strong><span>Residents</span></div>
                <div class="us-meta"><span>Units <b>${row.unit_count ?? 0}</b></span><span>Rooms <b>${row.room_count ?? 0}</b></span></div>
            </button>`;
        }).join('')
    }</div>`;

    host.querySelectorAll('[data-colony]').forEach(btn=>{
        btn.onclick=()=>{
            const label = btn.querySelector('.us-title')?.textContent || '';
            const group = detectGroup(label);
            if(group === 'bachelor' || group === 'hostel'){
                loadRooms(btn.dataset.colony, label);
            } else {
                loadResidents(btn.dataset.colony, label);
            }
        };
    });
}


async function loadRooms(colony, label){
    const params = new URLSearchParams(window.location.search || '');
    params.set('colony_type', colony);

    const card = document.getElementById('residentRoomCard');
    const title = document.getElementById('residentRoomTitle');
    const body = document.getElementById('residentRoomRows');

    card.style.display = 'block';
    title.textContent = 'Rooms - ' + label;

    const r = await req('/api/units/resident-rooms?' + params.toString());
    const rows = r.body?.rows || [];

    if(!rows.length){
        body.innerHTML = '<tr><td colspan="4"><div class="empty">No rooms found.</div></td></tr>';
        return;
    }

    body.innerHTML = rows.map(row => `<tr>
        <td>
            <button type="button" class="btn room-link-btn" data-colony="${String(colony).replaceAll('"','&quot;')}" data-room="${String(row.room_no || '').replaceAll('"','&quot;')}">
                ${row.room_no ?? ''}
            </button>
        </td>
        <td>${row.unit_id ?? ''}</td>
        <td>${row.block_floor ?? ''}</td>
        <td><strong>${row.employee_count ?? 0}</strong></td>
    </tr>`).join('');

    body.querySelectorAll('[data-room]').forEach(btn=>{
        btn.onclick=()=>loadResidents(btn.dataset.colony, 'Room ' + btn.dataset.room, btn.dataset.room);
    });
}


async function loadResidents(colony, label, roomNo=""){
    const params=new URLSearchParams(window.location.search || '');
    params.set('colony_type', colony);
    if(roomNo) params.set('room_no', roomNo);

    const card=document.getElementById('residentDetailsCard');
    const title=document.getElementById('residentDetailsTitle');
    const body=document.getElementById('residentRows');

    card.style.display='block';
    title.textContent='Residents - '+label;

    const r=await req('/api/units/residents?'+params.toString());
    const rows=r.body?.rows || [];

    if(!rows.length){
        body.innerHTML='<tr><td colspan="10"><div class="empty">No residents found.</div></td></tr>';
        return;
    }

    body.innerHTML=rows.map(x=>`<tr>
        <td>${x.company_id ?? ''}</td>
        <td>${x.name ?? ''}</td>
        <td>${x.department ?? ''}</td>
        <td>${x.designation ?? ''}</td>
        <td>${x.family_members ?? 0}</td>
        <td>${x.colony_type ?? ''}</td>
        <td>${x.unit_id ?? ''}</td>
        <td>${x.block_floor ?? ''}</td>
        <td>${x.room_no ?? ''}</td>
        <td>${x.active_days ?? ''}</td>
    </tr>`).join('');
}

document.addEventListener('DOMContentLoaded', () => {
    loadFilteredUnits();
    loadResidentGroups();
});
</script>

<div class="grid" style="margin-top:14px"><div class="col-12" data-grid="units"></div></div>
<script src="/js/crud-grids.js"></script>
@endsection
