from flask import Flask, request, jsonify, send_file, render_template
import sqlite3, os, uuid, csv, re, json
from datetime import datetime
from io import StringIO, BytesIO
from reportlab.lib.pagesizes import A4
from reportlab.pdfgen import canvas

try:
    from domain.billing_engine import build_line, deterministic_fingerprint
except Exception:
    build_line = None
    deterministic_fingerprint = None

DB_PATH = os.environ.get('MBS_DB_PATH', r'C:\Users\Bilal\clawd\mbs_project\MBS-002_clean\proof\mbs010_apply_test.db')

BASE_DIR = os.path.dirname(os.path.dirname(__file__))
app = Flask(__name__, template_folder=os.path.join(BASE_DIR, 'templates'), static_folder=os.path.join(BASE_DIR, 'static'))

UNIT_ID_ALIASES = ['Unit_ID', 'Unit ID', 'UnitID', 'unit_id', 'unit id', 'unitid']
HEADER_ALIAS_MAP = {
    'unit_id': UNIT_ID_ALIASES,
    'month_cycle': ['Month_Cycle', 'Month Cycle', 'month_cycle', 'month cycle'],
    'meter_id': ['Meter_ID', 'Meter ID', 'meter_id', 'meter id'],
    'meter_type': ['Meter_Type', 'Meter Type', 'meter_type', 'meter type'],
    'previous_reading': ['Previous_Reading', 'Previous Reading', 'previous_reading', 'previous reading'],
    'current_reading': ['Current_Reading', 'Current Reading', 'current_reading', 'current reading'],
}

FREE_UNITS_PER_ROOM = {
    'Family A+': 600,
    'Family A': 300,
    'Family B': 250,
    'Family C': 200,
    'Hostel': 200,
    'Container': 200,
    'Bachelor': 150,
}

EMPLOYEE_MASTER_REQUIRED_COLUMNS = [
    'CompanyID', 'Name', "Father's Name", 'CNIC_No.', 'Mobile_No.', 'Department', 'Section', 'Sub Section',
    'Designation', 'Employee Type', 'Colony Type', 'Block Floor', 'Room No', 'Shared Room',
    'Join Date', 'Leave Date', 'Active', 'Iron Cot', 'Single Bed', 'Double Bed', 'Mattress', 'Sofa Set',
    'Bed Sheet', 'Wardrobe', 'Centre Table', 'Wooden Chair', 'Dinning Table', 'Dinning Chair', 'Side Table',
    'Fridge', 'Water Dispenser', 'Washing Machine', 'Air Cooler', 'A/C', 'LED', 'Gyser', 'Electric Kettle',
    'Wifi Rtr', 'Water Bottle', 'LPG cylinder', 'Gas Stove', 'Crockery', 'Kitchen Cabinet', 'Mug', 'Bucket',
    'Mirror', 'Dustbin', 'Remarks', 'Unit_ID'
]

# Standardized CSV headers (single style, underscore-based)
EMPLOYEE_CSV_HEADERS = [
    'CompanyID', 'Name', 'Fathers_Name', 'CNIC_No', 'Mobile_No', 'Department', 'Section', 'Sub_Section',
    'Designation', 'Employee_Type', 'Colony_Type', 'Block_Floor', 'Room_No', 'Shared_Room',
    'Join_Date', 'Leave_Date', 'Active', 'Iron_Cot', 'Single_Bed', 'Double_Bed', 'Mattress', 'Sofa_Set',
    'Bed_Sheet', 'Wardrobe', 'Centre_Table', 'Wooden_Chair', 'Dinning_Table', 'Dinning_Chair', 'Side_Table',
    'Fridge', 'Water_Dispenser', 'Washing_Machine', 'Air_Cooler', 'AC', 'LED', 'Gyser', 'Electric_Kettle',
    'Wifi_Rtr', 'Water_Bottle', 'LPG_cylinder', 'Gas_Stove', 'Crockery', 'Kitchen_Cabinet', 'Mug', 'Bucket',
    'Mirror', 'Dustbin', 'Remarks', 'Unit_ID'
]

# CSV -> DB column mapping
EMPLOYEE_CSV_TO_DB = {
    'CompanyID': 'CompanyID',
    'Name': 'Name',
    'Fathers_Name': "Father's Name",
    'CNIC_No': 'CNIC_No.',
    'Mobile_No': 'Mobile_No.',
    'Department': 'Department',
    'Section': 'Section',
    'Sub_Section': 'Sub Section',
    'Designation': 'Designation',
    'Employee_Type': 'Employee Type',
    'Colony_Type': 'Colony Type',
    'Block_Floor': 'Block Floor',
    'Room_No': 'Room No',
    'Shared_Room': 'Shared Room',
    'Join_Date': 'Join Date',
    'Leave_Date': 'Leave Date',
    'Active': 'Active',
    'Iron_Cot': 'Iron Cot',
    'Single_Bed': 'Single Bed',
    'Double_Bed': 'Double Bed',
    'Mattress': 'Mattress',
    'Sofa_Set': 'Sofa Set',
    'Bed_Sheet': 'Bed Sheet',
    'Wardrobe': 'Wardrobe',
    'Centre_Table': 'Centre Table',
    'Wooden_Chair': 'Wooden Chair',
    'Dinning_Table': 'Dinning Table',
    'Dinning_Chair': 'Dinning Chair',
    'Side_Table': 'Side Table',
    'Fridge': 'Fridge',
    'Water_Dispenser': 'Water Dispenser',
    'Washing_Machine': 'Washing Machine',
    'Air_Cooler': 'Air Cooler',
    'AC': 'A/C',
    'LED': 'LED',
    'Gyser': 'Gyser',
    'Electric_Kettle': 'Electric Kettle',
    'Wifi_Rtr': 'Wifi Rtr',
    'Water_Bottle': 'Water Bottle',
    'LPG_cylinder': 'LPG cylinder',
    'Gas_Stove': 'Gas Stove',
    'Crockery': 'Crockery',
    'Kitchen_Cabinet': 'Kitchen Cabinet',
    'Mug': 'Mug',
    'Bucket': 'Bucket',
    'Mirror': 'Mirror',
    'Dustbin': 'Dustbin',
    'Remarks': 'Remarks',
    'Unit_ID': 'Unit_ID',
}

ERROR_REPORT_CACHE = {}


def json_dumps_safe(d):
    try:
        return json.dumps(d, ensure_ascii=False)
    except Exception:
        return '{}'


def get_con():
    con = sqlite3.connect(DB_PATH)
    con.row_factory = sqlite3.Row
    return con


def q(sql, params=(), one=False):
    con = get_con()
    cur = con.cursor()
    cur.execute(sql, params)
    rows = cur.fetchall()
    con.commit()
    con.close()
    if one:
        return dict(rows[0]) if rows else None
    return [dict(r) for r in rows]


def exec_txn(fn):
    con = get_con()
    try:
        out = fn(con)
        con.commit()
        return out
    finally:
        con.close()


def _admin_user_ids():
    raw = (os.environ.get('MBS_ADMIN_USER_IDS') or '1').strip()
    ids = {x.strip() for x in raw.split(',') if x.strip()}
    return ids or {'1'}


def require_admin_from_request(payload=None):
    payload = payload or {}
    actor = str(
        payload.get('actor_user_id')
        or request.headers.get('X-Actor-User-Id')
        or request.args.get('actor_user_id')
        or ''
    ).strip()
    if not actor:
        return None, (jsonify({'status': 'error', 'error': 'actor_user_id is required (admin only)'}), 401)
    if actor not in _admin_user_ids():
        return None, (jsonify({'status': 'error', 'error': 'admin only'}), 403)
    return actor, None


def ensure_audit_schema():
    con = get_con()
    cur = con.cursor()
    cur.execute('''CREATE TABLE IF NOT EXISTS util_audit_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        entity_type TEXT NOT NULL,
        entity_id TEXT NOT NULL,
        action TEXT NOT NULL,
        actor_user_id TEXT NOT NULL,
        before_json TEXT NULL,
        after_json TEXT NULL,
        correlation_id TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )''')
    cur.execute('''CREATE INDEX IF NOT EXISTS idx_util_audit_entity ON util_audit_log(entity_type, entity_id, created_at)''')
    con.commit()
    con.close()


def audit_log(entity_type, entity_id, action, actor_user_id, before_obj=None, after_obj=None, correlation_id=None):
    q('''INSERT INTO util_audit_log(entity_type,entity_id,action,actor_user_id,before_json,after_json,correlation_id)
         VALUES(?,?,?,?,?,?,?)''', (
        entity_type,
        str(entity_id),
        action,
        str(actor_user_id),
        json_dumps_safe(before_obj or {}),
        json_dumps_safe(after_obj or {}),
        correlation_id
    ))


def ensure_adjustment_schema():
    con = get_con()
    cur = con.cursor()
    cur.execute('''CREATE TABLE IF NOT EXISTS util_billing_adjustment (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        month_cycle TEXT NOT NULL,
        employee_id TEXT NOT NULL,
        utility_type TEXT NOT NULL,
        amount_delta NUMERIC(14,2) NOT NULL,
        reason TEXT NOT NULL,
        created_by_user_id TEXT NOT NULL,
        approved_by_user_id TEXT NULL,
        status TEXT NOT NULL DEFAULT 'PENDING',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        approved_at DATETIME NULL
    )''')
    cur.execute('''CREATE INDEX IF NOT EXISTS idx_billing_adj_month_emp ON util_billing_adjustment(month_cycle, employee_id, utility_type)''')
    con.commit()
    con.close()


def ensure_recovery_schema():
    con = get_con()
    cur = con.cursor()
    cur.execute('''CREATE TABLE IF NOT EXISTS util_recovery_payment (
        payment_id INTEGER PRIMARY KEY AUTOINCREMENT,
        employee_id TEXT NOT NULL,
        month_cycle TEXT NOT NULL,
        amount_paid NUMERIC(14,2) NOT NULL,
        payment_date TEXT NOT NULL,
        payment_method TEXT NULL,
        reference_no TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )''')
    cur.execute('''CREATE INDEX IF NOT EXISTS idx_recovery_month_emp
                   ON util_recovery_payment(month_cycle, employee_id)''')
    con.commit()
    con.close()


def month_state(month_cycle: str):
    row = q('SELECT state FROM util_month_cycle WHERE month_cycle=?', (month_cycle,), one=True)
    return (row or {}).get('state')


def reject_if_month_locked(month_cycle: str):
    st = month_state(month_cycle)
    if st == 'LOCKED':
        return jsonify({'status': 'error', 'error': f'month_cycle {month_cycle} is LOCKED; post lock edits are blocked'}), 409
    return None


def ensure_occupancy_schema():
    ddl = [
        '''CREATE TABLE IF NOT EXISTS util_unit_room_snapshot (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL,
            unit_id TEXT NOT NULL,
            category TEXT NOT NULL,
            block_floor TEXT NULL,
            room_no TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(month_cycle, unit_id, room_no)
        )''',
        '''CREATE INDEX IF NOT EXISTS idx_util_room_snapshot_month_unit
           ON util_unit_room_snapshot(month_cycle, unit_id)''',
        '''CREATE TABLE IF NOT EXISTS util_occupancy_monthly (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL,
            category TEXT NOT NULL,
            block_floor TEXT NULL,
            room_no TEXT NOT NULL,
            unit_id TEXT NOT NULL,
            employee_id TEXT NOT NULL,
            active_days INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(month_cycle, employee_id)
        )''',
        '''CREATE INDEX IF NOT EXISTS idx_util_occupancy_month_unit
           ON util_occupancy_monthly(month_cycle, unit_id)''',
        '''CREATE TABLE IF NOT EXISTS util_meter_unit_monthly (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL,
            unit_id TEXT NOT NULL,
            meter_units NUMERIC(14,4) NOT NULL DEFAULT 0,
            source_ref TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(month_cycle, unit_id)
        )''',
        '''CREATE TABLE IF NOT EXISTS util_meter_reading_monthly (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL,
            unit_id TEXT NOT NULL,
            previous_reading NUMERIC(14,4) NOT NULL DEFAULT 0,
            current_reading NUMERIC(14,4) NOT NULL DEFAULT 0,
            rollover_flag INTEGER NOT NULL DEFAULT 0,
            source_ref TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(month_cycle, unit_id)
        )''',
        '''CREATE INDEX IF NOT EXISTS idx_util_meter_unit_month
           ON util_meter_unit_monthly(month_cycle, unit_id)''',
        '''CREATE TABLE IF NOT EXISTS util_elec_unit_monthly_result (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL,
            unit_id TEXT NOT NULL,
            category TEXT NOT NULL,
            usage_units NUMERIC(14,4) NOT NULL DEFAULT 0,
            rooms_count INTEGER NOT NULL DEFAULT 0,
            free_per_room NUMERIC(14,4) NOT NULL DEFAULT 0,
            unit_free_units NUMERIC(14,4) NOT NULL DEFAULT 0,
            net_units NUMERIC(14,4) NOT NULL DEFAULT 0,
            elec_rate NUMERIC(14,4) NOT NULL DEFAULT 0,
            unit_amount NUMERIC(14,2) NOT NULL DEFAULT 0,
            total_attendance NUMERIC(14,4) NOT NULL DEFAULT 0,
            computed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(month_cycle, unit_id)
        )''',
        '''CREATE TABLE IF NOT EXISTS util_elec_employee_share_monthly (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL,
            unit_id TEXT NOT NULL,
            employee_id TEXT NOT NULL,
            attendance NUMERIC(14,4) NOT NULL DEFAULT 0,
            share_amount NUMERIC(14,2) NOT NULL DEFAULT 0,
            share_units NUMERIC(14,4) NOT NULL DEFAULT 0,
            allocation_method TEXT NOT NULL DEFAULT 'attendance_split',
            computed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(month_cycle, employee_id)
        )''',
        '''CREATE INDEX IF NOT EXISTS idx_util_elec_share_month_unit
           ON util_elec_employee_share_monthly(month_cycle, unit_id)''',
        '''CREATE TABLE IF NOT EXISTS monthly_variable_expenses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL,
            expense_type TEXT NOT NULL,
            amount NUMERIC(14,2) NOT NULL DEFAULT 0,
            notes TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(month_cycle, expense_type)
        )'''
    ]

    con = get_con()
    cur = con.cursor()
    for s in ddl:
        cur.execute(s)

    # Backward-compatible migration
    try:
        cur.execute("ALTER TABLE util_elec_employee_share_monthly ADD COLUMN allocation_method TEXT NOT NULL DEFAULT 'attendance_split'")
    except Exception:
        pass

    con.commit()
    con.close()


def ensure_monthly_rates_schema():
    ddl = [
        '''CREATE TABLE IF NOT EXISTS util_monthly_rates_config (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL UNIQUE,
            elec_rate NUMERIC(14,4) NOT NULL DEFAULT 0,
            water_general_rate NUMERIC(14,4) NOT NULL DEFAULT 0,
            water_drinking_rate NUMERIC(14,4) NOT NULL DEFAULT 0,
            school_van_rate NUMERIC(14,4) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'''
    ]
    con = get_con()
    cur = con.cursor()
    for s in ddl:
        cur.execute(s)
    con.commit()
    con.close()


MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']


def month_cycle_to_index(month_cycle: str):
    try:
        mon, year = month_cycle.split('-')
        mi = MONTHS.index(mon) + 1
        return int(year) * 12 + mi
    except Exception:
        return None


def current_month_cycle():
    now = datetime.now()
    return f"{MONTHS[now.month-1]}-{now.year}"


def is_next_month(month_cycle: str):
    idx = month_cycle_to_index(month_cycle)
    cur = month_cycle_to_index(current_month_cycle())
    if idx is None or cur is None:
        return False
    return idx == cur + 1


def unit_exists(unit_id: str):
    if not unit_id:
        return False
    try:
        r1 = q('SELECT unit_id FROM util_unit WHERE unit_id=? LIMIT 1', (unit_id,), one=True)
        if r1:
            return True
    except Exception:
        pass
    try:
        r2 = q('SELECT unit_id FROM util_unit_room_snapshot WHERE unit_id=? LIMIT 1', (unit_id,), one=True)
        return bool(r2)
    except Exception:
        return False


def cnic_is_sane(cnic: str):
    c = (cnic or '').strip()
    if re.match(r'^\d{5}-\d{7}-\d$', c):
        return True
    if re.match(r'^\d{13}$', c):
        return True
    return False


def normalize_employee_payload(d: dict):
    x = dict(d or {})
    if 'Remark' in x and 'Remarks' not in x:
        x['Remarks'] = x.get('Remark')
    return x


def _norm_header_token(s: str) -> str:
    return re.sub(r'[^a-z0-9]', '', (s or '').lower())


def build_employee_header_index():
    idx = {}

    # Primary standardized headers
    for csv_h, db_h in EMPLOYEE_CSV_TO_DB.items():
        idx[_norm_header_token(csv_h)] = db_h

    # Also accept legacy DB-style headers directly
    for db_h in EMPLOYEE_MASTER_REQUIRED_COLUMNS:
        idx[_norm_header_token(db_h)] = db_h

    # Common aliases
    idx[_norm_header_token('CNIC_No.')] = 'CNIC_No.'
    idx[_norm_header_token('CNIC No')] = 'CNIC_No.'
    idx[_norm_header_token("Father's Name")] = "Father's Name"
    idx[_norm_header_token('Sub Section')] = 'Sub Section'
    idx[_norm_header_token('Room No')] = 'Room No'
    idx[_norm_header_token('A/C')] = 'A/C'

    return idx


def ensure_employee_master_schema():
    ddl = [
        '''CREATE TABLE IF NOT EXISTS "Employees_Master" (
            "CompanyID" TEXT PRIMARY KEY,
            "Name" TEXT NOT NULL,
            "Father's Name" TEXT NULL,
            "CNIC_No." TEXT NOT NULL,
            "Mobile_No." TEXT NULL,
            "Department" TEXT NOT NULL,
            "Section" TEXT NULL,
            "Sub Section" TEXT NULL,
            "Designation" TEXT NOT NULL,
            "Employee Type" TEXT NULL,
            "Colony Type" TEXT NULL,
            "Block Floor" TEXT NULL,
            "Room No" TEXT NULL,
            "Shared Room" TEXT NULL,
            "Join Date" TEXT NULL,
            "Leave Date" TEXT NULL,
            "Active" TEXT NOT NULL DEFAULT 'Yes',
            "Iron Cot" TEXT NULL,
            "Single Bed" TEXT NULL,
            "Double Bed" TEXT NULL,
            "Mattress" TEXT NULL,
            "Sofa Set" TEXT NULL,
            "Bed Sheet" TEXT NULL,
            "Wardrobe" TEXT NULL,
            "Centre Table" TEXT NULL,
            "Wooden Chair" TEXT NULL,
            "Dinning Table" TEXT NULL,
            "Dinning Chair" TEXT NULL,
            "Side Table" TEXT NULL,
            "Fridge" TEXT NULL,
            "Water Dispenser" TEXT NULL,
            "Washing Machine" TEXT NULL,
            "Air Cooler" TEXT NULL,
            "A/C" TEXT NULL,
            "LED" TEXT NULL,
            "Gyser" TEXT NULL,
            "Electric Kettle" TEXT NULL,
            "Wifi Rtr" TEXT NULL,
            "Water Bottle" TEXT NULL,
            "LPG cylinder" TEXT NULL,
            "Gas Stove" TEXT NULL,
            "Crockery" TEXT NULL,
            "Kitchen Cabinet" TEXT NULL,
            "Mug" TEXT NULL,
            "Bucket" TEXT NULL,
            "Mirror" TEXT NULL,
            "Dustbin" TEXT NULL,
            "Remarks" TEXT NULL,
            "Unit_ID" TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )''',
        '''CREATE TABLE IF NOT EXISTS "Employees_Master_Audit" (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            "CompanyID" TEXT NOT NULL,
            action TEXT NOT NULL,
            actor_user_id INTEGER NULL,
            payload_json TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )''',
        '''CREATE TABLE IF NOT EXISTS util_employee_residence_snapshot (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL,
            "CompanyID" TEXT NOT NULL,
            "Colony Type" TEXT NULL,
            "Block Floor" TEXT NULL,
            "Room No" TEXT NULL,
            "Unit_ID" TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(month_cycle, "CompanyID")
        )'''
    ]
    con = get_con()
    cur = con.cursor()
    for s in ddl:
        cur.execute(s)

    existing = {r['name'] for r in cur.execute('PRAGMA table_info("Employees_Master")').fetchall()}
    for col in EMPLOYEE_MASTER_REQUIRED_COLUMNS:
        if col not in existing:
            cur.execute(f'ALTER TABLE "Employees_Master" ADD COLUMN "{col}" TEXT NULL')

    con.commit()
    con.close()


def ensure_employee_registry_schema():
    con = get_con()
    cur = con.cursor()
    cur.execute('''CREATE TABLE IF NOT EXISTS "Employees_Registry" (
        "CompanyID" TEXT PRIMARY KEY,
        "Name" TEXT NULL,
        "Father's Name" TEXT NULL,
        "CNIC_No." TEXT NULL,
        "Mobile_No." TEXT NULL,
        "Department" TEXT NULL,
        "Section" TEXT NULL,
        "Sub Section" TEXT NULL,
        "Designation" TEXT NULL,
        "Employee Type" TEXT NULL,
        "Colony Type" TEXT NULL,
        "Block Floor" TEXT NULL,
        "Room No" TEXT NULL,
        "Shared Room" TEXT NULL,
        "Join Date" TEXT NULL,
        "Leave Date" TEXT NULL,
        "Active" TEXT NULL,
        "Iron Cot" TEXT NULL,
        "Single Bed" TEXT NULL,
        "Double Bed" TEXT NULL,
        "Mattress" TEXT NULL,
        "Sofa Set" TEXT NULL,
        "Bed Sheet" TEXT NULL,
        "Wardrobe" TEXT NULL,
        "Centre Table" TEXT NULL,
        "Wooden Chair" TEXT NULL,
        "Dinning Table" TEXT NULL,
        "Dinning Chair" TEXT NULL,
        "Side Table" TEXT NULL,
        "Fridge" TEXT NULL,
        "Water Dispenser" TEXT NULL,
        "Washing Machine" TEXT NULL,
        "Air Cooler" TEXT NULL,
        "A/C" TEXT NULL,
        "LED" TEXT NULL,
        "Gyser" TEXT NULL,
        "Electric Kettle" TEXT NULL,
        "Wifi Rtr" TEXT NULL,
        "Water Bottle" TEXT NULL,
        "LPG cylinder" TEXT NULL,
        "Gas Stove" TEXT NULL,
        "Crockery" TEXT NULL,
        "Kitchen Cabinet" TEXT NULL,
        "Mug" TEXT NULL,
        "Bucket" TEXT NULL,
        "Mirror" TEXT NULL,
        "Dustbin" TEXT NULL,
        "Remarks" TEXT NULL,
        "Unit_ID" TEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    )''')
    con.commit()
    con.close()


def norm_key(s: str) -> str:
    return re.sub(r'[^a-z0-9]+', '', (s or '').strip().lower())


def canonical_header_map(headers):
    inverse = {}
    for canonical, aliases in HEADER_ALIAS_MAP.items():
        for a in aliases:
            inverse[norm_key(a)] = canonical
    mapped = {}
    for h in headers:
        key = norm_key(h)
        mapped[h] = inverse.get(key, (h or '').strip())
    return mapped


def map_row_keys(row: dict):
    header_map = canonical_header_map(list(row.keys()))
    out = {}
    for k, v in row.items():
        out[header_map[k]] = (v or '').strip() if isinstance(v, str) else v
    return out


def is_garbage_header_like(mapped_row: dict):
    values = [str(v or '').strip().lower() for v in mapped_row.values()]
    joined = ' '.join(values)
    if 'month cycle' in joined and 'meter' in joined and 'unit' in joined:
        return True
    if any(v.startswith('=') for v in values):
        return True
    if all((not v) for v in values):
        return True
    return False


@app.get('/')
def home():
    return render_template('month.html')


@app.get('/ui/month-cycle')
def ui_month_cycle():
    return render_template('month.html')


@app.get('/ui/rates')
def ui_rates():
    return render_template('rates.html')


@app.get('/ui/monthly-setup')
def ui_monthly_setup():
    actor, err = require_admin_from_request({})
    if err:
        return err
    return render_template('admin/monthly_setup.html', actor_user_id=actor)


@app.get('/ui/imports')
def ui_imports():
    return render_template('imports.html')


@app.get('/ui/van')
def ui_van():
    return render_template('van.html')


@app.get('/ui/billing')
def ui_billing():
    return render_template('billing.html')


@app.get('/ui/reports')
def ui_reports():
    return render_template('reports.html')


@app.get('/ui/reconciliation')
def ui_reconciliation():
    return render_template('reconciliation.html')


@app.get('/ui/employee-master')
def ui_employee_master():
    return render_template('employee_master.html')


@app.get('/ui/employees')
def ui_employees():
    return render_template('employees.html')


@app.get('/ui/employee-helper')
def ui_employee_helper():
    return render_template('employee_helper.html')


@app.get('/ui/unit-master')
def ui_unit_master():
    return render_template('unit_master.html')


@app.get('/ui/meter-master')
def ui_meter_master():
    return render_template('meter_master.html')


@app.get('/ui/meter-register-ingest')
def ui_meter_register_ingest():
    return render_template('meter_register_ingest.html')


@app.get('/ui/rooms')
def ui_rooms():
    return render_template('rooms.html')


@app.get('/ui/occupancy')
def ui_occupancy():
    return render_template('occupancy.html')


@app.get('/ui/elec-summary')
def ui_elec_summary():
    return render_template('elec_summary.html')


@app.get('/health')
def health():
    return {'ok': True, 'db': DB_PATH}


@app.post('/month/open')
def month_open():
    d = request.json or {}
    actor, err = require_admin_from_request(d)
    if err:
        return err
    m = d.get('month_cycle')
    q("INSERT OR IGNORE INTO util_month_cycle(month_cycle,state) VALUES(?,'OPEN')", (m,))
    audit_log('month_cycle', m, 'OPEN', actor, None, {'state': 'OPEN'}, correlation_id=f'month-open:{m}')
    return {'status': 'ok', 'month_cycle': m}


@app.post('/month/transition')
def month_transition():
    d = request.json or {}
    actor, err = require_admin_from_request(d)
    if err:
        return err
    m = d.get('month_cycle')
    to_state = d.get('to_state')
    before = q('SELECT month_cycle, state FROM util_month_cycle WHERE month_cycle=?', (m,), one=True)
    if to_state == 'LOCKED':
        q('UPDATE util_month_cycle SET locked_by_user_id=?, locked_at=CURRENT_TIMESTAMP, state=? WHERE month_cycle=?', (actor, to_state, m))
    else:
        q('UPDATE util_month_cycle SET state=? WHERE month_cycle=?', (to_state, m))
    after = q('SELECT month_cycle, state FROM util_month_cycle WHERE month_cycle=?', (m,), one=True)
    audit_log('month_cycle', m, 'TRANSITION', actor, before, after, correlation_id=f'month-transition:{m}:{to_state}')
    return {'status': 'ok', 'month_cycle': m, 'to_state': to_state}


@app.post('/rates/upsert')
def rates_upsert():
    d = request.json or {}
    actor, err = require_admin_from_request(d)
    if err:
        return err
    m = d['month_cycle']
    lock_err = reject_if_month_locked(m)
    if lock_err:
        return lock_err
    q('''INSERT INTO util_rate_monthly(month_cycle,elec_rate,water_general_rate,water_drinking_rate,school_van_rate)
         VALUES(?,?,?,?,?)
         ON CONFLICT(month_cycle) DO UPDATE SET
         elec_rate=excluded.elec_rate,
         water_general_rate=excluded.water_general_rate,
         water_drinking_rate=excluded.water_drinking_rate,
         school_van_rate=excluded.school_van_rate''',
      (m, d['elec_rate'], d['water_general_rate'], d['water_drinking_rate'], d['school_van_rate']))
    return {'status': 'ok', 'month_cycle': m, 'actor_user_id': actor}


@app.post('/rates/approve')
def rates_approve():
    d = request.json
    q('UPDATE util_rate_monthly SET approved_by_user_id=?, approved_at=CURRENT_TIMESTAMP WHERE month_cycle=?', (d.get('actor_user_id', 1), d['month_cycle']))
    return {'status': 'ok'}


def initialize_new_month(month_cycle: str, seed_from_previous: bool = True):
    month_cycle = (month_cycle or '').strip()
    if not month_cycle:
        raise ValueError('month_cycle is required')

    month_rec = q('SELECT month_cycle FROM util_month_cycle WHERE month_cycle=? LIMIT 1', (month_cycle,), one=True)
    if not month_rec:
        q("INSERT INTO util_month_cycle(month_cycle,state) VALUES(?,'OPEN')", (month_cycle,))

    existing = q('SELECT month_cycle FROM util_monthly_rates_config WHERE month_cycle=? LIMIT 1', (month_cycle,), one=True)
    if existing:
        return {'status': 'ok', 'month_cycle': month_cycle, 'seeded': False, 'already_exists': True}

    seed = {
        'elec_rate': 0,
        'water_general_rate': 0,
        'water_drinking_rate': 0,
        'school_van_rate': 0,
    }

    if seed_from_previous:
        prev = q('''SELECT elec_rate, water_general_rate, water_drinking_rate, school_van_rate
                    FROM util_monthly_rates_config
                    ORDER BY id DESC LIMIT 1''', one=True)
        if prev:
            seed.update({
                'elec_rate': float(prev.get('elec_rate') or 0),
                'water_general_rate': float(prev.get('water_general_rate') or 0),
                'water_drinking_rate': float(prev.get('water_drinking_rate') or 0),
                'school_van_rate': float(prev.get('school_van_rate') or 0),
            })

    q('''INSERT INTO util_monthly_rates_config
         (month_cycle, elec_rate, water_general_rate, water_drinking_rate, school_van_rate)
         VALUES(?,?,?,?,?)''',
      (month_cycle, seed['elec_rate'], seed['water_general_rate'], seed['water_drinking_rate'], seed['school_van_rate']))

    return {'status': 'ok', 'month_cycle': month_cycle, 'seeded': True, 'already_exists': False, 'rates': seed}


@app.post('/monthly-rates/initialize')
def monthly_rates_initialize():
    d = request.json or {}
    actor, err = require_admin_from_request(d)
    if err:
        return err
    month_cycle = (d.get('month_cycle') or '').strip()
    seed_from_previous = bool(d.get('seed_from_previous', True))
    if not month_cycle:
        return jsonify({'status': 'error', 'error': 'month_cycle is required'}), 400
    try:
        lock_err = reject_if_month_locked(month_cycle)
        if lock_err:
            return lock_err
        out = initialize_new_month(month_cycle, seed_from_previous)
        out['actor_user_id'] = actor
        return jsonify(out)
    except ValueError as e:
        return jsonify({'status': 'error', 'error': str(e)}), 400


@app.get('/monthly-rates/config')
def monthly_rates_get_config():
    month_cycle = (request.args.get('month_cycle') or '').strip()
    if not month_cycle:
        return jsonify({'status': 'error', 'error': 'month_cycle is required'}), 400
    row = q('''SELECT month_cycle, elec_rate, water_general_rate, water_drinking_rate, school_van_rate,
                      created_at, updated_at
               FROM util_monthly_rates_config WHERE month_cycle=? LIMIT 1''', (month_cycle,), one=True)
    if not row:
        return jsonify({'status': 'error', 'error': 'config not found'}), 404
    return jsonify({'status': 'ok', 'row': row})


@app.get('/monthly-rates/history')
def monthly_rates_history():
    limit = int(request.args.get('limit') or 24)
    limit = max(1, min(limit, 120))
    rows = q('''SELECT month_cycle, elec_rate, water_general_rate, water_drinking_rate, school_van_rate,
                       created_at, updated_at
                FROM util_monthly_rates_config
                ORDER BY month_cycle DESC
                LIMIT ?''', (limit,))
    return jsonify({'status': 'ok', 'rows': rows})


@app.post('/monthly-rates/config/upsert')
def monthly_rates_upsert_config():
    d = request.json or {}
    actor, err = require_admin_from_request(d)
    if err:
        return err

    month_cycle = (d.get('month_cycle') or '').strip()
    if not month_cycle:
        return jsonify({'status': 'error', 'error': 'month_cycle is required'}), 400

    lock_err = reject_if_month_locked(month_cycle)
    if lock_err:
        return lock_err

    elec_rate = float(d.get('elec_rate') or 0)
    water_general_rate = float(d.get('water_general_rate') or 0)
    water_drinking_rate = float(d.get('water_drinking_rate') or 0)
    school_van_rate = float(d.get('school_van_rate') or 0)

    before = q('''SELECT month_cycle, elec_rate, water_general_rate, water_drinking_rate, school_van_rate
                  FROM util_monthly_rates_config WHERE month_cycle=? LIMIT 1''', (month_cycle,), one=True)

    q('''INSERT INTO util_monthly_rates_config
         (month_cycle, elec_rate, water_general_rate, water_drinking_rate, school_van_rate)
         VALUES(?,?,?,?,?)
         ON CONFLICT(month_cycle) DO UPDATE SET
         elec_rate=excluded.elec_rate,
         water_general_rate=excluded.water_general_rate,
         water_drinking_rate=excluded.water_drinking_rate,
         school_van_rate=excluded.school_van_rate,
         updated_at=CURRENT_TIMESTAMP''',
      (month_cycle, elec_rate, water_general_rate, water_drinking_rate, school_van_rate))

    after = q('''SELECT month_cycle, elec_rate, water_general_rate, water_drinking_rate, school_van_rate
                 FROM util_monthly_rates_config WHERE month_cycle=? LIMIT 1''', (month_cycle,), one=True)
    audit_log('monthly_rates', month_cycle, 'UPSERT', actor, before, after, correlation_id=f'rates:{month_cycle}')

    return jsonify({'status': 'ok', 'month_cycle': month_cycle, 'actor_user_id': actor})


@app.get('/expenses/monthly-variable')
def monthly_variable_expenses_list():
    m = (request.args.get('month_cycle') or '').strip()
    et = (request.args.get('expense_type') or '').strip()
    sql = 'SELECT id, month_cycle, expense_type, amount, notes, created_at, updated_at FROM monthly_variable_expenses WHERE 1=1'
    params = []
    if m:
        sql += ' AND month_cycle=?'
        params.append(m)
    if et:
        sql += ' AND expense_type=?'
        params.append(et)
    sql += ' ORDER BY month_cycle DESC, expense_type'
    return jsonify({'status': 'ok', 'rows': q(sql, tuple(params))})


@app.post('/expenses/monthly-variable/upsert')
def monthly_variable_expenses_upsert():
    d = request.json or {}
    month_cycle = (d.get('month_cycle') or '').strip()
    expense_type = (d.get('expense_type') or '').strip().upper()
    amount = float(d.get('amount') or 0)
    notes = (d.get('notes') or '').strip() or None

    if not month_cycle or not expense_type:
        return jsonify({'status': 'error', 'error': 'month_cycle and expense_type are required'}), 400
    lock_err = reject_if_month_locked(month_cycle)
    if lock_err:
        return lock_err
    if amount < 0:
        return jsonify({'status': 'error', 'error': 'amount cannot be negative'}), 400

    q('''INSERT INTO monthly_variable_expenses(month_cycle,expense_type,amount,notes)
         VALUES(?,?,?,?)
         ON CONFLICT(month_cycle,expense_type)
         DO UPDATE SET amount=excluded.amount, notes=excluded.notes, updated_at=CURRENT_TIMESTAMP''',
      (month_cycle, expense_type, amount, notes))

    return jsonify({'status': 'ok', 'month_cycle': month_cycle, 'expense_type': expense_type, 'amount': amount})


@app.post('/imports/mark-validated')
def import_validated():
    d = request.json
    q("UPDATE util_import_batch SET status='VALIDATED', valid_rows=?, rejected_rows=?, validated_at=CURRENT_TIMESTAMP WHERE id=?",
      (d.get('valid_rows', 0), d.get('rejected_rows', 0), d['batch_id']))
    return {'status': 'ok'}


@app.get('/imports/unit-id-aliases')
def unit_id_aliases():
    return jsonify({'canonical_key': 'Unit_ID', 'aliases': UNIT_ID_ALIASES})


@app.post('/imports/meter-register/ingest-preview')
def meter_register_ingest_preview():
    d = request.json or {}
    csv_text = d.get('csv_text', '')
    if not csv_text.strip():
        return jsonify({'status': 'error', 'error': 'csv_text is required'}), 400

    reader = csv.DictReader(StringIO(csv_text))
    if not reader.fieldnames:
        return jsonify({'status': 'error', 'error': 'CSV header row is required'}), 400

    accepted, errors = [], []
    skipped_garbage = 0

    for idx, raw in enumerate(reader, start=2):
        mapped = map_row_keys(raw)
        if is_garbage_header_like(mapped):
            skipped_garbage += 1
            continue

        unit_id = str(mapped.get('unit_id', '') or '').strip()
        if not unit_id:
            errors.append({'row_no': idx, 'error_code': 'MISSING_UNIT_ID', 'error_field': 'Unit_ID', 'error_message': 'Unit_ID is mandatory after alias normalization'})
            continue

        prev_raw = str(mapped.get('previous_reading', '') or '').strip()
        curr_raw = str(mapped.get('current_reading', '') or '').strip()
        try:
            prev = float(prev_raw or 0)
            curr = float(curr_raw or 0)
        except ValueError:
            errors.append({'row_no': idx, 'error_code': 'INVALID_READING', 'error_field': 'Previous_Reading/Current_Reading', 'error_message': 'Readings must be numeric'})
            continue

        if curr < prev:
            errors.append({'row_no': idx, 'error_code': 'CURRENT_LT_PREVIOUS', 'error_field': 'Current_Reading', 'error_message': 'Current_Reading must be >= Previous_Reading'})
            continue

        accepted.append({
            'row_no': idx,
            'Month_Cycle': str(mapped.get('month_cycle', '') or '').strip(),
            'Unit_ID': unit_id,
            'Meter_ID': str(mapped.get('meter_id', '') or '').strip(),
            'Meter_Type': str(mapped.get('meter_type', '') or '').strip(),
            'Previous_Reading': prev,
            'Current_Reading': curr,
            'Net_Units': curr - prev,
        })

    token = uuid.uuid4().hex[:12]
    err_buf = StringIO()
    w = csv.writer(err_buf)
    w.writerow(['row_no', 'error_code', 'error_field', 'error_message'])
    for e in errors:
        w.writerow([e['row_no'], e['error_code'], e['error_field'], e['error_message']])
    ERROR_REPORT_CACHE[token] = err_buf.getvalue().encode('utf-8')

    mapped_headers = canonical_header_map(reader.fieldnames)
    canonical_headers = sorted(list(set(mapped_headers.values())))

    return jsonify({
        'status': 'ok',
        'canonical_key': 'Unit_ID',
        'canonical_headers': canonical_headers,
        'accepted_rows': len(accepted),
        'rejected_rows': len(errors),
        'skipped_garbage_rows': skipped_garbage,
        'accepted_preview': accepted[:5],
        'errors_preview': errors[:20],
        'error_report_download': f'/imports/error-report/{token}'
    })


@app.get('/imports/error-report/<token>')
def imports_error_report(token):
    data = ERROR_REPORT_CACHE.get(token)
    if not data:
        return jsonify({'status': 'error', 'error': 'report token not found'}), 404
    out = BytesIO(data)
    out.seek(0)
    return send_file(out, as_attachment=True, download_name=f'import_errors_{token}.csv', mimetype='text/csv')


@app.get('/employees')
def employees_list():
    qtxt = (request.args.get('q') or '').strip()
    active_only = (request.args.get('active_only') or '').strip() in ('1', 'true', 'True', 'yes', 'Yes')

    sql = 'SELECT * FROM "Employees_Master" WHERE 1=1'
    params = []
    if active_only:
        sql += ' AND UPPER(COALESCE(NULLIF(TRIM("Active"),\'\'),\'Yes\'))=\'YES\''
    if qtxt:
        sql += ' AND ("CompanyID" LIKE ? OR "Name" LIKE ? OR "CNIC_No." LIKE ?)'
        like = f'%{qtxt}%'
        params.extend([like, like, like])
    sql += ' ORDER BY "CompanyID"'
    return jsonify({'status': 'ok', 'rows': q(sql, tuple(params))})


@app.post('/registry/employees/upsert')
def registry_employees_upsert():
    d = request.json or {}
    company_id = (d.get('CompanyID') or '').strip()
    if not company_id:
        return jsonify({'status': 'error', 'error': 'CompanyID required'}), 400

    cols = EMPLOYEE_MASTER_REQUIRED_COLUMNS
    quoted = ','.join([f'"{c}"' for c in cols])
    placeholders = ','.join(['?'] * len(cols))
    values = [d.get(c) for c in cols]
    values[0] = company_id

    set_clause = ','.join([f'"{c}"=excluded."{c}"' for c in cols if c != 'CompanyID']) + ', updated_at=CURRENT_TIMESTAMP'
    q(f'''INSERT INTO "Employees_Registry" ({quoted}) VALUES ({placeholders})
          ON CONFLICT("CompanyID") DO UPDATE SET {set_clause}''', tuple(values))
    return jsonify({'status': 'ok', 'CompanyID': company_id})


@app.get('/registry/employees/<company_id>')
def registry_employee_get(company_id):
    row = q('SELECT * FROM "Employees_Registry" WHERE "CompanyID"=?', (company_id,), one=True)
    if not row:
        return jsonify({'status': 'error', 'error': 'CompanyID not found in registry'}), 404
    return jsonify({'status': 'ok', 'row': row})


@app.post('/registry/employees/import-preview')
def registry_import_preview():
    d = request.json or {}
    csv_text = d.get('csv_text', '')
    if not (csv_text or '').strip():
        return jsonify({'status': 'error', 'error': 'csv_text is required'}), 400

    reader = csv.DictReader(StringIO(csv_text))
    if not reader.fieldnames:
        return jsonify({'status': 'error', 'error': 'CSV header row is required'}), 400

    required = ['CompanyID', 'Name', 'CNIC_No.', 'Department', 'Designation', 'Unit_ID']
    row_errors = []
    accepted = []
    seen = set()

    for idx, raw in enumerate(reader, start=2):
        row = {k: (raw.get(k) or '').strip() for k in raw.keys()}
        miss = [f for f in required if not row.get(f)]
        if miss:
            row_errors.append({'row_no': idx, 'error_code': 'MISSING_REQUIRED', 'error_message': f'Missing: {", ".join(miss)}'})
            continue

        cid = row.get('CompanyID')
        if cid in seen:
            row_errors.append({'row_no': idx, 'error_code': 'DUPLICATE_IN_FILE', 'error_message': f'Duplicate CompanyID in file: {cid}'})
            continue
        seen.add(cid)

        if not unit_exists(row.get('Unit_ID')):
            row_errors.append({'row_no': idx, 'error_code': 'INVALID_UNIT_ID', 'error_message': f'Unit_ID not found: {row.get("Unit_ID")}'})
            continue

        exists_master = q('SELECT "CompanyID" FROM "Employees_Master" WHERE "CompanyID"=?', (cid,), one=True)
        accepted.append({'row_no': idx, 'CompanyID': cid, 'exists_in_master': bool(exists_master), 'row': row})

    token = uuid.uuid4().hex[:12]
    buf = StringIO(); w = csv.writer(buf)
    w.writerow(['row_no', 'error_code', 'error_message'])
    for e in row_errors:
        w.writerow([e['row_no'], e['error_code'], e['error_message']])
    ERROR_REPORT_CACHE[token] = buf.getvalue().encode('utf-8')

    return jsonify({
        'status': 'ok',
        'total_rows': len(accepted) + len(row_errors),
        'accepted_rows': len(accepted),
        'rejected_rows': len(row_errors),
        'accepted_preview': accepted[:20],
        'errors_preview': row_errors[:50],
        'error_report_download': f'/imports/error-report/{token}'
    })


@app.post('/registry/employees/import-commit')
def registry_import_commit():
    d = request.json or {}
    csv_text = d.get('csv_text', '')
    if not (csv_text or '').strip():
        return jsonify({'status': 'error', 'error': 'csv_text is required'}), 400

    reader = csv.DictReader(StringIO(csv_text))
    required = ['CompanyID', 'Name', 'CNIC_No.', 'Department', 'Designation', 'Unit_ID']
    inserted = 0
    updated = 0
    rejected = 0

    for raw in reader:
        row = {k: (raw.get(k) or '').strip() for k in raw.keys()}
        if any(not row.get(f) for f in required):
            rejected += 1
            continue
        if not unit_exists(row.get('Unit_ID')):
            rejected += 1
            continue

        cols = EMPLOYEE_MASTER_REQUIRED_COLUMNS
        values = [row.get(c) for c in cols]
        values[0] = row.get('CompanyID')

        existed = q('SELECT "CompanyID" FROM "Employees_Registry" WHERE "CompanyID"=?', (row.get('CompanyID'),), one=True)
        quoted = ','.join([f'"{c}"' for c in cols])
        placeholders = ','.join(['?'] * len(cols))
        set_clause = ','.join([f'"{c}"=excluded."{c}"' for c in cols if c != 'CompanyID']) + ', updated_at=CURRENT_TIMESTAMP'
        q(f'''INSERT INTO "Employees_Registry" ({quoted}) VALUES ({placeholders})
              ON CONFLICT("CompanyID") DO UPDATE SET {set_clause}''', tuple(values))

        if existed:
            updated += 1
        else:
            inserted += 1

    return jsonify({'status': 'ok', 'inserted': inserted, 'updated': updated, 'rejected': rejected})


@app.post('/registry/employees/promote-to-master')
def registry_promote_to_master():
    d = request.json or {}
    upsert_mode = bool(d.get('upsert', False))

    rows = q('SELECT * FROM "Employees_Registry" ORDER BY "CompanyID"')
    promoted = 0
    skipped = 0
    rejected = 0

    insert_cols = [
        'CompanyID', 'Name', "Father's Name", 'CNIC_No.', 'Mobile_No.', 'Department', 'Section', 'Sub Section',
        'Designation', 'Employee Type', 'Colony Type', 'Block Floor', 'Room No', 'Shared Room',
        'Join Date', 'Leave Date', 'Active', 'Iron Cot', 'Single Bed', 'Double Bed', 'Mattress', 'Sofa Set',
        'Bed Sheet', 'Wardrobe', 'Centre Table', 'Wooden Chair', 'Dinning Table', 'Dinning Chair', 'Side Table',
        'Fridge', 'Water Dispenser', 'Washing Machine', 'Air Cooler', 'A/C', 'LED', 'Gyser', 'Electric Kettle',
        'Wifi Rtr', 'Water Bottle', 'LPG cylinder', 'Gas Stove', 'Crockery', 'Kitchen Cabinet', 'Mug', 'Bucket',
        'Mirror', 'Dustbin', 'Remarks', 'Unit_ID'
    ]
    quoted = ','.join([f'"{c}"' for c in insert_cols])
    placeholders = ','.join(['?'] * len(insert_cols))
    set_clause = ','.join([f'"{c}"=excluded."{c}"' for c in insert_cols if c not in ('CompanyID', 'CNIC_No.')]) + ', updated_at=CURRENT_TIMESTAMP'

    for r in rows:
        missing = [f for f in ['CompanyID', 'Name', 'CNIC_No.', 'Department', 'Designation', 'Unit_ID'] if not (r.get(f) or '').strip()]
        if missing or (not unit_exists((r.get('Unit_ID') or '').strip())):
            rejected += 1
            continue

        cid = (r.get('CompanyID') or '').strip()
        exists_master = q('SELECT "CompanyID" FROM "Employees_Master" WHERE "CompanyID"=?', (cid,), one=True)
        if exists_master and not upsert_mode:
            skipped += 1
            continue

        values = [r.get(c) for c in insert_cols]
        if upsert_mode:
            q(f'''INSERT INTO "Employees_Master" ({quoted}) VALUES ({placeholders})
                  ON CONFLICT("CompanyID") DO UPDATE SET {set_clause}''', tuple(values))
        else:
            q(f'INSERT INTO "Employees_Master" ({quoted}) VALUES ({placeholders})', tuple(values))
        promoted += 1

    return jsonify({'status': 'ok', 'promoted': promoted, 'skipped_existing': skipped, 'rejected': rejected, 'upsert': upsert_mode})


@app.get('/employees/search')
def employees_search():
    qtxt = (request.args.get('q') or '').strip()
    if not qtxt:
        return jsonify({'status': 'ok', 'rows': []})
    like = f'%{qtxt}%'
    rows = q('''SELECT "CompanyID", "Name", "CNIC_No.", "Department", "Designation", "Active", "Unit_ID"
                FROM "Employees_Master"
                WHERE "CompanyID" LIKE ? OR "CNIC_No." LIKE ? OR "Name" LIKE ?
                ORDER BY "CompanyID" LIMIT 100''', (like, like, like))
    return jsonify({'status': 'ok', 'rows': rows})


@app.get('/employees/<company_id>')
def employees_get(company_id):
    row = q('SELECT * FROM "Employees_Master" WHERE "CompanyID"=?', (company_id,), one=True)
    if not row:
        return jsonify({'status': 'error', 'error': 'CompanyID not found'}), 404
    return jsonify({'status': 'ok', 'row': row})


@app.get('/employees/meta/departments')
def employees_meta_departments():
    rows = q('''SELECT DISTINCT "Department", "Section", "Sub Section"
                FROM "Employees_Master"
                WHERE COALESCE("Department",'')<>''
                ORDER BY "Department","Section","Sub Section"''')
    return jsonify({'status': 'ok', 'rows': rows})


@app.get('/units')
def units_list():
    qtxt = (request.args.get('q') or '').strip()
    sql = '''SELECT u.unit_id, u.colony_type, u.block_name, u.room_no, u.is_active,
                    (SELECT s.category
                     FROM util_unit_room_snapshot s
                     WHERE s.unit_id=u.unit_id
                     ORDER BY s.month_cycle DESC, s.id DESC
                     LIMIT 1) AS category
             FROM util_unit u WHERE 1=1'''
    params = []
    if qtxt:
        sql += ' AND (unit_id LIKE ? OR colony_type LIKE ? OR block_name LIKE ? OR room_no LIKE ?)'
        like = f'%{qtxt}%'
        params.extend([like, like, like, like])
    sql += ' ORDER BY unit_id'
    try:
        rows = q(sql, tuple(params))
    except Exception:
        rows = q('''SELECT u.unit_id, u.colony_type, u.block_name, u.room_no, 1 AS is_active,
                           (SELECT s.category FROM util_unit_room_snapshot s
                            WHERE s.unit_id=u.unit_id
                            ORDER BY s.month_cycle DESC, s.id DESC LIMIT 1) AS category
                    FROM util_unit u ORDER BY u.unit_id''')
    return jsonify({'status': 'ok', 'rows': rows})


@app.post('/units/upsert')
def units_upsert():
    d = request.json or {}
    unit_id = (d.get('unit_id') or d.get('Unit_ID') or '').strip()
    if not unit_id:
        return jsonify({'status': 'error', 'error': 'unit_id required'}), 400
    colony_type = (d.get('colony_type') or d.get('Colony Type') or '').strip() or None
    block_name = (d.get('block_name') or d.get('Block Floor') or '').strip() or None
    room_no = (d.get('room_no') or d.get('Room No') or '').strip() or None
    is_active = int(d.get('is_active', 1) or 1)

    try:
        q('''INSERT INTO util_unit(unit_id,colony_type,block_name,room_no,is_active)
             VALUES(?,?,?,?,?)
             ON CONFLICT(unit_id) DO UPDATE SET
             colony_type=excluded.colony_type, block_name=excluded.block_name, room_no=excluded.room_no, is_active=excluded.is_active''',
          (unit_id, colony_type, block_name, room_no, is_active))
    except Exception:
        q('''INSERT INTO util_unit(unit_id,colony_type,block_name,room_no)
             VALUES(?,?,?,?)
             ON CONFLICT(unit_id) DO UPDATE SET
             colony_type=excluded.colony_type, block_name=excluded.block_name, room_no=excluded.room_no''',
          (unit_id, colony_type, block_name, room_no))

    return jsonify({'status': 'ok', 'unit_id': unit_id})


@app.delete('/units/<unit_id>')
def units_delete(unit_id):
    try:
        q('UPDATE util_unit SET is_active=0 WHERE unit_id=?', (unit_id,))
        return jsonify({'status': 'ok', 'unit_id': unit_id, 'policy': 'soft-delete'})
    except Exception:
        q('DELETE FROM util_unit WHERE unit_id=?', (unit_id,))
        return jsonify({'status': 'ok', 'unit_id': unit_id, 'policy': 'hard-delete'})


@app.get('/units/suggest')
def units_suggest():
    colony = (request.args.get('colony_type') or '').strip()
    block = (request.args.get('block_floor') or '').strip()
    room = (request.args.get('room_no') or '').strip()

    rows = []
    try:
        rows = q('''SELECT unit_id AS "Unit_ID", colony_type AS "Colony Type", block_name AS "Block Floor", room_no AS "Room No"
                    FROM util_unit
                    WHERE (?='' OR colony_type=?) AND (?='' OR block_name=?) AND (?='' OR room_no=?)
                    ORDER BY unit_id LIMIT 20''', (colony, colony, block, block, room, room))
    except Exception:
        rows = []

    if not rows:
        rows = q('''SELECT DISTINCT unit_id AS "Unit_ID", category AS "Colony Type", block_floor AS "Block Floor", room_no AS "Room No"
                    FROM util_unit_room_snapshot
                    WHERE (?='' OR category=?) AND (?='' OR block_floor=?) AND (?='' OR room_no=?)
                    ORDER BY unit_id LIMIT 20''', (colony, colony, block, block, room, room))

    return jsonify({'status': 'ok', 'rows': rows})


@app.get('/units/resolve/<unit_id>')
def unit_resolve(unit_id):
    row = None
    try:
        row = q('''SELECT unit_id AS "Unit_ID", colony_type AS "Colony Type", block_name AS "Block Floor", room_no AS "Room No"
                   FROM util_unit WHERE unit_id=? LIMIT 1''', (unit_id,), one=True)
    except Exception:
        row = None
    if not row:
        row = q('''SELECT unit_id AS "Unit_ID", category AS "Colony Type", block_floor AS "Block Floor", room_no AS "Room No"
                   FROM util_unit_room_snapshot WHERE unit_id=? ORDER BY id DESC LIMIT 1''', (unit_id,), one=True)
    if not row:
        return jsonify({'status': 'error', 'error': 'Unit_ID not found'}), 404
    return jsonify({'status': 'ok', 'row': row})


@app.post('/employees/import')
def employees_import():
    d = request.json or {}
    csv_text = (d.get('csv_text') or '').strip()
    commit = bool(d.get('commit', False))

    if not csv_text:
        return jsonify({'status': 'error', 'error': 'csv_text is required'}), 400

    reader = csv.DictReader(StringIO(csv_text))
    headers = reader.fieldnames or []
    if not headers:
        return jsonify({'status': 'error', 'error': 'CSV header row is required'}), 400

    header_idx = build_employee_header_index()
    mapped = {}
    unknown = []
    for h in headers:
        db_col = header_idx.get(_norm_header_token(h))
        if not db_col:
            unknown.append(h)
            continue
        mapped[h] = db_col

    required_db = ['CompanyID', 'Name', 'CNIC_No.', 'Department', 'Designation', 'Unit_ID']
    mapped_db_cols = set(mapped.values())
    missing_required = [c for c in required_db if c not in mapped_db_cols]

    if unknown or missing_required:
        return jsonify({
            'status': 'error',
            'error': 'Invalid/incomplete CSV headers. Use standardized headers everywhere.',
            'missing_required': missing_required,
            'unknown_headers': unknown,
            'expected_headers_standard': EMPLOYEE_CSV_HEADERS,
            'received_headers': headers
        }), 400

    preview = []
    errors = []
    inserted = 0
    updated = 0

    for idx, raw in enumerate(reader, start=2):
        row = {c: '' for c in EMPLOYEE_MASTER_REQUIRED_COLUMNS}
        for src_h, db_h in mapped.items():
            row[db_h] = (raw.get(src_h) or '').strip()
        row = normalize_employee_payload(row)
        row['Active'] = (row.get('Active') or 'Yes').strip() or 'Yes'

        miss = [f for f in ['CompanyID', 'Name', 'CNIC_No.', 'Department', 'Designation', 'Unit_ID'] if not row.get(f)]
        if miss:
            errors.append({'row_no': idx, 'CompanyID': row.get('CompanyID'), 'error': f'Missing required: {", ".join(miss)}'})
            continue
        if not cnic_is_sane(row.get('CNIC_No.')):
            errors.append({'row_no': idx, 'CompanyID': row.get('CompanyID'), 'error': 'Invalid CNIC_No. format'})
            continue
        if not unit_exists(row.get('Unit_ID')):
            errors.append({'row_no': idx, 'CompanyID': row.get('CompanyID'), 'error': f'Invalid Unit_ID: {row.get("Unit_ID")}'})
            continue

        existing = q('SELECT "CompanyID","CNIC_No." FROM "Employees_Master" WHERE "CompanyID"=?', (row['CompanyID'],), one=True)
        if existing and (existing.get('CNIC_No.') or '') != row.get('CNIC_No.'):
            errors.append({'row_no': idx, 'CompanyID': row.get('CompanyID'), 'error': 'CNIC_No. immutable for existing CompanyID'})
            continue

        preview.append({'row_no': idx, 'CompanyID': row.get('CompanyID'), 'mode': 'update' if existing else 'create'})

        if commit:
            cols = EMPLOYEE_MASTER_REQUIRED_COLUMNS
            quoted = ','.join([f'"{c}"' for c in cols])
            placeholders = ','.join(['?'] * len(cols))
            set_clause = ','.join([f'"{c}"=excluded."{c}"' for c in cols if c not in ('CompanyID', 'CNIC_No.')]) + ', updated_at=CURRENT_TIMESTAMP'
            values = [row.get(c) for c in cols]
            q(f'''INSERT INTO "Employees_Master" ({quoted}) VALUES ({placeholders})
                  ON CONFLICT("CompanyID") DO UPDATE SET {set_clause}''', tuple(values))
            if existing:
                updated += 1
            else:
                inserted += 1

    if commit:
        return jsonify({'status': 'ok', 'mode': 'commit', 'inserted': inserted, 'updated': updated, 'rejected': len(errors), 'errors_preview': errors[:100]})

    return jsonify({'status': 'ok', 'mode': 'preview', 'accepted_rows': len(preview), 'rejected_rows': len(errors), 'preview': preview[:100], 'errors_preview': errors[:100]})


@app.post('/employees/upsert')
def employees_upsert():
    d = normalize_employee_payload(request.json or {})
    company_id = (d.get('CompanyID') or '').strip()
    name = (d.get('Name') or '').strip()
    cnic = (d.get('CNIC_No.') or '').strip()
    dept = (d.get('Department') or '').strip()
    desg = (d.get('Designation') or '').strip()
    unit_id = (d.get('Unit_ID') or '').strip()
    active = (d.get('Active') or 'Yes').strip() or 'Yes'

    missing = [f for f, v in [('CompanyID', company_id), ('Name', name), ('CNIC_No.', cnic), ('Department', dept), ('Designation', desg), ('Unit_ID', unit_id)] if not v]
    if missing:
        return jsonify({'status': 'error', 'error': 'Missing mandatory fields', 'missing_fields': missing}), 400
    if active not in ('Yes', 'No'):
        return jsonify({'status': 'error', 'error': 'Active must be Yes/No'}), 400
    if not cnic_is_sane(cnic):
        return jsonify({'status': 'error', 'error': 'Invalid CNIC_No. format'}), 400
    if not unit_exists(unit_id):
        return jsonify({'status': 'error', 'error': 'Unit_ID not found in authoritative mapping'}), 400

    existing = q('SELECT * FROM "Employees_Master" WHERE "CompanyID"=?', (company_id,), one=True)

    if existing:
        if cnic != (existing.get('CNIC_No.') or ''):
            return jsonify({'status': 'error', 'error': 'CNIC_No. immutable'}), 400

        res_fields = {'Colony Type', 'Block Floor', 'Room No', 'Shared Room', 'Unit_ID'}
        changing_res = any((k in d and (d.get(k) or '') != (existing.get(k) or '')) for k in res_fields)
        if changing_res:
            hist = q('SELECT 1 FROM util_occupancy_monthly WHERE employee_id=? LIMIT 1', (company_id,), one=True)
            if hist:
                month_cycle = (d.get('month_cycle') or '').strip()
                if not month_cycle or not is_next_month(month_cycle):
                    return jsonify({'status': 'error', 'error': 'Residence edits allowed only from next month when occupancy history exists'}), 400

    cnic_owner = q('SELECT "CompanyID" FROM "Employees_Master" WHERE "CNIC_No."=? AND "CompanyID"<>? LIMIT 1', (cnic, company_id), one=True)
    if cnic_owner:
        return jsonify({'status': 'error', 'error': 'CNIC_No. already used by another CompanyID'}), 409

    cols = [
        'CompanyID', 'Name', "Father's Name", 'CNIC_No.', 'Mobile_No.', 'Department', 'Section', 'Sub Section',
        'Designation', 'Employee Type', 'Colony Type', 'Block Floor', 'Room No', 'Shared Room',
        'Join Date', 'Leave Date', 'Active', 'Iron Cot', 'Single Bed', 'Double Bed', 'Mattress', 'Sofa Set',
        'Bed Sheet', 'Wardrobe', 'Centre Table', 'Wooden Chair', 'Dinning Table', 'Dinning Chair', 'Side Table',
        'Fridge', 'Water Dispenser', 'Washing Machine', 'Air Cooler', 'A/C', 'LED', 'Gyser', 'Electric Kettle',
        'Wifi Rtr', 'Water Bottle', 'LPG cylinder', 'Gas Stove', 'Crockery', 'Kitchen Cabinet', 'Mug', 'Bucket',
        'Mirror', 'Dustbin', 'Remarks', 'Unit_ID'
    ]
    values = [d.get(c) for c in cols]
    values[0] = company_id
    values[3] = cnic
    values[16] = active

    quoted = ','.join([f'"{c}"' for c in cols])
    placeholders = ','.join(['?'] * len(cols))
    set_clause = ','.join([f'"{c}"=excluded."{c}"' for c in cols if c not in ('CompanyID', 'CNIC_No.')]) + ', updated_at=CURRENT_TIMESTAMP'
    q(f'''INSERT INTO "Employees_Master" ({quoted}) VALUES ({placeholders})
          ON CONFLICT("CompanyID") DO UPDATE SET {set_clause}''', tuple(values))

    q('INSERT INTO "Employees_Master_Audit" ("CompanyID",action,actor_user_id,payload_json) VALUES (?,?,?,?)',
      (company_id, 'UPSERT', d.get('actor_user_id', 1), json_dumps_safe(d)))

    return jsonify({'status': 'ok', 'CompanyID': company_id, 'mode': 'update' if existing else 'create'})


@app.delete('/employees/<company_id>')
def employees_delete(company_id):
    exists = q('SELECT "CompanyID" FROM "Employees_Master" WHERE "CompanyID"=?', (company_id,), one=True)
    if not exists:
        return jsonify({'status': 'error', 'error': 'CompanyID not found'}), 404
    q('UPDATE "Employees_Master" SET "Active"=\'No\', updated_at=CURRENT_TIMESTAMP WHERE "CompanyID"=?', (company_id,))
    q('INSERT INTO "Employees_Master_Audit" ("CompanyID",action,actor_user_id,payload_json) VALUES (?,?,?,?)',
      (company_id, 'SOFT_DELETE', 1, '{"Active":"No"}'))
    return jsonify({'status': 'ok', 'CompanyID': company_id, 'policy': 'soft-delete'})


@app.post('/employees/add')
def employees_add():
    d = normalize_employee_payload(request.json or {})
    company_id = (d.get('CompanyID') or '').strip()
    name = (d.get('Name') or '').strip()
    cnic = (d.get('CNIC_No.') or '').strip()
    dept = (d.get('Department') or '').strip()
    desg = (d.get('Designation') or '').strip()
    unit_id = (d.get('Unit_ID') or '').strip()
    active = (d.get('Active') or 'Yes').strip() or 'Yes'

    missing = []
    if not company_id: missing.append('CompanyID')
    if not name: missing.append('Name')
    if not cnic: missing.append('CNIC_No.')
    if not dept: missing.append('Department')
    if not desg: missing.append('Designation')
    if not unit_id: missing.append('Unit_ID')
    if missing:
        return jsonify({'status': 'error', 'error': 'Missing mandatory fields', 'missing_fields': missing}), 400
    if active not in ('Yes', 'No'):
        return jsonify({'status': 'error', 'error': 'Active must be Yes/No'}), 400
    if not unit_exists(unit_id):
        return jsonify({'status': 'error', 'error': 'Unit_ID not found in Units/Rooms master'}), 400

    exists = q('SELECT "CompanyID" FROM "Employees_Master" WHERE "CompanyID"=?', (company_id,), one=True)
    if exists:
        return jsonify({'status': 'error', 'error': 'Duplicate CompanyID'}), 409

    insert_cols = [
        'CompanyID', 'Name', "Father's Name", 'CNIC_No.', 'Mobile_No.', 'Department', 'Section', 'Sub Section',
        'Designation', 'Employee Type', 'Colony Type', 'Block Floor', 'Room No', 'Shared Room',
        'Join Date', 'Leave Date', 'Active', 'Iron Cot', 'Single Bed', 'Double Bed', 'Mattress', 'Sofa Set',
        'Bed Sheet', 'Wardrobe', 'Centre Table', 'Wooden Chair', 'Dinning Table', 'Dinning Chair', 'Side Table',
        'Fridge', 'Water Dispenser', 'Washing Machine', 'Air Cooler', 'A/C', 'LED', 'Gyser', 'Electric Kettle',
        'Wifi Rtr', 'Water Bottle', 'LPG cylinder', 'Gas Stove', 'Crockery', 'Kitchen Cabinet', 'Mug', 'Bucket',
        'Mirror', 'Dustbin', 'Remarks', 'Unit_ID'
    ]
    values = [
        company_id, name, d.get("Father's Name"), cnic, d.get('Mobile_No.'), dept, d.get('Section'), d.get('Sub Section'),
        desg, d.get('Employee Type'), d.get('Colony Type'), d.get('Block Floor'), d.get('Room No'), d.get('Shared Room'),
        d.get('Join Date'), d.get('Leave Date'), active, d.get('Iron Cot'), d.get('Single Bed'), d.get('Double Bed'), d.get('Mattress'), d.get('Sofa Set'),
        d.get('Bed Sheet'), d.get('Wardrobe'), d.get('Centre Table'), d.get('Wooden Chair'), d.get('Dinning Table'), d.get('Dinning Chair'), d.get('Side Table'),
        d.get('Fridge'), d.get('Water Dispenser'), d.get('Washing Machine'), d.get('Air Cooler'), d.get('A/C'), d.get('LED'), d.get('Gyser'), d.get('Electric Kettle'),
        d.get('Wifi Rtr'), d.get('Water Bottle'), d.get('LPG cylinder'), d.get('Gas Stove'), d.get('Crockery'), d.get('Kitchen Cabinet'), d.get('Mug'), d.get('Bucket'),
        d.get('Mirror'), d.get('Dustbin'), d.get('Remarks') or d.get('Remark'), unit_id
    ]
    quoted = ','.join([f'"{c}"' for c in insert_cols])
    placeholders = ','.join(['?'] * len(insert_cols))
    q(f'INSERT INTO "Employees_Master" ({quoted}) VALUES ({placeholders})', tuple(values))

    q('INSERT INTO "Employees_Master_Audit" ("CompanyID",action,actor_user_id,payload_json) VALUES (?,?,?,?)',
      (company_id, 'CREATE', d.get('actor_user_id', 1), json_dumps_safe(d)))

    return jsonify({'status': 'ok', 'CompanyID': company_id})


@app.patch('/employees/<company_id>')
def employees_edit(company_id):
    d = request.json or {}
    current = q('SELECT * FROM "Employees_Master" WHERE "CompanyID"=?', (company_id,), one=True)
    if not current:
        return jsonify({'status': 'error', 'error': 'CompanyID not found'}), 404

    if 'CompanyID' in d and (d.get('CompanyID') or '').strip() != company_id:
        return jsonify({'status': 'error', 'error': 'CompanyID immutable'}), 400
    if 'CNIC_No.' in d and (d.get('CNIC_No.') or '').strip() != (current.get('CNIC_No.') or ''):
        return jsonify({'status': 'error', 'error': 'CNIC_No. immutable'}), 400

    residence_fields = {'Colony Type', 'Block Floor', 'Room No', 'Unit_ID'}
    changing_residence = any(k in d for k in residence_fields)
    if changing_residence:
        month_cycle = (d.get('month_cycle') or '').strip()
        if not month_cycle or not is_next_month(month_cycle):
            return jsonify({'status': 'error', 'error': 'Residence fields editable only for NEXT month; pass month_cycle'}), 400
        target_unit = (d.get('Unit_ID') or current.get('Unit_ID') or '').strip()
        if not unit_exists(target_unit):
            return jsonify({'status': 'error', 'error': 'Unit_ID not found in Units/Rooms master'}), 400
        q('''INSERT INTO util_employee_residence_snapshot(month_cycle,"CompanyID","Colony Type","Block Floor","Room No","Unit_ID")
             VALUES(?,?,?,?,?,?)
             ON CONFLICT(month_cycle,"CompanyID") DO UPDATE SET
             "Colony Type"=excluded."Colony Type", "Block Floor"=excluded."Block Floor", "Room No"=excluded."Room No", "Unit_ID"=excluded."Unit_ID", updated_at=CURRENT_TIMESTAMP''',
          (month_cycle, company_id, d.get('Colony Type', current.get('Colony Type')), d.get('Block Floor', current.get('Block Floor')),
           d.get('Room No', current.get('Room No')), target_unit))

    allowed = [
        'Name', "Father's Name", 'Mobile_No.', 'Department', 'Section', 'Sub Section', 'Designation', 'Employee Type',
        'Join Date', 'Leave Date', 'Active', 'Shared Room', 'Iron Cot', 'Single Bed', 'Double Bed', 'Mattress',
        'Sofa Set', 'Bed Sheet', 'Wardrobe', 'Centre Table', 'Wooden Chair', 'Dinning Table', 'Dinning Chair',
        'Side Table', 'Fridge', 'Water Dispenser', 'Washing Machine', 'Air Cooler', 'A/C', 'LED', 'Gyser',
        'Electric Kettle', 'Wifi Rtr', 'Water Bottle', 'LPG cylinder', 'Gas Stove', 'Crockery', 'Kitchen Cabinet',
        'Mug', 'Bucket', 'Mirror', 'Dustbin', 'Remarks'
    ]
    updates = []
    params = []
    for k in allowed:
        if k in d:
            updates.append(f'"{k}"=?')
            params.append(d.get(k))
    if updates:
        updates.append('updated_at=CURRENT_TIMESTAMP')
        params.append(company_id)
        q(f'UPDATE "Employees_Master" SET {", ".join(updates)} WHERE "CompanyID"=?', tuple(params))

    q('INSERT INTO "Employees_Master_Audit" ("CompanyID",action,actor_user_id,payload_json) VALUES (?,?,?,?)',
      (company_id, 'UPDATE', d.get('actor_user_id', 1), json_dumps_safe(d)))

    return jsonify({'status': 'ok', 'CompanyID': company_id})


@app.get('/rooms')
def rooms_list():
    m = request.args.get('month_cycle')
    unit_id = request.args.get('unit_id')
    category = request.args.get('category')

    sql = '''SELECT id, month_cycle, unit_id, category, block_floor, room_no, created_at
             FROM util_unit_room_snapshot WHERE 1=1'''
    params = []
    if m:
        sql += ' AND month_cycle=?'
        params.append(m)
    if unit_id:
        sql += ' AND unit_id=?'
        params.append(unit_id)
    if category:
        sql += ' AND category=?'
        params.append(category)
    sql += ' ORDER BY month_cycle DESC, unit_id, room_no'

    rows = q(sql, tuple(params))
    counts = q('''SELECT month_cycle, unit_id, COUNT(DISTINCT room_no) AS rooms_count
                  FROM util_unit_room_snapshot
                  WHERE (? IS NULL OR month_cycle=?)
                    AND (? IS NULL OR unit_id=?)
                  GROUP BY month_cycle, unit_id
                  ORDER BY month_cycle DESC, unit_id''', (m, m, unit_id, unit_id))

    return jsonify({'status': 'ok', 'rows': rows, 'rooms_count': counts})


@app.post('/rooms/upsert')
def rooms_upsert():
    d = request.json or {}
    m = (d.get('month_cycle') or '').strip()
    unit_id = (d.get('unit_id') or '').strip()
    category = (d.get('category') or '').strip()
    room_no = (d.get('room_no') or '').strip()
    block_floor = (d.get('block_floor') or '').strip()

    if not (m and unit_id and category and room_no):
        return jsonify({'status': 'error', 'error': 'month_cycle, unit_id, category, room_no are required'}), 400
    if category not in FREE_UNITS_PER_ROOM:
        return jsonify({'status': 'error', 'error': f'Invalid category: {category}'}), 400

    q('''INSERT INTO util_unit_room_snapshot(month_cycle,unit_id,category,block_floor,room_no)
         VALUES(?,?,?,?,?)
         ON CONFLICT(month_cycle,unit_id,room_no) DO UPDATE SET
         category=excluded.category, block_floor=excluded.block_floor''',
      (m, unit_id, category, block_floor, room_no))

    return jsonify({'status': 'ok'})


@app.delete('/rooms/<int:row_id>')
def rooms_delete(row_id):
    q('DELETE FROM util_unit_room_snapshot WHERE id=?', (row_id,))
    return jsonify({'status': 'ok', 'id': row_id})


@app.get('/meter-reading/latest/<unit_id>')
def meter_reading_latest(unit_id):
    row = q('''SELECT month_cycle, unit_id, previous_reading, current_reading, rollover_flag, source_ref, updated_at
               FROM util_meter_reading_monthly
               WHERE unit_id=?
               ORDER BY month_cycle DESC, id DESC
               LIMIT 1''', (unit_id,), one=True)
    if not row:
        return jsonify({'status': 'ok', 'row': None})
    return jsonify({'status': 'ok', 'row': row})


@app.post('/meter-reading/upsert')
def meter_reading_upsert():
    d = request.json or {}
    m = (d.get('month_cycle') or '').strip()
    unit_id = (d.get('unit_id') or '').strip()
    source_ref = (d.get('source_ref') or '').strip() or None
    rollover_flag = 1 if str(d.get('rollover_flag') or '').strip().lower() in ('1', 'true', 'yes') else 0

    if not m or not unit_id:
        return jsonify({'status': 'error', 'error': 'month_cycle and unit_id are required'}), 400

    try:
        prev = float(d.get('previous_reading') or 0)
        curr = float(d.get('current_reading') or 0)
    except Exception:
        return jsonify({'status': 'error', 'error': 'previous_reading and current_reading must be numeric'}), 400

    if prev < 0 or curr < 0:
        return jsonify({'status': 'error', 'error': 'readings cannot be negative'}), 400

    if curr < prev and not rollover_flag:
        return jsonify({'status': 'error', 'error': 'Current_Reading must be >= Previous_Reading unless rollover_flag=1'}), 400

    if curr >= prev:
        net_units = curr - prev
    else:
        # minimal rollover handling (meter reset)
        meter_max = float(d.get('meter_max') or 10000)
        net_units = (meter_max - prev) + curr

    q('''INSERT INTO util_meter_reading_monthly(month_cycle,unit_id,previous_reading,current_reading,rollover_flag,source_ref)
         VALUES(?,?,?,?,?,?)
         ON CONFLICT(month_cycle,unit_id) DO UPDATE SET
         previous_reading=excluded.previous_reading,
         current_reading=excluded.current_reading,
         rollover_flag=excluded.rollover_flag,
         source_ref=excluded.source_ref,
         updated_at=CURRENT_TIMESTAMP''',
      (m, unit_id, prev, curr, rollover_flag, source_ref))

    q('''INSERT INTO util_meter_unit_monthly(month_cycle,unit_id,meter_units,source_ref)
         VALUES(?,?,?,?)
         ON CONFLICT(month_cycle,unit_id) DO UPDATE SET
         meter_units=excluded.meter_units,
         source_ref=excluded.source_ref,
         updated_at=CURRENT_TIMESTAMP''',
      (m, unit_id, net_units, source_ref or 'from_reading'))

    return jsonify({'status': 'ok', 'month_cycle': m, 'unit_id': unit_id, 'net_units': round(net_units, 4), 'rollover_flag': rollover_flag})


@app.get('/meter-unit')
def meter_unit_list():
    m = (request.args.get('month_cycle') or '').strip()
    unit_id = (request.args.get('unit_id') or '').strip()
    sql = '''SELECT month_cycle, unit_id, meter_units, source_ref, updated_at
             FROM util_meter_unit_monthly WHERE 1=1'''
    params = []
    if m:
        sql += ' AND month_cycle=?'
        params.append(m)
    if unit_id:
        sql += ' AND unit_id LIKE ?'
        params.append(f'%{unit_id}%')
    sql += ' ORDER BY month_cycle DESC, unit_id'
    rows = q(sql, tuple(params))
    return jsonify({'status': 'ok', 'rows': rows})


@app.post('/meter-unit/upsert')
def meter_unit_upsert():
    d = request.json or {}
    m = (d.get('month_cycle') or '').strip()
    unit_id = (d.get('unit_id') or '').strip()
    meter_units = float(d.get('meter_units') or 0)
    source_ref = (d.get('source_ref') or '').strip() or None

    if not (m and unit_id):
        return jsonify({'status': 'error', 'error': 'month_cycle and unit_id required'}), 400
    if meter_units < 0:
        return jsonify({'status': 'error', 'error': 'meter_units cannot be negative'}), 400

    q('''INSERT INTO util_meter_unit_monthly(month_cycle,unit_id,meter_units,source_ref)
         VALUES(?,?,?,?)
         ON CONFLICT(month_cycle,unit_id) DO UPDATE SET
         meter_units=excluded.meter_units, source_ref=excluded.source_ref, updated_at=CURRENT_TIMESTAMP''',
      (m, unit_id, meter_units, source_ref))

    return jsonify({'status': 'ok'})


@app.get('/occupancy')
def occupancy_list():
    m = request.args.get('month_cycle')
    unit_id = request.args.get('unit_id')
    category = request.args.get('category')

    sql = '''SELECT o.id, o.month_cycle, o.category, o.block_floor, o.room_no, o.unit_id,
                    o.employee_id,
                    e."Name" AS employee_name,
                    e."Department" AS department,
                    e."Designation" AS designation,
                    o.active_days, o.created_at, o.updated_at
             FROM util_occupancy_monthly o
             LEFT JOIN "Employees_Master" e ON e."CompanyID" = o.employee_id
             WHERE 1=1'''
    params = []
    if m:
        sql += ' AND o.month_cycle=?'
        params.append(m)
    if unit_id:
        sql += ' AND o.unit_id=?'
        params.append(unit_id)
    if category:
        sql += ' AND o.category=?'
        params.append(category)
    sql += ' ORDER BY o.month_cycle DESC, o.unit_id, o.employee_id'

    return jsonify({'status': 'ok', 'rows': q(sql, tuple(params))})


@app.post('/occupancy/upsert')
def occupancy_upsert():
    d = request.json or {}
    m = (d.get('month_cycle') or '').strip()
    category = (d.get('category') or '').strip()
    block_floor = (d.get('block_floor') or '').strip()
    room_no = (d.get('room_no') or '').strip()
    unit_id = (d.get('unit_id') or '').strip()
    employee_id = (d.get('employee_id') or d.get('CompanyID') or '').strip()
    active_days = int(d.get('active_days') or 0)

    if not (m and category and room_no and unit_id and employee_id):
        return jsonify({'status': 'error', 'error': 'month_cycle, category, room_no, unit_id, CompanyID/employee_id are required'}), 400
    if category not in FREE_UNITS_PER_ROOM:
        return jsonify({'status': 'error', 'error': f'Invalid category: {category}'}), 400
    if active_days < 0:
        return jsonify({'status': 'error', 'error': 'active_days cannot be negative'}), 400

    emp = q('''SELECT "CompanyID", "Name", "Department", "Designation", COALESCE("Active",'Yes') AS active
               FROM "Employees_Master" WHERE "CompanyID"=?''', (employee_id,), one=True)
    if not emp:
        return jsonify({'status': 'error', 'error': 'CompanyID not found in Employees_Master'}), 400
    if (emp.get('active') or 'Yes') != 'Yes':
        return jsonify({'status': 'error', 'error': 'Inactive employee cannot be used in occupancy/billing'}), 400

    existing = q('SELECT unit_id FROM util_occupancy_monthly WHERE month_cycle=? AND employee_id=?', (m, employee_id), one=True)
    if existing and (existing.get('unit_id') != unit_id):
        return jsonify({'status': 'error', 'error': 'Employee already assigned to another Unit_ID in this month'}), 400

    room_ref = q('''SELECT id FROM util_unit_room_snapshot
                    WHERE month_cycle=? AND unit_id=? AND room_no=?''', (m, unit_id, room_no), one=True)
    if not room_ref:
        return jsonify({'status': 'error', 'error': 'Unit_ID/Room_No not found in locked /rooms snapshot for month'}), 400

    try:
        q('''INSERT INTO util_occupancy_monthly(month_cycle,category,block_floor,room_no,unit_id,employee_id,active_days)
             VALUES(?,?,?,?,?,?,?)
             ON CONFLICT(month_cycle,employee_id) DO UPDATE SET
             category=excluded.category, block_floor=excluded.block_floor, room_no=excluded.room_no,
             unit_id=excluded.unit_id, active_days=excluded.active_days, updated_at=CURRENT_TIMESTAMP''',
          (m, category, block_floor, room_no, unit_id, employee_id, active_days))
    except sqlite3.IntegrityError as e:
        return jsonify({'status': 'error', 'error': str(e)}), 400

    return jsonify({'status': 'ok', 'employee_name': emp['Name'], 'department': emp['Department'], 'designation': emp['Designation']})


@app.delete('/occupancy/<int:row_id>')
def occupancy_delete(row_id):
    q('DELETE FROM util_occupancy_monthly WHERE id=?', (row_id,))
    return jsonify({'status': 'ok', 'id': row_id})


def _compute_elec_for_month(con, month_cycle, zero_attendance_policy='zero'):
    cur = con.cursor()

    rate_row = cur.execute('SELECT elec_rate FROM util_rate_monthly WHERE month_cycle=?', (month_cycle,)).fetchone()
    elec_rate = float(rate_row['elec_rate']) if rate_row else 0.0

    units = cur.execute('SELECT DISTINCT unit_id FROM util_meter_unit_monthly WHERE month_cycle=?', (month_cycle,)).fetchall()

    cur.execute('DELETE FROM util_elec_employee_share_monthly WHERE month_cycle=?', (month_cycle,))
    cur.execute('DELETE FROM util_elec_unit_monthly_result WHERE month_cycle=?', (month_cycle,))

    for ur in units:
        unit_id = ur['unit_id']

        usage_row = cur.execute('SELECT meter_units FROM util_meter_unit_monthly WHERE month_cycle=? AND unit_id=?', (month_cycle, unit_id)).fetchone()
        usage_units = float(usage_row['meter_units']) if usage_row else 0.0

        room_rows = cur.execute('''SELECT DISTINCT room_no, category FROM util_unit_room_snapshot
                                   WHERE month_cycle=? AND unit_id=?''', (month_cycle, unit_id)).fetchall()
        rooms_count = len(room_rows)
        category = room_rows[0]['category'] if room_rows else 'Family C'
        free_per_room = float(FREE_UNITS_PER_ROOM.get(category, 0.0))
        unit_free_units = rooms_count * free_per_room
        net_units = max(0.0, usage_units - unit_free_units)
        unit_amount = round(net_units * elec_rate, 2)

        occ_rows = cur.execute('''SELECT employee_id, active_days FROM util_occupancy_monthly
                                  WHERE month_cycle=? AND unit_id=?
                                  ORDER BY employee_id''', (month_cycle, unit_id)).fetchall()
        total_attendance = sum(float(r['active_days'] or 0) for r in occ_rows)

        cur.execute('''INSERT INTO util_elec_unit_monthly_result
                       (month_cycle,unit_id,category,usage_units,rooms_count,free_per_room,unit_free_units,net_units,elec_rate,unit_amount,total_attendance)
                       VALUES(?,?,?,?,?,?,?,?,?,?,?)''',
                    (month_cycle, unit_id, category, usage_units, rooms_count, free_per_room, unit_free_units, net_units, elec_rate, unit_amount, total_attendance))

        if not occ_rows:
            continue

        if total_attendance <= 0:
            # Possession fallback: recover full unit amount even when attendance is zero
            if unit_amount > 0 and occ_rows:
                per_head_share = round(unit_amount / len(occ_rows), 2)
                per_head_units = round(net_units / len(occ_rows), 4)

                shares = []
                for r in occ_rows:
                    shares.append({
                        'employee_id': r['employee_id'],
                        'attendance': float(r['active_days'] or 0),
                        'share_amount': per_head_share,
                        'share_units': per_head_units,
                        'allocation_method': 'forced_possession_split'
                    })

                # Remainder correction to guarantee exact recovery
                distributed = round(sum(s['share_amount'] for s in shares), 2)
                remainder = round(unit_amount - distributed, 2)
                shares[-1]['share_amount'] = round(shares[-1]['share_amount'] + remainder, 2)

                for s in shares:
                    cur.execute('''INSERT INTO util_elec_employee_share_monthly
                                   (month_cycle,unit_id,employee_id,attendance,share_amount,share_units,allocation_method)
                                   VALUES(?,?,?,?,?,?,?)''',
                                (month_cycle, unit_id, s['employee_id'], s['attendance'], s['share_amount'], s['share_units'], s['allocation_method']))
            else:
                # unit_amount == 0, keep zero shares
                for r in occ_rows:
                    cur.execute('''INSERT INTO util_elec_employee_share_monthly
                                   (month_cycle,unit_id,employee_id,attendance,share_amount,share_units,allocation_method)
                                   VALUES(?,?,?,?,?,?,?)''',
                                (month_cycle, unit_id, r['employee_id'], float(r['active_days'] or 0), 0.0, 0.0, 'attendance_split'))
            continue

        per_att_rate_amount = unit_amount / total_attendance if total_attendance else 0.0
        per_att_rate_units = net_units / total_attendance if total_attendance else 0.0

        shares = []
        for r in occ_rows:
            att = float(r['active_days'] or 0)
            shares.append({
                'employee_id': r['employee_id'],
                'attendance': att,
                'share_amount': round(att * per_att_rate_amount, 2),
                'share_units': round(att * per_att_rate_units, 4)
            })

        if shares:
            distributed = round(sum(s['share_amount'] for s in shares), 2)
            remainder = round(unit_amount - distributed, 2)
            shares[-1]['share_amount'] = round(shares[-1]['share_amount'] + remainder, 2)

        for s in shares:
            cur.execute('''INSERT INTO util_elec_employee_share_monthly
                           (month_cycle,unit_id,employee_id,attendance,share_amount,share_units,allocation_method)
                           VALUES(?,?,?,?,?,?,?)''',
                        (month_cycle, unit_id, s['employee_id'], s['attendance'], s['share_amount'], s['share_units'], 'attendance_split'))


@app.post('/billing/elec/compute')
def billing_elec_compute():
    d = request.json or {}
    m = (d.get('month_cycle') or '').strip()
    if not m:
        return jsonify({'status': 'error', 'error': 'month_cycle is required'}), 400

    policy = (d.get('zero_attendance_policy') or 'zero').strip().lower()
    if policy not in ('zero', 'error'):
        return jsonify({'status': 'error', 'error': 'zero_attendance_policy must be zero|error'}), 400

    try:
        exec_txn(lambda con: _compute_elec_for_month(con, m, policy))
        return jsonify({'status': 'ok', 'month_cycle': m})
    except ValueError as e:
        return jsonify({'status': 'error', 'error': str(e)}), 400


def _rebuild_school_van_monthly_charge(month_cycle: str):
    exp = q('''SELECT amount FROM monthly_variable_expenses
               WHERE month_cycle=? AND expense_type='SCHOOL_VAN_TOTAL' ''', (month_cycle,), one=True)
    if not exp:
        raise ValueError(f'SCHOOL_VAN_TOTAL expense missing for {month_cycle}')

    total_van_expense = float(exp.get('amount') or 0)
    if total_van_expense < 0:
        raise ValueError('SCHOOL_VAN_TOTAL cannot be negative')

    active_children = q('''SELECT employee_id, child_name, school_name, class_level
                           FROM util_school_van_student
                           WHERE UPPER(COALESCE(van_status,'ACTIVE'))='ACTIVE'
                           ORDER BY employee_id, child_name''')

    total_active_children = len(active_children)
    if total_active_children <= 0:
        raise ValueError('Cannot distribute expense, no active children.')

    per_child_rate = round(total_van_expense / total_active_children, 2)

    q('DELETE FROM util_school_van_monthly_charge WHERE month_cycle=?', (month_cycle,))

    distributed = 0.0
    for i, c in enumerate(active_children):
        amount = per_child_rate
        # last-child remainder correction to recover exact total
        if i == total_active_children - 1:
            amount = round(total_van_expense - distributed, 2)
        distributed = round(distributed + amount, 2)

        q('''INSERT INTO util_school_van_monthly_charge
             (month_cycle,employee_id,child_name,school_name,class_level,service_mode,rate,amount,charged_flag)
             VALUES(?,?,?,?,?,?,?,?,?)''',
          (month_cycle, c.get('employee_id'), c.get('child_name'), c.get('school_name'), c.get('class_level'),
           'derived_from_total_expense', per_child_rate, amount, 1))


@app.post('/billing/run')
def billing_run():
    d = request.json
    m = d['month_cycle']
    lock_err = reject_if_month_locked(m)
    if lock_err:
        return lock_err
    run_key = d.get('run_key') or f'{m}:{uuid.uuid4().hex[:8]}'
    actor = d.get('actor_user_id', 1)

    q("INSERT OR IGNORE INTO util_billing_run(month_cycle,run_key,run_status,started_by_user_id) VALUES(?,?, 'DRAFT',?)", (m, run_key, actor))
    run = q('SELECT id FROM util_billing_run WHERE month_cycle=? AND run_key=?', (m, run_key), one=True)
    run_id = run['id']

    q('DELETE FROM util_billing_line WHERE billing_run_id=?', (run_id,))

    elec_share_rows = q('SELECT COUNT(1) AS c FROM util_elec_employee_share_monthly WHERE month_cycle=?', (m,), one=True)
    if (elec_share_rows or {}).get('c', 0) > 0:
        q('''INSERT INTO util_billing_line(billing_run_id,month_cycle,employee_id,utility_type,qty,rate,amount,source_ref)
             SELECT ?, month_cycle, employee_id, 'ELEC', share_units,
                    CASE WHEN share_units=0 THEN 0 ELSE ROUND(share_amount/share_units,4) END,
                    share_amount, 'util_elec_employee_share_monthly'
             FROM util_elec_employee_share_monthly WHERE month_cycle=?
             ON CONFLICT(billing_run_id,employee_id,utility_type)
             DO UPDATE SET qty=excluded.qty, rate=excluded.rate, amount=excluded.amount, source_ref=excluded.source_ref''', (run_id, m))
    else:
        q('''INSERT INTO util_billing_line(billing_run_id,month_cycle,employee_id,utility_type,qty,rate,amount,source_ref)
             SELECT ?, month_cycle, employee_id, 'ELEC', elec_units,
                    CASE WHEN elec_units=0 THEN 0 ELSE ROUND(elec_amount/elec_units,4) END,
                    elec_amount, 'util_formula_result'
             FROM util_formula_result WHERE month_cycle=?
             ON CONFLICT(billing_run_id,employee_id,utility_type)
             DO UPDATE SET qty=excluded.qty, rate=excluded.rate, amount=excluded.amount, source_ref=excluded.source_ref''', (run_id, m))

    q('''INSERT INTO util_billing_line(billing_run_id,month_cycle,employee_id,utility_type,qty,rate,amount,source_ref)
         SELECT ?, month_cycle, employee_id, 'WATER_GENERAL', chargeable_general_water_liters,
                CASE WHEN chargeable_general_water_liters=0 THEN 0 ELSE ROUND(water_general_amount/chargeable_general_water_liters,4) END,
                water_general_amount, 'util_formula_result'
         FROM util_formula_result WHERE month_cycle=?
         ON CONFLICT(billing_run_id,employee_id,utility_type)
         DO UPDATE SET qty=excluded.qty, rate=excluded.rate, amount=excluded.amount, source_ref=excluded.source_ref''', (run_id, m))

    q('''INSERT INTO util_billing_line(billing_run_id,month_cycle,employee_id,utility_type,qty,rate,amount,source_ref)
         SELECT ?, month_cycle, employee_id, 'WATER_DRINKING', billed_liters, rate, amount, 'util_drinking_formula_result'
         FROM util_drinking_formula_result WHERE month_cycle=?
         ON CONFLICT(billing_run_id,employee_id,utility_type)
         DO UPDATE SET qty=excluded.qty, rate=excluded.rate, amount=excluded.amount, source_ref=excluded.source_ref''', (run_id, m))

    try:
        _rebuild_school_van_monthly_charge(m)
    except ValueError as e:
        return jsonify({'status': 'error', 'error': str(e)}), 400

    q('''INSERT INTO util_billing_line(billing_run_id,month_cycle,employee_id,utility_type,qty,rate,amount,source_ref)
         SELECT ?, month_cycle, employee_id, 'SCHOOL_VAN', COUNT(*),
                CASE WHEN COUNT(*)=0 THEN 0 ELSE ROUND(SUM(amount)/COUNT(*),2) END,
                SUM(amount), 'util_school_van_monthly_charge'
         FROM util_school_van_monthly_charge WHERE month_cycle=? GROUP BY month_cycle, employee_id
         ON CONFLICT(billing_run_id,employee_id,utility_type)
         DO UPDATE SET qty=excluded.qty, rate=excluded.rate, amount=excluded.amount, source_ref=excluded.source_ref''', (run_id, m))

    audit_log('billing_run', run_id, 'RUN_REBUILT', actor, None, {'month_cycle': m, 'run_key': run_key}, correlation_id=f'billing:{m}:{run_id}')

    return {'status': 'ok', 'run_id': run_id, 'run_key': run_key}


@app.post('/billing/approve')
def billing_approve():
    # Approval system intentionally removed (non-negotiable governance)
    return jsonify({'status': 'error', 'error': 'approval flow removed; use direct finalize flow'}), 410


@app.post('/billing/lock')
def billing_lock():
    d = request.json or {}
    run_id = d.get('run_id')

    if run_id is None:
        return jsonify({'status': 'error', 'error': 'run_id is required'}), 400

    run = q('SELECT id, month_cycle, run_status FROM util_billing_run WHERE id=?', (run_id,), one=True)
    if not run:
        return jsonify({'status': 'error', 'error': 'billing_run not found'}), 404

    if run['run_status'] != 'APPROVED':
        return jsonify({'status': 'error', 'error': f"Invalid billing_run transition from {run['run_status']}"}), 409

    month = q('SELECT state FROM util_month_cycle WHERE month_cycle=?', (run['month_cycle'],), one=True)
    if not month:
        return jsonify({'status': 'error', 'error': 'month_cycle not found for billing_run'}), 409

    if month['state'] != 'APPROVAL':
        return jsonify({'status': 'error', 'error': 'Month must be in APPROVAL before lock'}), 409

    try:
        before = q('SELECT id, month_cycle, run_status FROM util_billing_run WHERE id=?', (run_id,), one=True)
        q("UPDATE util_billing_run SET run_status='LOCKED' WHERE id=? AND run_status='APPROVED'", (run_id,))
        after = q('SELECT id, month_cycle, run_status FROM util_billing_run WHERE id=?', (run_id,), one=True)
        actor = (request.json or {}).get('actor_user_id', 1)
        audit_log('billing_run', run_id, 'LOCK', actor, before, after, correlation_id=f'billing-lock:{run_id}')
        return jsonify({'status': 'ok', 'run_id': run_id, 'run_status': 'LOCKED'})
    except sqlite3.IntegrityError as e:
        return jsonify({'status': 'error', 'error': str(e)}), 409


def _billing_lines_for_run(run_id):
    rows = q('''SELECT month_cycle, employee_id, utility_type, qty, rate, amount, source_ref
                FROM util_billing_line WHERE billing_run_id=?''', (run_id,))
    if build_line is None:
        return rows
    out = []
    for r in rows:
        bl = build_line(r['month_cycle'], r['employee_id'], r['utility_type'], r.get('qty'), r.get('rate'), r.get('amount'), r.get('source_ref') or '')
        out.append(bl)
    return out


@app.post('/billing/adjustments/create')
def billing_adjustment_create():
    d = request.json or {}
    actor, err = require_admin_from_request(d)
    if err:
        return err

    m = (d.get('month_cycle') or '').strip()
    employee_id = (d.get('employee_id') or '').strip()
    utility_type = (d.get('utility_type') or '').strip().upper()
    reason = (d.get('reason') or '').strip()
    amount_delta = float(d.get('amount_delta') or 0)

    if not m or not employee_id or not utility_type or not reason:
        return jsonify({'status': 'error', 'error': 'month_cycle, employee_id, utility_type, reason are required'}), 400

    if month_state(m) != 'LOCKED':
        return jsonify({'status': 'error', 'error': 'adjustments allowed only for LOCKED month'}), 409

    def _ins(con):
        cur = con.cursor()
        cur.execute('''INSERT INTO util_billing_adjustment(month_cycle,employee_id,utility_type,amount_delta,reason,created_by_user_id,status)
                       VALUES(?,?,?,?,?,?, 'PENDING')''', (m, employee_id, utility_type, amount_delta, reason, actor))
        return cur.lastrowid

    adj_id = exec_txn(_ins)
    audit_log('billing_adjustment', adj_id or f'{m}:{employee_id}:{utility_type}', 'CREATE', actor, None, d, correlation_id=f'adj:{m}:{employee_id}:{utility_type}')
    return jsonify({'status': 'ok', 'adjustment_id': adj_id})


@app.post('/billing/adjustments/approve')
def billing_adjustment_approve():
    # Approval system intentionally removed (non-negotiable governance)
    return jsonify({'status': 'error', 'error': 'adjustment approvals removed'}), 410


@app.get('/billing/adjustments/list')
def billing_adjustment_list():
    m = (request.args.get('month_cycle') or '').strip()
    employee_id = (request.args.get('employee_id') or '').strip()
    sql = 'SELECT * FROM util_billing_adjustment WHERE 1=1'
    params = []
    if m:
        sql += ' AND month_cycle=?'
        params.append(m)
    if employee_id:
        sql += ' AND employee_id=?'
        params.append(employee_id)
    sql += ' ORDER BY id DESC'
    return jsonify({'status': 'ok', 'rows': q(sql, tuple(params))})


def reporting_run_id(month_cycle):
    run = q('''SELECT id, run_status FROM util_billing_run
               WHERE month_cycle=? AND run_status IN ('LOCKED','APPROVED')
               ORDER BY CASE run_status WHEN 'LOCKED' THEN 1 WHEN 'APPROVED' THEN 2 ELSE 3 END, id DESC
               LIMIT 1''', (month_cycle,), one=True)
    return run['id'] if run else None


@app.get('/reports/monthly-summary')
def monthly_summary():
    m = request.args.get('month_cycle')
    run_id = reporting_run_id(m)
    if not run_id:
        return jsonify({'month_cycle': m, 'rows': [], 'error': 'No APPROVED/LOCKED billing run found'}), 404
    rows = q('''SELECT utility_type, ROUND(SUM(amount),2) as total_amount, ROUND(SUM(qty),4) as total_qty
                FROM util_billing_line WHERE billing_run_id=? GROUP BY utility_type ORDER BY utility_type''', (run_id,))
    return jsonify({'month_cycle': m, 'billing_run_id': run_id, 'rows': rows})


@app.get('/reports/recovery')
def recovery():
    m = request.args.get('month_cycle')
    rows = q('''SELECT employee_id, ROUND(SUM(amount),2) as billed
                FROM util_billing_line WHERE month_cycle=? GROUP BY employee_id ORDER BY employee_id''', (m,))
    return jsonify({'month_cycle': m, 'rows': rows})


@app.get('/reports/reconciliation')
def reconciliation_report():
    m = (request.args.get('month_cycle') or '').strip()
    if not m:
        return jsonify({'status': 'error', 'error': 'month_cycle is required'}), 400

    run_id = reporting_run_id(m)
    if not run_id:
        return jsonify({'status': 'error', 'error': 'No APPROVED/LOCKED billing run found'}), 404

    billed_total_row = q('SELECT ROUND(COALESCE(SUM(amount),0),2) AS billed_total FROM util_billing_line WHERE billing_run_id=?', (run_id,), one=True)
    billed_total = float((billed_total_row or {}).get('billed_total') or 0)

    billed_by_utility = q('''SELECT utility_type,
                                    ROUND(COALESCE(SUM(amount),0),2) AS billed_amount
                             FROM util_billing_line
                             WHERE billing_run_id=?
                             GROUP BY utility_type
                             ORDER BY utility_type''', (run_id,))

    billed_by_employee = q('''SELECT employee_id,
                                     ROUND(COALESCE(SUM(amount),0),2) AS billed_amount
                              FROM util_billing_line
                              WHERE billing_run_id=?
                              GROUP BY employee_id
                              ORDER BY employee_id''', (run_id,))

    recovered_total = 0.0
    recovered_by_employee = {}

    # Recovery ledger (required schema: util_recovery_payment.amount_paid)
    try:
        total = q('''SELECT ROUND(COALESCE(SUM(amount_paid),0),2) AS recovered_total
                     FROM util_recovery_payment
                     WHERE month_cycle=?''', (m,), one=True)
        recovered_total = float((total or {}).get('recovered_total') or 0)

        emp_rows = q('''SELECT employee_id, ROUND(COALESCE(SUM(amount_paid),0),2) AS recovered_amount
                        FROM util_recovery_payment
                        WHERE month_cycle=?
                        GROUP BY employee_id''', (m,))
        recovered_by_employee = {str(r.get('employee_id')): float(r.get('recovered_amount') or 0) for r in emp_rows}
    except Exception:
        # keep endpoint stable if table is not yet migrated
        recovered_total = 0.0
        recovered_by_employee = {}

    utility_rows = []
    for b in billed_by_utility:
        utility = b.get('utility_type')
        billed = float(b.get('billed_amount') or 0)
        # Recovery is tracked at employee/month ledger granularity.
        recovered = 0.0
        outstanding = round(billed - recovered, 2)
        utility_rows.append({
            'utility_type': utility,
            'billed_amount': round(billed, 2),
            'recovered_amount': round(recovered, 2),
            'outstanding_amount': round(outstanding, 2),
        })

    employee_rows = []
    for b in billed_by_employee:
        employee_id = str(b.get('employee_id'))
        billed = float(b.get('billed_amount') or 0)
        recovered = float(recovered_by_employee.get(employee_id, 0.0))
        outstanding = round(billed - recovered, 2)
        employee_rows.append({
            'employee_id': employee_id,
            'billed_amount': round(billed, 2),
            'recovered_amount': round(recovered, 2),
            'outstanding_amount': round(outstanding, 2),
        })

    return jsonify({
        'status': 'ok',
        'month_cycle': m,
        'billing_run_id': run_id,
        'summary': {
            'billed_total': round(billed_total, 2),
            'recovered_total': round(float(recovered_total or 0), 2),
            'outstanding_total': round(billed_total - float(recovered_total or 0), 2),
            'recovery_ratio': 0 if billed_total == 0 else round((float(recovered_total or 0) / billed_total) * 100, 2),
        },
        'by_utility': utility_rows,
        'by_employee': employee_rows,
        'notes': [
            'Recovery totals are sourced from util_recovery_payment.amount_paid.'
        ]
    })


@app.post('/recovery/payment')
def recovery_payment_create():
    d = request.json or {}
    actor, err = require_admin_from_request(d)
    if err:
        return err

    employee_id = (d.get('employee_id') or '').strip()
    month_cycle = (d.get('month_cycle') or '').strip()
    payment_method = (d.get('payment_method') or '').strip() or None
    reference_no = (d.get('reference_no') or '').strip() or None
    payment_date = (d.get('payment_date') or '').strip() or datetime.now().strftime('%Y-%m-%d')

    if not employee_id or not month_cycle:
        return jsonify({'status': 'error', 'error': 'employee_id and month_cycle are required'}), 400

    # Employee existence validation
    try:
        emp = q('SELECT "CompanyID" FROM "Employees_Master" WHERE "CompanyID"=? LIMIT 1', (employee_id,), one=True)
        if not emp:
            return jsonify({'status': 'error', 'error': f'employee_id not found: {employee_id}'}), 400
    except Exception:
        return jsonify({'status': 'error', 'error': 'Employees_Master lookup unavailable'}), 400

    try:
        amount_paid = float(d.get('amount_paid') or 0)
    except Exception:
        return jsonify({'status': 'error', 'error': 'amount_paid must be numeric'}), 400

    if amount_paid <= 0:
        return jsonify({'status': 'error', 'error': 'amount_paid must be > 0'}), 400

    def _ins(con):
        cur = con.cursor()
        cur.execute('''INSERT INTO util_recovery_payment(employee_id, month_cycle, amount_paid, payment_date, payment_method, reference_no)
                       VALUES(?,?,?,?,?,?)''', (employee_id, month_cycle, amount_paid, payment_date, payment_method, reference_no))
        return cur.lastrowid

    payment_id = exec_txn(_ins)
    audit_log('recovery_payment', payment_id, 'CREATE', actor, None, {
        'employee_id': employee_id,
        'month_cycle': month_cycle,
        'amount_paid': amount_paid,
        'payment_date': payment_date,
        'payment_method': payment_method,
        'reference_no': reference_no,
    }, correlation_id=f'recovery:{month_cycle}:{employee_id}:{payment_id}')

    return jsonify({'status': 'ok', 'payment_id': payment_id})


@app.get('/billing/fingerprint')
def billing_fingerprint():
    m = (request.args.get('month_cycle') or '').strip()
    run_id = reporting_run_id(m)
    if not run_id:
        return jsonify({'status': 'error', 'error': 'No APPROVED/LOCKED billing run found'}), 404

    lines = _billing_lines_for_run(run_id)
    if deterministic_fingerprint is None:
        rows = sorted(lines, key=lambda x: (x.get('month_cycle'), x.get('employee_id'), x.get('utility_type')))
        payload = '\n'.join([f"{r.get('month_cycle')}|{r.get('employee_id')}|{r.get('utility_type')}|{r.get('qty')}|{r.get('rate')}|{r.get('amount')}|{r.get('source_ref')}" for r in rows])
    else:
        payload = deterministic_fingerprint(lines)

    import hashlib
    fp = hashlib.sha256(payload.encode('utf-8')).hexdigest()
    return jsonify({'status': 'ok', 'month_cycle': m, 'billing_run_id': run_id, 'lines_count': len(lines), 'fingerprint_sha256': fp})


@app.get('/reports/van')
def van_report():
    m = request.args.get('month_cycle')
    rows = q('''SELECT employee_id, child_name, school_name, class_level, amount
                FROM util_school_van_monthly_charge WHERE month_cycle=? ORDER BY employee_id, child_name''', (m,))
    return jsonify({'month_cycle': m, 'rows': rows})


@app.get('/reports/elec-summary')
def elec_summary_report():
    m = request.args.get('month_cycle')
    unit_id = request.args.get('unit_id')

    unit_sql = '''SELECT month_cycle, unit_id, category, usage_units, rooms_count,
                         unit_free_units, net_units, elec_rate, unit_amount, total_attendance
                  FROM util_elec_unit_monthly_result
                  WHERE month_cycle=?'''
    params = [m]
    if unit_id:
        unit_sql += ' AND unit_id=?'
        params.append(unit_id)
    unit_sql += ' ORDER BY unit_id'

    share_sql = '''SELECT s.month_cycle, s.unit_id, s.employee_id, e."Name" AS employee_name,
                          s.attendance, s.share_units, s.share_amount
                   FROM util_elec_employee_share_monthly s
                   LEFT JOIN "Employees_Master" e ON e."CompanyID"=s.employee_id
                   WHERE s.month_cycle=?'''
    share_params = [m]
    if unit_id:
        share_sql += ' AND s.unit_id=?'
        share_params.append(unit_id)
    share_sql += ' ORDER BY s.unit_id, s.employee_id'

    return jsonify({'month_cycle': m, 'units': q(unit_sql, tuple(params)), 'employee_shares': q(share_sql, tuple(share_params))})


@app.route('/billing/print/<month_cycle>/<employee_id>')
def billing_print_view(month_cycle, employee_id):
    emp = q(
        '''SELECT "CompanyID" AS employee_id,
                  "Name" AS employee_name,
                  "Department" AS department,
                  "Unit_ID" AS unit_id
           FROM "Employees_Master"
           WHERE "CompanyID"=?''',
        (employee_id,),
        one=True
    )
    if not emp:
        return jsonify({'status': 'error', 'error': 'Employee not found in Employees_Master'}), 404

    lines = q(
        '''SELECT utility_type,
                  COALESCE(qty,0) AS qty,
                  COALESCE(rate,0) AS rate,
                  COALESCE(amount,0) AS amount,
                  source_ref
           FROM util_billing_line
           WHERE month_cycle=? AND employee_id=?
           ORDER BY utility_type''',
        (month_cycle, employee_id)
    )

    van_rows = q(
        '''SELECT child_name, school_name, class_level, service_mode,
                  COALESCE(rate,0) AS rate,
                  COALESCE(amount,0) AS amount
           FROM util_school_van_monthly_charge
           WHERE month_cycle=? AND employee_id=?
           ORDER BY child_name''',
        (month_cycle, employee_id)
    )
    child_names = [r.get('child_name') for r in van_rows if r.get('child_name')]

    elec_share = q(
        '''SELECT s.unit_id,
                  COALESCE(s.attendance,0) AS attendance,
                  COALESCE(s.share_units,0) AS share_units,
                  COALESCE(s.share_amount,0) AS share_amount,
                  COALESCE(u.usage_units,0) AS unit_usage_units,
                  COALESCE(u.unit_free_units,0) AS unit_free_units,
                  COALESCE(u.net_units,0) AS unit_net_units,
                  COALESCE(u.elec_rate,0) AS elec_rate
           FROM util_elec_employee_share_monthly s
           LEFT JOIN util_elec_unit_monthly_result u
                  ON u.month_cycle=s.month_cycle AND u.unit_id=s.unit_id
           WHERE s.month_cycle=? AND s.employee_id=?
           LIMIT 1''',
        (month_cycle, employee_id),
        one=True
    )

    meter = None
    elec_unit_id = (elec_share or {}).get('unit_id') or emp.get('unit_id')
    if elec_unit_id:
        meter = q(
            '''SELECT COALESCE(previous_reading,0) AS previous_reading,
                      COALESCE(current_reading,0) AS current_reading,
                      ROUND(COALESCE(current_reading,0) - COALESCE(previous_reading,0), 4) AS consumed_units
               FROM util_meter_reading_monthly
               WHERE month_cycle=? AND unit_id=?
               LIMIT 1''',
            (month_cycle, elec_unit_id),
            one=True
        )

    water_occ = q(
        '''SELECT category
           FROM util_occupancy_monthly
           WHERE month_cycle=? AND employee_id=?
           LIMIT 1''',
        (month_cycle, employee_id),
        one=True
    )
    water_category = (water_occ or {}).get('category')

    items = []
    for r in lines:
        ut = r['utility_type']
        item = {
            'utility_type': ut,
            'label': ut.replace('_', ' ').title(),
            'qty': float(r.get('qty') or 0),
            'rate': float(r.get('rate') or 0),
            'amount': float(r.get('amount') or 0),
            'meta': {}
        }
        if ut == 'SCHOOL_VAN':
            item['meta']['child_names'] = child_names
            item['meta']['children'] = van_rows
        elif ut == 'ELEC':
            item['meta']['unit_id'] = elec_unit_id
            item['meta']['meter'] = meter or {}
            item['meta']['share'] = elec_share or {}
        elif ut in ('WATER_GENERAL', 'WATER_DRINKING', 'WATER'):
            item['meta']['category'] = water_category
            item['meta']['logic'] = 'Category resolved from util_occupancy_monthly.category'
        items.append(item)

    grand_total = round(sum(float(x.get('amount') or 0) for x in lines), 2)

    bill = {
        'month_cycle': month_cycle,
        'employee_id': employee_id,
        'employee': {
            'id': emp['employee_id'],
            'name': emp['employee_name'],
            'department': emp['department'],
            'unit_id': emp['unit_id'],
        },
        'lines': lines,
        'items': items,
        'totals': {'grand_total': grand_total},
        'school_van': {
            'children': van_rows,
            'child_names': child_names,
            'children_display': ', '.join(child_names) if child_names else ''
        },
        'electricity': {
            'unit_id': elec_unit_id,
            'meter': meter or {},
            'share': elec_share or {}
        },
        'water': {
            'category': water_category,
            'logic': 'Category comes from util_occupancy_monthly.category'
        }
    }

    return render_template('reports/bill_print_view.html', bill=bill, current_date=datetime.now())


@app.get('/export/excel/reconciliation')
def export_excel_reconciliation():
    m = (request.args.get('month_cycle') or '').strip()
    if not m:
        return jsonify({'status': 'error', 'error': 'month_cycle is required'}), 400

    run_id = reporting_run_id(m)
    if not run_id:
        return jsonify({'status': 'error', 'error': 'No APPROVED/LOCKED billing run found'}), 404

    rows = q('''SELECT bl.employee_id,
                       COALESCE(em."Name", '') AS employee_name,
                       COALESCE(em."Unit_ID", '') AS unit_id,
                       ROUND(SUM(CASE WHEN bl.utility_type IN ('ELEC','ELECTRICITY') THEN bl.amount ELSE 0 END),2) AS elec_bill,
                       ROUND(SUM(CASE WHEN bl.utility_type IN ('WATER_GENERAL','WATER_DRINKING') THEN bl.amount ELSE 0 END),2) AS water_bill,
                       ROUND(SUM(CASE WHEN bl.utility_type='SCHOOL_VAN' THEN bl.amount ELSE 0 END),2) AS van_bill,
                       ROUND(SUM(bl.amount),2) AS total_billed
                FROM util_billing_line bl
                LEFT JOIN "Employees_Master" em ON em."CompanyID"=bl.employee_id
                WHERE bl.billing_run_id=?
                GROUP BY bl.employee_id, em."Name", em."Unit_ID"
                ORDER BY bl.employee_id''', (run_id,))

    paid_rows = []
    try:
        paid_rows = q('''SELECT employee_id, ROUND(COALESCE(SUM(amount_paid),0),2) AS amount_paid
                         FROM util_recovery_payment
                         WHERE month_cycle=?
                         GROUP BY employee_id''', (m,))
    except Exception:
        paid_rows = []
    paid_map = {str(r.get('employee_id')): float(r.get('amount_paid') or 0) for r in paid_rows}

    billed_map = {str(r.get('employee_id') or ''): r for r in rows}
    all_employee_ids = sorted(set(list(billed_map.keys()) + list(paid_map.keys())))

    export_rows = []
    for employee_id in all_employee_ids:
        r = billed_map.get(employee_id)
        if r:
            name = r.get('employee_name') or ''
            unit_id = r.get('unit_id') or ''
            elec_bill = round(float(r.get('elec_bill') or 0), 2)
            water_bill = round(float(r.get('water_bill') or 0), 2)
            van_bill = round(float(r.get('van_bill') or 0), 2)
            total_billed = round(float(r.get('total_billed') or 0), 2)
        else:
            emp = q('SELECT COALESCE("Name","") AS employee_name, COALESCE("Unit_ID","") AS unit_id FROM "Employees_Master" WHERE "CompanyID"=? LIMIT 1', (employee_id,), one=True) or {}
            name = emp.get('employee_name') or ''
            unit_id = emp.get('unit_id') or ''
            elec_bill = 0.0
            water_bill = 0.0
            van_bill = 0.0
            total_billed = 0.0

        amount_paid = round(float(paid_map.get(employee_id, 0)), 2)
        balance = round(total_billed - amount_paid, 2)

        export_rows.append({
            'Employee_ID': employee_id,
            'Name': name,
            'Unit_ID': unit_id,
            'Elec_Bill': elec_bill,
            'Water_Bill': water_bill,
            'Van_Bill': van_bill,
            'Total_Billed': total_billed,
            'Amount_Paid': amount_paid,
            'Balance': balance,
        })

    try:
        import pandas as pd
    except Exception:
        return jsonify({'status': 'error', 'error': 'pandas/openpyxl not installed for XLSX export'}), 500

    out = BytesIO()
    df = pd.DataFrame(export_rows, columns=['Employee_ID', 'Name', 'Unit_ID', 'Elec_Bill', 'Water_Bill', 'Van_Bill', 'Total_Billed', 'Amount_Paid', 'Balance'])
    with pd.ExcelWriter(out, engine='openpyxl') as writer:
        df.to_excel(writer, index=False, sheet_name='Reconciliation')
    out.seek(0)
    return send_file(
        out,
        as_attachment=True,
        download_name=f'reconciliation_{m}.xlsx',
        mimetype='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    )


@app.get('/export/excel/monthly-summary')
def export_excel_monthly_summary():
    m = request.args.get('month_cycle')
    run_id = reporting_run_id(m)
    if not run_id:
        return jsonify({'month_cycle': m, 'error': 'No APPROVED/LOCKED billing run found'}), 404
    rows = q('''SELECT utility_type, SUM(qty) as total_qty, SUM(amount) as total_amount
                FROM util_billing_line WHERE billing_run_id=? GROUP BY utility_type ORDER BY utility_type''', (run_id,))
    buf = StringIO(); w = csv.writer(buf)
    w.writerow(['month_cycle', 'utility_type', 'total_qty', 'total_amount'])
    for r in rows:
        w.writerow([m, r['utility_type'], r['total_qty'], r['total_amount']])
    out = BytesIO(buf.getvalue().encode('utf-8')); out.seek(0)
    return send_file(out, as_attachment=True, download_name=f'monthly_summary_{m}.csv', mimetype='text/csv')


@app.get('/export/pdf/monthly-summary')
def export_pdf_monthly_summary():
    m = request.args.get('month_cycle')
    run_id = reporting_run_id(m)
    if not run_id:
        return jsonify({'month_cycle': m, 'error': 'No APPROVED/LOCKED billing run found'}), 404
    rows = q('''SELECT utility_type, SUM(amount) as total_amount
                FROM util_billing_line WHERE billing_run_id=? GROUP BY utility_type ORDER BY utility_type''', (run_id,))
    out = BytesIO(); c = canvas.Canvas(out, pagesize=A4)
    c.drawString(50, 800, f'Monthly Summary - {m}')
    y = 770
    for r in rows:
        c.drawString(50, y, f"{r['utility_type']}: {round(r['total_amount'] or 0, 2)}")
        y -= 20
    c.save(); out.seek(0)
    return send_file(out, as_attachment=True, download_name=f'monthly_summary_{m}.pdf', mimetype='application/pdf')



# -------- Colony Utilities Extras Management (new contract) --------
def ensure_colony_extras_schema():
    ddl = [
        '''CREATE TABLE IF NOT EXISTS employees (
            company_id TEXT PRIMARY KEY,
            name TEXT NULL,
            cnic TEXT NULL,
            section TEXT NULL,
            dept TEXT NULL,
            designation TEXT NULL,
            mobile TEXT NULL,
            residence_type TEXT NULL,
            colony_name TEXT NULL,
            block_floor TEXT NULL,
            room_no TEXT NULL,
            active INTEGER NOT NULL DEFAULT 1,
            notes TEXT NULL
        )''',
        '''CREATE TABLE IF NOT EXISTS units (
            unit_id TEXT PRIMARY KEY,
            residence_type TEXT NULL,
            colony_name TEXT NULL,
            block_floor TEXT NULL,
            room_no TEXT NULL,
            shared_room TEXT NULL,
            notes TEXT NULL
        )''',
        '''CREATE TABLE IF NOT EXISTS meters (
            meter_id TEXT PRIMARY KEY,
            meter_type TEXT NOT NULL,
            meter_scope TEXT NULL,
            unit_id TEXT NULL,
            active INTEGER NOT NULL DEFAULT 1,
            install_date TEXT NULL,
            notes TEXT NULL
        )''',
        '''CREATE TABLE IF NOT EXISTS rates (
            month_cycle TEXT PRIMARY KEY,
            elec_rate NUMERIC(14,2) NOT NULL DEFAULT 0,
            water_rate NUMERIC(14,2) NOT NULL DEFAULT 0,
            drink_rate NUMERIC(14,2) NOT NULL DEFAULT 0,
            entered_by TEXT NULL,
            entry_date TEXT NULL,
            notes TEXT NULL
        )''',
        '''CREATE TABLE IF NOT EXISTS allowances (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL,
            unit_id TEXT NOT NULL,
            free_elec NUMERIC(14,2) NOT NULL DEFAULT 0,
            free_water NUMERIC(14,2) NOT NULL DEFAULT 0,
            free_drink NUMERIC(14,2) NOT NULL DEFAULT 0,
            UNIQUE(month_cycle, unit_id)
        )''',
        '''CREATE TABLE IF NOT EXISTS map_room (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL,
            unit_id TEXT NOT NULL,
            company_id TEXT NOT NULL,
            from_date TEXT NULL,
            to_date TEXT NULL,
            notes TEXT NULL,
            UNIQUE(month_cycle, unit_id, company_id)
        )''',
        '''CREATE TABLE IF NOT EXISTS hr_input (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL,
            company_id TEXT NOT NULL,
            active_days NUMERIC(14,2) NOT NULL DEFAULT 0,
            notes TEXT NULL,
            UNIQUE(month_cycle, company_id)
        )''',
        '''CREATE TABLE IF NOT EXISTS readings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL,
            meter_id TEXT NOT NULL,
            unit_id TEXT NOT NULL,
            reading_date TEXT NULL,
            prev_reading NUMERIC(14,2) NOT NULL DEFAULT 0,
            curr_reading NUMERIC(14,2) NOT NULL DEFAULT 0,
            usage NUMERIC(14,2) NOT NULL DEFAULT 0,
            amount NUMERIC(14,2) NOT NULL DEFAULT 0,
            status TEXT NULL,
            notes TEXT NULL,
            meter_type TEXT NULL,
            UNIQUE(month_cycle, meter_id)
        )''',
        '''CREATE TABLE IF NOT EXISTS ro_drinking (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            month_cycle TEXT NOT NULL,
            unit_id TEXT NOT NULL,
            liters NUMERIC(14,2) NOT NULL DEFAULT 0,
            amount NUMERIC(14,2) NOT NULL DEFAULT 0,
            provided_date TEXT NULL,
            notes TEXT NULL
        )''',
        '''CREATE TABLE IF NOT EXISTS billing_run (
            run_id TEXT PRIMARY KEY,
            month_cycle TEXT NOT NULL,
            status TEXT NOT NULL,
            started_at TEXT NOT NULL,
            finalized_at TEXT NULL,
            fingerprint TEXT NULL
        )''',
        '''CREATE TABLE IF NOT EXISTS billing_rows (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            run_id TEXT NOT NULL,
            month_cycle TEXT NOT NULL,
            company_id TEXT NOT NULL,
            unit_id TEXT NOT NULL,
            water_amt NUMERIC(14,2) NOT NULL DEFAULT 0,
            power_amt NUMERIC(14,2) NOT NULL DEFAULT 0,
            drink_amt NUMERIC(14,2) NOT NULL DEFAULT 0,
            adjustment NUMERIC(14,2) NOT NULL DEFAULT 0,
            total_amt NUMERIC(14,2) NOT NULL DEFAULT 0,
            rounded_2dp NUMERIC(14,2) NOT NULL DEFAULT 0
        )''',
        '''CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            run_id TEXT NULL,
            month_cycle TEXT NOT NULL,
            severity TEXT NOT NULL,
            code TEXT NOT NULL,
            message TEXT NOT NULL,
            ref_json TEXT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )''',
        '''CREATE TABLE IF NOT EXISTS finalized_months (
            month_cycle TEXT PRIMARY KEY,
            finalized_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'''
    ]
    con = get_con(); cur = con.cursor()
    for s in ddl:
        cur.execute(s)
    con.commit(); con.close()


# new UI pages (light neutral templates reuse)
@app.get('/ui/dashboard')
def ui_dashboard():
    return render_template('reports.html')


@app.get('/ui/month-control')
def ui_month_control():
    return render_template('month.html')


@app.get('/ui/masters/employees')
def ui_masters_employees():
    return render_template('employees.html')


@app.get('/ui/masters/units')
def ui_masters_units():
    return render_template('unit_master.html')


@app.get('/ui/masters/meters')
def ui_masters_meters():
    return render_template('meter_master.html')


@app.get('/ui/masters/rates')
def ui_masters_rates():
    return render_template('rates.html')


@app.get('/ui/inputs/mapping')
def ui_inputs_mapping():
    return render_template('rooms.html')


@app.get('/ui/inputs/hr')
def ui_inputs_hr():
    return render_template('occupancy.html')


@app.get('/ui/inputs/readings')
def ui_inputs_readings():
    return render_template('meter_register_ingest.html')


@app.get('/ui/inputs/ro')
def ui_inputs_ro():
    return render_template('van.html')


@app.get('/ui/results/employee-wise')
def ui_results_employee():
    return render_template('employees.html')


@app.get('/ui/results/unit-wise')
def ui_results_unit():
    return render_template('unit_master.html')


@app.get('/ui/logs')
def ui_logs():
    return render_template('reports.html')


@app.get('/ui/finalized-months')
def ui_finalized_months():
    return render_template('month.html')


def month_is_finalized(month_cycle: str):
    r = q('SELECT month_cycle FROM finalized_months WHERE month_cycle=?', (month_cycle,), one=True)
    return bool(r)


def assert_month_editable(month_cycle: str):
    if month_is_finalized(month_cycle):
        return jsonify({'status': 'error', 'error': f'{month_cycle} is finalized and read-only'}), 409
    return None


@app.post('/api/billing/precheck')
def api_billing_precheck():
    d = request.json or {}
    month_cycle = (d.get('month_cycle') or '').strip()
    from domain.billing_engine import run_colony_billing

    hr_rows = q('SELECT month_cycle, company_id, active_days FROM hr_input WHERE month_cycle=?', (month_cycle,))
    map_rows = q('SELECT month_cycle, unit_id, company_id FROM map_room WHERE month_cycle=?', (month_cycle,))
    unit_rows = q('SELECT month_cycle, meter_id, unit_id, meter_type, usage, amount FROM readings WHERE month_cycle=?', (month_cycle,))
    ro_rows = q('SELECT month_cycle, unit_id, liters, amount FROM ro_drinking WHERE month_cycle=?', (month_cycle,))

    out = run_colony_billing(month_cycle, unit_rows, hr_rows, map_rows, ro_rows)
    return jsonify({'status': out['status'], 'stop': out['stop'], 'logs': out['logs'], 'rows_preview': out['billing_rows'][:20]})


@app.post('/api/billing/finalize')
def api_billing_finalize():
    d = request.json or {}
    month_cycle = (d.get('month_cycle') or '').strip()
    edit_err = assert_month_editable(month_cycle)
    if edit_err:
        return edit_err

    from domain.billing_engine import run_colony_billing, build_line, deterministic_fingerprint

    hr_rows = q('SELECT month_cycle, company_id, active_days FROM hr_input WHERE month_cycle=?', (month_cycle,))
    map_rows = q('SELECT month_cycle, unit_id, company_id FROM map_room WHERE month_cycle=?', (month_cycle,))
    unit_rows = q('SELECT month_cycle, meter_id, unit_id, meter_type, usage, amount FROM readings WHERE month_cycle=?', (month_cycle,))
    ro_rows = q('SELECT month_cycle, unit_id, liters, amount FROM ro_drinking WHERE month_cycle=?', (month_cycle,))

    out = run_colony_billing(month_cycle, unit_rows, hr_rows, map_rows, ro_rows)
    run_id = uuid.uuid4().hex[:12]

    def _txn(con):
        cur = con.cursor()
        # idempotent replace for same month
        cur.execute('DELETE FROM billing_rows WHERE month_cycle=?', (month_cycle,))
        cur.execute('DELETE FROM logs WHERE month_cycle=?', (month_cycle,))
        cur.execute('DELETE FROM billing_run WHERE month_cycle=?', (month_cycle,))

        if out['stop']:
            cur.execute('INSERT INTO billing_run(run_id,month_cycle,status,started_at) VALUES(?,?,?,CURRENT_TIMESTAMP)', (run_id, month_cycle, 'failed'))
            for lg in out['logs']:
                cur.execute('''INSERT INTO logs(run_id,month_cycle,severity,code,message,ref_json)
                               VALUES(?,?,?,?,?,?)''', (run_id, month_cycle, lg['severity'], lg['code'], lg['message'], json_dumps_safe(lg.get('ref_json') or {})))
            return {'status': 'failed', 'run_id': run_id}

        # fingerprint deterministic
        lines = []
        for r in out['billing_rows']:
            lines.append(build_line(month_cycle, r['company_id'], 'TOTAL', 1, 1, r['total_amt'], r['unit_id']))
        fp = deterministic_fingerprint(lines)

        cur.execute('INSERT INTO billing_run(run_id,month_cycle,status,started_at,finalized_at,fingerprint) VALUES(?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,?)', (run_id, month_cycle, 'final', fp))

        for r in out['billing_rows']:
            cur.execute('''INSERT INTO billing_rows(run_id,month_cycle,company_id,unit_id,water_amt,power_amt,drink_amt,adjustment,total_amt,rounded_2dp)
                           VALUES(?,?,?,?,?,?,?,?,?,?)''', (run_id, month_cycle, r['company_id'], r['unit_id'], float(r['water_amt']), float(r['power_amt']), float(r['drink_amt']), float(r['adjustment']), float(r['total_amt']), float(r['total_amt'])))

        for lg in out['logs']:
            cur.execute('''INSERT INTO logs(run_id,month_cycle,severity,code,message,ref_json)
                           VALUES(?,?,?,?,?,?)''', (run_id, month_cycle, lg['severity'], lg['code'], lg['message'], json_dumps_safe(lg.get('ref_json') or {})))

        cur.execute('INSERT OR REPLACE INTO finalized_months(month_cycle,finalized_at) VALUES(?,CURRENT_TIMESTAMP)', (month_cycle,))
        return {'status': 'ok', 'run_id': run_id, 'rows': len(out['billing_rows'])}

    result = exec_txn(_txn)
    return jsonify(result)


@app.get('/api/results/employee-wise')
def api_results_employee_wise():
    m = (request.args.get('month_cycle') or '').strip()
    rows = q('''SELECT company_id, unit_id, ROUND(water_amt,2) AS water_amt, ROUND(power_amt,2) AS power_amt,
                       ROUND(drink_amt,2) AS drink_amt, ROUND(total_amt,2) AS total_amt
                FROM billing_rows WHERE month_cycle=? ORDER BY company_id''', (m,))
    return jsonify({'status': 'ok', 'month_cycle': m, 'rows': rows})


@app.get('/api/results/unit-wise')
def api_results_unit_wise():
    m = (request.args.get('month_cycle') or '').strip()
    rows = q('''SELECT unit_id,
                       ROUND(SUM(water_amt),2) AS water_amt,
                       ROUND(SUM(power_amt),2) AS power_amt,
                       ROUND(SUM(drink_amt),2) AS drink_amt,
                       ROUND(SUM(total_amt),2) AS total_amt
                FROM billing_rows WHERE month_cycle=? GROUP BY unit_id ORDER BY unit_id''', (m,))
    return jsonify({'status': 'ok', 'month_cycle': m, 'rows': rows})


@app.get('/api/logs')
def api_logs():
    m = (request.args.get('month_cycle') or '').strip()
    rows = q('SELECT severity, code, message, ref_json, created_at FROM logs WHERE month_cycle=? ORDER BY id', (m,))
    return jsonify({'status': 'ok', 'month_cycle': m, 'rows': rows})


ensure_occupancy_schema()
ensure_monthly_rates_schema()
ensure_employee_master_schema()
ensure_employee_registry_schema()
ensure_audit_schema()
ensure_adjustment_schema()
ensure_recovery_schema()
ensure_colony_extras_schema()

if __name__ == '__main__':
    port = int(os.environ.get('MBS_UNIFIED_PORT', '8010'))
    app.run(port=port)
