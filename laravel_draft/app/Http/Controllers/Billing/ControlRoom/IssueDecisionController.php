<?php
namespace App\Http\Controllers\Billing\ControlRoom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IssueDecisionController extends Controller
{
    private const WAIVE_ROLES = ['SUPER_ADMIN', 'BILLING_ADMIN'];

    public function store(Request $request)
    {
        $data = $request->validate([
            'cycle_start_date' => 'required|date',
            'cycle_end_date'   => 'required|date',
            'issue_code'       => 'required|string|max:64',
            'unit_id'          => 'nullable|string|max:255',
            'room_no'          => 'nullable|string|max:64',
            'company_id'       => 'nullable|string|max:255',
            'decision'         => 'required|in:FIX,EXCLUDE,USE_PREVIOUS,USE_ZERO,WAIVE',
            'reason'           => 'nullable|string|max:2000',
        ]);

        $user = $request->user();
        $role = strtoupper((string) ($user->role ?? ''));

        // WAIVE ke liye role + reason lazmi
        if ($data['decision'] === 'WAIVE') {
            if (!in_array($role, self::WAIVE_ROLES, true)) {
                return back()->with('error', 'You are not authorized to waive issues.');
            }
            if (trim((string) ($data['reason'] ?? '')) === '') {
                return back()->with('error', 'A reason is required when waiving an issue.');
            }
        }

        DB::table('bill_run_issue_decisions')->updateOrInsert(
            [
                'cycle_start_date' => $data['cycle_start_date'],
                'cycle_end_date'   => $data['cycle_end_date'],
                'issue_code'       => $data['issue_code'],
                'unit_id'          => $data['unit_id'] ?? null,
                'room_no'          => $data['room_no'] ?? null,
            ],
            [
                'company_id'         => $data['company_id'] ?? null,
                'decision'           => $data['decision'],
                'reason'             => $data['reason'] ?? null,
                'decided_by_user_id' => $user->id ?? null,
                'decided_by_name'    => $user->name ?? $user->email ?? null,
                'updated_at'         => now(),
                'created_at'         => now(),
            ]
        );

        return back()->with('status', 'Decision saved: '.$data['decision']);
    }
}
