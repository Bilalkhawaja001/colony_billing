<?php

namespace App\Http\Controllers\Billing\V2;

use App\Http\Controllers\Controller;
use App\Models\BillRun;
use App\Services\Billing\V2\BillRunPreflightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillRunPreflightController extends Controller
{
    public function show(Request $request, BillRunPreflightService $service): JsonResponse
    {
        $run = BillRun::query()->findOrFail((int) $request->query('bill_run_id'));

        return response()->json($service->evaluate($run));
    }

    public function save(Request $request, BillRunPreflightService $service): JsonResponse
    {
        $run = BillRun::query()->findOrFail((int) $request->input('bill_run_id'));
        $result = $service->evaluate($run);
        $saved = $service->saveResult($run, $result);

        return response()->json([
            'status' => 'ok',
            'saved_checks' => $saved,
            'result' => $result,
        ]);
    }
}
