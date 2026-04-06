import json
from datetime import datetime


def build_audit_event(entity_type: str, entity_id: str, action: str, actor_user_id: str, before=None, after=None):
    return {
        "entity_type": entity_type,
        "entity_id": entity_id,
        "action": action,
        "actor_user_id": str(actor_user_id),
        "before_json": json.dumps(before or {}, ensure_ascii=False),
        "after_json": json.dumps(after or {}, ensure_ascii=False),
        "created_at": datetime.utcnow().isoformat(timespec="seconds") + "Z",
    }
