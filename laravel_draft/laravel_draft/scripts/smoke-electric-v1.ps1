param(
  [string]$Root = "C:\Users\Bilal\clawd\_tmp_colony_billing_patch"
)

Set-Location $Root
php artisan migrate --force
php artisan route:list | findstr electric-v1

$body = '{"cycle_start":"2026-03-01","cycle_end":"2026-03-31","flat_rate":2.5}'
$run = Invoke-RestMethod -Method Post -Uri "http://127.0.0.1:8000/api/electric-v1/run" -ContentType "application/json" -Body $body
if ($run.status -ne 'ok') { throw "Run failed" }

$bundle = Invoke-RestMethod -Method Get -Uri "http://127.0.0.1:8000/api/electric-v1/outputs?cycle_start=2026-03-01&cycle_end=2026-03-31"
if ($bundle.status -ne 'ok') { throw "Bundle fetch failed" }
Write-Host "Electric V1 smoke passed"
