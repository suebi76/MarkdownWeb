# MarkdownWeb

**Schlanke, datenschutzkonforme Dokumentationsplattform in PHP.**

Markdown-Dateien + Ordnerstruktur = fertige Website. Kein Framework, kein Node.js, kein Build-Schritt. Läuft auf jedem Standard-Webhosting mit PHP 8.1+.

![Screenshot](https://img.shields.io/badge/PHP-8.1+-8892BF?logo=php&logoColor=white) ![License](https://img.shields.io/badge/Lizenz-CC%20BY%204.0-green)

---

## Features

| Feature | Beschreibung |
|---------|-------------|
| **Automatische Navigation** | Ordnerstruktur wird zur Seitennavigation |
| **Volltextsuche** | SQLite FTS5 mit Highlighting (LIKE-Fallback wenn FTS5 nicht verfügbar) |
| **Dark / Light Mode** | Umschaltbar, gespeichert im Browser |
| **Inhaltsverzeichnis** | Automatisch aus H2/H3-Überschriften generiert |
| **Backlinks** | Zeigt, welche Seiten auf die aktuelle Seite verlinken |
| **Admin-Backend** | Dateien hochladen (einzeln, mehrere, ZIP), Dateimanager, Suchindex verwalten |
| **DSGVO-konform** | Keine externen Ressourcen, kein Tracking, kein CDN |
| **Responsive** | Drei-Spalten-Layout mit mobilem Sidebar |
| **Frontmatter** | YAML-Frontmatter für Titel, Beschreibung, Sortierung, CSS-Klassen |
| **GFM-Support** | Tabellen, Strikethrough, Task-Listen, Autolinks |

## Voraussetzungen

- PHP 8.1 oder höher
- Apache mit `mod_rewrite`
- PHP-Erweiterungen: `pdo_sqlite`, `mbstring`, `zip` (für ZIP-Upload)

## Installation

### 1. Dateien hochladen

Das komplette Verzeichnis auf den Webserver kopieren (inkl. `vendor/`).

### 2. Konfiguration anpassen

**`config.php`** – Grundeinstellungen:

```php
'base_url'     => '/mein-ordner',   // Unterordner oder '' für Root
'site_name'    => 'Meine Doku',
'accent_color' => '#E50046',
```

**`.htaccess`** – RewriteBase muss zum Unterordner passen:

```apache
RewriteBase /mein-ordner/
```

> Bei Root-Installation: `RewriteBase /`

### 3. Admin-Passwort setzen

Beim ersten Aufruf von `/admin/` erscheint automatisch der Setup-Assistent.

### 4. Inhalte erstellen

Markdown-Dateien in den `content/`-Ordner legen. Die Ordnerstruktur wird zur Navigation:

```
content/
├── Home.md                    ← Startseite
├── Erste Schritte/
│   ├── Installation.md
│   └── Konfiguration.md
├── Handbuch/
│   ├── Grundlagen.md
│   └── Erweitert/
│       └── Plugins.md
└── FAQ.md
```

## Frontmatter

Optionale YAML-Metadaten am Anfang jeder `.md`-Datei:

```yaml
---
title: Seitentitel
description: Kurzbeschreibung für Meta-Tags
order: 1
cssclasses:
  - list-cards
---
```

| Feld | Beschreibung |
|------|-------------|
| `title` | Überschreibt den automatisch erkannten Titel |
| `description` | Meta-Description der Seite |
| `order` | Sortierreihenfolge in der Navigation (kleiner = weiter oben) |
| `cssclasses` | Zusätzliche CSS-Klassen für die Seite |

## Projektstruktur

```
├── admin/              Admin-Backend (Login, Upload, Dateimanager)
├── assets/
│   ├── css/            Stylesheets (main.css, admin.css)
│   └── js/             JavaScript (main.js, search.js)
├── cache/              Nav-Cache, Backlinks-Cache, Suchindex (SQLite)
├── content/            Markdown-Dateien und Medien
├── src/                PHP-Klassen (PSR-4 Autoload)
│   ├── Backlinks.php
│   ├── Config.php
│   ├── MarkdownRenderer.php
│   ├── Navigation.php
│   ├── Router.php
│   └── Search.php
├── templates/          PHP-Templates (Layout, 404, Partials)
├── vendor/             Composer-Dependencies (mitgeliefert)
├── config.php          Konfiguration
├── index.php           Einstiegspunkt
└── .htaccess           Apache Rewrite-Regeln
```

## Abhängigkeiten

| Paket | Version | Zweck |
|-------|---------|-------|
| [league/commonmark](https://commonmark.thephpleague.com/) | ^2.5 | Markdown-Parsing (CommonMark + GFM + Frontmatter + HeadingPermalink) |
| [symfony/yaml](https://symfony.com/doc/current/components/yaml.html) | ^6.4 | YAML-Frontmatter-Parsing |

`vendor/` ist im Repository enthalten, damit das Projekt ohne Composer direkt auf Shared-Hosting deployed werden kann.

## Admin-Backend

Erreichbar unter `/admin/`. Geschützt durch bcrypt-Passwort mit Brute-Force-Schutz (5 Versuche, danach 15 Min. Sperre).

- **Dateien hochladen** – Einzelne Dateien, mehrere gleichzeitig oder ZIP-Archive
- **Dateimanager** – Dateien und Ordner umbenennen, löschen, Struktur verwalten
- **Suchindex** – SQLite-Volltextindex manuell neu aufbauen

## Sicherheit

- Kein externes CSS/JS/Fonts (DSGVO-konform)
- CSRF-Token auf allen Admin-Formularen
- Session: `HttpOnly`, `SameSite=Strict`
- Brute-Force-Schutz beim Login
- Path-Traversal-Schutz bei Medien-Auslieferung
- Direktzugriff auf `src/`, `cache/`, `vendor/` blockiert
- Direktzugriff auf `.md`-Dateien blockiert

## Lizenz

[CC BY 4.0](https://creativecommons.org/licenses/by/4.0/) – Steffen Schwabe
