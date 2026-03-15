---
title: config.php
description: Alle Konfigurationsoptionen von MarkdownWeb erklärt
order: 1
---

# config.php

Die Datei `config.php` im Stammverzeichnis enthält alle Einstellungen für deine MarkdownWeb-Installation.

## Vollständige Übersicht

```php
<?php
return [
    // Site-Einstellungen
    'site_name'        => 'MarkdownWeb',
    'site_description' => 'Dokumentation',
    'author'           => 'Steffen Schwabe',
    'accent_color'     => '#E50046',

    // Verzeichnisse
    'content_dir' => __DIR__ . '/content',
    'cache_dir'   => __DIR__ . '/cache',

    // Startseite
    'home_file' => 'Home.md',

    // Admin-Passwort (bcrypt-Hash)
    'admin_password_hash' => '$2y$10$...',

    // Suche
    'search_results_limit' => 10,

    // Datum-Format
    'date_format' => 'd.m.Y',

    // Erlaubte Dateitypen
    'allowed_media'  => ['jpg', 'jpeg', 'png', ...],
    'allowed_upload' => ['md', 'jpg', 'zip', ...],

    // Max. Upload-Größe in Bytes
    'max_upload_size' => 52428800,
];
```

## Einstellungen im Detail

### `site_name`

Der Name deiner Dokumentationsseite. Erscheint in der Sidebar, dem Browser-Tab und der Navigation.

```php
'site_name' => 'Mein Handbuch',
```

### `author`

Dein Name. Erscheint im Seiten-Footer und im HTML-Meta-Tag.

```php
'author' => 'Steffen Schwabe',
```

### `accent_color`

Die Hauptfarbe der Seite – für Links, aktive Navigationseinträge, Buttons. Als Hex-Farbwert angeben.

```php
'accent_color' => '#E50046',
```

### `home_file`

Name der Markdown-Datei, die als Startseite (`/`) angezeigt wird.

```php
'home_file' => 'Home.md',
```

### `search_results_limit`

Maximale Anzahl der Suchergebnisse pro Suchanfrage.

```php
'search_results_limit' => 10,
```

### `max_upload_size`

Maximale Dateigröße für Uploads in Bytes. Standardwert: 50 MB.

```php
'max_upload_size' => 52428800, // 50 MB
// 'max_upload_size' => 104857600, // 100 MB
```

## Passwort-Hash generieren

```bash
php -r "echo password_hash('DeinPasswort', PASSWORD_BCRYPT);"
```

Den ausgegebenen Hash in `admin_password_hash` eintragen.
