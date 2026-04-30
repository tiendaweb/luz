<?php
declare(strict_types=1);
require_once __DIR__ . '/../_session.php';
require_once __DIR__ . '/../layouts/main.php';

ob_start();
?>

<section class="py-24 animate-fadeIn bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-20">
            <h2 class="text-5xl font-extrabold text-stone-900 mb-6 tracking-tight">Foro LATAM 2026</h2>
            <p class="text-xl text-stone-600 max-w-2xl mx-auto">Agenda viva desde API: próximos encuentros, plataforma y horarios por zona.</p>
        </div>

        <div id="forumsApiAlert" class="hidden mb-8 rounded-2xl border border-stone-200 bg-stone-50 p-4 text-sm font-semibold text-stone-800"></div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 mb-24">
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white rounded-3xl p-10 shadow-xl border border-stone-100">
                    <h3 class="text-2xl font-bold mb-2 text-stone-800">Próximo foro publicado</h3>
                    <p class="text-sm text-stone-500 mb-6">Referencia temporal del servidor en UTC. La zona del foro se informa en cada registro.</p>
                    
                    <div id="nextForumCard" class="rounded-2xl border border-stone-200 bg-stone-50 p-6">
                        <p class="text-sm text-stone-500">Cargando próximo foro...</p>
                    </div>

                    <div class="mt-6 rounded-2xl bg-stone-800 text-white p-6 border border-stone-700">
                        <p class="text-xs uppercase tracking-widest text-stone-300 font-bold mb-4">Cuenta regresiva</p>
                        <div id="forumCountdown" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="rounded-xl bg-stone-700/50 p-4 text-center">
                                <p class="text-3xl font-extrabold" data-unit="days">00</p>
                                <p class="text-xs text-stone-400">Días</p>
                            </div>
                            <div class="rounded-xl bg-stone-700/50 p-4 text-center">
                                <p class="text-3xl font-extrabold" data-unit="hours">00</p>
                                <p class="text-xs text-stone-400">Horas</p>
                            </div>
                            <div class="rounded-xl bg-stone-700/50 p-4 text-center">
                                <p class="text-3xl font-extrabold" data-unit="minutes">00</p>
                                <p class="text-xs text-stone-400">Min</p>
                            </div>
                            <div class="rounded-xl bg-stone-700/50 p-4 text-center">
                                <p class="text-3xl font-extrabold" data-unit="seconds">00</p>
                                <p class="text-xs text-stone-400">Seg</p>
                            </div>
                        </div>
                        <p id="forumCountdownStatus" class="mt-4 text-sm text-stone-300">Esperando fecha...</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-10 shadow-xl border border-stone-100">
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                        <h3 class="text-2xl font-bold text-stone-800">Agenda publicada</h3>
                        <div class="flex gap-2">
                            <button id="forumsPrevPage" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 font-semibold text-stone-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Anterior</button>
                            <button id="forumsNextPage" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-stone-200 font-semibold text-stone-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Siguiente</button>
                        </div>
                    </div>
                    <div id="forumsList" class="grid grid-cols-1 md:grid-cols-2 gap-5"></div>
                    <p id="forumsPaginationMeta" class="mt-5 text-sm text-stone-500"></p>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-stone-800 rounded-3xl p-8 text-white sticky top-32 shadow-2xl">
                    <h4 class="text-2xl font-bold mb-6">Inversión y Cupos</h4>
                    <div class="space-y-3 mb-8">
                        <div class="flex justify-between">
                            <span class="text-stone-300">Profesionales</span>
                            <span class="font-bold text-stone-100">$35.000</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-stone-300">Estudiantes</span>
                            <span class="font-bold text-stone-100">$20.000</span>
                        </div>
                        <div class="flex justify-between border-t border-stone-600 pt-3 mt-3">
                            <span class="text-stone-300">Promo grupal</span>
                            <span class="font-bold text-stone-200">20% OFF</span>
                        </div>
                    </div>
                    <a href="/inscripcion" class="w-full bg-stone-500 hover:bg-stone-600 text-white font-extrabold py-4 rounded-2xl transition-all shadow-xl shadow-stone-900/20 flex items-center justify-center">
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