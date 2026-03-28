import re
from pathlib import Path

root = Path(__file__).resolve().parents[1]
app = root / 'app'
migs = root / 'database' / 'migrations'

ref = set()
for p in app.rglob('*.php'):
    t = p.read_text(encoding='utf-8', errors='ignore')
    for m in re.finditer(r"\b(util_[a-z0-9_]+|billing_rows|finalized_months|monthly_variable_expenses|logs)\b", t):
        ref.add(m.group(1))

owned = {}
for p in migs.glob('*.php'):
    t = p.read_text(encoding='utf-8', errors='ignore')
    for m in re.finditer(r"Schema::create\('([^']+)'", t):
        owned.setdefault(m.group(1), []).append(p.name)

missing = sorted([t for t in ref if t not in owned])
print('REFERENCED_TABLES=', len(ref))
print('OWNED_TABLES=', len(owned))
print('MISSING=', len(missing))
for t in missing:
    print(' -', t)

if not missing:
    print('OK: all referenced util/month/rate/billing tables are migration-owned')
