---
title: Erweiterungen
description: MarkdownWeb um neue Markdown-Syntax und Funktionen erweitern
order: 3
---

# Erweiterungen

MarkdownWeb wurde von Grund auf erweiterbar gebaut. Neue Markdown-Syntaxelemente werden als **CommonMark-Extensions** in `src/Extensions/` hinzugefügt.

## Wie Erweiterungen funktionieren

`league/commonmark` arbeitet mit einem Extension-System. Jede Erweiterung implementiert das `ExtensionInterface` und registriert neue Parser, Renderer oder Transformer.

```
src/
└── Extensions/
    ├── MeineErweiterung.php
    └── ...
```

## Neue Erweiterung hinzufügen

### Schritt 1 – PHP-Klasse erstellen

```php
<?php
namespace MarkdownWeb\Extensions;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

class MeineErweiterung implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        // Hier Parser, Renderer, etc. registrieren
        $environment->addInlineParser(new MeinParser());
    }
}
```

### Schritt 2 – In MarkdownRenderer registrieren

In `src/MarkdownRenderer.php` in der `buildConverter()`-Methode:

```php
$environment->addExtension(new \MarkdownWeb\Extensions\MeineErweiterung());
```

Das ist alles.

## Geplante Erweiterungen

Diese Erweiterungen sind für zukünftige Versionen vorbereitet:

| Erweiterung | Beschreibung |
|---|---|
| **Callouts** | `> [!note]` Hinweisboxen (Info, Warnung, Tipp) |
| **Wikilinks** | `[[Seitenname]]` interne Links im Obsidian-Stil |
| **Embeds** | `![[seite]]` Inhalte anderer Seiten einbetten |

## Beispiel: Callout-Erweiterung

Callouts ermöglichen optisch hervorgehobene Hinweisboxen:

```markdown
> [!note]
> Das ist ein Hinweis.

> [!warning]
> Achtung – das ist eine Warnung.

> [!tip]
> Das ist ein nützlicher Tipp.
```

Um diese Syntax zu aktivieren, muss eine `CalloutExtension`-Klasse in `src/Extensions/` erstellt und registriert werden.

## Weiterführende Ressourcen

Die vollständige Dokumentation des CommonMark-Extension-Systems findest du auf der offiziellen Website der Bibliothek: `commonmark.thephpleague.com`
