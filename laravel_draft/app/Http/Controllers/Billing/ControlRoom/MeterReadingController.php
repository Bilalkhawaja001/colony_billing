<?php

namespace App\Http\Controllers\Billing\ControlRoom;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\ControlRoom\SaveMeterReadingsRequest;

class MeterReadingController extends Controller
{
    public function index()
    {
        return view('billing_control.readings', [
            'pageTitle' => 'Fix Meter Readings',
            'rows' => [],
        ]);
    }

    public function save(SaveMeterReadingsRequest $request)
    {
        return back()->with('status', 'SCAFFOLD_ONLY: meter reading save disabled until Phase 2 real service wiring.');
    }
}
