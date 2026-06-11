<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/ajax.php';
require_once __DIR__ . '/../includes/activity-tracker.php';

// Maintenance constants and page/session helpers
const MAINTENANCE_ALLOWED_TYPES = ['repair', 'routine_service', 'inspection', 'brake_service', 'other'];
const MAINTENANCE_ALLOWED_STATUSES = ['reported', 'in_progress', 'completed', 'cancelled'];

// Starts the session used for maintenance flash notifications if it is not already active.
function maintenanceStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

// Returns the maintenance page URL used after redirects.
function maintenancePageUrl(): string
{
    return '/fleet-system/modules/maintenance/';
}

// Returns the POST endpoint URL for maintenance form submissions.
function maintenanceHandlerUrl(): string
{
    return '/fleet-system/handlers/maintenance.php';
}

// Stores one-time maintenance feedback in session flash state.
function maintenanceSetFlash(array $payload): void
{
    maintenanceStartSession();
    $_SESSION['maintenance_flash'] = $payload;
}

// Pulls and clears one-time maintenance feedback from session flash state.
function maintenancePullFlash(): ?array
{
    maintenanceStartSession();

    if (!isset($_SESSION['maintenance_flash']) || !is_array($_SESSION['maintenance_flash'])) {
        return null;
    }

    $flash = $_SESSION['maintenance_flash'];
    unset($_SESSION['maintenance_flash']);

    return $flash;
}

// Converts database maintenance type values into display labels.
function maintenanceNormalizeType(string $type): string
{
    return match ($type) {
        'routine_service' => 'Routine Service',
        'brake_service' => 'Brake Service',
        default => ucwords(str_replace('_', ' ', $type)),
    };
}

// Converts database maintenance status values into display labels.
function maintenanceNormalizeStatus(string $status): string
{
    return match ($status) {
        'in_progress' => 'In Progress',
        default => ucwords(str_replace('_', ' ', $status)),
    };
}

// Formats numeric maintenance totals for table display.
function maintenanceFormatMoney(float $amount): string
{
    return number_format($amount, 0);
}

// Formats stored maintenance dates for the table view.
function maintenanceFormatDate(?string $date): string
{
    if ($date === null || $date === '') {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y', $timestamp) : $date;
}

// Loads current vehicle options for the maintenance modal dropdown.
function maintenanceFetchVehicleOptions(PDO $pdo): array
{
    $statement = $pdo->query(
        "SELECT id, registration_no
        FROM vehicles
        WHERE status <> 'disposed'
        ORDER BY registration_no ASC"
    );

    return $statement->fetchAll();
}

// Loads current service provider options for the maintenance modal dropdown.
function maintenanceFetchProviderOptions(PDO $pdo): array
{
    $statement = $pdo->query(
        "SELECT id, name
        FROM service_providers
        WHERE status <> 'inactive'
        ORDER BY name ASC"
    );

    return $statement->fetchAll();
}

// Loads maintenance rows, totals, dropdown options, and flash state for the page.
function maintenanceFetchPageData(): array
{
    $flash = maintenancePullFlash();
    $notification = $flash['notification'] ?? null;
    $formData = $flash['form_data'] ?? [];
    $openModal = (bool) ($flash['open_modal'] ?? false);
    $formMode = $flash['form_mode'] ?? 'create';

    $records = [];
    $vehicleOptions = [];
    $providerOptions = [];
    $totalCost = 0.0;

    try {
        $pdo = fleetDb();
        $vehicleOptions = maintenanceFetchVehicleOptions($pdo);
        $providerOptions = maintenanceFetchProviderOptions($pdo);
        $statement = $pdo->query(
            'SELECT
                mr.id,
                mr.vehicle_id,
                mr.service_provider_id,
                mr.maintenance_type,
                mr.date_reported,
                mr.date_completed,
                mr.description,
                mr.parts_replaced,
                mr.total_cost,
                mr.mileage_at_service,
                mr.invoice_number,
                mr.status,
                mr.remarks,
                v.registration_no,
                sp.name AS provider_name
            FROM maintenance_records mr
            INNER JOIN vehicles v ON v.id = mr.vehicle_id
            LEFT JOIN service_providers sp ON sp.id = mr.service_provider_id
            ORDER BY mr.date_reported DESC, mr.id DESC'
        );

        foreach ($statement->fetchAll() as $row) {
            $cost = (float) $row['total_cost'];
            $records[] = [
                'id' => (int) $row['id'],
                'vehicle_id' => (int) $row['vehicle_id'],
                'service_provider_id' => $row['service_provider_id'] !== null ? (int) $row['service_provider_id'] : null,
                'type_value' => $row['maintenance_type'],
                'status_value' => $row['status'],
                'date_raw' => $row['date_reported'],
                'date_completed_raw' => $row['date_completed'] ?: '',
                'description_raw' => $row['description'],
                'parts_replaced_raw' => $row['parts_replaced'] ?: '',
                'cost_raw' => (string) $cost,
                'mileage_raw' => $row['mileage_at_service'] !== null ? (string) $row['mileage_at_service'] : '',
                'invoice_raw' => $row['invoice_number'] ?: '',
                'remarks_raw' => $row['remarks'] ?: '',
                'date' => maintenanceFormatDate($row['date_reported']),
                'vehicle' => $row['registration_no'],
                'type' => maintenanceNormalizeType((string) $row['maintenance_type']),
                'description' => $row['description'],
                'provider' => $row['provider_name'] ?: '-',
                'cost' => maintenanceFormatMoney($cost),
                'status' => maintenanceNormalizeStatus((string) $row['status']),
            ];
            $totalCost += $cost;
        }
    } catch (Throwable $exception) {
        $notification = [
            'type' => 'error',
            'title' => 'Unable to load maintenance records',
            'message' => 'The maintenance records could not be loaded from the database right now.',
        ];
    }

    return [
        'records' => $records,
        'hasRecords' => count($records) > 0,
        'totalCost' => $totalCost,
        'maintenanceNotification' => $notification,
        'maintenanceFormData' => $formData,
        'shouldOpenMaintenanceModal' => $openModal,
        'maintenanceFormMode' => $formMode,
        'maintenanceFormAction' => maintenanceHandlerUrl(),
        'maintenanceVehicleOptions' => $vehicleOptions,
        'maintenanceProviderOptions' => $providerOptions,
    ];
}

// Collects and trims raw POST values from the maintenance form.
function maintenanceBuildFormDataFromPost(): array
{
    return [
        'record_id' => trim((string) ($_POST['record_id'] ?? '')),
        'vehicle' => trim((string) ($_POST['vehicle'] ?? '')),
        'maintenance_type' => strtolower(trim((string) ($_POST['maintenance_type'] ?? 'repair'))),
        'date_reported' => trim((string) ($_POST['date_reported'] ?? '')),
        'date_completed' => trim((string) ($_POST['date_completed'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
        'service_provider' => trim((string) ($_POST['service_provider'] ?? '')),
        'parts_replaced' => trim((string) ($_POST['parts_replaced'] ?? '')),
        'total_cost' => trim((string) ($_POST['total_cost'] ?? '')),
        'mileage_at_service' => trim((string) ($_POST['mileage_at_service'] ?? '')),
        'invoice_number' => trim((string) ($_POST['invoice_number'] ?? '')),
        'status' => strtolower(trim((string) ($_POST['status'] ?? 'reported'))),
        'remarks' => trim((string) ($_POST['remarks'] ?? '')),
    ];
}

// Validates and normalizes submitted maintenance form values.
function maintenanceValidateFormData(array $formData): array
{
    if ($formData['vehicle'] === '' || $formData['date_reported'] === '' || $formData['description'] === '') {
        throw new RuntimeException('Vehicle, date reported, and description are required.');
    }

    $vehicleId = filter_var($formData['vehicle'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($vehicleId === false) {
        throw new RuntimeException('Please select a valid vehicle.');
    }

    if (!in_array($formData['maintenance_type'], MAINTENANCE_ALLOWED_TYPES, true)) {
        throw new RuntimeException('Please select a valid maintenance type.');
    }

    if (!in_array($formData['status'], MAINTENANCE_ALLOWED_STATUSES, true)) {
        throw new RuntimeException('Please select a valid maintenance status.');
    }

    $dateReported = DateTimeImmutable::createFromFormat('Y-m-d', $formData['date_reported']);
    $dateReportedErrors = DateTimeImmutable::getLastErrors();
    if (!$dateReported || ($dateReportedErrors['warning_count'] ?? 0) > 0 || ($dateReportedErrors['error_count'] ?? 0) > 0) {
        throw new RuntimeException('Please enter a valid reported date.');
    }

    $dateCompleted = null;
    if ($formData['date_completed'] !== '') {
        $dateCompletedObj = DateTimeImmutable::createFromFormat('Y-m-d', $formData['date_completed']);
        $dateCompletedErrors = DateTimeImmutable::getLastErrors();
        if (!$dateCompletedObj || ($dateCompletedErrors['warning_count'] ?? 0) > 0 || ($dateCompletedErrors['error_count'] ?? 0) > 0) {
            throw new RuntimeException('Please enter a valid completion date.');
        }

        $dateCompleted = $dateCompletedObj->format('Y-m-d');
        if ($dateCompleted < $dateReported->format('Y-m-d')) {
            throw new RuntimeException('Date completed cannot be earlier than date reported.');
        }
    }

    $providerId = null;
    if ($formData['service_provider'] !== '') {
        $providerId = filter_var($formData['service_provider'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($providerId === false) {
            throw new RuntimeException('Please select a valid service provider.');
        }
    }

    $totalCost = 0.0;
    if ($formData['total_cost'] !== '') {
        $totalCost = filter_var($formData['total_cost'], FILTER_VALIDATE_FLOAT);
        if ($totalCost === false || $totalCost < 0) {
            throw new RuntimeException('Please enter a valid total cost.');
        }
    }

    $mileageAtService = null;
    if ($formData['mileage_at_service'] !== '') {
        $mileageAtService = filter_var($formData['mileage_at_service'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($mileageAtService === false) {
            throw new RuntimeException('Please enter a valid mileage at service.');
        }
    }

    return [
        'vehicle_id' => (int) $vehicleId,
        'maintenance_type' => $formData['maintenance_type'],
        'date_reported' => $dateReported->format('Y-m-d'),
        'date_completed' => $dateCompleted,
        'description' => $formData['description'],
        'service_provider_id' => $providerId === null ? null : (int) $providerId,
        'parts_replaced' => $formData['parts_replaced'] === '' ? null : $formData['parts_replaced'],
        'total_cost' => (float) $totalCost,
        'mileage_at_service' => $mileageAtService === null ? null : (int) $mileageAtService,
        'invoice_number' => $formData['invoice_number'] === '' ? null : $formData['invoice_number'],
        'status' => $formData['status'],
        'remarks' => $formData['remarks'] === '' ? null : $formData['remarks'],
    ];
}

// Confirms referenced vehicles and providers still exist before saving.
function maintenanceAssertForeignKeysExist(PDO $pdo, int $vehicleId, ?int $serviceProviderId): void
{
    $vehicleExists = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE id = :id AND status <> 'disposed'");
    $vehicleExists->execute(['id' => $vehicleId]);
    if ((int) $vehicleExists->fetchColumn() === 0) {
        throw new RuntimeException('The selected vehicle no longer exists.');
    }

    if ($serviceProviderId !== null) {
        $providerExists = $pdo->prepare('SELECT COUNT(*) FROM service_providers WHERE id = :id');
        $providerExists->execute(['id' => $serviceProviderId]);
        if ((int) $providerExists->fetchColumn() === 0) {
            throw new RuntimeException('The selected service provider no longer exists.');
        }
    }
}

// Handles both create and update requests for maintenance records.
function maintenanceHandleCreateOrUpdate(string $action): void
{
    $formData = maintenanceBuildFormDataFromPost();
    $responsePayload = [
        'success' => false,
        'message' => 'The maintenance record could not be saved.',
        'reload' => false,
        'action' => $action,
    ];
    $responseStatus = 200;

    try {
        $validated = maintenanceValidateFormData($formData);
        $pdo = fleetDb();
        maintenanceAssertForeignKeysExist($pdo, $validated['vehicle_id'], $validated['service_provider_id']);

        if ($action === 'update') {
            $recordId = filter_var($formData['record_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($recordId === false) {
                throw new RuntimeException('The selected maintenance record could not be identified.');
            }

            $exists = $pdo->prepare('SELECT COUNT(*) FROM maintenance_records WHERE id = :id');
            $exists->execute(['id' => $recordId]);
            if ((int) $exists->fetchColumn() === 0) {
                throw new RuntimeException('The selected maintenance record no longer exists.');
            }

            // Updates keep the same record row while refreshing editable maintenance fields.
            $statement = $pdo->prepare(
                'UPDATE maintenance_records SET
                    vehicle_id = :vehicle_id,
                    service_provider_id = :service_provider_id,
                    maintenance_type = :maintenance_type,
                    date_reported = :date_reported,
                    date_completed = :date_completed,
                    description = :description,
                    parts_replaced = :parts_replaced,
                    total_cost = :total_cost,
                    mileage_at_service = :mileage_at_service,
                    invoice_number = :invoice_number,
                    status = :status,
                    remarks = :remarks
                WHERE id = :record_id'
            );
            $statement->bindValue(':record_id', (int) $recordId, PDO::PARAM_INT);
        } else {
            // New maintenance records are inserted directly with validated form values.
            $statement = $pdo->prepare(
                'INSERT INTO maintenance_records (
                    vehicle_id,
                    service_provider_id,
                    maintenance_type,
                    date_reported,
                    date_completed,
                    description,
                    parts_replaced,
                    total_cost,
                    mileage_at_service,
                    invoice_number,
                    status,
                    remarks
                ) VALUES (
                    :vehicle_id,
                    :service_provider_id,
                    :maintenance_type,
                    :date_reported,
                    :date_completed,
                    :description,
                    :parts_replaced,
                    :total_cost,
                    :mileage_at_service,
                    :invoice_number,
                    :status,
                    :remarks
                )'
            );
        }

        $statement->bindValue(':vehicle_id', $validated['vehicle_id'], PDO::PARAM_INT);
        $statement->bindValue(':service_provider_id', $validated['service_provider_id'], $validated['service_provider_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':maintenance_type', $validated['maintenance_type']);
        $statement->bindValue(':date_reported', $validated['date_reported']);
        $statement->bindValue(':date_completed', $validated['date_completed'], $validated['date_completed'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':description', $validated['description']);
        $statement->bindValue(':parts_replaced', $validated['parts_replaced'], $validated['parts_replaced'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':total_cost', $validated['total_cost'], PDO::PARAM_STR);
        $statement->bindValue(':mileage_at_service', $validated['mileage_at_service'], $validated['mileage_at_service'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':invoice_number', $validated['invoice_number'], $validated['invoice_number'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':status', $validated['status']);
        $statement->bindValue(':remarks', $validated['remarks'], $validated['remarks'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->execute();
        $targetRecordId = $action === 'update' ? (int) $recordId : (int) $pdo->lastInsertId();
        fleetTrackActivity([
            'module_key' => 'maintenance',
            'action_key' => $action === 'update' ? 'updated' : 'created',
            'action_label' => $action === 'update' ? 'Updated maintenance' : 'Created maintenance',
            'description' => $action === 'update'
                ? 'Updated a maintenance record.'
                : 'Created a maintenance record.',
            'target_type' => 'maintenance_record',
            'target_id' => $targetRecordId,
            'target_label' => $validated['description'],
            'metadata' => [
                'vehicle_id' => $validated['vehicle_id'],
                'status' => $validated['status'],
            ],
        ], $pdo);

        maintenanceSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => $action === 'update' ? 'Maintenance record updated successfully' : 'Maintenance record added successfully',
                'message' => $action === 'update'
                    ? 'The maintenance record has been updated successfully.'
                    : 'The maintenance record has been created successfully.',
            ],
        ]);
        $responsePayload = [
            'success' => true,
            'message' => $action === 'update'
                ? 'The maintenance record has been updated successfully.'
                : 'The maintenance record has been created successfully.',
            'reload' => true,
            'action' => $action,
        ];
    } catch (RuntimeException $exception) {
        maintenanceSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Maintenance record was not updated' : 'Maintenance record was not added',
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
    } catch (Throwable $exception) {
        maintenanceSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Maintenance record was not updated' : 'Maintenance record was not added',
                'message' => $action === 'update'
                    ? 'A system error occurred while updating the maintenance record.'
                    : 'A system error occurred while saving the maintenance record.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
        $responsePayload = [
            'success' => false,
            'message' => $action === 'update'
                ? 'A system error occurred while updating the maintenance record.'
                : 'A system error occurred while saving the maintenance record.',
            'reload' => false,
            'action' => $action,
        ];
        $responseStatus = 500;
    }

    fleetFinishResponse(maintenancePageUrl(), $responsePayload, $responseStatus);
}

// Handles delete requests for maintenance records.
function maintenanceHandleDelete(): void
{
    $recordId = filter_var((string) ($_POST['record_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($recordId === false) {
        maintenanceSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Maintenance record was not deleted',
                'message' => 'The selected maintenance record could not be identified.',
            ],
        ]);
        fleetFinishResponse(
            maintenancePageUrl(),
            [
                'success' => false,
                'message' => 'The selected maintenance record could not be identified.',
                'reload' => false,
            ],
            422
        );
    }

    $responsePayload = [
        'success' => false,
        'message' => 'The maintenance record could not be deleted.',
        'reload' => false,
    ];
    $responseStatus = 200;

    try {
        $pdo = fleetDb();
        $lookup = $pdo->prepare('SELECT description FROM maintenance_records WHERE id = :id');
        $lookup->execute(['id' => $recordId]);
        $existingRecord = $lookup->fetch() ?: null;
        $statement = $pdo->prepare('DELETE FROM maintenance_records WHERE id = :id');
        $statement->execute(['id' => $recordId]);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('The selected maintenance record no longer exists.');
        }

        fleetTrackActivity([
            'module_key' => 'maintenance',
            'action_key' => 'deleted',
            'action_label' => 'Deleted maintenance',
            'description' => 'Removed a maintenance record.',
            'target_type' => 'maintenance_record',
            'target_id' => (int) $recordId,
            'target_label' => (string) (($existingRecord['description'] ?? 'Maintenance record')),
        ], $pdo);

        maintenanceSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Maintenance record deleted successfully',
                'message' => 'The selected maintenance record has been removed.',
            ],
        ]);
        $responsePayload = [
            'success' => true,
            'message' => 'The selected maintenance record has been removed.',
            'reload' => true,
        ];
    } catch (RuntimeException $exception) {
        maintenanceSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Maintenance record was not deleted',
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
        maintenanceSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Maintenance record was not deleted',
                'message' => 'A system error occurred while deleting the maintenance record.',
            ],
        ]);
        $responsePayload = [
            'success' => false,
            'message' => 'A system error occurred while deleting the maintenance record.',
            'reload' => false,
        ];
        $responseStatus = 500;
    }

    fleetFinishResponse(maintenancePageUrl(), $responsePayload, $responseStatus);
}

// Dispatches incoming maintenance POST requests by action type.
function maintenanceHandleRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        fleetFinishResponse(
            maintenancePageUrl(),
            [
                'success' => false,
                'message' => 'Invalid request method.',
                'reload' => false,
            ],
            405
        );
    }

    $action = trim((string) ($_POST['maintenance_action'] ?? 'create'));

    if ($action === 'delete') {
        maintenanceHandleDelete();
    }

    if ($action === 'update') {
        maintenanceHandleCreateOrUpdate('update');
    }

    maintenanceHandleCreateOrUpdate('create');
}

if (basename(__FILE__) === basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    // This file can be included for page data or called directly as the POST endpoint.
    maintenanceHandleRequest();
}
