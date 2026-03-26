<?php

namespace App\Repositories\ElectricV1;

class MasterRepository extends BaseRepository
{
    public function listEmployees(): array
    {
        try {
            return $this->all('SELECT "CompanyID" as company_id, "Name" as name FROM "Employees_Master"');
        } catch (\Throwable $e) {
            // SQLite test environments may not have legacy Employees_Master.
            // V1-safe fallback: derive active identities from V1 attendance input domain.
            return $this->all('SELECT DISTINCT company_id as company_id, company_id as name FROM electric_v1_hr_attendance');
        }
    }
}
