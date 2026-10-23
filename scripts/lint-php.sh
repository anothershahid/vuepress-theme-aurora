#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
find "$ROOT" -name "*.php" -not -path "*/vendor/*" -print0 | while IFS= read -r -d '' file; do
  php -l "$file" >/dev/null
  echo "lint ok: ${file#$ROOT/}"
done
