@extends('layouts.app')
@section('page_title','Inputs Mapping')
@section('page_subtitle','Room and occupancy mapping context for downstream compute and allocation correctness.')
@section('content')
<div class="card">
  <h3 class="section-title">Linked APIs</h3>
  <div class="grid">
    <div class="col-4 card soft"><code>/rooms</code></div>
    <div class="col-4 card soft"><code>/occupancy/context</code></div>
    <div class="col-4 card soft"><code>/api/rooms/cascade</code></div>
  </div>
  <div style="margin-top:12px" class="toolbar">
    <a class="btn" href="/ui/rooms">Open Rooms</a>
    <a class="btn" href="/ui/occupancy">Open Occupancy</a>
  </div>
</div>
@endsection