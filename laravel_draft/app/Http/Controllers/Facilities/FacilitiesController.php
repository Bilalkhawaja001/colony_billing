<?php

namespace App\Http\Controllers\Facilities;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

use Illuminate\Validation\ValidationException;

class FacilitiesController extends Controller
{
    private const FACILITY_TYPES = [
        'Washroom', 'Toilet / Bath Area', 'Office', 'Colony Block', 'Guest House', 'Market Area',
        'Masjid Area', 'RO Plant', 'Boiler Area', 'Garden / Fountain', 'Gate', 'Workshop',
        'Common Area', 'Other Physical Facility',
    ];

    private const CONDITIONS = ['Good', 'Average', 'Poor', 'Closed', 'Under Repair'];
    private const FACILITY_STATUSES = ['OPEN', 'OPERATIONAL', 'UNDER_REPAIR', 'CLOSED'];
    private const REQUEST_TYPES = ['REPAIR', 'PREVENTIVE', 'DEEP_CLEANING', 'EMERGENCY_CLEANING', 'PEST_CONTROL', 'OTHER'];
    private const PRIORITIES = ['LOW', 'NORMAL', 'HIGH', 'CRITICAL'];
    private const APPROVAL_LEVELS = ['SUPERVISOR', 'FACILITIES_MANAGER'];
    private const WORK_ORDER_TRANSITIONS = [
        'OPEN' => ['ASSIGNED', 'CANCELLED'],
        'ASSIGNED' => ['IN_PROGRESS', 'CANCELLED'],
        'IN_PROGRESS' => ['COMPLETED', 'CANCELLED'],
        'COMPLETED' => ['VERIFIED', 'REWORK_REQUIRED'],
        'VERIFIED' => ['CLOSED'],
        'REWORK_REQUIRED' => ['ASSIGNED', 'IN_PROGRESS', 'CANCELLED'],
        'CLOSED' => [],
        'CANCELLED' => [],
    ];
    private const ROUTINE_SERVICES = [
        'Daily Cleaning', 'Deep Cleaning', 'Emergency Cleaning', 'Mosquito Spray',
        'Bedbugs / Bugs Treatment', 'Rats / Rodents Control', 'General Insect Spray',
        'Fumigation / Pest Control Follow-Up',
    ];

    public function overview(): View
    {
        return view('facilities.overview', [
            'kpis' => $this->overviewKpis(),
            'workCategories' => $this->workCategories(),
            'facilityTypes' => self::FACILITY_TYPES,
            'routineServices' => self::ROUTINE_SERVICES,
        ]);
    }

    public function registry(): View
    {
        return view('facilities.registry', [
            'facilities' => $this->facilities(),
            'components' => $this->components(),
            'facilityTypes' => self::FACILITY_TYPES,
            'conditions' => self::CONDITIONS,
            'statuses' => self::FACILITY_STATUSES,
            'componentTypes' => $this->componentTypes(),
        ]);
    }

    public function inspections(): View
    {
        return $this->placeholder('Inspections', 'Inspection schedules and checklists are not part of Phase 2A.');
    }

    public function serviceRequests(Request $request): View
    {
        return view('facilities.service-requests', [
            'rows' => $this->serviceRequestRows($request),
            'facilities' => $this->facilities(),
            'componentTypeRows' => $this->componentTypeRows(),
            'requesterEmployees' => $this->activeRequesterEmployees(),
            'workCategories' => $this->workCategories(),
            'requestTypes' => self::REQUEST_TYPES,
            'priorities' => self::PRIORITIES,
            'approvalLevels' => self::APPROVAL_LEVELS,
            'filters' => $request->only(['status', 'priority', 'work_category_id']),
        ]);
    }

    public function approvalQueue(): View
    {
        return view('facilities.approval-queue', [
            'rows' => $this->pendingApprovalRows(),
            'history' => $this->approvalHistoryRows(),
        ]);
    }

    public function workOrders(): View
    {
        return view('facilities.work-orders', [
            'rows' => $this->loadWorkOrders(),
            'histories' => $this->statusHistoryRows(),
            'workCategories' => $this->workCategories(),
        ]);
    }

    public function dailyServices(): View
    {
        return view('facilities.daily-services', [
            'rows' => $this->loadDailyServices(),
            'routineServices' => self::ROUTINE_SERVICES,
        ]);
    }

    public function verificationClosure(): View
    {
        return view('facilities.verification-closure', [
            'completedRows' => $this->loadWorkOrders(['COMPLETED']),
            'verifiedRows' => $this->loadWorkOrders(['VERIFIED']),
            'reworkRows' => $this->loadWorkOrders(['REWORK_REQUIRED']),
        ]);
    }

    public function reports(): View
    {
        return $this->placeholder('Reports', 'Facilities reports expansion is deferred.');
    }

    public function storeFacility(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'facility_code' => ['required', 'string', 'max:80'],
            'facility_name' => ['required', 'string', 'max:255'],
            'facility_type' => ['required', 'string', 'max:80'],
            'section' => ['nullable', 'string', 'max:120'],
            'area' => ['nullable', 'string', 'max:160'],
            'specific_location' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:40'],
            'condition' => ['nullable', 'string', 'max:40'],
            'is_active' => ['nullable'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::table('facility_registries')->updateOrInsert(
            ['facility_code' => trim($data['facility_code'])],
            [
                'facility_name' => trim($data['facility_name']),
                'facility_type' => trim($data['facility_type']),
                'section' => $this->nullableTrim($data['section'] ?? null),
                'area' => $this->nullableTrim($data['area'] ?? null),
                'specific_location' => $this->nullableTrim($data['specific_location'] ?? null),
                'status' => $this->nullableTrim($data['status'] ?? null) ?: 'OPEN',
                'condition' => $this->nullableTrim($data['condition'] ?? null),
                'is_active' => $request->boolean('is_active', true) ? 1 : 0,
                'notes' => $this->nullableTrim($data['notes'] ?? null),
                'updated_by' => $this->actor(),
                'created_by' => $this->actor(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect('/facilities-management/registry')->with('status', 'Facility registry record saved.');
    }

    public function storeComponent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'facility_id' => ['required', 'integer'],
            'component_type' => ['required', 'string', 'max:100'],
            'component_name' => ['nullable', 'string', 'max:160'],
            'quantity' => ['nullable', 'numeric'],
            'condition' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'string', 'max:40'],
            'is_active' => ['nullable'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::table('facility_components')->insert([
            'facility_id' => (int) $data['facility_id'],
            'component_type' => trim($data['component_type']),
            'component_name' => $this->nullableTrim($data['component_name'] ?? null),
            'quantity' => (float) ($data['quantity'] ?? 1),
            'condition' => $this->nullableTrim($data['condition'] ?? null),
            'status' => $this->nullableTrim($data['status'] ?? null) ?: 'ACTIVE',
            'is_active' => $request->boolean('is_active', true) ? 1 : 0,
            'notes' => $this->nullableTrim($data['notes'] ?? null),
            'created_by' => $this->actor(),
            'updated_by' => $this->actor(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/facilities-management/registry')->with('status', 'Facility component added.');
    }

    public function storeServiceRequest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'request_type' => ['required', 'in:'.implode(',', self::REQUEST_TYPES)],
            'facility_registry_id' => ['nullable', 'integer'],
            'facility_component_id' => ['nullable', 'integer'],
            'facility_component_type_id' => ['required', 'integer'],
            'requester_employee_id' => ['required', 'string', 'max:40'],
            'location_text' => ['nullable', 'string'],
            'work_category_id' => ['required', 'integer'],
            'problem_description' => ['required', 'string'],
            'priority' => ['required', 'in:'.implode(',', self::PRIORITIES)],
            'emergency_flag' => ['nullable'],
            'emergency_reason' => ['nullable', 'string'],
            'material_required' => ['nullable'],
            'material_remarks' => ['nullable', 'string'],
            'estimated_cost' => ['nullable', 'numeric'],
            'approval_required_level' => ['required', 'in:'.implode(',', self::APPROVAL_LEVELS)],
        ]);

        $facilityId = $this->nullableInt($data['facility_registry_id'] ?? null);
        $componentId = $this->nullableInt($data['facility_component_id'] ?? null);
        $componentTypeId = $this->nullableInt($data['facility_component_type_id'] ?? null);

        if (!$componentTypeId || !DB::table('facility_component_types')
            ->where('id', $componentTypeId)
            ->where('is_active', 1)
            ->exists()) {
            return back()->withErrors([
                'facility_component_type_id' => 'Select a valid affected component / item.',
            ])->withInput();
        }

        $requester = DB::table('employees_master')
            ->where('company_id', trim($data['requester_employee_id']))
            ->where('active', 'Yes')
            ->first();

        if (!$requester) {
            return back()->withErrors([
                'requester_employee_id' => 'Select a valid active requester employee ID.',
            ])->withInput();
        }

        if (!$facilityId && $this->nullableTrim($data['location_text'] ?? null) === null) {
            return back()->withErrors(['location_text' => 'Location text is required when no registered facility is selected.'])->withInput();
        }

        if ($componentId) {
            $componentBelongsToFacility = $facilityId
                && DB::table('facility_components')
                    ->where('id', $componentId)
                    ->where('facility_id', $facilityId)
                    ->where('is_active', 1)
                    ->exists();

            if (!$componentBelongsToFacility) {
                return back()->withErrors([
                    'facility_component_id' => 'Select an installed component belonging to the selected facility.',
                ])->withInput();
            }
        }

        if ($request->boolean('emergency_flag') && $this->nullableTrim($data['emergency_reason'] ?? null) === null) {
            return back()->withErrors(['emergency_reason' => 'Emergency reason is required for emergency requests.'])->withInput();
        }

        DB::transaction(function () use ($data, $request, $facilityId, $componentId, $componentTypeId, $requester): void {
            $id = DB::table('facility_service_requests')->insertGetId([
                'request_no' => 'FSR-PENDING-'.uniqid(),
                'request_type' => $data['request_type'],
                'facility_registry_id' => $facilityId,
                'facility_component_id' => $componentId,
                'facility_component_type_id' => $componentTypeId,
                'location_text' => $this->nullableTrim($data['location_text'] ?? null),
                'work_category_id' => (int) $data['work_category_id'],
                'problem_description' => trim($data['problem_description']),
                'priority' => $data['priority'],
                'emergency_flag' => $request->boolean('emergency_flag') ? 1 : 0,
                'emergency_reason' => $this->nullableTrim($data['emergency_reason'] ?? null),
                'requested_by_user_id' => $this->actor(),
                'requested_at' => now(),
                'requester_employee_id' => $requester->company_id,
                'requester_name_snapshot' => $requester->name,
                'requester_designation_snapshot' => $requester->designation,
                'requester_department_snapshot' => $requester->department,
                'requester_section_snapshot' => $requester->section,
                'requester_sub_section_snapshot' => $requester->sub_section,
                'requester_mobile_no_snapshot' => $requester->mobile_no,
                'status' => 'SUBMITTED',
                'material_required' => $request->boolean('material_required') ? 1 : 0,
                'material_remarks' => $this->nullableTrim($data['material_remarks'] ?? null),
                'estimated_cost' => $data['estimated_cost'] ?? null,
                'approval_required_level' => $data['approval_required_level'],
                'created_by_user_id' => $this->actor(),
                'updated_by_user_id' => $this->actor(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('facility_service_requests')->where('id', $id)->update(['request_no' => sprintf('FSR-%06d', $id)]);
        });

        return redirect('/facilities-management/service-requests')->with('status', 'Service request submitted.');
    }

    public function approveRequest(Request $request, int $id): RedirectResponse
    {
        $remarks = $this->nullableTrim($request->input('approval_remarks'));
        $actor = $this->actor();

        DB::transaction(function () use ($id, $remarks, $actor): void {
            $row = DB::table('facility_service_requests')->where('id', $id)->first();
            abort_if(!$row, 404);
            abort_if(!in_array($row->status, ['SUBMITTED', 'UNDER_REVIEW'], true), 422, 'Only submitted/under-review requests may be approved.');
            if ($actor !== '' && (string) $row->requested_by_user_id === $actor) {
                throw ValidationException::withMessages([
                    'approval' => 'You cannot approve a request created by yourself. Please use another authorized approver.',
                ]);
            }

            DB::table('facility_service_requests')->where('id', $id)->update([
                'status' => 'APPROVED',
                'reviewed_by_user_id' => $actor,
                'reviewed_at' => now(),
                'approval_decision' => 'APPROVED',
                'approval_remarks' => $remarks,
                'updated_by_user_id' => $actor,
                'updated_at' => now(),
            ]);
            DB::table('facility_request_approvals')->insert([
                'facility_service_request_id' => $id,
                'decision' => 'APPROVED',
                'decision_level' => $row->approval_required_level ?: 'SUPERVISOR',
                'decided_by_user_id' => $actor,
                'decided_at' => now(),
                'remarks' => $remarks,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect('/facilities-management/approval-queue')->with('status', 'Request approved.');
    }

    public function rejectRequest(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate(['rejected_reason' => ['required', 'string']]);
        $actor = $this->actor();

        DB::transaction(function () use ($id, $data, $actor): void {
            $row = DB::table('facility_service_requests')->where('id', $id)->first();
            abort_if(!$row, 404);
            abort_if(!in_array($row->status, ['SUBMITTED', 'UNDER_REVIEW'], true), 422, 'Only submitted/under-review requests may be rejected.');
            DB::table('facility_service_requests')->where('id', $id)->update([
                'status' => 'REJECTED',
                'reviewed_by_user_id' => $actor,
                'reviewed_at' => now(),
                'approval_decision' => 'REJECTED',
                'rejected_reason' => trim($data['rejected_reason']),
                'updated_by_user_id' => $actor,
                'updated_at' => now(),
            ]);
            DB::table('facility_request_approvals')->insert([
                'facility_service_request_id' => $id,
                'decision' => 'REJECTED',
                'decision_level' => $row->approval_required_level ?: 'SUPERVISOR',
                'decided_by_user_id' => $actor,
                'decided_at' => now(),
                'remarks' => trim($data['rejected_reason']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect('/facilities-management/approval-queue')->with('status', 'Request rejected.');
    }

    public function convertRequestToWorkOrder(int $id): RedirectResponse
    {
        DB::transaction(function () use ($id): void {
            $requestRow = DB::table('facility_service_requests')->where('id', $id)->first();
            abort_if(!$requestRow, 404);
            abort_if($requestRow->status !== 'APPROVED', 422, 'Only approved requests may be converted to work orders.');
            abort_if(DB::table('facility_work_orders')->where('source_request_id', $id)->exists(), 422, 'This request already has a linked work order.');

            $title = $requestRow->request_no.' - '.$requestRow->request_type;
            $workOrderId = DB::table('facility_work_orders')->insertGetId([
                'source_request_id' => $id,
                'work_order_no' => 'FWO-PENDING-'.uniqid(),
                'facility_id' => $requestRow->facility_registry_id,
                'facility_component_id' => $requestRow->facility_component_id,
                'facility_component_type_id' => $requestRow->facility_component_type_id,
                'facility_work_category_id' => $requestRow->work_category_id,
                'title' => $title,
                'work_type' => $requestRow->request_type,
                'description' => $requestRow->problem_description,
                'priority' => $requestRow->priority,
                'status' => 'OPEN',
                'reported_on' => now()->toDateString(),
                'material_required' => $requestRow->material_required ? 'Yes' : 'No',
                'material_remarks' => $requestRow->material_remarks,
                'estimated_cost' => $requestRow->estimated_cost,
                'created_by' => $this->actor(),
                'updated_by' => $this->actor(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('facility_work_orders')->where('id', $workOrderId)->update(['work_order_no' => sprintf('FWO-%06d', $workOrderId)]);
            $this->writeStatusHistory($workOrderId, null, 'OPEN', 'Converted from approved request '.$requestRow->request_no);
            DB::table('facility_service_requests')->where('id', $id)->update([
                'status' => 'CONVERTED_TO_WORK_ORDER',
                'updated_by_user_id' => $this->actor(),
                'updated_at' => now(),
            ]);
        });

        return redirect('/facilities-management/work-orders')->with('status', 'Linked work order created.');
    }

    public function transitionWorkOrder(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'to_status' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'string', 'max:160'],
            'actual_cost' => ['nullable', 'numeric'],
        ]);
        $this->applyWorkOrderTransition($id, $data['to_status'], $data);
        return back()->with('status', 'Work order status updated.');
    }

    public function verifyWorkOrder(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'verification_result' => ['required', 'in:ACCEPTED,REWORK_REQUIRED'],
            'verification_remarks' => ['nullable', 'string'],
        ]);
        if ($data['verification_result'] === 'REWORK_REQUIRED' && $this->nullableTrim($data['verification_remarks'] ?? null) === null) {
            return back()->withErrors(['verification_remarks' => 'Rework reason is required.']);
        }
        $this->applyWorkOrderTransition($id, $data['verification_result'] === 'ACCEPTED' ? 'VERIFIED' : 'REWORK_REQUIRED', [
            'remarks' => $data['verification_remarks'] ?? null,
            'verification_result' => $data['verification_result'],
            'verification_remarks' => $data['verification_remarks'] ?? null,
        ]);
        return redirect('/facilities-management/verification-closure')->with('status', 'Verification decision saved.');
    }

    public function closeWorkOrder(Request $request, int $id): RedirectResponse
    {
        $this->applyWorkOrderTransition($id, 'CLOSED', ['remarks' => $request->input('remarks')]);
        return redirect('/facilities-management/verification-closure')->with('status', 'Work order closed.');
    }

    private function applyWorkOrderTransition(int $id, string $toStatus, array $data): void
    {
        DB::transaction(function () use ($id, $toStatus, $data): void {
            $row = DB::table('facility_work_orders')->where('id', $id)->first();
            abort_if(!$row, 404);
            $from = $row->status;
            abort_if(!in_array($toStatus, self::WORK_ORDER_TRANSITIONS[$from] ?? [], true), 422, "Invalid transition {$from} -> {$toStatus}.");
            if ($toStatus === 'CANCELLED' && $this->nullableTrim($data['remarks'] ?? null) === null) {
                abort(422, 'Cancellation reason is required.');
            }
            if ($toStatus === 'VERIFIED' && $this->actor() !== '' && (string) ($row->updated_by ?? '') === $this->actor()) {
                abort(422, 'Completer/worker cannot verify own work order where user reference is available.');
            }

            $update = ['status' => $toStatus, 'updated_by' => $this->actor(), 'updated_at' => now()];
            if ($toStatus === 'ASSIGNED') {
                $update['assigned_to'] = $this->nullableTrim($data['assigned_to'] ?? null) ?: $row->assigned_to;
                $update['assigned_at'] = now();
            }
            if ($toStatus === 'IN_PROGRESS') {
                $update['started_at'] = now();
            }
            if ($toStatus === 'COMPLETED') {
                $update['completed_on'] = now()->toDateString();
                $update['completed_at'] = now();
                $update['completion_remarks'] = $this->nullableTrim($data['remarks'] ?? null);
                $update['actual_cost'] = $data['actual_cost'] ?? $row->actual_cost;
            }
            if (in_array($toStatus, ['VERIFIED', 'REWORK_REQUIRED'], true)) {
                $update['verified_on'] = now()->toDateString();
                $update['verified_by'] = $this->actor();
                $update['verified_at'] = now();
                $update['verification_result'] = $data['verification_result'] ?? ($toStatus === 'VERIFIED' ? 'ACCEPTED' : 'REWORK_REQUIRED');
                $update['verification_remarks'] = $this->nullableTrim($data['verification_remarks'] ?? $data['remarks'] ?? null);
            }
            if ($toStatus === 'CLOSED') {
                $update['closed_at'] = now();
            }
            if ($toStatus === 'CANCELLED') {
                $update['cancelled_reason'] = $this->nullableTrim($data['remarks'] ?? null);
            }
            DB::table('facility_work_orders')->where('id', $id)->update($update);
            $this->writeStatusHistory($id, $from, $toStatus, $data['remarks'] ?? null);
        });
    }

    private function overviewKpis(): array
    {
        $empty = [
            'total_registered_facilities' => 0,
            'open_service_requests' => 0,
            'pending_approvals' => 0,
            'open_work_orders' => 0,
            'critical_pending_works' => 0,
            'completed_verified_this_month' => 0,
            'today_daily_services_status' => '0 / 0',
            'pest_control_followups_due' => 0,
        ];
        if (!Schema::hasTable('facility_registries')) {
            return $empty;
        }
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $serviceTotal = Schema::hasTable('facility_daily_services') ? DB::table('facility_daily_services')->whereDate('service_date', $today)->count() : 0;
        $serviceDone = Schema::hasTable('facility_daily_services') ? DB::table('facility_daily_services')->whereDate('service_date', $today)->whereIn('status', ['COMPLETED', 'VERIFIED'])->count() : 0;
        return [
            'total_registered_facilities' => DB::table('facility_registries')->where('is_active', 1)->count(),
            'open_service_requests' => Schema::hasTable('facility_service_requests') ? DB::table('facility_service_requests')->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW', 'APPROVED'])->count() : 0,
            'pending_approvals' => Schema::hasTable('facility_service_requests') ? DB::table('facility_service_requests')->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])->count() : 0,
            'open_work_orders' => Schema::hasTable('facility_work_orders') ? DB::table('facility_work_orders')->whereIn('status', ['OPEN', 'ASSIGNED', 'IN_PROGRESS', 'REWORK_REQUIRED'])->count() : 0,
            'critical_pending_works' => Schema::hasTable('facility_work_orders') ? DB::table('facility_work_orders')->where('priority', 'CRITICAL')->whereNotIn('status', ['COMPLETED', 'VERIFIED', 'CLOSED', 'CANCELLED'])->count() : 0,
            'completed_verified_this_month' => Schema::hasTable('facility_work_orders') ? DB::table('facility_work_orders')->whereBetween('verified_on', [$monthStart, $monthEnd])->count() : 0,
            'today_daily_services_status' => $serviceDone.' / '.$serviceTotal,
            'pest_control_followups_due' => Schema::hasTable('facility_work_orders') ? DB::table('facility_work_orders as wo')->leftJoin('facility_work_categories as wc', 'wc.id', '=', 'wo.facility_work_category_id')->where('wc.name', 'Pest Control / Fumigation')->whereNotIn('wo.status', ['COMPLETED', 'VERIFIED', 'CLOSED', 'CANCELLED'])->count() : 0,
        ];
    }

    private function serviceRequestRows(Request $request)
    {
        if (!Schema::hasTable('facility_service_requests')) {
            return collect();
        }
        return DB::table('facility_service_requests as sr')
            ->leftJoin('facility_registries as f', 'f.id', '=', 'sr.facility_registry_id')
            ->leftJoin('facility_work_categories as wc', 'wc.id', '=', 'sr.work_category_id')
            ->leftJoin('facility_component_types as ct', 'ct.id', '=', 'sr.facility_component_type_id')
            ->select('sr.*', 'f.facility_code', 'f.facility_name', 'wc.name as category_name', 'ct.name as affected_component_name')
            ->when($request->query('status'), fn ($q, $v) => $q->where('sr.status', $v))
            ->when($request->query('priority'), fn ($q, $v) => $q->where('sr.priority', $v))
            ->when($request->query('work_category_id'), fn ($q, $v) => $q->where('sr.work_category_id', $v))
            ->orderByDesc('sr.id')
            ->limit(250)
            ->get();
    }

    private function pendingApprovalRows()
    {
        return Schema::hasTable('facility_service_requests')
            ? DB::table('facility_service_requests as sr')->leftJoin('facility_registries as f', 'f.id', '=', 'sr.facility_registry_id')->leftJoin('facility_work_categories as wc', 'wc.id', '=', 'sr.work_category_id')->select('sr.*', 'f.facility_code', 'f.facility_name', 'wc.name as category_name')->whereIn('sr.status', ['SUBMITTED', 'UNDER_REVIEW'])->orderByDesc('sr.priority')->orderBy('sr.id')->get()
            : collect();
    }

    private function approvalHistoryRows()
    {
        return Schema::hasTable('facility_request_approvals')
            ? DB::table('facility_request_approvals as a')->join('facility_service_requests as sr', 'sr.id', '=', 'a.facility_service_request_id')->select('a.*', 'sr.request_no')->orderByDesc('a.id')->limit(100)->get()
            : collect();
    }

    private function facilities()
    {
        return Schema::hasTable('facility_registries') ? DB::table('facility_registries')->orderBy('section')->orderBy('area')->orderBy('facility_code')->limit(200)->get() : collect();
    }

    private function components()
    {
        if (!Schema::hasTable('facility_components')) {
            return collect();
        }

        return DB::table('facility_components as c')
            ->leftJoin('facility_registries as f', 'f.id', '=', 'c.facility_id')
            ->select('c.*', 'f.facility_code', 'f.facility_name')
            ->where('c.is_active', 1)
            ->orderBy('f.facility_code')
            ->orderBy('c.component_type')
            ->get();
    }

    private function loadWorkOrders(?array $statuses = null)
    {
        if (!Schema::hasTable('facility_work_orders')) {
            return collect();
        }
        return DB::table('facility_work_orders as wo')
            ->leftJoin('facility_service_requests as sr', 'sr.id', '=', 'wo.source_request_id')
            ->leftJoin('facility_registries as f', 'f.id', '=', 'wo.facility_id')
            ->leftJoin('facility_work_categories as wc', 'wc.id', '=', 'wo.facility_work_category_id')
            ->select('wo.*', 'sr.request_no', 'f.facility_code', 'f.facility_name', 'wc.name as category_name')
            ->when($statuses, fn ($q) => $q->whereIn('wo.status', $statuses))
            ->orderByDesc('wo.id')
            ->limit(250)
            ->get();
    }

    private function statusHistoryRows()
    {
        return Schema::hasTable('facility_work_order_status_histories') ? DB::table('facility_work_order_status_histories')->orderByDesc('id')->limit(100)->get()->groupBy('facility_work_order_id') : collect();
    }

    private function loadDailyServices()
    {
        return Schema::hasTable('facility_daily_services') ? DB::table('facility_daily_services as ds')->leftJoin('facility_registries as f', 'f.id', '=', 'ds.facility_id')->select('ds.*', 'f.facility_code', 'f.facility_name')->orderByDesc('ds.service_date')->orderByDesc('ds.id')->limit(200)->get() : collect();
    }

    private function workCategories()
    {
        return Schema::hasTable('facility_work_categories') ? DB::table('facility_work_categories')->where('is_active', 1)->orderBy('sort_order')->get() : collect();
    }

    private function activeRequesterEmployees()
    {
        return Schema::hasTable('employees_master')
            ? DB::table('employees_master')
                ->where('active', 'Yes')
                ->whereNotNull('company_id')
                ->where('company_id', '!=', '')
                ->orderBy('company_id')
                ->get([
                    'company_id',
                    'name',
                    'designation',
                    'department',
                    'section',
                    'sub_section',
                    'mobile_no',
                ])
            : collect();
    }

    private function componentTypeRows()
    {
        return Schema::hasTable('facility_component_types')
            ? DB::table('facility_component_types')
                ->where('is_active', 1)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();
    }

    private function componentTypes()
    {
        return Schema::hasTable('facility_component_types') ? DB::table('facility_component_types')->where('is_active', 1)->orderBy('sort_order')->pluck('name')->all() : [];
    }

    private function writeStatusHistory(int $workOrderId, ?string $from, string $to, ?string $remarks): void
    {
        DB::table('facility_work_order_status_histories')->insert([
            'facility_work_order_id' => $workOrderId,
            'from_status' => $from,
            'to_status' => $to,
            'action_by_user_id' => $this->actor(),
            'action_at' => now(),
            'remarks' => $this->nullableTrim($remarks),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function placeholder(string $title, string $message): View
    {
        return view('facilities.placeholder', ['title' => $title, 'message' => $message]);
    }

    private function actor(): string
    {
        return (string) session('user_id', '');
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (int) $value;
    }
}
