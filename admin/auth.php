<?php
declare(strict_types=1);

/**
 * MarkdownWeb Admin – Authentifizierungs-Helper
 * Wird von allen Admin-Seiten eingebunden.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use MarkdownWeb\Config;

$config  = Config::getInstance();
$baseUrl = rtrim($config->get('base_url', ''), '/');

// Sichere Session-Einstellungen
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// Erster Start: Kein Passwort gesetzt → Setup
define('ADMIN_SETUP_NEEDED', empty($config->get('admin_password_hash')));

function getBaseUrl(): string
{
    return rtrim(Config::getInstance()->get('base_url', ''), '/');
}

function isAuthenticated(): bool
{
    return !empty($_SESSION['mw_admin_auth']) && $_SESSION['mw_admin_auth'] === true;
}

function requireAuth(): void
{
    $base = getBaseUrl();
    if (ADMIN_SETUP_NEEDED) {
        header('Location: ' . $base . '/admin/setup.php');
        exit;
    }
    if (!isAuthenticated()) {
        header('Location: ' . $base . '/admin/index.php');
        exit;
    }
}

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool
{
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect innerhalb des Admin-Bereichs.
 * $path ist immer relativ zum base_url, z.B. '/admin/filetree.php'
 */
function adminRedirect(string $path, string $message = '', string $type = 'success'): never
{
    if ($message !== '') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type']    = $type;
    }
    header('Location: ' . getBaseUrl() . $path);
    exit;
}

function getFlash(): array
{
    $msg  = $_SESSION['flash_message'] ?? '';
    $type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    return ['message' => $msg, 'type' => $type];
}
