@extends('layouts.app')
@section('page_title','Employee Profile')
@section('page_subtitle','Permanent employee identity, residence, family and issued-asset profile.')
@section('content')
@php
$employee = $profile['employee'];
$residence = $profile['residence'];
$kpis = $profile['kpis'];
$familyRows = $profile['family_rows'];
$familyMovements = $profile['family_movements'];
$assets = $profile['assets'];

$initials = collect(preg_split('/\s+/', trim($employee['name'])))
    ->filter()
    ->take(2)
    ->map(fn($part) => strtoupper(substr($part, 0, 1)))
    ->implode('');
@endphp

<style>
.page-head{display:none!important}
.container{padding-top:10px!important}
.ep-shell{font-family:Inter,Arial,sans-serif;color:#10263d}
.ep-hero{background:linear-gradient(115deg,#1557db 0%,#1876e7 55%,#1bb8b1 100%);border-radius:20px;padding:16px 22px;color:#fff;display:flex;align-items:center;justify-content:space-between;gap:18px;box-shadow:0 15px 34px rgba(17,82,184,.18)}
.ep-person{display:flex;align-items:center;gap:15px;min-width:300px}
.ep-avatar{width:62px;height:62px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.2);border:3px solid rgba(255,255,255,.32);font-size:23px;font-weight:900}
.ep-name{font-size:23px;font-weight:900;line-height:1.1;margin-bottom:4px}
.ep-badge{display:inline-flex;align-items:center;gap:5px;margin-left:8px;background:#18b97c;color:#fff;border-radius:999px;padding:4px 11px;font-size:12px;font-weight:800;vertical-align:middle}
.ep-sub{font-size:12px;color:rgba(255,255,255,.88);line-height:1.55}
.ep-hero-info{display:grid;grid-template-columns:repeat(3,minmax(112px,1fr));gap:13px 22px;min-width:430px}
.ep-hero-label{font-size:10px;text-transform:uppercase;letter-spacing:.7px;color:rgba(255,255,255,.74);margin-bottom:3px}
.ep-hero-value{font-size:13px;font-weight:800}
.ep-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:12px 0}
.ep-kpi{border-radius:16px;padding:13px 17px;min-height:76px;border:1px solid rgba(158,186,222,.4);box-shadow:0 7px 18px rgba(20,52,89,.05)}
.ep-kpi.blue{background:linear-gradient(135deg,#eef5ff,#deebff)}
.ep-kpi.teal{background:linear-gradient(135deg,#e9fcf8,#dbf7f1)}
.ep-kpi.violet{background:linear-gradient(135deg,#f4f0ff,#e9e2ff)}
.ep-kpi.amber{background:linear-gradient(135deg,#fff7e6,#ffedc7)}
.ep-kpi-label{font-size:12px;font-weight:700;color:#60748b;margin-bottom:5px}
.ep-kpi-value{font-size:26px;font-weight:900;color:#10263d;line-height:1}
.ep-kpi-note{font-size:11px;color:#627991;margin-top:5px}
.ep-tabs{display:flex;gap:7px;background:#fff;border:1px solid #e3ebf5;padding:5px;border-radius:14px;margin-bottom:12px}
.ep-tab{border:0;background:transparent;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:750;color:#5d718a;cursor:pointer}
.ep-tab.active{background:#1567db;color:#fff;box-shadow:0 7px 18px rgba(21,103,219,.23)}
.ep-panel{display:none}
.ep-panel.active{display:block}
.ep-card{background:#fff;border:1px solid #e2eaf4;border-radius:17px;box-shadow:0 8px 22px rgba(18,44,76,.05);padding:15px 17px}
.ep-card-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:11px}
.ep-card-title{margin:0;color:#11283f;font-size:18px;font-weight:850}
.ep-card-sub{font-size:12px;color:#71859a;margin-top:4px}
.ep-pill{border-radius:999px;background:#eaf2ff;color:#1460c9;padding:7px 13px;font-size:12px;font-weight:800}
.ep-table{width:100%;border-collapse:collapse;font-size:13px}
.ep-table th{padding:12px 11px;text-align:left;background:#f4f7fb;color:#627890;font-size:11px;letter-spacing:.55px;text-transform:uppercase;border-bottom:1px solid #e5ecf4}
.ep-table td{padding:13px 11px;border-bottom:1px solid #edf2f7;color:#243d57}
.ep-table tr:last-child td{border-bottom:0}
.ep-family-head td{background:#edf5ff}
.ep-role{display:inline-flex;padding:4px 9px;border-radius:999px;font-size:11px;font-weight:800;background:#e7efff;color:#1359c7}
.ep-status{display:inline-flex;padding:4px 10px;border-radius:999px;background:#e7f8ed;color:#158348;font-size:11px;font-weight:850}
.ep-grids{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.ep-detail{background:#f8fbff;border:1px solid #e6eef7;border-radius:13px;padding:11px 13px}
.ep-detail-label{color:#71849a;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;margin-bottom:7px}
.ep-detail-value{font-size:14px;color:#142d47;font-weight:700}
.ep-empty{padding:36px 18px;text-align:center;color:#6d8299;border:1px dashed #d9e5f2;border-radius:15px;background:#fbfdff}
.ep-actions{display:flex;gap:10px;margin-bottom:10px}
.ep-back{display:inline-flex;text-decoration:none;padding:9px 14px;border:1px solid #d8e4f1;border-radius:11px;background:#fff;color:#24507e;font-size:13px;font-weight:750}
.ep-alert{margin-bottom:12px;padding:11px 14px;border-radius:12px;font-size:13px;font-weight:700}
.ep-alert.error{background:#fff0f0;border:1px solid #f1c6c6;color:#ae3737}
.ep-moved{background:#fff4e5;color:#aa6700}
.ep-move-open{display:inline-flex;align-items:center;padding:6px 10px;border-radius:9px;border:1px solid #d7e5f3;background:#f7fbff;color:#24507e;font-size:12px;font-weight:750;cursor:pointer}
.ep-move-open.return{background:#eaf9f1;color:#16804d;border-color:#c4ebd5}
.ep-modal{position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(7,20,38,.46);backdrop-filter:blur(6px)}
.ep-modal.is-open{display:flex}
.ep-modal-card{width:min(520px,94vw);border-radius:18px;background:linear-gradient(180deg,#fff,#f5f9ff);border:1px solid #dbe8f5;box-shadow:0 24px 70px rgba(18,44,76,.28);padding:18px}
.ep-modal-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:12px}
.ep-modal-title{font-size:18px;font-weight:900;color:#11283f}
.ep-modal-sub{font-size:12px;color:#71859a;margin-top:4px}
.ep-modal-close{border:0;background:#eef4fb;border-radius:10px;padding:7px 10px;cursor:pointer;color:#315579;font-weight:900}
.ep-modal-form label{display:block;margin:10px 0 5px;font-size:12px;font-weight:800;color:#526a83}
.ep-modal-form input,.ep-modal-form textarea{width:100%;box-sizing:border-box;padding:10px 11px;border:1px solid #d7e3f0;border-radius:10px;background:#fff;font-size:13px}
.ep-modal-form textarea{min-height:88px;resize:vertical}
.ep-modal-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:14px}
.ep-modal-cancel{border:1px solid #d8e4f1;background:#fff;color:#315579;border-radius:10px;padding:9px 13px;font-weight:800;cursor:pointer}
.ep-submit{border:0;border-radius:10px;padding:9px 13px;background:#1567db;color:#fff;font-size:13px;font-weight:850;cursor:pointer}
.ep-submit.return{background:#18a567}
.ep-history-empty{padding:26px;text-align:center;color:#71859a;border:1px dashed #d9e5f2;border-radius:13px;background:#fbfdff}
@media(max-width:1050px){.ep-hero{display:block}.ep-hero-info{margin-top:22px;min-width:0}.ep-kpis,.ep-grids{grid-template-columns:1fr 1fr}}
</style>

<div class="ep-shell">
  @if($errors->has('family_movement'))
    <div class="ep-alert error">{{ $errors->first('family_movement') }}</div>
  @endif

  <div class="ep-actions">
    <a class="ep-back" href="/people-residency">← Back to People & Residency</a>
  </div>

  <section class="ep-hero">
    <div class="ep-person">
      <div class="ep-avatar">{{ $initials ?: 'EP' }}</div>
      <div>
        <div class="ep-name">
          {{ $employee['name'] }}
          <span class="ep-badge">{{ $employee['active_label'] }}</span>
        </div>
        <div class="ep-sub">
          Company ID: {{ $employee['company_id'] }}<br>
          Residence: {{ $residence['unit_id'] ?: '—' }} &nbsp;|&nbsp; {{ $residence['colony_type'] ?: 'No residence assigned' }}
        </div>
      </div>
    </div>
    <div class="ep-hero-info">
      <div>
        <div class="ep-hero-label">Employee Type</div>
        <div class="ep-hero-value">{{ $employee['employee_type'] ?: '—' }}</div>
      </div>
      <div>
        <div class="ep-hero-label">Department</div>
        <div class="ep-hero-value">{{ $employee['department'] ?: '—' }}</div>
      </div>
      <div>
        <div class="ep-hero-label">Designation</div>
        <div class="ep-hero-value">{{ $employee['designation'] ?: '—' }}</div>
      </div>
      <div>
        <div class="ep-hero-label">Section</div>
        <div class="ep-hero-value">{{ $employee['section'] ?: '—' }}</div>
      </div>
      <div>
        <div class="ep-hero-label">Contact</div>
        <div class="ep-hero-value">{{ $employee['mobile_no'] ?: '—' }}</div>
      </div>
      <div>
        <div class="ep-hero-label">CNIC</div>
        <div class="ep-hero-value">{{ $employee['cnic_no'] ?: '—' }}</div>
      </div>
    </div>
  </section>

  <section class="ep-kpis">
    <div class="ep-kpi blue">
      <div class="ep-kpi-label">Total Family Members</div>
      <div class="ep-kpi-value">{{ $kpis['total_family_members'] }}</div>
    </div>
    <div class="ep-kpi teal">
      <div class="ep-kpi-label">Issued Assets</div>
      <div class="ep-kpi-value">{{ $kpis['total_issued_assets'] }}</div>
      <div class="ep-kpi-note">Company property assigned</div>
    </div>
    <div class="ep-kpi violet">
      <div class="ep-kpi-label">Current Residence</div>
      <div class="ep-kpi-value" style="font-size:25px">{{ $residence['unit_id'] ?: '—' }}</div>
      <div class="ep-kpi-note">{{ $residence['colony_type'] ?: 'No assignment' }}</div>
    </div>
    <div class="ep-kpi amber">
      <div class="ep-kpi-label">Family Status</div>
      <div class="ep-kpi-value" style="font-size:25px">{{ $kpis['family_status'] }}</div>
    </div>
  </section>

  <nav class="ep-tabs" aria-label="Employee profile sections">
    <button class="ep-tab" type="button" data-profile-tab="overview">Overview</button>
    <button class="ep-tab" type="button" data-profile-tab="residence">Residence</button>
    <button class="ep-tab active" type="button" data-profile-tab="family">Family</button>
    <button class="ep-tab" type="button" data-profile-tab="assets">Issued Assets</button>
    <button class="ep-tab" type="button" data-profile-tab="history">History</button>
  </nav>

  <section id="profile-tab-overview" class="ep-panel">
    <div class="ep-card">
      <div class="ep-card-head"><div><h3 class="ep-card-title">Employee Overview</h3><div class="ep-card-sub">Permanent identity and assignment record.</div></div></div>
      <div class="ep-grids">
        <div class="ep-detail"><div class="ep-detail-label">Father Name</div><div class="ep-detail-value">{{ $employee['father_name'] ?: '—' }}</div></div>
        <div class="ep-detail"><div class="ep-detail-label">Sub Section</div><div class="ep-detail-value">{{ $employee['sub_section'] ?: '—' }}</div></div>
        <div class="ep-detail"><div class="ep-detail-label">Join Date</div><div class="ep-detail-value">{{ $employee['join_date'] ?: 'Not recorded' }}</div></div>
      </div>
    </div>
  </section>

  <section id="profile-tab-residence" class="ep-panel">
    <div class="ep-card">
      <div class="ep-card-head"><div><h3 class="ep-card-title">Current Residence Assignment</h3><div class="ep-card-sub">Employee-linked current housing details.</div></div></div>
      <div class="ep-grids">
        <div class="ep-detail"><div class="ep-detail-label">Unit / House</div><div class="ep-detail-value">{{ $residence['unit_id'] ?: '—' }}</div></div>
        <div class="ep-detail"><div class="ep-detail-label">Colony Type</div><div class="ep-detail-value">{{ $residence['colony_type'] ?: '—' }}</div></div>
        <div class="ep-detail"><div class="ep-detail-label">Floor</div><div class="ep-detail-value">{{ $residence['block_floor'] ?: '—' }}</div></div>
        <div class="ep-detail"><div class="ep-detail-label">Room No</div><div class="ep-detail-value">{{ $residence['room_no'] ?: '—' }}</div></div>
        <div class="ep-detail"><div class="ep-detail-label">Shared Room</div><div class="ep-detail-value">{{ $residence['shared_room'] ?: 'No' }}</div></div>
      </div>
    </div>
  </section>

  <section id="profile-tab-family" class="ep-panel active">
    <div class="ep-card">
      <div class="ep-card-head">
        <div>
          <h3 class="ep-card-title">Family Members — {{ $employee['name'] }}</h3>
          <div class="ep-card-sub">Family member movement is date-wise and retained in history.</div>
        </div>
        <span class="ep-pill">Total Family Members: {{ $kpis['total_family_members'] }}</span>
      </div>
      <table class="ep-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Member Name</th>
            <th>Relation / Role</th>
            <th>Age</th>
            <th>School Going</th>
            <th>Current Status</th>
            <th>Current House</th>
            <th>Latest Movement</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($familyRows as $index => $member)
          <tr class="{{ $member['is_family_head'] ? 'ep-family-head' : '' }}">
            <td>{{ $index + 1 }}</td>
            <td><strong>{{ $member['member_name'] }}</strong></td>
            <td>
              @if($member['is_family_head'])
                <span class="ep-role">{{ $member['relation'] }}</span>
              @else
                {{ $member['relation'] }}
              @endif
            </td>
            <td>{{ $member['age'] === null ? '—' : $member['age'] }}</td>
            <td>{{ $member['school_going'] === null ? '—' : ((int)$member['school_going'] === 1 ? 'Yes' : 'No') }}</td>
            <td>
              <span class="ep-status {{ $member['current_status'] === 'MOVED_OUT' ? 'ep-moved' : '' }}">
                {{ $member['current_status'] }}
              </span>
            </td>
            <td>{{ $member['current_house'] ?: '—' }}</td>
            <td>
              @if($member['latest_movement'])
                {{ $member['latest_movement']['movement_type'] === 'MOVE_OUT' ? 'Move Out' : 'Return Back' }}
                <br><span class="muted">{{ $member['latest_movement']['movement_date'] }}</span>
              @else
                —
              @endif
            </td>
            <td>
              @if($member['is_family_head'])
                <span class="muted">Residence workflow</span>
              @elseif($member['next_movement_type'])
                <button
                  class="ep-move-open {{ $member['next_movement_type'] === 'RETURN_BACK' ? 'return' : '' }}"
                  type="button"
                  data-move-open
                  data-member-name="{{ e($member['member_name']) }}"
                  data-action-label="{{ e($member['next_action_label']) }}"
                  data-movement-type="{{ $member['next_movement_type'] }}"
                  data-form-action="/employee-profile/{{ rawurlencode($employee['company_id']) }}/family-members/{{ $member['member_id'] }}/movement"
                >
                  {{ $member['next_action_label'] }}
                </button>
              @else
                <span class="muted">Review status</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>

  <section id="profile-tab-assets" class="ep-panel">
    <div class="ep-card">
      <div class="ep-card-head">
        <div><h3 class="ep-card-title">Company Issued Assets</h3><div class="ep-card-sub">Only actual assigned quantities from Employee Master are shown.</div></div>
        <span class="ep-pill">Total Quantity: {{ $kpis['total_issued_assets'] }}</span>
      </div>
      @if(count($assets) === 0)
        <div class="ep-empty">No issued asset quantity is currently recorded for this employee.</div>
      @else
        <table class="ep-table">
          <thead><tr><th>#</th><th>Asset</th><th>Quantity</th><th>Status</th></tr></thead>
          <tbody>
            @foreach($assets as $index => $asset)
            <tr><td>{{ $index + 1 }}</td><td>{{ $asset['label'] }}</td><td>{{ $asset['quantity'] }}</td><td><span class="ep-status">Issued</span></td></tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </section>

  <section id="profile-tab-history" class="ep-panel">
    <div class="ep-card">
      <div class="ep-card-head">
        <div>
          <h3 class="ep-card-title">Non-Billing History</h3>
          <div class="ep-card-sub">Family Move Out / Return Back transactions recorded against permanent family members.</div>
        </div>
      </div>

      @if(count($familyMovements) === 0)
        <div class="ep-history-empty">No family movements recorded yet.</div>
      @else
        <table class="ep-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Member Name</th>
              <th>Relation</th>
              <th>Movement</th>
              <th>Remarks</th>
              <th>Recorded By</th>
            </tr>
          </thead>
          <tbody>
            @foreach($familyMovements as $index => $movement)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $movement['movement_date'] }}</td>
              <td><strong>{{ $movement['member_name'] }}</strong></td>
              <td>{{ $movement['relation'] }}</td>
              <td>
                <span class="ep-role">
                  {{ $movement['movement_type'] === 'MOVE_OUT' ? 'Move Out' : 'Return Back' }}
                </span>
              </td>
              <td>{{ $movement['remarks'] ?: '—' }}</td>
              <td>{{ $movement['created_by'] ?: '—' }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </section>
</div>

  <div class="ep-modal" id="familyMovementModal" aria-hidden="true">
    <div class="ep-modal-card" role="dialog" aria-modal="true" aria-labelledby="familyMovementTitle">
      <div class="ep-modal-head">
        <div>
          <div class="ep-modal-title" id="familyMovementTitle">Family Movement</div>
          <div class="ep-modal-sub">Record date-wise family movement history.</div>
        </div>
        <button class="ep-modal-close" type="button" data-move-close>×</button>
      </div>

      <form class="ep-modal-form" id="familyMovementForm" method="POST" action="#">
        @csrf
        <input type="hidden" name="movement_type" id="familyMovementType">

        <label for="familyMovementDate">Movement Date</label>
        <input type="date" name="movement_date" id="familyMovementDate" max="{{ now()->format('Y-m-d') }}" required>

        <label for="familyMovementRemarks">Remarks</label>
        <textarea name="remarks" id="familyMovementRemarks" maxlength="1000" placeholder="Remarks (optional)"></textarea>

        <div class="ep-modal-actions">
          <button class="ep-modal-cancel" type="button" data-move-close>Cancel</button>
          <button class="ep-submit" id="familyMovementSubmit" type="submit">Save Movement</button>
        </div>
      </form>
    </div>
  </div>



<script>
document.querySelectorAll('[data-profile-tab]').forEach(button => {
  button.addEventListener('click', () => {
    const tab = button.dataset.profileTab;
    document.querySelectorAll('[data-profile-tab]').forEach(item => item.classList.remove('active'));
    document.querySelectorAll('.ep-panel').forEach(panel => panel.classList.remove('active'));
    button.classList.add('active');
    document.getElementById('profile-tab-' + tab).classList.add('active');
  });
});

const movementModal = document.getElementById('familyMovementModal');
const movementForm = document.getElementById('familyMovementForm');
const movementTitle = document.getElementById('familyMovementTitle');
const movementType = document.getElementById('familyMovementType');
const movementSubmit = document.getElementById('familyMovementSubmit');
const movementDate = document.getElementById('familyMovementDate');
const movementRemarks = document.getElementById('familyMovementRemarks');

function closeFamilyMovementModal(){
  movementModal.classList.remove('is-open');
  movementModal.setAttribute('aria-hidden', 'true');
  movementForm.setAttribute('action', '#');
  movementType.value = '';
  movementDate.value = '';
  movementRemarks.value = '';
}

document.querySelectorAll('[data-move-open]').forEach(button => {
  button.addEventListener('click', () => {
    const label = button.dataset.actionLabel || 'Movement';
    const member = button.dataset.memberName || 'Family Member';
    const type = button.dataset.movementType || '';

    movementTitle.textContent = label + ' — ' + member;
    movementForm.setAttribute('action', button.dataset.formAction || '#');
    movementType.value = type;
    movementSubmit.textContent = 'Save ' + label;
    movementSubmit.classList.toggle('return', type === 'RETURN_BACK');

    movementModal.classList.add('is-open');
    movementModal.setAttribute('aria-hidden', 'false');
    movementDate.focus();
  });
});

document.querySelectorAll('[data-move-close]').forEach(button => {
  button.addEventListener('click', closeFamilyMovementModal);
});

movementModal.addEventListener('click', event => {
  if (event.target === movementModal) {
    closeFamilyMovementModal();
  }
});

document.addEventListener('keydown', event => {
  if (event.key === 'Escape' && movementModal.classList.contains('is-open')) {
    closeFamilyMovementModal();
  }
});

</script>
@endsection
