@extends('layouts.app')
@section('page_title','Transport')
@section('page_subtitle','Van rent, fuel, adjustments, and father/company billing split in one workspace.')
@section('content')
<div class="grid">
    <div class="col-12" id="transportBannerHost"></div>

    <div class="col-12 card">
        <h3 class="section-title">Transport Summary Loader</h3>
        <div id="transportMonthLockState" class="muted" style="margin-bottom:10px;">Month lock state: Unknown</div>
        <form id="transportForm" class="form-grid">
            <div class="field col-4">
                <label class="label">Month Cycle</label>
                <input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}">
            </div>
            <div class="col-8" style="display:flex;align-items:flex-end;gap:8px;flex-wrap:wrap">
                <button class="btn btn-primary" type="button" id="transportLoad">Load Summary</button>
                <a class="btn" id="transportCsvExport" href="#">Export CSV</a>
            </div>
        </form>
    </div>

    <div class="col-6 card">
        <h3 class="section-title">Vehicle Master</h3>
        <form id="vehicleForm" class="form-grid">
            <input type="hidden" name="id">
            <div class="field col-4"><label class="label">Code</label><input name="vehicle_code" required placeholder="VAN-01"></div>
            <div class="field col-5"><label class="label">Name</label><input name="vehicle_name" required placeholder="School Van"></div>
            <div class="field col-3"><label class="label">Status</label><select name="is_active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
            <div class="field col-12"><label class="label">Notes</label><input name="notes" placeholder="Optional notes"></div>
            <div class="col-12 toolbar"><button class="btn btn-primary" type="submit">Save Vehicle</button><button class="btn" type="button" id="vehicleCancelEdit">Cancel Edit</button></div>
        </form>
    </div>

    <div class="col-6 card">
        <h3 class="section-title">Vehicle Master List</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="vehicleEntriesRows">
                    <tr><td colspan="5" class="muted">No vehicles found.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-6 card">
        <h3 class="section-title">Rent Entry</h3>
        <form id="rentForm" class="form-grid">
            <input type="hidden" name="id">
            <div class="field col-4"><label class="label">Month</label><input name="month_cycle" required placeholder="MM-YYYY" value="{{ $monthCycle }}"></div>
            <div class="field col-4"><label class="label">Vehicle</label><select name="vehicle_id" id="rentVehicleId" required></select></div>
            <div class="field col-4"><label class="label">Rent Amount</label><input name="rent_amount" type="number" step="0.01" min="0" required></div>
            <div class="field col-12"><label class="label">Notes</label><input name="notes" placeholder="Optional notes"></div>
            <div class="col-12 toolbar"><button class="btn btn-primary" type="submit">Save Rent</button><button class="btn" type="button" id="rentCancelEdit">Cancel Edit</button></div>
        </form>
    </div>

    <div class="col-6 card">
        <h3 class="section-title">Fuel Entry</h3>
        <form id="fuelForm" class="form-grid">
            <input type="hidden" name="id">
            <div class="field col-4"><label class="label">Month</label><input name="month_cycle" required placeholder="MM-YYYY" value="{{ $monthCycle }}"></div>
            <div class="field col-4"><label class="label">Date</label><input name="entry_date" type="date" required></div>
            <div class="field col-4"><label class="label">Vehicle</label><select name="vehicle_id" id="fuelVehicleId" required></select></div>
            <div class="field col-3"><label class="label">Liters</label><input name="fuel_liters" type="number" step="0.001" min="0.001" required></div>
            <div class="field col-3"><label class="label">Fuel Price</label><input name="fuel_price" type="number" step="0.01" min="0" required></div>
            <div class="field col-3"><label class="label">Slip Ref</label><input name="slip_ref" placeholder="Receipt no"></div>
            <div class="field col-3"><label class="label">Auto Fuel Cost</label><input id="fuelCostPreview" value="0.00" disabled></div>
            <div class="field col-12"><label class="label">Notes</label><input name="notes" placeholder="Optional notes"></div>
            <div class="col-12 toolbar"><button class="btn btn-primary" type="submit">Save Fuel</button><button class="btn" type="button" id="fuelCancelEdit">Cancel Edit</button></div>
        </form>
    </div>

    <div class="col-6 card">
        <h3 class="section-title">Adjustment Entry</h3>
        <form id="adjustmentForm" class="form-grid">
            <input type="hidden" name="id">
            <div class="field col-4"><label class="label">Month</label><input name="month_cycle" required placeholder="MM-YYYY" value="{{ $monthCycle }}"></div>
            <div class="field col-4"><label class="label">Vehicle (optional)</label><select name="vehicle_id" id="adjustmentVehicleId"></select></div>
            <div class="field col-4"><label class="label">Direction</label><select name="direction" required><option value="plus">Plus</option><option value="minus">Minus</option></select></div>
            <div class="field col-4"><label class="label">Amount</label><input name="amount" type="number" step="0.01" min="0.01" required></div>
            <div class="field col-8"><label class="label">Reason</label><input name="reason" required placeholder="Reason"></div>
            <div class="field col-12"><label class="label">Notes</label><input name="notes" placeholder="Optional notes"></div>
            <div class="col-12 toolbar"><button class="btn btn-primary" type="submit">Save Adjustment</button><button class="btn" type="button" id="adjustmentCancelEdit">Cancel Edit</button></div>
        </form>
    </div>

    <div class="col-3 card soft"><div class="muted">Van Rent</div><div class="kpi" id="kpiRent">0.00</div></div>
    <div class="col-3 card soft"><div class="muted">Fuel Cost</div><div class="kpi" id="kpiFuelCost">0.00</div></div>
    <div class="col-3 card soft"><div class="muted">Total Cost</div><div class="kpi" id="kpiTotal">0.00</div></div>
    <div class="col-3 card soft"><div class="muted">Net Father Bill</div><div class="kpi" id="kpiFather">0.00</div></div>

    <div class="col-12 card">
        <h3 class="section-title">Frozen Formula</h3>
        <div class="stack muted">
            <div>Total Cost = Van Rent + (Fuel Liters × Fuel Price)</div>
            <div>Company Share = 50%</div>
            <div>Father Share = 50%</div>
            <div>Net Father Bill = Father Share ± Adjustments</div>
        </div>
    </div>

    <div class="col-12 card">
        <h3 class="section-title">Father Bill Preview</h3>
        <div class="table-wrap" style="margin-bottom:12px;">
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Total Rent</th>
                        <th>Total Fuel Cost</th>
                        <th>Total Cost</th>
                        <th>Company Share</th>
                        <th>Father Share</th>
                        <th>Plus Adj</th>
                        <th>Minus Adj</th>
                        <th>Net Father Bill</th>
                    </tr>
                </thead>
                <tbody id="fatherBillSummaryRows">
                    <tr><td colspan="9" class="muted">Load a month to preview father bill.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Rent</th>
                        <th>Fuel Cost</th>
                        <th>Total Cost</th>
                        <th>Father Share</th>
                        <th>Adj +</th>
                        <th>Adj -</th>
                        <th>Net Father Bill</th>
                    </tr>
                </thead>
                <tbody id="fatherBillVehicleRows">
                    <tr><td colspan="8" class="muted">Load a month to preview father bill breakdown.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12 card">
        <h3 class="section-title">Vehicle Summary</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Rent</th>
                        <th>Fuel Liters</th>
                        <th>Fuel Cost</th>
                        <th>Adjust +</th>
                        <th>Adjust -</th>
                        <th>Total Cost</th>
                        <th>Company Share</th>
                        <th>Father Share</th>
                        <th>Net Father Bill</th>
                    </tr>
                </thead>
                <tbody id="transportRows">
                    <tr><td colspan="10" class="muted">Load a month to view transport summary.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-6 card">
        <h3 class="section-title">Rent Entries</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Month</th>
                        <th>Rent</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="rentEntriesRows">
                    <tr><td colspan="5" class="muted">No rent entries loaded.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-6 card">
        <h3 class="section-title">Fuel Entries</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vehicle</th>
                        <th>Liters</th>
                        <th>Price</th>
                        <th>Cost</th>
                        <th>Slip</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="fuelEntriesRows">
                    <tr><td colspan="7" class="muted">No fuel entries loaded.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12 card">
        <h3 class="section-title">Adjustments</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Vehicle</th>
                        <th>Direction</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="adjustmentEntriesRows">
                    <tr><td colspan="7" class="muted">No adjustments loaded.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12 card">
        <h3 class="section-title">Child Transport Month Usage</h3>
        <form id="childUsageForm" class="form-grid">
            <div class="field col-2"><label class="label">Month</label><input name="month_cycle" placeholder="MM-YYYY" value="{{ $monthCycle }}"></div>
            <div class="field col-3"><label class="label">Child Profile</label><select name="child_profile_id" id="childProfileId"></select></div>
            <div class="field col-2"><label class="label">Status</label><input name="usage_status" placeholder="active"></div>
            <div class="field col-2"><label class="label">Usage From</label><input type="date" name="usage_from_date"></div>
            <div class="field col-2"><label class="label">Usage To</label><input type="date" name="usage_to_date"></div>
            <div class="field col-3"><label class="label">Vehicle</label><select name="vehicle_id" id="childUsageVehicleId"></select></div>
            <div class="field col-3"><label class="label">Route Label</label><input name="route_label"></div>
            <div class="field col-2"><label class="label">Charge Amount</label><input type="number" step="0.01" name="charge_amount"></div>
            <div class="field col-4"><label class="label">Remarks</label><input name="remarks"></div>
            <div class="col-12 toolbar"><button class="btn btn-primary" type="submit">Save Child Usage</button></div>
        </form>
        <div class="table-wrap" style="margin-top:12px;">
            <table>
                <thead>
                    <tr>
                        <th>Child</th>
                        <th>CompanyID</th>
                        <th>Father</th>
                        <th>Room</th>
                        <th>Usage From</th>
                        <th>Usage To</th>
                        <th>Vehicle</th>
                        <th>Route</th>
                        <th>Charge</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="childUsageRows">
                    <tr><td colspan="10" class="muted">Load a month to view child transport usage.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12 card">
        <h3 class="section-title">Raw Response</h3>
        <pre id="transportResult">Ready.</pre>
    </div>
</div>

<script>
const transportForm = document.getElementById('transportForm');
const transportResult = document.getElementById('transportResult');
const transportRows = document.getElementById('transportRows');
const fatherBillSummaryRows = document.getElementById('fatherBillSummaryRows');
const fatherBillVehicleRows = document.getElementById('fatherBillVehicleRows');
const transportCsvExport = document.getElementById('transportCsvExport');
const vehicleEntriesRows = document.getElementById('vehicleEntriesRows');
const rentEntriesRows = document.getElementById('rentEntriesRows');
const fuelEntriesRows = document.getElementById('fuelEntriesRows');
const adjustmentEntriesRows = document.getElementById('adjustmentEntriesRows');
const childUsageRows = document.getElementById('childUsageRows');
const transportBannerHost = document.getElementById('transportBannerHost');
const transportMonthLockState = document.getElementById('transportMonthLockState');
const vehicleForm = document.getElementById('vehicleForm');
const rentForm = document.getElementById('rentForm');
const fuelForm = document.getElementById('fuelForm');
const adjustmentForm = document.getElementById('adjustmentForm');
const childUsageForm = document.getElementById('childUsageForm');
const childProfileId = document.getElementById('childProfileId');
const childUsageVehicleId = document.getElementById('childUsageVehicleId');
const rentVehicleId = document.getElementById('rentVehicleId');
const fuelVehicleId = document.getElementById('fuelVehicleId');
const adjustmentVehicleId = document.getElementById('adjustmentVehicleId');
const fuelCostPreview = document.getElementById('fuelCostPreview');
const vehicleCancelEdit = document.getElementById('vehicleCancelEdit');
const rentCancelEdit = document.getElementById('rentCancelEdit');
const fuelCancelEdit = document.getElementById('fuelCancelEdit');
const adjustmentCancelEdit = document.getElementById('adjustmentCancelEdit');

let currentVehicles = [];
let currentRentEntries = [];
let currentFuelEntries = [];
let currentAdjustmentEntries = [];
let currentMonthLock = { state: null, is_locked: false };

const money = (n) => Number(n || 0).toFixed(2);
const getPayload = () => Object.fromEntries(new FormData(transportForm));

function showBanner(kind, message) {
    const cls = kind === 'error' ? 'alert' : 'banner';
    transportBannerHost.innerHTML = `<div class="${cls}">${message}</div>`;
}

function clearBanner() {
    transportBannerHost.innerHTML = '';
}

async function getJson(url) {
    const r = await fetch(url);
    const j = await r.json().catch(() => ({}));
    return { status: r.status, body: j };
}

async function postJson(url, payload) {
    const r = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload),
    });
    const j = await r.json().catch(() => ({}));
    return { status: r.status, body: j };
}

function setText(id, value) {
    document.getElementById(id).textContent = value;
}

function renderRows(rows) {
    if (!rows || !rows.length) {
        transportRows.innerHTML = '<tr><td colspan="10" class="muted">No transport rows found for selected month.</td></tr>';
        return;
    }

    transportRows.innerHTML = rows.map((row) => `
        <tr>
            <td>${row.vehicle_name} <span class="muted">(${row.vehicle_code})</span></td>
            <td>${money(row.van_rent)}</td>
            <td>${Number(row.fuel_liters || 0).toFixed(3)}</td>
            <td>${money(row.fuel_cost)}</td>
            <td>${money(row.adjustment_plus)}</td>
            <td>${money(row.adjustment_minus)}</td>
            <td>${money(row.total_cost)}</td>
            <td>${money(row.company_share)}</td>
            <td>${money(row.father_share)}</td>
            <td>${money(row.net_father_bill)}</td>
        </tr>
    `).join('');
}

function renderFatherBill(bill) {
    if (!bill || !bill.vehicle_rows) {
        fatherBillSummaryRows.innerHTML = '<tr><td colspan="9" class="muted">No father bill data found for selected month.</td></tr>';
        fatherBillVehicleRows.innerHTML = '<tr><td colspan="8" class="muted">No father bill vehicle breakdown found for selected month.</td></tr>';
        return;
    }

    fatherBillSummaryRows.innerHTML = `
        <tr>
            <td>${bill.month_cycle || ''}</td>
            <td>${money(bill.total_rent)}</td>
            <td>${money(bill.total_fuel_cost)}</td>
            <td>${money(bill.total_cost)}</td>
            <td>${money(bill.company_share)}</td>
            <td>${money(bill.father_share)}</td>
            <td>${money(bill.plus_adjustments)}</td>
            <td>${money(bill.minus_adjustments)}</td>
            <td>${money(bill.net_father_bill)}</td>
        </tr>
    `;

    const rows = bill.vehicle_rows || [];
    if (!rows.length) {
        fatherBillVehicleRows.innerHTML = '<tr><td colspan="8" class="muted">No father bill vehicle breakdown found for selected month.</td></tr>';
        return;
    }

    fatherBillVehicleRows.innerHTML = rows.map((row) => `
        <tr>
            <td>${row.vehicle_name} <span class="muted">(${row.vehicle_code})</span></td>
            <td>${money(row.van_rent)}</td>
            <td>${money(row.fuel_cost)}</td>
            <td>${money(row.total_cost)}</td>
            <td>${money(row.father_share)}</td>
            <td>${money(row.adjustment_plus)}</td>
            <td>${money(row.adjustment_minus)}</td>
            <td>${money(row.net_father_bill)}</td>
        </tr>
    `).join('');
}

function renderVehicles(rows) {
    currentVehicles = rows || [];
    if (!currentVehicles.length) {
        vehicleEntriesRows.innerHTML = '<tr><td colspan="5" class="muted">No vehicles found.</td></tr>';
        return;
    }

    vehicleEntriesRows.innerHTML = currentVehicles.map((row) => `
        <tr>
            <td>${row.vehicle_code}</td>
            <td>${row.vehicle_name}</td>
            <td>${Number(row.is_active) ? 'Active' : 'Inactive'}</td>
            <td>${row.notes || ''}</td>
            <td><button type="button" class="btn" data-edit-vehicle="${row.id}">Edit</button></td>
        </tr>
    `).join('');
}

function renderRentEntries(rows) {
    currentRentEntries = rows || [];
    if (!currentRentEntries.length) {
        rentEntriesRows.innerHTML = '<tr><td colspan="5" class="muted">No rent entries found for selected month.</td></tr>';
        return;
    }

    rentEntriesRows.innerHTML = currentRentEntries.map((row) => `
        <tr>
            <td>${row.vehicle_name} <span class="muted">(${row.vehicle_code})</span></td>
            <td>${row.month_cycle}</td>
            <td>${money(row.rent_amount)}</td>
            <td>${row.notes || ''}</td>
            <td><button type="button" class="btn" data-edit-rent="${row.id}">Edit</button></td>
        </tr>
    `).join('');
}

function renderFuelEntries(rows) {
    currentFuelEntries = rows || [];
    if (!currentFuelEntries.length) {
        fuelEntriesRows.innerHTML = '<tr><td colspan="7" class="muted">No fuel entries found for selected month.</td></tr>';
        return;
    }

    fuelEntriesRows.innerHTML = currentFuelEntries.map((row) => `
        <tr>
            <td>${row.entry_date}</td>
            <td>${row.vehicle_name} <span class="muted">(${row.vehicle_code})</span></td>
            <td>${Number(row.fuel_liters || 0).toFixed(3)}</td>
            <td>${money(row.fuel_price)}</td>
            <td>${money(row.fuel_cost)}</td>
            <td>${row.slip_ref || ''}</td>
            <td><button type="button" class="btn" data-edit-fuel="${row.id}">Edit</button></td>
        </tr>
    `).join('');
}

function renderAdjustments(rows) {
    currentAdjustmentEntries = rows || [];
    if (!currentAdjustmentEntries.length) {
        adjustmentEntriesRows.innerHTML = '<tr><td colspan="7" class="muted">No adjustments found for selected month.</td></tr>';
        return;
    }

    adjustmentEntriesRows.innerHTML = currentAdjustmentEntries.map((row) => `
        <tr>
            <td>${row.month_cycle}</td>
            <td>${row.vehicle_id ? `${row.vehicle_name} (${row.vehicle_code})` : 'Global'}</td>
            <td>${row.direction}</td>
            <td>${money(row.amount)}</td>
            <td>${row.reason}</td>
            <td>${row.notes || ''}</td>
            <td><button type="button" class="btn" data-edit-adjustment="${row.id}">Edit</button></td>
        </tr>
    `).join('');
}

function applyMonthLockUi() {
    const isLocked = !!currentMonthLock.is_locked;
    const stateText = currentMonthLock.state || 'UNAVAILABLE';
    transportMonthLockState.innerHTML = isLocked
        ? `<span class="badge warn">LOCKED</span> Selected month is locked. Rent, fuel, and adjustment saves are blocked.`
        : `<span class="badge success">${stateText}</span> Selected month is open for transport entry saves.`;

    rentForm.querySelector('button[type="submit"]').disabled = isLocked;
    fuelForm.querySelector('button[type="submit"]').disabled = isLocked;
    adjustmentForm.querySelector('button[type="submit"]').disabled = isLocked;
}

function renderVehicleOptions(vehicles) {
    const options = (vehicles || []).map((vehicle) => `<option value="${vehicle.id}">${vehicle.vehicle_name} (${vehicle.vehicle_code})</option>`).join('');
    rentVehicleId.innerHTML = options || '<option value="">No vehicles</option>';
    fuelVehicleId.innerHTML = options || '<option value="">No vehicles</option>';
    adjustmentVehicleId.innerHTML = '<option value="">Global month adjustment</option>' + options;
}

function resetVehicleForm() {
    vehicleForm.reset();
    vehicleForm.querySelector('[name="id"]').value = '';
    vehicleForm.querySelector('[name="is_active"]').value = '1';
}

function resetRentForm(clearMonth = false) {
    rentForm.reset();
    rentForm.querySelector('[name="id"]').value = '';
    if (!clearMonth) {
        rentForm.querySelector('[name="month_cycle"]').value = transportForm.querySelector('[name="month_cycle"]').value || '{{ $monthCycle }}';
    }
}

function resetFuelForm(clearMonth = false) {
    fuelForm.reset();
    fuelForm.querySelector('[name="id"]').value = '';
    if (!clearMonth) {
        fuelForm.querySelector('[name="month_cycle"]').value = transportForm.querySelector('[name="month_cycle"]').value || '{{ $monthCycle }}';
    }
    fuelCostPreview.value = '0.00';
}

function resetAdjustmentForm(clearMonth = false) {
    adjustmentForm.reset();
    adjustmentForm.querySelector('[name="id"]').value = '';
    if (!clearMonth) {
        adjustmentForm.querySelector('[name="month_cycle"]').value = transportForm.querySelector('[name="month_cycle"]').value || '{{ $monthCycle }}';
    }
}

function clearEditState(clearMonth = false) {
    resetVehicleForm();
    resetRentForm(clearMonth);
    resetFuelForm(clearMonth);
    resetAdjustmentForm(clearMonth);
}

function renderChildUsageProfiles(profiles, vehicles) {
    const profileOptions = (profiles || []).map((row) => `<option value="${row.id}">${row.company_id} - ${row.child_name}</option>`).join('');
    childProfileId.innerHTML = profileOptions || '<option value="">No child profiles</option>';
    const vehicleOptions = '<option value="">No vehicle</option>' + (vehicles || []).map((row) => `<option value="${row.id}">${row.vehicle_name} (${row.vehicle_code})</option>`).join('');
    childUsageVehicleId.innerHTML = vehicleOptions;
}

function renderChildUsageRows(rows) {
    if (!rows || !rows.length) {
        childUsageRows.innerHTML = '<tr><td colspan="10" class="muted">No child transport usage found for selected month.</td></tr>';
        return;
    }
    childUsageRows.innerHTML = rows.map((row) => `
        <tr>
            <td>${row.child_name}</td>
            <td>${row.company_id}</td>
            <td>${row.father_name || ''}</td>
            <td>${row.room_no || ''}</td>
            <td>${row.usage_from_date || ''}</td>
            <td>${row.usage_to_date || ''}</td>
            <td>${row.vehicle_name ? `${row.vehicle_name} (${row.vehicle_code})` : ''}</td>
            <td>${row.route_label || row.default_route_label || ''}</td>
            <td>${money(row.charge_amount || 0)}</td>
            <td>${row.usage_status || ''}</td>
        </tr>
    `).join('');
}

async function loadTransport() {
    clearBanner();
    clearEditState();
    const payload = getPayload();
    const month = encodeURIComponent(payload.month_cycle || '');
    const result = await getJson(`/api/transport/summary?month_cycle=${month}`);
    transportResult.textContent = JSON.stringify(result, null, 2);

    const body = result.body || {};
    const totals = body.totals || {};
    currentMonthLock = body.month_lock || { state: null, is_locked: false };

    setText('kpiRent', money(totals.van_rent));
    setText('kpiFuelCost', money(totals.fuel_cost));
    setText('kpiTotal', money(totals.total_cost));
    setText('kpiFather', money(totals.net_father_bill));

    renderRows(body.rows || []);
    renderFatherBill(body.father_bill || null);
    renderVehicles(body.vehicles || []);
    renderVehicleOptions(body.vehicles || []);
    transportCsvExport.href = `/api/transport/export/csv?month_cycle=${encodeURIComponent(body.month_cycle || payload.month_cycle || '')}`;
    applyMonthLockUi();
    renderRentEntries(body.rent_entries || []);
    renderFuelEntries(body.fuel_entries || []);
    renderAdjustments(body.adjustments || []);

    const childUsage = await getJson(`/api/transport/child-month-usage?month_cycle=${month}`);
    renderChildUsageProfiles(childUsage.body?.child_profiles || [], body.vehicles || []);
    renderChildUsageRows(childUsage.body?.rows || []);
}

function formToObject(form) {
    const raw = Object.fromEntries(new FormData(form));
    Object.keys(raw).forEach((key) => {
        if (raw[key] === '') {
            delete raw[key];
        }
    });
    return raw;
}

async function handlePost(form, url, resetAfter = true) {
    clearBanner();
    const payload = formToObject(form);

    if (currentMonthLock.is_locked && url !== '/api/transport/vehicles/upsert') {
        showBanner('error', `Transport month ${payload.month_cycle || transportForm.querySelector('[name="month_cycle"]').value} is locked. Save is blocked for this entry.`);
        return;
    }

    const result = await postJson(url, payload);
    const body = result.body || {};

    if (result.status >= 400 || body.status === 'error') {
        const validationErrors = body.errors ? Object.values(body.errors).flat().join(' | ') : '';
        showBanner('error', body.message || body.error || validationErrors || 'Request failed.');
        return;
    }

    showBanner('success', body.message || 'Saved successfully.');
    if (resetAfter) {
        form.reset();
    }

    const currentMonth = payload.month_cycle || document.querySelector('#transportForm input[name="month_cycle"]').value || '{{ $monthCycle }}';
    document.querySelector('#transportForm input[name="month_cycle"]').value = currentMonth;
    document.querySelector('#rentForm input[name="month_cycle"]').value = currentMonth;
    document.querySelector('#fuelForm input[name="month_cycle"]').value = currentMonth;
    document.querySelector('#adjustmentForm input[name="month_cycle"]').value = currentMonth;
    fuelCostPreview.value = '0.00';
    await loadTransport();
}

function updateFuelCostPreview() {
    const liters = Number(fuelForm.querySelector('[name="fuel_liters"]').value || 0);
    const price = Number(fuelForm.querySelector('[name="fuel_price"]').value || 0);
    fuelCostPreview.value = money(liters * price);
}

vehicleEntriesRows.addEventListener('click', (e) => {
    const id = e.target.getAttribute('data-edit-vehicle');
    if (!id) return;
    const row = currentVehicles.find((item) => String(item.id) === String(id));
    if (!row) return;
    vehicleForm.querySelector('[name="id"]').value = row.id;
    vehicleForm.querySelector('[name="vehicle_code"]').value = row.vehicle_code;
    vehicleForm.querySelector('[name="vehicle_name"]').value = row.vehicle_name;
    vehicleForm.querySelector('[name="is_active"]').value = Number(row.is_active) ? '1' : '0';
    vehicleForm.querySelector('[name="notes"]').value = row.notes || '';
});

rentEntriesRows.addEventListener('click', (e) => {
    const id = e.target.getAttribute('data-edit-rent');
    if (!id) return;
    const row = currentRentEntries.find((item) => String(item.id) === String(id));
    if (!row) return;
    rentForm.querySelector('[name="id"]').value = row.id;
    rentForm.querySelector('[name="month_cycle"]').value = row.month_cycle;
    rentForm.querySelector('[name="vehicle_id"]').value = row.vehicle_id;
    rentForm.querySelector('[name="rent_amount"]').value = row.rent_amount;
    rentForm.querySelector('[name="notes"]').value = row.notes || '';
});

fuelEntriesRows.addEventListener('click', (e) => {
    const id = e.target.getAttribute('data-edit-fuel');
    if (!id) return;
    const row = currentFuelEntries.find((item) => String(item.id) === String(id));
    if (!row) return;
    fuelForm.querySelector('[name="id"]').value = row.id;
    fuelForm.querySelector('[name="month_cycle"]').value = row.month_cycle;
    fuelForm.querySelector('[name="entry_date"]').value = row.entry_date;
    fuelForm.querySelector('[name="vehicle_id"]').value = row.vehicle_id;
    fuelForm.querySelector('[name="fuel_liters"]').value = row.fuel_liters;
    fuelForm.querySelector('[name="fuel_price"]').value = row.fuel_price;
    fuelForm.querySelector('[name="slip_ref"]').value = row.slip_ref || '';
    fuelForm.querySelector('[name="notes"]').value = row.notes || '';
    updateFuelCostPreview();
});

adjustmentEntriesRows.addEventListener('click', (e) => {
    const id = e.target.getAttribute('data-edit-adjustment');
    if (!id) return;
    const row = currentAdjustmentEntries.find((item) => String(item.id) === String(id));
    if (!row) return;
    adjustmentForm.querySelector('[name="id"]').value = row.id;
    adjustmentForm.querySelector('[name="month_cycle"]').value = row.month_cycle;
    adjustmentForm.querySelector('[name="vehicle_id"]').value = row.vehicle_id || '';
    adjustmentForm.querySelector('[name="direction"]').value = row.direction;
    adjustmentForm.querySelector('[name="amount"]').value = row.amount;
    adjustmentForm.querySelector('[name="reason"]').value = row.reason;
    adjustmentForm.querySelector('[name="notes"]').value = row.notes || '';
});

document.getElementById('transportLoad').addEventListener('click', loadTransport);
vehicleForm.addEventListener('submit', async (e) => { e.preventDefault(); await handlePost(vehicleForm, '/api/transport/vehicles/upsert'); });
rentForm.addEventListener('submit', async (e) => { e.preventDefault(); await handlePost(rentForm, '/api/transport/rent-entries/upsert', false); });
fuelForm.addEventListener('submit', async (e) => { e.preventDefault(); await handlePost(fuelForm, '/api/transport/fuel-entries/upsert'); });
adjustmentForm.addEventListener('submit', async (e) => { e.preventDefault(); await handlePost(adjustmentForm, '/api/transport/adjustments/upsert'); });
childUsageForm.addEventListener('submit', async (e) => { e.preventDefault(); await handlePost(childUsageForm, '/api/transport/child-month-usage/upsert'); });
vehicleCancelEdit.addEventListener('click', () => resetVehicleForm());
rentCancelEdit.addEventListener('click', () => resetRentForm());
fuelCancelEdit.addEventListener('click', () => resetFuelForm());
adjustmentCancelEdit.addEventListener('click', () => resetAdjustmentForm());
transportForm.querySelector('[name="month_cycle"]').addEventListener('change', () => {
    clearEditState();
    currentMonthLock = { state: null, is_locked: false };
    transportMonthLockState.textContent = 'Month lock state: Refresh summary to load current month state.';
});
fuelForm.querySelector('[name="fuel_liters"]').addEventListener('input', updateFuelCostPreview);
fuelForm.querySelector('[name="fuel_price"]').addEventListener('input', updateFuelCostPreview);
loadTransport();
</script>
@endsection
