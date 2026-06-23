<?php

namespace App\Http\Controllers\Billing\ControlRoom;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\ControlRoom\ExportBillRequest;

class ExportController extends Controller
{
    public function index()
    {
        return view('billing_control.export', [
            'pageTitle' => 'Download Excel',
        ]);
    }

    public function download(ExportBillRequest $request)
    {
        return back()->with('status', 'SCAFFOLD_ONLY: Excel export disabled until real 17-column query is wired.');
    }
}
