<?php
declare(strict_types=1);
require_once __DIR__ . '/../_session.php';
require_once __DIR__ . '/../layouts/main.php';

ob_start();
?>

<!-- Hero -->
<div class="relative bg-white pt-16 pb-32 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row items-center relative z-10">
        <div class="lg:w-1/2 text-center lg:text-left mb-16 lg:mb-0">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-teal-50 text-teal-700 text-sm font-bold mb-6 border border-teal-100">
                <span class="flex h-2 w-2 rounded-full bg-teal-500 animate-pulse"></span>
                Inscripciones Abiertas - Ciclo Mayo 2026
            </div>
            <h1 class="text-5xl lg:text-7xl font-extrabold text-slate-900 mb-8 leading-[1.1]">
                Foros Internacionales de <span class="text-teal-600">Salud Mental.</span>
            </h1>
            <p class="text-xl text-slate-600 mb-10 max-w-xl leading-relaxed">
                Únete a la comunidad de debate, teoría y reflexión grupal más activa de LATAM.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <a href="/inscripcion" class="bg-teal-600 text-white px-10 py-5 rounded-2xl font-bold text-lg hover:bg-teal-700 shadow-xl shadow-teal-200 transition-all flex items-center justify-center gap-3">
                    Inscribirse al Foro <i class="fa-solid fa-arrow-right"></i>
                </a>
                <a href="/foros" class="bg-white border-2 border-slate-200 text-slate-700 px-10 py-5 rounded-2xl font-bold text-lg hover:border-teal-600 hover:text-teal-600 transition-all text-center">
                    Ver Agenda y Ejes
                </a>
            </div>
        </div>
        <div class="lg:w-1/2 relative">
            <div class="absolute -top-20 -right-20 w-96 h-96 bg-teal-100 rounded-full blur-3xl opacity-50"></div>
            <div class="relative rounded-3xl overflow-hidden shadow-2xl border-8 border-white transform rotate-2">
                <img src="https://images.unsplash.com/photo-1543269865-cbf427effbad?auto=format&fit=crop&q=80" alt="Foros Psicosociales" class="w-full h-full object-cover">
            </div>
            <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-2xl shadow-xl flex items-center gap-4 animate-bounce">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-widest">Comunidad</p>
                    <p class="font-extrabold text-slate-800">+10 Países LATAM</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="bg-slate-900 py-12">
    <div class="max-w-7xl mx-auto px-4 flex flex-wrap justify-around gap-8 text-center">
        <div><h3 class="text-4xl font-extrabold text-white">20+</h3><p class="text-teal-400 text-sm font-bold uppercase mt-1">Foros Realizados</p></div>
        <div><h3 class="text-4xl font-extrabold text-white">500+</h3><p class="text-teal-400 text-sm font-bold uppercase mt-1">Profesionales Activos</p></div>
        <div><h3 class="text-4xl font-extrabold text-white">100%</h3><p class="text-teal-400 text-sm font-bold uppercase mt-1">Sincrónico y Virtual</p></div>
        <div><h3 class="text-4xl font-extrabold text-white"><i class="fa-solid fa-certificate"></i></h3><p class="text-teal-400 text-sm font-bold uppercase mt-1">Certificación Incluida</p></div>
    </div>
</div>

<!-- Metodología -->
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-20">
            <h2 class="text-sm font-extrabold text-teal-600 uppercase tracking-[0.2em] mb-4">Nuestra Dinámica</h2>
            <h3 class="text-4xl font-bold text-slate-900">¿Qué sucede en nuestros Foros?</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="group p-8 rounded-3xl hover:bg-teal-50 transition-all duration-500">
                <div class="text-4xl text-teal-600 mb-6"><i class="fa-solid fa-comments"></i></div>
                <h4 class="text-xl font-bold mb-4">Intercambio Dialógico</h4>
                <p class="text-slate-600">No son conferencias planas; son diálogos circulares donde cada voz construye el conocimiento grupal.</p>
            </div>
            <div class="group p-8 rounded-3xl hover:bg-teal-50 transition-all duration-500">
                <div class="text-4xl text-teal-600 mb-6"><i class="fa-solid fa-layer-group"></i></div>
                <h4 class="text-xl font-bold mb-4">Análisis de Caso</h4>
                <p class="text-slate-600">Abordamos realidades sociales actuales desde un marco teórico sólido, ético y contextualizado.</p>
            </div>
            <div class="group p-8 rounded-3xl hover:bg-teal-50 transition-all duration-500">
                <div class="text-4xl text-teal-600 mb-6"><i class="fa-solid fa-certificate"></i></div>
                <h4 class="text-xl font-bold mb-4">Crecimiento Profesional</h4>
                <p class="text-slate-600">Adquiere herramientas prácticas para nutrir tu ejercicio profesional.</p>
            </div>
            <div class="group p-8 rounded-3xl hover:bg-teal-50 transition-all duration-500">
                <div class="text-4xl text-teal-600 mb-6"><i class="fa-solid fa-heart-pulse"></i></div>
                <h4 class="text-xl font-bold mb-4">Soporte Colectivo</h4>
                <p class="text-slate-600">Un espacio seguro diseñado también para el cuidado de quienes cuidan a otros.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonios -->
<section class="py-24 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-teal-900 rounded-[3rem] p-12 md:p-20 text-white flex flex-col md:flex-row items-center gap-16 shadow-2xl overflow-hidden relative">
            <div class="absolute top-0 right-0 w-64 h-64 bg-teal-800 rounded-full blur-3xl -mr-32 -mt-32 opacity-40"></div>
            <div class="md:w-1/3">
                <div class="w-full aspect-square rounded-3xl overflow-hidden shadow-2xl border-4 border-teal-700">
                    <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80" alt="Profesional Testimonio" class="w-full h-full object-cover">
                </div>
            </div>
            <div class="md:w-2/3">
                <div class="text-teal-400 text-5xl mb-6"><i class="fa-solid fa-quote-left"></i></div>
                <p class="text-2xl md:text-3xl font-medium leading-relaxed mb-10 italic">
                    "Participar en el Foro PSME cambió por completo mi forma de abordar ciertas patologías."
                </p>
                <div>
                    <h4 class="text-xl font-bold">Dra. Claudia Vaca</h4>
                    <p class="text-teal-400 uppercase tracking-widest text-sm font-bold mt-1">Psicóloga Clínica - Bogotá, Colombia</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQs -->
<section class="py-24 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h3 class="text-3xl font-bold text-center text-slate-900 mb-16">Preguntas sobre Inscripción y Foros</h3>
        <div class="space-y-4">
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <button onclick="toggleFaq(this)" class="w-full text-left p-6 font-bold flex justify-between items-center bg-white hover:bg-slate-50">
                    <span>¿Los foros son en vivo o grabados?</span>
                    <i class="fa-solid fa-chevron-down text-slate-400"></i>
                </button>
                <div class="hidden p-6 text-slate-600 border-t border-slate-100">Son 100% sincrónicos (en vivo) a través de Meet/Zoom. Esto garantiza la interacción real y el debate.</div>
            </div>
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <button onclick="toggleFaq(this)" class="w-full text-left p-6 font-bold flex justify-between items-center bg-white hover:bg-slate-50">
                    <span>¿Hay descuentos si nos inscribimos en grupo?</span>
                    <i class="fa-solid fa-chevron-down text-slate-400"></i>
                </button>
                <div class="hidden p-6 text-slate-600 border-t border-slate-100">¡Sí! Si se inscriben 4 o más personas de una misma institución, obtienen un 20% de descuento automático.</div>
            </div>
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <button onclick="toggleFaq(this)" class="w-full text-left p-6 font-bold flex justify-between items-center bg-white hover:bg-slate-50">
                    <span>¿Cómo y cuándo recibo el certificado?</span>
                    <i class="fa-solid fa-chevron-down text-slate-400"></i>
                </button>
                <div class="hidden p-6 text-slate-600 border-t border-slate-100">Se envía certificación digital vía email a quienes cumplan con el 75% de asistencia activa.</div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../partials/modals/register.php';

render_main_layout([
    'title' => 'Foros LATAM PSME',
    'role' => $_viewCurrentRole,
    'content' => $content,
    'scripts' => ['/assets/js/navigation.js', '/assets/js/auth.js', '/assets/js/registrations.js']
]);
