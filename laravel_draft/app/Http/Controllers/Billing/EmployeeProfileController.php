<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\EmployeeProfileService;

class EmployeeProfileController extends Controller
{
    public function __construct(private readonly EmployeeProfileService $service)
    {
    }

    public function show(string $companyId)
    {
        $profile = $this->service->profile($companyId);

        if (($profile['_http'] ?? null) === 404) {
            abort(404, 'Employee profile not found.');
        }

        return view('ui.employee-profile', [
            'profile' => $profile,
        ]);
    }
}
