<?php
// Navigation items for the driver-facing panel.

$driverNavItems = [
    ['key' => 'driver-dashboard', 'label' => 'Dashboard', 'href' => ($basePath ?: '') . '/driver-panel/index.php', 'icon' => 'dashboard'],
    ['key' => 'driver-vehicle', 'label' => 'My Vehicle', 'href' => ($basePath ?: '') . '/driver-panel/my-vehicle.php', 'icon' => 'car'],
    ['key' => 'driver-pre-trip', 'label' => 'Pre-Trip Inspection', 'href' => ($basePath ?: '') . '/driver-panel/pre-trip-inspection.php', 'icon' => 'clipboard-list'],
    ['key' => 'driver-trip-log', 'label' => 'Trip Log', 'href' => ($basePath ?: '') . '/driver-panel/trip-log.php', 'icon' => 'book'],
    ['key' => 'driver-history', 'label' => 'History', 'href' => ($basePath ?: '') . '/driver-panel/history.php', 'icon' => 'history'],
    ['key' => 'driver-messages', 'label' => 'Messages', 'href' => ($basePath ?: '') . '/driver-panel/messages.php', 'icon' => 'message'],
    ['key' => 'driver-profile', 'label' => 'Profile', 'href' => ($basePath ?: '') . '/driver-panel/profile.php', 'icon' => 'users'],
];
