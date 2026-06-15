<?php

require_once __DIR__ . '/../handlers/driver-panel.php';

driverPanelRequireAuthenticatedDriver();
driverPanelSetTripFlash([
    'notification' => [
        'type' => 'error',
        'title' => 'Driver inspection page removed',
        'message' => 'Pre-inspections are now managed from the admin side only.',
    ],
]);
header('Location: ' . driverPanelTripLogUrl());
exit;
