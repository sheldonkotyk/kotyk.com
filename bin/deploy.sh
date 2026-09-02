#!/usr/bin/env bash
#
# Run the full test suite and, only if it passes, trigger the Laravel Cloud deploy hook.
#
set -euo pipefail

cd "$(dirname "$0")/.."

if [[ -f .env ]]; then
    CLOUD_DEPLOY_URL=$(grep -E '^CLOUD_DEPLOY_URL=' .env | tail -n1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//')
fi

if [[ -z "${CLOUD_DEPLOY_URL:-}" ]]; then
    echo "==> CLOUD_DEPLOY_URL is not set in .env" >&2
    exit 1
fi

# public/build is gitignored, so the manifest only exists once Vite has run.
# Tests render templates through the {{ vite }} tag and fail without it.
echo "==> Building front-end assets"
npm run build

echo "==> Running test suite"
php artisan test

echo "==> Tests passed, triggering Laravel Cloud deploy"
# Cloud's hook expects POST; a GET is redirected (302) and does not deploy.
http_code=$(curl -sS -X POST -o /dev/null -w "%{http_code}" "$CLOUD_DEPLOY_URL")

# Forge answered 200; Laravel Cloud accepts the hook and answers 302, so treat
# any 2xx/3xx as accepted rather than re-firing and queueing a duplicate deploy.
if [[ "$http_code" =~ ^[23][0-9][0-9]$ ]]; then
    echo "==> Deploy triggered (HTTP $http_code)"
else
    echo "==> Deploy hook returned HTTP $http_code" >&2
    exit 1
fi
