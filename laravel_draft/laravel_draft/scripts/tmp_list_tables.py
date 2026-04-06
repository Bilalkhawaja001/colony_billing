import sqlite3

db = r'C:\Users\Bilal\clawd\mbs_project\laravel_draft\database\database.sqlite'
con = sqlite3.connect(db)
cur = con.cursor()
tables = {r[0] for r in cur.execute("select name from sqlite_master where type='table'")}
for t in ['hr_input','map_room','readings','ro_drinking','billing_rows','billing_run','logs','finalized_months','util_formula_result','util_drinking_formula_result','util_school_van_monthly_charge','util_billing_run','util_billing_line','util_month_cycle']:
    print(t, t in tables)
