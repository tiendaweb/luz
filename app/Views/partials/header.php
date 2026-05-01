<?php

declare(strict_types=1);

// _session.php is loaded at the page entry point (e.g. pages/dashboard.php) and defines
// $_viewIsLoggedIn etc. in *page* scope. But this partial is included from inside
// render_main_layout() (function scope), so those page-scope variables are not visible here
// and `require_once _session.php` would be a no-op. Read from $_SESSION directly.
require_once __DIR__ . '/../../Support/SiteSettings.php';

if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../_session.php';
}

$_headerCurrentUser = $_SESSION['auth_user'] ?? null;
$_viewIsLoggedIn = is_array($_headerCurrentUser);

// $siteSettings may not be in scope when render_main_layout includes us; fall back to public defaults.
if (!isset($siteSettings) || !is_array($siteSettings)) {
    $siteSettings = app_public_site_settings();
}

$isStaticHeader = true;
$directorName = (string)($siteSettings['director_name'] ?? 'María Luz Genovese');

?>
 <!-- NAVBAR -->
 <nav class="glass fixed w-full z-50 border-b border-slate-100">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="flex justify-between h-20">
 <a class="flex items-center cursor-pointer" href="/">
 <img src="<?= htmlspecialchars((string)($siteSettings['brand_logo_path'] ?? '/uploads/logo.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="Foros PSME" class="w-11 h-11 rounded-xl shadow-lg mr-4 object-cover">
 <div>
 <h1 class="text-xl font-extrabold text-slate-800 tracking-tight leading-none">Foros PSME</h1>
 <p class="text-[10px] uppercase tracking-widest font-bold mt-1" style="color: var(--color-accent);">Dir. <?= htmlspecialchars($directorName, ENT_QUOTES, 'UTF-8') ?></p>
 </div>
 </a>

 <!-- Desktop Menu -->
 <div class="hidden md:flex items-center space-x-8">
 <a href="/" class="text-sm font-semibold hover:text-brand transition-colors">Inicio</a>
 <a href="/foros" class="text-sm font-semibold hover:text-brand transition-colors">Foros y Agenda</a>
 <a href="/directora" class="text-sm font-semibold hover:text-brand transition-colors">La Directora</a>
 <a href="/blog" class="text-sm font-semibold hover:text-brand transition-colors">Blog</a>

 <?php if ($_viewIsLoggedIn): ?>
 <a href="/dashboard" class="user-access-btn bg-slate-900 text-white px-5 py-2.5 rounded-full text-sm font-bold hover:bg-brand transition-all flex items-center gap-2">
 <i class="fa-solid fa-circle-user"></i> Mi Área
 </a>
 <?php else: ?>
 <a href="/login" class="font-semibold px-5 py-2.5 rounded-full text-sm transition-colors flex items-center gap-2 border" style="color: var(--color-accent); border-color: var(--color-accent);">
 <i class="fa-solid fa-right-to-bracket"></i> Ingresar
 </a>
 <?php endif; ?>

 <a href="/inscripcion" class="btn-primary px-6 py-2.5 rounded-full text-sm font-bold shadow-md transition-all flex items-center gap-2">
 <i class="fa-solid fa-ticket"></i> Inscribirse
 </a>
 </div>

 <!-- Mobile menu button -->
 <div class="flex items-center md:hidden">
 <button onclick="toggleMobileMenu()" class="text-slate-600 p-2">
 <i class="fa-solid fa-bars-staggered text-2xl"></i>
 </button>
 </div>
 </div>
 </div>
 </nav>

 <!-- MOBILE MENU -->
 <div id="mobileMenu" class="fixed inset-0 z-[60] bg-white hidden">
 <div class="p-6">
 <div class="flex justify-end mb-8">
 <button onclick="toggleMobileMenu()"><i class="fa-solid fa-xmark text-3xl"></i></button>
 </div>
 <div class="flex flex-col space-y-6 text-center text-xl font-bold">
 <a href="/" onclick="toggleMobileMenu()">Inicio</a>
 <a href="/foros" onclick="toggleMobileMenu()">Foros y Agenda</a>
 <a href="/directora" onclick="toggleMobileMenu()">La Directora</a>
 <a href="/blog" onclick="toggleMobileMenu()">Blog</a>
 <hr>
 <?php if ($_viewIsLoggedIn): ?>
 <a href="/dashboard" onclick="toggleMobileMenu()" class="mobile-user-access-btn bg-slate-900 text-white py-4 rounded-2xl flex items-center justify-center gap-2"><i class="fa-solid fa-circle-user"></i> Mi Área</a>
 <?php else: ?>
 <a href="/login" onclick="toggleMobileMenu()" class="font-bold py-4 rounded-2xl flex items-center justify-center gap-2 border" style="color: var(--color-accent); border-color: var(--color-accent);"><i class="fa-solid fa-right-to-bracket"></i> Ingresar</a>
 <?php endif; ?>
 <a href="/inscripcion" onclick="toggleMobileMenu()" class="btn-primary py-4 rounded-2xl flex items-center justify-center gap-2"><i class="fa-solid fa-ticket"></i> Inscribirse a los Foros</a>
 </div>
 </div>
 </div>
