<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InitializeResidenceAssignments extends Command
{
    protected $signature = 'residence:initialize
                            {month=05-2026 : Source month in MM-YYYY format}
                            {--actor=SYSTEM_INITIAL_IMPORT : Recorded by value}';

    protected $description = 'Initialize active employee residence assignments from monthly occupancy snapshot.';

    public function handle(): int
    {
        $month = trim((string) $this->argument('month'));
        $actor = trim((string) $this->option('actor'));

        try {
            $startDate = Carbon::createFromFormat('m-Y', $month)->startOfMonth()->toDateString();
        } catch (\Throwable $e) {
            $this->error('Invalid month. Required format: MM-YYYY');
            return self::FAILURE;
        }

        foreach (['employee_residence_assignments', 'util_occupancy_monthly', 'util_unit_room_snapshot', 'employees_master'] as $table) {
            if (!Schema::hasTable($table)) {
                $this->error('Required table missing: ' . $table);
                return self::FAILURE;
            }
        }

        $existingAssignments = DB::table('employee_residence_assignments')->count();

        if ($existingAssignments > 0) {
            $this->error('Import stopped: employee_residence_assignments is not empty. ROWS=' . $existingAssignments);
            return self::FAILURE;
        }

        $sourceRows = DB::table('util_occupancy_monthly')
            ->where('month_cycle', $month)
            ->count();

        if ($sourceRows === 0) {
            $this->error('Import stopped: no occupancy rows found for ' . $month);
            return self::FAILURE;
        }

        $missingRooms = DB::table('util_occupancy_monthly as o')
            ->leftJoin('util_unit_room_snapshot as r', function ($join) {
                $join->on('r.month_cycle', '=', 'o.month_cycle')
                    ->on('r.unit_id', '=', 'o.unit_id')
                    ->on('r.room_no', '=', 'o.room_no');
            })
            ->where('o.month_cycle', $month)
            ->whereNull('r.id')
            ->count();

        if ($missingRooms > 0) {
            $this->error('Import stopped: occupancy rows without room registry match. ROWS=' . $missingRooms);
            return self::FAILURE;
        }

        $missingEmployees = DB::table('util_occupancy_monthly as o')
            ->leftJoin('employees_master as e', 'e.company_id', '=', 'o.employee_id')
            ->where('o.month_cycle', $month)
            ->whereNull('e.company_id')
            ->count();

        if ($missingEmployees > 0) {
            $this->error('Import stopped: occupancy rows without employee master match. ROWS=' . $missingEmployees);
            return self::FAILURE;
        }

        $duplicateEmployees = DB::table('util_occupancy_monthly')
            ->where('month_cycle', $month)
            ->selectRaw('employee_id, COUNT(*) AS total')
            ->groupBy('employee_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicateEmployees->isNotEmpty()) {
            $this->error('Import stopped: employees with multiple current occupancy rows. EMPLOYEES=' . $duplicateEmployees->count());

            foreach ($duplicateEmployees->take(20) as $row) {
                $this->line($row->employee_id . ' | ROWS=' . $row->total);
            }

            return self::FAILURE;
        }

        $rows = DB::table('util_occupancy_monthly as o')
            ->join('util_unit_room_snapshot as r', function ($join) {
                $join->on('r.month_cycle', '=', 'o.month_cycle')
                    ->on('r.unit_id', '=', 'o.unit_id')
                    ->on('r.room_no', '=', 'o.room_no');
            })
            ->where('o.month_cycle', $month)
            ->select([
                'o.employee_id',
                'o.category as occupancy_category',
                'o.unit_id',
                'o.block_floor',
                'o.room_no',
                'r.residence_type',
                'r.category as room_category',
            ])
            ->orderBy('o.employee_id')
            ->get();

        $familyEmployeeIds = DB::table('family_members')
            ->where('is_active', 1)
            ->distinct()
            ->pluck('company_id')
            ->flip();

        $now = now();
        $insertRows = [];
        $householdCount = 0;
        $individualCount = 0;

        foreach ($rows as $row) {
            $isHouse = str_starts_with(strtolower(trim((string) $row->residence_type)), 'house');
            $hasFamily = $familyEmployeeIds->has((string) $row->employee_id);
            $mode = ($isHouse && $hasFamily) ? 'HOUSEHOLD' : 'INDIVIDUAL';

            if ($mode === 'HOUSEHOLD') {
                $householdCount++;
            } else {
                $individualCount++;
            }

            $insertRows[] = [
                'company_id' => (string) $row->employee_id,
                'residence_type' => (string) $row->residence_type,
                'category' => (string) ($row->room_category ?: $row->occupancy_category),
                'unit_id' => (string) $row->unit_id,
                'block_floor' => $row->block_floor,
                'room_no' => (string) $row->room_no,
                'occupancy_mode' => $mode,
                'start_date' => $startDate,
                'end_date' => null,
                'status' => 'ACTIVE',
                'start_reason' => 'INITIAL_IMPORT',
                'closure_reason' => null,
                'source_month_cycle' => $month,
                'source_record_type' => 'UTIL_OCCUPANCY_MONTHLY',
                'remarks' => null,
                'created_by' => $actor === '' ? 'SYSTEM_INITIAL_IMPORT' : $actor,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($insertRows) {
            foreach (array_chunk($insertRows, 300) as $chunk) {
                DB::table('employee_residence_assignments')->insert($chunk);
            }
        });

        $this->info('INITIAL_RESIDENCE_IMPORT=SUCCESS');
        $this->line('SOURCE_MONTH=' . $month);
        $this->line('START_DATE=' . $startDate);
        $this->line('SOURCE_ROWS=' . $sourceRows);
        $this->line('IMPORTED_ROWS=' . count($insertRows));
        $this->line('HOUSEHOLD_ROWS=' . $householdCount);
        $this->line('INDIVIDUAL_ROWS=' . $individualCount);

        return self::SUCCESS;
    }
}
