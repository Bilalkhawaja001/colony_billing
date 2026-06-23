@extends('billing_control.layout')

@section('content')
<section class="bc-panel">
    <h2>Generate Bill</h2>
    <p class="bc-muted">Generate is locked until real readiness service is wired.</p>

    <form method="post" action="{{ route('billing.control.generate.store') }}">
        @csrf
        <button class="bc-btn" type="submit" disabled>Generate Bill</button>
    </form>

    @foreach(($readiness['blockers'] ?? []) as $issue)
        @include('billing_control.components.issue-card', ['issue' => $issue])
    @endforeach
</section>
@endsection
