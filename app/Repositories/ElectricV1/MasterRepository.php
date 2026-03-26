<?php

namespace App\Repositories\ElectricV1;

class MasterRepository extends BaseRepository
{
    public function listEmployees(): array
    {
        return $this->all('SELECT "CompanyID" as company_id, "Name" as name FROM "Employees_Master"');
    }
}
