<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\EmployeeProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeProfileController extends Controller
{
    public function __construct(private readonly EmployeeProfileService $service)
    {
    }

    public function show(Request $request, string $companyId): View|JsonResponse
    {
        $profile = $this->service->profile($companyId);
        $httpCode = (int) ($profile['_http'] ?? 200);
        unset($profile['_http']);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($profile, $httpCode);
        }

        if ($httpCode === 404) {
            abort(404, 'Employee profile not found.');
        }

        return view('ui.employee-profile', [
            'profile' => $profile,
        ]);
    }

    public function storeFamilyMember(Request $request, string $companyId): JsonResponse|RedirectResponse
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

        return $this->actionResponse($request, $companyId, $result, 'family_member');
    }

    public function updateFamilyMember(Request $request, string $companyId, int $familyMemberId): JsonResponse|RedirectResponse
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

        return $this->actionResponse($request, $companyId, $result, 'family_member');
    }

    public function assignResidence(Request $request, string $companyId): JsonResponse|RedirectResponse
    {
        $result = $this->service->assignResidence($companyId, [
            'unit_id' => $request->input('unit_id'),
            'room_no' => $request->input('room_no'),
            'effective_date' => $request->input('effective_date'),
            'remarks' => $request->input('remarks'),
            'created_by' => (string) session('user_id', ''),
        ]);

        return $this->actionResponse($request, $companyId, $result, 'residence_action');
    }

    public function shiftResidence(Request $request, string $companyId): JsonResponse|RedirectResponse
    {
        $result = $this->service->shiftResidence($companyId, [
            'unit_id' => $request->input('unit_id'),
            'room_no' => $request->input('room_no'),
            'effective_date' => $request->input('effective_date'),
            'remarks' => $request->input('remarks'),
            'created_by' => (string) session('user_id', ''),
        ]);

        return $this->actionResponse($request, $companyId, $result, 'residence_action');
    }

    public function vacateResidence(Request $request, string $companyId): JsonResponse|RedirectResponse
    {
        $result = $this->service->vacateResidence($companyId, [
            'effective_date' => $request->input('effective_date'),
            'remarks' => $request->input('remarks'),
            'created_by' => (string) session('user_id', ''),
        ]);

        return $this->actionResponse($request, $companyId, $result, 'residence_action');
    }

    public function recordFamilyMovement(Request $request, string $companyId, int $familyMemberId): JsonResponse|RedirectResponse
    {
        $result = $this->service->recordFamilyMovement($companyId, $familyMemberId, [
            'movement_type' => $request->input('movement_type'),
            'movement_date' => $request->input('movement_date'),
            'remarks' => $request->input('remarks'),
            'created_by' => (string) session('user_id', ''),
        ]);

        return $this->actionResponse($request, $companyId, $result, 'family_movement');
    }

    private function actionResponse(
        Request $request,
        string $companyId,
        array $result,
        string $errorBagKey
    ): JsonResponse|RedirectResponse {
        $httpCode = (int) ($result['_http'] ?? 200);
        unset($result['_http']);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result, $httpCode);
        }

        if (($result['status'] ?? 'error') !== 'ok') {
            return redirect()
                ->to('/employee-profile/' . rawurlencode($companyId))
                ->withErrors([$errorBagKey => $result['error'] ?? 'Action could not be completed.']);
        }

        return redirect()
            ->to('/employee-profile/' . rawurlencode($companyId))
            ->with('status', $result['message'] ?? 'Action completed successfully.');
    }
}
