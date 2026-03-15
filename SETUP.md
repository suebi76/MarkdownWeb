# MarkdownWeb – Setup-Anleitung
Autor: Steffen Schwabe

## Voraussetzungen

- PHP 8.1 oder neuer
- Composer (einmalig zum Installieren der Abhängigkeiten)
- Apache-Webserver mit mod_rewrite (IONOS Webhosting: ✓)
- PHP-Erweiterungen: `pdo_sqlite`, `zip`, `mbstring` (IONOS: ✓)

## Einmalige Installation (lokal oder per SSH)

```bash
# Abhängigkeiten installieren
composer install --no-dev --optimize-autoloader
```

Falls kein Composer auf dem Server: Lokal ausführen und das `vendor/`-Verzeichnis mit hochladen.

## Auf IONOS hochladen

1. Alle Dateien per FTP in das Webroot-Verzeichnis hochladen
2. Schreibrechte setzen:
   - `cache/` → 755 (oder 775 je nach Server-Konfiguration)
   - `content/` → 755
   - `config.php` → 644

## Erste Einrichtung

1. Browser: `https://deinedomain.de/admin/`
2. Passwort setzen (mind. 8 Zeichen)
3. Fertig – jetzt kannst du Dateien hochladen

## Ordnerstruktur für Uploads

```
content/
├── Home.md                    → Startseite (/)
├── Kapitel 1/
│   ├── Einleitung.md          → /Kapitel 1/Einleitung
│   └── Fortgeschritten.md
└── Kapitel 2/
    └── Übersicht.md
```

Ordner-Namen werden direkt als Navigationsbereiche verwendet.

## Frontmatter-Felder

```yaml
---
title: Seitentitel          # Wird in Navigation + Tab angezeigt
description: Beschreibung   # Meta-Description für SEO
order: 1                    # Sortierung in der Navigation
cssclasses:                 # Spezielle Layout-Klassen
  - list-cards
---
```

## Anpassungen

- **Seitenname, Farbe**: `config.php`
- **Design**: `assets/css/main.css` (CSS Custom Properties in `:root`)
- **Neue Erweiterungen**: `src/Extensions/` (CommonMark Extension Interface)
