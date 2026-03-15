<?php
declare(strict_types=1);

namespace MarkdownWeb;

class Config
{
    private static ?self $instance = null;
    private array $data;

    private function __construct()
    {
        $this->data = require dirname(__DIR__) . '/config.php';
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function all(): array
    {
        return $this->data;
    }
}
