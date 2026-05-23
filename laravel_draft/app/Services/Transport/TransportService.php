<?php

namespace App\Services\Transport;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransportService
{
    private function normalizeMonthCycle(string $monthCycle): string
    {
        $monthCycle = trim($monthCycle);
        if (preg_match('/^\d{4}-\d{2}$/', $monthCycle) === 1) {
            return substr($monthCycle, 5, 2).'-'.substr($monthCycle, 0, 4);
        }

        return $monthCycle;
    }

    private function monthValid(string $monthCycle): bool
    {
        return (bool) preg_match('/^\d{2}-\d{4}$/', $monthCycle);
    }

    private const SCHOOL_VAN_LEFT_REASONS = [
        'SERVICE_WITHDRAWN' => 'Service Withdrawn',
        'NO_LONGER_USING_SCHOOL_VAN' => 'No Longer Using School Van',
        'SCHOOL_CHANGED' => 'School Changed',
        'ROUTE_NOT_REQUIRED' => 'Route Not Required',
        'PARENT_REQUEST' => 'Parent Request',
        'OTHER' => 'Other',
    ];

    private const SCHOOL_VAN_CANCEL_REASONS = [
        'MISTAKEN_ENTRY' => 'Mistaken Entry',
        'DUPLICATE_ENROLMENT' => 'Duplicate Enrolment',
        'INCORRECT_CHILD_SELECTED' => 'Incorrect Child Selected',
        'INCORRECT_EMPLOYEE_FATHER_LINK' => 'Incorrect Employee/Father Link',
        'INCORRECT_JOIN_DATE' => 'Incorrect Join Date',
        'SERVICE_NOT_AVAILED' => 'Service Not Availed',
        'ENTRY_CREATED_FOR_TESTING' => 'Entry Created for Testing',
        'OTHER_ADMINISTRATIVE_CORRECTION' => 'Other Administrative Correction',
    ];

    private const SCHOOL_VAN_REACTIVATION_REASONS = [
        'MARKED_LEFT_BY_MISTAKE' => 'Marked Left by Mistake',
        'INCORRECT_LEAVE_DATE' => 'Incorrect Leave Date',
        'SERVICE_CONTINUED' => 'Service Continued',
        'ADMINISTRATIVE_CORRECTION' => 'Administrative Correction',
    ];

    private const SCHOOL_VAN_CANCELLATION_REVERSAL_REASONS = [
        'CANCELLED_BY_MISTAKE' => 'Cancelled by Mistake',
        'INCORRECT_CANCELLATION_REASON' => 'Incorrect Cancellation Reason',
        'ADMINISTRATIVE_CORRECTION' => 'Administrative Correction',
    ];

    private function monthState(string $monthCycle): ?string
    {
        $row = DB::selectOne('SELECT state FROM util_month_cycle WHERE month_cycle=?', [$monthCycle]);
        return $row ? strtoupper((string) $row->state) : null;
    }

    private function blockedLockedMonth(string $monthCycle, string $action): array
    {
        return [
            '_http' => 409,
            'status' => 'error',
            'action' => $action,
            'month_cycle' => $monthCycle,
            'message' => "Transport month {$monthCycle} is locked. Save is blocked for this entry.",
            'lock_state' => 'LOCKED',
        ];
    }

    private function schoolVanCycleForDate(string $date): ?array
    {
        $cycles = DB::table('util_month_cycle')
            ->select(['month_cycle', 'state'])
            ->get();

        foreach ($cycles as $row) {
            $monthCycle = (string) $row->month_cycle;

            if (!$this->monthValid($monthCycle)) {
                continue;
            }

            $cycle = $this->billingCycleDates($monthCycle);

            if (!$cycle) {
                continue;
            }

            if ($date >= $cycle['start'] && $date <= $cycle['end']) {
                return [
                    'month_cycle' => $monthCycle,
                    'state' => strtoupper((string) $row->state),
                    'start' => $cycle['start'],
                    'end' => $cycle['end'],
                ];
            }
        }

        return null;
    }

    private function schoolVanOpenCycleGuard(string $date, string $action): ?array
    {
        $cycle = $this->schoolVanCycleForDate($date);

        if (!$cycle) {
            return [
                '_http' => 422,
                'status' => 'error',
                'message' => 'No configured billing cycle contains the selected school van date.',
            ];
        }

        if ($cycle['state'] === 'LOCKED') {
            return $this->blockedLockedMonth($cycle['month_cycle'], $action);
        }

        if ($this->hasGeneratedSchoolVanBill($cycle['month_cycle'])) {
            return $this->blockedGeneratedSchoolVanBill($cycle['month_cycle'], $action);
        }

        return null;
    }

    private function actorId(): string
    {
        return (string) (session('actor_user_id') ?? session('user_id') ?? 'system');
    }

    private function auditLog(string $action, string $recordType, string|int $recordId, ?string $monthCycle, $before, $after): void
    {
        DB::table('util_audit_log')->insert([
            'entity_type' => 'transport',
            'entity_id' => (string) $recordId,
            'action' => $action,
            'actor_user_id' => $this->actorId(),
            'before_json' => $before ? json_encode([
                'module' => 'transport',
                'record_type' => $recordType,
                'record_id' => $recordId,
                'month_cycle' => $monthCycle,
                'snapshot' => $before,
            ], JSON_UNESCAPED_UNICODE) : null,
            'after_json' => json_encode([
                'module' => 'transport',
                'record_type' => $recordType,
                'record_id' => $recordId,
                'month_cycle' => $monthCycle,
                'snapshot' => $after,
            ], JSON_UNESCAPED_UNICODE),
            'correlation_id' => null,
            'created_at' => now(),
        ]);
    }

    private function ok(string $message, string $monthCycle, array $data = []): array
    {
        return [
            'status' => 'ok',
            'message' => $message,
            'month_cycle' => $monthCycle,
        ] + $data;
    }

    public function monthCycleUpsert(array $payload): array
    {
        $month = $this->normalizeMonthCycle((string) ($payload['month_cycle'] ?? ''));
        $cycleStart = trim((string) ($payload['cycle_start_date'] ?? ''));
        $cycleEnd = trim((string) ($payload['cycle_end_date'] ?? ''));

        if (!$this->monthValid($month)) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Valid month cycle is required in MM-YYYY format.'];
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $cycleStart) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $cycleEnd)) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Cycle start date and cycle end date are required.'];
        }

        try {
            $start = Carbon::createFromFormat('Y-m-d', $cycleStart);
            $end = Carbon::createFromFormat('Y-m-d', $cycleEnd);
        } catch (\Throwable $e) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Valid cycle dates are required.'];
        }

        if ($start->gt($end)) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Cycle end date cannot be before cycle start date.'];
        }

        if ($this->monthState($month) === 'LOCKED') {
            return $this->blockedLockedMonth($month, 'month_cycle_upsert');
        }

        if ($this->hasGeneratedSchoolVanBill($month)) {
            return $this->blockedGeneratedSchoolVanBill($month, 'month_cycle_upsert');
        }

        $overlap = DB::table('util_month_cycle')
            ->where('month_cycle', '!=', $month)
            ->whereNotNull('cycle_start_date')
            ->whereNotNull('cycle_end_date')
            ->whereDate('cycle_start_date', '<=', $cycleEnd)
            ->whereDate('cycle_end_date', '>=', $cycleStart)
            ->first();

        if ($overlap) {
            return [
                '_http' => 409,
                'status' => 'error',
                'message' => "Cycle dates overlap with configured month {$overlap->month_cycle}.",
            ];
        }

        return DB::transaction(function () use ($month, $cycleStart, $cycleEnd) {
            $before = DB::table('util_month_cycle')
                ->where('month_cycle', $month)
                ->lockForUpdate()
                ->first();

            if ($before && strtoupper((string) $before->state) === 'LOCKED') {
                return $this->blockedLockedMonth($month, 'month_cycle_upsert');
            }

            if ($before) {
                DB::table('util_month_cycle')
                    ->where('month_cycle', $month)
                    ->update([
                        'cycle_start_date' => $cycleStart,
                        'cycle_end_date' => $cycleEnd,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('util_month_cycle')->insert([
                    'month_cycle' => $month,
                    'state' => 'OPEN',
                    'cycle_start_date' => $cycleStart,
                    'cycle_end_date' => $cycleEnd,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $after = DB::table('util_month_cycle')->where('month_cycle', $month)->first();

            $this->auditLog(
                'transport_month_cycle_dates_upsert',
                'month_cycle',
                $month,
                $month,
                $before,
                $after
            );

            return [
                'status' => 'ok',
                'message' => 'School van billing cycle saved successfully.',
                'month_cycle' => $month,
                'billing_cycle' => [
                    'start' => $cycleStart,
                    'end' => $cycleEnd,
                    'month_end' => Carbon::parse($cycleStart)->endOfMonth()->toDateString(),
                    'source' => 'MANUAL_MONTH_CYCLE',
                ],
            ];
        });
    }

    public function vehicleUpsert(array $payload): array
    {
        return DB::transaction(function () use ($payload) {
            $id = isset($payload['id']) ? (int) $payload['id'] : null;
            $vehicleCode = trim((string) ($payload['vehicle_code'] ?? ''));
            $vehicleName = trim((string) ($payload['vehicle_name'] ?? ''));
            $isActive = filter_var($payload['is_active'] ?? true, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $notes = isset($payload['notes']) ? trim((string) $payload['notes']) : null;

            if ($id) {
                $before = DB::table('transport_vehicles')->where('id', $id)->first();

                DB::table('transport_vehicles')
                    ->where('id', $id)
                    ->update([
                        'vehicle_code' => $vehicleCode,
                        'vehicle_name' => $vehicleName,
                        'is_active' => $isActive ?? true,
                        'notes' => $notes !== '' ? $notes : null,
                        'updated_at' => now(),
                    ]);

                $after = DB::table('transport_vehicles')->where('id', $id)->first();
                $this->auditLog('vehicle_upsert', 'vehicle', $id, null, $before, $after);

                return $this->ok('Vehicle updated successfully.', '', [
                    'record_type' => 'vehicle',
                    'record_id' => $id,
                ]);
            }

            $newId = DB::table('transport_vehicles')->insertGetId([
                'vehicle_code' => $vehicleCode,
                'vehicle_name' => $vehicleName,
                'is_active' => $isActive ?? true,
                'notes' => $notes !== '' ? $notes : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $after = DB::table('transport_vehicles')->where('id', $newId)->first();
            $this->auditLog('vehicle_upsert', 'vehicle', $newId, null, null, $after);

            return $this->ok('Vehicle created successfully.', '', [
                'record_type' => 'vehicle',
                'record_id' => $newId,
            ]);
        });
    }

    public function rentEntryUpsert(array $payload): array
    {
        $month = $this->normalizeMonthCycle((string) ($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($month)) {
            return ['_http' => 400, 'status' => 'error', 'message' => 'Valid month_cycle is required (MM-YYYY).'];
        }

        if ($this->monthState($month) === 'LOCKED') {
            return $this->blockedLockedMonth($month, 'rent_entry_upsert');
        }

        if ($this->hasGeneratedSchoolVanBill($month)) {
            return $this->blockedGeneratedSchoolVanBill($month, 'rent_entry_upsert');
        }

        return DB::transaction(function () use ($payload, $month) {
            $id = isset($payload['id']) ? (int) $payload['id'] : null;
            $vehicleId = (int) $payload['vehicle_id'];
            $rentAmount = round((float) $payload['rent_amount'], 2);
            $notes = isset($payload['notes']) ? trim((string) $payload['notes']) : null;

            if ($id) {
                $before = DB::table('transport_rent_entries')->where('id', $id)->first();

                DB::table('transport_rent_entries')
                    ->where('id', $id)
                    ->update([
                        'month_cycle' => $month,
                        'vehicle_id' => $vehicleId,
                        'rent_amount' => $rentAmount,
                        'notes' => $notes !== '' ? $notes : null,
                        'updated_at' => now(),
                    ]);

                $after = DB::table('transport_rent_entries')->where('id', $id)->first();
                $this->auditLog('rent_entry_upsert', 'rent_entry', $id, $month, $before, $after);

                return $this->ok('Rent entry updated successfully.', $month, [
                    'record_type' => 'rent_entry',
                    'record_id' => $id,
                ]);
            }

            $existingId = DB::table('transport_rent_entries')
                ->where('month_cycle', $month)
                ->where('vehicle_id', $vehicleId)
                ->value('id');

            if ($existingId) {
                $before = DB::table('transport_rent_entries')->where('id', $existingId)->first();

                DB::table('transport_rent_entries')
                    ->where('id', $existingId)
                    ->update([
                        'rent_amount' => $rentAmount,
                        'notes' => $notes !== '' ? $notes : null,
                        'updated_at' => now(),
                    ]);

                $after = DB::table('transport_rent_entries')->where('id', $existingId)->first();
                $this->auditLog('rent_entry_upsert', 'rent_entry', (int) $existingId, $month, $before, $after);

                return $this->ok('Rent entry updated successfully.', $month, [
                    'record_type' => 'rent_entry',
                    'record_id' => (int) $existingId,
                ]);
            }

            $newId = DB::table('transport_rent_entries')->insertGetId([
                'month_cycle' => $month,
                'vehicle_id' => $vehicleId,
                'rent_amount' => $rentAmount,
                'notes' => $notes !== '' ? $notes : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $after = DB::table('transport_rent_entries')->where('id', $newId)->first();
            $this->auditLog('rent_entry_upsert', 'rent_entry', $newId, $month, null, $after);

            return $this->ok('Rent entry created successfully.', $month, [
                'record_type' => 'rent_entry',
                'record_id' => $newId,
            ]);
        });
    }

    public function fuelEntryUpsert(array $payload): array
    {
        $month = $this->normalizeMonthCycle((string) ($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($month)) {
            return ['_http' => 400, 'status' => 'error', 'message' => 'Valid month_cycle is required (MM-YYYY).'];
        }

        if ($this->monthState($month) === 'LOCKED') {
            return $this->blockedLockedMonth($month, 'fuel_entry_upsert');
        }

        if ($this->hasGeneratedSchoolVanBill($month)) {
            return $this->blockedGeneratedSchoolVanBill($month, 'fuel_entry_upsert');
        }

        $cycle = $this->billingCycleDates($month);
        $entryDate = trim((string) ($payload['entry_date'] ?? ''));

        if (!$cycle) {
            return [
                '_http' => 422,
                'status' => 'error',
                'message' => "Configure manual billing cycle dates for {$month} before saving fuel entries.",
            ];
        }

        if ($entryDate < $cycle['start'] || $entryDate > $cycle['end']) {
            return [
                '_http' => 422,
                'status' => 'error',
                'message' => "Fuel entry date must be within billing cycle {$cycle['start']} to {$cycle['end']}.",
                'billing_cycle' => $cycle,
            ];
        }

        return DB::transaction(function () use ($payload, $month) {
            $id = isset($payload['id']) ? (int) $payload['id'] : null;
            $fuelLiters = round((float) $payload['fuel_liters'], 3);
            $fuelPrice = round((float) $payload['fuel_price'], 2);
            $fuelCost = round($fuelLiters * $fuelPrice, 2);
            $slipRef = isset($payload['slip_ref']) ? trim((string) $payload['slip_ref']) : null;
            $notes = isset($payload['notes']) ? trim((string) $payload['notes']) : null;

            if ($id) {
                $before = DB::table('transport_fuel_entries')->where('id', $id)->first();

                DB::table('transport_fuel_entries')
                    ->where('id', $id)
                    ->update([
                        'month_cycle' => $month,
                        'entry_date' => $payload['entry_date'],
                        'vehicle_id' => (int) $payload['vehicle_id'],
                        'fuel_liters' => $fuelLiters,
                        'fuel_price' => $fuelPrice,
                        'fuel_cost' => $fuelCost,
                        'slip_ref' => $slipRef !== '' ? $slipRef : null,
                        'notes' => $notes !== '' ? $notes : null,
                        'updated_at' => now(),
                    ]);

                $after = DB::table('transport_fuel_entries')->where('id', $id)->first();
                $this->auditLog('fuel_entry_upsert', 'fuel_entry', $id, $month, $before, $after);

                return $this->ok('Fuel entry updated successfully.', $month, [
                    'record_type' => 'fuel_entry',
                    'record_id' => $id,
                    'fuel_cost' => $fuelCost,
                ]);
            }

            $newId = DB::table('transport_fuel_entries')->insertGetId([
                'month_cycle' => $month,
                'entry_date' => $payload['entry_date'],
                'vehicle_id' => (int) $payload['vehicle_id'],
                'fuel_liters' => $fuelLiters,
                'fuel_price' => $fuelPrice,
                'fuel_cost' => $fuelCost,
                'slip_ref' => $slipRef !== '' ? $slipRef : null,
                'notes' => $notes !== '' ? $notes : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $after = DB::table('transport_fuel_entries')->where('id', $newId)->first();
            $this->auditLog('fuel_entry_upsert', 'fuel_entry', $newId, $month, null, $after);

            return $this->ok('Fuel entry saved successfully.', $month, [
                'record_type' => 'fuel_entry',
                'record_id' => $newId,
                'fuel_cost' => $fuelCost,
            ]);
        });
    }

    public function adjustmentUpsert(array $payload): array
    {
        $month = $this->normalizeMonthCycle((string) ($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($month)) {
            return ['_http' => 400, 'status' => 'error', 'message' => 'Valid month_cycle is required (MM-YYYY).'];
        }

        if ($this->monthState($month) === 'LOCKED') {
            return $this->blockedLockedMonth($month, 'adjustment_upsert');
        }

        if ($this->hasGeneratedSchoolVanBill($month)) {
            return $this->blockedGeneratedSchoolVanBill($month, 'adjustment_upsert');
        }

        return DB::transaction(function () use ($payload, $month) {
            $id = isset($payload['id']) ? (int) $payload['id'] : null;
            $notes = isset($payload['notes']) ? trim((string) $payload['notes']) : null;

            if ($id) {
                $before = DB::table('transport_adjustments')->where('id', $id)->first();

                DB::table('transport_adjustments')
                    ->where('id', $id)
                    ->update([
                        'month_cycle' => $month,
                        'vehicle_id' => isset($payload['vehicle_id']) && $payload['vehicle_id'] !== '' ? (int) $payload['vehicle_id'] : null,
                        'direction' => (string) $payload['direction'],
                        'amount' => round((float) $payload['amount'], 2),
                        'reason' => trim((string) $payload['reason']),
                        'notes' => $notes !== '' ? $notes : null,
                        'updated_at' => now(),
                    ]);

                $after = DB::table('transport_adjustments')->where('id', $id)->first();
                $this->auditLog('adjustment_upsert', 'adjustment', $id, $month, $before, $after);

                return $this->ok('Adjustment updated successfully.', $month, [
                    'record_type' => 'adjustment',
                    'record_id' => $id,
                ]);
            }

            $newId = DB::table('transport_adjustments')->insertGetId([
                'month_cycle' => $month,
                'vehicle_id' => isset($payload['vehicle_id']) && $payload['vehicle_id'] !== '' ? (int) $payload['vehicle_id'] : null,
                'direction' => (string) $payload['direction'],
                'amount' => round((float) $payload['amount'], 2),
                'reason' => trim((string) $payload['reason']),
                'notes' => $notes !== '' ? $notes : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $after = DB::table('transport_adjustments')->where('id', $newId)->first();
            $this->auditLog('adjustment_upsert', 'adjustment', $newId, $month, null, $after);

            return $this->ok('Adjustment saved successfully.', $month, [
                'record_type' => 'adjustment',
                'record_id' => $newId,
            ]);
        });
    }

    private function hasGeneratedSchoolVanBill(string $monthCycle): bool
    {
        return DB::table('util_school_van_monthly_charge')
            ->where('month_cycle', $monthCycle)
            ->where('charged_flag', 1)
            ->exists();
    }

    private function blockedGeneratedSchoolVanBill(string $monthCycle, string $action): array
    {
        return [
            '_http' => 409,
            'status' => 'error',
            'action' => $action,
            'month_cycle' => $monthCycle,
            'bill_status' => 'GENERATED',
            'message' => "School van bill for {$monthCycle} is already generated. Changes are blocked until a controlled reversal workflow is implemented.",
        ];
    }

    private function billingCycleDates(string $monthCycle): ?array
    {
        $row = DB::table('util_month_cycle')
            ->where('month_cycle', $monthCycle)
            ->first(['cycle_start_date', 'cycle_end_date']);

        if (!$row || !$row->cycle_start_date || !$row->cycle_end_date) {
            return null;
        }

        try {
            $cycleStart = Carbon::parse((string) $row->cycle_start_date);
            $cycleEnd = Carbon::parse((string) $row->cycle_end_date);
        } catch (\Throwable $e) {
            return null;
        }

        if ($cycleStart->gt($cycleEnd)) {
            return null;
        }

        return [
            'start' => $cycleStart->toDateString(),
            'end' => $cycleEnd->toDateString(),
            'month_end' => $cycleStart->copy()->endOfMonth()->toDateString(),
            'source' => 'MANUAL_MONTH_CYCLE',
        ];
    }

    public function summary(?string $monthCycle): array
    {
        $month = $this->normalizeMonthCycle((string) ($monthCycle ?? ''));

        if (!$this->monthValid($month)) {
            return ['_http' => 400, 'status' => 'error', 'error' => 'month_cycle is required in MM-YYYY'];
        }

        $cycle = $this->billingCycleDates($month);

        if (!$cycle) {
            return [
                '_http' => 422,
                'status' => 'error',
                'month_cycle' => $month,
                'error' => "Configure manual billing cycle dates for {$month} before calculating school van charges.",
                'allocation_status' => 'BLOCKED_CYCLE_CONFIGURATION_REQUIRED',
            ];
        }

        $vehicles = DB::table('transport_vehicles')
            ->select(['id', 'vehicle_code', 'vehicle_name', 'is_active', 'notes'])
            ->orderByDesc('is_active')
            ->orderBy('vehicle_name')
            ->get();

        $rentByVehicle = DB::table('transport_rent_entries')
            ->where('month_cycle', $month)
            ->select('vehicle_id', DB::raw('SUM(rent_amount) as rent_amount'))
            ->groupBy('vehicle_id');

        $fuelByVehicle = DB::table('transport_fuel_entries')
            ->where('month_cycle', $month)
            ->whereBetween('entry_date', [$cycle['start'], $cycle['end']])
            ->select(
                'vehicle_id',
                DB::raw('SUM(fuel_liters) as fuel_liters'),
                DB::raw('SUM(fuel_cost) as fuel_cost')
            )
            ->groupBy('vehicle_id');

        $adjustmentByVehicle = DB::table('transport_adjustments')
            ->where('month_cycle', $month)
            ->whereNotNull('vehicle_id')
            ->select(
                'vehicle_id',
                DB::raw("SUM(CASE WHEN direction = 'plus' THEN amount ELSE 0 END) as adjustment_plus"),
                DB::raw("SUM(CASE WHEN direction = 'minus' THEN amount ELSE 0 END) as adjustment_minus")
            )
            ->groupBy('vehicle_id');

        $rows = DB::table('transport_vehicles as v')
            ->leftJoinSub($rentByVehicle, 'r', fn ($join) => $join->on('r.vehicle_id', '=', 'v.id'))
            ->leftJoinSub($fuelByVehicle, 'f', fn ($join) => $join->on('f.vehicle_id', '=', 'v.id'))
            ->leftJoinSub($adjustmentByVehicle, 'a', fn ($join) => $join->on('a.vehicle_id', '=', 'v.id'))
            ->select([
                'v.id as vehicle_id',
                'v.vehicle_code',
                'v.vehicle_name',
                DB::raw('COALESCE(r.rent_amount, 0) as van_rent'),
                DB::raw('COALESCE(f.fuel_liters, 0) as fuel_liters'),
                DB::raw('COALESCE(f.fuel_cost, 0) as fuel_cost'),
                DB::raw('COALESCE(a.adjustment_plus, 0) as adjustment_plus'),
                DB::raw('COALESCE(a.adjustment_minus, 0) as adjustment_minus'),
                DB::raw('ROUND(COALESCE(r.rent_amount, 0) + COALESCE(f.fuel_cost, 0) + COALESCE(a.adjustment_plus, 0) - COALESCE(a.adjustment_minus, 0), 2) as total_cost'),
                DB::raw('ROUND((COALESCE(r.rent_amount, 0) + COALESCE(f.fuel_cost, 0) + COALESCE(a.adjustment_plus, 0) - COALESCE(a.adjustment_minus, 0)) * 0.50, 2) as company_share'),
                DB::raw('ROUND((COALESCE(r.rent_amount, 0) + COALESCE(f.fuel_cost, 0) + COALESCE(a.adjustment_plus, 0) - COALESCE(a.adjustment_minus, 0)) * 0.50, 2) as father_share'),
                DB::raw('ROUND((COALESCE(r.rent_amount, 0) + COALESCE(f.fuel_cost, 0) + COALESCE(a.adjustment_plus, 0) - COALESCE(a.adjustment_minus, 0)) * 0.50, 2) as net_father_bill'),
            ])
            ->orderBy('v.vehicle_name')
            ->get();

        $totalRent = round((float) DB::table('transport_rent_entries')
            ->where('month_cycle', $month)
            ->sum('rent_amount'), 2);

        $totalFuelLiters = round((float) DB::table('transport_fuel_entries')
            ->where('month_cycle', $month)
            ->whereBetween('entry_date', [$cycle['start'], $cycle['end']])
            ->sum('fuel_liters'), 3);

        $totalFuelCost = round((float) DB::table('transport_fuel_entries')
            ->where('month_cycle', $month)
            ->whereBetween('entry_date', [$cycle['start'], $cycle['end']])
            ->sum('fuel_cost'), 2);

        $invalidFuelEntries = DB::table('transport_fuel_entries as f')
            ->leftJoin('transport_vehicles as v', 'v.id', '=', 'f.vehicle_id')
            ->where('f.month_cycle', $month)
            ->where(function ($query) use ($cycle) {
                $query->whereDate('f.entry_date', '<', $cycle['start'])
                    ->orWhereDate('f.entry_date', '>', $cycle['end']);
            })
            ->select([
                'f.id',
                'f.month_cycle',
                'f.entry_date',
                'f.vehicle_id',
                'v.vehicle_code',
                'v.vehicle_name',
                'f.fuel_liters',
                'f.fuel_price',
                'f.fuel_cost',
            ])
            ->get();

        $expenseBlockers = $invalidFuelEntries->map(function ($row) use ($cycle) {
            return [
                'record_type' => 'fuel_entry',
                'record_id' => (int) $row->id,
                'vehicle_code' => $row->vehicle_code,
                'vehicle_name' => $row->vehicle_name,
                'entry_date' => $row->entry_date,
                'fuel_cost' => (float) $row->fuel_cost,
                'reason' => "Fuel entry date is outside billing cycle {$cycle['start']} to {$cycle['end']}.",
            ];
        })->values()->all();

        $adjustmentPlus = round((float) DB::table('transport_adjustments')
            ->where('month_cycle', $month)
            ->where('direction', 'plus')
            ->sum('amount'), 2);

        $adjustmentMinus = round((float) DB::table('transport_adjustments')
            ->where('month_cycle', $month)
            ->where('direction', 'minus')
            ->sum('amount'), 2);

        $totalExpense = round($totalRent + $totalFuelCost + $adjustmentPlus - $adjustmentMinus, 2);
        $companyShare = round($totalExpense * 0.50, 2);
        $employeeShare = round($totalExpense - $companyShare, 2);

        $candidateKids = DB::table('transport_school_van_enrolments as sve')
            ->join('family_members as fm', 'fm.id', '=', 'sve.family_member_id')
            ->leftJoin('employees_master as emp', 'emp.company_id', '=', 'fm.company_id')
            ->whereIn('sve.status', ['ACTIVE', 'LEFT'])
            ->whereDate('sve.joined_on', '<=', $cycle['end'])
            ->where(function ($query) use ($cycle) {
                $query->whereNull('sve.left_on')
                    ->orWhereDate('sve.left_on', '>=', $cycle['start']);
            })
            ->select([
                'sve.id as enrolment_id',
                'sve.joined_on',
                'sve.left_on',
                'fm.id as family_member_id',
                'fm.company_id',
                'fm.member_name as child_name',
                'emp.name as father_name',
            ])
            ->orderBy('fm.company_id')
            ->orderBy('fm.member_name')
            ->get();

        $allocationBlockers = [];
        $childAllocations = [];
        $chargeableUnits = 0.0;

        foreach ($candidateKids as $kid) {
            $factor = 1.0;
            $rule = 'FULL_CHARGE';

            if ($kid->joined_on > $cycle['start']) {
                $factor = 0.0;
                $rule = 'JOINED_DURING_CYCLE_RULE_PENDING';
                $allocationBlockers[] = [
                    'enrolment_id' => (int) $kid->enrolment_id,
                    'company_id' => $kid->company_id,
                    'child_name' => $kid->child_name,
                    'reason' => 'Join date after cycle start requires approved partial-cycle rule.',
                ];
            } elseif ($kid->left_on !== null) {
                if ($kid->left_on < $cycle['month_end']) {
                    $factor = 0.0;
                    $rule = 'LEFT_BEFORE_MONTH_END_RULE_PENDING';
                    $allocationBlockers[] = [
                        'enrolment_id' => (int) $kid->enrolment_id,
                        'company_id' => $kid->company_id,
                        'child_name' => $kid->child_name,
                        'reason' => 'Leave between cycle start and month-end requires approved rule.',
                    ];
                } elseif ($kid->left_on === $cycle['month_end']) {
                    $factor = 0.5;
                    $rule = 'MONTH_END_LEFT_HALF_CHARGE';
                } else {
                    $factor = 1.0;
                    $rule = 'LEFT_AFTER_MONTH_END_FULL_CHARGE';
                }
            }

            $chargeableUnits += $factor;

            $childAllocations[] = [
                'enrolment_id' => (int) $kid->enrolment_id,
                'family_member_id' => (int) $kid->family_member_id,
                'company_id' => (string) $kid->company_id,
                'father_name' => $kid->father_name,
                'child_name' => $kid->child_name,
                'joined_on' => $kid->joined_on,
                'left_on' => $kid->left_on,
                'charge_factor' => $factor,
                'charge_rule' => $rule,
            ];
        }

        $allocationStatus = count($expenseBlockers) > 0
            ? 'BLOCKED_EXPENSE_CORRECTION_REQUIRED'
            : (count($allocationBlockers) > 0
                ? 'BLOCKED_RULE_CONFIRMATION_REQUIRED'
                : 'READY');

        $perChildCharge = ($allocationStatus === 'READY' && $chargeableUnits > 0)
            ? round($employeeShare / $chargeableUnits, 2)
            : null;

        $employeeMap = [];

        foreach ($childAllocations as $child) {
            $companyId = $child['company_id'];

            if (!isset($employeeMap[$companyId])) {
                $employeeMap[$companyId] = [
                    'company_id' => $companyId,
                    'father_name' => $child['father_name'],
                    'children_count' => 0,
                    'chargeable_units' => 0.0,
                    'payable_amount' => $perChildCharge === null ? null : 0.0,
                    'rounding_adjustment' => 0.0,
                ];
            }

            $employeeMap[$companyId]['children_count']++;
            $employeeMap[$companyId]['chargeable_units'] += (float) $child['charge_factor'];

            if ($perChildCharge !== null) {
                $employeeMap[$companyId]['payable_amount'] = round(
                    $employeeMap[$companyId]['chargeable_units'] * $perChildCharge,
                    2
                );
            }
        }

        $employeeAllocations = array_values($employeeMap);

        if ($perChildCharge !== null && count($employeeAllocations) > 0) {
            $allocatedTotal = round(array_sum(array_column($employeeAllocations, 'payable_amount')), 2);
            $roundingDifference = round($employeeShare - $allocatedTotal, 2);

            if ($roundingDifference !== 0.0) {
                $lastIndex = array_key_last($employeeAllocations);
                $employeeAllocations[$lastIndex]['rounding_adjustment'] = $roundingDifference;
                $employeeAllocations[$lastIndex]['payable_amount'] = round(
                    $employeeAllocations[$lastIndex]['payable_amount'] + $roundingDifference,
                    2
                );
            }
        }

        $employeeAllocatedTotal = $perChildCharge === null
            ? null
            : round(array_sum(array_column($employeeAllocations, 'payable_amount')), 2);

        $rentEntries = DB::table('transport_rent_entries as r')
            ->join('transport_vehicles as v', 'v.id', '=', 'r.vehicle_id')
            ->where('r.month_cycle', $month)
            ->select(['r.id', 'r.month_cycle', 'r.vehicle_id', 'v.vehicle_name', 'v.vehicle_code', 'r.rent_amount', 'r.notes', 'r.created_at', 'r.updated_at'])
            ->orderBy('v.vehicle_name')
            ->orderByDesc('r.id')
            ->get();

        $fuelEntries = DB::table('transport_fuel_entries as f')
            ->join('transport_vehicles as v', 'v.id', '=', 'f.vehicle_id')
            ->where('f.month_cycle', $month)
            ->select(['f.id', 'f.month_cycle', 'f.entry_date', 'f.vehicle_id', 'v.vehicle_name', 'v.vehicle_code', 'f.fuel_liters', 'f.fuel_price', 'f.fuel_cost', 'f.slip_ref', 'f.notes'])
            ->orderByDesc('f.entry_date')
            ->orderByDesc('f.id')
            ->get();

        $adjustments = DB::table('transport_adjustments as a')
            ->leftJoin('transport_vehicles as v', 'v.id', '=', 'a.vehicle_id')
            ->where('a.month_cycle', $month)
            ->select(['a.id', 'a.month_cycle', 'a.vehicle_id', 'v.vehicle_name', 'v.vehicle_code', 'a.direction', 'a.amount', 'a.reason', 'a.notes', 'a.created_at'])
            ->orderByDesc('a.id')
            ->get();

        $monthState = $this->monthState($month);

        $generatedRowsQuery = DB::table('util_school_van_monthly_charge')
            ->where('month_cycle', $month)
            ->where('charged_flag', 1);

        $generatedRowsCount = (clone $generatedRowsQuery)->count();
        $generatedTotal = round((float) (clone $generatedRowsQuery)->sum('amount'), 2);

        $billGeneration = [
            'status' => $generatedRowsCount > 0 ? 'GENERATED' : 'NOT_GENERATED',
            'rows_count' => $generatedRowsCount,
            'generated_total' => $generatedTotal,
        ];

        $totals = [
            'van_rent' => $totalRent,
            'fuel_liters' => $totalFuelLiters,
            'fuel_cost' => $totalFuelCost,
            'adjustment_plus' => $adjustmentPlus,
            'adjustment_minus' => $adjustmentMinus,
            'total_cost' => $totalExpense,
            'total_expense' => $totalExpense,
            'company_share' => $companyShare,
            'father_share' => $employeeShare,
            'employee_share' => $employeeShare,
            'net_father_bill' => $employeeShare,
            'chargeable_kids' => count($childAllocations),
            'chargeable_units' => round($chargeableUnits, 2),
            'per_child_charge' => $perChildCharge,
            'employee_allocated_total' => $employeeAllocatedTotal,
        ];

        return [
            'status' => 'ok',
            'month_cycle' => $month,
            'billing_cycle' => $cycle,
            'month_lock' => [
                'state' => $monthState,
                'is_locked' => $monthState === 'LOCKED',
            ],
            'bill_generation' => $billGeneration,
            'allocation_status' => $allocationStatus,
            'allocation_blockers' => $allocationBlockers,
            'expense_blockers' => $expenseBlockers,
            'father_bill' => [
                'month_cycle' => $month,
                'total_rent' => $totalRent,
                'total_fuel_cost' => $totalFuelCost,
                'total_cost' => $totalExpense,
                'total_expense' => $totalExpense,
                'company_share' => $companyShare,
                'father_share' => $employeeShare,
                'employee_share' => $employeeShare,
                'plus_adjustments' => $adjustmentPlus,
                'minus_adjustments' => $adjustmentMinus,
                'net_father_bill' => $employeeShare,
                'chargeable_kids' => count($childAllocations),
                'chargeable_units' => round($chargeableUnits, 2),
                'per_child_charge' => $perChildCharge,
                'employee_allocated_total' => $employeeAllocatedTotal,
                'vehicle_rows' => $rows,
                'employee_allocations' => $employeeAllocations,
            ],
            'formula' => [
                'total_expense' => 'Rent + Fuel Cost + Plus Adjustment - Minus Adjustment',
                'company_share' => 'Total Expense × 50%',
                'employee_share' => 'Total Expense × 50%',
                'per_child_charge' => 'Employee Share ÷ Chargeable Child Units',
                'employee_payable' => 'Per Child Charge × Employee Chargeable Child Units ± Rounding Adjustment',
            ],
            'vehicles' => $vehicles,
            'rows' => $rows,
            'totals' => $totals,
            'rent_entries' => $rentEntries,
            'fuel_entries' => $fuelEntries,
            'adjustments' => $adjustments,
            'child_allocations' => $childAllocations,
            'employee_allocations' => $employeeAllocations,
        ];
    }

    public function childMonthUsage(?string $monthCycle): array
    {
        $month = $this->normalizeMonthCycle((string) ($monthCycle ?? ''));
        if (!$this->monthValid($month)) {
            return ['_http' => 400, 'status' => 'error', 'error' => 'month_cycle is required in MM-YYYY'];
        }

        $rows = DB::select(
            "SELECT u.id, u.month_cycle, u.child_profile_id, u.usage_status, u.usage_from_date, u.usage_to_date,
                    u.vehicle_id, v.vehicle_name, v.vehicle_code, u.route_label, u.charge_amount, u.remarks,
                    p.company_id, p.child_name, p.school_name, p.class_grade, p.default_route_label,
                    e.name AS father_name, e.room_no
             FROM transport_child_month_usage u
             INNER JOIN family_child_profiles p ON p.id = u.child_profile_id
             LEFT JOIN transport_vehicles v ON v.id = u.vehicle_id
             LEFT JOIN employees_master e ON e.company_id = p.company_id
             WHERE u.month_cycle=?
             ORDER BY p.company_id ASC, p.sort_order ASC, p.id ASC",
            [$month]
        );

        $profiles = DB::select(
            "SELECT p.id, p.company_id, p.child_name, p.school_name, p.class_grade, p.school_going, p.van_using,
                    p.transport_join_date, p.transport_leave_date, p.default_route_label, p.is_active, p.sort_order,
                    e.name AS father_name, e.room_no
             FROM family_child_profiles p
             LEFT JOIN employees_master e ON e.company_id = p.company_id
             ORDER BY p.company_id ASC, p.sort_order ASC, p.id ASC"
        );

        return [
            'status' => 'ok',
            'month_cycle' => $month,
            'rows' => $rows,
            'child_profiles' => $profiles,
        ];
    }

    public function childMonthUsageUpsert(array $payload): array
    {
        $month = $this->normalizeMonthCycle((string) ($payload['month_cycle'] ?? ''));
        if (!$this->monthValid($month)) {
            return ['_http' => 400, 'status' => 'error', 'message' => 'Valid month_cycle is required (MM-YYYY).'];
        }
        if ($this->monthState($month) === 'LOCKED') {
            return $this->blockedLockedMonth($month, 'transport_child_month_usage_upsert');
        }

        return DB::transaction(function () use ($payload, $month) {
            $profileId = (int) ($payload['child_profile_id'] ?? 0);
            if ($profileId <= 0) {
                return ['_http' => 400, 'status' => 'error', 'message' => 'child_profile_id is required.'];
            }

            $existingId = DB::table('transport_child_month_usage')
                ->where('month_cycle', $month)
                ->where('child_profile_id', $profileId)
                ->value('id');

            $data = [
                'month_cycle' => $month,
                'child_profile_id' => $profileId,
                'usage_status' => isset($payload['usage_status']) ? trim((string) $payload['usage_status']) : null,
                'usage_from_date' => $payload['usage_from_date'] ?? null,
                'usage_to_date' => $payload['usage_to_date'] ?? null,
                'vehicle_id' => isset($payload['vehicle_id']) && $payload['vehicle_id'] !== '' ? (int) $payload['vehicle_id'] : null,
                'route_label' => isset($payload['route_label']) ? trim((string) $payload['route_label']) : null,
                'charge_amount' => isset($payload['charge_amount']) && $payload['charge_amount'] !== '' ? round((float) $payload['charge_amount'], 2) : null,
                'remarks' => isset($payload['remarks']) ? trim((string) $payload['remarks']) : null,
                'updated_at' => now(),
            ];

            if ($existingId) {
                DB::table('transport_child_month_usage')->where('id', $existingId)->update($data);
                return $this->ok('Child transport month usage updated successfully.', $month, [
                    'record_type' => 'transport_child_month_usage',
                    'record_id' => (int) $existingId,
                ]);
            }

            $newId = DB::table('transport_child_month_usage')->insertGetId($data + ['created_at' => now()]);
            return $this->ok('Child transport month usage saved successfully.', $month, [
                'record_type' => 'transport_child_month_usage',
                'record_id' => $newId,
            ]);
        });
    }


    public function schoolVanEnrolments(): array
    {
        $rows = DB::table('transport_school_van_enrolments as sve')
            ->join('family_members as fm', 'fm.id', '=', 'sve.family_member_id')
            ->leftJoin('employees_master as emp', 'emp.company_id', '=', 'fm.company_id')
            ->leftJoin('transport_vehicles as tv', 'tv.id', '=', 'sve.vehicle_id')
            ->select([
                'sve.id',
                'sve.family_member_id',
                'sve.vehicle_id',
                'sve.joined_on',
                'sve.left_on',
                'sve.left_reason',
                'sve.left_remarks',
                'sve.status',
                'sve.source',
                'sve.route_label',
                'sve.remarks',
                'sve.cancel_reason',
                'sve.cancellation_remarks',
                'sve.cancelled_at',
                'sve.cancelled_by_user_id',
                'sve.reactivation_reason',
                'sve.reactivation_remarks',
                'sve.reactivated_at',
                'sve.reactivated_by_user_id',
                'sve.cancelled_from_status',
                'sve.cancellation_reversal_reason',
                'sve.cancellation_reversal_remarks',
                'sve.cancellation_reversed_at',
                'sve.cancellation_reversed_by_user_id',
                'fm.company_id',
                'fm.member_name as child_name',
                'fm.relation',
                'fm.school_name',
                'fm.class_name',
                'emp.name as father_name',
                'tv.vehicle_code',
                'tv.vehicle_name',
            ])
            ->orderByRaw("CASE WHEN sve.status = 'ACTIVE' THEN 0 ELSE 1 END")
            ->orderBy('fm.company_id')
            ->orderBy('fm.member_name')
            ->get();

        $eligibleKids = DB::table('family_members as fm')
            ->leftJoin('employees_master as emp', 'emp.company_id', '=', 'fm.company_id')
            ->leftJoin('transport_school_van_enrolments as active_sve', function ($join) {
                $join->on('active_sve.family_member_id', '=', 'fm.id')
                    ->where('active_sve.status', '=', 'ACTIVE');
            })
            ->whereIn('fm.relation', ['Son', 'Daughter'])
            ->where('fm.school_going', 1)
            ->where('fm.is_active', 1)
            ->whereNull('active_sve.id')
            ->select([
                'fm.id as family_member_id',
                'fm.company_id',
                'fm.member_name as child_name',
                'fm.relation',
                'fm.school_name',
                'fm.class_name',
                'emp.name as father_name',
            ])
            ->orderBy('fm.company_id')
            ->orderBy('fm.member_name')
            ->get();

        return [
            'status' => 'ok',
            'rows' => $rows,
            'eligible_kids' => $eligibleKids,
            'totals' => [
                'all_enrolments' => $rows->count(),
                'active_enrolments' => $rows->where('status', 'ACTIVE')->count(),
                'left_enrolments' => $rows->where('status', 'LEFT')->count(),
                'cancelled_enrolments' => $rows->where('status', 'CANCELLED')->count(),
                'eligible_not_enrolled' => $eligibleKids->count(),
            ],
        ];
    }

    public function schoolVanEnrolmentAdd(array $payload): array
    {
        $familyMemberId = (int) ($payload['family_member_id'] ?? 0);
        $joinedOn = trim((string) ($payload['joined_on'] ?? ''));
        $vehicleId = isset($payload['vehicle_id']) && $payload['vehicle_id'] !== ''
            ? (int) $payload['vehicle_id']
            : null;
        $routeLabel = trim((string) ($payload['route_label'] ?? ''));
        $remarks = trim((string) ($payload['remarks'] ?? ''));

        if ($familyMemberId <= 0) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Valid school-going child is required.'];
        }

        if ($joinedOn === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $joinedOn)) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Valid joined_on date is required.'];
        }

        if ($joinedOn > now()->toDateString()) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Future join date is not allowed.'];
        }

        if ($guard = $this->schoolVanOpenCycleGuard($joinedOn, 'school_van_enrolment_add')) {
            return $guard;
        }

        $child = DB::table('family_members')
            ->where('id', $familyMemberId)
            ->whereIn('relation', ['Son', 'Daughter'])
            ->where('school_going', 1)
            ->where('is_active', 1)
            ->first();

        if (!$child) {
            return ['_http' => 404, 'status' => 'error', 'message' => 'Eligible school-going family child not found.'];
        }

        $alreadyActive = DB::table('transport_school_van_enrolments')
            ->where('family_member_id', $familyMemberId)
            ->where('status', 'ACTIVE')
            ->exists();

        if ($alreadyActive) {
            return ['_http' => 409, 'status' => 'error', 'message' => 'This child is already active in school van.'];
        }

        if ($vehicleId !== null && !DB::table('transport_vehicles')->where('id', $vehicleId)->exists()) {
            return ['_http' => 404, 'status' => 'error', 'message' => 'Selected vehicle not found.'];
        }

        return DB::transaction(function () use ($familyMemberId, $joinedOn, $vehicleId, $routeLabel, $remarks) {
            $newId = DB::table('transport_school_van_enrolments')->insertGetId([
                'family_member_id' => $familyMemberId,
                'vehicle_id' => $vehicleId,
                'joined_on' => $joinedOn,
                'left_on' => null,
                'status' => 'ACTIVE',
                'source' => 'FAMILY_DETAILS',
                'route_label' => $routeLabel !== '' ? $routeLabel : null,
                'remarks' => $remarks !== '' ? $remarks : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $after = DB::table('transport_school_van_enrolments')->where('id', $newId)->first();
            $this->auditLog('school_van_enrolment_add', 'school_van_enrolment', $newId, null, null, $after);

            return [
                'status' => 'ok',
                'message' => 'Child added to school van successfully.',
                'enrolment_id' => $newId,
            ];
        });
    }

    public function schoolVanEnrolmentLeave(int $enrolmentId, array $payload): array
    {
        $leftOn = trim((string) ($payload['left_on'] ?? ''));
        $leftReason = strtoupper(trim((string) ($payload['left_reason'] ?? '')));
        $leftRemarks = trim((string) ($payload['left_remarks'] ?? ''));

        if ($leftOn === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $leftOn)) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Valid left date is required.'];
        }

        if (!array_key_exists($leftReason, self::SCHOOL_VAN_LEFT_REASONS)) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Valid left reason is required.'];
        }

        if ($leftReason === 'OTHER' && $leftRemarks === '') {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Remarks are required when left reason is Other.'];
        }

        if ($leftOn > now()->toDateString()) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Future leave date is not allowed.'];
        }

        if ($guard = $this->schoolVanOpenCycleGuard($leftOn, 'school_van_enrolment_left')) {
            return $guard;
        }

        return DB::transaction(function () use ($enrolmentId, $leftOn, $leftReason, $leftRemarks) {
            $before = DB::table('transport_school_van_enrolments')
                ->where('id', $enrolmentId)
                ->lockForUpdate()
                ->first();

            if (!$before) {
                return ['_http' => 404, 'status' => 'error', 'message' => 'School van enrolment not found.'];
            }

            if ($before->status !== 'ACTIVE') {
                return ['_http' => 409, 'status' => 'error', 'message' => 'Only active school van enrolment can be marked left.'];
            }

            if ($leftOn < $before->joined_on) {
                return ['_http' => 422, 'status' => 'error', 'message' => 'Leave date cannot be before join date.'];
            }

            DB::table('transport_school_van_enrolments')
                ->where('id', $enrolmentId)
                ->update([
                    'left_on' => $leftOn,
                    'left_reason' => $leftReason,
                    'left_remarks' => $leftRemarks !== '' ? $leftRemarks : null,
                    'status' => 'LEFT',
                    'updated_at' => now(),
                ]);

            $after = DB::table('transport_school_van_enrolments')->where('id', $enrolmentId)->first();
            $this->auditLog('school_van_enrolment_left', 'school_van_enrolment', $enrolmentId, null, $before, $after);

            return [
                'status' => 'ok',
                'message' => 'Child marked left from school van successfully.',
                'enrolment_id' => $enrolmentId,
            ];
        });
    }

    public function schoolVanEnrolmentCancel(int $enrolmentId, array $payload): array
    {
        $reason = strtoupper(trim((string) ($payload['cancel_reason'] ?? '')));
        $remarks = trim((string) ($payload['remarks'] ?? ''));

        if (!array_key_exists($reason, self::SCHOOL_VAN_CANCEL_REASONS)) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Valid cancellation reason is required.'];
        }

        if ($reason === 'OTHER_ADMINISTRATIVE_CORRECTION' && $remarks === '') {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Remarks are required for Other Administrative Correction.'];
        }

        return DB::transaction(function () use ($enrolmentId, $reason, $remarks) {
            $before = DB::table('transport_school_van_enrolments')
                ->where('id', $enrolmentId)
                ->lockForUpdate()
                ->first();

            if (!$before) {
                return ['_http' => 404, 'status' => 'error', 'message' => 'School van enrolment not found.'];
            }

            if ($before->status === 'CANCELLED') {
                return ['_http' => 409, 'status' => 'error', 'message' => 'This school van enrolment is already cancelled.'];
            }

            if (!in_array($before->status, ['ACTIVE', 'LEFT'], true)) {
                return ['_http' => 409, 'status' => 'error', 'message' => 'Only active or left enrolment can be cancelled.'];
            }

            if ($guard = $this->schoolVanOpenCycleGuard((string) $before->joined_on, 'school_van_enrolment_cancel')) {
                return $guard;
            }

            $cycle = $this->schoolVanCycleForDate((string) $before->joined_on);

            DB::table('transport_school_van_enrolments')
                ->where('id', $enrolmentId)
                ->update([
                    'status' => 'CANCELLED',
                    'cancel_reason' => $reason,
                    'cancelled_from_status' => $before->status,
                    'cancellation_remarks' => $remarks !== '' ? $remarks : null,
                    'cancelled_at' => now(),
                    'cancelled_by_user_id' => $this->actorId(),
                    'updated_at' => now(),
                ]);

            $after = DB::table('transport_school_van_enrolments')->where('id', $enrolmentId)->first();

            $this->auditLog(
                'school_van_enrolment_cancelled',
                'school_van_enrolment',
                $enrolmentId,
                $cycle['month_cycle'] ?? null,
                $before,
                $after
            );

            return [
                'status' => 'ok',
                'message' => 'Wrong school van entry cancelled successfully. It is excluded from billing.',
                'enrolment_id' => $enrolmentId,
                'cancel_reason' => $reason,
            ];
        });
    }

    public function schoolVanEnrolmentReactivate(int $enrolmentId, array $payload): array
    {
        $reason = strtoupper(trim((string) ($payload['reactivation_reason'] ?? '')));
        $remarks = trim((string) ($payload['remarks'] ?? ''));

        if (!array_key_exists($reason, self::SCHOOL_VAN_REACTIVATION_REASONS)) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Valid reactivation reason is required.'];
        }

        return DB::transaction(function () use ($enrolmentId, $reason, $remarks) {
            $before = DB::table('transport_school_van_enrolments')
                ->where('id', $enrolmentId)
                ->lockForUpdate()
                ->first();

            if (!$before) {
                return ['_http' => 404, 'status' => 'error', 'message' => 'School van enrolment not found.'];
            }

            if ($before->status !== 'LEFT') {
                return ['_http' => 409, 'status' => 'error', 'message' => 'Only left enrolment can be reactivated.'];
            }

            $actionDate = (string) ($before->left_on ?: $before->joined_on);

            if ($guard = $this->schoolVanOpenCycleGuard($actionDate, 'school_van_enrolment_reactivate')) {
                return $guard;
            }

            $alreadyActive = DB::table('transport_school_van_enrolments')
                ->where('family_member_id', $before->family_member_id)
                ->where('status', 'ACTIVE')
                ->where('id', '!=', $enrolmentId)
                ->exists();

            if ($alreadyActive) {
                return ['_http' => 409, 'status' => 'error', 'message' => 'Another active enrolment already exists for this child.'];
            }

            $cycle = $this->schoolVanCycleForDate($actionDate);

            DB::table('transport_school_van_enrolments')
                ->where('id', $enrolmentId)
                ->update([
                    'left_on' => null,
                    'left_reason' => null,
                    'left_remarks' => null,
                    'status' => 'ACTIVE',
                    'reactivation_reason' => $reason,
                    'reactivation_remarks' => $remarks !== '' ? $remarks : null,
                    'reactivated_at' => now(),
                    'reactivated_by_user_id' => $this->actorId(),
                    'updated_at' => now(),
                ]);

            $after = DB::table('transport_school_van_enrolments')->where('id', $enrolmentId)->first();

            $this->auditLog(
                'school_van_enrolment_reactivated',
                'school_van_enrolment',
                $enrolmentId,
                $cycle['month_cycle'] ?? null,
                $before,
                $after
            );

            return [
                'status' => 'ok',
                'message' => 'School van enrolment reactivated successfully.',
                'enrolment_id' => $enrolmentId,
                'reactivation_reason' => $reason,
            ];
        });
    }

    public function schoolVanEnrolmentRestoreCancellation(int $enrolmentId, array $payload): array
    {
        $reason = strtoupper(trim((string) ($payload['cancellation_reversal_reason'] ?? '')));
        $remarks = trim((string) ($payload['remarks'] ?? ''));

        if (!array_key_exists($reason, self::SCHOOL_VAN_CANCELLATION_REVERSAL_REASONS)) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Valid restore cancellation reason is required.'];
        }

        return DB::transaction(function () use ($enrolmentId, $reason, $remarks) {
            $before = DB::table('transport_school_van_enrolments')
                ->where('id', $enrolmentId)
                ->lockForUpdate()
                ->first();

            if (!$before) {
                return ['_http' => 404, 'status' => 'error', 'message' => 'School van enrolment not found.'];
            }

            if ($before->status !== 'CANCELLED') {
                return ['_http' => 409, 'status' => 'error', 'message' => 'Only cancelled enrolment can be restored.'];
            }

            $restoreStatus = strtoupper(trim((string) ($before->cancelled_from_status ?? '')));

            if (!in_array($restoreStatus, ['ACTIVE', 'LEFT'], true)) {
                return ['_http' => 409, 'status' => 'error', 'message' => 'Original status is missing. Restore is blocked until audit correction is completed.'];
            }

            if ($guard = $this->schoolVanOpenCycleGuard((string) $before->joined_on, 'school_van_enrolment_restore_cancellation')) {
                return $guard;
            }

            if ($restoreStatus === 'ACTIVE') {
                $alreadyActive = DB::table('transport_school_van_enrolments')
                    ->where('family_member_id', $before->family_member_id)
                    ->where('status', 'ACTIVE')
                    ->where('id', '!=', $enrolmentId)
                    ->exists();

                if ($alreadyActive) {
                    return ['_http' => 409, 'status' => 'error', 'message' => 'Another active enrolment already exists for this child.'];
                }
            }

            $cycle = $this->schoolVanCycleForDate((string) $before->joined_on);

            DB::table('transport_school_van_enrolments')
                ->where('id', $enrolmentId)
                ->update([
                    'status' => $restoreStatus,
                    'left_on' => $restoreStatus === 'ACTIVE' ? null : $before->left_on,
                    'cancellation_reversal_reason' => $reason,
                    'cancellation_reversal_remarks' => $remarks !== '' ? $remarks : null,
                    'cancellation_reversed_at' => now(),
                    'cancellation_reversed_by_user_id' => $this->actorId(),
                    'updated_at' => now(),
                ]);

            $after = DB::table('transport_school_van_enrolments')->where('id', $enrolmentId)->first();

            $this->auditLog(
                'school_van_enrolment_cancellation_restored',
                'school_van_enrolment',
                $enrolmentId,
                $cycle['month_cycle'] ?? null,
                $before,
                $after
            );

            return [
                'status' => 'ok',
                'message' => 'Cancelled school van entry restored to its original status successfully.',
                'enrolment_id' => $enrolmentId,
                'restored_status' => $restoreStatus,
                'cancellation_reversal_reason' => $reason,
            ];
        });
    }

    public function generateSchoolVanBill(array $payload): array
    {
        $month = $this->normalizeMonthCycle((string) ($payload['month_cycle'] ?? ''));

        if (!$this->monthValid($month)) {
            return ['_http' => 422, 'status' => 'error', 'message' => 'Valid month cycle is required in MM-YYYY format.'];
        }

        if ($this->monthState($month) === 'LOCKED') {
            return $this->blockedLockedMonth($month, 'school_van_bill_generate');
        }

        if ($this->hasGeneratedSchoolVanBill($month)) {
            return [
                '_http' => 409,
                'status' => 'error',
                'message' => "School van bill for {$month} is already generated.",
                'bill_status' => 'GENERATED',
            ];
        }

        $summary = $this->summary($month);

        if (($summary['allocation_status'] ?? '') !== 'READY') {
            return [
                '_http' => 409,
                'status' => 'error',
                'message' => 'School van bill cannot be generated until allocation status is READY.',
                'allocation_status' => $summary['allocation_status'] ?? 'UNAVAILABLE',
                'expense_blockers' => $summary['expense_blockers'] ?? [],
                'allocation_blockers' => $summary['allocation_blockers'] ?? [],
            ];
        }

        $children = (array) ($summary['child_allocations'] ?? []);
        $perChildRate = round((float) ($summary['totals']['per_child_charge'] ?? 0), 2);
        $employeeShare = round((float) ($summary['totals']['employee_share'] ?? 0), 2);

        if (empty($children) || $perChildRate <= 0 || $employeeShare <= 0) {
            return ['_http' => 409, 'status' => 'error', 'message' => 'No chargeable School Van rows available for generation.'];
        }

        $familyIds = array_values(array_unique(array_map(
            fn ($child) => (int) ($child['family_member_id'] ?? 0),
            $children
        )));

        $familyRows = DB::table('family_members')
            ->whereIn('id', $familyIds)
            ->get(['id', 'school_name', 'class_name'])
            ->keyBy('id');

        $actor = (string) (session('actor_user_id') ?? session('user_id') ?? 'system');
        $generatedRows = [];

        foreach ($children as $child) {
            $family = $familyRows->get((int) $child['family_member_id']);
            $factor = round((float) ($child['charge_factor'] ?? 0), 4);
            $amount = round($perChildRate * $factor, 2);

            $generatedRows[] = [
                'month_cycle' => $month,
                'employee_id' => (string) $child['company_id'],
                'enrolment_id' => (int) $child['enrolment_id'],
                'family_member_id' => (int) $child['family_member_id'],
                'child_name' => (string) $child['child_name'],
                'school_name' => $family?->school_name,
                'class_level' => $family?->class_name,
                'service_mode' => 'SCHOOL_VAN',
                'charge_factor' => $factor,
                'charge_rule' => (string) ($child['charge_rule'] ?? 'FULL_CHARGE'),
                'rate' => $perChildRate,
                'amount' => $amount,
                'rounding_adjustment' => 0.00,
                'charged_flag' => 1,
                'generated_at' => now(),
                'generated_by_user_id' => $actor,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $generatedTotal = round(array_sum(array_column($generatedRows, 'amount')), 2);
        $roundingDifference = round($employeeShare - $generatedTotal, 2);

        if ($roundingDifference !== 0.0) {
            $lastIndex = array_key_last($generatedRows);
            $generatedRows[$lastIndex]['rounding_adjustment'] = $roundingDifference;
            $generatedRows[$lastIndex]['amount'] = round(
                (float) $generatedRows[$lastIndex]['amount'] + $roundingDifference,
                2
            );
        }

        $finalTotal = round(array_sum(array_column($generatedRows, 'amount')), 2);

        if ($finalTotal !== $employeeShare) {
            return ['_http' => 500, 'status' => 'error', 'message' => 'Generated bill total does not match employee share.'];
        }

        return DB::transaction(function () use ($month, $generatedRows, $finalTotal, $actor) {
            if ($this->hasGeneratedSchoolVanBill($month)) {
                return [
                    '_http' => 409,
                    'status' => 'error',
                    'message' => "School van bill for {$month} is already generated.",
                    'bill_status' => 'GENERATED',
                ];
            }

            DB::table('util_school_van_monthly_charge')->insert($generatedRows);

            $rows = DB::table('util_school_van_monthly_charge')
                ->where('month_cycle', $month)
                ->where('charged_flag', 1)
                ->orderBy('employee_id')
                ->orderBy('child_name')
                ->get();

            DB::table('util_audit_log')->insert([
                'entity_type' => 'transport',
                'entity_id' => $month,
                'action' => 'school_van_bill_generated',
                'actor_user_id' => $actor,
                'before_json' => json_encode([
                    'month_cycle' => $month,
                    'generated_rows' => 0,
                ], JSON_UNESCAPED_UNICODE),
                'after_json' => json_encode([
                    'month_cycle' => $month,
                    'generated_rows' => $rows->count(),
                    'generated_total' => $finalTotal,
                ], JSON_UNESCAPED_UNICODE),
                'correlation_id' => 'school-van-bill-'.$month,
                'created_at' => now(),
            ]);

            return [
                'status' => 'ok',
                'message' => 'School van bill generated successfully.',
                'month_cycle' => $month,
                'generated_rows' => $rows->count(),
                'generated_total' => $finalTotal,
                'bill_status' => 'GENERATED',
            ];
        });
    }

    public function exportCsv(?string $monthCycle): array
    {
        $summary = $this->summary($monthCycle);
        $code = (int) ($summary['_http'] ?? 200);
        if ($code !== 200) {
            return $summary;
        }

        $month = (string) $summary['month_cycle'];
        $bill = (array) ($summary['father_bill'] ?? []);
        $rows = (array) ($bill['vehicle_rows'] ?? []);

        $lines = [];
        $lines[] = 'TRANSPORT MONTHLY FATHER BILL';
        $lines[] = 'Month Cycle,'. $month;
        $lines[] = '';
        $lines[] = 'SUMMARY TOTALS';
        $lines[] = 'metric,value';
        $lines[] = 'total_rent,'.number_format((float) ($bill['total_rent'] ?? 0), 2, '.', '');
        $lines[] = 'total_fuel_cost,'.number_format((float) ($bill['total_fuel_cost'] ?? 0), 2, '.', '');
        $lines[] = 'total_cost,'.number_format((float) ($bill['total_cost'] ?? 0), 2, '.', '');
        $lines[] = 'company_share,'.number_format((float) ($bill['company_share'] ?? 0), 2, '.', '');
        $lines[] = 'father_share,'.number_format((float) ($bill['father_share'] ?? 0), 2, '.', '');
        $lines[] = 'plus_adjustments,'.number_format((float) ($bill['plus_adjustments'] ?? 0), 2, '.', '');
        $lines[] = 'minus_adjustments,'.number_format((float) ($bill['minus_adjustments'] ?? 0), 2, '.', '');
        $lines[] = 'net_father_bill,'.number_format((float) ($bill['net_father_bill'] ?? 0), 2, '.', '');
        $lines[] = '';
        $lines[] = 'PER VEHICLE BREAKDOWN';
        $lines[] = 'vehicle,vehicle_code,rent,fuel_cost,total_cost,father_share,adj_plus,adj_minus,net_father_bill';

        foreach ($rows as $row) {
            $vehicleName = '"'.str_replace('"', '""', (string) ($row->vehicle_name ?? '')).'"';
            $vehicleCode = '"'.str_replace('"', '""', (string) ($row->vehicle_code ?? '')).'"';
            $lines[] = implode(',', [
                $vehicleName,
                $vehicleCode,
                number_format((float) ($row->van_rent ?? 0), 2, '.', ''),
                number_format((float) ($row->fuel_cost ?? 0), 2, '.', ''),
                number_format((float) ($row->total_cost ?? 0), 2, '.', ''),
                number_format((float) ($row->father_share ?? 0), 2, '.', ''),
                number_format((float) ($row->adjustment_plus ?? 0), 2, '.', ''),
                number_format((float) ($row->adjustment_minus ?? 0), 2, '.', ''),
                number_format((float) ($row->net_father_bill ?? 0), 2, '.', ''),
            ]);
        }

        return [
            '_http' => 200,
            'content' => implode("\r\n", $lines)."\r\n",
            'content_type' => 'text/csv; charset=UTF-8',
            'filename' => 'transport-father-bill-'.$month.'.csv',
        ];
    }
}
