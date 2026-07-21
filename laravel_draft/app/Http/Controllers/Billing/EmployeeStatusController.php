<?php
namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeStatusController extends Controller
{
    public function setLeave(Request $request, string $companyId)
    {
        $data = $request->validate([
            'leave_date' => 'required|date',
            'remarks'    => 'nullable|string|max:500',
        ]);

        $emp = DB::table('employees_master')->where('company_id', $companyId)->first();
        if (!$emp) {
            return back()->with('error', 'Employee not found: '.$companyId);
        }

        $leave = $data['leave_date'];
        // past ya aaj ki date -> foran inactive; future -> abhi active rahe
        $isPastOrToday = strtotime($leave) <= strtotime(date('Y-m-d'));

        DB::table('employees_master')->where('company_id', $companyId)->update([
            'leave_date' => $leave,
            'active'     => $isPastOrToday ? 'No' : 'Yes',
            'remarks'    => $data['remarks'] ?? $emp->remarks,
            'updated_at' => now(),
        ]);

        return back()->with('status', $isPastOrToday
            ? $companyId.' marked inactive (left on '.$leave.').'
            : $companyId.' will be deactivated automatically on '.$leave.'.');
    }

    public function reactivate(string $companyId)
    {
        DB::table('employees_master')->where('company_id', $companyId)->update([
            'leave_date' => null,
            'active'     => 'Yes',
            'updated_at' => now(),
        ]);
        return back()->with('status', $companyId.' reactivated.');
    }
}
