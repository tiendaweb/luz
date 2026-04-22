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

  window.setRole = (roleName) => {
    document.body.setAttribute("data-active-role", roleName);
    const userBtn = document.querySelector(".user-access-btn");
    const badge = document.getElementById("userRoleBadge");
    const initial = document.getElementById("userInitial");
    const nameDisp = document.getElementById("userName");

    if (roleName === "guest") {
      userBtn?.classList.add("hidden");
      window.showView("home");
      return;
    }

    userBtn?.classList.remove("hidden");

    if (badge) {
      badge.innerText = roleName.toUpperCase();
    }

    if (initial && nameDisp && initial.parentElement) {
      if (roleName === "admin") {
        initial.innerText = "ML";
        nameDisp.innerText = "Luz Genovese";
        initial.parentElement.className = "w-12 h-12 bg-teal-600 text-white rounded-2xl flex items-center justify-center font-bold text-xl";
      } else if (roleName === "associate") {
        initial.innerText = "A";
        nameDisp.innerText = "Coordinador Red";
        initial.parentElement.className = "w-12 h-12 bg-purple-600 text-white rounded-2xl flex items-center justify-center font-bold text-xl";
      } else {
        initial.innerText = "U";
        nameDisp.innerText = "Inscripto Foro";
        initial.parentElement.className = "w-12 h-12 bg-blue-600 text-white rounded-2xl flex items-center justify-center font-bold text-xl";
      }
    }

    window.showView("dashboard");
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

  window.toggleFaq = (buttonElement) => {
    const content = buttonElement?.nextElementSibling;
    const icon = buttonElement?.querySelector("i");

    if (content) {
      content.classList.toggle("hidden");
    }

    if (icon) {
      icon.classList.toggle("rotate-180");
    }
  };

  window.toggleCertFields = (show) => {
    const certFields = document.getElementById("certFields");
    certFields?.classList.toggle("hidden", !show);
  };

  window.logout = () => {
    alert("Sesión cerrada (Simulación)");
    window.setRole("guest");
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
