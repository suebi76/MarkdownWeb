<?php
/**
 * Rekursives Nav-Item-Template
 * Erwartet: $item (array), $currentPath (string), $navItemTemplate (string), $baseUrl (string)
 */
$baseUrl ??= rtrim($config->get('base_url', ''), '/');

if ($item['type'] === 'folder'): ?>
<div class="nav-folder">
    <button
        class="nav-folder-btn"
        aria-expanded="false"
        aria-controls="folder-<?= htmlspecialchars(md5($item['name'])) ?>"
    >
        <svg class="nav-folder-icon" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
            <path d="M5 2.5L7 4.5H12.5V11.5H1.5V2.5H5Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/>
        </svg>
        <span><?= htmlspecialchars($item['name']) ?></span>
        <svg class="nav-chevron" width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true">
            <path d="M4 2L8 6L4 10" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>
    <ul class="nav-folder-children" id="folder-<?= htmlspecialchars(md5($item['name'])) ?>">
        <?php foreach ($item['children'] as $child): ?>
        <li><?php $item = $child; include $navItemTemplate; ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php else: ?>
<a
    href="<?= htmlspecialchars($baseUrl . $item['path']) ?>"
    class="nav-link<?= ($item['path'] === $currentPath) ? ' nav-link--active' : '' ?>"
    <?= ($item['path'] === $currentPath) ? 'aria-current="page"' : '' ?>
>
    <?= htmlspecialchars($item['name']) ?>
</a>
<?php endif; ?>
