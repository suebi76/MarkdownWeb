---
title: Erste Seite erstellen
description: Wie du deine erste Markdown-Seite anlegst und veröffentlichst
order: 2
---

# Erste Seite erstellen

## Eine neue Seite anlegen

Jede Seite in MarkdownWeb ist eine einfache `.md`-Datei. Der Dateiname und der Ordner, in dem sie liegt, bestimmen die URL der Seite.

| Datei im `content/`-Ordner | URL auf der Website |
|---|---|
| `Home.md` | `/` |
| `Über uns.md` | `/Über uns` |
| `Handbuch/Einleitung.md` | `/Handbuch/Einleitung` |
| `Handbuch/Kapitel 1/Start.md` | `/Handbuch/Kapitel 1/Start` |

## Über den Admin-Bereich hochladen

1. `/admin/` aufrufen und einloggen
2. → **Dateien hochladen**
3. Zielordner auswählen oder neu eingeben
4. `.md`-Datei auswählen und hochladen

Die neue Seite erscheint sofort in der Navigation.

## Dateistruktur = Navigation

MarkdownWeb liest deine Ordnerstruktur direkt aus und baut daraus die linke Seitennavigation. Du musst nichts konfigurieren.

```
content/
├── Home.md                  → Startseite
├── Produkt/
│   ├── Funktionen.md        → Menüpunkt unter „Produkt"
│   └── Preise.md
└── Support/
    ├── FAQ.md
    └── Kontakt.md
```

## Frontmatter verwenden

Am Anfang jeder Markdown-Datei kannst du optional einen **Frontmatter-Block** einfügen – in drei Bindestrichen eingeschlossen:

```markdown
---
title: Mein Seitentitel
description: Kurze Beschreibung für Suchmaschinen
order: 1
---

# Inhalt beginnt hier
```

| Feld | Bedeutung |
|---|---|
| `title` | Wird in der Navigation und im Browser-Tab angezeigt |
| `description` | Meta-Description für Suchmaschinen |
| `order` | Sortierreihenfolge in der Navigation (1, 2, 3 …) |
| `cssclasses` | Zusätzliche CSS-Klassen für diese Seite |

## Startseite festlegen

Die Datei `content/Home.md` ist immer die Startseite (`/`). Du kannst den Namen in `config.php` ändern:

```php
'home_file' => 'Home.md',
```

## Seiten sortieren

Standardmäßig werden Seiten alphabetisch sortiert. Um eine eigene Reihenfolge festzulegen, nutze `order:` im Frontmatter:

```markdown
---
order: 1
---
```

Seiten ohne `order` erscheinen alphabetisch nach allen Seiten mit `order`.
