#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-http://localhost:8080}"

check_url() {
  local path="$1"
  local status
  status=$(curl -sS -o /dev/null -w "%{http_code}" "$BASE_URL$path")
  if [[ "$status" != "200" && "$status" != "302" ]]; then
    echo "[FAIL] $path -> HTTP $status"
    return 1
  fi
  echo "[OK]   $path -> HTTP $status"
}

echo "Running local smoke checks against $BASE_URL"
check_url "/home.php"
check_url "/products.php"
check_url "/products.php?categoria=alimentacion&q=miel&orden=precio_asc"
check_url "/auth.php"
check_url "/cart.php"
check_url "/orders.php"
check_url "/profile.php"

echo "Smoke checks completed"
