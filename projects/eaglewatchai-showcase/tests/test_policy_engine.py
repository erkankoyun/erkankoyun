from __future__ import annotations

import sys
import unittest
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_ROOT))

from policy_engine import evaluate  # noqa: E402


POLICY = {
    "known_actions": ["view_record", "external_email", "data_export", "blocked_action"],
    "auto_allow": ["view_record"],
    "approval_required": ["external_email", "data_export"],
    "denied_actions": ["blocked_action"],
}


class PolicyEngineTest(unittest.TestCase):
    def base_request(self, action: str) -> dict:
        return {
            "request_id": "test-001",
            "user_id": "user-1",
            "device_id": "device-1",
            "action": action,
            "sensitivity": "normal",
        }

    def test_low_risk_known_action_can_be_allowed(self) -> None:
        decision = evaluate(self.base_request("view_record"), POLICY)

        self.assertEqual("allow", decision.decision)

    def test_high_impact_action_requires_approval(self) -> None:
        decision = evaluate(self.base_request("external_email"), POLICY)

        self.assertEqual("approval_required", decision.decision)

    def test_approved_high_impact_action_can_be_allowed(self) -> None:
        request = self.base_request("data_export")
        request["approval"] = {"status": "approved", "approved_by": "manager-1"}

        decision = evaluate(request, POLICY)

        self.assertEqual("allow", decision.decision)

    def test_unknown_action_is_denied(self) -> None:
        decision = evaluate(self.base_request("unknown_action"), POLICY)

        self.assertEqual("deny", decision.decision)

    def test_explicitly_denied_action_is_denied(self) -> None:
        decision = evaluate(self.base_request("blocked_action"), POLICY)

        self.assertEqual("deny", decision.decision)

    def test_missing_identity_fields_are_denied(self) -> None:
        decision = evaluate({"request_id": "bad", "action": "view_record"}, POLICY)

        self.assertEqual("deny", decision.decision)
        self.assertIn("Missing required fields", decision.reasons[0])


if __name__ == "__main__":
    unittest.main()
