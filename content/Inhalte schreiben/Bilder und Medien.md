---
title: Bilder und Medien
description: Bilder, PDFs und andere Mediendateien einbinden
order: 4
---

# Bilder und Medien

## Bilder hochladen

Bilder werden über den Admin-Bereich hochgeladen:

1. Admin-Bereich öffnen → **Dateien hochladen**
2. Zielordner wählen (z. B. `Handbuch/images`)
3. Bilddatei auswählen (JPG, PNG, GIF, WebP, SVG)
4. Hochladen

## Bild einbinden

```markdown
![Bildbeschreibung](images/screenshot.png)
```

Der Pfad ist **relativ zur aktuellen Markdown-Datei**. Liegt die Datei in `content/Handbuch/Kapitel.md` und das Bild in `content/Handbuch/images/screenshot.png`, dann:

```markdown
![Screenshot](images/screenshot.png)
```

## Bild mit Titel

```markdown
![Bildbeschreibung](images/screenshot.png "Optionaler Hover-Titel")
```

## Unterstützte Formate

| Format | Endung | Hinweis |
|---|---|---|
| JPEG | `.jpg`, `.jpeg` | Fotos und Grafiken |
| PNG | `.png` | Screenshots, Logos mit Transparenz |
| GIF | `.gif` | Animierte Bilder |
| WebP | `.webp` | Modern, kleine Dateigröße |
| SVG | `.svg` | Vektorgrafiken, ideal für Icons/Logos |
| PDF | `.pdf` | Dokumente zum Download |

## Bilder skalieren

Mit dem Attribut-Block kannst du Breite und Höhe setzen:

```markdown
![Logo](images/logo.png){width=200}
```

```markdown
![Breites Bild](images/banner.jpg){width=100%}
```

## PDFs verlinken

PDFs werden nicht direkt angezeigt, sondern als Download-Link eingebunden:

```markdown
[Dokument herunterladen (PDF)](dokumente/handbuch.pdf)
```

## Empfehlungen

- **Bildgröße:** Bilder vor dem Upload komprimieren – das beschleunigt die Ladezeit
- **Dateinamen:** Keine Leerzeichen oder Sonderzeichen in Dateinamen verwenden, z. B. `mein-bild.png` statt `mein bild.png`
- **Ordner:** Lege einen `images/`-Unterordner neben deinen `.md`-Dateien an, um die Struktur übersichtlich zu halten
