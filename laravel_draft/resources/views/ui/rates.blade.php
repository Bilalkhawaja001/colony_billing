<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.material-symbols-local')

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Monthly Rates | Billing Management</title>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>body{font-family:Inter,sans-serif;background:#f5f3f4}</style>
</head>
<body class="text-[#1b1b1d]">
@include('partials.global-navbar')

<main class="max-w-4xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-bold mb-1">Monthly Rate Configuration</h1>
  <p class="text-sm text-gray-500 mb-6">Electric rate per unit (PKR) used by the billing engine.</p>

  <div id="msg" class="hidden mb-4 px-4 py-2 rounded-lg text-sm font-semibold"></div>

  <section class="bg-white border border-gray-200 rounded-xl p-5 mb-6">
    <div class="flex flex-wrap gap-4 items-end">
      <div>
        <label class="block text-sm font-semibold mb-1">Month Cycle</label>
        <input id="mc" type="text" placeholder="MM-YYYY" maxlength="7"
               value="{{ $monthCycle ?: now()->format('m-Y') }}"
               class="border border-gray-300 rounded-lg px-3 py-2 w-36 font-mono">
      </div>
      <div>
        <label class="block text-sm font-semibold mb-1">Electric Rate (PKR/unit)</label>
        <input id="rate" type="number" step="0.0001" min="0"
               class="border border-gray-300 rounded-lg px-3 py-2 w-40">
      </div>
      <button id="loadBtn" class="bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 text-sm font-semibold">Load</button>
      <button id="saveBtn" class="bg-[#1e293b] text-white rounded-lg px-4 py-2 text-sm font-semibold">Save Rate</button>
    </div>
    <p id="meta" class="text-xs text-gray-500 mt-3"></p>
  </section>

  <section class="bg-white border border-gray-200 rounded-xl p-5">
    <h2 class="font-bold mb-3">Rate History</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-left text-gray-500 border-b">
          <tr><th class="py-2">Month Cycle</th><th>Electric Rate</th><th>Last Updated</th></tr>
        </thead>
        <tbody id="hist"><tr><td colspan="3" class="py-3 text-gray-400">Loading…</td></tr></tbody>
      </table>
    </div>
  </section>
</main>

<script>
const BASE = @json(url('/'));
const CSRF = @json(csrf_token());
const $ = id => document.getElementById(id);

function flash(text, ok) {
  const m = $('msg');
  m.textContent = text;
  m.className = 'mb-4 px-4 py-2 rounded-lg text-sm font-semibold ' +
    (ok ? 'bg-green-50 text-green-800 border border-green-200'
        : 'bg-red-50 text-red-800 border border-red-200');
}

async function loadRate() {
  const mc = $('mc').value.trim();
  if (!/^\d{2}-\d{4}$/.test(mc)) { flash('Month cycle must be MM-YYYY.', false); return; }
  try {
    const r = await fetch(BASE + '/monthly-rates/config?month_cycle=' + encodeURIComponent(mc), {headers:{'Accept':'application/json'}});
    const j = await r.json();
    if (j.status === 'ok' && j.row) {
      $('rate').value = j.row.elec_rate;
      $('meta').textContent = 'Last updated: ' + (j.row.updated_at || '—');
      flash('Loaded rate for ' + mc + '.', true);
    } else {
      $('rate').value = '';
      $('meta').textContent = '';
      flash('No rate configured for ' + mc + ' yet. Enter one and save.', false);
    }
  } catch (e) { flash('Could not load: ' + e, false); }
}

async function saveRate() {
  const mc = $('mc').value.trim();
  const rate = $('rate').value;
  if (!/^\d{2}-\d{4}$/.test(mc)) { flash('Month cycle must be MM-YYYY.', false); return; }
  if (rate === '' || Number(rate) < 0) { flash('Enter a valid rate.', false); return; }
  if (!confirm('Save electric rate ' + rate + ' for ' + mc + '?')) return;
  try {
    const r = await fetch(BASE + '/monthly-rates/config/upsert', {
      method: 'POST',
      headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},
      body: JSON.stringify({month_cycle: mc, elec_rate: Number(rate)})
    });
    const j = await r.json();
    if (r.ok) { flash('Rate saved for ' + mc + '.', true); loadHistory(); loadRate(); }
    else { flash('Save failed: ' + (j.error || JSON.stringify(j)), false); }
  } catch (e) { flash('Save failed: ' + e, false); }
}

async function loadHistory() {
  try {
    const r = await fetch(BASE + '/monthly-rates/history?limit=24', {headers:{'Accept':'application/json'}});
    const j = await r.json();
    const rows = j.rows || [];
    $('hist').innerHTML = rows.length
      ? rows.map(x => `<tr class="border-b last:border-0"><td class="py-2 font-mono">${x.month_cycle}</td><td>${Number(x.elec_rate).toFixed(2)}</td><td class="text-gray-500">${x.updated_at || '—'}</td></tr>`).join('')
      : '<tr><td colspan="3" class="py-3 text-gray-400">No rates configured yet.</td></tr>';
  } catch (e) {
    $('hist').innerHTML = '<tr><td colspan="3" class="py-3 text-red-600">Could not load history.</td></tr>';
  }
}

$('loadBtn').onclick = loadRate;
$('saveBtn').onclick = saveRate;
loadRate();
loadHistory();
</script>
</body>
</html>
