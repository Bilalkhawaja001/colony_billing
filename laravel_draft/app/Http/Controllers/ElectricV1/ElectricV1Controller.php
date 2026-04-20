<?php

namespace App\Http\Controllers\ElectricV1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ElectricV1\{RunElectricV1Request,GetOutputsBundleRequest,GetExceptionsRequest,GetRunsRequest,UpsertAllowanceRequest,UpsertReadingsRequest,UpsertAttendanceRequest,UpsertOccupancyRequest,UpsertAdjustmentsRequest};
use App\Repositories\ElectricV1\{AllowanceRepository,ReadingsRepository,AttendanceRepository,OccupancyRepository,AdjustmentsRepository};
use App\Services\ElectricV1\{OrchestrationService,ReadService};
use Illuminate\Support\Facades\DB;

class ElectricV1Controller extends Controller
{
    public function __construct(private readonly OrchestrationService $orchestrator, private readonly ReadService $read) {}

    public function run(RunElectricV1Request $request)
    {
        $v = $request->validated();
        $billingMonthDate = (string)($v['billing_month_date'] ?? $v['cycle_start']);
        return response()->json(['status' => 'ok', 'data' => $this->orchestrator->run($billingMonthDate, $v['cycle_start'], $v['cycle_end'], (float)$v['flat_rate'])]);
    }

    public function outputs(GetOutputsBundleRequest $request)
    {
        $v = $request->validated();
        return response()->json(['status' => 'ok', 'data' => $this->read->bundle($v['cycle_start'], $v['cycle_end'], $v['run_id'] ?? null)]);
    }

    public function exceptions(GetExceptionsRequest $request)
    {
        $v = $request->validated();
        $params = [$v['cycle_start'], $v['cycle_end']];
        $sql = 'SELECT * FROM electric_v1_exception_log WHERE cycle_start_date=? AND cycle_end_date=?';
        if (!empty($v['run_id'])) { $sql .= ' AND run_id=?'; $params[] = $v['run_id']; }
        $sql .= ' ORDER BY logged_at, exception_code';
        return response()->json(['status' => 'ok', 'data' => array_map(fn($r)=>(array)$r, DB::select($sql, $params))]);
    }

    public function runs(GetRunsRequest $request)
    {
        $v = $request->validated();
        return response()->json(['status' => 'ok', 'data' => array_map(fn($r)=>(array)$r, DB::select('SELECT * FROM electric_v1_run_history WHERE cycle_start_date=? AND cycle_end_date=? ORDER BY run_start, run_id', [$v['cycle_start'], $v['cycle_end']]))]);
    }

    public function upsertAllowance(UpsertAllowanceRequest $request, AllowanceRepository $repo)
    {
        return response()->json(['status'=>'ok','upserted'=>$repo->upsertMany($request->validated('rows'))]);
    }

    public function upsertReadings(UpsertReadingsRequest $request, ReadingsRepository $repo)
    {
        return response()->json(['status'=>'ok','upserted'=>$repo->upsertMany($request->validated('rows'))]);
    }

    public function upsertAttendance(UpsertAttendanceRequest $request, AttendanceRepository $repo)
    {
        return response()->json(['status'=>'ok','upserted'=>$repo->upsertMany($request->validated('rows'))]);
    }

    public function upsertOccupancy(UpsertOccupancyRequest $request, OccupancyRepository $repo)
    {
        return response()->json(['status'=>'ok','upserted'=>$repo->upsertMany($request->validated('rows'))]);
    }

    public function upsertAdjustments(UpsertAdjustmentsRequest $request, AdjustmentsRepository $repo)
    {
        return response()->json(['status'=>'ok','upserted'=>$repo->upsertMany($request->validated('rows'))]);
    }
}
