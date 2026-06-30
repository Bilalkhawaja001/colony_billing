@extends('layouts.app')
@section('page_title','Family Directory')
@section('page_subtitle','All registered family members across colony employees, with school and presence status.')
@section('content')
<style>
.fam-wrap{display:flex;flex-direction:column;gap:12px}
.fam-toolbar{position:sticky;top:0;z-index:4;background:#fff;padding:12px;border:1px solid #e2e8f0;border-radius:12px;display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.fam-search{flex:1;min-width:220px;height:38px;padding:0 12px;border:1px solid #cbd5e1;border-radius:10px;font-size:13px}
.fam-badges{display:flex;gap:8px;flex-wrap:wrap}
.fam-badge{border-radius:999px;padding:6px 12px;font-size:12px;font-weight:800;border:1px solid #e2e8f0;background:#f8fafc;color:#0f172a}
.fam-badge b{color:#2563eb}

.fam-card-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px}
.fam-card{border:1px solid #e2e8f0;border-radius:14px;background:linear-gradient(180deg,#ffffff,#f8fafc);padding:12px;box-shadow:0 8px 18px rgba(15,23,42,.06)}
.fam-card-title{font-size:12px;font-weight:900;color:#334155;line-height:1.25;min-height:30px}
.fam-card-num{font-size:26px;font-weight:950;color:#0f172a;line-height:1;margin-top:8px}
.fam-card-meta{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px}
.fam-card-meta span{border-radius:999px;background:#eef2ff;color:#3730a3;font-size:11px;font-weight:850;padding:4px 7px}
.fam-card:nth-child(1) .fam-card-meta span,.fam-card:nth-child(3) .fam-card-meta span,.fam-card:nth-child(5) .fam-card-meta span{background:#eff6ff;color:#1d4ed8}
.fam-card:nth-child(2) .fam-card-meta span,.fam-card:nth-child(4) .fam-card-meta span,.fam-card:nth-child(6) .fam-card-meta span{background:#fdf2f8;color:#be185d}
@media(max-width:1100px){.fam-card-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:720px){.fam-card-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.fam-card-num{font-size:22px}}

.fam-table-card{border:1px solid #e2e8f0;border-radius:14px;background:#fff;overflow:hidden}
.fam-table{width:100%;border-collapse:collapse;font-size:13px}
.fam-table thead th{position:sticky;top:0;background:#f1f5f9;text-align:left;padding:10px 12px;font-weight:900;color:#334155;font-size:12px;text-transform:uppercase;letter-spacing:.03em;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.fam-table tbody td{padding:9px 12px;border-bottom:1px solid #f1f5f9;color:#0f172a;vertical-align:middle}
.fam-table tbody tr:hover{background:#f8fbff}
.fam-emp{font-weight:800}
.fam-emp small{display:block;color:#64748b;font-weight:600;font-size:11px}
.fam-pill{display:inline-block;border-radius:999px;padding:3px 9px;font-size:11px;font-weight:800}
.fam-pill.rel{background:#eff6ff;color:#1d4ed8}
.fam-pill.present{background:#f0fdf4;color:#16a34a}
.fam-pill.absent{background:#fef2f2;color:#dc2626}
.fam-pill.school{background:#fff7ed;color:#ea580c}
.fam-empty{padding:30px;text-align:center;color:#64748b;font-weight:700}
.fam-link{color:#2563eb;font-weight:800;text-decoration:none}
.fam-link:hover{text-decoration:underline}
@media(max-width:720px){.fam-table thead{display:none}.fam-table tbody td{display:block;border:none;padding:4px 12px}.fam-table tbody tr{display:block;border-bottom:1px solid #e2e8f0;padding:8px 0}}
</style>

<div class="fam-wrap">
  <div class="fam-toolbar">
    <input type="text" id="famSearch" class="fam-search" placeholder="Search by employee, member name, or relation...">
    <div class="fam-badges">
      <span class="fam-badge">Total <b>{{ count($familyRows ?? []) }}</b></span>
      <span class="fam-badge">Active <b>{{ collect($familyRows ?? [])->where('is_active',1)->count() }}</b></span>
      <span class="fam-badge">Present <b>{{ collect($familyRows ?? [])->where('current_status','PRESENT')->count() }}</b></span>
      <span class="fam-badge">School <b>{{ collect($familyRows ?? [])->where('school_going',1)->count() }}</b></span>
    </div>
  </div>


  @if(!empty($familyCards ?? []))
  <div class="fam-card-grid" id="famHouseCards">
    @foreach(($familyCards ?? []) as $card)
      <div class="fam-card"
           data-card-label="{{ $card['label'] }}"
           data-family-rows="{{ $card['family_rows'] }}"
           data-active-rows="{{ $card['active_rows'] }}"
           data-present-rows="{{ $card['present_rows'] }}"
           data-distinct-employees="{{ $card['distinct_employees'] }}">
        <div class="fam-card-title">{{ $card['label'] }}</div>
        <div class="fam-card-num">{{ $card['family_rows'] }}</div>
        <div class="fam-card-meta">
          <span>Active {{ $card['active_rows'] }}</span>
          <span>Present {{ $card['present_rows'] }}</span>
          <span>Emp {{ $card['distinct_employees'] }}</span>
        </div>
      </div>
    @endforeach
  </div>
  @endif


  <div class="fam-table-card">
    <table class="fam-table" id="famTable">
      <thead>
        <tr>
          <th>Employee</th>
          <th>Member</th>
          <th>Relation</th>
          <th>Age</th>
          <th>School</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse(($familyRows ?? []) as $row)
        <tr class="fam-trow"
            data-search="{{ strtolower(
              ($row['employee'] ?? '').' '.
              ($row['company_id'] ?? '').' '.
              ($row['member_name'] ?? '').' '.
              ($row['relation'] ?? '').' '.
              ($row['source_room_no'] ?? '').' '.
              ($row['employee_unit_id'] ?? '').' '.
              ($row['effective_unit'] ?? '').' '.
              ($row['card_label'] ?? '')
            ) }}">
          <td class="fam-emp">
            <a class="fam-link" href="{{ url('/employee-profile/'.rawurlencode($row['company_id'])) }}">{{ $row['employee'] ?: '—' }}</a>
            <small>{{ $row['company_id'] }}</small>
          </td>
          <td>{{ $row['member_name'] }}</td>
          <td><span class="fam-pill rel">{{ $row['relation'] }}</span></td>
          <td>{{ ($row['age'] !== null && $row['age'] > 0) ? rtrim(rtrim(number_format($row['age'],1),'0'),'.') : '—' }}</td>
          <td>
            @if($row['school_going'])
              <span class="fam-pill school">{{ $row['school_name'] ?: 'School' }}{{ $row['class_name'] ? ' · '.$row['class_name'] : '' }}</span>
            @else — @endif
          </td>
          <td>
            @if($row['current_status'] === 'PRESENT')
              <span class="fam-pill present">Present</span>
            @else
              <span class="fam-pill absent">{{ ucfirst(strtolower($row['current_status'])) }}</span>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="6" class="fam-empty">No family members found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<script>
(function(){
  var search = document.getElementById('famSearch');
  var rows = Array.prototype.slice.call(document.querySelectorAll('.fam-trow'));
  if(search){
    search.addEventListener('input', function(){
      var q = this.value.trim().toLowerCase();
      rows.forEach(function(r){
        r.style.display = (!q || (r.getAttribute('data-search')||'').indexOf(q) !== -1) ? '' : 'none';
      });
    });
  }
})();
</script>
@endsection
