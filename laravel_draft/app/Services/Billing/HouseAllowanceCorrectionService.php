<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class HouseAllowanceCorrectionService
{
    private const HOUSE_TYPES = ['HOUSE A TYPE', 'HOUSE B TYPE', 'HOUSE C TYPE'];

    public function preview(array $payload): array
    {
        $plan = $this->buildPlan((string) $payload['month_cycle'], false);

        return $this->publicPlan($plan);
    }

    public function apply(array $payload, int $actorUserId): array
    {
        $monthCycle = (string) $payload['month_cycle'];
        $previewToken = (string) ($payload['preview_token'] ?? '');

        if ($previewToken === '') {
            return ['_http' => 422, 'status' => 'error', 'error' => 'preview_token is required before apply'];
        }

        try {
            return DB::transaction(function () use ($monthCycle, $previewToken, $actorUserId): array {
                $plan = $this->buildPlan($monthCycle, true);

                if (($plan['status'] ?? 'error') !== 'ok') {
                    return $this->publicPlan($plan);
                }

                if (!hash_equals((string) $plan['preview_token'], $previewToken)) {
                    return [
                        '_http' => 409,
                        'status' => 'error',
                        'error' => 'Preview is stale. Run preview again before applying correction.',
                    ];
                }

                if ((int) $plan['summary']['changed_units'] === 0) {
                    return $this->publicPlan($plan) + [
                        'applied' => false,
                        'already_corrected' => true,
                        'message' => 'HOUSE full allowance correction is already applied for this month.',
                    ];
                }

                $beforeSummary = [
                    'rule' => 'HOUSE A/B/C full configured allowance once per house; no attendance proration',
                    'summary' => $plan['summary'],
                    'control_units' => $plan['control_units'],
                ];

                $beforeSnapshot = $this->captureSnapshot($monthCycle);

                foreach ($plan['_unit_updates'] as $row) {
                    DB::table('util_elec_unit_monthly_result')
                        ->where('id', $row['id'])
                        ->update([
                            'net_units' => $row['net_units'],
                            'unit_amount' => $row['unit_amount'],
                            'updated_at' => now(),
                        ]);
                }

                foreach ($plan['_share_updates'] as $row) {
                    DB::table('util_elec_employee_share_monthly')
                        ->where('id', $row['id'])
                        ->update([
                            'share_units' => $row['share_units'],
                            'share_amount' => $row['share_amount'],
                            'allocation_method' => $row['allocation_method'],
                            'explain_usage_share_units' => $row['emp_used_units'],
                            'explain_free_share_units' => $row['eligible_units'],
                            'explain_billable_units' => $row['billable_units'],
                            'emp_used_units' => $row['emp_used_units'],
                            'eligible_units' => $row['eligible_units'],
                            'billable_units' => $row['billable_units'],
                            'amount' => $row['amount'],
                            'updated_at' => now(),
                        ]);
                }

                $proof = $this->buildPlan($monthCycle, true);
                if (($proof['status'] ?? 'error') !== 'ok') {
                    throw new \RuntimeException('Post-apply proof could not be generated.');
                }

                if ((int) $proof['summary']['changed_units'] !== 0) {
                    throw new \RuntimeException('Post-apply proof failed: remaining HOUSE units still require correction.');
                }

                if (abs((float) $proof['summary']['old_amount_total'] - (float) $plan['summary']['new_amount_total']) > 0.01) {
                    throw new \RuntimeException('Post-apply proof failed: corrected amount total mismatch.');
                }

                $correlationId = 'HOUSE-ALLOW-'.$monthCycle.'-'.Str::uuid()->toString();

                $afterSummary = [
                    'rule' => 'HOUSE A/B/C full configured allowance once per house; no attendance proration',
                    'summary' => $proof['summary'],
                    'control_units' => $proof['control_units'],
                ];
                $afterSnapshot = $this->captureSnapshot($monthCycle);

                $beforeJson = json_encode($beforeSnapshot, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                $afterJson = json_encode($afterSnapshot, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

                $snapshotId = DB::table('util_house_allowance_correction_snapshots')->insertGetId([
                    'month_cycle' => $monthCycle,
                    'correlation_id' => $correlationId,
                    'action' => 'APPLY',
                    'actor_user_id' => (string) $actorUserId,
                    'rule' => 'HOUSE A/B/C full configured allowance once per house; no attendance proration; one cycle-overlap HOUSEHOLD payer for multi-employee house.',
                    'before_json' => $beforeJson,
                    'after_json' => $afterJson,
                    'created_at' => now(),
                ]);

                DB::table('util_audit_log')->insert([
                    'entity_type' => 'HOUSE_ALLOWANCE_CORRECTION',
                    'entity_id' => $monthCycle,
                    'action' => 'APPLY',
                    'actor_user_id' => $actorUserId,
                    'before_json' => json_encode($beforeSummary + [
                        'snapshot_table' => 'util_house_allowance_correction_snapshots',
                        'snapshot_id' => $snapshotId,
                        'snapshot_bytes' => strlen($beforeJson),
                    ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                    'after_json' => json_encode($afterSummary + [
                        'snapshot_table' => 'util_house_allowance_correction_snapshots',
                        'snapshot_id' => $snapshotId,
                        'snapshot_bytes' => strlen($afterJson),
                    ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                    'correlation_id' => $correlationId,
                ]);

                return $this->publicPlan($proof) + [
                    'applied' => true,
                    'already_corrected' => false,
                    'correlation_id' => $correlationId,
                    'snapshot_id' => $snapshotId,
                    'previous_summary' => $plan['summary'],
                    'message' => 'HOUSE full allowance correction applied successfully.',
                ];
            });
        } catch (Throwable $e) {
            return [
                '_http' => 500,
                'status' => 'error',
                'error' => 'HOUSE correction rolled back: '.$e->getMessage(),
            ];
        }
    }

    private function buildPlan(string $monthCycle, bool $lockRows): array
    {
        $shareQuery = DB::table('util_elec_employee_share_monthly as s')
            ->join('util_unit_room_snapshot as r', function ($join) {
                $join->on('r.month_cycle', '=', 's.month_cycle')
                    ->on('r.unit_id', '=', 's.unit_id')
                    ->on('r.room_no', '=', 's.room_no');
            })
            ->where('s.month_cycle', $monthCycle)
            ->whereIn(DB::raw('UPPER(TRIM(r.residence_type))'), self::HOUSE_TYPES)
            ->select([
                's.id',
                's.unit_id',
                's.room_no',
                's.employee_id',
                's.emp_used_units',
                's.eligible_units',
                's.billable_units',
                's.share_units',
                's.share_amount',
                's.amount',
                's.allocation_method',
                'r.residence_type',
            ])
            ->orderBy('s.unit_id')
            ->orderBy('s.employee_id');

        if ($lockRows) {
            $shareQuery->lockForUpdate();
        }

        $shares = $shareQuery->get();

        if ($shares->isEmpty()) {
            return [
                '_http' => 409,
                'status' => 'error',
                'error' => 'No House A/B/C electric result rows exist for selected month.',
            ];
        }

        $unitIds = $shares->pluck('unit_id')->unique()->values();

        $unitQuery = DB::table('util_elec_unit_monthly_result')
            ->where('month_cycle', $monthCycle)
            ->whereIn('unit_id', $unitIds)
            ->orderBy('unit_id');

        if ($lockRows) {
            $unitQuery->lockForUpdate();
        }

        $units = $unitQuery->get()->keyBy('unit_id');
        $expectedUnitCount = $shares->groupBy('unit_id')->count();

        if ($units->count() !== $expectedUnitCount) {
            return [
                '_http' => 409,
                'status' => 'error',
                'error' => 'HOUSE unit result rows do not match employee share units.',
                'share_units' => $expectedUnitCount,
                'unit_results' => $units->count(),
            ];
        }

        $cycle = DB::table('util_month_cycle')
            ->where('month_cycle', $monthCycle)
            ->first(['cycle_start_date', 'cycle_end_date']);

        if (!$cycle || !$cycle->cycle_start_date || !$cycle->cycle_end_date) {
            return [
                '_http' => 409,
                'status' => 'error',
                'error' => 'Selected month must have cycle_start_date and cycle_end_date before HOUSE correction.',
            ];
        }

        $cycleStartDate = (string) $cycle->cycle_start_date;
        $cycleEndDate = (string) $cycle->cycle_end_date;

        $assignments = DB::table('employee_residence_assignments')
            ->whereIn('unit_id', $unitIds)
            ->where('start_date', '<=', $cycleEndDate)
            ->where(function ($query) use ($cycleStartDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $cycleStartDate);
            })
            ->get(['company_id', 'unit_id', 'occupancy_mode', 'start_date', 'end_date', 'status'])
            ->groupBy('unit_id');

        $unitUpdates = [];
        $shareUpdates = [];
        $controls = [];
        $tokenRows = [];
        $oldUnitAmount = 0.0;
        $oldShareAmount = 0.0;
        $newAmount = 0.0;
        $eligibleTotal = 0.0;
        $changedUnits = 0;
        $changedRows = 0;
        $multipleHouseCount = 0;

        foreach ($shares->groupBy('unit_id') as $unitId => $group) {
            $unit = $units->get($unitId);
            $employees = $group->pluck('employee_id')->map(fn ($id) => (string) $id)->unique()->values();
            $payer = null;

            if ($employees->count() === 1) {
                $payer = (string) $employees->first();
            } else {
                $multipleHouseCount++;

                $households = ($assignments->get($unitId, collect()))
                    ->filter(function ($assignment) use ($employees) {
                        return strtoupper(trim((string) $assignment->occupancy_mode)) === 'HOUSEHOLD'
                            && $employees->contains((string) $assignment->company_id);
                    });

                if ($households->count() !== 1) {
                    return [
                        '_http' => 409,
                        'status' => 'error',
                        'error' => 'Multiple-employee HOUSE must resolve to exactly one HOUSEHOLD allowance holder within the selected billing cycle.',
                        'unit_id' => $unitId,
                        'cycle_start_date' => $cycleStartDate,
                        'cycle_end_date' => $cycleEndDate,
                        'employees' => $employees->all(),
                        'household_matches' => $households->count(),
                    ];
                }

                $payer = (string) $households->first()->company_id;
            }

            $usedUnits = round((float) $unit->usage_units, 4);
            $freeUnits = round((float) $unit->unit_free_units, 4);
            $rate = (float) $unit->elec_rate;
            $billableUnits = round(max(0.0, $usedUnits - $freeUnits), 4);
            $unitAmount = round($billableUnits * $rate, 2);

            $unitChanged = round((float) $unit->net_units, 4) !== $billableUnits
                || round((float) $unit->unit_amount, 2) !== $unitAmount;

            $oldUnitAmount += (float) $unit->unit_amount;
            $newAmount += $unitAmount;
            $eligibleTotal += $freeUnits;

            $unitUpdates[] = [
                'id' => $unit->id,
                'unit_id' => $unitId,
                'net_units' => $billableUnits,
                'unit_amount' => $unitAmount,
            ];

            $targetRows = [];
            foreach ($group as $share) {
                $isPayer = (string) $share->employee_id === $payer;

                $target = [
                    'id' => $share->id,
                    'unit_id' => $unitId,
                    'employee_id' => (string) $share->employee_id,
                    'role' => $isPayer ? 'ALLOWANCE_HOLDER' : 'MEMBER_NO_HOUSE_ALLOWANCE',
                    'emp_used_units' => $isPayer ? $usedUnits : 0.0,
                    'eligible_units' => $isPayer ? $freeUnits : 0.0,
                    'billable_units' => $isPayer ? $billableUnits : 0.0,
                    'share_units' => $isPayer ? $billableUnits : 0.0,
                    'share_amount' => $isPayer ? $unitAmount : 0.0,
                    'amount' => $isPayer ? $unitAmount : 0.0,
                    'allocation_method' => $isPayer
                        ? ($employees->count() > 1 ? 'house_full_allowance_once_household_payer' : 'house_full_allowance_once')
                        : 'house_member_no_separate_electric_bill',
                ];

                $rowChanged = round((float) $share->emp_used_units, 4) !== round($target['emp_used_units'], 4)
                    || round((float) $share->eligible_units, 4) !== round($target['eligible_units'], 4)
                    || round((float) $share->billable_units, 4) !== round($target['billable_units'], 4)
                    || round((float) $share->amount, 2) !== round($target['amount'], 2)
                    || (string) $share->allocation_method !== $target['allocation_method'];

                if ($rowChanged) {
                    $changedRows++;
                }

                $oldShareAmount += (float) $share->amount;
                $shareUpdates[] = $target;
                $targetRows[] = $target;
            }

            if ($unitChanged || collect($targetRows)->contains(function ($target) use ($group) {
                $source = $group->firstWhere('id', $target['id']);
                return round((float) $source->emp_used_units, 4) !== round((float) $target['emp_used_units'], 4)
                    || round((float) $source->eligible_units, 4) !== round((float) $target['eligible_units'], 4)
                    || round((float) $source->billable_units, 4) !== round((float) $target['billable_units'], 4)
                    || round((float) $source->amount, 2) !== round((float) $target['amount'], 2)
                    || (string) $source->allocation_method !== $target['allocation_method'];
            })) {
                $changedUnits++;
            }

            if (in_array($unitId, ['WB-105', 'WC-002'], true)) {
                $controls[] = [
                    'unit_id' => $unitId,
                    'residence_type' => $group->first()->residence_type,
                    'payer' => $payer,
                    'used_units' => number_format($usedUnits, 4, '.', ''),
                    'full_allowance' => number_format($freeUnits, 4, '.', ''),
                    'billable_units' => number_format($billableUnits, 4, '.', ''),
                    'new_amount' => number_format($unitAmount, 2, '.', ''),
                    'employees' => $employees->all(),
                ];
            }

            $tokenRows[] = [
                'unit_id' => $unitId,
                'payer' => $payer,
                'current_unit_amount' => round((float) $unit->unit_amount, 2),
                'target_unit_amount' => $unitAmount,
                'target_rows' => $targetRows,
            ];
        }

        if (abs(round($oldUnitAmount, 2) - round($oldShareAmount, 2)) > 0.01) {
            return [
                '_http' => 409,
                'status' => 'error',
                'error' => 'Current HOUSE unit and employee-share totals do not reconcile.',
                'unit_amount_total' => round($oldUnitAmount, 2),
                'share_amount_total' => round($oldShareAmount, 2),
            ];
        }

        $summary = [
            'month_cycle' => $monthCycle,
            'house_units' => $expectedUnitCount,
            'house_share_rows' => $shares->count(),
            'multiple_employee_houses' => $multipleHouseCount,
            'changed_units' => $changedUnits,
            'changed_rows' => $changedRows,
            'cycle_start_date' => $cycleStartDate,
            'cycle_end_date' => $cycleEndDate,
            'new_eligible_units_total' => number_format($eligibleTotal, 4, '.', ''),
            'old_amount_total' => number_format($oldUnitAmount, 2, '.', ''),
            'new_amount_total' => number_format($newAmount, 2, '.', ''),
            'bill_reduction' => number_format($oldUnitAmount - $newAmount, 2, '.', ''),
        ];

        return [
            'status' => 'ok',
            'rule' => 'House A/B/C full configured allowance once per house; no attendance proration; one HOUSEHOLD payer for multi-employee house.',
            'summary' => $summary,
            'control_units' => $controls,
            'preview_token' => hash('sha256', json_encode([$monthCycle, $summary, $tokenRows], JSON_UNESCAPED_SLASHES)),
            '_unit_updates' => $unitUpdates,
            '_share_updates' => $shareUpdates,
        ];
    }

    private function captureSnapshot(string $monthCycle): array
    {
        $shares = DB::table('util_elec_employee_share_monthly as s')
            ->join('util_unit_room_snapshot as r', function ($join) {
                $join->on('r.month_cycle', '=', 's.month_cycle')
                    ->on('r.unit_id', '=', 's.unit_id')
                    ->on('r.room_no', '=', 's.room_no');
            })
            ->where('s.month_cycle', $monthCycle)
            ->whereIn(DB::raw('UPPER(TRIM(r.residence_type))'), self::HOUSE_TYPES)
            ->orderBy('s.unit_id')
            ->orderBy('s.employee_id')
            ->get([
                's.id',
                's.month_cycle',
                's.unit_id',
                's.room_no',
                's.employee_id',
                's.share_units',
                's.share_amount',
                's.allocation_method',
                's.explain_usage_share_units',
                's.explain_free_share_units',
                's.explain_billable_units',
                's.emp_used_units',
                's.eligible_units',
                's.billable_units',
                's.rate',
                's.amount',
                'r.residence_type',
            ]);

        $units = DB::table('util_elec_unit_monthly_result')
            ->where('month_cycle', $monthCycle)
            ->whereIn('unit_id', $shares->pluck('unit_id')->unique()->values())
            ->orderBy('unit_id')
            ->get([
                'id',
                'month_cycle',
                'unit_id',
                'category',
                'usage_units',
                'rooms_count',
                'unit_free_units',
                'net_units',
                'elec_rate',
                'unit_amount',
                'total_attendance',
            ]);

        return [
            'rule' => 'HOUSE A/B/C full configured allowance once per house; no attendance proration; one cycle-overlap HOUSEHOLD payer for multi-employee house.',
            'month_cycle' => $monthCycle,
            'unit_rows' => $units,
            'share_rows' => $shares,
        ];
    }

    private function publicPlan(array $plan): array
    {
        unset($plan['_unit_updates'], $plan['_share_updates']);

        return $plan;
    }
}
