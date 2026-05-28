<?php
// Shared header partial for common page setup and asset loading.
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$projectRoot = realpath(dirname(__DIR__));
$basePath = '';

if ($documentRoot && $projectRoot && substr($projectRoot, 0, strlen($documentRoot)) === $documentRoot) {
    $basePath = str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
}

$assetVersion = file_exists($projectRoot . '/assets/css/app.css') ? filemtime($projectRoot . '/assets/css/app.css') : time();
$assetPath = ($basePath ?: '') . '/assets/css/app.css?v=' . $assetVersion;
$scriptPath = ($basePath ?: '') . '/assets/js/app.js';
$moduleScriptPath = ($basePath ?: '') . '/assets/js/module-modals.js';
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
