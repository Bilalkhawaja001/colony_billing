@extends('billing_control.layout')

@section('content')
<section class="bc-panel">
    <h2>Readiness Gate</h2>
    <p class="bc-muted">Server-side scaffold. Generate remains blocked.</p>

    @foreach(($readiness['blockers'] ?? []) as $issue)
        @include('billing_control.components.issue-card', ['issue' => $issue])
    @endforeach
</section>
@endsection
