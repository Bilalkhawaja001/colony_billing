<?php

namespace App\Http\Controllers\Billing\V2;

use App\Http\Controllers\Controller;
use App\Services\Billing\V2\PeopleResidencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeopleResidencyController extends Controller
{
    public function __construct(private readonly PeopleResidencyService $service)
    {
    }

    public function employees(Request $request): JsonResponse
    {
        return $this->respond($this->service->employees($request->query()));
    }

    public function createEmployee(Request $request): JsonResponse
    {
        return $this->respond($this->service->createEmployee($request->all()));
    }

    public function updateEmployee(Request $request, string $companyId): JsonResponse
    {
        return $this->respond($this->service->updateEmployee($companyId, $request->all()));
    }

    public function importEmployees(Request $request): JsonResponse
    {
        return $this->respond($this->service->importEmployees((string) $request->input('csv_text', '')));
    }

    public function profile(string $companyId): JsonResponse
    {
        return $this->respond($this->service->profile($companyId));
    }

    public function families(Request $request): JsonResponse
    {
        return $this->respond($this->service->families($request->query()));
    }

    public function createFamilyMember(Request $request, string $companyId): JsonResponse
    {
        return $this->respond($this->service->createFamilyMember($companyId, $request->all()));
    }

    public function occupancy(Request $request): JsonResponse
    {
        return $this->respond($this->service->occupancy($request->query()));
    }

    public function assignResidence(Request $request, string $companyId): JsonResponse
    {
        return $this->respond($this->service->assignResidence($companyId, $this->withUser($request)));
    }

    public function shiftResidence(Request $request, string $companyId): JsonResponse
    {
        return $this->respond($this->service->shiftResidence($companyId, $this->withUser($request)));
    }

    public function vacateResidence(Request $request, string $companyId): JsonResponse
    {
        return $this->respond($this->service->vacateResidence($companyId, $this->withUser($request)));
    }

    public function registryGet(string $companyId): JsonResponse
    {
        return $this->respond($this->service->registryGet($companyId));
    }

    public function registryUpsert(Request $request): JsonResponse
    {
        return $this->respond($this->service->registryUpsert($request->all()));
    }

    public function registryPreview(Request $request): JsonResponse
    {
        return $this->respond($this->service->registryPreview((string) $request->input('csv_text', '')));
    }

    public function registryCommit(Request $request): JsonResponse
    {
        return $this->respond($this->service->registryCommit((string) $request->input('csv_text', '')));
    }

    public function residenceTypes(): JsonResponse
    {
        return $this->respond($this->service->residenceTypes());
    }

    public function colonies(Request $request): JsonResponse
    {
        return $this->respond($this->service->colonies(trim((string) $request->query('residence_type', ''))));
    }

    public function blocks(Request $request, string $colony): JsonResponse
    {
        return $this->respond($this->service->blocks(urldecode($colony), trim((string) $request->query('residence_type', ''))));
    }

    public function blocksQuery(Request $request): JsonResponse
    {
        return $this->respond($this->service->blocks(
            trim((string) $request->query('colony', '')),
            trim((string) $request->query('residence_type', ''))
        ));
    }

    public function rooms(Request $request, string $colony, string $block): JsonResponse
    {
        return $this->respond($this->service->rooms(urldecode($colony), urldecode($block), trim((string) $request->query('residence_type', ''))));
    }

    public function roomsQuery(Request $request): JsonResponse
    {
        return $this->respond($this->service->rooms(
            trim((string) $request->query('colony', '')),
            trim((string) $request->query('block', '')),
            trim((string) $request->query('residence_type', ''))
        ));
    }

    private function withUser(Request $request): array
    {
        return array_merge($request->all(), ['created_by' => (string) session('user_id', '')]);
    }

    private function respond(array $result): JsonResponse
    {
        $http = (int) ($result['_http'] ?? 200);
        unset($result['_http']);
        return response()->json($result, $http);
    }
}
