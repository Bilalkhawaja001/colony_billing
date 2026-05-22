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

    public function storeFamilyMember(Request $request, string $companyId)
    {
        $result = $this->service->createFamilyMember($companyId, [
            'member_name' => $request->input('member_name'),
            'relation' => $request->input('relation'),
            'age' => $request->input('age'),
            'school_going' => $request->boolean('school_going'),
            'school_name' => $request->input('school_name'),
            'class_name' => $request->input('class_name'),
            'remarks' => $request->input('remarks'),
        ]);

        return $this->familyMemberRedirectResponse($companyId, $result);
    }

    public function updateFamilyMember(Request $request, string $companyId, int $familyMemberId)
    {
        $result = $this->service->updateFamilyMember($companyId, $familyMemberId, [
            'member_name' => $request->input('member_name'),
            'relation' => $request->input('relation'),
            'age' => $request->input('age'),
            'school_going' => $request->boolean('school_going'),
            'school_name' => $request->input('school_name'),
            'class_name' => $request->input('class_name'),
            'remarks' => $request->input('remarks'),
        ]);

        return $this->familyMemberRedirectResponse($companyId, $result);
    }

    private function familyMemberRedirectResponse(string $companyId, array $result)
    {
        if (($result['status'] ?? 'error') !== 'ok') {
            return redirect()
                ->to('/employee-profile/' . rawurlencode($companyId))
                ->withErrors(['family_member' => $result['error'] ?? 'Family member could not be saved.']);
        }

        return redirect()
            ->to('/employee-profile/' . rawurlencode($companyId))
            ->with('status', $result['message']);
    }

    public function assignResidence(Request $request, string $companyId)
    {
        return $this->residenceRedirectResponse($companyId, $this->service->assignResidence($companyId, [
            'unit_id' => $request->input('unit_id'),
            'room_no' => $request->input('room_no'),
            'effective_date' => $request->input('effective_date'),
            'remarks' => $request->input('remarks'),
            'created_by' => (string) session('user_id', ''),
        ]));
    }

    public function shiftResidence(Request $request, string $companyId)
    {
        return $this->residenceRedirectResponse($companyId, $this->service->shiftResidence($companyId, [
            'unit_id' => $request->input('unit_id'),
            'room_no' => $request->input('room_no'),
            'effective_date' => $request->input('effective_date'),
            'remarks' => $request->input('remarks'),
            'created_by' => (string) session('user_id', ''),
        ]));
    }

    public function vacateResidence(Request $request, string $companyId)
    {
        return $this->residenceRedirectResponse($companyId, $this->service->vacateResidence($companyId, [
            'effective_date' => $request->input('effective_date'),
            'remarks' => $request->input('remarks'),
            'created_by' => (string) session('user_id', ''),
        ]));
    }

    private function residenceRedirectResponse(string $companyId, array $result)
    {
        if (($result['status'] ?? 'error') !== 'ok') {
            return redirect()
                ->to('/employee-profile/' . rawurlencode($companyId))
                ->withErrors(['residence_action' => $result['error'] ?? 'Residence action could not be completed.']);
        }

        return redirect()
            ->to('/employee-profile/' . rawurlencode($companyId))
            ->with('status', $result['message']);
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
