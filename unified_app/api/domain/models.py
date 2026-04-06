from dataclasses import dataclass
from decimal import Decimal


@dataclass(frozen=True)
class MonthContext:
    month_cycle: str
    actor_user_id: str


@dataclass(frozen=True)
class BillingLine:
    month_cycle: str
    employee_id: str
    utility_type: str
    qty: Decimal
    rate: Decimal
    amount: Decimal
    source_ref: str
