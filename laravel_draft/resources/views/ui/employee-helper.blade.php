@extends('layouts.app')
@section('content')
<div class="card">
  <h3>Employee Helper Workspace</h3>
  <p>Helper actions for registry import/commit/promote.</p>
  <ul>
    <li>POST /registry/employees/import-preview</li>
    <li>POST /registry/employees/import-commit</li>
    <li>POST /registry/employees/promote-to-master</li>
  </ul>
</div>
@endsection