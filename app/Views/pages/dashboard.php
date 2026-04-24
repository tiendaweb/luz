<?php
declare(strict_types=1);
require_once __DIR__ . '/../_session.php';
require_once __DIR__ . '/../layouts/main.php';

// Guard: solo usuarios autenticados
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
                    <div class="flex flex-col items-center text-center mb-8">
                        <div id="userInitial" class="w-16 h-16 rounded-full bg-teal-600 text-white flex items-center justify-center text-2xl font-extrabold mb-4">U</div>
                        <h3 id="userName" class="text-lg font-bold text-slate-900">Usuario</h3>
                        <div id="userRoleBadge" class="mt-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 text-xs font-bold uppercase">Usuario</div>
                    </div>

                    <nav class="space-y-2 border-t border-slate-100 pt-6">
                        <!-- Common for all roles -->
                        <button id="dashTab-overview" onclick="setDashTab('overview')" class="w-full text-left px-4 py-3 rounded-xl bg-slate-100 font-bold text-slate-700 hover:bg-slate-200 transition-all">
                            <i class="fa-solid fa-chart-line mr-3"></i> Resumen
                        </button>

                        <!-- User tabs -->
                        <div class="user-only hidden space-y-2">
                            <button id="dashTab-myforums" onclick="setDashTab('myforums')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
                                <i class="fa-solid fa-calendar mr-3"></i> Mis Foros Activos
                            </button>
                            <button id="dashTab-ebooks" onclick="setDashTab('ebooks')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
                                <i class="fa-solid fa-book mr-3"></i> Materiales y Certificados
                            </button>
                        </div>

                        <!-- Associate tabs -->
                        <div class="associate-only hidden space-y-2">
                            <button id="dashTab-referrals" onclick="setDashTab('referrals')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
                                <i class="fa-solid fa-share-nodes mr-3"></i> Link Referido Grupo
                            </button>
                            <button id="dashTab-myreferrals" onclick="setDashTab('myreferrals')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
                                <i class="fa-solid fa-users mr-3"></i> Mis Referidos
                            </button>
                            <button id="dashTab-validatepayments" onclick="setDashTab('validatepayments')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
                                <i class="fa-solid fa-credit-card mr-3"></i> Validar Pagos
                            </button>
                        </div>

                        <!-- Admin tabs -->
                        <div class="admin-only hidden space-y-2">
                            <button id="dashTab-registrations" onclick="setDashTab('registrations')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
                                <i class="fa-solid fa-clipboard-list mr-3"></i> Gestión Inscripciones
                            </button>
                            <button id="dashTab-adminvalidate" onclick="setDashTab('adminvalidate')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
                                <i class="fa-solid fa-credit-card mr-3"></i> Validar Pagos
                            </button>
                            <button id="dashTab-blog" onclick="setDashTab('blog')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
                                <i class="fa-solid fa-newspaper mr-3"></i> Blog
                            </button>
                            <button id="dashTab-pages" onclick="setDashTab('pages')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
                                <i class="fa-solid fa-file-lines mr-3"></i> Paginas
                            </button>
                            <button id="dashTab-settings" onclick="setDashTab('settings')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
                                <i class="fa-solid fa-gear mr-3"></i> Ajustes
                            </button>
                            <button id="dashTab-associates" onclick="setDashTab('associates')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
                                <i class="fa-solid fa-handshake mr-3"></i> Asociados
                            </button>
                            <button id="dashTab-users" onclick="setDashTab('users')" class="w-full text-left px-4 py-3 rounded-xl font-bold text-slate-700 hover:bg-slate-100 transition-all">
                                <i class="fa-solid fa-users mr-3"></i> Usuarios
                            </button>
                        </div>
                    </nav>

                    <button onclick="window.logout()" class="w-full mt-8 px-4 py-3 rounded-xl border-2 border-slate-200 font-bold text-slate-600 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 transition-all">
                        <i class="fa-solid fa-right-from-bracket mr-2"></i> Cerrar Sesión
                    </button>
                </div>
            </aside>

            <!-- Contenido Principal -->
            <main class="lg:col-span-3 space-y-8">
                <!-- Tab: Overview/Resumen - Para todos los roles -->
                <div id="dashTab-overview-content" class="space-y-8">
                    <div class="admin-only hidden space-y-8">
                        <div id="adminKpiCards" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
                                <p class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-2">KPIs</p>
                                <h3 class="text-3xl font-extrabold text-slate-900 mb-4">Cargando...</h3>
                                <p class="text-sm text-slate-600">Resumen general de operación.</p>
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
                                <p class="text-sm text-slate-600">Resumen de tu red comercial.</p>
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
                            <button id="refreshAdminRegistrations" class="px-4 py-2 rounded-lg bg-teal-600 text-white font-bold hover:bg-teal-700 transition-all">
                                <i class="fa-solid fa-refresh"></i> Refrescar
                            </button>
                        </div>
                        <p class="text-slate-600 mb-6">Gestiona todas las inscripciones de los usuarios a los foros.</p>
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
                            <button class="px-4 py-2 rounded-lg bg-teal-600 text-white font-bold hover:bg-teal-700 transition-all">
                                <i class="fa-solid fa-refresh"></i> Refrescar
                            </button>
                        </div>
                        <div class="mb-6 space-y-4">
                            <div class="flex gap-4">
                                <input type="text" placeholder="Buscar por email o número de transacción..." class="flex-1 px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-teal-500">
                                <select class="px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-teal-500">
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
                            <button class="px-4 py-2 rounded-lg bg-teal-600 text-white font-bold hover:bg-teal-700 transition-all">
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
                            <button class="px-4 py-2 rounded-lg bg-teal-600 text-white font-bold hover:bg-teal-700 transition-all">
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
                        <div class="space-y-6">
                            <div class="border-b border-slate-200 pb-6">
                                <h4 class="font-bold text-slate-900 mb-4">Configuración General</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Nombre del Sitio</label>
                                        <input type="text" value="Foros PSME" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-teal-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Email de Soporte</label>
                                        <input type="email" value="soporte@forospsme.com" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-teal-500">
                                    </div>
                                </div>
                            </div>
                            <div class="border-b border-slate-200 pb-6">
                                <h4 class="font-bold text-slate-900 mb-4">Configuración de Seguridad</h4>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" checked class="w-5 h-5 rounded text-teal-600">
                                    <span class="font-bold text-slate-700">Requerir verificación de email</span>
                                </label>
                            </div>
                            <button class="w-full px-6 py-3 rounded-xl bg-teal-600 text-white font-bold hover:bg-teal-700 transition-all">
                                <i class="fa-solid fa-save mr-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tab: Admin - Asociados -->
                <div id="dashTab-associates-content" class="hidden admin-only space-y-8">
                    <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl font-bold text-slate-900">Gestión de Asociados</h3>
                            <button id="refreshAdminAssociates" class="px-4 py-2 rounded-lg bg-teal-600 text-white font-bold hover:bg-teal-700 transition-all">
                                <i class="fa-solid fa-refresh"></i> Refrescar
                            </button>
                        </div>
                        <div id="adminAssociatesList" class="space-y-4">
                            <p class="text-slate-500">Cargando asociados...</p>
                        </div>
                    </div>
                </div>

                <!-- Tab: Admin - Usuarios -->
                <div id="dashTab-users-content" class="hidden admin-only space-y-8">
                    <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl font-bold text-slate-900">Gestión de Usuarios</h3>
                            <div class="flex gap-2">
                                <input type="text" placeholder="Buscar usuario..." class="px-4 py-2 rounded-lg border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-teal-500">
                                <button class="px-4 py-2 rounded-lg bg-teal-600 text-white font-bold hover:bg-teal-700 transition-all">
                                    <i class="fa-solid fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div id="usersList" class="space-y-4">
                            <p class="text-slate-500">Cargando usuarios...</p>
                        </div>
                    </div>
                </div>

                <!-- ASSOCIATE TABS -->
                <!-- Tab: Associate - Link Referido Grupo -->
                <div id="dashTab-referrals-content" class="hidden associate-only space-y-8">
                    <div class="bg-white rounded-3xl p-8 shadow-lg border border-slate-100">
                        <h3 class="text-2xl font-bold text-slate-900 mb-6">Mi Link de Referido</h3>
                        <div class="space-y-6">
                            <div class="bg-violet-50 border border-violet-200 rounded-2xl p-6">
                                <div class="flex gap-4 items-center mb-4">
                                    <input type="text" id="myReferralCode" value="Generando link..." readonly class="flex-1 px-4 py-3 rounded-xl border border-slate-300 bg-white font-mono text-sm">
                                    <button onclick="navigator.clipboard.writeText(document.getElementById('myReferralCode').value)" class="px-6 py-3 rounded-xl bg-teal-600 text-white font-bold hover:bg-teal-700">
                                        <i class="fa-solid fa-copy"></i> Copiar
                                    </button>
                                </div>
                                <p class="text-sm text-violet-900">Comparte este link para que otros profesionales se registren a través de tu referido y obtengas comisión.</p>
                            </div>

                            <div class="border-t border-slate-200 pt-6">
                                <h4 class="font-bold text-slate-900 mb-4">Configurar Datos de Pago</h4>
                                <form id="paymentMethodsForm" class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 mb-2">Banco *</label>
                                            <input name="bankName" type="text" placeholder="Ej: Banco Galicia" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-teal-500" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 mb-2">Moneda *</label>
                                            <select name="currency" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-teal-500">
                                                <option value="ARS">ARS (Pesos Argentinos)</option>
                                                <option value="USD">USD (Dólares)</option>
                                                <option value="COP">COP (Pesos Colombianos)</option>
                                                <option value="MXN">MXN (Pesos Mexicanos)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 mb-2">Titular de la Cuenta *</label>
                                            <input name="accountHolder" type="text" placeholder="Nombre del titular" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-teal-500" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 mb-2">Número de Cuenta *</label>
                                            <input name="accountNumber" type="text" placeholder="Número de cuenta" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-teal-500" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 mb-2">Tipo de Cuenta</label>
                                            <select name="accountType" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-teal-500">
                                                <option value="">Seleccionar...</option>
                                                <option value="checking">Cuenta Corriente</option>
                                                <option value="savings">Cuenta de Ahorros</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 mb-2">Alias o Referencia</label>
                                            <input name="aliasOrReference" type="text" placeholder="Alias, CBU, IBAN u otra referencia" class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-teal-500">
                                        </div>
                                    </div>
                                    <div id="paymentMethodsStatus" class="text-sm text-slate-600"></div>
                                    <button type="submit" class="w-full px-6 py-3 rounded-xl bg-teal-600 text-white font-bold hover:bg-teal-700 transition-all">
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
                                    <option value="approved">Aprobadas</option>
                                    <option value="rejected">Rechazadas</option>
                                </select>
                                <button id="refreshAssociateRegistrations" class="ml-2 px-4 py-2 rounded-lg bg-teal-600 text-white font-bold hover:bg-teal-700 transition-all">
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
                            <button class="px-4 py-2 rounded-lg bg-teal-600 text-white font-bold hover:bg-teal-700 transition-all">
                                <i class="fa-solid fa-refresh"></i> Refrescar
                            </button>
                        </div>
                        <div class="mb-6">
                            <input type="text" placeholder="Buscar por email o número de transacción..." class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 font-medium focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div id="associatePaymentsContainer" class="space-y-4">
                            <p class="text-slate-500">Cargando comprobantes de pago...</p>
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
            </main>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();

render_main_layout([
    'title'   => 'Mi Área | Foros PSME',
    'role'    => $_viewCurrentRole,
    'content' => $content,
    'scripts' => [
        '/assets/js/auth.js',
        '/assets/js/registrations.js',
        '/assets/js/forums.js',
        '/assets/js/pages-admin.js',
    ],
]);
