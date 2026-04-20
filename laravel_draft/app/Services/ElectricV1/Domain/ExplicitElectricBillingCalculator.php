<?php

namespace App\Services\ElectricV1\Domain;

use DateTimeImmutable;

class ExplicitElectricBillingCalculator
{
    public static function billingMonthDays(string $billingMonthDate): int
    {
        $anchor = new DateTimeImmutable($billingMonthDate);
        return (int)$anchor->format('t');
    }

    public static function roomPersons(array $unitOccupancyRows): int
    {
        $rooms = [];
        foreach ($unitOccupancyRows as $row) {
            $roomId = trim((string)($row['room_id'] ?? ''));
            if ($roomId === '') {
                continue;
            }
            $rooms[$roomId] = true;
        }

        return count($rooms);
    }

    public static function employeeActiveDaysInUnit(array $employeeUnitRows, string $readingFrom, string $readingTo, float $attendanceDays): float
    {
        $readingStart = new DateTimeImmutable($readingFrom);
        $readingEnd = new DateTimeImmutable($readingTo);

        $totalStayDays = 0.0;
        $perRoomDays = [];

        foreach ($employeeUnitRows as $row) {
            $roomId = trim((string)($row['room_id'] ?? ''));
            $from = trim((string)($row['from_date'] ?? ''));
            $to = trim((string)($row['to_date'] ?? ''));
            if ($roomId === '' || $from === '' || $to === '') {
                continue;
            }

            $stayStart = new DateTimeImmutable($from);
            $stayEnd = new DateTimeImmutable($to);
            if ($stayStart > $stayEnd) {
                continue;
            }

            $overlapStart = $stayStart > $readingStart ? $stayStart : $readingStart;
            $overlapEnd = $stayEnd < $readingEnd ? $stayEnd : $readingEnd;
            if ($overlapStart > $overlapEnd) {
                continue;
            }

            $days = (float)$overlapStart->diff($overlapEnd)->days + 1.0;
            $totalStayDays += $days;
            $perRoomDays[$roomId] = ($perRoomDays[$roomId] ?? 0.0) + $days;
        }

        if ($totalStayDays <= 0.0 || $attendanceDays <= 0.0) {
            return 0.0;
        }

        return round($attendanceDays, 4);
    }

    public static function allocateUsageShare(float $unitGrossUnits, float $employeeActiveDays, float $unitActiveDays, bool $isLast, float &$runningAllocated): float
    {
        if ($unitGrossUnits <= 0.0 || $employeeActiveDays <= 0.0 || $unitActiveDays <= 0.0) {
            return 0.0;
        }

        if ($isLast) {
            return round($unitGrossUnits - $runningAllocated, 4);
        }

        $allocated = round(($employeeActiveDays / $unitActiveDays) * $unitGrossUnits, 4);
        $runningAllocated += $allocated;
        return $allocated;
    }

    public static function eligibleUnits(float $unitFreeElectric, int $roomPersons, int $billingMonthDays, float $activeDays): float
    {
        return round(($unitFreeElectric / $roomPersons / $billingMonthDays) * $activeDays, 4);
    }

    public static function billableUnits(float $empUsedElec, float $eligibleUnits): float
    {
        return round(max(0.0, $empUsedElec - $eligibleUnits), 4);
    }

    public static function amount(float $billableUnits, float $rate): float
    {
        return round($billableUnits * $rate, 4);
    }
}
