<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/ajax.php';

// Service provider constants used by validation and display helpers.
const PROVIDER_ALLOWED_STATUSES = ['active', 'pending', 'inactive'];

// Starts the session used for service provider flash notifications if it is not already active.
function providerStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

// Returns the service providers page URL used after redirects.
function providerPageUrl(): string
{
    return '/fleet-system/modules/service-providers/';
}

// Returns the POST endpoint URL for service provider form submissions.
function providerHandlerUrl(): string
{
    return '/fleet-system/handlers/provider.php';
}

// Stores one-time service provider feedback in session flash state.
function providerSetFlash(array $payload): void
{
    providerStartSession();
    $_SESSION['provider_flash'] = $payload;
}

// Pulls and clears one-time service provider feedback from session flash state.
function providerPullFlash(): ?array
{
    providerStartSession();

    if (!isset($_SESSION['provider_flash']) || !is_array($_SESSION['provider_flash'])) {
        return null;
    }

    $flash = $_SESSION['provider_flash'];
    unset($_SESSION['provider_flash']);

    return $flash;
}

// Converts database status values into display labels for provider cards.
function providerNormalizeStatus(string $status): string
{
    return ucfirst($status);
}

// Loads service provider cards and any flash state needed by the page.
function providerFetchPageData(): array
{
    $flash = providerPullFlash();
    $notification = $flash['notification'] ?? null;
    $formData = $flash['form_data'] ?? [];
    $openModal = (bool) ($flash['open_modal'] ?? false);
    $formMode = $flash['form_mode'] ?? 'create';

    $providers = [];

    try {
        $statement = fleetDb()->query(
            'SELECT
                id,
                name,
                town,
                contact_person,
                phone,
                email,
                specialty,
                status
            FROM service_providers
            ORDER BY name ASC, id ASC'
        );

        foreach ($statement->fetchAll() as $row) {
            $providers[] = [
                'id' => (int) $row['id'],
                'name_raw' => $row['name'],
                'town_raw' => $row['town'] ?? '',
                'contact_person_raw' => $row['contact_person'] ?? '',
                'phone_raw' => $row['phone'] ?? '',
                'email_raw' => $row['email'] ?? '',
                'specialty_raw' => $row['specialty'] ?? '',
                'status_raw' => $row['status'],
                'name' => $row['name'],
                'town' => $row['town'] ?: '-',
                'contact_person' => $row['contact_person'] ?: '-',
                'contact' => $row['phone'] ?: '-',
                'email' => $row['email'] ?: '-',
                'specialty' => $row['specialty'] ?: 'General service support',
                'status' => providerNormalizeStatus((string) $row['status']),
            ];
        }
    } catch (Throwable $exception) {
        $notification = [
            'type' => 'error',
            'title' => 'Unable to load service providers',
            'message' => 'The service providers could not be loaded from the database right now.',
        ];
    }

    return [
        'providers' => $providers,
        'hasProviders' => count($providers) > 0,
        'providerNotification' => $notification,
        'providerFormData' => $formData,
        'shouldOpenProviderModal' => $openModal,
        'providerFormMode' => $formMode,
        'providerFormAction' => providerHandlerUrl(),
    ];
}

// Collects and trims raw POST values from the service provider form.
function providerBuildFormDataFromPost(): array
{
    return [
        'provider_id' => trim((string) ($_POST['provider_id'] ?? '')),
        'name' => trim((string) ($_POST['name'] ?? '')),
        'contact_person' => trim((string) ($_POST['contact_person'] ?? '')),
        'phone' => trim((string) ($_POST['phone'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'town' => trim((string) ($_POST['town'] ?? '')),
        'specialty' => trim((string) ($_POST['specialty'] ?? '')),
        'status' => strtolower(trim((string) ($_POST['status'] ?? 'active'))),
    ];
}

// Validates and normalizes submitted service provider form values.
function providerValidateFormData(array $formData): array
{
    if ($formData['name'] === '') {
        throw new RuntimeException('Provider name is required.');
    }

    if (!in_array($formData['status'], PROVIDER_ALLOWED_STATUSES, true)) {
        throw new RuntimeException('Please select a valid provider status.');
    }

    if ($formData['email'] !== '' && filter_var($formData['email'], FILTER_VALIDATE_EMAIL) === false) {
        throw new RuntimeException('Please enter a valid email address.');
    }

    return [
        'name' => $formData['name'],
        'contact_person' => $formData['contact_person'] === '' ? null : $formData['contact_person'],
        'phone' => $formData['phone'] === '' ? null : $formData['phone'],
        'email' => $formData['email'] === '' ? null : strtolower($formData['email']),
        'town' => $formData['town'] === '' ? null : $formData['town'],
        'specialty' => $formData['specialty'] === '' ? null : $formData['specialty'],
        'status' => $formData['status'],
    ];
}

// Handles both create and update requests for service providers.
function providerHandleCreateOrUpdate(string $action): void
{
    $formData = providerBuildFormDataFromPost();
    $responseStatus = 200;
    $responsePayload = [
        'success' => false,
        'message' => $action === 'update'
            ? 'The service provider could not be updated right now.'
            : 'The service provider could not be added right now.',
        'reload' => false,
    ];

    try {
        $validated = providerValidateFormData($formData);
        $pdo = fleetDb();

        if ($action === 'update') {
            $providerId = filter_var($formData['provider_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($providerId === false) {
                throw new RuntimeException('The selected service provider could not be identified.');
            }

            $exists = $pdo->prepare('SELECT COUNT(*) FROM service_providers WHERE id = :id');
            $exists->execute(['id' => $providerId]);
            if ((int) $exists->fetchColumn() === 0) {
                throw new RuntimeException('The selected service provider no longer exists.');
            }

            // Updates keep the same provider row while refreshing editable provider details.
            $statement = $pdo->prepare(
                'UPDATE service_providers SET
                    name = :name,
                    town = :town,
                    contact_person = :contact_person,
                    phone = :phone,
                    email = :email,
                    specialty = :specialty,
                    status = :status
                WHERE id = :provider_id'
            );
            $statement->bindValue(':provider_id', (int) $providerId, PDO::PARAM_INT);
        } else {
            // New providers are inserted directly with the validated form values.
            $statement = $pdo->prepare(
                'INSERT INTO service_providers (
                    name,
                    town,
                    contact_person,
                    phone,
                    email,
                    specialty,
                    status
                ) VALUES (
                    :name,
                    :town,
                    :contact_person,
                    :phone,
                    :email,
                    :specialty,
                    :status
                )'
            );
        }

        $statement->bindValue(':name', $validated['name']);
        $statement->bindValue(':town', $validated['town'], $validated['town'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':contact_person', $validated['contact_person'], $validated['contact_person'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':phone', $validated['phone'], $validated['phone'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':email', $validated['email'], $validated['email'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':specialty', $validated['specialty'], $validated['specialty'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':status', $validated['status']);
        $statement->execute();

        providerSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => $action === 'update' ? 'Service provider updated successfully' : 'Service provider added successfully',
                'message' => $action === 'update'
                    ? 'The service provider details have been updated successfully.'
                    : 'The service provider has been saved successfully.',
            ],
        ]);
        $responsePayload = [
            'success' => true,
            'message' => $action === 'update'
                ? 'The service provider details have been updated successfully.'
                : 'The service provider has been saved successfully.',
            'reload' => true,
            'action' => $action,
        ];
    } catch (RuntimeException $exception) {
        providerSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Service provider was not updated' : 'Service provider was not added',
                'message' => $exception->getMessage(),
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
        $responseStatus = 422;
        $responsePayload = [
            'success' => false,
            'message' => $exception->getMessage(),
            'reload' => false,
            'action' => $action,
        ];
    } catch (Throwable $exception) {
        providerSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Service provider was not updated' : 'Service provider was not added',
                'message' => $action === 'update'
                    ? 'A system error occurred while updating the service provider.'
                    : 'A system error occurred while saving the service provider.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
        $responseStatus = 500;
        $responsePayload = [
            'success' => false,
            'message' => $action === 'update'
                ? 'A system error occurred while updating the service provider.'
                : 'A system error occurred while saving the service provider.',
            'reload' => false,
            'action' => $action,
        ];
    }

    // Returns JSON to jQuery submissions and preserves redirects for normal PHP form posts.
    fleetFinishResponse(providerPageUrl(), $responsePayload, $responseStatus);
}

// Handles delete requests for service providers.
function providerHandleDelete(): void
{
    $providerId = filter_var((string) ($_POST['provider_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($providerId === false) {
        providerSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Service provider was not deleted',
                'message' => 'The selected service provider could not be identified.',
            ],
        ]);
        fleetFinishResponse(
            providerPageUrl(),
            [
                'success' => false,
                'message' => 'The selected service provider could not be identified.',
                'reload' => false,
            ],
            422
        );
    }

    $responseStatus = 200;
    $responsePayload = [
        'success' => false,
        'message' => 'The selected service provider could not be deleted.',
        'reload' => false,
    ];

    try {
        $statement = fleetDb()->prepare('DELETE FROM service_providers WHERE id = :id');
        $statement->execute(['id' => $providerId]);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('The selected service provider no longer exists.');
        }

        providerSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Service provider deleted successfully',
                'message' => 'The selected service provider has been removed.',
            ],
        ]);
        $responsePayload = [
            'success' => true,
            'message' => 'The selected service provider has been removed.',
            'reload' => true,
            'action' => 'delete',
        ];
    } catch (RuntimeException $exception) {
        providerSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Service provider was not deleted',
                'message' => $exception->getMessage(),
            ],
        ]);
        $responseStatus = 422;
        $responsePayload = [
            'success' => false,
            'message' => $exception->getMessage(),
            'reload' => false,
            'action' => 'delete',
        ];
    } catch (Throwable $exception) {
        providerSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Service provider was not deleted',
                'message' => 'A system error occurred while deleting the service provider.',
            ],
        ]);
        $responseStatus = 500;
        $responsePayload = [
            'success' => false,
            'message' => 'A system error occurred while deleting the service provider.',
            'reload' => false,
            'action' => 'delete',
        ];
    }

    // Returns JSON to jQuery submissions and preserves redirects for normal PHP form posts.
    fleetFinishResponse(providerPageUrl(), $responsePayload, $responseStatus);
}

// Dispatches incoming service provider POST requests by action type.
function providerHandleRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        fleetFinishResponse(
            providerPageUrl(),
            [
                'success' => false,
                'message' => 'Only POST requests are allowed for service provider actions.',
                'reload' => false,
            ],
            405
        );
    }

    $action = trim((string) ($_POST['provider_action'] ?? 'create'));

    if ($action === 'delete') {
        providerHandleDelete();
    }

    if ($action === 'update') {
        providerHandleCreateOrUpdate('update');
    }

    providerHandleCreateOrUpdate('create');
}

if (basename(__FILE__) === basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    // This file can be included for page data or called directly as the POST endpoint.
    providerHandleRequest();
}
