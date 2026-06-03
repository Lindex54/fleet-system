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
    $formMode = $flash['form_mode'] ?? 'create';

    $sql = <<<SQL
        SELECT
            v.id,
            v.registration_no,
            v.make,
            v.model,
            v.manufacture_year,
            v.vehicle_type,
            v.fuel_type,
            COALESCE(d.name, '-') AS department_name,
            v.current_mileage,
            v.insurance_expiry,
            v.status,
            v.notes
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
                'id' => (int) $row['id'],
                'reg' => $row['registration_no'],
                'make' => $row['make'],
                'model' => $row['model'],
                'year' => $row['manufacture_year'] ?: '-',
                'type' => vehicleNormalizeType((string) $row['vehicle_type']),
                'type_value' => (string) $row['vehicle_type'],
                'fuel_type' => (string) $row['fuel_type'],
                'department' => $row['department_name'],
                'mileage' => (string) $row['current_mileage'],
                'insurance' => $row['insurance_expiry'] ?: '-',
                'insurance_raw' => $row['insurance_expiry'] ?: '',
                'repairs' => trim((string) ($row['notes'] ?? '')) !== '' ? trim((string) $row['notes']) : '-',
                'repairs_raw' => trim((string) ($row['notes'] ?? '')),
                'status' => vehicleNormalizeStatus((string) $row['status']),
                'status_value' => (string) $row['status'],
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
        'vehicleFormMode' => $formMode,
        'vehicleFormAction' => vehicleHandlerUrl(),
    ];
}

// Collects and trims raw POST values from the vehicle form.
function vehicleBuildFormDataFromPost(): array
{
    return [
        'vehicle_id' => trim((string) ($_POST['vehicle_id'] ?? '')),
        'registration_number' => strtoupper(trim((string) ($_POST['registration_number'] ?? ''))),
        'make' => trim((string) ($_POST['make'] ?? '')),
        'model' => trim((string) ($_POST['model'] ?? '')),
        'year' => trim((string) ($_POST['year'] ?? '')),
        'vehicle_type' => strtolower(trim((string) ($_POST['vehicle_type'] ?? 'other'))),
        'fuel_type' => strtolower(trim((string) ($_POST['fuel_type'] ?? 'diesel'))),
        'department' => trim((string) ($_POST['department'] ?? '')),
        'current_mileage' => trim((string) ($_POST['current_mileage'] ?? '0')),
        'insurance_expiry' => trim((string) ($_POST['insurance_expiry'] ?? '')),
        'status' => strtolower(trim((string) ($_POST['status'] ?? 'active'))),
        'repairs_done' => trim((string) ($_POST['repairs_done'] ?? '')),
    ];
}

// Validates and normalizes submitted vehicle form values.
function vehicleValidateFormData(array $formData): array
{
    if ($formData['registration_number'] === '' || $formData['make'] === '' || $formData['model'] === '') {
        throw new RuntimeException('Registration number, make, and model are required.');
    }

    if (!in_array($formData['vehicle_type'], VEHICLE_ALLOWED_TYPES, true)) {
        $formData['vehicle_type'] = 'other';
    }

    if (!in_array($formData['fuel_type'], VEHICLE_ALLOWED_FUELS, true)) {
        $formData['fuel_type'] = 'other';
    }

    if (!in_array($formData['status'], VEHICLE_ALLOWED_STATUSES, true)) {
        $formData['status'] = 'active';
    }

    $currentMileage = filter_var($formData['current_mileage'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($currentMileage === false) {
        $currentMileage = 0;
    }

    $yearValue = null;
    if ($formData['year'] !== '') {
        $parsedYear = filter_var($formData['year'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1980, 'max_range' => 2035]]);
        if ($parsedYear === false) {
            throw new RuntimeException('Please enter a valid manufacture year between 1980 and 2035.');
        }

        $yearValue = (int) $parsedYear;
    }

    $insuranceExpiry = null;
    if ($formData['insurance_expiry'] !== '') {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $formData['insurance_expiry']);
        $errors = DateTimeImmutable::getLastErrors();

        if (!$date || ($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
            throw new RuntimeException('Please enter a valid insurance expiry date.');
        }

        $insuranceExpiry = $date->format('Y-m-d');
    }

    return [
        'registration_number' => $formData['registration_number'],
        'make' => $formData['make'],
        'model' => $formData['model'],
        'year' => $yearValue,
        'vehicle_type' => $formData['vehicle_type'],
        'fuel_type' => $formData['fuel_type'],
        'department' => $formData['department'],
        'current_mileage' => (int) $currentMileage,
        'insurance_expiry' => $insuranceExpiry,
        'status' => $formData['status'],
        'repairs_done' => $formData['repairs_done'] === '' ? null : $formData['repairs_done'],
    ];
}

// Inserts or updates a vehicle record using the same normalized payload.
function vehiclePersistRecord(array $validated, string $action): void
{
    $pdo = fleetDb();
    $departmentId = vehicleFindOrCreateDepartmentId($pdo, $validated['department']);

    if ($action === 'update') {
        $vehicleId = filter_var((string) ($_POST['vehicle_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($vehicleId === false) {
            throw new RuntimeException('The selected vehicle could not be identified for editing.');
        }

        $statement = $pdo->prepare(
            'UPDATE vehicles SET
                department_id = :department_id,
                registration_no = :registration_no,
                make = :make,
                model = :model,
                manufacture_year = :manufacture_year,
                vehicle_type = :vehicle_type,
                fuel_type = :fuel_type,
                current_mileage = :current_mileage,
                insurance_expiry = :insurance_expiry,
                status = :status,
                notes = :notes
            WHERE id = :vehicle_id'
        );
        $statement->bindValue(':vehicle_id', $vehicleId, PDO::PARAM_INT);
    } else {
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
                insurance_expiry,
                status,
                notes
            ) VALUES (
                :department_id,
                :registration_no,
                :make,
                :model,
                :manufacture_year,
                :vehicle_type,
                :fuel_type,
                :current_mileage,
                :insurance_expiry,
                :status,
                :notes
            )'
        );
    }

    $statement->bindValue(':department_id', $departmentId, $departmentId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $statement->bindValue(':registration_no', $validated['registration_number']);
    $statement->bindValue(':make', $validated['make']);
    $statement->bindValue(':model', $validated['model']);
    $statement->bindValue(':manufacture_year', $validated['year'], $validated['year'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $statement->bindValue(':vehicle_type', $validated['vehicle_type']);
    $statement->bindValue(':fuel_type', $validated['fuel_type']);
    $statement->bindValue(':current_mileage', $validated['current_mileage'], PDO::PARAM_INT);
    $statement->bindValue(':insurance_expiry', $validated['insurance_expiry'], $validated['insurance_expiry'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $statement->bindValue(':status', $validated['status']);
    $statement->bindValue(':notes', $validated['repairs_done'], $validated['repairs_done'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $statement->execute();
}

// Handles delete requests for vehicle records.
function vehicleHandleDelete(): void
{
    $vehicleId = filter_var((string) ($_POST['vehicle_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($vehicleId === false) {
        vehicleSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Vehicle was not deleted',
                'message' => 'The selected vehicle could not be identified.',
            ],
        ]);

        header('Location: ' . vehiclePageUrl());
        exit;
    }

    try {
        $statement = fleetDb()->prepare('DELETE FROM vehicles WHERE id = :id');
        $statement->execute(['id' => $vehicleId]);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('The selected vehicle no longer exists.');
        }

        vehicleSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Vehicle deleted successfully',
                'message' => 'The selected vehicle has been removed from the fleet register.',
            ],
        ]);
    } catch (RuntimeException $exception) {
        vehicleSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Vehicle was not deleted',
                'message' => $exception->getMessage(),
            ],
        ]);
    } catch (Throwable $exception) {
        vehicleSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Vehicle was not deleted',
                'message' => 'A system error occurred while deleting the vehicle.',
            ],
        ]);
    }

    header('Location: ' . vehiclePageUrl());
    exit;
}

// POST handler for adding vehicles
// Validates and stores a newly submitted vehicle record.
function vehicleHandleUpsert(string $action): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . vehiclePageUrl());
        exit;
    }

    $formData = vehicleBuildFormDataFromPost();

    try {
        $validated = vehicleValidateFormData($formData);
        vehiclePersistRecord($validated, $action);

        vehicleSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => $action === 'update' ? 'Vehicle updated successfully' : 'Vehicle added successfully',
                'message' => sprintf(
                    '%s has been %s in the fleet register.',
                    $validated['registration_number'],
                    $action === 'update' ? 'updated' : 'saved'
                ),
            ],
        ]);
    } catch (RuntimeException $exception) {
        vehicleSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Vehicle was not updated' : 'Vehicle was not added',
                'message' => $exception->getMessage(),
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
    } catch (PDOException $exception) {
        $message = $action === 'update'
            ? 'Vehicle could not be updated. Please try again.'
            : 'Vehicle could not be added. Please try again.';

        // MySQL uses SQLSTATE 23000 for duplicate keys and similar integrity violations.
        if ((int) $exception->getCode() === 23000) {
            $message = $action === 'update'
                ? 'Vehicle could not be updated because that registration number already exists.'
                : 'Vehicle could not be added because that registration number already exists.';
        }

        vehicleSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Vehicle was not updated' : 'Vehicle was not added',
                'message' => $message,
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
    } catch (Throwable $exception) {
        vehicleSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Vehicle was not updated' : 'Vehicle was not added',
                'message' => $action === 'update'
                    ? 'A system error occurred while updating the vehicle.'
                    : 'A system error occurred while saving the vehicle.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
    }

    header('Location: ' . vehiclePageUrl());
    exit;
}

// Dispatches incoming vehicle POST requests by action type.
function vehicleHandleRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . vehiclePageUrl());
        exit;
    }

    $action = strtolower(trim((string) ($_POST['vehicle_action'] ?? 'create')));

    if ($action === 'delete') {
        vehicleHandleDelete();
    }

    if ($action === 'update') {
        vehicleHandleUpsert('update');
    }

    vehicleHandleUpsert('create');
}

if (basename(__FILE__) === basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    // Allow this file to act as both a reusable helper and a direct form endpoint.
    vehicleHandleRequest();
}
