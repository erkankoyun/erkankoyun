from __future__ import annotations

from dataclasses import dataclass, asdict
from typing import Any


@dataclass(frozen=True)
class Decision:
    request_id: str
    decision: str
    reasons: list[str]

    def to_dict(self) -> dict[str, Any]:
        return asdict(self)


def _string_list(policy: dict[str, Any], key: str) -> set[str]:
    value = policy.get(key, [])
    if not isinstance(value, list):
        raise ValueError(f"Policy field '{key}' must be a list.")
    return {str(item) for item in value}


def evaluate(request: dict[str, Any], policy: dict[str, Any]) -> Decision:
    required_fields = ["request_id", "user_id", "device_id", "action"]
    missing = [field for field in required_fields if not str(request.get(field, "")).strip()]

    request_id = str(request.get("request_id", "unknown"))

    if missing:
        return Decision(
            request_id=request_id,
            decision="deny",
            reasons=["Missing required fields: " + ", ".join(missing) + "."],
        )

    action = str(request["action"])
    known_actions = _string_list(policy, "known_actions")
    denied_actions = _string_list(policy, "denied_actions")
    auto_allow = _string_list(policy, "auto_allow")
    approval_required = _string_list(policy, "approval_required")

    if action not in known_actions:
        return Decision(
            request_id=request_id,
            decision="deny",
            reasons=["The requested action is not recognized by policy."],
        )

    if action in denied_actions:
        return Decision(
            request_id=request_id,
            decision="deny",
            reasons=["The requested action is explicitly denied by policy."],
        )

    sensitivity = str(request.get("sensitivity", "normal")).lower()
    approval = request.get("approval")
    is_approved = isinstance(approval, dict) and approval.get("status") == "approved"

    if action in auto_allow and sensitivity != "restricted":
        return Decision(
            request_id=request_id,
            decision="allow",
            reasons=["The requested action is allowed by the public demo policy."],
        )

    if action in approval_required or sensitivity == "restricted":
        if is_approved:
            return Decision(
                request_id=request_id,
                decision="allow",
                reasons=["Human approval is present for an approval-controlled action."],
            )

        return Decision(
            request_id=request_id,
            decision="approval_required",
            reasons=["The requested action requires human approval."],
        )

    return Decision(
        request_id=request_id,
        decision="deny",
        reasons=["No allow rule matched; the demo policy denies by default."],
    )
