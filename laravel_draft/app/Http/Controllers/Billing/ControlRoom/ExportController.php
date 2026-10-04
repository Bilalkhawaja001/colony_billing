<?php
namespace App\Http\Controllers\Billing\ControlRoom;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\ControlRoom\ExportBillRequest;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportController extends Controller
{
    private const ELECTRIC_SCOPE_DEPARTMENTS = ['SPINNING', 'WEAVING', 'CENTRALIZED'];

    public function index()
    {
        return view('billing_control.export', [
            'pageTitle' => 'Download Excel',
        ]);
    }

    public function download(ExportBillRequest $request)
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '1024M');
        $billType = (string) $request->input('bill_type', 'electric_v1');

        if ($billType !== 'electric_v1') {
            return back()->with('error', 'Is waqt detailed export sirf Electricity ke liye available hai.');
        }

        return $this->downloadDetailedElectricity($request);
    }

    public function simpleBreakdown(ExportBillRequest $request)
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '1024M');

        $monthCycle = $request->input('billing_month') ?: $request->input('month_cycle') ?: now()->format('m-Y');
        $monthCycle = $this->normalizeMonthCycle((string) $monthCycle);
        $cycle = $this->resolveElectricCycle($monthCycle);
        if (!$cycle) {
            return back()->with('error', 'Is month ka cycle nahi mila: '.$monthCycle);
        }

        $rows = DB::table('electric_v1_output_employee_unit_drilldown as d')
            ->leftJoin('employees_master as e', 'e.company_id', '=', 'd.company_id')
            ->leftJoin('util_unit as u', 'u.unit_id', '=', 'd.unit_id')
            ->leftJoin('util_unit_rooms as ur', function ($j) {
                $j->on('ur.unit_id', '=', 'd.unit_id')->on('ur.room_no', '=', 'd.room_no');
            })
            ->leftJoin('employee_residence_assignments as a', function ($j) {
                $j->on('a.company_id', '=', 'd.company_id')->where('a.status', 'ACTIVE');
            })
            ->where('d.cycle_start_date', $cycle->cycle_start_date)
            ->where('d.cycle_end_date', $cycle->cycle_end_date)
            ->orderBy('d.unit_id')->orderBy('d.room_no')->orderBy('d.company_id')
            ->get([
                'd.month_cycle', 'd.company_id', 'd.name', 'd.residence_type',
                'a.residence_type as assign_type', 'ur.occupant_grade',
                'u.colony_type', 'e.block_floor', 'd.unit_id', 'd.room_no',
                'd.active_days', 'd.emp_used_units', 'd.eligible_units',
                'd.billable_units', 'd.rate', 'd.amount',
            ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Breakdown');

        $headers = [
            'Month Cycle', 'Employee Code', 'Employee Name', 'Residence Type',
            'Mill Residence Category', 'Block/Floor', 'Meter No.', 'Room No.',
            'Present Days', 'Current Month Individual Used Units', 'Eligible Units',
            'Billable Units', 'Per Units Rate', 'Amount',
        ];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:N1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:N1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF1F4E79');
        $sheet->freezePane('A2');

        $r = 2;
        foreach ($rows as $row) {
            $sheet->fromArray([
                $row->month_cycle,
                $row->company_id,
                $row->name,
                $this->normalizeResidenceType($row->assign_type ?? $row->residence_type, $row->occupant_grade ?? null),
                $row->colony_type,
                $row->block_floor,
                $row->unit_id,
                $row->room_no,
                $row->active_days !== null ? (float) $row->active_days : null,
                round((float) ($row->emp_used_units ?? 0), 4),
                round((float) ($row->eligible_units ?? 0), 4),
                round((float) ($row->billable_units ?? 0), 4),
                (float) ($row->rate ?? 0),
                round((float) ($row->amount ?? 0), 2),
            ], null, 'A'.$r);
            $r++;
        }

        // Simple Breakdown: numeric cells must never remain blank.
        $lastDataRow = $r - 1;
        if ($lastDataRow >= 2) {
            foreach (range('I', 'N') as $col) {
                for ($rowNo = 2; $rowNo <= $lastDataRow; $rowNo++) {
                    $cell = $sheet->getCell($col.$rowNo);
                    if ($cell->getValue() === null || $cell->getValue() === '') {
                        $cell->setValue(0);
                    }
                }
            }
        }

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->setAutoFilter('A1:N'.max(1, $r - 1));

        $filename = 'electricity-simple-'.$monthCycle.'-'.date('Ymd_His').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function normalizeResidenceType(?string $type, ?string $grade): string
    {
        $t = strtoupper(str_replace([' ', '-'], '_', trim((string) $type)));
        $g = strtoupper(trim((string) $grade));

        if ($t === 'ROOM' || $t === '' || $t === 'COMMON') {
            if ($g === 'SENIOR_STAFF') { return 'Hostel'; }
            if ($g === 'FAMILY') { return 'House B Type'; }
            return 'Bachelor';
        }
        if (str_starts_with($t, 'CONTAINER')) { return 'Container'; }
        if (str_starts_with($t, 'HOSTEL') || $t === 'SENIOR_STAFF') { return 'Hostel'; }
        if (str_starts_with($t, 'BACHELOR')) { return 'Bachelor'; }
        if ($t === 'HOUSE_A' || $t === 'HOUSE_A+' || $t === 'HOUSE_A_TYPE' || $t === 'WEAVING_A+') { return 'House A Type'; }
        if ($t === 'HOUSE_B' || $t === 'HOUSE_B_TYPE') { return 'House B Type'; }
        if ($t === 'HOUSE_C' || $t === 'HOUSE_C_TYPE') { return 'House C Type'; }
        if ($g === 'SENIOR_STAFF') { return 'Hostel'; }
        if ($g === 'FAMILY') { return 'House B Type'; }
        return 'Bachelor';
    }

    public function detailedElectricBreakdown(ExportBillRequest $request)
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '1024M');
        $monthCycle = $request->input('billing_month') ?: $request->input('month_cycle') ?: now()->format('m-Y');
        $monthCycle = $this->normalizeMonthCycle((string) $monthCycle);
        $cycle = $this->resolveElectricCycle($monthCycle);

        if (!$cycle) {
            return back()->with('error', 'Selected billing month ke liye electric readings cycle nahi mila.');
        }

        $query = DB::table('electric_v1_output_employee_unit_drilldown as d')
            ->leftJoin('electric_v1_readings as rd', function ($join) {
                $join->on('rd.unit_id', '=', 'd.unit_id')
                    ->on('rd.cycle_start_date', '=', 'd.cycle_start_date')
                    ->on('rd.cycle_end_date', '=', 'd.cycle_end_date');
            })
            ->leftJoin('employees_master as e', 'e.company_id', '=', 'd.company_id')
            ->leftJoin('util_unit_rooms as ur', function ($join) {
                $join->on('ur.unit_id', '=', 'd.unit_id')
                    ->on('ur.room_no', '=', 'd.room_no');
            })
            ->where('d.cycle_start_date', $cycle->cycle_start_date)
            ->where('d.cycle_end_date', $cycle->cycle_end_date);

        $scope = strtoupper(trim((string) $request->input('scope', 'all')));
        if ($scope !== '' && $scope !== 'ALL' && in_array($scope, self::ELECTRIC_SCOPE_DEPARTMENTS, true)) {
            $query->where('e.department', $scope);
        }

        $unitType = trim((string) $request->input('unit_type', ''));
        $occupantGrade = $this->occupantGradeForUnitType($unitType);
        if ($occupantGrade !== null) {
            $query->where('ur.occupant_grade', $occupantGrade);
        }

        $roomType = strtoupper(trim((string) $request->input('room_type', '')));
        if ($roomType !== '') {
            $query->where('ur.residence_type', $roomType);
        }

        $rows = $query
            ->orderBy('d.unit_id')
            ->orderBy('d.room_no')
            ->orderBy('d.company_id')
            ->select([
                'd.company_id',
                DB::raw('COALESCE(e.name, d.name) as employee_name'),
                'd.unit_id',
                'e.colony_type',
                'd.room_no',
                'rd.previous_reading',
                'rd.current_reading',
                DB::raw('(COALESCE(rd.current_reading, 0) - COALESCE(rd.previous_reading, 0)) as meter_consumption'),
                'd.room_persons',
                DB::raw('(d.unit_used_elec / NULLIF(d.room_persons, 0)) as avg_per_employee'),
                'd.unit_total_attendance',
                'd.employee_attendance_in_unit',
                DB::raw("CONCAT(COALESCE(d.active_days, 0), ' / ', COALESCE(d.month_days, 0)) as active_month_days"),
                'd.gross_units',
                'd.free_allowance_units',
                'd.room_free_units',
                'd.net_units_after_adj',
                'd.billable_units',
                'd.rate',
                'd.amount',
                'ur.occupant_grade',
            ])
            ->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $sheetMap = [
            'House' => fn ($row) => strtoupper(trim((string) $row->occupant_grade)) === 'FAMILY',
            'Bachelor' => fn ($row) => strtoupper(trim((string) $row->occupant_grade)) === 'BACHELOR',
            'Hostel - Guest House' => fn ($row) => strtoupper(trim((string) $row->occupant_grade)) === 'SENIOR_STAFF',
        ];

        foreach ($sheetMap as $sheetName => $filter) {
            $this->addDetailedElectricBreakdownSheet($spreadsheet, $sheetName, $rows->filter($filter)->values());
        }

        $otherRows = $rows->filter(function ($row) {
            return !in_array(strtoupper(trim((string) $row->occupant_grade)), ['FAMILY', 'BACHELOR', 'SENIOR_STAFF'], true);
        })->values();

        if ($otherRows->isNotEmpty()) {
            $this->addDetailedElectricBreakdownSheet($spreadsheet, 'Other', $otherRows);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'detailed-electric-breakdown-'.$monthCycle.'-'.($scope ?: 'ALL').'-'.date('Ymd_His').'.xlsx';
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function downloadDetailedElectricity(ExportBillRequest $request)
    {
        $monthCycle = $request->input('billing_month') ?: $request->input('month_cycle') ?: now()->format('m-Y');
        $monthCycle = $this->normalizeMonthCycle((string) $monthCycle);

        $cycle = $this->resolveElectricCycle($monthCycle);

        if (!$cycle) {
            return back()->with('error', 'Selected billing month ke liye electric readings cycle nahi mila.');
        }

        $query = DB::table('electric_v1_output_employee_unit_drilldown as d')
            ->leftJoin('employees_master as e', 'e.company_id', '=', 'd.company_id')
            ->leftJoin('electric_v1_readings as er', function ($join) {
                $join->on('er.unit_id', '=', 'd.unit_id')
                    ->on('er.cycle_start_date', '=', 'd.cycle_start_date')
                    ->on('er.cycle_end_date', '=', 'd.cycle_end_date');
            })
            ->leftJoin('util_unit_rooms as r', function ($join) {
                $join->on('r.unit_id', '=', 'd.unit_id')
                    ->on('r.room_no', '=', 'd.room_no');
            })
            ->where('d.cycle_start_date', $cycle->cycle_start_date)
            ->where('d.cycle_end_date', $cycle->cycle_end_date);

        $scope = strtoupper(trim((string) $request->input('scope', 'all')));
        if ($scope !== '' && $scope !== 'ALL' && in_array($scope, self::ELECTRIC_SCOPE_DEPARTMENTS, true)) {
            $query->where('e.department', $scope);
        }

        $unitType = trim((string) $request->input('unit_type', ''));
        $occupantGrade = $this->occupantGradeForUnitType($unitType);
        if ($occupantGrade !== null) {
            $query->where('r.occupant_grade', $occupantGrade);
        }

        $roomType = strtoupper(trim((string) $request->input('room_type', '')));
        if ($roomType !== '') {
            $query->where('r.residence_type', $roomType);
        }

        $rows = $query
            ->orderBy('r.occupant_grade')
            ->orderBy('d.company_id')
            ->orderBy('d.unit_id')
            ->orderBy('d.room_no')
            ->select([
                'd.company_id',
                DB::raw('COALESCE(e.name, d.name) as employee_name'),
                'd.unit_id',
                'e.colony_type',
                'd.room_no',
                'er.previous_reading',
                'er.current_reading',
                DB::raw('(COALESCE(er.current_reading, 0) - COALESCE(er.previous_reading, 0)) as total_consumed_units'),
                'd.room_persons',
                'd.emp_used_units',
                'd.billable_units',
                'd.rate',
                'd.amount',
                'r.occupant_grade',
            ])
            ->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $sheetMap = [
            'House' => fn ($row) => strtoupper(trim((string) $row->occupant_grade)) === 'FAMILY',
            'Bachelor' => fn ($row) => strtoupper(trim((string) $row->occupant_grade)) === 'BACHELOR',
            'Hostel - Guest House' => fn ($row) => strtoupper(trim((string) $row->occupant_grade)) === 'SENIOR_STAFF',
        ];

        foreach ($sheetMap as $sheetName => $filter) {
            $this->addElectricitySheet($spreadsheet, $sheetName, $rows->filter($filter)->values());
        }

        $otherRows = $rows->filter(function ($row) {
            return !in_array(strtoupper(trim((string) $row->occupant_grade)), ['FAMILY', 'BACHELOR', 'SENIOR_STAFF'], true);
        })->values();

        if ($otherRows->isNotEmpty()) {
            $this->addElectricitySheet($spreadsheet, 'Other', $otherRows);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'electricity-detailed-'.$monthCycle.'-'.($scope ?: 'ALL').'-'.date('Ymd_His').'.xlsx';
        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function addElectricitySheet(Spreadsheet $spreadsheet, string $sheetName, $rows): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($sheetName);

        $headers = [
            'Company ID',
            'Name',
            'Meter No',
            'Colony',
            'Room No',
            'Previous Unit',
            'Current Unit',
            'Total Consumed Units',
            'Room Persons',
            'Employee Units',
            'Billable Units',
            'Rate',
            'Amount',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1).'1', $header);
        }

        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = 'A1:'.$lastCol.'1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E40AF');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->freezePane('A2');

        $rowNumber = 2;
        $amountTotal = 0.0;

        foreach ($rows as $row) {
            $amount = (float) ($row->amount ?? 0);
            $amountTotal += $amount;

            $values = [
                (string) ($row->company_id ?? ''),
                (string) ($row->employee_name ?? ''),
                (string) ($row->unit_id ?? ''),
                (string) ($row->colony_type ?? ''),
                (string) ($row->room_no ?? ''),
                $row->previous_reading === null ? null : (float) $row->previous_reading,
                $row->current_reading === null ? null : (float) $row->current_reading,
                $row->total_consumed_units === null ? null : (float) $row->total_consumed_units,
                $row->room_persons === null ? null : (float) $row->room_persons,
                $row->emp_used_units === null ? null : (float) $row->emp_used_units,
                $row->billable_units === null ? null : (float) $row->billable_units,
                $row->rate === null ? null : (float) $row->rate,
                $amount,
            ];

            foreach ($values as $index => $value) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1).$rowNumber, $value);
            }

            $rowNumber++;
        }

        if ($rows->isEmpty()) {
            $sheet->setCellValue('A2', 'No records');
            $rowNumber = 3;
        }

        $totalRow = $rowNumber;
        $sheet->setCellValue('L'.$totalRow, 'TOTAL');
        $sheet->setCellValue('M'.$totalRow, $amountTotal);
        $sheet->getStyle('A'.$totalRow.':'.$lastCol.$totalRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$totalRow.':'.$lastCol.$totalRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0F2FE');

        $lastRow = $totalRow;
        $sheet->getStyle('A1:'.$lastCol.$lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E5E7EB');
        $sheet->getStyle('F2:M'.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');

        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
    }

    private function addDetailedElectricBreakdownSheet(Spreadsheet $spreadsheet, string $sheetName, $rows): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($sheetName);

        $headers = [
            'Company ID',
            'Name',
            'Meter No',
            'Colony',
            'Room No',
            'June Reading',
            'July Reading',
            'Meter Consumption',
            'Employees in Room',
            'Avg per Employee',
            'Unit Total Attendance',
            'Employee Attendance',
            'Active/Month Days',
            'Gross Units',
            'Free Allowance Units',
            'Room Free Units',
            'Net Units After Adj',
            'Billable Units',
            'Rate',
            'Amount',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1).'1', $header);
        }

        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = 'A1:'.$lastCol.'1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F2937');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->freezePane('A2');

        $rowNumber = 2;
        $meterConsumptionTotal = 0.0;
        $billableUnitsTotal = 0.0;
        $amountTotal = 0.0;

        foreach ($rows as $row) {
            $meterConsumption = (float) ($row->meter_consumption ?? 0);
            $billableUnits = (float) ($row->billable_units ?? 0);
            $amount = (float) ($row->amount ?? 0);

            $meterConsumptionTotal += $meterConsumption;
            $billableUnitsTotal += $billableUnits;
            $amountTotal += $amount;

            $values = [
                (string) ($row->company_id ?? ''),
                (string) ($row->employee_name ?? ''),
                (string) ($row->unit_id ?? ''),
                (string) ($row->colony_type ?? ''),
                (string) ($row->room_no ?? ''),
                $row->previous_reading === null ? null : (float) $row->previous_reading,
                $row->current_reading === null ? null : (float) $row->current_reading,
                $meterConsumption,
                $row->room_persons === null ? null : (float) $row->room_persons,
                $row->avg_per_employee === null ? null : (float) $row->avg_per_employee,
                $row->unit_total_attendance === null ? null : (float) $row->unit_total_attendance,
                $row->employee_attendance_in_unit === null ? null : (float) $row->employee_attendance_in_unit,
                (string) ($row->active_month_days ?? ''),
                $row->gross_units === null ? null : (float) $row->gross_units,
                $row->free_allowance_units === null ? null : (float) $row->free_allowance_units,
                $row->room_free_units === null ? null : (float) $row->room_free_units,
                $row->net_units_after_adj === null ? null : (float) $row->net_units_after_adj,
                $billableUnits,
                $row->rate === null ? null : (float) $row->rate,
                $amount,
            ];

            foreach ($values as $index => $value) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($index + 1).$rowNumber, $value);
            }

            $rowNumber++;
        }

        if ($rows->isEmpty()) {
            $sheet->setCellValue('A2', 'No records');
            $rowNumber = 3;
        }

        $totalRow = $rowNumber;
        $sheet->setCellValue('G'.$totalRow, 'TOTAL');
        $sheet->setCellValue('H'.$totalRow, $meterConsumptionTotal);
        $sheet->setCellValue('R'.$totalRow, $billableUnitsTotal);
        $sheet->setCellValue('T'.$totalRow, $amountTotal);
        $sheet->getStyle('A'.$totalRow.':'.$lastCol.$totalRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$totalRow.':'.$lastCol.$totalRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0F2FE');

        $lastRow = $totalRow;
        $sheet->getStyle('A1:'.$lastCol.$lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E5E7EB');
        $sheet->getStyle('F2:L'.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('N2:T'.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');

        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
    }

    private function resolveElectricCycle(string $monthCycle)
    {
        return DB::table('electric_v1_readings')
            ->whereRaw("DATE_FORMAT(cycle_end_date, '%m-%Y') = ?", [$monthCycle])
            ->select('cycle_start_date', 'cycle_end_date')
            ->groupBy('cycle_start_date', 'cycle_end_date')
            ->orderByDesc('cycle_end_date')
            ->first();
    }

    private function occupantGradeForUnitType(string $unitType): ?string
    {
        return [
            'House' => 'FAMILY',
            'Bachelor' => 'BACHELOR',
            'Hostel - Guest House' => 'SENIOR_STAFF',
        ][$unitType] ?? null;
    }

    private function normalizeMonthCycle(string $monthCycle): string
    {
        $monthCycle = trim($monthCycle);

        if (preg_match('/^(\d{4})-(\d{2})$/', $monthCycle, $matches)) {
            return $matches[2].'-'.$matches[1];
        }

        if (preg_match('/^(\d{2})-(\d{4})$/', $monthCycle)) {
            return $monthCycle;
        }

        return now()->format('m-Y');
    }
}
