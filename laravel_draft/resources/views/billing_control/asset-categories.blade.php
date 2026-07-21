@extends('billing_control.layout')
@section('content')
@include('billing_control.components.tw-head')
<style>
  .sidebar, .billing-center-nav { display:none !important; }
  .body-row { display:block !important; }
  .main, .main-inner { max-width:100% !important; width:100% !important; padding-left:28px !important; padding-right:28px !important; }
</style>

<div style="font-family:Inter,sans-serif">

  <div style="margin-bottom:20px">
    <div class="text-xs font-semibold text-blue uppercase tracking-wider mb-1">Master Data</div>
    <h1 class="text-3xl font-bold text-ink">Asset Categories</h1>
    <div class="text-sm text-muted mt-1">Define default assets per room/house type. These are auto-assigned when a residence is allotted.</div>
  </div>

  @if(session('status'))<div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:14px;font-weight:600">{{ session('status') }}</div>@endif
  @if($errors->any())<div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif


  <div class="bg-surface border border-border rounded-xl p-4 mb-5">
    <details>
      <summary style="font-weight:700;color:#0f172a;cursor:pointer;user-select:none">Manage Assets Master ({{ count($assets) }} assets)</summary>
      <div style="margin-top:12px">
        <form method="post" action="{{ route('billing.assets.master.store') }}" style="display:flex;gap:8px;align-items:center;margin-bottom:12px;flex-wrap:wrap">
          @csrf
          <input name="asset_name" required placeholder="New asset name" style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 10px;font-size:13px;width:220px">
          <button type="submit" style="padding:7px 16px;background:#0f172a;color:#fff;border:0;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">+ Add to Master</button>
        </form>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          @foreach($assets as $a)
            <span style="display:inline-flex;align-items:center;gap:6px;background:#f2f4f6;border:1px solid #e2e8f0;border-radius:20px;padding:4px 6px 4px 12px;font-size:13px">
              {{ $a->asset_name }}
              <form method="post" action="{{ route('billing.assets.master.delete', $a->id) }}" style="display:inline" onsubmit="return confirm('Remove {{ $a->asset_name }} from master?')">
                @csrf @method('DELETE')
                <button type="submit" style="background:#e2e8f0;border:0;border-radius:50%;width:18px;height:18px;line-height:1;cursor:pointer;color:#64748b;font-size:12px">×</button>
              </form>
            </span>
          @endforeach
        </div>
      </div>
    </details>
  </div>
  <div class="bg-surface border border-border rounded-xl p-4 mb-5">
    <div style="font-weight:700;color:#0f172a;margin-bottom:10px">New Category</div>
    <form method="post" action="{{ route('billing.assets.category.store') }}" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
      @csrf
      <div>
        <label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">Name</label>
        <input name="name" required placeholder="e.g. Officer Room" style="border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;font-size:14px;width:200px">
      </div>
      <div>
        <label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">Type</label>
        <select name="residence_type" required style="border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;font-size:14px">
          <option value="ROOM">ROOM</option>
          <option value="HOUSE">HOUSE</option>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:4px">Description</label>
        <input name="description" placeholder="optional" style="border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;font-size:14px;width:240px">
      </div>
      <button type="submit" style="padding:9px 18px;background:#2563eb;color:#fff;border:0;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">Create</button>
    </form>
  </div>

  @forelse($categories as $cat)
    <div class="bg-surface border border-border rounded-xl p-4 mb-4">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:12px">
        <div>
          <div style="font-weight:700;font-size:17px;color:#0f172a">
            {{ $cat->name }}
            <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:4px;margin-left:6px;{{ $cat->residence_type === 'HOUSE' ? 'background:#d1fae5;color:#047857' : 'background:#f2f4f6;color:#45464d' }}">{{ $cat->residence_type }}</span>
          </div>
          @if($cat->description)<div style="font-size:13px;color:#64748b">{{ $cat->description }}</div>@endif
        </div>
        <form method="post" action="{{ route('billing.assets.category.delete', $cat->id) }}" onsubmit="return confirm('Delete this category and its assets?')">
          @csrf @method('DELETE')
          <button type="submit" style="font-size:12px;color:#ef4444;background:none;border:0;font-weight:600;cursor:pointer">Delete</button>
        </form>
      </div>

      <table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:10px">
        <thead>
          <tr style="background:#f8f9fb">
            <th style="text-align:left;padding:7px 12px;border-bottom:1px solid #e5e7eb">Asset</th>
            <th style="text-align:right;padding:7px 12px;border-bottom:1px solid #e5e7eb;width:90px">Qty</th>
            <th style="text-align:right;padding:7px 12px;border-bottom:1px solid #e5e7eb;width:80px"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($items[$cat->id] ?? [] as $it)
            <tr>
              <td style="padding:6px 12px;border-bottom:1px solid #f1f2f4">{{ $it->asset_name }}</td>
              <td style="padding:6px 12px;border-bottom:1px solid #f1f2f4;text-align:right;font-family:monospace">{{ $it->quantity }}</td>
              <td style="padding:6px 12px;border-bottom:1px solid #f1f2f4;text-align:right">
                <form method="post" action="{{ route('billing.assets.item.delete', $it->id) }}" style="display:inline">
                  @csrf @method('DELETE')
                  <button type="submit" style="font-size:12px;color:#ef4444;background:none;border:0;cursor:pointer">Remove</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="3" style="padding:10px 12px;color:#94a3b8;text-align:center">No assets yet.</td></tr>
          @endforelse
        </tbody>
      </table>

      <form method="post" action="{{ route('billing.assets.item.store') }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        @csrf
        <input type="hidden" name="category_id" value="{{ $cat->id }}">
        <select name="asset_name" required style="border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;font-size:13px;width:200px">
          <option value="">— select asset —</option>
          @foreach($assets as $a)<option value="{{ $a->asset_name }}">{{ $a->asset_name }}</option>@endforeach
        </select>
        <input name="quantity" type="number" min="1" value="1" required style="border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;font-size:13px;width:80px">
        <button type="submit" style="padding:6px 14px;background:#0f172a;color:#fff;border:0;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">+ Add Asset</button>
      </form>
    </div>
  @empty
    <div class="bg-surface border border-border rounded-xl p-8" style="text-align:center;color:#94a3b8">No categories yet. Create one above.</div>
  @endforelse

</div>
@endsection
