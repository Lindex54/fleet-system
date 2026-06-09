<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
fleetAuthRequireAdmin();

// Driver constants and page/session helpers
const DRIVER_ALLOWED_STATUSES = ['active', 'inactive', 'suspended'];
const DRIVER_ALLOWED_GENDERS = ['male', 'female', 'other'];
const DRIVER_UPLOAD_MAX_BYTES = 5242880;
const DRIVER_ALLOWED_UPLOAD_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf'];
const DRIVER_ALLOWED_UPLOAD_MIME_TYPES = ['image/jpeg', 'image/png', 'application/pdf'];
const DRIVER_CODE_PREFIX = 'BUESMIS';

// Returns the absolute uploads directory used for driver files.
function driverUploadDirectoryPath(): string
{
    return dirname(__DIR__) . '/uploads/drivers';
}

// Builds a public URL for a stored driver upload path.
function driverBuildUploadUrl(?string $storedPath): string
{
    if ($storedPath === null || trim($storedPath) === '') {
        return '';
    }

    return '/fleet-system/' . ltrim($storedPath, '/');
}

// Detects whether the stored upload path should be previewed as an image.
function driverUploadIsImage(?string $storedPath): bool
{
    $extension = strtolower(pathinfo((string) $storedPath, PATHINFO_EXTENSION));

    return in_array($extension, ['jpg', 'jpeg', 'png'], true);
}

// Returns a display label for an uploaded file.
function driverUploadDisplayName(?string $storedPath): string
{
    if ($storedPath === null || trim($storedPath) === '') {
        return 'No file uploaded';
    }

    return basename($storedPath);
}

// Removes a stored driver upload when it has been replaced.
function driverDeleteStoredUpload(?string $storedPath): void
{
    if ($storedPath === null || trim($storedPath) === '') {
        return;
    }

    $normalizedPath = str_replace('\\', '/', $storedPath);
    if (!str_starts_with($normalizedPath, 'uploads/drivers/')) {
        return;
    }

    $absolutePath = dirname(__DIR__) . '/' . $normalizedPath;
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

// Ensures the uploads directory exists before files are moved into it.
function driverEnsureUploadDirectoryExists(): void
{
    $directory = driverUploadDirectoryPath();

    if (is_dir($directory)) {
        @chmod($directory, 0777);
        return;
    }

    if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('The uploads directory could not be prepared for driver files.');
    }

    @chmod($directory, 0777);
}

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
    return '/fleet-system/modules/drivers/';
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

function driverBuildLicenseDaysLeftLabel(?string $expiryDate): string
{
    if ($expiryDate === null || trim($expiryDate) === '') {
        return 'Not set';
    }

    $expiry = DateTimeImmutable::createFromFormat('Y-m-d', $expiryDate);
    if (!$expiry) {
        return 'Not set';
    }

    $today = new DateTimeImmutable(date('Y-m-d'));
    $daysLeft = (int) $today->diff($expiry)->format('%r%a');

    if ($daysLeft < 0) {
        return 'Expired';
    }

    return $daysLeft . ' day' . ($daysLeft === 1 ? '' : 's') . ' left';
}

function driverBuildCodeNamePart(string $fullName): string
{
    $letters = (string) preg_replace('/[^A-Z]/', '', strtoupper($fullName));

    return substr($letters !== '' ? $letters : 'DRVR', 0, 4);
}

function driverGenerateCode(PDO $pdo, string $fullName): string
{
    $prefix = DRIVER_CODE_PREFIX . driverBuildCodeNamePart($fullName);
    $statement = $pdo->prepare(
        'SELECT driver_code
        FROM drivers
        WHERE driver_code LIKE :prefix
        FOR UPDATE'
    );
    $statement->execute(['prefix' => DRIVER_CODE_PREFIX . '%']);
    $nextNumber = 1;

    foreach ($statement->fetchAll() as $row) {
        $driverCode = (string) ($row['driver_code'] ?? '');
        if (preg_match('/(\d{3})$/', $driverCode, $matches)) {
            $nextNumber = max($nextNumber, ((int) $matches[1]) + 1);
        }
    }

    if ($nextNumber > 999) {
        throw new RuntimeException('Driver ID numbers have been exhausted.');
    }

    return $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
}

function driverGenerateOneTimePassword(): string
{
    return 'Drv-' . substr(bin2hex(random_bytes(8)), 0, 12);
}

function driverCreateCredentials(PDO $pdo, array $validated, ?int $departmentId): array
{
    $driverCode = driverGenerateCode($pdo, $validated['full_name']);
    $oneTimePassword = driverGenerateOneTimePassword();
    $email = strtolower($driverCode) . '@drivers.local';

    $statement = $pdo->prepare(
        'INSERT INTO users (
            department_id,
            username,
            name,
            email,
            password_hash,
            role,
            status,
            must_change_password
        ) VALUES (
            :department_id,
            :username,
            :name,
            :email,
            :password_hash,
            \'driver\',
            :status,
            1
        )'
    );
    $statement->bindValue(':department_id', $departmentId, $departmentId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $statement->bindValue(':username', $driverCode);
    $statement->bindValue(':name', $validated['full_name']);
    $statement->bindValue(':email', $email);
    $statement->bindValue(':password_hash', password_hash($oneTimePassword, PASSWORD_DEFAULT));
    $statement->bindValue(':status', $validated['status']);
    $statement->execute();

    return [
        'user_id' => (int) $pdo->lastInsertId(),
        'driver_code' => $driverCode,
        'username' => $driverCode,
        'one_time_password' => $oneTimePassword,
    ];
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

function driverEnsureSecondaryVehicleTable(PDO $pdo): void
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
    $credentials = $flash['credentials'] ?? null;

    $drivers = [];
    $vehicleOptions = [];

    try {
        $pdo = fleetDb();
        driverEnsureSecondaryVehicleTable($pdo);
        $vehicleOptions = driverFetchVehicleOptions($pdo);
        $statement = $pdo->query(
            'SELECT
                d.id,
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
                COALESCE(dep.name, \'-\') AS department_name,
                v.id AS assigned_vehicle_id,
                v.registration_no AS assigned_vehicle_reg,
                (
                    SELECT GROUP_CONCAT(dsv.vehicle_id ORDER BY sv.registration_no ASC SEPARATOR \',\')
                    FROM driver_secondary_vehicles dsv
                    INNER JOIN vehicles sv ON sv.id = dsv.vehicle_id
                    WHERE dsv.driver_id = d.id
                ) AS other_vehicle_ids,
                (
                    SELECT GROUP_CONCAT(sv.registration_no ORDER BY sv.registration_no ASC SEPARATOR \', \')
                    FROM driver_secondary_vehicles dsv
                    INNER JOIN vehicles sv ON sv.id = dsv.vehicle_id
                    WHERE dsv.driver_id = d.id
                ) AS other_vehicle_regs
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
                'driver_code' => $row['driver_code'] ?: '',
                'name' => $row['full_name'],
                'employee_id' => $row['employee_id'] ?: '',
                'email' => $row['email'] ?: '-',
                'phone' => $row['phone'] ?: '-',
                'gender' => $row['gender'] ?: '',
                'national_id_number' => $row['national_id_number'] ?: '',
                'license' => $row['license_number'],
                'license_classes' => $row['license_classes'] ?: '',
                'license_issue_date' => $row['license_issue_date'] ?: '',
                'license_issuing_authority' => $row['license_issuing_authority'] ?: '',
                'license_expiry' => $row['license_expiry'] ?: '',
                'license_days_left' => driverBuildLicenseDaysLeftLabel($row['license_expiry'] ?? null),
                'driver_photo' => $row['driver_photo'] ?: '',
                'driver_photo_url' => driverBuildUploadUrl($row['driver_photo'] ?? ''),
                'driver_photo_name' => driverUploadDisplayName($row['driver_photo'] ?? ''),
                'driver_photo_is_image' => driverUploadIsImage($row['driver_photo'] ?? ''),
                'national_id_photo' => $row['national_id_photo'] ?: '',
                'national_id_photo_url' => driverBuildUploadUrl($row['national_id_photo'] ?? ''),
                'national_id_photo_name' => driverUploadDisplayName($row['national_id_photo'] ?? ''),
                'national_id_photo_is_image' => driverUploadIsImage($row['national_id_photo'] ?? ''),
                'driving_license_scan' => $row['driving_license_scan'] ?: '',
                'driving_license_scan_url' => driverBuildUploadUrl($row['driving_license_scan'] ?? ''),
                'driving_license_scan_name' => driverUploadDisplayName($row['driving_license_scan'] ?? ''),
                'driving_license_scan_is_image' => driverUploadIsImage($row['driving_license_scan'] ?? ''),
                'department' => $row['department_name'] === '-' ? '' : $row['department_name'],
                'assigned' => $row['assigned_vehicle_reg'] ?: '-',
                'assigned_vehicle_id' => $row['assigned_vehicle_id'] !== null ? (int) $row['assigned_vehicle_id'] : null,
                'other_vehicle_ids' => $row['other_vehicle_ids'] !== null && $row['other_vehicle_ids'] !== ''
                    ? array_map('intval', explode(',', (string) $row['other_vehicle_ids']))
                    : [],
                'other_vehicles' => $row['other_vehicle_regs'] ?: '-',
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
        'driverCredentials' => is_array($credentials) ? $credentials : null,
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
        'gender' => strtolower(trim((string) ($_POST['gender'] ?? ''))),
        'national_id_number' => trim((string) ($_POST['national_id_number'] ?? '')),
        'license_number' => trim((string) ($_POST['license_number'] ?? '')),
        'license_classes' => trim((string) ($_POST['license_classes'] ?? '')),
        'license_issue_date' => trim((string) ($_POST['license_issue_date'] ?? '')),
        'license_issuing_authority' => trim((string) ($_POST['license_issuing_authority'] ?? '')),
        'license_expiry' => trim((string) ($_POST['license_expiry'] ?? '')),
        'department' => trim((string) ($_POST['department'] ?? '')),
        'assigned_vehicle' => trim((string) ($_POST['assigned_vehicle'] ?? '')),
        'other_vehicles' => array_map(
            static fn ($value): string => trim((string) $value),
            is_array($_POST['other_vehicles'] ?? null) ? $_POST['other_vehicles'] : []
        ),
        'status' => strtolower(trim((string) ($_POST['status'] ?? 'active'))),
        'driver_photo' => trim((string) ($_POST['existing_driver_photo'] ?? '')),
        'national_id_photo' => trim((string) ($_POST['existing_national_id_photo'] ?? '')),
        'driving_license_scan' => trim((string) ($_POST['existing_driving_license_scan'] ?? '')),
    ];
}

function driverAssertRequiredCreateFields(array $formData): void
{
    $requiredFields = [
        'full_name' => 'Full name',
        'phone' => 'Phone',
        'email' => 'Email',
        'gender' => 'Gender',
        'national_id_number' => 'National ID Number / NIN',
        'license_number' => 'License number',
        'license_classes' => 'License class(es)',
        'license_issue_date' => 'License issue date',
        'license_issuing_authority' => 'License issuing authority',
        'license_expiry' => 'License expiry',
        'department' => 'Department',
    ];

    foreach ($requiredFields as $field => $label) {
        if (($formData[$field] ?? '') === '') {
            throw new RuntimeException($label . ' is required before a new driver can be added.');
        }
    }

    if (($formData['assigned_vehicle'] ?? '') === '' || $formData['assigned_vehicle'] === 'unassigned') {
        throw new RuntimeException('Assigned vehicle is required before a new driver can be added.');
    }
}

function driverAssertRequiredCreateUploads(): void
{
    $requiredUploads = [
        'driver_photo' => 'Driver photo',
        'national_id_photo' => 'National ID photo',
        'driving_license_scan' => 'Driving license scan',
    ];

    foreach ($requiredUploads as $fieldName => $label) {
        $upload = $_FILES[$fieldName] ?? null;
        if (!is_array($upload) || (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException($label . ' is required before a new driver can be added.');
        }
    }
}

// Validates and normalizes submitted driver form values.
function driverValidateFormData(array $formData, string $action = 'create'): array
{
    if ($action === 'create') {
        driverAssertRequiredCreateFields($formData);
        driverAssertRequiredCreateUploads();
    }

    // Shared validation keeps create and update behavior consistent.
    if ($formData['full_name'] === '' || $formData['license_number'] === '') {
        throw new RuntimeException('Full name and license number are required.');
    }

    if ($formData['email'] !== '' && filter_var($formData['email'], FILTER_VALIDATE_EMAIL) === false) {
        throw new RuntimeException('Please enter a valid email address.');
    }

    if ($formData['gender'] !== '' && !in_array($formData['gender'], DRIVER_ALLOWED_GENDERS, true)) {
        throw new RuntimeException('Please select a valid gender.');
    }

    if (!in_array($formData['status'], DRIVER_ALLOWED_STATUSES, true)) {
        throw new RuntimeException('Please select a valid driver status.');
    }

    $licenseIssueDate = null;
    if ($formData['license_issue_date'] !== '') {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $formData['license_issue_date']);
        $errors = DateTimeImmutable::getLastErrors();

        if (!$date || ($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
            throw new RuntimeException('Please enter a valid license issue date.');
        }

        $licenseIssueDate = $date->format('Y-m-d');
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

    $otherVehicleIds = [];
    foreach ($formData['other_vehicles'] as $vehicleIdValue) {
        if ($vehicleIdValue === '') {
            continue;
        }

        $otherVehicleId = filter_var($vehicleIdValue, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($otherVehicleId === false) {
            throw new RuntimeException('Please choose only valid other vehicles.');
        }

        $otherVehicleIds[] = (int) $otherVehicleId;
    }

    $otherVehicleIds = array_values(array_unique($otherVehicleIds));

    if ($assignedVehicleId !== null) {
        $otherVehicleIds = array_values(array_filter(
            $otherVehicleIds,
            static fn (int $vehicleId): bool => $vehicleId !== (int) $assignedVehicleId
        ));
    }

    return [
        'full_name' => $formData['full_name'],
        'employee_id' => $formData['employee_id'] === '' ? null : $formData['employee_id'],
        'phone' => $formData['phone'] === '' ? null : $formData['phone'],
        'email' => $formData['email'] === '' ? null : $formData['email'],
        'gender' => $formData['gender'] === '' ? null : $formData['gender'],
        'national_id_number' => $formData['national_id_number'] === '' ? null : $formData['national_id_number'],
        'license_number' => $formData['license_number'],
        'license_classes' => $formData['license_classes'] === '' ? null : $formData['license_classes'],
        'license_issue_date' => $licenseIssueDate,
        'license_issuing_authority' => $formData['license_issuing_authority'] === '' ? null : $formData['license_issuing_authority'],
        'license_expiry' => $licenseExpiry,
        'department' => $formData['department'],
        'assigned_vehicle_id' => $assignedVehicleId === null ? null : (int) $assignedVehicleId,
        'other_vehicle_ids' => $otherVehicleIds,
        'status' => $formData['status'],
        'driver_photo' => $formData['driver_photo'] === '' ? null : $formData['driver_photo'],
        'national_id_photo' => $formData['national_id_photo'] === '' ? null : $formData['national_id_photo'],
        'driving_license_scan' => $formData['driving_license_scan'] === '' ? null : $formData['driving_license_scan'],
    ];
}

// Loads the current driver row when an update needs existing file values.
function driverFetchExistingRecord(PDO $pdo, int $driverId): array
{
    $statement = $pdo->prepare(
        'SELECT id, user_id, driver_photo, national_id_photo, driving_license_scan
        FROM drivers
        WHERE id = :id
        LIMIT 1'
    );
    $statement->execute(['id' => $driverId]);
    $driver = $statement->fetch();

    if (!$driver) {
        throw new RuntimeException('The selected driver no longer exists.');
    }

    return $driver;
}

// Validates and stores one optional driver upload, returning the final stored path.
function driverStoreOptionalUpload(string $fieldName, string $label, ?string $existingPath, array &$newUploads, array &$oldUploadsToDelete): ?string
{
    $upload = $_FILES[$fieldName] ?? null;
    if (!is_array($upload) || (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $existingPath;
    }

    $errorCode = (int) ($upload['error'] ?? UPLOAD_ERR_OK);
    if ($errorCode !== UPLOAD_ERR_OK) {
        throw new RuntimeException($label . ' could not be uploaded right now.');
    }

    $size = (int) ($upload['size'] ?? 0);
    if ($size > DRIVER_UPLOAD_MAX_BYTES) {
        throw new RuntimeException($label . ' must be 5MB or smaller.');
    }

    $originalName = (string) ($upload['name'] ?? '');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($extension, DRIVER_ALLOWED_UPLOAD_EXTENSIONS, true)) {
        throw new RuntimeException($label . ' must be a JPG, JPEG, PNG, or PDF file.');
    }

    $temporaryPath = (string) ($upload['tmp_name'] ?? '');
    $mimeType = '';
    if ($temporaryPath !== '' && is_uploaded_file($temporaryPath)) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = (string) $finfo->file($temporaryPath);
    }

    if ($mimeType === '' || !in_array($mimeType, DRIVER_ALLOWED_UPLOAD_MIME_TYPES, true)) {
        throw new RuntimeException($label . ' must be a JPG, PNG, or PDF file.');
    }

    driverEnsureUploadDirectoryExists();

    $storedFileName = sprintf(
        '%s-%s-%s.%s',
        $fieldName,
        date('YmdHis'),
        bin2hex(random_bytes(6)),
        $extension
    );
    $relativePath = 'uploads/drivers/' . $storedFileName;
    $absolutePath = driverUploadDirectoryPath() . '/' . $storedFileName;

    if (!move_uploaded_file($temporaryPath, $absolutePath)) {
        throw new RuntimeException($label . ' could not be saved.');
    }

    $newUploads[] = $relativePath;
    if ($existingPath !== null && trim($existingPath) !== '') {
        $oldUploadsToDelete[] = $existingPath;
    }

    return $relativePath;
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

function driverAssertVehicleCanBeUsedAsSecondary(PDO $pdo, int $vehicleId): void
{
    $statement = $pdo->prepare(
        'SELECT id
        FROM vehicles
        WHERE id = :id
            AND status <> \'disposed\'
        LIMIT 1'
    );
    $statement->execute(['id' => $vehicleId]);

    if (!$statement->fetchColumn()) {
        throw new RuntimeException('One of the selected other vehicles is not available.');
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

function driverSyncSecondaryVehicles(PDO $pdo, int $driverId, array $vehicleIds, ?int $assignedVehicleId): void
{
    $normalizedVehicleIds = array_values(array_unique(array_map('intval', $vehicleIds)));
    if ($assignedVehicleId !== null) {
        $normalizedVehicleIds = array_values(array_filter(
            $normalizedVehicleIds,
            static fn (int $vehicleId): bool => $vehicleId !== $assignedVehicleId
        ));
    }

    foreach ($normalizedVehicleIds as $vehicleId) {
        driverAssertVehicleCanBeUsedAsSecondary($pdo, $vehicleId);
    }

    $delete = $pdo->prepare('DELETE FROM driver_secondary_vehicles WHERE driver_id = :driver_id');
    $delete->execute(['driver_id' => $driverId]);

    if ($normalizedVehicleIds === []) {
        return;
    }

    $insert = $pdo->prepare(
        'INSERT INTO driver_secondary_vehicles (driver_id, vehicle_id)
        VALUES (:driver_id, :vehicle_id)'
    );

    foreach ($normalizedVehicleIds as $vehicleId) {
        $insert->execute([
            'driver_id' => $driverId,
            'vehicle_id' => $vehicleId,
        ]);
    }
}

// POST handler for create/update actions
// Handles both create and update requests for driver records.
function driverHandleCreateOrUpdate(string $action): void
{
    $formData = driverBuildFormDataFromPost();
    $newUploads = [];
    $oldUploadsToDelete = [];

    try {
        $validated = driverValidateFormData($formData, $action);
        $pdo = fleetDb();
        driverEnsureSecondaryVehicleTable($pdo);
        $pdo->beginTransaction();
        $departmentId = driverFindOrCreateDepartmentId($pdo, $validated['department']);
        $existingRecord = null;
        $driverId = null;

        if ($action === 'update') {
            $driverId = filter_var($formData['driver_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($driverId === false) {
                throw new RuntimeException('The selected driver could not be identified.');
            }

            $existingRecord = driverFetchExistingRecord($pdo, (int) $driverId);

            $validated['driver_photo'] = driverStoreOptionalUpload(
                'driver_photo',
                'Driver photo',
                $existingRecord['driver_photo'] ?: null,
                $newUploads,
                $oldUploadsToDelete
            );
            $validated['national_id_photo'] = driverStoreOptionalUpload(
                'national_id_photo',
                'National ID photo',
                $existingRecord['national_id_photo'] ?: null,
                $newUploads,
                $oldUploadsToDelete
            );
            $validated['driving_license_scan'] = driverStoreOptionalUpload(
                'driving_license_scan',
                'Driving license scan',
                $existingRecord['driving_license_scan'] ?: null,
                $newUploads,
                $oldUploadsToDelete
            );

            // Updates keep the same driver row while refreshing editable profile fields.
            $statement = $pdo->prepare(
                'UPDATE drivers SET
                    department_id = :department_id,
                    full_name = :full_name,
                    employee_id = :employee_id,
                    phone = :phone,
                    email = :email,
                    gender = :gender,
                    national_id_number = :national_id_number,
                    license_number = :license_number,
                    license_classes = :license_classes,
                    license_issue_date = :license_issue_date,
                    license_issuing_authority = :license_issuing_authority,
                    license_expiry = :license_expiry,
                    driver_photo = :driver_photo,
                    national_id_photo = :national_id_photo,
                    driving_license_scan = :driving_license_scan,
                    status = :status
                WHERE id = :driver_id'
            );
            $statement->bindValue(':driver_id', (int) $driverId, PDO::PARAM_INT);
        } else {
            $credentials = driverCreateCredentials($pdo, $validated, $departmentId);
            $validated['driver_photo'] = driverStoreOptionalUpload(
                'driver_photo',
                'Driver photo',
                null,
                $newUploads,
                $oldUploadsToDelete
            );
            $validated['national_id_photo'] = driverStoreOptionalUpload(
                'national_id_photo',
                'National ID photo',
                null,
                $newUploads,
                $oldUploadsToDelete
            );
            $validated['driving_license_scan'] = driverStoreOptionalUpload(
                'driving_license_scan',
                'Driving license scan',
                null,
                $newUploads,
                $oldUploadsToDelete
            );

            // New drivers are inserted first, then any selected vehicle is assigned afterwards.
            $statement = $pdo->prepare(
                'INSERT INTO drivers (
                    user_id,
                    driver_code,
                    department_id,
                    full_name,
                    employee_id,
                    phone,
                    email,
                    gender,
                    national_id_number,
                    license_number,
                    license_classes,
                    license_issue_date,
                    license_issuing_authority,
                    license_expiry,
                    driver_photo,
                    national_id_photo,
                    driving_license_scan,
                    status
                ) VALUES (
                    :user_id,
                    :driver_code,
                    :department_id,
                    :full_name,
                    :employee_id,
                    :phone,
                    :email,
                    :gender,
                    :national_id_number,
                    :license_number,
                    :license_classes,
                    :license_issue_date,
                    :license_issuing_authority,
                    :license_expiry,
                    :driver_photo,
                    :national_id_photo,
                    :driving_license_scan,
                    :status
                )'
            );
            $statement->bindValue(':user_id', $credentials['user_id'], PDO::PARAM_INT);
            $statement->bindValue(':driver_code', $credentials['driver_code']);
        }

        $statement->bindValue(':department_id', $departmentId, $departmentId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':full_name', $validated['full_name']);
        $statement->bindValue(':employee_id', $validated['employee_id'], $validated['employee_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':phone', $validated['phone'], $validated['phone'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':email', $validated['email'], $validated['email'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':gender', $validated['gender'], $validated['gender'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':national_id_number', $validated['national_id_number'], $validated['national_id_number'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':license_number', $validated['license_number']);
        $statement->bindValue(':license_classes', $validated['license_classes'], $validated['license_classes'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':license_issue_date', $validated['license_issue_date'], $validated['license_issue_date'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':license_issuing_authority', $validated['license_issuing_authority'], $validated['license_issuing_authority'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':license_expiry', $validated['license_expiry'], $validated['license_expiry'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':driver_photo', $validated['driver_photo'], $validated['driver_photo'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':national_id_photo', $validated['national_id_photo'], $validated['national_id_photo'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':driving_license_scan', $validated['driving_license_scan'], $validated['driving_license_scan'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':status', $validated['status']);
        $statement->execute();

        $driverId = $action === 'update' ? (int) $formData['driver_id'] : (int) $pdo->lastInsertId();
        driverSyncVehicleAssignment($pdo, $driverId, $validated['assigned_vehicle_id']);
        driverSyncSecondaryVehicles($pdo, $driverId, $validated['other_vehicle_ids'], $validated['assigned_vehicle_id']);
        $pdo->commit();

        foreach ($oldUploadsToDelete as $oldUpload) {
            driverDeleteStoredUpload($oldUpload);
        }

        driverSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => $action === 'update' ? 'Driver updated successfully' : 'Driver login created',
                'message' => $action === 'update'
                    ? 'The driver record has been updated successfully.'
                    : 'Username: ' . ($credentials['username'] ?? '') . ' | One-time password: ' . ($credentials['one_time_password'] ?? ''),
            ],
            'credentials' => $action === 'create' ? [
                'driver_code' => $credentials['driver_code'] ?? '',
                'username' => $credentials['username'] ?? '',
                'one_time_password' => $credentials['one_time_password'] ?? '',
            ] : null,
        ]);
    } catch (RuntimeException $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        foreach ($newUploads as $newUpload) {
            driverDeleteStoredUpload($newUpload);
        }

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
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        foreach ($newUploads as $newUpload) {
            driverDeleteStoredUpload($newUpload);
        }

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
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        foreach ($newUploads as $newUpload) {
            driverDeleteStoredUpload($newUpload);
        }

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
        $pdo = fleetDb();
        $existingRecord = driverFetchExistingRecord($pdo, (int) $driverId);

        $statement = $pdo->prepare('DELETE FROM drivers WHERE id = :id');
        $statement->execute(['id' => $driverId]);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('The selected driver no longer exists.');
        }

        if (!empty($existingRecord['user_id'])) {
            $deleteUser = $pdo->prepare('DELETE FROM users WHERE id = :id AND role = \'driver\'');
            $deleteUser->execute(['id' => (int) $existingRecord['user_id']]);
        }

        driverDeleteStoredUpload($existingRecord['driver_photo'] ?? null);
        driverDeleteStoredUpload($existingRecord['national_id_photo'] ?? null);
        driverDeleteStoredUpload($existingRecord['driving_license_scan'] ?? null);

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
