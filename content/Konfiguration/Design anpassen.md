---
title: Design anpassen
description: Farben, Schriften und Layout von MarkdownWeb anpassen
order: 2
---

# Design anpassen

Das gesamte Design von MarkdownWeb ist über **CSS Custom Properties** (Variablen) in `assets/css/main.css` steuerbar. Du musst keinen PHP-Code anfassen.

## Akzentfarbe ändern

Die schnellste Anpassung: Trage deine Wunschfarbe in `config.php` ein.

```php
'accent_color' => '#E50046',
```

Diese Farbe wird automatisch als CSS-Variable eingebunden und wirkt auf Links, Buttons, aktive Navigationspunkte und Fokus-Rahmen.

## CSS Custom Properties

Alle Design-Token findest du am Anfang von `assets/css/main.css` im `:root`-Block:

```css
:root {
    --color-accent:        #E50046;
    --color-bg:            #ffffff;
    --color-bg-sidebar:    #f7f7f8;
    --color-text:          #1a1a1a;
    --color-text-muted:    #6b7280;
    --color-border:        #e5e7eb;

    --sidebar-width:       260px;
    --toc-width:           220px;
    --content-max-width:   760px;

    --font-sans: system-ui, -apple-system, sans-serif;
    --font-mono: 'Cascadia Code', 'Consolas', monospace;
}
```

## Dark Mode anpassen

Die Dark-Mode-Farben stehen im `[data-theme="dark"]`-Block direkt darunter:

```css
[data-theme="dark"] {
    --color-bg:         #1e1e2e;
    --color-bg-sidebar: #181825;
    --color-text:       #cdd6f4;
    --color-border:     #313244;
}
```

## Breiten anpassen

```css
:root {
    --sidebar-width:      260px;  /* Linke Navigation */
    --toc-width:          220px;  /* Rechtes Inhaltsverzeichnis */
    --content-max-width:  760px;  /* Maximale Breite des Inhalts */
}
```

## Schriften

MarkdownWeb verwendet bewusst **System-Fonts** – dadurch sind keine externen Schriften nötig (DSGVO). Wenn du eine eigene Schrift verwenden möchtest, lade sie selbst herunter und binde sie per `@font-face` ein:

```css
@font-face {
    font-family: 'MeineSchrift';
    src: url('/assets/fonts/meine-schrift.woff2') format('woff2');
    font-display: swap;
}

:root {
    --font-sans: 'MeineSchrift', system-ui, sans-serif;
}
```

> **DSGVO-Hinweis:** Nutze niemals Google Fonts per `<link>`-Tag. Lade Schriften immer selbst herunter und hoste sie auf deinem eigenen Server.

## Logo hinzufügen

Passe in `templates/layout.php` den Sidebar-Header an:

```html
<a href="/" class="site-logo">
    <img src="/assets/img/logo.svg" alt="Mein Logo" height="28">
</a>
```
