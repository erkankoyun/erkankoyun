# EagleWatchAI — Public Permission Gateway Showcase

This directory is a deliberately limited public showcase of the EagleWatchAI security concept.

It demonstrates a small, deterministic permission-decision workflow for portfolio and architecture discussion purposes. It does **not** contain private EagleWatchAI implementation details, customer configuration, proprietary detection logic, enforcement agents, credentials, infrastructure configuration, or patent-sensitive mechanisms.

## Public demo goal

Show the engineering pattern behind a permission gateway:

1. Receive a structured action request.
2. Validate the request against a simple policy.
3. Return one of three explainable decisions:
   - `allow`
   - `deny`
   - `approval_required`
4. Record clear reasons for the decision.

The public code intentionally uses ordinary, transparent rules instead of proprietary AI or security algorithms.

## Example policy behavior

- Unknown actions are denied by default.
- Low-risk actions can be allowed directly.
- Selected high-impact actions require human approval.
- Approved requests can proceed when the policy allows that action.
- Every decision includes human-readable reasons.

## Files

```text
policy_engine.py          Deterministic policy evaluator
demo.py                   Command-line demo
policy.example.json       Safe example policy
examples/request.json     Example permission request
tests/                    Automated tests
```

## Run the showcase

Requirements: Python 3.11+; no third-party packages.

```bash
python demo.py examples/request.json --policy policy.example.json
```

Example response:

```json
{
  "decision": "approval_required",
  "reasons": [
    "The requested action requires human approval."
  ]
}
```

## Tests

```bash
python -m unittest discover -s tests -v
```

## Security boundary

This is **not** a production security product and should not be used as an access-control system. The real EagleWatchAI project remains private while product-specific and security-sensitive work is developed and reviewed.

## Author

**Erkan Koyun**  
EagleWatchAI · AI/security portfolio work
