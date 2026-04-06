@extends('layouts.app')
@section('page_title','Employees Workspace')
@section('page_subtitle','Employee API hub for search, detail and update flows.')
@section('content')
<div class="card">
  <h3 class="section-title">Linked APIs</h3>
  <div class="grid">
    <div class="col-4 card soft"><code>/employees</code></div>
    <div class="col-4 card soft"><code>/employees/search</code></div>
    <div class="col-4 card soft"><code>/employees/{company_id}</code></div>
  </div>
  <div class="toolbar" style="margin-top:12px"><a class="btn btn-primary" href="/people-residency">Open Employee Master</a></div>
</div>
@endsection
