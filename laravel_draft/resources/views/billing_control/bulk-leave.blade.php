@extends('billing_control.layout')
@section('content')
@include('billing_control.components.tw-head')
<style>
  .sidebar,.billing-center-nav{display:none!important}
  .main,.main-inner{max-width:100%!important;width:100%!important;padding-left:24px!important;padding-right:24px!important}
</style>

<div style="font-family:Inter,sans-serif;max-width:1100px">
  <div style="margin-bottom:18px">
    <div class="text-xs font-semibold text-blue uppercase tracking-wider mb-1">HR Operations</div>
    <h1 class="text-3xl font-bold text-ink">Bulk Mark as Left</h1>
    <div class="text-sm text-muted mt-1">Upload a CSV of employees who have left. Columns: <code>CompanyID</code>, <code>leave_date</code>.</div>
  </div>

  @if(session('status'))<div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:14px;font-weight:600">{{ session('status') }}</div>@endif
  @if(session('error'))<div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:14px;font-weight:600">{{ session('error') }}</div>@endif

  <div class="bg-surface border border-border rounded-xl p-4 mb-5">
    <form method="post" action="{{ route('billing.emp.bulkleave') }}" enctype="multipart/form-data" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
      @csrf
      <div>
        <label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">CSV File</label>
        <input type="file" name="csv_file" accept=".csv" required style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 10px;font-size:14px">
      </div>
      <div>
        <label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">Default Leave Date</label>
        <input type="date" name="default_date" value="{{ session('leave_default', date('Y-m-d')) }}" style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 10px;font-size:14px">
      </div>
      <button type="submit" style="padding:9px 18px;background:#2563eb;color:#fff;border:0;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">Preview</button>
    </form>
    <div style="font-size:12px;color:#64748b;margin-top:8px">If a row has no leave_date, the default date above is used.</div>
  </div>

  @php($pv = session('leave_preview'))
  @if($pv)
    @php($found = collect($pv)->where('found', true)->count())
    <div class="bg-surface border border-border rounded-xl p-4">
      <div style="font-weight:700;color:#0f172a;margin-bottom:10px">
        Preview — {{ count($pv) }} rows · {{ $found }} matched · {{ count($pv) - $found }} not found
      </div>

      <div style="max-height:420px;overflow:auto;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:12px">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
          <thead style="position:sticky;top:0;background:#f8f9fb">
            <tr>
              <th style="text-align:left;padding:8px 12px;border-bottom:1px solid #e5e7eb">Company ID</th>
              <th style="text-align:left;padding:8px 12px;border-bottom:1px solid #e5e7eb">Name</th>
              <th style="text-align:left;padding:8px 12px;border-bottom:1px solid #e5e7eb">Leave Date</th>
              <th style="text-align:left;padding:8px 12px;border-bottom:1px solid #e5e7eb">Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($pv as $r)
              <tr>
                <td style="padding:7px 12px;border-bottom:1px solid #f1f2f4;font-family:monospace;font-weight:600">{{ $r['company_id'] }}</td>
                <td style="padding:7px 12px;border-bottom:1px solid #f1f2f4">{{ $r['name'] ?: '—' }}</td>
                <td style="padding:7px 12px;border-bottom:1px solid #f1f2f4;font-family:monospace">{{ $r['leave_date'] }}</td>
                <td style="padding:7px 12px;border-bottom:1px solid #f1f2f4;font-weight:600;color:{{ !$r['found'] ? '#b91c1c' : ($r['already'] ? '#64748b' : '#047857') }}">
                  {{ !$r['found'] ? 'Not found' : ($r['already'] ? 'Already inactive' : 'Will be marked left') }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <form method="post" action="{{ route('billing.emp.bulkleave') }}" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        @csrf
        <input type="hidden" name="commit" value="1">
        <input type="hidden" name="default_date" value="{{ session('leave_default') }}">
        <input type="file" name="csv_file" accept=".csv" required style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 10px;font-size:14px">
        <button type="submit" onclick="return confirm('Mark these employees as left?')" style="padding:9px 18px;background:#ef4444;color:#fff;border:0;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">Confirm &amp; Apply</button>
        <span style="font-size:12px;color:#64748b">Re-select the same file to confirm.</span>
      </form>
    </div>
  @endif
</div>
@endsection
