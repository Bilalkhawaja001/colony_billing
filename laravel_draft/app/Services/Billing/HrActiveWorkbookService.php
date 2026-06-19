<?php

namespace App\Services\Billing;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class HrActiveWorkbookService
{
    private array $targetColumns = [
        'CompanyID',
        'Name',
        "Father's Name",
        'CNIC_No.',
        'Mobile_No.',
        'Department',
        'Section',
        'Sub Section',
        'Designation',
        'Employee Type',
        'Colony Type',
        'Block Floor',
        'Room No',
        'Shared Room',
        'Join Date',
        'Unit_ID',
    ];

    private array $aliases = [
        'CompanyID' => ['companyid','company id','employeeid','employee id','empid','emp id','emp_id','emp code','employee code','code','id','emp id1','emp_id1'],
        'Name' => ['name','employee name','emp name','worker name','emp_name'],
        "Father's Name" => ["father's name",'father name','father_name','father','father/husband name','father husband name','emp f name','emp_f_name','employee father name'],
        'CNIC_No.' => ['cnic_no.','cnic_no','cnic no.','cnic no','cnic','national id'],
        'Mobile_No.' => ['mobile_no.','mobile_no','mobile no.','mobile no','mobile','phone','contact no','contact'],
        'Department' => ['department','dept','dept name','dept_name','department name'],
        'Section' => ['section','section name','section_name'],
        'Sub Section' => ['sub section','sub_section','subsection','sub dept','sub department'],
        'Designation' => ['designation','desig','job title','position'],
        'Employee Type' => ['employee type','emp type','employment type','type','type desc','type_desc'],
        'Colony Type' => ['colony type','residence type','resident type'],
        'Block Floor' => ['block floor','block/floor','block','floor'],
        'Room No' => ['room no','room no.','room_no','room','room number'],
        'Shared Room' => ['shared room','sharing','shared'],
        'Join Date' => ['join date','joining date','date of joining','doj'],
        'Unit_ID' => ['unit_id','unit id','unit','unit code'],
    ];

    public function importUploadedFile(UploadedFile $file, string $monthCycle): array
    {
        $monthCycle = $this->normalizeMonthCycle($monthCycle);
        $ext = strtolower((string) $file->getClientOriginalExtension());

        if (!in_array($ext, ['xlsx', 'csv', 'txt'], true)) {
            throw new \RuntimeException('Only XLSX, CSV or TXT supported. Old XLS format ko XLSX me save karke upload karein.');
        }

        $storedPath = $file->storeAs(
            'hr_active_workbooks/'.$monthCycle,
            date('Ymd_His').'_'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.$ext
        );

        $absolutePath = Storage::path($storedPath);

        $parsed = in_array($ext, ['csv', 'txt'], true)
            ? $this->parseCsvWorkbook($absolutePath)
            : $this->parseXlsxWorkbook($absolutePath);

        $uploadId = DB::table('hr_active_workbook_uploads')->insertGetId([
            'month_cycle' => $monthCycle,
            'original_file_name' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'sheet_count' => count($parsed['sheets']),
            'total_rows' => $parsed['total_rows'],
            'imported_rows' => 0,
            'duplicate_company_ids' => json_encode($parsed['duplicates'], JSON_UNESCAPED_UNICODE),
            'summary_json' => json_encode($parsed['summary'], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $imported = 0;

        foreach ($this->bestRowsByCompanyId($parsed['rows']) as $row) {
            $companyId = $this->normalizeCompanyId($row['CompanyID'] ?? '');

            if ($companyId === '') {
                continue;
            }

            DB::table('hr_active_employee_snapshots')->updateOrInsert(
                [
                    'month_cycle' => $monthCycle,
                    'company_id' => $companyId,
                ],
                [
                    'upload_id' => $uploadId,
                    'sheet_name' => $row['_sheet_name'] ?? '',
                    'row_no' => (int)($row['_row_no'] ?? 0),
                    'name' => $row['Name'] ?? null,
                    'father_name' => $row["Father's Name"] ?? null,
                    'cnic_no' => $row['CNIC_No.'] ?? null,
                    'mobile_no' => $row['Mobile_No.'] ?? null,
                    'department' => $row['Department'] ?? null,
                    'section' => $row['Section'] ?? null,
                    'sub_section' => $row['Sub Section'] ?? null,
                    'designation' => $row['Designation'] ?? null,
                    'employee_type' => $row['Employee Type'] ?? null,
                    'colony_type' => $row['Colony Type'] ?? null,
                    'block_floor' => $row['Block Floor'] ?? null,
                    'room_no' => $row['Room No'] ?? null,
                    'shared_room' => $row['Shared Room'] ?? null,
                    'join_date' => $row['Join Date'] ?? null,
                    'unit_id' => $row['Unit_ID'] ?? null,
                    'raw_json' => json_encode($row['_raw'] ?? $row, JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $imported++;
        }

        DB::table('hr_active_workbook_uploads')->where('id', $uploadId)->update([
            'imported_rows' => $imported,
            'updated_at' => now(),
        ]);

        return [
            'status' => 'ok',
            'mode' => 'reference_only',
            'upload_id' => $uploadId,
            'month_cycle' => $monthCycle,
            'sheet_count' => count($parsed['sheets']),
            'total_rows' => $parsed['total_rows'],
            'imported_rows' => $imported,
            'duplicates' => $parsed['duplicates'],
            'summary' => $parsed['summary'],
        ];
    }


    private function bestRowsByCompanyId(array $rows): array
    {
        $best = [];

        foreach ($rows as $row) {
            $companyId = $this->normalizeCompanyId($row['CompanyID'] ?? '');

            if ($companyId === '') {
                continue;
            }

            $row['CompanyID'] = $companyId;

            if (!isset($best[$companyId])) {
                $best[$companyId] = $row;
                continue;
            }

            foreach ($this->targetColumns as $col) {
                $old = trim((string)($best[$companyId][$col] ?? ''));
                $new = trim((string)($row[$col] ?? ''));

                if ($old === '' && $new !== '') {
                    $best[$companyId][$col] = $new;
                }
            }

            $oldRaw = is_array($best[$companyId]['_raw'] ?? null) ? $best[$companyId]['_raw'] : [];
            $newRaw = is_array($row['_raw'] ?? null) ? $row['_raw'] : [];

            foreach ($newRaw as $k => $v) {
                if (trim((string)($oldRaw[$k] ?? '')) === '' && trim((string)$v) !== '') {
                    $oldRaw[$k] = $v;
                }
            }

            $best[$companyId]['_raw'] = $oldRaw;
        }

        return array_values($best);
    }

    public function latestEmployeeReference(string $companyId): ?array
    {
        $companyId = $this->normalizeCompanyId($companyId);

        $row = DB::table('hr_active_employee_snapshots as s')
            ->leftJoin('hr_active_workbook_uploads as u', 'u.id', '=', 's.upload_id')
            ->where('s.company_id', $companyId)
            ->orderByDesc('s.month_cycle')
            ->orderByDesc('s.id')
            ->select('s.*', 'u.original_file_name', 'u.created_at as upload_created_at')
            ->first();

        if (!$row) {
            return null;
        }

        return [
            'CompanyID' => $row->company_id,
            'Name' => $row->name,
            "Father's Name" => $row->father_name,
            'CNIC_No.' => $row->cnic_no,
            'Mobile_No.' => $row->mobile_no,
            'Department' => $row->department,
            'Section' => $row->section,
            'Sub Section' => $row->sub_section,
            'Designation' => $row->designation,
            'Employee Type' => $row->employee_type,
            'Colony Type' => $row->colony_type,
            'Block Floor' => $row->block_floor,
            'Room No' => $row->room_no,
            'Shared Room' => $row->shared_room,
            'Join Date' => $row->join_date,
            'Unit_ID' => $row->unit_id,
            '_hr_month_cycle' => $row->month_cycle,
            '_hr_sheet_name' => $row->sheet_name,
            '_hr_row_no' => $row->row_no,
            '_hr_upload_file' => $row->original_file_name,
            '_hr_upload_created_at' => $row->upload_created_at,
        ];
    }

    private function parseCsvWorkbook(string $path): array
    {
        $fh = fopen($path, 'r');

        if (!$fh) {
            throw new \RuntimeException('CSV file cannot be opened');
        }

        $rows = [];

        while (($line = fgetcsv($fh)) !== false) {
            $rows[] = $line;
        }

        fclose($fh);

        return $this->parseSheetRows(['CSV' => $rows]);
    }

    private function parseXlsxWorkbook(string $path): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new \RuntimeException('PHP ZipArchive extension required for XLSX import');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('XLSX file cannot be opened');
        }

        $shared = $this->readSharedStrings($zip);
        $sheets = $this->readWorkbookSheets($zip);
        $sheetRows = [];

        foreach ($sheets as $sheetName => $sheetPath) {
            $xml = $zip->getFromName($sheetPath);

            if ($xml === false) {
                continue;
            }

            $sheetRows[$sheetName] = $this->readSheetRows($xml, $shared);
        }

        $zip->close();

        if (!$sheetRows) {
            throw new \RuntimeException('No readable sheets found in workbook');
        }

        return $this->parseSheetRows($sheetRows);
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $strings = [];

        preg_match_all('/<si\b[^>]*>(.*?)<\/si>/is', $xml, $items);

        foreach ($items[1] as $si) {
            preg_match_all('/<t\b[^>]*>(.*?)<\/t>/is', $si, $texts);
            $parts = array_map(fn($v) => html_entity_decode(strip_tags($v), ENT_QUOTES | ENT_XML1, 'UTF-8'), $texts[1] ?? []);
            $strings[] = trim(implode('', $parts));
        }

        return $strings;
    }

    private function readWorkbookSheets(ZipArchive $zip): array
    {
        $wbXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($wbXml === false || $relsXml === false) {
            throw new \RuntimeException('Workbook metadata missing');
        }

        $rels = [];

        preg_match_all('/<Relationship\b([^>]*)\/?>/is', $relsXml, $relMatches);

        foreach ($relMatches[1] as $attrsText) {
            $attrs = $this->xmlAttrs($attrsText);
            $id = $attrs['Id'] ?? '';
            $target = $attrs['Target'] ?? '';

            if ($id !== '' && $target !== '') {
                $target = str_replace('\\', '/', $target);
                if (!str_starts_with($target, 'xl/')) {
                    $target = 'xl/'.ltrim($target, '/');
                }
                $rels[$id] = $target;
            }
        }

        $sheets = [];

        preg_match_all('/<sheet\b([^>]*)\/?>/is', $wbXml, $sheetMatches);

        foreach ($sheetMatches[1] as $attrsText) {
            $attrs = $this->xmlAttrs($attrsText);
            $name = html_entity_decode((string)($attrs['name'] ?? ''), ENT_QUOTES | ENT_XML1, 'UTF-8');
            $rid = (string)($attrs['r:id'] ?? '');

            if ($name !== '' && isset($rels[$rid])) {
                $sheets[$name] = $rels[$rid];
            }
        }

        return $sheets;
    }

    private function readSheetRows(string $xml, array $shared): array
    {
        $rows = [];

        preg_match_all('/<row\b([^>]*)>(.*?)<\/row>/is', $xml, $rowMatches, PREG_SET_ORDER);

        foreach ($rowMatches as $rowMatch) {
            $rowAttrs = $this->xmlAttrs($rowMatch[1]);
            $rowNo = (int)($rowAttrs['r'] ?? 0);
            $body = $rowMatch[2];
            $values = [];

            preg_match_all('/<c\b([^>]*)>(.*?)<\/c>/is', $body, $cellMatches, PREG_SET_ORDER);

            foreach ($cellMatches as $cellMatch) {
                $attrs = $this->xmlAttrs($cellMatch[1]);
                $ref = (string)($attrs['r'] ?? 'A');
                $type = (string)($attrs['t'] ?? '');
                $colIndex = $this->columnIndex($ref);
                $cellBody = $cellMatch[2];
                $value = '';

                if ($type === 'inlineStr') {
                    if (preg_match('/<t\b[^>]*>(.*?)<\/t>/is', $cellBody, $m)) {
                        $value = html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_XML1, 'UTF-8');
                    }
                } else {
                    if (preg_match('/<v\b[^>]*>(.*?)<\/v>/is', $cellBody, $m)) {
                        $raw = html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_XML1, 'UTF-8');

                        if ($type === 's') {
                            $idx = is_numeric($raw) ? (int)$raw : -1;
                            $value = $shared[$idx] ?? '';
                        } else {
                            $value = $raw;
                        }
                    }
                }

                $values[$colIndex] = trim((string)$value);
            }

            if ($values) {
                ksort($values);
                $max = max(array_keys($values));
                $line = [];

                for ($i = 1; $i <= $max; $i++) {
                    $line[] = $values[$i] ?? '';
                }

                $rows[] = [
                    '_row_no' => $rowNo ?: count($rows) + 1,
                    '_cells' => $line,
                ];
            }
        }

        return $rows;
    }

    private function parseSheetRows(array $sheetRows): array
    {
        $out = [];
        $summary = [];
        $seen = [];
        $duplicates = [];
        $total = 0;
        $sheetNames = array_keys($sheetRows);

        foreach ($sheetRows as $sheetName => $rawRows) {
            $rows = [];

            foreach ($rawRows as $idx => $raw) {
                $rows[] = is_array($raw) && array_key_exists('_cells', $raw)
                    ? $raw
                    : ['_row_no' => $idx + 1, '_cells' => $raw];
            }

            [$headerIndex, $map] = $this->detectHeader($rows);
            $sheetImported = 0;

            if ($headerIndex === null) {
                $summary[] = [
                    'sheet' => $sheetName,
                    'status' => 'skipped_no_header',
                    'rows' => count($rows),
                    'imported' => 0,
                ];
                continue;
            }

            for ($i = $headerIndex + 1; $i < count($rows); $i++) {
                $cells = $rows[$i]['_cells'];
                $assoc = [];

                foreach ($this->targetColumns as $target) {
                    $idx = $map[$target] ?? null;
                    $assoc[$target] = $idx !== null ? $this->cleanValue($target, (string)($cells[$idx] ?? '')) : '';
                }

                $companyId = $this->normalizeCompanyId($assoc['CompanyID'] ?? '');

                if ($companyId === '') {
                    continue;
                }

                $assoc['CompanyID'] = $companyId;
                $assoc['_sheet_name'] = $sheetName;
                $assoc['_row_no'] = $rows[$i]['_row_no'] ?? ($i + 1);
                $assoc['_raw'] = $this->rawAssoc($rows[$headerIndex]['_cells'], $cells);

                if (isset($seen[$companyId])) {
                    $duplicates[$companyId] = ($duplicates[$companyId] ?? 1) + 1;
                } else {
                    $seen[$companyId] = true;
                }

                $out[] = $assoc;
                $sheetImported++;
                $total++;
            }

            $summary[] = [
                'sheet' => $sheetName,
                'status' => 'ok',
                'rows' => count($rows),
                'header_row' => $rows[$headerIndex]['_row_no'] ?? ($headerIndex + 1),
                'imported' => $sheetImported,
            ];
        }

        return [
            'sheets' => $sheetNames,
            'rows' => $out,
            'total_rows' => $total,
            'duplicates' => $duplicates,
            'summary' => $summary,
        ];
    }

    private function detectHeader(array $rows): array
    {
        $bestIndex = null;
        $bestMap = [];
        $bestScore = 0;
        $limit = min(25, count($rows));

        for ($i = 0; $i < $limit; $i++) {
            $cells = $rows[$i]['_cells'] ?? [];
            $map = [];
            $score = 0;

            foreach ($cells as $idx => $cell) {
                $target = $this->targetForHeader((string)$cell);

                if ($target && !isset($map[$target])) {
                    $map[$target] = $idx;
                    $score++;
                }
            }

            if (isset($map['CompanyID'])) {
                $score += 5;
            }

            if (isset($map['Name'])) {
                $score += 2;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIndex = $i;
                $bestMap = $map;
            }
        }

        if ($bestScore < 5 || !isset($bestMap['CompanyID'])) {
            return [null, []];
        }

        return [$bestIndex, $bestMap];
    }

    private function targetForHeader(string $header): ?string
    {
        $clean = $this->cleanHeader($header);

        foreach ($this->aliases as $target => $aliases) {
            foreach ($aliases as $alias) {
                if ($clean === $this->cleanHeader($alias)) {
                    return $target;
                }
            }
        }

        return null;
    }

    private function cleanHeader(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
        return trim((string)$value);
    }

    private function cleanValue(string $target, string $value): string
    {
        $value = trim($value);

        if ($target === 'Join Date' && is_numeric($value) && (float)$value > 20000) {
            $ts = strtotime('1899-12-30 +'.(int)$value.' days');
            if ($ts) {
                return date('Y-m-d', $ts);
            }
        }

        return $value;
    }

    private function normalizeCompanyId($value): string
    {
        $value = trim((string)$value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/^\d+\.0+$/', $value)) {
            $value = preg_replace('/\.0+$/', '', $value);
        }

        return trim($value);
    }

    private function normalizeMonthCycle(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            throw new \InvalidArgumentException('month_cycle is required');
        }

        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return substr($value, 0, 7);
        }

        $ts = strtotime($value);

        return $ts ? date('Y-m', $ts) : $value;
    }

    private function columnIndex(string $ref): int
    {
        preg_match('/^[A-Z]+/i', $ref, $m);
        $letters = strtoupper($m[0] ?? 'A');
        $n = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $n = $n * 26 + (ord($letters[$i]) - 64);
        }

        return max(1, $n);
    }

    private function rawAssoc(array $headers, array $cells): array
    {
        $out = [];

        foreach ($headers as $i => $h) {
            $key = trim((string)$h);
            if ($key === '') {
                $key = 'Column_'.($i + 1);
            }
            $out[$key] = trim((string)($cells[$i] ?? ''));
        }

        return $out;
    }

    private function xmlAttrs(string $text): array
    {
        $out = [];

        preg_match_all('/([a-zA-Z0-9:_-]+)\s*=\s*"([^"]*)"/', $text, $m, PREG_SET_ORDER);

        foreach ($m as $a) {
            $out[$a[1]] = html_entity_decode($a[2], ENT_QUOTES | ENT_XML1, 'UTF-8');
        }

        return $out;
    }
}
