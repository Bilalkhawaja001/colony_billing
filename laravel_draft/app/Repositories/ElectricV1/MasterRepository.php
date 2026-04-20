<?php

namespace App\Repositories\ElectricV1;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterRepository extends BaseRepository
{
    public function listEmployees(): array
    {
        if (Schema::hasTable('employees_master')) {
            $rows = DB::table('employees_master')->select(['company_id', 'name'])->get()->map(fn ($row) => (array) $row)->all();
            if (count($rows) > 0) {
                return $rows;
            }
        }

        try {
            $rows = $this->all('SELECT "CompanyID" as company_id, "Name" as name FROM "Employees_Master"');
            if (count($rows) > 0) {
                return $rows;
            }
        } catch (\Throwable $e) {
            // fallback below
        }

        // SQLite / draft parity fallback: derive active identities from V1 attendance domain.
        return $this->all('SELECT DISTINCT company_id as company_id, company_id as name FROM electric_v1_hr_attendance');
    }
}
