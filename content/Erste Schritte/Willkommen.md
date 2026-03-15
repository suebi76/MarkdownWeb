---
title: Erste Schritte
description: Wie du mit MarkdownWeb startest
order: 1
---

# Erste Schritte

## Installation

1. Dateien per FTP auf deinen IONOS-Webhosting-Account hochladen
2. `vendor/`-Verzeichnis mit Composer erzeugen (oder fertig mitliefern)
3. Browser öffnen und `/admin/` aufrufen
4. Passwort festlegen
5. Markdown-Dateien hochladen

## Markdown schreiben

Alle Standard-Markdown-Elemente werden unterstützt:

### Überschriften

```markdown
# H1
## H2
### H3
```

### Listen

- Punkt 1
- Punkt 2
  - Unterpunkt

1. Erster Schritt
2. Zweiter Schritt

### Links

[Linktext](https://example.com)

[Interne Seite](/Erste Schritte/Willkommen)

### Bilder

```markdown
![Bildbeschreibung](images/meinbild.png)
```

### Tabellen

| Spalte 1 | Spalte 2 | Spalte 3 |
|----------|----------|----------|
| Wert A   | Wert B   | Wert C   |

### Code

Inline-Code: `$variable = 'Wert';`

Code-Block:

```php
<?php
echo 'Hallo Welt!';
```

### Zitate

> Dies ist ein Blockzitat.

### Aufgabenlisten

- [x] Erledigt
- [ ] Noch offen
