<?php

namespace App\Http\Controllers\Billing\ControlRoom;

use App\Http\Controllers\Controller;
use App\Services\Billing\ControlRoom\ReadinessService;

class ControlRoomController extends Controller
{
    public function index(ReadinessService $readiness)
    {
        return view('billing_control.control_room', [
            'pageTitle' => 'Colony Billing Control Room',
            'readiness' => $readiness->summary(),
        ]);
    }
}
