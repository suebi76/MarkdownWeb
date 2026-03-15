---
title: Suchindex
description: Den Volltext-Suchindex aufbauen und verwalten
order: 4
---

# Suchindex

MarkdownWeb verwendet **SQLite FTS5** (Full-Text Search) für die Volltextsuche. Der Index wird in `cache/search.db` gespeichert.

## Wann muss der Index neu aufgebaut werden?

| Situation | Index nötig? |
|---|---|
| Neue Dateien hochgeladen | ✓ Ja |
| Datei umbenannt | ✓ Ja |
| Datei gelöscht | ✓ Ja |
| Inhalt einer Datei geändert | ✓ Ja |
| Nur Navigation geändert | ✗ Nein |

> Nach einem Datei-Upload wird der Index **nicht automatisch** aktualisiert. Baue ihn manuell neu auf, damit neue Inhalte in der Suche erscheinen.

## Index aufbauen

1. Admin-Bereich → **Suchindex**
2. Klicke **Suchindex neu aufbauen**

MarkdownWeb liest alle `.md`-Dateien im `content/`-Ordner, extrahiert Titel, Überschriften und Volltext und schreibt alles in die SQLite-Datenbank.

Die Seite zeigt danach:
- Anzahl der indizierten Seiten
- Größe der Datenbankdatei
- Zeitpunkt der letzten Aktualisierung

## Was wird indiziert?

| Feld | Inhalt |
|---|---|
| `title` | Seitentitel (aus Frontmatter oder erster H1) |
| `headings` | Alle H2- und H3-Überschriften |
| `content` | Volltext der Seite (ohne Markdown-Syntax) |
| `path` | URL der Seite |

## Skalierung

SQLite FTS5 ist für sehr große Dokumentationsmengen ausgelegt. Es gibt keine praktische Obergrenze – auch 10.000 Seiten werden problemlos indiziert und blitzschnell durchsucht.

## Suche verwenden

Die Suche wird über `Strg+K` oder den Suchknopf in der linken Sidebar geöffnet. Suchergebnisse zeigen Titel und einen Textauszug mit hervorgehobenen Treffern.
