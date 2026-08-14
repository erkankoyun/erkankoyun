from __future__ import annotations

import argparse
import json
import shutil
from datetime import datetime, timezone
from pathlib import Path
from typing import Iterable

DEFAULT_RULES: dict[str, list[str]] = {
    "Documents": [".pdf", ".doc", ".docx", ".txt", ".rtf", ".csv", ".xlsx"],
    "Images": [".jpg", ".jpeg", ".png", ".gif", ".webp", ".svg"],
    "Archives": [".zip", ".tar", ".gz", ".7z", ".rar"],
    "Code": [".py", ".php", ".js", ".ts", ".html", ".css", ".json", ".sql"],
    "Media": [".mp3", ".wav", ".mp4", ".mov", ".mkv"],
}


def normalize_rules(rules: dict[str, Iterable[str]]) -> dict[str, set[str]]:
    normalized: dict[str, set[str]] = {}

    for category, extensions in rules.items():
        clean_category = str(category).strip()
        if not clean_category:
            raise ValueError("Category names cannot be empty.")

        clean_extensions: set[str] = set()
        for extension in extensions:
            value = str(extension).strip().lower()
            if not value:
                continue
            if not value.startswith("."):
                value = f".{value}"
            clean_extensions.add(value)

        normalized[clean_category] = clean_extensions

    return normalized


def load_rules(config_path: Path | None = None) -> dict[str, set[str]]:
    if config_path is None:
        return normalize_rules(DEFAULT_RULES)

    with config_path.open("r", encoding="utf-8") as handle:
        data = json.load(handle)

    if not isinstance(data, dict):
        raise ValueError("Configuration must be a JSON object.")

    return normalize_rules(data)


def category_for(file_path: Path, rules: dict[str, set[str]]) -> str:
    extension = file_path.suffix.lower()

    for category, extensions in rules.items():
        if extension in extensions:
            return category

    return "Other"


def unique_destination(destination: Path) -> Path:
    if not destination.exists():
        return destination

    counter = 1
    while True:
        candidate = destination.with_name(
            f"{destination.stem} ({counter}){destination.suffix}"
        )
        if not candidate.exists():
            return candidate
        counter += 1


def write_log(log_path: Path, record: dict[str, object]) -> None:
    log_path.parent.mkdir(parents=True, exist_ok=True)
    with log_path.open("a", encoding="utf-8") as handle:
        handle.write(json.dumps(record, ensure_ascii=False) + "\n")


def organize_directory(
    source: Path,
    rules: dict[str, set[str]],
    *,
    dry_run: bool = False,
    log_path: Path | None = None,
) -> dict[str, object]:
    source = source.expanduser().resolve()

    if not source.exists():
        raise FileNotFoundError(f"Source folder does not exist: {source}")
    if not source.is_dir():
        raise NotADirectoryError(f"Source path is not a directory: {source}")

    log_path = (log_path or source / ".filepilot.log.jsonl").expanduser().resolve()
    files = [
        item
        for item in source.iterdir()
        if item.is_file()
        and not item.name.startswith(".")
        and item.resolve() != log_path
    ]

    moved = 0
    by_category: dict[str, int] = {}

    for file_path in sorted(files, key=lambda path: path.name.lower()):
        category = category_for(file_path, rules)
        destination_dir = source / category
        destination = unique_destination(destination_dir / file_path.name)

        record: dict[str, object] = {
            "timestamp": datetime.now(timezone.utc).isoformat(),
            "source": str(file_path),
            "destination": str(destination),
            "category": category,
            "dry_run": dry_run,
        }

        if not dry_run:
            destination_dir.mkdir(parents=True, exist_ok=True)
            shutil.move(str(file_path), str(destination))
            moved += 1
        else:
            moved += 1

        by_category[category] = by_category.get(category, 0) + 1
        write_log(log_path, record)

    return {
        "source": str(source),
        "dry_run": dry_run,
        "processed": moved,
        "categories": by_category,
        "log": str(log_path),
    }


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(
        description="Organize files into configurable folders without overwriting duplicates."
    )
    parser.add_argument("--source", required=True, type=Path, help="Folder to organize")
    parser.add_argument("--config", type=Path, help="Optional JSON rules file")
    parser.add_argument("--log", type=Path, help="Optional JSONL log path")
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Preview moves without changing file locations",
    )
    return parser


def main() -> int:
    args = build_parser().parse_args()

    try:
        rules = load_rules(args.config)
        summary = organize_directory(
            args.source,
            rules,
            dry_run=args.dry_run,
            log_path=args.log,
        )
    except (OSError, ValueError, json.JSONDecodeError) as exc:
        print(json.dumps({"error": str(exc)}, ensure_ascii=False))
        return 1

    print(json.dumps(summary, indent=2, ensure_ascii=False))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
