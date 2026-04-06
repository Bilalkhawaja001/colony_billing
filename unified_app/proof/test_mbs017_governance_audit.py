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
    spec = importlib.util.spec_from_file_location('mbs_unified_app17', str(APP_PATH))
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


class TestMBS017GovernanceAudit(unittest.TestCase):
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
              state TEXT,
              locked_by_user_id TEXT,
              locked_at DATETIME
            );
            INSERT INTO util_month_cycle(month_cycle, state) VALUES('Mar-2026', 'APPROVAL');
            INSERT INTO util_month_cycle(month_cycle, state) VALUES('Apr-2026', 'LOCKED');

            CREATE TABLE util_billing_run (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT,
              run_key TEXT,
              run_status TEXT,
              started_by_user_id INTEGER,
              approved_by_user_id INTEGER,
              approved_at DATETIME,
              UNIQUE(month_cycle, run_key)
            );

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
            INSERT INTO util_formula_result(month_cycle, employee_id, chargeable_general_water_liters, water_general_amount, elec_units, elec_amount)
            VALUES('Mar-2026', 'E001', 10, 5, 2, 10);

            CREATE TABLE util_drinking_formula_result (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT,
              employee_id TEXT,
              billed_liters NUMERIC DEFAULT 0,
              rate NUMERIC DEFAULT 0,
              amount NUMERIC DEFAULT 0,
              UNIQUE(month_cycle, employee_id)
            );
            INSERT INTO util_drinking_formula_result(month_cycle, employee_id, billed_liters, rate, amount)
            VALUES('Mar-2026', 'E001', 3, 2, 6);

            CREATE TABLE monthly_variable_expenses (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT NOT NULL,
              expense_type TEXT NOT NULL,
              amount NUMERIC(14,2) NOT NULL DEFAULT 0,
              notes TEXT NULL,
              created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              UNIQUE(month_cycle, expense_type)
            );
            INSERT INTO monthly_variable_expenses(month_cycle, expense_type, amount)
            VALUES('Mar-2026', 'SCHOOL_VAN_TOTAL', 100);

            CREATE TABLE util_school_van_student (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              employee_id TEXT,
              child_name TEXT,
              school_name TEXT,
              class_level INTEGER,
              van_status TEXT
            );
            INSERT INTO util_school_van_student(employee_id, child_name, school_name, class_level, van_status)
            VALUES('E001', 'Kid1', 'Noor Acedamy Faisal School', 5, 'ACTIVE');

            CREATE TABLE util_school_van_monthly_charge (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT,
              employee_id TEXT,
              child_name TEXT,
              school_name TEXT,
              class_level INTEGER,
              service_mode TEXT,
              rate NUMERIC,
              amount NUMERIC,
              charged_flag INTEGER
            );

            CREATE TABLE util_rate_monthly (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              month_cycle TEXT NOT NULL UNIQUE,
              elec_rate NUMERIC(12,4) NOT NULL,
              water_general_rate NUMERIC(12,4) NOT NULL DEFAULT 0,
              water_drinking_rate NUMERIC(12,4) NOT NULL DEFAULT 0,
              school_van_rate NUMERIC(12,2) NOT NULL DEFAULT 0,
              approved_by_user_id INTEGER,
              approved_at DATETIME
            );
        ''')
        con.commit()
        con.close()

    def test_governance_and_audit_controls(self):
        c = self.client

        # non-admin monthly rates upsert blocked
        r_non_admin = c.post('/monthly-rates/config/upsert', json={
            'actor_user_id': '9',
            'month_cycle': 'Mar-2026',
            'elec_rate': 1,
            'water_general_rate': 1,
            'water_drinking_rate': 1,
            'school_van_rate': 1,
        })
        self.assertEqual(r_non_admin.status_code, 403)

        # locked month mutation blocked
        r_locked = c.post('/monthly-rates/config/upsert', json={
            'actor_user_id': '1',
            'month_cycle': 'Apr-2026',
            'elec_rate': 1,
            'water_general_rate': 1,
            'water_drinking_rate': 1,
            'school_van_rate': 1,
        })
        self.assertEqual(r_locked.status_code, 409)

        # locked month billing run blocked
        r_run_locked = c.post('/billing/run', json={'month_cycle': 'Apr-2026', 'run_key': 'LOCKED-RUN', 'actor_user_id': 1})
        self.assertEqual(r_run_locked.status_code, 409)

        # open/approval month rates upsert allowed + audited
        r_rates_ok = c.post('/monthly-rates/config/upsert', json={
            'actor_user_id': '1',
            'month_cycle': 'Mar-2026',
            'elec_rate': 2.5,
            'water_general_rate': 0.5,
            'water_drinking_rate': 2,
            'school_van_rate': 100,
        })
        self.assertEqual(r_rates_ok.status_code, 200)

        # month transition audited
        r_transition = c.post('/month/transition', json={
            'actor_user_id': '1',
            'month_cycle': 'Mar-2026',
            'to_state': 'APPROVAL'
        })
        self.assertEqual(r_transition.status_code, 200)

        # billing run/approve/lock audited
        r_run = c.post('/billing/run', json={'month_cycle': 'Mar-2026', 'run_key': 'RUN-1', 'actor_user_id': 1})
        self.assertEqual(r_run.status_code, 200)
        run_id = r_run.get_json()['run_id']

        r_approve = c.post('/billing/approve', json={'run_id': run_id, 'actor_user_id': 2})
        self.assertEqual(r_approve.status_code, 200)

        r_lock = c.post('/billing/lock', json={'run_id': run_id, 'actor_user_id': 2})
        self.assertEqual(r_lock.status_code, 200)

        audits = self.mod.q('SELECT entity_type, action FROM util_audit_log ORDER BY id')
        got = {(a['entity_type'], a['action']) for a in audits}

        self.assertIn(('monthly_rates', 'UPSERT'), got)
        self.assertIn(('month_cycle', 'TRANSITION'), got)
        self.assertIn(('billing_run', 'RUN_REBUILT'), got)
        self.assertIn(('billing_run', 'APPROVE'), got)
        self.assertIn(('billing_run', 'LOCK'), got)


if __name__ == '__main__':
    unittest.main()
