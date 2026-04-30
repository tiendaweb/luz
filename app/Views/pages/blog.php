<?php
declare(strict_types=1);
require_once __DIR__ . '/../_session.php';
require_once __DIR__ . '/../layouts/main.php';

ob_start();
?>

<!-- Blog PSME -->
<section class="py-24">
 <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="text-center mb-20">
 <h2 class="text-sm font-extrabold text-brand uppercase tracking-[0.2em] mb-4">Recursos y Reflexión</h2>
 <h3 class="text-5xl font-extrabold text-slate-900 mb-6 tracking-tight">Blog PSME</h3>
 <p class="text-xl text-slate-600 max-w-2xl mx-auto">Lecturas breves para profundizar los ejes trabajados en los foros de salud mental y emocional.</p>
 </div>

 <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
 <article class="rounded-3xl overflow-hidden bg-white border border-slate-100 shadow-lg hover:shadow-xl transition-shadow card-shadow group">
 <div class="h-48 bg-gradient-to-br from-[var(--color-accent)] to-[color:color-mix(in_srgb,var(--color-accent)_80%,black_20%)] relative overflow-hidden">
 <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&q=80" alt="Intervención grupal" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
 </div>
 <div class="p-6 sm:p-8">
 <span class="inline-block px-3 py-1 rounded-full bg-brand text-brand text-xs font-bold uppercase tracking-widest mb-4">Artículo</span>
 <h4 class="text-xl font-bold text-slate-900 mb-3">Intervención grupal en crisis</h4>
 <p class="text-slate-600 mb-6 leading-relaxed">Claves para sostener dispositivos comunitarios en contextos de alta demanda emocional.</p>
 <a href="#" class="inline-flex items-center gap-2 text-brand font-bold hover:text-brand transition-colors">
 Leer más <i class="fa-solid fa-arrow-right"></i>
 </a>
 </div>
 </article>

 <article class="rounded-3xl overflow-hidden bg-white border border-slate-100 shadow-lg hover:shadow-xl transition-shadow card-shadow group">
 <div class="h-48 bg-gradient-to-br from-amber-500 to-amber-700 relative overflow-hidden">
 <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&q=80" alt="Territorio" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
 </div>
 <div class="p-6 sm:p-8">
 <span class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold uppercase tracking-widest mb-4">Análisis</span>
 <h4 class="text-xl font-bold text-slate-900 mb-3">Salud mental y territorio</h4>
 <p class="text-slate-600 mb-6 leading-relaxed">Prácticas situadas para profesionales que trabajan con realidades locales diversas.</p>
 <a href="#" class="inline-flex items-center gap-2 text-amber-600 font-bold hover:text-amber-700 transition-colors">
 Leer más <i class="fa-solid fa-arrow-right"></i>
 </a>
 </div>
 </article>

 <article class="rounded-3xl overflow-hidden bg-white border border-slate-100 shadow-lg hover:shadow-xl transition-shadow card-shadow group">
 <div class="h-48 bg-gradient-to-br from-amber-500 to-amber-700 relative overflow-hidden">
 <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&q=80" alt="Autocuidado" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
 </div>
 <div class="p-6 sm:p-8">
 <span class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold uppercase tracking-widest mb-4">Guía</span>
 <h4 class="text-xl font-bold text-slate-900 mb-3">Estrategias de autocuidado</h4>
 <p class="text-slate-600 mb-6 leading-relaxed">Recomendaciones para prevenir el desgaste en equipos de asistencia psicosocial.</p>
 <a href="#" class="inline-flex items-center gap-2 text-amber-600 font-bold hover:text-amber-700 transition-colors">
 Leer más <i class="fa-solid fa-arrow-right"></i>
 </a>
 </div>
 </article>
 </div>

 <div class="mt-20 text-center">
 <p class="text-slate-600 mb-6">¿Tienes una contribución para el blog?</p>
 <a href="/directora" class="inline-flex items-center gap-2 px-8 py-4 btn-primary font-bold rounded-2xl transition-all shadow-xl shadow-[color:color-mix(in_srgb,var(--color-accent)_35%,transparent)]">
 <i class="fa-solid fa-envelope"></i> Escribir a Luz
 </a>
 </div>
 </div>
</section>

<?php
$content = ob_get_clean();

render_main_layout([
 'title' => 'Blog | Foros PSME',
 'role' => $_viewCurrentRole,
 'content' => $content,
 'scripts' => ['/assets/js/auth.js'],
]);
