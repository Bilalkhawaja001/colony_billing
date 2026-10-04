<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UnitDirectoryExportController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $colony = trim((string) $request->query('colony', ''));
        $type = strtoupper(trim((string) $request->query('type', '')));

        $query = DB::table('util_unit as u');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('u.unit_id', 'like', '%'.$q.'%')
                    ->orWhere('u.room_no', 'like', '%'.$q.'%')
                    ->orWhere('u.block_name', 'like', '%'.$q.'%');
            });
        }

        if ($colony !== '') {
            $query->where('u.colony_type', $colony);
        }

        $matchAllRooms = static function ($builder, callable $match): void {
            $builder->whereExists(function ($exists) {
                $exists->selectRaw('1')
                    ->from('util_unit_rooms as r_any')
                    ->whereColumn('r_any.unit_id', 'u.unit_id')
                    ->where('r_any.is_active', 1);
            })->whereNotExists(function ($not) use ($match) {
                $not->selectRaw('1')
                    ->from('util_unit_rooms as r_bad')
                    ->whereColumn('r_bad.unit_id', 'u.unit_id')
                    ->where('r_bad.is_active', 1)
                    ->where(function ($w) use ($match) {
                        $match($w, 'r_bad');
                    });
            });
        };

        if ($type === 'BACHELOR') {
            $matchAllRooms($query, fn ($w, $a) => $w->where($a.'.occupant_grade', '<>', 'BACHELOR')->orWhereNull($a.'.occupant_grade'));
        } elseif ($type === 'HOSTEL') {
            $matchAllRooms($query, fn ($w, $a) => $w->where($a.'.occupant_grade', '<>', 'SENIOR_STAFF')->orWhereNull($a.'.occupant_grade'));
        } elseif ($type === 'CONTAINER') {
            $matchAllRooms($query, fn ($w, $a) => $w->where($a.'.residence_type', '<>', 'CONTAINER')->orWhereNull($a.'.residence_type'));
        } elseif ($type === 'HOUSE') {
            $houseTypes = ['HOUSE_A+', 'HOUSE_A', 'HOUSE_B', 'HOUSE_C'];
            $matchAllRooms($query, fn ($w, $a) => $w->whereNotIn($a.'.residence_type', $houseTypes)->orWhereNull($a.'.residence_type'));
        } elseif ($type === 'COMMON') {
            $matchAllRooms($query, fn ($w, $a) => $w
                ->where(fn ($x) => $x->where($a.'.residence_type', '<>', 'COMMON')->orWhereNull($a.'.residence_type'))
                ->where(fn ($x) => $x->where($a.'.occupant_grade', '<>', 'COMMON')->orWhereNull($a.'.occupant_grade'))
            );
        } elseif ($type === 'UNSET') {
            $query->whereExists(function ($exists) {
                $exists->selectRaw('1')
                    ->from('util_unit_rooms as r_unset')
                    ->whereColumn('r_unset.unit_id', 'u.unit_id')
                    ->where('r_unset.is_active', 1)
                    ->where(fn ($w) => $w->whereNull('r_unset.residence_type')->orWhereNull('r_unset.occupant_grade'));
            });
        }

        $rows = $query
            ->leftJoin('util_unit_rooms as r', function ($join) {
                $join->on('r.unit_id', '=', 'u.unit_id')
                    ->where('r.is_active', '=', 1);
            })
            ->leftJoin('electric_v1_occupancy as o', function ($join) {
                $join->on('o.unit_id', '=', 'r.unit_id')
                    ->on('o.room_id', '=', 'r.id');
            })
            ->select(
                'u.unit_id', 'u.colony_type', 'u.block_name', 'r.room_no',
                'r.residence_type', 'r.occupant_grade', 'r.floor',
                DB::raw('COUNT(DISTINCT o.company_id) as occupant_count'),
                DB::raw("CASE WHEN u.is_active = 1 THEN 'Active' ELSE 'Inactive' END as unit_status")
            )
            ->groupBy('u.unit_id', 'u.colony_type', 'u.block_name', 'r.room_no', 'r.residence_type', 'r.occupant_grade', 'r.floor', 'u.is_active')
            ->orderBy('u.unit_id')
            ->orderBy('r.room_no')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Unit Directory');

        $headers = ['Unit ID', 'Colony', 'Block / Floor', 'Room No', 'Residence Type', 'Occupant Grade', 'Room Floor', 'Occupants', 'Unit Status'];
        $sheet->fromArray($headers, null, 'A1');
        $data = $rows->map(fn ($row) => [
            $row->unit_id, $row->colony_type, $row->block_name, $row->room_no,
            $row->residence_type, $row->occupant_grade, $row->floor,
            (int) $row->occupant_count, $row->unit_status,
        ])->all();

        if ($data !== []) {
            $sheet->fromArray($data, null, 'A2');
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $lastRow = max(1, count($data) + 1);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E78');
        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        for ($column = 1; $column <= count($headers); $column++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'unit-directory-'.date('Ymd_His').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
