#!/usr/bin/env bash
#
# Build, test, deploy to Laravel Cloud, then warm the cache.
#
# Warming runs here, from a developer machine, rather than as a Cloud deploy
# command. Statamic's `static:warm` runs inside the container, which means
# reaching our own public hostname: out to the edge and back into the same
# origin. Cloudflare's Browser Integrity Check rejects that with a 403 (error
# 1010). A full browser header signature clears BIC from an ordinary client but
# not from Cloud's egress address, because BIC weighs the client address too.
# Rate limiting was ruled out - the environment has none configured.
#
# `cloud deploy` blocks until the deployment reaches a terminal state, so the
# warm below always runs against the new release. That matters: the deploy
# commands run `static:clear`, so warming an outgoing release would be wiped.
set -euo pipefail

cd "$(dirname "$0")/.."

APPLICATION="${LARAVEL_CLOUD_APPLICATION:-kotyk.com}"
ENVIRONMENT="${LARAVEL_CLOUD_ENVIRONMENT:-production}"

read_env() {
    [[ -f .env ]] || return 0
    grep -E "^$1=" .env | tail -n1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'
}

# The CLI reads LARAVEL_CLOUD_TOKEN; we keep it in .env under the same name the
# API docs use. Exported rather than passed so it stays out of the process list.
export LARAVEL_CLOUD_TOKEN="${LARAVEL_CLOUD_TOKEN:-$(read_env LARAVEL_CLOUD_API_TOKEN)}"

# public/build is gitignored, so the Vite manifest only exists once Vite has
# run. The suite renders templates through the {{ vite }} tag and 500s without it.
echo "==> Building front-end assets"
npm run build

echo "==> Running test suite"
php artisan test

if git status --porcelain | grep -q .; then
    echo "==> Warning: working tree is dirty; Cloud deploys the pushed commit" >&2
fi

if ! command -v cloud >/dev/null 2>&1; then
    echo "==> laravel/cloud-cli not found. Install it with:" >&2
    echo "        composer global require laravel/cloud-cli" >&2
    exit 1
fi

if [[ -z "${LARAVEL_CLOUD_TOKEN:-}" ]]; then
    echo "==> LARAVEL_CLOUD_API_TOKEN is not set in .env" >&2
    echo "    Create one at cloud.laravel.com -> Settings -> API Tokens" >&2
    exit 1
fi

echo "==> Deploying $APPLICATION/$ENVIRONMENT"
# Waits for a terminal state by default; --no-wait would return immediately.
cloud deploy "$APPLICATION" "$ENVIRONMENT" -n

echo "==> Deployed. Warming cache."
exec bin/warm.sh
