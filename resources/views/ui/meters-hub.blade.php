@extends('layouts.app')
@section('page_title','Meters & Readings')
@section('page_subtitle','Hub: choose Meter Registry, Readings, or Water Tools workspace.')
@section('content')
<div class="grid">
  <div class="col-4 card">
    <h3 class="section-title">Meter Registry</h3>
    <p class="muted">Manage meter16unit mapping, registry entries, and CSV tools.</p>
    <a class="btn btn-primary" href="/meters-readings/registry">Open Meter Registry</a>
  </div>

  <div class="col-4 card">
    <h3 class="section-title">Readings</h3>
    <p class="muted">Operator console for latest lookup and quick reading upsert.</p>
    <a class="btn btn-primary" href="/meters-readings/readings">Open Readings Console</a>
  </div>

  <div class="col-4 card">
    <h3 class="section-title">Water Tools</h3>
    <p class="muted">Pre-billing water allocation controls and zone adjustments (not final billing).</p>
    <a class="btn btn-primary" href="/meters-readings/water-tools">Open Water Tools</a>
  </div>
</div>
@endsection
