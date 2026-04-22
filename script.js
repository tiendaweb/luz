(() => {
  const validViews = new Set(["home", "forums", "about", "blog", "dashboard"]);

  function normalizeView(viewId) {
    return validViews.has(viewId) ? viewId : "home";
  }

  function closeMobileMenu() {
    const mobileMenu = document.getElementById("mobileMenu");
    if (mobileMenu) {
      mobileMenu.classList.add("hidden");
    }
  }

  window.showView = (viewId) => {
    const normalizedView = normalizeView(viewId);

    document.querySelectorAll(".view-section").forEach((section) => {
      section.classList.remove("active");
    });

    const target = document.getElementById(`view-${normalizedView}`);
    if (target) {
      target.classList.add("active");
      window.location.hash = `view-${normalizedView}`;
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  };

  window.toggleMobileMenu = () => {
    const mobileMenu = document.getElementById("mobileMenu");
    if (!mobileMenu) return;
    mobileMenu.classList.toggle("hidden");
  };

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

  window.addEventListener("hashchange", () => {
    const hash = window.location.hash.replace("#", "");
    const view = hash.startsWith("view-") ? hash.replace("view-", "") : "home";
    window.showView(view);
    closeMobileMenu();
  });

  window.addEventListener("DOMContentLoaded", () => {
    const initialHash = window.location.hash.replace("#", "");
    const initialView = initialHash.startsWith("view-")
      ? initialHash.replace("view-", "")
      : "home";
    window.showView(initialView);
  });
})();
