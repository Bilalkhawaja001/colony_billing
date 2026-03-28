@extends('layouts.app')
@section('page_title','Employee Helper')
@section('page_subtitle','Support utilities for registry import, commit and promote-to-master operations.')
@section('content')
<div class="grid">
  <div class="col-12 card">
    <h3 class="section-title">Helper Actions</h3>
    <div class="grid">
      <div class="col-4 card soft"><div class="muted">Step 1</div><div style="font-weight:700">Import Preview</div><code>POST /registry/employees/import-preview</code></div>
      <div class="col-4 card soft"><div class="muted">Step 2</div><div style="font-weight:700">Import Commit</div><code>POST /registry/employees/import-commit</code></div>
      <div class="col-4 card soft"><div class="muted">Step 3</div><div style="font-weight:700">Promote</div><code>POST /registry/employees/promote-to-master</code></div>
    </div>
  </div>
</div>
@endsection