<?php
declare(strict_types=1);

/**
 * MarkdownWeb – Einstiegspunkt
 * Autor: Steffen Schwabe
 */

require_once __DIR__ . '/vendor/autoload.php';

use MarkdownWeb\Config;
use MarkdownWeb\Router;

$config = Config::getInstance();
$router = new Router($config);
$router->dispatch();
