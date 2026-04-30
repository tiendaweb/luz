#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${BASE_URL:-http://127.0.0.1:8787}"
OUT_MD="${1:?markdown output path required}"
OUT_JSON="${2:?json output path required}"
mkdir -p "$(dirname "$OUT_MD")" "$(dirname "$OUT_JSON")"
COOKIE_JAR="$(mktemp)"
BODY_FILE="/tmp/business_validation_body.$$"
trap 'rm -f "$COOKIE_JAR" "$BODY_FILE"' EXIT

json_field(){ python - "$1" "$2" <<'PY'
import json,sys
try:data=json.loads(sys.argv[1])
except Exception:
    print('')
    raise SystemExit
cur=data
for p in sys.argv[2].split('.'):
    if isinstance(cur,dict): cur=cur.get(p)
    else: cur=None
print('' if cur is None else cur)
PY
}

request(){
  local method="$1" path="$2" data="${3:-}" csrf="${4:-}" content_type="${5:-application/json}"
  local code
  if [[ -n "$data" ]]; then
    code=$(curl -sS -o "$BODY_FILE" -w "%{http_code}" -c "$COOKIE_JAR" -b "$COOKIE_JAR" -H "Content-Type: $content_type" ${csrf:+-H "X-CSRF-Token: $csrf"} -X "$method" "$BASE_URL$path" --data "$data")
  else
    code=$(curl -sS -o "$BODY_FILE" -w "%{http_code}" -c "$COOKIE_JAR" -b "$COOKIE_JAR" -X "$method" "$BASE_URL$path")
  fi
  echo "$code"
}

admin_login(){
  request GET /api/auth/me.php >/dev/null
  local guest_body=$(cat "$BODY_FILE")
  local csrf=$(json_field "$guest_body" csrfToken)
  request POST /api/auth/login.php '{"email":"admin@psme.local","password":"Admin123*"}' "$csrf" >/dev/null
  local login_body=$(cat "$BODY_FILE")
  json_field "$login_body" csrfToken
}

ADMIN_CSRF=$(admin_login)

# discover sample ids
request GET /api/admin/certificates.php >/dev/null
cert_list_body=$(cat "$BODY_FILE")
ATT_USER=$(python - "$cert_list_body" <<'PY'
import json,sys
items=json.loads(sys.argv[1]).get('items',[])
first=items[0] if items else {}
print(first.get('user_id',''))
PY
)
ATT_FORUM=$(python - "$cert_list_body" <<'PY'
import json,sys
items=json.loads(sys.argv[1]).get('items',[])
first=items[0] if items else {}
print(first.get('forum_id',''))
PY
)

if [[ -z "$ATT_USER" || -z "$ATT_FORUM" ]]; then
  echo "No hay datos base para validación" >&2
  exit 1
fi

python - "$BASE_URL" "$OUT_MD" "$OUT_JSON" "$ATT_USER" "$ATT_FORUM" "$ADMIN_CSRF" "$COOKIE_JAR" <<'PY'
import json,sys,subprocess,shlex,os
base,md_path,json_path,user_id,forum_id,csrf,cookie=sys.argv[1:]

def call(method,path,data=None,csrf_token=None,accept_json=True):
    bodyf='/tmp/business_validation_py_body.json'
    cmd=['curl','-sS','-o',bodyf,'-w','%{http_code}','-c',cookie,'-b',cookie,'-X',method,f'{base}{path}']
    if data is not None:
        cmd.extend(['-H','Content-Type: application/json','--data',json.dumps(data)])
    if csrf_token:
        cmd.extend(['-H',f'X-CSRF-Token: {csrf_token}'])
    code=subprocess.check_output(cmd,text=True).strip()
    body=open(bodyf).read()
    return int(code),body

cases=[]

def add_case(group,name,pre,req,expected,actual,status,details=''):
    cases.append({'group':group,'name':name,'precondition':pre,'request':req,'expected':expected,'actual':actual,'result':status,'details':details})

# 1 certificados
code,body=call('GET','/api/admin/certificates.php?type=attendance')
add_case('Certificados','Elegibilidad attendance','Admin autenticado','GET /api/admin/certificates.php?type=attendance','200 y lista con type=attendance',f'{code}', 'PASS' if code==200 and '"type":"attendance"' in body else 'FAIL')

code,body=call('GET','/api/admin/certificates.php?type=completion')
add_case('Certificados','Elegibilidad completion','Admin autenticado','GET /api/admin/certificates.php?type=completion','200 y lista con type=completion',f'{code}', 'PASS' if code==200 and '"type":"completion"' in body else 'FAIL')

code,body=call('POST','/api/admin/certificates.php',{'userId':int(user_id),'forumId':int(forum_id),'type':'attendance'},csrf)
ok1 = code==200
first=json.loads(body)
cert_id=first.get('certificateId')
add_case('Certificados','Generación única (1ra)','Usuario elegible','POST /api/admin/certificates.php attendance','200 y certificateId',f'{code} cert={cert_id}', 'PASS' if ok1 and cert_id else 'FAIL')

code2,body2=call('POST','/api/admin/certificates.php',{'userId':int(user_id),'forumId':int(forum_id),'type':'attendance'},csrf)
msg=json.loads(body2).get('message','') if code2==200 else ''
add_case('Certificados','No duplicado (2da)','Certificado ya emitido','POST /api/admin/certificates.php attendance repetido','200 + mensaje ya existe',f'{code2} msg={msg}', 'PASS' if code2==200 and 'ya existe' in msg.lower() else 'FAIL')

code,body=call('GET',f'/api/admin/certificate-view.php?id={cert_id}&type=attendance')
integrity=('CERTIFICADO' in body.upper() and str(user_id) not in body)
add_case('Certificados','Visualización/descarga','Certificado emitido','GET /api/admin/certificate-view.php?id=...','200 HTML renderizado',f'{code}', 'PASS' if code==200 else 'FAIL')
add_case('Certificados','Integridad plantilla/datos','Template v1 y datos dinámicos','Validación de HTML de certificado','Contiene estructura de certificado y datos renderizados',f'len={len(body)}', 'PASS' if integrity else 'FAIL')

# 2 CRUD críticos
crud_targets=[
    ('Users','/api/admin/users.php','GET',None,200,'403', '/api/admin/users.php'),
    ('Registrations','/api/admin/registrations.php','GET',None,200,'403','/api/admin/registrations.php'),
    ('Blog','/api/admin/blog/list.php','GET',None,200,'403','/api/admin/blog/list.php'),
    ('Pages','/api/admin/pages/list.php','GET',None,200,'403','/api/admin/pages/list.php'),
    ('Settings','/api/admin/settings.php','GET',None,200,'200','/api/admin/settings.php'),
]
for name,path,method,data,ok,forbidden,req in crud_targets:
    c,_=call(method,path,data,csrf)
    add_case('CRUD críticos',f'{name} leer', 'Admin autenticado', f'{method} {req}', str(ok), str(c), 'PASS' if c==ok else 'FAIL')

# create/update/delete for blog+pages, update for users/settings, delete reg invalid
c,b=call('POST','/api/admin/blog/create.php',{'title':'QA release','slug':'qa-release-validation','excerpt':'x','content':'contenido','status':'draft'},csrf)
blog_id = json.loads(b).get('id') if c in (200,201) else None
add_case('CRUD críticos','Blog crear','Admin autenticado','POST /api/admin/blog/create.php','201/200',str(c),'PASS' if c in (200,201) and blog_id else 'FAIL')
if blog_id:
    c,_=call('PATCH','/api/admin/blog/update.php',{'id':blog_id,'title':'QA release up','slug':'qa-release-validation','excerpt':'x','content':'contenido','status':'published'},csrf)
    add_case('CRUD críticos','Blog actualizar','Blog creado', 'PATCH /api/admin/blog/update.php','200',str(c),'PASS' if c==200 else 'FAIL')
    c,_=call('DELETE',f'/api/admin/blog/delete.php?id={blog_id}',None,csrf)
    add_case('CRUD críticos','Blog eliminar','Blog creado','DELETE /api/admin/blog/delete.php?id=...','200',str(c),'PASS' if c==200 else 'FAIL')

c,b=call('POST','/api/admin/pages/create.php',{'title':'QA Page','slug':'qa-page-validation','content':'Contenido QA','status':'draft'},csrf)
page_id=json.loads(b).get('id') if c in (200,201) else None
add_case('CRUD críticos','Pages crear','Admin autenticado','POST /api/admin/pages/create.php','201/200',str(c),'PASS' if c in (200,201) and page_id else 'FAIL')
if page_id:
    c,_=call('PATCH','/api/admin/pages/update.php',{'id':page_id,'title':'QA Page Up','slug':'qa-page-validation','content':'Contenido QA','status':'published'},csrf)
    add_case('CRUD críticos','Pages actualizar','Page creada','PATCH /api/admin/pages/update.php','200',str(c),'PASS' if c==200 else 'FAIL')
    c,_=call('DELETE',f'/api/admin/pages/delete.php?id={page_id}',None,csrf)
    add_case('CRUD críticos','Pages eliminar','Page creada','DELETE /api/admin/pages/delete.php?id=...','200',str(c),'PASS' if c==200 else 'FAIL')

c,_=call('PATCH','/api/admin/users.php',{'userId':999999,'isValidated':True,'isPaid':True},csrf)
add_case('CRUD críticos','Users actualizar inexistente','Admin autenticado','PATCH /api/admin/users.php userId inexistente','404 controlado',str(c),'PASS' if c==404 else 'FAIL')

c,_=call('PATCH','/api/admin/settings.php',{'settings':{'public_email_primary':'correo-invalido'}},csrf)
add_case('CRUD críticos','Settings validación 422','Admin autenticado','PATCH /api/admin/settings.php email inválido','422',str(c),'PASS' if c==422 else 'FAIL')

c,_=call('DELETE','/api/admin/registrations.php?id=0',None,csrf)
add_case('CRUD críticos','Registrations error 422','Admin autenticado','DELETE /api/admin/registrations.php?id=0','422',str(c),'PASS' if c==422 else 'FAIL')

# permiso rol
subprocess.check_output(['curl','-sS','-o','/tmp/business_validation_py_body.json','-w','%{http_code}','-c',cookie,'-b',cookie,'-X','POST',f'{base}/api/auth/logout.php','-H',f'X-CSRF-Token: {csrf}','-H','Content-Type: application/json','--data','{}'],text=True)
# login usuario
subprocess.check_output(['curl','-sS','-o','/tmp/business_validation_py_body.json','-w','%{http_code}','-c',cookie,'-b',cookie,'-X','POST',f'{base}/api/auth/login.php','-H','Content-Type: application/json','--data',json.dumps({'email':'usuario@psme.local','password':'Usuario123*'})],text=True)
c,_=call('GET','/api/admin/users.php')
add_case('CRUD críticos','Permisos por rol 403','Usuario autenticado no admin','GET /api/admin/users.php','403',str(c),'PASS' if c==403 else 'FAIL')

summary={'total':len(cases),'passed':sum(1 for c in cases if c['result']=='PASS'),'failed':sum(1 for c in cases if c['result']=='FAIL')}
with open(json_path,'w') as f: json.dump({'summary':summary,'cases':cases},f,indent=2,ensure_ascii=False)
with open(md_path,'w') as f:
    f.write('# Reporte final de liberación - Validación funcional por flujo de negocio\n\n')
    f.write(f"**Resultado:** {summary['passed']}/{summary['total']} casos PASS; FAIL={summary['failed']}\n\n")
    f.write('| Flujo | Caso | Precondición | Request | Respuesta esperada | Resultado |\n')
    f.write('|---|---|---|---|---|---|\n')
    for c in cases:
      f.write(f"| {c['group']} | {c['name']} | {c['precondition']} | `{c['request']}` | {c['expected']} | **{c['result']}** ({c['actual']}) |\n")
print('done')
PY
