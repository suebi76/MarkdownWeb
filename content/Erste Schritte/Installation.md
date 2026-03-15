---
title: Installation
description: MarkdownWeb lokal einrichten und auf dem Server installieren
order: 1
---

# Installation

## Systemvoraussetzungen

Bevor du anfängst, stelle sicher dass dein Webhosting folgendes bietet:

- **PHP 8.1+** (IONOS Webhosting: ✓)
- **Apache** mit `mod_rewrite` (IONOS: ✓)
- **PHP-Erweiterungen:** `pdo_sqlite`, `zip`, `mbstring` (IONOS: ✓)

## Schritt 1 – Dateien vorbereiten

Das Projektverzeichnis enthält nach der Einrichtung folgende Struktur:

```
markdownweb/
├── admin/          ← Admin-Backend
├── assets/         ← CSS und JavaScript
├── cache/          ← Automatisch generierte Dateien
├── content/        ← Deine Markdown-Dateien (hier arbeitest du)
├── src/            ← PHP-Klassen
├── templates/      ← HTML-Templates
├── vendor/         ← Composer-Abhängigkeiten
├── .htaccess
├── config.php      ← Konfiguration
└── index.php
```

## Schritt 2 – Per FTP hochladen

Lade alle Dateien in das **Webroot-Verzeichnis** deines Hostings hoch. Bei IONOS ist das typischerweise:

```
/html/
```

oder der Ordner, der deiner Domain zugewiesen ist.

> **Tipp:** Nutze FileZilla oder den IONOS FTP-Manager. Übertrage alle Ordner und Dateien vollständig, einschließlich des `vendor/`-Ordners.

## Schritt 3 – Schreibrechte setzen

Die folgenden Verzeichnisse benötigen Schreibrechte:

| Verzeichnis | Berechtigung |
|---|---|
| `cache/` | `755` |
| `content/` | `755` |
| `config.php` | `644` |

Bei IONOS kannst du diese im FTP-Manager per Rechtsklick → *Dateiberechtigungen* setzen.

## Schritt 4 – Admin einrichten

Öffne im Browser:

```
https://deinedomain.de/admin/
```

Da noch kein Passwort gesetzt ist, erscheint automatisch der **Setup-Assistent**. Gib ein Passwort mit mindestens 8 Zeichen ein – es wird sicher als bcrypt-Hash in `config.php` gespeichert.

## Schritt 5 – Suchindex aufbauen

Nach dem ersten Login im Admin-Bereich:

1. → **Suchindex** aufrufen
2. → **Suchindex neu aufbauen** klicken

Die Suche ist danach sofort einsatzbereit.

## Fertig

Deine Dokumentationsseite ist jetzt live. Besuche die Startseite und lade über den Admin-Bereich deine ersten Markdown-Dateien hoch.
