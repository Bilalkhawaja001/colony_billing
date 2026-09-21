<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json([
                'query' => $term,
                'results' => [],
            ]);
        }

        $like = '%' . $term . '%';
        $results = [];

        $subtitle = static function (array $parts): string {
            $parts = array_filter(
                $parts,
                static fn ($value) => $value !== null && trim((string) $value) !== ''
            );

            return implode(' • ', array_values($parts));
        };

        /*
        |--------------------------------------------------------------------------
        | Employees
        |--------------------------------------------------------------------------
        */
        $employees = DB::table('employees_master')
            ->select([
                'company_id',
                'name',
                'department',
                'section',
                'sub_section',
                'designation',
                'cnic_no',
                'mobile_no',
                'unit_id',
                'room_no',
                'active',
            ])
            ->where(function ($query) use ($like) {
                $query
                    ->where('company_id', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('father_name', 'like', $like)
                    ->orWhere('cnic_no', 'like', $like)
                    ->orWhere('mobile_no', 'like', $like)
                    ->orWhere('department', 'like', $like)
                    ->orWhere('section', 'like', $like)
                    ->orWhere('sub_section', 'like', $like)
                    ->orWhere('designation', 'like', $like)
                    ->orWhere('unit_id', 'like', $like)
                    ->orWhere('room_no', 'like', $like);
            })
            ->orderByRaw(
                'CASE WHEN company_id = ? THEN 0 ELSE 1 END',
                [$term]
            )
            ->orderBy('name')
            ->limit(8)
            ->get();

        foreach ($employees as $row) {
            $results[] = [
                'group' => 'Employees',
                'type' => 'employee',
                'title' => $row->company_id . ' — ' . $row->name,
                'subtitle' => $subtitle([
                    $row->department,
                    $row->designation,
                    $row->unit_id ? 'Unit ' . $row->unit_id : null,
                    $row->room_no ? 'Room ' . $row->room_no : null,
                    $row->active ? 'Status: ' . $row->active : null,
                ]),
                'url' => url(
                    'employee-profile/' . rawurlencode($row->company_id)
                ),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Family Members
        |--------------------------------------------------------------------------
        */
        $families = DB::table('family_members as fm')
            ->leftJoin(
                'employees_master as em',
                'em.company_id',
                '=',
                'fm.company_id'
            )
            ->select([
                'fm.id',
                'fm.company_id',
                'fm.member_name',
                'fm.relation',
                'fm.age',
                'fm.school_name',
                'fm.current_status',
                'fm.is_active',
                'em.name as employee_name',
            ])
            ->where(function ($query) use ($like) {
                $query
                    ->where('fm.company_id', 'like', $like)
                    ->orWhere('fm.member_name', 'like', $like)
                    ->orWhere('fm.relation', 'like', $like)
                    ->orWhere('fm.school_name', 'like', $like)
                    ->orWhere('em.name', 'like', $like);
            })
            ->orderByRaw(
                'CASE WHEN fm.company_id = ? THEN 0 ELSE 1 END',
                [$term]
            )
            ->orderBy('fm.member_name')
            ->limit(8)
            ->get();

        foreach ($families as $row) {
            $results[] = [
                'group' => 'Family',
                'type' => 'family',
                'title' => $row->member_name
                    . ($row->relation ? ' — ' . $row->relation : ''),
                'subtitle' => $subtitle([
                    'Employee ' . $row->company_id,
                    $row->employee_name,
                    $row->age !== null ? 'Age ' . $row->age : null,
                    $row->current_status,
                ]),
                'url' => url(
                    'employee-profile/' . rawurlencode($row->company_id)
                ),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Units / Rooms
        |--------------------------------------------------------------------------
        */
        $units = DB::table('util_unit')
            ->select([
                'unit_id',
                'colony_type',
                'block_name',
                'room_no',
                'department',
                'is_active',
            ])
            ->where(function ($query) use ($like) {
                $query
                    ->where('unit_id', 'like', $like)
                    ->orWhere('colony_type', 'like', $like)
                    ->orWhere('block_name', 'like', $like)
                    ->orWhere('room_no', 'like', $like)
                    ->orWhere('department', 'like', $like);
            })
            ->orderByRaw(
                'CASE WHEN unit_id = ? THEN 0 ELSE 1 END',
                [$term]
            )
            ->orderBy('unit_id')
            ->limit(8)
            ->get();

        foreach ($units as $row) {
            $results[] = [
                'group' => 'Units & Rooms',
                'type' => 'unit',
                'title' => $row->unit_id,
                'subtitle' => $subtitle([
                    $row->colony_type,
                    $row->block_name,
                    $row->room_no ? 'Room ' . $row->room_no : null,
                    $row->department,
                ]),
                'url' => url('unit-directory')
                    . '?q=' . urlencode($row->unit_id),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Meters
        |--------------------------------------------------------------------------
        */
        $meters = DB::table('util_meter_unit')
            ->select([
                'meter_id',
                'unit_id',
                'meter_type',
                'is_active',
            ])
            ->where(function ($query) use ($like) {
                $query
                    ->where('meter_id', 'like', $like)
                    ->orWhere('unit_id', 'like', $like)
                    ->orWhere('meter_type', 'like', $like);
            })
            ->orderByRaw(
                'CASE WHEN meter_id = ? THEN 0 ELSE 1 END',
                [$term]
            )
            ->orderBy('meter_id')
            ->limit(8)
            ->get();

        foreach ($meters as $row) {
            $results[] = [
                'group' => 'Meters',
                'type' => 'meter',
                'title' => $row->meter_id,
                'subtitle' => $subtitle([
                    $row->meter_type,
                    $row->unit_id ? 'Unit ' . $row->unit_id : null,
                    ((int) $row->is_active === 1) ? 'Active' : 'Inactive',
                ]),
                'url' => url('meters-readings/registry')
                    . '?q=' . urlencode($row->meter_id),
            ];
        }

        return response()->json([
            'query' => $term,
            'results' => $results,
        ]);
    }
}
