#!/usr/bin/env bash
set -euo pipefail
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PORT="${E2E_PORT:-8787}"
BASE_URL="http://127.0.0.1:${PORT}"
REPORT_DIR="$ROOT_DIR/tests/e2e/reports"
mkdir -p "$REPORT_DIR"

SERVER_LOG="/tmp/luz-e2e-server.log"
cleanup(){
  if [[ -n "${SERVER_PID:-}" ]] && kill -0 "$SERVER_PID" 2>/dev/null; then
    kill "$SERVER_PID" || true
    wait "$SERVER_PID" 2>/dev/null || true
  fi
}
trap cleanup EXIT

php -S "127.0.0.1:${PORT}" -t "$ROOT_DIR/public" >"$SERVER_LOG" 2>&1 &
SERVER_PID=$!
sleep 1

API_JSON="$REPORT_DIR/api.json"
UI_JSON="$REPORT_DIR/ui.json"
FINAL_JSON="$REPORT_DIR/final-report.json"
FINAL_MD="$REPORT_DIR/final-report.md"

BASE_URL="$BASE_URL" "$ROOT_DIR/tests/e2e/cases/api_matrix.sh" "$API_JSON"
BASE_URL="$BASE_URL" "$ROOT_DIR/tests/e2e/cases/ui_matrix.sh" "$UI_JSON"

python - "$API_JSON" "$UI_JSON" "$FINAL_JSON" "$FINAL_MD" <<'PY'
import json,sys
api=json.load(open(sys.argv[1])); ui=json.load(open(sys.argv[2]))
modules={"api":api,"ui":ui}
summary={}
critical_failed=False
for name,data in modules.items():
    codes=[]
    def walk(v):
        if isinstance(v,int): codes.append(v)
        elif isinstance(v,dict):
            for x in v.values(): walk(x)
        elif isinstance(v,list):
            for x in v: walk(x)
    walk(data)
    has_500=any(c>=500 for c in codes)
    bad=any(not (c in (200,201) or 400<=c<500) for c in codes)
    status='pass' if not has_500 and not bad else 'fail'
    summary[name]={"status":status,"total":len(codes),"has_500":has_500}
    critical_failed=critical_failed or status=='fail'

final={"modules":summary,"critical_failed":critical_failed,"no_deploy":critical_failed,"artifacts":{"api":"tests/e2e/reports/api.json","ui":"tests/e2e/reports/ui.json"}}
json.dump(final,open(sys.argv[3],'w'),indent=2)
with open(sys.argv[4],'w') as f:
    f.write("# Reporte E2E de Release\n\n")
    for m,s in summary.items():
      f.write(f"- **{m}**: {s['status'].upper()} (checks={s['total']}, has_500={s['has_500']})\\n")
    f.write(f"\n## Criterio de deploy\n- no_deploy: **{str(critical_failed).lower()}**\\n")
print('PASS' if not critical_failed else 'FAIL')
PY
