#!/usr/bin/env bash
#
# Warm the static page cache (and, as a side effect, the Glide derivative
# bucket) by requesting every URL in the sitemap.
#
# Statamic's own `static:warm` cannot do this from inside Laravel Cloud: the
# container has to reach its own public hostname, which means going out to the
# edge and back into the same origin, and every request comes back 403. That is
# not a user-agent or rate-limit problem - both were ruled out by testing - it
# is the request path itself. See statamic/cms#13145.
#
# Running it from a developer machine sidesteps that entirely, because an
# ordinary external client reaches the site normally.
#
# Usage:  bin/warm.sh [base-url] [concurrency]
set -euo pipefail

cd "$(dirname "$0")/.."

BASE="${1:-}"
CONCURRENCY="${2:-5}"

if [[ -z "$BASE" && -f .env ]]; then
    BASE=$(grep -E '^APP_URL=' .env | tail -n1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//')
fi

if [[ -z "${BASE:-}" ]]; then
    echo "==> No base URL. Pass one: bin/warm.sh https://kotyk.com" >&2
    exit 1
fi

BASE="${BASE%/}"

echo "==> Fetching sitemap from $BASE"
urls=$(curl -fsS --max-time 30 "$BASE/sitemap.xml" \
    | grep -oE '<loc>[^<]+</loc>' \
    | sed -e 's/<loc>//' -e 's|</loc>||')

count=$(printf '%s\n' "$urls" | grep -c . || true)

if [[ "$count" -eq 0 ]]; then
    echo "==> Sitemap contained no URLs" >&2
    exit 1
fi

echo "==> Warming $count URLs at concurrency $CONCURRENCY"

# -o /dev/null so we pay for the render but do not buffer the HTML.
results=$(printf '%s\n' "$urls" \
    | xargs -P "$CONCURRENCY" -I{} curl -s -o /dev/null -w '%{http_code}\n' --max-time 30 "{}")

printf '%s\n' "$results" | sort | uniq -c | while read -r n code; do
    echo "     $code x$n"
done

failed=$(printf '%s\n' "$results" | grep -cvE '^(200|301|302)$' || true)

if [[ "$failed" -gt 0 ]]; then
    echo "==> $failed URL(s) did not return a success or redirect" >&2
    exit 1
fi

echo "==> Warmed"
