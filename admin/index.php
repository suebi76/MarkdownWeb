<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

// Bereits eingeloggt → Dashboard
if (isAuthenticated()) {
    header('Location: ' . $baseUrl . '/admin/dashboard.php');
    exit;
}

// Setup nötig → Setup-Seite
if (ADMIN_SETUP_NEEDED) {
    header('Location: ' . $baseUrl . '/admin/setup.php');
    exit;
}

$error = '';

// Login-Versuch
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Brute-Force-Schutz
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_locked_until'] = 0;
    }

    if (time() < $_SESSION['login_locked_until']) {
        $remaining = (int) ceil(($_SESSION['login_locked_until'] - time()) / 60);
        $error = "Zu viele Fehlversuche. Bitte $remaining Minute(n) warten.";
    } else {
        $password = $_POST['password'] ?? '';
        $hash     = $config->get('admin_password_hash');

        if (password_verify($password, $hash)) {
            $_SESSION['login_attempts']    = 0;
            $_SESSION['mw_admin_auth']     = true;
            session_regenerate_id(true);
            header('Location: ' . $baseUrl . '/admin/dashboard.php');
            exit;
        } else {
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['login_locked_until'] = time() + 900; // 15 Minuten
                $error = 'Zu viele Fehlversuche. Bitte 15 Minuten warten.';
            } else {
                $remaining = 5 - $_SESSION['login_attempts'];
                $error = "Falsches Passwort. Noch $remaining Versuch(e).";
            }
        }
    }
}

$siteName = $config->get('site_name', 'MarkdownWeb');
?>
<!DOCTYPE html>
<html lang="de" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – <?= htmlspecialchars($siteName) ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
    <style>:root { --color-accent: <?= htmlspecialchars($config->get('accent_color', '#E50046')) ?>; }</style>
</head>
<body class="admin-login-page">
<div class="login-wrap">
    <div class="login-box">
        <div class="login-logo">
            <span class="site-logo-accent">M</span><?= htmlspecialchars(substr($siteName, 1)) ?>
        </div>
        <h1 class="login-title">Admin-Bereich</h1>

        <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= $baseUrl ?>/admin/index.php" autocomplete="off">
            <div class="form-group">
                <label for="password">Passwort</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autofocus
                    autocomplete="current-password"
                >
            </div>
            <button type="submit" class="btn btn-primary btn-full">Einloggen</button>
        </form>

        <p class="login-back"><a href="<?= $baseUrl ?>/">← Zur Website</a></p>
    </div>
</div>
<script src="<?= $baseUrl ?>/assets/js/main.js"></script>
</body>
</html>
