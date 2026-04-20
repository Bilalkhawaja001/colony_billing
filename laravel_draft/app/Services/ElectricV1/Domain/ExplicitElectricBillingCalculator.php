<?php

namespace App\Services\ElectricV1\Domain;

use DateInterval;
use DatePeriod;
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
        $employees = [];
        foreach ($unitOccupancyRows as $row) {
            $companyId = trim((string)($row['company_id'] ?? ''));
            if ($companyId === '') {
                continue;
            }
            $employees[$companyId] = true;
        }

        return count($employees);
    }

    public static function employeeActiveDaysInUnit(array $employeeUnitRows, string $readingFrom, string $readingTo, float $attendanceDays): float
    {
        $readingStart = new DateTimeImmutable($readingFrom);
        $readingEnd = new DateTimeImmutable($readingTo);

        $totalStayDays = 0.0;

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

            $totalStayDays += (float)$overlapStart->diff($overlapEnd)->days + 1.0;
        }

        if ($totalStayDays <= 0.0 || $attendanceDays <= 0.0) {
            return 0.0;
        }

        $readingCycleDays = (float)$readingStart->diff($readingEnd)->days + 1.0;
        if ($readingCycleDays <= 0.0) {
            return 0.0;
        }

        return round(($totalStayDays / $readingCycleDays) * $attendanceDays, 4);
    }

    public static function buildUnitDayRoomTimeline(array $unitOccupancyRows, string $readingFrom, string $readingTo): array
    {
        $readingStart = new DateTimeImmutable($readingFrom);
        $readingEnd = new DateTimeImmutable($readingTo);
        $timeline = [];

        foreach ($unitOccupancyRows as $row) {
            $companyId = trim((string)($row['company_id'] ?? ''));
            $roomId = trim((string)($row['room_id'] ?? ''));
            $from = trim((string)($row['from_date'] ?? ''));
            $to = trim((string)($row['to_date'] ?? ''));
            if ($companyId === '' || $roomId === '' || $from === '' || $to === '') {
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

            $period = new DatePeriod($overlapStart, new DateInterval('P1D'), $overlapEnd->modify('+1 day'));
            foreach ($period as $day) {
                $dateKey = $day->format('Y-m-d');
                $timeline[$dateKey][$roomId][$companyId] = true;
            }
        }

        ksort($timeline);
        return $timeline;
    }

    public static function roomSharedAllocation(array $unitOccupancyRows, array $attendanceByCompany, string $readingFrom, string $readingTo, float $unitGrossUnits, float $unitFreeElectric, int $billingMonthDays): array
    {
        $timeline = self::buildUnitDayRoomTimeline($unitOccupancyRows, $readingFrom, $readingTo);
        if ($timeline === []) {
            return ['presence' => [], 'gross' => [], 'allowance' => []];
        }

        $readingStart = new DateTimeImmutable($readingFrom);
        $readingEnd = new DateTimeImmutable($readingTo);
        $readingCycleDays = (float)$readingStart->diff($readingEnd)->days + 1.0;
        if ($readingCycleDays <= 0.0) {
            return ['presence' => [], 'gross' => [], 'allowance' => []];
        }

        $presence = [];
        $allowance = [];
        $dailyAllowance = $billingMonthDays > 0 ? ($unitFreeElectric / $billingMonthDays) : 0.0;

        foreach ($timeline as $rooms) {
            $roomWeights = [];
            $totalRoomWeight = 0.0;

            foreach ($rooms as $roomId => $occupants) {
                $occupantCount = count($occupants);
                if ($occupantCount <= 0) {
                    continue;
                }
                $roomWeights[$roomId] = (float)$occupantCount;
                $totalRoomWeight += (float)$occupantCount;
            }

            foreach ($rooms as $roomId => $occupants) {
                $occupantCount = count($occupants);
                if ($occupantCount <= 0) {
                    continue;
                }

                $roomAllowance = $totalRoomWeight > 0.0
                    ? $dailyAllowance * (($roomWeights[$roomId] ?? 0.0) / $totalRoomWeight)
                    : 0.0;
                $perPersonAllowance = $occupantCount > 0 ? $roomAllowance / $occupantCount : 0.0;

                foreach (array_keys($occupants) as $companyId) {
                    $attendanceDays = max(0.0, (float)($attendanceByCompany[$companyId] ?? 0.0));
                    $attendanceFactor = min(1.0, $attendanceDays / $readingCycleDays);
                    $presence[$companyId] = ($presence[$companyId] ?? 0.0) + $attendanceFactor;
                    $allowance[$companyId] = ($allowance[$companyId] ?? 0.0) + ($perPersonAllowance * $attendanceFactor);
                }
            }
        }

        $gross = [];
        $totalPresence = array_sum($presence);
        $runningAllocated = 0.0;
        $companyIds = array_keys($presence);
        sort($companyIds);
        foreach ($companyIds as $index => $companyId) {
            $employeePresence = (float)($presence[$companyId] ?? 0.0);
            if ($employeePresence <= 0.0 || $unitGrossUnits <= 0.0 || $totalPresence <= 0.0) {
                $gross[$companyId] = 0.0;
                continue;
            }

            if ($index === count($companyIds) - 1) {
                $gross[$companyId] = round($unitGrossUnits - $runningAllocated, 4);
                continue;
            }

            $allocated = round(($employeePresence / $totalPresence) * $unitGrossUnits, 4);
            $gross[$companyId] = $allocated;
            $runningAllocated += $allocated;
        }

        foreach ($presence as $companyId => $value) {
            $presence[$companyId] = round((float)$value, 4);
        }
        foreach ($allowance as $companyId => $value) {
            $allowance[$companyId] = round((float)$value, 4);
        }

        return ['presence' => $presence, 'gross' => $gross, 'allowance' => $allowance];
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
