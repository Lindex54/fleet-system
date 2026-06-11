<?php
// Shared sidebar partial for the main navigation across fleet management modules.
$activePage = $activePage ?? '';
require_once __DIR__ . '/sidebar-data.php';
require_once __DIR__ . '/sidebar-icons.php';
?>
<aside id="app-sidebar" class="fixed inset-y-0 left-0 z-30 flex w-64 -translate-x-full flex-col bg-fleet-sidebar text-fleet-sidebar-text shadow-xl transition-transform duration-200 lg:translate-x-0">
    <div class="flex h-20 items-center gap-3 px-5">
        <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg bg-white p-1 shadow-sm">
            <img src="<?= htmlspecialchars($brandingLogoPath ?? (($basePath ?: '') . '/assets/images/branding/logo1.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="BUESMIS logo" class="h-full w-full object-contain">
        </div>
        <div>
            <p class="text-sm font-extrabold leading-5 text-white">BUESMIS</p>
            <p class="text-xs text-fleet-sidebar-muted">Busitema University Estates MIS</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 px-2">
        <?php foreach ($navItems as $item): ?>
            <?php $isActive = $activePage === $item['key']; ?>
            <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>" class="flex h-10 items-center gap-3 rounded-lg px-4 text-sm font-semibold transition <?= $isActive ? 'bg-fleet-sidebar-active text-fleet-warning ring-1 ring-white/70' : 'text-fleet-sidebar-text hover:bg-fleet-sidebar-soft hover:text-white'; ?>">
                <?= sidebarIcon($item['icon']); ?>
                <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="border-t border-white/10 p-4">
        <a href="<?= htmlspecialchars(($basePath ?: '') . '/logout', ENT_QUOTES, 'UTF-8'); ?>" class="flex h-10 items-center gap-3 rounded-lg px-3 text-sm font-semibold text-fleet-sidebar-text hover:bg-fleet-sidebar-soft hover:text-white">
            <span class="flex h-5 w-5 items-center justify-center text-sm">&larr;</span>
            <span>Sign Out</span>
        </a>
    </div>
</aside>
<div id="sidebar-backdrop" class="fixed inset-0 z-20 hidden bg-black/40 lg:hidden"></div>
