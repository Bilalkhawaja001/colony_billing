@extends('layouts.app')
@section('page_title', $title)
@section('page_subtitle','Workspace module shell (parity route active).')
@section('content')
<div class="card">
    <h3 class="section-title">{{ $title }}</h3>
    <p class="muted" style="margin:0"><strong>Route:</strong> {{ $path }}</p>
</div>
@endsection