---
title: Dateien hochladen
description: Markdown-Dateien, Bilder und ZIP-Archive über den Admin-Bereich hochladen
order: 2
---

# Dateien hochladen

## Einzelne Dateien

1. Admin-Bereich öffnen → **Dateien hochladen**
2. Zielordner auswählen oder neuen Ordnernamen eingeben
3. Datei per Klick oder Drag & Drop auswählen
4. **Hochladen** klicken

Die Datei erscheint sofort in der Navigation.

## Zielordner

Du kannst einen bestehenden Ordner aus der Dropdown-Liste wählen oder einen neuen Pfad eingeben:

```
Handbuch/Kapitel 1
```

Wenn der Ordner noch nicht existiert, wird er automatisch erstellt.

## ZIP-Archive hochladen

Das ist die schnellste Methode, um eine komplette Dokumentation auf einmal zu übertragen.

### Vorbereitung

Packe deine Markdown-Dateien als ZIP – **die Ordnerstruktur bleibt erhalten**:

```
dokumentation.zip
├── Einleitung.md
├── Kapitel 1/
│   ├── Übersicht.md
│   └── Details.md
└── Kapitel 2/
    └── Start.md
```

### Hochladen

1. ZIP-Datei im Upload-Bereich auswählen
2. Optional: Zielordner angeben (ZIP-Inhalt wird dort entpackt)
3. **Hochladen** klicken

MarkdownWeb entpackt das Archiv und erstellt die Ordnerstruktur automatisch.

## Erlaubte Dateitypen

| Typ | Endungen |
|---|---|
| Markdown | `.md` |
| Bilder | `.jpg`, `.jpeg`, `.png`, `.gif`, `.webp`, `.svg` |
| Dokumente | `.pdf` |
| Video | `.mp4`, `.webm` |
| Audio | `.mp3`, `.ogg` |
| Archiv | `.zip` |

## Maximale Dateigröße

Standardmäßig sind Dateien bis **50 MB** erlaubt. Den Wert kannst du in `config.php` anpassen:

```php
'max_upload_size' => 52428800, // 50 MB in Bytes
```

## Nach dem Upload

Der Navigations-Cache wird automatisch geleert – die neue Seite erscheint sofort in der Seitennavigation.

Der **Suchindex** wird nicht automatisch aktualisiert. Um neue Seiten durchsuchbar zu machen, gehe zu [Admin → Suchindex](/Admin-Bereich/Suchindex) und baue ihn neu auf.
