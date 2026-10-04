<!doctype html>
<html lang="en">
<head>
@include('partials.material-symbols-local')

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
@include('partials.global-navbar')

<div class="app">

    <div class="body-row">
        @include('billing_control.components.stepper')

        <main class="main">
            <div class="main-inner">
                @if(session('success'))
                    <div id="billingFlashMessage"
                         style="
                            position:fixed;
                            top:82px;
                            left:50%;
                            transform:translateX(-50%);
                            z-index:99999;
                            width:min(760px,calc(100% - 40px));
                            background:#ecfdf5;
                            border:1px solid #86efac;
                            color:#166534;
                            padding:14px 18px;
                            border-radius:10px;
                            box-shadow:0 10px 30px rgba(0,0,0,.15);
                            font-weight:700;
                            font-size:14px;
                         ">
                        ✓ {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div id="billingFlashMessage"
                         style="
                            position:fixed;
                            top:82px;
                            left:50%;
                            transform:translateX(-50%);
                            z-index:99999;
                            width:min(760px,calc(100% - 40px));
                            background:#fef2f2;
                            border:1px solid #fca5a5;
                            color:#b91c1c;
                            padding:14px 18px;
                            border-radius:10px;
                            box-shadow:0 10px 30px rgba(0,0,0,.15);
                            font-weight:700;
                            font-size:14px;
                         ">
                        ✕ {{ session('error') }}
                    </div>
                @endif

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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const flash = document.getElementById('billingFlashMessage');
    if (flash) {
        setTimeout(function () {
            flash.style.transition = 'opacity .35s ease';
            flash.style.opacity = '0';
            setTimeout(function () {
                flash.remove();
            }, 400);
        }, 12000);
    }
});
</script>
</body>
</html>
