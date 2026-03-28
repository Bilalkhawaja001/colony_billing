<?php

namespace App\Repositories\ElectricV1;

class ControlRepository extends BaseRepository
{
    public function validateCycleExists(string $cycleStart, string $cycleEnd): bool
    {
        return $cycleStart !== '' && $cycleEnd !== '';
    }
}
