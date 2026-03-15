<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

// Wenn bereits ein Passwort gesetzt → Login
if (!ADMIN_SETUP_NEEDED) {
    header('Location: ' . $baseUrl . '/admin/index.php');
    exit;
}

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw1 = $_POST['password']  ?? '';
    $pw2 = $_POST['password2'] ?? '';

    if (strlen($pw1) < 8) {
        $error = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
    } elseif ($pw1 !== $pw2) {
        $error = 'Die Passwörter stimmen nicht überein.';
    } else {
        $hash = password_hash($pw1, PASSWORD_BCRYPT);

        // Hash in config.php schreiben
        $configFile = dirname(__DIR__) . '/config.php';
        $content    = file_get_contents($configFile);
        $content    = preg_replace_callback(
            "/'admin_password_hash'\s*=>\s*'[^']*'/",
            fn() => "'admin_password_hash' => '" . $hash . "'",
            $content
        );

        if (file_put_contents($configFile, $content) !== false) {
            $success = true;
        } else {
            $error = 'Fehler beim Speichern. Bitte Schreibrechte auf config.php prüfen.';
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
    <title>Ersteinrichtung – <?= htmlspecialchars($siteName) ?></title>
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
        <h1 class="login-title">Ersteinrichtung</h1>
        <p class="login-desc">Lege ein Passwort für den Admin-Bereich fest.</p>

        <?php if ($success): ?>
        <div class="alert alert-success">
            Passwort erfolgreich gesetzt!
            <a href="<?= $baseUrl ?>/admin/index.php">Jetzt einloggen →</a>
        </div>
        <?php else: ?>

        <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= $baseUrl ?>/admin/setup.php">
            <div class="form-group">
                <label for="password">Neues Passwort</label>
                <input type="password" id="password" name="password" required minlength="8" autofocus>
            </div>
            <div class="form-group">
                <label for="password2">Passwort wiederholen</label>
                <input type="password" id="password2" name="password2" required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary btn-full">Passwort setzen</button>
        </form>

        <?php endif; ?>
    </div>
</div>
</body>
</html>
