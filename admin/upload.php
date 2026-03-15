<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
requireAuth();

use MarkdownWeb\Navigation;
use MarkdownWeb\Backlinks;
use MarkdownWeb\Search;

$siteName    = $config->get('site_name', 'MarkdownWeb');
$accentColor = $config->get('accent_color', '#E50046');
$contentDir  = $config->get('content_dir');
$allowedExt  = $config->get('allowed_upload', []);
$maxSize     = (int) $config->get('max_upload_size', 52428800);
$csrfToken   = generateCsrfToken();
$messages    = [];

// ── Upload verarbeiten ────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $messages[] = ['type' => 'error', 'text' => 'Sicherheitsfehler. Bitte Seite neu laden.'];
    } else {
        $targetSubDir = sanitizePath($_POST['target_dir'] ?? '');
        $targetDir    = $contentDir . ($targetSubDir ? '/' . $targetSubDir : '');

        if (!ensureDirectory($targetDir)) {
            $messages[] = ['type' => 'error', 'text' => 'Zielverzeichnis konnte nicht erstellt werden.'];
        } elseif (!empty($_FILES['files']['name'][0])) {
            foreach ($_FILES['files']['name'] as $i => $name) {
                if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) {
                    $messages[] = ['type' => 'error', 'text' => "Fehler bei: " . htmlspecialchars($name)];
                    continue;
                }

                $size = (int) $_FILES['files']['size'][$i];
                $tmp  = $_FILES['files']['tmp_name'][$i];
                $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if ($size > $maxSize) {
                    $messages[] = ['type' => 'error', 'text' => "\"$name\" ist zu groß (max. " . formatBytes($maxSize) . ")."];
                    continue;
                }

                if (!in_array($ext, $allowedExt, true)) {
                    $messages[] = ['type' => 'error', 'text' => "\"$name\": Dateityp nicht erlaubt."];
                    continue;
                }

                if ($ext === 'zip') {
                    $result = extractZip($tmp, $contentDir, $targetSubDir, $allowedExt, $maxSize);
                    $messages = array_merge($messages, $result);
                } else {
                    $safeName = sanitizeFilename($name);
                    $dest     = $targetDir . '/' . $safeName;
                    if (move_uploaded_file($tmp, $dest)) {
                        $messages[] = ['type' => 'success', 'text' => "\"$safeName\" erfolgreich hochgeladen."];
                    } else {
                        $messages[] = ['type' => 'error', 'text' => "Fehler beim Speichern von \"$safeName\"."];
                    }
                }
            }

            // Caches invalidieren
            (new Navigation($config))->invalidateCache();
            (new Backlinks($config))->invalidateCache();
        }
    }
}

// ── Hilfsfunktionen ───────────────────────────────────────────────────────────

function sanitizePath(string $path): string
{
    $path = str_replace(['..', "\0"], '', $path);
    $path = trim($path, '/\\');
    $path = preg_replace('/[^\w\s\-\.\/äöüÄÖÜß]/u', '_', $path) ?? '';
    return $path;
}

function sanitizeFilename(string $name): string
{
    $name = basename($name);
    $name = preg_replace('/[^\w\s\-\.äöüÄÖÜß]/u', '_', $name) ?? $name;
    return $name;
}

function ensureDirectory(string $dir): bool
{
    if (is_dir($dir)) return true;
    return mkdir($dir, 0755, true);
}

function formatBytes(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

function extractZip(string $tmpFile, string $contentDir, string $targetSubDir, array $allowedExt, int $maxSize): array
{
    $messages = [];
    $zip      = new ZipArchive();

    if ($zip->open($tmpFile) !== true) {
        return [['type' => 'error', 'text' => 'ZIP-Archiv konnte nicht geöffnet werden.']];
    }

    $extracted = 0;
    $skipped   = 0;
    $baseDir   = $contentDir . ($targetSubDir ? '/' . $targetSubDir : '');

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entryName = $zip->getNameIndex($i);
        if ($entryName === false) continue;

        // Sicherheit: Directory Traversal verhindern
        if (str_contains($entryName, '..') || str_contains($entryName, "\0")) {
            $skipped++;
            continue;
        }

        // Verzeichnisse überspringen (werden bei Dateien auto-erstellt)
        if (str_ends_with($entryName, '/')) continue;

        $ext = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true) || $ext === 'zip') {
            $skipped++;
            continue;
        }

        $stat = $zip->statIndex($i);
        if ($stat !== false && $stat['size'] > $maxSize) {
            $skipped++;
            continue;
        }

        // Pfad aufbauen
        $parts    = explode('/', $entryName);
        $fileName = array_pop($parts);
        $subDir   = implode('/', $parts);

        $destDir  = $baseDir . ($subDir ? '/' . $subDir : '');
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $safeName = sanitizeFilename($fileName);
        $destPath = $destDir . '/' . $safeName;

        $content = $zip->getFromIndex($i);
        if ($content !== false && file_put_contents($destPath, $content) !== false) {
            $extracted++;
        } else {
            $skipped++;
        }
    }

    $zip->close();

    if ($extracted > 0) {
        $messages[] = ['type' => 'success', 'text' => "ZIP extrahiert: $extracted Datei(en) importiert."];
    }
    if ($skipped > 0) {
        $messages[] = ['type' => 'warning', 'text' => "$skipped Datei(en) übersprungen (Typ/Größe nicht erlaubt)."];
    }

    return $messages;
}

// ── Unterordner-Liste ─────────────────────────────────────────────────────────

function getSubdirectories(string $dir, string $base = '', int $depth = 0): array
{
    $result  = [];
    $entries = @scandir($dir);
    if ($entries === false || $depth > 5) return $result;

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) continue;
        $full = $dir . '/' . $entry;
        if (is_dir($full)) {
            $rel      = $base ? $base . '/' . $entry : $entry;
            $result[] = $rel;
            $result   = array_merge($result, getSubdirectories($full, $rel, $depth + 1));
        }
    }
    return $result;
}

$subdirs = getSubdirectories($contentDir);
$flash   = getFlash();
?>
<!DOCTYPE html>
<html lang="de" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload – <?= htmlspecialchars($siteName) ?> Admin</title>
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
            <a href="<?= $baseUrl ?>/admin/upload.php"    class="admin-nav-link active">Dateien hochladen</a>
            <a href="<?= $baseUrl ?>/admin/filetree.php"  class="admin-nav-link">Dateimanager</a>
            <a href="<?= $baseUrl ?>/admin/reindex.php"   class="admin-nav-link">Suchindex</a>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="<?= $baseUrl ?>/" class="admin-nav-link" target="_blank">Website ansehen ↗</a>
            <a href="<?= $baseUrl ?>/admin/logout.php" class="admin-nav-link admin-nav-logout">Abmelden</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Dateien hochladen</h1>
        </div>

        <?php if ($flash['message'] !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <?php foreach ($messages as $msg): ?>
        <div class="alert alert-<?= htmlspecialchars($msg['type']) ?>"><?= htmlspecialchars($msg['text']) ?></div>
        <?php endforeach; ?>

        <form method="post" enctype="multipart/form-data" action="<?= $baseUrl ?>/admin/upload.php" id="uploadForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label for="target_dir">Zielordner</label>
                <select name="target_dir" id="target_dir" class="form-select">
                    <option value="">(Stammverzeichnis)</option>
                    <?php foreach ($subdirs as $dir): ?>
                    <option value="<?= htmlspecialchars($dir) ?>"><?= htmlspecialchars($dir) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="form-hint">Oder neuen Ordnernamen eingeben:</p>
                <input type="text" name="target_dir" id="target_dir_new" class="form-input" placeholder="z. B. Handbuch/Kapitel-1">
            </div>

            <div class="upload-area" id="uploadArea">
                <div class="upload-area-inner">
                    <div class="upload-icon">↑</div>
                    <p><strong>Dateien hierher ziehen</strong> oder klicken zum Auswählen</p>
                    <p class="upload-hint">
                        Erlaubt: .md, .jpg, .jpeg, .png, .gif, .webp, .svg, .pdf, .zip<br>
                        Max. <?= htmlspecialchars(formatBytes($maxSize)) ?> pro Datei
                    </p>
                    <input
                        type="file"
                        id="fileInput"
                        name="files[]"
                        multiple
                        accept=".md,.jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.zip,.mp4,.mp3"
                        class="upload-file-input"
                    >
                </div>
            </div>

            <div id="fileList" class="file-list"></div>

            <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                Hochladen
            </button>
        </form>
    </main>
</div>

<script src="<?= $baseUrl ?>/assets/js/main.js"></script>
<script>
// Drag & Drop + Dateiliste
const area     = document.getElementById('uploadArea');
const input    = document.getElementById('fileInput');
const fileList = document.getElementById('fileList');
const btn      = document.getElementById('uploadBtn');
// "Neuer Ordner"-Input überschreibt Select
const sel = document.getElementById('target_dir');
const newDir = document.getElementById('target_dir_new');
newDir.addEventListener('input', () => { sel.disabled = newDir.value !== ''; });

area.addEventListener('click', () => input.click());
area.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('drag-over'); });
area.addEventListener('dragleave', () => area.classList.remove('drag-over'));
area.addEventListener('drop', e => {
    e.preventDefault();
    area.classList.remove('drag-over');
    input.files = e.dataTransfer.files;
    updateFileList();
});
input.addEventListener('change', updateFileList);

function updateFileList() {
    const files = Array.from(input.files);
    btn.disabled = files.length === 0;
    fileList.innerHTML = files.map(f =>
        `<div class="file-list-item">
            <span class="file-list-name">${escHtml(f.name)}</span>
            <span class="file-list-size">${formatBytes(f.size)}</span>
        </div>`
    ).join('');
}

function formatBytes(b) {
    if (b >= 1048576) return (b/1048576).toFixed(1)+' MB';
    if (b >= 1024)    return (b/1024).toFixed(1)+' KB';
    return b+' B';
}
function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
</body>
</html>
