<?php
namespace App\Services\BillingEngine;

use App\Services\BillingEngine\Support\SnapshotBuilder;

class Engine
{
    /**
     * Method + cycle + rate le kar bill compute kare (sirf compute — DB write nahi).
     */
    public function preview(string $methodCode, string $cycleStart, string $cycleEnd, float $rate): array
    {
        $method = MethodRegistry::get($methodCode);
        if (!$method) {
            return ['ok'=>false, 'error'=>'METHOD_NOT_FOUND', 'method'=>$methodCode];
        }

        $snapshot = (new SnapshotBuilder())->build($cycleStart, $cycleEnd, $rate);
        $result = $method->compute($snapshot);

        return [
            'ok'          => true,
            'method_code' => $method->code(),
            'method_label'=> $method->label(),
            'cycle_start' => $cycleStart,
            'cycle_end'   => $cycleEnd,
            'rate'        => $rate,
            'snapshot_issues' => $snapshot['issues'],
            'rows'        => $result['rows'],
            'issues'      => $result['issues'],
            'summary'     => $result['summary'],
        ];
    }
}
