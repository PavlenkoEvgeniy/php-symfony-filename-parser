# 📂 Filename Parser

PHP CLI tool that scans a folder, extracts numbers that match a regex pattern from filenames, optionally writes them to a text file in ascending order, and can rename files by prefixing the first matched number.

## ✅ Requirements

Choose one of the following:

- Docker + Docker Compose (recommended)
- PHP 8.4 (CLI)

## ▶️ Usage

### Docker (recommended)

Start the container and install dependencies:

```
docker compose -f docker/docker-compose.yml up -d --build
docker compose -f docker/docker-compose.yml run --rm app composer install
```

Or use the Makefile:

```
make init
```

Run the command:

```
docker compose -f docker/docker-compose.yml run --rm app php bin/console [folder] [output]
```

Stop the container:

```
docker compose -f docker/docker-compose.yml down
```

Or use the Makefile:

```
make stop
```

### Local PHP

Run from the project folder:

```
php bin/console [folder] [output]
```

- `folder` (optional): Path to the folder to scan. Defaults to the current folder (prompted).
- `output` (optional): Path to the output text file (prompted if needed). Defaults to `numbers.txt` in the current folder.

### Interactive prompts

The command will ask:

- **Action**: `extract numbers`, `rename files`, or `both`
- **Pattern (regex)**: defaults to `#(\d+)` (you can enter a raw regex, with or without delimiters)
- **Output path** (only when extracting numbers): defaults to `numbers.txt` in the current folder

### Examples

Scan a specific folder and write to a specific file:

```
php bin/console /path/to/files /path/to/numbers.txt
```

Scan the current folder (interactive prompts):

```
php bin/console
```

## 🛠️ What it does

- Finds all occurrences that match the configured regex in each filename (default: `#(\d+)`).
- Writes all captured numbers (first capture group) to the output file, one per line, in ascending order.
- Renames each file by **prefixing the first matched number** as `n.` (for example, `2.`) while keeping the rest of the filename intact.

Example rename (default pattern):

- `report #2 final.pdf` → `2.report #2 final.pdf`

## 📌 Notes

- Only files (not folders) are processed.
- If a target filename already exists, the rename is skipped and a warning is printed.
- If no matches are found and you choose extraction, an empty output file is still created.

## 🧪 Tests

Run PHPUnit in Docker:

```
docker compose -f docker/docker-compose.yml run --rm app vendor/bin/phpunit
```

Or use the Makefile:

```
make test
```

Run coverage in Docker:

```
make test-coverage
```

Run PHPUnit locally:

```
./vendor/bin/phpunit
```

## 🔧 Quality checks (Makefile)

Run all linters and static analysis:

```
make lint
```

Run individual tools:

```
make phpstan
make psalm
make cs-check
make peck
make security-check
```
