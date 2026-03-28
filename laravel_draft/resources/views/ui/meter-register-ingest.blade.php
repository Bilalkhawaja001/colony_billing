@extends('layouts.app')
@section('page_title','Meter Register Ingest')
@section('page_subtitle','Ingest control hub. Use Imports workspace for preview/validation and token-based error loop.')
@section('content')
<div class="card">
  <h3 class="section-title">Ingest Routing</h3>
  <p class="muted">Operator ingest workflow is centralized in Imports for parity and audit consistency.</p>
  <div class="toolbar">
    <a class="btn btn-primary" href="/ui/imports">Open Imports Workspace</a>
    <a class="btn" href="/ui/meter-master">Open Meter Master</a>
  </div>
</div>
@endsection