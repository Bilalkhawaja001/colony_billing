<?php
namespace App\Http\Controllers\Billing\ControlRoom;

use App\Http\Controllers\Controller;
use App\Services\Billing\ControlRoom\ReadinessService;
use App\Services\Billing\ControlRoom\WizardStepService;
use App\Services\BillingEngine\MethodRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WizardController extends Controller
{
    public function index(Request $request, ReadinessService $readiness, WizardStepService $wizard)
    {
        $monthCycle = $request->input('month_cycle');
        $ctx = $readiness->resolveCycleContext($monthCycle);

        $cycleStart = $ctx['cycle_start_date'] ?? null;
        $cycleEnd   = $ctx['cycle_end_date'] ?? null;
        $month      = $ctx['month_cycle'] ?? $monthCycle;

        $steps = ($cycleStart && $cycleEnd)
            ? $wizard->steps($cycleStart, $cycleEnd, $month)
            : [];

        $months = DB::table('util_month_cycle')->orderBy('cycle_start_date', 'desc')->get();

        return view('billing_control.wizard', [
            'pageTitle'  => 'Billing Wizard',
            'steps'      => $steps,
            'month'      => $month,
            'months'     => $months,
            'cycleStart' => $cycleStart,
            'cycleEnd'   => $cycleEnd,
            'methods'    => MethodRegistry::options(),
            'allOk'      => count($steps) > 0 && collect($steps)->every(fn($s) => $s['ok']),
        ]);
    }

    public function saveCycle(Request $request)
    {
        $data = $request->validate([
            'month_cycle'      => 'required|regex:/^[0-9]{2}-[0-9]{4}$/',
            'cycle_start_date' => 'required|date',
            'cycle_end_date'   => 'required|date|after:cycle_start_date',
        ]);

        // guard: dates must match the month_cycle (prev month 16 -> this month 15)
        [$mm, $yyyy] = array_map('intval', explode('-', $data['month_cycle']));
        $expEnd   = sprintf('%04d-%02d-15', $yyyy, $mm);
        $pm = $mm - 1; $py = $yyyy;
        if ($pm === 0) { $pm = 12; $py = $yyyy - 1; }
        $expStart = sprintf('%04d-%02d-16', $py, $pm);
        $cycleWarning = null;
        if ($data['cycle_start_date'] !== $expStart || $data['cycle_end_date'] !== $expEnd) {
            $cycleWarning = 'Note: saved dates ('.$data['cycle_start_date'].' to '.$data['cycle_end_date']
                .') differ from the usual pattern for '.$data['month_cycle'].' ('.$expStart.' to '.$expEnd
                .'). Saved anyway — please confirm this is intended.';
        }

        $clash = DB::table('util_month_cycle')
            ->where('month_cycle', '<>', $data['month_cycle'])
            ->where('cycle_start_date', $data['cycle_start_date'])
            ->where('cycle_end_date', $data['cycle_end_date'])
            ->value('month_cycle');
        if ($clash) {
            return back()->with('error', 'These dates are already used by '.$clash
                .'. Two cycles cannot share the same date range.');
        }

        DB::table('util_month_cycle')->updateOrInsert(
            ['month_cycle' => $data['month_cycle']],
            [
                'cycle_start_date' => $data['cycle_start_date'],
                'cycle_end_date'   => $data['cycle_end_date'],
                'state'            => 'OPEN',
                'updated_at'       => now(),
            ]
        );

        return redirect()->route('billing.control.wizard', ['month_cycle' => $data['month_cycle']])
            ->with('status', 'Cycle saved: '.$data['month_cycle'])
            ->with('warning', $cycleWarning);
    }
}
