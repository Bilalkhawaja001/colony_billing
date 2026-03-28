@extends('layouts.app')

@section('title', 'Month Cycle Governance')

@section('content')
<div class="container py-4">
    <h3 class="mb-3">Month Cycle Governance</h3>
    <p class="text-muted">Operational month state controls (OPEN/INGEST/VALIDATION/APPROVAL/LOCKED).</p>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Open Month</h5>
                <form id="monthOpenForm" class="row g-2">
                    <div class="col-8"><input class="form-control" name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY"></div>
                    <div class="col-4"><button class="btn btn-primary w-100" type="submit">Open</button></div>
                </form>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card"><div class="card-body">
                <h5>Transition Month</h5>
                <form id="monthTransitionForm" class="row g-2">
                    <div class="col-5"><input class="form-control" name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY"></div>
                    <div class="col-4">
                        <select class="form-select" name="to_state">
                            <option>OPEN</option><option>INGEST</option><option>VALIDATION</option><option>APPROVAL</option><option>LOCKED</option>
                        </select>
                    </div>
                    <div class="col-3"><button class="btn btn-warning w-100" type="submit">Apply</button></div>
                </form>
            </div></div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5>Month States</h5>
            <table class="table table-sm">
                <thead><tr><th>Month</th><th>State</th><th>Locked By</th><th>Locked At</th></tr></thead>
                <tbody>
                @forelse($rows as $r)
                    <tr>
                        <td>{{ $r->month_cycle ?? '' }}</td>
                        <td>{{ $r->state ?? '' }}</td>
                        <td>{{ $r->locked_by_user_id ?? '' }}</td>
                        <td>{{ $r->locked_at ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted">No month rows yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <pre id="monthCycleResult" class="mt-3 p-2 bg-light border rounded">Ready.</pre>
</div>

<script>
const csrf = @json(csrf_token());
async function postJson(url, payload){
  const r = await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});
  const j = await r.json().catch(()=>({raw:'non-json'}));
  document.getElementById('monthCycleResult').textContent = JSON.stringify({status:r.status,body:j},null,2);
}
document.getElementById('monthOpenForm').addEventListener('submit', e=>{e.preventDefault(); postJson('/month/open', Object.fromEntries(new FormData(e.target)));});
document.getElementById('monthTransitionForm').addEventListener('submit', e=>{e.preventDefault(); postJson('/month/transition', Object.fromEntries(new FormData(e.target)));});
</script>
@endsection