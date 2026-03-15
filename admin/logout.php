<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

session_destroy();
header('Location: ' . $baseUrl . '/admin/index.php');
exit;
