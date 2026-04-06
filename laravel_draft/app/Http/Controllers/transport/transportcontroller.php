<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transport\TransportAdjustmentUpsertRequest;
use App\Http\Requests\Transport\TransportFuelEntryUpsertRequest;
use App\Http\Requests\Transport\TransportRentEntryUpsertRequest;
use App\Http\Requests\Transport\TransportVehicleUpsertRequest;
use App\Services\Transport\TransportService;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    public function __construct(private readonly TransportService $service)
    {
    }

    public function summary(Request $request)
    {
        $result = $this->service->summary((string) $request->query('month_cycle', ''));
        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);

        return response()->json($result, $code);
    }

    public function vehicleUpsert(TransportVehicleUpsertRequest $request)
    {
        return $this->jsonResult($this->service->vehicleUpsert($request->validated()));
    }

    public function rentEntryUpsert(TransportRentEntryUpsertRequest $request)
    {
        return $this->jsonResult($this->service->rentEntryUpsert($request->validated()));
    }

    public function fuelEntryUpsert(TransportFuelEntryUpsertRequest $request)
    {
        return $this->jsonResult($this->service->fuelEntryUpsert($request->validated()));
    }

    public function adjustmentUpsert(TransportAdjustmentUpsertRequest $request)
    {
        return $this->jsonResult($this->service->adjustmentUpsert($request->validated()));
    }

    public function exportCsv(Request $request)
    {
        $result = $this->service->exportCsv((string) $request->query('month_cycle', ''));
        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);

        if ($code !== 200) {
            return response()->json($result, $code);
        }

        return response($result['content'], 200, [
            'Content-Type' => $result['content_type'],
            'Content-Disposition' => 'attachment; filename="'.$result['filename'].'"',
        ]);
    }

    public function childMonthUsage(Request $request)
    {
        return $this->jsonResult($this->service->childMonthUsage((string) $request->query('month_cycle', '')));
    }

    public function childMonthUsageUpsert(Request $request)
    {
        return $this->jsonResult($this->service->childMonthUsageUpsert($request->all()));
    }

    private function jsonResult(array $result)
    {
        $code = (int) ($result['_http'] ?? 200);
        unset($result['_http']);

        return response()->json($result, $code);
    }
}
