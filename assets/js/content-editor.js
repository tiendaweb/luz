(function(){
  const state = { enabled:false, role:document.body?.dataset?.activeRole || 'guest' };
  const admin = state.role === 'admin';
  function badge(el,msg,cls){
    let b=el.nextElementSibling; if(!b || !b.classList.contains('content-edit-status')){b=document.createElement('span'); b.className='content-edit-status text-xs ml-2'; el.insertAdjacentElement('afterend',b);} b.textContent=msg; b.className='content-edit-status text-xs ml-2 '+cls;
  }
  async function save(el,val){
    badge(el,'Guardando...','text-amber-600');
    try {
      await window.appApiFetch('/api/admin/content-blocks',{method:'PATCH',headers:{'Content-Type':'application/json','X-CSRF-Token':window.__csrfToken||''},body:JSON.stringify({blockKey:el.dataset.contentKey,context:el.dataset.contentContext,locale:el.dataset.contentLocale||'es',contentType:el.dataset.contentType||'text',value:val})});
      el.textContent=val; badge(el,'Guardado','text-emerald-600');
    } catch(e){ badge(el,'Error al guardar','text-rose-600'); }
  }
  function makeEditable(el){
    el.classList.add('cursor-pointer','outline-dashed','outline-1','outline-slate-300');
    el.addEventListener('click',()=>{
      if(!state.enabled) return;
      const type=el.dataset.contentType||'text';
      const input = type === 'textarea' ? document.createElement('textarea') : document.createElement('input');
      input.value=el.textContent.trim(); input.className='w-full rounded border p-2 text-sm';
      el.replaceWith(input); input.focus();
      const done=()=>{ const v=input.value.trim(); input.replaceWith(el); if(v && v!==el.textContent.trim()) save(el,v);};
      input.addEventListener('blur',done,{once:true});
      input.addEventListener('keydown',(ev)=>{if(ev.key==='Enter' && type!=='textarea'){ev.preventDefault(); input.blur();}});
    });
  }
  function addToggle(){
    if(!admin) return;
    const btn=document.createElement('button');
    btn.textContent='Editar contenido'; btn.className='fixed bottom-24 right-6 z-[75] px-4 py-2 rounded-xl bg-slate-900 text-white text-sm';
    btn.onclick=()=>{state.enabled=!state.enabled; btn.textContent=state.enabled?'Salir edición':'Editar contenido';};
    document.body.appendChild(btn);
  }
  document.addEventListener('DOMContentLoaded',()=>{
    addToggle();
    if(!admin) return;
    document.querySelectorAll('[data-content-key]').forEach(makeEditable);
  });
})();
