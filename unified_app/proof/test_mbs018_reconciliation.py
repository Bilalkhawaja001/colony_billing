import os
import sqlite3
import tempfile
import unittest
import importlib.util
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
APP_PATH = ROOT / 'api' / 'app.py'


def load_app(db_path: str):
    os.environ['MBS_DB_PATH'] = db_path
    os.environ['MBS_ADMIN_USER_IDS'] = '1,2'
    api_dir = str(ROOT / 'api')
    if api_dir not in sys.path:
        sys.path.insert(0, api_dir)
    spec = importlib.util.spec_from_file_location('mbs_unified_app18', str(APP_PATH))
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


class TestMBS018Reconciliation(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.NamedTemporaryFile(suffix='.db', delete=False)
        self.db_path = self.tmp.name
        self.tmp.close()
        self.bootstrap(self.db_path)
        self.mod = load_app(self.db_path)
        self.client = self.mod.app.test_client()

    def tearDown(self):
        try:
            os.unlink(self.db_path)
        except FileNotFoundError:
            pass

    def bootstrap(self, db_path):
        con = sqlite3.connect(db_path)
        cur = con.cursor()
        cur.executescript('''
            CREATE TABLE util_month_cycle (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT UNIQUE,
              state TEXT
            );
            INSERT INTO util_month_cycle(month_cycle, state) VALUES('Mar-2026', 'APPROVAL');

            CREATE TABLE util_billing_run (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT,
              run_key TEXT,
              run_status TEXT,
              started_by_user_id INTEGER,
              UNIQUE(month_cycle, run_key)
            );
            INSERT INTO util_billing_run(month_cycle, run_key, run_status, started_by_user_id)
            VALUES('Mar-2026', 'RUN-1', 'LOCKED', 1);

            CREATE TABLE util_billing_line (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              billing_run_id INTEGER,
              month_cycle TEXT,
              employee_id TEXT,
              utility_type TEXT,
              qty NUMERIC,
              rate NUMERIC,
              amount NUMERIC,
              source_ref TEXT,
              UNIQUE(billing_run_id, employee_id, utility_type)
            );
            INSERT INTO util_billing_line(billing_run_id, month_cycle, employee_id, utility_type, qty, rate, amount, source_ref)
            VALUES
            (1, 'Mar-2026', 'E001', 'ELEC', 10, 2, 20, 'x1'),
            (1, 'Mar-2026', 'E002', 'WATER_GENERAL', 5, 3, 15, 'x2');

            CREATE TABLE util_rate_monthly (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT NOT NULL UNIQUE,
              elec_rate NUMERIC(12,4) NOT NULL,
              water_general_rate NUMERIC(12,4) NOT NULL DEFAULT 0,
              water_drinking_rate NUMERIC(12,4) NOT NULL DEFAULT 0,
              school_van_rate NUMERIC(12,2) NOT NULL DEFAULT 0
            );
            CREATE TABLE util_formula_result (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT,
              employee_id TEXT,
              chargeable_general_water_liters NUMERIC DEFAULT 0,
              water_general_amount NUMERIC DEFAULT 0,
              elec_units NUMERIC DEFAULT 0,
              elec_amount NUMERIC DEFAULT 0,
              UNIQUE(month_cycle, employee_id)
            );
            CREATE TABLE util_drinking_formula_result (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT,
              employee_id TEXT,
              billed_liters NUMERIC DEFAULT 0,
              rate NUMERIC DEFAULT 0,
              amount NUMERIC DEFAULT 0,
              UNIQUE(month_cycle, employee_id)
            );
            CREATE TABLE util_school_van_monthly_charge (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT,
              employee_id TEXT,
              amount NUMERIC DEFAULT 0
            );
        ''')
        con.commit()
        con.close()

    def test_reconciliation_without_recovery_table_defaults_to_zero_recovered(self):
        r = self.client.get('/reports/reconciliation', query_string={'month_cycle': 'Mar-2026'})
        self.assertEqual(r.status_code, 200)
        j = r.get_json()
        self.assertEqual(j['summary']['billed_total'], 35.0)
        self.assertEqual(j['summary']['recovered_total'], 0.0)
        self.assertEqual(j['summary']['outstanding_total'], 35.0)
        self.assertEqual(len(j['by_employee']), 2)


if __name__ == '__main__':
    unittest.main()
