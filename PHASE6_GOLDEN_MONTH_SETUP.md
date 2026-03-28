# PHASE6_GOLDEN_MONTH_SETUP

## Goal
Same controlled dataset par Flask vs Laravel non-EV1 limited workflow compare karna.

## Artifacts/scripts
- Flask probe: `mbs_project/phase6_flask_probe.py`
- Laravel probe: `mbs_project/laravel_draft/scripts/phase6_laravel_probe.php`
- Raw dump compare: `mbs_project/reports/phase6_dump_lines.py`
- Comparator: `mbs_project/phase6_compare.py`

## Dataset coverage
- valid month row
- rates/input-equivalent seed for run path
- billing run (first + rerun same run_key)
- report summary
- month lock + write protection check
- export check
- finalize check with separate month inputs

## Commands
1. `python phase6_flask_probe.py`
2. `C:\tools\php85\php.exe artisan migrate:fresh --force`
3. `C:\tools\php85\php.exe scripts\phase6_laravel_probe.php`
4. `python reports\phase6_dump_lines.py`
5. `python phase6_compare.py`

## Output files
- `reports/phase6_flask_results.json`
- `reports/phase6_laravel_results.json`
- `reports/phase6_line_dump.json`
- `reports/phase6_parity_compare.json`