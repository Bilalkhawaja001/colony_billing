<?php
namespace App\Services\BillingEngine\Contracts;

/**
 * Har billing method (Attendance Prorated, Occupied Room Split, etc.)
 * is interface ko implement karega.
 */
interface BillingMethod
{
    /** Method ka unique code, e.g. 'OCCUPIED_ROOM_EQUAL_SPLIT' */
    public function code(): string;

    /** Insaani naam, e.g. 'Occupied Room Equal Split' */
    public function label(): string;

    /**
     * Snapshot (immutable input data) le kar bill rows compute kare.
     * @param array $snapshot  units, rooms, occupancy, allowance, readings, rate
     * @return array  ['rows' => [...], 'issues' => [...], 'summary' => [...]]
     */
    public function compute(array $snapshot): array;
}
