<!doctype html>
<html lang="en">
<head>
@include('partials.material-symbols-local')

<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Monthly Meter Readings | Colony Billing</title>

<style>
*{box-sizing:border-box}
body{
    margin:0;
    background:#f6f8fb;
    color:#172033;
    font:14px Inter,Arial,sans-serif
}

.mm-top{
    background:#fff;
    border-bottom:1px solid #e2e8f0
}

.mm-topin,
.mm-main{
    max-width:1450px;
    margin:auto;
    padding:16px 24px
}

.mm-topin{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px
}

.mm-brand{
    font-size:25px;
    font-weight:800
}

.mm-brand span{color:#075bd8}

.mm-nav{
    display:flex;
    gap:8px
}

.mm-nav a{
    text-decoration:none;
    color:#475569;
    padding:9px 13px;
    border-radius:8px;
    font-weight:700
}

.mm-nav a.active{
    color:#075bd8;
    background:#eef5ff
}

.mm-back{
    color:#075bd8;
    font-weight:700;
    text-decoration:none
}

.mm-main{
    padding-top:24px;
    padding-bottom:40px
}

.mm-card{
    background:#fff;
    border:1px solid #e1e7ef;
    border-radius:14px;
    box-shadow:0 4px 14px rgba(15,23,42,.04);
    overflow:hidden
}

.mm-head{
    padding:22px 24px;
    border-bottom:1px solid #e5eaf0;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px
}

.mm-title{
    margin:0;
    font-size:27px;
    font-weight:800
}

.mm-title span{color:#075bd8}

.mm-sub{
    margin-top:5px;
    color:#64748b;
    font-size:13px
}

.mm-controls{
    padding:18px 24px;
    display:flex;
    align-items:end;
    gap:12px;
    flex-wrap:wrap;
    background:#fbfcfe;
    border-bottom:1px solid #e5eaf0
}

.mm-field label{
    display:block;
    font-size:12px;
    color:#526075;
    font-weight:700;
    margin-bottom:6px
}

.mm-input{
    height:40px;
    border:1px solid #cfd8e5;
    border-radius:8px;
    background:#fff;
    padding:8px 11px;
    outline:none;
    min-width:190px
}

.mm-input:focus{
    border-color:#075bd8;
    box-shadow:0 0 0 3px rgba(7,91,216,.10)
}

.mm-search{min-width:250px}

.mm-btn{
    border:0;
    height:40px;
    border-radius:8px;
    padding:0 17px;
    font-weight:700;
    cursor:pointer
}

.mm-btn-primary{
    background:#075bd8;
    color:#fff
}

.mm-btn-light{
    background:#edf2f7;
    color:#334155;
    border:1px solid #d7e0ea
}

.mm-btn:disabled{
    opacity:.55;
    cursor:not-allowed
}

.mm-summary{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:14px;
    padding:18px 24px
}

.mm-kpi{
    border:1px solid #e1e7ef;
    background:#f8fafc;
    border-radius:10px;
    padding:15px
}

.mm-kpi label{
    display:block;
    font-size:11px;
    font-weight:700;
    color:#64748b;
    text-transform:uppercase;
    margin-bottom:7px
}

.mm-kpi strong{
    font-size:22px;
    color:#0f172a
}

.mm-status{
    display:none !important;
}

.mm-status.ok{
    background:#ecfdf5;
    color:#047857
}

.mm-status.err{
    background:#fef2f2;
    color:#b91c1c
}

.mm-tablewrap{
    margin:0 24px 24px;
    border:1px solid #dfe6ee;
    border-radius:10px;
    overflow:auto;
    max-height:650px
}

.mm-table{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
    min-width:760px
}

.mm-table thead th{
    position:sticky;
    top:0;
    z-index:2;
    background:#eef2f6;
    color:#53657c;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.02em;
    text-align:left;
    padding:13px 16px;
    border-bottom:1px solid #dce4ed
}

.mm-table th.num,
.mm-table td.num{
    text-align:right
}

.mm-table td{
    padding:11px 16px;
    border-bottom:1px solid #edf1f5;
    vertical-align:middle
}

.mm-table tbody tr:hover{
    background:#f8fbff
}

.mm-meter{
    font-weight:800;
    color:#172033
}

.mm-unit{
    font-size:11px;
    color:#8492a6;
    margin-top:3px
}

.readonly-value{
    display:inline-block;
    min-width:95px;
    padding:9px 11px;
    background:#f1f5f9;
    border:1px solid #e2e8f0;
    border-radius:7px;
    text-align:right;
    font-family:"Courier New",monospace
}

.current-reading{
    width:150px;
    height:38px;
    border:1px solid #cbd5e1;
    border-radius:7px;
    padding:7px 10px;
    text-align:right;
    font-family:"Courier New",monospace;
    font-size:14px;
    outline:none
}

.current-reading:focus{
    border-color:#075bd8;
    box-shadow:0 0 0 3px rgba(7,91,216,.10)
}

.consume{
    font-family:"Courier New",monospace;
    font-weight:800
}

.consume.negative{
    color:#dc2626
}

.consume.positive{
    color:#047857
}

.changed{
    background:#fffdf3 !important
}

.mm-empty{
    padding:35px !important;
    text-align:center !important;
    color:#64748b
}

@media(max-width:850px){
    .mm-head{
        align-items:flex-start;
        flex-direction:column
    }

    .mm-summary{
        grid-template-columns:repeat(2,1fr)
    }
}

@media(max-width:600px){
    .mm-nav{display:none}

    .mm-main{
        padding:14px
    }

    .mm-summary{
        grid-template-columns:1fr
    }

    .mm-controls,
    .mm-head{
        padding-left:16px;
        padding-right:16px
    }

    .mm-tablewrap{
        margin-left:16px;
        margin-right:16px
    }
}
</style>
</head>

<body>

@include('partials.global-navbar')

<header class="mm-top">
    <div class="mm-topin">
        <div class="mm-brand">
            <span>⚡</span> Colony Billing
        </div>

        <nav class="mm-nav">
            <a href="{{ url('/dashboard-v2') }}">Dashboard</a>
            <a class="active" href="{{ url('/meters-readings') }}">Operations</a>
            <a href="{{ url('/reports') }}">Reports</a>
        </nav>

        <a class="mm-back" href="{{ url('/meters-readings') }}">
            ← Meters Hub
        </a>
    </div>
</header>

<main class="mm-main">

    <section class="mm-card">

        <div class="mm-head">
            <div>
                <h1 class="mm-title">
                    <span>▥</span> Monthly Meter Readings
                </h1>

                <div class="mm-sub">
                    Select billing month, enter current units and save all readings.
                </div>
            </div>

            <button
                type="button"
                class="mm-btn mm-btn-primary"
                id="saveAllBtn"
            >
                Save All Readings
            </button>
        </div>

        <div id="statusBox" class="mm-status">
            Loading meter readings...
        </div>

        <div class="mm-summary">

            <div class="mm-kpi">
                <label>Total Meters</label>
                <strong id="kpiMeters">0</strong>
            </div>

            <div class="mm-kpi">
                <label>Readings Entered</label>
                <strong id="kpiEntered">0</strong>
            </div>

            <div class="mm-kpi">
                <label>Total Consume Unit</label>
                <strong id="kpiConsumption">0</strong>
            </div>

            <div class="mm-kpi">
                <label>Cycle End</label>
                <strong id="kpiCycle" style="font-size:15px">-</strong>
            </div>

        </div>

        <div class="mm-controls">

            <div class="mm-field">
                <label>Billing Month</label>
                <input
                    type="month"
                    id="billingMonth"
                    class="mm-input"
                    value="{{ now()->format('Y-m') }}"
                >
            </div>

            <div class="mm-field">
                <label>Search Meter</label>
                <input
                    type="text"
                    id="meterSearch"
                    class="mm-input mm-search"
                    placeholder="Meter ID or Unit ID..."
                >
            </div>

            <button
                type="button"
                class="mm-btn mm-btn-light"
                id="loadBtn"
            >
                Load Readings
            </button>

        </div>


        <div class="mm-tablewrap">

            <table class="mm-table">

                <thead>
                    <tr>
                        <th>Meter ID</th>
                        <th class="num">Previous Unit</th>
                        <th class="num">Current Unit</th>
                        <th class="num">Total Consume Unit</th>
                        <th class="num">Free Allowance</th>
                        <th class="num">Billable Units</th>
                    </tr>
                </thead>

                <tbody id="meterRows">
                    <tr>
                        <td colspan="6" class="mm-empty">
                            Loading...
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

    </section>

</main>

<script>
const csrf = @json(csrf_token());

const billingMonth = document.getElementById('billingMonth');
const loadBtn = document.getElementById('loadBtn');
const saveAllBtn = document.getElementById('saveAllBtn');
const meterSearch = document.getElementById('meterSearch');
const meterRows = document.getElementById('meterRows');
const statusBox = document.getElementById('statusBox');

const kpiMeters = document.getElementById('kpiMeters');
const kpiEntered = document.getElementById('kpiEntered');
const kpiConsumption = document.getElementById('kpiConsumption');
const kpiCycle = document.getElementById('kpiCycle');

const pageBase =
    window.location.origin +
    window.location.pathname.replace(/\/+$/, '');

const dataUrl = pageBase + '/monthly-data';
const saveUrl = pageBase + '/monthly-save';

let allRows = [];

const esc = value =>
    String(value ?? '').replace(/[&<>"']/g, char => ({
        '&':'&amp;',
        '<':'&lt;',
        '>':'&gt;',
        '"':'&quot;',
        "'":'&#39;'
    }[char]));

function numberText(value)
{
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    const num = Number(value);

    if (!Number.isFinite(num)) {
        return '—';
    }

    return num.toLocaleString(undefined, {
        maximumFractionDigits:4
    });
}

function setStatus(message, type='')
{
    statusBox.className = 'mm-status' + (type ? ' ' + type : '');
    statusBox.textContent = message;
}

function calculateBillable(rowData, consumption)
{
    if (
        consumption === null ||
        !Number.isFinite(consumption) ||
        consumption < 0
    ) {
        return null;
    }

    const method = String(
        rowData.billing_method || 'ATTENDANCE_PRORATED'
    ).toUpperCase();

    const ctx = rowData.billing_context || {};

    if (method === 'OCCUPIED_ROOM_EQUAL_SPLIT') {

        const allowances = Array.isArray(ctx.occupied_room_allowances)
            ? ctx.occupied_room_allowances
            : [];

        const count = Number(ctx.occupied_room_count || 0);

        if (count <= 0) {
            return null;
        }

        const perRoom = consumption / count;
        let total = 0;

        allowances.forEach(value => {
            const allowance = Number(value);

            if (Number.isFinite(allowance) && allowance > 0) {
                total += Math.max(perRoom - allowance, 0);
            }
        });

        return total;
    }

    if (method === 'ATTENDANCE_PRORATED') {

        const attendance = Number(ctx.unit_attendance || 0);

        const free = attendance > 0
            ? Number(rowData.effective_free_allowance || 0)
            : Number(rowData.free_allowance || 0);

        return Math.max(consumption - free, 0);
    }

    return null;
}

function calculateConsumption(input)
{
    const tr = input.closest('tr');
    const previousRaw = input.dataset.previous;
    const currentRaw = input.value.trim();

    const consumptionCell = tr.querySelector('.consume');
    const billableCell = tr.querySelector('.billable');

    tr.classList.toggle(
        'changed',
        currentRaw !== String(input.dataset.original ?? '')
    );

    if (previousRaw === '' || currentRaw === '') {
        consumptionCell.textContent = '—';
        consumptionCell.dataset.value = '';
        consumptionCell.className = 'consume';

        billableCell.textContent = '—';
        billableCell.dataset.value = '';

        updateKpis();
        return;
    }

    const previous = Number(previousRaw);
    const current = Number(currentRaw);

    if (!Number.isFinite(previous) || !Number.isFinite(current)) {
        consumptionCell.textContent = '—';
        consumptionCell.dataset.value = '';

        billableCell.textContent = '—';
        billableCell.dataset.value = '';

        updateKpis();
        return;
    }

    const consume = current - previous;

    consumptionCell.textContent = numberText(consume);
    consumptionCell.dataset.value = consume;

    consumptionCell.className =
        'consume ' +
        (consume < 0 ? 'negative' : 'positive');

    const rowData = allRows.find(row =>
        String(row.meter_id) === String(input.dataset.meterId)
    );

    const billable = rowData
        ? calculateBillable(rowData, consume)
        : null;

    if (billable === null) {
        billableCell.textContent = '—';
        billableCell.dataset.value = '';
    } else {
        billableCell.textContent = numberText(billable);
        billableCell.dataset.value = billable;
    }

    updateKpis();
}

function updateKpis()
{
    const visibleRows =
        [...meterRows.querySelectorAll('tr[data-meter]')]
            .filter(row => row.style.display !== 'none');

    kpiMeters.textContent = visibleRows.length;

    let entered = 0;
    let total = 0;

    visibleRows.forEach(row => {
        const input = row.querySelector('.current-reading');

        if (input && input.value.trim() !== '') {
            entered++;
        }

        const consume = row.querySelector('.consume');

        if (
            consume &&
            consume.dataset.value !== undefined &&
            consume.dataset.value !== ''
        ) {
            const n = Number(consume.dataset.value);

            if (Number.isFinite(n)) {
                total += n;
            }
        }
    });

    kpiEntered.textContent = entered;
    kpiConsumption.textContent = numberText(total);
}

function renderRows(rows)
{
    if (!rows.length) {
        meterRows.innerHTML =
            '<tr><td colspan="6" class="mm-empty">No active meters found.</td></tr>';

        updateKpis();
        return;
    }

    meterRows.innerHTML = rows.map(row => {

        const previous =
            row.previous_reading === null
                ? ''
                : String(row.previous_reading);

        const current =
            row.current_reading === null
                ? ''
                : String(row.current_reading);

        let consumption = '—';
        let consumptionValue = '';
        let consumeClass = '';

        if (previous !== '' && current !== '') {
            const diff = Number(current) - Number(previous);

            consumption = numberText(diff);
            consumptionValue = diff;
            consumeClass =
                diff < 0
                    ? 'negative'
                    : 'positive';
        }

        return `
            <tr
                data-meter="${esc(row.meter_id)}"
                data-unit="${esc(row.unit_id)}"
            >
                <td>
                    <div class="mm-meter">${esc(row.meter_id)}</div>
                    <div class="mm-unit">${esc(row.unit_id || '')}</div>
                </td>

                <td class="num">
                    <span class="readonly-value">
                        ${numberText(row.previous_reading)}
                    </span>
                </td>

                <td class="num">
                    <input
                        type="number"
                        min="0"
                        step="0.001"
                        class="current-reading"
                        value="${esc(current)}"
                        data-original="${esc(current)}"
                        data-previous="${esc(previous)}"
                        data-meter-id="${esc(row.meter_id)}"
                    >
                </td>

                <td class="num">
                    <span
                        class="consume ${consumeClass}"
                        data-value="${esc(consumptionValue)}"
                    >
                        ${consumption}
                    </span>
                </td>

                <td class="num">
                    <span class="readonly-value">
                        ${numberText(row.free_allowance)}
                    </span>
                </td>

                <td class="num">
                    <span
                        class="readonly-value billable"
                        data-value="${row.billable_units === null ? '' : esc(row.billable_units)}"
                    >
                        ${numberText(row.billable_units)}
                    </span>
                </td>
            </tr>
        `;
    }).join('');

    document
        .querySelectorAll('.current-reading')
        .forEach(input => {

            input.addEventListener('input', () => {
                calculateConsumption(input);
            });

            input.addEventListener('keydown', event => {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();

                const inputs =
                    [...document.querySelectorAll('.current-reading')]
                        .filter(i => i.closest('tr').style.display !== 'none');

                const currentIndex = inputs.indexOf(input);

                if (
                    currentIndex >= 0 &&
                    inputs[currentIndex + 1]
                ) {
                    inputs[currentIndex + 1].focus();
                    inputs[currentIndex + 1].select();
                }
            });
        });

    updateKpis();
}

async function loadReadings()
{
    const month = billingMonth.value;

    if (!month) {
        setStatus('Please select billing month.', 'err');
        return;
    }

    loadBtn.disabled = true;
    saveAllBtn.disabled = true;

    meterRows.innerHTML =
        '<tr><td colspan="6" class="mm-empty">Loading readings...</td></tr>';

    setStatus('Loading ' + month + ' readings...');

    try {
        const url =
            dataUrl +
            '?' +
            new URLSearchParams({
                month: month
            }).toString();

        const response = await fetch(url, {
            method:'GET',
            credentials:'same-origin',
            headers:{
                'Accept':'application/json',
                'X-Requested-With':'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.error ||
                data.message ||
                'Unable to load readings.'
            );
        }

        allRows = data.rows || [];

        renderRows(allRows);

        kpiCycle.textContent =
            data.cycle_end_date || '-';

        setStatus(
            data.month_label +
            ' loaded · Billing cycle ' +
            data.cycle_start_date +
            ' → ' +
            data.cycle_end_date +
            ' · Method: ' +
            data.method_code,
            'ok'
        );

    } catch (error) {

        allRows = [];

        meterRows.innerHTML =
            '<tr><td colspan="6" class="mm-empty">' +
            esc(error.message) +
            '</td></tr>';

        kpiMeters.textContent = '0';
        kpiEntered.textContent = '0';
        kpiConsumption.textContent = '0';
        kpiCycle.textContent = '-';

        setStatus(error.message, 'err');

    } finally {
        loadBtn.disabled = false;
        saveAllBtn.disabled = false;
    }
}

async function saveAll()
{
    const inputs =
        [...document.querySelectorAll('.current-reading')];

    const changed = inputs.filter(input => {
        return (
            input.value.trim() !== '' &&
            input.value.trim() !==
                String(input.dataset.original ?? '')
        );
    });

    if (!changed.length) {
        setStatus('No changed readings to save.');
        return;
    }

    const rows = changed.map(input => ({
        meter_id: input.dataset.meterId,
        current_reading: input.value.trim()
    }));

    if (!confirm(
        'Save ' +
        rows.length +
        ' changed meter reading(s) for ' +
        billingMonth.value +
        '?'
    )) {
        return;
    }

    saveAllBtn.disabled = true;
    loadBtn.disabled = true;

    setStatus(
        'Saving ' +
        rows.length +
        ' reading(s)...'
    );

    try {
        const response = await fetch(saveUrl, {
            method:'POST',
            credentials:'same-origin',
            headers:{
                'Accept':'application/json',
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':csrf,
                'X-Requested-With':'XMLHttpRequest'
            },
            body:JSON.stringify({
                month: billingMonth.value,
                rows: rows
            })
        });

        const data = await response.json();

        if (!response.ok) {
            let message =
                data.error ||
                data.message ||
                'Readings could not be saved.';

            if (
                Array.isArray(data.errors) &&
                data.errors.length
            ) {
                message +=
                    ' ' +
                    data.errors
                        .slice(0, 5)
                        .map(e =>
                            (e.meter_id || 'Row') +
                            ': ' +
                            e.error
                        )
                        .join(' | ');
            }

            throw new Error(message);
        }

        setStatus(
            'Saved successfully · ' +
            data.inserted +
            ' new · ' +
            data.updated +
            ' updated.',
            'ok'
        );

        await loadReadings();

    } catch (error) {
        setStatus(error.message, 'err');
    } finally {
        saveAllBtn.disabled = false;
        loadBtn.disabled = false;
    }
}

function applySearch()
{
    const q =
        meterSearch.value
            .trim()
            .toLowerCase();

    document
        .querySelectorAll('tr[data-meter]')
        .forEach(row => {

            const text =
                (
                    row.dataset.meter +
                    ' ' +
                    row.dataset.unit
                ).toLowerCase();

            row.style.display =
                !q || text.includes(q)
                    ? ''
                    : 'none';
        });

    updateKpis();
}

loadBtn.addEventListener('click', loadReadings);

billingMonth.addEventListener('change', loadReadings);

saveAllBtn.addEventListener('click', saveAll);

meterSearch.addEventListener('input', applySearch);

loadReadings();
</script>

</body>
</html>
