<?php require_once __DIR__ . '/../../../Support/ContentBlocks.php'; ?>
    <!-- MODAL: REGISTRO A FOROS -->
    <div id="registerModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md">
        <div class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50 rounded-t-[2.5rem]">
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-800">Inscripción al Foro</h2>
                    <p class="text-sm text-slate-500 font-bold">Ciclo Mayo 2026 - Certificado Incluido</p>
                </div>
                <button onclick="closeModal('registerModal')" class="w-10 h-10 bg-white shadow-sm rounded-full flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 transition-all">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="registerForm" class="p-8 space-y-6">
                <div id="registerFormAlert" class="hidden rounded-2xl px-4 py-3 text-sm font-bold"></div>
                <div id="referralOfferNotice" class="hidden rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"></div>
                <input type="hidden" id="referralCodeInput" name="referralCode" value="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">País para medios de pago *</label>
                    <select id="registrationCountrySelect" name="countryCode" class="w-full bg-slate-100 border-none rounded-xl p-4 font-medium focus:ring-2 focus:ring-amber-500">
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
                        <select name="forumId" id="forumIdSelect" class="w-full bg-slate-100 border-none rounded-xl p-4 font-medium focus:ring-2 focus:ring-amber-500" required>
                            <option value="">Cargando foros disponibles...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nombre y Apellidos *</label>
                        <input name="fullName" type="text" placeholder="Como figurará en certificado" class="w-full bg-slate-100 border-none rounded-xl p-4 font-medium focus:ring-2 focus:ring-amber-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">DNI / Documento *</label>
                        <input name="documentId" type="text" placeholder="Nº identificación" class="w-full bg-slate-100 border-none rounded-xl p-4 font-medium focus:ring-2 focus:ring-amber-500" required>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">¿Requieres Certificación con Aval? *</label>
                        <div class="flex gap-6 mt-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="certif" value="no" class="w-5 h-5 text-amber-700 focus:ring-amber-500" checked onchange="toggleCertFields(false)">
                                <span class="font-bold text-slate-700">No</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="certif" value="yes" class="w-5 h-5 text-amber-700 focus:ring-amber-500" onchange="toggleCertFields(true)">
                                <span class="font-bold text-slate-700">Sí</span>
                            </label>
                        </div>
                    </div>
                    <div id="certFields" class="col-span-1 md:col-span-2 hidden bg-amber-50 p-6 rounded-3xl border border-amber-100 space-y-4">
                        <p id="paymentInstructions" class="text-sm text-amber-800 font-bold"><i class="fa-solid fa-link mr-2"></i> Realiza el abono de inscripción al Foro aquí:</p>
                        <a id="paymentGatewayLink" href="#" target="_blank" rel="noopener noreferrer" class="inline-block bg-amber-700 text-white px-6 py-2 rounded-xl text-sm font-bold hover:bg-amber-800 transition-all">Ir a la pasarela de Pago</a>
                        <div>
                            <label class="block text-xs font-bold text-amber-800 mb-2 uppercase tracking-widest">Adjuntar Comprobante (Ref: DNI)</label>
                            <input id="paymentProof" name="paymentProof" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-white file:text-amber-800 hover:file:bg-amber-100">
                        </div>
                    </div>
                </div>
                <div class="bg-yellow-50 p-6 rounded-3xl border-l-4 border-yellow-400">
                    <p class="text-sm text-yellow-800 font-bold italic mb-4">"Los foros se basan en la interacción. Tu aceptación es tu firma: te pedimos compromiso y asistencia para cuidar la dinámica del grupo."</p>
                    <label class="flex items-center gap-3">
                        <input id="acceptanceCheck" name="acceptanceCheck" type="checkbox" class="w-5 h-5 rounded text-amber-700" required>
                        <span class="text-sm font-extrabold text-slate-800 uppercase">Acepto y Firmo</span>
                    </label>
                    <div class="mt-4 space-y-2">
                        <label class="block text-xs font-bold text-yellow-900 uppercase tracking-widest">Firma digital</label>
                        <canvas id="signatureCanvas" class="w-full h-40 rounded-2xl bg-white border border-yellow-300"></canvas>
                        <button id="clearSignatureBtn" type="button" class="text-xs font-bold text-yellow-900 underline decoration-2 underline-offset-2">Limpiar firma</button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-amber-700 text-white font-extrabold py-5 rounded-2xl text-lg hover:bg-amber-800 transition-all shadow-xl shadow-amber-100">Confirmar Inscripción</button>
            </form>
        </div>
    </div>
