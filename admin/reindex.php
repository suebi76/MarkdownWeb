<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAuth();

use MarkdownWeb\Search;
use MarkdownWeb\Navigation;
use MarkdownWeb\Backlinks;

$siteName    = $config->get('site_name', 'MarkdownWeb');
$accentColor = $config->get('accent_color', '#E50046');
$csrfToken   = generateCsrfToken();
$result      = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $result = ['success' => false, 'error' => 'Sicherheitsfehler.'];
    } else {
        $search = new Search($config);
        $result = $search->buildIndex();
        // Auch Navigation und Backlinks neu aufbauen
        (new Navigation($config))->invalidateCache();
        (new Backlinks($config))->invalidateCache();
    }
}

$flash   = getFlash();
$dbPath  = $config->get('cache_dir') . '/search.db';
$dbSize  = file_exists($dbPath) ? filesize($dbPath) : 0;

function formatBytes(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}
?>
<!DOCTYPE html>
<html lang="de" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suchindex – <?= htmlspecialchars($siteName) ?> Admin</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
    <style>:root { --color-accent: <?= htmlspecialchars($accentColor) ?>; }</style>
</head>
<body class="admin-page">
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-logo">
            <a href="<?= $baseUrl ?>/admin/dashboard.php">
                <span class="site-logo-accent">M</span><?= htmlspecialchars(substr($siteName, 1)) ?>
            </a>
            <span class="admin-badge">Admin</span>
        </div>
        <nav class="admin-nav">
            <a href="<?= $baseUrl ?>/admin/dashboard.php" class="admin-nav-link">Dashboard</a>
            <a href="<?= $baseUrl ?>/admin/upload.php"    class="admin-nav-link">Dateien hochladen</a>
            <a href="<?= $baseUrl ?>/admin/filetree.php"  class="admin-nav-link">Dateimanager</a>
            <a href="<?= $baseUrl ?>/admin/reindex.php"   class="admin-nav-link active">Suchindex</a>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="<?= $baseUrl ?>/" class="admin-nav-link" target="_blank">Website ansehen ↗</a>
            <a href="<?= $baseUrl ?>/admin/logout.php" class="admin-nav-link admin-nav-logout">Abmelden</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Suchindex</h1>
        </div>

        <?php if ($flash['message'] !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <?php if ($result !== null): ?>
            <?php if ($result['success']): ?>
            <div class="alert alert-success">
                Suchindex erfolgreich aufgebaut – <?= (int) $result['indexed'] ?> Seite(n) indexiert.
                <?php if (!empty($result['mode'])): ?>
                <br><small>Modus: <?= htmlspecialchars($result['mode']) ?></small>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-error">
                Fehler: <?= htmlspecialchars($result['error'] ?? 'Unbekannter Fehler') ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="info-card">
            <table class="info-table">
                <tr>
                    <th>Datenbank</th>
                    <td><?= file_exists($dbPath) ? 'Vorhanden' : 'Noch nicht erstellt' ?></td>
                </tr>
                <?php if ($dbSize > 0): ?>
                <tr>
                    <th>Größe</th>
                    <td><?= htmlspecialchars(formatBytes($dbSize)) ?></td>
                </tr>
                <tr>
                    <th>Letzte Aktualisierung</th>
                    <td><?= date('d.m.Y H:i', filemtime($dbPath)) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <form method="post" action="<?= $baseUrl ?>/admin/reindex.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <button type="submit" class="btn btn-primary" id="reindexBtn">
                Suchindex neu aufbauen
            </button>
        </form>

        <p class="form-hint" style="margin-top: 12px;">
            Der Suchindex wird nach jedem Datei-Upload automatisch invalidiert.
            Klicke hier, um ihn manuell neu aufzubauen.
        </p>
    </main>
</div>
<script src="<?= $baseUrl ?>/assets/js/main.js"></script>
</body>
</html>
