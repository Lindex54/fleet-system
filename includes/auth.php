<?php
declare(strict_types=1);

function fleetAuthStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function fleetAuthBasePath(): string
{
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $projectRoot = realpath(dirname(__DIR__));

    if ($documentRoot && $projectRoot && substr($projectRoot, 0, strlen($documentRoot)) === $documentRoot) {
        return str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
    }

    return '';
}

function fleetAuthCurrentPath(): string
{
    $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);

    return is_string($path) ? $path : '';
}

function fleetAuthIsPublicPath(string $path): bool
{
    $basePath = fleetAuthBasePath();
    $relativePath = $basePath !== '' && str_starts_with($path, $basePath)
        ? substr($path, strlen($basePath))
        : $path;

    $relativePath = '/' . ltrim($relativePath, '/');

    return in_array($relativePath, ['/', '/index.php', '/home.php', '/login.php', '/logout.php', '/change-password.php'], true);
}

function fleetAuthIsDriverPanelPath(string $path): bool
{
    $basePath = fleetAuthBasePath();
    $relativePath = $basePath !== '' && str_starts_with($path, $basePath)
        ? substr($path, strlen($basePath))
        : $path;

    return str_starts_with('/' . ltrim($relativePath, '/'), '/driver-panel/');
}

function fleetAuthLoginUrl(): string
{
    return (fleetAuthBasePath() ?: '') . '/login.php';
}

function fleetAuthAdminPasswordUrl(): string
{
    return (fleetAuthBasePath() ?: '') . '/change-password.php';
}

function fleetAuthRequireAdmin(): void
{
    fleetAuthStartSession();

    if ((string) ($_SESSION['user_role'] ?? '') !== 'admin' || empty($_SESSION['admin_user_id'])) {
        header('Location: ' . fleetAuthLoginUrl());
        exit;
    }

    $path = fleetAuthCurrentPath();
    $basePath = fleetAuthBasePath();
    $relativePath = $basePath !== '' && str_starts_with($path, $basePath)
        ? substr($path, strlen($basePath))
        : $path;
    $relativePath = '/' . ltrim($relativePath, '/');

    if ((int) ($_SESSION['must_change_password'] ?? 0) === 1 && $relativePath !== '/change-password.php') {
        header('Location: ' . fleetAuthAdminPasswordUrl());
        exit;
    }
}

function fleetAuthGuardCurrentPage(): void
{
    $path = fleetAuthCurrentPath();

    if (fleetAuthIsPublicPath($path) || fleetAuthIsDriverPanelPath($path)) {
        return;
    }

    fleetAuthRequireAdmin();
}
