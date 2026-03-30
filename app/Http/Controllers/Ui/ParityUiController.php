<?php

namespace App\Http\Controllers\Ui;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardParityService;
use Illuminate\Http\Request;

class ParityUiController extends Controller
{
    public function __construct(private readonly DashboardParityService $dashboard)
    {
    }

    private function renderUiPage(string $title, string $path)
    {
        return view('ui.page', [
            'title' => $title,
            'path' => $path,
        ]);
    }

    public function home()
    {
        return session()->has('user_id')
            ? redirect('/dashboard')
            : redirect('/login');
    }

    public function dashboard(Request $request)
    {
        $month = $this->dashboard->resolveMonthCycle($request->query('month_cycle'));

        return view('ui.dashboard', [
            'monthCycle' => $month,
            'kpis' => $this->dashboard->colonyKpis($month)['kpis'] ?? [],
            'familyRows' => $this->dashboard->familyMembers($month)['rows'] ?? [],
            'vanRows' => $this->dashboard->vanKids($month)['rows'] ?? [],
        ]);
    }

    public function reports(Request $request)
    {
        $data = $this->dashboard->reportsSummary($request->query('month_cycle'));

        return view('ui.reports', [
            'monthCycle' => $data['month_cycle'] ?? null,
            'rows' => $data['rows'] ?? [],
        ]);
    }

    public function reconciliation(Request $request)
    {
        $data = $this->dashboard->reconciliation($request->query('month_cycle'));

        return view('ui.reconciliation', [
            'monthCycle' => $data['month_cycle'] ?? null,
            'rows' => $data['rows'] ?? [],
        ]);
    }

    public function monthControl()
    {
        $data = $this->dashboard->monthControl();

        return view('ui.month-control', [
            'rows' => $data['rows'] ?? [],
        ]);
    }

    public function monthCycle(Request $request)
    {
        return view('ui.month-cycle', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
            'rows' => $this->dashboard->monthControl()['rows'] ?? [],
        ]);
    }

    public function imports(Request $request)
    {
        return view('ui.imports', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function billing(Request $request)
    {
        return view('ui.billing', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function elecSummary(Request $request)
    {
        return view('ui.elec-summary', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
            'unitId' => (string)($request->query('unit_id') ?? ''),
        ]);
    }

    public function familyDetails(Request $request)
    {
        return view('family-details', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
            'companyId' => (string)($request->query('company_id') ?? ''),
        ]);
    }

    public function resultsEmployeeWise(Request $request)
    {
        return view('results-employee-wise', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function resultsUnitWise(Request $request)
    {
        return view('results-unit-wise', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function logs(Request $request)
    {
        return view('logs', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function rates(Request $request)
    {
        return view('ui.rates', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }
    public function waterMeters(Request $request)
    {
        return view('ui.water-meters', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function van(Request $request)
    {
        return view('ui.van', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }
    public function employeeMaster() { return view('ui.employee-master'); }
    public function employees() { return view('ui.employees'); }
    public function employeeHelper() { return view('ui.employee-helper'); }
    public function unitMaster() { return view('ui.unit-master'); }
    public function metersHub()
    {
        return view('ui.meters-hub');
    }

    public function meterRegistry()
    {
        return view('ui.meter-registry');
    }

    public function meterReadings()
    {
        return view('ui.meter-readings');
    }

    public function waterTools(Request $request)
    {
        return view('ui.water-tools', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
        ]);
    }

    public function meterMaster() { return view('ui.meter-master'); }
    public function meterRegisterIngest() { return view('ui.meter-register-ingest'); }
    public function rooms() { return view('ui.rooms'); }
    public function occupancy() { return view('ui.occupancy'); }
    public function electricV1Run() { return $this->renderUiPage('Electric V1 Run', '/ui/electric-v1-run'); }
    public function electricV1Outputs() { return $this->renderUiPage('Electric V1 Outputs', '/ui/electric-v1-outputs'); }
    public function mastersEmployees() { return view('ui.employee-master'); }
    public function mastersUnits() { return view('ui.unit-master'); }
    public function mastersMeters() { return view('ui.meter-master'); }
    public function mastersRates() { return view('ui.rates'); }
    public function inputsMapping() { return view('ui.inputs-mapping'); }
    public function inputsHr() { return view('ui.inputs-hr'); }
    public function inputsReadings() { return view('ui.inputs-readings'); }
    public function inputsRo() { return view('ui.inputs-ro'); }
    public function finalizedMonths(Request $request)
    {
        return view('ui.finalized-months', [
            'monthCycle' => (string)($request->query('month_cycle') ?? ''),
            'rows' => $this->dashboard->monthControl()['rows'] ?? [],
        ]);
    }

    public function colonyKpis(Request $request)
    {
        return response()->json($this->dashboard->colonyKpis($request->query('month_cycle')));
    }

    public function familyMembers(Request $request)
    {
        return response()->json($this->dashboard->familyMembers($request->query('month_cycle')));
    }

    public function vanKids(Request $request)
    {
        return response()->json($this->dashboard->vanKids($request->query('month_cycle')));
    }
}
