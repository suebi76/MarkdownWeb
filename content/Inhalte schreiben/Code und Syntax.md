---
title: Code und Syntax
description: Inline-Code und Code-Blöcke mit Syntax-Highlighting
order: 3
---

# Code und Syntax

## Inline-Code

Für kurze Code-Schnipsel im Fließtext verwendest du einfache Backticks:

```markdown
Setze die Variable `$name = 'Wert';` und rufe `echo $name;` auf.
```

Ergebnis: Setze die Variable `$name = 'Wert';` und rufe `echo $name;` auf.

## Code-Blöcke

Für mehrzeilige Code-Abschnitte verwendest du drei Backticks, optional gefolgt von der Sprache:

````markdown
```php
<?php
$name = 'MarkdownWeb';
echo 'Willkommen bei ' . $name;
```
````

```php
<?php
$name = 'MarkdownWeb';
echo 'Willkommen bei ' . $name;
```

## Unterstützte Sprachen

Du kannst nach den drei Backticks die Sprache angeben. Die häufigsten:

| Bezeichner | Sprache |
|---|---|
| `php` | PHP |
| `html` | HTML |
| `css` | CSS |
| `js` | JavaScript |
| `bash` | Shell / Bash |
| `sql` | SQL |
| `json` | JSON |
| `yaml` | YAML |
| `markdown` | Markdown |
| `python` | Python |
| `java` | Java |

## Beispiele

**HTML:**
```html
<!DOCTYPE html>
<html lang="de">
<head>
    <title>Meine Seite</title>
</head>
<body>
    <h1>Hallo Welt</h1>
</body>
</html>
```

**CSS:**
```css
:root {
    --color-accent: #E50046;
}

body {
    font-family: system-ui, sans-serif;
    color: var(--color-text);
}
```

**Bash:**
```bash
# Dateien auflisten
ls -la /var/www/html/

# PHP-Version prüfen
php --version
```

**JSON:**
```json
{
    "name": "MarkdownWeb",
    "version": "1.0.0",
    "author": "Steffen Schwabe"
}
```

## Code ohne Highlighting

Wenn du keine Sprache angibst, wird der Code ohne Highlighting dargestellt:

````markdown
```
Das ist reiner Text in einem Code-Block.
Keine Sprache angegeben.
```
````
