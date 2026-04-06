import os
import sqlite3
import tempfile
import unittest
import importlib.util
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
APP_PATH = ROOT / 'api' / 'app.py'


def load_app(db_path: str):
    os.environ['MBS_DB_PATH'] = db_path
    spec = importlib.util.spec_from_file_location('mbs_unified_app3', str(APP_PATH))
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


class TestEmployeeHelper(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.NamedTemporaryFile(suffix='.db', delete=False)
        self.db_path = self.tmp.name
        self.tmp.close()
        self.bootstrap()
        self.mod = load_app(self.db_path)
        self.client = self.mod.app.test_client()

    def tearDown(self):
        try:
            os.unlink(self.db_path)
        except FileNotFoundError:
            pass

    def bootstrap(self):
        con = sqlite3.connect(self.db_path)
        cur = con.cursor()
        cur.executescript('''
            CREATE TABLE util_unit (id INTEGER PRIMARY KEY AUTOINCREMENT, unit_id TEXT UNIQUE, colony_type TEXT, block_name TEXT, room_no TEXT);
            INSERT INTO util_unit(unit_id,colony_type,block_name,room_no) VALUES('U1','Family A','B1','R1');
            CREATE TABLE util_occupancy_monthly (id INTEGER PRIMARY KEY AUTOINCREMENT, month_cycle TEXT, employee_id TEXT, unit_id TEXT);
            CREATE TABLE util_rate_monthly (id INTEGER PRIMARY KEY AUTOINCREMENT, month_cycle TEXT UNIQUE, elec_rate NUMERIC, water_general_rate NUMERIC, water_drinking_rate NUMERIC, school_van_rate NUMERIC);
            CREATE TABLE util_billing_run (id INTEGER PRIMARY KEY AUTOINCREMENT, month_cycle TEXT, run_key TEXT, run_status TEXT, started_by_user_id INTEGER, UNIQUE(month_cycle,run_key));
            CREATE TABLE util_billing_line (id INTEGER PRIMARY KEY AUTOINCREMENT, billing_run_id INTEGER, month_cycle TEXT, employee_id TEXT, utility_type TEXT, qty NUMERIC, rate NUMERIC, amount NUMERIC, source_ref TEXT, UNIQUE(billing_run_id,employee_id,utility_type));
            CREATE TABLE util_formula_result (id INTEGER PRIMARY KEY AUTOINCREMENT, month_cycle TEXT, employee_id TEXT, chargeable_general_water_liters NUMERIC DEFAULT 0, water_general_amount NUMERIC DEFAULT 0, elec_units NUMERIC DEFAULT 0, elec_amount NUMERIC DEFAULT 0, UNIQUE(month_cycle,employee_id));
            CREATE TABLE util_drinking_formula_result (id INTEGER PRIMARY KEY AUTOINCREMENT, month_cycle TEXT, employee_id TEXT, billed_liters NUMERIC DEFAULT 0, rate NUMERIC DEFAULT 0, amount NUMERIC DEFAULT 0, UNIQUE(month_cycle,employee_id));
            CREATE TABLE util_school_van_monthly_charge (id INTEGER PRIMARY KEY AUTOINCREMENT, month_cycle TEXT, employee_id TEXT, amount NUMERIC DEFAULT 0);
            CREATE TABLE util_month_cycle (id INTEGER PRIMARY KEY AUTOINCREMENT, month_cycle TEXT UNIQUE, state TEXT);
        ''')
        con.commit(); con.close()

    def test_add_search_get_and_duplicate(self):
        p = {
            'CompanyID': 'C100', 'Name': 'Bilal', "Father's Name": 'Munir', 'CNIC_No.': '32403-2931025-7',
            'Department': 'Admin', 'Designation': 'Officer', 'Unit_ID': 'U1', 'Active': 'Yes'
        }
        r1 = self.client.post('/employees/upsert', json=p)
        self.assertEqual(r1.status_code, 200)

        s = self.client.get('/employees/search?q=C100').get_json()
        self.assertEqual(len(s['rows']), 1)

        g = self.client.get('/employees/C100').get_json()
        self.assertEqual(g['row']['Name'], 'Bilal')

        # duplicate CompanyID via /employees/add blocked
        r2 = self.client.post('/employees/add', json=p)
        self.assertEqual(r2.status_code, 409)

    def test_change_cnic_blocked(self):
        p = {'CompanyID': 'C101', 'Name': 'A', 'CNIC_No.': '11111-1111111-1', 'Department': 'Admin', 'Designation': 'Officer', 'Unit_ID': 'U1', 'Active': 'Yes'}
        self.client.post('/employees/upsert', json=p)
        p2 = dict(p)
        p2['CNIC_No.'] = '22222-2222222-2'
        r = self.client.post('/employees/upsert', json=p2)
        self.assertEqual(r.status_code, 400)

    def test_invalid_unit_rejected(self):
        p = {'CompanyID': 'C102', 'Name': 'A', 'CNIC_No.': '11111-1111111-1', 'Department': 'Admin', 'Designation': 'Officer', 'Unit_ID': 'BAD', 'Active': 'Yes'}
        r = self.client.post('/employees/upsert', json=p)
        self.assertEqual(r.status_code, 400)

    def test_inactive_rejected_in_occupancy(self):
        p = {'CompanyID': 'C103', 'Name': 'A', 'CNIC_No.': '11111-1111111-1', 'Department': 'Admin', 'Designation': 'Officer', 'Unit_ID': 'U1', 'Active': 'No'}
        self.client.post('/employees/upsert', json=p)
        # need room snapshot record for occupancy rule
        self.client.post('/rooms/upsert', json={'month_cycle':'Jan-2026','unit_id':'U1','category':'Family A','block_floor':'B1','room_no':'R1'})
        r = self.client.post('/occupancy/upsert', json={'month_cycle':'Jan-2026','category':'Family A','block_floor':'B1','room_no':'R1','unit_id':'U1','CompanyID':'C103','active_days':5})
        self.assertEqual(r.status_code, 400)

    def test_bulk_import_preview_and_commit(self):
        headers = [
            'CompanyID','Name',"Father's Name",'CNIC_No.','Mobile_No.','Department','Section','Sub Section','Designation','Employee Type',
            'Colony Type','Block Floor','Room No','Shared Room','Join Date','Leave Date','Active','Iron Cot','Single Bed','Double Bed',
            'Mattress','Sofa Set','Bed Sheet','Wardrobe','Centre Table','Wooden Chair','Dinning Table','Dinning Chair','Side Table','Fridge',
            'Water Dispenser','Washing Machine','Air Cooler','A/C','LED','Gyser','Electric Kettle','Wifi Rtr','Water Bottle','LPG cylinder',
            'Gas Stove','Crockery','Kitchen Cabinet','Mug','Bucket','Mirror','Dustbin','Remarks','Unit_ID'
        ]
        vals = {h: '' for h in headers}
        vals.update({'CompanyID':'C200','Name':'Bulk Emp',"Father's Name":'F','CNIC_No.':'32403-2931025-7','Department':'Admin','Designation':'Officer','Active':'Yes','Unit_ID':'U1'})
        row = [vals[h] for h in headers]
        csv_text = ','.join(headers) + '\n' + ','.join(row)

        p = self.client.post('/employees/import', json={'csv_text': csv_text, 'commit': False})
        self.assertEqual(p.status_code, 200)
        self.assertEqual(p.get_json()['accepted_rows'], 1)

        c = self.client.post('/employees/import', json={'csv_text': csv_text, 'commit': True})
        self.assertEqual(c.status_code, 200)
        self.assertEqual(c.get_json()['inserted'], 1)

        # idempotent upsert
        c2 = self.client.post('/employees/import', json={'csv_text': csv_text, 'commit': True})
        self.assertEqual(c2.status_code, 200)
        self.assertEqual(c2.get_json()['updated'], 1)


if __name__ == '__main__':
    unittest.main()
