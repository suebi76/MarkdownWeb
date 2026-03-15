/**
 * MarkdownWeb – Suche (Server-seitig via SQLite FTS5)
 * Autor: Steffen Schwabe
 */

(function () {
    'use strict';

    const input   = document.getElementById('searchInput');
    const results = document.getElementById('searchResults');

    if (!input || !results) return;

    let debounceTimer = null;
    let currentIndex  = -1;
    let resultItems   = [];

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performSearch, 200);
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            navigate(1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            navigate(-1);
        } else if (e.key === 'Enter') {
            const active = results.querySelector('.search-result-item.active');
            if (active) {
                window.location.href = active.getAttribute('href');
            }
        }
    });

    function navigate(direction) {
        resultItems = Array.from(results.querySelectorAll('.search-result-item'));
        if (resultItems.length === 0) return;

        resultItems[currentIndex]?.classList.remove('active');
        currentIndex = Math.max(0, Math.min(currentIndex + direction, resultItems.length - 1));
        resultItems[currentIndex].classList.add('active');
        resultItems[currentIndex].scrollIntoView({ block: 'nearest' });
    }

    async function performSearch() {
        const query = input.value.trim();
        currentIndex = -1;

        if (query.length < 2) {
            results.innerHTML = '<p class="search-empty">Suchbegriff eingeben…</p>';
            return;
        }

        results.innerHTML = '<p class="search-empty">Suche läuft…</p>';

        try {
            const url      = SITE.searchUrl + '?q=' + encodeURIComponent(query);
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });

            if (!response.ok) throw new Error('Netzwerkfehler');

            const data = await response.json();
            renderResults(data.results || []);
        } catch {
            results.innerHTML = '<p class="search-no-results">Suche nicht verfügbar.</p>';
        }
    }

    function renderResults(items) {
        if (items.length === 0) {
            results.innerHTML = '<p class="search-no-results">Keine Ergebnisse gefunden.</p>';
            return;
        }

        const html = items.map(item => {
            const title   = escapeHtml(item.title ?? '');
            const excerpt = item.excerpt ?? '';  // Enthält bereits <mark>-Tags vom Server

            return `<a href="${escapeAttr(item.path)}" class="search-result-item">
                <div class="search-result-title">${title}</div>
                <div class="search-result-excerpt">${excerpt}</div>
            </a>`;
        }).join('');

        results.innerHTML = html;

        // Klick schließt Modal
        results.querySelectorAll('.search-result-item').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('searchModal')?.setAttribute('hidden', '');
            });
        });
    }

    function escapeHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function escapeAttr(str) {
        return str.replace(/"/g, '&quot;').replace(/</g, '%3C').replace(/>/g, '%3E');
    }

})();
