<?php

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/internal-messages.php';
fleetAuthRequireAdmin();
header('Location: ' . (fleetMessageBasePath() ?: '') . '/modules/communications/?folder=sent');
exit;
