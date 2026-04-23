<?php

declare(strict_types=1);

require_once __DIR__ . '/layouts/auth.php';

ob_start();
?>
<main class="relative min-h-screen overflow-hidden py-10 px-4 sm:px-6 lg:px-8">
  <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-teal-100 blur-3xl opacity-80"></div>
  <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full bg-cyan-100 blur-3xl opacity-80"></div>

  <div class="relative mx-auto w-full max-w-5xl grid gap-8 lg:grid-cols-2 items-stretch">
    <section class="hidden lg:flex rounded-3xl bg-gradient-to-br from-teal-700 to-teal-500 text-white p-10 flex-col justify-between shadow-2xl">
      <div>
        <p class="uppercase tracking-[0.2em] text-xs font-bold text-teal-100">Foros LATAM PSME</p>
        <h1 class="mt-4 text-4xl font-extrabold leading-tight">Ingreso al área de gestión y seguimiento.</h1>
        <p class="mt-4 text-teal-50 text-lg">Accede con tus credenciales para ver panel, actividad e inscripciones.</p>
      </div>
      <div class="rounded-2xl bg-white/15 border border-white/30 p-5 backdrop-blur-sm">
        <p class="text-sm font-semibold">Tip de testing</p>
        <p class="mt-2 text-sm text-teal-50">Usa “Autocompletar credenciales de prueba” para cargar perfiles demo en un clic.</p>
      </div>
    </section>

    <section class="rounded-3xl bg-white border border-slate-200 shadow-xl p-6 sm:p-10">
      <div class="mb-8">
        <p class="text-sm uppercase tracking-widest text-teal-600 font-bold">Acceso Seguro</p>
        <h2 class="mt-2 text-3xl font-extrabold text-slate-900">Iniciar sesión</h2>
        <p class="mt-2 text-slate-500">Ingresa para continuar al dashboard.</p>
      </div>

      <div id="loginAlert" class="hidden rounded-2xl px-4 py-3 text-sm font-bold mb-6"></div>

      <form id="loginForm" class="space-y-5" novalidate>
        <div>
          <label for="username" class="block text-sm font-bold text-slate-700 mb-2">Email o usuario</label>
          <input id="username" name="username" type="text" autocomplete="username" required
            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"
            placeholder="ej: admin@forospsme.com o admin" />
        </div>

        <div>
          <label for="password" class="block text-sm font-bold text-slate-700 mb-2">Contraseña</label>
          <input id="password" name="password" type="password" autocomplete="current-password" required
            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"
            placeholder="••••••••" />
        </div>

        <div>
          <label for="role" class="block text-sm font-bold text-slate-700 mb-2">Rol (opcional para testing visual)</label>
          <select id="role" name="role"
            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
            <option value="user" selected>Usuario</option>
            <option value="associate">Asociado</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <details class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
          <summary class="cursor-pointer list-none font-bold text-slate-800 flex items-center justify-between">
            <span><i class="fa-solid fa-wand-magic-sparkles mr-2"></i>Autocompletar credenciales de prueba</span>
          </summary>
          <div class="mt-3 grid gap-2 sm:grid-cols-3">
            <button type="button" data-demo-role="admin" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold hover:border-teal-500 hover:text-teal-700 transition">Admin</button>
            <button type="button" data-demo-role="associate" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold hover:border-teal-500 hover:text-teal-700 transition">Asociado</button>
            <button type="button" data-demo-role="user" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold hover:border-teal-500 hover:text-teal-700 transition">Usuario</button>
          </div>
        </details>

        <button id="loginSubmitBtn" type="submit"
          class="w-full rounded-2xl bg-teal-600 text-white px-5 py-3 font-bold hover:bg-teal-700 transition focus:outline-none focus:ring-2 focus:ring-teal-200 focus:ring-offset-2">
          Ingresar
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-slate-500">¿Volver al sitio? <a class="font-bold text-teal-700 hover:text-teal-600" href="/index.php">Ir al inicio</a></p>
    </section>
  </div>
</main>
<?php
$content = (string)ob_get_clean();

render_auth_layout([
    'title' => 'Ingreso | Foros PSME',
    'content' => $content,
    'scripts' => ['/assets/js/login.js'],
]);
