<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\ResidencyMasterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResidencyMasterController extends Controller
{
    public function index(): View
    {
        return view('ui.residency-master');
    }

    public function list(Request $request, ResidencyMasterService $service): JsonResponse
    {
        return response()->json($service->list($request->only([
            'residence_type',
            'colony_type',
            'block_floor',
            'unit_id',
            'room_no',
            'unit_active',
            'occupancy_status',
        ])));
    }
}
