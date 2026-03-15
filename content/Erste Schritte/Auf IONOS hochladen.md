---
title: Auf IONOS hochladen
description: Schritt-für-Schritt-Anleitung für das Hochladen auf IONOS Webhosting
order: 3
---

# Auf IONOS hochladen

## FTP-Zugangsdaten

Deine FTP-Daten findest du im IONOS-Kundenbereich:

1. Einloggen unter my.ionos.de
2. → **Hosting** → dein Paket auswählen
3. → **FTP** → Zugangsdaten anzeigen

Du benötigst:
- FTP-Server (z. B. `access.ionos.de`)
- FTP-Benutzer
- FTP-Passwort
- Zielverzeichnis (meistens `/html/`)

## Mit FileZilla hochladen

FileZilla ist ein kostenloser FTP-Client für Windows.

### Verbindung herstellen

Trage oben in der Schnellverbindungsleiste ein:
- **Server:** dein FTP-Server
- **Benutzername:** dein FTP-Benutzer
- **Passwort:** dein FTP-Passwort
- **Port:** `21`

Klicke **Verbinden**.

### Dateien übertragen

1. Links (lokaler Rechner): Navigiere zum MarkdownWeb-Projektordner
2. Rechts (Server): Navigiere in dein Webroot-Verzeichnis (`/html/`)
3. Wähle alle Dateien und Ordner aus (`Strg+A`)
4. Rechtsklick → **Hochladen**

> **Wichtig:** Lade auch den `vendor/`-Ordner vollständig hoch. Er enthält die benötigten PHP-Bibliotheken.

## Dateiberechtigungen setzen

Nach dem Upload müssen die Schreibrechte stimmen. In FileZilla:

1. Rechtsklick auf `cache/` → **Dateiattribute**
2. Numerischen Wert auf `755` setzen, Haken bei **In Unterverzeichnisse einschließen**
3. Gleiches für `content/`

## Erste Einrichtung im Browser

Öffne nach dem Upload:

```
https://deinedomain.de/admin/
```

Da noch kein Passwort gesetzt ist, erscheint der Setup-Assistent. Nach dem Setzen des Passworts:

1. **Suchindex aufbauen** – unter Admin → Suchindex
2. Fertig

## Häufige Probleme

### „500 Internal Server Error"

Ursache ist meistens die `.htaccess`-Datei. Prüfe im IONOS-Kundenbereich ob `mod_rewrite` aktiviert ist. Bei IONOS ist das standardmäßig aktiv.

### Seiten werden nicht gefunden

Stelle sicher dass die `.htaccess`-Datei hochgeladen wurde. FTP-Clients blenden Dateien mit führendem Punkt (`.`) manchmal aus.

In FileZilla: **Server** → **Versteckte Dateien anzeigen** aktivieren.

### Admin zeigt leere Seite

Prüfe ob `config.php` Schreibrechte hat (`644`) und PHP 8.1+ auf dem Server läuft. Im IONOS-Backend unter **PHP-Einstellungen** nachschauen.
