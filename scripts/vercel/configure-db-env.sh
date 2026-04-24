#!/usr/bin/env bash

set -euo pipefail

# Usage:
#   export DB_HOST=...
#   export DB_PORT=3306
#   export DB_DATABASE=...
#   export DB_USERNAME=...
#   export DB_PASSWORD=...
#   ./scripts/vercel/configure-db-env.sh
#
# Optional:
#   export VERCEL_SCOPE=rodrigosambadesaas-projects
#   export VERCEL_PROJECT=a23rodrigoss

required_vars=(DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD)
for var_name in "${required_vars[@]}"; do
    if [[ -z "${!var_name:-}" ]]; then
        echo "Missing required env var: ${var_name}" >&2
        exit 1
    fi
done

VERCEL_SCOPE="${VERCEL_SCOPE:-rodrigosambadesaas-projects}"
VERCEL_PROJECT="${VERCEL_PROJECT:-a23rodrigoss}"

upsert_env() {
    local key="$1"
    local value="$2"

    # Remove previous value if exists (ignore failures).
    npx --yes vercel env rm "${key}" production --yes --scope "${VERCEL_SCOPE}" >/dev/null 2>&1 || true
    printf '%s' "${value}" | npx --yes vercel env add "${key}" production --scope "${VERCEL_SCOPE}" --project "${VERCEL_PROJECT}" >/dev/null
    echo "Configured ${key} for production"
}

upsert_env DB_HOST "${DB_HOST}"
upsert_env DB_PORT "${DB_PORT}"
upsert_env DB_DATABASE "${DB_DATABASE}"
upsert_env DB_USERNAME "${DB_USERNAME}"
upsert_env DB_PASSWORD "${DB_PASSWORD}"

echo "Done. Triggering production redeploy..."
npx --yes vercel --prod --force --yes --scope "${VERCEL_SCOPE}" >/dev/null
echo "Production redeploy triggered."