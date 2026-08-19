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

        DB::transaction(function () use ($companyId, $leave, $isPastOrToday, $data, $emp) {
            DB::table('employees_master')->where('company_id', $companyId)->update([
                'leave_date' => $leave,
                'active'     => $isPastOrToday ? 'No' : 'Yes',
                'remarks'    => $data['remarks'] ?? $emp->remarks,
                'updated_at' => now(),
            ]);

            if ($isPastOrToday) {
                $this->closeResidenceForLeftEmployee($companyId, $leave);
            }
        });

        return back()->with('status', $isPastOrToday
            ? $companyId.' marked inactive (left on '.$leave.').'
            : $companyId.' will be deactivated automatically on '.$leave.'.');
    }

    public function bulkLeaveForm()
    {
        return view('billing_control.bulk-leave', ['pageTitle' => 'Bulk Mark as Left']);
    }

    public function bulkLeave(Request $request)
    {
        $request->validate([
            'csv_file'      => 'required|file|mimes:csv,txt',
            'default_date'  => 'nullable|date',
            'commit'        => 'nullable',
        ]);

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        $headers = fgetcsv($handle);
        if (!$headers) { fclose($handle); return back()->with('error', 'CSV is empty.'); }

        $headers = array_map(fn($h) => strtolower(trim((string) $h)), $headers);
        $iId = false; $iDate = false;
        foreach ($headers as $k => $h) {
            if (in_array($h, ['companyid', 'company_id'], true)) { $iId = $k; }
            if (in_array($h, ['leave_date', 'leavedate', 'date'], true)) { $iDate = $k; }
        }
        if ($iId === false) { fclose($handle); return back()->with('error', 'CSV must contain a CompanyID column.'); }

        $default = $request->input('default_date') ?: date('Y-m-d');
        $today = date('Y-m-d');
        $rows = [];
        while (($l = fgetcsv($handle)) !== false) {
            $cid = trim((string) ($l[$iId] ?? ''));
            if ($cid === '') { continue; }
            $d = ($iDate !== false && trim((string) ($l[$iDate] ?? '')) !== '') ? trim($l[$iDate]) : $default;
            $emp = DB::table('employees_master')->where('company_id', $cid)->first();
            $rows[] = [
                'company_id' => $cid,
                'leave_date' => $d,
                'name'       => $emp->name ?? null,
                'found'      => (bool) $emp,
                'already'    => $emp && $emp->active === 'No',
            ];
        }
        fclose($handle);

        if (!$request->boolean('commit')) {
            return back()->with('leave_preview', $rows)->with('leave_default', $default);
        }

        $done = 0; $skipped = 0;
        DB::transaction(function () use ($rows, $today, &$done, &$skipped) {
            foreach ($rows as $r) {
                if (!$r['found']) { $skipped++; continue; }
                $isPastOrToday = strtotime($r['leave_date']) <= strtotime($today);

                DB::table('employees_master')->where('company_id', $r['company_id'])->update([
                    'leave_date' => $r['leave_date'],
                    'active'     => $isPastOrToday ? 'No' : 'Yes',
                    'updated_at' => now(),
                ]);

                if ($isPastOrToday) {
                    $this->closeResidenceForLeftEmployee($r['company_id'], $r['leave_date']);
                }

                $done++;
            }
        });

        return back()->with('status', $done.' employee(s) updated, '.$skipped.' not found.');
    }

    private function closeResidenceForLeftEmployee(string $companyId, string $leaveDate): void
    {
        DB::table('employee_residence_assignments')
            ->where('company_id', $companyId)
            ->whereRaw("UPPER(TRIM(status)) = 'ACTIVE'")
            ->whereNull('end_date')
            ->update([
                'status' => 'VACATED',
                'end_date' => $leaveDate,
                'closure_reason' => 'LEFT',
                'updated_at' => now(),
            ]);
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
