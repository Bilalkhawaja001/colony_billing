<?php
namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PendingEmployeeController extends Controller
{
    private const COPY_FIELDS = [
        'company_id','name','father_name','cnic_no','mobile_no','department','section','sub_section',
        'designation','employee_type','join_date','leave_date','colony_type','block_floor','room_no',
        'shared_room','active','remarks','unit_id',
    ];

    public function index()
    {
        $q = trim((string) request()->query('q', ''));
        $pending = DB::table('employees_registry as r')
            ->leftJoin('employees_master as m', 'm.company_id', '=', 'r.company_id')
            ->whereNull('m.company_id')
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('r.company_id', 'like', '%'.$q.'%')
                      ->orWhere('r.name', 'like', '%'.$q.'%')
                      ->orWhere('r.department', 'like', '%'.$q.'%');
                });
            })
            ->select('r.*')
            ->orderBy('r.company_id')
            ->get();

        // cascade tree: colony -> floor -> rooms
        $tree = [];
        $rows = DB::table('util_unit as u')
            ->join('util_unit_rooms as r', 'r.unit_id', '=', 'u.unit_id')
            ->where('u.is_active', 1)->where('r.is_active', 1)
            ->whereNotNull('u.colony_type')
            ->select('u.colony_type', 'u.block_name', 'u.unit_id', 'r.room_no')
            ->orderBy('u.colony_type')->orderBy('u.block_name')->orderBy('r.room_no')
            ->get();
        $occupied = DB::table('electric_v1_occupancy')
            ->select('unit_id', 'room_id', DB::raw('COUNT(DISTINCT company_id) c'))
            ->groupBy('unit_id', 'room_id')->get()
            ->mapWithKeys(fn($r) => [$r->unit_id.'|'.$r->room_id => $r->c]);
        foreach ($rows as $r) {
            $floor = $r->block_name ?: '—';
            $tree[$r->colony_type][$floor][] = [
                'unit' => $r->unit_id,
                'room' => $r->room_no,
                'n'    => (int) ($occupied[$r->unit_id.'|'.$r->room_no] ?? 0),
            ];
        }

        // Outside Colony option (employees who do not live in the colony)
        $tree['Outside Colony'] = ['—' => [['unit' => 'OUTSIDE', 'room' => 'Outside Colony', 'n' => 0]]];

        $units = DB::table('util_unit')->where('is_active', 1)->orderBy('unit_id')->pluck('unit_id');
        $rooms = DB::table('util_unit_rooms')->where('is_active', 1)->orderBy('room_no')->get()->groupBy('unit_id');

        return view('billing_control.pending-employees', [
            'pageTitle' => 'Pending Employees',
            'q'         => $q,
            'tree'      => $tree,
            'pending'   => $pending,
            'units'     => $units,
            'rooms'     => $rooms,
        ]);
    }

    public function approve(Request $request, string $companyId)
    {
        $data = $request->validate([
            'unit_id'     => 'nullable|string|max:255',
            'room_no'     => 'nullable|string|max:255',
            'colony_type' => 'nullable|string|max:255',
            'block_floor' => 'nullable|string|max:255',
        ]);

        $reg = DB::table('employees_registry')->where('company_id', $companyId)->first();
        if (!$reg) {
            return back()->with('error', 'Registry row not found: '.$companyId);
        }
        if (DB::table('employees_master')->where('company_id', $companyId)->exists()) {
            return back()->with('error', 'Already in master: '.$companyId);
        }

        $row = [];
        foreach (self::COPY_FIELDS as $f) {
            $row[$f] = $reg->$f ?? null;
        }
        foreach ($data as $k => $v) {
            if ($v !== null && $v !== '') { $row[$k] = $v; }
        }
        $row['active']     = $row['active'] ?: 'Yes';
        $row['created_at'] = now();
        $row['updated_at'] = now();

        DB::table('employees_master')->insert($row);

        return back()->with('status', 'Added to master: '.$companyId.' — '.($reg->name ?? ''));
    }

    public function reject(string $companyId)
    {
        DB::table('employees_registry')->where('company_id', $companyId)->delete();
        return back()->with('status', 'Removed from pending: '.$companyId);
    }
}
