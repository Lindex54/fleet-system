<?php
// Shared sidebar partial for the main navigation across fleet management modules.
$activePage = $activePage ?? '';
$navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => ($basePath ?: '') . '/index.php', 'icon' => 'dashboard'],
    ['key' => 'vehicles', 'label' => 'Vehicles', 'href' => ($basePath ?: '') . '/modules/vehicles/index.php', 'icon' => 'car'],
    ['key' => 'logbook', 'label' => 'Vehicle Log Book', 'href' => ($basePath ?: '') . '/modules/logbook/index.php', 'icon' => 'book'],
    ['key' => 'drivers', 'label' => 'Drivers', 'href' => ($basePath ?: '') . '/modules/drivers/index.php', 'icon' => 'users'],
    ['key' => 'maintenance', 'label' => 'Maintenance', 'href' => ($basePath ?: '') . '/modules/maintenance/index.php', 'icon' => 'wrench'],
    ['key' => 'reports', 'label' => 'Reports', 'href' => ($basePath ?: '') . '/modules/reports/index.php', 'icon' => 'file'],
    ['key' => 'pre-inspection', 'label' => 'Pre-Inspection Report', 'href' => ($basePath ?: '') . '/modules/inspections/index.php', 'icon' => 'clipboard-list'],
    ['key' => 'post-inspection', 'label' => 'Post-Inspection Report', 'href' => ($basePath ?: '') . '/modules/post-inspection/index.php', 'icon' => 'clipboard-check'],
    ['key' => 'providers', 'label' => 'Service Providers', 'href' => ($basePath ?: '') . '/modules/service-providers/index.php', 'icon' => 'building'],
    ['key' => 'communications', 'label' => 'Communications', 'href' => ($basePath ?: '') . '/modules/communications/index.php', 'icon' => 'message'],
    ['key' => 'history', 'label' => 'Comm. History', 'href' => ($basePath ?: '') . '/modules/communication-history/index.php', 'icon' => 'history'],
    ['key' => 'estates', 'label' => 'Estates & Works', 'href' => ($basePath ?: '') . '/modules/estates/index.php', 'icon' => 'hard-hat'],
];

function sidebarIcon(string $name): string
{
    $attrs = 'class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';

    $paths = [
        'dashboard' => '<rect width="7" height="7" x="3" y="3" rx="1"></rect><rect width="7" height="7" x="14" y="3" rx="1"></rect><rect width="7" height="7" x="14" y="14" rx="1"></rect><rect width="7" height="7" x="3" y="14" rx="1"></rect>',
        'car' => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18.7 8c-.4-.6-1-.9-1.7-.9H7c-.7 0-1.3.3-1.7.9l-1.8 3.1C2.7 11.3 2 12.1 2 13v3c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle><path d="M5 11h14"></path>',
        'book' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"></path>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.9"></path><path d="M16 3.1a4 4 0 0 1 0 7.8"></path>',
        'wrench' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.7 2.7-3-3z"></path>',
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><path d="M8 13h8"></path><path d="M8 17h5"></path>',
        'clipboard-list' => '<rect width="16" height="18" x="4" y="4" rx="2"></rect><path d="M9 2h6a1 1 0 0 1 1 1v2H8V3a1 1 0 0 1 1-1z"></path><path d="M8 11h.01"></path><path d="M12 11h4"></path><path d="M8 16h.01"></path><path d="M12 16h4"></path>',
        'clipboard-check' => '<rect width="16" height="18" x="4" y="4" rx="2"></rect><path d="M9 2h6a1 1 0 0 1 1 1v2H8V3a1 1 0 0 1 1-1z"></path><path d="m9 14 2 2 4-5"></path>',
        'building' => '<path d="M3 21h18"></path><path d="M5 21V7l8-4v18"></path><path d="M19 21V11l-6-4"></path><path d="M9 9h1"></path><path d="M9 13h1"></path><path d="M9 17h1"></path>',
        'message' => '<path d="M21 15a2 2 0 0 1-2 2H8l-5 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>',
        'history' => '<path d="M3 12a9 9 0 1 0 3-6.7"></path><path d="M3 3v6h6"></path><path d="M12 7v5l3 2"></path>',
        'hard-hat' => '<path d="M2 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2"></path><path d="M4 18v-2a8 8 0 0 1 16 0v2"></path><path d="M10 10v8"></path><path d="M14 10v8"></path>',
    ];

    return '<svg ' . $attrs . '>' . ($paths[$name] ?? $paths['dashboard']) . '</svg>';
}
?>
<aside id="app-sidebar" class="fixed inset-y-0 left-0 z-30 flex w-64 -translate-x-full flex-col bg-fleet-sidebar text-fleet-sidebar-text shadow-xl transition-transform duration-200 lg:translate-x-0">
    <div class="flex h-20 items-center gap-3 px-5">
        <div class="flex h-9 w-9 items-center justify-center rounded bg-fleet-primary text-xs font-black text-white">BU</div>
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
        <a href="<?= htmlspecialchars(($basePath ?: '') . '/logout.php', ENT_QUOTES, 'UTF-8'); ?>" class="flex h-10 items-center gap-3 rounded-lg px-3 text-sm font-semibold text-fleet-sidebar-text hover:bg-fleet-sidebar-soft hover:text-white">
            <span class="flex h-5 w-5 items-center justify-center text-sm">&larr;</span>
            <span>Sign Out</span>
        </a>
    </div>
</aside>
<div id="sidebar-backdrop" class="fixed inset-0 z-20 hidden bg-black/40 lg:hidden"></div>
