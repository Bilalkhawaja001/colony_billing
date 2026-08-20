<?php
namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UnitDirectoryController extends Controller
{
    private const RESIDENCE_TYPES = ['ROOM', 'CONTAINER', 'HOUSE_A+', 'HOUSE_A', 'HOUSE_B', 'HOUSE_C', 'COMMON'];
    private const OCCUPANT_GRADES = ['BACHELOR', 'SENIOR_STAFF', 'FAMILY', 'COMMON'];
    private const DEPARTMENTS = ['Weaving', 'Spinning', 'Centralized', 'External'];
    private const FLOORS = ['Ground', '1st', '2nd', '3rd', '4th'];

    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id'     => 'required|string|max:255',
            'room_no'     => 'nullable|string|max:255',
            'colony_type' => 'nullable|string|max:255',
            'block_name'  => 'nullable|string|max:255',
            'department'  => ['nullable', 'string', Rule::in(self::DEPARTMENTS)],
        ]);
        DB::table('util_unit')->updateOrInsert(
            ['unit_id' => $data['unit_id']],
            [
                'room_no'     => $data['room_no'] ?? null,
                'colony_type' => $data['colony_type'] ?? null,
                'block_name'  => $data['block_name'] ?? null,
                'department'  => $data['department'] ?? null,
                'is_active'   => 1,
                'updated_at'  => now(),
                'created_at'  => now(),
            ]
        );
        return back()->with('status', 'Unit saved: '.$data['unit_id']);
    }

    public function update(Request $request, string $unitId)
    {
        $data = $request->validate([
            'room_no'     => 'nullable|string|max:255',
            'colony_type' => 'nullable|string|max:255',
            'block_name'  => 'nullable|string|max:255',
            'department'  => ['nullable', 'string', Rule::in(self::DEPARTMENTS)],
        ]);
        DB::table('util_unit')->where('unit_id', $unitId)->update($data + ['updated_at' => now()]);
        return back()->with('status', 'Unit updated: '.$unitId);
    }

    public function setResidenceStatus(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|string|max:255',
            'status'     => ['required', Rule::in(['UNASSIGNED','FORM_PENDING','OUTSIDE'])],
        ]);

        $emp = DB::table('employees_master')->where('company_id', $data['company_id'])->first();
        if (!$emp) {
            return back()->with('error', 'Employee not found: '.$data['company_id']);
        }

        DB::table('employees_master')->where('company_id', $data['company_id'])->update([
            'residence_status' => $data['status'],
            'unit_id'    => $data['status'] === 'OUTSIDE' ? 'OUTSIDE' : null,
            'room_no'    => null,
            'updated_at' => now(),
        ]);
        DB::table('electric_v1_occupancy')->where('company_id', $data['company_id'])->delete();

        $label = ['UNASSIGNED'=>'No Residence','FORM_PENDING'=>'Form Not Received','OUTSIDE'=>'Outside Colony'][$data['status']];
        return back()->with('status', $emp->name.' ('.$data['company_id'].') marked as '.$label.'.');
    }

    public function storeRoom(Request $request)
    {
        $data = $request->validate([
            'unit_id'        => 'required|string|max:255',
            'room_no'        => 'required|string|max:255',
            'residence_type' => ['nullable', 'string', Rule::in(self::RESIDENCE_TYPES)],
            'occupant_grade' => ['nullable', 'string', Rule::in(self::OCCUPANT_GRADES)],
            'floor'          => ['required', 'string', Rule::in(self::FLOORS)],
        ]);
        DB::table('util_unit_rooms')->updateOrInsert(
            ['unit_id' => $data['unit_id'], 'room_no' => $data['room_no']],
            [
                'residence_type' => $data['residence_type'] ?? null,
                'occupant_grade' => $data['occupant_grade'] ?? null,
                'floor'          => $data['floor'] ?? 'Ground',
                'is_active'      => 1,
                'updated_at'     => now(),
                'created_at'     => now(),
            ]
        );
        return back()->with('status', 'Room saved: '.$data['room_no']);
    }

    public function toggleRoom(int $id)
    {
        $row = DB::table('util_unit_rooms')->where('id', $id)->first();
        if ($row) {
            DB::table('util_unit_rooms')->where('id', $id)
                ->update(['is_active' => $row->is_active ? 0 : 1, 'updated_at' => now()]);
        }
        return back()->with('status', 'Room status updated.');
    }

    public function toggle(string $unitId)
    {
        $row = DB::table('util_unit')->where('unit_id', $unitId)->first();
        if ($row) {
            DB::table('util_unit')->where('unit_id', $unitId)
                ->update(['is_active' => $row->is_active ? 0 : 1, 'updated_at' => now()]);
        }
        return back()->with('status', $row && $row->is_active ? 'Unit deactivated.' : 'Unit activated.');
    }
}
