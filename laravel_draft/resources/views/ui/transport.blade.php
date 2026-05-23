@extends('layouts.app')
@section('page_title','School Van Kids Management')
@section('page_subtitle','Manage school van enrolment, active students and service withdrawals from Family Details.')
@section('content')
<style>
.sv-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;margin:14px 0;}
.sv-toolbar-note{font-size:13px;color:var(--muted,#64748b);}
.sv-modal[hidden]{display:none;}
.sv-modal{
    position:fixed;
    inset:0;
    z-index:99999;
    background:rgba(15,23,42,.52);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:16px;
}
.sv-modal-panel{
    background:#fff;
    border-radius:16px;
    box-shadow:0 24px 70px rgba(15,23,42,.24);
    width:min(760px,calc(100vw - 32px));
    max-height:calc(100vh - 32px);
    overflow:auto;
    padding:16px;
}
#schoolVanExpenseModal .sv-modal-panel{
    width:min(920px,calc(100vw - 32px)) !important;
}
#schoolVanCostModal .sv-modal-panel{
    width:min(980px,calc(100vw - 32px)) !important;
}
.sv-modal-panel.small{width:min(460px,calc(100vw - 32px));}
.sv-modal-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:10px;
    position:sticky;
    top:-16px;
    background:#fff;
    padding:4px 0 8px;
    z-index:2;
}
.sv-modal-head h3{margin:0;font-size:17px;}
.sv-modal-close{
    border:0;
    background:#eef2f7;
    width:32px;
    height:32px;
    border-radius:10px;
    cursor:pointer;
    font-size:20px;
    line-height:1;
}
.sv-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:12px;}
.sv-action-btn{white-space:nowrap;}
.sv-top-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}
.sv-expense-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:10px;
}
.sv-mini-card{
    border:1px solid #e7edf5;
    border-radius:12px;
    padding:10px 12px;
    background:#fbfdff;
}
.sv-mini-card h4{margin:0 0 8px;font-size:14px;color:#0f172a;}
.sv-mini-card .field{margin-bottom:6px;}
.sv-mini-card .label{font-size:11px;margin-bottom:4px;}
.sv-mini-card input,
.sv-mini-card select{
    height:34px;
    min-height:34px;
    padding:6px 10px;
    font-size:13px;
}
.sv-mini-card .toolbar{margin-top:4px;}
.sv-mini-card .btn{min-height:34px;padding:7px 12px;font-size:13px;}
.sv-cost-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-bottom:12px;}
.sv-cost-kpi{border:1px solid #e7edf5;background:#f8fbff;border-radius:12px;padding:10px;}
.sv-cost-kpi .value{font-size:19px;font-weight:700;color:#0f172a;margin-top:4px;}
.sv-policy{
    padding:9px 11px;
    border-radius:10px;
    background:#f4f8ff;
    border:1px solid #dde8ff;
    font-size:12px;
    margin-bottom:10px;
}
.sv-kpi-grid{
    display:grid;
    grid-template-columns:repeat(5,minmax(0,1fr));
    gap:10px;
    margin-bottom:14px;
}
.sv-kpi-grid .card{margin:0;}
.sv-row-actions{display:flex;gap:6px;align-items:center;flex-wrap:wrap;}
.sv-action-danger{border-color:#f1c8c8;color:#991b1b;background:#fff7f7;}
.sv-history-note{font-size:12px;color:#64748b;line-height:1.35;}
.sv-manage-summary{
    display:grid;
    grid-template-columns:1fr auto;
    gap:10px;
    align-items:center;
    padding:10px 12px;
    background:#f8fbff;
    border:1px solid #e7edf5;
    border-radius:12px;
    margin-bottom:12px;
}
.sv-manage-panels{margin-top:12px;}
.sv-manage-info{
    padding:10px 12px;
    background:#f8fbff;
    border:1px solid #e7edf5;
    border-radius:12px;
    font-size:13px;
    line-height:1.5;
}
@media (max-width:1100px){
    .sv-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr));}
}
@media (max-width:900px){
    .sv-expense-grid,.sv-cost-kpis{grid-template-columns:1fr;}
    #schoolVanExpenseModal .sv-modal-panel,
    #schoolVanCostModal .sv-modal-panel{width:calc(100vw - 20px) !important;}
}
</style>
<div class="grid">
    <div class="col-12" id="transportBannerHost"></div>

    <div class="col-12 card school-van-workspace">
        <h3 class="section-title">School Van Kids Management</h3>
        <p class="muted" style="margin-bottom:12px;">
            Kids are fetched from permanent Family Details. Add selected school-going children to van service or mark active children as left.
        </p>

        <div class="sv-kpi-grid">
            <div class="card soft"><div class="muted">Total Enrolments</div><div class="kpi" id="svTotal">0</div></div>
            <div class="card soft"><div class="muted">Active Kids</div><div class="kpi" id="svActive">0</div></div>
            <div class="card soft"><div class="muted">Left Kids</div><div class="kpi" id="svLeft">0</div></div>
            <div class="card soft"><div class="muted">Cancelled Entries</div><div class="kpi" id="svCancelled">0</div></div>
            <div class="card soft"><div class="muted">Eligible to Add</div><div class="kpi" id="svEligible">0</div></div>
        </div>

        <div class="sv-toolbar">
            <div class="sv-toolbar-note">Manage active school van enrolments linked with permanent Family Details.</div>
            <div class="sv-top-actions">
                <button type="button" class="btn" id="schoolVanOpenExpense">Setup & Expenses</button>
                <button type="button" class="btn" id="schoolVanOpenCost">Cost Allocation</button>
                <button type="button" class="btn btn-primary" id="schoolVanOpenAdd">+ Add Kid</button>
            </div>
        </div>

        <div id="schoolVanExpenseModal" class="sv-modal" hidden>
            <div class="sv-modal-panel" style="width:min(1100px,100%);">
                <div class="sv-modal-head">
                    <h3>Setup & Expenses</h3>
                    <button type="button" class="sv-modal-close" id="schoolVanExpenseClose" aria-label="Close">&times;</button>
                </div>
                <div class="sv-policy">
                    Enter the school van, monthly rent, fuel entries and approved adjustments. These values feed the 50% Company / 50% Employees allocation.
                </div>

                <div class="sv-expense-grid">
                    <div class="sv-mini-card">
                        <h4>Billing Cycle Setup</h4>
                        <form id="svCycleSetupForm" class="form-grid">
                            <div class="field col-4">
                                <label class="label">Month Cycle</label>
                                <input name="month_cycle" value="{{ $monthCycle }}" placeholder="MM-YYYY" required>
                            </div>
                            <div class="field col-4">
                                <label class="label">Cycle Start Date</label>
                                <input name="cycle_start_date" type="date" required>
                            </div>
                            <div class="field col-4">
                                <label class="label">Cycle End Date</label>
                                <input name="cycle_end_date" type="date" required>
                            </div>
                            <div class="col-12 toolbar">
                                <button class="btn btn-primary" type="submit">Save Cycle</button>
                            </div>
                        </form>
                        <div id="svCycleSavedState" class="muted" style="margin-top:10px;">
                            Enter manual cycle dates before calculating charges.
                        </div>
                    </div>

                    <div class="sv-mini-card">
                        <h4>School Van Master</h4>
                        <form id="svVehicleSetupForm" class="form-grid">
                            <div class="field col-4"><label class="label">Code</label><input name="vehicle_code" required placeholder="VAN-01"></div>
                            <div class="field col-5"><label class="label">Name</label><input name="vehicle_name" required placeholder="School Van"></div>
                            <div class="field col-3"><label class="label">Status</label><select name="is_active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
                            <div class="field col-12"><label class="label">Notes</label><input name="notes" placeholder="Optional notes"></div>
                            <div class="col-12 toolbar"><button class="btn btn-primary" type="submit">Save Van</button></div>
                        </form>
                        <div id="svVehicleList" class="muted" style="margin-top:10px;">No school van configured.</div>
                    </div>

                    <div class="sv-mini-card">
                        <h4>Monthly Rent</h4>
                        <form id="svRentSetupForm" class="form-grid">
                            <div class="field col-4"><label class="label">Month</label><input name="month_cycle" value="{{ $monthCycle }}" required></div>
                            <div class="field col-4"><label class="label">Vehicle</label><select name="vehicle_id" id="svRentVehicleId" required></select></div>
                            <div class="field col-4"><label class="label">Rent Amount</label><input name="rent_amount" type="number" step="0.01" min="0" required></div>
                            <div class="field col-12"><label class="label">Notes</label><input name="notes" placeholder="Optional notes"></div>
                            <div class="col-12 toolbar"><button class="btn btn-primary" type="submit">Save Rent</button></div>
                        </form>
                        <div id="svRentList" class="muted" style="margin-top:10px;">No rent entry recorded.</div>
                    </div>

                    <div class="sv-mini-card">
                        <h4>Fuel Entry</h4>
                        <form id="svFuelSetupForm" class="form-grid">
                            <div class="field col-3"><label class="label">Month</label><input name="month_cycle" value="{{ $monthCycle }}" required></div>
                            <div class="field col-3"><label class="label">Date</label><input name="entry_date" type="date" required></div>
                            <div class="field col-3"><label class="label">Vehicle</label><select name="vehicle_id" id="svFuelVehicleId" required></select></div>
                            <div class="field col-3"><label class="label">Liters</label><input name="fuel_liters" type="number" step="0.001" min="0.001" required></div>
                            <div class="field col-4"><label class="label">Fuel Price</label><input name="fuel_price" type="number" step="0.01" min="0" required></div>
                            <div class="field col-4"><label class="label">Slip Ref</label><input name="slip_ref" placeholder="Receipt no"></div>
                            <div class="field col-4"><label class="label">Notes</label><input name="notes" placeholder="Optional"></div>
                            <div class="col-12 toolbar"><button class="btn btn-primary" type="submit">Save Fuel</button></div>
                        </form>
                        <div id="svFuelList" class="muted" style="margin-top:10px;">No fuel entry recorded.</div>
                    </div>

                    <div class="sv-mini-card">
                        <h4>Adjustment Entry</h4>
                        <form id="svAdjustmentSetupForm" class="form-grid">
                            <div class="field col-3"><label class="label">Month</label><input name="month_cycle" value="{{ $monthCycle }}" required></div>
                            <div class="field col-3"><label class="label">Vehicle</label><select name="vehicle_id" id="svAdjustmentVehicleId"><option value="">Overall</option></select></div>
                            <div class="field col-3"><label class="label">Direction</label><select name="direction"><option value="plus">Add Cost</option><option value="minus">Deduct Cost</option></select></div>
                            <div class="field col-3"><label class="label">Amount</label><input name="amount" type="number" step="0.01" min="0.01" required></div>
                            <div class="field col-6"><label class="label">Reason</label><input name="reason" required placeholder="Reason"></div>
                            <div class="field col-6"><label class="label">Notes</label><input name="notes" placeholder="Optional notes"></div>
                            <div class="col-12 toolbar"><button class="btn btn-primary" type="submit">Save Adjustment</button></div>
                        </form>
                        <div id="svAdjustmentList" class="muted" style="margin-top:10px;">No adjustment recorded.</div>
                    </div>
                </div>
            </div>
        </div>

        <div id="schoolVanCostModal" class="sv-modal" hidden>
            <div class="sv-modal-panel" style="width:min(1120px,100%);">
                <div class="sv-modal-head">
                    <h3>Monthly Cost Allocation</h3>
                    <button type="button" class="sv-modal-close" id="schoolVanCostClose" aria-label="Close">&times;</button>
                </div>

                <div class="sv-policy" id="svAllocationPolicy">
                    Total Expense = Rent + Fuel Cost + Plus Adjustment - Minus Adjustment. Company Share = 50%. Employee Share = 50%.
                </div>

                <div class="sv-cost-kpis">
                    <div class="sv-cost-kpi"><div class="muted">Total Expense</div><div class="value" id="svCostTotal">0.00</div></div>
                    <div class="sv-cost-kpi"><div class="muted">Company Share 50%</div><div class="value" id="svCostCompany">0.00</div></div>
                    <div class="sv-cost-kpi"><div class="muted">Employees Share 50%</div><div class="value" id="svCostEmployee">0.00</div></div>
                    <div class="sv-cost-kpi"><div class="muted">Per Child Charge</div><div class="value" id="svCostPerChild">0.00</div></div>
                </div>

                <div class="toolbar" style="margin-bottom:12px;">
                    <span class="badge" id="svAllocationStatus">Loading</span>
                    <span class="muted" id="svAllocationCycle"></span>
                    <span class="muted" id="svAllocationUnits"></span>
                </div>

                <div class="toolbar" style="margin-bottom:12px;justify-content:space-between;align-items:center;">
                    <span class="muted" id="svGeneratedBillState">Bill Status: Not Generated</span>
                    <button type="button" class="btn btn-primary" id="schoolVanGenerateBill" disabled>
                        Generate School Van Bill
                    </button>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Father Name</th>
                                <th>Kids</th>
                                <th>Chargeable Units</th>
                                <th>Rounding Adj.</th>
                                <th>Payable Amount</th>
                            </tr>
                        </thead>
                        <tbody id="svEmployeeAllocationRows">
                            <tr><td colspan="6" class="muted">No calculation loaded.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="schoolVanAddModal" class="sv-modal" hidden>
            <div class="sv-modal-panel">
                <div class="sv-modal-head">
                    <h3>Add Kid to School Van</h3>
                    <button type="button" class="sv-modal-close" id="schoolVanAddClose" aria-label="Close">&times;</button>
                </div>
        <form id="schoolVanAddForm" class="form-grid">
            <div class="field col-4">
                <label class="label">Select Kid from Family Details</label>
                <select name="family_member_id" id="schoolVanFamilyMemberId" required></select>
            </div>
            <div class="field col-2">
                <label class="label">Join Date</label>
                <input type="date" name="joined_on" id="schoolVanJoinedOn" required>
            </div>
            <div class="field col-3">
                <label class="label">Vehicle (optional)</label>
                <select name="vehicle_id" id="schoolVanVehicleId">
                    <option value="">Not Assigned</option>
                </select>
            </div>
            <div class="field col-3">
                <label class="label">Route Label</label>
                <input name="route_label" placeholder="Optional route">
            </div>
            <div class="field col-9">
                <label class="label">Remarks</label>
                <input name="remarks" placeholder="Optional remarks">
            </div>
            <div class="col-3 toolbar" style="align-items:flex-end;">
                <button class="btn btn-primary" type="submit">Add Kid to School Van</button>
            </div>
        </form>


            </div>
        </div>

        <div class="form-grid" style="margin-top:16px;margin-bottom:12px;">
            <div class="field col-6">
                <label class="label">Search Active Students</label>
                <input id="schoolVanSearch" placeholder="Search child, father or employee ID">
            </div>
            <div class="field col-3">
                <label class="label">Status Filter</label>
                <select id="schoolVanStatusFilter">
                    <option value="ACTIVE">Active Students</option>
                    <option value="LEFT">Withdrawn Students</option>
                    <option value="CANCELLED">Cancelled Entries</option>
                    <option value="ALL">All Records</option>
                </select>
            </div>
            <div class="field col-3">
                <label class="label">Rows Per Page</label>
                <select id="schoolVanPageSize">
                    <option value="10" selected>10 Rows</option>
                    <option value="25">25 Rows</option>
                    <option value="50">50 Rows</option>
                </select>
            </div>
        </div>

        <div class="table-wrap" style="margin-top:8px;">
            <table>
                <thead>
                    <tr>
                        <th>Child</th>
                        <th>Company ID</th>
                        <th>Father</th>
                        <th>School / Class</th>
                        <th>Joined On</th>
                        <th>Left On</th>
                        <th>Status</th>
                        <th>Action / History</th>
                    </tr>
                </thead>
                <tbody id="schoolVanRows">
                    <tr><td colspan="8" class="muted">Loading school van kids...</td></tr>
                </tbody>
            </table>
        </div>
        <div id="schoolVanPagination" class="toolbar" style="justify-content:space-between;margin-top:12px;"></div>

        <div id="schoolVanLeftModal" class="sv-modal" hidden>
            <div class="sv-modal-panel small">
                <div class="sv-modal-head">
                    <h3>Mark Kid Left</h3>
                    <button type="button" class="sv-modal-close" id="schoolVanLeftClose" aria-label="Close">&times;</button>
                </div>
                <div class="stack" style="gap:12px;">
                    <div>
                        <div class="label">Student</div>
                        <div id="schoolVanLeftChildName" style="font-weight:700;"></div>
                    </div>
                    <div class="field">
                        <label class="label">Left Date</label>
                        <input type="date" id="schoolVanLeftDate" required>
                    </div>
                    <div class="field">
                        <label class="label">Remarks</label>
                        <input id="schoolVanLeftRemarks" placeholder="Optional remarks">
                    </div>
                    <div class="muted">Billing impact calculation next phase mein apply hogi.</div>
                </div>
                <div class="sv-actions">
                    <button type="button" class="btn" id="schoolVanLeftCancel">Cancel</button>
                    <button type="button" class="btn btn-primary" id="schoolVanLeftConfirm">Confirm Left</button>
                </div>
            </div>
        </div>
    </div>

    <div id="schoolVanManageModal" class="sv-modal" hidden>
        <div class="sv-modal-panel small">
            <div class="sv-modal-head">
                <h3>Manage Student Status</h3>
                <button type="button" class="sv-modal-close" id="schoolVanManageClose" aria-label="Close">&times;</button>
            </div>

            <div class="sv-manage-summary">
                <div>
                    <div class="label">Student</div>
                    <div id="schoolVanManageChildName" style="font-weight:700;"></div>
                </div>
                <span class="badge" id="schoolVanManageCurrentStatus">ACTIVE</span>
            </div>

            <div class="field">
                <label class="label">Select Action</label>
                <select id="schoolVanManageAction" required></select>
            </div>

            <div class="sv-manage-panels">
                <div id="schoolVanManageHistoryPanel" class="sv-manage-info" hidden>
                    <div><strong>Status:</strong> <span id="svManageHistoryStatus"></span></div>
                    <div><strong>Original Status:</strong> <span id="svManageHistoryFromStatus"></span></div>
                    <div><strong>Cancellation Reason:</strong> <span id="svManageHistoryCancelReason"></span></div>
                    <div><strong>Remarks:</strong> <span id="svManageHistoryRemarks"></span></div>
                    <div><strong>Cancelled At / By:</strong> <span id="svManageHistoryMeta"></span></div>
                </div>

                <div id="schoolVanManageLeftPanel" hidden>
                    <div class="field">
                        <label class="label">Left Date</label>
                        <input type="date" id="schoolVanManageLeftDate">
                    </div>
                    <div class="field">
                        <label class="label">Reason</label>
                        <select id="schoolVanManageLeftReason">
                            <option value="">Select reason</option>
                            <option value="SERVICE_WITHDRAWN">Service Withdrawn</option>
                            <option value="NO_LONGER_USING_SCHOOL_VAN">No Longer Using School Van</option>
                            <option value="SCHOOL_CHANGED">School Changed</option>
                            <option value="ROUTE_NOT_REQUIRED">Route Not Required</option>
                            <option value="PARENT_REQUEST">Parent Request</option>
                            <option value="OTHER">Other</option>
                        </select>
                    </div>
                    <div class="field">
                        <label class="label">Remarks</label>
                        <input id="schoolVanManageLeftRemarks" placeholder="Required only when reason is Other">
                    </div>
                </div>

                <div id="schoolVanManageCancelPanel" hidden>
                    <div class="field">
                        <label class="label">Cancellation Reason</label>
                        <select id="schoolVanManageCancelReason">
                            <option value="">Select reason</option>
                            <option value="MISTAKEN_ENTRY">Mistaken Entry</option>
                            <option value="DUPLICATE_ENROLMENT">Duplicate Enrolment</option>
                            <option value="INCORRECT_CHILD_SELECTED">Incorrect Child Selected</option>
                            <option value="INCORRECT_EMPLOYEE_FATHER_LINK">Incorrect Employee/Father Link</option>
                            <option value="INCORRECT_JOIN_DATE">Incorrect Join Date</option>
                            <option value="SERVICE_NOT_AVAILED">Service Not Availed</option>
                            <option value="ENTRY_CREATED_FOR_TESTING">Entry Created for Testing</option>
                            <option value="OTHER_ADMINISTRATIVE_CORRECTION">Other Administrative Correction</option>
                        </select>
                    </div>
                    <div class="field">
                        <label class="label">Remarks</label>
                        <input id="schoolVanManageCancelRemarks" placeholder="Required only for Other Administrative Correction">
                    </div>
                    <div class="muted">Wrong entry cancel hone par billing se exclude hogi; audit history retain rahegi.</div>
                </div>

                <div id="schoolVanManageReactivatePanel" hidden>
                    <div class="field">
                        <label class="label">Restore Reason</label>
                        <select id="schoolVanManageReactivateReason">
                            <option value="">Select reason</option>
                            <option value="MARKED_LEFT_BY_MISTAKE">Marked Left by Mistake</option>
                            <option value="INCORRECT_LEAVE_DATE">Incorrect Leave Date</option>
                            <option value="SERVICE_CONTINUED">Service Continued</option>
                            <option value="ADMINISTRATIVE_CORRECTION">Administrative Correction</option>
                        </select>
                    </div>
                    <div class="field">
                        <label class="label">Remarks</label>
                        <input id="schoolVanManageReactivateRemarks" placeholder="Optional remarks">
                    </div>
                </div>

                <div id="schoolVanManageRestoreCancelPanel" hidden>
                    <div class="field">
                        <label class="label">Restore Cancelled Entry Reason</label>
                        <select id="schoolVanManageRestoreCancelReason">
                            <option value="">Select reason</option>
                            <option value="CANCELLED_BY_MISTAKE">Cancelled by Mistake</option>
                            <option value="INCORRECT_CANCELLATION_REASON">Incorrect Cancellation Reason</option>
                            <option value="ADMINISTRATIVE_CORRECTION">Administrative Correction</option>
                        </select>
                    </div>
                    <div class="field">
                        <label class="label">Remarks</label>
                        <input id="schoolVanManageRestoreCancelRemarks" placeholder="Optional remarks">
                    </div>
                    <div class="muted">Entry original status par restore hogi: ACTIVE ya LEFT.</div>
                </div>
            </div>

            <div class="sv-actions">
                <button type="button" class="btn" id="schoolVanManageDismiss">Close</button>
                <button type="button" class="btn btn-primary" id="schoolVanManageConfirm" hidden>Confirm Action</button>
            </div>
        </div>
    </div>

    <div id="schoolVanCancelModal" class="sv-modal" hidden>
        <div class="sv-modal-panel small">
            <div class="sv-modal-head">
                <h3>Cancel Wrong Entry</h3>
                <button type="button" class="sv-modal-close" id="schoolVanCancelClose" aria-label="Close">&times;</button>
            </div>
            <div class="stack" style="gap:12px;">
                <div>
                    <div class="label">Student</div>
                    <div id="schoolVanCancelChildName" style="font-weight:700;"></div>
                </div>
                <div class="field">
                    <label class="label">Cancellation Reason</label>
                    <select id="schoolVanCancelReason" required>
                        <option value="">Select reason</option>
                        <option value="MISTAKEN_ENTRY">Mistaken Entry</option>
                        <option value="DUPLICATE_ENROLMENT">Duplicate Enrolment</option>
                        <option value="INCORRECT_CHILD_SELECTED">Incorrect Child Selected</option>
                        <option value="INCORRECT_EMPLOYEE_FATHER_LINK">Incorrect Employee/Father Link</option>
                        <option value="INCORRECT_JOIN_DATE">Incorrect Join Date</option>
                        <option value="SERVICE_NOT_AVAILED">Service Not Availed</option>
                        <option value="ENTRY_CREATED_FOR_TESTING">Entry Created for Testing</option>
                        <option value="OTHER_ADMINISTRATIVE_CORRECTION">Other Administrative Correction</option>
                    </select>
                </div>
                <div class="field">
                    <label class="label">Remarks</label>
                    <input id="schoolVanCancelRemarks" placeholder="Required for Other Administrative Correction">
                </div>
                <div class="muted">Cancelled entry billing se exclude hogi, lekin audit/history retain rahegi.</div>
            </div>
            <div class="sv-actions">
                <button type="button" class="btn" id="schoolVanCancelDismiss">Close</button>
                <button type="button" class="btn sv-action-danger" id="schoolVanCancelConfirm">Confirm Cancellation</button>
            </div>
        </div>
    </div>

    <div id="schoolVanReactivateModal" class="sv-modal" hidden>
        <div class="sv-modal-panel small">
            <div class="sv-modal-head">
                <h3>Reactivate Student</h3>
                <button type="button" class="sv-modal-close" id="schoolVanReactivateClose" aria-label="Close">&times;</button>
            </div>
            <div class="stack" style="gap:12px;">
                <div>
                    <div class="label">Student</div>
                    <div id="schoolVanReactivateChildName" style="font-weight:700;"></div>
                </div>
                <div class="field">
                    <label class="label">Reactivation Reason</label>
                    <select id="schoolVanReactivateReason" required>
                        <option value="">Select reason</option>
                        <option value="MARKED_LEFT_BY_MISTAKE">Marked Left by Mistake</option>
                        <option value="INCORRECT_LEAVE_DATE">Incorrect Leave Date</option>
                        <option value="SERVICE_CONTINUED">Service Continued</option>
                        <option value="ADMINISTRATIVE_CORRECTION">Administrative Correction</option>
                    </select>
                </div>
                <div class="field">
                    <label class="label">Remarks</label>
                    <input id="schoolVanReactivateRemarks" placeholder="Optional remarks">
                </div>
            </div>
            <div class="sv-actions">
                <button type="button" class="btn" id="schoolVanReactivateDismiss">Close</button>
                <button type="button" class="btn btn-primary" id="schoolVanReactivateConfirm">Confirm Reactivate</button>
            </div>
        </div>
    </div>

    <div id="schoolVanDeferredModules" hidden aria-hidden="true">
        <div class="grid">
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
        <h3 class="section-title">Raw Response</h3>
        <pre id="transportResult">Ready.</pre>
    </div>
        </div>
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
const schoolVanRows = document.getElementById('schoolVanRows');
const schoolVanAddForm = document.getElementById('schoolVanAddForm');
const schoolVanFamilyMemberId = document.getElementById('schoolVanFamilyMemberId');
const schoolVanVehicleId = document.getElementById('schoolVanVehicleId');
const schoolVanJoinedOn = document.getElementById('schoolVanJoinedOn');
const schoolVanSearch = document.getElementById('schoolVanSearch');
const schoolVanStatusFilter = document.getElementById('schoolVanStatusFilter');
const schoolVanPageSize = document.getElementById('schoolVanPageSize');
const schoolVanPagination = document.getElementById('schoolVanPagination');
const schoolVanOpenAdd = document.getElementById('schoolVanOpenAdd');
const schoolVanAddModal = document.getElementById('schoolVanAddModal');
const schoolVanAddClose = document.getElementById('schoolVanAddClose');
const schoolVanLeftModal = document.getElementById('schoolVanLeftModal');
const schoolVanLeftClose = document.getElementById('schoolVanLeftClose');
const schoolVanLeftCancel = document.getElementById('schoolVanLeftCancel');
const schoolVanLeftConfirm = document.getElementById('schoolVanLeftConfirm');
const schoolVanLeftChildName = document.getElementById('schoolVanLeftChildName');
const schoolVanLeftDate = document.getElementById('schoolVanLeftDate');
const schoolVanLeftRemarks = document.getElementById('schoolVanLeftRemarks');
const schoolVanCancelModal = document.getElementById('schoolVanCancelModal');
const schoolVanCancelClose = document.getElementById('schoolVanCancelClose');
const schoolVanCancelDismiss = document.getElementById('schoolVanCancelDismiss');
const schoolVanCancelConfirm = document.getElementById('schoolVanCancelConfirm');
const schoolVanCancelChildName = document.getElementById('schoolVanCancelChildName');
const schoolVanCancelReason = document.getElementById('schoolVanCancelReason');
const schoolVanCancelRemarks = document.getElementById('schoolVanCancelRemarks');
const schoolVanReactivateModal = document.getElementById('schoolVanReactivateModal');
const schoolVanReactivateClose = document.getElementById('schoolVanReactivateClose');
const schoolVanReactivateDismiss = document.getElementById('schoolVanReactivateDismiss');
const schoolVanReactivateConfirm = document.getElementById('schoolVanReactivateConfirm');
const schoolVanReactivateChildName = document.getElementById('schoolVanReactivateChildName');
const schoolVanReactivateReason = document.getElementById('schoolVanReactivateReason');
const schoolVanReactivateRemarks = document.getElementById('schoolVanReactivateRemarks');
const schoolVanManageModal = document.getElementById('schoolVanManageModal');
const schoolVanManageClose = document.getElementById('schoolVanManageClose');
const schoolVanManageDismiss = document.getElementById('schoolVanManageDismiss');
const schoolVanManageConfirm = document.getElementById('schoolVanManageConfirm');
const schoolVanManageChildName = document.getElementById('schoolVanManageChildName');
const schoolVanManageCurrentStatus = document.getElementById('schoolVanManageCurrentStatus');
const schoolVanManageAction = document.getElementById('schoolVanManageAction');
const schoolVanManageHistoryPanel = document.getElementById('schoolVanManageHistoryPanel');
const schoolVanManageLeftPanel = document.getElementById('schoolVanManageLeftPanel');
const schoolVanManageCancelPanel = document.getElementById('schoolVanManageCancelPanel');
const schoolVanManageReactivatePanel = document.getElementById('schoolVanManageReactivatePanel');
const schoolVanManageRestoreCancelPanel = document.getElementById('schoolVanManageRestoreCancelPanel');
const schoolVanManageLeftDate = document.getElementById('schoolVanManageLeftDate');
const schoolVanManageLeftReason = document.getElementById('schoolVanManageLeftReason');
const schoolVanManageLeftRemarks = document.getElementById('schoolVanManageLeftRemarks');
const schoolVanManageCancelReason = document.getElementById('schoolVanManageCancelReason');
const schoolVanManageCancelRemarks = document.getElementById('schoolVanManageCancelRemarks');
const schoolVanManageReactivateReason = document.getElementById('schoolVanManageReactivateReason');
const schoolVanManageReactivateRemarks = document.getElementById('schoolVanManageReactivateRemarks');
const schoolVanManageRestoreCancelReason = document.getElementById('schoolVanManageRestoreCancelReason');
const schoolVanManageRestoreCancelRemarks = document.getElementById('schoolVanManageRestoreCancelRemarks');
const schoolVanOpenExpense = document.getElementById('schoolVanOpenExpense');
const schoolVanExpenseModal = document.getElementById('schoolVanExpenseModal');
const schoolVanExpenseClose = document.getElementById('schoolVanExpenseClose');
const schoolVanOpenCost = document.getElementById('schoolVanOpenCost');
const schoolVanCostModal = document.getElementById('schoolVanCostModal');
const schoolVanCostClose = document.getElementById('schoolVanCostClose');
const schoolVanGenerateBill = document.getElementById('schoolVanGenerateBill');
const svGeneratedBillState = document.getElementById('svGeneratedBillState');
const svCycleSetupForm = document.getElementById('svCycleSetupForm');
const svCycleSavedState = document.getElementById('svCycleSavedState');
const svVehicleSetupForm = document.getElementById('svVehicleSetupForm');
const svRentSetupForm = document.getElementById('svRentSetupForm');
const svFuelSetupForm = document.getElementById('svFuelSetupForm');
const svAdjustmentSetupForm = document.getElementById('svAdjustmentSetupForm');
const svRentVehicleId = document.getElementById('svRentVehicleId');
const svFuelVehicleId = document.getElementById('svFuelVehicleId');
const svAdjustmentVehicleId = document.getElementById('svAdjustmentVehicleId');
const transportBannerHost = document.getElementById('transportBannerHost');
const transportMonthLockState = document.getElementById('transportMonthLockState');
const vehicleForm = document.getElementById('vehicleForm');
const rentForm = document.getElementById('rentForm');
const fuelForm = document.getElementById('fuelForm');
const adjustmentForm = document.getElementById('adjustmentForm');
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
let schoolVanAllRows = [];
let schoolVanCurrentPage = 1;
let schoolVanPendingLeaveId = null;
let schoolVanPendingCancelId = null;
let schoolVanPendingReactivateId = null;
let schoolVanPendingManageId = null;

// Render modals directly under body so fixed positioning is true viewport-centered,
// independent of the transformed/scrolling workspace container.
[
    schoolVanExpenseModal,
    schoolVanCostModal,
    schoolVanAddModal,
    schoolVanLeftModal,
    schoolVanCancelModal,
    schoolVanReactivateModal,
    schoolVanManageModal
].forEach((modal) => {
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }
});

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
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const r = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
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

    // Legacy expense save actions remain disabled until transport calculation workflow is activated.
    vehicleForm.querySelector('button[type="submit"]').disabled = true;
    rentForm.querySelector('button[type="submit"]').disabled = true;
    fuelForm.querySelector('button[type="submit"]').disabled = true;
    adjustmentForm.querySelector('button[type="submit"]').disabled = true;
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

function esc(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function renderSchoolVanVehicleOptions(vehicles) {
    const options = (vehicles || []).map((vehicle) =>
        `<option value="${esc(vehicle.id)}">${esc(vehicle.vehicle_name)} (${esc(vehicle.vehicle_code)})</option>`
    ).join('');
    schoolVanVehicleId.innerHTML = '<option value="">Not Assigned</option>' + options;
}

function renderSchoolVanEligibleKids(rows) {
    const options = (rows || []).map((row) =>
        `<option value="${esc(row.family_member_id)}">${esc(row.company_id)} - ${esc(row.child_name)} / ${esc(row.father_name || '')}</option>`
    ).join('');

    schoolVanFamilyMemberId.innerHTML = options || '<option value="">No eligible school-going kids available</option>';
    schoolVanAddForm.querySelector('button[type="submit"]').disabled = !(rows || []).length;
}

function renderSchoolVanRows(rows = null) {
    if (Array.isArray(rows)) {
        schoolVanAllRows = rows;
        schoolVanCurrentPage = 1;
    }

    const query = String(schoolVanSearch?.value || '').trim().toLowerCase();
    const status = String(schoolVanStatusFilter?.value || 'ACTIVE');
    const pageSize = Number(schoolVanPageSize?.value || 10);

    const filtered = schoolVanAllRows.filter((row) => {
        const matchesStatus = status === 'ALL' || row.status === status;
        const haystack = [
            row.child_name,
            row.company_id,
            row.father_name,
            row.school_name,
            row.class_name
        ].join(' ').toLowerCase();

        return matchesStatus && (!query || haystack.includes(query));
    });

    const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
    schoolVanCurrentPage = Math.min(Math.max(1, schoolVanCurrentPage), totalPages);

    const startIndex = (schoolVanCurrentPage - 1) * pageSize;
    const visibleRows = filtered.slice(startIndex, startIndex + pageSize);

    if (!visibleRows.length) {
        schoolVanRows.innerHTML = '<tr><td colspan="8" class="muted">No matching school van students found.</td></tr>';
    } else {
        schoolVanRows.innerHTML = visibleRows.map((row) => {
            const schoolClass = [row.school_name || '', row.class_name || ''].filter(Boolean).join(' / ');
            const action = `<button type="button" class="btn sv-action-btn" data-open-manage="${esc(row.id)}">Manage Status</button>`;

            return `
                <tr>
                    <td>${esc(row.child_name)}</td>
                    <td>${esc(row.company_id)}</td>
                    <td>${esc(row.father_name || '')}</td>
                    <td>${esc(schoolClass)}</td>
                    <td>${esc(row.joined_on)}</td>
                    <td>${esc(row.left_on || '')}</td>
                    <td>${esc(row.status)}</td>
                    <td>${action}</td>
                </tr>
            `;
        }).join('');
    }

    const from = filtered.length ? startIndex + 1 : 0;
    const to = Math.min(startIndex + pageSize, filtered.length);

    schoolVanPagination.innerHTML = `
        <div class="muted">Showing ${from}-${to} of ${filtered.length} records</div>
        <div style="display:flex;gap:6px;">
            <button type="button" class="btn" data-sv-page="prev" ${schoolVanCurrentPage <= 1 ? 'disabled' : ''}>Previous</button>
            <span class="btn" style="pointer-events:none;">Page ${schoolVanCurrentPage} of ${totalPages}</span>
            <button type="button" class="btn" data-sv-page="next" ${schoolVanCurrentPage >= totalPages ? 'disabled' : ''}>Next</button>
        </div>
    `;
}

async function loadSchoolVan() {
    const result = await getJson('/api/transport/school-van/enrolments');
    const body = result.body || {};

    if (result.status >= 400 || body.status === 'error') {
        showBanner('error', body.message || body.error || 'School van enrolments could not be loaded.');
        return;
    }

    const totals = body.totals || {};
    setText('svTotal', totals.all_enrolments || 0);
    setText('svActive', totals.active_enrolments || 0);
    setText('svLeft', totals.left_enrolments || 0);
    setText('svCancelled', totals.cancelled_enrolments || 0);
    setText('svEligible', totals.eligible_not_enrolled || 0);

    renderSchoolVanEligibleKids(body.eligible_kids || []);
    renderSchoolVanRows(body.rows || []);
    renderSchoolVanVehicleOptions(currentVehicles || []);
}

function svMoney(value) {
    return Number(value || 0).toLocaleString('en-PK', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function svExpenseMonth() {
    return String(svRentSetupForm.querySelector('[name="month_cycle"]').value || '{{ $monthCycle }}').trim();
}

function renderSvSetupVehicles(vehicles) {
    const rows = vehicles || [];
    const options = rows.map((row) =>
        `<option value="${esc(row.id)}">${esc(row.vehicle_name)} (${esc(row.vehicle_code)})</option>`
    ).join('');

    svRentVehicleId.innerHTML = options || '<option value="">Add school van first</option>';
    svFuelVehicleId.innerHTML = options || '<option value="">Add school van first</option>';
    svAdjustmentVehicleId.innerHTML = '<option value="">Overall</option>' + options;

    document.getElementById('svVehicleList').innerHTML = rows.length
        ? rows.map((row) => `<div>${esc(row.vehicle_code)} - ${esc(row.vehicle_name)}</div>`).join('')
        : 'No school van configured.';
}

function renderSvExpenseEntries(body) {
    const rents = body.rent_entries || [];
    const fuels = body.fuel_entries || [];
    const adjustments = body.adjustments || [];
    const cycle = body.billing_cycle || {};

    svCycleSetupForm.querySelector('[name="month_cycle"]').value = body.month_cycle || svExpenseMonth();
    svCycleSetupForm.querySelector('[name="cycle_start_date"]').value = cycle.start || '';
    svCycleSetupForm.querySelector('[name="cycle_end_date"]').value = cycle.end || '';
    svCycleSavedState.textContent = cycle.start && cycle.end
        ? `Saved cycle: ${cycle.start} to ${cycle.end}`
        : 'Enter manual cycle dates before calculating charges.';

    document.getElementById('svRentList').innerHTML = rents.length
        ? rents.map((row) => `<div>${esc(row.vehicle_name)}: PKR ${svMoney(row.rent_amount)}</div>`).join('')
        : 'No rent entry recorded.';

    document.getElementById('svFuelList').innerHTML = fuels.length
        ? fuels.map((row) => `<div>${esc(row.entry_date)} / ${esc(row.vehicle_name)}: ${esc(row.fuel_liters)} L = PKR ${svMoney(row.fuel_cost)}</div>`).join('')
        : 'No fuel entry recorded.';

    document.getElementById('svAdjustmentList').innerHTML = adjustments.length
        ? adjustments.map((row) => `<div>${esc(row.direction)}: PKR ${svMoney(row.amount)} / ${esc(row.reason || '')}</div>`).join('')
        : 'No adjustment recorded.';
}

function renderSvCostAllocation(body) {
    const totals = body.totals || {};
    const rows = body.employee_allocations || [];
    const cycle = body.billing_cycle || {};

    setText('svCostTotal', svMoney(totals.total_expense));
    setText('svCostCompany', svMoney(totals.company_share));
    setText('svCostEmployee', svMoney(totals.employee_share));
    setText('svCostPerChild', svMoney(totals.per_child_charge));

    setText('svAllocationStatus', body.allocation_status || 'UNKNOWN');
    setText('svAllocationCycle', `Cycle: ${cycle.start || '-'} to ${cycle.end || '-'}`);
    setText('svAllocationUnits', `Chargeable Units: ${totals.chargeable_units ?? 0}`);

    const generation = body.bill_generation || {};
    const generated = generation.status === 'GENERATED';
    const readyToGenerate = body.allocation_status === 'READY' && !generated;

    svGeneratedBillState.textContent = generated
        ? `Bill Status: GENERATED / PKR ${svMoney(generation.generated_total || 0)}`
        : 'Bill Status: Not Generated';

    schoolVanGenerateBill.disabled = !readyToGenerate;
    schoolVanGenerateBill.textContent = generated
        ? 'Bill Already Generated'
        : 'Generate School Van Bill';

    const table = document.getElementById('svEmployeeAllocationRows');
    table.innerHTML = rows.length
        ? rows.map((row) => `
            <tr>
                <td>${esc(row.company_id)}</td>
                <td>${esc(row.father_name || '')}</td>
                <td>${esc(row.children_count)}</td>
                <td>${esc(row.chargeable_units)}</td>
                <td>${svMoney(row.rounding_adjustment)}</td>
                <td>PKR ${svMoney(row.payable_amount)}</td>
            </tr>
        `).join('')
        : '<tr><td colspan="6" class="muted">No employee allocation available.</td></tr>';
}

async function loadSvExpenseAndCost() {
    const month = svExpenseMonth();

    if (!/^\d{2}-\d{4}$/.test(month)) {
        showBanner('error', 'Valid month cycle MM-YYYY required.');
        return;
    }

    const result = await getJson(`/api/transport/summary?month_cycle=${encodeURIComponent(month)}`);
    const body = result.body || {};

    if (result.status >= 400 || body.status === 'error') {
        showBanner('error', body.message || body.error || 'Expense summary could not be loaded.');
        return;
    }

    currentVehicles = body.vehicles || [];
    renderSvSetupVehicles(currentVehicles);
    renderSchoolVanVehicleOptions(currentVehicles);
    renderSvExpenseEntries(body);
    renderSvCostAllocation(body);
}

async function submitSvExpenseForm(form, url) {
    clearBanner();

    const payload = formToObject(form);
    const result = await postJson(url, payload);
    const body = result.body || {};

    if (result.status >= 400 || body.status === 'error') {
        showBanner('error', body.message || body.error || 'Entry could not be saved.');
        return;
    }

    showBanner('success', body.message || 'Entry saved successfully.');
    await loadSvExpenseAndCost();
}

function openSchoolVanModal(modal) {
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
}

function closeSchoolVanModal(modal) {
    modal.hidden = true;
    document.body.style.overflow = '';
}

function openSchoolVanLeftModal(enrolmentId) {
    const row = schoolVanAllRows.find((item) => String(item.id) === String(enrolmentId));
    if (!row) return;

    schoolVanPendingLeaveId = enrolmentId;
    schoolVanLeftChildName.textContent = `${row.child_name} / ${row.company_id} / ${row.father_name || ''}`;
    schoolVanLeftDate.value = '';
    schoolVanLeftRemarks.value = '';
    openSchoolVanModal(schoolVanLeftModal);
}

function schoolVanManageCancelLabel(code) {
    const labels = {
        MISTAKEN_ENTRY: 'Mistaken Entry',
        DUPLICATE_ENROLMENT: 'Duplicate Enrolment',
        INCORRECT_CHILD_SELECTED: 'Incorrect Child Selected',
        INCORRECT_EMPLOYEE_FATHER_LINK: 'Incorrect Employee/Father Link',
        INCORRECT_JOIN_DATE: 'Incorrect Join Date',
        SERVICE_NOT_AVAILED: 'Service Not Availed',
        ENTRY_CREATED_FOR_TESTING: 'Entry Created for Testing',
        OTHER_ADMINISTRATIVE_CORRECTION: 'Other Administrative Correction'
    };
    return labels[code] || code || '-';
}

function syncSchoolVanManagePanels() {
    const action = schoolVanManageAction.value;

    [
        schoolVanManageHistoryPanel,
        schoolVanManageLeftPanel,
        schoolVanManageCancelPanel,
        schoolVanManageReactivatePanel,
        schoolVanManageRestoreCancelPanel
    ].forEach((panel) => panel.hidden = true);

    schoolVanManageConfirm.hidden = false;

    if (!action) {
        schoolVanManageConfirm.hidden = true;
        return;
    }

    if (action === 'VIEW_HISTORY') {
        schoolVanManageHistoryPanel.hidden = false;
        schoolVanManageConfirm.hidden = true;
    } else if (action === 'MARK_LEFT') {
        schoolVanManageLeftPanel.hidden = false;
    } else if (action === 'CANCEL_ENTRY') {
        schoolVanManageCancelPanel.hidden = false;
    } else if (action === 'RESTORE_ACTIVE') {
        schoolVanManageReactivatePanel.hidden = false;
    } else if (action === 'RESTORE_CANCELLATION') {
        schoolVanManageHistoryPanel.hidden = false;
        schoolVanManageRestoreCancelPanel.hidden = false;
    }
}

function openSchoolVanManageModal(enrolmentId) {
    const row = schoolVanAllRows.find((item) => String(item.id) === String(enrolmentId));
    if (!row) return;

    schoolVanPendingManageId = enrolmentId;
    schoolVanManageChildName.textContent = `${row.child_name} / ${row.company_id} / ${row.father_name || ''}`;
    schoolVanManageCurrentStatus.textContent = row.status || '-';

    document.getElementById('svManageHistoryStatus').textContent = row.status || '-';
    document.getElementById('svManageHistoryFromStatus').textContent = row.cancelled_from_status || '-';
    document.getElementById('svManageHistoryCancelReason').textContent = schoolVanManageCancelLabel(row.cancel_reason);
    document.getElementById('svManageHistoryRemarks').textContent = row.cancellation_remarks || '-';
    document.getElementById('svManageHistoryMeta').textContent = `${row.cancelled_at || '-'} / ${row.cancelled_by_user_id || '-'}`;

    schoolVanManageLeftDate.value = '';
    schoolVanManageLeftReason.value = '';
    schoolVanManageLeftRemarks.value = '';
    schoolVanManageCancelReason.value = '';
    schoolVanManageCancelRemarks.value = '';
    schoolVanManageReactivateReason.value = '';
    schoolVanManageReactivateRemarks.value = '';
    schoolVanManageRestoreCancelReason.value = '';
    schoolVanManageRestoreCancelRemarks.value = '';

    if (row.status === 'ACTIVE') {
        schoolVanManageAction.innerHTML = `
            <option value="">Select action</option>
            <option value="MARK_LEFT">Mark Left / Service Withdrawn</option>
            <option value="CANCEL_ENTRY">Cancel Wrong Entry</option>
        `;
    } else if (row.status === 'LEFT') {
        schoolVanManageAction.innerHTML = `
            <option value="">Select action</option>
            <option value="RESTORE_ACTIVE">Restore Active / Reverse Withdrawal</option>
            <option value="CANCEL_ENTRY">Cancel Wrong Entry</option>
        `;
    } else {
        schoolVanManageAction.innerHTML = `
            <option value="VIEW_HISTORY">View History</option>
            <option value="RESTORE_CANCELLATION">Restore Cancelled Entry</option>
        `;
    }

    syncSchoolVanManagePanels();
    openSchoolVanModal(schoolVanManageModal);
}

async function handleSchoolVanManageConfirm() {
    const row = schoolVanAllRows.find((item) => String(item.id) === String(schoolVanPendingManageId));
    const action = schoolVanManageAction.value;
    if (!row || !action) return;

    let url = '';
    let payload = {};

    if (action === 'MARK_LEFT') {
        if (!schoolVanManageLeftDate.value || !schoolVanManageLeftReason.value) {
            showBanner('error', 'Left date aur reason select karein.');
            return;
        }
        if (schoolVanManageLeftReason.value === 'OTHER' && !schoolVanManageLeftRemarks.value.trim()) {
            showBanner('error', 'Other reason ke liye remarks required hain.');
            return;
        }
        url = `/api/transport/school-van/enrolments/${row.id}/left`;
        payload = {
            left_on: schoolVanManageLeftDate.value,
            left_reason: schoolVanManageLeftReason.value,
            left_remarks: schoolVanManageLeftRemarks.value || ''
        };
    }

    if (action === 'CANCEL_ENTRY') {
        if (!schoolVanManageCancelReason.value) {
            showBanner('error', 'Cancellation reason select karein.');
            return;
        }
        if (schoolVanManageCancelReason.value === 'OTHER_ADMINISTRATIVE_CORRECTION' && !schoolVanManageCancelRemarks.value.trim()) {
            showBanner('error', 'Other Administrative Correction ke liye remarks required hain.');
            return;
        }
        url = `/api/transport/school-van/enrolments/${row.id}/cancel`;
        payload = {
            cancel_reason: schoolVanManageCancelReason.value,
            remarks: schoolVanManageCancelRemarks.value || ''
        };
    }

    if (action === 'RESTORE_ACTIVE') {
        if (!schoolVanManageReactivateReason.value) {
            showBanner('error', 'Restore reason select karein.');
            return;
        }
        url = `/api/transport/school-van/enrolments/${row.id}/reactivate`;
        payload = {
            reactivation_reason: schoolVanManageReactivateReason.value,
            remarks: schoolVanManageReactivateRemarks.value || ''
        };
    }

    if (action === 'RESTORE_CANCELLATION') {
        if (!schoolVanManageRestoreCancelReason.value) {
            showBanner('error', 'Restore cancelled entry reason select karein.');
            return;
        }
        url = `/api/transport/school-van/enrolments/${row.id}/restore-cancellation`;
        payload = {
            cancellation_reversal_reason: schoolVanManageRestoreCancelReason.value,
            remarks: schoolVanManageRestoreCancelRemarks.value || ''
        };
    }

    if (!url) return;

    clearBanner();
    const result = await postJson(url, payload);
    const body = result.body || {};

    if (result.status >= 400 || body.status === 'error') {
        showBanner('error', body.message || body.error || 'Status action complete nahi ho saki.');
        return;
    }

    showBanner('success', body.message || 'Student status updated successfully.');
    closeSchoolVanModal(schoolVanManageModal);
    schoolVanPendingManageId = null;
    await loadSchoolVan();
}

function openSchoolVanCancelModal(enrolmentId) {
    const row = schoolVanAllRows.find((item) => String(item.id) === String(enrolmentId));
    if (!row || row.status === 'CANCELLED') return;

    schoolVanPendingCancelId = enrolmentId;
    schoolVanCancelChildName.textContent = `${row.child_name} / ${row.company_id} / ${row.father_name || ''}`;
    schoolVanCancelReason.value = '';
    schoolVanCancelRemarks.value = '';
    openSchoolVanModal(schoolVanCancelModal);
}

function openSchoolVanReactivateModal(enrolmentId) {
    const row = schoolVanAllRows.find((item) => String(item.id) === String(enrolmentId));
    if (!row || row.status !== 'LEFT') return;

    schoolVanPendingReactivateId = enrolmentId;
    schoolVanReactivateChildName.textContent = `${row.child_name} / ${row.company_id} / ${row.father_name || ''}`;
    schoolVanReactivateReason.value = '';
    schoolVanReactivateRemarks.value = '';
    openSchoolVanModal(schoolVanReactivateModal);
}

async function handleSchoolVanCancel() {
    if (!schoolVanPendingCancelId) return;

    const reason = schoolVanCancelReason.value || '';
    const remarks = schoolVanCancelRemarks.value || '';

    if (!reason) {
        showBanner('error', 'Cancellation reason select karein.');
        return;
    }

    if (reason === 'OTHER_ADMINISTRATIVE_CORRECTION' && !remarks.trim()) {
        showBanner('error', 'Other Administrative Correction ke liye remarks required hain.');
        return;
    }

    const result = await postJson(`/api/transport/school-van/enrolments/${schoolVanPendingCancelId}/cancel`, {
        cancel_reason: reason,
        remarks: remarks
    });
    const body = result.body || {};

    if (result.status >= 400 || body.status === 'error') {
        showBanner('error', body.message || body.error || 'Wrong entry cancel nahi ho saki.');
        return;
    }

    showBanner('success', body.message || 'Wrong entry cancelled successfully.');
    closeSchoolVanModal(schoolVanCancelModal);
    schoolVanPendingCancelId = null;
    await loadSchoolVan();
}

async function handleSchoolVanReactivate() {
    if (!schoolVanPendingReactivateId) return;

    const reason = schoolVanReactivateReason.value || '';
    if (!reason) {
        showBanner('error', 'Reactivation reason select karein.');
        return;
    }

    const result = await postJson(`/api/transport/school-van/enrolments/${schoolVanPendingReactivateId}/reactivate`, {
        reactivation_reason: reason,
        remarks: schoolVanReactivateRemarks.value || ''
    });
    const body = result.body || {};

    if (result.status >= 400 || body.status === 'error') {
        showBanner('error', body.message || body.error || 'Student reactivate nahi ho saka.');
        return;
    }

    showBanner('success', body.message || 'Student reactivated successfully.');
    closeSchoolVanModal(schoolVanReactivateModal);
    schoolVanPendingReactivateId = null;
    await loadSchoolVan();
}

async function handleSchoolVanAdd(event) {
    event.preventDefault();
    clearBanner();

    const payload = formToObject(schoolVanAddForm);
    const result = await postJson('/api/transport/school-van/enrolments/add', payload);
    const body = result.body || {};

    if (result.status >= 400 || body.status === 'error') {
        showBanner('error', body.message || body.error || 'Kid could not be added to school van.');
        return;
    }

    showBanner('success', body.message || 'Kid added to school van successfully.');
    schoolVanAddForm.reset();
    schoolVanJoinedOn.value = new Date().toISOString().slice(0, 10);
    closeSchoolVanModal(schoolVanAddModal);
    await loadSchoolVan();
}

async function handleSchoolVanLeft(enrolmentId, leftOn) {
    clearBanner();

    if (!leftOn) {
        showBanner('error', 'Leave date select karein.');
        return;
    }

    const result = await postJson(`/api/transport/school-van/enrolments/${enrolmentId}/left`, {
        left_on: leftOn,
        remarks: schoolVanLeftRemarks.value || '',
    });
    const body = result.body || {};

    if (result.status >= 400 || body.status === 'error') {
        showBanner('error', body.message || body.error || 'Kid could not be marked left.');
        return;
    }

    showBanner('success', body.message || 'Kid marked left successfully.');
    closeSchoolVanModal(schoolVanLeftModal);
    schoolVanPendingLeaveId = null;
    await loadSchoolVan();
}

async function loadTransport() {
    clearBanner();
    clearEditState();
    const payload = getPayload();
    const selectedMonth = String(payload.month_cycle || '').trim();

    if (!/^\\d{2}-\\d{4}$/.test(selectedMonth)) {
        transportResult.textContent = 'Select a valid Month Cycle in MM-YYYY format to load rent/fuel summary.';
        return;
    }

    const month = encodeURIComponent(selectedMonth);
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

    renderSchoolVanVehicleOptions(body.vehicles || []);
    await loadSchoolVan();
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
[vehicleForm, rentForm, fuelForm, adjustmentForm].forEach((form) => {
    const button = form.querySelector('button[type="submit"]');
    if (button) {
        button.disabled = true;
        button.title = 'This entry action will be enabled in the transport calculation phase.';
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        showBanner('error', 'Vehicle, rent, fuel and adjustment saving will be enabled in the transport calculation phase.');
    });
});

schoolVanAddForm.addEventListener('submit', handleSchoolVanAdd);

schoolVanSearch.addEventListener('input', () => {
    schoolVanCurrentPage = 1;
    renderSchoolVanRows();
});

schoolVanStatusFilter.addEventListener('change', () => {
    schoolVanCurrentPage = 1;
    renderSchoolVanRows();
});

schoolVanPageSize.addEventListener('change', () => {
    schoolVanCurrentPage = 1;
    renderSchoolVanRows();
});

schoolVanPagination.addEventListener('click', (e) => {
    const direction = e.target.getAttribute('data-sv-page');
    if (!direction) return;

    if (direction === 'prev') schoolVanCurrentPage -= 1;
    if (direction === 'next') schoolVanCurrentPage += 1;
    renderSchoolVanRows();
});

schoolVanOpenExpense.addEventListener('click', async () => {
    await loadSvExpenseAndCost();
    openSchoolVanModal(schoolVanExpenseModal);
});

schoolVanExpenseClose.addEventListener('click', () => closeSchoolVanModal(schoolVanExpenseModal));

schoolVanOpenCost.addEventListener('click', async () => {
    await loadSvExpenseAndCost();
    openSchoolVanModal(schoolVanCostModal);
});

schoolVanCostClose.addEventListener('click', () => closeSchoolVanModal(schoolVanCostModal));

schoolVanGenerateBill.addEventListener('click', async () => {
    clearBanner();

    const month = svExpenseMonth();
    const confirmed = window.confirm(
        `Generate official School Van Bill for ${month}? After generation, cycle, expense and student-status changes for this month will be blocked.`
    );

    if (!confirmed) return;

    const result = await postJson('/api/transport/school-van/bill/generate', {
        month_cycle: month
    });
    const body = result.body || {};

    if (result.status >= 400 || body.status === 'error') {
        showBanner('error', body.message || body.error || 'School van bill could not be generated.');
        return;
    }

    showBanner('success', `Official School Van Bill generated: PKR ${svMoney(body.generated_total || 0)}.`);
    await loadSvExpenseAndCost();
});

svCycleSetupForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearBanner();

    const payload = formToObject(svCycleSetupForm);
    const result = await postJson('/api/transport/month-cycle/upsert', payload);
    const body = result.body || {};

    if (result.status >= 400 || body.status === 'error') {
        showBanner('error', body.message || body.error || 'Billing cycle could not be saved.');
        return;
    }

    showBanner('success', body.message || 'Billing cycle saved successfully.');
    svRentSetupForm.querySelector('[name="month_cycle"]').value = payload.month_cycle;
    svFuelSetupForm.querySelector('[name="month_cycle"]').value = payload.month_cycle;
    svAdjustmentSetupForm.querySelector('[name="month_cycle"]').value = payload.month_cycle;
    await loadSvExpenseAndCost();
});

svVehicleSetupForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    await submitSvExpenseForm(svVehicleSetupForm, '/api/transport/vehicles/upsert');
});

svRentSetupForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    await submitSvExpenseForm(svRentSetupForm, '/api/transport/rent-entries/upsert');
});

svFuelSetupForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    await submitSvExpenseForm(svFuelSetupForm, '/api/transport/fuel-entries/upsert');
});

svAdjustmentSetupForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    await submitSvExpenseForm(svAdjustmentSetupForm, '/api/transport/adjustments/upsert');
});

[schoolVanExpenseModal, schoolVanCostModal].forEach((modal) => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeSchoolVanModal(modal);
    });
});

schoolVanOpenAdd.addEventListener('click', () => {
    schoolVanJoinedOn.value = new Date().toISOString().slice(0, 10);
    openSchoolVanModal(schoolVanAddModal);
});

schoolVanAddClose.addEventListener('click', () => closeSchoolVanModal(schoolVanAddModal));

schoolVanRows.addEventListener('click', (e) => {
    const manageId = e.target.getAttribute('data-open-manage');
    if (!manageId) return;
    openSchoolVanManageModal(manageId);
});

schoolVanLeftClose.addEventListener('click', () => closeSchoolVanModal(schoolVanLeftModal));
schoolVanLeftCancel.addEventListener('click', () => closeSchoolVanModal(schoolVanLeftModal));
schoolVanLeftConfirm.addEventListener('click', async () => {
    if (!schoolVanPendingLeaveId) return;
    await handleSchoolVanLeft(schoolVanPendingLeaveId, schoolVanLeftDate.value);
});

schoolVanCancelClose.addEventListener('click', () => closeSchoolVanModal(schoolVanCancelModal));
schoolVanCancelDismiss.addEventListener('click', () => closeSchoolVanModal(schoolVanCancelModal));
schoolVanCancelConfirm.addEventListener('click', handleSchoolVanCancel);

schoolVanReactivateClose.addEventListener('click', () => closeSchoolVanModal(schoolVanReactivateModal));
schoolVanReactivateDismiss.addEventListener('click', () => closeSchoolVanModal(schoolVanReactivateModal));
schoolVanReactivateConfirm.addEventListener('click', handleSchoolVanReactivate);

schoolVanManageClose.addEventListener('click', () => closeSchoolVanModal(schoolVanManageModal));
schoolVanManageDismiss.addEventListener('click', () => closeSchoolVanModal(schoolVanManageModal));
schoolVanManageAction.addEventListener('change', syncSchoolVanManagePanels);
schoolVanManageConfirm.addEventListener('click', handleSchoolVanManageConfirm);

[schoolVanAddModal, schoolVanLeftModal, schoolVanCancelModal, schoolVanReactivateModal, schoolVanManageModal].forEach((modal) => {
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeSchoolVanModal(modal);
    });
});
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
schoolVanJoinedOn.value = new Date().toISOString().slice(0, 10);
loadSchoolVan();
loadTransport();
</script>
@endsection
