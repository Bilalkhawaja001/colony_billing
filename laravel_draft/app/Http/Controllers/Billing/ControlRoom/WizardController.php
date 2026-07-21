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
            ->with('status', 'Cycle saved: '.$data['month_cycle']);
    }
}
