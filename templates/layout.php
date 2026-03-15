<!DOCTYPE html>
<html lang="de" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> – <?= htmlspecialchars($siteName) ?></title>
    <?php if (!empty($pageData['description'])): ?>
    <meta name="description" content="<?= htmlspecialchars($pageData['description']) ?>">
    <?php endif; ?>
    <meta name="author" content="<?= htmlspecialchars($config->get('author', '')) ?>">
    <meta name="robots" content="index, follow">
    <!-- Kein externes CSS/JS – DSGVO-konform -->
    <?php $baseUrl = rtrim($config->get('base_url', ''), '/'); ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/main.css">
    <style>:root { --color-accent: <?= htmlspecialchars($accentColor) ?>; }</style>
</head>
<body>

<div class="layout">

    <!-- ===== Mobile Header ===== -->
    <header class="mobile-header" id="mobileHeader">
        <button class="icon-btn" id="menuBtn" aria-label="Navigation öffnen" aria-expanded="false" aria-controls="sidebar">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" aria-hidden="true">
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

    <!-- ===== Sidebar Overlay (Mobile) ===== -->
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    <!-- ===== Linke Sidebar ===== -->
    <aside class="sidebar" id="sidebar" aria-label="Seitennavigation">
        <div class="sidebar-head">
            <a href="<?= $baseUrl ?>/" class="site-logo">
                <span class="site-logo-accent">M</span><?= htmlspecialchars(substr($siteName, 1)) ?>
            </a>
            <button class="icon-btn theme-btn" id="themeBtn" aria-label="Design wechseln" title="Hell/Dunkel wechseln">
                <?php include __DIR__ . '/partials/icon-theme.php'; ?>
            </button>
        </div>

        <div class="search-bar">
            <button class="search-trigger" id="searchTrigger" aria-label="Suche öffnen">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <circle cx="6.5" cy="6.5" r="5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M10.5 10.5L14 14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <span>Suchen…</span>
                <kbd>Strg&thinsp;K</kbd>
            </button>
        </div>

        <nav class="nav-tree" id="navTree" aria-label="Dokumentation">
            <?php
            $navItemTemplate = __DIR__ . '/partials/nav-item.php';
            foreach ($navTree as $item) {
                include $navItemTemplate;
            }
            ?>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= $baseUrl ?>/admin/" class="sidebar-admin-link" title="Admin-Bereich">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
                    <circle cx="7" cy="4" r="2.5" stroke="currentColor" stroke-width="1.2"/>
                    <path d="M2 12.5C2 10 4.2 8 7 8s5 2 5 4.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                </svg>
                Admin
            </a>
            <span class="sidebar-credit">MarkdownWeb by <?= htmlspecialchars($config->get('author', '')) ?> · <a href="https://creativecommons.org/licenses/by/4.0/" target="_blank" rel="noopener">CC BY 4.0</a></span>
        </div>
    </aside>

    <!-- ===== Hauptinhalt ===== -->
    <main class="main" id="main">
        <article
            class="page-content <?= implode(' ', array_map('htmlspecialchars', $pageData['css_classes'] ?? [])) ?>"
            id="pageContent"
        >
            <?= $pageData['html'] ?>
        </article>

        <?php if (!empty($pageBacklinks)): ?>
        <section class="backlinks" aria-label="Verlinkende Seiten">
            <h2 class="backlinks-heading">Links zu dieser Seite</h2>
            <ul class="backlinks-list">
                <?php foreach ($pageBacklinks as $bl): ?>
                <li>
                    <a href="<?= htmlspecialchars($baseUrl . $bl['path']) ?>">
                        <?= htmlspecialchars($bl['title']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <footer class="page-footer">
            <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($config->get('author', '')) ?></p>
        </footer>
    </main>

    <!-- ===== Rechte TOC-Sidebar ===== -->
    <?php if (!empty($pageData['toc'])): ?>
    <aside class="toc-sidebar" aria-label="Inhaltsverzeichnis">
        <div class="toc-inner">
            <p class="toc-label">Auf dieser Seite</p>
            <nav class="toc-nav" id="tocNav">
                <ul>
                <?php foreach ($pageData['toc'] as $heading): ?>
                    <li class="toc-level-<?= (int) $heading['level'] ?>">
                        <a href="#<?= htmlspecialchars($heading['id']) ?>">
                            <?= htmlspecialchars($heading['text']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </aside>
    <?php endif; ?>

</div><!-- .layout -->

<!-- ===== Such-Modal ===== -->
<div class="search-modal" id="searchModal" role="dialog" aria-modal="true" aria-label="Suche" hidden>
    <div class="search-modal-backdrop" id="searchBackdrop"></div>
    <div class="search-modal-box">
        <div class="search-input-row">
            <svg class="search-icon" width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true">
                <circle cx="7.5" cy="7.5" r="5.5" stroke="currentColor" stroke-width="1.6"/>
                <path d="M12 12L16 16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            <input
                type="search"
                id="searchInput"
                class="search-input"
                placeholder="Suchen…"
                autocomplete="off"
                spellcheck="false"
                aria-label="Suchbegriff eingeben"
            >
            <kbd class="search-esc-hint">Esc</kbd>
        </div>
        <div class="search-results" id="searchResults" aria-live="polite">
            <p class="search-empty">Suchbegriff eingeben…</p>
        </div>
    </div>
</div>

<script>
const SITE = {
    currentPath: <?= json_encode($currentPath) ?>,
    searchUrl: <?= json_encode($baseUrl . '/_search') ?>
};
</script>
<script src="<?= $baseUrl ?>/assets/js/main.js"></script>
<script src="<?= $baseUrl ?>/assets/js/search.js"></script>
</body>
</html>
