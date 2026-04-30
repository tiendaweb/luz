// Dashboard Dynamic Menu & Tabs Management
let currentRole = null;
let currentUser = null;

document.addEventListener('DOMContentLoaded', async () => {
  try {
    // window.appApiFetch returns the parsed JSON body (not a fetch Response).
    // It already throws on non-2xx or { ok: false }, so reaching here means we have user data.
    const data = await window.appApiFetch('/api/auth/me', { method: 'GET' });
    if (!data?.authenticated || !data?.user || data.user.role === 'guest') {
      window.location.href = '/login';
      return;
    }
    currentUser = data.user;
    currentRole = data.user.role;

    updateUserDisplay();
    initializeDashboard();
  } catch (err) {
    console.error('Error loading dashboard:', err);
    window.location.href = '/login';
  }
});

function updateUserDisplay() {
  if (!currentUser) return;
  const displayName = currentUser.name || currentUser.full_name || currentUser.email || 'Usuario';
  const initial = (displayName[0] || 'U').toUpperCase();
  const initialEl = document.getElementById('userInitial');
  const nameEl = document.getElementById('userName');
  if (initialEl) initialEl.textContent = initial;
  if (nameEl) nameEl.textContent = displayName;

  const roleBadges = {
    'admin': 'Admin',
    'associate': 'Asociado',
    'user': 'Usuario'
  };
  const badgeEl = document.getElementById('userRoleBadge');
  if (badgeEl) badgeEl.textContent = roleBadges[currentRole] || 'Usuario';
  document.body.setAttribute('data-active-role', currentRole);
}

function initializeDashboard() {
  const roleMenus = {
    'user': 'user-menu',
    'associate': 'associate-menu',
    'admin': 'admin-menu'
  };

  const menuId = roleMenus[currentRole];
  if (menuId) {
    document.getElementById(menuId).style.display = 'block';
  }

  setDashTab('overview');
}

function setDashTab(tabName) {
  document.querySelectorAll('main > div[id^="dashTab-"]').forEach(el => {
    el.classList.add('hidden');
  });

  const tabContent = document.getElementById(`dashTab-${tabName}-content`);
  if (tabContent) {
    tabContent.classList.remove('hidden');
  }

  document.querySelectorAll('nav button[id^="dashTab-"]').forEach(btn => {
    btn.classList.remove('bg-slate-100');
  });
  const activeBtn = document.getElementById(`dashTab-${tabName}`);
  if (activeBtn) {
    activeBtn.classList.add('bg-slate-100');
  }

  loadTabData(tabName);
}

async function loadTabData(tabName) {
  try {
    switch(tabName) {
      case 'overview':
        loadOverview();
        break;
      case 'profile':
        loadProfile();
        break;
      case 'myforums':
        loadUserForums();
        break;
      case 'ebooks':
        loadUserEbooks();
        break;
      case 'referrals':
        loadAssociateReferrals();
        break;
      case 'myreferrals':
        loadAssociateReferralsList();
        break;
      case 'validatepayments':
        loadAssociatePayments();
        break;
      case 'registrations':
        loadAdminRegistrations();
        break;
      case 'adminvalidate':
        loadAdminPayments();
        break;
      case 'blog':
        loadAdminBlog();
        break;
      case 'pages':
        loadAdminPages();
        break;
      case 'settings':
        loadAdminSettings();
        break;
      case 'associates':
        loadAdminAssociates();
        break;
      case 'users':
        loadAdminUsers();
        break;
      case 'certificates':
        loadCertificates();
        break;
      case 'viewcerts':
        loadViewCertificates();
        break;
      case 'signatures':
        loadSignatures();
        break;
    }
  } catch (err) {
    console.error('Error loading tab data:', err);
  }
}

async function loadOverview() {
  if (currentRole === 'admin') {
    await loadAdminOverview();
  } else if (currentRole === 'associate') {
    await loadAssociateOverview();
  } else {
    await loadUserOverview();
  }
}

async function loadAdminOverview() {
  try {
    const data = await window.appApiFetch('/api/admin/registrations', { method: 'GET' });
    if (!data) return;

    const adminFilter = (document.getElementById('adminRegistrationsFilter')?.value || 'all');
    const sourceItems = data.items || [];
    const items = adminFilter === 'all' ? sourceItems : sourceItems.filter(r => r.status === adminFilter);
    const total = items.length;
    const pending = items.filter(r => r.status === 'pending').length;
    const approved = items.filter(r => r.status === 'approved').length;

    const html = `
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
          <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Total Inscripciones</p>
          <h3 class="text-3xl font-extrabold text-slate-900 mb-4">${total}</h3>
          <p class="text-sm text-slate-600">Registro global de participantes.</p>
        </div>
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
          <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Pendientes</p>
          <h3 class="text-3xl font-extrabold text-slate-900 mb-4">${pending}</h3>
          <p class="text-sm text-slate-600">Inscripciones en revisión.</p>
        </div>
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
          <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Aprobadas</p>
          <h3 class="text-3xl font-extrabold text-slate-900 mb-4">${approved}</h3>
          <p class="text-sm text-slate-600">Inscripciones confirmadas.</p>
        </div>
      </div>
    `;
    document.getElementById('adminKpiCards').innerHTML = html;
  } catch (err) {
    console.error('Error loading admin overview:', err);
  }
}

async function loadAssociateOverview() {
  try {
    const data = await window.appApiFetch('/api/associate/registrations', { method: 'GET' });
    if (!data) return;

    const adminFilter = (document.getElementById('adminRegistrationsFilter')?.value || 'all');
    const sourceItems = data.items || [];
    const items = adminFilter === 'all' ? sourceItems : sourceItems.filter(r => r.status === adminFilter);
    const total = items.length;
    const pending = items.filter(r => r.status === 'pending').length;

    const html = `
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
          <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Total Referidos</p>
          <h3 class="text-3xl font-extrabold text-slate-900 mb-4">${total}</h3>
          <p class="text-sm text-slate-600">Red de tu grupo de referencia.</p>
        </div>
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
          <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Pendientes</p>
          <h3 class="text-3xl font-extrabold text-slate-900 mb-4">${pending}</h3>
          <p class="text-sm text-slate-600">Awaiting your approval.</p>
        </div>
      </div>
    `;
    document.getElementById('associateNetworkOverview').innerHTML = html;
  } catch (err) {
    console.error('Error loading associate overview:', err);
  }
}

async function loadUserOverview() {
  try {
    const data = await window.appApiFetch('/api/registrations/me', { method: 'GET' });
    if (!data) return;

    const items = data.items || [];
    const approved = items.filter(r => r.admin_status === 'approved').length;
    const pending = items.filter(r => r.admin_status !== 'approved').length;

    const html = `
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
          <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Inscripciones Activas</p>
          <h3 class="text-3xl font-extrabold text-slate-900 mb-4">${approved}</h3>
          <p class="text-sm text-slate-600">Foros confirmados en tu cuenta.</p>
        </div>
        <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
          <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">En Proceso</p>
          <h3 class="text-3xl font-extrabold text-slate-900 mb-4">${pending}</h3>
          <p class="text-sm text-slate-600">Esperando validación.</p>
        </div>
      </div>
    `;
    document.getElementById('userPaymentStatus').innerHTML = html;
  } catch (err) {
    console.error('Error loading user overview:', err);
  }
}

async function loadProfile() {
  const html = `
    <div class="md:col-span-2 text-center mb-6">
      <div class="w-24 h-24 rounded-full btn-primary flex items-center justify-center text-3xl font-extrabold text-white mx-auto mb-4">
        ${(currentUser.first_name?.[0] || currentUser.email[0]).toUpperCase()}
      </div>
      <h4 class="text-xl font-bold text-slate-900">${currentUser.first_name || 'Usuario'}</h4>
      <p class="text-sm text-slate-600">${currentUser.email}</p>
    </div>
    <div>
      <label class="block text-sm font-bold text-slate-700 mb-2">Nombre</label>
      <input type="text" value="${currentUser.first_name || ''}" disabled class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium">
    </div>
    <div>
      <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
      <input type="email" value="${currentUser.email}" disabled class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium">
    </div>
    <div>
      <label class="block text-sm font-bold text-slate-700 mb-2">Rol</label>
      <input type="text" value="${currentRole === 'admin' ? 'Administrador' : currentRole === 'associate' ? 'Asociado' : 'Usuario'}" disabled class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium">
    </div>
    <div>
      <label class="block text-sm font-bold text-slate-700 mb-2">Miembro desde</label>
      <input type="text" value="${new Date(currentUser.created_at).toLocaleDateString('es-AR')}" disabled class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium">
    </div>
    <div class="md:col-span-2 mt-6 border-t border-slate-200 pt-6">
      <button onclick="showChangePasswordModal()" class="px-6 py-3 rounded-xl btn-primary font-bold transition-all">
        <i class="fa-solid fa-lock mr-2"></i> Cambiar Contraseña
      </button>
    </div>
  `;

  const content = document.getElementById('dashTab-profile-content');
  const gridContent = content.querySelector('.grid');
  gridContent.innerHTML = html;
}

async function loadUserForums() {
  try {
    const data = await window.appApiFetch('/api/registrations/me', { method: 'GET' });
    if (!data) return;

    const items = data.items || [];
    let html = '';
    if (items.length === 0) {
      html = '<p class="text-slate-600">Aún no te has inscrito en ningún foro. ¡Mira los foros disponibles!</p>';
    } else {
      html = items.map(reg => `
        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h4 class="font-bold text-slate-900">Foro Slot ${reg.forum_slot}</h4>
              <p class="text-sm text-slate-600">Asistencia: ${reg.attendance_percent}% (${reg.sessions_with_attendance}/${reg.sessions_total} sesiones)</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold ${
              reg.admin_status === 'approved' ? 'bg-amber-100 text-amber-700' : 'bg-yellow-100 text-yellow-700'
            }">
              ${reg.admin_status === 'approved' ? 'Confirmada' : 'Pendiente'}
            </span>
          </div>
          <div class="text-xs text-slate-600 space-y-1">
            <p><strong>Certificado:</strong> ${reg.benefits.certificate_enabled ? '✓ Habilitado' : '✗ No habilitado'}</p>
            <p><strong>Materiales:</strong> ${reg.benefits.ebooks_enabled ? '✓ Disponibles' : '✗ No disponibles'}</p>
          </div>
        </div>
      `).join('');
    }

    document.getElementById('userBenefitsList').innerHTML = html;
  } catch (err) {
    console.error('Error loading user forums:', err);
  }
}

async function loadUserEbooks() {
  try {
    const data = await window.appApiFetch('/api/user/ebooks', { method: 'GET' });
    if (!data) return;

    const items = data.items || data.ebooks || [];
    let html = '';
    if (items.length === 0) {
      html = '<p class="text-slate-600">Completa una inscripción aprobada para acceder a materiales.</p>';
    } else {
      html = items.map(ebook => `
        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 flex items-start justify-between">
          <div class="flex-1">
            <h4 class="font-bold text-slate-900">${ebook.title}</h4>
            <p class="text-sm text-slate-600">${ebook.description || ''}</p>
            <p class="text-xs text-slate-500 mt-2">Tipo: ${ebook.type}</p>
          </div>
          <a href="${ebook.url}" target="_blank" class="ml-4 px-4 py-2 rounded-lg btn-primary font-bold text-sm">
            <i class="fa-solid fa-download"></i> Descargar
          </a>
        </div>
      `).join('');
    }

    document.getElementById('userEbooksList').innerHTML = html;
  } catch (err) {
    console.error('Error loading ebooks:', err);
  }
}

async function loadAssociateReferrals() {
  try {
    const data = await window.appApiFetch('/api/associate/offer', { method: 'GET' });
    const referralCode = data?.offer?.referralCode || data?.referral_code || data?.code || 'Generando...';
    document.getElementById('myReferralCode').value = referralCode;
  } catch (err) {
    console.error('Error loading referral code:', err);
    document.getElementById('myReferralCode').value = 'Error al cargar';
  }
}

async function loadAssociateReferralsList() {
  try {
    const filter = (document.getElementById('associateRegistrationsFilter')?.value || 'all');
    const data = await window.appApiFetch('/api/associate/registrations', { method: 'GET' });

    const sourceItems = data.items || [];
    const items = filter === 'all' ? sourceItems : sourceItems.filter(r => r.status === filter);
    let html = '';
    if (items.length === 0) {
      html = '<p class="text-slate-600">Aún no tienes referidos registrados.</p>';
    } else {
      html = items.map(reg => `
        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h4 class="font-bold text-slate-900">${reg.full_name || 'Usuario'}</h4>
              <p class="text-sm text-slate-600">Foro: ${reg.forum_slot}</p>
              <div class="mt-2 space-y-1 text-xs text-slate-600">
                <p><strong>DNI:</strong> ${reg.document_id}</p>
                <p><strong>Fecha de alta:</strong> ${new Date(reg.created_at).toLocaleDateString('es-AR')}</p>
              <p><strong>Quién refirió:</strong> ${reg.referrer_name || reg.referrer_email || 'N/D'}</p>
                <p><strong>Conversión a compra:</strong> ${Number(reg.converted_to_purchase) === 1 ? 'Sí' : 'No'}</p>
              </div>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap ${
              reg.status === 'approved' ? 'bg-amber-100 text-amber-700' :
              reg.status === 'pending' ? 'bg-yellow-100 text-yellow-700' :
              'bg-red-100 text-red-700'
            }">
              ${reg.status === 'approved' ? 'Aprobada' : reg.status === 'pending' ? 'Pendiente' : 'Rechazada'}
            </span>
          </div>
        </div>
      `).join('');
    }

    document.getElementById('associateRegistrationsList').innerHTML = html;
  } catch (err) {
    console.error('Error loading associate referrals:', err);
  }
}

async function loadAssociatePayments() {
  try {
    const data = await window.appApiFetch('/api/associate/registrations', { method: 'GET' });
    if (!data) return;

    const items = data.items || [];
    const pending = items.filter(r => r.status === 'pending');

    let html = '';
    if (pending.length === 0) {
      html = '<p class="text-slate-600">No hay pagos pendientes de validación.</p>';
    } else {
      html = pending.map(reg => `
        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h4 class="font-bold text-slate-900">${reg.full_name}</h4>
              <p class="text-sm text-slate-600">Foro: ${reg.forum_slot}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">Pendiente</span>
          </div>
          <div class="flex gap-2">
            <button onclick="approveRegistration(${reg.id})" class="flex-1 px-3 py-2 rounded-lg bg-amber-100 text-amber-700 font-bold text-sm hover:bg-amber-200">
              <i class="fa-solid fa-check mr-1"></i> Aprobar
            </button>
            <button onclick="rejectRegistration(${reg.id})" class="flex-1 px-3 py-2 rounded-lg bg-red-100 text-red-700 font-bold text-sm hover:bg-red-200">
              <i class="fa-solid fa-times mr-1"></i> Rechazar
            </button>
          </div>
        </div>
      `).join('');
    }

    document.getElementById('associatePaymentsContainer').innerHTML = html;
  } catch (err) {
    console.error('Error loading associate payments:', err);
  }
}

async function loadAdminRegistrations() {
  try {
    const data = await window.appApiFetch('/api/admin/registrations', { method: 'GET' });
    if (!data) return;

    const items = data.items || [];
    const html = items.map(reg => `
      <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h4 class="font-bold text-slate-900">${reg.full_name}</h4>
            <p class="text-sm text-slate-600">Foro: ${reg.forum_slot}</p>
            <div class="mt-2 text-xs text-slate-600 space-y-1">
              <p><strong>DNI:</strong> ${reg.document_id}</p>
              <p><strong>Fecha de alta:</strong> ${new Date(reg.created_at).toLocaleDateString('es-AR')}</p>
            <p><strong>Quién refirió:</strong> ${reg.referrer_name || reg.referrer_email || 'Directo'}</p>
              <p><strong>Estado referido:</strong> ${Number(reg.referred_is_approved) === 1 ? 'Aprobado' : 'No aprobado'}</p>
              <p><strong>Conversión a compra:</strong> ${Number(reg.converted_to_purchase) === 1 ? 'Sí' : 'No'}</p>
            </div>
          </div>
          <div class="flex flex-col gap-2">
            <select onchange="updateRegistrationStatus(${reg.id}, this.value)" class="px-3 py-2 rounded-lg border border-slate-300 bg-white font-bold text-sm">
              <option value="pending" ${reg.status === 'pending' ? 'selected' : ''}>Pendiente</option>
              <option value="approved" ${reg.status === 'approved' ? 'selected' : ''}>Aprobada</option>
              <option value="rejected" ${reg.status === 'rejected' ? 'selected' : ''}>Rechazada</option>
            </select>
          </div>
        </div>
      </div>
    `).join('');

    document.getElementById('adminRegistrationsList').innerHTML = html;
  } catch (err) {
    console.error('Error loading admin registrations:', err);
  }
}

async function loadAdminPayments() {
  try {
    const data = await window.appApiFetch('/api/admin/registrations', { method: 'GET' });
    if (!data) return;

    const items = data.items || [];
    const html = items.map(reg => `
      <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h4 class="font-bold text-slate-900">${reg.full_name}</h4>
            <p class="text-sm text-slate-600">${reg.forum_slot}</p>
          </div>
          <span class="px-3 py-1 rounded-full text-xs font-bold ${
            reg.status === 'approved' ? 'bg-amber-100 text-amber-700' : 'bg-yellow-100 text-yellow-700'
          }">
            ${reg.status === 'approved' ? 'Confirmada' : 'Pendiente'}
          </span>
        </div>
      </div>
    `).join('');

    document.getElementById('adminPaymentsContainer').innerHTML = html;
  } catch (err) {
    console.error('Error loading admin payments:', err);
  }
}

async function loadAdminBlog() {
  try {
    const data = await window.appApiFetch('/api/admin/blog/list', { method: 'GET' });
    if (!data) return;

    const items = data.items || [];
    const html = items.map(post => `
      <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h4 class="font-bold text-slate-900">${post.title}</h4>
            <p class="text-sm text-slate-600">${post.excerpt || ''}</p>
            <p class="text-xs text-slate-500 mt-2">Publicado: ${new Date(post.created_at).toLocaleDateString('es-AR')}</p>
          </div>
          <span class="px-3 py-1 rounded-full text-xs font-bold ${
            post.status === 'published' ? 'bg-amber-100 text-amber-700' : 'bg-slate-300 text-slate-700'
          }">
            ${post.status === 'published' ? 'Publicado' : 'Borrador'}
          </span>
        </div>
        <div class="flex gap-2">
          <button onclick="editBlogPost(${post.id})" class="px-3 py-2 rounded-lg bg-amber-100 text-amber-700 font-bold text-sm">
            <i class="fa-solid fa-edit"></i> Editar
          </button>
          <button onclick="deleteBlogPost(${post.id})" class="px-3 py-2 rounded-lg bg-red-100 text-red-700 font-bold text-sm">
            <i class="fa-solid fa-trash"></i> Eliminar
          </button>
        </div>
      </div>
    `).join('');

    document.getElementById('blogPostsList').innerHTML = html;
  } catch (err) {
    console.error('Error loading blog:', err);
  }
}

async function loadAdminPages() {
  try {
    const data = await window.appApiFetch('/api/admin/pages/list', { method: 'GET' });
    if (!data) return;

    const items = data.items || [];
    const html = items.map(page => `
      <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h4 class="font-bold text-slate-900">${page.title}</h4>
            <p class="text-xs text-slate-500 mb-2">Slug: <code class="bg-slate-200 px-2 py-1 rounded">/p/${page.slug}</code></p>
            <p class="text-sm text-slate-600">${(page.content_html || 'Sin contenido').substring(0, 100)}...</p>
          </div>
          <span class="px-3 py-1 rounded-full text-xs font-bold ${
            page.status === 'published' ? 'bg-amber-100 text-amber-700' : 'bg-slate-300 text-slate-700'
          }">
            ${page.status === 'published' ? 'Publicada' : 'Borrador'}
          </span>
        </div>
        <div class="flex gap-2">
          <button onclick="editPage(${page.id})" class="px-3 py-2 rounded-lg bg-amber-100 text-amber-700 font-bold text-sm">
            <i class="fa-solid fa-edit"></i> Editar
          </button>
          <button onclick="deletePage(${page.id})" class="px-3 py-2 rounded-lg bg-red-100 text-red-700 font-bold text-sm">
            <i class="fa-solid fa-trash"></i> Eliminar
          </button>
        </div>
      </div>
    `).join('');

    document.getElementById('pagesContainer').innerHTML = html;
  } catch (err) {
    console.error('Error loading pages:', err);
  }
}

async function loadAdminSettings() {
  try {
    const data = await window.appApiFetch('/api/admin/settings', { method: 'GET' });
    if (!data) return;

    const form = document.getElementById('adminSettingsForm');
    Object.keys(data.settings || {}).forEach(key => {
      const field = form.querySelector(`[name="${key}"]`);
      if (field) {
        field.value = data.settings[key];
      }
    });
  } catch (err) {
    console.error('Error loading settings:', err);
  }
}

async function loadAdminAssociates() {
  try {
    const data = await window.appApiFetch('/api/admin/associates', { method: 'GET' });
    if (!data) return;

    const items = data.items || [];
    const html = items.map(assoc => `
      <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <h4 class="font-bold text-slate-900">${assoc.email}</h4>
            <p class="text-sm text-slate-600">${assoc.full_name || 'Sin nombre'}</p>
            <div class="mt-2 text-xs text-slate-600 space-y-1">
              <p><strong>Referidos:</strong> (cargando...)</p>
              <p><strong>Desde:</strong> (cargando...)</p>
            </div>
          </div>
          <button onclick="viewAssociateDetails(${assoc.id})" class="px-3 py-2 rounded-lg bg-amber-100 text-amber-700 font-bold text-sm">
            Ver Detalles
          </button>
        </div>
      </div>
    `).join('');

    document.getElementById('adminAssociatesList').innerHTML = html;
  } catch (err) {
    console.error('Error loading associates:', err);
  }
}

async function loadAdminUsers() {
  try {
    const data = await window.appApiFetch('/api/admin/users', { method: 'GET' });
    if (!data) return;

    const items = data.items || [];
    const html = items.map(user => `
      <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <h4 class="font-bold text-slate-900">${user.email}</h4>
            <p class="text-sm text-slate-600">${user.full_name || 'Sin nombre'}</p>
            <div class="mt-2 text-xs text-slate-600 space-y-1">
              <p><strong>Estado Validado:</strong> ${user.is_validated ? '✓ Sí' : user.legacy_is_validated ? '✓ Herencia' : '✗ No'}</p>
              <p><strong>Estado Pago:</strong> ${user.is_paid ? '✓ Pagado' : user.legacy_is_paid ? '✓ Herencia' : '✗ Pendiente'}</p>
            </div>
          </div>
          <button onclick="viewUserDetails(${user.id})" class="px-3 py-2 rounded-lg bg-amber-100 text-amber-700 font-bold text-sm">
            Ver Detalles
          </button>
        </div>
      </div>
    `).join('');

    document.getElementById('usersList').innerHTML = html;
  } catch (err) {
    console.error('Error loading users:', err);
  }
}

// Helper functions
async function approveRegistration(id) {
  if (!confirm('¿Aprobar esta inscripción?')) return;
  try {
    const data = await window.appApiFetch(`/api/associate/registrations`, {
      method: 'PATCH',
      body: JSON.stringify({ registrationId: id, status: 'approved' })
    });
    if (data) {
      loadAssociatePayments();
    }
  } catch (err) {
    console.error('Error approving registration:', err);
  }
}

async function rejectRegistration(id) {
  if (!confirm('¿Rechazar esta inscripción?')) return;
  try {
    const data = await window.appApiFetch(`/api/associate/registrations`, {
      method: 'PATCH',
      body: JSON.stringify({ registrationId: id, status: 'rejected' })
    });
    if (data) {
      loadAssociatePayments();
    }
  } catch (err) {
    console.error('Error rejecting registration:', err);
  }
}

async function updateRegistrationStatus(id, status) {
  try {
    const data = await window.appApiFetch(`/api/admin/registrations`, {
      method: 'PATCH',
      body: JSON.stringify({ registrationId: id, status: status })
    });
    if (data) {
      loadAdminRegistrations();
    }
  } catch (err) {
    console.error('Error updating registration:', err);
  }
}

function editBlogPost(id) {
  alert('Edición de blog post ' + id + ' (por implementar)');
}

function deleteBlogPost(id) {
  if (!confirm('¿Eliminar este artículo?')) return;
}

function editPage(id) {
  alert('Edición de página ' + id + ' (por implementar)');
}

function deletePage(id) {
  if (!confirm('¿Eliminar esta página?')) return;
}

function viewAssociateDetails(id) {
  alert('Detalles del asociado ' + id + ' (por implementar)');
}

function viewUserDetails(id) {
  alert('Detalles del usuario ' + id + ' (por implementar)');
}

function showChangePasswordModal() {
  alert('Cambiar contraseña (por implementar)');
}

function renderCertificates(items) {
  const target = document.getElementById('certificatesList');
  if (!target) return;

  if (!Array.isArray(items) || items.length === 0) {
    target.innerHTML = '<p class="text-slate-500">No hay usuarios elegibles para certificados en este momento.</p>';
    return;
  }

  const groupedByForum = items.reduce((acc, item) => {
    const key = `${item.forum_id}-${item.forum_code}`;
    if (!acc[key]) {
      acc[key] = { code: item.forum_code, title: item.forum_title, users: [] };
    }
    acc[key].users.push(item);
    return acc;
  }, {});

  let html = '';
  Object.entries(groupedByForum).forEach(([key, forum]) => {
    html += `
      <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 overflow-hidden">
        <h4 class="font-bold text-slate-900 mb-4">
          <i class="fa-solid fa-certificate text-amber-700 mr-2"></i>${escapeHtml(forum.code)} - ${escapeHtml(forum.title)}
        </h4>
        <div class="space-y-2">
          ${forum.users.map((user) => `
            <article class="bg-white rounded-lg border border-slate-200 p-4 flex items-center justify-between">
              <div>
                <p class="font-bold text-slate-900">${escapeHtml(user.full_name)}</p>
                <p class="text-xs text-slate-600">${escapeHtml(user.email)}</p>
              </div>
              <div class="flex items-center gap-2">
                ${user.has_certificate ? `
                  <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                    <i class="fa-solid fa-check mr-1"></i> Generado
                  </span>
                ` : `
                  <button data-action="generate-cert" data-user-id="${user.id}" data-forum-id="${user.forum_id}" class="rounded-lg bg-amber-700 px-4 py-2 text-xs font-bold text-white hover:bg-amber-800">
                    <i class="fa-solid fa-file-pdf mr-1"></i> Generar
                  </button>
                `}
              </div>
            </article>
          `).join('')}
        </div>
      </div>
    `;
  });

  target.innerHTML = html;

  target.querySelectorAll('[data-action="generate-cert"]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const userId = Number(btn.dataset.userId);
      const forumId = Number(btn.dataset.forumId);
      await generateCertificate(userId, forumId, btn);
    });
  });
}

function escapeHtml(str) {
  return String(str || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function showCertificatesAlert(message, isError = false) {
  const box = document.getElementById('certificatesAlert');
  if (!box) return;
  if (!message) {
    box.classList.add('hidden');
    return;
  }
  box.classList.remove('hidden', 'bg-amber-50', 'text-amber-700', 'border-amber-200', 'bg-rose-50', 'text-rose-700', 'border-rose-200', 'border');
  box.classList.add('border');
  if (isError) {
    box.classList.add('bg-rose-50', 'text-rose-700', 'border-rose-200');
  } else {
    box.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-200');
  }
  box.textContent = message;
}

async function loadCertificates() {
  if (document.body.getAttribute('data-active-role') !== 'admin') return;
  try {
    showCertificatesAlert('');
    const result = await window.appApiFetch('/api/admin/certificates');
    renderCertificates(result.items || []);
  } catch (error) {
    showCertificatesAlert(error instanceof Error ? error.message : 'No se pudieron cargar los certificados.', true);
  }
}

async function generateCertificate(userId, forumId, btn) {
  try {
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Generando...';
    }

    const result = await window.appApiFetch('/api/admin/certificates', {
      method: 'POST',
      body: JSON.stringify({ userId, forumId })
    });

    showCertificatesAlert('✓ Certificado generado exitosamente.', false);
    await loadCertificates();
  } catch (error) {
    const msg = error instanceof Error ? error.message : 'Error al generar certificado';
    showCertificatesAlert('✗ ' + msg, true);
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Generar';
    }
  }
}

function renderViewCertificates(items) {
  const target = document.getElementById('viewCertsList');
  if (!target) return;

  if (!Array.isArray(items) || items.length === 0) {
    target.innerHTML = '<p class="text-slate-500">No hay certificados generados aún.</p>';
    return;
  }

  const groupedByForum = items.reduce((acc, item) => {
    const key = `${item.forum_id}-${item.forum_code}`;
    if (!acc[key]) {
      acc[key] = { code: item.forum_code, title: item.forum_title, certs: [] };
    }
    acc[key].certs.push(item);
    return acc;
  }, {});

  let html = '';
  Object.entries(groupedByForum).forEach(([key, forum]) => {
    html += `
      <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
        <h4 class="font-bold text-slate-900 mb-4">
          <i class="fa-solid fa-file-pdf text-red-600 mr-2"></i>${escapeHtml(forum.code)} - ${escapeHtml(forum.title)}
        </h4>
        <div class="space-y-2">
          ${forum.certs.map((cert) => `
            <article class="bg-white rounded-lg border border-slate-200 p-4 flex items-center justify-between">
              <div>
                <p class="font-bold text-slate-900">${escapeHtml(cert.full_name)}</p>
                <p class="text-xs text-slate-600">${escapeHtml(cert.email)}</p>
                <p class="text-xs text-slate-500">Emitido: ${new Date(cert.created_at).toLocaleDateString()}</p>
              </div>
              <button onclick="window.open('/certificate-view?id=${cert.id}', '_blank')" class="rounded-lg bg-red-600 px-4 py-2 text-xs font-bold text-white hover:bg-red-700">
                <i class="fa-solid fa-file-pdf mr-1"></i> Ver / Descargar
              </button>
            </article>
          `).join('')}
        </div>
      </div>
    `;
  });

  target.innerHTML = html;
}

function renderSignatures(items) {
  const target = document.getElementById('signaturesList');
  if (!target) return;

  if (!Array.isArray(items) || items.length === 0) {
    target.innerHTML = '<p class="text-slate-500">No hay firmas para mostrar.</p>';
    return;
  }

  const groupedByForum = items.reduce((acc, item) => {
    const key = `${item.forum_id}-${item.forum_code}`;
    if (!acc[key]) {
      acc[key] = { code: item.forum_code, title: item.forum_title, sigs: [] };
    }
    acc[key].sigs.push(item);
    return acc;
  }, {});

  let html = '';
  Object.entries(groupedByForum).forEach(([key, forum]) => {
    html += `
      <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
        <h4 class="font-bold text-slate-900 mb-4">
          <i class="fa-solid fa-pen-fancy text-slate-600 mr-2"></i>${escapeHtml(forum.code)} - ${escapeHtml(forum.title)}
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          ${forum.sigs.map((sig) => `
            <article class="bg-white rounded-lg border border-slate-200 p-4">
              <p class="font-bold text-slate-900 mb-2">${escapeHtml(sig.full_name)}</p>
              <p class="text-xs text-slate-600 mb-3">${escapeHtml(sig.email)}</p>
              <div class="border border-slate-200 rounded-lg p-3 bg-slate-50 min-h-20 flex items-center justify-center">
                ${sig.signature_data_url || sig.signature_data ? `
                  <img src="${escapeHtml(sig.signature_data_url || sig.signature_data)}" alt="Firma" style="max-width: 100%; max-height: 80px; object-fit: contain;">
                ` : '<p class="text-xs text-slate-400">Sin firma</p>'}
              </div>
            </article>
          `).join('')}
        </div>
      </div>
    `;
  });

  target.innerHTML = html;
}

function showViewCertsAlert(message, isError = false) {
  const box = document.getElementById('viewCertsAlert');
  if (!box) return;
  if (!message) {
    box.classList.add('hidden');
    return;
  }
  box.classList.remove('hidden', 'bg-amber-50', 'text-amber-700', 'border-amber-200', 'bg-rose-50', 'text-rose-700', 'border-rose-200', 'border');
  box.classList.add('border');
  if (isError) {
    box.classList.add('bg-rose-50', 'text-rose-700', 'border-rose-200');
  } else {
    box.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-200');
  }
  box.textContent = message;
}

function showSignaturesAlert(message, isError = false) {
  const box = document.getElementById('signaturesAlert');
  if (!box) return;
  if (!message) {
    box.classList.add('hidden');
    return;
  }
  box.classList.remove('hidden', 'bg-amber-50', 'text-amber-700', 'border-amber-200', 'bg-rose-50', 'text-rose-700', 'border-rose-200', 'border');
  box.classList.add('border');
  if (isError) {
    box.classList.add('bg-rose-50', 'text-rose-700', 'border-rose-200');
  } else {
    box.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-200');
  }
  box.textContent = message;
}

async function loadViewCertificates() {
  if (document.body.getAttribute('data-active-role') !== 'admin') return;
  try {
    showViewCertsAlert('');
    const result = await window.appApiFetch('/api/admin/certificates?generated=1');
    renderViewCertificates(result.items || []);
  } catch (error) {
    showViewCertsAlert(error instanceof Error ? error.message : 'No se pudieron cargar los certificados.', true);
  }
}

async function loadSignatures() {
  if (document.body.getAttribute('data-active-role') !== 'admin') return;
  try {
    showSignaturesAlert('');
    const result = await window.appApiFetch('/api/admin/signatures');
    renderSignatures(result.items || []);
  } catch (error) {
    showSignaturesAlert(error instanceof Error ? error.message : 'No se pudieron cargar las firmas.', true);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('refreshCertificates')?.addEventListener('click', loadCertificates);
  document.getElementById('refreshViewCerts')?.addEventListener('click', loadViewCertificates);
  document.getElementById('refreshSignatures')?.addEventListener('click', loadSignatures);
});


document.addEventListener('change', (event) => {
  if (event.target?.id === 'associateRegistrationsFilter') {
    loadAssociateReferralsList();
  }
  if (event.target?.id === 'adminRegistrationsFilter') {
    loadAdminRegistrations();
  }
});
