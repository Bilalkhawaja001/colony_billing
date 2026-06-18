<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\DB;

class DepartmentMasterService
{
    private string $sourceTable = 'employees_master';

    public function list(array $filters = []): array
    {
        $department = trim((string)($filters['department'] ?? ''));
        $section = trim((string)($filters['section'] ?? ''));
        $q = trim((string)($filters['q'] ?? ''));

        return [
            'meta' => [
                'source_table' => $this->sourceTable,
                'mode' => 'read_only',
                'db_write' => false,
            ],
            'department_summary' => $this->departmentSummary(),
            'section_summary' => $this->sectionSummary($department),
            'rows' => $this->detailRows($department, $section, $q),
        ];
    }

    private function departmentSummary(): array
    {
        return DB::table($this->sourceTable)
            ->selectRaw("COALESCE(NULLIF(TRIM(department), ''), '(BLANK)') AS department, COUNT(*) AS total")
            ->groupByRaw("COALESCE(NULLIF(TRIM(department), ''), '(BLANK)')")
            ->orderByDesc('total')
            ->orderBy('department')
            ->get()
            ->map(fn ($r) => [
                'department' => $r->department,
                'total' => (int)$r->total,
            ])
            ->toArray();
    }

    private function sectionSummary(string $department = ''): array
    {
        $query = DB::table($this->sourceTable)
            ->selectRaw("
                COALESCE(NULLIF(TRIM(department), ''), '(BLANK)') AS department,
                COALESCE(NULLIF(TRIM(section), ''), '(BLANK)') AS section,
                COUNT(*) AS total
            ");

        $this->applyExactFilter($query, 'department', $department);

        return $query
            ->groupByRaw("
                COALESCE(NULLIF(TRIM(department), ''), '(BLANK)'),
                COALESCE(NULLIF(TRIM(section), ''), '(BLANK)')
            ")
            ->orderBy('department')
            ->orderByDesc('total')
            ->orderBy('section')
            ->get()
            ->map(fn ($r) => [
                'department' => $r->department,
                'section' => $r->section,
                'total' => (int)$r->total,
            ])
            ->toArray();
    }

    private function detailRows(string $department = '', string $section = '', string $q = ''): array
    {
        $query = DB::table($this->sourceTable)
            ->selectRaw("
                COALESCE(NULLIF(TRIM(department), ''), '(BLANK)') AS department,
                COALESCE(NULLIF(TRIM(section), ''), '(BLANK)') AS section,
                COALESCE(NULLIF(TRIM(sub_section), ''), '(BLANK)') AS sub_section,
                COUNT(*) AS total
            ");

        $this->applyExactFilter($query, 'department', $department);
        $this->applyExactFilter($query, 'section', $section);

        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($w) use ($like) {
                $w->where('department', 'LIKE', $like)
                  ->orWhere('section', 'LIKE', $like)
                  ->orWhere('sub_section', 'LIKE', $like);
            });
        }

        return $query
            ->groupByRaw("
                COALESCE(NULLIF(TRIM(department), ''), '(BLANK)'),
                COALESCE(NULLIF(TRIM(section), ''), '(BLANK)'),
                COALESCE(NULLIF(TRIM(sub_section), ''), '(BLANK)')
            ")
            ->orderBy('department')
            ->orderBy('section')
            ->orderBy('sub_section')
            ->limit(1000)
            ->get()
            ->map(fn ($r) => [
                'department' => $r->department,
                'section' => $r->section,
                'sub_section' => $r->sub_section,
                'total' => (int)$r->total,
            ])
            ->toArray();
    }

    private function applyExactFilter($query, string $column, string $value): void
    {
        if ($value === '' || $value === 'ALL') {
            return;
        }

        if (!in_array($column, ['department', 'section', 'sub_section'], true)) {
            return;
        }

        if ($value === '(BLANK)') {
            $query->where(function ($w) use ($column) {
                $w->whereNull($column)->orWhereRaw("TRIM(`{$column}`) = ''");
            });
            return;
        }

        $query->whereRaw("TRIM(`{$column}`) = ?", [$value]);
    }
}
