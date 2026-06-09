<?php

declare(strict_types=1);

// Detects whether the current request was sent through XMLHttpRequest/AJAX.
function fleetIsAjaxRequest(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Sends a JSON response for AJAX requests and stops script execution immediately.
function fleetJsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

// Returns JSON for AJAX requests or performs the existing redirect for standard PHP submissions.
function fleetFinishResponse(string $redirectUrl, array $payload, int $statusCode = 200): void
{
    if (fleetIsAjaxRequest()) {
        $payload['redirect'] = $payload['redirect'] ?? $redirectUrl;
        fleetJsonResponse($payload, $statusCode);
    }

    header('Location: ' . $redirectUrl);
    exit;
}
