<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatementV2Controller extends Controller
{
    public function show(Request $request)
    {
        $companyId = trim((string) $request->query('company_id', ''));
        $requestedCycle = trim((string) $request->query('cycle', ''));

        $view = [
            'pageTitle' => 'Employee Statement',
            'companyId' => $companyId,
            'data' => null,
            'history' => [],
            'cycles' => [],
            'overall' => [
                'total_billable_units' => 0,
                'total_amount' => 0,
            ],
            'error' => null,
        ];

        if ($companyId === '') {
            return view('billing_control.statement-v2', $view);
        }

        $emp = DB::table('employees_master')
            ->where('company_id', $companyId)
            ->first();

        $finalRows = DB::table('electric_v1_output_employee_final')
            ->where('company_id', $companyId)
            ->orderByDesc('cycle_end_date')
            ->orderByDesc('id')
            ->get();

        if ($finalRows->isEmpty()) {
            $view['error'] = 'No production V2 billing record found for employee '.$companyId.'.';
            return view('billing_control.statement-v2', $view);
        }

        $allDrill = DB::table('electric_v1_output_employee_unit_drilldown')
            ->where('company_id', $companyId)
            ->orderByDesc('cycle_end_date')
            ->orderBy('unit_id')
            ->get();

        $drillGroups = $allDrill->groupBy(function ($row) {
            return $row->cycle_start_date.'|'.$row->cycle_end_date.'|'.$row->run_id;
        });

        $selectedFinal = null;

        if ($requestedCycle !== '' && str_contains($requestedCycle, '|')) {
            [$reqStart, $reqEnd] = array_pad(explode('|', $requestedCycle, 2), 2, '');

            $selectedFinal = $finalRows->first(function ($row) use ($reqStart, $reqEnd) {
                return (string) $row->cycle_start_date === $reqStart
                    && (string) $row->cycle_end_date === $reqEnd;
            });
        }

        if (!$selectedFinal) {
            $selectedFinal = $finalRows->first();
        }

        $lineMapper = function ($d, $defaultRate = 0) {
            $pick = function ($primary, $legacy) {
                if ($primary === null || $primary === '') {
                    return (float) ($legacy ?? 0);
                }

                $p = (float) $primary;
                $l = (float) ($legacy ?? 0);

                // Older V2 rows have newer compatibility columns present as zero.
                // If legacy field contains the real value, use it.
                if (abs($p) < 0.0000001 && abs($l) > 0.0000001) {
                    return $l;
                }

                return $p;
            };

            return [
                'unit_id' => (string) ($d->unit_id ?? ''),
                'room_no' => (string) ($d->room_no ?? ''),
                'residence_type' => (string) ($d->residence_type ?? ''),
                'attendance' => $pick($d->active_days ?? null, $d->employee_attendance_in_unit ?? null),
                'gross_units' => $pick($d->emp_used_units ?? null, $d->gross_units ?? null),
                'free_units' => $pick($d->eligible_units ?? null, $d->free_allowance_units ?? null),
                'billable_units' => $pick($d->billable_units ?? null, $d->net_units_after_adj ?? null),
                'rate' => $pick($d->rate ?? null, $defaultRate),
                'amount' => $pick($d->amount ?? null, $d->amount_before_rounding ?? null),
            ];
        };

        $selectedKey = $selectedFinal->cycle_start_date.'|'
            .$selectedFinal->cycle_end_date.'|'.$selectedFinal->run_id;

        $selectedLines = collect($drillGroups->get($selectedKey, collect()))
            ->map(fn ($d) => $lineMapper($d, $selectedFinal->flat_rate))
            ->values()
            ->all();

        $history = [];

        foreach ($finalRows as $final) {
            $key = $final->cycle_start_date.'|'.$final->cycle_end_date.'|'.$final->run_id;

            $lines = collect($drillGroups->get($key, collect()))
                ->map(fn ($d) => $lineMapper($d, $final->flat_rate))
                ->values();

            $unitRooms = $lines->map(function ($line) {
                $label = $line['unit_id'] ?: '-';
                if ($line['room_no'] !== '') {
                    $label .= ' / '.$line['room_no'];
                }
                return $label;
            })->filter()->unique()->implode(', ');

            $history[] = [
                'cycle_key' => $final->cycle_start_date.'|'.$final->cycle_end_date,
                'month' => Carbon::parse($final->cycle_end_date)->format('F Y'),
                'cycle_start' => (string) $final->cycle_start_date,
                'cycle_end' => (string) $final->cycle_end_date,
                'run_id' => (string) $final->run_id,
                'unit_room' => $unitRooms ?: '-',
                'attendance' => (float) $lines->sum('attendance'),
                'used_units' => (float) $lines->sum('gross_units'),
                'free_units' => (float) $lines->sum('free_units'),
                'billable_units' => (float) $final->total_net_billable_units,
                'rate' => (float) $final->flat_rate,
                'amount' => (float) $final->final_amount_rounded,
                'lines' => $lines->all(),
            ];
        }

        $view['cycles'] = collect($history)->map(fn ($h) => [
            'key' => $h['cycle_key'],
            'label' => $h['month'],
        ])->values()->all();

        $view['history'] = $history;

        $view['overall'] = [
            'total_billable_units' => (float) $finalRows->sum('total_net_billable_units'),
            'total_amount' => (float) $finalRows->sum('final_amount_rounded'),
        ];

        $view['data'] = [
            'selected_cycle_key' => $selectedFinal->cycle_start_date.'|'.$selectedFinal->cycle_end_date,
            'selected_month' => Carbon::parse($selectedFinal->cycle_end_date)->format('F Y'),
            'cycle_start' => (string) $selectedFinal->cycle_start_date,
            'cycle_end' => (string) $selectedFinal->cycle_end_date,
            'employee' => [
                'company_id' => $companyId,
                'name' => $emp->name ?? $selectedFinal->name ?? $companyId,
                'father_name' => $emp->father_name ?? '',
                'department' => $emp->department ?? '',
                'designation' => $emp->designation ?? '',
                'colony_type' => $emp->colony_type ?? '',
                'block_floor' => $emp->block_floor ?? '',
            ],
            'lines' => $selectedLines,
            'summary' => [
                'total_billable_units' => (float) $selectedFinal->total_net_billable_units,
                'rate' => (float) $selectedFinal->flat_rate,
                'total_amount' => (float) $selectedFinal->final_amount_rounded,
                'bill_reference' => (string) ($selectedFinal->run_id ?? ''),
            ],
        ];

        return view('billing_control.statement-v2', $view);
    }
}
