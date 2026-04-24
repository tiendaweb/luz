<?php

declare(strict_types=1);

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
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
