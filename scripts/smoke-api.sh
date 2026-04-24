#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PORT="${SMOKE_API_PORT:-8787}"
BASE_URL="http://127.0.0.1:${PORT}"
COOKIE_JAR="$(mktemp)"
SERVER_LOG="/tmp/luz-smoke-server.log"

cleanup() {
  if [[ -n "${SERVER_PID:-}" ]] && kill -0 "$SERVER_PID" 2>/dev/null; then
    kill "$SERVER_PID" || true
    wait "$SERVER_PID" 2>/dev/null || true
  fi
  rm -f "$COOKIE_JAR"
}
trap cleanup EXIT

php -S "127.0.0.1:${PORT}" -t "$ROOT_DIR/public" >"$SERVER_LOG" 2>&1 &
SERVER_PID=$!
sleep 1

php <<'PHP'
<?php
require __DIR__ . '/app/Database/connection.php';
$pdo = app_db_connection();
$pdo->exec("INSERT OR IGNORE INTO roles (slug, name) VALUES ('admin', 'Administrador')");
$roleStmt = $pdo->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
$roleStmt->execute(['slug' => 'admin']);
$roleId = (int)$roleStmt->fetchColumn();
$userStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$userStmt->execute(['email' => 'admin@psme.local']);
$userId = (int)$userStmt->fetchColumn();
if ($userId < 1) {
    $insert = $pdo->prepare('INSERT INTO users (full_name, email, role_id, password_hash, updated_at) VALUES (:name, :email, :role_id, :password_hash, CURRENT_TIMESTAMP)');
    $insert->execute([
        'name' => 'Admin Demo',
        'email' => 'admin@psme.local',
        'role_id' => $roleId,
        'password_hash' => password_hash('Admin123*', PASSWORD_DEFAULT),
    ]);
}
PHP

extract_json_field() {
  local payload="$1"
  local field="$2"
  python - "$payload" "$field" <<'PY'
import json,sys
obj=json.loads(sys.argv[1])
field=sys.argv[2]
cur=obj
for part in field.split('.'):
    if isinstance(cur, dict):
        cur=cur.get(part)
    else:
        cur=None
        break
print('' if cur is None else cur)
PY
}

ME_GUEST=$(curl -sS -c "$COOKIE_JAR" -b "$COOKIE_JAR" "$BASE_URL/api/auth/me.php")
CSRF_TOKEN=$(extract_json_field "$ME_GUEST" "csrfToken")
[[ -n "$CSRF_TOKEN" ]]

LOGIN_BODY='{"email":"admin@psme.local","password":"Admin123*"}'
LOGIN_RESPONSE=$(curl -sS -c "$COOKIE_JAR" -b "$COOKIE_JAR" \
  -H "Content-Type: application/json" \
  -X POST "$BASE_URL/api/auth/login.php" \
  --data "$LOGIN_BODY")
LOGIN_OK=$(extract_json_field "$LOGIN_RESPONSE" "ok")
[[ "$LOGIN_OK" == "True" || "$LOGIN_OK" == "true" ]]

SESSION_CSRF=$(extract_json_field "$LOGIN_RESPONSE" "csrfToken")
[[ -n "$SESSION_CSRF" ]]

ME_AUTH=$(curl -sS -c "$COOKIE_JAR" -b "$COOKIE_JAR" "$BASE_URL/api/auth/me.php")
AUTH_OK=$(extract_json_field "$ME_AUTH" "authenticated")
[[ "$AUTH_OK" == "True" || "$AUTH_OK" == "true" ]]

LOGOUT_RESPONSE=$(curl -sS -c "$COOKIE_JAR" -b "$COOKIE_JAR" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-Token: $SESSION_CSRF" \
  -X POST "$BASE_URL/api/auth/logout.php" \
  --data '{}')
LOGOUT_OK=$(extract_json_field "$LOGOUT_RESPONSE" "ok")
[[ "$LOGOUT_OK" == "True" || "$LOGOUT_OK" == "true" ]]

echo "Smoke API OK"
