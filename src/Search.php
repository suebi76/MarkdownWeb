<?php
declare(strict_types=1);

namespace MarkdownWeb;

use PDO;

class Search
{
    private Config $config;
    private string $dbPath;
    private ?PDO $pdo = null;
    private bool $hasFts5 = false;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->dbPath = $config->get('cache_dir') . '/search.db';
    }

    public function search(string $query): array
    {
        if (!file_exists($this->dbPath)) {
            return [];
        }

        try {
            $db    = $this->getDb();
            $limit = (int) $this->config->get('search_results_limit', 10);

            if ($this->hasFts5) {
                return $this->searchFts5($db, $query, $limit);
            }
            return $this->searchLike($db, $query, $limit);
        } catch (\Throwable) {
            return [];
        }
    }

    public function buildIndex(): array
    {
        if (!extension_loaded('pdo_sqlite')) {
            return ['success' => false, 'error' => 'PHP-Erweiterung pdo_sqlite ist nicht verfügbar. Bitte beim Hoster aktivieren lassen.'];
        }

        $cacheDir = $this->config->get('cache_dir');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        try {
            // Alte DB löschen und neu aufbauen
            if (file_exists($this->dbPath)) {
                unlink($this->dbPath);
                $this->pdo = null;
            }

            $db = $this->getDb();

            $renderer   = new MarkdownRenderer($this->config);
            $contentDir = $this->config->get('content_dir');
            $count      = $this->indexDirectory($contentDir, $contentDir, $renderer, $db);

            $mode = $this->hasFts5 ? 'FTS5-Volltextsuche' : 'LIKE-Suche (FTS5 nicht verfügbar)';
            return ['success' => true, 'indexed' => $count, 'mode' => $mode];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------

    private function getDb(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = new PDO('sqlite:' . $this->dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->initSchema();
        }
        return $this->pdo;
    }

    private function initSchema(): void
    {
        // Prüfen ob FTS5 verfügbar ist
        $this->hasFts5 = $this->checkFts5();

        if ($this->hasFts5) {
            $this->pdo->exec('
                CREATE VIRTUAL TABLE IF NOT EXISTS pages USING fts5(
                    path     UNINDEXED,
                    title,
                    content,
                    headings,
                    tokenize = "unicode61"
                )
            ');
        } else {
            $this->pdo->exec('
                CREATE TABLE IF NOT EXISTS pages (
                    id       INTEGER PRIMARY KEY AUTOINCREMENT,
                    path     TEXT NOT NULL,
                    title    TEXT NOT NULL DEFAULT "",
                    content  TEXT NOT NULL DEFAULT "",
                    headings TEXT NOT NULL DEFAULT ""
                )
            ');
            $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_pages_title ON pages(title)');
        }
    }

    private function checkFts5(): bool
    {
        try {
            // Testtabelle erstellen um FTS5-Verfügbarkeit zu prüfen
            $this->pdo->exec('CREATE VIRTUAL TABLE IF NOT EXISTS _fts5_test USING fts5(test)');
            $this->pdo->exec('DROP TABLE IF EXISTS _fts5_test');
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function searchFts5(PDO $db, string $query, int $limit): array
    {
        $fts  = $this->buildFtsQuery($query);
        $stmt = $db->prepare('
            SELECT
                path,
                title,
                snippet(pages, 2, "<mark>", "</mark>", "…", 30) AS excerpt
            FROM pages
            WHERE pages MATCH ?
            ORDER BY rank
            LIMIT ?
        ');
        $stmt->execute([$fts, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function searchLike(PDO $db, string $query, int $limit): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        $words      = preg_split('/\s+/', $query) ?: [];
        $conditions = [];
        $params     = [];

        foreach ($words as $word) {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $word) . '%';
            $conditions[] = '(title LIKE ? ESCAPE "\\" OR content LIKE ? ESCAPE "\\" OR headings LIKE ? ESCAPE "\\")';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $where = implode(' AND ', $conditions);
        $params[] = $limit;

        $stmt = $db->prepare("
            SELECT path, title, '' AS excerpt
            FROM pages
            WHERE $where
            LIMIT ?
        ");
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Einfache Excerpt-Generierung für LIKE-Modus
        foreach ($results as &$row) {
            $row['excerpt'] = $this->generateExcerpt($db, $row['path'], $words);
        }

        return $results;
    }

    private function generateExcerpt(PDO $db, string $path, array $words): string
    {
        $stmt = $db->prepare('SELECT content FROM pages WHERE path = ? LIMIT 1');
        $stmt->execute([$path]);
        $content = $stmt->fetchColumn();

        if (!$content) {
            return '';
        }

        // Ersten Treffer im Text finden und Umgebung extrahieren
        $lowerContent = mb_strtolower($content);
        $bestPos = mb_strlen($content);

        foreach ($words as $word) {
            $pos = mb_strpos($lowerContent, mb_strtolower($word));
            if ($pos !== false && $pos < $bestPos) {
                $bestPos = $pos;
            }
        }

        $start  = max(0, $bestPos - 60);
        $length = 150;
        $excerpt = mb_substr($content, $start, $length);

        if ($start > 0) {
            $excerpt = '…' . $excerpt;
        }
        if ($start + $length < mb_strlen($content)) {
            $excerpt .= '…';
        }

        // Suchbegriffe hervorheben
        foreach ($words as $word) {
            $excerpt = preg_replace(
                '/(' . preg_quote($word, '/') . ')/iu',
                '<mark>$1</mark>',
                $excerpt
            ) ?? $excerpt;
        }

        return $excerpt;
    }

    private function indexDirectory(
        string $dir,
        string $baseDir,
        MarkdownRenderer $renderer,
        PDO $db
    ): int {
        $entries = @scandir($dir);
        if ($entries === false) {
            return 0;
        }

        $count = 0;
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
                continue;
            }
            $full = $dir . '/' . $entry;
            if (is_dir($full)) {
                $count += $this->indexDirectory($full, $baseDir, $renderer, $db);
            } elseif (strtolower(pathinfo($entry, PATHINFO_EXTENSION)) === 'md') {
                $this->indexFile($full, $baseDir, $renderer, $db);
                $count++;
            }
        }

        return $count;
    }

    private function indexFile(
        string $filePath,
        string $baseDir,
        MarkdownRenderer $renderer,
        PDO $db
    ): void {
        $relPath  = str_replace('\\', '/', substr($filePath, strlen($baseDir) + 1));
        $homeFile = $this->config->get('home_file', 'Home.md');
        $urlPath  = ($relPath === $homeFile) ? '/' : '/' . substr($relPath, 0, -3);

        $pageData  = $renderer->render($filePath);
        $plainText = $renderer->extractPlainText($filePath);
        $title     = $pageData['title'] ?? pathinfo($filePath, PATHINFO_FILENAME);
        $headings  = implode(' ', array_column($pageData['toc'], 'text'));

        $stmt = $db->prepare('INSERT INTO pages (path, title, content, headings) VALUES (?, ?, ?, ?)');
        $stmt->execute([$urlPath, $title, $plainText, $headings]);
    }

    private function buildFtsQuery(string $query): string
    {
        $query = preg_replace('/[^\w\s\-äöüÄÖÜß]/u', ' ', $query) ?? '';
        $query = trim($query);

        if ($query === '') {
            return '""';
        }

        $words = preg_split('/\s+/', $query) ?: [];
        $parts = array_map(static fn(string $w): string => '"' . $w . '"*', $words);

        return implode(' ', $parts);
    }
}
