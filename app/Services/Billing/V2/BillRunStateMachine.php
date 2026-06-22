<?php

namespace App\Services\Billing\V2;

use DateTimeImmutable;
use InvalidArgumentException;

final class BillRunStateMachine
{
    public const DRAFT = 'DRAFT';
    public const PREVIEW_READY = 'PREVIEW_READY';
    public const PENDING_APPROVAL = 'PENDING_APPROVAL';
    public const APPROVED = 'APPROVED';
    public const GENERATED = 'GENERATED';
    public const PUBLISHED = 'PUBLISHED';
    public const CLOSED = 'CLOSED';
    public const VOIDED = 'VOIDED';

    public const ALL = [
        self::DRAFT,
        self::PREVIEW_READY,
        self::PENDING_APPROVAL,
        self::APPROVED,
        self::GENERATED,
        self::PUBLISHED,
        self::CLOSED,
        self::VOIDED,
    ];

    public const COMMITTED = [self::GENERATED, self::PUBLISHED, self::CLOSED];
    public const TERMINAL = [self::CLOSED, self::VOIDED];

    private const TRANSITIONS = [
        self::DRAFT => [self::PREVIEW_READY],
        self::PREVIEW_READY => [self::DRAFT, self::PENDING_APPROVAL, self::APPROVED, self::GENERATED],
        self::PENDING_APPROVAL => [self::DRAFT, self::APPROVED],
        self::APPROVED => [self::DRAFT, self::GENERATED],
        self::GENERATED => [self::PUBLISHED, self::VOIDED],
        self::PUBLISHED => [self::CLOSED, self::VOIDED],
        self::CLOSED => [],
        self::VOIDED => [],
    ];

    public static function normalize(?string $status): string
    {
        $value = strtoupper(trim((string) $status));
        $value = str_replace([' ', '-'], '_', $value);

        if (!in_array($value, self::ALL, true)) {
            throw new InvalidArgumentException('Invalid bill run status: '.$value);
        }

        return $value;
    }

    public static function canTransition(?string $from, string $to): bool
    {
        $from = self::normalize($from ?: self::DRAFT);
        $to = self::normalize($to);

        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public static function assertTransition(?string $from, string $to): void
    {
        if (!self::canTransition($from, $to)) {
            throw new InvalidArgumentException('Invalid bill run transition from '.self::normalize($from ?: self::DRAFT).' to '.self::normalize($to));
        }
    }

    public static function isCommitted(?string $status): bool
    {
        return in_array(self::normalize($status ?: self::DRAFT), self::COMMITTED, true);
    }

    public static function isTerminal(?string $status): bool
    {
        return in_array(self::normalize($status ?: self::DRAFT), self::TERMINAL, true);
    }

    public static function periodKey(string $monthCycle, string $cycleStartDate, string $cycleEndDate): string
    {
        return trim($monthCycle).'|'.trim($cycleStartDate).'|'.trim($cycleEndDate);
    }

    public static function committedScopeKey(string $periodKey, string $billType, string $scopeHash, ?string $status): ?string
    {
        if (!self::isCommitted($status ?: self::DRAFT)) {
            return null;
        }

        return $periodKey.'|'.trim($billType).'|'.trim($scopeHash);
    }

    public static function scopeHash(array $unitIds, array $flags = []): string
    {
        $unitIds = array_values(array_unique(array_map(static fn ($v) => strtoupper(trim((string) $v)), $unitIds)));
        sort($unitIds, SORT_STRING);
        ksort($flags);

        return hash('sha256', json_encode([
            'units' => $unitIds,
            'flags' => $flags,
        ], JSON_UNESCAPED_SLASHES));
    }

    public static function cycleDays(string $cycleStartDate, string $cycleEndDate): int
    {
        $start = new DateTimeImmutable($cycleStartDate);
        $end = new DateTimeImmutable($cycleEndDate);

        if ($end < $start) {
            throw new InvalidArgumentException('cycle_end_date must be greater than or equal to cycle_start_date');
        }

        return ((int) $start->diff($end)->days) + 1;
    }
}
