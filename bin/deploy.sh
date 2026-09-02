#!/usr/bin/env bash
#
# Build, test, deploy to Laravel Cloud, wait for the deployment to finish, then
# warm the cache.
#
# Warming happens here, from a developer machine, rather than as a Cloud deploy
# command. Statamic's `static:warm` runs inside the container, which means
# reaching our own public hostname: out to the edge and back into the same
# origin. Cloudflare's Browser Integrity Check rejects that with a 403 (error
# 1010). Sending a full browser header signature clears BIC from an ordinary
# client but not from Cloud's egress address, because BIC weighs the client
# address too. Rate limiting was ruled out - the environment has none
# configured. An external client has no such problem.
#
# The wait is not a fixed delay: the deploy hook returns as soon as the
# deployment is queued, and the deploy commands run `static:clear`, so warming
# too early would warm the outgoing release and then have it wiped. We poll the
# Cloud API for this commit's deployment until it reaches a terminal state.
set -euo pipefail

cd "$(dirname "$0")/.."

read_env() {
    [[ -f .env ]] || return 0
    grep -E "^$1=" .env | tail -n1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'
}

CLOUD_DEPLOY_URL="${CLOUD_DEPLOY_URL:-$(read_env CLOUD_DEPLOY_URL)}"
API_TOKEN="${LARAVEL_CLOUD_API_TOKEN:-$(read_env LARAVEL_CLOUD_API_TOKEN)}"
ENVIRONMENT_ID="${LARAVEL_CLOUD_ENVIRONMENT_ID:-$(read_env LARAVEL_CLOUD_ENVIRONMENT_ID)}"

if [[ -z "${CLOUD_DEPLOY_URL:-}" ]]; then
    echo "==> CLOUD_DEPLOY_URL is not set in .env" >&2
    exit 1
fi

# public/build is gitignored, so the Vite manifest only exists once Vite has
# run. The suite renders templates through the {{ vite }} tag and 500s without it.
echo "==> Building front-end assets"
npm run build

echo "==> Running test suite"
php artisan test

COMMIT=$(git rev-parse HEAD)

if git status --porcelain | grep -q .; then
    echo "==> Warning: working tree is dirty; deploying committed state ($COMMIT)" >&2
fi

echo "==> Triggering deploy for ${COMMIT:0:8}"
# Cloud's hook expects POST; a GET is redirected with a 302 and never deploys.
# Forge answered 200, Cloud answers 201, so accept any 2xx/3xx as accepted
# rather than re-firing and queueing a duplicate.
http_code=$(curl -sS -X POST -o /dev/null -w "%{http_code}" "$CLOUD_DEPLOY_URL")

if [[ ! "$http_code" =~ ^[23][0-9][0-9]$ ]]; then
    echo "==> Deploy hook returned HTTP $http_code" >&2
    exit 1
fi

echo "==> Deploy triggered (HTTP $http_code)"

if [[ -z "${API_TOKEN:-}" || -z "${ENVIRONMENT_ID:-}" ]]; then
    echo "==> LARAVEL_CLOUD_API_TOKEN or LARAVEL_CLOUD_ENVIRONMENT_ID not set;"
    echo "    cannot wait for completion. Run bin/warm.sh once the deploy lands."
    exit 0
fi

# cloud.laravel.com sits behind the same Browser Integrity Check, so these
# requests need a full browser header signature or they come back 403/1010.
api() {
    curl -sS --max-time 30 \
        -H "Authorization: Bearer $API_TOKEN" \
        -H "Accept: application/vnd.api+json" \
        -H "Accept-Language: en-US,en;q=0.9" \
        -H "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36" \
        "https://cloud.laravel.com/api$1"
}

echo "==> Waiting for deployment to finish"

status=""
for _ in $(seq 1 120); do
    status=$(api "/environments/$ENVIRONMENT_ID/deployments?filter[commit_hash]=$COMMIT" \
        | jq -r '.data[0].attributes.status // empty' 2>/dev/null || true)

    case "$status" in
        deployment.succeeded)
            echo "    status: $status"
            break
            ;;
        failed|cancelled|build.failed|deployment.failed)
            reason=$(api "/environments/$ENVIRONMENT_ID/deployments?filter[commit_hash]=$COMMIT" \
                | jq -r '.data[0].attributes.failure_reason // "unknown"')
            echo "==> Deployment $status: $reason" >&2
            exit 1
            ;;
        "")
            # The deployment may not be registered against this commit yet.
            ;;
        *)
            echo "    status: $status"
            ;;
    esac

    sleep 5
done

if [[ "$status" != "deployment.succeeded" ]]; then
    echo "==> Gave up waiting (last status: ${status:-unknown}). Run bin/warm.sh manually." >&2
    exit 1
fi

echo "==> Deployed. Warming cache."
exec bin/warm.sh
