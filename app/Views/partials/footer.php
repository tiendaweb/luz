<?php

declare(strict_types=1);

$forumsHref = '/foros';
$blogHref = '/blog';
$aboutHref = '/directora';
$contactShortText = (string)($siteSettings['contact_short_text'] ?? 'Comunidad de debate y fortalecimiento psicosocial en Latinoamérica.');
$directorName = (string)($siteSettings['director_name'] ?? 'María Luz Genovese');
$publicPhone = (string)($siteSettings['public_phone_primary'] ?? '+54 9 11 4000-0000');
$publicEmail = (string)($siteSettings['public_email_primary'] ?? 'contacto@forospsme.com');
?>
    <!-- FOOTER -->
    <footer class="bg-slate-950 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-12">
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold mr-3 shadow-lg rotate-3" style="background-color: var(--brand-primary);">
                        <i class="fa-solid fa-users-viewfinder"></i>
                    </div>
                    <h2 class="text-2xl font-bold">Foros PSME</h2>
                </div>
                <p class="text-slate-400 max-w-sm mb-4"><?= htmlspecialchars($contactShortText, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="text-sm font-bold mb-4 uppercase tracking-widest" style="color: var(--brand-primary);">Bajo la dirección de <?= htmlspecialchars($directorName, ENT_QUOTES, 'UTF-8') ?></p>
                <p class="text-sm text-slate-400 mb-8"><i class="fa-solid fa-phone mr-2"></i><?= htmlspecialchars($publicPhone, ENT_QUOTES, 'UTF-8') ?> · <i class="fa-solid fa-envelope ml-2 mr-2"></i><?= htmlspecialchars($publicEmail, ENT_QUOTES, 'UTF-8') ?></p>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center border border-slate-800 hover:border-teal-600 transition-all"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center border border-slate-800 hover:border-teal-600 transition-all"><i class="fa-brands fa-linkedin"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center border border-slate-800 hover:border-teal-600 transition-all"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>
            <div>
                <h4 class="font-bold mb-6 uppercase tracking-widest text-sm" style="color: var(--brand-primary);">Foros</h4>
                <ul class="space-y-4 text-slate-400">
                    <li><a href="<?= $forumsHref ?>" class="hover:text-white transition-colors">Ver Cronogramas</a></li>
                    <li><a href="/" class="hover:text-white transition-colors">Inscripciones</a></li>
                    <li><a href="<?= $blogHref ?>" class="hover:text-white transition-colors">Materiales Blog</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-6 uppercase tracking-widest text-sm" style="color: var(--brand-primary);"><?= htmlspecialchars($directorName, ENT_QUOTES, 'UTF-8') ?></h4>
                <ul class="space-y-4 text-slate-400">
                    <li><a href="<?= $aboutHref ?>" class="hover:text-white transition-colors">Perfil Profesional</a></li>
                    <li><a href="<?= $aboutHref ?>" class="hover:text-white transition-colors">Contacto Particular</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Políticas y Términos</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 mt-20 pt-8 border-t border-slate-900 text-center text-slate-600 text-sm">
            © 2026 Foros PSME (Salud Mental y Emocional) - Dir. <?= htmlspecialchars($directorName, ENT_QUOTES, 'UTF-8') ?>. Todos los derechos reservados.
        </div>
    </footer>
