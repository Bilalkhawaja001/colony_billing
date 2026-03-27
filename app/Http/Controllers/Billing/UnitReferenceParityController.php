<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitReferenceParityController extends Controller
{
    public function index()
    {
        $rows = DB::table('util_units_reference')->orderBy('unit_id')->get();
        return response()->json(['status' => 'ok', 'rows' => $rows]);
    }

    public function show(string $unit_id)
    {
        $row = DB::table('util_units_reference')->where('unit_id', $unit_id)->first();
        if (!$row) {
            return response()->json(['status' => 'error', 'error' => 'unit_id not found'], 404);
        }
        return response()->json(['status' => 'ok', 'row' => $row]);
    }

    public function cascade(Request $request)
    {
        $colony = (string)$request->query('colony_type', '');
        $rows = DB::table('util_units_reference')
            ->when($colony !== '', fn($q) => $q->where('colony_type', $colony))
            ->orderBy('colony_type')
            ->orderBy('block_name')
            ->orderBy('room_no')
            ->get();
        return response()->json(['status' => 'ok', 'rows' => $rows]);
    }

    public function upsert(Request $request)
    {
        $unitId = trim((string)$request->input('unit_id', ''));
        if ($unitId === '') {
            return response()->json(['status' => 'error', 'error' => 'unit_id required'], 400);
        }

        DB::table('util_units_reference')->updateOrInsert(
            ['unit_id' => $unitId],
            [
                'colony_type' => $request->input('colony_type'),
                'block_name' => $request->input('block_name'),
                'room_no' => $request->input('room_no'),
                'is_active' => (int)$request->input('is_active', 1),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json(['status' => 'ok', 'unit_id' => $unitId]);
    }

    public function suggest(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        $rows = DB::table('util_units_reference')
            ->when($q !== '', fn($x) => $x->where('unit_id', 'like', "%{$q}%"))
            ->limit((int)$request->query('limit', 20))
            ->orderBy('unit_id')
            ->get(['unit_id', 'colony_type', 'block_name', 'room_no']);

        return response()->json(['status' => 'ok', 'rows' => $rows]);
    }

    public function resolve(string $unit_id)
    {
        $row = DB::table('util_units_reference')->where('unit_id', $unit_id)->first();
        if (!$row) {
            return response()->json(['status' => 'error', 'error' => 'unit_id not found'], 404);
        }

        return response()->json(['status' => 'ok', 'row' => $row]);
    }
}
