<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

class ResidencyMasterService
{
    public function list(array $filters = []): array
    {
        $snapshotInfo = $this->snapshotInfo();
        $snapshotMonth = $snapshotInfo['snapshot_month'];

        $snapshotRows = DB::table('util_unit_room_snapshot as r')
            ->leftJoin('util_unit as u', 'u.unit_id', '=', 'r.unit_id')
            ->where('r.month_cycle', $snapshotMonth)
            ->select([
                'r.residence_type',
                'r.category',
                'r.block_floor',
                'r.room_no',
                'r.unit_id',
                'u.colony_type as unit_colony_type',
                'u.block_name as unit_block_name',
                'u.is_active as unit_is_active',
            ])
            ->orderBy('r.residence_type')
            ->orderBy('u.colony_type')
            ->orderBy('r.block_floor')
            ->orderBy('r.room_no')
            ->get();

        $assignments = DB::table('employee_residence_assignments as a')
            ->leftJoin('employees_master as e', 'e.company_id', '=', 'a.company_id')
            ->where('a.status', 'ACTIVE')
            ->select([
                'a.company_id',
                'a.unit_id',
                'a.room_no',
                'a.block_floor',
                'a.occupancy_mode',
                'e.name',
                'e.department',
            ])
            ->get();

        $employeeRoomCounts = [];
        foreach ($assignments as $assignment) {
            $companyId = trim((string) $assignment->company_id);
            if ($companyId === '') {
                continue;
            }
            $employeeRoomCounts[$companyId][$this->roomKey($assignment->unit_id, $assignment->room_no)] = true;
        }

        $occupancyByRoom = [];
        foreach ($assignments as $assignment) {
            $occupancyByRoom[$this->roomKey($assignment->unit_id, $assignment->room_no)][] = $assignment;
        }

        $rows = [];
        foreach ($snapshotRows as $snapshot) {
            $key = $this->roomKey($snapshot->unit_id, $snapshot->room_no);
            $occupants = $occupancyByRoom[$key] ?? [];
            $conflictNotes = [];
            $companyIds = [];
            $names = [];
            $departments = [];
            $modes = [];

            foreach ($occupants as $occupant) {
                $companyId = trim((string) $occupant->company_id);
                if ($companyId !== '') {
                    $companyIds[] = $companyId;
                    if (count($employeeRoomCounts[$companyId] ?? []) > 1) {
                        $conflictNotes[] = 'Employee '.$companyId.' has multiple active room assignments';
                    }
                }
                if (trim((string) $occupant->name) !== '') {
                    $names[] = trim((string) $occupant->name);
                }
                if (trim((string) $occupant->department) !== '') {
                    $departments[] = trim((string) $occupant->department);
                }
                $mode = strtoupper(trim((string) $occupant->occupancy_mode));
                if ($mode !== '') {
                    $modes[] = $mode;
                }
                $snapshotFloor = $this->normalizeFloor($snapshot->block_floor);
                $assignmentFloor = $this->normalizeFloor($occupant->block_floor);
                if ($snapshotFloor !== '' && $assignmentFloor !== '' && $snapshotFloor !== $assignmentFloor) {
                    $conflictNotes[] = 'Block/Floor mismatch: snapshot='.$snapshot->block_floor.' assignment='.$occupant->block_floor;
                }
            }

            $occupantCount = count($occupants);
            $sharingAllowed = $this->sharingAllowed($modes);
            if ($occupantCount > 1 && !$sharingAllowed) {
                $modeLabel = empty($modes) ? 'blank occupancy_mode' : 'occupancy_mode='.implode(',', array_values(array_unique($modes)));
                $conflictNotes[] = 'Multiple active occupants but '.$modeLabel.' does not indicate sharing';
            }

            if ($occupantCount === 0) {
                $status = 'Vacant';
            } elseif (!empty($conflictNotes)) {
                $status = 'Conflict';
            } elseif ($occupantCount === 1) {
                $status = 'Occupied';
            } else {
                $status = 'Shared';
            }

            $row = [
                'residence_type' => (string) ($snapshot->residence_type ?? ''),
                'colony_type' => (string) ($snapshot->unit_colony_type ?: $snapshot->category ?: ''),
                'block_floor' => (string) ($snapshot->block_floor ?? ''),
                'unit_id' => (string) ($snapshot->unit_id ?? ''),
                'room_no' => (string) ($snapshot->room_no ?? ''),
                'unit_active' => (int) ($snapshot->unit_is_active ?? 0) === 1 ? 'Active' : 'Inactive',
                'occupancy_status' => $status,
                'occupant_count' => $occupantCount,
                'assigned_company_ids' => array_values(array_unique($companyIds)),
                'assigned_employee_names' => array_values(array_unique($names)),
                'departments' => array_values(array_unique($departments)),
                'conflict_notes' => array_values(array_unique($conflictNotes)),
                'action_placeholder' => 'Write actions disabled pending approval',
            ];

            if (!$this->passesFilters($row, $filters)) {
                continue;
            }
            $rows[] = $row;
        }

        return [
            'metadata' => [
                'snapshot_month' => $snapshotInfo['snapshot_month'],
                'snapshot_source' => $snapshotInfo['snapshot_source'],
                'snapshot_warning' => $snapshotInfo['snapshot_warning'],
                'total_rows' => count($rows),
                'filters_applied' => $this->cleanFilters($filters),
            ],
            'rows' => $rows,
        ];
    }

    private function snapshotInfo(): array
    {
        $columns = collect(DB::select('SHOW COLUMNS FROM util_unit_room_snapshot'))->pluck('Field')->all();
        $flags = array_values(array_intersect($columns, ['is_finalized', 'finalized', 'is_verified', 'verified_at', 'status', 'locked_at']));

        if (!empty($flags)) {
            $query = DB::table('util_unit_room_snapshot');
            if (in_array('is_finalized', $flags, true)) {
                $query->where('is_finalized', 1);
            } elseif (in_array('finalized', $flags, true)) {
                $query->where('finalized', 1);
            } elseif (in_array('is_verified', $flags, true)) {
                $query->where('is_verified', 1);
            } elseif (in_array('verified_at', $flags, true)) {
                $query->whereNotNull('verified_at');
            } elseif (in_array('locked_at', $flags, true)) {
                $query->whereNotNull('locked_at');
            } elseif (in_array('status', $flags, true)) {
                $query->whereIn('status', ['FINALIZED', 'VERIFIED', 'LOCKED']);
            }
            $month = (string) $query->max('month_cycle');
            return [
                'snapshot_month' => $month,
                'snapshot_source' => 'FINALIZED_OR_VERIFIED',
                'snapshot_warning' => null,
            ];
        }

        return [
            'snapshot_month' => (string) DB::table('util_unit_room_snapshot')->max('month_cycle'),
            'snapshot_source' => 'LATEST_MONTH_UNVERIFIED',
            'snapshot_warning' => 'No finalized snapshot flag exists.',
        ];
    }

    private function passesFilters(array $row, array $filters): bool
    {
        $map = [
            'residence_type' => 'residence_type',
            'colony_type' => 'colony_type',
            'block_floor' => 'block_floor',
            'unit_id' => 'unit_id',
            'room_no' => 'room_no',
            'unit_active' => 'unit_active',
            'occupancy_status' => 'occupancy_status',
        ];
        foreach ($map as $filterKey => $rowKey) {
            $value = trim((string) ($filters[$filterKey] ?? ''));
            if ($value === '') {
                continue;
            }
            if (strcasecmp((string) $row[$rowKey], $value) !== 0) {
                return false;
            }
        }
        return true;
    }

    private function cleanFilters(array $filters): array
    {
        return collect($filters)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->all();
    }

    private function roomKey($unitId, $roomNo): string
    {
        return strtoupper(trim((string) $unitId)).'|'.strtoupper(trim((string) $roomNo));
    }

    private function sharingAllowed(array $modes): bool
    {
        if (empty($modes)) {
            return false;
        }
        foreach ($modes as $mode) {
            $normalized = strtoupper(trim((string) $mode));
            if (str_contains($normalized, 'SHARE') || str_contains($normalized, 'MULTI') || str_contains($normalized, 'BACHELOR')) {
                return true;
            }
        }
        return false;
    }

    private function normalizeFloor($value): string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) $value));
        $map = [
            'GroundFloor' => 'Ground Floor',
            '1stFloor' => '1st Floor',
            '2ndFloor' => '2nd Floor',
            '3rdFloor' => '3rd Floor',
            '4thFloor' => '4th Floor',
        ];
        $compact = str_replace(' ', '', $value);
        return $map[$compact] ?? $value;
    }
}
