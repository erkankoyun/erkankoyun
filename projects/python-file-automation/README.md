# FilePilot — Python File Automation

A small, working Python automation project that organizes files from an inbox/download folder into configurable categories.

The project is intentionally dependency-free and focuses on practical automation fundamentals: filesystem operations, configuration, safe duplicate handling, dry-run mode, structured logging, CLI design, and automated tests.

## Features

- Organizes files by extension
- Configurable category rules using JSON
- Safe duplicate naming instead of overwriting files
- `--dry-run` mode to preview actions
- JSON Lines activity log
- Cross-platform paths through `pathlib`
- No third-party Python packages required
- Automated tests using temporary directories

## Example

Given this folder:

```text
Downloads/
  invoice.pdf
  photo.jpg
  project.zip
  notes.txt
  unknown.bin
```

FilePilot can produce:

```text
Downloads/
  Documents/
    invoice.pdf
    notes.txt
  Images/
    photo.jpg
  Archives/
    project.zip
  Other/
    unknown.bin
```

## Run it

Requirements: Python 3.11+

```bash
python organizer.py --source ~/Downloads --dry-run
```

Run the real organization:

```bash
python organizer.py --source ~/Downloads
```

Use a custom configuration:

```bash
python organizer.py --source ~/Downloads --config config.example.json
```

## Configuration

`config.example.json` maps destination folder names to file extensions. Extensions are case-insensitive and should include the leading dot.

## Safety behavior

FilePilot never intentionally overwrites an existing file. If `report.pdf` already exists, a new file is moved as `report (1).pdf`, then `report (2).pdf`, and so on.

Use `--dry-run` before organizing an important folder.

## Tests

```bash
python -m unittest discover -s tests -v
```

## Author

**Erkan Koyun**  
Software Developer | PHP • Laravel • Python | Backend Development | IT Specialist
