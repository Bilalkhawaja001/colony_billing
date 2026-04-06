from decimal import Decimal, ROUND_HALF_UP
import re
from .models import BillingLine

QTY_SCALE = Decimal("0.0001")
AMT_SCALE = Decimal("0.01")


def _q(x):
    return Decimal(str(x or 0)).quantize(QTY_SCALE, rounding=ROUND_HALF_UP)


def _a(x):
    return Decimal(str(x or 0)).quantize(AMT_SCALE, rounding=ROUND_HALF_UP)


def build_line(month_cycle: str, employee_id: str, utility_type: str, qty, rate, amount, source_ref: str) -> BillingLine:
    return BillingLine(
        month_cycle=month_cycle,
        employee_id=employee_id,
        utility_type=utility_type,
        qty=_q(qty),
        rate=_q(rate),
        amount=_a(amount),
        source_ref=source_ref,
    )


def deterministic_fingerprint(lines: list[BillingLine]) -> str:
    rows = sorted(
        lines,
        key=lambda x: (x.month_cycle, x.employee_id, x.utility_type, str(x.qty), str(x.rate), str(x.amount), x.source_ref),
    )
    return "\n".join(
        f"{r.month_cycle}|{r.employee_id}|{r.utility_type}|{r.qty}|{r.rate}|{r.amount}|{r.source_ref}" for r in rows
    )


# -------- Colony Utilities Extras Engine (pure functions) ---------

def month_cycle_valid(month_cycle: str) -> bool:
    # required format: 12-2025
    return bool(re.match(r"^(0[1-9]|1[0-2])-\d{4}$", str(month_cycle or "").strip()))


def run_colony_billing(month_cycle: str, unit_rows: list[dict], hr_rows: list[dict], map_rows: list[dict], ro_rows: list[dict]):
    """
    Pure deterministic engine.
    Inputs are row dictionaries already loaded from DB.
    Returns: {status, stop, logs, unit_totals, billing_rows}
    """
    logs = []
    billing_rows = []

    def log(sev, code, msg, ref=None):
        logs.append({
            'severity': sev,
            'code': code,
            'message': msg,
            'ref_json': ref or {}
        })

    # Phase-0 precheck
    if not month_cycle_valid(month_cycle):
        log('CRIT', 'BAD_MONTH', 'Invalid Month_Cycle format. Expected MM-YYYY', {'month_cycle': month_cycle})
        return {'status': 'failed', 'stop': True, 'logs': logs, 'unit_totals': [], 'billing_rows': []}

    seen_hr = set()
    company_to_days = {}
    for r in hr_rows:
        company_id = str(r.get('company_id') or '').strip()
        key = (month_cycle, company_id)
        if key in seen_hr:
            log('CRIT', 'DUP_HR', 'Duplicate HR row for CompanyID + Month_Cycle', {'company_id': company_id})
            return {'status': 'failed', 'stop': True, 'logs': logs, 'unit_totals': [], 'billing_rows': []}
        seen_hr.add(key)

        try:
            d = Decimal(str(r.get('active_days') or 0))
        except Exception:
            log('CRIT', 'BAD_DAYS', 'Active_Days is non-numeric', {'company_id': company_id})
            return {'status': 'failed', 'stop': True, 'logs': logs, 'unit_totals': [], 'billing_rows': []}

        if d < 0:
            log('CRIT', 'BAD_DAYS', 'Active_Days is negative', {'company_id': company_id, 'active_days': str(d)})
            return {'status': 'failed', 'stop': True, 'logs': logs, 'unit_totals': [], 'billing_rows': []}

        company_to_days[company_id] = d

    # Phase-1 virtual merge
    unit_to_companies = {}
    for m in map_rows:
        u = str(m.get('unit_id') or '').strip()
        c = str(m.get('company_id') or '').strip()
        if not u:
            continue
        unit_to_companies.setdefault(u, []).append(c)

    hr_companies = set(company_to_days.keys())
    mapped_companies = set(c for _, cs in unit_to_companies.items() for c in cs)
    for c in sorted(hr_companies - mapped_companies):
        log('INFO', 'HR_UNUSED', 'HR row not used because no room mapping', {'company_id': c})

    # Build unit extras totals (water/power from readings + drinking from RO)
    unit_totals = {}
    for r in unit_rows:
        u = str(r.get('unit_id') or '').strip()
        meter_type = str(r.get('meter_type') or '').strip().lower()
        usage = Decimal(str(r.get('usage') or 0))
        amount = Decimal(str(r.get('amount') or 0))
        x = unit_totals.setdefault(u, {'water': Decimal('0'), 'power': Decimal('0'), 'drink': Decimal('0')})
        if meter_type == 'water':
            x['water'] += amount
        elif meter_type in ('power', 'electricity', 'elec'):
            x['power'] += amount

    for r in ro_rows:
        u = str(r.get('unit_id') or '').strip()
        amt = Decimal(str(r.get('amount') or 0))
        x = unit_totals.setdefault(u, {'water': Decimal('0'), 'power': Decimal('0'), 'drink': Decimal('0')})
        x['drink'] += amt

    # Phase-3 allocation
    for unit_id, totals in sorted(unit_totals.items(), key=lambda kv: kv[0]):
        occupants = unit_to_companies.get(unit_id, [])
        if not occupants:
            log('WARN', 'UNIT_NO_MAP', 'Unit has charges but no room mapping', {'unit_id': unit_id})
            continue

        day_map = {}
        for c in occupants:
            if c in company_to_days:
                day_map[c] = company_to_days[c]
            else:
                day_map[c] = Decimal('30')
                log('WARN', 'GHOST_TENANT_PENALTY', 'Mapped employee missing in HR; penalty days=30 applied', {'unit_id': unit_id, 'company_id': c})

        unit_total_days = sum(day_map.values()) if day_map else Decimal('0')

        # single occupant bypass
        if len(occupants) == 1:
            c = occupants[0]
            billing_rows.append({
                'company_id': c,
                'unit_id': unit_id,
                'water_amt': _a(totals['water']),
                'power_amt': _a(totals['power']),
                'drink_amt': _a(totals['drink']),
                'adjustment': Decimal('0.00'),
                'total_amt': _a(totals['water'] + totals['power'] + totals['drink'])
            })
            continue

        if unit_total_days == 0:
            log('CRIT', 'UNIT_ZERO_DAYS', 'Multi-occupant unit has zero total days; no bill generated', {'unit_id': unit_id})
            continue

        # multi split by attendance
        for c in occupants:
            d = day_map[c]
            ratio = d / unit_total_days
            water_amt = _a(totals['water'] * ratio)
            power_amt = _a(totals['power'] * ratio)
            drink_amt = _a(totals['drink'] * ratio)
            billing_rows.append({
                'company_id': c,
                'unit_id': unit_id,
                'water_amt': water_amt,
                'power_amt': power_amt,
                'drink_amt': drink_amt,
                'adjustment': Decimal('0.00'),
                'total_amt': _a(water_amt + power_amt + drink_amt)
            })

        # remainder correction per utility for deterministic reconciliation
        unit_rows_alloc = [x for x in billing_rows if x['unit_id'] == unit_id]
        if unit_rows_alloc:
            last = unit_rows_alloc[-1]
            for k, total in [('water_amt', totals['water']), ('power_amt', totals['power']), ('drink_amt', totals['drink'])]:
                current = sum([r[k] for r in unit_rows_alloc])
                diff = _a(total - current)
                if diff != Decimal('0.00'):
                    last[k] = _a(last[k] + diff)
            last['total_amt'] = _a(last['water_amt'] + last['power_amt'] + last['drink_amt'] + last['adjustment'])

    # Phase-4 reconciliation
    sum_emp = _a(sum([r['total_amt'] for r in billing_rows]) if billing_rows else Decimal('0'))
    sum_units = _a(sum([v['water'] + v['power'] + v['drink'] for _, v in unit_totals.items()]) if unit_totals else Decimal('0'))
    if abs(sum_emp - sum_units) > Decimal('0.01'):
        log('CRIT', 'RECON_FAIL', 'Employee totals and unit totals mismatch', {'employee_total': str(sum_emp), 'unit_total': str(sum_units)})
        return {'status': 'failed', 'stop': True, 'logs': logs, 'unit_totals': unit_totals, 'billing_rows': billing_rows}

    return {'status': 'ok', 'stop': False, 'logs': logs, 'unit_totals': unit_totals, 'billing_rows': billing_rows}
