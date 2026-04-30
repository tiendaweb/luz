#!/usr/bin/env bash
set -euo pipefail
BASE_URL="${BASE_URL:-http://127.0.0.1:8787}"
OUT="${1:?json output path required}"
check(){ curl -sS -o /dev/null -w "%{http_code}" "$BASE_URL$1"; }
cat > "$OUT" <<JSON
{"module":"ui",
"navigation_spa_hash":{"login_hash":$(check "/login#view-login")},
"pages":{"home":$(check "/"),"login":$(check "/login"),"foros":$(check "/foros.php"),"blog":$(check "/blog.php"),"index_hash":$(check "/index.php#view-home")}}
JSON
