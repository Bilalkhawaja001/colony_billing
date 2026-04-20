<?php

namespace App\Services\ElectricV1;

use App\Repositories\ElectricV1\{AllowanceRepository, ReadingsRepository, AttendanceRepository, OccupancyRepository, AdjustmentsRepository, OutputRepository, AuditRepository, MasterRepository, ControlRepository};
use App\Services\ElectricV1\Domain\{Validators, ConsumptionEngine, ExplicitElectricBillingCalculator};
use App\Models\ElectricActiveDaysMonthly;
use Illuminate\Support\Facades\DB;

class OrchestrationService
{
    public function __construct(
        private readonly ControlRepository $control,
        private readonly MasterRepository $master,
        private readonly AllowanceRepository $allowance,
        private readonly ReadingsRepository $readings,
        private readonly AttendanceRepository $attendance,
        private readonly OccupancyRepository $occupancy,
        private readonly AdjustmentsRepository $adjustments,
        private readonly OutputRepository $output,
        private readonly AuditRepository $audit,
    ) {}

    public function run(string $billingMonthDate, string $cycleStart, string $cycleEnd, float $flatRate): array
    {
        if (!$this->control->validateCycleExists($cycleStart, $cycleEnd)) {
            throw new \InvalidArgumentException('Invalid cycle');
        }
        if ($billingMonthDate === '') {
            throw new \InvalidArgumentException('billing_month_date is required');
        }
        if (strtotime($cycleStart) === false || strtotime($cycleEnd) === false || strtotime($cycleStart) > strtotime($cycleEnd)) {
            throw new \InvalidArgumentException('reading_from must be before or equal to reading_to');
        }
        if ($flatRate <= 0) {
            throw new \InvalidArgumentException('rate missing');
        }

        $runId = 'RUN-'.substr(bin2hex(random_bytes(8)), 0, 12);
        $runStart = gmdate('c');
        $billingMonthDays = ExplicitElectricBillingCalculator::billingMonthDays($billingMonthDate);
        $monthlyActiveDays = ElectricActiveDaysMonthly::query()
            ->whereDate('billing_month_date', $billingMonthDate)
            ->pluck('active_days', 'company_id')
            ->map(fn ($value) => (float) $value)
            ->all();

        $empRows = $this->master->listEmployees();
        $allowRows = $this->allowance->listAllowances();
        $readRows = $this->readings->listCycleReadings($cycleStart, $cycleEnd);
        $attRows = $this->attendance->listCycleAttendance($cycleStart, $cycleEnd);
        $occRows = $this->occupancy->listOccupancy();
        $adjRows = $this->adjustments->listCycleAdjustments($cycleStart, $cycleEnd);

        $issues = array_merge(
            Validators::uniqueByKey($allowRows, fn($r) => trim((string)($r['unit_id'] ?? '')), 'E_ALLOW_DUP_KEY', 'Duplicate Allowance Unit_ID'),
            Validators::uniqueByKey($readRows, fn($r) => "('".trim((string)($r['unit_id'] ?? ''))."', '".($r['cycle_start_date'] ?? '')."', '".($r['cycle_end_date'] ?? '')."')", 'E_READ_DUP_KEY', 'Duplicate Reading key')
        );

        $allowByUnit=[]; foreach ($allowRows as $r) { $allowByUnit[(string)$r['unit_id']] = $r; }
        $readByUnit=[]; foreach ($readRows as $r) { $readByUnit[(string)$r['unit_id']] = $r; }
        $empByCompany=[]; foreach ($empRows as $r) { $empByCompany[(string)$r['company_id']] = $r; }
        $attByCompany=[]; foreach ($attRows as $r) { $attByCompany[(string)$r['company_id']] = $r; }
        $occByUnit=[]; $occByCompanyUnit=[];
        foreach ($occRows as $r) {
            $unitId = (string)($r['unit_id'] ?? '');
            $companyId = (string)($r['company_id'] ?? '');
            if ($unitId !== '') {
                $occByUnit[$unitId][] = $r;
            }
            if ($unitId !== '' && $companyId !== '') {
                $occByCompanyUnit[$companyId.'|'.$unitId][] = $r;
            }
        }

        $adjMap=[]; foreach ($adjRows as $r) { $k = ($r['company_id'] ?? '').'|'.($r['unit_id'] ?? ''); $adjMap[$k] = ($adjMap[$k] ?? 0.0) + (float)($r['adjustment_units'] ?? 0); }

        $finalMap=[]; $drill=[]; $processed=0; $skipped=0;
        $units = array_keys($allowByUnit); sort($units);

        foreach ($units as $unitId) {
            $allow = $allowByUnit[$unitId];
            $resType = strtoupper(trim((string)($allow['residence_type'] ?? 'ROOM')));

            if (!array_key_exists('free_electric', $allow) || $allow['free_electric'] === null || $allow['free_electric'] === '') {
                $issues[] = ['code' => 'E_ALLOW_MISSING', 'message' => 'Unit_Free_Elec missing', 'severity' => 'ERROR', 'unit_id' => $unitId];
                $skipped++;
                continue;
            }
            $unitFreeElectric = (float)$allow['free_electric'];

            $cons = ConsumptionEngine::compute($unitId, $readByUnit[$unitId] ?? null, $this->readings->listUnitHistory($unitId, $cycleStart));
            $issues = array_merge($issues, $cons['issues']);
            if (!$cons['result']) { $skipped++; continue; }

            $unitOccupancy = $occByUnit[$unitId] ?? [];
            $roomPersons = $resType === 'HOUSE' ? 1 : ExplicitElectricBillingCalculator::roomPersons($unitOccupancy);
            if ($roomPersons <= 0) {
                $issues[] = ['code' => 'E_ROOM_PERSONS_INVALID', 'message' => 'Room_Persons must be greater than zero', 'severity' => 'ERROR', 'unit_id' => $unitId];
                $skipped++;
                continue;
            }

            $employeeIds = [];
            foreach ($unitOccupancy as $o) {
                $companyId = trim((string)($o['company_id'] ?? ''));
                if ($companyId !== '') {
                    $employeeIds[$companyId] = true;
                }
            }
            $employeeIds = array_keys($employeeIds);
            sort($employeeIds);
            if (count($employeeIds) === 0) {
                $issues[] = ['code' => 'E_EMP_NO_VALID_ROOM', 'message' => 'No valid room mapping', 'severity' => 'ERROR', 'unit_id' => $unitId];
                $skipped++;
                continue;
            }
            if ($resType === 'HOUSE') {
                if (count($employeeIds) !== 1) {
                    $issues[] = ['code' => 'E_HOUSE_RESP_NOT_SINGLE', 'message' => 'HOUSE unit must resolve to exactly one responsible employee', 'severity' => 'ERROR', 'unit_id' => $unitId];
                    $skipped++;
                    continue;
                }
            }

            $activeDaysByEmployee = [];
            $unitActiveDays = 0.0;
            foreach ($employeeIds as $companyId) {
                if (!isset($empByCompany[$companyId])) {
                    $issues[] = ['code' => 'E_EMP_NOT_ELIGIBLE', 'message' => 'Employee missing in master', 'severity' => 'ERROR', 'company_id' => $companyId, 'unit_id' => $unitId];
                    continue;
                }
                $hasMonthlyOverride = $resType !== 'HOUSE' && array_key_exists($companyId, $monthlyActiveDays);
                $attRow = $attByCompany[$companyId] ?? null;
                if (!$hasMonthlyOverride && (!$attRow || !is_numeric($attRow['attendance_days'] ?? null))) {
                    $issues[] = ['code' => 'E_ACTIVE_DAYS_MISSING', 'message' => 'ActiveDays missing/invalid', 'severity' => 'ERROR', 'company_id' => $companyId, 'unit_id' => $unitId];
                    continue;
                }
                $attendanceDays = $hasMonthlyOverride ? (float) $monthlyActiveDays[$companyId] : (float)$attRow['attendance_days'];
                if ($attendanceDays < 0) {
                    $issues[] = ['code' => 'E_ACTIVE_DAYS_INVALID', 'message' => 'ActiveDays missing/invalid', 'severity' => 'ERROR', 'company_id' => $companyId, 'unit_id' => $unitId];
                    continue;
                }

                $employeeUnitRows = $occByCompanyUnit[$companyId.'|'.$unitId] ?? [];
                $employeeActiveDays = ExplicitElectricBillingCalculator::employeeActiveDaysInUnit($employeeUnitRows, $cycleStart, $cycleEnd, $attendanceDays);
                $activeDaysByEmployee[$companyId] = $employeeActiveDays;
                $unitActiveDays += $employeeActiveDays;
            }

            if ($unitActiveDays <= 0.0) {
                $issues[] = ['code' => 'E_UNIT_ZERO_ATT_WITH_CONS', 'message' => 'ROOM unit has consumption but zero attendance; unit skipped', 'severity' => 'ERROR', 'unit_id' => $unitId];
                $skipped++;
                continue;
            }

            $runningAllocated = 0.0;
            $grossUnits = (float)$cons['result']['gross_units'];
            $employeeIds = array_values(array_filter($employeeIds, fn($cid) => array_key_exists($cid, $activeDaysByEmployee)));
            foreach ($employeeIds as $index => $companyId) {
                $employeeActiveDays = (float)($activeDaysByEmployee[$companyId] ?? 0.0);
                $empUsedElec = $resType === 'HOUSE'
                    ? round($grossUnits, 4)
                    : ExplicitElectricBillingCalculator::allocateUsageShare($grossUnits, $employeeActiveDays, $unitActiveDays, $index === count($employeeIds) - 1, $runningAllocated);
                $eligibleUnits = $resType === 'HOUSE'
                    ? round($unitFreeElectric, 4)
                    : ExplicitElectricBillingCalculator::eligibleUnits($unitFreeElectric, $roomPersons, $billingMonthDays, $employeeActiveDays);
                $billableUnits = ExplicitElectricBillingCalculator::billableUnits($empUsedElec, $eligibleUnits);
                $adj = (float)($adjMap[$companyId.'|'.$unitId] ?? 0.0);
                $netAfterAdj = round(max(0.0, $billableUnits + $adj), 4);
                $amountBefore = ExplicitElectricBillingCalculator::amount($netAfterAdj, $flatRate);

                $drill[] = [
                    'cycle_start_date'=>$cycleStart,'cycle_end_date'=>$cycleEnd,'run_id'=>$runId,'company_id'=>$companyId,'unit_id'=>$unitId,
                    'residence_type'=>$resType,'employee_attendance_in_unit'=>$employeeActiveDays,'gross_units'=>$empUsedElec,
                    'free_allowance_units'=>$eligibleUnits,'net_units_before_adj'=>$billableUnits,'adjustment_units'=>$adj,
                    'net_units_after_adj'=>$netAfterAdj,'amount_before_rounding'=>$amountBefore,'is_estimated'=>!empty($cons['result']['is_estimated']) ? 'Y':'N',
                    'estimate_source_cycle1'=>$cons['result']['estimate_source_cycle1'] ?? null,'estimate_source_cycle2'=>$cons['result']['estimate_source_cycle2'] ?? null,
                    'estimate_source_cycle3'=>$cons['result']['estimate_source_cycle3'] ?? null,'estimated_from_valid_cycle_count'=>$cons['result']['estimated_from_valid_cycle_count'] ?? 0
                ];

                if (!isset($finalMap[$companyId])) {
                    $finalMap[$companyId] = ['cycle_start_date'=>$cycleStart,'cycle_end_date'=>$cycleEnd,'run_id'=>$runId,'company_id'=>$companyId,'name'=>$empByCompany[$companyId]['name'] ?? $companyId,'total_net_billable_units'=>0.0,'flat_rate'=>$flatRate,'final_amount_before_rounding'=>0.0,'has_estimated_units'=>'N'];
                }
                $finalMap[$companyId]['total_net_billable_units'] += $netAfterAdj;
                $finalMap[$companyId]['final_amount_before_rounding'] += $amountBefore;
                if (!empty($cons['result']['is_estimated'])) $finalMap[$companyId]['has_estimated_units'] = 'Y';
            }
            $processed++;
        }

        ksort($finalMap);
        $final = [];
        foreach ($finalMap as $cid => $r) {
            $r['final_amount_rounded'] = \App\Services\ElectricV1\Domain\Rules::customFinalRound((float)$r['final_amount_before_rounding']);
            $final[] = $r;
        }

        DB::transaction(function () use ($cycleStart, $cycleEnd, $final, $drill, $issues, $runId, $runStart, $processed, $skipped) {
            $this->output->replaceCycleOutputs($cycleStart, $cycleEnd, $final, $drill);
            if (count($issues)) {
                $this->audit->appendExceptions(array_map(fn($i) => [
                    'run_id'=>$runId,'logged_at'=>gmdate('c'),'severity'=>$i['severity'] ?? 'ERROR','exception_code'=>$i['code'] ?? 'E_UNKNOWN','message'=>$i['message'] ?? 'unknown',
                    'company_id'=>$i['company_id'] ?? 'N/A','unit_id'=>$i['unit_id'] ?? 'N/A','room_id'=>$i['room_id'] ?? 'N/A','cycle_start_date'=>$cycleStart,'cycle_end_date'=>$cycleEnd
                ], $issues));
            }
            $this->audit->appendRunHistory([
                'run_id'=>$runId,'run_start'=>$runStart,'run_end'=>gmdate('c'),'cycle_start_date'=>$cycleStart,'cycle_end_date'=>$cycleEnd,
                'status'=>count($issues) ? 'SUCCESS_WITH_EXCEPTIONS' : 'SUCCESS',
                'processed_count'=>$processed,'skipped_count'=>$skipped,'exception_count'=>count($issues)
            ]);
        });

        return [
            'run_id' => $runId,
            'cycle_start_date' => $cycleStart,
            'cycle_end_date' => $cycleEnd,
            'run_status' => count($issues) ? 'SUCCESS_WITH_EXCEPTIONS' : 'SUCCESS',
            'processed_count' => $processed,
            'skipped_count' => $skipped,
            'exception_count' => count($issues),
            'final_output_rows' => count($final),
            'drilldown_rows' => count($drill),
        ];
    }
}
