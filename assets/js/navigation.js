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

  window.setDashTab = (tabName) => {
    // Hide all tab content
    document.querySelectorAll('[id^="dashTab-"][id$="-content"]').forEach((tab) => {
      tab.classList.add("hidden");
    });

    // Show the selected tab
    const selectedTab = document.getElementById(`dashTab-${tabName}-content`);
    if (selectedTab) {
      selectedTab.classList.remove("hidden");
    }

    // Update button highlighting
    document.querySelectorAll("aside nav button").forEach((btn) => {
      btn.classList.remove("bg-slate-100", "text-teal-700");
      btn.classList.add("text-slate-700");
    });
    const selectedBtn = document.getElementById(`dashTab-${tabName}`);
    if (selectedBtn) {
      selectedBtn.classList.add("bg-slate-100");
      selectedBtn.classList.add("text-teal-700");
    }
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
