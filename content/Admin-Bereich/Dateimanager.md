---
title: Dateimanager
description: Dateien im Admin-Bereich verwalten, umbenennen und löschen
order: 3
---

# Dateimanager

Der Dateimanager zeigt alle Dateien im `content/`-Ordner als aufklappbare Baumstruktur.

## Aufrufen

Admin-Bereich → **Dateimanager**

## Ordner auf- und zuklappen

Klicke auf einen Ordnernamen um ihn auf- oder zuzuklappen. Alle Ordner sind standardmäßig aufgeklappt.

## Datei umbenennen

1. Klicke auf **Umbenennen** neben der gewünschten Datei
2. Gib den neuen Namen ein (mit Dateiendung, z. B. `neue-seite.md`)
3. Bestätige mit **Umbenennen**

> **Wichtig:** Wenn du eine Datei umbenennst, ändert sich die URL der Seite. Bestehende Links zu dieser Seite funktionieren dann nicht mehr. Aktualisiere interne Links entsprechend.

## Datei löschen

1. Klicke auf **Löschen** neben der Datei
2. Bestätige die Sicherheitsabfrage

Das Löschen kann nicht rückgängig gemacht werden.

## Ordner löschen

Ordner können im Dateimanager nicht direkt gelöscht werden. Lösche zuerst alle Dateien im Ordner – der leere Ordner wird beim nächsten Navigations-Cache-Rebuild ignoriert.

## Nach Änderungen

Umbenennen und Löschen leeren den Navigations-Cache automatisch. Die Änderung ist sofort in der Seitennavigation sichtbar.

Der Suchindex muss anschließend manuell neu aufgebaut werden: [Admin → Suchindex](/Admin-Bereich/Suchindex)
