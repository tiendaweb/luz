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
    colors: {
      primary: '#faf5f0',
      secondary: '#d9b9a0',
      accent: '#8a5a2b',
      surface: '#ffffff',
      text: '#0f172a',
      border: '#e2e8f0',
      text_muted: '#475569',
      primary_contrast: '#4e3b2a',
      accent_700: '#6f4620',
      accent_contrast: '#ffffff',
      status_approved_bg: '#dcfce7',
      status_approved_text: '#14532d',
      status_pending_bg: '#fef3c7',
      status_pending_text: '#78350f',
      status_rejected_bg: '#fee2e2',
      status_rejected_text: '#7f1d1d'
    },
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

  const toRgb = (hex) => {
    if (!/^#[0-9a-fA-F]{6}$/.test(hex || '')) return null;
    return [parseInt(hex.slice(1, 3), 16), parseInt(hex.slice(3, 5), 16), parseInt(hex.slice(5, 7), 16)];
  };
  const toHex = (rgb) => `#${rgb.map((x) => x.toString(16).padStart(2, '0')).join('')}`;
  const mix = (a, b, ratio = 0.5) => {
    const rgbA = toRgb(a); const rgbB = toRgb(b);
    if (!rgbA) return b; if (!rgbB) return a;
    return toHex(rgbA.map((ch, i) => Math.round(ch * (1 - ratio) + rgbB[i] * ratio)));
  };
  const luminance = (hex) => {
    const rgb = toRgb(hex) || [0, 0, 0];
    const srgb = rgb.map((v) => {
      const c = v / 255;
      return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
    });
    return 0.2126 * srgb[0] + 0.7152 * srgb[1] + 0.0722 * srgb[2];
  };
  const contrastColor = (bg) => (luminance(bg) > 0.45 ? '#0f172a' : '#ffffff');
  const pick = (value, fallback) => (value && value.trim() ? value : fallback);

  const resolvedColors = () => {
    const c = draft.colors || {};
    const primary = pick(c.primary, defaults.colors.primary);
    const surface = pick(c.surface, defaults.colors.surface);
    const accent = pick(c.accent, defaults.colors.accent);
    const text = pick(c.text, defaults.colors.text);
    return {
      primary,
      primary700: pick(c.secondary, mix(primary, '#000000', 0.22)),
      primaryContrast: pick(c.primary_contrast, contrastColor(primary)),
      accent,
      accent700: pick(c.accent_700, mix(accent, '#000000', 0.2)),
      accentContrast: pick(c.accent_contrast, contrastColor(accent)),
      surface,
      surfaceMuted: mix(surface, '#0f172a', 0.03),
      border: pick(c.border, mix(surface, '#0f172a', 0.12)),
      text,
      textMuted: pick(c.text_muted, mix(text, surface, 0.35)),
      approvedBg: pick(c.status_approved_bg, mix('#22c55e', '#ffffff', 0.8)),
      approvedText: pick(c.status_approved_text, contrastColor(pick(c.status_approved_bg, '#dcfce7'))),
      pendingBg: pick(c.status_pending_bg, mix('#f59e0b', '#ffffff', 0.78)),
      pendingText: pick(c.status_pending_text, contrastColor(pick(c.status_pending_bg, '#fef3c7'))),
      rejectedBg: pick(c.status_rejected_bg, mix('#ef4444', '#ffffff', 0.8)),
      rejectedText: pick(c.status_rejected_text, contrastColor(pick(c.status_rejected_bg, '#fee2e2')))
    };
  };

  const applyPreview = () => {
    const colors = resolvedColors();
    document.documentElement.style.setProperty('--color-primary', colors.primary);
    document.documentElement.style.setProperty('--color-primary-700', colors.primary700);
    document.documentElement.style.setProperty('--color-primary-contrast', colors.primaryContrast);
    document.documentElement.style.setProperty('--color-accent', colors.accent);
    document.documentElement.style.setProperty('--color-accent-700', colors.accent700);
    document.documentElement.style.setProperty('--color-accent-contrast', colors.accentContrast);
    document.documentElement.style.setProperty('--color-surface', colors.surface);
    document.documentElement.style.setProperty('--color-surface-muted', colors.surfaceMuted);
    document.documentElement.style.setProperty('--color-border', colors.border);
    document.documentElement.style.setProperty('--color-text', colors.text);
    document.documentElement.style.setProperty('--color-text-muted', colors.textMuted);
    document.documentElement.style.setProperty('--color-status-approved-bg', colors.approvedBg);
    document.documentElement.style.setProperty('--color-status-approved-text', colors.approvedText);
    document.documentElement.style.setProperty('--color-status-pending-bg', colors.pendingBg);
    document.documentElement.style.setProperty('--color-status-pending-text', colors.pendingText);
    document.documentElement.style.setProperty('--color-status-rejected-bg', colors.rejectedBg);
    document.documentElement.style.setProperty('--color-status-rejected-text', colors.rejectedText);
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
