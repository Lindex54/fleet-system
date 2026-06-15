<?php
// Navigation items for the driver-facing panel.

$driverNavItems = [
    ['key' => 'driver-dashboard', 'label' => 'Dashboard', 'href' => ($basePath ?: '') . '/driver-panel/', 'icon' => 'dashboard'],
    ['key' => 'driver-vehicle', 'label' => 'My Vehicle', 'href' => ($basePath ?: '') . '/driver-panel/my-vehicle', 'icon' => 'car'],
    ['key' => 'driver-trip-log', 'label' => 'Trip Log', 'href' => ($basePath ?: '') . '/driver-panel/trip-log', 'icon' => 'book'],
    ['key' => 'driver-history', 'label' => 'History', 'href' => ($basePath ?: '') . '/driver-panel/history', 'icon' => 'history'],
    ['key' => 'driver-messages', 'label' => 'Messages', 'href' => ($basePath ?: '') . '/driver-panel/messages', 'icon' => 'message'],
    ['key' => 'driver-profile', 'label' => 'Profile', 'href' => ($basePath ?: '') . '/driver-panel/profile', 'icon' => 'users'],
];
