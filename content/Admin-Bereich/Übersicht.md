---
title: Admin-Übersicht
description: Der MarkdownWeb Admin-Bereich im Überblick
order: 1
---

# Admin-Bereich

Der Admin-Bereich ist das Herzstück der Inhaltsverwaltung. Du erreichst ihn unter:

```
https://deinedomain.de/admin/
```

## Bereiche

### Dashboard

Die Startseite des Admin-Bereichs zeigt dir auf einen Blick:

- Anzahl der Markdown-Seiten
- Anzahl der Mediendateien
- Status des Suchindex

Von hier aus erreichst du alle Funktionen per Klick.

### Dateien hochladen

Lade einzelne `.md`-Dateien, Bilder oder ganze ZIP-Archive hoch. Der Inhalt wird automatisch in der Navigation verfügbar.

→ Mehr dazu: [Dateien hochladen](/Admin-Bereich/Dateien hochladen)

### Dateimanager

Zeigt alle Dateien im `content/`-Ordner als Baumstruktur. Von hier kannst du Dateien umbenennen und löschen.

→ Mehr dazu: [Dateimanager](/Admin-Bereich/Dateimanager)

### Suchindex

Baut den Volltext-Suchindex neu auf. Muss nach dem ersten Hochladen und bei größeren Änderungen manuell ausgeführt werden.

→ Mehr dazu: [Suchindex](/Admin-Bereich/Suchindex)

## Sicherheit

- **Passwort** wird als bcrypt-Hash in `config.php` gespeichert – niemals im Klartext
- **Brute-Force-Schutz:** Nach 5 falschen Versuchen wird der Login für 15 Minuten gesperrt
- **Session-Sicherheit:** `HttpOnly`, `SameSite=Strict`, kein Zugriff per JavaScript
- **CSRF-Schutz:** Alle Formulare sind gegen Cross-Site-Request-Forgery abgesichert
- **Passwort ändern:** Das Passwort kannst du jederzeit ändern, indem du in `config.php` einen neuen Hash einträgst

## Passwort ändern

Generiere einen neuen bcrypt-Hash über die Kommandozeile:

```bash
php -r "echo password_hash('NeuesPasswort', PASSWORD_BCRYPT);"
```

Trage den ausgegebenen Hash in `config.php` ein:

```php
'admin_password_hash' => '$2y$10$...',
```
