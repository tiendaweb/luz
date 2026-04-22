(() => {
  const DEMO_CREDENTIALS = {
    admin: { username: 'admin@forospsme.com', password: 'Admin123*' },
    associate: { username: 'asociado@forospsme.com', password: 'Asociado123*' },
    user: { username: 'usuario@forospsme.com', password: 'Usuario123*' }
  };

  function showLoginFeedback(type, message) {
    const box = document.getElementById('loginAlert');
    if (!box) return;

    box.classList.remove(
      'hidden',
      'bg-emerald-50',
      'text-emerald-800',
      'border',
      'border-emerald-200',
      'bg-rose-50',
      'text-rose-700',
      'border-rose-200'
    );

    if (type === 'success') {
      box.classList.add('bg-emerald-50', 'text-emerald-800', 'border', 'border-emerald-200');
    } else {
      box.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
    }

    box.textContent = message;
  }

  function normalizeRole(role) {
    return ['admin', 'associate', 'user'].includes(role) ? role : 'user';
  }

  function buildApiCandidates(path) {
    const normalizedPath = path.startsWith('/api/') ? path : `/api/${path.replace(/^\/+/, '')}`;
    return [normalizedPath, normalizedPath.replace(/^\/api\//, '/public/api/')];
  }

  window.fillDemoCredentials = (role) => {
    const normalizedRole = normalizeRole(role);
    const creds = DEMO_CREDENTIALS[normalizedRole];

    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const roleSelect = document.getElementById('role');
    const submitBtn = document.getElementById('loginSubmitBtn');

    if (!usernameInput || !passwordInput || !roleSelect || !submitBtn) return;

    usernameInput.value = creds.username;
    passwordInput.value = creds.password;
    roleSelect.value = normalizedRole;

    showLoginFeedback('success', `Credenciales demo cargadas para ${normalizedRole.toUpperCase()}.`);
    submitBtn.focus();
  };

  async function loginRequest(payload) {
    const [primaryUrl, fallbackUrl] = buildApiCandidates('/api/auth/login.php');
    const requestConfig = {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    };

    const send = async (url) => {
      const response = await fetch(url, requestConfig);
      const data = await response.json().catch(() => ({}));
      return { response, data };
    };

    let result = await send(primaryUrl);
    if (result.response.status === 404 && fallbackUrl !== primaryUrl) {
      result = await send(fallbackUrl);
    }

    if (!result.response.ok || result.data.ok === false) {
      throw new Error(result.data.error || 'No se pudo iniciar sesión.');
    }

    return result.data;
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
      const role = normalizeRole(String(document.getElementById('role')?.value || 'user'));

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
        await loginRequest({ username, email: username, password, role });
        window.location.assign(`index.php#view-dashboard?role=${encodeURIComponent(role)}`);
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
