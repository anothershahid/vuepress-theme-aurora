#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PORT=8099

php -S 127.0.0.1:${PORT} -t "$ROOT/public" > /tmp/aurora-php-server.log 2>&1 &
PID=$!
trap 'kill $PID >/dev/null 2>&1 || true' EXIT
sleep 1

check() {
  local path="$1"
  local code
  code=$(curl -s -o /tmp/aurora-page.out -w "%{http_code}" "http://127.0.0.1:${PORT}${path}")
  if [[ "$code" != "200" ]]; then
    echo "রুট ব্যর্থ: ${path} (HTTP ${code})" >&2
    exit 1
  fi
}

check "/"
check "/posts"
check "/categories"
check "/tags"
check "/timeline"
check "/moments"
check "/friends"
check "/albums"
check "/music"
check "/post/php-aurora-porichiti"

headers=$(curl -sI "http://127.0.0.1:${PORT}/")
echo "$headers" | grep -qi "x-content-type-options: nosniff"
echo "$headers" | grep -qi "x-frame-options: DENY"

cookie_file=/tmp/aurora-cookie.txt
token=$(curl -s -c "$cookie_file" "http://127.0.0.1:${PORT}/post/php-aurora-porichiti" | sed -n 's/.*name="csrf" value="\([^"]*\)".*/\1/p' | head -n1)
if [[ -z "$token" ]]; then
  echo "CSRF token পাওয়া যায়নি" >&2
  exit 1
fi

bad=$(curl -s -o /tmp/aurora-bad.out -w "%{http_code}" -X POST "http://127.0.0.1:${PORT}/comment" -d "csrf=bad&post_slug=php-aurora-porichiti&name=টেস্ট&content=ভুল")
if [[ "$bad" != "400" ]]; then
  echo "CSRF validation ব্যর্থ হয়নি" >&2
  exit 1
fi

good=$(curl -s -o /tmp/aurora-good.out -w "%{http_code}" -X POST "http://127.0.0.1:${PORT}/comment" \
  -b "$cookie_file" \
  --data-urlencode "csrf=${token}" \
  --data-urlencode "post_slug=php-aurora-porichiti" \
  --data-urlencode "name=পরীক্ষক" \
  --data-urlencode "content=এটি নিরাপদ মন্তব্য পরীক্ষা" )
if [[ "$good" != "302" ]]; then
  echo "মন্তব্য POST সফল হয়নি" >&2
  exit 1
fi

echo "Smoke test সফল"
