import json, os, sqlite3, sys
from copy import deepcopy

ROOT = r"C:\Users\Bilal\clawd\_tmp_colony_billing_patch"
FLASK_API = r"C:\Users\Bilal\clawd\mbs_project\unified_app\api"
sys.path.insert(0, FLASK_API)

from electric_v1.orchestration_service import ElectricBillingV1OrchestrationService
from electric_v1.read_service import ElectricBillingV1ReadService

FIX_ROOT = os.path.join(ROOT, "tests", "Fixtures", "electric_v1")


def ensure_schema(con):
    cur = con.cursor()
    cur.executescript("""
    CREATE TABLE IF NOT EXISTS electric_v1_master_employee (
      company_id TEXT, name TEXT, isresident INTEGER, isactive INTEGER,
      join_date TEXT, leave_date TEXT, updated_at TEXT
    );
    CREATE TABLE IF NOT EXISTS electric_v1_allowance (
      unit_id TEXT, free_electric REAL, unit_name TEXT, residence_type TEXT, updated_at TEXT
    );
    CREATE TABLE IF NOT EXISTS electric_v1_readings (
      cycle_start_date TEXT, cycle_end_date TEXT, unit_id TEXT,
      previous_reading REAL, current_reading REAL, reading_status TEXT, updated_at TEXT
    );
    CREATE TABLE IF NOT EXISTS electric_v1_hr_attendance (
      cycle_start_date TEXT, cycle_end_date TEXT, company_id TEXT, attendance_days REAL, updated_at TEXT
    );
    CREATE TABLE IF NOT EXISTS electric_v1_occupancy (
      company_id TEXT, unit_id TEXT, room_id TEXT, from_date TEXT, to_date TEXT, updated_at TEXT
    );
    CREATE TABLE IF NOT EXISTS electric_v1_manual_adjustments (
      cycle_start_date TEXT, cycle_end_date TEXT, company_id TEXT, unit_id TEXT, adjustment_units REAL, reason TEXT, updated_at TEXT
    );
    CREATE TABLE IF NOT EXISTS electric_v1_output_employee_final (
      cycle_start_date TEXT, cycle_end_date TEXT, run_id TEXT, company_id TEXT, name TEXT,
      total_net_billable_units REAL, flat_rate REAL, final_amount_before_rounding REAL,
      final_amount_rounded REAL, has_estimated_units TEXT
    );
    CREATE TABLE IF NOT EXISTS electric_v1_output_employee_unit_drilldown (
      cycle_start_date TEXT, cycle_end_date TEXT, run_id TEXT, company_id TEXT, unit_id TEXT, residence_type TEXT,
      employee_attendance_in_unit REAL, gross_units REAL, free_allowance_units REAL,
      net_units_before_adj REAL, adjustment_units REAL, net_units_after_adj REAL,
      amount_before_rounding REAL, is_estimated TEXT,
      estimate_source_cycle1 TEXT, estimate_source_cycle2 TEXT, estimate_source_cycle3 TEXT,
      estimated_from_valid_cycle_count INTEGER
    );
    CREATE TABLE IF NOT EXISTS electric_v1_exception_log (
      run_id TEXT, logged_at TEXT, severity TEXT, exception_code TEXT, message TEXT,
      company_id TEXT, unit_id TEXT, room_id TEXT, cycle_start_date TEXT, cycle_end_date TEXT
    );
    CREATE TABLE IF NOT EXISTS electric_v1_run_history (
      run_id TEXT, run_start TEXT, run_end TEXT, cycle_start_date TEXT, cycle_end_date TEXT,
      status TEXT, processed_count INTEGER, skipped_count INTEGER, exception_count INTEGER
    );
    """)
    con.commit()


def insert_rows(con, table, rows):
    if not rows:
        return
    keys = list(rows[0].keys())
    cols = ",".join(keys)
    qs = ",".join(["?"] * len(keys))
    vals = [[r.get(k) for k in keys] for r in rows]
    con.executemany(f"INSERT INTO {table} ({cols}) VALUES ({qs})", vals)


def write_json(path, obj):
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, "w", encoding="utf-8") as f:
        json.dump(obj, f, indent=2, ensure_ascii=False)


def normalize_rows(rows):
    out = []
    for r in rows:
        rr = dict(r)
        rr.pop("run_id", None)
        rr.pop("logged_at", None)
        rr.pop("run_start", None)
        rr.pop("run_end", None)
        for k, v in list(rr.items()):
            if isinstance(v, float):
                rr[k] = round(v, 4)
        out.append(rr)
    out.sort(key=lambda x: json.dumps(x, sort_keys=True))
    return out


BASE = {
    "run": {"cycle_start": "2026-03-01", "cycle_end": "2026-03-31", "flat_rate": 2.5},
    "allowance": [{"unit_id": "U1", "free_electric": 20, "unit_name": "Unit 1", "residence_type": "ROOM", "updated_at": "2026-03-01T00:00:00"}],
    "readings": [{"cycle_start_date": "2026-03-01", "cycle_end_date": "2026-03-31", "unit_id": "U1", "previous_reading": 100, "current_reading": 140, "reading_status": "NORMAL", "updated_at": "2026-03-31T23:59:00"}],
    "attendance": [{"cycle_start_date": "2026-03-01", "cycle_end_date": "2026-03-31", "company_id": "E1", "attendance_days": 20, "updated_at": "2026-03-31T23:59:00"}],
    "occupancy": [{"company_id": "E1", "unit_id": "U1", "room_id": "R1", "from_date": "2026-03-01", "to_date": "2026-03-31", "updated_at": "2026-03-31T23:59:00"}],
    "adjustments": [{"cycle_start_date": "2026-03-01", "cycle_end_date": "2026-03-31", "company_id": "E1", "unit_id": "U1", "adjustment_units": 0, "updated_at": "2026-03-31T23:59:00"}],
}


def build_cases():
    cases = {}

    c = deepcopy(BASE)
    cases["case_01_happy_path_room_split"] = c

    c = deepcopy(BASE)
    c["readings"][0]["previous_reading"] = 140
    c["readings"][0]["current_reading"] = 100
    cases["case_02_reverse_read_reject"] = c

    c = deepcopy(BASE)
    c["readings"][0]["reading_status"] = "FAULTY"
    c["history"] = [
        {"cycle_start_date": "2025-12-01", "cycle_end_date": "2025-12-31", "unit_id": "U1", "previous_reading": 50, "current_reading": 70, "reading_status": "NORMAL", "updated_at": "2025-12-31"},
        {"cycle_start_date": "2026-01-01", "cycle_end_date": "2026-01-31", "unit_id": "U1", "previous_reading": 70, "current_reading": 95, "reading_status": "NORMAL", "updated_at": "2026-01-31"},
        {"cycle_start_date": "2026-02-01", "cycle_end_date": "2026-02-28", "unit_id": "U1", "previous_reading": 95, "current_reading": 125, "reading_status": "NORMAL", "updated_at": "2026-02-28"},
    ]
    cases["case_03_faulty_read_estimate_3_valid_cycles"] = c

    c = deepcopy(BASE)
    c["readings"] = []
    c["history"] = []
    cases["case_04_missing_read_estimate_insufficient_history"] = c

    c = deepcopy(BASE)
    c["allowance"][0]["residence_type"] = "HOUSE"
    c["occupancy"] = [
        {"company_id": "E1", "unit_id": "U1", "room_id": "R1", "from_date": "2026-03-01", "to_date": "2026-03-31", "updated_at": "2026-03-31"},
        {"company_id": "E2", "unit_id": "U1", "room_id": "R1", "from_date": "2026-03-01", "to_date": "2026-03-31", "updated_at": "2026-03-31"},
    ]
    c["attendance"].append({"cycle_start_date": "2026-03-01", "cycle_end_date": "2026-03-31", "company_id": "E2", "attendance_days": 20, "updated_at": "2026-03-31"})
    cases["case_05_house_not_single_responsible"] = c

    c = deepcopy(BASE)
    c["attendance"][0]["attendance_days"] = 0
    cases["case_06_room_consumption_zero_attendance_skip"] = c

    c = deepcopy(BASE)
    c["adjustments"][0]["adjustment_units"] = -999
    cases["case_07_adjustment_negative_floor_to_zero"] = c

    c = deepcopy(BASE)
    c["run"]["flat_rate"] = 2.51
    c["readings"][0]["current_reading"] = 120.12
    c["allowance"][0]["free_electric"] = 0
    cases["case_08_rounding_boundaries_050_051"] = c

    c = deepcopy(BASE)
    c["allowance"].append({"unit_id": "U1", "free_electric": 30, "unit_name": "Unit 1", "residence_type": "ROOM", "updated_at": "2026-03-31T23:59:59"})
    cases["case_09_duplicate_allowance_key"] = c

    c = deepcopy(BASE)
    c["readings"].append({"cycle_start_date": "2026-03-01", "cycle_end_date": "2026-03-31", "unit_id": "U1", "previous_reading": 100, "current_reading": 145, "reading_status": "NORMAL", "updated_at": "2026-03-31T23:59:59"})
    cases["case_10_duplicate_reading_key_same_cycle"] = c

    c = deepcopy(BASE)
    c["allowance"] = [
        {"unit_id": "U1", "free_electric": 20, "unit_name": "Unit 1", "residence_type": "ROOM", "updated_at": "2026-03-01"},
        {"unit_id": "U2", "free_electric": 10, "unit_name": "Unit 2", "residence_type": "ROOM", "updated_at": "2026-03-01"},
    ]
    c["readings"] = [
        {"cycle_start_date": "2026-03-01", "cycle_end_date": "2026-03-31", "unit_id": "U1", "previous_reading": 100, "current_reading": 140, "reading_status": "NORMAL", "updated_at": "2026-03-31"},
        {"cycle_start_date": "2026-03-01", "cycle_end_date": "2026-03-31", "unit_id": "U2", "previous_reading": 50, "current_reading": 90, "reading_status": "NORMAL", "updated_at": "2026-03-31"},
    ]
    c["attendance"] = [
        {"cycle_start_date": "2026-03-01", "cycle_end_date": "2026-03-31", "company_id": "E1", "attendance_days": 20, "updated_at": "2026-03-31"},
        {"cycle_start_date": "2026-03-01", "cycle_end_date": "2026-03-31", "company_id": "E2", "attendance_days": 10, "updated_at": "2026-03-31"},
    ]
    c["occupancy"] = [
        {"company_id": "E1", "unit_id": "U1", "room_id": "R1", "from_date": "2026-03-01", "to_date": "2026-03-20", "updated_at": "2026-03-31"},
        {"company_id": "E1", "unit_id": "U2", "room_id": "R2", "from_date": "2026-03-21", "to_date": "2026-03-31", "updated_at": "2026-03-31"},
        {"company_id": "E2", "unit_id": "U1", "room_id": "R1", "from_date": "2026-03-01", "to_date": "2026-03-31", "updated_at": "2026-03-31"},
    ]
    c["adjustments"] = [
        {"cycle_start_date": "2026-03-01", "cycle_end_date": "2026-03-31", "company_id": "E1", "unit_id": "U1", "adjustment_units": 1, "updated_at": "2026-03-31"},
        {"cycle_start_date": "2026-03-01", "cycle_end_date": "2026-03-31", "company_id": "E2", "unit_id": "U1", "adjustment_units": -0.5, "updated_at": "2026-03-31"},
    ]
    cases["case_11_multi_unit_multi_room_overlap"] = c

    c = deepcopy(BASE)
    cases["case_12_rerun_same_cycle_replace_only"] = c

    return cases


def derive_master_rows(case):
    cids = set()
    for r in case.get("attendance", []):
        cids.add(r["company_id"])
    for r in case.get("occupancy", []):
        cids.add(r["company_id"])
    return [{
        "company_id": cid,
        "name": cid,
        "isresident": 1,
        "isactive": 1,
        "join_date": "2020-01-01",
        "leave_date": None,
        "updated_at": "2026-03-01",
    } for cid in sorted(cids)]


def main():
    cases = build_cases()
    for name, case in cases.items():
        con = sqlite3.connect(":memory:")
        con.row_factory = sqlite3.Row
        ensure_schema(con)

        insert_rows(con, "electric_v1_master_employee", derive_master_rows(case))
        insert_rows(con, "electric_v1_allowance", case["allowance"])
        insert_rows(con, "electric_v1_readings", case.get("history", []) + case["readings"])
        insert_rows(con, "electric_v1_hr_attendance", case["attendance"])
        insert_rows(con, "electric_v1_occupancy", case["occupancy"])
        adj_rows = []
        for a in case["adjustments"]:
            x = dict(a)
            x.setdefault("reason", "fixture")
            adj_rows.append(x)
        insert_rows(con, "electric_v1_manual_adjustments", adj_rows)
        con.commit()

        from datetime import date
        cs = date.fromisoformat(case["run"]["cycle_start"])
        ce = date.fromisoformat(case["run"]["cycle_end"])

        svc = ElectricBillingV1OrchestrationService(con)
        summary = svc.run(cs, ce, case["run"]["flat_rate"])

        rd = ElectricBillingV1ReadService(con)
        bundle = rd.get_bundle(cs, ce, run_id=summary.run_id)

        case_root = os.path.join(FIX_ROOT, name)
        in_root = os.path.join(case_root, "inputs")
        ex_root = os.path.join(case_root, "expected")

        for k in ["allowance", "readings", "attendance", "occupancy", "adjustments", "run"]:
            write_json(os.path.join(in_root, f"{k}.json"), case[k])

        write_json(os.path.join(ex_root, "final_outputs.json"), normalize_rows(bundle.final_outputs))
        write_json(os.path.join(ex_root, "drilldown_outputs.json"), normalize_rows(bundle.drilldown_outputs))
        write_json(os.path.join(ex_root, "exceptions.json"), normalize_rows(bundle.exceptions))
        write_json(os.path.join(ex_root, "run_summary.json"), {
            "status": summary.status,
            "processed_count": summary.processed_count,
            "skipped_count": summary.skipped_count,
            "exception_count": summary.exception_count,
            "final_output_rows": summary.final_output_rows,
            "drilldown_rows": summary.drilldown_rows,
        })
        write_json(os.path.join(case_root, "source_of_truth.json"), {
            "source_type": "flask",
            "source_file": "mbs_project/unified_app/api/electric_v1/orchestration_service.py",
            "source_hash": "local-derived",
        })

        con.close()
        print("built", name)


if __name__ == "__main__":
    main()
