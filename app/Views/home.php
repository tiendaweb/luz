<?php

declare(strict_types=1);
?><!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foros LATAM PSME | Salud Mental y Emocional</title>
    <script>
        tailwind = window.tailwind || {};
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        teal: {
                            50: '#f7f2eb',
                            100: '#ede3d7',
                            200: '#dcc8b0',
                            300: '#c9aa86',
                            400: '#b58b63',
                            500: '#a06f48',
                            600: '#86583a',
                            700: '#6a4530',
                            800: '#503624',
                            900: '#3b281b'
                        }
                    }
                }
            }
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .gradient-teal { background: linear-gradient(135deg, #a06f48 0%, #6a4530 100%); }
        .card-shadow { transition: all 0.3s ease; box-shadow: 0 10px 30px -15px rgba(0,0,0,0.1); }
        .card-shadow:hover { transform: translateY(-5px); box-shadow: 0 20px 40px -15px rgba(0,0,0,0.15); }
        .role-badge { display: none; }
        [data-active-role="admin"] .admin-only { display: block !important; }
        [data-active-role="associate"] .associate-only { display: block !important; }
        [data-active-role="user"] .user-only { display: block !important; }
        [data-active-role="guest"] .guest-only { display: block !important; }
        .view-section { display: none; }
        .view-section.active { display: block; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900" data-active-role="guest">
<?php require __DIR__ . '/partials/header.php'; ?>
    <!-- MAIN SECTIONS -->
    <div id="main-content" class="pt-20">
        
        <!-- HOME SECTION -->
        <section id="view-home" class="view-section active animate-fadeIn">
            <!-- Hero -->
            <div class="relative bg-white pt-16 pb-32 overflow-hidden">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row items-center relative z-10">
                    <div class="lg:w-1/2 text-center lg:text-left mb-16 lg:mb-0">
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-50 text-amber-800 text-sm font-bold mb-6 border border-amber-100">
                            <span class="flex h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Inscripciones Abiertas - Ciclo Mayo 2026
                        </div>
                        <h1 class="text-5xl lg:text-7xl font-extrabold text-slate-900 mb-8 leading-[1.1]">
                            Foros Internacionales de <span class="text-amber-700">Salud Mental.</span>
                        </h1>
                        <p class="text-xl text-slate-600 mb-10 max-w-xl leading-relaxed">
                            Únete a la comunidad de debate, teoría y reflexión grupal más activa de LATAM. Espacios sincrónicos diseñados para profesionales y estudiantes, con la coordinación experta de Maria Luz Genovese.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <button onclick="openModal('registerModal')" class="bg-amber-700 text-white px-10 py-5 rounded-2xl font-bold text-lg hover:bg-amber-800 shadow-xl shadow-amber-200 transition-all flex items-center justify-center gap-3">
                                Inscribirse al Foro <i class="fa-solid fa-arrow-right"></i>
                            </button>
                            <button onclick="showView('forums')" class="bg-white border-2 border-slate-200 text-slate-700 px-10 py-5 rounded-2xl font-bold text-lg hover:border-amber-700 hover:text-amber-700 transition-all">
                                Ver Agenda y Ejes
                            </button>
                        </div>
                    </div>
                    <div class="lg:w-1/2 relative">
                        <div class="absolute -top-20 -right-20 w-96 h-96 bg-amber-100 rounded-full blur-3xl opacity-50"></div>
                        <div class="relative rounded-3xl overflow-hidden shadow-2xl border-8 border-white transform rotate-2">
                            <!-- Cambio a imagen que refleje un entorno de conferencia/foro/grupo -->
                            <img src="https://images.unsplash.com/photo-1543269865-cbf427effbad?auto=format&fit=crop&q=80" alt="Foros Psicosociales" class="w-full h-full object-cover">
                        </div>
                        <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-2xl shadow-xl flex items-center gap-4 animate-bounce">
                            <div class="w-12 h-12 bg-amber-600 rounded-full flex items-center justify-center text-white">
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

            <!-- Stats & Network -->
            <div class="bg-slate-900 py-12">
                <div class="max-w-7xl mx-auto px-4 flex flex-wrap justify-around gap-8 text-center">
                    <div><h3 class="text-4xl font-extrabold text-white">20+</h3><p class="text-amber-400 text-sm font-bold uppercase mt-1">Foros Realizados</p></div>
                    <div><h3 class="text-4xl font-extrabold text-white">500+</h3><p class="text-amber-400 text-sm font-bold uppercase mt-1">Profesionales Activos</p></div>
                    <div><h3 class="text-4xl font-extrabold text-white">100%</h3><p class="text-amber-400 text-sm font-bold uppercase mt-1">Sincrónico y Virtual</p></div>
                    <div><h3 class="text-4xl font-extrabold text-white"><i class="fa-solid fa-certificate"></i></h3><p class="text-amber-400 text-sm font-bold uppercase mt-1">Certificación Incluida</p></div>
                </div>
            </div>

            <!-- Ampliación: Metodología -->
            <section class="py-24 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-20">
                        <h2 class="text-sm font-extrabold text-amber-700 uppercase tracking-[0.2em] mb-4">Nuestra Dinámica</h2>
                        <h3 class="text-4xl font-bold text-slate-900">¿Qué sucede en nuestros Foros?</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="group p-8 rounded-3xl hover:bg-amber-50 transition-all duration-500">
                            <div class="text-4xl text-amber-700 mb-6"><i class="fa-solid fa-comments"></i></div>
                            <h4 class="text-xl font-bold mb-4">Intercambio Dialógico</h4>
                            <p class="text-slate-600">No son conferencias planas; son diálogos circulares donde cada voz construye el conocimiento grupal.</p>
                        </div>
                        <div class="group p-8 rounded-3xl hover:bg-amber-50 transition-all duration-500">
                            <div class="text-4xl text-amber-700 mb-6"><i class="fa-solid fa-layer-group"></i></div>
                            <h4 class="text-xl font-bold mb-4">Análisis de Caso</h4>
                            <p class="text-slate-600">Abordamos realidades sociales actuales desde un marco teórico sólido, ético y contextualizado.</p>
                        </div>
                        <div class="group p-8 rounded-3xl hover:bg-amber-50 transition-all duration-500">
                            <div class="text-4xl text-amber-700 mb-6"><i class="fa-solid fa-certificate"></i></div>
                            <h4 class="text-xl font-bold mb-4">Crecimiento Profesional</h4>
                            <p class="text-slate-600">Adquiere herramientas prácticas y avaladas para nutrir tu ejercicio profesional en salud mental.</p>
                        </div>
                        <div class="group p-8 rounded-3xl hover:bg-amber-50 transition-all duration-500">
                            <div class="text-4xl text-amber-700 mb-6"><i class="fa-solid fa-heart-pulse"></i></div>
                            <h4 class="text-xl font-bold mb-4">Soporte Colectivo</h4>
                            <p class="text-slate-600">Un espacio seguro diseñado también para el cuidado de quienes cuidan a otros.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Testimonios -->
            <section class="py-24 bg-slate-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="bg-amber-900 rounded-[3rem] p-12 md:p-20 text-white flex flex-col md:flex-row items-center gap-16 shadow-2xl overflow-hidden relative">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-amber-800 rounded-full blur-3xl -mr-32 -mt-32 opacity-40"></div>
                        <div class="md:w-1/3">
                            <div class="w-full aspect-square rounded-3xl overflow-hidden shadow-2xl border-4 border-amber-800">
                                <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80" alt="Profesional Testimonio" class="w-full h-full object-cover">
                            </div>
                        </div>
                        <div class="md:w-2/3">
                            <div class="text-amber-400 text-5xl mb-6"><i class="fa-solid fa-quote-left"></i></div>
                            <p class="text-2xl md:text-3xl font-medium leading-relaxed mb-10 italic">
                                "Participar en el Foro PSME cambió por completo mi forma de abordar ciertas patologías. El espacio de debate guiado por Luz permite que la teoría tome forma en acciones concretas."
                            </p>
                            <div>
                                <h4 class="text-xl font-bold">Dra. Claudia Vaca</h4>
                                <p class="text-amber-400 uppercase tracking-widest text-sm font-bold mt-1">Psicóloga Clínica - Bogotá, Colombia</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQs sobre inscripciones -->
            <section class="py-24 bg-white">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h3 class="text-3xl font-bold text-center text-slate-900 mb-16">Preguntas sobre Inscripción y Foros</h3>
                    <div class="space-y-4">
                        <div class="border border-slate-200 rounded-2xl overflow-hidden">
                            <button onclick="toggleFaq(this)" class="w-full text-left p-6 font-bold flex justify-between items-center bg-white hover:bg-slate-50">
                                <span>¿Los foros son en vivo o grabados?</span>
                                <i class="fa-solid fa-chevron-down text-slate-400"></i>
                            </button>
                            <div class="hidden p-6 text-slate-600 border-t border-slate-100">Son 100% sincrónicos (en vivo) a través de Meet/Zoom. Esto es fundamental para garantizar la interacción real, el debate y el análisis en conjunto.</div>
                        </div>
                        <div class="border border-slate-200 rounded-2xl overflow-hidden">
                            <button onclick="toggleFaq(this)" class="w-full text-left p-6 font-bold flex justify-between items-center bg-white hover:bg-slate-50">
                                <span>¿Hay descuentos si nos inscribimos en grupo?</span>
                                <i class="fa-solid fa-chevron-down text-slate-400"></i>
                            </button>
                            <div class="hidden p-6 text-slate-600 border-t border-slate-100">¡Sí! Fomentamos la participación colectiva. Si se inscriben 4 o más personas de una misma institución o grupo de estudio, obtienen un 20% de descuento automático.</div>
                        </div>
                        <div class="border border-slate-200 rounded-2xl overflow-hidden">
                            <button onclick="toggleFaq(this)" class="w-full text-left p-6 font-bold flex justify-between items-center bg-white hover:bg-slate-50">
                                <span>¿Cómo y cuándo recibo el certificado?</span>
                                <i class="fa-solid fa-chevron-down text-slate-400"></i>
                            </button>
                            <div class="hidden p-6 text-slate-600 border-t border-slate-100">Al finalizar el ciclo del foro, se envía la certificación digital avalada vía email a quienes hayan cumplido con el 75% de asistencia activa en los encuentros.</div>
                        </div>
                    </div>
                </div>
            </section>
        </section>

        <!-- FORUMS VIEW -->
        <section id="view-forums" class="view-section py-24 animate-fadeIn">
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
                                <p class="text-xs uppercase tracking-widest text-amber-300 font-bold mb-4">Cuenta regresiva</p>
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
                                    <button id="forumsPrevPage" type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 disabled:opacity-40">Anterior</button>
                                    <button id="forumsNextPage" type="button" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 disabled:opacity-40">Siguiente</button>
                                </div>
                            </div>
                            <div id="forumsList" class="grid grid-cols-1 md:grid-cols-2 gap-5"></div>
                            <p id="forumsPaginationMeta" class="mt-5 text-sm text-slate-500"></p>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-slate-900 rounded-3xl p-8 text-white sticky top-32">
                            <h4 class="text-2xl font-bold mb-6">Inversión y Cupos</h4>
                            <div class="space-y-6 mb-10">
                                <div class="flex justify-between items-center pb-4 border-b border-slate-700">
                                    <span class="text-slate-400">Profesionales</span>
                                    <span class="text-2xl font-bold text-amber-400">$35.000</span>
                                </div>
                                <div class="flex justify-between items-center pb-4 border-b border-slate-700">
                                    <span class="text-slate-400">Estudiantes</span>
                                    <span class="text-2xl font-bold text-amber-400">$20.000</span>
                                </div>
                            </div>
                            <div class="bg-amber-800/50 p-6 rounded-2xl border border-amber-700 mb-8">
                                <p class="text-sm font-bold flex items-center gap-3">
                                    <i class="fa-solid fa-gift text-xl"></i>
                                    PROMO GRUPAL: 20% OFF para grupos de estudio o de +4 personas.
                                </p>
                            </div>
                            <button onclick="openModal('registerModal')" class="w-full bg-amber-700 text-white font-bold py-5 rounded-2xl hover:bg-amber-500 shadow-lg shadow-amber-900 transition-all text-lg">Inscribirse Ahora</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        <!-- FORUM DETAIL VIEW -->
        <section id="view-forum-detail" class="view-section py-24 animate-fadeIn">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div id="forumDetailAlert" class="hidden mb-8 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800"></div>
                <div id="forumDetailContainer" class="space-y-8">
                    <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-xl">
                        <p class="text-sm text-slate-500">Selecciona un foro desde la agenda para ver el detalle.</p>
                    </div>
                </div>
                <div class="mt-8">
                    <button type="button" onclick="showView('forums')" class="rounded-2xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 hover:border-amber-500 hover:text-amber-800 transition-all">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Volver a agenda
                    </button>
                </div>
            </div>
        </section>

                <!-- ABOUT VIEW (Luz Genovese as Director) -->
        <section id="view-about" class="view-section py-24 animate-fadeIn">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row gap-20">
                    <div class="md:w-2/5">
                        <div class="sticky top-32">
                            <div class="relative mb-10">
                                <div class="absolute -top-6 -left-6 w-32 h-32 bg-amber-700 rounded-3xl -z-10 rotate-12"></div>
                                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&q=80" alt="Luz Genovese" class="rounded-3xl shadow-2xl w-full">
                            </div>
                            <div class="bg-white p-8 rounded-3xl shadow-xl border border-slate-100">
                                <h4 class="text-xl font-bold mb-4">Contacto Directo</h4>
                                <ul class="space-y-4">
                                    <li class="flex items-center gap-4 text-slate-600"><i class="fa-brands fa-whatsapp text-amber-700 text-xl w-6"></i> (+54) 9 11 5593 6719</li>
                                    <li class="flex items-center gap-4 text-slate-600"><i class="fa-solid fa-envelope text-amber-700 text-xl w-6"></i> luz@forospsme.com</li>
                                    <li class="flex items-center gap-4 text-slate-600"><i class="fa-solid fa-location-dot text-amber-700 text-xl w-6"></i> Buenos Aires, Argentina</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="md:w-3/5">
                        <h2 class="text-sm font-extrabold text-amber-700 uppercase tracking-widest mb-4">Dirección y Coordinación de Foros</h2>
                        <h3 class="text-5xl font-bold text-slate-900 mb-8">Maria Luz Genovese</h3>
                        <div class="prose prose-lg text-slate-600 space-y-6">
                            <p class="text-xl leading-relaxed font-medium text-slate-800">Psicóloga Social con enfoque en Salud Mental y Emocional (SmE).</p>
                            <p>Me especializo en la coordinación de grupos operativos y en el abordaje psicosocial de problemáticas contemporáneas. Mi misión como creadora y directora de los Foros PSME es democratizar el conocimiento psicológico y crear puentes sólidos entre la teoría académica y la práctica en terreno.</p>
                            <p>Entiendo la salud mental como un derecho inalienable y un proceso profundamente colectivo. Por eso, dedico gran parte de mi labor a moderar estos espacios internacionales de debate donde rompemos las barreras geográficas para aprender en conjunto.</p>
                        </div>

                        <!-- Reserva Cita (Secundario) -->
                        <div class="mt-16 bg-slate-100 border border-slate-200 rounded-[2rem] p-10">
                            <h4 class="text-2xl font-bold mb-2">Consulta Profesional Individual</h4>
                            <p class="text-slate-600 mb-8">Si bien mi foco está en la coordinación de los Foros, también brindo espacios de acompañamiento profesional personalizado de forma particular.</p>
                            <form onsubmit="event.preventDefault(); alert('Solicitud de consulta enviada')" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <input type="text" placeholder="Tu Nombre" class="bg-white border-slate-300 border rounded-xl p-4 text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-transparent" required>
                                <input type="tel" placeholder="WhatsApp" class="bg-white border-slate-300 border rounded-xl p-4 text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-transparent" required>
                                <textarea placeholder="Breve motivo de consulta o duda" class="bg-white border-slate-300 border rounded-xl p-4 text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-transparent sm:col-span-2" rows="3" required></textarea>
                                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold py-4 rounded-xl sm:col-span-2 transition-all">Solicitar Entrevista Individual</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- BLOG VIEW -->
        <section id="view-blog" class="view-section py-24 animate-fadeIn">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-sm font-extrabold text-amber-700 uppercase tracking-[0.2em] mb-4">Blog PSME</h2>
                    <h3 class="text-5xl font-extrabold text-slate-900 mb-6 tracking-tight">Reflexiones y Materiales</h3>
                    <p class="text-xl text-slate-600 max-w-3xl mx-auto">Lecturas breves para profundizar los ejes trabajados en los foros de salud mental y emocional.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <article class="bg-white rounded-3xl border border-slate-100 shadow-lg p-8 card-shadow">
                        <span class="inline-block text-xs font-extrabold uppercase tracking-widest text-amber-700 mb-4">Artículo</span>
                        <h4 class="text-2xl font-bold mb-4">Intervención grupal en crisis</h4>
                        <p class="text-slate-600">Claves para sostener dispositivos comunitarios en contextos de alta demanda emocional.</p>
                    </article>
                    <article class="bg-white rounded-3xl border border-slate-100 shadow-lg p-8 card-shadow">
                        <span class="inline-block text-xs font-extrabold uppercase tracking-widest text-amber-700 mb-4">Análisis</span>
                        <h4 class="text-2xl font-bold mb-4">Salud mental y territorio</h4>
                        <p class="text-slate-600">Prácticas situadas para profesionales que trabajan con realidades locales diversas.</p>
                    </article>
                    <article class="bg-white rounded-3xl border border-slate-100 shadow-lg p-8 card-shadow">
                        <span class="inline-block text-xs font-extrabold uppercase tracking-widest text-amber-700 mb-4">Guía</span>
                        <h4 class="text-2xl font-bold mb-4">Estrategias de autocuidado</h4>
                        <p class="text-slate-600">Recomendaciones para prevenir el desgaste en equipos de asistencia psicosocial.</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- DASHBOARD VIEW -->
        <section id="view-dashboard" class="view-section min-h-screen bg-slate-100 animate-fadeIn">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row gap-6 p-6">
                <!-- Sidebar -->
                <aside class="w-full md:w-72 space-y-4">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-amber-100 text-amber-700 rounded-2xl flex items-center justify-center font-bold text-xl">
                                <span id="userInitial">U</span>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-slate-800" id="userName">Mi Panel</h4>
                                <span class="text-xs font-bold text-amber-700 uppercase" id="userRoleBadge">Usuario</span>
                            </div>
                        </div>
                        <nav class="space-y-2">
                            <button onclick="setDashTab('overview')" class="w-full text-left p-4 rounded-2xl hover:bg-amber-50 hover:text-amber-800 font-bold transition-all flex items-center gap-3"><i class="fa-solid fa-chart-line"></i> Resumen</button>
                            
                            <!-- Admin only -->
                            <div class="admin-only hidden space-y-2 pt-2 border-t mt-2">
                                <button class="w-full text-left p-4 rounded-2xl hover:bg-amber-50 hover:text-amber-800 font-bold flex items-center gap-3"><i class="fa-solid fa-users"></i> Gestión Inscripciones</button>
                                <button class="w-full text-left p-4 rounded-2xl hover:bg-amber-50 hover:text-amber-800 font-bold flex items-center gap-3"><i class="fa-solid fa-file-invoice-dollar"></i> Validar Pagos Foros</button>
                                <button class="w-full text-left p-4 rounded-2xl hover:bg-amber-50 hover:text-amber-800 font-bold flex items-center gap-3"><i class="fa-solid fa-file-lines"></i> Páginas</button>
                            </div>

                            <!-- Associate only -->
                            <div class="associate-only hidden space-y-2 pt-2 border-t mt-2">
                                <button class="w-full text-left p-4 rounded-2xl hover:bg-amber-50 hover:text-amber-800 font-bold flex items-center gap-3"><i class="fa-solid fa-link"></i> Link Referido Grupo</button>
                                <button class="w-full text-left p-4 rounded-2xl hover:bg-amber-50 hover:text-amber-800 font-bold flex items-center gap-3"><i class="fa-solid fa-users-viewfinder"></i> Mi Grupo / Referidos</button>
                            </div>

                            <!-- User only -->
                            <div class="user-only hidden space-y-2 pt-2 border-t mt-2">
                                <button class="w-full text-left p-4 rounded-2xl hover:bg-amber-50 hover:text-amber-800 font-bold flex items-center gap-3"><i class="fa-solid fa-calendar-check"></i> Mis Foros Activos</button>
                                <button class="w-full text-left p-4 rounded-2xl hover:bg-amber-50 hover:text-amber-800 font-bold flex items-center gap-3"><i class="fa-solid fa-cloud-arrow-down"></i> Materiales y Certificados</button>
                            </div>

                            <button onclick="logout()" class="w-full text-left p-4 rounded-2xl hover:bg-rose-50 hover:text-rose-600 font-bold transition-all flex items-center gap-3 text-slate-400 mt-10"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</button>
                        </nav>
                    </div>
                </aside>

                <!-- Dashboard Content -->
                <div class="flex-1 bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-200">
                    <div id="dash-overview">
                        <h3 class="text-3xl font-extrabold text-slate-900 mb-8">Portal de Asistencia</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-amber-50 border border-amber-100 p-8 rounded-3xl">
                                <p class="text-amber-700 text-sm font-bold uppercase mb-2">Estado Inscripción</p>
                                <h4 class="text-3xl font-extrabold text-amber-900">Confirmada</h4>
                            </div>
                            <div class="bg-amber-50 border border-amber-200 p-8 rounded-3xl">
                                <p class="text-amber-700 text-sm font-bold uppercase mb-2">Eventos Próximos</p>
                                <h4 class="text-3xl font-extrabold text-amber-900">4 Sesiones</h4>
                            </div>
                            <div class="bg-slate-50 border border-slate-200 p-8 rounded-3xl">
                                <p class="text-slate-500 text-sm font-bold uppercase mb-2">Certificados</p>
                                <h4 class="text-3xl font-extrabold text-slate-800">Pendiente</h4>
                            </div>
                        </div>
                        <div class="mt-12 space-y-6">
                            <div>
                                <h4 class="text-xl font-bold mb-6">Actividad y Anuncios del Foro</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-6 bg-slate-50 rounded-2xl border border-slate-100">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-amber-700 shadow-sm"><i class="fa-solid fa-check"></i></div>
                                            <div><p class="font-bold">Enlace de Zoom - Sesión 1</p><p class="text-xs text-slate-500">Estará disponible 24hs antes del evento.</p></div>
                                        </div>
                                        <span class="text-xs font-bold text-slate-400">Automático</span>
                                    </div>
                                </div>
                            </div>
                            <div class="admin-only hidden bg-amber-50 border border-amber-100 p-6 rounded-3xl space-y-6">
                                <p class="text-xs font-bold uppercase text-amber-600 mb-2">Módulo exclusivo Admin</p>
                                <h5 class="text-xl font-extrabold text-amber-900 mb-2">Validación masiva de pagos</h5>
                                <p class="text-amber-800">Accede al consolidado de transferencias y libera cupos de forma centralizada para todos los grupos del ciclo.</p>
                                <div class="bg-white rounded-2xl border border-amber-100 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h6 class="font-bold text-slate-800">Inscripciones (CRUD)</h6>
                                        <button type="button" id="refreshAdminRegistrations" class="text-xs font-bold text-amber-700">Actualizar</button>
                                    </div>
                                    <div id="adminRegistrationsList" class="space-y-3 text-sm text-slate-700"></div>
                                </div>
                                <div class="bg-white rounded-2xl border border-amber-100 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h6 class="font-bold text-slate-800">Asociados y oferta activa</h6>
                                        <button type="button" id="refreshAdminAssociates" class="text-xs font-bold text-amber-700">Actualizar</button>
                                    </div>
                                    <div id="adminAssociatesList" class="space-y-2 text-sm text-slate-700"></div>
                                </div>
                                <div class="bg-white rounded-2xl border border-amber-100 p-4 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h6 class="font-bold text-slate-800">Páginas (CRUD)</h6>
                                        <button type="button" id="refreshAdminPages" class="text-xs font-bold text-amber-700">Actualizar</button>
                                    </div>
                                    <form id="adminPageForm" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <input type="hidden" name="id">
                                        <input name="slug" placeholder="slug (ej: quienes-somos)" class="rounded-xl border border-slate-200 px-3 py-2" required>
                                        <input name="title" placeholder="Título" class="rounded-xl border border-slate-200 px-3 py-2" required>
                                        <select name="status" class="rounded-xl border border-slate-200 px-3 py-2">
                                            <option value="draft">Borrador</option>
                                            <option value="published">Publicada</option>
                                        </select>
                                        <input name="seo_title" placeholder="SEO title" class="rounded-xl border border-slate-200 px-3 py-2">
                                        <textarea name="seo_description" placeholder="SEO description" class="rounded-xl border border-slate-200 px-3 py-2 md:col-span-2" rows="2"></textarea>
                                        <textarea name="content_html" placeholder="HTML permitido (p, a, ul, h1-h6, img, etc.)" class="rounded-xl border border-slate-200 px-3 py-2 md:col-span-2" rows="6" required></textarea>
                                        <div class="md:col-span-2 flex items-center gap-3">
                                            <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded-xl font-bold text-sm">Guardar página</button>
                                            <button type="button" id="adminPageFormReset" class="border border-slate-300 px-4 py-2 rounded-xl font-bold text-sm">Limpiar</button>
                                            <span id="adminPagesStatus" class="text-xs font-bold text-amber-700"></span>
                                        </div>
                                    </form>
                                    <div id="adminPagesList" class="space-y-2 text-sm text-slate-700"></div>
                                </div>
                            </div>
                            <div class="associate-only hidden bg-amber-50 border border-amber-100 p-6 rounded-3xl space-y-4">
                                <p class="text-xs font-bold uppercase text-amber-700 mb-2">Módulo exclusivo Asociado</p>
                                <h5 class="text-xl font-extrabold text-amber-900 mb-2">Panel de referidos</h5>
                                <p class="text-amber-800">Comparte tu link, revisa conversiones y gestiona miembros activos de tu grupo derivado.</p>
                                <div class="bg-white rounded-2xl border border-amber-200 p-4 space-y-3">
                                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                        <h6 class="font-bold text-slate-800">Gestión de inscripciones referidas</h6>
                                        <div class="flex items-center gap-2">
                                            <select id="associateRegistrationsFilter" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
                                                <option value="all">Todos</option>
                                                <option value="pending">Pendiente</option>
                                                <option value="approved">Aprobada</option>
                                                <option value="rejected">Rechazada</option>
                                            </select>
                                            <button type="button" id="refreshAssociateRegistrations" class="text-xs font-bold text-amber-600">Actualizar</button>
                                        </div>
                                    </div>
                                    <div id="associateRegistrationsList" class="space-y-2 text-sm text-slate-700"></div>
                                </div>
                                <form id="associateOfferForm" class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-white rounded-2xl p-4 border border-amber-200">
                                    <input name="referralCode" placeholder="Código referido (ASOCIADO2026)" class="rounded-xl border border-slate-200 px-3 py-2" required>
                                    <input name="currencyCode" placeholder="Moneda (USD, ARS)" maxlength="3" class="rounded-xl border border-slate-200 px-3 py-2" required>
                                    <input name="priceAmount" type="number" min="0.01" step="0.01" placeholder="Precio" class="rounded-xl border border-slate-200 px-3 py-2" required>
                                    <input name="paymentMethod" placeholder="Método de cobro" class="rounded-xl border border-slate-200 px-3 py-2" required>
                                    <input name="paymentLink" type="url" placeholder="https://link-de-cobro..." class="rounded-xl border border-slate-200 px-3 py-2 md:col-span-2" required>
                                    <div class="md:col-span-2 flex items-center gap-3">
                                        <button type="submit" class="bg-amber-700 text-white px-4 py-2 rounded-xl font-bold text-sm">Guardar configuración</button>
                                        <span id="associateOfferStatus" class="text-xs font-bold text-amber-600"></span>
                                    </div>
                                    <p class="md:col-span-2 text-xs text-slate-500">Link de referido: <span id="associateReferralPreview" class="font-bold text-amber-600"></span></p>
                                </form>
                            </div>
                            <div class="user-only hidden bg-amber-50 border border-amber-100 p-6 rounded-3xl">
                                <p class="text-xs font-bold uppercase text-amber-700 mb-2">Módulo exclusivo Inscripto</p>
                                <h5 class="text-xl font-extrabold text-amber-900 mb-2">Materiales y certificados</h5>
                                <p class="text-amber-800">Descarga bibliografía recomendada, enlaces de reunión y constancias habilitadas para tu cohorte.</p>
                                <div id="userEbooksAlert" class="hidden mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-3 text-xs font-semibold text-amber-800"></div>
                                <div class="mt-5 rounded-2xl border border-amber-200 bg-white p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h6 class="font-bold text-slate-800">Ebooks habilitados</h6>
                                        <button type="button" id="refreshUserEbooks" class="text-xs font-bold text-amber-600">Actualizar</button>
                                    </div>
                                    <div id="userEbooksList" class="space-y-3 text-sm text-slate-700">
                                        <p class="text-xs text-slate-500">Inicia sesión como inscripto para consultar tu acceso.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="guest-only hidden bg-amber-50 border border-amber-100 p-6 rounded-3xl">
                                <p class="text-xs font-bold uppercase text-amber-600 mb-2">Acceso restringido</p>
                                <h5 class="text-xl font-extrabold text-amber-900 mb-2">Inicia sesión para desbloquear módulos</h5>
                                <p class="text-amber-800">Como invitado sólo puedes navegar la portada y agenda. Regístrate para activar tu panel personal.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>

<?php require __DIR__ . '/partials/modals/register.php'; ?>
    <!-- ROLE SIMULATOR (DOCK) -->
    <div class="fixed bottom-6 left-1/2 -translate-x-1/2 glass px-6 py-3 rounded-2xl shadow-2xl border border-slate-200 z-[999] flex items-center gap-4">
        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest border-r pr-4">Simulador de Rol</span>
        <div class="flex gap-2">
            <button onclick="setRole('guest')" class="px-3 py-1 text-xs font-bold rounded-lg border hover:bg-slate-100">Invitado</button>
            <button onclick="setRole('user')" class="px-3 py-1 text-xs font-bold rounded-lg border bg-amber-50 text-amber-600 border-amber-100 hover:bg-amber-100">Inscripto</button>
            <button onclick="setRole('associate')" class="px-3 py-1 text-xs font-bold rounded-lg border bg-amber-100 text-amber-800 border-amber-200 hover:bg-amber-200">Asociado</button>
            <button onclick="setRole('admin')" class="px-3 py-1 text-xs font-bold rounded-lg border bg-amber-50 text-amber-700 border-amber-100 hover:bg-amber-100">Admin (Luz)</button>
        </div>
    </div>

<?php require __DIR__ . '/partials/footer.php'; ?>

    <script type="module" src="/assets/js/navigation.js"></script>
    <script type="module" src="/assets/js/auth.js"></script>
    <script type="module" src="/assets/js/registrations.js"></script>
    <script type="module" src="/assets/js/forums.js"></script>
    <script type="module" src="/assets/js/pages-admin.js"></script>

</body>
</html>
