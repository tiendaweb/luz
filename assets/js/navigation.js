(() => {
  const validViews = new Set(["home", "forums", "forum-detail", "about", "blog", "dashboard"]);

  const normalizeView = (viewId) => (validViews.has(viewId) ? viewId : "home");

  const normalizeRole = (roleName) => {
    const validRoles = new Set(["guest", "user", "associate", "admin"]);
    return validRoles.has(roleName) ? roleName : "guest";
  };

  function closeMobileMenu() {
    document.getElementById("mobileMenu")?.classList.add("hidden");
  }

  function parseHashState() {
    const rawHash = window.location.hash.replace("#", "");
    const [rawView, query = ""] = rawHash.split("?");
    const view = rawView.startsWith("view-") ? rawView.replace("view-", "") : "home";
    const params = new URLSearchParams(query);
    const role = normalizeRole(params.get("role") || "guest");
    return { view: normalizeView(view), role, params };
  }

  function updateHash(viewId) {
    const role = normalizeRole(document.body.getAttribute("data-active-role") || "guest");
    const query = role === "guest" ? "" : `?role=${role}`;
    const nextHash = `#view-${normalizeView(viewId)}${query}`;
    if (window.location.hash !== nextHash) {
      window.location.hash = nextHash;
    }
  }

  window.showView = (viewId) => {
    const view = normalizeView(viewId);
    document.querySelectorAll(".view-section").forEach((section) => section.classList.remove("active"));
    document.getElementById(`view-${view}`)?.classList.add("active");
    updateHash(view);
    window.scrollTo({ top: 0, behavior: "smooth" });
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

  window.setDashTab = () => {
    const firstBtn = document.querySelector("#view-dashboard nav button");
    firstBtn?.classList.add("bg-teal-50", "text-teal-700");
  };

  window.__navigation = { parseHashState, updateHash, closeMobileMenu, normalizeRole, normalizeView };

  window.addEventListener("hashchange", () => {
    const { view, role } = parseHashState();
    document.querySelectorAll(".view-section").forEach((section) => section.classList.remove("active"));
    document.getElementById(`view-${view}`)?.classList.add("active");
    window.__auth?.applyRoleUI(role, { redirectToDashboard: false });
    closeMobileMenu();
  });
})();
