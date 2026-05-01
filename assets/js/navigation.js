(() => {
  const validViews = new Set(["home", "forums", "forum-detail", "about", "blog", "dashboard"]);

  const normalizeView = (viewId) => (validViews.has(viewId) ? viewId : "home");

  const normalizeRole = (roleName) => {
    const validRoles = new Set(["guest", "user", "associate", "admin"]);
    return validRoles.has(roleName) ? roleName : "guest";
  };
  const allowedTabsByRole = {
    guest: ["overview"],
    user: ["overview", "profile", "myforums", "ebooks"],
    associate: ["overview", "profile", "referrals", "myreferrals", "validatepayments"],
    admin: ["overview", "profile", "registrations", "adminvalidate", "blog", "pages", "settings", "associates", "users"]
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

  const setDashboardTab = (tabName) => {
    const role = normalizeRole(document.body.getAttribute("data-active-role") || "guest");
    const allowedTabs = allowedTabsByRole[role] || allowedTabsByRole.guest;
    const normalizedTab = allowedTabs.includes(tabName) ? tabName : (window.__navigation?.getDefaultDashTabByRole(role) || "overview");

    // Hide all tab content
    document.querySelectorAll('[id^="dashTab-"][id$="-content"]').forEach((tab) => {
      tab.classList.add("hidden");
    });

    // Show the selected tab
    const selectedTab = document.getElementById(`dashTab-${normalizedTab}-content`);
    if (selectedTab) {
      selectedTab.classList.remove("hidden");
    }

    // Update button highlighting
    document.querySelectorAll("aside nav button").forEach((btn) => {
      btn.classList.remove("bg-slate-100", "text-blue-800");
      btn.classList.add("text-slate-700");
    });
    const selectedBtn = document.getElementById(`dashTab-${normalizedTab}`);
    if (selectedBtn) {
      selectedBtn.classList.add("bg-slate-100");
      selectedBtn.classList.add("text-blue-800");
    }

    window.dashboardTabs?.loadData?.(normalizedTab);
    return normalizedTab;
  };

  const dashboardActionHandlers = {
    logout: () => window.logout?.(),
    "refresh-registrations": () => window.dashboardTabs?.loadData?.("registrations"),
    "refresh-adminvalidate": () => window.dashboardTabs?.loadData?.("adminvalidate")
  };

  const runDashboardAction = (actionName) => {
    if (!actionName) return;
    if (dashboardActionHandlers[actionName]) {
      dashboardActionHandlers[actionName]();
      return;
    }
    if (actionName.startsWith("refresh-")) {
      const tabName = actionName.replace("refresh-", "");
      window.dashboardTabs?.loadData?.(tabName);
    }
  };

  const onDashboardSidebarClick = (event) => {
    const trigger = event.target.closest("[data-tab], [data-action]");
    if (!trigger) return;
    if (!event.currentTarget.contains(trigger)) return;

    const tabName = trigger.getAttribute("data-tab");
    if (tabName) {
      setDashboardTab(tabName);
      return;
    }

    const actionName = trigger.getAttribute("data-action");
    runDashboardAction(actionName);
  };

  const sidebarRoot = document.querySelector("aside");
  sidebarRoot?.addEventListener("click", onDashboardSidebarClick);

  function getDefaultDashTabByRole(roleName) {
    const role = normalizeRole(roleName);
    if (role === "admin") return "profile";
    if (role === "associate") return "profile";
    if (role === "user") return "profile";
    return "overview";
  }

  window.__navigation = {
    parseHashState,
    updateHash,
    closeMobileMenu,
    normalizeRole,
    normalizeView,
    getDefaultDashTabByRole,
    allowedTabsByRole
  };

  window.dashboardTabs = window.dashboardTabs || {};
  // Legacy shim: keep wrapper until all HTML views stop invoking global calls directly.
  // Retirement target: after 2026-06-30 audit verifies no inline onclick usage remains.
  window.dashboardTabs.set = setDashboardTab;

  window.addEventListener("hashchange", () => {
    const { view } = parseHashState();
    document.querySelectorAll(".view-section").forEach((section) => section.classList.remove("active"));
    document.getElementById(`view-${view}`)?.classList.add("active");
    window.__auth?.syncDashboardByRole?.(document.body.getAttribute("data-active-role") || "guest");
    closeMobileMenu();
  });
})();
