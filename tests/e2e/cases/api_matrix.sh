#!/usr/bin/env bash
set -euo pipefail
BASE_URL="${BASE_URL:-http://127.0.0.1:8787}"
COOKIE_JAR="$(mktemp)"
trap 'rm -f "$COOKIE_JAR" /tmp/e2e_body.$$ 2>/dev/null || true' EXIT
OUT="${1:?json output path required}"

extract_json_field(){ python - "$1" "$2" <<'PY'
import json,sys
try: obj=json.loads(sys.argv[1])
except Exception: print(''); raise SystemExit
cur=obj
for p in sys.argv[2].split('.'): cur=cur.get(p) if isinstance(cur,dict) else None
print('' if cur is None else cur)
PY
}

request(){
  local method="$1" path="$2" data="${3:-}" csrf="${4:-}"
  local code
  if [[ -n "$data" ]]; then
    code=$(curl -sS -o /tmp/e2e_body.$$ -w "%{http_code}" -c "$COOKIE_JAR" -b "$COOKIE_JAR" -H 'Content-Type: application/json' ${csrf:+-H "X-CSRF-Token: $csrf"} -X "$method" "$BASE_URL$path" --data "$data")
  else
    code=$(curl -sS -o /tmp/e2e_body.$$ -w "%{http_code}" -c "$COOKIE_JAR" -b "$COOKIE_JAR" -X "$method" "$BASE_URL$path")
  fi
  echo "$code"
}

role_auth(){
  local role="$1" email="$2" pass="$3"
  request GET /api/auth/me.php >/dev/null; local guest_body=$(cat /tmp/e2e_body.$$)
  local csrf=$(extract_json_field "$guest_body" csrfToken)
  local login_code=$(request POST /api/auth/login.php "{\"email\":\"$email\",\"password\":\"$pass\"}")
  local login_body=$(cat /tmp/e2e_body.$$)
  local session_csrf=$(extract_json_field "$login_body" csrfToken)
  local me_code=$(request GET /api/auth/me.php)
  local logout_code=$(request POST /api/auth/logout.php '{}' "$session_csrf")
  printf '{"role":"%s","auth":{"login":%s,"me":%s,"logout":%s}}' "$role" "$login_code" "$me_code" "$logout_code"
}

visitor_me=$(request GET /api/auth/me.php)
visitor=$(printf '{"role":"visitante","auth":{"me":%s}}' "$visitor_me")
usuario=$(role_auth usuario usuario@psme.local 'Usuario123*')
asociado=$(role_auth asociado asociado@psme.local 'Asociado123*')
admin=$(role_auth admin admin@psme.local 'Admin123*')

cat > "$OUT" <<JSON
{"module":"api","roles":[$visitor,$usuario,$asociado,$admin],
"admin_crud":{"pages_list":$(request GET /api/admin/pages/list.php),"blog_list":$(request GET /api/admin/blog/list.php),"users_list":$(request GET /api/admin/users/list.php),"registrations":$(request GET /api/admin/registrations.php)},
"associate_flows":{"registrations":$(request GET /api/associate/registrations.php),"offer":$(request GET /api/referrals/offer.php)},
"user_flows":{"registrations_me":$(request GET /api/registrations/me.php),"certificates":$(request GET /api/user/certificates.php)},
"certificates":{"admin_list":$(request GET /api/admin/certificates.php),"user_list":$(request GET /api/user/certificates.php)}}
JSON
