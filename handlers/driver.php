<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

// Driver constants and page/session helpers
const DRIVER_ALLOWED_STATUSES = ['active', 'inactive', 'suspended'];

// Starts the session used for driver flash notifications if it is not already active.
function driverStartSession(): void
{
    // Flash notifications for driver actions are stored in the session across redirects.
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

// Returns the drivers page URL used after redirects.
function driverPageUrl(): string
{
    return '/fleet-system/modules/drivers/index.php';
}

// Returns the POST endpoint URL for driver form submissions.
function driverHandlerUrl(): string
{
    return '/fleet-system/handlers/driver.php';
}

// Stores one-time driver feedback in session flash state.
function driverSetFlash(array $payload): void
{
    // Save one-time UI feedback that is rendered after the redirect back to the page.
    driverStartSession();
    $_SESSION['driver_flash'] = $payload;
}

// Pulls and clears one-time driver feedback from session flash state.
function driverPullFlash(): ?array
{
    driverStartSession();

    if (!isset($_SESSION['driver_flash']) || !is_array($_SESSION['driver_flash'])) {
        return null;
    }

    // Flash data is consumed once so it does not repeat after refresh.
    $flash = $_SESSION['driver_flash'];
    unset($_SESSION['driver_flash']);

    return $flash;
}

// Converts database status values into table-friendly labels.
function driverNormalizeStatus(string $status): string
{
    return match ($status) {
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
        default => ucfirst($status),
    };
}

// Department and vehicle option helpers used by the driver modal
// Finds a department by name or creates it when a new one is typed in the form.
function driverFindOrCreateDepartmentId(PDO $pdo, string $departmentName): ?int
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

    // New department names typed into the form are created on demand.
    $code = strtoupper(preg_replace('/[^A-Z0-9]+/', '', substr($departmentName, 0, 10)) ?: 'DEPT');
    $insert = $pdo->prepare('INSERT INTO departments (name, code) VALUES (:name, :code)');
    $insert->execute([
        'name' => $departmentName,
        'code' => $code . '-' . strtoupper(substr(sha1($departmentName . microtime(true)), 0, 6)),
    ]);

    return (int) $pdo->lastInsertId();
}

// Loads vehicle options and current assignment state for the driver dropdown.
function driverFetchVehicleOptions(PDO $pdo): array
{
    // The assignment dropdown is powered by current vehicles from the database, not free text.
    $statement = $pdo->query(
        'SELECT
            v.id,
            v.registration_no,
            v.status,
            active_assignment.driver_id AS assigned_driver_id,
            assigned_driver.full_name AS assigned_driver_name
        FROM vehicles v
        LEFT JOIN vehicle_assignments active_assignment
            ON active_assignment.vehicle_id = v.id
            AND active_assignment.released_at IS NULL
        LEFT JOIN drivers assigned_driver
            ON assigned_driver.id = active_assignment.driver_id
        WHERE v.status <> \'disposed\'
        ORDER BY v.registration_no ASC'
    );

    return $statement->fetchAll();
}

// Page data loader for the drivers table and modal
// Loads driver rows, vehicle dropdown options, and flash state for the page.
function driverFetchPageData(): array
{
    // The page needs current drivers, vehicle options, and any flash state from the last action.
    $flash = driverPullFlash();
    $notification = $flash['notification'] ?? null;
    $formData = $flash['form_data'] ?? [];
    $openModal = (bool) ($flash['open_modal'] ?? false);
    $formMode = $flash['form_mode'] ?? 'create';

    $drivers = [];
    $vehicleOptions = [];

    try {
        $pdo = fleetDb();
        $vehicleOptions = driverFetchVehicleOptions($pdo);
        $statement = $pdo->query(
            'SELECT
                d.id,
                d.full_name,
                d.employee_id,
                d.phone,
                d.email,
                d.license_number,
                d.license_classes,
                d.license_expiry,
                d.status,
                COALESCE(dep.name, \'-\') AS department_name,
                v.id AS assigned_vehicle_id,
                v.registration_no AS assigned_vehicle_reg
            FROM drivers d
            LEFT JOIN departments dep ON dep.id = d.department_id
            LEFT JOIN vehicle_assignments active_assignment
                ON active_assignment.driver_id = d.id
                AND active_assignment.released_at IS NULL
            LEFT JOIN vehicles v ON v.id = active_assignment.vehicle_id
            ORDER BY d.created_at DESC, d.id DESC'
        );

        foreach ($statement->fetchAll() as $row) {
            // Shape database rows into the format the driver table and edit modal expect.
            $drivers[] = [
                'id' => (int) $row['id'],
                'name' => $row['full_name'],
                'employee_id' => $row['employee_id'] ?: '',
                'email' => $row['email'] ?: '-',
                'phone' => $row['phone'] ?: '-',
                'license' => $row['license_number'],
                'license_classes' => $row['license_classes'] ?: '',
                'license_expiry' => $row['license_expiry'] ?: '',
                'department' => $row['department_name'] === '-' ? '' : $row['department_name'],
                'assigned' => $row['assigned_vehicle_reg'] ?: '-',
                'assigned_vehicle_id' => $row['assigned_vehicle_id'] !== null ? (int) $row['assigned_vehicle_id'] : null,
                'status' => driverNormalizeStatus((string) $row['status']),
                'status_value' => (string) $row['status'],
            ];
        }
    } catch (Throwable $exception) {
        $notification = [
            'type' => 'error',
            'title' => 'Unable to load drivers',
            'message' => 'The driver list could not be loaded from the database right now.',
        ];
    }

    return [
        'drivers' => $drivers,
        'hasDrivers' => count($drivers) > 0,
        'driverNotification' => $notification,
        'driverFormData' => $formData,
        'shouldOpenDriverModal' => $openModal,
        'driverFormMode' => $formMode,
        'driverFormAction' => driverHandlerUrl(),
        'driverVehicleOptions' => $vehicleOptions,
    ];
}

// Form normalization and validation helpers
// Collects and trims raw POST values from the driver form.
function driverBuildFormDataFromPost(): array
{
    // Normalize submitted values before validation and database writes.
    return [
        'driver_id' => trim((string) ($_POST['driver_id'] ?? '')),
        'full_name' => trim((string) ($_POST['full_name'] ?? '')),
        'employee_id' => trim((string) ($_POST['employee_id'] ?? '')),
        'phone' => trim((string) ($_POST['phone'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'license_number' => trim((string) ($_POST['license_number'] ?? '')),
        'license_classes' => trim((string) ($_POST['license_classes'] ?? '')),
        'license_expiry' => trim((string) ($_POST['license_expiry'] ?? '')),
        'department' => trim((string) ($_POST['department'] ?? '')),
        'assigned_vehicle' => trim((string) ($_POST['assigned_vehicle'] ?? '')),
        'status' => strtolower(trim((string) ($_POST['status'] ?? 'active'))),
    ];
}

// Validates and normalizes submitted driver form values.
function driverValidateFormData(array $formData): array
{
    // Shared validation keeps create and update behavior consistent.
    if ($formData['full_name'] === '' || $formData['license_number'] === '') {
        throw new RuntimeException('Full name and license number are required.');
    }

    if ($formData['email'] !== '' && filter_var($formData['email'], FILTER_VALIDATE_EMAIL) === false) {
        throw new RuntimeException('Please enter a valid email address.');
    }

    if (!in_array($formData['status'], DRIVER_ALLOWED_STATUSES, true)) {
        throw new RuntimeException('Please select a valid driver status.');
    }

    $licenseExpiry = null;
    if ($formData['license_expiry'] !== '') {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $formData['license_expiry']);
        $errors = DateTimeImmutable::getLastErrors();

        if (!$date || ($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
            throw new RuntimeException('Please enter a valid license expiry date.');
        }

        $licenseExpiry = $date->format('Y-m-d');
    }

    $assignedVehicleId = null;
    if ($formData['assigned_vehicle'] !== '' && $formData['assigned_vehicle'] !== 'unassigned') {
        $assignedVehicleId = filter_var($formData['assigned_vehicle'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($assignedVehicleId === false) {
            throw new RuntimeException('Please select a valid vehicle from the dropdown.');
        }
    }

    return [
        'full_name' => $formData['full_name'],
        'employee_id' => $formData['employee_id'] === '' ? null : $formData['employee_id'],
        'phone' => $formData['phone'] === '' ? null : $formData['phone'],
        'email' => $formData['email'] === '' ? null : $formData['email'],
        'license_number' => $formData['license_number'],
        'license_classes' => $formData['license_classes'] === '' ? null : $formData['license_classes'],
        'license_expiry' => $licenseExpiry,
        'department' => $formData['department'],
        'assigned_vehicle_id' => $assignedVehicleId === null ? null : (int) $assignedVehicleId,
        'status' => $formData['status'],
    ];
}

// Vehicle assignment guards and sync helpers
// Confirms a vehicle exists and is not actively assigned to another driver.
function driverAssertVehicleCanBeAssigned(PDO $pdo, int $vehicleId, ?int $currentDriverId = null): void
{
    // Only real vehicles can be assigned, and active assignments cannot be stolen silently.
    $statement = $pdo->prepare(
        'SELECT
            v.id,
            active_assignment.driver_id
        FROM vehicles v
        LEFT JOIN vehicle_assignments active_assignment
            ON active_assignment.vehicle_id = v.id
            AND active_assignment.released_at IS NULL
        WHERE v.id = :id
            AND v.status <> \'disposed\'
        LIMIT 1'
    );
    $statement->execute(['id' => $vehicleId]);
    $vehicle = $statement->fetch();

    if (!$vehicle) {
        throw new RuntimeException('The selected vehicle is not available for assignment.');
    }

    if ($vehicle['driver_id'] !== null && (int) $vehicle['driver_id'] !== $currentDriverId) {
        throw new RuntimeException('The selected vehicle is already assigned to another driver.');
    }
}

// Updates active vehicle assignments so they match the saved driver form.
function driverSyncVehicleAssignment(PDO $pdo, int $driverId, ?int $vehicleId): void
{
    // Driver assignment changes are managed in vehicle_assignments so they stay consistent with the schema.
    $currentAssignmentStatement = $pdo->prepare(
        'SELECT id, vehicle_id
        FROM vehicle_assignments
        WHERE driver_id = :driver_id
            AND released_at IS NULL
        ORDER BY id DESC
        LIMIT 1'
    );
    $currentAssignmentStatement->execute(['driver_id' => $driverId]);
    $currentAssignment = $currentAssignmentStatement->fetch();

    if ($vehicleId === null) {
        if ($currentAssignment) {
            $release = $pdo->prepare(
                'UPDATE vehicle_assignments
                SET released_at = CURDATE()
                WHERE driver_id = :driver_id
                    AND released_at IS NULL'
            );
            $release->execute(['driver_id' => $driverId]);
        }

        return;
    }

    driverAssertVehicleCanBeAssigned($pdo, $vehicleId, $driverId);

    if ($currentAssignment && (int) $currentAssignment['vehicle_id'] === $vehicleId) {
        return;
    }

    if ($currentAssignment) {
        $release = $pdo->prepare(
            'UPDATE vehicle_assignments
            SET released_at = CURDATE()
            WHERE driver_id = :driver_id
                AND released_at IS NULL'
        );
        $release->execute(['driver_id' => $driverId]);
    }

    $insert = $pdo->prepare(
        'INSERT INTO vehicle_assignments (vehicle_id, driver_id, assigned_at)
        VALUES (:vehicle_id, :driver_id, CURDATE())'
    );
    $insert->execute([
        'vehicle_id' => $vehicleId,
        'driver_id' => $driverId,
    ]);
}

// POST handler for create/update actions
// Handles both create and update requests for driver records.
function driverHandleCreateOrUpdate(string $action): void
{
    $formData = driverBuildFormDataFromPost();

    try {
        $validated = driverValidateFormData($formData);
        $pdo = fleetDb();
        $departmentId = driverFindOrCreateDepartmentId($pdo, $validated['department']);

        if ($action === 'update') {
            $driverId = filter_var($formData['driver_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($driverId === false) {
                throw new RuntimeException('The selected driver could not be identified.');
            }

            $exists = $pdo->prepare('SELECT COUNT(*) FROM drivers WHERE id = :id');
            $exists->execute(['id' => $driverId]);
            if ((int) $exists->fetchColumn() === 0) {
                throw new RuntimeException('The selected driver no longer exists.');
            }

            // Updates keep the same driver row while refreshing editable profile fields.
            $statement = $pdo->prepare(
                'UPDATE drivers SET
                    department_id = :department_id,
                    full_name = :full_name,
                    employee_id = :employee_id,
                    phone = :phone,
                    email = :email,
                    license_number = :license_number,
                    license_classes = :license_classes,
                    license_expiry = :license_expiry,
                    status = :status
                WHERE id = :driver_id'
            );
            $statement->bindValue(':driver_id', (int) $driverId, PDO::PARAM_INT);
        } else {
            // New drivers are inserted first, then any selected vehicle is assigned afterwards.
            $statement = $pdo->prepare(
                'INSERT INTO drivers (
                    department_id,
                    full_name,
                    employee_id,
                    phone,
                    email,
                    license_number,
                    license_classes,
                    license_expiry,
                    status
                ) VALUES (
                    :department_id,
                    :full_name,
                    :employee_id,
                    :phone,
                    :email,
                    :license_number,
                    :license_classes,
                    :license_expiry,
                    :status
                )'
            );
        }

        $statement->bindValue(':department_id', $departmentId, $departmentId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':full_name', $validated['full_name']);
        $statement->bindValue(':employee_id', $validated['employee_id'], $validated['employee_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':phone', $validated['phone'], $validated['phone'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':email', $validated['email'], $validated['email'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':license_number', $validated['license_number']);
        $statement->bindValue(':license_classes', $validated['license_classes'], $validated['license_classes'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':license_expiry', $validated['license_expiry'], $validated['license_expiry'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':status', $validated['status']);
        $statement->execute();

        $driverId = $action === 'update' ? (int) $formData['driver_id'] : (int) $pdo->lastInsertId();
        driverSyncVehicleAssignment($pdo, $driverId, $validated['assigned_vehicle_id']);

        driverSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => $action === 'update' ? 'Driver updated successfully' : 'Driver added successfully',
                'message' => $action === 'update'
                    ? 'The driver record has been updated successfully.'
                    : 'The driver record has been added successfully.',
            ],
        ]);
    } catch (RuntimeException $exception) {
        driverSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Driver was not updated' : 'Driver was not added',
                'message' => $exception->getMessage(),
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
    } catch (PDOException $exception) {
        $message = $action === 'update'
            ? 'The driver could not be updated. Please try again.'
            : 'The driver could not be added. Please try again.';

        // These are the most likely user-facing uniqueness failures in the drivers table.
        if ((int) $exception->getCode() === 23000) {
            $message = 'That employee ID or license number already exists in the system.';
        }

        driverSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Driver was not updated' : 'Driver was not added',
                'message' => $message,
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
    } catch (Throwable $exception) {
        driverSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Driver was not updated' : 'Driver was not added',
                'message' => $action === 'update'
                    ? 'A system error occurred while updating the driver.'
                    : 'A system error occurred while saving the driver.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
    }

    header('Location: ' . driverPageUrl());
    exit;
}

// POST handler for delete actions
// Handles delete requests for driver records.
function driverHandleDelete(): void
{
    // Deletes are handled as explicit POST requests from the driver row action.
    $driverId = filter_var((string) ($_POST['driver_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($driverId === false) {
        driverSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Driver was not deleted',
                'message' => 'The selected driver could not be identified.',
            ],
        ]);
        header('Location: ' . driverPageUrl());
        exit;
    }

    try {
        $statement = fleetDb()->prepare('DELETE FROM drivers WHERE id = :id');
        $statement->execute(['id' => $driverId]);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('The selected driver no longer exists.');
        }

        driverSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Driver deleted successfully',
                'message' => 'The selected driver has been removed from the system.',
            ],
        ]);
    } catch (RuntimeException $exception) {
        driverSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Driver was not deleted',
                'message' => $exception->getMessage(),
            ],
        ]);
    } catch (Throwable $exception) {
        driverSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Driver was not deleted',
                'message' => 'A system error occurred while deleting the driver.',
            ],
        ]);
    }

    header('Location: ' . driverPageUrl());
    exit;
}

// Request dispatcher for the driver handler endpoint
// Dispatches incoming driver POST requests by action type.
function driverHandleRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . driverPageUrl());
        exit;
    }

    // One handler dispatches create, update, and delete requests for the driver page.
    $action = trim((string) ($_POST['driver_action'] ?? 'create'));

    if ($action === 'delete') {
        driverHandleDelete();
    }

    if ($action === 'update') {
        driverHandleCreateOrUpdate('update');
    }

    driverHandleCreateOrUpdate('create');
}

if (basename(__FILE__) === basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    // This file can be included for page data or called directly as the POST endpoint.
    driverHandleRequest();
}
