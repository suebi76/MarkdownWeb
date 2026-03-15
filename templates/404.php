<!DOCTYPE html>
<html lang="de" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seite nicht gefunden – <?= htmlspecialchars($siteName) ?></title>
    <?php $baseUrl = rtrim($config->get('base_url', ''), '/'); ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/main.css">
    <style>:root { --color-accent: <?= htmlspecialchars($accentColor) ?>; }</style>
</head>
<body>

<div class="layout">

    <header class="mobile-header">
        <button class="icon-btn" id="menuBtn" aria-label="Navigation öffnen">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
                <rect x="1" y="4"  width="20" height="2" rx="1" fill="currentColor"/>
                <rect x="1" y="10" width="20" height="2" rx="1" fill="currentColor"/>
                <rect x="1" y="16" width="20" height="2" rx="1" fill="currentColor"/>
            </svg>
        </button>
        <a href="<?= $baseUrl ?>/" class="mobile-logo"><?= htmlspecialchars($siteName) ?></a>
        <button class="icon-btn theme-btn" id="themeBtnMobile" aria-label="Design wechseln">
            <?php include __DIR__ . '/partials/icon-theme.php'; ?>
        </button>
    </header>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-head">
            <a href="<?= $baseUrl ?>/" class="site-logo">
                <span class="site-logo-accent">M</span><?= htmlspecialchars(substr($siteName, 1)) ?>
            </a>
            <button class="icon-btn theme-btn" id="themeBtn" aria-label="Design wechseln">
                <?php include __DIR__ . '/partials/icon-theme.php'; ?>
            </button>
        </div>
        <nav class="nav-tree" id="navTree">
            <?php
            $navItemTemplate = __DIR__ . '/partials/nav-item.php';
            $currentPath = '';
            foreach ($navTree as $item) {
                include $navItemTemplate;
            }
            ?>
        </nav>
    </aside>

    <main class="main">
        <article class="page-content page-404">
            <h1>404 – Seite nicht gefunden</h1>
            <p>Die aufgerufene Seite existiert nicht.</p>
            <p><a href="<?= $baseUrl ?>/">← Zur Startseite</a></p>
        </article>
    </main>

</div>

<script>const SITE = { currentPath: '', searchUrl: <?= json_encode($baseUrl . '/_search') ?> };</script>
<script src="<?= $baseUrl ?>/assets/js/main.js"></script>
</body>
</html>
