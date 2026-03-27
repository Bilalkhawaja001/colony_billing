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
            ? redirect('/ui/dashboard')
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

    public function monthCycle()
    {
        return $this->renderUiPage('Month Cycle', '/ui/month-cycle');
    }

    public function imports()
    {
        return $this->renderUiPage('Imports', '/ui/imports');
    }

    public function billing()
    {
        return $this->renderUiPage('Billing', '/ui/billing');
    }

    public function elecSummary()
    {
        return $this->renderUiPage('Electric Summary', '/ui/elec-summary');
    }

    public function familyDetails()
    {
        return view('family-details');
    }

    public function resultsEmployeeWise()
    {
        return view('results-employee-wise');
    }

    public function resultsUnitWise()
    {
        return view('results-unit-wise');
    }

    public function logs()
    {
        return view('logs');
    }

    public function rates() { return $this->renderUiPage('Rates', '/ui/rates'); }
    public function waterMeters() { return $this->renderUiPage('Water Meters', '/ui/water-meters'); }
    public function van() { return $this->renderUiPage('VAN', '/ui/van'); }
    public function employeeMaster() { return $this->renderUiPage('Employee Master', '/ui/employee-master'); }
    public function employees() { return $this->renderUiPage('Employees', '/ui/employees'); }
    public function employeeHelper() { return $this->renderUiPage('Employee Helper', '/ui/employee-helper'); }
    public function unitMaster() { return $this->renderUiPage('Unit Master', '/ui/unit-master'); }
    public function meterMaster() { return $this->renderUiPage('Meter Master', '/ui/meter-master'); }
    public function meterRegisterIngest() { return $this->renderUiPage('Meter Register Ingest', '/ui/meter-register-ingest'); }
    public function rooms() { return $this->renderUiPage('Rooms', '/ui/rooms'); }
    public function occupancy() { return $this->renderUiPage('Occupancy', '/ui/occupancy'); }
    public function electricV1Run() { return $this->renderUiPage('Electric V1 Run', '/ui/electric-v1-run'); }
    public function electricV1Outputs() { return $this->renderUiPage('Electric V1 Outputs', '/ui/electric-v1-outputs'); }
    public function mastersEmployees() { return $this->renderUiPage('Masters -+ Employees', '/ui/masters/employees'); }
    public function mastersUnits() { return $this->renderUiPage('Masters -+ Units', '/ui/masters/units'); }
    public function mastersMeters() { return $this->renderUiPage('Masters -+ Meters', '/ui/masters/meters'); }
    public function mastersRates() { return $this->renderUiPage('Masters -+ Rates', '/ui/masters/rates'); }
    public function inputsMapping() { return $this->renderUiPage('Inputs -+ Mapping', '/ui/inputs/mapping'); }
    public function inputsHr() { return $this->renderUiPage('Inputs -+ HR', '/ui/inputs/hr'); }
    public function inputsReadings() { return $this->renderUiPage('Inputs -+ Readings', '/ui/inputs/readings'); }
    public function inputsRo() { return $this->renderUiPage('Inputs -+ RO', '/ui/inputs/ro'); }
    public function finalizedMonths() { return $this->renderUiPage('Finalized Months', '/ui/finalized-months'); }

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
