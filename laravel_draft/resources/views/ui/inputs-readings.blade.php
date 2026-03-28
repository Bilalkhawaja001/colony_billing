@extends('layouts.app')
@section('page_title','Inputs Readings')
@section('page_subtitle','Reading-source integration references for latest meter values and write operations.')
@section('content')
<div class="card">
  <h3 class="section-title">Linked APIs</h3>
  <div class="grid">
    <div class="col-6 card soft"><code>/meter-reading/latest/*</code></div>
    <div class="col-6 card soft"><code>/meter-reading/upsert</code></div>
  </div>
  <div style="margin-top:12px" class="toolbar"><a class="btn" href="/ui/meter-master">Open Meter Master</a></div>
</div>
@endsection