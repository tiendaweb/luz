<?php

declare(strict_types=1);

// Iniciar sesión con la misma configuración que _bootstrap.php
if (session_status() === PHP_SESSION_NONE) {
    $_viewSessionDir = dirname(__DIR__, 2) . '/data/sessions';
    if (!is_dir($_viewSessionDir)) {
        @mkdir($_viewSessionDir, 0700, true);
    }
    session_save_path($_viewSessionDir);
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
    session_start();
}

require_once __DIR__ . '/../Support/SiteSettings.php';

// Variables globales de sesión para las vistas
$_viewCurrentUser = $_SESSION['auth_user'] ?? null;
$_viewCurrentRole = $_viewCurrentUser['role'] ?? 'guest';
$_viewIsLoggedIn = $_viewCurrentUser !== null;
$_viewUserEmail = $_viewCurrentUser['email'] ?? null;
$_viewUserName = $_viewCurrentUser['full_name'] ?? null;
$_viewSiteSettings = app_public_site_settings();
