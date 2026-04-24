<?php
declare(strict_types=1);
require_once __DIR__ . '/../_session.php';
require_once __DIR__ . '/../layouts/main.php';

ob_start();
?>

<!-- Foros y Agenda -->
<section class="py-24 animate-fadeIn">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-20">
 <h2 class="text-5xl font-extrabold text-slate-900 mb-6 tracking-tight">Foro LATAM 2026</h2>
 <p class="text-xl text-slate-600 max-w-2xl mx-auto">Agenda viva desde API: próximos encuentros, plataforma y horarios por zona.</p>
 </div>

 <div id="forumsApiAlert" class="hidden mb-8 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800"></div>

 <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 mb-24">
 <div class="lg:col-span-2 space-y-8">
 <div class="bg-white rounded-3xl p-10 shadow-xl border border-slate-100">
 <h3 class="text-2xl font-bold mb-2">Próximo foro publicado</h3>
 <p class="text-sm text-slate-500 mb-6">Referencia temporal del servidor en UTC. La zona del foro se informa en cada registro.</p>
 <div id="nextForumCard" class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
 <p class="text-sm text-slate-500">Cargando próximo foro...</p>
 </div>
 <div class="mt-6 rounded-2xl bg-slate-900 text-white p-6 border border-slate-800">
 <p class="text-xs uppercase tracking-widest text-[var(--color-accent)] font-bold mb-4">Cuenta regresiva</p>
 <div id="forumCountdown" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
 <div class="rounded-xl bg-slate-800 p-4 text-center"><p class="text-3xl font-extrabold" data-unit="days">00</p><p class="text-xs text-slate-400">Días</p></div>
 <div class="rounded-xl bg-slate-800 p-4 text-center"><p class="text-3xl font-extrabold" data-unit="hours">00</p><p class="text-xs text-slate-400">Horas</p></div>
 <div class="rounded-xl bg-slate-800 p-4 text-center"><p class="text-3xl font-extrabold" data-unit="minutes">00</p><p class="text-xs text-slate-400">Min</p></div>
 <div class="rounded-xl bg-slate-800 p-4 text-center"><p class="text-3xl font-extrabold" data-unit="seconds">00</p><p class="text-xs text-slate-400">Seg</p></div>
 </div>
 <p id="forumCountdownStatus" class="mt-4 text-sm text-slate-300">Esperando fecha...</p>
 </div>
 </div>
 <div class="bg-white rounded-3xl p-10 shadow-xl border border-slate-100">
 <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
 <h3 class="text-2xl font-bold">Agenda publicada</h3>
 <div class="flex gap-2">
 <button id="forumsPrevPage" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 font-semibold text-slate-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Anterior</button>
 <button id="forumsNextPage" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 font-semibold text-slate-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Siguiente</button>
 </div>
 </div>
 <div id="forumsList" class="grid grid-cols-1 md:grid-cols-2 gap-5"></div>
 <p id="forumsPaginationMeta" class="mt-5 text-sm text-slate-500"></p>
 </div>
 </div>
 <div class="lg:col-span-1">
 <div class="bg-slate-900 rounded-3xl p-8 text-white sticky top-32">
 <h4 class="text-2xl font-bold mb-6">Inversión y Cupos</h4>
 <div class="space-y-3 mb-8">
 <div class="flex justify-between">
 <span class="text-slate-300">Profesionales</span>
 <span class="font-bold text-[var(--color-accent)]">$35.000</span>
 </div>
 <div class="flex justify-between">
 <span class="text-slate-300">Estudiantes</span>
 <span class="font-bold text-[var(--color-accent)]">$20.000</span>
 </div>
 <div class="flex justify-between border-t border-slate-700 pt-3 mt-3">
 <span class="text-slate-300">Promo grupal</span>
 <span class="font-bold text-[var(--color-accent)]">20% OFF</span>
 </div>
 </div>
 <a href="/inscripcion" class="w-full btn-primary font-extrabold py-4 rounded-2xl transition-all shadow-xl shadow-[color:color-mix(in_srgb,var(--color-accent)_35%,transparent)] flex items-center justify-center">
 Inscribirse Ahora
 </a>
 </div>
 </div>
 </div>
 </div>
</section>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/modals/register.php';

render_main_layout([
 'title' => 'Foros y Agenda | Foros PSME',
 'role' => $_viewCurrentRole,
 'content' => $content,
 'scripts' => ['/assets/js/auth.js', '/assets/js/forums.js', '/assets/js/registrations.js'],
]);
