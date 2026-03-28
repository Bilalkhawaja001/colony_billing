<?php

declare(strict_types=1);

use App\Services\Billing\DraftBillingFlowService;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

/** @var DraftBillingFlowService $svc */
$svc = $app->make(DraftBillingFlowService::class);

$out = [];

// clean target rows
foreach (['util_billing_line','util_billing_run','util_formula_result','util_drinking_formula_result','util_school_van_monthly_charge','util_month_cycle','billing_rows','billing_run','logs','finalized_months','hr_input','map_room','readings','ro_drinking'] as $t) {
    DB::statement("DELETE FROM {$t}");
}

// util workflow month
DB::statement("INSERT INTO util_month_cycle(month_cycle,state) VALUES('03-2026','OPEN')");
DB::statement("INSERT INTO util_formula_result(month_cycle,employee_id,elec_units,elec_amount,chargeable_general_water_liters,water_general_amount,created_at,updated_at) VALUES('03-2026','E100',10,500,100,20,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
DB::statement("INSERT INTO util_drinking_formula_result(month_cycle,employee_id,billed_liters,rate,amount,created_at,updated_at) VALUES('03-2026','E100',20,0.5,10,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");
DB::statement("INSERT INTO util_school_van_monthly_charge(month_cycle,employee_id,child_name,school_name,class_level,service_mode,rate,amount,charged_flag,created_at,updated_at) VALUES('03-2026','E100','Kid','School','1','BOTH_WAY',100,100,1,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)");

$out['run_first'] = $svc->run(['month_cycle' => '03-2026', 'run_key' => 'GOLDEN-RUN', 'actor_user_id' => 1]);

DB::statement("UPDATE util_formula_result SET elec_amount=600 WHERE month_cycle='03-2026' AND employee_id='E100'");
$out['run_second'] = $svc->run(['month_cycle' => '03-2026', 'run_key' => 'GOLDEN-RUN', 'actor_user_id' => 1]);

$out['monthly_summary'] = $svc->monthlySummary(['month_cycle' => '03-2026']);

DB::statement("UPDATE util_month_cycle SET state='APPROVAL' WHERE month_cycle='03-2026'");
$runId = (int)($out['run_second']['run_id'] ?? 0);
$out['lock'] = $svc->lock(['run_id' => $runId, 'actor_user_id' => 1]);

DB::statement("UPDATE util_month_cycle SET state='LOCKED' WHERE month_cycle='03-2026'");
$out['run_when_locked'] = $svc->run(['month_cycle' => '03-2026', 'run_key' => 'LOCKED-TRY', 'actor_user_id' => 1]);

$out['export_excel'] = $svc->exportExcelMonthlySummary(['month_cycle' => '03-2026']);
$out['export_excel_meta'] = [
    '_http' => $out['export_excel']['_http'] ?? 200,
    'status' => $out['export_excel']['status'] ?? null,
    'filename' => $out['export_excel']['filename'] ?? null,
    'bytes' => strlen((string)($out['export_excel']['content'] ?? '')),
];
unset($out['export_excel']);

// finalize month
DB::statement("INSERT INTO hr_input(month_cycle,company_id,active_days) VALUES('04-2026','E100',30)");
DB::statement("INSERT INTO map_room(month_cycle,unit_id,company_id) VALUES('04-2026','U1','E100')");
DB::statement("INSERT INTO readings(month_cycle,meter_id,unit_id,meter_type,usage,amount) VALUES('04-2026','M1','U1','ELEC',10,500)");
DB::statement("INSERT INTO ro_drinking(month_cycle,unit_id,liters,amount) VALUES('04-2026','U1',20,10)");
$out['finalize'] = $svc->finalize(['month_cycle' => '04-2026', 'actor_user_id' => 1]);

$out['db'] = [
    'line_count_run' => (int)(DB::selectOne("SELECT COUNT(*) AS c FROM util_billing_line bl JOIN util_billing_run br ON br.id=bl.billing_run_id WHERE br.month_cycle='03-2026' AND br.run_key='GOLDEN-RUN'")->c ?? 0),
    'line_total_run' => (float)(DB::selectOne("SELECT ROUND(COALESCE(SUM(amount),0),2) AS s FROM util_billing_line bl JOIN util_billing_run br ON br.id=bl.billing_run_id WHERE br.month_cycle='03-2026' AND br.run_key='GOLDEN-RUN'")->s ?? 0),
    'finalize_rows' => (int)(DB::selectOne("SELECT COUNT(*) AS c FROM billing_rows WHERE month_cycle='04-2026'")->c ?? 0),
];

$outFile = dirname(__DIR__).'/../reports/phase6_laravel_results.json';
if (!is_dir(dirname($outFile))) {
    mkdir(dirname($outFile), 0777, true);
}
file_put_contents($outFile, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo $outFile.PHP_EOL;
