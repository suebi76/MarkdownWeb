---
title: Frontmatter
description: Metadaten für Seiten mit YAML-Frontmatter festlegen
order: 5
---

# Frontmatter

Frontmatter ist ein optionaler Block am **Anfang** einer Markdown-Datei, eingeschlossen in jeweils drei Bindestriche (`---`). Hier kannst du Metadaten für die Seite festlegen.

## Aufbau

```markdown
---
title: Mein Seitentitel
description: Kurze Beschreibung der Seite
order: 2
cssclasses:
  - list-cards
---

# Hier beginnt der eigentliche Inhalt
```

Der Frontmatter-Block muss ganz am Anfang der Datei stehen – kein Leerzeichen davor.

## Alle verfügbaren Felder

### `title`

Der Seitentitel, der in der Navigation und im Browser-Tab erscheint.

```yaml
title: Meine Dokumentationsseite
```

Ohne `title` wird die erste H1-Überschrift der Seite verwendet. Gibt es keine H1, wird der Dateiname genommen.

### `description`

Kurze Beschreibung der Seite – wird als Meta-Description in den HTML-`<head>` eingefügt. Wichtig für Suchmaschinen.

```yaml
description: Schritt-für-Schritt-Anleitung zur Installation von MarkdownWeb
```

### `order`

Legt die Reihenfolge der Seite in der Navigation fest. Niedrigere Zahlen erscheinen weiter oben.

```yaml
order: 1
```

Seiten ohne `order` werden alphabetisch nach allen Seiten mit `order`-Angabe sortiert.

### `cssclasses`

Fügt CSS-Klassen zum `<article>`-Element der Seite hinzu. Damit lassen sich spezielle Layouts aktivieren.

```yaml
cssclasses:
  - list-cards
```

Mehrere Klassen sind möglich:

```yaml
cssclasses:
  - list-cards
  - wide-layout
```

#### Verfügbare Layout-Klassen

| Klasse | Effekt |
|---|---|
| `list-cards` | Verwandelt Listen in Karten-Raster (wie auf der Startseite) |

Eigene Klassen können in `assets/css/main.css` definiert werden.

## Frontmatter ist optional

Alle Felder sind optional. Eine Markdown-Datei ohne Frontmatter funktioniert genauso – der Dateiname wird als Titel verwendet.
