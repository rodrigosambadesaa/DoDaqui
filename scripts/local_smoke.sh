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

check_contains() {
  local path="$1"
  local pattern="$2"
  local label="$3"
  if ! curl -sS "$BASE_URL$path" | grep -q "$pattern"; then
    echo "[FAIL] $label not found in $path"
    return 1
  fi
  echo "[OK]   $label found in $path"
}

echo "Running local smoke checks against $BASE_URL"
check_url "/home.php"
check_url "/products.php"
check_url "/products.php?categoria=alimentacion&q=miel&orden=precio_asc"
check_url "/auth.php"
check_url "/cart.php"
check_url "/orders.php"
check_url "/profile.php"
check_contains "/products.php" 'name="orden"' "Sort select"
check_contains "/products.php" '>Aplicar</button>' "Sort apply button"

echo "Smoke checks completed"
