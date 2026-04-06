import os
import sqlite3
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DB = ROOT / 'proof' / 'e2e_colony_check.db'
os.environ['MBS_DB_PATH'] = str(DB)

# Import app after env set
import sys
sys.path.insert(0, str(ROOT / 'api'))
import app as appmod

# Ensure fresh db
if DB.exists():
    DB.unlink()

# initialize schemas
appmod.ensure_colony_extras_schema()

con = sqlite3.connect(DB)
cur = con.cursor()

month = '12-2025'

# seed map_room
cur.execute("INSERT INTO map_room(month_cycle, unit_id, company_id) VALUES(?,?,?)", (month, 'U-1', 'C-100'))
cur.execute("INSERT INTO map_room(month_cycle, unit_id, company_id) VALUES(?,?,?)", (month, 'U-1', 'C-200'))

# seed hr_input
cur.execute("INSERT INTO hr_input(month_cycle, company_id, active_days) VALUES(?,?,?)", (month, 'C-100', 10))
cur.execute("INSERT INTO hr_input(month_cycle, company_id, active_days) VALUES(?,?,?)", (month, 'C-200', 20))

# seed readings
cur.execute("""
INSERT INTO readings(month_cycle, meter_id, unit_id, meter_type, usage, amount)
VALUES(?,?,?,?,?,?)
""", (month, 'M-W-1', 'U-1', 'water', 50, 300))
cur.execute("""
INSERT INTO readings(month_cycle, meter_id, unit_id, meter_type, usage, amount)
VALUES(?,?,?,?,?,?)
""", (month, 'M-P-1', 'U-1', 'power', 80, 600))

# seed ro
cur.execute("INSERT INTO ro_drinking(month_cycle, unit_id, liters, amount) VALUES(?,?,?,?)", (month, 'U-1', 100, 100))

con.commit()
con.close()

client = appmod.app.test_client()

out = {}

r1 = client.post('/api/billing/precheck', json={'month_cycle': month})
out['precheck_status_code'] = r1.status_code
out['precheck'] = r1.json

r2 = client.post('/api/billing/finalize', json={'month_cycle': month})
out['finalize_status_code'] = r2.status_code
out['finalize'] = r2.json

r3 = client.get('/api/results/employee-wise', query_string={'month_cycle': month})
out['employee_status_code'] = r3.status_code
out['employee_rows'] = r3.json.get('rows', []) if r3.is_json else []

r4 = client.get('/api/results/unit-wise', query_string={'month_cycle': month})
out['unit_status_code'] = r4.status_code
out['unit_rows'] = r4.json.get('rows', []) if r4.is_json else []

r5 = client.get('/api/logs', query_string={'month_cycle': month})
out['logs_status_code'] = r5.status_code
out['logs_rows'] = r5.json.get('rows', []) if r5.is_json else []

# verify lock behavior
r6 = client.post('/api/billing/finalize', json={'month_cycle': month})
out['second_finalize_status_code'] = r6.status_code
out['second_finalize'] = r6.json

print(json.dumps(out, indent=2))
