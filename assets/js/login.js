(() => {
  const DEMO_CREDENTIALS = {
    admin: [
      { username: 'admin@psme.local', password: 'Admin123*' }
    ],
    associate: [
      { username: 'asociado@psme.local', password: 'Asociado123*' },
      { username: 'asociada.red@psme.local', password: 'demo1234' },
      { username: 'asociado', password: 'Asociado123*' }
    ],
    user: [
      { username: 'usuario@psme.local', password: 'Usuario123*' },
      { username: 'usuario.directo@psme.local', password: 'demo1234' },
      { username: 'referido.aprobado@psme.local', password: 'demo1234' },
      { username: 'usuario', password: 'Usuario123*' }
    ]
  };

  function showLoginFeedback(type, message) {
    const box = document.getElementById('loginAlert');
    if (!box) return;

    box.classList.remove(
      'hidden',
      'bg-blue-50',
      'text-blue-800',
      'border',
      'border-blue-200',
      'bg-rose-50',
      'text-rose-700',
      'border-rose-200'
    );

    if (type === 'success') {
      box.classList.add('bg-blue-50', 'text-blue-800', 'border', 'border-blue-200');
    } else {
      box.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
    }

    box.textContent = message;
  }

  function normalizeRole(role) {
    return ['admin', 'associate', 'user'].includes(role) ? role : 'user';
  }

  function buildApiPath(path) {
    const normalized = path.replace(/^\/+/, '');
    const cleanPath = normalized.startsWith('api/') ? normalized.slice(4) : normalized;

    return [
      `/api/${cleanPath}`,
      `/public/api/${cleanPath}`,
      `api/${cleanPath}`,
      `public/api/${cleanPath}`
    ];
  }

  window.fillDemoCredentials = (role) => {
    const normalizedRole = normalizeRole(role);
    const creds = DEMO_CREDENTIALS[normalizedRole][0];

    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('loginSubmitBtn');

    if (!usernameInput || !passwordInput || !submitBtn) return;

    usernameInput.value = creds.username;
    passwordInput.value = creds.password;

    showLoginFeedback('success', `Credenciales demo cargadas para ${normalizedRole.toUpperCase()}.`);
    submitBtn.focus();
  };

  async function tryDemoLogin(role) {
    const normalizedRole = normalizeRole(role);
    const candidates = DEMO_CREDENTIALS[normalizedRole] || [];

    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('loginSubmitBtn');

    if (!usernameInput || !passwordInput || !submitBtn || candidates.length === 0) return false;

    showLoginFeedback('success', `Probando credenciales demo para ${normalizedRole.toUpperCase()}...`);
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-70', 'cursor-not-allowed');

    try {
      for (const candidate of candidates) {
        usernameInput.value = candidate.username;
        passwordInput.value = candidate.password;

        try {
          await loginRequest({ username: candidate.username, email: candidate.username, password: candidate.password });
          window.location.assign('/dashboard');
          return true;
        } catch (error) {
          // Intentar siguiente credencial demo compatible.
        }
      }

      showLoginFeedback('error', 'No se encontró una credencial demo válida para este perfil. Ejecutá: php scripts/seed.php');
      return false;
    } finally {
      submitBtn.disabled = false;
      submitBtn.classList.remove('opacity-70', 'cursor-not-allowed');
    }
  }

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
    const requestConfig = {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    };
    const candidateUrls = buildApiPath('/api/auth/login');
    let lastError = null;

    for (const loginUrl of candidateUrls) {
      const response = await fetch(loginUrl, requestConfig);
      const data = await response.json().catch(() => ({}));

      if (response.status === 404) {
        lastError = new Error('Endpoint de autenticación no encontrado.');
        continue;
      }

      if (!response.ok || data.ok === false) {
        throw new Error(getErrorMessage(data));
      }

      if (data?.csrfToken) {
        window.__csrfToken = data.csrfToken;
      }

      return data;
    }

    throw lastError || new Error('No se pudo conectar con la API de autenticación.');
  }

  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('loginSubmitBtn');
    const demoButtons = document.querySelectorAll('[data-demo-role]');

    demoButtons.forEach((button) => {
      button.addEventListener('click', async () => {
        window.fillDemoCredentials(button.getAttribute('data-demo-role') || 'user');
        await tryDemoLogin(button.getAttribute('data-demo-role') || 'user');
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
