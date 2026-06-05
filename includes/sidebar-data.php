<?php
// Main application navigation items.
// The sidebar template reads this list and applies the active style based on $activePage.

$navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => ($basePath ?: '') . '/dashboard.php', 'icon' => 'dashboard'],
    ['key' => 'vehicles', 'label' => 'Vehicles', 'href' => ($basePath ?: '') . '/modules/vehicles/index.php', 'icon' => 'car'],
    ['key' => 'logbook', 'label' => 'Vehicle Log Book', 'href' => ($basePath ?: '') . '/modules/logbook/index.php', 'icon' => 'book'],
    ['key' => 'vehicle-usage', 'label' => 'Vehicle Usage', 'href' => ($basePath ?: '') . '/modules/vehicle-usage/index.php', 'icon' => 'history'],
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
