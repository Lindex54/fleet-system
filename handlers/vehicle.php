<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/ajax.php';
require_once __DIR__ . '/../includes/activity-tracker.php';

// Vehicle constants and lightweight page/session helpers
const VEHICLE_ALLOWED_TYPES = ['sedan', 'suv', 'pickup', 'truck', 'van', 'bus', 'motorcycle', 'other'];
const VEHICLE_ALLOWED_FUELS = ['petrol', 'diesel', 'hybrid', 'electric', 'other'];
const VEHICLE_ALLOWED_STATUSES = ['active', 'maintenance', 'grounded', 'disposed'];
const VEHICLE_UPLOAD_MAX_BYTES = 5242880;
const VEHICLE_ALLOWED_UPLOAD_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
const VEHICLE_ALLOWED_UPLOAD_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

// Returns the absolute uploads directory used for vehicle images.
function vehicleUploadDirectoryPath(): string
{
    return dirname(__DIR__) . '/uploads/vehicles';
}

// Builds a public URL for a stored vehicle image path.
function vehicleBuildUploadUrl(?string $storedPath): string
{
    if ($storedPath === null || trim($storedPath) === '') {
        return '';
    }

    return '/fleet-system/' . ltrim($storedPath, '/');
}

// Detects whether the stored upload path should be previewed as an image.
function vehicleUploadIsImage(?string $storedPath): bool
{
    $extension = strtolower(pathinfo((string) $storedPath, PATHINFO_EXTENSION));

    return in_array($extension, VEHICLE_ALLOWED_UPLOAD_EXTENSIONS, true);
}

// Returns a display label for an uploaded vehicle image.
function vehicleUploadDisplayName(?string $storedPath): string
{
    if ($storedPath === null || trim($storedPath) === '') {
        return 'No image uploaded';
    }

    return basename($storedPath);
}

// Removes a stored vehicle image when it has been replaced or deleted.
function vehicleDeleteStoredUpload(?string $storedPath): void
{
    if ($storedPath === null || trim($storedPath) === '') {
        return;
    }

    $normalizedPath = str_replace('\\', '/', $storedPath);
    if (!str_starts_with($normalizedPath, 'uploads/vehicles/')) {
        return;
    }

    $absolutePath = dirname(__DIR__) . '/' . $normalizedPath;
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

// Ensures the uploads directory exists before vehicle images are moved into it.
function vehicleEnsureUploadDirectoryExists(): void
{
    $directory = vehicleUploadDirectoryPath();

    if (is_dir($directory)) {
        @chmod($directory, 0777);
        return;
    }

    if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('The uploads directory could not be prepared for vehicle images.');
    }

    @chmod($directory, 0777);
}

// Ensures legacy databases have the vehicle image column before reads and writes.
function vehicleEnsureImageColumn(PDO $pdo): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $statement = $pdo->query("SHOW COLUMNS FROM vehicles LIKE 'vehicle_image'");
    if ($statement->fetch() === false) {
        $pdo->exec("ALTER TABLE vehicles ADD COLUMN vehicle_image VARCHAR(255) DEFAULT NULL AFTER notes");
    }

    $checked = true;
}

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
    return '/fleet-system/modules/vehicles/';
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
            v.notes,
            v.vehicle_image
        FROM vehicles v
        LEFT JOIN departments d ON d.id = v.department_id
        ORDER BY v.created_at DESC, v.id DESC
    SQL;

    $vehicles = [];

    try {
        $pdo = fleetDb();
        vehicleEnsureImageColumn($pdo);
        $rows = $pdo->query($sql)->fetchAll();

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
                'vehicle_image' => $row['vehicle_image'] ?: '',
                'vehicle_image_url' => vehicleBuildUploadUrl($row['vehicle_image'] ?? ''),
                'vehicle_image_name' => vehicleUploadDisplayName($row['vehicle_image'] ?? ''),
                'vehicle_image_is_image' => vehicleUploadIsImage($row['vehicle_image'] ?? ''),
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
        'vehicle_image' => trim((string) ($_POST['existing_vehicle_image'] ?? '')),
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
        'vehicle_image' => $formData['vehicle_image'] === '' ? null : $formData['vehicle_image'],
    ];
}

// Loads the current vehicle row when an update needs the stored image value.
function vehicleFetchExistingRecord(PDO $pdo, int $vehicleId): array
{
    vehicleEnsureImageColumn($pdo);

    $statement = $pdo->prepare(
        'SELECT id, vehicle_image
        FROM vehicles
        WHERE id = :id
        LIMIT 1'
    );
    $statement->execute(['id' => $vehicleId]);
    $vehicle = $statement->fetch();

    if (!$vehicle) {
        throw new RuntimeException('The selected vehicle no longer exists.');
    }

    return $vehicle;
}

// Validates and stores one optional vehicle image, returning the final stored path.
function vehicleStoreOptionalUpload(string $fieldName, string $label, ?string $existingPath, array &$newUploads, array &$oldUploadsToDelete): ?string
{
    $upload = $_FILES[$fieldName] ?? null;
    if (!is_array($upload) || (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $existingPath;
    }

    $errorCode = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($errorCode !== UPLOAD_ERR_OK) {
        throw new RuntimeException($label . ' could not be uploaded.');
    }

    $temporaryPath = (string) ($upload['tmp_name'] ?? '');
    if ($temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
        throw new RuntimeException($label . ' upload was not received correctly.');
    }

    $fileSize = (int) ($upload['size'] ?? 0);
    if ($fileSize <= 0 || $fileSize > VEHICLE_UPLOAD_MAX_BYTES) {
        throw new RuntimeException($label . ' must be smaller than 5 MB.');
    }

    $originalName = (string) ($upload['name'] ?? '');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($extension, VEHICLE_ALLOWED_UPLOAD_EXTENSIONS, true)) {
        throw new RuntimeException($label . ' must be a JPG, PNG, or WEBP image.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = (string) $finfo->file($temporaryPath);
    if (!in_array($mimeType, VEHICLE_ALLOWED_UPLOAD_MIME_TYPES, true)) {
        throw new RuntimeException($label . ' must be a valid image file.');
    }

    vehicleEnsureUploadDirectoryExists();

    $storedFileName = 'vehicle-' . date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;
    $relativePath = 'uploads/vehicles/' . $storedFileName;
    $absolutePath = vehicleUploadDirectoryPath() . '/' . $storedFileName;

    if (!move_uploaded_file($temporaryPath, $absolutePath)) {
        throw new RuntimeException($label . ' could not be saved.');
    }

    $newUploads[] = $relativePath;
    if ($existingPath !== null && trim($existingPath) !== '') {
        $oldUploadsToDelete[] = $existingPath;
    }

    return $relativePath;
}

// Inserts or updates a vehicle record using the same normalized payload.
function vehiclePersistRecord(array $validated, string $action, array &$newUploads, array &$oldUploadsToDelete): void
{
    $pdo = fleetDb();
    vehicleEnsureImageColumn($pdo);
    $departmentId = vehicleFindOrCreateDepartmentId($pdo, $validated['department']);

    if ($action === 'update') {
        $vehicleId = filter_var((string) ($_POST['vehicle_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($vehicleId === false) {
            throw new RuntimeException('The selected vehicle could not be identified for editing.');
        }

        $existingRecord = vehicleFetchExistingRecord($pdo, (int) $vehicleId);
        $validated['vehicle_image'] = vehicleStoreOptionalUpload(
            'vehicle_image',
            'Vehicle image',
            $existingRecord['vehicle_image'] ?: null,
            $newUploads,
            $oldUploadsToDelete
        );

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
                notes = :notes,
                vehicle_image = :vehicle_image
            WHERE id = :vehicle_id'
        );
        $statement->bindValue(':vehicle_id', $vehicleId, PDO::PARAM_INT);
    } else {
        $validated['vehicle_image'] = vehicleStoreOptionalUpload(
            'vehicle_image',
            'Vehicle image',
            null,
            $newUploads,
            $oldUploadsToDelete
        );

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
                notes,
                vehicle_image
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
                :notes,
                :vehicle_image
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
    $statement->bindValue(':vehicle_image', $validated['vehicle_image'], $validated['vehicle_image'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
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

        fleetFinishResponse(
            vehiclePageUrl(),
            [
                'success' => false,
                'message' => 'The selected vehicle could not be identified.',
                'reload' => false,
            ],
            422
        );
    }

    $responsePayload = [
        'success' => false,
        'message' => 'The vehicle could not be deleted.',
        'reload' => false,
    ];
    $responseStatus = 200;

    try {
        $pdo = fleetDb();
        vehicleEnsureImageColumn($pdo);
        $existingRecord = vehicleFetchExistingRecord($pdo, (int) $vehicleId);
        $statement = $pdo->prepare('DELETE FROM vehicles WHERE id = :id');
        $statement->execute(['id' => $vehicleId]);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('The selected vehicle no longer exists.');
        }

        vehicleDeleteStoredUpload($existingRecord['vehicle_image'] ?? null);
        fleetTrackActivity([
            'module_key' => 'vehicles',
            'action_key' => 'deleted',
            'action_label' => 'Deleted vehicle',
            'description' => 'Removed a vehicle from the fleet register.',
            'target_type' => 'vehicle',
            'target_id' => (int) $vehicleId,
            'target_label' => (string) ($existingRecord['registration_no'] ?? 'Vehicle record'),
        ], $pdo);

        vehicleSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Vehicle deleted successfully',
                'message' => 'The selected vehicle has been removed from the fleet register.',
            ],
        ]);
        $responsePayload = [
            'success' => true,
            'message' => 'The selected vehicle has been removed from the fleet register.',
            'reload' => true,
        ];
    } catch (RuntimeException $exception) {
        vehicleSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Vehicle was not deleted',
                'message' => $exception->getMessage(),
            ],
        ]);
        $responsePayload = [
            'success' => false,
            'message' => $exception->getMessage(),
            'reload' => false,
        ];
        $responseStatus = 422;
    } catch (Throwable $exception) {
        vehicleSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Vehicle was not deleted',
                'message' => 'A system error occurred while deleting the vehicle.',
            ],
        ]);
        $responsePayload = [
            'success' => false,
            'message' => 'A system error occurred while deleting the vehicle.',
            'reload' => false,
        ];
        $responseStatus = 500;
    }

    fleetFinishResponse(vehiclePageUrl(), $responsePayload, $responseStatus);
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
    $newUploads = [];
    $oldUploadsToDelete = [];
    $responsePayload = [
        'success' => false,
        'message' => 'The vehicle could not be saved.',
        'reload' => false,
        'action' => $action,
    ];
    $responseStatus = 200;

    try {
        $validated = vehicleValidateFormData($formData);
        vehiclePersistRecord($validated, $action, $newUploads, $oldUploadsToDelete);
        $targetVehicleId = $action === 'update'
            ? (int) ($_POST['vehicle_id'] ?? 0)
            : (int) fleetDb()->lastInsertId();
        fleetTrackActivity([
            'module_key' => 'vehicles',
            'action_key' => $action === 'update' ? 'updated' : 'created',
            'action_label' => $action === 'update' ? 'Updated vehicle' : 'Created vehicle',
            'description' => $action === 'update'
                ? 'Updated vehicle information in the fleet register.'
                : 'Added a new vehicle to the fleet register.',
            'target_type' => 'vehicle',
            'target_id' => $targetVehicleId > 0 ? $targetVehicleId : null,
            'target_label' => $validated['registration_number'],
            'metadata' => [
                'status' => $validated['status'],
                'vehicle_type' => $validated['vehicle_type'],
            ],
        ]);

        foreach ($oldUploadsToDelete as $oldUpload) {
            vehicleDeleteStoredUpload($oldUpload);
        }

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
        $responsePayload = [
            'success' => true,
            'message' => sprintf(
                '%s has been %s in the fleet register.',
                $validated['registration_number'],
                $action === 'update' ? 'updated' : 'saved'
            ),
            'reload' => true,
            'action' => $action,
        ];
    } catch (RuntimeException $exception) {
        foreach ($newUploads as $newUpload) {
            vehicleDeleteStoredUpload($newUpload);
        }

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
        $responsePayload = [
            'success' => false,
            'message' => $exception->getMessage(),
            'reload' => false,
            'action' => $action,
        ];
        $responseStatus = 422;
    } catch (PDOException $exception) {
        foreach ($newUploads as $newUpload) {
            vehicleDeleteStoredUpload($newUpload);
        }

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
        $responsePayload = [
            'success' => false,
            'message' => $message,
            'reload' => false,
            'action' => $action,
        ];
        $responseStatus = 422;
    } catch (Throwable $exception) {
        foreach ($newUploads as $newUpload) {
            vehicleDeleteStoredUpload($newUpload);
        }

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
        $responsePayload = [
            'success' => false,
            'message' => $action === 'update'
                ? 'A system error occurred while updating the vehicle.'
                : 'A system error occurred while saving the vehicle.',
            'reload' => false,
            'action' => $action,
        ];
        $responseStatus = 500;
    }

    fleetFinishResponse(vehiclePageUrl(), $responsePayload, $responseStatus);
}

// Dispatches incoming vehicle POST requests by action type.
function vehicleHandleRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        fleetFinishResponse(
            vehiclePageUrl(),
            [
                'success' => false,
                'message' => 'Invalid request method.',
                'reload' => false,
            ],
            405
        );
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
