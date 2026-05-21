<?php

namespace App\Services\Billing;

use App\Models\ElectricActiveDaysMonthly;
use DateTimeImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MonthlyActiveDaysImportService
{
    public function preview(string $billingMonthDate, string $cycleStartDate, string $cycleEndDate, UploadedFile $file, bool $replaceExisting = false): array
    {
        $cycleStart = new DateTimeImmutable($cycleStartDate);
        $cycleEnd = new DateTimeImmutable($cycleEndDate);

        if ($cycleEnd < $cycleStart) {
            return ['status' => 'error', 'error' => 'cycle_end_date cannot be before cycle_start_date', '_http' => 422];
        }

        $cycleDays = $cycleStart->diff($cycleEnd)->days + 1;

        $content = (string) file_get_contents($file->getRealPath());
        if (trim($content) === '') {
            return ['status' => 'error', 'error' => 'Uploaded file is empty', '_http' => 400];
        }

        $rows = $this->csvToAssoc($content);
        if ($rows === []) {
            return ['status' => 'error', 'error' => 'Template header is required', '_http' => 400];
        }

        $employees = DB::table('employees_master')
            ->get(['company_id', 'join_date', 'leave_date'])
            ->mapWithKeys(fn ($row) => [(string) $row->company_id => [
                'join_date' => $row->join_date ? new DateTimeImmutable((string) $row->join_date) : null,
                'leave_date' => $row->leave_date ? new DateTimeImmutable((string) $row->leave_date) : null,
            ]])
            ->all();
        $employeeLookup = array_fill_keys(array_keys($employees), true);

        $validRows = [];
        $invalidRows = [];
        $seenCompanyIds = [];
        $blankRows = 0;

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $normalized = [
                'company_id' => trim((string) ($row['company_id'] ?? '')),
                'active_days' => trim((string) ($row['active_days'] ?? '')),
                'remarks' => trim((string) ($row['remarks'] ?? '')),
            ];

            if ($normalized['company_id'] === '' && $normalized['active_days'] === '' && $normalized['remarks'] === '') {
                $blankRows++;
                continue;
            }

            $errors = [];
            if ($normalized['company_id'] === '') {
                $errors[] = 'company_id is required';
            } elseif (!isset($employeeLookup[$normalized['company_id']])) {
                $errors[] = 'company_id does not exist in employees master';
            }

            if ($normalized['active_days'] === '' || !is_numeric($normalized['active_days'])) {
                $errors[] = 'active_days must be numeric';
            } else {
                $activeDays = (float) $normalized['active_days'];
                if ($activeDays < 0) {
                    $errors[] = 'active_days must be at least 0';
                }
                if (isset($employeeLookup[$normalized['company_id']])) {
                    $maxActiveDays = $this->maxActiveDaysForCycle($employees[$normalized['company_id']], $cycleStart, $cycleEnd);
                    if ($activeDays > $maxActiveDays) {
                        $errors[] = 'active_days cannot exceed '.$maxActiveDays.' for selected billing cycle';
                    }
                } elseif ($activeDays > $cycleDays) {
                    $errors[] = 'active_days cannot exceed '.$cycleDays.' for selected billing cycle';
                }
            }

            if ($normalized['company_id'] !== '') {
                if (isset($seenCompanyIds[$normalized['company_id']])) {
                    $errors[] = 'duplicate company_id in upload';
                }
                $seenCompanyIds[$normalized['company_id']] = true;
            }

            if ($errors !== []) {
                $invalidRows[] = ['row_no' => $line, 'row' => $normalized, 'errors' => $errors];
                continue;
            }

            $validRows[] = [
                'row_no' => $line,
                'company_id' => $normalized['company_id'],
                'active_days' => round((float) $normalized['active_days'], 4),
                'remarks' => $normalized['remarks'] !== '' ? $normalized['remarks'] : null,
            ];
        }

        $existingCompanyIds = ElectricActiveDaysMonthly::query()
            ->whereDate('billing_month_date', $billingMonthDate)
            ->pluck('company_id')
            ->map(fn ($v) => (string) $v)
            ->all();
        $existingLookup = array_fill_keys($existingCompanyIds, true);

        $summary = [
            'total_rows' => count($rows),
            'valid_rows' => count($validRows),
            'invalid_rows' => count($invalidRows),
            'skipped_rows' => $blankRows,
            'replace_existing' => $replaceExisting,
            'would_insert' => count(array_filter($validRows, fn ($row) => !isset($existingLookup[$row['company_id']]))),
            'would_update' => count(array_filter($validRows, fn ($row) => isset($existingLookup[$row['company_id']]))),
            'existing_rows_for_month' => count($existingCompanyIds),
            'cycle_days' => $cycleDays,
        ];

        return [
            'status' => 'ok',
            'billing_month_date' => $billingMonthDate,
            'cycle_start_date' => $cycleStartDate,
            'cycle_end_date' => $cycleEndDate,
            'source_file' => $file->getClientOriginalName(),
            'summary' => $summary,
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
        ];
    }

    public function commit(array $preview, string $uploadedBy = ''): array
    {
        $billingMonthDate = (string) ($preview['billing_month_date'] ?? '');
        $replaceExisting = (bool) ($preview['summary']['replace_existing'] ?? false);
        $validRows = $preview['valid_rows'] ?? [];
        $sourceFile = (string) ($preview['source_file'] ?? '');

        $inserted = 0;
        $updated = 0;
        $skippedDuplicates = 0;

        DB::transaction(function () use ($billingMonthDate, $replaceExisting, $validRows, $sourceFile, $uploadedBy, &$inserted, &$updated, &$skippedDuplicates) {
            if ($replaceExisting) {
                ElectricActiveDaysMonthly::query()->whereDate('billing_month_date', $billingMonthDate)->delete();
            }

            foreach ($validRows as $row) {
                $existing = ElectricActiveDaysMonthly::query()
                    ->whereDate('billing_month_date', $billingMonthDate)
                    ->where('company_id', $row['company_id'])
                    ->exists();

                if ($existing) {
                    $skippedDuplicates++;
                    continue;
                }

                ElectricActiveDaysMonthly::query()->create([
                    'billing_month_date' => $billingMonthDate,
                    'company_id' => $row['company_id'],
                    'active_days' => $row['active_days'],
                    'remarks' => $row['remarks'] ?? null,
                    'source_file' => $sourceFile !== '' ? $sourceFile : null,
                    'uploaded_by' => $uploadedBy !== '' ? $uploadedBy : null,
                ]);

                $inserted++;
            }
        });

        return [
            'status' => 'ok',
            'billing_month_date' => $billingMonthDate,
            'summary' => [
                'total_rows' => (int) ($preview['summary']['total_rows'] ?? 0),
                'valid_rows' => count($validRows),
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped_duplicates' => $skippedDuplicates,
                'skipped_rows' => (int) ($preview['summary']['skipped_rows'] ?? 0),
                'invalid_rows' => (int) ($preview['summary']['invalid_rows'] ?? 0),
                'replace_existing' => $replaceExisting,
            ],
        ];
    }

    public function rowsForMonth(string $billingMonthDate): array
    {
        return ElectricActiveDaysMonthly::query()
            ->whereDate('billing_month_date', $billingMonthDate)
            ->orderBy('company_id')
            ->get(['billing_month_date', 'company_id', 'active_days', 'remarks', 'source_file', 'uploaded_by', 'updated_at'])
            ->toArray();
    }

    private function maxActiveDaysForCycle(array $employee, DateTimeImmutable $cycleStart, DateTimeImmutable $cycleEnd): int
    {
        $start = $cycleStart;
        $end = $cycleEnd;

        if (($employee['join_date'] ?? null) instanceof DateTimeImmutable && $employee['join_date'] > $start) {
            $start = $employee['join_date'];
        }

        if (($employee['leave_date'] ?? null) instanceof DateTimeImmutable && $employee['leave_date'] < $end) {
            $end = $employee['leave_date'];
        }

        if ($end < $start) {
            return 0;
        }

        return $start->diff($end)->days + 1;
    }

    private function csvToAssoc(string $csvText): array
    {
        $lines = array_values(preg_split('/\r\n|\n|\r/', $csvText) ?: []);
        if (count($lines) < 2) {
            return [];
        }

        $headers = array_map(fn ($value) => Str::of((string) $value)->trim()->lower()->toString(), str_getcsv((string) array_shift($lines)));
        $rows = [];

        foreach ($lines as $line) {
            $values = str_getcsv($line);
            if ($values === [null] || $values === false) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $idx => $header) {
                $assoc[$header] = isset($values[$idx]) ? trim((string) $values[$idx]) : '';
            }
            $rows[] = $assoc;
        }

        return $rows;
    }
}
