<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class FamilyExportController extends Controller
{
    public function export(Request $request)
    {
        $q = DB::table('family_members as f')
            ->leftJoin('employees_master as e', 'e.company_id', '=', 'f.company_id')
            ->leftJoin('util_unit_rooms as r', function ($j) {
                $j->on('r.unit_id', '=', 'e.unit_id')->on('r.room_no', '=', 'e.room_no');
            })
            ->where('f.is_active', 1);

        if (($v = trim((string) $request->query('department', ''))) !== '') {
            $q->where('e.department', $v);
        }
        if (($v = trim((string) $request->query('residence_status', ''))) !== '') {
            $q->where('e.residence_status', $v);
        }
        if (($v = trim((string) $request->query('house_type', ''))) !== '') {
            $q->where('r.residence_type', $v);
        }
        if (($v = trim((string) $request->query('floor', ''))) !== '') {
            $q->where('r.floor', $v);
        }
        if (($v = trim((string) $request->query('room_no', ''))) !== '') {
            $q->where('e.room_no', $v);
        }
        if (($v = trim((string) $request->query('school_name', ''))) !== '') {
            $q->where('f.school_name', $v);
        }
        // school: going | not | (empty = all)
        $school = trim((string) $request->query('school', ''));
        if ($school === 'going') {
            $q->where('f.school_going', 1);
        } elseif ($school === 'not') {
            $q->where('f.school_going', 0);
        }

        $rows = $q->orderBy('e.name')->orderBy('f.member_name')->get([
            'f.company_id',
            'e.name as employee_name',
            'e.department',
            'e.designation',
            'e.colony_type',
            'r.residence_type as house_type',
            'e.unit_id',
            'r.floor as house_floor',
            'e.room_no',
            'e.residence_status',
            'f.member_name',
            'f.relation',
            'f.age',
            'f.school_going',
            'f.school_name',
            'f.class_name',
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Family Export');

        $headers = ['Company ID','Employee Name','Department','Designation','Colony Type','House Type','Unit','Floor','Room No','Residence Status','Member Name','Relation','Age','School Going','School Name','Class'];
        $col = 1;
        foreach ($headers as $h) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col).'1', $h);
            $col++;
        }

        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = 'A1:'.$lastCol.'1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E40AF');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $r = 2;
        foreach ($rows as $row) {
            $vals = [
                (string) $row->company_id,
                (string) $row->employee_name,
                (string) $row->department,
                (string) $row->designation,
                (string) $row->colony_type,
                (string) $row->house_type,
                (string) $row->unit_id,
                (string) $row->house_floor,
                (string) $row->room_no,
                (string) $row->residence_status,
                (string) $row->member_name,
                (string) $row->relation,
                $row->age !== null ? (string) $row->age : '',
                ((int) $row->school_going === 1) ? 'Yes' : 'No',
                (string) $row->school_name,
                (string) $row->class_name,
            ];
            $c = 1;
            foreach ($vals as $val) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($c).$r, $val);
                $c++;
            }
            if ((int) $row->school_going === 1) {
                $sheet->getStyle('A'.$r.':'.$lastCol.$r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF7ED');
            }
            $r++;
        }

        $lastRow = $r - 1;
        if ($lastRow >= 1) {
            $sheet->getStyle('A1:'.$lastCol.$lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E5E7EB');
        }

        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $sheet->freezePane('A2');

        $filename = 'family-export-'.date('Ymd_His').'.xlsx';

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
