<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\HrActiveWorkbookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HrActiveWorkbookController extends Controller
{
    public function upload(Request $request, HrActiveWorkbookService $service)
    {
        $request->validate([
            'month_cycle' => ['required', 'string', 'max:20'],
            'upload_file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:51200'],
        ]);

        try {
            return response()->json(
                $service->importUploadedFile(
                    $request->file('upload_file'),
                    (string) $request->input('month_cycle')
                )
            );
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function reference(Request $request, HrActiveWorkbookService $service)
    {
        $companyId = trim((string) $request->query('company_id', ''));

        if ($companyId === '') {
            return response()->json([
                'status' => 'error',
                'error' => 'company_id is required',
            ], 422);
        }

        $existsInMaster = false;

        if (Schema::hasTable('employees_master')) {
            $existsInMaster = DB::table('employees_master')
                ->where('company_id', $companyId)
                ->exists();
        }

        if (!$existsInMaster && Schema::hasTable('Employees_Master')) {
            $existsInMaster = DB::table('Employees_Master')
                ->where('CompanyID', $companyId)
                ->exists();
        }

        if ($existsInMaster) {
            return response()->json([
                'status' => 'ok',
                'mode' => 'reference_only',
                'company_id' => $companyId,
                'employee_exists' => true,
                'reference_allowed' => false,
                'message' => 'Employee already exists. HR workbook reference is not applied to existing employees.',
                'row' => null,
            ]);
        }

        $row = $service->latestEmployeeReference($companyId);

        if (!$row) {
            return response()->json([
                'status' => 'not_found',
                'mode' => 'reference_only',
                'company_id' => $companyId,
                'employee_exists' => false,
                'reference_allowed' => true,
                'message' => 'No HR workbook reference found for this new CompanyID.',
                'row' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'ok',
            'mode' => 'reference_only',
            'company_id' => $companyId,
            'employee_exists' => false,
            'reference_allowed' => true,
            'message' => 'New employee reference found from HR workbook.',
            'row' => $row,
        ]);
    }

    public function recent()
    {
        return response()->json([
            'status' => 'ok',
            'uploads' => DB::table('hr_active_workbook_uploads')
                ->orderByDesc('id')
                ->limit(10)
                ->get(),
        ]);
    }
}
