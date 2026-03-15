<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAuth();

use MarkdownWeb\Navigation;
use MarkdownWeb\Search;

$siteName    = $config->get('site_name', 'MarkdownWeb');
$accentColor = $config->get('accent_color', '#E50046');
$contentDir  = $config->get('content_dir');
$flash       = getFlash();

// Statistiken
function countFiles(string $dir, string $ext): int {
    $count   = 0;
    $entries = @scandir($dir);
    if ($entries === false) return 0;
    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $full = $dir . '/' . $entry;
        if (is_dir($full)) {
            $count += countFiles($full, $ext);
        } elseif (strtolower(pathinfo($entry, PATHINFO_EXTENSION)) === $ext) {
            $count++;
        }
    }
    return $count;
}

$mdCount    = countFiles($contentDir, 'md');
$mediaCount = 0;
$mediaExts  = ['jpg','jpeg','png','gif','webp','svg','pdf'];
foreach ($mediaExts as $ext) {
    $mediaCount += countFiles($contentDir, $ext);
}
$dbExists   = file_exists($config->get('cache_dir') . '/search.db');
?>
<!DOCTYPE html>
<html lang="de" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – <?= htmlspecialchars($siteName) ?> Admin</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
    <style>:root { --color-accent: <?= htmlspecialchars($accentColor) ?>; }</style>
</head>
<body class="admin-page">

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-sidebar-logo">
            <a href="<?= $baseUrl ?>/admin/dashboard.php">
                <span class="site-logo-accent">M</span><?= htmlspecialchars(substr($siteName, 1)) ?>
            </a>
            <span class="admin-badge">Admin</span>
        </div>
        <nav class="admin-nav">
            <a href="<?= $baseUrl ?>/admin/dashboard.php" class="admin-nav-link active">Dashboard</a>
            <a href="<?= $baseUrl ?>/admin/upload.php"    class="admin-nav-link">Dateien hochladen</a>
            <a href="<?= $baseUrl ?>/admin/filetree.php"  class="admin-nav-link">Dateimanager</a>
            <a href="<?= $baseUrl ?>/admin/reindex.php"   class="admin-nav-link">Suchindex</a>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="<?= $baseUrl ?>/" class="admin-nav-link" target="_blank">Website ansehen ↗</a>
            <a href="<?= $baseUrl ?>/admin/logout.php" class="admin-nav-link admin-nav-logout">Abmelden</a>
        </div>
    </aside>

    <!-- Hauptbereich -->
    <main class="admin-main">
        <div class="admin-header">
            <h1>Dashboard</h1>
        </div>

        <?php if ($flash['message'] !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <!-- Statistik-Karten -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $mdCount ?></div>
                <div class="stat-label">Markdown-Seiten</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $mediaCount ?></div>
                <div class="stat-label">Mediendateien</div>
            </div>
            <div class="stat-card">
                <div class="stat-number <?= $dbExists ? 'text-success' : 'text-warning' ?>">
                    <?= $dbExists ? 'OK' : '–' ?>
                </div>
                <div class="stat-label">Suchindex</div>
            </div>
        </div>

        <!-- Schnellaktionen -->
        <h2 class="admin-section-title">Schnellaktionen</h2>
        <div class="action-grid">
            <a href="<?= $baseUrl ?>/admin/upload.php" class="action-card">
                <div class="action-icon">↑</div>
                <div class="action-title">Dateien hochladen</div>
                <div class="action-desc">.md, Bilder oder ZIP-Archiv hochladen</div>
            </a>
            <a href="<?= $baseUrl ?>/admin/filetree.php" class="action-card">
                <div class="action-icon">📁</div>
                <div class="action-title">Dateimanager</div>
                <div class="action-desc">Dateien verwalten, umbenennen, löschen</div>
            </a>
            <a href="<?= $baseUrl ?>/admin/reindex.php" class="action-card">
                <div class="action-icon">⟳</div>
                <div class="action-title">Suchindex aufbauen</div>
                <div class="action-desc">Volltext-Suchindex neu generieren</div>
            </a>
        </div>
    </main>
</div>

<script src="<?= $baseUrl ?>/assets/js/main.js"></script>
</body>
</html>
