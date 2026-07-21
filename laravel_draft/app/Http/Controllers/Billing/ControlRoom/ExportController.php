<?php
namespace App\Http\Controllers\Billing\ControlRoom;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\ControlRoom\ExportBillRequest;
class ExportController extends Controller
{
    public function index()
    {
        return view('billing_control.export', [
            'pageTitle' => 'Download Excel',
        ]);
    }
    public function download(ExportBillRequest $request)
    {
        $monthCycle = $request->input('billing_month') ?: $request->input('month_cycle');

        $ctx = app(\App\Services\Billing\ControlRoom\ReadinessService::class)
            ->resolveCycleContext($monthCycle);
        $cs = $ctx['cycle_start_date'] ?? null;
        $ce = $ctx['cycle_end_date'] ?? null;

        if (!$cs || !$ce) {
            return back()->with('error', 'Selected month ke liye cycle dates nahi mile.');
        }

        $bundle = app(\App\Services\ElectricV1\ReadService::class)->bundle($cs, $ce);
        $rows = $bundle['final_outputs'] ?? [];

        $filename = 'electric_bills_'.($monthCycle ?: 'month').'_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return response()->streamDownload(function () use ($rows, $cs, $ce) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Company ID', 'Name', 'Cycle Start', 'Cycle End', 'Billable Units', 'Rate', 'Amount']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['company_id'] ?? '',
                    $r['name'] ?? '',
                    $cs,
                    $ce,
                    $r['total_net_billable_units'] ?? 0,
                    $r['flat_rate'] ?? 0,
                    $r['final_amount_rounded'] ?? 0,
                ]);
            }
            fclose($out);
        }, $filename, $headers);
    }
}
