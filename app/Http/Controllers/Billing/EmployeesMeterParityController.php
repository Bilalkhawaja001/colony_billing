<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\EmployeesMeterParityService;
use Illuminate\Http\Request;

class EmployeesMeterParityController extends Controller
{
    public function __construct(private readonly EmployeesMeterParityService $service)
    {
    }

    public function employees(Request $request)
    {
        return response()->json($this->service->employees($request->query()));
    }

    public function employeesSearch(Request $request)
    {
        return $this->jsonFromService($this->service->employeesSearch($request->query()));
    }

    public function employeeGet(string $companyId)
    {
        return $this->jsonFromService($this->service->employeeGet($companyId));
    }

    public function employeesDepartments()
    {
        return response()->json($this->service->employeesDepartments());
    }

    public function employeesImport(Request $request)
    {
        return $this->jsonFromService($this->service->employeesImport($request->all()));
    }

    public function employeesUpsert(Request $request)
    {
        return $this->jsonFromService($this->service->employeesUpsert($request->all()));
    }

    public function employeesAdd(Request $request)
    {
        return $this->jsonFromService($this->service->employeesAdd($request->all()));
    }

    public function employeePatch(Request $request, string $companyId)
    {
        return $this->jsonFromService($this->service->employeePatch($companyId, $request->all()));
    }

    public function employeeDelete(string $companyId)
    {
        return $this->jsonFromService($this->service->employeeDelete($companyId));
    }

    public function meterReadingLatest(string $unitId)
    {
        return $this->jsonFromService($this->service->meterReadingLatest($unitId));
    }

    public function meterReadingUpsert(Request $request)
    {
        return $this->jsonFromService($this->service->meterReadingUpsert($request->all()));
    }

    public function meterUnit(Request $request)
    {
        return response()->json($this->service->meterUnit($request->query()));
    }

    public function meterUnitUpsert(Request $request)
    {
        return $this->jsonFromService($this->service->meterUnitUpsert($request->all()));
    }

    public function roomsCascade(Request $request)
    {
        return $this->jsonFromService($this->service->roomsCascade($request->all()));
    }

    private function jsonFromService(array $result)
    {
        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);

        return response()->json($result, $code);
    }
}
