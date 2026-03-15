# MarkdownWeb – Entwicklungsplan

Priorisierte Feature-Roadmap, basierend auf einer vollständigen Codebase-Analyse.
Priorisierung nach **Alltagsnutzen** und **Aufwand**.

---

## Priorität 1 — Hoher Nutzen, moderater Aufwand

| # | Feature | Warum | Aufwand |
|---|---------|-------|---------|
| 1 | **Neue Seiten/Ordner anlegen** | Aktuell muss man Dateien extern erstellen und hochladen. "Neue Seite"-Button im Dateimanager ist das offensichtlich fehlende Gegenstück zum Editor. | klein |
| 2 | **Draft/Entwurf-Status** | Frontmatter-Feld `draft: true` → Seite wird in der Navigation ausgeblendet und nur im Admin sichtbar. Verhindert halbfertige Inhalte auf der Frontseite. | klein |
| 3 | **Admin-Sidebar als Partial** | Die Sidebar ist in 6+ Admin-Seiten copy-pasted. Ein `admin/_sidebar.php`-Include spart Wartungsaufwand bei jedem neuen Feature. | klein |
| 4 | **Syntax-Highlighting im Editor** | CodeMirror 6 lokal einbinden (~60KB). Zeilennummern, Markdown-Syntax-Farben, bessere Tab-/Einrückungshilfe. Großer UX-Sprung. | mittel |
| 5 | **Breadcrumb-Navigation** | Pfad-Anzeige auf der Frontseite (Ordner > Unterordner > Seite). Verbessert Orientierung erheblich. | klein |

---

## Priorität 2 — Solider Mehrwert

| # | Feature | Warum | Aufwand |
|---|---------|-------|---------|
| 6 | **Tags/Schlagwörter** | Frontmatter-Feld `tags: [php, setup]` → Tag-Übersichtsseite, klickbare Tags unter jeder Seite. Zweite Ordnungsachse neben Ordnern. | mittel |
| 7 | **Suchverbesserungen** | Snippet-Vorschau in Ergebnissen, Highlighting des Suchbegriffs auf der Zielseite, Pagination bei vielen Treffern. | mittel |
| 8 | **Content Security Policy Headers** | `Content-Security-Policy`, `X-Content-Type-Options`, `X-Frame-Options` — Hardening ohne funktionale Änderung. | klein |
| 9 | **Bildvorschau im Editor** | Beim Einfügen von `![](bild.png)` eine kleine Vorschau unterhalb der Toolbar. Macht Bildreferenzen prüfbar ohne Vorschau-Modus. | klein |
| 10 | **Sortierung im Dateimanager** | Dateien nach Name/Datum/Größe sortieren, Ordner auf-/zuklappen-Buttons ("Alle öffnen / schließen"). | klein |

---

## Priorität 3 — Nice to have

| # | Feature | Warum | Aufwand |
|---|---------|-------|---------|
| 11 | **Versionshistorie** | Letzte 10 Versionen einer Datei speichern (z.B. in `cache/history/`). "Rückgängig"-Möglichkeit bei versehentlichem Überschreiben. | mittel |
| 12 | **Multi-User mit Rollen** | Zweiter Benutzer-Typ "Redakteur" (darf editieren, aber nicht löschen/konfigurieren). Relevant erst bei Team-Nutzung. | groß |
| 13 | **Rate Limiting auf `/_search`** | Einfacher Token-Bucket im Session-Speicher. Verhindert Missbrauch des öffentlichen Suchendpunkts. | klein |
| 14 | **Sitemap.xml generieren** | Automatische `sitemap.xml` aus der Navigation. Hilft bei SEO, wenn die Seite öffentlich ist. | klein |
| 15 | **Mermaid-Diagramme** | Lokales `mermaid.min.js` für Flowcharts/Sequenzdiagramme in Markdown-Codeblöcken. | klein |

---

## Priorität 4 — Langfristig / bei Bedarf

| # | Feature | Warum | Aufwand |
|---|---------|-------|---------|
| 16 | **Offline-Support (PWA)** | Service Worker + Manifest für Offline-Lesen. Nützlich für Dokumentation unterwegs. | mittel |
| 17 | **Asset-Minifizierung** | CSS/JS zusammenfassen und minifizieren. Relevant erst bei Performance-Problemen. | mittel |
| 18 | **Error-Logging** | Fehler in `cache/error.log` schreiben statt still schlucken. Debugging auf Shared Hosting. | klein |
| 19 | **Export (PDF/ZIP)** | Einzelne Seite oder ganzes Wiki als PDF/ZIP exportieren. | groß |
| 20 | **API-Endpunkte** | REST-API für Seiten lesen/schreiben. Ermöglicht externe Tools/Integrationen. | groß |

---

## Empfohlene nächste Releases

| Version | Features | Schwerpunkt |
|---------|----------|-------------|
| **v0.5** | 1–3 | Neue Seiten anlegen, Draft-Status, Sidebar-Partial |
| **v0.6** | 4–5 | CodeMirror-Editor, Breadcrumbs |
| **v0.7** | 6–8 | Tags, Suchverbesserungen, Security-Headers |
