<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MBS Laravel Draft</title>
    <style>body{font-family:Arial,sans-serif;max-width:900px;margin:24px auto;padding:0 16px}input,button{padding:8px;margin:4px 0} .card{border:1px solid #ddd;padding:16px;border-radius:8px;margin:12px 0}</style>
</head>
<body>
    <h2>MBS Laravel Draft Shell</h2>
    @if(session('status'))<div class="card">{{ session('status') }}</div>@endif
    @if(session('draft_notice'))<div class="card">{{ session('draft_notice') }}</div>@endif
    @yield('content')
</body>
</html>
