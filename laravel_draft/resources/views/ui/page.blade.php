@extends('layouts.app')
@section('content')
<div class="card">
    <h3>{{ $title }}</h3>
    <p><strong>Route:</strong> {{ $path }}</p>
    <p>Parity draft page is active.</p>
</div>
@endsection
