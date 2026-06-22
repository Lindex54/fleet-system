<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/ajax.php';
require_once __DIR__ . '/../includes/activity-tracker.php';
require_once __DIR__ . '/../includes/driver-login-verification.php';

// Driver panel helpers use the existing schema and gracefully fall back while auth is not yet implemented.
const DRIVER_PANEL_PRETRIP_CHECKLIST = [
    'Engine oil level',
    'Coolant / radiator',
    'Tyres and pressure',
    'Brakes',
    'Lights and indicators',
    'Battery / electricals',
    'Mirrors and windscreen',
    'Seatbelts and safety kit',
];

function driverPanelStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function driverPanelHandlerUrl(): string
{
    return '/fleet-system/handlers/driver-panel.php';
}

function driverPanelDashboardUrl(): string
{
    return '/fleet-system/driver-panel/';
}

function driverPanelLoginUrl(): string
{
    return '/fleet-system/login';
}

function driverPanelPasswordChangeUrl(): string
{
    return '/fleet-system/driver-panel/change-password';
}

function driverPanelVehicleUrl(): string
{
    return '/fleet-system/driver-panel/my-vehicle';
}

function driverPanelPreTripUrl(): string
{
    return '/fleet-system/driver-panel/pre-trip-inspection';
}

function driverPanelTripLogUrl(): string
{
    return '/fleet-system/driver-panel/trip-log';
}

function driverPanelLogbookUrl(): string
{
    return '/fleet-system/driver-panel/logbook';
}

function driverPanelRequireAuthenticatedDriver(bool $allowPasswordChange = false): void
{
    driverPanelStartSession();

    $driverId = filter_var((string) ($_SESSION['driver_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $role = (string) ($_SESSION['user_role'] ?? '');

    if ($driverId === false || $role !== 'driver') {
        header('Location: ' . driverPanelLoginUrl());
        exit;
    }

    if (!$allowPasswordChange && (int) ($_SESSION['must_change_password'] ?? 0) === 1) {
        header('Location: ' . driverPanelPasswordChangeUrl());
        exit;
    }
}

function driverPanelSetFlash(string $key, array $payload): void
{
    driverPanelStartSession();
    $_SESSION[$key] = $payload;
}

function driverPanelPullFlash(string $key): ?array
{
    driverPanelStartSession();

    if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
        return null;
    }

    $flash = $_SESSION[$key];
    unset($_SESSION[$key]);

    return $flash;
}

function driverPanelSetPreTripFlash(array $payload): void
{
    driverPanelSetFlash('driver_panel_pre_trip_flash', $payload);
}

function driverPanelPullPreTripFlash(): ?array
{
    return driverPanelPullFlash('driver_panel_pre_trip_flash');
}

function driverPanelSetTripFlash(array $payload): void
{
    driverPanelSetFlash('driver_panel_trip_flash', $payload);
}

function driverPanelPullTripFlash(): ?array
{
    return driverPanelPullFlash('driver_panel_trip_flash');
}

function driverPanelSetMessagesFlash(array $payload): void
{
    driverPanelSetFlash('driver_panel_messages_flash', $payload);
}

function driverPanelPullMessagesFlash(): ?array
{
    return driverPanelPullFlash('driver_panel_messages_flash');
}

function driverPanelSetPasswordFlash(array $payload): void
{
    driverPanelSetFlash('driver_panel_password_flash', $payload);
}

function driverPanelPullPasswordFlash(): ?array
{
    return driverPanelPullFlash('driver_panel_password_flash');
}

function driverPanelHandlePasswordChange(): void
{
    driverPanelRequireAuthenticatedDriver(true);

    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (strlen($newPassword) < 8) {
        driverPanelSetPasswordFlash([
            'type' => 'error',
            'message' => 'Your new password must be at least 8 characters long.',
        ]);
        fleetFinishResponse(
            driverPanelPasswordChangeUrl(),
            [
                'success' => false,
                'message' => 'Your new password must be at least 8 characters long.',
                'reload' => false,
            ],
            422
        );
    }

    if ($newPassword !== $confirmPassword) {
        driverPanelSetPasswordFlash([
            'type' => 'error',
            'message' => 'The new password and confirmation do not match.',
        ]);
        fleetFinishResponse(
            driverPanelPasswordChangeUrl(),
            [
                'success' => false,
                'message' => 'The new password and confirmation do not match.',
                'reload' => false,
            ],
            422
        );
    }

    try {
        $pdo = fleetDb();
        $statement = $pdo->prepare(
            'UPDATE users
            SET password_hash = :password_hash,
                must_change_password = 0
            WHERE id = :user_id
                AND role = \'driver\''
        );
        $statement->execute([
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'user_id' => (int) $_SESSION['user_id'],
        ]);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('Password could not be updated.');
        }

        $_SESSION['must_change_password'] = 0;
        fleetTrackAuthEvent([
            'user_id' => (int) $_SESSION['user_id'],
            'name' => (string) ($_SESSION['user_name'] ?? ''),
            'role' => 'driver',
            'event_type' => 'password_changed',
            'event_description' => 'Driver password changed successfully',
        ], $pdo);
        fleetTrackActivity([
            'module_key' => 'driver-panel',
            'action_key' => 'password_changed',
            'action_label' => 'Changed password',
            'description' => 'Driver updated account password.',
            'target_type' => 'account',
            'target_id' => (int) $_SESSION['user_id'],
            'target_label' => (string) ($_SESSION['user_name'] ?? 'Driver account'),
        ], $pdo);
        driverPanelSetPasswordFlash([
            'type' => 'success',
            'message' => 'Your password has been updated.',
        ]);
        // Sends JSON to jQuery requests while preserving the original redirect flow for normal posts.
        fleetFinishResponse(
            driverPanelDashboardUrl(),
            [
                'success' => true,
                'message' => 'Your password has been updated.',
                'redirect' => driverPanelDashboardUrl(),
                'reload' => false,
            ]
        );
    } catch (Throwable $exception) {
        driverPanelSetPasswordFlash([
            'type' => 'error',
            'message' => 'Your password could not be updated right now.',
        ]);
        fleetFinishResponse(
            driverPanelPasswordChangeUrl(),
            [
                'success' => false,
                'message' => 'Your password could not be updated right now.',
                'reload' => false,
            ],
            500
        );
    }
}

function driverPanelEnsureIncidentReportsTable(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS incident_reports (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            driver_id INT UNSIGNED NOT NULL,
            vehicle_id INT UNSIGNED DEFAULT NULL,
            incident_type ENUM('breakdown','accident','unusual_issue') NOT NULL,
            incident_date DATE NOT NULL,
            location VARCHAR(150) DEFAULT NULL,
            subject VARCHAR(180) NOT NULL,
            description TEXT NOT NULL,
            urgency ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
            status ENUM('reported','under_review','resolved') NOT NULL DEFAULT 'reported',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_incident_reports_driver_id (driver_id),
            KEY idx_incident_reports_vehicle_id (vehicle_id),
            CONSTRAINT fk_incident_reports_driver
                FOREIGN KEY (driver_id) REFERENCES drivers(id)
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_incident_reports_vehicle
                FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
                ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

function driverPanelNormalizeVehicleStatus(string $status): array
{
    return match ($status) {
        'active' => ['label' => 'Active', 'classes' => 'border-green-200 bg-fleet-success-soft text-fleet-success'],
        'maintenance' => ['label' => 'Maintenance', 'classes' => 'border-orange-200 bg-fleet-warning-soft text-fleet-warning-strong'],
        'grounded' => ['label' => 'Grounded', 'classes' => 'border-red-200 bg-fleet-danger-soft text-fleet-danger'],
        'disposed' => ['label' => 'Disposed', 'classes' => 'border-slate-200 bg-slate-100 text-slate-600'],
        default => ['label' => ucfirst($status), 'classes' => 'border-slate-200 bg-slate-100 text-slate-600'],
    };
}

function driverPanelFormatDate(?string $date): string
{
    if ($date === null || $date === '') {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d M Y', $timestamp) : $date;
}

function driverPanelFormatMoney(?float $amount): string
{
    if ($amount === null) {
        return '-';
    }

    return 'UGX ' . number_format($amount, 0);
}

function driverPanelBuildLicenseExpiryStatus(?string $expiryDate): array
{
    if ($expiryDate === null || trim($expiryDate) === '') {
        return [
            'label' => 'Not set',
            'classes' => 'border-slate-200 bg-slate-100 text-slate-600',
        ];
    }

    $expiry = DateTimeImmutable::createFromFormat('Y-m-d', $expiryDate);
    if (!$expiry) {
        return [
            'label' => 'Not set',
            'classes' => 'border-slate-200 bg-slate-100 text-slate-600',
        ];
    }

    $today = new DateTimeImmutable(date('Y-m-d'));
    $daysLeft = (int) $today->diff($expiry)->format('%r%a');
    $expiryLabel = $expiry->format('j F Y');

    if ($daysLeft < 0) {
        return [
            'label' => 'Expired on ' . $expiryLabel,
            'classes' => 'border-red-200 bg-fleet-danger-soft text-fleet-danger',
        ];
    }

    if ($daysLeft === 0) {
        return [
            'label' => 'Expires today (' . $expiryLabel . ')',
            'classes' => 'border-orange-200 bg-fleet-warning-soft text-fleet-warning-strong',
        ];
    }

    $interval = $today->diff($expiry);
    $yearsLeft = $interval->y;
    $monthsLeft = $interval->m;
    $daysRemainder = $interval->d;
    $parts = [];

    if ($yearsLeft > 0) {
        $parts[] = $yearsLeft . ' year' . ($yearsLeft === 1 ? '' : 's');
    }

    if ($monthsLeft > 0) {
        $parts[] = $monthsLeft . ' month' . ($monthsLeft === 1 ? '' : 's');
    }

    if ($daysRemainder > 0) {
        $parts[] = $daysRemainder . ' day' . ($daysRemainder === 1 ? '' : 's');
    }

    if ($parts === []) {
        $parts[] = 'less than 1 day';
    }

    return [
        'label' => implode(', ', $parts) . ' left (expires ' . $expiryLabel . ')',
        'classes' => $daysLeft <= 30
            ? 'border-orange-200 bg-fleet-warning-soft text-fleet-warning-strong'
            : 'border-green-200 bg-fleet-success-soft text-fleet-success',
    ];
}

function driverPanelBuildUploadUrl(?string $storedPath): string
{
    if ($storedPath === null || trim($storedPath) === '') {
        return '';
    }

    return '/fleet-system/' . ltrim($storedPath, '/');
}

function driverPanelUploadIsImage(?string $storedPath): bool
{
    $extension = strtolower(pathinfo((string) $storedPath, PATHINFO_EXTENSION));

    return in_array($extension, ['jpg', 'jpeg', 'png'], true);
}

function driverPanelFindCurrentDriver(PDO $pdo): ?array
{
    driverPanelRequireAuthenticatedDriver();
    $driverId = filter_var((string) $_SESSION['driver_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($driverId === false) {
        header('Location: ' . driverPanelLoginUrl());
        exit;
    }

    $statement = $pdo->prepare(
        'SELECT
            d.id,
            d.user_id,
            d.driver_code,
            d.full_name,
            d.employee_id,
            d.phone,
            d.email,
            d.gender,
            d.national_id_number,
            d.license_number,
            d.license_classes,
            d.license_issue_date,
            d.license_issuing_authority,
            d.license_expiry,
            d.driver_photo,
            d.national_id_photo,
            d.driving_license_scan,
            d.status,
            d.created_at,
            d.updated_at,
            u.username,
            u.must_change_password,
            COALESCE(dep.name, \'Transport\') AS department_name
        FROM drivers d
        INNER JOIN users u ON u.id = d.user_id
        LEFT JOIN departments dep ON dep.id = d.department_id
        WHERE d.id = :id
            AND u.role = \'driver\'
            AND u.status = \'active\'
            AND d.status = \'active\'
        LIMIT 1'
    );
    $statement->execute(['id' => (int) $driverId]);

    $driver = $statement->fetch();

    if (!$driver) {
        $_SESSION = [];
        header('Location: ' . driverPanelLoginUrl());
        exit;
    }

    $_SESSION['must_change_password'] = (int) $driver['must_change_password'];

    if ((int) $driver['must_change_password'] === 1) {
        header('Location: ' . driverPanelPasswordChangeUrl());
        exit;
    }

    // Keep the driver dashboard message center aligned with the welcome email flow.
    driverLoginVerificationStoreWelcomeMessage($pdo, [
        'driver_id' => (int) $driver['id'],
        'user_id' => (int) $driver['user_id'],
        'email' => (string) ($driver['email'] ?? ''),
        'name' => (string) ($driver['full_name'] ?? 'Driver'),
        'full_name' => (string) ($driver['full_name'] ?? 'Driver'),
    ]);

    return $driver;
}

function driverPanelFetchAssignedVehicle(PDO $pdo, int $driverId): ?array
{
    $statement = $pdo->prepare(
        'SELECT
            v.id,
            v.registration_no,
            v.make,
            v.model,
            v.manufacture_year,
            v.vehicle_type,
            v.fuel_type,
            v.current_mileage,
            v.insurance_expiry,
            v.status,
            v.notes,
            COALESCE(dep.name, \'Unassigned\') AS department_name,
            va.assigned_at
        FROM vehicle_assignments va
        INNER JOIN vehicles v ON v.id = va.vehicle_id
        LEFT JOIN departments dep ON dep.id = v.department_id
        WHERE va.driver_id = :driver_id
            AND va.released_at IS NULL
        ORDER BY va.assigned_at DESC, va.id DESC
        LIMIT 1'
    );
    $statement->execute(['driver_id' => $driverId]);

    $vehicle = $statement->fetch();

    if (!$vehicle) {
        return null;
    }

    return driverPanelMapVehicleRow($vehicle);
}

function driverPanelEnsureSecondaryVehicleTable(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS driver_secondary_vehicles (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            driver_id INT UNSIGNED NOT NULL,
            vehicle_id INT UNSIGNED NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_driver_secondary_vehicle (driver_id, vehicle_id),
            KEY idx_driver_secondary_vehicle_vehicle (vehicle_id),
            CONSTRAINT fk_driver_secondary_vehicle_driver
                FOREIGN KEY (driver_id) REFERENCES drivers(id)
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_driver_secondary_vehicle_vehicle
                FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

function driverPanelMapVehicleRow(array $vehicle): array
{
    $status = driverPanelNormalizeVehicleStatus((string) $vehicle['status']);

    return [
        'id' => (int) $vehicle['id'],
        'registration_no' => $vehicle['registration_no'],
        'make_model' => trim($vehicle['make'] . ' ' . $vehicle['model']),
        'manufacture_year' => $vehicle['manufacture_year'] ?: '-',
        'vehicle_type' => ucfirst((string) $vehicle['vehicle_type']),
        'fuel_type' => ucfirst((string) $vehicle['fuel_type']),
        'current_mileage' => number_format((int) $vehicle['current_mileage']) . ' km',
        'current_mileage_raw' => (int) $vehicle['current_mileage'],
        'insurance_expiry' => $vehicle['insurance_expiry'],
        'insurance_expiry_label' => driverPanelFormatDate($vehicle['insurance_expiry']),
        'department_name' => $vehicle['department_name'],
        'assigned_at' => driverPanelFormatDate($vehicle['assigned_at']),
        'notes' => $vehicle['notes'] ?: 'No vehicle notes available.',
        'status_label' => $status['label'],
        'status_classes' => $status['classes'],
    ];
}

function driverPanelFetchOtherVehicles(PDO $pdo, int $driverId, ?int $assignedVehicleId): array
{
    driverPanelEnsureSecondaryVehicleTable($pdo);

    $statement = $pdo->prepare(
        'SELECT
            v.id,
            v.registration_no,
            v.make,
            v.model,
            v.manufacture_year,
            v.vehicle_type,
            v.fuel_type,
            v.current_mileage,
            v.insurance_expiry,
            v.status,
            v.notes,
            COALESCE(dep.name, \'Unassigned\') AS department_name,
            NULL AS assigned_at
        FROM driver_secondary_vehicles dsv
        INNER JOIN vehicles v ON v.id = dsv.vehicle_id
        LEFT JOIN departments dep ON dep.id = v.department_id
        WHERE dsv.driver_id = :driver_id
        ORDER BY v.registration_no ASC'
    );
    $statement->execute(['driver_id' => $driverId]);

    $vehicles = [];

    foreach ($statement->fetchAll() as $vehicle) {
        if ($assignedVehicleId !== null && (int) $vehicle['id'] === $assignedVehicleId) {
            continue;
        }

        $vehicles[] = driverPanelMapVehicleRow($vehicle);
    }

    return $vehicles;
}

function driverPanelBuildTripVehicleOptions(?array $assignedVehicle, array $otherVehicles): array
{
    $vehicles = [];

    if ($assignedVehicle !== null) {
        $vehicles[] = $assignedVehicle + ['option_label' => 'Assigned vehicle'];
    }

    foreach ($otherVehicles as $vehicle) {
        $vehicles[] = $vehicle + ['option_label' => 'Other vehicle'];
    }

    return $vehicles;
}

function driverPanelFindTripVehicleOption(array $tripVehicleOptions, int $vehicleId): ?array
{
    foreach ($tripVehicleOptions as $vehicle) {
        if ((int) $vehicle['id'] === $vehicleId) {
            return $vehicle;
        }
    }

    return null;
}

function driverPanelFetchLatestTrip(PDO $pdo, int $driverId): ?array
{
    $statement = $pdo->prepare(
        'SELECT
            vl.id,
            vl.trip_date,
            vl.departure_location,
            vl.destination,
            vl.purpose,
            vl.odometer_start,
            vl.odometer_end,
            vl.distance_km,
            v.registration_no
        FROM vehicle_logs vl
        INNER JOIN vehicles v ON v.id = vl.vehicle_id
        WHERE vl.driver_id = :driver_id
        ORDER BY vl.trip_date DESC, vl.id DESC
        LIMIT 1'
    );
    $statement->execute(['driver_id' => $driverId]);
    $trip = $statement->fetch();

    if (!$trip) {
        return null;
    }

    $isInProgress = $trip['odometer_start'] !== null && $trip['odometer_end'] === null;

    return [
        'id' => (int) $trip['id'],
        'date' => driverPanelFormatDate($trip['trip_date']),
        'date_raw' => $trip['trip_date'],
        'vehicle' => $trip['registration_no'],
        'from' => $trip['departure_location'],
        'to' => $trip['destination'],
        'purpose' => $trip['purpose'],
        'odometer_start' => $trip['odometer_start'] !== null ? number_format((int) $trip['odometer_start']) . ' km' : '-',
        'odometer_end' => $trip['odometer_end'] !== null ? number_format((int) $trip['odometer_end']) . ' km' : '-',
        'distance' => $trip['distance_km'] !== null ? number_format((int) $trip['distance_km']) . ' km' : '-',
        'status_label' => $isInProgress ? 'Trip in progress' : 'Last trip completed',
        'status_classes' => $isInProgress
            ? 'border-blue-200 bg-fleet-primary-soft text-fleet-primary'
            : 'border-green-200 bg-fleet-success-soft text-fleet-success',
        'is_in_progress' => $isInProgress,
    ];
}

function driverPanelFetchLatestPreInspection(PDO $pdo, int $driverId, ?int $vehicleId): ?array
{
    if ($vehicleId === null) {
        return null;
    }

    $statement = $pdo->prepare(
        'SELECT
            inspection_date,
            overall_status,
            defects
        FROM inspections
        WHERE inspection_type = \'pre\'
            AND driver_id = :driver_id
            AND vehicle_id = :vehicle_id
        ORDER BY inspection_date DESC, id DESC
        LIMIT 1'
    );
    $statement->execute([
        'driver_id' => $driverId,
        'vehicle_id' => $vehicleId,
    ]);

    $report = $statement->fetch();

    if (!$report) {
        return null;
    }

    return [
        'date' => driverPanelFormatDate($report['inspection_date']),
        'date_raw' => $report['inspection_date'],
        'overall_status' => (string) $report['overall_status'],
        'defects' => $report['defects'] ?: 'No defects recorded.',
    ];
}

function driverPanelBuildAlerts(?array $driver, ?array $vehicle, array $tripVehicleOptions, ?array $latestTrip, ?array $latestPreInspection): array
{
    $alerts = [];
    $today = date('Y-m-d');

    if ($driver === null) {
        return [[
            'tone' => 'danger',
            'title' => 'Driver profile unavailable',
            'message' => 'No active driver profile could be resolved for the driver panel.',
        ]];
    }

    if ($vehicle === null && $tripVehicleOptions === []) {
        $alerts[] = [
            'tone' => 'warning',
            'title' => 'No vehicle assigned',
            'message' => 'You do not currently have an active vehicle assignment. Please contact transport office.',
        ];
    } elseif ($vehicle === null && $tripVehicleOptions !== []) {
        $alerts[] = [
            'tone' => 'info',
            'title' => 'Using linked vehicles',
            'message' => 'No primary vehicle is assigned, but other approved vehicles are available for trip work.',
        ];
    }

    if ($vehicle !== null && in_array($vehicle['status_label'], ['Maintenance', 'Grounded'], true)) {
        $alerts[] = [
            'tone' => 'danger',
            'title' => 'Assigned vehicle needs attention',
            'message' => 'Your assigned vehicle is currently marked as ' . strtolower($vehicle['status_label']) . '.',
        ];
    }

    if ($driver['license_expiry'] !== null && $driver['license_expiry'] !== '') {
        $daysToLicenseExpiry = (int) floor((strtotime((string) $driver['license_expiry']) - strtotime($today)) / 86400);

        if ($daysToLicenseExpiry < 0) {
            $alerts[] = [
                'tone' => 'danger',
                'title' => 'Driving license expired',
                'message' => 'Your driving license expired on ' . driverPanelFormatDate($driver['license_expiry']) . '.',
            ];
        } elseif ($daysToLicenseExpiry <= 30) {
            $alerts[] = [
                'tone' => 'warning',
                'title' => 'Driving license expiring soon',
                'message' => 'Your driving license expires on ' . driverPanelFormatDate($driver['license_expiry']) . '.',
            ];
        }
    }

    if ($vehicle !== null && $vehicle['insurance_expiry'] !== null && $vehicle['insurance_expiry'] !== '') {
        $daysToInsuranceExpiry = (int) floor((strtotime((string) $vehicle['insurance_expiry']) - strtotime($today)) / 86400);

        if ($daysToInsuranceExpiry < 0) {
            $alerts[] = [
                'tone' => 'danger',
                'title' => 'Vehicle insurance expired',
                'message' => 'Insurance for ' . $vehicle['registration_no'] . ' expired on ' . $vehicle['insurance_expiry_label'] . '.',
            ];
        } elseif ($daysToInsuranceExpiry <= 30) {
            $alerts[] = [
                'tone' => 'warning',
                'title' => 'Vehicle insurance expiring soon',
                'message' => 'Insurance for ' . $vehicle['registration_no'] . ' expires on ' . $vehicle['insurance_expiry_label'] . '.',
            ];
        }
    }

    if ($latestTrip === null) {
        $alerts[] = [
            'tone' => 'info',
            'title' => 'No trip activity yet',
            'message' => 'Your trip history is empty. Start logging journeys from the Trip Log page.',
        ];
    }

    if ($alerts === []) {
        $alerts[] = [
            'tone' => 'success',
            'title' => 'All key checks look good',
            'message' => 'Your profile, vehicle, and recent operational status do not have urgent alerts right now.',
        ];
    }

    return $alerts;
}

function driverPanelBuildTripStatus(?array $vehicle, array $tripVehicleOptions, ?array $latestTrip, ?array $latestPreInspection): array
{
    if ($vehicle === null && $tripVehicleOptions === []) {
        return [
            'label' => 'No vehicle assigned',
            'detail' => 'Transport office needs to assign a vehicle before trip work can begin.',
            'classes' => 'border-orange-200 bg-fleet-warning-soft text-fleet-warning-strong',
        ];
    }

    if ($vehicle === null && $tripVehicleOptions !== []) {
        return [
            'label' => 'Other vehicle available',
            'detail' => 'You can start a trip with one of the other vehicles linked to your profile.',
            'classes' => 'border-blue-200 bg-fleet-primary-soft text-fleet-primary',
        ];
    }

    if (in_array($vehicle['status_label'], ['Maintenance', 'Grounded'], true)) {
        return [
            'label' => 'Vehicle unavailable',
            'detail' => 'Your assigned vehicle is currently marked ' . strtolower($vehicle['status_label']) . '.',
            'classes' => 'border-red-200 bg-fleet-danger-soft text-fleet-danger',
        ];
    }

    if ($latestTrip !== null && $latestTrip['is_in_progress']) {
        return [
            'label' => 'Trip in progress',
            'detail' => $latestTrip['from'] . ' to ' . $latestTrip['to'] . ' on ' . $latestTrip['date'] . '.',
            'classes' => 'border-blue-200 bg-fleet-primary-soft text-fleet-primary',
        ];
    }

    return [
        'label' => 'Ready for trip',
        'detail' => 'Assigned vehicle is active and ready for normal trip logging.',
        'classes' => 'border-green-200 bg-fleet-success-soft text-fleet-success',
    ];
}

function driverPanelFetchCommonData(): array
{
    $emptyState = [
        'driverProfile' => null,
        'assignedVehicle' => null,
        'otherVehicles' => [],
        'tripVehicleOptions' => [],
        'tripStatus' => [
            'label' => 'Unavailable',
            'detail' => 'Driver panel data could not be loaded.',
            'classes' => 'border-red-200 bg-fleet-danger-soft text-fleet-danger',
        ],
        'latestTrip' => null,
        'latestPreInspection' => null,
        'alerts' => [[
            'tone' => 'danger',
            'title' => 'Unable to load driver panel',
            'message' => 'A system error occurred while loading driver information.',
        ]],
    ];

    try {
        $pdo = fleetDb();
        $driver = driverPanelFindCurrentDriver($pdo);

        if ($driver === null) {
            return $emptyState;
        }

        $assignedVehicle = driverPanelFetchAssignedVehicle($pdo, (int) $driver['id']);
        $otherVehicles = driverPanelFetchOtherVehicles($pdo, (int) $driver['id'], $assignedVehicle['id'] ?? null);
        $latestTrip = driverPanelFetchLatestTrip($pdo, (int) $driver['id']);
        $latestPreInspection = driverPanelFetchLatestPreInspection($pdo, (int) $driver['id'], $assignedVehicle['id'] ?? null);
        $tripVehicleOptions = driverPanelBuildTripVehicleOptions($assignedVehicle, $otherVehicles);
        $alerts = driverPanelBuildAlerts($driver, $assignedVehicle, $tripVehicleOptions, $latestTrip, $latestPreInspection);
        $tripStatus = driverPanelBuildTripStatus($assignedVehicle, $tripVehicleOptions, $latestTrip, $latestPreInspection);

        return [
            'driverProfile' => [
                'id' => (int) $driver['id'],
                'name' => $driver['full_name'],
                'employee_id' => $driver['employee_id'] ?: 'Not assigned',
                'phone' => $driver['phone'] ?: 'No phone on file',
                'email' => $driver['email'] ?: 'No email on file',
                'gender' => $driver['gender'] ? ucfirst((string) $driver['gender']) : 'Not specified',
                'national_id_number' => $driver['national_id_number'] ?: 'Not available',
                'department' => $driver['department_name'],
                'license_number' => $driver['license_number'],
                'license_classes' => $driver['license_classes'] ?: '-',
                'license_issue_date' => driverPanelFormatDate($driver['license_issue_date']),
                'license_issuing_authority' => $driver['license_issuing_authority'] ?: 'Not available',
                'license_expiry' => driverPanelFormatDate($driver['license_expiry']),
                'driver_photo_url' => driverPanelBuildUploadUrl($driver['driver_photo'] ?? ''),
                'driver_photo_is_image' => driverPanelUploadIsImage($driver['driver_photo'] ?? ''),
                'status' => ucfirst((string) $driver['status']),
                'initial' => strtoupper(substr((string) $driver['full_name'], 0, 1)),
            ],
            'assignedVehicle' => $assignedVehicle,
            'otherVehicles' => $otherVehicles,
            'tripVehicleOptions' => $tripVehicleOptions,
            'tripStatus' => $tripStatus,
            'latestTrip' => $latestTrip,
            'latestPreInspection' => $latestPreInspection,
            'alerts' => $alerts,
        ];
    } catch (Throwable $exception) {
        return $emptyState;
    }
}

function driverPanelFetchDashboardData(): array
{
    $commonData = driverPanelFetchCommonData();

    $overviewCards = [
        [
            'label' => 'Assigned Vehicle',
            'value' => $commonData['assignedVehicle']['registration_no'] ?? 'Not assigned',
            'icon' => 'V',
        ],
        [
            'label' => 'Current Mileage',
            'value' => $commonData['assignedVehicle']['current_mileage'] ?? '-',
            'icon' => 'M',
        ],
        [
            'label' => 'Trip Status',
            'value' => $commonData['tripStatus']['label'],
            'icon' => 'T',
        ],
        [
            'label' => 'Other Vehicles',
            'value' => (string) count($commonData['otherVehicles']),
            'icon' => 'O',
        ],
    ];

    return $commonData + [
        'overviewCards' => $overviewCards,
    ];
}

function driverPanelFetchVehiclePageData(): array
{
    $commonData = driverPanelFetchCommonData();
    $vehicle = $commonData['assignedVehicle'];

    $vehicleHighlights = [
        [
            'label' => 'Registration Number',
            'value' => $vehicle['registration_no'] ?? 'Not assigned',
        ],
        [
            'label' => 'Make & Model',
            'value' => $vehicle['make_model'] ?? '-',
        ],
        [
            'label' => 'Current Mileage',
            'value' => $vehicle['current_mileage'] ?? '-',
        ],
        [
            'label' => 'Other Vehicles',
            'value' => (string) count($commonData['otherVehicles']),
        ],
    ];

    return $commonData + [
        'vehicleHighlights' => $vehicleHighlights,
    ];
}

function driverPanelFetchProfilePageData(): array
{
    driverPanelRequireAuthenticatedDriver();

    $pdo = fleetDb();
    $driver = driverPanelFindCurrentDriver($pdo);
    $assignedVehicle = driverPanelFetchAssignedVehicle($pdo, (int) $driver['id']);
    $licenseExpiryStatus = driverPanelBuildLicenseExpiryStatus($driver['license_expiry'] ?? null);

    $profileRows = [
        'Account' => [
            'Driver ID' => $driver['driver_code'] ?: 'Not assigned',
            'Username' => $driver['username'] ?: 'Not assigned',
            'Status' => ucfirst((string) $driver['status']),
            'Department' => $driver['department_name'] ?: 'Not available',
            'Record Created' => driverPanelFormatDate($driver['created_at']),
            'Last Updated' => driverPanelFormatDate($driver['updated_at']),
        ],
        'Personal Details' => [
            'Full Name' => $driver['full_name'],
            'Employee ID' => $driver['employee_id'] ?: 'Not assigned',
            'Gender' => $driver['gender'] ? ucfirst((string) $driver['gender']) : 'Not specified',
            'National ID / NIN' => $driver['national_id_number'] ?: 'Not available',
        ],
        'Contact' => [
            'Phone' => $driver['phone'] ?: 'No phone on file',
            'Email' => $driver['email'] ?: 'No email on file',
        ],
        'Driving License' => [
            'License Number' => $driver['license_number'],
            'License Classes' => $driver['license_classes'] ?: 'Not set',
            'Issued On' => driverPanelFormatDate($driver['license_issue_date']),
            'Issuing Authority' => $driver['license_issuing_authority'] ?: 'Not available',
            'Expiry Date' => driverPanelFormatDate($driver['license_expiry']),
        ],
        'Vehicle' => [
            'Assigned Vehicle' => $assignedVehicle['registration_no'] ?? 'Not assigned',
            'Vehicle Details' => $assignedVehicle['make_model'] ?? '-',
        ],
    ];

    return [
        'profileDriver' => $driver,
        'profileRows' => $profileRows,
        'licenseExpiryStatus' => $licenseExpiryStatus,
        'assignedVehicle' => $assignedVehicle,
        'driverPhotoUrl' => driverPanelBuildUploadUrl($driver['driver_photo'] ?? ''),
        'driverPhotoIsImage' => driverPanelUploadIsImage($driver['driver_photo'] ?? ''),
        'nationalIdPhotoUrl' => driverPanelBuildUploadUrl($driver['national_id_photo'] ?? ''),
        'licenseScanUrl' => driverPanelBuildUploadUrl($driver['driving_license_scan'] ?? ''),
    ];
}

function driverPanelBuildChecklistRowsFromFormData(array $formData): array
{
    $points = isset($formData['inspection_point']) && is_array($formData['inspection_point']) ? $formData['inspection_point'] : [];
    $statuses = isset($formData['item_status']) && is_array($formData['item_status']) ? $formData['item_status'] : [];
    $remarks = isset($formData['item_remarks']) && is_array($formData['item_remarks']) ? $formData['item_remarks'] : [];
    $actions = isset($formData['item_action']) && is_array($formData['item_action']) ? $formData['item_action'] : [];

    $rows = [];

    foreach (DRIVER_PANEL_PRETRIP_CHECKLIST as $index => $point) {
        $rows[] = [
            'inspection_point' => $points[$index] ?? $point,
            'item_status' => $statuses[$index] ?? 'good',
            'item_remarks' => $remarks[$index] ?? '',
            'item_action' => $actions[$index] ?? '',
        ];
    }

    return $rows;
}

function driverPanelBuildInspectionItemPayload(array $checklistRows): array
{
    $items = [];

    foreach ($checklistRows as $row) {
        $status = strtolower(trim((string) ($row['item_status'] ?? 'good')));
        $remarks = trim((string) ($row['item_remarks'] ?? ''));
        $action = trim((string) ($row['item_action'] ?? ''));

        $findings = ucfirst($status);
        if ($remarks !== '') {
            $findings .= ' - ' . $remarks;
        }

        $items[] = [
            'inspection_point' => trim((string) ($row['inspection_point'] ?? '')),
            'findings' => $findings,
            'action_point' => $action === '' ? null : $action,
        ];
    }

    return $items;
}

function driverPanelGenerateInspectionReference(int $driverId): string
{
    return 'DPI-' . date('YmdHis') . '-' . $driverId;
}

function driverPanelBuildPreTripFormDataFromPost(): array
{
    $points = $_POST['inspection_point'] ?? [];
    $statuses = $_POST['item_status'] ?? [];
    $remarks = $_POST['item_remarks'] ?? [];
    $actions = $_POST['item_action'] ?? [];

    return [
        'inspection_date' => trim((string) ($_POST['inspection_date'] ?? date('Y-m-d'))),
        'mileage' => trim((string) ($_POST['mileage'] ?? '')),
        'overall_status' => strtolower(trim((string) ($_POST['overall_status'] ?? 'good'))),
        'defects' => trim((string) ($_POST['defects'] ?? '')),
        'inspection_point' => array_map(static fn ($value): string => trim((string) $value), is_array($points) ? $points : []),
        'item_status' => array_map(static fn ($value): string => strtolower(trim((string) $value)), is_array($statuses) ? $statuses : []),
        'item_remarks' => array_map(static fn ($value): string => trim((string) $value), is_array($remarks) ? $remarks : []),
        'item_action' => array_map(static fn ($value): string => trim((string) $value), is_array($actions) ? $actions : []),
    ];
}

function driverPanelValidatePreTripFormData(array $formData, array $driverProfile, array $assignedVehicle): array
{
    $allowedStatuses = ['good', 'fair', 'faulty', 'needs_repair'];
    $inspectionDate = $formData['inspection_date'] !== '' ? $formData['inspection_date'] : date('Y-m-d');
    $overallStatus = in_array($formData['overall_status'], $allowedStatuses, true) ? $formData['overall_status'] : 'good';

    $date = DateTimeImmutable::createFromFormat('Y-m-d', $inspectionDate);
    $dateErrors = DateTimeImmutable::getLastErrors();

    if (!$date || ($dateErrors['warning_count'] ?? 0) > 0 || ($dateErrors['error_count'] ?? 0) > 0) {
        throw new RuntimeException('Please provide a valid inspection date.');
    }

    $mileage = $formData['mileage'] === ''
        ? $assignedVehicle['current_mileage_raw']
        : filter_var($formData['mileage'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

    if ($mileage === false) {
        throw new RuntimeException('Please provide a valid mileage reading.');
    }

    $checklistRows = driverPanelBuildChecklistRowsFromFormData($formData);
    $items = driverPanelBuildInspectionItemPayload($checklistRows);

    return [
        'vehicle_id' => (int) $assignedVehicle['id'],
        'driver_id' => (int) $driverProfile['id'],
        'inspection_date' => $date->format('Y-m-d'),
        'mileage' => (int) $mileage,
        'overall_status' => $overallStatus,
        'defects' => $formData['defects'] === '' ? null : $formData['defects'],
        'invoice_number' => driverPanelGenerateInspectionReference((int) $driverProfile['id']),
        'inspector_name' => $driverProfile['name'],
        'items' => $items,
        'checklist_rows' => $checklistRows,
    ];
}

function driverPanelSaveInspectionItems(PDO $pdo, int $inspectionId, array $items): void
{
    $deleteStatement = $pdo->prepare('DELETE FROM inspection_items WHERE inspection_id = :inspection_id');
    $deleteStatement->execute(['inspection_id' => $inspectionId]);

    $insertStatement = $pdo->prepare(
        'INSERT INTO inspection_items (inspection_id, inspection_point, findings, action_point)
        VALUES (:inspection_id, :inspection_point, :findings, :action_point)'
    );

    foreach ($items as $item) {
        $insertStatement->bindValue(':inspection_id', $inspectionId, PDO::PARAM_INT);
        $insertStatement->bindValue(':inspection_point', $item['inspection_point']);
        $insertStatement->bindValue(':findings', $item['findings'], $item['findings'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $insertStatement->bindValue(':action_point', $item['action_point'], $item['action_point'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $insertStatement->execute();
    }
}

function driverPanelHandlePreTripSubmission(): void
{
    $formData = driverPanelBuildPreTripFormDataFromPost();
    $responseStatus = 200;
    $responsePayload = [
        'success' => false,
        'message' => 'Your pre-trip inspection could not be saved right now.',
        'reload' => false,
    ];

    try {
        $pdo = fleetDb();
        $commonData = driverPanelFetchCommonData();
        $driverProfile = $commonData['driverProfile'];
        $assignedVehicle = $commonData['assignedVehicle'];

        if ($driverProfile === null || $assignedVehicle === null) {
            throw new RuntimeException('A driver profile and assigned vehicle are required before submitting an inspection.');
        }

        $validated = driverPanelValidatePreTripFormData($formData, $driverProfile, $assignedVehicle);
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            "INSERT INTO inspections (
                vehicle_id,
                driver_id,
                inspection_type,
                invoice_number,
                inspection_date,
                inspector_name,
                inspector_title,
                mileage,
                overall_status,
                defects
            ) VALUES (
                :vehicle_id,
                :driver_id,
                'pre',
                :invoice_number,
                :inspection_date,
                :inspector_name,
                'Driver',
                :mileage,
                :overall_status,
                :defects
            )"
        );
        $statement->bindValue(':vehicle_id', $validated['vehicle_id'], PDO::PARAM_INT);
        $statement->bindValue(':driver_id', $validated['driver_id'], PDO::PARAM_INT);
        $statement->bindValue(':invoice_number', $validated['invoice_number']);
        $statement->bindValue(':inspection_date', $validated['inspection_date']);
        $statement->bindValue(':inspector_name', $validated['inspector_name']);
        $statement->bindValue(':mileage', $validated['mileage'], PDO::PARAM_INT);
        $statement->bindValue(':overall_status', $validated['overall_status']);
        $statement->bindValue(':defects', $validated['defects'], $validated['defects'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->execute();

        $inspectionId = (int) $pdo->lastInsertId();
        driverPanelSaveInspectionItems($pdo, $inspectionId, $validated['items']);
        $pdo->commit();
        fleetTrackActivity([
            'module_key' => 'driver-panel',
            'action_key' => 'submitted_pre_trip',
            'action_label' => 'Submitted pre-trip',
            'description' => 'Driver submitted a pre-trip inspection.',
            'target_type' => 'inspection',
            'target_id' => $inspectionId,
            'target_label' => (string) ($_SESSION['user_name'] ?? 'Driver inspection'),
            'metadata' => [
                'vehicle_id' => $validated['vehicle_id'],
                'overall_status' => $validated['overall_status'],
            ],
        ], $pdo);

        driverPanelSetPreTripFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Inspection submitted successfully',
                'message' => 'Your pre-trip inspection has been saved.',
            ],
        ]);
        $responsePayload = [
            'success' => true,
            'message' => 'Your pre-trip inspection has been saved.',
            'reload' => true,
            'action' => 'submit_pre_trip',
        ];
    } catch (RuntimeException $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        driverPanelSetPreTripFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Inspection was not submitted',
                'message' => $exception->getMessage(),
            ],
            'form_data' => $formData,
        ]);
        $responseStatus = 422;
        $responsePayload = [
            'success' => false,
            'message' => $exception->getMessage(),
            'reload' => false,
            'action' => 'submit_pre_trip',
        ];
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        driverPanelSetPreTripFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Inspection was not submitted',
                'message' => 'A system error occurred while saving the inspection.',
            ],
            'form_data' => $formData,
        ]);
        $responseStatus = 500;
        $responsePayload = [
            'success' => false,
            'message' => 'A system error occurred while saving the inspection.',
            'reload' => false,
            'action' => 'submit_pre_trip',
        ];
    }

    // Sends JSON to jQuery requests while preserving the original redirect flow for normal posts.
    fleetFinishResponse(driverPanelPreTripUrl(), $responsePayload, $responseStatus);
}

function driverPanelFetchPreTripReports(PDO $pdo, int $driverId, ?int $vehicleId): array
{
    if ($vehicleId === null) {
        return [];
    }

    $statement = $pdo->prepare(
        "SELECT id, invoice_number, inspection_date, overall_status, defects, mileage
        FROM inspections
        WHERE inspection_type = 'pre'
            AND driver_id = :driver_id
            AND vehicle_id = :vehicle_id
        ORDER BY inspection_date DESC, id DESC
        LIMIT 6"
    );
    $statement->execute([
        'driver_id' => $driverId,
        'vehicle_id' => $vehicleId,
    ]);

    $reports = [];

    foreach ($statement->fetchAll() as $row) {
        $status = (string) ($row['overall_status'] ?? '');
        $reports[] = [
            'invoice' => $row['invoice_number'] ?: '-',
            'date' => driverPanelFormatDate($row['inspection_date']),
            'status' => ucwords(str_replace('_', ' ', $status ?: 'pending')),
            'status_classes' => match ($status) {
                'good' => 'border-green-200 bg-fleet-success-soft text-fleet-success',
                'fair' => 'border-orange-200 bg-fleet-warning-soft text-fleet-warning-strong',
                'faulty', 'needs_repair' => 'border-red-200 bg-fleet-danger-soft text-fleet-danger',
                default => 'border-slate-200 bg-slate-100 text-slate-600',
            },
            'defects' => $row['defects'] ?: 'No defects summary provided.',
            'mileage' => $row['mileage'] !== null ? number_format((int) $row['mileage']) . ' km' : '-',
        ];
    }

    return $reports;
}

function driverPanelFetchPreTripPageData(): array
{
    $commonData = driverPanelFetchCommonData();
    $flash = driverPanelPullPreTripFlash();
    $notification = $flash['notification'] ?? null;
    $formData = $flash['form_data'] ?? [];
    $driverProfile = $commonData['driverProfile'];
    $assignedVehicle = $commonData['assignedVehicle'];
    $recentReports = [];

    if ($formData === []) {
        $formData = [
            'inspection_date' => date('Y-m-d'),
            'mileage' => $assignedVehicle['current_mileage_raw'] ?? '',
            'overall_status' => 'good',
            'defects' => '',
        ];
    }

    try {
        if ($driverProfile !== null && $assignedVehicle !== null) {
            $recentReports = driverPanelFetchPreTripReports(fleetDb(), (int) $driverProfile['id'], (int) $assignedVehicle['id']);
        }
    } catch (Throwable $exception) {
        $notification = $notification ?? [
            'type' => 'error',
            'title' => 'Unable to load pre-trip history',
            'message' => 'Recent pre-trip inspection records could not be loaded right now.',
        ];
    }

    $latestReport = $recentReports[0] ?? null;

    return $commonData + [
        'preTripNotification' => $notification,
        'preTripFormData' => $formData,
        'preTripChecklistRows' => driverPanelBuildChecklistRowsFromFormData($formData),
        'preTripRecentReports' => $recentReports,
        'preTripLatestStatus' => $latestReport,
        'preTripFormAction' => driverPanelHandlerUrl(),
    ];
}

function driverPanelBuildTripStartFormDataFromPost(): array
{
    return [
        'vehicle_id' => trim((string) ($_POST['vehicle_id'] ?? '')),
        'trip_date' => trim((string) ($_POST['trip_date'] ?? date('Y-m-d'))),
        'departure_location' => trim((string) ($_POST['departure_location'] ?? '')),
        'destination' => trim((string) ($_POST['destination'] ?? '')),
        'purpose' => trim((string) ($_POST['purpose'] ?? '')),
        'odometer_start' => trim((string) ($_POST['odometer_start'] ?? '')),
    ];
}

function driverPanelBuildTripEndFormDataFromPost(): array
{
    return [
        'trip_id' => trim((string) ($_POST['trip_id'] ?? '')),
        'odometer_end' => trim((string) ($_POST['odometer_end'] ?? '')),
        'fuel_litres' => trim((string) ($_POST['fuel_litres'] ?? '')),
        'fuel_cost' => trim((string) ($_POST['fuel_cost'] ?? '')),
        'remarks' => trim((string) ($_POST['remarks'] ?? '')),
    ];
}

function driverPanelFetchOpenTrip(PDO $pdo, int $driverId): ?array
{
    $statement = $pdo->prepare(
        "SELECT
            vl.id,
            vl.vehicle_id,
            vl.trip_date,
            vl.departure_location,
            vl.destination,
            vl.purpose,
            vl.odometer_start,
            vl.odometer_end,
            vl.fuel_litres,
            vl.fuel_cost,
            vl.remarks,
            v.registration_no
        FROM vehicle_logs vl
        INNER JOIN vehicles v ON v.id = vl.vehicle_id
        WHERE vl.driver_id = :driver_id
            AND vl.odometer_end IS NULL
        ORDER BY vl.trip_date DESC, vl.id DESC
        LIMIT 1"
    );
    $statement->execute(['driver_id' => $driverId]);
    $trip = $statement->fetch();

    if (!$trip) {
        return null;
    }

    return [
        'id' => (int) $trip['id'],
        'vehicle_id' => (int) $trip['vehicle_id'],
        'date' => driverPanelFormatDate($trip['trip_date']),
        'date_raw' => $trip['trip_date'],
        'vehicle' => $trip['registration_no'],
        'from' => $trip['departure_location'],
        'to' => $trip['destination'],
        'purpose' => $trip['purpose'],
        'odometer_start' => $trip['odometer_start'] !== null ? (int) $trip['odometer_start'] : null,
        'odometer_start_label' => $trip['odometer_start'] !== null ? number_format((int) $trip['odometer_start']) . ' km' : '-',
    ];
}

function driverPanelFetchRecentTrips(PDO $pdo, int $driverId): array
{
    $statement = $pdo->prepare(
        "SELECT
            vl.id,
            vl.trip_date,
            vl.departure_location,
            vl.destination,
            vl.purpose,
            vl.odometer_start,
            vl.odometer_end,
            vl.distance_km,
            vl.fuel_litres,
            vl.remarks,
            v.registration_no
        FROM vehicle_logs vl
        INNER JOIN vehicles v ON v.id = vl.vehicle_id
        WHERE vl.driver_id = :driver_id
        ORDER BY vl.trip_date DESC, vl.id DESC
        LIMIT 8"
    );
    $statement->execute(['driver_id' => $driverId]);

    $trips = [];

    foreach ($statement->fetchAll() as $row) {
        $isOpen = $row['odometer_end'] === null;
        $trips[] = [
            'date' => driverPanelFormatDate($row['trip_date']),
            'vehicle' => $row['registration_no'],
            'route' => $row['departure_location'] . ' - ' . $row['destination'],
            'purpose' => $row['purpose'],
            'distance' => $row['distance_km'] !== null ? number_format((int) $row['distance_km']) . ' km' : '-',
            'fuel' => $row['fuel_litres'] !== null ? rtrim(rtrim(number_format((float) $row['fuel_litres'], 2, '.', ''), '0'), '.') . ' L' : '-',
            'remarks' => $row['remarks'] ?: '-',
            'status' => $isOpen ? 'In Progress' : 'Completed',
            'status_classes' => $isOpen
                ? 'border-blue-200 bg-fleet-primary-soft text-fleet-primary'
                : 'border-green-200 bg-fleet-success-soft text-fleet-success',
        ];
    }

    return $trips;
}

function driverPanelValidateTripVehicleSelection(array $formData, array $tripVehicleOptions): array
{
    if ($tripVehicleOptions === []) {
        throw new RuntimeException('No trip vehicle is available for this driver.');
    }

    $defaultVehicleId = (int) $tripVehicleOptions[0]['id'];
    $selectedVehicleId = $formData['vehicle_id'] === ''
        ? $defaultVehicleId
        : filter_var($formData['vehicle_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($selectedVehicleId === false) {
        throw new RuntimeException('Please choose a valid trip vehicle.');
    }

    $selectedVehicle = driverPanelFindTripVehicleOption($tripVehicleOptions, (int) $selectedVehicleId);
    if ($selectedVehicle === null) {
        throw new RuntimeException('The selected trip vehicle is not available to this driver.');
    }

    if (in_array($selectedVehicle['status_label'], ['Maintenance', 'Grounded', 'Disposed'], true)) {
        throw new RuntimeException('The selected trip vehicle is not currently available for travel.');
    }

    return $selectedVehicle;
}

function driverPanelValidateTripStart(array $formData, array $selectedVehicle): array
{
    if ($formData['departure_location'] === '' || $formData['destination'] === '' || $formData['purpose'] === '') {
        throw new RuntimeException('Departure location, destination, and purpose are required to start a trip.');
    }

    $date = DateTimeImmutable::createFromFormat('Y-m-d', $formData['trip_date']);
    $dateErrors = DateTimeImmutable::getLastErrors();
    if (!$date || ($dateErrors['warning_count'] ?? 0) > 0 || ($dateErrors['error_count'] ?? 0) > 0) {
        throw new RuntimeException('Please provide a valid trip date.');
    }

    $odometerStart = filter_var($selectedVehicle['current_mileage_raw'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

    if ($odometerStart === false) {
        throw new RuntimeException('The selected vehicle does not have a valid current mileage reading.');
    }

    return [
        'vehicle_id' => (int) $selectedVehicle['id'],
        'trip_date' => $date->format('Y-m-d'),
        'departure_location' => $formData['departure_location'],
        'destination' => $formData['destination'],
        'purpose' => $formData['purpose'],
        'odometer_start' => (int) $odometerStart,
    ];
}

function driverPanelHandleStartTrip(): void
{
    $formData = driverPanelBuildTripStartFormDataFromPost();
    $responseStatus = 200;
    $responsePayload = [
        'success' => false,
        'message' => 'The trip could not be started right now.',
        'reload' => false,
    ];

    try {
        $pdo = fleetDb();
        $commonData = driverPanelFetchCommonData();
        $driverProfile = $commonData['driverProfile'];
        $tripVehicleOptions = $commonData['tripVehicleOptions'];

        if ($driverProfile === null || $tripVehicleOptions === []) {
            throw new RuntimeException('A driver profile and at least one trip vehicle are required before starting a trip.');
        }

        if (driverPanelFetchOpenTrip($pdo, (int) $driverProfile['id']) !== null) {
            throw new RuntimeException('Finish the current trip before starting a new one.');
        }

        $selectedVehicle = driverPanelValidateTripVehicleSelection($formData, $tripVehicleOptions);
        $validated = driverPanelValidateTripStart($formData, $selectedVehicle);
        $statement = $pdo->prepare(
            "INSERT INTO vehicle_logs (
                vehicle_id,
                driver_id,
                trip_date,
                departure_location,
                destination,
                purpose,
                odometer_start
            ) VALUES (
                :vehicle_id,
                :driver_id,
                :trip_date,
                :departure_location,
                :destination,
                :purpose,
                :odometer_start
            )"
        );
        $statement->execute([
            'vehicle_id' => $validated['vehicle_id'],
            'driver_id' => (int) $driverProfile['id'],
            'trip_date' => $validated['trip_date'],
            'departure_location' => $validated['departure_location'],
            'destination' => $validated['destination'],
            'purpose' => $validated['purpose'],
            'odometer_start' => $validated['odometer_start'],
        ]);
        $tripId = (int) $pdo->lastInsertId();
        fleetTrackActivity([
            'module_key' => 'driver-panel',
            'action_key' => 'started_trip',
            'action_label' => 'Started trip',
            'description' => 'Driver started a trip.',
            'target_type' => 'trip_log',
            'target_id' => $tripId,
            'target_label' => $validated['destination'],
            'metadata' => [
                'vehicle_id' => $validated['vehicle_id'],
            ],
        ], $pdo);

        driverPanelSetTripFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Trip started successfully',
                'message' => 'The trip has been opened and is now in progress.',
            ],
        ]);
        $responsePayload = [
            'success' => true,
            'message' => 'The trip has been opened and is now in progress.',
            'reload' => true,
            'action' => 'start_trip',
        ];
    } catch (RuntimeException $exception) {
        driverPanelSetTripFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Trip could not be started',
                'message' => $exception->getMessage(),
            ],
            'start_form_data' => $formData,
        ]);
        $responseStatus = 422;
        $responsePayload = [
            'success' => false,
            'message' => $exception->getMessage(),
            'reload' => false,
            'action' => 'start_trip',
        ];
    } catch (Throwable $exception) {
        driverPanelSetTripFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Trip could not be started',
                'message' => 'A system error occurred while starting the trip.',
            ],
            'start_form_data' => $formData,
        ]);
        $responseStatus = 500;
        $responsePayload = [
            'success' => false,
            'message' => 'A system error occurred while starting the trip.',
            'reload' => false,
            'action' => 'start_trip',
        ];
    }

    // Sends JSON to jQuery requests while preserving the original redirect flow for normal posts.
    fleetFinishResponse(driverPanelTripLogUrl(), $responsePayload, $responseStatus);
}

function driverPanelValidateTripEnd(array $formData, array $openTrip): array
{
    $tripId = filter_var($formData['trip_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($tripId === false || (int) $tripId !== (int) $openTrip['id']) {
        throw new RuntimeException('The active trip could not be identified.');
    }

    $odometerEnd = filter_var($formData['odometer_end'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($odometerEnd === false) {
        throw new RuntimeException('Please provide a valid odometer end reading.');
    }

    if ($openTrip['odometer_start'] !== null && $odometerEnd <= (int) $openTrip['odometer_start']) {
        throw new RuntimeException('Odometer end must be greater than the last recorded odometer start.');
    }

    $fuelLitres = $formData['fuel_litres'] === '' ? null : filter_var($formData['fuel_litres'], FILTER_VALIDATE_FLOAT);
    $fuelCost = $formData['fuel_cost'] === '' ? null : filter_var($formData['fuel_cost'], FILTER_VALIDATE_FLOAT);

    if ($fuelLitres === false || $fuelCost === false) {
        throw new RuntimeException('Please review the fuel fields and enter valid values.');
    }

    return [
        'trip_id' => (int) $tripId,
        'odometer_end' => (int) $odometerEnd,
        'fuel_litres' => $fuelLitres === null ? null : (float) $fuelLitres,
        'fuel_cost' => $fuelCost === null ? null : (float) $fuelCost,
        'remarks' => $formData['remarks'] === '' ? null : $formData['remarks'],
    ];
}

function driverPanelHandleEndTrip(): void
{
    $formData = driverPanelBuildTripEndFormDataFromPost();
    $responseStatus = 200;
    $responsePayload = [
        'success' => false,
        'message' => 'The trip could not be ended right now.',
        'reload' => false,
    ];

    try {
        $pdo = fleetDb();
        $commonData = driverPanelFetchCommonData();
        $driverProfile = $commonData['driverProfile'];

        if ($driverProfile === null) {
            throw new RuntimeException('A driver profile is required before ending a trip.');
        }

        $openTrip = driverPanelFetchOpenTrip($pdo, (int) $driverProfile['id']);
        if ($openTrip === null) {
            throw new RuntimeException('There is no active trip to end.');
        }

        $validated = driverPanelValidateTripEnd($formData, $openTrip);
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            "UPDATE vehicle_logs SET
                odometer_end = :odometer_end,
                fuel_litres = :fuel_litres,
                fuel_cost = :fuel_cost,
                remarks = :remarks
            WHERE id = :trip_id"
        );
        $statement->bindValue(':odometer_end', $validated['odometer_end'], PDO::PARAM_INT);
        $statement->bindValue(':fuel_litres', $validated['fuel_litres'], $validated['fuel_litres'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':fuel_cost', $validated['fuel_cost'], $validated['fuel_cost'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':remarks', $validated['remarks'], $validated['remarks'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':trip_id', $validated['trip_id'], PDO::PARAM_INT);
        $statement->execute();

        $mileageStatement = $pdo->prepare(
            'UPDATE vehicles
             SET current_mileage = GREATEST(current_mileage, :odometer_end)
             WHERE id = :vehicle_id'
        );
        $mileageStatement->execute([
            'odometer_end' => $validated['odometer_end'],
            'vehicle_id' => (int) $openTrip['vehicle_id'],
        ]);

        $pdo->commit();
        fleetTrackActivity([
            'module_key' => 'driver-panel',
            'action_key' => 'ended_trip',
            'action_label' => 'Ended trip',
            'description' => 'Driver completed a trip.',
            'target_type' => 'trip_log',
            'target_id' => (int) $validated['trip_id'],
            'target_label' => (string) ($openTrip['destination'] ?? 'Completed trip'),
        ], $pdo);

        driverPanelSetTripFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Trip ended successfully',
                'message' => 'The trip has been completed and saved to the logbook.',
            ],
        ]);
        $responsePayload = [
            'success' => true,
            'message' => 'The trip has been completed and saved to the logbook.',
            'reload' => true,
            'action' => 'end_trip',
        ];
    } catch (RuntimeException $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        driverPanelSetTripFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Trip could not be ended',
                'message' => $exception->getMessage(),
            ],
            'end_form_data' => $formData,
        ]);
        $responseStatus = 422;
        $responsePayload = [
            'success' => false,
            'message' => $exception->getMessage(),
            'reload' => false,
            'action' => 'end_trip',
        ];
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        driverPanelSetTripFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Trip could not be ended',
                'message' => 'A system error occurred while closing the trip.',
            ],
            'end_form_data' => $formData,
        ]);
        $responseStatus = 500;
        $responsePayload = [
            'success' => false,
            'message' => 'A system error occurred while closing the trip.',
            'reload' => false,
            'action' => 'end_trip',
        ];
    }

    // Sends JSON to jQuery requests while preserving the original redirect flow for normal posts.
    fleetFinishResponse(driverPanelTripLogUrl(), $responsePayload, $responseStatus);
}

function driverPanelFetchTripLogPageData(): array
{
    $commonData = driverPanelFetchCommonData();
    $flash = driverPanelPullTripFlash();
    $notification = $flash['notification'] ?? null;
    $driverProfile = $commonData['driverProfile'];
    $assignedVehicle = $commonData['assignedVehicle'];
    $tripVehicleOptions = $commonData['tripVehicleOptions'];
    $activeTrip = null;
    $recentTrips = [];

    try {
        if ($driverProfile !== null) {
            $pdo = fleetDb();
            $activeTrip = driverPanelFetchOpenTrip($pdo, (int) $driverProfile['id']);
            $recentTrips = driverPanelFetchRecentTrips($pdo, (int) $driverProfile['id']);
        }
    } catch (Throwable $exception) {
        $notification = $notification ?? [
            'type' => 'error',
            'title' => 'Unable to load trip history',
            'message' => 'Trip details could not be loaded right now.',
        ];
    }

    $startFormData = $flash['start_form_data'] ?? [
        'vehicle_id' => $tripVehicleOptions[0]['id'] ?? '',
        'trip_date' => date('Y-m-d'),
        'departure_location' => '',
        'destination' => '',
        'purpose' => '',
        'odometer_start' => $tripVehicleOptions[0]['current_mileage_raw'] ?? $assignedVehicle['current_mileage_raw'] ?? '',
    ];

    $endFormData = $flash['end_form_data'] ?? [
        'trip_id' => $activeTrip['id'] ?? '',
        'odometer_end' => '',
        'fuel_litres' => '',
        'fuel_cost' => '',
        'remarks' => '',
    ];

    return $commonData + [
        'tripLogNotification' => $notification,
        'activeTrip' => $activeTrip,
        'recentTrips' => $recentTrips,
        'tripVehicleOptions' => $tripVehicleOptions,
        'tripStartFormData' => $startFormData,
        'tripEndFormData' => $endFormData,
        'tripFormAction' => driverPanelHandlerUrl(),
    ];
}

function driverPanelBuildLogbookFilters(): array
{
    return [
        'vehicle_id' => trim((string) ($_GET['vehicle_id'] ?? '')),
        'period' => strtolower(trim((string) ($_GET['period'] ?? 'all'))),
        'week' => trim((string) ($_GET['week'] ?? '')),
        'month' => trim((string) ($_GET['month'] ?? '')),
        'date_from' => trim((string) ($_GET['date_from'] ?? '')),
        'date_to' => trim((string) ($_GET['date_to'] ?? '')),
    ];
}

function driverPanelResolveLogbookPeriodRange(array $filters): array
{
    $period = in_array($filters['period'] ?? 'all', ['all', 'week', 'month', 'custom'], true)
        ? (string) $filters['period']
        : 'all';

    if ($period === 'week' && ($filters['week'] ?? '') !== '') {
        $weekStart = DateTimeImmutable::createFromFormat('o-\WW-N', $filters['week'] . '-1');
        $weekEnd = DateTimeImmutable::createFromFormat('o-\WW-N', $filters['week'] . '-7');

        if ($weekStart && $weekEnd) {
            return [
                'period' => 'week',
                'date_from' => $weekStart->format('Y-m-d'),
                'date_to' => $weekEnd->format('Y-m-d'),
                'label' => 'Week of ' . driverPanelFormatDate($weekStart->format('Y-m-d')) . ' to ' . driverPanelFormatDate($weekEnd->format('Y-m-d')),
            ];
        }
    }

    if ($period === 'month' && preg_match('/^\d{4}-\d{2}$/', (string) ($filters['month'] ?? '')) === 1) {
        $monthStart = DateTimeImmutable::createFromFormat('Y-m-d', $filters['month'] . '-01');
        if ($monthStart) {
            $monthEnd = $monthStart->modify('last day of this month');

            return [
                'period' => 'month',
                'date_from' => $monthStart->format('Y-m-d'),
                'date_to' => $monthEnd->format('Y-m-d'),
                'label' => $monthStart->format('F Y'),
            ];
        }
    }

    if ($period === 'custom' && ($filters['date_from'] ?? '') !== '' && ($filters['date_to'] ?? '') !== '') {
        $dateFrom = DateTimeImmutable::createFromFormat('Y-m-d', $filters['date_from']);
        $fromErrors = DateTimeImmutable::getLastErrors();
        $dateTo = DateTimeImmutable::createFromFormat('Y-m-d', $filters['date_to']);
        $toErrors = DateTimeImmutable::getLastErrors();
        $fromErrors = is_array($fromErrors) ? $fromErrors : ['warning_count' => 0, 'error_count' => 0];
        $toErrors = is_array($toErrors) ? $toErrors : ['warning_count' => 0, 'error_count' => 0];

        if (
            $dateFrom
            && $dateTo
            && ($fromErrors['warning_count'] ?? 0) === 0
            && ($fromErrors['error_count'] ?? 0) === 0
            && ($toErrors['warning_count'] ?? 0) === 0
            && ($toErrors['error_count'] ?? 0) === 0
            && $dateFrom <= $dateTo
        ) {
            return [
                'period' => 'custom',
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
                'label' => driverPanelFormatDate($dateFrom->format('Y-m-d')) . ' to ' . driverPanelFormatDate($dateTo->format('Y-m-d')),
            ];
        }
    }

    return [
        'period' => 'all',
        'date_from' => null,
        'date_to' => null,
        'label' => 'All recorded dates',
    ];
}

function driverPanelFetchLogbookVehicleOptions(PDO $pdo, int $driverId): array
{
    $statement = $pdo->prepare(
        "SELECT DISTINCT
            v.id,
            v.registration_no,
            CONCAT_WS(' ', v.make, v.model) AS make_model
        FROM vehicle_logs vl
        INNER JOIN vehicles v ON v.id = vl.vehicle_id
        WHERE vl.driver_id = :driver_id
        ORDER BY v.registration_no ASC"
    );
    $statement->execute(['driver_id' => $driverId]);

    return $statement->fetchAll();
}

function driverPanelFindLogbookVehicleOption(array $vehicleOptions, ?int $vehicleId): ?array
{
    if ($vehicleId === null) {
        return null;
    }

    foreach ($vehicleOptions as $vehicleOption) {
        if ((int) $vehicleOption['id'] === $vehicleId) {
            return $vehicleOption;
        }
    }

    return null;
}

function driverPanelFetchLogbookRows(PDO $pdo, int $driverId, ?int $vehicleId, array $periodRange): array
{
    $where = ['vl.driver_id = :driver_id'];
    $params = ['driver_id' => $driverId];

    if ($vehicleId !== null) {
        $where[] = 'vl.vehicle_id = :vehicle_id';
        $params['vehicle_id'] = $vehicleId;
    }

    if (($periodRange['date_from'] ?? null) !== null && ($periodRange['date_to'] ?? null) !== null) {
        $where[] = 'vl.trip_date BETWEEN :date_from AND :date_to';
        $params['date_from'] = $periodRange['date_from'];
        $params['date_to'] = $periodRange['date_to'];
    }

    $statement = $pdo->prepare(
        "SELECT
            vl.id,
            vl.trip_date,
            vl.departure_location,
            vl.destination,
            vl.purpose,
            vl.odometer_start,
            vl.odometer_end,
            vl.distance_km,
            vl.fuel_litres,
            vl.fuel_cost,
            vl.remarks,
            v.id AS vehicle_id,
            v.registration_no,
            CONCAT_WS(' ', v.make, v.model) AS make_model
        FROM vehicle_logs vl
        INNER JOIN vehicles v ON v.id = vl.vehicle_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY vl.trip_date DESC, vl.id DESC"
    );
    $statement->execute($params);

    $rows = [];

    foreach ($statement->fetchAll() as $row) {
        $distanceRaw = $row['distance_km'] !== null ? (int) $row['distance_km'] : 0;
        $fuelRaw = $row['fuel_litres'] !== null ? (float) $row['fuel_litres'] : 0.0;
        $costRaw = $row['fuel_cost'] !== null ? (float) $row['fuel_cost'] : 0.0;

        $rows[] = [
            'id' => (int) $row['id'],
            'date' => driverPanelFormatDate($row['trip_date']),
            'date_raw' => (string) $row['trip_date'],
            'vehicle' => (string) $row['registration_no'],
            'vehicle_label' => trim((string) $row['registration_no'] . ' - ' . (string) $row['make_model']),
            'from' => (string) $row['departure_location'],
            'to' => (string) $row['destination'],
            'purpose' => (string) $row['purpose'],
            'odometer_start' => $row['odometer_start'] !== null ? number_format((int) $row['odometer_start']) : '-',
            'odometer_end' => $row['odometer_end'] !== null ? number_format((int) $row['odometer_end']) : '-',
            'distance' => number_format($distanceRaw) . ' km',
            'distance_raw' => $distanceRaw,
            'fuel_litres' => $row['fuel_litres'] !== null ? rtrim(rtrim(number_format($fuelRaw, 2, '.', ''), '0'), '.') . ' L' : '-',
            'fuel_litres_raw' => $fuelRaw,
            'fuel_cost' => driverPanelFormatMoney($row['fuel_cost'] !== null ? $costRaw : null),
            'fuel_cost_raw' => $costRaw,
            'remarks' => trim((string) ($row['remarks'] ?? '')) !== '' ? (string) $row['remarks'] : '-',
        ];
    }

    return $rows;
}

function driverPanelBuildLogbookSummary(array $rows): array
{
    $totalDistance = 0;
    $totalFuel = 0.0;
    $totalCost = 0.0;

    foreach ($rows as $row) {
        $totalDistance += (int) ($row['distance_raw'] ?? 0);
        $totalFuel += (float) ($row['fuel_litres_raw'] ?? 0);
        $totalCost += (float) ($row['fuel_cost_raw'] ?? 0);
    }

    return [
        'trip_count' => count($rows),
        'total_distance' => number_format($totalDistance) . ' km',
        'total_fuel' => rtrim(rtrim(number_format($totalFuel, 2, '.', ''), '0'), '.') . ' L',
        'total_cost' => driverPanelFormatMoney($totalCost),
    ];
}

function driverPanelBuildLogbookPrintTitle(array $driverProfile, ?array $selectedVehicle, string $periodLabel): string
{
    $parts = ['Driver Vehicle Log Book', $driverProfile['name'] ?? 'Driver'];

    if ($selectedVehicle !== null) {
        $parts[] = $selectedVehicle['registration_no'];
    }

    $parts[] = $periodLabel;

    return implode(' - ', $parts);
}

function driverPanelFetchLogbookPageData(): array
{
    $commonData = driverPanelFetchCommonData();
    $driverProfile = $commonData['driverProfile'];
    $filters = driverPanelBuildLogbookFilters();

    $empty = $commonData + [
        'driverLogbookPageUrl' => driverPanelLogbookUrl(),
        'driverLogbookFilters' => $filters,
        'driverLogbookPeriodLabel' => 'All recorded dates',
        'driverLogbookVehicleOptions' => [],
        'driverLogbookSelectedVehicle' => null,
        'driverLogbookRows' => [],
        'driverLogbookHasRows' => false,
        'driverLogbookSummary' => [
            'trip_count' => 0,
            'total_distance' => '0 km',
            'total_fuel' => '0 L',
            'total_cost' => driverPanelFormatMoney(0.0),
        ],
        'driverLogbookMemoTo' => 'University Secretary',
        'driverLogbookMemoThruOne' => 'University Bursar',
        'driverLogbookMemoThruTwo' => 'Programme Controller',
        'driverLogbookMemoFrom' => 'Ag. AEO. (Mech.) Simali Habert',
        'driverLogbookMemoDate' => date('F j, Y'),
        'driverLogbookMemoFor' => $driverProfile['name'] ?? 'Driver',
        'driverLogbookMemoSubject' => 'DRIVER VEHICLE LOG BOOK FOR ' . strtoupper((string) ($driverProfile['name'] ?? 'DRIVER')) . '.',
        'driverLogbookPrintTitle' => 'Driver Vehicle Log Book',
        'driverLogbookNotification' => null,
    ];

    if ($driverProfile === null) {
        return $empty;
    }

    try {
        $pdo = fleetDb();
        $periodRange = driverPanelResolveLogbookPeriodRange($filters);
        $vehicleOptions = driverPanelFetchLogbookVehicleOptions($pdo, (int) $driverProfile['id']);
        $selectedVehicleId = filter_var($filters['vehicle_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $selectedVehicle = driverPanelFindLogbookVehicleOption($vehicleOptions, $selectedVehicleId === false ? null : (int) $selectedVehicleId);
        $rows = driverPanelFetchLogbookRows($pdo, (int) $driverProfile['id'], $selectedVehicleId === false ? null : (int) $selectedVehicleId, $periodRange);
        $summary = driverPanelBuildLogbookSummary($rows);
        $memoFor = $selectedVehicle !== null ? (string) $selectedVehicle['registration_no'] : ($driverProfile['name'] ?? 'Driver');
        $memoSubject = $selectedVehicle !== null
            ? 'DRIVER VEHICLE LOG BOOK FOR MOTOR VEHICLE REG: NO. ' . strtoupper((string) $selectedVehicle['registration_no']) . '.'
            : 'DRIVER VEHICLE LOG BOOK FOR ' . strtoupper((string) ($driverProfile['name'] ?? 'DRIVER')) . '.';

        return $commonData + [
            'driverLogbookPageUrl' => driverPanelLogbookUrl(),
            'driverLogbookFilters' => $filters,
            'driverLogbookPeriodLabel' => $periodRange['label'],
            'driverLogbookVehicleOptions' => $vehicleOptions,
            'driverLogbookSelectedVehicle' => $selectedVehicle,
            'driverLogbookRows' => $rows,
            'driverLogbookHasRows' => $rows !== [],
            'driverLogbookSummary' => $summary,
            'driverLogbookMemoTo' => 'University Secretary',
            'driverLogbookMemoThruOne' => 'University Bursar',
            'driverLogbookMemoThruTwo' => 'Programme Controller',
            'driverLogbookMemoFrom' => 'Ag. AEO. (Mech.) Simali Habert',
            'driverLogbookMemoDate' => date('F j, Y'),
            'driverLogbookMemoFor' => $memoFor,
            'driverLogbookMemoSubject' => $memoSubject,
            'driverLogbookPrintTitle' => driverPanelBuildLogbookPrintTitle($driverProfile, $selectedVehicle, $periodRange['label']),
            'driverLogbookNotification' => null,
        ];
    } catch (Throwable $exception) {
        return $empty + [
            'driverLogbookNotification' => [
                'type' => 'error',
                'title' => 'Unable to load your log book',
                'message' => 'Your vehicle log book could not be loaded right now.',
            ],
        ];
    }
}

function driverPanelFetchTripHistory(PDO $pdo, int $driverId): array
{
    $statement = $pdo->prepare(
        "SELECT
            vl.id,
            vl.trip_date,
            vl.departure_location,
            vl.destination,
            vl.purpose,
            vl.odometer_start,
            vl.odometer_end,
            vl.distance_km,
            vl.fuel_litres,
            vl.fuel_cost,
            vl.remarks,
            v.registration_no
        FROM vehicle_logs vl
        INNER JOIN vehicles v ON v.id = vl.vehicle_id
        WHERE vl.driver_id = :driver_id
        ORDER BY vl.trip_date DESC, vl.id DESC"
    );
    $statement->execute(['driver_id' => $driverId]);

    $trips = [];

    foreach ($statement->fetchAll() as $row) {
        $isInProgress = $row['odometer_end'] === null;
        $trips[] = [
            'id' => (int) $row['id'],
            'date' => driverPanelFormatDate($row['trip_date']),
            'date_raw' => $row['trip_date'],
            'vehicle' => $row['registration_no'],
            'route' => $row['departure_location'] . ' - ' . $row['destination'],
            'from' => $row['departure_location'],
            'to' => $row['destination'],
            'purpose' => $row['purpose'],
            'distance' => $row['distance_km'] !== null ? number_format((int) $row['distance_km']) . ' km' : '-',
            'distance_raw' => $row['distance_km'] !== null ? (int) $row['distance_km'] : null,
            'odometer_start' => $row['odometer_start'] !== null ? number_format((int) $row['odometer_start']) . ' km' : '-',
            'odometer_end' => $row['odometer_end'] !== null ? number_format((int) $row['odometer_end']) . ' km' : '-',
            'fuel_litres' => $row['fuel_litres'] !== null ? rtrim(rtrim(number_format((float) $row['fuel_litres'], 2, '.', ''), '0'), '.') . ' L' : '-',
            'fuel_cost' => $row['fuel_cost'] !== null ? 'UGX ' . number_format((float) $row['fuel_cost'], 0) : '-',
            'remarks' => $row['remarks'] ?: 'No remarks recorded.',
            'status' => $isInProgress ? 'In Progress' : 'Completed',
            'status_classes' => $isInProgress
                ? 'border-blue-200 bg-fleet-primary-soft text-fleet-primary'
                : 'border-green-200 bg-fleet-success-soft text-fleet-success',
        ];
    }

    return $trips;
}

function driverPanelSelectTripDetail(array $tripHistory): ?array
{
    if ($tripHistory === []) {
        return null;
    }

    $requestedTripId = filter_var((string) ($_GET['trip_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($requestedTripId !== false) {
        foreach ($tripHistory as $trip) {
            if ((int) $trip['id'] === (int) $requestedTripId) {
                return $trip;
            }
        }
    }

    return $tripHistory[0];
}

function driverPanelFetchReportHistory(PDO $pdo, int $driverId): array
{
    $statement = $pdo->prepare(
        "SELECT
            i.id,
            i.inspection_type,
            i.invoice_number,
            i.inspection_date,
            i.overall_status,
            i.defects,
            i.works_done,
            i.repair_cost,
            v.registration_no,
            (
                SELECT mr.status
                FROM maintenance_records mr
                WHERE mr.vehicle_id = i.vehicle_id
                    AND mr.date_reported >= i.inspection_date
                ORDER BY mr.date_reported DESC, mr.id DESC
                LIMIT 1
            ) AS maintenance_status,
            (
                SELECT mr.description
                FROM maintenance_records mr
                WHERE mr.vehicle_id = i.vehicle_id
                    AND mr.date_reported >= i.inspection_date
                ORDER BY mr.date_reported DESC, mr.id DESC
                LIMIT 1
            ) AS maintenance_description,
            (
                SELECT mr.date_completed
                FROM maintenance_records mr
                WHERE mr.vehicle_id = i.vehicle_id
                    AND mr.date_reported >= i.inspection_date
                ORDER BY mr.date_reported DESC, mr.id DESC
                LIMIT 1
            ) AS maintenance_completed_date
        FROM inspections i
        INNER JOIN vehicles v ON v.id = i.vehicle_id
        WHERE i.driver_id = :driver_id
            AND i.inspection_type IN ('pre', 'post')
        ORDER BY i.inspection_date DESC, i.id DESC"
    );
    $statement->execute(['driver_id' => $driverId]);

    $reports = [];

    foreach ($statement->fetchAll() as $row) {
        $inspectionType = (string) $row['inspection_type'];
        $overallStatus = (string) ($row['overall_status'] ?? '');
        $maintenanceStatus = $row['maintenance_status'] ? ucwords(str_replace('_', ' ', (string) $row['maintenance_status'])) : 'No maintenance feedback';

        $reports[] = [
            'id' => (int) $row['id'],
            'type' => $inspectionType === 'post' ? 'Post-Trip / Follow-up' : 'Pre-Inspection',
            'type_classes' => $inspectionType === 'post'
                ? 'border-blue-200 bg-fleet-primary-soft text-fleet-primary'
                : 'border-orange-200 bg-fleet-warning-soft text-fleet-warning-strong',
            'date' => driverPanelFormatDate($row['inspection_date']),
            'vehicle' => $row['registration_no'],
            'reference' => $row['invoice_number'] ?: '-',
            'status' => $overallStatus !== '' ? ucwords(str_replace('_', ' ', $overallStatus)) : 'Pending',
            'status_classes' => match ($overallStatus) {
                'good', 'completed' => 'border-green-200 bg-fleet-success-soft text-fleet-success',
                'fair' => 'border-orange-200 bg-fleet-warning-soft text-fleet-warning-strong',
                'faulty', 'needs_repair' => 'border-red-200 bg-fleet-danger-soft text-fleet-danger',
                default => 'border-slate-200 bg-slate-100 text-slate-600',
            },
            'report_summary' => $inspectionType === 'post'
                ? ($row['works_done'] ?: 'No post-trip summary recorded.')
                : ($row['defects'] ?: 'No defects summary recorded.'),
            'maintenance_feedback' => $row['maintenance_description'] ?: 'No maintenance feedback linked yet.',
            'maintenance_status' => $maintenanceStatus,
            'maintenance_completed_date' => driverPanelFormatDate($row['maintenance_completed_date']),
        ];
    }

    return $reports;
}

function driverPanelFetchHistoryPageData(): array
{
    $commonData = driverPanelFetchCommonData();
    $driverProfile = $commonData['driverProfile'];
    $tripHistory = [];
    $tripDetail = null;
    $reportHistory = [];

    try {
        if ($driverProfile !== null) {
            $pdo = fleetDb();
            $tripHistory = driverPanelFetchTripHistory($pdo, (int) $driverProfile['id']);
            $tripDetail = driverPanelSelectTripDetail($tripHistory);
            $reportHistory = driverPanelFetchReportHistory($pdo, (int) $driverProfile['id']);
        }
    } catch (Throwable $exception) {
        // Keep the page resilient and render empty states instead of failing hard.
    }

    return $commonData + [
        'tripHistory' => $tripHistory,
        'tripDetail' => $tripDetail,
        'reportHistory' => $reportHistory,
    ];
}

function driverPanelBuildReminderNotifications(array $commonData): array
{
    $reminders = [];
    $latestTrip = $commonData['latestTrip'];
    $assignedVehicle = $commonData['assignedVehicle'];

    if ($assignedVehicle === null && $commonData['tripVehicleOptions'] === []) {
        $reminders[] = [
            'title' => 'Vehicle assignment reminder',
            'message' => 'You need an assigned vehicle before normal trip workflow can continue.',
            'tone' => 'warning',
        ];
    }

    if ($latestTrip !== null && $latestTrip['is_in_progress']) {
        $reminders[] = [
            'title' => 'Trip log reminder',
            'message' => 'You have an open trip record. End the trip after reaching your destination.',
            'tone' => 'info',
        ];
    }

    if ($reminders === []) {
        $reminders[] = [
            'title' => 'No pending reminders',
            'message' => 'There are no outstanding driver reminders right now.',
            'tone' => 'success',
        ];
    }

    return $reminders;
}

function driverPanelBuildVehicleNotifications(array $commonData): array
{
    $alerts = [];
    $assignedVehicle = $commonData['assignedVehicle'];

    if ($assignedVehicle !== null) {
        $alerts[] = [
            'title' => 'Vehicle condition',
            'message' => $assignedVehicle['registration_no'] . ' is currently marked ' . strtolower($assignedVehicle['status_label']) . '.',
            'tone' => in_array($assignedVehicle['status_label'], ['Maintenance', 'Grounded'], true) ? 'danger' : 'success',
        ];
    }

    if ($alerts === []) {
        $alerts[] = [
            'title' => 'No vehicle alerts',
            'message' => 'No urgent vehicle-specific alerts are active at the moment.',
            'tone' => 'success',
        ];
    }

    return $alerts;
}

function driverPanelFetchDriverMessages(PDO $pdo, array $driverProfile): array
{
    $statement = $pdo->prepare(
        "SELECT
            c.subject,
            c.message,
            c.message_type,
            c.created_at,
            COALESCE(u.name, 'Transport Office') AS sender_name,
            cr.delivery_status
        FROM communication_recipients cr
        INNER JOIN communications c ON c.id = cr.communication_id
        LEFT JOIN users u ON u.id = c.sender_user_id
        WHERE cr.driver_id = :driver_id
            OR (cr.recipient_email = :driver_email AND :driver_email <> '')
        ORDER BY c.created_at DESC, c.id DESC
        LIMIT 10"
    );
    $statement->execute([
        'driver_id' => (int) $driverProfile['id'],
        'driver_email' => (string) ($driverProfile['email'] ?? ''),
    ]);

    $messages = [];

    foreach ($statement->fetchAll() as $row) {
        $messages[] = [
            'subject' => $row['subject'],
            'message' => $row['message'],
            'sender' => $row['sender_name'],
            'date' => date('d M Y H:i', strtotime((string) $row['created_at'])),
            'type' => ucwords(str_replace('_', ' ', (string) $row['message_type'])),
            'delivery_status' => ucwords((string) $row['delivery_status']),
        ];
    }

    return $messages;
}

function driverPanelBuildIncidentFormDataFromPost(): array
{
    return [
        'incident_type' => strtolower(trim((string) ($_POST['incident_type'] ?? 'breakdown'))),
        'incident_date' => trim((string) ($_POST['incident_date'] ?? date('Y-m-d'))),
        'location' => trim((string) ($_POST['location'] ?? '')),
        'subject' => trim((string) ($_POST['subject'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
        'urgency' => strtolower(trim((string) ($_POST['urgency'] ?? 'medium'))),
    ];
}

function driverPanelValidateIncidentFormData(array $formData, array $driverProfile, ?array $assignedVehicle): array
{
    $allowedTypes = ['breakdown', 'accident', 'unusual_issue'];
    $allowedUrgency = ['low', 'medium', 'high', 'critical'];

    if ($formData['subject'] === '' || $formData['description'] === '') {
        throw new RuntimeException('Incident subject and description are required.');
    }

    if (!in_array($formData['incident_type'], $allowedTypes, true)) {
        throw new RuntimeException('Please choose a valid incident type.');
    }

    if (!in_array($formData['urgency'], $allowedUrgency, true)) {
        throw new RuntimeException('Please choose a valid urgency level.');
    }

    $date = DateTimeImmutable::createFromFormat('Y-m-d', $formData['incident_date']);
    $dateErrors = DateTimeImmutable::getLastErrors();
    if (!$date || ($dateErrors['warning_count'] ?? 0) > 0 || ($dateErrors['error_count'] ?? 0) > 0) {
        throw new RuntimeException('Please provide a valid incident date.');
    }

    return [
        'driver_id' => (int) $driverProfile['id'],
        'vehicle_id' => $assignedVehicle['id'] ?? null,
        'incident_type' => $formData['incident_type'],
        'incident_date' => $date->format('Y-m-d'),
        'location' => $formData['location'] === '' ? null : $formData['location'],
        'subject' => $formData['subject'],
        'description' => $formData['description'],
        'urgency' => $formData['urgency'],
    ];
}

function driverPanelHandleIncidentReport(): void
{
    $formData = driverPanelBuildIncidentFormDataFromPost();
    $responseStatus = 200;
    $responsePayload = [
        'success' => false,
        'message' => 'The incident report could not be saved right now.',
        'reload' => false,
    ];

    try {
        $pdo = fleetDb();
        driverPanelEnsureIncidentReportsTable($pdo);
        $commonData = driverPanelFetchCommonData();
        $driverProfile = $commonData['driverProfile'];
        $assignedVehicle = $commonData['assignedVehicle'];

        if ($driverProfile === null) {
            throw new RuntimeException('A driver profile is required before submitting an incident report.');
        }

        $validated = driverPanelValidateIncidentFormData($formData, $driverProfile, $assignedVehicle);
        $statement = $pdo->prepare(
            "INSERT INTO incident_reports (
                driver_id,
                vehicle_id,
                incident_type,
                incident_date,
                location,
                subject,
                description,
                urgency
            ) VALUES (
                :driver_id,
                :vehicle_id,
                :incident_type,
                :incident_date,
                :location,
                :subject,
                :description,
                :urgency
            )"
        );
        $statement->bindValue(':driver_id', $validated['driver_id'], PDO::PARAM_INT);
        $statement->bindValue(':vehicle_id', $validated['vehicle_id'], $validated['vehicle_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':incident_type', $validated['incident_type']);
        $statement->bindValue(':incident_date', $validated['incident_date']);
        $statement->bindValue(':location', $validated['location'], $validated['location'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':subject', $validated['subject']);
        $statement->bindValue(':description', $validated['description']);
        $statement->bindValue(':urgency', $validated['urgency']);
        $statement->execute();
        $incidentId = (int) $pdo->lastInsertId();
        fleetTrackActivity([
            'module_key' => 'driver-panel',
            'action_key' => 'reported_incident',
            'action_label' => 'Reported incident',
            'description' => 'Driver submitted an incident report.',
            'target_type' => 'incident_report',
            'target_id' => $incidentId,
            'target_label' => $validated['subject'],
            'metadata' => [
                'urgency' => $validated['urgency'],
            ],
        ], $pdo);

        driverPanelSetMessagesFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Incident reported successfully',
                'message' => 'Your incident report has been submitted to the transport office.',
            ],
        ]);
        $responsePayload = [
            'success' => true,
            'message' => 'Your incident report has been submitted to the transport office.',
            'reload' => true,
            'action' => 'submit_incident',
        ];
    } catch (RuntimeException $exception) {
        driverPanelSetMessagesFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Incident was not reported',
                'message' => $exception->getMessage(),
            ],
            'incident_form_data' => $formData,
        ]);
        $responseStatus = 422;
        $responsePayload = [
            'success' => false,
            'message' => $exception->getMessage(),
            'reload' => false,
            'action' => 'submit_incident',
        ];
    } catch (Throwable $exception) {
        driverPanelSetMessagesFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Incident was not reported',
                'message' => 'A system error occurred while saving the incident report.',
            ],
            'incident_form_data' => $formData,
        ]);
        $responseStatus = 500;
        $responsePayload = [
            'success' => false,
            'message' => 'A system error occurred while saving the incident report.',
            'reload' => false,
            'action' => 'submit_incident',
        ];
    }

    // Sends JSON to jQuery requests while preserving the original redirect flow for normal posts.
    fleetFinishResponse(driverPanelMessagesUrlPath(), $responsePayload, $responseStatus);
}

function driverPanelMessagesUrlPath(): string
{
    return '/fleet-system/driver-panel/messages';
}

function driverPanelFetchIncidentHistory(PDO $pdo, int $driverId): array
{
    driverPanelEnsureIncidentReportsTable($pdo);

    $statement = $pdo->prepare(
        "SELECT
            ir.incident_type,
            ir.incident_date,
            ir.location,
            ir.subject,
            ir.description,
            ir.urgency,
            ir.status,
            v.registration_no
        FROM incident_reports ir
        LEFT JOIN vehicles v ON v.id = ir.vehicle_id
        WHERE ir.driver_id = :driver_id
        ORDER BY ir.incident_date DESC, ir.id DESC
        LIMIT 10"
    );
    $statement->execute(['driver_id' => $driverId]);

    $incidents = [];

    foreach ($statement->fetchAll() as $row) {
        $status = (string) $row['status'];
        $incidents[] = [
            'type' => ucwords(str_replace('_', ' ', (string) $row['incident_type'])),
            'date' => driverPanelFormatDate($row['incident_date']),
            'location' => $row['location'] ?: 'No location given',
            'subject' => $row['subject'],
            'description' => $row['description'],
            'urgency' => ucfirst((string) $row['urgency']),
            'vehicle' => $row['registration_no'] ?: 'No linked vehicle',
            'status' => ucwords(str_replace('_', ' ', $status)),
            'status_classes' => match ($status) {
                'resolved' => 'border-green-200 bg-fleet-success-soft text-fleet-success',
                'under_review' => 'border-blue-200 bg-fleet-primary-soft text-fleet-primary',
                default => 'border-orange-200 bg-fleet-warning-soft text-fleet-warning-strong',
            },
        ];
    }

    return $incidents;
}

function driverPanelFetchMessagesPageData(): array
{
    $commonData = driverPanelFetchCommonData();
    $flash = driverPanelPullMessagesFlash();
    $notification = $flash['notification'] ?? null;
    $driverProfile = $commonData['driverProfile'];
    $transportMessages = [];
    $incidentHistory = [];

    try {
        if ($driverProfile !== null) {
            $pdo = fleetDb();
            $transportMessages = driverPanelFetchDriverMessages($pdo, $driverProfile);
            $incidentHistory = driverPanelFetchIncidentHistory($pdo, (int) $driverProfile['id']);
        }
    } catch (Throwable $exception) {
        $notification = $notification ?? [
            'type' => 'error',
            'title' => 'Unable to load message center',
            'message' => 'Messages or incident records could not be loaded right now.',
        ];
    }

    $incidentFormData = $flash['incident_form_data'] ?? [
        'incident_type' => 'breakdown',
        'incident_date' => date('Y-m-d'),
        'location' => '',
        'subject' => '',
        'description' => '',
        'urgency' => 'medium',
    ];

    return $commonData + [
        'messagesNotification' => $notification,
        'transportMessages' => $transportMessages,
        'driverReminders' => driverPanelBuildReminderNotifications($commonData),
        'vehicleNotifications' => driverPanelBuildVehicleNotifications($commonData),
        'incidentFormData' => $incidentFormData,
        'incidentHistory' => $incidentHistory,
        'messagesFormAction' => driverPanelHandlerUrl(),
    ];
}

function driverPanelHandleRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        fleetFinishResponse(
            driverPanelDashboardUrl(),
            [
                'success' => false,
                'message' => 'Only POST requests are allowed for driver panel actions.',
                'reload' => false,
            ],
            405
        );
    }

    $action = trim((string) ($_POST['driver_panel_action'] ?? ''));

    if ($action === 'change_password') {
        driverPanelHandlePasswordChange();
    }

    if ($action === 'submit_pre_trip') {
        driverPanelSetPreTripFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Driver inspection submission disabled',
                'message' => 'Pre-inspections are now managed from the admin side only.',
            ],
        ]);
        fleetFinishResponse(
            driverPanelTripLogUrl(),
            [
                'success' => false,
                'message' => 'Pre-inspections are now managed from the admin side only.',
                'reload' => true,
                'action' => 'submit_pre_trip',
            ],
            403
        );
    }

    if ($action === 'start_trip') {
        driverPanelHandleStartTrip();
    }

    if ($action === 'end_trip') {
        driverPanelHandleEndTrip();
    }

    if ($action === 'submit_incident') {
        driverPanelHandleIncidentReport();
    }

    fleetFinishResponse(
        driverPanelDashboardUrl(),
        [
            'success' => false,
            'message' => 'The requested driver panel action is not supported.',
            'reload' => false,
        ],
        400
    );
}

if (basename(__FILE__) === basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    driverPanelHandleRequest();
}
