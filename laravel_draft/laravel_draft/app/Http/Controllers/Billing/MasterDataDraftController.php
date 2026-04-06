<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataDraftController extends Controller
{
    private const VALID_CATEGORIES = [
        'Family A+',
        'Family A',
        'Family B',
        'Family C',
        'Container',
        'Hostel',
        'Bachelor',
    ];

    public function units(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $rows = DB::table('util_unit as u')
            ->select(['u.unit_id', 'u.colony_type', 'u.block_name', 'u.room_no', 'u.is_active'])
            ->when($q !== '', function ($query) use ($q) {
                $like = "%{$q}%";
                $query->where(function ($w) use ($like) {
                    $w->where('u.unit_id', 'like', $like)
                        ->orWhere('u.colony_type', 'like', $like)
                        ->orWhere('u.block_name', 'like', $like)
                        ->orWhere('u.room_no', 'like', $like);
                });
            })
            ->orderBy('u.unit_id')
            ->get();

        return response()->json(['status' => 'ok', 'rows' => $rows]);
    }

    public function unitsUpsert(Request $request)
    {
        $unitId = trim((string) ($request->input('unit_id') ?? $request->input('Unit_ID') ?? ''));
        if ($unitId === '') {
            return response()->json(['status' => 'error', 'error' => 'unit_id required'], 400);
        }

        DB::table('util_unit')->updateOrInsert(
            ['unit_id' => $unitId],
            [
                'colony_type' => $this->nullableTrim($request->input('colony_type') ?? $request->input('Colony Type')),
                'block_name' => $this->nullableTrim($request->input('block_name') ?? $request->input('Block Floor')),
                'room_no' => $this->nullableTrim($request->input('room_no') ?? $request->input('Room No')),
                'is_active' => (int) ($request->input('is_active', 1) ?: 1),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json(['status' => 'ok', 'unit_id' => $unitId]);
    }

    public function unitsDelete(string $unitId)
    {
        DB::table('util_unit')->where('unit_id', $unitId)->update([
            'is_active' => 0,
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 'ok', 'unit_id' => $unitId, 'policy' => 'soft-delete']);
    }

    public function unitsDeleteCompat(string $unit_id)
    {
        return $this->unitsDelete($unit_id);
    }

    public function rooms(Request $request)
    {
        $monthCycle = $request->query('month_cycle');
        $unitId = $request->query('unit_id');
        $category = $request->query('category');

        $rows = DB::table('util_unit_room_snapshot')
            ->select(['id', 'month_cycle', 'unit_id', 'category', 'block_floor', 'room_no', 'created_at'])
            ->when($monthCycle, fn ($q) => $q->where('month_cycle', $monthCycle))
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderByDesc('month_cycle')
            ->orderBy('unit_id')
            ->orderBy('room_no')
            ->get();

        $counts = DB::table('util_unit_room_snapshot')
            ->selectRaw('month_cycle, unit_id, COUNT(DISTINCT room_no) AS rooms_count')
            ->when($monthCycle, fn ($q) => $q->where('month_cycle', $monthCycle))
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->groupBy('month_cycle', 'unit_id')
            ->orderByDesc('month_cycle')
            ->orderBy('unit_id')
            ->get();

        return response()->json(['status' => 'ok', 'rows' => $rows, 'rooms_count' => $counts]);
    }

    public function roomsUpsert(Request $request)
    {
        $monthCycle = trim((string) $request->input('month_cycle', ''));
        $unitId = trim((string) $request->input('unit_id', ''));
        $category = trim((string) $request->input('category', ''));
        $roomNo = trim((string) $request->input('room_no', ''));

        if ($monthCycle === '' || $unitId === '' || $category === '' || $roomNo === '') {
            return response()->json(['status' => 'error', 'error' => 'month_cycle, unit_id, category, room_no are required'], 400);
        }
        if (!in_array($category, self::VALID_CATEGORIES, true)) {
            return response()->json(['status' => 'error', 'error' => "Invalid category: {$category}"], 400);
        }

        DB::table('util_unit_room_snapshot')->updateOrInsert(
            ['month_cycle' => $monthCycle, 'unit_id' => $unitId, 'room_no' => $roomNo],
            [
                'category' => $category,
                'block_floor' => $this->nullableTrim($request->input('block_floor')),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json(['status' => 'ok']);
    }

    public function roomsDelete(int $id)
    {
        DB::table('util_unit_room_snapshot')->where('id', $id)->delete();
        return response()->json(['status' => 'ok', 'id' => $id]);
    }

    public function roomsDeleteCompat(int $row_id)
    {
        return $this->roomsDelete($row_id);
    }

    public function occupancyContext(Request $request)
    {
        $monthCycle = trim((string) $request->query('month_cycle', ''));
        $employeeId = trim((string) ($request->query('company_id') ?? $request->query('employee_id') ?? ''));

        if ($monthCycle === '' || $employeeId === '') {
            return response()->json(['status' => 'error', 'error' => 'month_cycle and company_id/employee_id are required'], 400);
        }

        $emp = DB::table('employees_master')
            ->where('company_id', $employeeId)
            ->first(['company_id', 'name', 'unit_id', 'colony_type', 'block_floor', 'room_no', 'department', 'designation']);

        if (!$emp) {
            return response()->json(['status' => 'error', 'error' => 'CompanyID not found'], 404);
        }

        $room = DB::table('util_unit_room_snapshot')
            ->where('month_cycle', $monthCycle)
            ->where('unit_id', $emp->unit_id)
            ->orderBy('id')
            ->first(['unit_id', 'category', 'block_floor', 'room_no']);

        $unitId = $room->unit_id ?? $emp->unit_id ?? '';
        $category = $room->category ?? '';
        $blockFloor = $room->block_floor ?? $emp->block_floor ?? 'N/A';
        $roomNo = $room->room_no ?? $emp->room_no ?? $unitId;

        if ($category === '' || !in_array($category, self::VALID_CATEGORIES, true)) {
            return response()->json(['status' => 'error', 'error' => 'Unable to resolve occupancy category for this employee/month. Please complete room mapping first.'], 400);
        }
        if ($unitId === '' || $roomNo === '') {
            return response()->json(['status' => 'error', 'error' => 'Unable to resolve unit/room for this employee/month. Please complete room mapping first.'], 400);
        }

        return response()->json([
            'status' => 'ok',
            'row' => [
                'month_cycle' => $monthCycle,
                'employee_id' => $employeeId,
                'employee_name' => $emp->name,
                'department' => $emp->department,
                'designation' => $emp->designation,
                'unit_id' => $unitId,
                'category' => $category,
                'block_floor' => $blockFloor,
                'room_no' => $roomNo,
            ],
        ]);
    }

    public function occupancy(Request $request)
    {
        $monthCycle = $request->query('month_cycle');
        $unitId = $request->query('unit_id');
        $category = $request->query('category');

        $rows = DB::table('util_occupancy_monthly as o')
            ->leftJoin('employees_master as e', 'e.company_id', '=', 'o.employee_id')
            ->select([
                'o.id', 'o.month_cycle', 'o.category', 'o.block_floor', 'o.room_no', 'o.unit_id',
                'o.employee_id', 'e.name as employee_name', 'e.department as department', 'e.designation as designation',
                'o.active_days', 'o.created_at', 'o.updated_at',
            ])
            ->when($monthCycle, fn ($q) => $q->where('o.month_cycle', $monthCycle))
            ->when($unitId, fn ($q) => $q->where('o.unit_id', $unitId))
            ->when($category, fn ($q) => $q->where('o.category', $category))
            ->orderByDesc('o.month_cycle')
            ->orderBy('o.unit_id')
            ->orderBy('o.employee_id')
            ->get();

        return response()->json(['status' => 'ok', 'rows' => $rows]);
    }

    public function occupancyUpsert(Request $request)
    {
        $monthCycle = trim((string) $request->input('month_cycle', ''));
        $category = trim((string) $request->input('category', ''));
        $roomNo = trim((string) $request->input('room_no', ''));
        $unitId = trim((string) $request->input('unit_id', ''));
        $employeeId = trim((string) ($request->input('employee_id') ?? $request->input('CompanyID') ?? ''));
        $activeDays = (int) ($request->input('active_days', 0) ?? 0);

        if ($monthCycle === '' || $category === '' || $roomNo === '' || $unitId === '' || $employeeId === '') {
            return response()->json(['status' => 'error', 'error' => 'month_cycle, category, room_no, unit_id, CompanyID/employee_id are required'], 400);
        }
        if (!in_array($category, self::VALID_CATEGORIES, true)) {
            return response()->json(['status' => 'error', 'error' => "Invalid category: {$category}"], 400);
        }
        if ($activeDays < 0) {
            return response()->json(['status' => 'error', 'error' => 'active_days cannot be negative'], 400);
        }

        $emp = DB::table('employees_master')->where('company_id', $employeeId)->first(['company_id', 'name', 'department', 'designation', 'active']);
        if (!$emp) {
            return response()->json(['status' => 'error', 'error' => 'CompanyID not found in Employees_Master'], 400);
        }
        if (($emp->active ?? 'Yes') !== 'Yes') {
            return response()->json(['status' => 'error', 'error' => 'Inactive employee cannot be used in occupancy/billing'], 400);
        }

        $existing = DB::table('util_occupancy_monthly')
            ->where('month_cycle', $monthCycle)
            ->where('employee_id', $employeeId)
            ->first(['unit_id']);
        if ($existing && $existing->unit_id !== $unitId) {
            return response()->json(['status' => 'error', 'error' => 'Employee already assigned to another Unit_ID in this month'], 400);
        }

        $roomRef = DB::table('util_unit_room_snapshot')
            ->where('month_cycle', $monthCycle)
            ->where('unit_id', $unitId)
            ->where('room_no', $roomNo)
            ->exists();
        if (!$roomRef) {
            return response()->json(['status' => 'error', 'error' => 'Unit_ID/Room_No not found in locked /rooms snapshot for month'], 400);
        }

        DB::table('util_occupancy_monthly')->updateOrInsert(
            ['month_cycle' => $monthCycle, 'employee_id' => $employeeId],
            [
                'category' => $category,
                'block_floor' => $this->nullableTrim($request->input('block_floor')),
                'room_no' => $roomNo,
                'unit_id' => $unitId,
                'active_days' => $activeDays,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'ok',
            'employee_name' => $emp->name,
            'department' => $emp->department,
            'designation' => $emp->designation,
        ]);
    }

    public function occupancyDelete(int $id)
    {
        DB::table('util_occupancy_monthly')->where('id', $id)->delete();
        return response()->json(['status' => 'ok', 'id' => $id]);
    }

    public function occupancyDeleteCompat(int $row_id)
    {
        return $this->occupancyDelete($row_id);
    }

    public function occupancyAutofill(Request $request)
    {
        $monthCycle = trim((string) $request->query('month_cycle', ''));
        if ($monthCycle === '') {
            return response()->json(['status' => 'error', 'error' => 'month_cycle parameter is required'], 400);
        }

        if (!preg_match('/^\d{2}-\d{4}$/', $monthCycle)) {
            return response()->json(['status' => 'error', 'error' => 'month_cycle must be MM-YYYY'], 400);
        }

        $employees = DB::table('employees_master')
            ->whereRaw("COALESCE(active, 'Yes') = 'Yes'")
            ->orderBy('company_id')
            ->get(['company_id', 'unit_id']);

        if ($employees->isEmpty()) {
            return response()->json(['status' => 'error', 'error' => 'No active employees found in master tables'], 400);
        }

        $rowsInserted = 0;
        DB::transaction(function () use ($monthCycle, $employees, &$rowsInserted) {
            DB::table('util_occupancy_monthly')->where('month_cycle', $monthCycle)->delete();
            $rowsInserted = 0;

            foreach ($employees as $emp) {
                $room = DB::table('util_unit_room_snapshot')
                    ->where('month_cycle', $monthCycle)
                    ->where('unit_id', $emp->unit_id)
                    ->orderBy('id')
                    ->first(['unit_id', 'category', 'block_floor', 'room_no']);

                if (!$room) {
                    $room = DB::table('util_unit_room_snapshot')
                        ->where('month_cycle', $monthCycle)
                        ->orderBy('unit_id')
                        ->orderBy('room_no')
                        ->first(['unit_id', 'category', 'block_floor', 'room_no']);
                }

                if (!$room || !in_array((string) $room->category, self::VALID_CATEGORIES, true)) {
                    continue;
                }

                DB::table('util_occupancy_monthly')->insert([
                    'month_cycle' => $monthCycle,
                    'category' => $room->category,
                    'block_floor' => $room->block_floor,
                    'room_no' => $room->room_no,
                    'unit_id' => $room->unit_id,
                    'employee_id' => $emp->company_id,
                    'active_days' => 30,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $rowsInserted++;
            }
        });

        return response()->json([
            'status' => 'ok',
            'month_cycle' => $monthCycle,
            'rows' => $rowsInserted,
            'message' => "Successfully auto-filled {$rowsInserted} occupancy records for {$monthCycle}",
        ]);
    }

    private function nullableTrim(mixed $value): ?string
    {
        $out = trim((string) ($value ?? ''));
        return $out === '' ? null : $out;
    }
}
