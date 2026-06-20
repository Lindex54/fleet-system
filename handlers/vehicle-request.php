<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/ajax.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/activity-tracker.php';

function vehicleRequestStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function vehicleRequestBasePath(): string
{
    return fleetAuthBasePath() ?: '/fleet-system';
}

function vehicleRequestHomeUrl(): string
{
    return vehicleRequestBasePath() . '/home#request';
}

function vehicleRequestAdminPageUrl(): string
{
    return vehicleRequestBasePath() . '/modules/vehicle-requests/';
}

function vehicleRequestHandlerUrl(): string
{
    return vehicleRequestBasePath() . '/handlers/vehicle-request.php';
}

function vehicleRequestSetFlash(array $payload): void
{
    vehicleRequestStartSession();
    $_SESSION['vehicle_request_flash'] = $payload;
}

function vehicleRequestPullFlash(): ?array
{
    vehicleRequestStartSession();

    if (!isset($_SESSION['vehicle_request_flash']) || !is_array($_SESSION['vehicle_request_flash'])) {
        return null;
    }

    $flash = $_SESSION['vehicle_request_flash'];
    unset($_SESSION['vehicle_request_flash']);

    return $flash;
}

function vehicleRequestEnsureTable(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS vehicle_requests (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(160) NOT NULL,
            email_address VARCHAR(190) NOT NULL,
            phone_number VARCHAR(50) NOT NULL,
            job_title VARCHAR(160) NOT NULL,
            department VARCHAR(160) NOT NULL,
            reason TEXT NOT NULL,
            trip_destination VARCHAR(190) DEFAULT NULL,
            request_date DATE DEFAULT NULL,
            preferred_vehicle_type VARCHAR(100) DEFAULT NULL,
            purpose_of_trip VARCHAR(255) DEFAULT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'pending',
            reviewed_at DATETIME DEFAULT NULL,
            reviewed_by_admin_id INT DEFAULT NULL,
            reviewed_by_name VARCHAR(160) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_vehicle_requests_status (status),
            INDEX idx_vehicle_requests_request_date (request_date),
            INDEX idx_vehicle_requests_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

function vehicleRequestBuildPublicFormDataFromPost(): array
{
    return [
        'full_name' => trim((string) ($_POST['full_name'] ?? '')),
        'email_address' => trim((string) ($_POST['email_address'] ?? '')),
        'phone_number' => trim((string) ($_POST['phone_number'] ?? '')),
        'job_title' => trim((string) ($_POST['job_title'] ?? '')),
        'department' => trim((string) ($_POST['department'] ?? '')),
        'reason' => trim((string) ($_POST['reason'] ?? '')),
        'trip_destination' => trim((string) ($_POST['trip_destination'] ?? '')),
        'request_date' => trim((string) ($_POST['request_date'] ?? '')),
        'preferred_vehicle_type' => trim((string) ($_POST['preferred_vehicle_type'] ?? '')),
        'purpose_of_trip' => trim((string) ($_POST['purpose_of_trip'] ?? '')),
    ];
}

function vehicleRequestValidatePublicFormData(array $formData): array
{
    foreach (['full_name', 'email_address', 'phone_number', 'job_title', 'department', 'reason', 'trip_destination', 'request_date', 'purpose_of_trip'] as $field) {
        if ($formData[$field] === '') {
            throw new RuntimeException('Please complete all required request details before submitting.');
        }
    }

    if (!filter_var($formData['email_address'], FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Please enter a valid email address.');
    }

    if (!preg_match('/^\+?[0-9\s\-()]{7,20}$/', $formData['phone_number'])) {
        throw new RuntimeException('Please enter a valid phone number.');
    }

    $requestDate = DateTimeImmutable::createFromFormat('Y-m-d', $formData['request_date']);
    $dateErrors = DateTimeImmutable::getLastErrors();
    if (!$requestDate || ($dateErrors['warning_count'] ?? 0) > 0 || ($dateErrors['error_count'] ?? 0) > 0) {
        throw new RuntimeException('Please enter a valid request date.');
    }

    return [
        'full_name' => $formData['full_name'],
        'email_address' => strtolower($formData['email_address']),
        'phone_number' => $formData['phone_number'],
        'job_title' => $formData['job_title'],
        'department' => $formData['department'],
        'reason' => $formData['reason'],
        'trip_destination' => $formData['trip_destination'],
        'request_date' => $requestDate->format('Y-m-d'),
        'preferred_vehicle_type' => $formData['preferred_vehicle_type'] === '' ? null : $formData['preferred_vehicle_type'],
        'purpose_of_trip' => $formData['purpose_of_trip'],
    ];
}

function vehicleRequestNormalizeStatus(string $status): string
{
    return match ($status) {
        'reviewed' => 'Reviewed',
        default => 'Pending',
    };
}

function vehicleRequestFetchPublicPageData(): array
{
    $flash = vehicleRequestPullFlash();
    $notification = $flash['notification'] ?? null;
    $formData = $flash['form_data'] ?? [];

    if (!isset($formData['request_date']) || trim((string) $formData['request_date']) === '') {
        $formData['request_date'] = date('Y-m-d');
    }

    try {
        vehicleRequestEnsureTable(fleetDb());
    } catch (Throwable $exception) {
        $notification ??= [
            'type' => 'error',
            'title' => 'Request form unavailable',
            'message' => 'Vehicle requests cannot be submitted right now.',
        ];
    }

    return [
        'vehicleRequestNotification' => $notification,
        'vehicleRequestFormData' => $formData,
        'vehicleRequestFormAction' => vehicleRequestHandlerUrl(),
    ];
}

function vehicleRequestFetchAdminPageData(): array
{
    fleetAuthRequireAdmin();
    vehicleRequestEnsureTable(fleetDb());

    $flash = vehicleRequestPullFlash();
    $notification = $flash['notification'] ?? null;
    $search = trim((string) ($_GET['q'] ?? ''));
    $statusFilter = trim((string) ($_GET['status'] ?? ''));
    $rows = [];
    $summary = [
        'total' => 0,
        'pending' => 0,
        'reviewed' => 0,
        'today' => 0,
    ];

    try {
        $pdo = fleetDb();

        $summaryRow = $pdo->query(
            "SELECT
                COUNT(*) AS total_count,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) AS reviewed_count,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today_count
            FROM vehicle_requests"
        )->fetch() ?: [];

        $summary = [
            'total' => (int) ($summaryRow['total_count'] ?? 0),
            'pending' => (int) ($summaryRow['pending_count'] ?? 0),
            'reviewed' => (int) ($summaryRow['reviewed_count'] ?? 0),
            'today' => (int) ($summaryRow['today_count'] ?? 0),
        ];

        $sql = "SELECT
                id,
                full_name,
                email_address,
                phone_number,
                job_title,
                department,
                reason,
                trip_destination,
                request_date,
                preferred_vehicle_type,
                purpose_of_trip,
                status,
                reviewed_at,
                reviewed_by_name,
                created_at
            FROM vehicle_requests
            WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (
                full_name LIKE :search
                OR email_address LIKE :search
                OR phone_number LIKE :search
                OR job_title LIKE :search
                OR department LIKE :search
                OR reason LIKE :search
                OR trip_destination LIKE :search
                OR purpose_of_trip LIKE :search
            )";
            $params['search'] = '%' . $search . '%';
        }

        if ($statusFilter !== '' && in_array($statusFilter, ['pending', 'reviewed'], true)) {
            $sql .= ' AND status = :status';
            $params['status'] = $statusFilter;
        }

        $sql .= ' ORDER BY created_at DESC, id DESC LIMIT 200';

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        foreach ($statement->fetchAll() as $row) {
            $rows[] = [
                'id' => (int) $row['id'],
                'full_name' => (string) $row['full_name'],
                'email_address' => (string) $row['email_address'],
                'phone_number' => (string) $row['phone_number'],
                'job_title' => (string) $row['job_title'],
                'department' => (string) $row['department'],
                'reason' => (string) $row['reason'],
                'trip_destination' => (string) ($row['trip_destination'] ?? ''),
                'request_date' => $row['request_date'] ? date('d/m/Y', strtotime((string) $row['request_date'])) : '-',
                'preferred_vehicle_type' => (string) ($row['preferred_vehicle_type'] ?? '-'),
                'purpose_of_trip' => (string) ($row['purpose_of_trip'] ?? ''),
                'status' => (string) $row['status'],
                'status_label' => vehicleRequestNormalizeStatus((string) $row['status']),
                'reviewed_at' => $row['reviewed_at'] ? date('d/m/Y H:i', strtotime((string) $row['reviewed_at'])) : '-',
                'reviewed_by_name' => (string) ($row['reviewed_by_name'] ?? '-'),
                'created_at' => $row['created_at'] ? date('d/m/Y H:i', strtotime((string) $row['created_at'])) : '-',
            ];
        }
    } catch (Throwable $exception) {
        $notification ??= [
            'type' => 'error',
            'title' => 'Requests could not be loaded',
            'message' => 'Vehicle request notifications are unavailable right now.',
        ];
    }

    return [
        'vehicleRequestAdminRows' => $rows,
        'vehicleRequestAdminHasRows' => $rows !== [],
        'vehicleRequestAdminSummary' => $summary,
        'vehicleRequestAdminNotification' => $notification,
        'vehicleRequestAdminSearch' => $search,
        'vehicleRequestAdminStatusFilter' => $statusFilter,
        'vehicleRequestAdminPageUrl' => vehicleRequestAdminPageUrl(),
        'vehicleRequestFormAction' => vehicleRequestHandlerUrl(),
    ];
}

function vehicleRequestHandleCreate(): void
{
    $formData = vehicleRequestBuildPublicFormDataFromPost();
    $responsePayload = [
        'success' => false,
        'message' => 'The vehicle request could not be submitted.',
        'reload' => false,
        'reset_form' => false,
        'redirect' => '',
    ];
    $responseStatus = 200;

    try {
        $validated = vehicleRequestValidatePublicFormData($formData);
        $pdo = fleetDb();
        vehicleRequestEnsureTable($pdo);

        $statement = $pdo->prepare(
            "INSERT INTO vehicle_requests (
                full_name,
                email_address,
                phone_number,
                job_title,
                department,
                reason,
                trip_destination,
                request_date,
                preferred_vehicle_type,
                purpose_of_trip,
                status
            ) VALUES (
                :full_name,
                :email_address,
                :phone_number,
                :job_title,
                :department,
                :reason,
                :trip_destination,
                :request_date,
                :preferred_vehicle_type,
                :purpose_of_trip,
                'pending'
            )"
        );
        $statement->execute($validated);

        vehicleRequestSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Vehicle request submitted',
                'message' => 'Your vehicle request has been sent to the transport office successfully.',
            ],
        ]);

        $responsePayload = [
            'success' => true,
            'message' => 'Your vehicle request has been sent to the transport office successfully.',
            'reload' => false,
            'reset_form' => true,
            'redirect' => '',
        ];
    } catch (RuntimeException $exception) {
        vehicleRequestSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Vehicle request not submitted',
                'message' => $exception->getMessage(),
            ],
            'form_data' => $formData,
        ]);
        $responsePayload['message'] = $exception->getMessage();
        $responseStatus = 422;
    } catch (Throwable $exception) {
        vehicleRequestSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Vehicle request not submitted',
                'message' => 'A system error occurred while sending the vehicle request.',
            ],
            'form_data' => $formData,
        ]);
        $responsePayload['message'] = 'A system error occurred while sending the vehicle request.';
        $responseStatus = 500;
    }

    fleetFinishResponse(vehicleRequestHomeUrl(), $responsePayload, $responseStatus);
}

function vehicleRequestHandleMarkReviewed(): void
{
    fleetAuthRequireAdmin();
    $requestId = filter_var((string) ($_POST['request_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($requestId === false) {
        vehicleRequestSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Request not updated',
                'message' => 'The selected request could not be identified.',
            ],
        ]);
        header('Location: ' . vehicleRequestAdminPageUrl());
        exit;
    }

    try {
        $pdo = fleetDb();
        vehicleRequestEnsureTable($pdo);

        $statement = $pdo->prepare(
            "UPDATE vehicle_requests
            SET
                status = 'reviewed',
                reviewed_at = NOW(),
                reviewed_by_admin_id = :reviewed_by_admin_id,
                reviewed_by_name = :reviewed_by_name
            WHERE id = :id AND status <> 'reviewed'"
        );
        $statement->execute([
            'reviewed_by_admin_id' => (int) ($_SESSION['admin_user_id'] ?? 0),
            'reviewed_by_name' => (string) ($_SESSION['user_name'] ?? 'Admin'),
            'id' => (int) $requestId,
        ]);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('This request is already reviewed or no longer exists.');
        }

        fleetTrackActivity([
            'module_key' => 'vehicle-requests',
            'action_key' => 'reviewed',
            'action_label' => 'Reviewed vehicle request',
            'description' => 'Reviewed a public vehicle request notification.',
            'target_type' => 'vehicle_request',
            'target_id' => (int) $requestId,
            'target_label' => 'Vehicle request #' . $requestId,
        ], $pdo);

        vehicleRequestSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Request marked as reviewed',
                'message' => 'The vehicle request has been marked as reviewed.',
            ],
        ]);
    } catch (RuntimeException $exception) {
        vehicleRequestSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Request not updated',
                'message' => $exception->getMessage(),
            ],
        ]);
    } catch (Throwable $exception) {
        vehicleRequestSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Request not updated',
                'message' => 'A system error occurred while updating the request.',
            ],
        ]);
    }

    header('Location: ' . vehicleRequestAdminPageUrl());
    exit;
}

function vehicleRequestHandleRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        fleetFinishResponse(
            vehicleRequestHomeUrl(),
            [
                'success' => false,
                'message' => 'Invalid request method.',
                'reload' => false,
            ],
            405
        );
    }

    $action = strtolower(trim((string) ($_POST['vehicle_request_action'] ?? 'create')));

    if ($action === 'mark_reviewed') {
        vehicleRequestHandleMarkReviewed();
    }

    vehicleRequestHandleCreate();
}

if (basename(__FILE__) === basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    vehicleRequestHandleRequest();
}
