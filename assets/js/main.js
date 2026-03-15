/**
 * MarkdownWeb – Haupt-JavaScript
 * Autor: Steffen Schwabe
 * Kein externes Framework – Vanilla JS
 */

(function () {
    'use strict';

    // ── Theme (Hell/Dunkel) ─────────────────────────────────────────────────

    const HTML = document.documentElement;
    const THEME_KEY = 'mw-theme';

    function getTheme() {
        const stored = localStorage.getItem(THEME_KEY);
        if (stored === 'dark' || stored === 'light') return stored;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        HTML.setAttribute('data-theme', theme);
        localStorage.setItem(THEME_KEY, theme);
    }

    function toggleTheme() {
        applyTheme(HTML.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    }

    applyTheme(getTheme());

    document.querySelectorAll('.theme-btn').forEach(btn => {
        btn.addEventListener('click', toggleTheme);
    });

    // ── Mobile Sidebar ──────────────────────────────────────────────────────

    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const menuBtn  = document.getElementById('menuBtn');

    function openSidebar() {
        sidebar?.classList.add('open');
        overlay?.classList.add('active');
        menuBtn?.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('active');
        menuBtn?.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    menuBtn?.addEventListener('click', () => {
        sidebar?.classList.contains('open') ? closeSidebar() : openSidebar();
    });

    overlay?.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });

    // ── Navigation Accordion (Ordner) ───────────────────────────────────────

    document.querySelectorAll('.nav-folder-btn').forEach(btn => {
        const targetId = btn.getAttribute('aria-controls');
        const children = document.getElementById(targetId);

        if (!children) return;

        // Aktive Seite → Elternordner aufklappen
        const hasActive = children.querySelector('.nav-link--active');
        if (hasActive) {
            btn.setAttribute('aria-expanded', 'true');
        } else {
            children.classList.add('collapsed');
        }

        btn.addEventListener('click', () => {
            const isExpanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', String(!isExpanded));
            children.classList.toggle('collapsed', isExpanded);
        });
    });

    // ── TOC: Aktiver Abschnitt hervorheben ──────────────────────────────────

    const tocLinks = document.querySelectorAll('.toc-nav a');

    if (tocLinks.length > 0) {
        const headings = Array.from(
            document.querySelectorAll('.page-content h2[id], .page-content h3[id]')
        );

        let activeTocLink = null;

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.getAttribute('id');
                        const link = document.querySelector(`.toc-nav a[href="#${CSS.escape(id)}"]`);
                        if (link) {
                            activeTocLink?.classList.remove('toc-active');
                            activeTocLink = link;
                            link.classList.add('toc-active');
                        }
                    }
                });
            },
            { rootMargin: '-80px 0px -60% 0px', threshold: 0 }
        );

        headings.forEach(h => observer.observe(h));
    }

    // ── Suche: Modal öffnen/schließen ───────────────────────────────────────

    const searchModal    = document.getElementById('searchModal');
    const searchTrigger  = document.getElementById('searchTrigger');
    const searchBackdrop = document.getElementById('searchBackdrop');
    const searchInput    = document.getElementById('searchInput');

    function openSearch() {
        searchModal?.removeAttribute('hidden');
        searchInput?.focus();
    }

    function closeSearch() {
        searchModal?.setAttribute('hidden', '');
        if (searchInput) {
            searchInput.value = '';
            document.getElementById('searchResults').innerHTML =
                '<p class="search-empty">Suchbegriff eingeben…</p>';
        }
    }

    searchTrigger?.addEventListener('click', openSearch);
    searchBackdrop?.addEventListener('click', closeSearch);

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            searchModal?.hasAttribute('hidden') ? openSearch() : closeSearch();
        }
        if (e.key === 'Escape' && !searchModal?.hasAttribute('hidden')) {
            closeSearch();
        }
    });

    // Esc-Badge im Modal
    document.querySelector('.search-esc-hint')?.addEventListener('click', closeSearch);

})();
