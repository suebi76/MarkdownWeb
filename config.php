<?php
/**
 * MarkdownWeb – Konfiguration
 * Autor: Steffen Schwabe
 */
return [
    // Basis-URL des Systems (ohne trailing Slash)
    // Root-Installation: '' (leer)
    // Unterordner: z.B. '/md-web'
    // WICHTIG: Muss auch in .htaccess als RewriteBase angepasst werden!
    'base_url' => '/md-web',

    // Site-Einstellungen
    'site_name'        => 'MarkdownWeb',
    'site_description' => 'Dokumentation',
    'author'           => 'Steffen Schwabe',
    'accent_color'     => '#E50046',

    // Verzeichnisse (absolute Pfade)
    'content_dir' => __DIR__ . '/content',
    'cache_dir'   => __DIR__ . '/cache',

    // Startseite
    'home_file' => 'Home.md',

    // Admin-Passwort (bcrypt-Hash)
    // Generieren mit: password_hash('DeinPasswort', PASSWORD_BCRYPT)
    // Beim ersten Start leer lassen → Setup-Assistent erscheint automatisch
    'admin_password_hash' => '',

    // Suche
    'search_results_limit' => 10,

    // Datum-Format
    'date_format' => 'd.m.Y',

    // Erlaubte Medien-Erweiterungen
    'allowed_media' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'mp4', 'mp3', 'ogg', 'webm'],

    // Erlaubte Upload-Erweiterungen
    'allowed_upload' => ['md', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'mp4', 'mp3', 'ogg', 'webm', 'zip'],

    // Max. Upload-Größe in Bytes (Standard: 50 MB)
    'max_upload_size' => 52428800,
];
