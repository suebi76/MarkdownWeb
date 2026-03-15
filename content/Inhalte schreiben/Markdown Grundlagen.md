---
title: Markdown Grundlagen
description: Die wichtigsten Markdown-Elemente auf einen Blick
order: 1
---

# Markdown Grundlagen

Markdown ist eine einfache Auszeichnungssprache, die du im normalen Texteditor schreiben kannst. MarkdownWeb unterstützt den **CommonMark**-Standard sowie **GitHub Flavored Markdown (GFM)**.

## Überschriften

```markdown
# Überschrift 1
## Überschrift 2
### Überschrift 3
#### Überschrift 4
```

Überschriften der Ebenen H2 und H3 erscheinen automatisch im **Inhaltsverzeichnis** auf der rechten Seite.

## Fließtext und Absätze

Absätze werden durch eine Leerzeile getrennt.

```markdown
Das ist der erste Absatz. Er kann mehrere Sätze enthalten.

Das ist der zweite Absatz.
```

Einen **Zeilenumbruch** ohne neuen Absatz erzwingst du mit zwei Leerzeichen am Zeilenende oder `\`:

```markdown
Erste Zeile
Zweite Zeile (gleicher Absatz)
```

## Hervorhebungen

```markdown
**Fett gedruckter Text**
*Kursiver Text*
~~Durchgestrichener Text~~
`Inline-Code`
```

Ergebnis: **Fett**, *Kursiv*, ~~Durchgestrichen~~, `Inline-Code`

## Trennlinie

```markdown
---
```

---

## Zitat (Blockquote)

```markdown
> Das ist ein Zitat.
> Es kann mehrere Zeilen haben.
```

> Das ist ein Zitat.
> Es kann mehrere Zeilen haben.

## Links

```markdown
[Linktext](https://example.com)
[Interne Seite](/Erste Schritte/Installation)
[Link mit Titel](https://example.com "Beschreibung")
```

Externe Links werden automatisch mit einem ↗-Symbol markiert.

## Bilder

```markdown
![Bildbeschreibung](images/meinbild.png)
![Logo](images/logo.svg "Optionaler Titel")
```

Bilder werden relativ zur aktuellen Markdown-Datei aufgelöst. Lade Bilddateien über den [Admin-Bereich](/Admin-Bereich/Dateien hochladen) hoch.

## Aufgabenlisten

```markdown
- [x] Erledigt
- [ ] Noch offen
- [ ] Auch noch offen
```

- [x] Erledigt
- [ ] Noch offen
