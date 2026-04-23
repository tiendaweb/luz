<?php

declare(strict_types=1);

$headerMode = $headerMode ?? 'spa';
$isStaticHeader = $headerMode === 'static';
$homeHref = $isStaticHeader ? '/index.php' : 'javascript:void(0)';
$forumsHref = $isStaticHeader ? '/index.php#view-forums' : 'javascript:void(0)';
$aboutHref = $isStaticHeader ? '/index.php#view-about' : 'javascript:void(0)';
$blogHref = $isStaticHeader ? '/index.php#view-blog' : 'javascript:void(0)';

$homeAction = $isStaticHeader ? '' : 'onclick="showView(\'home\')"';
$forumsAction = $isStaticHeader ? '' : 'onclick="showView(\'forums\')"';
$aboutAction = $isStaticHeader ? '' : 'onclick="showView(\'about\')"';
$blogAction = $isStaticHeader ? '' : 'onclick="showView(\'blog\')"';
?>
    <!-- NAVBAR -->
    <nav class="glass fixed w-full z-50 border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <a class="flex items-center cursor-pointer" href="<?= $homeHref ?>" <?= $homeAction ?>>
                    <div class="w-11 h-11 bg-teal-600 rounded-xl flex items-center justify-center text-white shadow-lg rotate-3 mr-4">
                        <i class="fa-solid fa-users-viewfinder text-xl -rotate-3"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-800 tracking-tight leading-none">Foros PSME</h1>
                        <p class="text-[10px] uppercase tracking-widest text-teal-600 font-bold mt-1">Dir. Maria Luz Genovese</p>
                    </div>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="<?= $homeHref ?>" <?= $homeAction ?> class="text-sm font-semibold hover:text-teal-600 transition-colors">Inicio</a>
                    <a href="<?= $forumsHref ?>" <?= $forumsAction ?> class="text-sm font-semibold hover:text-teal-600 transition-colors">Foros y Agenda</a>
                    <a href="<?= $aboutHref ?>" <?= $aboutAction ?> class="text-sm font-semibold hover:text-teal-600 transition-colors">La Directora</a>
                    <a href="<?= $blogHref ?>" <?= $blogAction ?> class="text-sm font-semibold hover:text-teal-600 transition-colors">Blog</a>

                    <?php if (!$isStaticHeader): ?>
                    <button onclick="showView('dashboard')" class="user-access-btn hidden bg-slate-900 text-white px-5 py-2.5 rounded-full text-sm font-bold hover:bg-teal-700 transition-all flex items-center gap-2">
                        <i class="fa-solid fa-circle-user"></i> Mi Área
                    </button>

                    <button onclick="openModal('registerModal')" class="bg-teal-600 text-white px-6 py-2.5 rounded-full text-sm font-bold hover:bg-teal-700 shadow-md transition-all flex items-center gap-2">
                        <i class="fa-solid fa-ticket"></i> Inscribirse
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Mobile menu button -->
                <?php if (!$isStaticHeader): ?>
                <div class="flex items-center md:hidden">
                    <button onclick="toggleMobileMenu()" class="text-slate-600 p-2">
                        <i class="fa-solid fa-bars-staggered text-2xl"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if (!$isStaticHeader): ?>
    <!-- MOBILE MENU -->
    <div id="mobileMenu" class="fixed inset-0 z-[60] bg-white hidden">
        <div class="p-6">
            <div class="flex justify-end mb-8">
                <button onclick="toggleMobileMenu()"><i class="fa-solid fa-xmark text-3xl"></i></button>
            </div>
            <div class="flex flex-col space-y-6 text-center text-xl font-bold">
                <a onclick="showView('home'); toggleMobileMenu()">Inicio</a>
                <a onclick="showView('forums'); toggleMobileMenu()">Foros y Agenda</a>
                <a onclick="showView('about'); toggleMobileMenu()">La Directora</a>
                <a onclick="showView('blog'); toggleMobileMenu()">Blog</a>
                <hr>
                <button onclick="showView('dashboard'); toggleMobileMenu()" class="mobile-user-access-btn hidden bg-slate-900 text-white py-4 rounded-2xl flex items-center justify-center gap-2"><i class="fa-solid fa-circle-user"></i> Mi Área</button>
                <button onclick="openModal('registerModal')" class="bg-teal-600 text-white py-4 rounded-2xl flex items-center justify-center gap-2"><i class="fa-solid fa-ticket"></i> Inscribirse a los Foros</button>
            </div>
        </div>
    </div>
    <?php endif; ?>
