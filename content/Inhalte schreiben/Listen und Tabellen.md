---
title: Listen und Tabellen
description: Aufzählungen, nummerierte Listen und Tabellen in Markdown
order: 2
---

# Listen und Tabellen

## Ungeordnete Liste

```markdown
- Erster Punkt
- Zweiter Punkt
- Dritter Punkt
```

- Erster Punkt
- Zweiter Punkt
- Dritter Punkt

Alternativ kannst du `*` oder `+` als Aufzählungszeichen verwenden.

## Geordnete Liste

```markdown
1. Erster Schritt
2. Zweiter Schritt
3. Dritter Schritt
```

1. Erster Schritt
2. Zweiter Schritt
3. Dritter Schritt

Die tatsächliche Zahl spielt keine Rolle – Markdown zählt automatisch:

```markdown
1. Erster
1. Zweiter
1. Dritter
```

## Verschachtelte Listen

```markdown
- Hauptpunkt
  - Unterpunkt
  - Noch ein Unterpunkt
    - Noch tiefer
- Nächster Hauptpunkt
```

- Hauptpunkt
  - Unterpunkt
  - Noch ein Unterpunkt
    - Noch tiefer
- Nächster Hauptpunkt

> Einrückung: genau **2 Leerzeichen** pro Ebene.

## Tabellen

```markdown
| Spalte 1 | Spalte 2 | Spalte 3 |
|----------|----------|----------|
| Wert A   | Wert B   | Wert C   |
| Wert D   | Wert E   | Wert F   |
```

| Spalte 1 | Spalte 2 | Spalte 3 |
|----------|----------|----------|
| Wert A   | Wert B   | Wert C   |
| Wert D   | Wert E   | Wert F   |

## Spaltenausrichtung

```markdown
| Links     | Zentriert  | Rechts    |
|:----------|:----------:|----------:|
| Text      | Text       | Text      |
| Mehr Text | Mehr Text  | Mehr Text |
```

| Links     | Zentriert  | Rechts    |
|:----------|:----------:|----------:|
| Text      | Text       | Text      |
| Mehr Text | Mehr Text  | Mehr Text |

Die Ausrichtung wird durch den Doppelpunkt (`:`) in der Trennzeile bestimmt:

| Syntax | Ausrichtung |
|---|---|
| `:---` | Linksbündig |
| `:---:` | Zentriert |
| `---:` | Rechtsbündig |
