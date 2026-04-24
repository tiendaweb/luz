<?php
declare(strict_types=1);
require_once __DIR__ . '/../_session.php';
require_once __DIR__ . '/../layouts/main.php';

ob_start();
?>

<!-- Inscripción al Foro -->
<section class="py-24">
 <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
 <div class="bg-white rounded-3xl p-10 shadow-xl border border-slate-100">
 <div class="mb-8">
 <h1 class="text-4xl font-extrabold text-slate-900">Inscripción al Foro</h1>
 <p class="text-lg text-slate-600 mt-2">Ciclo Mayo 2026 - Certificado Incluido</p>
 </div>

 <form id="registerForm" class="space-y-6" novalidate>
 <div id="registerFormAlert" class="hidden rounded-2xl px-4 py-3 text-sm font-bold"></div>
 <div id="referralOfferNotice" class="hidden rounded-2xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm text-violet-900"></div>
 <input type="hidden" id="referralCodeInput" name="referralCode" value="">

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <div class="col-span-1 md:col-span-2">
 <label class="block text-sm font-bold text-slate-700 mb-2">País para medios de pago *</label>
 <select id="registrationCountrySelect" name="countryCode" class="w-full bg-slate-100 border-none rounded-xl p-4 font-medium focus:ring-2 focus:ring-[var(--color-accent)]">
 <option value="AR">Argentina (AR)</option>
 <option value="CO">Colombia (CO)</option>
 <option value="MX">México (MX)</option>
 <option value="PE">Perú (PE)</option>
 <option value="US">Estados Unidos (US)</option>
 <option value="CL">Chile (CL)</option>
 <option value="UY">Uruguay (UY)</option>
 </select>
 </div>

 <div class="col-span-1 md:col-span-2">
 <label class="block text-sm font-bold text-slate-700 mb-2">Foro Elegido *</label>
 <select name="forumId" id="forumIdSelect" class="w-full bg-slate-100 border-none rounded-xl p-4 font-medium focus:ring-2 focus:ring-[var(--color-accent)]" required>
 <option value="">Selecciona tu grupo y horario...</option>
 <option value="1">Foro de la mañana</option>
 <option value="2">Foro de la tarde</option>
 </select>
 </div>
 <div>
 <label class="block text-sm font-bold text-slate-700 mb-2">Email de ingreso *</label>
 <input name="email" type="email" placeholder="tu@email.com" class="w-full bg-slate-100 border-none rounded-xl p-4 font-medium focus:ring-2 focus:ring-[var(--color-accent)]" required>
 </div>
 <div>
 <label class="block text-sm font-bold text-slate-700 mb-2">Contraseña *</label>
 <input name="password" type="password" placeholder="Mínimo 6 caracteres" class="w-full bg-slate-100 border-none rounded-xl p-4 font-medium focus:ring-2 focus:ring-[var(--color-accent)]" required minlength="6">
 </div>
 <div>
 <label class="block text-sm font-bold text-slate-700 mb-2">Nombre y Apellidos *</label>
 <input name="fullName" type="text" placeholder="Como figurará en certificado" class="w-full bg-slate-100 border-none rounded-xl p-4 font-medium focus:ring-2 focus:ring-[var(--color-accent)]" required>
 </div>
 <div>
 <label class="block text-sm font-bold text-slate-700 mb-2">DNI / Documento *</label>
 <input name="documentId" type="text" placeholder="Nº identificación" class="w-full bg-slate-100 border-none rounded-xl p-4 font-medium focus:ring-2 focus:ring-[var(--color-accent)]" required>
 </div>
 <div class="col-span-1 md:col-span-2">
 <label class="block text-sm font-bold text-slate-700 mb-2">¿Requieres Certificación con Aval? *</label>
 <div class="flex gap-6 mt-2">
 <label class="flex items-center gap-3 cursor-pointer">
 <input type="radio" name="certif" value="no" class="w-5 h-5 text-brand focus:ring-[var(--color-accent)]" checked onchange="toggleCertFields(false)">
 <span class="font-bold text-slate-700">No</span>
 </label>
 <label class="flex items-center gap-3 cursor-pointer">
 <input type="radio" name="certif" value="yes" class="w-5 h-5 text-brand focus:ring-[var(--color-accent)]" onchange="toggleCertFields(true)">
 <span class="font-bold text-slate-700">Sí</span>
 </label>
 </div>
 </div>
 <div id="certFields" class="col-span-1 md:col-span-2 hidden bg-brand p-6 rounded-3xl border border-[var(--color-accent)]/20 space-y-4">
 <p id="paymentInstructions" class="text-sm text-brand font-bold"><i class="fa-solid fa-link mr-2"></i> Realiza el abono de inscripción al Foro aquí:</p>
 <a id="paymentGatewayLink" href="#" target="_blank" rel="noopener noreferrer" class="inline-block btn-primary px-6 py-2 rounded-xl text-sm font-bold transition-all">Ir a la pasarela de Pago</a>
 <div>
 <label class="block text-xs font-bold text-brand mb-2 uppercase tracking-widest">Adjuntar Comprobante (Ref: DNI)</label>
 <input id="paymentProof" name="paymentProof" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-white file:text-brand hover:file:bg-brand">
 </div>
 </div>
 </div>

 <div class="bg-yellow-50 p-6 rounded-3xl border-l-4 border-yellow-400">
 <p class="text-sm text-yellow-800 font-bold italic mb-4">"Los foros se basan en la interacción. Tu aceptación es tu firma: te pedimos compromiso y asistencia para cuidar la dinámica del grupo."</p>
 <label class="flex items-center gap-3">
 <input id="acceptanceCheck" name="acceptanceCheck" type="checkbox" class="w-5 h-5 rounded text-brand" required>
 <span class="text-sm font-extrabold text-slate-800 uppercase">Acepto y Firmo</span>
 </label>
 <div class="mt-4 space-y-2">
 <label class="block text-xs font-bold text-yellow-900 uppercase tracking-widest">Firma digital</label>
 <canvas id="signatureCanvas" class="w-full h-40 rounded-2xl bg-white border border-yellow-300"></canvas>
 <button id="clearSignatureBtn" type="button" class="text-xs font-bold text-yellow-900 underline decoration-2 underline-offset-2">Limpiar firma</button>
 </div>
 </div>

 <button type="submit" class="w-full btn-primary font-extrabold py-5 rounded-2xl text-lg transition-all shadow-xl shadow-[color:color-mix(in_srgb,var(--color-accent)_35%,transparent)]">
 Confirmar Inscripción
 </button>
 </form>
 </div>

 <div class="mt-12 text-center">
 <p class="text-slate-600 mb-6">¿Preguntas sobre el proceso?</p>
 <a href="/directora" class="inline-flex items-center gap-2 px-8 py-4 bg-slate-900 text-white font-bold rounded-2xl hover:bg-slate-800 transition-all">
 <i class="fa-solid fa-arrow-left"></i> Volver a la página principal
 </a>
 </div>
 </div>
</section>

<?php
$content = ob_get_clean();

render_main_layout([
 'title' => 'Inscripción al Foro | Foros PSME',
 'role' => $_viewCurrentRole,
 'content' => $content,
 'scripts' => ['/assets/js/auth.js', '/assets/js/registrations.js'],
]);
