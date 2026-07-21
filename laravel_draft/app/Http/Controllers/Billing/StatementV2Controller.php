<?php
namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatementV2Controller extends Controller
{
    // June cycle (V2 committed run). Aage months aayein to yahan extend kar sakte hain.
    private string $cs = '2026-05-16';
    private string $ce = '2026-06-15';

    public function show(Request $request)
    {
        $companyId = trim((string) $request->query('company_id', ''));
        $data = $companyId !== '' ? $this->buildStatement($companyId) : null;

        return view('billing_control.statement-v2', [
            'pageTitle'  => 'Employee Statement (V2)',
            'companyId'  => $companyId,
            'data'       => $data,
            'cycleStart' => $this->cs,
            'cycleEnd'   => $this->ce,
        ]);
    }

    private function buildStatement(string $companyId): ?array
    {
        $final = DB::table('electric_v1_output_employee_final')
            ->where('cycle_start_date', $this->cs)
            ->where('cycle_end_date', $this->ce)
            ->where('company_id', $companyId)
            ->first();

        if (!$final) {
            return null; // is company ka June bill nahi mila
        }

        $emp = DB::table('employees_master')->where('company_id', $companyId)->first();

        $drill = DB::table('electric_v1_output_employee_unit_drilldown')
            ->where('cycle_start_date', $this->cs)
            ->where('cycle_end_date', $this->ce)
            ->where('company_id', $companyId)
            ->get()
            ->map(function ($d) use ($final) {
                return [
                    'unit_id'        => $d->unit_id,
                    'residence_type' => $d->residence_type,
                    'attendance'     => (float) $d->employee_attendance_in_unit,
                    'gross_units'    => (float) $d->gross_units,
                    'free_units'     => (float) $d->free_allowance_units,
                    'billable_units' => (float) $d->net_units_after_adj,
                    'rate'           => (float) $final->flat_rate,
                    'amount'         => round((float) $d->amount_before_rounding, 2),
                ];
            })->all();

        return [
            'employee' => [
                'company_id'  => $companyId,
                'name'        => $emp->name ?? ($final->name ?? $companyId),
                'father_name' => $emp->father_name ?? '',
                'department'  => $emp->department ?? '',
                'designation' => $emp->designation ?? '',
                'colony_type' => $emp->colony_type ?? '',
                'block_floor' => $emp->block_floor ?? '',
            ],
            'lines'   => $drill,
            'summary' => [
                'total_billable_units' => (float) $final->total_net_billable_units,
                'rate'                 => (float) $final->flat_rate,
                'total_amount'         => (float) $final->final_amount_rounded,
                'is_estimated'         => ($final->has_estimated_units ?? 'N') === 'Y',
                'bill_reference'       => $final->run_id ?? '',
            ],
        ];
    }
}
