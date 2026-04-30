<?php
declare(strict_types=1);
require_once __DIR__ . '/../_session.php';
require_once __DIR__ . '/../layouts/main.php';
require_once __DIR__ . '/../../Support/ContentBlocks.php';

if (!$_viewIsLoggedIn) {
    header('Location: /login', true, 302);
    exit;
}

ob_start();
?>

<!-- Dashboard Usuario -->
<section class="py-24">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
 <!-- Sidebar -->
 <aside class="lg:col-span-1">
 <div class="bg-white rounded-3xl p-8 shadow-xl border border-slate-100 sticky top-32">


 <nav class="space-y-2 border-t border-slate-100 pt-6">
 <!-- Common for all roles -->
 <button id="dashTab-overview" data-tab="overview" class="w-full text-left px-4 py-3 rounded-xl bg-slate-100 font-bold text-slate-700 hover:bg-slate-200 transition-all">
 <i class="fa-solid fa-chart-line mr-3"></i> <?= htmlspecialchars(content_block_value('dashboard','menu_overview','Resumen'), ENT_QUOTES, 'UTF-8') ?>
 </button>

 <!-- Mi Perfil para todos -->
 <button id="dashTab-profile" data-tab="profile" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-user mr-3"></i> Mi Perfil
 </button>

 <!-- User tabs -->
 <div id="user-menu" class="user-only space-y-2">
 <button id="dashTab-myforums" data-tab="myforums" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-calendar mr-3"></i> Mis Foros Activos
 </button>
 <button id="dashTab-ebooks" data-tab="ebooks" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-book mr-3"></i> Materiales y Certificados
 </button>
 </div>

 <!-- Associate tabs -->
 <div id="associate-menu" class="associate-only space-y-2">
 <button id="dashTab-referrals" data-tab="referrals" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-share-nodes mr-3"></i> Link Referido Grupo
 </button>
 <button id="dashTab-myreferrals" data-tab="myreferrals" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-users mr-3"></i> Mis Referidos
 </button>
 <button id="dashTab-validatepayments" data-tab="validatepayments" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-credit-card mr-3"></i> Validar Pagos
 </button>
 </div>

 <!-- Admin tabs -->
 <div id="admin-menu" class="admin-only space-y-2">
 <button id="dashTab-registrations" data-tab="registrations" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-clipboard-list mr-3"></i> Gestión Inscripciones
 </button>
 <button id="dashTab-adminvalidate" data-tab="adminvalidate" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-credit-card mr-3"></i> Validar Pagos
 </button>
 <button id="dashTab-blog" data-tab="blog" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-newspaper mr-3"></i> Blog
 </button>
 <button id="dashTab-pages" data-tab="pages" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-file-lines mr-3"></i> Páginas
 </button>
 <button id="dashTab-settings" data-tab="settings" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-gear mr-3"></i> Ajustes
 </button>
 <button id="dashTab-associates" data-tab="associates" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-handshake mr-3"></i> Asociados
 </button>
 <button id="dashTab-users" data-tab="users" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-users mr-3"></i> Usuarios
 </button>
 <button id="dashTab-certificates" data-tab="certificates" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-certificate mr-3"></i> Certificados
 </button>
 <button id="dashTab-viewcerts" data-tab="viewcerts" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-file-pdf mr-3"></i> Ver Certificados
 </button>
 <button id="dashTab-signatures" data-tab="signatures" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
 <i class="fa-solid fa-pen-fancy mr-3"></i> Firmas
 </button>
 </div>
 </nav>

 <button data-action="logout" class="w-full mt-8 px-4 py-3 rounded-xl border-2 border-slate-200 font-bold text-slate-600 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 transition-all">
 <i class="fa-solid fa-right-from-bracket mr-2"></i> Cerrar Sesión
 </button>
 </div>
 </aside>

 <!-- Contenido Principal -->
 <main class="lg:col-span-3 space-y-8">
 <!-- Tab: Overview/<?= htmlspecialchars(content_block_value('dashboard','menu_overview','Resumen'), ENT_QUOTES, 'UTF-8') ?> - Para todos los roles -->
 <div id="dashTab-overview-content" class="space-y-8">
 <div class="admin-only hidden space-y-8">
 <div id="adminKpiCards" class="grid grid-cols-1 md:grid-cols-3 gap-6">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">KPIs</p>
 <h3 class="text-3xl font-extrabold text-slate-900 mb-4">Cargando...</h3>
 <p class="text-sm text-slate-600"><?= htmlspecialchars(content_block_value('dashboard','menu_overview','Resumen'), ENT_QUOTES, 'UTF-8') ?> general de operación.</p>
 </div>
 </div>
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <h3 class="text-2xl font-bold text-slate-900 mb-6">Módulos Administrativos</h3>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="adminModuleOverview">
 <article class="rounded-xl bg-slate-50 border border-slate-100 p-4">
 <p class="font-bold text-slate-900">Gestión de Asociados</p>
 <p class="text-sm text-slate-600">Administra altas, datos comerciales y desempeño.</p>
 </article>
 <article class="rounded-xl bg-slate-50 border border-slate-100 p-4">
 <p class="font-bold text-slate-900">Pagos</p>
 <p class="text-sm text-slate-600">Supervisa aprobaciones y comprobantes pendientes.</p>
 </article>
 <article class="rounded-xl bg-slate-50 border border-slate-100 p-4">
 <p class="font-bold text-slate-900">Páginas</p>
 <p class="text-sm text-slate-600">Controla contenido público y páginas institucionales.</p>
 </article>
 <article class="rounded-xl bg-slate-50 border border-slate-100 p-4">
 <p class="font-bold text-slate-900">Blog y Ajustes</p>
 <p class="text-sm text-slate-600">Publica novedades y configura parámetros del sistema.</p>
 </article>
 </div>
 </div>
 </div>

 <div class="associate-only hidden space-y-8">
 <div id="associateNetworkOverview" class="grid grid-cols-1 md:grid-cols-3 gap-6">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Red de Referidos</p>
 <h3 class="text-3xl font-extrabold text-slate-900 mb-4">Cargando...</h3>
 <p class="text-sm text-slate-600"><?= htmlspecialchars(content_block_value('dashboard','menu_overview','Resumen'), ENT_QUOTES, 'UTF-8') ?> de tu red comercial.</p>
 </div>
 </div>

 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <h3 class="text-2xl font-bold text-slate-900 mb-6">Link Referido por País</h3>
 <div id="associateReferralCountryList" class="space-y-4">
 <p class="text-slate-500">Cargando países y links...</p>
 </div>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <h3 class="text-2xl font-bold text-slate-900 mb-6">Pagos Pendientes por Aprobar</h3>
 <div id="associatePendingApprovals" class="space-y-4">
 <p class="text-slate-500">Cargando pendientes...</p>
 </div>
 </div>
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <h3 class="text-2xl font-bold text-slate-900 mb-6">Historial de Referidos</h3>
 <div id="associateHistoryList" class="space-y-4">
 <p class="text-slate-500">Cargando historial...</p>
 </div>
 </div>
 </div>
 </div>

 <div class="user-only hidden space-y-8">
 <div id="userPaymentStatus" class="grid grid-cols-1 md:grid-cols-3 gap-6">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">Estado de Pago</p>
 <h3 class="text-3xl font-extrabold text-slate-900 mb-4">Cargando...</h3>
 <p class="text-sm text-slate-600">Verificando tus datos de inscripción.</p>
 </div>
 </div>
 </div>
 </div>

 <!-- ADMIN TABS -->
 <!-- Tab: Admin - Gestión Inscripciones -->
 <div id="dashTab-registrations-content" class="hidden admin-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <div class="flex items-center justify-between mb-6">
 <h3 class="text-2xl font-bold text-slate-900">Gestión de Inscripciones</h3>
 <button id="refreshAdminRegistrations" data-action="refresh-registrations" class="px-4 py-2 rounded-lg btn-primary font-bold transition-all">
 <i class="fa-solid fa-refresh"></i> Refrescar
 </button>
 </div>
 <p class="text-slate-600 mb-6">Gestiona todas las inscripciones de los usuarios a los foros.</p>
 <div class="mb-4">
 <select id="adminRegistrationsFilter" class="px-4 py-2 rounded-lg border border-slate-300 font-bold text-slate-700 hover:bg-slate-50">
 <option value="all">Referidos: todos</option>
 <option value="missing_signature">Faltante de firma</option>
 <option value="payment_pending">Pago pendiente</option>
 <option value="evidence_rejected">Evidencia rechazada</option>
 <option value="approved">Referidos aprobados</option>
  <option value="rejected">Referidos rechazados</option>
 </select>
 </div>
 <div id="adminRegistrationQuickReviewModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 p-4">
  <div class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow-2xl">
   <div class="mb-4 flex items-center justify-between">
    <h4 class="text-lg font-bold text-slate-900">Revisión rápida de evidencia</h4>
    <button id="closeAdminRegistrationQuickReview" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-bold">Cerrar</button>
   </div>
   <div id="adminRegistrationQuickReviewBody" class="space-y-4 text-sm text-slate-700"></div>
  </div>
 </div>
 <div id="adminRegistrationsList" class="space-y-4">
 <p class="text-slate-500">Cargando inscripciones...</p>
 </div>
 </div>
 </div>

 <!-- Tab: Admin - Validar Pagos -->
 <div id="dashTab-adminvalidate-content" class="hidden admin-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <div class="flex items-center justify-between mb-6">
 <h3 class="text-2xl font-bold text-slate-900">Validar Pagos</h3>
 <button data-action="refresh-adminvalidate" class="px-4 py-2 rounded-lg btn-primary font-bold transition-all">
 <i class="fa-solid fa-refresh"></i> Refrescar
 </button>
 </div>
 <div class="mb-6 space-y-4">
 <div class="flex gap-4">
 <input type="text" placeholder="Buscar por email o número de transacción..." class="flex-1 px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-[var(--color-accent)]">
 <select class="px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-[var(--color-accent)]">
 <option value="">Todos los estados</option>
 <option value="pending">Pendientes</option>
 <option value="verified">Verificados</option>
 <option value="rejected">Rechazados</option>
 </select>
 </div>
 </div>
 <div id="adminPaymentsContainer" class="space-y-4">
 <p class="text-slate-500">Cargando pagos...</p>
 </div>
 </div>
 </div>

 <!-- Tab: Admin - Blog -->
 <div id="dashTab-blog-content" class="hidden admin-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <div class="flex items-center justify-between mb-6">
 <h3 class="text-2xl font-bold text-slate-900">Gestión del Blog</h3>
 <button class="px-4 py-2 rounded-lg btn-primary font-bold transition-all">
 <i class="fa-solid fa-plus mr-2"></i> Nuevo Artículo
 </button>
 </div>
 <div id="blogPostsList" class="space-y-4">
 <p class="text-slate-500">Cargando artículos...</p>
 </div>
 </div>
 </div>

 <!-- Tab: Admin - Paginas -->
 <div id="dashTab-pages-content" class="hidden admin-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <div class="flex items-center justify-between mb-6">
 <h3 class="text-2xl font-bold text-slate-900">Páginas Personalizadas</h3>
 <button class="px-4 py-2 rounded-lg btn-primary font-bold transition-all">
 <i class="fa-solid fa-plus mr-2"></i> Nueva Página
 </button>
 </div>
 <div id="pagesContainer" class="space-y-4">
 <p class="text-slate-500">Cargando páginas...</p>
 </div>
 </div>
 </div>

 <!-- Tab: Admin - Ajustes -->
 <div id="dashTab-settings-content" class="hidden admin-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <h3 class="text-2xl font-bold text-slate-900 mb-6">Ajustes del Sistema</h3>
 <div class="flex gap-2 mb-6">
  <button id="settings-subtab-general" type="button" class="px-4 py-2 rounded-lg bg-slate-100 font-bold" onclick="setSettingsSubtab('general')">General</button>
  <button id="settings-subtab-styles" type="button" class="px-4 py-2 rounded-lg font-bold" onclick="setSettingsSubtab('styles')">Estilos</button>
 </div>
 <form id="adminSettingsForm" class="space-y-6">
 <div class="border-b border-slate-200 pb-6">
 <h4 class="font-bold text-slate-900 mb-4">Contacto Público</h4>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 <div><label class="block text-sm font-bold text-slate-700 mb-2">Teléfono principal</label><input name="public_phone_primary" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium"></div>
 <div><label class="block text-sm font-bold text-slate-700 mb-2">Teléfono secundario</label><input name="public_phone_secondary" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium"></div>
 <div><label class="block text-sm font-bold text-slate-700 mb-2">Email principal</label><input name="public_email_primary" type="email" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium"></div>
 <div><label class="block text-sm font-bold text-slate-700 mb-2">Email soporte</label><input name="public_email_support" type="email" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium"></div>
 </div>
 </div>
 <div class="border-b border-slate-200 pb-6">
 <h4 class="font-bold text-slate-900 mb-4">Directora y textos</h4>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 <div><label class="block text-sm font-bold text-slate-700 mb-2">Nombre de la directora</label><input name="director_name" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium"></div>
 <div><label class="block text-sm font-bold text-slate-700 mb-2">Título profesional</label><input name="director_title" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium"></div>
 <div><label class="block text-sm font-bold text-slate-700 mb-2">Ubicación</label><input name="director_location" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium"></div>
 <div class="md:col-span-2"><label class="block text-sm font-bold text-slate-700 mb-2">Texto corto (footer/home)</label><textarea name="contact_short_text" rows="2" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium"></textarea></div>
 <div class="md:col-span-2"><label class="block text-sm font-bold text-slate-700 mb-2">Texto CTA de contacto</label><textarea name="contact_cta_text" rows="2" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium"></textarea></div>
 </div>
 </div>
 <div class="border-b border-slate-200 pb-6">
 <h4 class="font-bold text-slate-900 mb-4">Marca visual</h4>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 <div><label class="block text-sm font-bold text-slate-700 mb-2">Color principal</label><input name="brand_color_primary" type="color" class="w-full h-12 rounded-xl border border-slate-300 bg-slate-50 font-medium"></div>
 <div><label class="block text-sm font-bold text-slate-700 mb-2">Color acento</label><input name="brand_color_accent" type="color" class="w-full h-12 rounded-xl border border-slate-300 bg-slate-50 font-medium"></div>
 </div>
 </div>
 <p id="adminSettingsStatus" class="text-sm text-slate-600">Cargando valores...</p>
 <button type="submit" class="w-full px-6 py-3 rounded-xl btn-primary font-bold transition-all">
 <i class="fa-solid fa-save mr-2"></i> Guardar Cambios
 </button>
 </form>
 <div id="settingsStylesPanel" class="hidden space-y-4">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
   <div><label class="block text-sm font-bold mb-2">Color primario</label><input name="theme_colors_primary" type="color" class="w-full h-12 rounded-xl border"></div>
   <div><label class="block text-sm font-bold mb-2">Color secundario</label><input name="theme_colors_secondary" type="color" class="w-full h-12 rounded-xl border"></div>
   <div><label class="block text-sm font-bold mb-2">Color acento</label><input name="theme_colors_accent" type="color" class="w-full h-12 rounded-xl border"></div>
   <div><label class="block text-sm font-bold mb-2">Tipografía</label><select name="theme_typography_font_family" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50"><option>Plus Jakarta Sans</option><option>Inter</option><option>Roboto</option><option>Montserrat</option><option>Lato</option></select></div>
   <div><label class="block text-sm font-bold mb-2">Radio (md)</label><input name="theme_radius_md" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50" placeholder="16px"></div>
   <div><label class="block text-sm font-bold mb-2">Sombra tarjeta</label><input name="theme_shadows_card" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50"></div>
   <div><label class="block text-sm font-bold mb-2">Espaciado (md)</label><input name="theme_spacing_md" type="text" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50"></div>
   <div><label class="block text-sm font-bold mb-2">Tamaño botón</label><select name="theme_buttons_size" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50"><option value="sm">sm</option><option value="md">md</option><option value="lg">lg</option></select></div>
  </div>
  <div id="themePreview" class="rounded-2xl border border-slate-200 p-4">
   <p class="font-bold mb-2">Preview instantáneo</p><button type="button" class="btn-primary">Botón de ejemplo</button>
  </div>
  <div class="flex gap-2">
   <button type="button" id="saveThemeBtn" class="px-6 py-3 rounded-xl btn-primary font-bold">Guardar estilos</button>
   <button type="button" id="resetThemeBtn" class="px-6 py-3 rounded-xl btn-secondary font-bold">Restablecer tema por defecto</button>
  </div>
  <p id="themeStatus" class="text-sm text-slate-600"></p>
 </div>
 </div>
 </div>

<!-- Tab: Admin - Asociados -->
 <div id="dashTab-associates-content" class="hidden admin-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <div class="flex items-center justify-between mb-6">
 <h3 class="text-2xl font-bold text-slate-900">Gestión de Asociados</h3>
 <button id="refreshAdminAssociates" class="px-4 py-2 rounded-lg btn-primary font-bold transition-all">
 <i class="fa-solid fa-refresh"></i> Refrescar
 </button>
 </div>
 <div id="adminAssociatesList" class="space-y-4">
 <p class="text-slate-500">Cargando asociados...</p>
 </div>
 <div class="mt-8 border-t border-slate-100 pt-6">
 <h4 class="text-lg font-bold text-slate-900 mb-4">Trazabilidad de red (quién invitó a quién)</h4>
 <div id="adminNetworkTraceList" class="space-y-3">
 <p class="text-slate-500">Cargando trazabilidad...</p>
 </div>
 </div>
 </div>
 </div>

 <!-- Tab: Admin - Usuarios -->
 <div id="dashTab-users-content" class="hidden admin-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
 <h3 class="text-2xl font-bold text-slate-900">Gestión de Usuarios</h3>
 <div class="flex gap-2">
 <input id="adminUsersSearch" type="text" placeholder="Buscar usuario..." class="px-4 py-2 rounded-lg border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-[var(--color-accent)]">
 <button id="refreshAdminUsers" class="px-4 py-2 rounded-lg btn-primary font-bold transition-all">
 <i class="fa-solid fa-refresh"></i>
 </button>
 </div>
 </div>
 <p class="text-slate-600 mb-4">Define flags manuales de <span class="font-bold">Validado</span> y <span class="font-bold">Pago</span>. Si un usuario aún no tiene flags explícitos, se mostrará el valor heredado desde el estado de inscripción.</p>
 <div id="adminUsersFeedback" class="hidden rounded-xl px-4 py-3 text-sm font-semibold mb-4"></div>
 <div id="usersList" class="space-y-4">
 <p class="text-slate-500">Cargando usuarios...</p>
 </div>
 </div>
 </div>

 <!-- Tab: Admin - Certificados -->
 <div id="dashTab-certificates-content" class="hidden admin-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <div class="flex items-center justify-between mb-6">
 <h3 class="text-2xl font-bold text-slate-900">Generador de Certificados</h3>
 <div class="flex gap-2">
 <select id="certificateForumFilter" class="px-4 py-2 rounded-lg border border-slate-300 font-bold text-slate-700 bg-slate-50">
 <option value="">Todos los foros</option>
 </select>
 <button id="refreshCertificates" class="px-4 py-2 rounded-lg btn-primary font-bold transition-all">
 <i class="fa-solid fa-refresh"></i> Refrescar
 </button>
 </div>
 </div>
 <p class="text-slate-600 mb-4">Genera automáticamente certificados para usuarios validados y pagados. Solo se muestra a usuarios con ambas condiciones cumplidas.</p>
 <div id="certificatesAlert" class="hidden rounded-xl px-4 py-3 text-sm font-semibold mb-4"></div>
 <div id="certificatesList" class="space-y-4">
 <p class="text-slate-500">Cargando usuarios elegibles...</p>
 </div>
 </div>
 </div>

 <!-- Tab: Admin - Ver Certificados -->
 <div id="dashTab-viewcerts-content" class="hidden admin-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <div class="flex items-center justify-between mb-6">
 <h3 class="text-2xl font-bold text-slate-900">Ver Certificados Generados</h3>
 <button id="refreshViewCerts" class="px-4 py-2 rounded-lg btn-primary font-bold transition-all">
 <i class="fa-solid fa-refresh"></i> Refrescar
 </button>
 </div>
 <p class="text-slate-600 mb-4">Visualiza y descarga los certificados generados en PDF.</p>
 <div id="viewCertsAlert" class="hidden rounded-xl px-4 py-3 text-sm font-semibold mb-4"></div>
 <div id="viewCertsList" class="space-y-4">
 <p class="text-slate-500">Cargando certificados...</p>
 </div>
 </div>
 </div>

 <!-- Tab: Admin - Firmas -->
 <div id="dashTab-signatures-content" class="hidden admin-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <div class="flex items-center justify-between mb-6">
 <h3 class="text-2xl font-bold text-slate-900">Firmas de Inscripciones</h3>
 <div class="flex gap-2">
 <select id="signaturesForumFilter" class="px-4 py-2 rounded-lg border border-slate-300 font-bold text-slate-700 bg-slate-50">
 <option value="">Todos los foros</option>
 </select>
 <button id="refreshSignatures" class="px-4 py-2 rounded-lg btn-primary font-bold transition-all">
 <i class="fa-solid fa-refresh"></i> Refrescar
 </button>
 </div>
 </div>
 <p class="text-slate-600 mb-4">Visualiza las firmas digitales de los usuarios que se inscribieron en los foros.</p>
 <div id="signaturesAlert" class="hidden rounded-xl px-4 py-3 text-sm font-semibold mb-4"></div>
 <div id="signaturesList" class="space-y-4">
 <p class="text-slate-500">Cargando firmas...</p>
 </div>
 </div>
 </div>

 <!-- ASSOCIATE TABS -->
 <!-- Tab: Associate - Link Referido Grupo -->
 <div id="dashTab-referrals-content" class="hidden associate-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <h3 class="text-2xl font-bold text-slate-900 mb-6">Mi Link de Referido</h3>
 <div class="space-y-6">
 <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6">
 <div class="flex gap-4 items-center mb-4">
 <input type="text" id="myReferralCode" value="Generando link..." readonly class="flex-1 px-4 py-3 rounded-xl border border-slate-300 bg-white font-mono text-sm">
 <button onclick="navigator.clipboard.writeText(document.getElementById('myReferralCode').value)" class="px-6 py-3 rounded-xl btn-primary font-bold ">
 <i class="fa-solid fa-copy"></i> Copiar
 </button>
 </div>
 <p class="text-sm text-amber-900">Comparte este link para que otros profesionales se registren a través de tu referido y obtengas comisión.</p>
 </div>

 <div class="border-t border-slate-200 pt-6">
 <h4 class="font-bold text-slate-900 mb-4">Configurar Datos de Pago</h4>
 <form id="paymentMethodsForm" class="space-y-4">
 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 <div>
 <label class="block text-sm font-bold text-slate-700 mb-2">Banco *</label>
 <input name="bankName" type="text" placeholder="Ej: Banco Galicia" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-[var(--color-accent)]" required>
 </div>
 <div>
 <label class="block text-sm font-bold text-slate-700 mb-2">Moneda *</label>
 <select name="currency" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-[var(--color-accent)]">
 <option value="ARS">ARS (Pesos Argentinos)</option>
 <option value="USD">USD (Dólares)</option>
 <option value="COP">COP (Pesos Colombianos)</option>
 <option value="MXN">MXN (Pesos Mexicanos)</option>
 </select>
 </div>
 <div>
 <label class="block text-sm font-bold text-slate-700 mb-2">Titular de la Cuenta *</label>
 <input name="accountHolder" type="text" placeholder="Nombre del titular" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-[var(--color-accent)]" required>
 </div>
 <div>
 <label class="block text-sm font-bold text-slate-700 mb-2">Número de Cuenta *</label>
 <input name="accountNumber" type="text" placeholder="Número de cuenta" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-[var(--color-accent)]" required>
 </div>
 <div>
 <label class="block text-sm font-bold text-slate-700 mb-2">Tipo de Cuenta</label>
 <select name="accountType" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-[var(--color-accent)]">
 <option value="">Seleccionar...</option>
 <option value="checking">Cuenta Corriente</option>
 <option value="savings">Cuenta de Ahorros</option>
 </select>
 </div>
 <div>
 <label class="block text-sm font-bold text-slate-700 mb-2">Alias o Referencia</label>
 <input name="aliasOrReference" type="text" placeholder="Alias, CBU, IBAN u otra referencia" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-[var(--color-accent)]">
 </div>
 </div>
 <div id="paymentMethodsStatus" class="text-sm text-slate-600"></div>
 <button type="submit" class="w-full px-6 py-3 rounded-xl btn-primary font-bold transition-all">
 <i class="fa-solid fa-save mr-2"></i> Guardar Datos de Pago
 </button>
 </form>
 </div>
 </div>
 </div>
 </div>

 <!-- Tab: Associate - Mis Referidos -->
 <div id="dashTab-myreferrals-content" class="hidden associate-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <div class="flex items-center justify-between mb-6">
 <h3 class="text-2xl font-bold text-slate-900">Mis Referidos</h3>
 <div>
 <select id="associateRegistrationsFilter" class="px-4 py-2 rounded-lg border border-slate-300 font-bold text-slate-700 hover:bg-slate-50">
 <option value="all">Todos los estados</option>
 <option value="pending">Pendientes</option>
 <option value="payment_submitted">Comprobante enviado</option>
 <option value="approved">Referidos aprobados</option>
  <option value="rejected">Referidos rechazados</option>
 </select>
 <button id="refreshAssociateRegistrations" class="ml-2 px-4 py-2 rounded-lg btn-primary font-bold transition-all">
 <i class="fa-solid fa-refresh"></i> Refrescar
 </button>
 </div>
 </div>
 <div id="associateRegistrationsList" class="space-y-4">
 <p class="text-slate-500">Cargando referidos...</p>
 </div>
 </div>
 </div>

 <!-- Tab: Associate - Validar Pagos -->
 <div id="dashTab-validatepayments-content" class="hidden associate-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <div class="flex items-center justify-between mb-6">
 <h3 class="text-2xl font-bold text-slate-900">Validar Pagos de Referidos</h3>
 <button id="refreshAssociatePayments" class="px-4 py-2 rounded-lg btn-primary font-bold transition-all">
 <i class="fa-solid fa-refresh"></i> Refrescar
 </button>
 </div>
 <div id="associatePaymentsContainer" class="space-y-4">
 <p class="text-slate-500">Cargando comprobantes de pago...</p>
 </div>
 <div class="mt-8 border-t border-slate-100 pt-6">
 <h4 class="text-lg font-bold text-slate-900 mb-4">Trazabilidad de red (quién invité)</h4>
 <div id="associateNetworkTraceList" class="space-y-3">
 <p class="text-slate-500">Cargando trazabilidad...</p>
 </div>
 </div>
 </div>
 </div>

 <!-- USER TABS -->
 <!-- Tab: User - Mis Foros Activos -->
 <div id="dashTab-myforums-content" class="hidden user-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <h3 class="text-2xl font-bold text-slate-900 mb-6">Mis Foros Activos</h3>
 <p id="userBenefitsSummary" class="text-slate-600 mb-6">Cargando información de tus inscripciones...</p>
 <div id="userBenefitsList" class="space-y-4">
 <p class="text-slate-500">Cargando inscripciones...</p>
 </div>
 </div>
 </div>

 <!-- Tab: User - Materiales y Certificados -->
 <div id="dashTab-ebooks-content" class="hidden user-only space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <h3 class="text-2xl font-bold text-slate-900 mb-6">Materiales y Certificados</h3>
 <div id="userEbooksAlert" class="hidden rounded-2xl px-4 py-3 text-sm font-bold mb-6"></div>
 <div id="userEbooksList" class="space-y-4">
 <p class="text-slate-500">Cargando materiales y certificados disponibles...</p>
 </div>
 </div>
 </div>

 <!-- Tab: Mi Perfil para todos los roles -->
 <div id="dashTab-profile-content" class="hidden space-y-8">
 <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
 <h3 class="text-2xl font-bold text-slate-900 mb-6">Mi Perfil</h3>
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="profileContent">
 <p class="text-slate-500">Cargando perfil...</p>
 </div>
 </div>
 </div>
 </main>
 </div>
 </div>
</section>

<?php
$content = ob_get_clean();

render_main_layout([
 'title' => 'Mi Área | Foros PSME',
 'role' => $_viewCurrentRole,
 'content' => $content,
 'scripts' => [
 '/assets/js/navigation.js',
 '/assets/js/auth.js',
 '/assets/js/registrations.js',
 '/assets/js/forums.js',
 '/assets/js/pages-admin.js',
 '/assets/js/dashboard.js',
 ],
]);
