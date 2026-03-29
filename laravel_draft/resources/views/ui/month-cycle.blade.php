@extends('layouts.app')
@section('page_title','Month Lifecycle')
@section('page_subtitle','Control month lifecycle states with clear operator guardrails and visibility.')
@section('content')
<div class="grid">
    <div class="col-6 card">
        <h3 class="section-title">Open Month</h3>
        <form id="monthOpenForm" class="form-grid">
            <div class="field col-8"><label class="label">Month Cycle</label><input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY"></div>
            <div class="col-4" style="display:flex;align-items:flex-end"><button class="btn btn-primary" type="submit">Open</button></div>
        </form>
    </div>
    <div class="col-6 card">
        <h3 class="section-title">Transition State</h3>
        <form id="monthTransitionForm" class="form-grid">
            <div class="field col-5"><label class="label">Month Cycle</label><input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY"></div>
            <div class="field col-4"><label class="label">To State</label>
                <select name="to_state"><option>OPEN</option><option>INGEST</option><option>VALIDATION</option><option>APPROVAL</option><option>LOCKED</option></select>
            </div>
            <div class="col-3" style="display:flex;align-items:flex-end"><button class="btn btn-warn" type="submit">Apply</button></div>
        </form>
    </div>

    <div class="col-12 card">
        <h3 class="section-title">Month State Register</h3>
        <table>
            <thead><tr><th>Month</th><th>State</th><th>Locked By</th><th>Locked At</th></tr></thead>
            <tbody>
            @forelse($rows as $r)
                <tr>
                    <td>{{ $r->month_cycle ?? '' }}</td>
                    <td><span class="badge">{{ $r->state ?? '' }}</span></td>
                    <td>{{ $r->locked_by_user_id ?? '—' }}</td>
                    <td>{{ $r->locked_at ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4"><div class="empty">No month rows yet.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="col-12 card soft">
        <h3 class="section-title">Execution Status</h3>
        <div id="monthStatus" class="banner">Ready.</div>
        <details style="margin-top:10px">
            <summary class="muted">Technical response</summary>
            <pre id="monthCycleResult" style="margin-top:8px">{}</pre>
        </details>
    </div>
</div>
<script>
const csrf = @json(csrf_token());
function setStatus(ok,text){
  const el=document.getElementById('monthStatus');
  el.className=ok?'banner':'alert';
  el.textContent=text;
}
async function postJson(url, payload){
  const r = await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});
  const j = await r.json().catch(()=>({raw:'non-json'}));
  document.getElementById('monthCycleResult').textContent = JSON.stringify({status:r.status,body:j},null,2);
  setStatus(r.ok, r.ok ? `Updated successfully (${r.status})` : `Failed to update (${r.status})`);
}
document.getElementById('monthOpenForm').addEventListener('submit', e=>{e.preventDefault(); postJson('/month/open', Object.fromEntries(new FormData(e.target)));});
document.getElementById('monthTransitionForm').addEventListener('submit', e=>{e.preventDefault(); postJson('/month/transition', Object.fromEntries(new FormData(e.target)));});
</script>
@endsection