<?php
declare(strict_types=1);

namespace MarkdownWeb;

class Backlinks
{
    private Config $config;
    private string $contentDir;
    private string $cacheDir;

    public function __construct(Config $config)
    {
        $this->config     = $config;
        $this->contentDir = $config->get('content_dir');
        $this->cacheDir   = $config->get('cache_dir');
    }

    /**
     * Gibt alle Seiten zurück, die auf $urlPath verlinken.
     */
    public function getBacklinks(string $urlPath): array
    {
        $map = $this->loadMap();
        return $map[$urlPath] ?? [];
    }

    public function invalidateCache(): void
    {
        $cacheFile = $this->cacheDir . '/backlinks.json';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    public function buildMap(): array
    {
        $map = [];
        $this->scanDirectory($this->contentDir, $map);
        return $map;
    }

    // -------------------------------------------------------------------------

    private function loadMap(): array
    {
        $cacheFile = $this->cacheDir . '/backlinks.json';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 600) {
            $data = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($data)) {
                return $data;
            }
        }

        $map = $this->buildMap();

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        file_put_contents($cacheFile, json_encode($map, JSON_UNESCAPED_UNICODE));

        return $map;
    }

    private function scanDirectory(string $dir, array &$map): void
    {
        $entries = @scandir($dir);
        if ($entries === false) {
            return;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
                continue;
            }
            $full = $dir . '/' . $entry;
            if (is_dir($full)) {
                $this->scanDirectory($full, $map);
            } elseif (strtolower(pathinfo($entry, PATHINFO_EXTENSION)) === 'md') {
                $this->extractLinks($full, $map);
            }
        }
    }

    private function extractLinks(string $filePath, array &$map): void
    {
        $content = @file_get_contents($filePath);
        if ($content === false) {
            return;
        }

        $relPath  = str_replace('\\', '/', substr($filePath, strlen($this->contentDir) + 1));
        $homeFile = $this->config->get('home_file', 'Home.md');
        $sourceUrl   = ($relPath === $homeFile) ? '/' : '/' . substr($relPath, 0, -3);
        $sourceTitle = $this->getTitle($filePath);

        // Markdown-Links: [Text](pfad)
        preg_match_all(
            '/\[([^\]]+)\]\((?!https?:\/\/|mailto:|#)([^)#]+)(?:#[^)]*)?\)/',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $target = '/' . ltrim(rawurldecode($match[2]), '/');
            // .md-Endung entfernen, falls vorhanden
            if (str_ends_with(strtolower($target), '.md')) {
                $target = substr($target, 0, -3);
            }
            $target = rtrim($target, '/') ?: '/';

            $this->addBacklink($map, $target, $sourceUrl, $sourceTitle);
        }
    }

    private function addBacklink(array &$map, string $target, string $source, string $title): void
    {
        if (!isset($map[$target])) {
            $map[$target] = [];
        }

        // Keine Duplikate
        foreach ($map[$target] as $existing) {
            if ($existing['path'] === $source) {
                return;
            }
        }

        $map[$target][] = ['path' => $source, 'title' => $title];
    }

    private function getTitle(string $filePath): string
    {
        $content = @file_get_contents($filePath);
        if ($content === false) {
            return pathinfo($filePath, PATHINFO_FILENAME);
        }

        if (preg_match('/^---\s*\n(.*?)\n---/s', $content, $fm)) {
            if (preg_match('/^title:\s*["\']?(.+?)["\']?\s*$/m', $fm[1], $t)) {
                return trim($t[1]);
            }
        }

        if (preg_match('/^#\s+(.+)$/m', $content, $h1)) {
            return trim($h1[1]);
        }

        return pathinfo($filePath, PATHINFO_FILENAME);
    }
}
