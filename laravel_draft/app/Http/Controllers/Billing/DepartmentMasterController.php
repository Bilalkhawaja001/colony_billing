<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\DepartmentMasterService;
use Illuminate\Http\Request;

class DepartmentMasterController extends Controller
{
    protected DepartmentMasterService $service;

    public function __construct(DepartmentMasterService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('ui.department-master');
    }

    public function list(Request $request)
    {
        return response()->json(
            $this->service->list($request->only(['department', 'section', 'q']))
        );
    }
}
