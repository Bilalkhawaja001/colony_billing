<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\FamilyRegistryResultsService;
use Illuminate\Http\Request;

class FamilyRegistryResultsController extends Controller
{
    public function __construct(private readonly FamilyRegistryResultsService $service)
    {
    }

    public function familyDetailsContext(Request $request)
    {
        $result = $this->service->familyDetailsContext($request->query());
        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function familyDetails(Request $request)
    {
        return response()->json($this->service->familyDetails($request->query()));
    }

    public function familyDetailsUpsert(Request $request)
    {
        $result = $this->service->familyDetailsUpsert($request->all());
        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function familyDetailsByEmployee(string $companyId, Request $request)
    {
        $monthCycle = trim((string) $request->query('month_cycle', ''));
        $payload = ['company_id' => $companyId];
        if ($monthCycle !== '') {
            $payload['month_cycle'] = $monthCycle;
        }

        $result = $this->service->familyDetails($payload);
        $rows = $result['rows'] ?? [];
        $row = $rows[0] ?? null;
        $children = $row['children'] ?? [];

        $response = [
            'status' => 'ok',
            'family' => $row,
            'children' => $children,
        ];

        return response()->json($response, 200);
    }

    public function registryEmployeesUpsert(Request $request)
    {
        $result = $this->service->registryEmployeesUpsert($request->all());
        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function registryEmployeeGet(string $companyId)
    {
        $result = $this->service->registryEmployeeGet($companyId);
        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function registryEmployeeGetLiteral()
    {
        $result = $this->service->registryEmployeeGet('');
        $code = (int) ($result['_http'] ?? 404);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function registryEmployeesImportPreview(Request $request)
    {
        $result = $this->service->registryEmployeesImportPreview((string) $request->input('csv_text', ''));
        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function registryEmployeesImportCommit(Request $request)
    {
        $result = $this->service->registryEmployeesImportCommit((string) $request->input('csv_text', ''));
        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $code);
    }

    public function registryEmployeesPromoteToMaster(Request $request)
    {
        return response()->json($this->service->registryEmployeesPromoteToMaster((bool) $request->boolean('upsert')));
    }

    public function resultsEmployeeWise(Request $request)
    {
        return response()->json($this->service->resultsEmployeeWise((string) $request->query('month_cycle', '')));
    }

    public function resultsUnitWise(Request $request)
    {
        return response()->json($this->service->resultsUnitWise((string) $request->query('month_cycle', '')));
    }

    public function logs(Request $request)
    {
        return response()->json($this->service->logs((string) $request->query('month_cycle', '')));
    }
}
