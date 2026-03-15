<?php
declare(strict_types=1);

namespace MarkdownWeb;

class Router
{
    private Config $config;
    private string $contentDir;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->contentDir = $config->get('content_dir');
    }

    public function dispatch(): void
    {
        $path = $this->getRequestPath();

        // Medien-Dateien ausliefern
        if (str_starts_with($path, '/_media/')) {
            $this->serveMedia(substr($path, 8));
            return;
        }

        // Such-Endpunkt
        if ($path === '/_search') {
            $this->handleSearch();
            return;
        }

        // Seite rendern
        $filePath = $this->resolveFilePath($path);

        if ($filePath === null) {
            $this->render404();
            return;
        }

        $this->renderPage($filePath, $path);
    }

    private function getRequestPath(): string
    {
        $uri     = $_SERVER['REQUEST_URI'] ?? '/';
        $path    = parse_url($uri, PHP_URL_PATH) ?? '/';
        $baseUrl = rtrim($this->config->get('base_url', ''), '/');

        // base_url vom Pfad entfernen
        if ($baseUrl !== '' && str_starts_with($path, $baseUrl)) {
            $path = substr($path, strlen($baseUrl));
        }

        // URL-Kodierung auflösen (z.B. %20 → Leerzeichen)
        $path = rawurldecode($path);
        $path = '/' . ltrim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        return $path;
    }

    private function resolveFilePath(string $urlPath): ?string
    {
        // Root → Home.md
        if ($urlPath === '/') {
            $homeFile = $this->config->get('home_file', 'Home.md');
            $home = $this->contentDir . '/' . $homeFile;
            if (file_exists($home)) {
                return $home;
            }
            // Case-insensitive Fallback für Home-Datei
            $entries = @scandir($this->contentDir);
            if ($entries !== false) {
                foreach ($entries as $entry) {
                    if (strcasecmp($entry, $homeFile) === 0 && is_file($this->contentDir . '/' . $entry)) {
                        return $this->contentDir . '/' . $entry;
                    }
                }
            }
            return null;
        }

        $parts = array_map('rawurldecode', explode('/', ltrim($urlPath, '/')));

        // Exakter Treffer
        $candidate = $this->contentDir . '/' . implode('/', $parts) . '.md';
        if (file_exists($candidate)) {
            return $candidate;
        }

        // Groß-/Kleinschreibung-unabhängige Suche
        $found = $this->findCaseInsensitive($parts);
        if ($found !== null) {
            return $found;
        }

        // Ordner-Index suchen
        $dirPath = $this->contentDir . '/' . implode('/', $parts);
        foreach (['index.md', 'Index.md', 'README.md'] as $idx) {
            if (file_exists($dirPath . '/' . $idx)) {
                return $dirPath . '/' . $idx;
            }
        }

        return null;
    }

    private function findCaseInsensitive(array $parts): ?string
    {
        $currentDir = $this->contentDir;

        foreach ($parts as $i => $segment) {
            $isLast = ($i === count($parts) - 1);
            $entries = @scandir($currentDir);

            if ($entries === false) {
                return null;
            }

            $matched = false;
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                if ($isLast) {
                    $nameOnly = pathinfo($entry, PATHINFO_FILENAME);
                    $ext = pathinfo($entry, PATHINFO_EXTENSION);
                    if ($ext === 'md' && strcasecmp($nameOnly, $segment) === 0) {
                        return $currentDir . '/' . $entry;
                    }
                } else {
                    if (is_dir($currentDir . '/' . $entry) && strcasecmp($entry, $segment) === 0) {
                        $currentDir .= '/' . $entry;
                        $matched = true;
                        break;
                    }
                }
            }

            if (!$matched && !$isLast) {
                return null;
            }
        }

        return null;
    }

    private function serveMedia(string $relativePath): void
    {
        $relativePath = ltrim($relativePath, '/');
        $contentDirReal = realpath($this->contentDir);
        $fullPath = realpath($this->contentDir . '/' . $relativePath);

        // Sicherheit: Path-Traversal verhindern
        if ($fullPath === false || $contentDirReal === false) {
            $this->sendHttpError(404);
        }

        if (!str_starts_with($fullPath, $contentDirReal . DIRECTORY_SEPARATOR) && $fullPath !== $contentDirReal) {
            $this->sendHttpError(403);
        }

        if (!is_file($fullPath)) {
            $this->sendHttpError(404);
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $allowed = $this->config->get('allowed_media', []);

        if (!in_array($ext, $allowed, true)) {
            $this->sendHttpError(403);
        }

        $mimeTypes = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png', 'gif' => 'image/gif',
            'webp' => 'image/webp', 'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4', 'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg', 'webm' => 'video/webm',
        ];

        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }

    private function handleSearch(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $query = trim($_GET['q'] ?? '');

        if (mb_strlen($query) < 2) {
            echo json_encode(['results' => []]);
            exit;
        }

        $search = new Search($this->config);
        $results = $search->search($query);
        echo json_encode(['results' => $results]);
        exit;
    }

    private function renderPage(string $filePath, string $urlPath): void
    {
        $config     = $this->config;
        $renderer   = new MarkdownRenderer($config);
        $navigation = new Navigation($config);
        $backlinks  = new Backlinks($config);

        $pageData       = $renderer->render($filePath);
        $navTree        = $navigation->getTree();
        $pageBacklinks  = $backlinks->getBacklinks($urlPath);

        $currentPath = $urlPath;
        $siteName    = $config->get('site_name', 'MarkdownWeb');
        $pageTitle   = $pageData['title'] ?? 'Seite';
        $accentColor = $config->get('accent_color', '#E50046');

        require dirname(__DIR__) . '/templates/layout.php';
    }

    private function render404(): void
    {
        http_response_code(404);
        $config      = $this->config;
        $navigation  = new Navigation($config);
        $navTree     = $navigation->getTree();
        $siteName    = $config->get('site_name', 'MarkdownWeb');
        $accentColor = $config->get('accent_color', '#E50046');
        require dirname(__DIR__) . '/templates/404.php';
    }

    private function sendHttpError(int $code): never
    {
        http_response_code($code);
        exit;
    }
}
