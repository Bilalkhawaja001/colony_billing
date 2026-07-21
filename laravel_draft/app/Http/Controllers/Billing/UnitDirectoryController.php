<?php
namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitDirectoryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'unit_id'     => 'required|string|max:255',
            'room_no'     => 'nullable|string|max:255',
            'colony_type' => 'nullable|string|max:255',
            'block_name'  => 'nullable|string|max:255',
        ]);
        DB::table('util_unit')->updateOrInsert(
            ['unit_id' => $data['unit_id']],
            [
                'room_no'     => $data['room_no'] ?? null,
                'colony_type' => $data['colony_type'] ?? null,
                'block_name'  => $data['block_name'] ?? null,
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
        ]);
        DB::table('util_unit')->where('unit_id', $unitId)->update($data + ['updated_at' => now()]);
        return back()->with('status', 'Unit updated: '.$unitId);
    }

    public function storeRoom(Request $request)
    {
        $data = $request->validate([
            'unit_id' => 'required|string|max:255',
            'room_no' => 'required|string|max:255',
        ]);
        DB::table('util_unit_rooms')->updateOrInsert(
            ['unit_id' => $data['unit_id'], 'room_no' => $data['room_no']],
            ['is_active' => 1, 'updated_at' => now(), 'created_at' => now()]
        );
        return back()->with('status', 'Room added: '.$data['room_no']);
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
