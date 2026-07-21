<?php
namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReadingImportController extends Controller
{
    public function preview(Request $request)
    {
        $data = $this->parse($request);
        if (isset($data['error'])) {
            return back()->with('error', $data['error']);
        }
        return back()->with('reading_preview', $data);
    }

    public function commit(Request $request)
    {
        $data = $this->parse($request);
        if (isset($data['error'])) {
            return back()->with('error', $data['error']);
        }

        $inserted = 0; $updated = 0;
        DB::transaction(function () use ($data, &$inserted, &$updated) {
            foreach ($data['rows'] as $r) {
                $exists = DB::table('electric_v1_readings')
                    ->where('cycle_start_date', $data['cycle_start'])
                    ->where('cycle_end_date', $data['cycle_end'])
                    ->where('unit_id', $r['unit_id'])->exists();

                if ($exists) {
                    DB::table('electric_v1_readings')
                        ->where('cycle_start_date', $data['cycle_start'])
                        ->where('cycle_end_date', $data['cycle_end'])
                        ->where('unit_id', $r['unit_id'])
                        ->update([
                            'previous_reading' => $r['previous_reading'],
                            'current_reading'  => $r['current_reading'],
                            'reading_status'   => $r['status'],
                            'updated_at'       => now(),
                        ]);
                    $updated++;
                } else {
                    DB::table('electric_v1_readings')->insert([
                        'cycle_start_date' => $data['cycle_start'],
                        'cycle_end_date'   => $data['cycle_end'],
                        'unit_id'          => $r['unit_id'],
                        'previous_reading' => $r['previous_reading'],
                        'current_reading'  => $r['current_reading'],
                        'reading_status'   => $r['status'],
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                    $inserted++;
                }
            }
        });

        return back()->with('status', "Readings imported — {$inserted} new, {$updated} updated.");
    }

    private function parse(Request $request): array
    {
        $request->validate([
            'csv_file'    => 'required|file|mimes:csv,txt',
            'month_cycle' => 'required|string',
        ]);

        $mc = $request->input('month_cycle');
        $cycle = DB::table('util_month_cycle')->where('month_cycle', $mc)->first();

        // agar cycle mojood nahi to default (16 -> 15) bana do
        if (!$cycle) {
            if (!preg_match('/^(\d{2})-(\d{4})$/', (string) $mc, $m)) {
                return ['error' => 'Invalid month format: '.$mc];
            }
            $mo = (int) $m[1]; $yr = (int) $m[2];
            $pm = $mo - 1; $py = $yr;
            if ($pm === 0) { $pm = 12; $py = $yr - 1; }
            $start = sprintf('%04d-%02d-16', $py, $pm);
            $end   = sprintf('%04d-%02d-15', $yr, $mo);
            DB::table('util_month_cycle')->insert([
                'month_cycle' => $mc, 'state' => 'OPEN',
                'cycle_start_date' => $start, 'cycle_end_date' => $end,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $cycle = DB::table('util_month_cycle')->where('month_cycle', $mc)->first();
        }

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        $headers = fgetcsv($handle);
        if (!$headers) { fclose($handle); return ['error' => 'CSV is empty.']; }

        $headers = array_map(fn($h) => strtolower(trim((string) $h)), $headers);
        $iUnit = array_search('unit_id', $headers, true);
        $iCurr = array_search('current_reading', $headers, true);
        $iPrev = array_search('previous_reading', $headers, true);

        if ($iUnit === false || $iCurr === false) {
            fclose($handle);
            return ['error' => 'CSV must contain unit_id and current_reading columns.'];
        }

        $rows = []; $errors = []; $line = 1;
        while (($l = fgetcsv($handle)) !== false) {
            $line++;
            if ($l === [null] || $l === []) { continue; }

            $unit = trim((string) ($l[$iUnit] ?? ''));
            if ($unit === '') { continue; }

            $curr = (float) ($l[$iCurr] ?? 0);
            $prev = null;
            if ($iPrev !== false && trim((string) ($l[$iPrev] ?? '')) !== '') {
                $prev = (float) $l[$iPrev];
            } else {
                // previous cycle se uthao
                $prev = (float) (DB::table('electric_v1_readings')
                    ->where('unit_id', $unit)
                    ->where('cycle_end_date', '<', $cycle->cycle_start_date)
                    ->orderBy('cycle_end_date', 'desc')
                    ->value('current_reading') ?? 0);
            }

            $status = 'NORMAL';
            if ($curr < $prev) { $status = 'REVERSED'; $errors[] = "Row {$line} ({$unit}): current < previous"; }

            $rows[] = [
                'unit_id'          => $unit,
                'previous_reading' => $prev,
                'current_reading'  => $curr,
                'consumption'      => max($curr - $prev, 0),
                'status'           => $status,
            ];
        }
        fclose($handle);

        return [
            'cycle_start' => $cycle->cycle_start_date,
            'cycle_end'   => $cycle->cycle_end_date,
            'month_cycle' => $request->input('month_cycle'),
            'rows'        => $rows,
            'errors'      => $errors,
        ];
    }
}
