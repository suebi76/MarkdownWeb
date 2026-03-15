<?php
declare(strict_types=1);

namespace MarkdownWeb;

class Navigation
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

    public function getTree(): array
    {
        $cacheFile = $this->cacheDir . '/nav-tree.json';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 300) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $tree = $this->buildTree($this->contentDir, 0);
        $this->writeCache($cacheFile, $tree);

        return $tree;
    }

    public function invalidateCache(): void
    {
        $cacheFile = $this->cacheDir . '/nav-tree.json';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
    }

    // -------------------------------------------------------------------------

    private function buildTree(string $dir, int $depth = 0): array
    {
        $entries = @scandir($dir);
        if ($entries === false) {
            return [];
        }

        $dirs  = [];
        $files = [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || str_starts_with($entry, '.')) {
                continue;
            }
            $full = $dir . '/' . $entry;
            if (is_dir($full)) {
                $dirs[] = $entry;
            } elseif (strtolower(pathinfo($entry, PATHINFO_EXTENSION)) === 'md') {
                $files[] = $entry;
            }
        }

        sort($dirs);
        sort($files);

        $items = [];

        // Ordner zuerst
        foreach ($dirs as $dirName) {
            $children = $this->buildTree($dir . '/' . $dirName, $depth + 1);
            if (!empty($children)) {
                $items[] = [
                    'type'     => 'folder',
                    'name'     => $dirName,
                    'children' => $children,
                    'order'    => PHP_INT_MAX,
                ];
            }
        }

        // Dateien
        $homeFile = $this->config->get('home_file', 'Home.md');
        foreach ($files as $fileName) {
            $fullPath = $dir . '/' . $fileName;
            $relPath  = substr($fullPath, strlen($this->contentDir) + 1);
            $relPath  = str_replace('\\', '/', $relPath);

            // URL-Pfad
            if ($relPath === $homeFile) {
                $urlPath = '/';
            } else {
                $urlPath = '/' . substr($relPath, 0, -3); // .md entfernen
            }

            $items[] = [
                'type'  => 'file',
                'name'  => $this->getTitle($fullPath, $fileName),
                'path'  => $urlPath,
                'order' => $this->getOrder($fullPath),
            ];
        }

        // Sortieren: Stammverzeichnis → Dateien vor Ordnern, sonst Ordner vor Dateien
        usort($items, static function (array $a, array $b) use ($depth): int {
            $aIsFolder = $a['type'] === 'folder' ? 0 : 1;
            $bIsFolder = $b['type'] === 'folder' ? 0 : 1;
            if ($aIsFolder !== $bIsFolder) {
                // Ebene 0: Dateien zuerst (1 vor 0), ab Ebene 1: Ordner zuerst (0 vor 1)
                return $depth === 0
                    ? $bIsFolder <=> $aIsFolder
                    : $aIsFolder <=> $bIsFolder;
            }
            if ($a['order'] !== $b['order']) {
                return $a['order'] <=> $b['order'];
            }
            return strcmp($a['name'], $b['name']);
        });

        return $items;
    }

    private function getTitle(string $filePath, string $fileName): string
    {
        $content = @file_get_contents($filePath);
        if ($content === false) {
            return pathinfo($fileName, PATHINFO_FILENAME);
        }

        // Frontmatter title
        if (preg_match('/^---\s*\n(.*?)\n---/s', $content, $fm)) {
            if (preg_match('/^title:\s*["\']?(.+?)["\']?\s*$/m', $fm[1], $t)) {
                return trim($t[1]);
            }
        }

        // Erste H1
        if (preg_match('/^#\s+(.+)$/m', $content, $h1)) {
            return trim($h1[1]);
        }

        return pathinfo($fileName, PATHINFO_FILENAME);
    }

    private function getOrder(string $filePath): int
    {
        $content = @file_get_contents($filePath);
        if ($content !== false && preg_match('/^---\s*\n(.*?)\n---/s', $content, $fm)) {
            if (preg_match('/^order:\s*(\d+)/m', $fm[1], $m)) {
                return (int) $m[1];
            }
        }
        return PHP_INT_MAX;
    }

    private function writeCache(string $path, array $data): void
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
