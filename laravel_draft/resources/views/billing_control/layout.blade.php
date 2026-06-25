<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle ?? 'Colony Billing | Billing Center' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php($colonyCssPath = public_path('css/colony-billing.css'))
    @php($bcCssPath = public_path('css/billing-control.css'))
    @if(is_file($colonyCssPath))
        <link rel="stylesheet" href="{{ asset('css/colony-billing.css') }}?v={{ filemtime($colonyCssPath) }}">
    @endif
    @if(is_file($bcCssPath))
        <link rel="stylesheet" href="{{ asset('css/billing-control.css') }}?v={{ filemtime($bcCssPath) }}">
    @endif
</head>
<body>
<div class="app">
    @include('billing_control.components.topbar')

    <div class="body-row">
        @include('billing_control.components.stepper')

        <main class="main">
            <div class="main-inner">
                @if(session('status'))
                    <div class="card" style="margin-bottom:16px">{{ session('status') }}</div>
                @endif

                @if(isset($errors) && $errors->any())
                    <div class="card" style="margin-bottom:16px;color:#DC2626">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</div>

@php($bcJsPath = public_path('billing-control/control-room.js'))
@if(is_file($bcJsPath))
    <script>{!! file_get_contents($bcJsPath) !!}</script>
@endif
</body>
</html>
