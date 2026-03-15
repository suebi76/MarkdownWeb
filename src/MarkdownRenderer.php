<?php
declare(strict_types=1);

namespace MarkdownWeb;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownRenderer
{
    private Config $config;
    private string $contentDir;
    private MarkdownConverter $converter;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->contentDir = $config->get('content_dir');
        $this->converter  = $this->buildConverter();
    }

    private function buildConverter(): MarkdownConverter
    {
        $environment = new Environment([
            'heading_permalink' => [
                'html_class'          => 'heading-anchor',
                'id_prefix'           => '',
                'apply_id_to_heading' => true,
                'heading_class'       => '',
                'fragment_prefix'     => '',
                'insert'              => 'after',
                'min_heading_level'   => 1,
                'max_heading_level'   => 6,
                'title'               => 'Direktlink zu diesem Abschnitt',
                'symbol'              => '#',
                'aria_hidden'         => true,
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new AttributesExtension());
        $environment->addExtension(new FrontMatterExtension());

        return new MarkdownConverter($environment);
    }

    /**
     * Rendert eine Markdown-Datei zu HTML.
     */
    public function render(string $filePath): array
    {
        $raw = file_get_contents($filePath);
        if ($raw === false) {
            return $this->errorResult();
        }

        $raw = $this->rewriteImagePaths($raw, $filePath);

        $result      = $this->converter->convert($raw);
        $frontmatter = [];

        if ($result instanceof RenderedContentWithFrontMatter) {
            $frontmatter = $result->getFrontMatter() ?? [];
        }

        $html  = (string) $result->getContent();
        $title = $frontmatter['title']
            ?? $this->extractFirstH1($html)
            ?? pathinfo($filePath, PATHINFO_FILENAME);

        $cssClasses = [];
        if (!empty($frontmatter['cssclasses'])) {
            $cssClasses = (array) $frontmatter['cssclasses'];
        }

        return [
            'html'        => $html,
            'title'       => $title,
            'frontmatter' => $frontmatter,
            'toc'         => $this->extractHeadings($html),
            'css_classes' => $cssClasses,
            'description' => $frontmatter['description'] ?? '',
        ];
    }

    /**
     * Gibt den bereinigten Plaintext einer Datei zurück (für Suchindex).
     */
    public function extractPlainText(string $filePath): string
    {
        $raw = file_get_contents($filePath);
        if ($raw === false) {
            return '';
        }

        // Frontmatter entfernen
        $raw = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $raw) ?? $raw;

        // Markdown-Syntax grob entfernen
        $raw = preg_replace('/#{1,6}\s+/', '', $raw) ?? $raw;                 // Überschriften
        $raw = preg_replace('/\*\*(.+?)\*\*/s', '$1', $raw) ?? $raw;          // Fett
        $raw = preg_replace('/\*(.+?)\*/s', '$1', $raw) ?? $raw;              // Kursiv
        $raw = preg_replace('/`{3}.*?`{3}/s', '', $raw) ?? $raw;              // Code-Blöcke
        $raw = preg_replace('/`[^`]+`/', '', $raw) ?? $raw;                   // Inline-Code
        $raw = preg_replace('/!\[[^\]]*\]\([^)]+\)/', '', $raw) ?? $raw;      // Bilder
        $raw = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $raw) ?? $raw;   // Links
        $raw = preg_replace('/[|>]/', ' ', $raw) ?? $raw;                     // Tabellen/Blockquotes
        $raw = preg_replace('/\s+/', ' ', $raw) ?? $raw;

        return trim($raw);
    }

    public function extractHeadings(string $html): array
    {
        $headings = [];
        preg_match_all(
            '/<h([23])\s[^>]*id="([^"]+)"[^>]*>(.*?)<\/h\1>/i',
            $html,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $m) {
            $headings[] = [
                'level' => (int) $m[1],
                'id'    => $m[2],
                'text'  => trim(strip_tags($m[3]), " \t\n\r\0\x0B#"),
            ];
        }

        return $headings;
    }

    // -------------------------------------------------------------------------

    private function rewriteImagePaths(string $content, string $filePath): string
    {
        $contentDirReal = realpath($this->contentDir);
        $fileDirReal    = realpath(dirname($filePath));

        if ($contentDirReal === false || $fileDirReal === false) {
            return $content;
        }

        // Relativer Pfad vom Content-Root zum Dateiordner
        $relDir = '';
        if ($fileDirReal !== $contentDirReal) {
            $relDir = ltrim(str_replace(
                $contentDirReal,
                '',
                $fileDirReal
            ), DIRECTORY_SEPARATOR);
            $relDir = str_replace(DIRECTORY_SEPARATOR, '/', $relDir);
        }

        // ![alt](relative.png) → ![alt](/_media/ordner/relative.png)
        return preg_replace_callback(
            '/!\[([^\]]*)\]\((?!https?:\/\/|\/|data:)([^)]+)\)/',
            static function (array $m) use ($relDir): string {
                $prefix = $relDir ? '/_media/' . $relDir . '/' : '/_media/';
                return '![' . $m[1] . '](' . $prefix . ltrim($m[2], '/') . ')';
            },
            $content
        ) ?? $content;
    }

    private function extractFirstH1(string $html): ?string
    {
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $m)) {
            return strip_tags($m[1]);
        }
        return null;
    }

    private function errorResult(): array
    {
        return [
            'html'        => '<p class="error">Fehler beim Laden der Seite.</p>',
            'title'       => 'Fehler',
            'frontmatter' => [],
            'toc'         => [],
            'css_classes' => [],
            'description' => '',
        ];
    }
}
