from __future__ import annotations

import sys
import tempfile
import unittest
from pathlib import Path

PROJECT_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_ROOT))

from organizer import load_rules, normalize_rules, organize_directory  # noqa: E402


class OrganizerTest(unittest.TestCase):
    def test_files_are_moved_to_expected_categories(self) -> None:
        with tempfile.TemporaryDirectory() as temporary:
            source = Path(temporary)
            (source / "invoice.pdf").write_text("invoice", encoding="utf-8")
            (source / "photo.jpg").write_text("image", encoding="utf-8")
            (source / "unknown.bin").write_bytes(b"data")

            summary = organize_directory(source, load_rules())

            self.assertEqual(3, summary["processed"])
            self.assertTrue((source / "Documents" / "invoice.pdf").exists())
            self.assertTrue((source / "Images" / "photo.jpg").exists())
            self.assertTrue((source / "Other" / "unknown.bin").exists())

    def test_existing_file_is_not_overwritten(self) -> None:
        with tempfile.TemporaryDirectory() as temporary:
            source = Path(temporary)
            destination = source / "Documents"
            destination.mkdir()
            (destination / "report.pdf").write_text("existing", encoding="utf-8")
            (source / "report.pdf").write_text("new", encoding="utf-8")

            organize_directory(source, load_rules())

            self.assertEqual(
                "existing",
                (destination / "report.pdf").read_text(encoding="utf-8"),
            )
            self.assertEqual(
                "new",
                (destination / "report (1).pdf").read_text(encoding="utf-8"),
            )

    def test_dry_run_keeps_original_file_in_place(self) -> None:
        with tempfile.TemporaryDirectory() as temporary:
            source = Path(temporary)
            original = source / "notes.txt"
            original.write_text("notes", encoding="utf-8")

            summary = organize_directory(source, load_rules(), dry_run=True)

            self.assertTrue(original.exists())
            self.assertFalse((source / "Documents" / "notes.txt").exists())
            self.assertTrue(summary["dry_run"])

    def test_extensions_are_normalized(self) -> None:
        rules = normalize_rules({"Pictures": ["JPG", ".PNG"]})

        self.assertEqual({".jpg", ".png"}, rules["Pictures"])


if __name__ == "__main__":
    unittest.main()
