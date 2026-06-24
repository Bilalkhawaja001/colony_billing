<?php

namespace App\Http\Controllers\Billing\V2;

use App\Http\Controllers\Controller;
use App\Models\BillRun;
use App\Services\Billing\V2\BillRunGateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillRunGateController extends Controller
{
    public function show(Request $request, BillRunGateService $service): JsonResponse
    {
        $run = BillRun::query()->findOrFail((int) $request->query('bill_run_id'));
        $role = (string) session('role', 'VIEWER');

        return response()->json([
            'bill_run_id' => $run->id,
            'status' => $run->status,
            'role' => $role,
            'allowed_actions' => $service->allowedActions($run, $role),
        ]);
    }

    public function transition(Request $request, BillRunGateService $service): JsonResponse
    {
        $result = $service->transition(
            (int) $request->input('bill_run_id'),
            (string) $request->input('action'),
            (string) session('role', 'VIEWER'),
            session('user_id') ? (int) session('user_id') : null,
            $request->input('reason') ? (string) $request->input('reason') : null
        );

        return response()->json($result);
    }
}
