(() => {
  const normalizeRole = (roleName) => window.__navigation?.normalizeRole(roleName) || "guest";

  let csrfToken = null;

  function getErrorMessage(payload) {
    if (payload && typeof payload.error === "object" && payload.error.message) return payload.error.message;
    return payload?.error || "Error de comunicación con el servidor.";
  }

  function updateSecurityState(payload) {
    if (payload?.csrfToken) {
      csrfToken = payload.csrfToken;
      window.__csrfToken = payload.csrfToken;
    }
  }

  async function apiFetch(url, options = {}) {
    const normalizedUrl = url.startsWith("/api/") ? url : `/api/${url.replace(/^\/+/, "")}`;
    const fallbackUrl = normalizedUrl.replace(/^\/api\//, "/public/api/");

    const method = String(options.method || "GET").toUpperCase();
    const headers = { "Content-Type": "application/json", ...(options.headers || {}) };
    if (["POST", "PUT", "PATCH", "DELETE"].includes(method) && csrfToken) {
      headers["X-CSRF-Token"] = csrfToken;
    }

    const requestConfig = {
      credentials: "same-origin",
      headers,
      ...options
    };

    const doRequest = async (requestUrl) => {
      const response = await fetch(requestUrl, requestConfig);
      const data = await response.json().catch(() => ({}));
      return { response, data };
    };

    let result = await doRequest(normalizedUrl);
    if (result.response.status === 404 && fallbackUrl !== normalizedUrl) {
      result = await doRequest(fallbackUrl);
    }
    updateSecurityState(result.data);
    if (!result.response.ok || result.data.ok === false) {
      throw new Error(getErrorMessage(result.data));
    }
    return result.data;
  }

  function applyRoleUI(roleName, options = {}) {
    const role = normalizeRole(roleName);
    const { redirectToDashboard = false } = options;

    document.body.setAttribute("data-active-role", role);

    const userBtn = document.querySelector(".user-access-btn");
    const mobileUserBtn = document.querySelector(".mobile-user-access-btn");
    const badge = document.getElementById("userRoleBadge");
    const initial = document.getElementById("userInitial");
    const nameDisp = document.getElementById("userName");

    if (role === "guest") {
      userBtn?.classList.add("hidden");
      mobileUserBtn?.classList.add("hidden");

      if (window.location.hash.includes("view-dashboard")) {
        window.showView("home");
      }
      return role;
    }

    userBtn?.classList.remove("hidden");
    mobileUserBtn?.classList.remove("hidden");
    if (badge) badge.innerText = role.toUpperCase();

    if (initial && nameDisp && initial.parentElement) {
      if (role === "admin") {
        initial.innerText = "ML";
        nameDisp.innerText = "Luz Genovese";
        initial.parentElement.className = "w-12 h-12 bg-teal-600 text-white rounded-2xl flex items-center justify-center font-bold text-xl";
      } else if (role === "associate") {
        initial.innerText = "A";
        nameDisp.innerText = "Coordinador Red";
        initial.parentElement.className = "w-12 h-12 bg-purple-600 text-white rounded-2xl flex items-center justify-center font-bold text-xl";
      } else {
        initial.innerText = "U";
        nameDisp.innerText = "Inscripto Foro";
        initial.parentElement.className = "w-12 h-12 bg-blue-600 text-white rounded-2xl flex items-center justify-center font-bold text-xl";
      }
    }

    if (redirectToDashboard) {
      window.showView("dashboard");
    }

    window.dispatchEvent(new CustomEvent("app:role-changed", { detail: { role } }));
    return role;
  }

  window.appApiFetch = apiFetch;
  window.__auth = { applyRoleUI };

  window.setRole = async (roleName) => {
    const role = normalizeRole(roleName);
    try {
      const result = await apiFetch("/api/auth/login", {
        method: "POST",
        body: JSON.stringify({ role })
      });
      applyRoleUI(result.user?.role || role, { redirectToDashboard: true });
      window.refreshDashboardSummary?.();
    } catch (_error) {
      applyRoleUI(role, { redirectToDashboard: true });
    }
  };

  window.logout = async () => {
    try {
      await apiFetch("/api/auth/logout", { method: "POST", body: JSON.stringify({}) });
    } catch (_error) {
      // seguimos con cierre local aun si backend falla
    }
    alert("Sesión cerrada");
    applyRoleUI("guest", { redirectToDashboard: false });
    window.showView("home");
  };

  window.addEventListener("DOMContentLoaded", async () => {
    // Handle SPA navigation only if navigation.js is loaded
    if (window.__navigation && window.__navigation.parseHashState) {
      const { view, role } = window.__navigation.parseHashState();
      document.querySelectorAll(".view-section").forEach((section) => section.classList.remove("active"));
      document.getElementById(`view-${view}`)?.classList.add("active");
    }

    if (window.setDashTab) {
      window.setDashTab("overview");
    }
    try {
      const me = await apiFetch("/api/auth/me");
      const sessionRole = me.user?.role || role;
      applyRoleUI(sessionRole, { redirectToDashboard: false });
    } catch (_error) {
      applyRoleUI(role, { redirectToDashboard: false });
    }
    window.__navigation.updateHash(view);
  });
})();
