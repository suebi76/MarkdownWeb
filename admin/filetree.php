<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAuth();

use MarkdownWeb\Navigation;
use MarkdownWeb\Backlinks;

$siteName    = $config->get('site_name', 'MarkdownWeb');
$accentColor = $config->get('accent_color', '#E50046');
$contentDir  = $config->get('content_dir');
$csrfToken   = generateCsrfToken();

// ── Datei löschen ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        adminRedirect('/admin/filetree.php', 'Sicherheitsfehler.', 'error');
    }

    $relPath  = trim($_POST['path'] ?? '');
    $fullPath = realpath($contentDir . '/' . $relPath);
    $realContent = realpath($contentDir);

    if (
        $fullPath !== false &&
        $realContent !== false &&
        str_starts_with($fullPath, $realContent . DIRECTORY_SEPARATOR) &&
        is_file($fullPath)
    ) {
        unlink($fullPath);
        (new Navigation($config))->invalidateCache();
        (new Backlinks($config))->invalidateCache();
        adminRedirect('/admin/filetree.php', 'Datei gelöscht.', 'success');
    } else {
        adminRedirect('/admin/filetree.php', 'Datei nicht gefunden.', 'error');
    }
}

// ── Datei umbenennen ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'rename') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        adminRedirect('/admin/filetree.php', 'Sicherheitsfehler.', 'error');
    }

    $relPath  = trim($_POST['path'] ?? '');
    $newName  = trim($_POST['new_name'] ?? '');
    $newName  = preg_replace('/[^\w\s\-\.äöüÄÖÜß]/u', '_', basename($newName)) ?? '';

    $fullPath    = realpath($contentDir . '/' . $relPath);
    $realContent = realpath($contentDir);

    if (
        $fullPath !== false &&
        $realContent !== false &&
        str_starts_with($fullPath, $realContent . DIRECTORY_SEPARATOR) &&
        is_file($fullPath) &&
        $newName !== ''
    ) {
        $newPath = dirname($fullPath) . '/' . $newName;
        rename($fullPath, $newPath);
        (new Navigation($config))->invalidateCache();
        (new Backlinks($config))->invalidateCache();
        adminRedirect('/admin/filetree.php', "Umbenannt in \"$newName\".", 'success');
    } else {
        adminRedirect('/admin/filetree.php', 'Fehler beim Umbenennen.', 'error');
    }
}

// ── Dateibaum bauen ───────────────────────────────────────────────────────────
function buildFileTree(string $dir, string $contentDir): array
{
    $entries = @scandir($dir);
    if ($entries === false) return [];

    $result = [];
    $dirs   = [];
    $files  = [];

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) continue;
        $full = $dir . '/' . $entry;
        is_dir($full) ? $dirs[] = $entry : $files[] = $entry;
    }

    sort($dirs);
    sort($files);

    foreach ($dirs as $d) {
        $children = buildFileTree($dir . '/' . $d, $contentDir);
        if (!empty($children)) {
            $result[] = ['type' => 'dir', 'name' => $d, 'children' => $children];
        }
    }

    foreach ($files as $f) {
        $full    = $dir . '/' . $f;
        $relPath = str_replace('\\', '/', substr($full, strlen($contentDir) + 1));
        $result[] = [
            'type'    => 'file',
            'name'    => $f,
            'path'    => $relPath,
            'size'    => filesize($full),
            'ext'     => strtolower(pathinfo($f, PATHINFO_EXTENSION)),
        ];
    }

    return $result;
}

function renderFileTree(array $items, string $csrfToken, string $baseUrl): void
{
    foreach ($items as $item) {
        if ($item['type'] === 'dir') {
            echo '<details class="ftree-folder" open>';
            echo '<summary class="ftree-folder-name">📁 ' . htmlspecialchars($item['name']) . '</summary>';
            echo '<div class="ftree-children">';
            renderFileTree($item['children'], $csrfToken, $baseUrl);
            echo '</div></details>';
        } else {
            $icon = match($item['ext']) {
                'md'   => '📄',
                'jpg','jpeg','png','gif','webp','svg' => '🖼',
                'pdf'  => '📕',
                default => '📎',
            };
            $sizeStr  = formatBytes((int)$item['size']);
            $safePath = htmlspecialchars($item['path']);
            $safeName = htmlspecialchars($item['name']);
            $safeCsrf = htmlspecialchars($csrfToken);
            $safeBase = htmlspecialchars($baseUrl);

            $editBtn = '';
            if ($item['ext'] === 'md') {
                $editHref = $safeBase . '/admin/editor.php?file=' . rawurlencode($item['path']);
                $editBtn  = "<a href=\"$editHref\" class=\"btn btn-sm btn-outline\">Bearbeiten</a>";
            }

            echo <<<HTML
            <div class="ftree-file">
                <span class="ftree-file-name">$icon $safeName</span>
                <span class="ftree-file-size">$sizeStr</span>
                <div class="ftree-file-actions">
                    $editBtn
                    <button class="btn btn-sm btn-outline rename-btn"
                        data-path="$safePath" data-name="$safeName">
                        Umbenennen
                    </button>
                    <form method="post" action="$safeBase/admin/filetree.php" class="inline-form"
                        onsubmit="return confirm('&quot;$safeName&quot; wirklich löschen?')">
                        <input type="hidden" name="action"     value="delete">
                        <input type="hidden" name="path"       value="$safePath">
                        <input type="hidden" name="csrf_token" value="$safeCsrf">
                        <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                    </form>
                </div>
            </div>
            HTML;
        }
    }
}

function formatBytes(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

$tree  = buildFileTree($contentDir, $contentDir);
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="de" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dateimanager – <?= htmlspecialchars($siteName) ?> Admin</title>
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
            <a href="<?= $baseUrl ?>/admin/filetree.php"  class="admin-nav-link active">Dateimanager</a>
            <a href="<?= $baseUrl ?>/admin/reindex.php"   class="admin-nav-link">Suchindex</a>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="<?= $baseUrl ?>/" class="admin-nav-link" target="_blank">Website ansehen ↗</a>
            <a href="<?= $baseUrl ?>/admin/logout.php" class="admin-nav-link admin-nav-logout">Abmelden</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Dateimanager</h1>
            <a href="<?= $baseUrl ?>/admin/upload.php" class="btn btn-primary">+ Hochladen</a>
        </div>

        <?php if ($flash['message'] !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <div class="file-tree-wrap">
            <?php if (empty($tree)): ?>
                <p class="empty-state">Noch keine Dateien. <a href="<?= $baseUrl ?>/admin/upload.php">Jetzt hochladen →</a></p>
            <?php else: ?>
                <?php renderFileTree($tree, $csrfToken, $baseUrl); ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Umbenennen-Modal -->
<div class="modal" id="renameModal" hidden>
    <div class="modal-backdrop" id="renameBackdrop"></div>
    <div class="modal-box">
        <h2 class="modal-title">Datei umbenennen</h2>
        <form method="post" action="<?= $baseUrl ?>/admin/filetree.php">
            <input type="hidden" name="action"     value="rename">
            <input type="hidden" name="path"       id="renamePath">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="form-group">
                <label for="renameInput">Neuer Name</label>
                <input type="text" id="renameInput" name="new_name" class="form-input" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" id="renameCancelBtn">Abbrechen</button>
                <button type="submit" class="btn btn-primary">Umbenennen</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= $baseUrl ?>/assets/js/main.js"></script>
<script>
document.querySelectorAll('.rename-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('renamePath').value  = btn.dataset.path;
        document.getElementById('renameInput').value = btn.dataset.name;
        document.getElementById('renameModal').removeAttribute('hidden');
        document.getElementById('renameInput').focus();
    });
});
document.getElementById('renameCancelBtn')?.addEventListener('click', () => {
    document.getElementById('renameModal').setAttribute('hidden', '');
});
document.getElementById('renameBackdrop')?.addEventListener('click', () => {
    document.getElementById('renameModal').setAttribute('hidden', '');
});
</script>
</body>
</html>
