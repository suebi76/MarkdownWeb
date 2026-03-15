---
title: Navigation und URLs
description: Wie URLs aus der Ordnerstruktur generiert werden
order: 1
---

# Navigation und URLs

## URL-Schema

MarkdownWeb generiert URLs direkt aus der Datei- und Ordnerstruktur im `content/`-Verzeichnis.

| Datei | URL |
|---|---|
| `content/Home.md` | `/` |
| `content/Über uns.md` | `/Über uns` |
| `content/Handbuch/Start.md` | `/Handbuch/Start` |
| `content/Handbuch/Kapitel 1/Intro.md` | `/Handbuch/Kapitel 1/Intro` |

URLs sind groß-/kleinschreibungsunempfindlich: `/handbuch/start` findet auch `Handbuch/Start.md`.

## Startseite

Die Datei `Home.md` im Stammverzeichnis ist immer unter `/` erreichbar. Den Dateinamen kannst du in `config.php` ändern:

```php
'home_file' => 'Home.md',
```

## Ordner als Navigation

Ordner im `content/`-Verzeichnis werden in der Seitennavigation als **aufklappbare Sektionen** dargestellt. Der Ordnername wird direkt als Sektionstitel verwendet.

Empfehlungen für Ordnernamen:
- Leserliche Namen wählen: `Erste Schritte` statt `01_start`
- Keine Sonderzeichen außer Leerzeichen und Bindestriche
- Konsistente Groß-/Kleinschreibung

## Sortierung

Die Reihenfolge in der Navigation wird bestimmt durch:

1. `order:`-Feld im Frontmatter (aufsteigend, niedrigste Zahl zuerst)
2. Alphabetisch für Seiten ohne `order`

Ordner erscheinen immer vor Dateien auf gleicher Ebene.

## Permalinks (geplant)

In einer zukünftigen Version wird das Frontmatter-Feld `permalink` unterstützt, um eine eigene URL unabhängig vom Dateipfad zu vergeben:

```yaml
---
permalink: /start
---
```

## Interne Links

Verlinke auf andere Seiten immer mit dem URL-Pfad:

```markdown
[Zur Installation](/Erste Schritte/Installation)
[Zum Admin-Bereich](/Admin-Bereich/Übersicht)
```

Relative Links funktionieren ebenfalls:

```markdown
[Nächste Seite](Zweite Seite)
```
