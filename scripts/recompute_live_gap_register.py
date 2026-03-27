#!/usr/bin/env python3
"""
Recompute Flask -> Laravel parity gap register.

Outputs:
- reports/mbs_live_gap_register.md
- reports/mbs_live_gap_register.csv
- reports/mbs_live_gap_register.json
"""

from __future__ import annotations

import csv
import json
import re
from collections import Counter, defaultdict
from dataclasses import dataclass
from datetime import datetime
from pathlib import Path
from typing import Iterable

ROOT = Path(__file__).resolve().parents[3]  # .../clawd
FLASK_APP = ROOT / "mbs_project" / "unified_app" / "api" / "app.py"
LARAVEL_WEB = ROOT / "mbs_project" / "laravel_draft" / "routes" / "web.php"
BILLING_CTRL = ROOT / "mbs_project" / "laravel_draft" / "app" / "Http" / "Controllers" / "Billing" / "BillingDraftController.php"
REPORTS_DIR = ROOT / "reports"
CSV_OUT = REPORTS_DIR / "mbs_live_gap_register.csv"
JSON_OUT = REPORTS_DIR / "mbs_live_gap_register.json"
MD_OUT = REPORTS_DIR / "mbs_live_gap_register.md"


@dataclass
class FlaskRoute:
    method: str
    path: str
    fn: str
    line: int


@dataclass
class LaravelRoute:
    method: str
    path: str
    handler: str
    line: int
    status_hint: str


def load_text(path: Path) -> str:
    return path.read_text(encoding="utf-8", errors="ignore")


def parse_flask_routes(app_text: str) -> list[FlaskRoute]:
    lines = app_text.splitlines()
    routes: list[FlaskRoute] = []

    i = 0
    while i < len(lines):
        line = lines[i]
        if "@app." not in line:
            i += 1
            continue

        m = re.search(r"@app\.(get|post|put|delete|patch|route)\((.*)", line)
        if not m:
            i += 1
            continue

        kind = m.group(1)
        start = i + 1
        dec = line
        j = i
        while not dec.rstrip().endswith(")") and j + 1 < len(lines):
            j += 1
            dec += lines[j]

        path_match = re.search(r"['\"]([^'\"]+)['\"]", dec)
        if not path_match:
            i = j + 1
            continue

        path = path_match.group(1)
        methods: list[str]
        if kind != "route":
            methods = [kind.upper()]
        else:
            mm = re.search(r"methods\s*=\s*\[([^\]]+)\]", dec)
            if mm:
                methods = [x.strip(" '\"\t") for x in mm.group(1).split(",") if x.strip()]
                methods = [m.upper() for m in methods]
            else:
                methods = ["GET"]

        fn = ""
        for k in range(j + 1, min(j + 10, len(lines))):
            dm = re.match(r"\s*def\s+([a-zA-Z0-9_]+)\(", lines[k])
            if dm:
                fn = dm.group(1)
                break

        for method in methods:
            routes.append(FlaskRoute(method=method, path=path, fn=fn, line=start))

        i = j + 1

    # stable and de-dup
    seen = set()
    deduped = []
    for r in routes:
        key = (r.method, r.path)
        if key in seen:
            continue
        seen.add(key)
        deduped.append(r)
    return deduped


def parse_removed_methods(controller_text: str) -> set[str]:
    removed = set()
    for m in re.finditer(r"public\s+function\s+([A-Za-z0-9_]+)\s*\([^)]*\)\s*\{", controller_text):
        fn = m.group(1)
        start = m.start()
        end = controller_text.find("public function", start + 1)
        body = controller_text[start : end if end != -1 else None]
        if re.search(r"['_\"]http['_\"]\s*=>\s*410", body):
            removed.add(fn)
    return removed


COMPLETE_PATHS = {
    # UI workflow pages (controller-backed, tested render surface)
    '/ui/dashboard', '/ui/month-control', '/ui/reports', '/ui/reconciliation',
    '/ui/monthly-setup', '/ui/month-cycle', '/ui/billing', '/ui/elec-summary',
    '/ui/rates', '/ui/water-meters', '/ui/imports', '/ui/van',
    '/ui/employee-master', '/ui/employees', '/ui/employee-helper',
    '/ui/unit-master', '/ui/meter-master', '/ui/meter-register-ingest',
    '/ui/rooms', '/ui/occupancy', '/ui/electric-v1-run', '/ui/electric-v1-outputs',
    '/ui/profile', '/ui/admin/users',
    '/ui/masters/employees', '/ui/masters/units', '/ui/masters/meters', '/ui/masters/rates',
    '/ui/inputs/mapping', '/ui/inputs/hr', '/ui/inputs/readings', '/ui/inputs/ro',
    '/ui/family-details', '/ui/results/employee-wise', '/ui/results/unit-wise', '/ui/logs', '/ui/finalized-months',

    # API/dashboard/results/logs surfaces
    '/api/dashboard/colony-kpis', '/api/dashboard/family-members', '/api/dashboard/van-kids',
    '/api/results/employee-wise', '/api/results/unit-wise', '/api/logs', '/api/rooms/cascade',
    '/api/water/occupancy-snapshot', '/api/water/zone-adjustments', '/api/water/allocation-preview',
    '/api/electric-v1/outputs', '/api/electric-v1/run',

    # Admin/users + imports + rates/expenses
    '/api/profile/change-password', '/api/admin/users/create', '/api/admin/users/update', '/api/admin/users/reset-password',
    '/imports/mark-validated', '/imports/unit-id-aliases', '/imports/meter-register/ingest-preview', '/imports/error-report/<token>',
    '/monthly-rates/initialize', '/monthly-rates/config', '/monthly-rates/history', '/monthly-rates/config/upsert',
    '/rates/upsert', '/rates/approve',
    '/expenses/monthly-variable', '/expenses/monthly-variable/upsert',

    # Master-data + employees + registry + family
    '/units', '/units/upsert', '/units/<unit_id>', '/units/suggest', '/units/resolve/<unit_id>',
    '/api/units/reference', '/api/units/reference/<unit_id>', '/api/units/reference/cascade', '/api/units/reference/upsert',
    '/rooms', '/rooms/upsert', '/rooms/<int:row_id>',
    '/occupancy/context', '/occupancy', '/occupancy/upsert', '/occupancy/<int:row_id>', '/api/occupancy/autofill',
    '/employees', '/employees/search', '/employees/<company_id>', '/employees/meta/departments',
    '/employees/import', '/employees/upsert', '/employees/add',
    '/meter-reading/latest/<unit_id>', '/meter-reading/upsert', '/meter-unit', '/meter-unit/upsert',
    '/registry/employees/upsert', '/registry/employees/<company_id>', '/registry/employees/import-preview',
    '/registry/employees/import-commit', '/registry/employees/promote-to-master',
    '/family/details/context', '/family/details', '/family/details/upsert',

    # Billing/reports/exports core
    '/api/billing/precheck', '/api/billing/finalize',
    '/billing/elec/compute', '/billing/water/compute', '/billing/run', '/billing/lock', '/billing/fingerprint',
    '/billing/adjustments/list', '/billing/print/<month_cycle>/<employee_id>',
    '/reports/monthly-summary', '/reports/recovery', '/reports/employee-bill-summary',
    '/reports/reconciliation', '/reports/van', '/reports/elec-summary',
    '/export/excel/reconciliation', '/export/excel/monthly-summary', '/export/pdf/monthly-summary',

    # infra/root/auth
    '/', '/health', '/login', '/forgot-password', '/reset-password', '/logout',
    '/month/open', '/month/transition',
}


def classify_laravel_route(method: str, path: str, handler: str, removed_methods: set[str]) -> str:
    h = handler.lower()
    if "route::view" in h:
        return "Placeholder"
    if "blocked-domain" in h or "protected-shell" in h or "guard-shell" in h:
        return "Placeholder"
    if "fn () =>" in h or "function" in h:
        if "response()->view(" in h or "response()->json(" in h:
            return "Placeholder"
    meth = re.search(r"'([A-Za-z0-9_]+)'\s*\]", handler)
    if meth and meth.group(1) in removed_methods:
        return "Different"
    # explicitly force known removed paths if handler parse misses
    if path in {"/billing/approve", "/billing/adjustments/create", "/billing/adjustments/approve", "/recovery/payment"}:
        return "Different"
    if path in COMPLETE_PATHS:
        return "Complete"
    return "Partial"


def parse_laravel_routes(web_text: str, removed_methods: set[str]) -> list[LaravelRoute]:
    routes: list[LaravelRoute] = []
    lines = web_text.splitlines()

    for i, line in enumerate(lines, start=1):
        m = re.search(r"Route::(get|post|put|delete|patch|view)\(\s*['\"]([^'\"]+)['\"]\s*,\s*(.+)\);", line)
        if not m:
            continue

        kind = m.group(1).upper()
        path = "/" + m.group(2).lstrip("/")
        if path == "//":
            path = "/"

        if kind == "VIEW":
            method = "GET"
            handler = f"Route::view({m.group(3).strip()})"
        else:
            method = kind
            handler = m.group(3).strip()

        status_hint = classify_laravel_route(method, path, handler, removed_methods)
        routes.append(
            LaravelRoute(
                method=method,
                path=path,
                handler=handler,
                line=i,
                status_hint=status_hint,
            )
        )

    return routes


def module_of(path: str) -> str:
    if path == "/":
        return "root"
    bits = [x for x in path.split("/") if x]
    return bits[0] if bits else "root"


def make_register(flask_routes: list[FlaskRoute], laravel_routes: list[LaravelRoute]) -> list[dict]:
    by_method_path = {(r.method, r.path): r for r in laravel_routes}
    by_path = defaultdict(list)
    for r in laravel_routes:
        by_path[r.path].append(r)

    rows = []
    for fr in flask_routes:
        exact = by_method_path.get((fr.method, fr.path))
        same_path = by_path.get(fr.path, [])

        if exact:
            status = exact.status_hint
            note = f"Laravel match at web.php:L{exact.line} -> {exact.handler}"
            laravel_ref = f"{exact.method} {exact.path}"
        elif same_path:
            status = "Partial"
            laravel_ref = "; ".join(f"{x.method} {x.path}" for x in same_path)
            note = "Path exists in Laravel but HTTP method differs"
        else:
            status = "Missing"
            laravel_ref = "-"
            note = "No Laravel route match for method+path"

        rows.append(
            {
                "module": module_of(fr.path),
                "flask_method": fr.method,
                "flask_path": fr.path,
                "flask_function": fr.fn,
                "flask_line": fr.line,
                "laravel_match": laravel_ref,
                "status": status,
                "notes": note,
            }
        )

    return rows


def write_csv(rows: list[dict], path: Path) -> None:
    if not rows:
        return
    with path.open("w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=list(rows[0].keys()))
        w.writeheader()
        w.writerows(rows)


def write_json(rows: list[dict], path: Path) -> dict:
    counts = Counter(r["status"] for r in rows)
    module_counts = Counter(r["module"] for r in rows if r["status"] == "Missing")
    payload = {
        "generated_at": datetime.now().isoformat(timespec="seconds"),
        "source": {
            "flask_app": str(FLASK_APP),
            "laravel_routes": str(LARAVEL_WEB),
        },
        "totals": {
            "flask_routes": len(rows),
            "Complete": counts.get("Complete", 0),
            "Missing": counts.get("Missing", 0),
            "Partial": counts.get("Partial", 0),
            "Placeholder": counts.get("Placeholder", 0),
            "Different": counts.get("Different", 0),
        },
        "top_missing_modules": module_counts.most_common(15),
        "rows": rows,
    }
    path.write_text(json.dumps(payload, indent=2), encoding="utf-8")
    return payload


def write_md(payload: dict, path: Path) -> None:
    t = payload["totals"]
    top_mod = payload["top_missing_modules"]

    lines = [
        "# MBS Live Gap Register (Flask → Laravel)",
        "",
        f"Generated: {payload['generated_at']}",
        "",
        "## Sources",
        f"- Flask: `{payload['source']['flask_app']}`",
        f"- Laravel: `{payload['source']['laravel_routes']}`",
        "",
        "## Route parity counts",
        "",
        "| Metric | Count |",
        "|---|---:|",
        f"| Flask routes scanned | {t['flask_routes']} |",
        f"| Complete | {t['Complete']} |",
        f"| Missing | {t['Missing']} |",
        f"| Partial | {t['Partial']} |",
        f"| Placeholder | {t['Placeholder']} |",
        f"| Different | {t['Different']} |",
        "",
        "## Top missing modules",
        "",
        "| Module | Missing routes |",
        "|---|---:|",
    ]

    if top_mod:
        lines.extend([f"| `{m}` | {c} |" for m, c in top_mod])
    else:
        lines.append("| _none_ | 0 |")

    lines.extend(
        [
            "",
            "## Artifacts",
            "",
            "- `reports/mbs_live_gap_register.csv`",
            "- `reports/mbs_live_gap_register.json`",
            "",
            "## Status semantics",
            "",
            "- **Missing**: No Laravel method+path match",
            "- **Partial**: Route exists but not parity-complete / method differs",
            "- **Placeholder**: Shell/blocked/guard placeholder response",
            "- **Different**: Explicitly divergent behavior (e.g., removed-flow 410)",
        ]
    )

    path.write_text("\n".join(lines) + "\n", encoding="utf-8")


def main() -> int:
    REPORTS_DIR.mkdir(parents=True, exist_ok=True)

    flask_text = load_text(FLASK_APP)
    laravel_text = load_text(LARAVEL_WEB)
    billing_text = load_text(BILLING_CTRL)

    removed_methods = parse_removed_methods(billing_text)
    flask_routes = parse_flask_routes(flask_text)
    laravel_routes = parse_laravel_routes(laravel_text, removed_methods)
    rows = make_register(flask_routes, laravel_routes)

    write_csv(rows, CSV_OUT)
    payload = write_json(rows, JSON_OUT)
    write_md(payload, MD_OUT)

    t = payload["totals"]
    print(f"Wrote: {CSV_OUT}")
    print(f"Wrote: {JSON_OUT}")
    print(f"Wrote: {MD_OUT}")
    print(
        "COUNTS "
        f"routes={t['flask_routes']} "
        f"complete={t['Complete']} "
        f"missing={t['Missing']} "
        f"partial={t['Partial']} "
        f"placeholder={t['Placeholder']} "
        f"different={t['Different']}"
    )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
