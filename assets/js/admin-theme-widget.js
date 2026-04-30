(function () {
  'use strict';

  const role = document.body?.dataset?.activeRole || 'guest';
  const modal = document.getElementById('adminThemeWidgetModal');
  const openBtn = document.getElementById('adminThemeWidgetButton');
  if (role !== 'admin' || !modal || !openBtn) return;

  const form = document.getElementById('adminThemeWidgetForm');
  const resetBtn = document.getElementById('adminThemeWidgetReset');
  const inputNodes = Array.from(document.querySelectorAll('[data-theme-input]'));

  const defaults = {
    colors: { primary: '#faf5f0', secondary: '#d9b9a0', accent: '#8a5a2b', surface: '#ffffff', text: '#0f172a' },
    typography: { font_family: 'Plus Jakarta Sans', font_size_base: '16px' }
  };

  let draft = JSON.parse(JSON.stringify(defaults));

  const setDeep = (obj, path, value) => {
    const parts = path.split('.');
    let ref = obj;
    parts.slice(0, -1).forEach((key) => {
      if (!ref[key] || typeof ref[key] !== 'object') ref[key] = {};
      ref = ref[key];
    });
    ref[parts[parts.length - 1]] = value;
  };

  const getDeep = (obj, path) => path.split('.').reduce((acc, key) => (acc && acc[key] !== undefined ? acc[key] : ''), obj);

  const applyPreview = () => {
    document.documentElement.style.setProperty('--color-primary', draft.colors.primary);
    document.documentElement.style.setProperty('--color-primary-700', draft.colors.secondary);
    document.documentElement.style.setProperty('--color-accent', draft.colors.accent);
    document.documentElement.style.setProperty('--color-surface', draft.colors.surface);
    document.documentElement.style.setProperty('--color-text', draft.colors.text);
    document.documentElement.style.setProperty('--font-family-base', `${draft.typography.font_family}, sans-serif`);
    document.documentElement.style.setProperty('--font-size-base', draft.typography.font_size_base);
  };

  const syncInputs = () => {
    inputNodes.forEach((node) => {
      const key = node.getAttribute('data-theme-input');
      node.value = String(getDeep(draft, key) || '');
    });
  };

  const open = () => {
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
  };

  const close = () => {
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
  };

  const loadTheme = async () => {
    try {
      const res = await window.appApiFetch('/api/admin/theme', { method: 'GET' });
      if (res?.theme) {
        draft = { ...draft, ...res.theme, colors: { ...draft.colors, ...(res.theme.colors || {}) }, typography: { ...draft.typography, ...(res.theme.typography || {}) } };
      }
      syncInputs();
      applyPreview();
    } catch (error) {
      console.error('No se pudo cargar theme admin widget:', error);
    }
  };

  inputNodes.forEach((node) => {
    node.addEventListener('input', (event) => {
      setDeep(draft, node.getAttribute('data-theme-input'), event.target.value.trim());
      applyPreview();
    });
  });

  openBtn.addEventListener('click', open);
  modal.querySelectorAll('[data-theme-close]').forEach((btn) => btn.addEventListener('click', close));

  resetBtn?.addEventListener('click', () => {
    const ok = window.confirm('Esto restablecerá cambios masivos visuales. ¿Deseas continuar?');
    if (!ok) return;
    draft = JSON.parse(JSON.stringify(defaults));
    syncInputs();
    applyPreview();
  });

  form?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const confirmed = window.confirm('Se aplicarán cambios masivos de tema en toda la SPA. ¿Confirmas guardar?');
    if (!confirmed) return;

    try {
      await window.appApiFetch('/api/admin/theme', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ theme: draft })
      });
      window.alert('Tema actualizado correctamente.');
      close();
    } catch (error) {
      console.error('Error al guardar theme:', error);
      window.alert('No se pudo guardar el tema. Revisa permisos de admin.');
    }
  });

  loadTheme();
})();
