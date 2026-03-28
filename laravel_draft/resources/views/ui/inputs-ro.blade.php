@extends('layouts.app')
@section('page_title','Inputs RO')
@section('page_subtitle','RO and water allocation dependent API context for monthly water parity handling.')
@section('content')
<div class="card">
  <h3 class="section-title">Linked APIs</h3>
  <div class="grid">
    <div class="col-6 card soft"><code>/api/water/allocation-preview</code></div>
    <div class="col-6 card soft"><code>/api/water/zone-adjustments</code></div>
  </div>
  <div style="margin-top:12px" class="toolbar"><a class="btn" href="/ui/water-meters">Open Water Meters</a></div>
</div>
@endsection