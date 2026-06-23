<?php

namespace App\Http\Controllers\Billing\ControlRoom;

use App\Http\Controllers\Controller;
use App\Services\Billing\ControlRoom\ReadinessService;
use Illuminate\Http\Request;

class ControlRoomController extends Controller
{
    public function index(Request $request, ReadinessService $readiness)
    {
        return view('billing_control.control_room', [
            'pageTitle' => 'Colony Billing Control Room',
            'readiness' => $readiness->summary($request->query('month_cycle')),
        ]);
    }
}
