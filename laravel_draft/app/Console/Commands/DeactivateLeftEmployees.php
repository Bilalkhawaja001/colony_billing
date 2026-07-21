<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeactivateLeftEmployees extends Command
{
    protected $signature = 'employees:deactivate-left';
    protected $description = 'Deactivate employees whose leave_date has passed';

    public function handle(): int
    {
        $today = date('Y-m-d');

        $rows = DB::table('employees_master')
            ->whereNotNull('leave_date')
            ->where('leave_date', '<=', $today)
            ->where('active', 'Yes')
            ->get(['company_id', 'name', 'leave_date']);

        if ($rows->isEmpty()) {
            $this->info('Nothing to deactivate.');
            return self::SUCCESS;
        }

        $count = DB::table('employees_master')
            ->whereNotNull('leave_date')
            ->where('leave_date', '<=', $today)
            ->where('active', 'Yes')
            ->update(['active' => 'No', 'updated_at' => now()]);

        foreach ($rows as $r) {
            $this->line('Deactivated: '.$r->company_id.' — '.$r->name.' (left '.$r->leave_date.')');
        }
        $this->info($count.' employee(s) deactivated.');

        return self::SUCCESS;
    }
}
