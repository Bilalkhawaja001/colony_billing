<?php

namespace App\Repositories\ElectricV1;

use Illuminate\Support\Facades\DB;

class AuditRepository extends BaseRepository
{
    public function appendExceptions(array $rows): void
    {
        foreach ($rows as $r) {
            DB::insert('INSERT INTO electric_v1_exception_log(run_id,logged_at,severity,exception_code,message,company_id,unit_id,room_id,cycle_start_date,cycle_end_date) VALUES(?,?,?,?,?,?,?,?,?,?)', [
                $r['run_id'],$r['logged_at'],$r['severity'],$r['exception_code'],$r['message'],$r['company_id'] ?? 'N/A',$r['unit_id'] ?? 'N/A',$r['room_id'] ?? 'N/A',$r['cycle_start_date'],$r['cycle_end_date']
            ]);
        }
    }

    public function appendRunHistory(array $r): void
    {
        DB::insert('INSERT INTO electric_v1_run_history(run_id,run_start,run_end,cycle_start_date,cycle_end_date,status,processed_count,skipped_count,exception_count) VALUES(?,?,?,?,?,?,?,?,?)', [
            $r['run_id'],$r['run_start'],$r['run_end'],$r['cycle_start_date'],$r['cycle_end_date'],$r['status'],$r['processed_count'],$r['skipped_count'],$r['exception_count']
        ]);
    }
}
