<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\EmployeeProfileService;
use Illuminate\Http\Request;

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

    public function recordFamilyMovement(Request $request, string $companyId, int $familyMemberId)
    {
        $result = $this->service->recordFamilyMovement($companyId, $familyMemberId, [
            'movement_type' => $request->input('movement_type'),
            'movement_date' => $request->input('movement_date'),
            'remarks' => $request->input('remarks'),
            'created_by' => (string) session('user_id', ''),
        ]);

        if (($result['status'] ?? 'error') !== 'ok') {
            return redirect()
                ->to('/employee-profile/' . rawurlencode($companyId))
                ->withErrors(['family_movement' => $result['error'] ?? 'Family movement could not be recorded.']);
        }

        return redirect()
            ->to('/employee-profile/' . rawurlencode($companyId))
            ->with('status', $result['message']);
    }
}
