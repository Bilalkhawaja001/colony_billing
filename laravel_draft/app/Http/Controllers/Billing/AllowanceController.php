<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AllowanceController extends Controller
{
    private const ALLOWANCE_TYPES = [
        'BACHELOR',
        'CONTAINER',
        'HOSTEL',
        'HOUSE',
    ];

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $type = strtoupper(trim((string) $request->query('type', '')));

        if (!in_array($type, self::ALLOWANCE_TYPES, true)) {
            $type = '';
        }

        $unitOccupancy = DB::table('electric_v1_occupancy')
            ->select('unit_id', DB::raw('COUNT(DISTINCT company_id) AS occupant_count'))
            ->groupBy('unit_id')
            ->pluck('occupant_count', 'unit_id');
        $roomOccupancy = DB::table('electric_v1_occupancy')
            ->select('unit_id', 'room_id', DB::raw('COUNT(DISTINCT company_id) AS occupant_count'))
            ->groupBy('unit_id', 'room_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $this->roomKey((string) $row->unit_id, (string) $row->room_id) => (int) $row->occupant_count,
            ]);

        $parents = DB::table('electric_v1_allowance')->orderBy('unit_id')->get();
        $parentByUnit = $parents->keyBy(fn ($row) => strtoupper(trim((string) $row->unit_id)));
        $roomMeta = $this->roomMetadata();

        $rows = $parents->map(function ($row) use ($unitOccupancy) {
            $allowanceType = $this->normalizedAllowanceType($row);

            return [
                'source' => 'unit',
                'id' => (int) $row->id,
                'unit_id' => (string) $row->unit_id,
                'room_no' => $row->room_no,
                'unit_name' => $row->unit_name,
                'floor' => $row->floor,
                'allowance_type' => $allowanceType,
                'residence_type' => (string) $row->residence_type,
                'free_electric' => (float) $row->free_electric,
                'is_active' => (bool) $row->is_active,
                'occupancy' => (int) ($unitOccupancy[$row->unit_id] ?? 0),
            ];
        });

        if (Schema::hasTable('electric_v1_room_allowance')) {
            $roomRows = DB::table('electric_v1_room_allowance')
                ->orderBy('unit_id')
                ->orderBy('room_no')
                ->get()
                ->map(function ($room) use ($parentByUnit, $roomMeta, $roomOccupancy) {
                    $unitKey = strtoupper(trim((string) $room->unit_id));
                    $parent = $parentByUnit->get($unitKey);
                    $meta = $roomMeta->get($this->roomKey((string) $room->unit_id, (string) $room->room_no));
                    $allowanceType = $parent
                        ? $this->normalizedAllowanceType($parent)
                        : $this->classifyType((string) ($meta->residence_type ?? ''));

                    return [
                        'source' => 'room',
                        'id' => (int) $room->id,
                        'unit_id' => (string) $room->unit_id,
                        'room_no' => (string) $room->room_no,
                        'unit_name' => $parent->unit_name ?? null,
                        'floor' => $meta->block_floor ?? ($parent->floor ?? null),
                        'allowance_type' => $allowanceType ?: 'UNCLASSIFIED',
                        'residence_type' => 'ROOM',
                        'free_electric' => (float) $room->room_free_allowance,
                        'is_active' => (bool) $room->is_active,
                        'occupancy' => (int) ($roomOccupancy[$this->roomKey((string) $room->unit_id, (string) $room->room_no)] ?? 0),
                    ];
                });

            $rows = $rows->concat($roomRows);
        }

        $rows = $rows
            ->filter(function (array $row) use ($q, $type) {
                if ($type !== '' && $row['allowance_type'] !== $type) {
                    return false;
                }

                if ($q === '') {
                    return true;
                }

                $haystack = strtoupper(implode(' ', [
                    $row['unit_id'],
                    $row['room_no'] ?? '',
                    $row['unit_name'] ?? '',
                ]));

                return str_contains($haystack, strtoupper($q));
            })
            ->sortBy([
                fn (array $a, array $b) => ($b['is_active'] <=> $a['is_active']),
                fn (array $a, array $b) => strcmp($a['unit_id'], $b['unit_id']),
                fn (array $a, array $b) => strcmp((string) $a['room_no'], (string) $b['room_no']),
            ])
            ->values();

        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;
        $pagedRows = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $totalOccupancy = (int) DB::table('electric_v1_occupancy')
            ->distinct('company_id')
            ->count('company_id');
        $totalAllocatedUnits = (float) $parents
            ->where('is_active', true)
            ->sum('free_electric');

        return view('billing_control.allowances', [
            'pageTitle' => 'Free Allowances',
            'roomImportPreview' => $this->loadRoomImportPreview(),
            'rows' => $pagedRows,
            'q' => $q,
            'type' => $type,
            'totalUnits' => $rows->count(),
            'totalOccupancy' => $totalOccupancy,
            'totalAllocatedUnits' => $totalAllocatedUnits,
            'allowanceTypes' => self::ALLOWANCE_TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedAllowance($request);

        if ($data['room_no'] !== null) {
            $this->storeRoomAllowance($data);

            return back()->with('status', 'Room allowance added successfully: '.$data['room_no']);
        }

        $data['residence_type'] = $this->billingResidenceType($data['allowance_type']);

        DB::transaction(function () use ($data) {
            abort_if(
                DB::table('electric_v1_allowance')->where('unit_id', $data['unit_id'])->exists(),
                422,
                'This unit allowance already exists.'
            );
            DB::table('electric_v1_allowance')->insert(array_merge($data, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        });

        return back()->with('status', 'Unit allowance added successfully: '.$data['unit_id']);
    }

    public function update(Request $request, int $allowance): RedirectResponse
    {
        $row = DB::table('electric_v1_allowance')->where('id', $allowance)->first();
        abort_if(!$row, 404, 'Allowance record not found.');

        $data = $this->validatedAllowance($request);
        $data['unit_id'] = (string) $row->unit_id;
        $data['residence_type'] = $this->billingResidenceType($data['allowance_type']);

        DB::transaction(function () use ($allowance, $data) {
            DB::table('electric_v1_allowance')
                ->where('id', $allowance)
                ->update(array_merge($data, ['updated_at' => now()]));
        });

        return back()->with('status', 'Unit allowance updated successfully: '.$data['unit_id']);
    }

    public function updateRoom(Request $request, int $roomAllowance): RedirectResponse
    {
        $room = DB::table('electric_v1_room_allowance')->where('id', $roomAllowance)->first();
        abort_if(!$room, 404, 'Room allowance record not found.');

        $data = $this->validatedAllowance($request);
        abort_if($data['room_no'] === null, 422, 'Room No. is required for a room allowance.');
        abort_if($data['allowance_type'] === 'HOUSE', 422, 'Room allowance cannot use HOUSE billing mode.');

        DB::transaction(function () use ($roomAllowance, $room, $data) {
            $duplicate = DB::table('electric_v1_room_allowance')
                ->where('unit_id', $room->unit_id)
                ->where('room_no', $data['room_no'])
                ->where('id', '<>', $roomAllowance)
                ->exists();
            abort_if($duplicate, 422, 'This room allowance already exists.');

            DB::table('electric_v1_room_allowance')
                ->where('id', $roomAllowance)
                ->update([
                    'room_no' => $data['room_no'],
                    'room_free_allowance' => $data['free_electric'],
                    'updated_at' => now(),
                ]);

            DB::table('electric_v1_allowance')
                ->where('unit_id', $room->unit_id)
                ->update([
                    'unit_name' => $data['unit_name'],
                    'floor' => $data['floor'],
                    'allowance_type' => $data['allowance_type'],
                    'residence_type' => $this->billingResidenceType($data['allowance_type']),
                    'updated_at' => now(),
                ]);
        });

        return back()->with('status', 'Room allowance updated successfully: '.$data['room_no']);
    }

    public function toggleStatus(Request $request, int $allowance): RedirectResponse
    {
        $newStatus = $this->toggle('electric_v1_allowance', $allowance);

        return back()->with('status', $newStatus ? 'Unit allowance activated.' : 'Unit allowance deactivated.');
    }

    public function toggleRoomStatus(Request $request, int $roomAllowance): RedirectResponse
    {
        $newStatus = $this->toggle('electric_v1_room_allowance', $roomAllowance);

        return back()->with('status', $newStatus ? 'Room allowance activated.' : 'Room allowance deactivated.');
    }

    public function importPreview(Request $request): RedirectResponse
    {
        $request->validate([
            'room_allowance_csv' => ['required', 'file', 'max:5120', 'mimes:csv,txt'],
        ]);

        $upload = $request->file('room_allowance_csv');
        abort_if(!$upload || !$upload->isValid(), 422, 'Upload failed. Please choose a valid CSV file.');

        $preview = $this->buildRoomAllowanceImportPreview(
            $upload->getRealPath(),
            (string) $upload->getClientOriginalName()
        );

        $token = Str::random(48);
        $preview['token'] = $token;
        $preview['session_id'] = hash('sha256', $request->session()->getId());
        $preview['user_id'] = optional($request->user())->getAuthIdentifier();
        $preview['expires_at'] = now()->addMinutes(30)->toIso8601String();

        Storage::disk('local')->put($this->roomImportPreviewPath($token), json_encode($preview, JSON_THROW_ON_ERROR));
        $request->session()->put('room_allowance_import_token', $token);

        return redirect()
            ->route('billing.allowances')
            ->with('status', 'Room allowance CSV preview generated. Review the results before importing.');
    }

    public function importCommit(Request $request): RedirectResponse
    {
        $request->validate([
            'import_token' => ['required', 'string'],
        ]);

        $preview = $this->loadRoomImportPreview((string) $request->input('import_token'));
        abort_if(!$preview, 419, 'Import preview expired or is no longer available. Please upload the CSV again.');
        abort_if((int) ($preview['summary']['invalid'] ?? 0) > 0, 422, 'Import cannot be committed while invalid CSV rows exist.');
        abort_if((int) ($preview['summary']['new'] ?? 0) < 1, 422, 'Import has no new rows to insert.');

        $inserted = 0;
        $skipped = 0;

        DB::transaction(function () use ($preview, &$inserted, &$skipped) {
            foreach ($preview['rows'] as $row) {
                if (($row['result'] ?? '') !== 'NEW') {
                    $skipped++;
                    continue;
                }

                $exists = DB::table('electric_v1_room_allowance')
                    ->where('unit_id', $row['unit_id'])
                    ->where('room_no', $row['room_no'])
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                DB::table('electric_v1_room_allowance')->insert([
                    'unit_id' => $row['unit_id'],
                    'room_no' => $row['room_no'],
                    'room_free_allowance' => $row['room_free_allowance'],
                    'is_active' => (int) $row['is_active'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $inserted++;
            }
        });

        $this->forgetRoomImportPreview($request);

        return redirect()
            ->route('billing.allowances')
            ->with('status', "Room allowance import complete. Inserted: {$inserted}. Skipped: {$skipped}.");
    }

    public function importCancel(Request $request): RedirectResponse
    {
        $this->forgetRoomImportPreview($request);

        return redirect()
            ->route('billing.allowances')
            ->with('status', 'Room allowance CSV preview cancelled. No records were imported.');
    }


    private function buildRoomAllowanceImportPreview(string $path, string $filename): array
    {
        abort_unless(Schema::hasTable('electric_v1_room_allowance'), 409, 'Room allowance table is unavailable.');

        $handle = fopen($path, 'rb');
        abort_if($handle === false, 422, 'Unable to read uploaded CSV file.');

        $rawHeaders = fgetcsv($handle);
        abort_if($rawHeaders === false || $rawHeaders === [null], 422, 'CSV file is empty or malformed.');

        $headers = array_map(function ($header) {
            return strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)));
        }, $rawHeaders);

        $required = ['unit_id', 'room_no', 'room_free_allowance', 'is_active'];
        $allowed = array_merge($required, ['id', 'created_at', 'updated_at']);
        $missing = array_values(array_diff($required, $headers));
        $unknown = array_values(array_diff($headers, $allowed));
        abort_if($missing !== [], 422, 'CSV is missing required headers: '.implode(', ', $missing));
        abort_if($unknown !== [], 422, 'CSV contains unknown headers: '.implode(', ', $unknown));

        $headerMap = array_flip($headers);
        $rows = [];
        $keyCounts = [];
        $lineNumber = 1;

        while (($csvRow = fgetcsv($handle)) !== false) {
            $lineNumber++;
            if ($csvRow === [null] || count(array_filter($csvRow, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            abort_if(count($rows) >= 5000, 422, 'CSV exceeds the maximum 5,000 data rows.');
            abort_if(count($csvRow) > count($headers), 422, "CSV row {$lineNumber} has more columns than the header row.");

            $unitId = strtoupper(trim((string) ($csvRow[$headerMap['unit_id']] ?? '')));
            $roomNo = strtoupper(trim((string) ($csvRow[$headerMap['room_no']] ?? '')));
            $allowanceRaw = trim((string) ($csvRow[$headerMap['room_free_allowance']] ?? ''));
            $statusRaw = trim((string) ($csvRow[$headerMap['is_active']] ?? ''));
            $key = $unitId.'|'.$roomNo;
            $errors = [];

            if ($unitId === '' || strlen($unitId) > 255) {
                $errors[] = 'unit_id is required and must be at most 255 characters';
            }
            if ($roomNo === '' || strlen($roomNo) > 64) {
                $errors[] = 'room_no is required and must be at most 64 characters';
            }
            if (!preg_match('/^\d+(?:\.\d+)?$/', $allowanceRaw)) {
                $errors[] = 'room_free_allowance must be a non-negative numeric value';
            } elseif ((float) $allowanceRaw < 0 || (float) $allowanceRaw > 9999999999.9999) {
                $errors[] = 'room_free_allowance is outside the allowed range';
            }
            if (!in_array($statusRaw, ['0', '1'], true)) {
                $errors[] = 'is_active must be 0 or 1';
            }

            if ($unitId !== '' && $roomNo !== '') {
                $keyCounts[$key] = ($keyCounts[$key] ?? 0) + 1;
            }

            $rows[] = [
                'csv_row' => $lineNumber,
                'unit_id' => $unitId,
                'room_no' => $roomNo,
                'room_free_allowance' => $allowanceRaw,
                'allowance_compare' => $allowanceRaw !== '' && preg_match('/^\d+(?:\.\d+)?$/', $allowanceRaw) ? number_format((float) $allowanceRaw, 4, '.', '') : null,
                'is_active' => $statusRaw,
                'key' => $key,
                'result' => $errors === [] ? 'PENDING' : 'INVALID',
                'reason' => $errors === [] ? '' : implode('; ', $errors),
            ];
        }
        fclose($handle);

        $existing = DB::table('electric_v1_room_allowance')
            ->select('unit_id', 'room_no', 'room_free_allowance', 'is_active')
            ->get()
            ->mapWithKeys(fn ($row) => [
                strtoupper(trim((string) $row->unit_id)).'|'.strtoupper(trim((string) $row->room_no)) => $row,
            ]);

        $summary = ['total' => count($rows), 'new' => 0, 'existing_skipped' => 0, 'conflict_skipped' => 0, 'invalid' => 0];

        foreach ($rows as &$row) {
            if ($row['result'] === 'INVALID') {
                $summary['invalid']++;
                continue;
            }

            if (($keyCounts[$row['key']] ?? 0) > 1) {
                $row['result'] = 'INVALID';
                $row['reason'] = 'Duplicate Unit ID + Room No inside CSV';
                $summary['invalid']++;
                continue;
            }

            $dbRow = $existing->get($row['key']);
            if (!$dbRow) {
                $row['result'] = 'NEW';
                $row['reason'] = 'New room allowance will be inserted on commit';
                $summary['new']++;
                continue;
            }

            $dbAllowance = number_format((float) $dbRow->room_free_allowance, 4, '.', '');
            $dbStatus = (string) (int) $dbRow->is_active;
            if ($dbAllowance === $row['allowance_compare'] && $dbStatus === $row['is_active']) {
                $row['result'] = 'EXISTING_SKIPPED';
                $row['reason'] = 'Matching existing room allowance; skipped';
                $summary['existing_skipped']++;
            } else {
                $row['result'] = 'CONFLICT_SKIPPED';
                $row['reason'] = 'Existing room allowance differs; skipped without overwrite';
                $summary['conflict_skipped']++;
            }
        }
        unset($row);

        return [
            'filename' => basename($filename),
            'summary' => $summary,
            'rows' => array_map(function (array $row) {
                unset($row['key'], $row['allowance_compare']);
                return $row;
            }, $rows),
        ];
    }

    private function loadRoomImportPreview(?string $token = null): ?array
    {
        $token = $token ?: (string) session('room_allowance_import_token', '');
        if ($token === '' || !preg_match('/^[A-Za-z0-9]{48}$/', $token)) {
            return null;
        }

        $path = $this->roomImportPreviewPath($token);
        if (!Storage::disk('local')->exists($path)) {
            session()->forget('room_allowance_import_token');
            return null;
        }

        $preview = json_decode(Storage::disk('local')->get($path), true);
        if (!is_array($preview)
            || ($preview['session_id'] ?? '') !== hash('sha256', session()->getId())
            || now()->greaterThan($preview['expires_at'] ?? now()->subMinute())) {
            Storage::disk('local')->delete($path);
            session()->forget('room_allowance_import_token');
            return null;
        }

        return $preview;
    }

    private function forgetRoomImportPreview(Request $request): void
    {
        $token = (string) $request->session()->pull('room_allowance_import_token', '');
        if ($token !== '' && preg_match('/^[A-Za-z0-9]{48}$/', $token)) {
            Storage::disk('local')->delete($this->roomImportPreviewPath($token));
        }
    }

    private function roomImportPreviewPath(string $token): string
    {
        return 'room_allowance_import_previews/'.$token.'.json';
    }

    private function toggle(string $table, int $id): bool
    {
        return DB::transaction(function () use ($table, $id) {
            $row = DB::table($table)->where('id', $id)->lockForUpdate()->first();
            abort_if(!$row, 404, 'Allowance record not found.');
            $newStatus = !(bool) $row->is_active;
            DB::table($table)->where('id', $id)->update(['is_active' => $newStatus, 'updated_at' => now()]);

            return $newStatus;
        });
    }

    private function storeRoomAllowance(array $data): void
    {
        abort_unless(Schema::hasTable('electric_v1_room_allowance'), 409, 'Room allowance table is unavailable.');
        abort_if($data['allowance_type'] === 'HOUSE', 422, 'Room allowance cannot use HOUSE billing mode.');

        DB::transaction(function () use ($data) {
            $parent = DB::table('electric_v1_allowance')
                ->where('unit_id', $data['unit_id'])
                ->lockForUpdate()
                ->first();
            abort_if(!$parent, 422, 'Create the unit allowance before adding its room allowance.');

            $duplicate = DB::table('electric_v1_room_allowance')
                ->where('unit_id', $data['unit_id'])
                ->where('room_no', $data['room_no'])
                ->exists();
            abort_if($duplicate, 422, 'This room allowance already exists.');

            DB::table('electric_v1_room_allowance')->insert([
                'unit_id' => $data['unit_id'],
                'room_no' => $data['room_no'],
                'room_free_allowance' => $data['free_electric'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('electric_v1_allowance')
                ->where('id', $parent->id)
                ->update([
                    'unit_name' => $data['unit_name'],
                    'floor' => $data['floor'],
                    'allowance_type' => $data['allowance_type'],
                    'residence_type' => $this->billingResidenceType($data['allowance_type']),
                    'updated_at' => now(),
                ]);
        });
    }

    private function validatedAllowance(Request $request): array
    {
        $request->merge([
            'unit_id' => strtoupper(trim((string) $request->input('unit_id'))),
            'room_no' => strtoupper(trim((string) $request->input('room_no'))),
            'unit_name' => trim((string) $request->input('unit_name')),
            'floor' => trim((string) $request->input('floor')),
            'allowance_type' => strtoupper(trim((string) $request->input('allowance_type'))),
        ]);

        $data = $request->validate([
            'unit_id' => ['required', 'string', 'max:255'],
            'room_no' => ['nullable', 'string', 'max:64'],
            'unit_name' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:64'],
            'allowance_type' => ['required', Rule::in(self::ALLOWANCE_TYPES)],
            'free_electric' => ['required', 'numeric', 'min:0', 'max:9999999999.9999'],
        ]);

        foreach (['room_no', 'unit_name', 'floor'] as $nullable) {
            $data[$nullable] = $data[$nullable] === '' ? null : $data[$nullable];
        }

        return $data;
    }

    private function roomMetadata(): Collection
    {
        if (!Schema::hasTable('util_unit_room_snapshot')) {
            return collect();
        }

        return DB::table('util_unit_room_snapshot')
            ->select(['unit_id', 'room_no', 'residence_type', 'block_floor'])
            ->whereNotNull('room_no')
            ->get()
            ->mapWithKeys(fn ($row) => [$this->roomKey((string) $row->unit_id, (string) $row->room_no) => $row]);
    }

    private function normalizedAllowanceType(object $row): string
    {
        $type = strtoupper(trim((string) ($row->allowance_type ?? '')));

        if (in_array($type, self::ALLOWANCE_TYPES, true)) {
            return $type;
        }

        return strtoupper(trim((string) ($row->residence_type ?? ''))) === 'HOUSE'
            ? 'HOUSE'
            : 'UNCLASSIFIED';
    }

    private function classifyType(string $value): ?string
    {
        $value = strtoupper(trim($value));

        foreach (self::ALLOWANCE_TYPES as $type) {
            if (str_contains($value, $type)) {
                return $type;
            }
        }

        return null;
    }

    private function billingResidenceType(string $allowanceType): string
    {
        return $allowanceType === 'HOUSE' ? 'HOUSE' : 'ROOM';
    }

    private function roomKey(string $unitId, string $roomNo): string
    {
        return strtoupper(trim($unitId)).'|'.strtoupper(trim($roomNo));
    }
}
