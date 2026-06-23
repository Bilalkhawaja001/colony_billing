<?php

namespace App\Http\Controllers\Billing\ControlRoom;

use App\Http\Controllers\Controller;
use App\Services\Billing\ControlRoom\ReadinessService;
use Illuminate\Http\Request;

class ReadinessController extends Controller
{
    public function index(Request $request, ReadinessService $readiness)
    {
        $data = $readiness->summary();

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return view('billing_control.readiness', [
            'pageTitle' => 'Check Readiness',
            'readiness' => $data,
        ]);
    }
}
