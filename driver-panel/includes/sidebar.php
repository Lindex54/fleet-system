<?php
// Sidebar for the driver-facing panel using the same shell and theme as the main app.
$activePage = $activePage ?? '';
require_once __DIR__ . '/sidebar-data.php';
require_once dirname(__DIR__, 2) . '/includes/sidebar-icons.php';
require_once dirname(__DIR__, 2) . '/includes/internal-messages.php';
$mailboxUnreadBadge = fleetMessageFetchUnreadCountForCurrentSession();
?>
<aside id="app-sidebar" class="fixed inset-y-0 left-0 z-30 flex w-64 -translate-x-full flex-col bg-fleet-sidebar text-fleet-sidebar-text shadow-xl transition-transform duration-200 lg:translate-x-0">
    <div class="flex h-20 items-center gap-3 px-5">
        <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg bg-white p-1 shadow-sm">
            <img src="<?= htmlspecialchars($brandingLogoPath ?? (($basePath ?: '') . '/assets/images/branding/logo1.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="BUESMIS logo" class="h-full w-full object-contain">
        </div>
        <div>
            <p class="text-sm font-extrabold leading-5 text-white">Driver Panel</p>
            <p class="text-xs text-fleet-sidebar-muted">Busitema University Fleet</p>
        </div>
    </div>

    <nav class="sidebar-scroll-hidden min-h-0 flex-1 space-y-1 overflow-y-auto px-2 pb-4">
        <?php foreach ($driverNavItems as $item): ?>
            <?php $isActive = $activePage === $item['key']; ?>
            <a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>" class="flex h-10 items-center gap-3 rounded-lg px-4 text-sm font-semibold transition <?= $isActive ? 'bg-fleet-sidebar-active text-fleet-warning ring-1 ring-white/70' : 'text-fleet-sidebar-text hover:bg-fleet-sidebar-soft hover:text-white'; ?>">
                <?= sidebarIcon($item['icon']); ?>
                <span class="flex min-w-0 flex-1 items-center justify-between gap-2">
                    <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($item['key'] === 'driver-messages' && $mailboxUnreadBadge > 0): ?>
                        <span class="inline-flex min-w-7 items-center justify-center rounded-full bg-fleet-danger px-2 py-0.5 text-xs font-extrabold text-white"><?= $mailboxUnreadBadge; ?></span>
                    <?php endif; ?>
                </span>
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
