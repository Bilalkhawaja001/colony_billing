@extends('layouts.app')
@section('page_title','Facilities Management - '.$title)
@section('page_subtitle','Visible Phase 1 page shell; transactional workflow is deferred.')
@section('content')
@include('facilities._tabs')
<div class="grid">
    <div class="col-12 card">
        <h3 class="section-title">{{ $title }}</h3>
        <div class="fm-note">{{ $message }}</div>
    </div>
</div>
@endsection
