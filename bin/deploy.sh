#!/usr/bin/env bash
#
# Build, test, deploy to Laravel Cloud, then warm the cache.
#
# Warming runs here, from a developer machine, rather than as a Cloud deploy
# command. A deploy command runs inside the container, which means reaching our
# own public hostname: out to the edge and back into the same origin.
# Cloudflare's Browser Integrity Check rejects that with a 403 (error 1010). A
# full browser header signature clears BIC from an ordinary client but not from
# Cloud's egress address, because BIC weighs the client address too. Rate
# limiting was ruled out - the environment has none configured.
#
# `cloud deploy` blocks until the deployment reaches a terminal state, so the
# warm below always runs against the new release rather than the outgoing one.
set -euo pipefail

cd "$(dirname "$0")/.."

APPLICATION="${LARAVEL_CLOUD_APPLICATION:-kotyk.com}"
ENVIRONMENT="${LARAVEL_CLOUD_ENVIRONMENT:-production}"

# Warming must target the deployed site, not APP_URL - that is the local
# development host, and bin/warm.sh falls back to it when given no argument.
SITE_URL="${LARAVEL_CLOUD_SITE_URL:-https://kotyk.com}"

read_env() {
    [[ -f .env ]] || return 0
    grep -E "^$1=" .env | tail -n1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'
}

# Deploy pings. A deploy is an event heartbeat: start opens a window, finish
# closes it, and a start with no finish inside the hub's timeout is itself a
# verdict - which is what catches a deploy that hung rather than one that broke.
#
# MONITORING_CLIENT_ENABLED is forced on for these calls only. This script runs
# from a developer machine, where .env deliberately has it off: a laptop
# reporting heartbeats is indistinguishable from the site reporting them. A real
# environment variable beats .env, so the deploy can say what it is doing
# without switching the laptop on for everything else.
#
# Never allowed to fail the deploy. A deploy script that stops because
# monitoring was unreachable has been made less reliable by being monitored.
MONITORING_DEPLOY_TOKEN="$(read_env MONITORING_CLIENT_DEPLOYMENT_TOKEN)"

ping_monitoring() {
    [[ -n "${MONITORING_DEPLOY_TOKEN:-}" ]] || return 0

    local stage="$1" message="${2:-}"

    MONITORING_CLIENT_ENABLED=true \
    MONITORING_CLIENT_DEPLOYMENT_TOKEN="$MONITORING_DEPLOY_TOKEN" \
        php artisan monitoring:deploy "$stage" ${message:+--message="$message"} \
        >/dev/null 2>&1 || true
}

# Anything that trips `set -e` from here on is a failed deploy, and the hub
# should hear it from us rather than infer it from a window that never closed.
trap 'ping_monitoring fail "deploy.sh failed"' ERR

# The CLI reads LARAVEL_CLOUD_TOKEN; we keep it in .env under the same name the
# API docs use. Exported rather than passed so it stays out of the process list.
export LARAVEL_CLOUD_TOKEN="${LARAVEL_CLOUD_TOKEN:-$(read_env LARAVEL_CLOUD_API_TOKEN)}"

# public/build is gitignored, so the Vite manifest only exists once Vite has
# run. The suite renders pages through @vite and 500s without it.
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
ping_monitoring start "deploying $APPLICATION/$ENVIRONMENT"
# Waits for a terminal state by default; --no-wait would return immediately.
cloud deploy "$APPLICATION" "$ENVIRONMENT" -n

# The zone caches pages for up to an hour, and each build renames its hashed
# CSS/JS. Without a purge the edge would keep serving the old release's HTML,
# pointing at assets the new release no longer has. Purged before warming, so
# the warm fills the edge with the new release.
CLOUDFLARE_TOKEN="$(read_env CLOUDFLARE_API_TOKEN)"
CLOUDFLARE_ZONE="$(read_env CLOUDFLARE_ZONE_ID)"

if [[ -n "$CLOUDFLARE_TOKEN" && -n "$CLOUDFLARE_ZONE" ]]; then
    echo "==> Purging the Cloudflare cache"
    curl -fsS --max-time 30 -X POST \
        "https://api.cloudflare.com/client/v4/zones/$CLOUDFLARE_ZONE/purge_cache" \
        -H "Authorization: Bearer $CLOUDFLARE_TOKEN" \
        -H "Content-Type: application/json" \
        --data '{"purge_everything":true}' >/dev/null
else
    echo "==> Warning: CLOUDFLARE_API_TOKEN / CLOUDFLARE_ZONE_ID not set; edge cache not purged" >&2
fi

echo "==> Deployed. Warming $SITE_URL"
# Not exec: the process has to survive the warm so it can close the deploy
# window afterwards. Warming is part of the deploy, so a failure there is a
# failed deploy and the ERR trap reports it.
bin/warm.sh "$SITE_URL"

ping_monitoring finish
echo "==> Done"
