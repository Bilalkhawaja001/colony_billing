<?php

namespace App\Http\Controllers\Facilities;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class FacilitiesController extends Controller
{
    private const FACILITY_TYPES = [
        'Washroom', 'Toilet / Bath Area', 'Office', 'Colony Block', 'Guest House', 'Market Area',
        'Masjid Area', 'RO Plant', 'Boiler Area', 'Garden / Fountain', 'Gate', 'Workshop',
        'Common Area', 'Other Physical Facility',
    ];

    private const CONDITIONS = ['Good', 'Average', 'Poor', 'Closed', 'Under Repair'];
    private const FACILITY_STATUSES = ['OPEN', 'OPERATIONAL', 'UNDER_REPAIR', 'CLOSED'];
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
        return $this->placeholder('Inspections', 'Inspection schedules and condition checklists will use Facility Registry and Components in Phase 2.');
    }

    public function workOrders(): View
    {
        return view('facilities.work-orders', [
            'rows' => $this->loadWorkOrders(),
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
        return $this->placeholder('Verification & Closure', 'Verified completion, closure evidence and reopen controls are deferred to Phase 2.');
    }

    public function reports(): View
    {
        return $this->placeholder('Reports', 'Facilities reports will be built from registry, inspections, work orders, services and closure records in Phase 2.');
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
                'updated_by' => (string) session('user_id', ''),
                'created_by' => (string) session('user_id', ''),
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
            'created_by' => (string) session('user_id', ''),
            'updated_by' => (string) session('user_id', ''),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/facilities-management/registry')->with('status', 'Facility component added.');
    }

    private function overviewKpis(): array
    {
        $empty = [
            'total_registered_facilities' => 0,
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

        $serviceTotal = Schema::hasTable('facility_daily_services')
            ? DB::table('facility_daily_services')->whereDate('service_date', $today)->count()
            : 0;
        $serviceDone = Schema::hasTable('facility_daily_services')
            ? DB::table('facility_daily_services')->whereDate('service_date', $today)->whereIn('status', ['COMPLETED', 'VERIFIED'])->count()
            : 0;

        return [
            'total_registered_facilities' => DB::table('facility_registries')->where('is_active', 1)->count(),
            'open_work_orders' => Schema::hasTable('facility_work_orders')
                ? DB::table('facility_work_orders')->whereIn('status', ['OPEN', 'IN_PROGRESS', 'PENDING'])->count()
                : 0,
            'critical_pending_works' => Schema::hasTable('facility_work_orders')
                ? DB::table('facility_work_orders')->where('priority', 'CRITICAL')->whereNotIn('status', ['COMPLETED', 'VERIFIED', 'CLOSED'])->count()
                : 0,
            'completed_verified_this_month' => Schema::hasTable('facility_work_orders')
                ? DB::table('facility_work_orders')->whereBetween('verified_on', [$monthStart, $monthEnd])->count()
                : 0,
            'today_daily_services_status' => $serviceDone.' / '.$serviceTotal,
            'pest_control_followups_due' => Schema::hasTable('facility_work_orders')
                ? DB::table('facility_work_orders as wo')
                    ->leftJoin('facility_work_categories as wc', 'wc.id', '=', 'wo.facility_work_category_id')
                    ->where('wc.name', 'Pest Control / Fumigation')
                    ->whereNotIn('wo.status', ['COMPLETED', 'VERIFIED', 'CLOSED'])
                    ->count()
                : 0,
        ];
    }

    private function facilities()
    {
        return Schema::hasTable('facility_registries')
            ? DB::table('facility_registries')->orderBy('section')->orderBy('area')->orderBy('facility_code')->limit(200)->get()
            : collect();
    }

    private function components()
    {
        return Schema::hasTable('facility_components')
            ? DB::table('facility_components as c')
                ->leftJoin('facility_registries as f', 'f.id', '=', 'c.facility_id')
                ->select('c.*', 'f.facility_code', 'f.facility_name')
                ->orderByDesc('c.id')
                ->limit(200)
                ->get()
            : collect();
    }

    private function loadWorkOrders()
    {
        return Schema::hasTable('facility_work_orders')
            ? DB::table('facility_work_orders as wo')
                ->leftJoin('facility_registries as f', 'f.id', '=', 'wo.facility_id')
                ->leftJoin('facility_work_categories as wc', 'wc.id', '=', 'wo.facility_work_category_id')
                ->select('wo.*', 'f.facility_code', 'f.facility_name', 'wc.name as category_name')
                ->orderByDesc('wo.id')
                ->limit(200)
                ->get()
            : collect();
    }

    private function loadDailyServices()
    {
        return Schema::hasTable('facility_daily_services')
            ? DB::table('facility_daily_services as ds')
                ->leftJoin('facility_registries as f', 'f.id', '=', 'ds.facility_id')
                ->select('ds.*', 'f.facility_code', 'f.facility_name')
                ->orderByDesc('ds.service_date')
                ->orderByDesc('ds.id')
                ->limit(200)
                ->get()
            : collect();
    }

    private function workCategories()
    {
        return Schema::hasTable('facility_work_categories')
            ? DB::table('facility_work_categories')->where('is_active', 1)->orderBy('sort_order')->get()
            : collect();
    }

    private function componentTypes()
    {
        return Schema::hasTable('facility_component_types')
            ? DB::table('facility_component_types')->where('is_active', 1)->orderBy('sort_order')->pluck('name')->all()
            : [];
    }

    private function placeholder(string $title, string $message): View
    {
        return view('facilities.placeholder', ['title' => $title, 'message' => $message]);
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
