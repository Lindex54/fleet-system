<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

// Vehicle constants and lightweight page/session helpers
const VEHICLE_ALLOWED_TYPES = ['sedan', 'suv', 'pickup', 'truck', 'van', 'bus', 'motorcycle', 'other'];
const VEHICLE_ALLOWED_FUELS = ['petrol', 'diesel', 'hybrid', 'electric', 'other'];
const VEHICLE_ALLOWED_STATUSES = ['active', 'maintenance', 'grounded', 'disposed'];

// Starts the session used for flash notifications if it is not already active.
function vehicleStartSession(): void
{
    // Flash notifications for form submissions are stored in session state.
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

// Returns the vehicles page URL used after redirects.
function vehiclePageUrl(): string
{
    return '/fleet-system/modules/vehicles/index.php';
}

// Returns the POST endpoint URL for vehicle form submissions.
function vehicleHandlerUrl(): string
{
    return '/fleet-system/handlers/vehicle.php';
}

// Stores one-time vehicle feedback in session flash state.
function vehicleSetFlash(array $payload): void
{
    // Save one-time UI feedback for the next page load after redirect.
    vehicleStartSession();
    $_SESSION['vehicle_flash'] = $payload;
}

// Pulls and clears one-time vehicle feedback from session flash state.
function vehiclePullFlash(): ?array
{
    vehicleStartSession();

    if (!isset($_SESSION['vehicle_flash']) || !is_array($_SESSION['vehicle_flash'])) {
        return null;
    }

    // Flash data is consumed once so old alerts do not keep reappearing.
    $flash = $_SESSION['vehicle_flash'];
    unset($_SESSION['vehicle_flash']);

    return $flash;
}

// Converts database status values into table-friendly labels.
function vehicleNormalizeStatus(string $status): string
{
    return match ($status) {
        'active' => 'Active',
        'maintenance' => 'Maintenance',
        'grounded' => 'Grounded',
        'disposed' => 'Disposed',
        default => ucfirst($status),
    };
}

// Formats stored vehicle type values for display.
function vehicleNormalizeType(string $type): string
{
    return ucfirst($type);
}

// Department helper used by create flow
// Finds a department by name or creates it when a new one is typed in the form.
function vehicleFindOrCreateDepartmentId(PDO $pdo, string $departmentName): ?int
{
    $departmentName = trim($departmentName);
    if ($departmentName === '') {
        return null;
    }

    $select = $pdo->prepare('SELECT id FROM departments WHERE name = :name LIMIT 1');
    $select->execute(['name' => $departmentName]);
    $departmentId = $select->fetchColumn();

    if ($departmentId !== false) {
        return (int) $departmentId;
    }

    // New department names entered from the form are created on demand.
    $code = strtoupper(preg_replace('/[^A-Z0-9]+/', '', substr($departmentName, 0, 10)) ?: 'DEPT');
    $insert = $pdo->prepare('INSERT INTO departments (name, code) VALUES (:name, :code)');
    $insert->execute([
        'name' => $departmentName,
        'code' => $code . '-' . strtoupper(substr(sha1($departmentName . microtime(true)), 0, 6)),
    ]);

    return (int) $pdo->lastInsertId();
}

// Page data loader for the vehicle table and add modal
// Loads vehicle rows plus flash state for the vehicles page.
function vehicleFetchPageData(): array
{
    // The page reads any flash notice first, then loads the freshest vehicle list from MySQL.
    $flash = vehiclePullFlash();
    $notification = $flash['notification'] ?? null;
    $formData = $flash['form_data'] ?? [];
    $openModal = (bool) ($flash['open_modal'] ?? false);

    $sql = <<<SQL
        SELECT
            v.registration_no,
            v.make,
            v.model,
            v.manufacture_year,
            v.vehicle_type,
            COALESCE(d.name, '-') AS department_name,
            v.current_mileage,
            v.insurance_expiry,
            v.status
        FROM vehicles v
        LEFT JOIN departments d ON d.id = v.department_id
        ORDER BY v.created_at DESC, v.id DESC
    SQL;

    $vehicles = [];

    try {
        $rows = fleetDb()->query($sql)->fetchAll();

        foreach ($rows as $row) {
            // Shape database rows into the same view-friendly structure the table expects.
            $vehicles[] = [
                'reg' => $row['registration_no'],
                'make' => $row['make'],
                'model' => $row['model'],
                'year' => $row['manufacture_year'] ?: '-',
                'type' => vehicleNormalizeType((string) $row['vehicle_type']),
                'department' => $row['department_name'],
                'mileage' => (string) $row['current_mileage'],
                'insurance' => $row['insurance_expiry'] ?: '-',
                'repairs' => '-',
                'status' => vehicleNormalizeStatus((string) $row['status']),
            ];
        }
    } catch (Throwable $exception) {
        $notification = [
            'type' => 'error',
            'title' => 'Unable to load vehicles',
            'message' => 'The vehicles list could not be loaded from the database right now.',
        ];
    }

    return [
        'vehicles' => $vehicles,
        'hasVehicles' => count($vehicles) > 0,
        'vehicleNotification' => $notification,
        'vehicleFormData' => $formData,
        'shouldOpenVehicleModal' => $openModal,
        'vehicleFormAction' => vehicleHandlerUrl(),
    ];
}

// POST handler for adding vehicles
// Validates and stores a newly submitted vehicle record.
function vehicleHandleCreate(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . vehiclePageUrl());
        exit;
    }

    // Normalize incoming form values before validation and database insert.
    $registrationNumber = strtoupper(trim((string) ($_POST['registration_number'] ?? '')));
    $make = trim((string) ($_POST['make'] ?? ''));
    $model = trim((string) ($_POST['model'] ?? ''));
    $year = trim((string) ($_POST['year'] ?? ''));
    $vehicleType = strtolower(trim((string) ($_POST['vehicle_type'] ?? 'other')));
    $fuelType = strtolower(trim((string) ($_POST['fuel_type'] ?? 'diesel')));
    $department = trim((string) ($_POST['department'] ?? ''));
    $currentMileage = trim((string) ($_POST['current_mileage'] ?? '0'));
    $status = strtolower(trim((string) ($_POST['status'] ?? 'active')));

    $formData = [
        'registration_number' => $registrationNumber,
        'make' => $make,
        'model' => $model,
        'year' => $year,
        'vehicle_type' => $vehicleType,
        'fuel_type' => $fuelType,
        'department' => $department,
        'current_mileage' => $currentMileage,
        'status' => $status,
    ];

    if ($registrationNumber === '' || $make === '' || $model === '') {
        vehicleSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Vehicle was not added',
                'message' => 'Registration number, make, and model are required.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
        ]);

        header('Location: ' . vehiclePageUrl());
        exit;
    }

    // Keep enum-backed values inside the options supported by the database schema.
    if (!in_array($vehicleType, VEHICLE_ALLOWED_TYPES, true)) {
        $vehicleType = 'other';
    }

    if (!in_array($fuelType, VEHICLE_ALLOWED_FUELS, true)) {
        $fuelType = 'other';
    }

    if (!in_array($status, VEHICLE_ALLOWED_STATUSES, true)) {
        $status = 'active';
    }

    $currentMileageValue = filter_var($currentMileage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($currentMileageValue === false) {
        $currentMileageValue = 0;
    }

    $yearValue = null;
    if ($year !== '') {
        $parsedYear = filter_var($year, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1980, 'max_range' => 2035]]);
        if ($parsedYear === false) {
            vehicleSetFlash([
                'notification' => [
                    'type' => 'error',
                    'title' => 'Vehicle was not added',
                    'message' => 'Please enter a valid manufacture year between 1980 and 2035.',
                ],
                'form_data' => $formData,
                'open_modal' => true,
            ]);

            header('Location: ' . vehiclePageUrl());
            exit;
        }

        $yearValue = (int) $parsedYear;
    }

    try {
        $pdo = fleetDb();
        $departmentId = vehicleFindOrCreateDepartmentId($pdo, $department);
        // Insert the new vehicle and let nullable fields remain null when left blank.
        $statement = $pdo->prepare(
            'INSERT INTO vehicles (
                department_id,
                registration_no,
                make,
                model,
                manufacture_year,
                vehicle_type,
                fuel_type,
                current_mileage,
                status
            ) VALUES (
                :department_id,
                :registration_no,
                :make,
                :model,
                :manufacture_year,
                :vehicle_type,
                :fuel_type,
                :current_mileage,
                :status
            )'
        );

        $statement->bindValue(':department_id', $departmentId, $departmentId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':registration_no', $registrationNumber);
        $statement->bindValue(':make', $make);
        $statement->bindValue(':model', $model);
        $statement->bindValue(':manufacture_year', $yearValue, $yearValue === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':vehicle_type', $vehicleType);
        $statement->bindValue(':fuel_type', $fuelType);
        $statement->bindValue(':current_mileage', $currentMileageValue, PDO::PARAM_INT);
        $statement->bindValue(':status', $status);
        $statement->execute();

        vehicleSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Vehicle added successfully',
                'message' => sprintf('%s has been saved to the fleet register.', $registrationNumber),
            ],
        ]);
    } catch (PDOException $exception) {
        $message = 'Vehicle could not be added. Please try again.';

        // MySQL uses SQLSTATE 23000 for duplicate keys and similar integrity violations.
        if ((int) $exception->getCode() === 23000) {
            $message = 'Vehicle could not be added because that registration number already exists.';
        }

        vehicleSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Vehicle was not added',
                'message' => $message,
            ],
            'form_data' => $formData,
            'open_modal' => true,
        ]);
    } catch (Throwable $exception) {
        vehicleSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Vehicle was not added',
                'message' => 'A system error occurred while saving the vehicle.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
        ]);
    }

    header('Location: ' . vehiclePageUrl());
    exit;
}

if (basename(__FILE__) === basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    // Allow this file to act as both a reusable helper and a direct form endpoint.
    vehicleHandleCreate();
}
