<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle ?? 'Colony Billing Control Room' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php($billingControlAssetBase = rtrim(config('app.url'), '/'))
    <link rel="stylesheet" href="{{ $billingControlAssetBase }}/billing-control/control-room.css?v=phase1b">
</head>
<body>
<div class="bc-shell">
    @include('billing_control.components.topbar')
    @include('billing_control.components.stepper')

    @if(session('status'))
        <div class="bc-alert">{{ session('status') }}</div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="bc-alert bc-alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <main class="bc-main">
        @yield('content')
    </main>
</div>
<script src="{{ $billingControlAssetBase }}/billing-control/control-room.js?v=phase1b"></script>
</body>
</html>
