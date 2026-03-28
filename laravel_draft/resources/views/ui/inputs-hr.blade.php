@extends('layouts.app')
@section('page_title','Inputs HR')
@section('page_subtitle','HR-linked employee source context feeding registry and monthly billing inputs.')
@section('content')
<div class="card">
  <h3 class="section-title">Linked APIs</h3>
  <div class="grid">
    <div class="col-6 card soft"><code>/employees</code></div>
    <div class="col-6 card soft"><code>/registry/employees/*</code></div>
  </div>
  <div style="margin-top:12px" class="toolbar">
    <a class="btn" href="/ui/employee-master">Open Employee Master</a>
    <a class="btn" href="/ui/employee-helper">Open Employee Helper</a>
  </div>
</div>
@endsection