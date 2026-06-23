<?php

namespace App\Http\Controllers\Billing\ControlRoom;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\ControlRoom\SaveRoomAssignmentRequest;

class RoomAssignmentController extends Controller
{
    public function index()
    {
        return view('billing_control.rooms', [
            'pageTitle' => 'Fix Room Assignment',
            'rows' => [],
        ]);
    }

    public function save(SaveRoomAssignmentRequest $request)
    {
        if (is_array($request->input('rows'))) {
            return back()->withErrors(['rows' => 'Bulk payload rejected. Save one row at a time.']);
        }

        return back()->with('status', 'SCAFFOLD_ONLY: room assignment save disabled until Phase 2 real service wiring.');
    }
}
