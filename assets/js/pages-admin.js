(() => {
  function getAdminPagesElements() {
    return {
      form: document.getElementById("adminPageForm"),
      list: document.getElementById("adminPagesList"),
      status: document.getElementById("adminPagesStatus"),
      refresh: document.getElementById("refreshAdminPages"),
      reset: document.getElementById("adminPageFormReset")
    };
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function setStatus(message, isError = false) {
    const { status } = getAdminPagesElements();
    if (!status) return;
    status.textContent = message;
    status.classList.toggle("text-rose-700", isError);
    status.classList.toggle("text-amber-700", !isError);
  }

  function fillForm(item) {
    const { form } = getAdminPagesElements();
    if (!form || !item) return;
    form.id.value = item.id || "";
    form.slug.value = item.slug || "";
    form.title.value = item.title || "";
    form.content_html.value = item.content_html || "";
    form.status.value = item.status || "draft";
    form.seo_title.value = item.seo_title || "";
    form.seo_description.value = item.seo_description || "";
  }

  function clearForm() {
    const { form } = getAdminPagesElements();
    if (!form) return;
    form.reset();
    form.id.value = "";
  }

  async function loadAdminPages() {
    if (document.body.getAttribute("data-active-role") !== "admin") return;
    const { list } = getAdminPagesElements();
    if (!list) return;

    try {
      const result = await window.appApiFetch("/api/admin/pages/list");
      const items = Array.isArray(result.items) ? result.items : [];
      if (items.length === 0) {
        list.innerHTML = '<p class="text-xs text-slate-500">Sin páginas cargadas.</p>';
        return;
      }

      list.innerHTML = items.map((item) => {
        const viewUrl = `/p/${encodeURIComponent(item.slug)}`;
        return `
          <article class="rounded-xl border border-slate-200 bg-slate-50 p-3">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
              <div>
                <p class="font-bold">${escapeHtml(item.title)} <span class="text-xs text-slate-500">(${escapeHtml(item.slug)})</span></p>
                <p class="text-xs text-slate-500">Estado: ${escapeHtml(item.status)} · Actualizada: ${escapeHtml(item.updated_at || "-")}</p>
                <p class="text-xs text-slate-500">Ruta: <a href="${viewUrl}" target="_blank" class="underline text-amber-700">${escapeHtml(viewUrl)}</a></p>
              </div>
              <div class="flex gap-2">
                <button data-action="edit-page" data-id="${escapeHtml(item.id)}" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-bold">Editar</button>
                <button data-action="delete-page" data-id="${escapeHtml(item.id)}" class="rounded-lg bg-rose-600 px-3 py-1 text-xs font-bold text-white">Eliminar</button>
              </div>
            </div>
          </article>`;
      }).join("");

      list.querySelectorAll('[data-action="edit-page"]').forEach((button) => {
        button.addEventListener("click", async () => {
          const id = button.dataset.id;
          try {
            const result = await window.appApiFetch(`/api/admin/pages/show?id=${encodeURIComponent(id || "")}`);
            fillForm(result.item || null);
            setStatus("Página cargada para edición.");
          } catch (error) {
            setStatus(error instanceof Error ? error.message : "No se pudo cargar la página.", true);
          }
        });
      });

      list.querySelectorAll('[data-action="delete-page"]').forEach((button) => {
        button.addEventListener("click", async () => {
          const id = button.dataset.id;
          if (!window.confirm("¿Eliminar esta página?")) return;
          try {
            await window.appApiFetch(`/api/admin/pages/delete?id=${encodeURIComponent(id || "")}`, { method: "DELETE" });
            setStatus("Página eliminada.");
            clearForm();
            await loadAdminPages();
          } catch (error) {
            setStatus(error instanceof Error ? error.message : "No se pudo eliminar.", true);
          }
        });
      });
    } catch (error) {
      list.innerHTML = '<p class="text-xs text-rose-700">No se pudo cargar el listado.</p>';
      setStatus(error instanceof Error ? error.message : "Error cargando páginas.", true);
    }
  }

  function wireEvents() {
    const { form, refresh, reset } = getAdminPagesElements();
    if (!form) return;

    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      const payload = {
        id: Number(form.id.value || 0),
        slug: String(form.slug.value || "").trim(),
        title: String(form.title.value || "").trim(),
        content_html: String(form.content_html.value || ""),
        status: String(form.status.value || "draft"),
        seo_title: String(form.seo_title.value || "").trim(),
        seo_description: String(form.seo_description.value || "").trim()
      };

      const isEdit = payload.id > 0;
      const endpoint = isEdit ? "/api/admin/pages/update" : "/api/admin/pages/create";
      const method = isEdit ? "PATCH" : "POST";

      try {
        await window.appApiFetch(endpoint, { method, body: JSON.stringify(payload) });
        setStatus(isEdit ? "Página actualizada." : "Página creada.");
        clearForm();
        await loadAdminPages();
      } catch (error) {
        setStatus(error instanceof Error ? error.message : "No se pudo guardar.", true);
      }
    });

    refresh?.addEventListener("click", () => { loadAdminPages(); });
    reset?.addEventListener("click", () => { clearForm(); setStatus(""); });

    window.addEventListener("app:role-changed", () => {
      if (document.body.getAttribute("data-active-role") === "admin") {
        loadAdminPages();
      }
    });
  }

  window.addEventListener("DOMContentLoaded", () => {
    wireEvents();
    loadAdminPages();
  });
})();
