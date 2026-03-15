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

// ── Datei speichern ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        adminRedirect('/admin/filetree.php', 'Sicherheitsfehler.', 'error');
    }

    $relPath     = trim($_POST['file'] ?? '');
    $fullPath    = realpath($contentDir . '/' . $relPath);
    $realContent = realpath($contentDir);

    if (
        $fullPath !== false &&
        $realContent !== false &&
        str_starts_with($fullPath, $realContent . DIRECTORY_SEPARATOR) &&
        is_file($fullPath) &&
        strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) === 'md'
    ) {
        $content = $_POST['content'] ?? '';
        // Normalize line endings: CRLF → LF
        $content = str_replace("\r\n", "\n", $content);
        $content = str_replace("\r", "\n", $content);

        file_put_contents($fullPath, $content);
        (new Navigation($config))->invalidateCache();
        (new Backlinks($config))->invalidateCache();
        adminRedirect('/admin/editor.php?file=' . rawurlencode($relPath), 'Datei gespeichert.', 'success');
    } else {
        adminRedirect('/admin/filetree.php', 'Fehler beim Speichern.', 'error');
    }
}

// ── Datei laden ──────────────────────────────────────────────────────────────
$relPath = trim($_GET['file'] ?? '');
if ($relPath === '') {
    adminRedirect('/admin/filetree.php', 'Keine Datei angegeben.', 'error');
}

$fullPath    = realpath($contentDir . '/' . $relPath);
$realContent = realpath($contentDir);

if (
    $fullPath === false ||
    $realContent === false ||
    !str_starts_with($fullPath, $realContent . DIRECTORY_SEPARATOR) ||
    !is_file($fullPath) ||
    strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) !== 'md'
) {
    adminRedirect('/admin/filetree.php', 'Datei nicht gefunden oder nicht editierbar.', 'error');
}

$fileContent = file_get_contents($fullPath);
$fileName    = basename($relPath);
$flash       = getFlash();
?>
<!DOCTYPE html>
<html lang="de" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($fileName) ?> – Editor – <?= htmlspecialchars($siteName) ?> Admin</title>
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
            <a href="<?= $baseUrl ?>/admin/reindex.php"   class="admin-nav-link">Suchindex</a>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="<?= $baseUrl ?>/" class="admin-nav-link" target="_blank">Website ansehen ↗</a>
            <a href="<?= $baseUrl ?>/admin/logout.php" class="admin-nav-link admin-nav-logout">Abmelden</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Editor</h1>
        </div>

        <div class="editor-breadcrumb">
            <a href="<?= $baseUrl ?>/admin/filetree.php">Dateimanager</a> / <?= htmlspecialchars($relPath) ?>
        </div>

        <?php if ($flash['message'] !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <div class="editor-actions">
            <button type="button" class="btn btn-primary" id="saveBtn">Speichern (Ctrl+S)</button>
            <button type="button" class="btn btn-outline" id="previewBtn">Vorschau</button>
            <a href="<?= $baseUrl ?>/admin/filetree.php" class="btn btn-outline">Abbrechen</a>
        </div>

        <div class="editor-container" id="editorContainer">
            <div class="editor-pane">
                <div class="editor-toolbar">
                    <button type="button" data-action="bold" title="Fett (Ctrl+B)"><b>F</b></button>
                    <button type="button" data-action="italic" title="Kursiv (Ctrl+I)"><i>K</i></button>
                    <span class="tb-sep"></span>
                    <button type="button" data-action="h2" title="Überschrift">H2</button>
                    <button type="button" data-action="h3" title="Überschrift">H3</button>
                    <span class="tb-sep"></span>
                    <button type="button" data-action="link" title="Link (Ctrl+K)">Link</button>
                    <button type="button" data-action="image" title="Bild">Bild</button>
                    <span class="tb-sep"></span>
                    <button type="button" data-action="code" title="Code-Block">Code</button>
                    <button type="button" data-action="ul" title="Liste">Liste</button>
                    <button type="button" data-action="ol" title="Nummerierte Liste">1. Liste</button>
                </div>
                <textarea class="editor-textarea" id="editor" spellcheck="true"><?= htmlspecialchars($fileContent) ?></textarea>
                <div class="editor-status">
                    <span id="editorStatus">Bereit</span>
                    <span id="editorInfo"></span>
                </div>
            </div>
            <div class="preview-pane" id="previewPane" style="display:none">
                <div id="previewContent" class="content-body"></div>
            </div>
        </div>

        <form method="post" id="saveForm" action="<?= $baseUrl ?>/admin/editor.php">
            <input type="hidden" name="action"     value="save">
            <input type="hidden" name="file"       value="<?= htmlspecialchars($relPath) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="content"    id="saveContent">
        </form>
    </main>
</div>

<script src="<?= $baseUrl ?>/assets/js/main.js"></script>
<script src="<?= $baseUrl ?>/assets/js/marked.min.js"></script>
<script>
(function() {
    const editor    = document.getElementById('editor');
    const saveBtn   = document.getElementById('saveBtn');
    const previewBtn = document.getElementById('previewBtn');
    const container = document.getElementById('editorContainer');
    const previewPane    = document.getElementById('previewPane');
    const previewContent = document.getElementById('previewContent');
    const saveForm    = document.getElementById('saveForm');
    const saveContent = document.getElementById('saveContent');
    const statusEl    = document.getElementById('editorStatus');
    const infoEl      = document.getElementById('editorInfo');

    let originalContent = editor.value;
    let isDirty = false;
    let previewVisible = false;

    // ── Dirty-Tracking ──
    function checkDirty() {
        const dirty = editor.value !== originalContent;
        if (dirty !== isDirty) {
            isDirty = dirty;
            editor.classList.toggle('unsaved', isDirty);
            statusEl.textContent = isDirty ? 'Ungespeicherte Änderungen' : 'Bereit';
        }
    }
    editor.addEventListener('input', function() {
        checkDirty();
        if (previewVisible) updatePreview();
    });

    // ── Browser-Warnung bei ungespeicherten Änderungen ──
    window.addEventListener('beforeunload', function(e) {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // ── Speichern ──
    function save() {
        saveContent.value = editor.value;
        isDirty = false;
        saveForm.submit();
    }
    saveBtn.addEventListener('click', save);

    // ── Vorschau ──
    function updatePreview() {
        if (typeof marked !== 'undefined' && marked.parse) {
            previewContent.innerHTML = marked.parse(editor.value);
        }
    }
    previewBtn.addEventListener('click', function() {
        previewVisible = !previewVisible;
        previewPane.style.display = previewVisible ? '' : 'none';
        container.classList.toggle('has-preview', previewVisible);
        previewBtn.classList.toggle('active', previewVisible);
        previewBtn.textContent = previewVisible ? 'Vorschau aus' : 'Vorschau';
        if (previewVisible) updatePreview();
    });

    // ── Toolbar ──
    function insertAround(before, after) {
        const start = editor.selectionStart;
        const end   = editor.selectionEnd;
        const sel   = editor.value.substring(start, end);
        const replacement = before + (sel || 'Text') + after;
        editor.setRangeText(replacement, start, end, 'select');
        editor.focus();
        checkDirty();
        if (previewVisible) updatePreview();
    }

    function insertAtLineStart(prefix) {
        const start = editor.selectionStart;
        const val   = editor.value;
        const lineStart = val.lastIndexOf('\n', start - 1) + 1;
        editor.setRangeText(prefix, lineStart, lineStart, 'end');
        editor.focus();
        checkDirty();
        if (previewVisible) updatePreview();
    }

    function insertBlock(block) {
        const start = editor.selectionStart;
        const end   = editor.selectionEnd;
        editor.setRangeText('\n' + block + '\n', start, end, 'end');
        editor.focus();
        checkDirty();
        if (previewVisible) updatePreview();
    }

    document.querySelector('.editor-toolbar').addEventListener('click', function(e) {
        const btn = e.target.closest('button');
        if (!btn) return;
        const action = btn.dataset.action;
        switch (action) {
            case 'bold':   insertAround('**', '**'); break;
            case 'italic': insertAround('*', '*'); break;
            case 'h2':     insertAtLineStart('## '); break;
            case 'h3':     insertAtLineStart('### '); break;
            case 'link':   insertAround('[', '](url)'); break;
            case 'image':  insertAround('![', '](url)'); break;
            case 'code':   insertBlock('```\nCode hier\n```'); break;
            case 'ul':     insertAtLineStart('- '); break;
            case 'ol':     insertAtLineStart('1. '); break;
        }
    });

    // ── Tastaturkürzel ──
    editor.addEventListener('keydown', function(e) {
        // Ctrl+S → Speichern
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            save();
            return;
        }
        // Ctrl+B → Fett
        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
            e.preventDefault();
            insertAround('**', '**');
            return;
        }
        // Ctrl+I → Kursiv
        if ((e.ctrlKey || e.metaKey) && e.key === 'i') {
            e.preventDefault();
            insertAround('*', '*');
            return;
        }
        // Ctrl+K → Link
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            insertAround('[', '](url)');
            return;
        }
        // Tab → 4 Spaces
        if (e.key === 'Tab' && !e.ctrlKey && !e.metaKey) {
            e.preventDefault();
            const start = this.selectionStart;
            const end   = this.selectionEnd;
            this.setRangeText('    ', start, end, 'end');
            checkDirty();
            if (previewVisible) updatePreview();
        }
    });

    // Ctrl+S global (auch außerhalb des Textareas)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            save();
        }
    });

    // ── Zeilen/Zeichen-Info ──
    function updateInfo() {
        const val   = editor.value;
        const lines = val.split('\n').length;
        const chars = val.length;
        infoEl.textContent = lines + ' Zeilen, ' + chars + ' Zeichen';
    }
    editor.addEventListener('input', updateInfo);
    updateInfo();
})();
</script>
</body>
</html>
