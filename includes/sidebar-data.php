<?php
// Main application navigation items.
// The sidebar template reads this list and applies the active style based on $activePage.

$navItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => ($basePath ?: '') . '/dashboard.php', 'icon' => 'dashboard'],
    ['key' => 'vehicles', 'label' => 'Vehicles', 'href' => ($basePath ?: '') . '/modules/vehicles/', 'icon' => 'car'],
    ['key' => 'logbook', 'label' => 'Vehicle Log Book', 'href' => ($basePath ?: '') . '/modules/logbook/', 'icon' => 'book'],
    ['key' => 'vehicle-usage', 'label' => 'Vehicle Usage', 'href' => ($basePath ?: '') . '/modules/vehicle-usage/', 'icon' => 'history'],
    ['key' => 'drivers', 'label' => 'Drivers', 'href' => ($basePath ?: '') . '/modules/drivers/', 'icon' => 'users'],
    ['key' => 'admins', 'label' => 'Admins', 'href' => ($basePath ?: '') . '/modules/admins/', 'icon' => 'users'],
    ['key' => 'maintenance', 'label' => 'Maintenance', 'href' => ($basePath ?: '') . '/modules/maintenance/', 'icon' => 'wrench'],
    ['key' => 'reports', 'label' => 'Reports', 'href' => ($basePath ?: '') . '/modules/reports/', 'icon' => 'file'],
    ['key' => 'pre-inspection', 'label' => 'Pre-Inspection Report', 'href' => ($basePath ?: '') . '/modules/inspections/', 'icon' => 'clipboard-list'],
    ['key' => 'post-inspection', 'label' => 'Post-Inspection Report', 'href' => ($basePath ?: '') . '/modules/post-inspection/', 'icon' => 'clipboard-check'],
    ['key' => 'providers', 'label' => 'Service Providers', 'href' => ($basePath ?: '') . '/modules/service-providers/', 'icon' => 'building'],
    ['key' => 'communications', 'label' => 'Communications', 'href' => ($basePath ?: '') . '/modules/communications/', 'icon' => 'message'],
    ['key' => 'history', 'label' => 'Comm. History', 'href' => ($basePath ?: '') . '/modules/communication-history/', 'icon' => 'history'],
    ['key' => 'estates', 'label' => 'Estates & Works', 'href' => ($basePath ?: '') . '/modules/estates/', 'icon' => 'hard-hat'],
];
