(() => {
  const validViews = new Set(["home", "forums", "about", "blog", "dashboard"]);
  const validRoles = new Set(["guest", "user", "associate", "admin"]);

  const normalizeView = (viewId) => (validViews.has(viewId) ? viewId : "home");
  const normalizeRole = (roleName) => (validRoles.has(roleName) ? roleName : "guest");

  function closeMobileMenu() {
    document.getElementById("mobileMenu")?.classList.add("hidden");
  }

  function parseHashState() {
    const rawHash = window.location.hash.replace("#", "");
    const [rawView, query = ""] = rawHash.split("?");
    const view = rawView.startsWith("view-") ? rawView.replace("view-", "") : "home";
    const role = normalizeRole(new URLSearchParams(query).get("role") || "guest");
    return { view: normalizeView(view), role };
  }

  function updateHash(viewId) {
    const role = normalizeRole(document.body.getAttribute("data-active-role") || "guest");
    const query = role === "guest" ? "" : `?role=${role}`;
    const nextHash = `#view-${normalizeView(viewId)}${query}`;
    if (window.location.hash !== nextHash) {
      window.location.hash = nextHash;
    }
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

    return role;
  }

  window.showView = (viewId) => {
    const view = normalizeView(viewId);
    document.querySelectorAll(".view-section").forEach((section) => section.classList.remove("active"));
    document.getElementById(`view-${view}`)?.classList.add("active");
    updateHash(view);
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  window.setRole = (roleName) => {
    applyRoleUI(roleName, { redirectToDashboard: true });
  };

  window.setDashTab = () => {
    const firstBtn = document.querySelector("#view-dashboard nav button");
    firstBtn?.classList.add("bg-teal-50", "text-teal-700");
  };

  window.toggleMobileMenu = () => document.getElementById("mobileMenu")?.classList.toggle("hidden");

  window.openModal = (id) => {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove("hidden");
    document.body.style.overflow = "hidden";
  };

  window.closeModal = (id) => {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add("hidden");
    document.body.style.overflow = "auto";
  };

  window.toggleFaq = (buttonElement) => {
    buttonElement?.nextElementSibling?.classList.toggle("hidden");
    buttonElement?.querySelector("i")?.classList.toggle("rotate-180");
  };

  window.toggleCertFields = (show) => document.getElementById("certFields")?.classList.toggle("hidden", !show);

  window.logout = () => {
    alert("Sesión cerrada (Simulación)");
    applyRoleUI("guest", { redirectToDashboard: false });
    window.showView("home");
  };

  window.addEventListener("hashchange", () => {
    const { view, role } = parseHashState();
    document.querySelectorAll(".view-section").forEach((section) => section.classList.remove("active"));
    document.getElementById(`view-${view}`)?.classList.add("active");
    applyRoleUI(role, { redirectToDashboard: false });
    closeMobileMenu();
  });

  window.addEventListener("DOMContentLoaded", () => {
    const { view, role } = parseHashState();
    document.querySelectorAll(".view-section").forEach((section) => section.classList.remove("active"));
    document.getElementById(`view-${view}`)?.classList.add("active");
    window.setDashTab("overview");
    applyRoleUI(role, { redirectToDashboard: false });
    updateHash(view);
  });
})();
