(() => {
  const DEMO_CREDENTIALS = {
    admin: { username: 'admin@psme.local', password: 'Admin123*' },
    associate: { username: 'asociado@psme.local', password: 'Asociado123*' },
    user: { username: 'usuario@psme.local', password: 'Usuario123*' }
  };

  function showLoginFeedback(type, message) {
    const box = document.getElementById('loginAlert');
    if (!box) return;

    box.classList.remove(
      'hidden',
      'bg-amber-50',
      'text-amber-800',
      'border',
      'border-amber-200',
      'bg-rose-50',
      'text-rose-700',
      'border-rose-200'
    );

    if (type === 'success') {
      box.classList.add('bg-amber-50', 'text-amber-800', 'border', 'border-amber-200');
    } else {
      box.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
    }

    box.textContent = message;
  }

  function normalizeRole(role) {
    return ['admin', 'associate', 'user'].includes(role) ? role : 'user';
  }

  function buildApiPath(path) {
    return path.startsWith('/api/') ? path : `/api/${path.replace(/^\/+/, '')}`;
  }

  window.fillDemoCredentials = (role) => {
    const normalizedRole = normalizeRole(role);
    const creds = DEMO_CREDENTIALS[normalizedRole];

    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('loginSubmitBtn');

    if (!usernameInput || !passwordInput || !submitBtn) return;

    usernameInput.value = creds.username;
    passwordInput.value = creds.password;

    showLoginFeedback('success', `Credenciales demo cargadas para ${normalizedRole.toUpperCase()}.`);
    submitBtn.focus();
  };

  function formatSchemaOutdatedError(payload, fallbackMessage) {
    const errorPayload = payload && typeof payload.error === "object" ? payload.error : null;
    if (!errorPayload || errorPayload.code !== "schema_outdated") {
      return fallbackMessage;
    }

    const baseMessage = errorPayload.message || fallbackMessage;
    const lines = [
      baseMessage,
      "",
      "Ejecutá en la raíz del proyecto: php scripts/migrate.php"
    ];

    const pendingMigrations = Array.isArray(errorPayload.details?.pending_migrations)
      ? errorPayload.details.pending_migrations.filter((item) => typeof item === "string" && item.trim() !== "")
      : [];

    if (pendingMigrations.length > 0) {
      lines.push("", `Migraciones pendientes: ${pendingMigrations.join(", ")}`);
    }

    return lines.join(" ");
  }

  function getErrorMessage(payload) {
    const fallbackMessage = payload?.error || "No se pudo iniciar sesión.";
    if (payload && typeof payload.error === "object" && payload.error.message) {
      return formatSchemaOutdatedError(payload, payload.error.message);
    }
    return formatSchemaOutdatedError(payload, fallbackMessage);
  }

  async function loginRequest(payload) {
    const loginUrl = buildApiPath('/api/auth/login');
    const requestConfig = {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    };

    const response = await fetch(loginUrl, requestConfig);
    const data = await response.json().catch(() => ({}));

    if (!response.ok || data.ok === false) {
      throw new Error(getErrorMessage(data));
    }

    if (data?.csrfToken) {
      window.__csrfToken = data.csrfToken;
    }

    return data;
  }

  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginSubmitBtn');
    const demoButtons = document.querySelectorAll('[data-demo-role]');

    demoButtons.forEach((button) => {
      button.addEventListener('click', () => {
        window.fillDemoCredentials(button.getAttribute('data-demo-role') || 'user');
      });
    });

    form?.addEventListener('submit', async (event) => {
      event.preventDefault();

      const username = String(document.getElementById('username')?.value || '').trim();
      const password = String(document.getElementById('password')?.value || '');

      if (!username || !password) {
        showLoginFeedback('error', 'Completa usuario/email y contraseña para continuar.');
        return;
      }

      showLoginFeedback('success', 'Validando credenciales...');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
      }

      try {
        await loginRequest({ username, email: username, password });
        window.location.assign('/dashboard');
      } catch (error) {
        showLoginFeedback('error', error instanceof Error ? error.message : 'Error inesperado al autenticar.');
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
        }
      }
    });
  });
})();
