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

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
          <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">
            <i class="fa-solid fa-flask mr-1"></i>Probar como…
          </p>
          <div class="grid gap-2 sm:grid-cols-3">
            <button type="button" data-demo-role="admin"
              class="rounded-xl border border-teal-200 bg-white px-3 py-3 text-sm font-semibold
                     text-teal-700 hover:bg-teal-50 hover:border-teal-400 transition text-left">
              <i class="fa-solid fa-shield-halved block mb-1 text-teal-600"></i>
              Admin (Luz)
            </button>
            <button type="button" data-demo-role="associate"
              class="rounded-xl border border-violet-200 bg-white px-3 py-3 text-sm font-semibold
                     text-violet-700 hover:bg-violet-50 hover:border-violet-400 transition text-left">
              <i class="fa-solid fa-users block mb-1 text-violet-600"></i>
              Asociado
            </button>
            <button type="button" data-demo-role="user"
              class="rounded-xl border border-blue-200 bg-white px-3 py-3 text-sm font-semibold
                     text-blue-700 hover:bg-blue-50 hover:border-blue-400 transition text-left">
              <i class="fa-solid fa-user block mb-1 text-blue-600"></i>
              Usuario
            </button>
          </div>
        </div>

        <button id="loginSubmitBtn" type="submit"
          class="w-full rounded-2xl bg-teal-600 text-white px-5 py-3 font-bold hover:bg-teal-700 transition focus:outline-none focus:ring-2 focus:ring-teal-200 focus:ring-offset-2">
          Ingresar
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-slate-500">¿Volver al sitio? <a class="font-bold text-teal-700 hover:text-teal-600" href="/">Ir al inicio</a></p>
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
