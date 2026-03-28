<?php

namespace App\Repositories\ElectricV1;

class MasterRepository extends BaseRepository
{
    public function listEmployees(): array
    {
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
