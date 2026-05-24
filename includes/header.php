<?php
// Shared header partial for common page setup and asset loading.
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$projectRoot = realpath(dirname(__DIR__));
$basePath = '';

if ($documentRoot && $projectRoot && substr($projectRoot, 0, strlen($documentRoot)) === $documentRoot) {
    $basePath = str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
}

$assetPath = ($basePath ?: '') . '/assets/css/app.css';
$scriptPath = ($basePath ?: '') . '/assets/js/app.js';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet Management System</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($assetPath, ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="bg-fleet-canvas text-fleet-ink antialiased">
