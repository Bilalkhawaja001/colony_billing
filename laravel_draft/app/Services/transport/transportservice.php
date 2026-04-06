<?php

namespace App\Services\Transport;

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

    public function summary(?string $monthCycle): array
    {
        $month = $this->normalizeMonthCycle((string) ($monthCycle ?? ''));

        if (!$this->monthValid($month)) {
            return ['_http' => 400, 'status' => 'error', 'error' => 'month_cycle is required in MM-YYYY'];
        }

        $vehicles = DB::select(
            'SELECT id, vehicle_code, vehicle_name, is_active, notes
             FROM transport_vehicles
             ORDER BY is_active DESC, vehicle_name ASC'
        );

        $rows = DB::select(
            "SELECT v.id AS vehicle_id,
                    v.vehicle_code,
                    v.vehicle_name,
                    COALESCE(r.rent_amount, 0) AS van_rent,
                    COALESCE(f.fuel_liters, 0) AS fuel_liters,
                    COALESCE(f.fuel_cost, 0) AS fuel_cost,
                    COALESCE(a.adjustment_plus, 0) AS adjustment_plus,
                    COALESCE(a.adjustment_minus, 0) AS adjustment_minus,
                    ROUND(COALESCE(r.rent_amount, 0) + COALESCE(f.fuel_cost, 0), 2) AS total_cost,
                    ROUND((COALESCE(r.rent_amount, 0) + COALESCE(f.fuel_cost, 0)) * 0.5, 2) AS company_share,
                    ROUND((COALESCE(r.rent_amount, 0) + COALESCE(f.fuel_cost, 0)) * 0.5, 2) AS father_share,
                    ROUND(((COALESCE(r.rent_amount, 0) + COALESCE(f.fuel_cost, 0)) * 0.5) + COALESCE(a.adjustment_plus, 0) - COALESCE(a.adjustment_minus, 0), 2) AS net_father_bill
             FROM transport_vehicles v
             LEFT JOIN (
                 SELECT vehicle_id, month_cycle, SUM(rent_amount) AS rent_amount
                 FROM transport_rent_entries
                 WHERE month_cycle=?
                 GROUP BY vehicle_id, month_cycle
             ) r ON r.vehicle_id = v.id
             LEFT JOIN (
                 SELECT vehicle_id, month_cycle, SUM(fuel_liters) AS fuel_liters, SUM(fuel_cost) AS fuel_cost
                 FROM transport_fuel_entries
                 WHERE month_cycle=?
                 GROUP BY vehicle_id, month_cycle
             ) f ON f.vehicle_id = v.id
             LEFT JOIN (
                 SELECT vehicle_id,
                        month_cycle,
                        SUM(CASE WHEN direction = 'plus' THEN amount ELSE 0 END) AS adjustment_plus,
                        SUM(CASE WHEN direction = 'minus' THEN amount ELSE 0 END) AS adjustment_minus
                 FROM transport_adjustments
                 WHERE month_cycle=?
                 GROUP BY vehicle_id, month_cycle
             ) a ON a.vehicle_id = v.id
             ORDER BY v.vehicle_name ASC",
            [$month, $month, $month]
        );

        $totals = [
            'van_rent' => 0.0,
            'fuel_liters' => 0.0,
            'fuel_cost' => 0.0,
            'adjustment_plus' => 0.0,
            'adjustment_minus' => 0.0,
            'total_cost' => 0.0,
            'company_share' => 0.0,
            'father_share' => 0.0,
            'net_father_bill' => 0.0,
        ];

        foreach ($rows as $row) {
            $totals['van_rent'] += (float) $row->van_rent;
            $totals['fuel_liters'] += (float) $row->fuel_liters;
            $totals['fuel_cost'] += (float) $row->fuel_cost;
            $totals['adjustment_plus'] += (float) $row->adjustment_plus;
            $totals['adjustment_minus'] += (float) $row->adjustment_minus;
            $totals['total_cost'] += (float) $row->total_cost;
            $totals['company_share'] += (float) $row->company_share;
            $totals['father_share'] += (float) $row->father_share;
            $totals['net_father_bill'] += (float) $row->net_father_bill;
        }

        foreach ($totals as $key => $value) {
            $totals[$key] = round($value, 2);
        }

        $fuelEntries = DB::select(
            'SELECT f.id, f.month_cycle, f.entry_date, f.vehicle_id, v.vehicle_name, v.vehicle_code,
                    f.fuel_liters, f.fuel_price, f.fuel_cost, f.slip_ref, f.notes
             FROM transport_fuel_entries f
             INNER JOIN transport_vehicles v ON v.id = f.vehicle_id
             WHERE f.month_cycle=?
             ORDER BY f.entry_date DESC, f.id DESC',
            [$month]
        );

        $adjustments = DB::select(
            'SELECT a.id, a.month_cycle, a.vehicle_id, v.vehicle_name, v.vehicle_code,
                    a.direction, a.amount, a.reason, a.notes, a.created_at
             FROM transport_adjustments a
             LEFT JOIN transport_vehicles v ON v.id = a.vehicle_id
             WHERE a.month_cycle=?
             ORDER BY a.id DESC',
            [$month]
        );

        $rentEntries = DB::select(
            'SELECT r.id, r.month_cycle, r.vehicle_id, v.vehicle_name, v.vehicle_code,
                    r.rent_amount, r.notes, r.created_at, r.updated_at
             FROM transport_rent_entries r
             INNER JOIN transport_vehicles v ON v.id = r.vehicle_id
             WHERE r.month_cycle=?
             ORDER BY v.vehicle_name ASC, r.id DESC',
            [$month]
        );

        $monthState = $this->monthState($month);

        return [
            'status' => 'ok',
            'month_cycle' => $month,
            'month_lock' => [
                'state' => $monthState,
                'is_locked' => $monthState === 'LOCKED',
            ],
            'father_bill' => [
                'month_cycle' => $month,
                'total_rent' => $totals['van_rent'],
                'total_fuel_cost' => $totals['fuel_cost'],
                'total_cost' => $totals['total_cost'],
                'company_share' => $totals['company_share'],
                'father_share' => $totals['father_share'],
                'plus_adjustments' => $totals['adjustment_plus'],
                'minus_adjustments' => $totals['adjustment_minus'],
                'net_father_bill' => $totals['net_father_bill'],
                'vehicle_rows' => $rows,
            ],
            'formula' => [
                'total_cost' => 'Van Rent + (Fuel Liters × Fuel Price)',
                'company_share' => '50%',
                'father_share' => '50%',
                'net_father_bill' => 'Father Share ± Adjustments',
            ],
            'vehicles' => $vehicles,
            'rows' => $rows,
            'totals' => $totals,
            'rent_entries' => $rentEntries,
            'fuel_entries' => $fuelEntries,
            'adjustments' => $adjustments,
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
