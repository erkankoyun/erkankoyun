from __future__ import annotations

import argparse
import json
from pathlib import Path

from policy_engine import evaluate


def read_json(path: Path) -> dict:
    with path.open("r", encoding="utf-8") as handle:
        data = json.load(handle)

    if not isinstance(data, dict):
        raise ValueError(f"{path} must contain a JSON object.")

    return data


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(
        description="Run the public EagleWatchAI permission-gateway showcase."
    )
    parser.add_argument("request", type=Path, help="Permission request JSON file")
    parser.add_argument(
        "--policy",
        type=Path,
        default=Path(__file__).with_name("policy.example.json"),
        help="Policy JSON file",
    )
    return parser


def main() -> int:
    args = build_parser().parse_args()

    try:
        request = read_json(args.request)
        policy = read_json(args.policy)
        decision = evaluate(request, policy)
    except (OSError, ValueError, json.JSONDecodeError) as exc:
        print(json.dumps({"error": str(exc)}, indent=2))
        return 1

    print(json.dumps(decision.to_dict(), indent=2))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
