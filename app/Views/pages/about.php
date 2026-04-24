<?php
declare(strict_types=1);
require_once __DIR__ . '/../_session.php';
require_once __DIR__ . '/../layouts/main.php';

ob_start();
?>

<!-- La Directora - Luz Genovese -->
<section class="py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-20">
            <h2 class="text-5xl font-extrabold text-slate-900 mb-6 tracking-tight">Luz Genovese</h2>
            <p class="text-xl text-slate-600 max-w-2xl mx-auto">Psicóloga Social especializada en Salud Mental y Emocional (SmE)</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center mb-20">
            <div>
                <div class="rounded-3xl overflow-hidden shadow-2xl border-8 border-white">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80" alt="Luz Genovese" class="w-full h-full object-cover">
                </div>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-slate-900 mb-6">Sobre María Luz Genovese</h3>
                <div class="prose prose-slate space-y-4 text-slate-600">
                    <p class="text-lg leading-relaxed">
                        Psicóloga Social con especialización en contextos comunitarios y problemáticas de salud mental y emocional. Ha trabajado durante más de 15 años en la construcción de espacios de reflexión grupal y fortalecimiento psicosocial en Argentina y América Latina.
                    </p>
                    <p class="text-lg leading-relaxed">
                        Su abordaje combina teoría psicosocial, análisis de caso y dispositivos dialógicos para acompañar a profesionales en su desarrollo teórico y práctica cotidiana.
                    </p>
                    <div class="bg-teal-50 border-l-4 border-teal-600 p-6 rounded-lg mt-8">
                        <p class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-phone text-teal-600"></i> Contacto
                        </p>
                        <ul class="space-y-2 text-slate-700">
                            <li><strong>WhatsApp:</strong> +54 9 11 xxxx-xxxx</li>
                            <li><strong>Email:</strong> luz@forospsme.com</li>
                            <li><strong>Ubicación:</strong> Buenos Aires, Argentina</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-10 shadow-xl border border-slate-100 max-w-2xl mx-auto">
            <h4 class="text-2xl font-bold text-slate-900 mb-8 text-center">Solicitar Consulta Profesional Individual</h4>
            <form id="consultaForm" class="space-y-6" novalidate>
                <div>
                    <label for="consultaNombre" class="block text-sm font-bold text-slate-700 mb-2">Nombre y Apellido *</label>
                    <input id="consultaNombre" name="nombre" type="text" required placeholder="Tu nombre" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                </div>
                <div>
                    <label for="consultaWhatsapp" class="block text-sm font-bold text-slate-700 mb-2">WhatsApp *</label>
                    <input id="consultaWhatsapp" name="whatsapp" type="tel" required placeholder="+54 9 11 xxxx-xxxx" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                </div>
                <div>
                    <label for="consultaMotivo" class="block text-sm font-bold text-slate-700 mb-2">Motivo de Consulta *</label>
                    <textarea id="consultaMotivo" name="motivo" required placeholder="Cuéntanos brevemente qué te gustaría consultar..." rows="5" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100"></textarea>
                </div>
                <button type="submit" class="w-full bg-teal-600 text-white font-extrabold py-4 rounded-2xl hover:bg-teal-700 transition-all shadow-xl shadow-teal-100">
                    Solicitar Entrevista Individual
                </button>
            </form>
            <div id="consultaAlert" class="hidden mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800"></div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();

render_main_layout([
    'title'   => 'La Directora | Foros PSME',
    'role'    => $_viewCurrentRole,
    'content' => $content,
    'scripts' => ['/assets/js/auth.js'],
]);
