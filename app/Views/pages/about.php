<?php
declare(strict_types=1);
require_once __DIR__ . '/../_session.php';
require_once __DIR__ . '/../layouts/main.php';
require_once __DIR__ . '/../../Support/ContentBlocks.php';

$settings = $_viewSiteSettings ?? [];
$directorName = (string)($settings['director_name'] ?? 'María Luz Genovese');
$directorTitle = (string)($settings['director_title'] ?? 'Psicóloga Social especializada en Salud Mental y Emocional (SmE)');
$directorLocation = (string)($settings['director_location'] ?? 'Buenos Aires, Argentina');
$publicPhone = (string)($settings['public_phone_primary'] ?? '+54 9 11 4000-0000');
$publicEmail = (string)($settings['public_email_primary'] ?? 'contacto@forospsme.com');
$contactCtaText = (string)($settings['contact_cta_text'] ?? 'Escribinos para coordinar entrevistas, consultas o información de próximos foros.');

ob_start();
?>

<!-- La Directora - Luz Genovese -->
<section class="py-24 bg-[#FDFBF7]"> <!-- Fondo principal cálido muy claro -->
    <!-- Encabezado y Perfil -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-20">
            <h2 class="text-5xl font-extrabold text-[#5C4A3D] mb-6 tracking-tight"><?= htmlspecialchars($directorName, ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-xl text-[#826F60] max-w-2xl mx-auto"><?= htmlspecialchars($directorTitle, ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center mb-20">
            <div>
                <div class="rounded-3xl overflow-hidden shadow-2xl border-8 border-[#F4EDE4]">
                    <!-- Mantenemos la imagen original -->
                    <img src="/uploads/marialuz.jpg" alt="Luz Genovese" class="w-full h-full object-cover">
                </div>
            </div>
            <div>
                <h3 class="text-3xl font-bold text-[#5C4A3D] mb-6"><span data-content-key="about_title" data-content-context="about"><?= htmlspecialchars(content_block_value('about','about_title','Sobre ' . $directorName), ENT_QUOTES, 'UTF-8') ?></span></h3>
                <div class="prose prose-slate space-y-4 text-[#826F60]">
                    <p class="text-lg leading-relaxed">
                        Psicóloga Social con especialización en contextos comunitarios y problemáticas de salud mental y emocional. Ha trabajado durante más de 15 años en la construcción de espacios de reflexión grupal y fortalecimiento psicosocial en Argentina y América Latina.
                    </p>
                    <p class="text-lg leading-relaxed">
                        Su abordaje combina teoría psicosocial, análisis de caso y dispositivos dialógicos para acompañar a profesionales en su desarrollo teórico y práctica cotidiana.
                    </p>
                    
                    <!-- Caja de Contacto Directo - Tonos Marrón Pastel -->
                    <div class="bg-[#F4EDE4] border-l-4 p-6 rounded-xl mt-8 border-[#C2A892]">
                        <p class="font-bold text-[#5C4A3D] mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-phone text-[#C2A892]"></i> Contacto
                        </p>
                        <ul class="space-y-2 text-[#6B5A4E]">
                            <li><strong>WhatsApp:</strong> <?= htmlspecialchars($publicPhone, ENT_QUOTES, 'UTF-8') ?></li>
                            <li><strong>Email:</strong> <?= htmlspecialchars($publicEmail, ENT_QUOTES, 'UTF-8') ?></li>
                            <li><strong>Ubicación:</strong> <?= htmlspecialchars($directorLocation, ENT_QUOTES, 'UTF-8') ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NUEVA SECCIÓN 1: Enfoque y Metodología -->
    <div class="bg-[#F4EDE4] py-16 my-20 border-y border-[#E8DCC8]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    <i class="fa-solid fa-seedling text-4xl text-[#A88C75] mb-6"></i>
                    <h3 class="text-2xl md:text-3xl font-bold text-[#5C4A3D] mb-6">La salud mental es una construcción colectiva</h3>
                    <p class="text-lg text-[#6B5A4E] leading-relaxed">
                        Entendemos al sujeto como un ser emergente de sus relaciones sociales. Nuestro enfoque psicosocial busca promover el bienestar a través del fortalecimiento de los vínculos, la fluidez en la comunicación y la resolución dialógica y participativa de los conflictos en la vida cotidiana.
                    </p>
                </div>
                <div class="rounded-3xl overflow-hidden shadow-xl border-8 border-[#FDFBF7] transform lg:rotate-2 hover:rotate-0 transition-transform duration-500">
                    <img src="/uploads/marialuz2.jpg" alt="Luz Genovese - Metodología" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </div>

    <!-- NUEVA SECCIÓN 2: Áreas de Especialización -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
        <h3 class="text-3xl font-bold text-[#5C4A3D] text-center mb-12">Áreas de Intervención</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Tarjeta 1 -->
            <div class="bg-white rounded-2xl p-8 shadow-lg border border-[#E8DCC8] hover:-translate-y-1 transition-transform duration-300">
                <div class="w-14 h-14 bg-[#FDFBF7] border border-[#E8DCC8] rounded-2xl flex items-center justify-center mb-6 text-[#C2A892] text-2xl shadow-sm">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h4 class="text-xl font-bold text-[#5C4A3D] mb-3">Coordinación Grupal</h4>
                <p class="text-[#826F60] leading-relaxed">Facilitación de grupos operativos y espacios de reflexión para abordar dinámicas vinculares, superar roles estancados y potenciar la tarea grupal.</p>
            </div>
            <!-- Tarjeta 2 -->
            <div class="bg-white rounded-2xl p-8 shadow-lg border border-[#E8DCC8] hover:-translate-y-1 transition-transform duration-300">
                <div class="w-14 h-14 bg-[#FDFBF7] border border-[#E8DCC8] rounded-2xl flex items-center justify-center mb-6 text-[#C2A892] text-2xl shadow-sm">
                    <i class="fa-solid fa-hands-holding-circle"></i>
                </div>
                <h4 class="text-xl font-bold text-[#5C4A3D] mb-3">Salud Comunitaria</h4>
                <p class="text-[#826F60] leading-relaxed">Diseño e implementación de dispositivos psicosociales orientados a la prevención y promoción de la salud emocional en contextos vulnerables o institucionales.</p>
            </div>
            <!-- Tarjeta 3 -->
            <div class="bg-white rounded-2xl p-8 shadow-lg border border-[#E8DCC8] hover:-translate-y-1 transition-transform duration-300">
                <div class="w-14 h-14 bg-[#FDFBF7] border border-[#E8DCC8] rounded-2xl flex items-center justify-center mb-6 text-[#C2A892] text-2xl shadow-sm">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <h4 class="text-xl font-bold text-[#5C4A3D] mb-3">Asesoramiento Profesional</h4>
                <p class="text-[#826F60] leading-relaxed">Supervisión, acompañamiento y capacitación a equipos de trabajo, educadores y otros profesionales del ámbito social y de la salud.</p>
            </div>
        </div>
    </div>

    <!-- NUEVA SECCIÓN 3: Certificaciones y Diplomas -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
        <div class="text-center mb-12">
            <h3 class="text-3xl font-bold text-[#5C4A3D] mb-4">Trayectoria y Acreditaciones</h3>
            <p class="text-lg text-[#826F60] max-w-2xl mx-auto">Un recorrido de formación continua y compromiso con la excelencia profesional en el ámbito psicosocial.</p>
        </div>
        
        <!-- Carrusel/Grilla horizontal nativa -->
        <div class="flex overflow-x-auto pb-8 gap-6 snap-x snap-mandatory custom-scrollbar">
            <!-- Certificado 1 -->
            <div class="snap-center shrink-0 w-72 md:w-80 bg-white p-3 rounded-2xl shadow-md border border-[#E8DCC8] hover:shadow-lg transition-all group">
                <div class="overflow-hidden rounded-xl mb-4 border border-[#F4EDE4]">
                    <!-- Imagen Placeholder usando la paleta de colores -->
                    <img src="https://placehold.co/600x400/F4EDE4/5C4A3D?text=Certificado+1" alt="Certificado" class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <h4 class="text-center font-bold text-[#5C4A3D] text-sm">Especialización en Salud Mental</h4>
            </div>
            
            <!-- Certificado 2 -->
            <div class="snap-center shrink-0 w-72 md:w-80 bg-white p-3 rounded-2xl shadow-md border border-[#E8DCC8] hover:shadow-lg transition-all group">
                <div class="overflow-hidden rounded-xl mb-4 border border-[#F4EDE4]">
                    <img src="https://placehold.co/600x400/F4EDE4/5C4A3D?text=Certificado+2" alt="Certificado" class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <h4 class="text-center font-bold text-[#5C4A3D] text-sm">Diplomatura en Psicología Social</h4>
            </div>
            
            <!-- Certificado 3 -->
            <div class="snap-center shrink-0 w-72 md:w-80 bg-white p-3 rounded-2xl shadow-md border border-[#E8DCC8] hover:shadow-lg transition-all group">
                <div class="overflow-hidden rounded-xl mb-4 border border-[#F4EDE4]">
                    <img src="https://placehold.co/600x400/F4EDE4/5C4A3D?text=Certificado+3" alt="Certificado" class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <h4 class="text-center font-bold text-[#5C4A3D] text-sm">Posgrado en Vínculos</h4>
            </div>
            
            <!-- Certificado 4 -->
            <div class="snap-center shrink-0 w-72 md:w-80 bg-white p-3 rounded-2xl shadow-md border border-[#E8DCC8] hover:shadow-lg transition-all group">
                <div class="overflow-hidden rounded-xl mb-4 border border-[#F4EDE4]">
                    <img src="https://placehold.co/600x400/F4EDE4/5C4A3D?text=Certificado+4" alt="Certificado" class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <h4 class="text-center font-bold text-[#5C4A3D] text-sm">Seminario Internacional</h4>
            </div>
            
            <!-- Certificado 5 -->
            <div class="snap-center shrink-0 w-72 md:w-80 bg-white p-3 rounded-2xl shadow-md border border-[#E8DCC8] hover:shadow-lg transition-all group">
                <div class="overflow-hidden rounded-xl mb-4 border border-[#F4EDE4]">
                    <img src="https://placehold.co/600x400/F4EDE4/5C4A3D?text=Certificado+5" alt="Certificado" class="w-full h-auto object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <h4 class="text-center font-bold text-[#5C4A3D] text-sm">Congreso Latinoamericano</h4>
            </div>
        </div>
        
        <!-- Estilos para una barra de scroll elegante -->
        <style>
            .custom-scrollbar::-webkit-scrollbar {
                height: 8px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
                background: #FDFBF7;
                border-radius: 10px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background-color: #D6C7B8;
                border-radius: 10px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background-color: #C2A892;
            }
        </style>
    </div>

    <!-- Formulario de Consulta -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl p-10 shadow-xl border border-[#E8DCC8] max-w-2xl mx-auto">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#F4EDE4] text-[#C2A892] mb-4">
                    <i class="fa-regular fa-envelope text-2xl"></i>
                </div>
                <h4 class="text-2xl font-bold text-[#5C4A3D] mb-2">Solicitar Consulta Profesional Individual</h4>
                <p class="text-[#826F60]"><?= htmlspecialchars($contactCtaText, ENT_QUOTES, 'UTF-8') ?></p>
            </div>

            <!-- El ID form y los inputs se mantienen idénticos para no romper JS -->
            <form id="consultaForm" class="space-y-6" novalidate>
                <div>
                    <label for="consultaNombre" class="block text-sm font-bold text-[#6B5A4E] mb-2">Nombre y Apellido *</label>
                    <input id="consultaNombre" name="nombre" type="text" required placeholder="Tu nombre" class="w-full rounded-2xl border border-[#D6C7B8] bg-[#FCFAF8] px-4 py-3 text-[#5C4A3D] placeholder-[#A88C75] focus:border-[#C2A892] focus:outline-none focus:ring-2 focus:ring-[#E8DCC8] transition-colors">
                </div>
                <div>
                    <label for="consultaWhatsapp" class="block text-sm font-bold text-[#6B5A4E] mb-2">WhatsApp *</label>
                    <input id="consultaWhatsapp" name="whatsapp" type="tel" required placeholder="+54 9 11 xxxx-xxxx" class="w-full rounded-2xl border border-[#D6C7B8] bg-[#FCFAF8] px-4 py-3 text-[#5C4A3D] placeholder-[#A88C75] focus:border-[#C2A892] focus:outline-none focus:ring-2 focus:ring-[#E8DCC8] transition-colors">
                </div>
                <div>
                    <label for="consultaMotivo" class="block text-sm font-bold text-[#6B5A4E] mb-2">Motivo de Consulta *</label>
                    <textarea id="consultaMotivo" name="motivo" required placeholder="Cuéntanos brevemente qué te gustaría consultar..." rows="5" class="w-full rounded-2xl border border-[#D6C7B8] bg-[#FCFAF8] px-4 py-3 text-[#5C4A3D] placeholder-[#A88C75] focus:border-[#C2A892] focus:outline-none focus:ring-2 focus:ring-[#E8DCC8] transition-colors"></textarea>
                </div>
                <button type="submit" class="w-full bg-[#C2A892] hover:bg-[#B0957D] text-white font-extrabold py-4 rounded-2xl transition-all shadow-lg shadow-[#D6C7B8]">
                    Solicitar Entrevista Individual
                </button>
            </form>
            <div id="consultaAlert" class="hidden mt-4 rounded-2xl border border-[#C2A892] bg-[#F4EDE4] p-4 text-sm font-semibold text-[#5C4A3D]"></div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();

render_main_layout([
    'title' => 'La Directora | Foros PSME',
    'role' => $_viewCurrentRole,
    'content' => $content,
    'scripts' => ['/assets/js/auth.js'],
]);