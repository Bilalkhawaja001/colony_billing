<?php

namespace App\Services\ElectricV1;

use App\Repositories\ElectricV1\{AllowanceRepository, ReadingsRepository, AttendanceRepository, OccupancyRepository, AdjustmentsRepository, OutputRepository, AuditRepository, MasterRepository, ControlRepository};
use App\Services\ElectricV1\Domain\{Validators, AttendanceAllocator, ConsumptionEngine, AllocationEngine, AdjustmentsEngine};
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

    public function run(string $cycleStart, string $cycleEnd, float $flatRate): array
    {
        if (!$this->control->validateCycleExists($cycleStart, $cycleEnd)) {
            throw new \InvalidArgumentException('Invalid cycle');
        }

        $runId = 'RUN-'.substr(bin2hex(random_bytes(8)), 0, 12);
        $runStart = gmdate('c');

        $empRows = $this->master->listEmployees();
        $allowRows = $this->allowance->listAllowances();
        $readRows = $this->readings->listCycleReadings($cycleStart, $cycleEnd);
        $attRows = $this->attendance->listCycleAttendance($cycleStart, $cycleEnd);
        $occRows = $this->occupancy->listOccupancy();
        $adjRows = $this->adjustments->listCycleAdjustments($cycleStart, $cycleEnd);

        $issues = array_merge(
            Validators::uniqueByKey($allowRows, fn($r) => trim((string)($r['unit_id'] ?? '')), 'E_ALLOW_DUP_KEY', 'Duplicate Allowance Unit_ID'),
            Validators::uniqueByKey($readRows, fn($r) => trim((string)($r['unit_id'] ?? '')).'|'.($r['cycle_start_date'] ?? '').'|'.($r['cycle_end_date'] ?? ''), 'E_READ_DUP_KEY', 'Duplicate Reading key')
        );

        $allowByUnit=[]; foreach ($allowRows as $r) { $allowByUnit[(string)$r['unit_id']] = $r; }
        $readByUnit=[]; foreach ($readRows as $r) { $readByUnit[(string)$r['unit_id']] = $r; }
        $empByCompany=[]; foreach ($empRows as $r) { $empByCompany[(string)$r['company_id']] = $r; }

        $attPack = AttendanceAllocator::buildEligible($attRows, $empByCompany, $cycleStart, $cycleEnd);
        $issues = array_merge($issues, $attPack['issues']);
        $roomPack = AttendanceAllocator::allocateRoom($attPack['eligible'], $occRows, $attPack['room_skip']);
        $issues = array_merge($issues, $roomPack['issues']);

        $adjMap=[]; foreach ($adjRows as $r) { $k = ($r['company_id'] ?? '').'|'.($r['unit_id'] ?? ''); $adjMap[$k] = ($adjMap[$k] ?? 0.0) + (float)($r['adjustment_units'] ?? 0); }

        $finalMap=[]; $drill=[]; $processed=0; $skipped=0;
        $units = array_keys($allowByUnit); sort($units);

        foreach ($units as $unitId) {
            $allow = $allowByUnit[$unitId];
            $resType = strtoupper(trim((string)($allow['residence_type'] ?? '')));
            $freeElectric = (float)($allow['free_electric'] ?? 0);

            $cons = ConsumptionEngine::compute($unitId, $readByUnit[$unitId] ?? null, $this->readings->listUnitHistory($unitId, $cycleStart));
            $issues = array_merge($issues, $cons['issues']);
            if (!$cons['result']) { $skipped++; continue; }

            $houseResp = [];
            foreach ($occRows as $o) if (($o['unit_id'] ?? '') === $unitId && !empty($o['company_id'])) $houseResp[] = $o['company_id'];

            $alloc = AllocationEngine::allocate($unitId, $resType, (float)$cons['result']['gross_units'], $roomPack['allocations'], $houseResp);
            $issues = array_merge($issues, $alloc['issues']);
            if (count($alloc['allocations']) === 0) { $skipped++; continue; }

            $totalAtt = 0.0; foreach ($alloc['allocations'] as $a) $totalAtt += (float)$a['employee_attendance_in_unit'];

            foreach ($alloc['allocations'] as $a) {
                $freeShare = $resType === 'HOUSE' ? $freeElectric : (($totalAtt > 0) ? $freeElectric * ((float)$a['employee_attendance_in_unit'] / $totalAtt) : 0.0);
                $adj = $adjMap[$a['company_id'].'|'.$unitId] ?? 0.0;
                $comp = AdjustmentsEngine::compute((float)$a['allocated_gross_units'], $freeShare, $adj, $flatRate);

                $drill[] = [
                    'cycle_start_date'=>$cycleStart,'cycle_end_date'=>$cycleEnd,'run_id'=>$runId,'company_id'=>$a['company_id'],'unit_id'=>$unitId,
                    'residence_type'=>$resType,'employee_attendance_in_unit'=>(float)$a['employee_attendance_in_unit'],'gross_units'=>(float)$comp['gross_units'],
                    'free_allowance_units'=>(float)$comp['free_allowance_units'],'net_units_before_adj'=>(float)$comp['net_units_before_adj'],'adjustment_units'=>(float)$comp['adjustment_units'],
                    'net_units_after_adj'=>(float)$comp['net_units_after_adj'],'amount_before_rounding'=>(float)$comp['amount_before_rounding'],'is_estimated'=>!empty($cons['result']['is_estimated']) ? 'Y':'N',
                    'estimate_source_cycle1'=>$cons['result']['estimate_source_cycle1'] ?? null,'estimate_source_cycle2'=>$cons['result']['estimate_source_cycle2'] ?? null,
                    'estimate_source_cycle3'=>$cons['result']['estimate_source_cycle3'] ?? null,'estimated_from_valid_cycle_count'=>$cons['result']['estimated_from_valid_cycle_count'] ?? 0
                ];

                if (!isset($finalMap[$a['company_id']])) {
                    $finalMap[$a['company_id']] = ['cycle_start_date'=>$cycleStart,'cycle_end_date'=>$cycleEnd,'run_id'=>$runId,'company_id'=>$a['company_id'],'name'=>$empByCompany[$a['company_id']]['name'] ?? $a['company_id'],'total_net_billable_units'=>0.0,'flat_rate'=>$flatRate,'final_amount_before_rounding'=>0.0,'has_estimated_units'=>'N'];
                }
                $finalMap[$a['company_id']]['total_net_billable_units'] += (float)$comp['net_units_after_adj'];
                $finalMap[$a['company_id']]['final_amount_before_rounding'] += (float)$comp['amount_before_rounding'];
                if (!empty($cons['result']['is_estimated'])) $finalMap[$a['company_id']]['has_estimated_units'] = 'Y';
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
