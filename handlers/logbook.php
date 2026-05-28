<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

// Logbook page/session helpers
// Starts the session used for logbook flash notifications if it is not already active.
function logbookStartSession(): void
{
    // Flash messages for the logbook form are stored in the session across redirects.
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

// Returns the logbook page URL used after redirects.
function logbookPageUrl(): string
{
    return '/fleet-system/modules/logbook/index.php';
}

// Returns the POST endpoint URL for logbook form submissions.
function logbookHandlerUrl(): string
{
    return '/fleet-system/handlers/logbook.php';
}

// Stores one-time logbook feedback in session flash state.
function logbookSetFlash(array $payload): void
{
    // Save one-time feedback that the page can render after POST redirects.
    logbookStartSession();
    $_SESSION['logbook_flash'] = $payload;
}

// Pulls and clears one-time logbook feedback from session flash state.
function logbookPullFlash(): ?array
{
    logbookStartSession();

    if (!isset($_SESSION['logbook_flash']) || !is_array($_SESSION['logbook_flash'])) {
        return null;
    }

    // Consume the flash immediately so the same notice does not repeat on refresh.
    $flash = $_SESSION['logbook_flash'];
    unset($_SESSION['logbook_flash']);

    return $flash;
}

// Display formatting helpers used by the table view
// Formats numeric fuel cost values for table display.
function logbookFormatMoney(?float $amount): string
{
    if ($amount === null) {
        return '-';
    }

    return 'UGX ' . number_format($amount, 0);
}

// Formats stored trip dates for the table view.
function logbookFormatDate(?string $date): string
{
    if ($date === null || $date === '') {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y', $timestamp) : $date;
}

// Dropdown option loaders for the logbook modal
// Loads current vehicle options for the logbook modal dropdown.
function logbookFetchVehicleOptions(PDO $pdo): array
{
    // The modal uses live vehicle records instead of hard-coded registration numbers.
    $statement = $pdo->query('SELECT id, registration_no FROM vehicles ORDER BY registration_no ASC');

    return $statement->fetchAll();
}

// Loads current driver options for the logbook modal dropdown.
function logbookFetchDriverOptions(PDO $pdo): array
{
    // Drivers are optional in the schema, so the form can still save unassigned trips.
    $statement = $pdo->query("SELECT id, full_name FROM drivers WHERE status = 'active' ORDER BY full_name ASC");

    return $statement->fetchAll();
}

// Page data loader for the logbook table, totals, and modal
// Loads logbook rows, totals, dropdown options, and flash state for the page.
function logbookFetchPageData(): array
{
    // The page needs current logs, select options, totals, and any flash state from the last POST.
    $flash = logbookPullFlash();
    $notification = $flash['notification'] ?? null;
    $formData = $flash['form_data'] ?? [];
    $openModal = (bool) ($flash['open_modal'] ?? false);
    $formMode = $flash['form_mode'] ?? 'create';

    $logs = [];
    $vehicleOptions = [];
    $driverOptions = [];
    $totalKm = 0;
    $totalFuel = 0.0;
    $totalCostAmount = 0.0;

    try {
        $pdo = fleetDb();
        $vehicleOptions = logbookFetchVehicleOptions($pdo);
        $driverOptions = logbookFetchDriverOptions($pdo);

        $statement = $pdo->query(
            'SELECT
                vl.id,
                vl.trip_date,
                vl.vehicle_id,
                vl.driver_id,
                v.registration_no,
                COALESCE(d.full_name, \'Unassigned\') AS driver_name,
                vl.departure_location,
                vl.destination,
                vl.purpose,
                vl.odometer_start,
                vl.odometer_end,
                vl.distance_km,
                vl.fuel_litres,
                vl.fuel_cost,
                vl.remarks
            FROM vehicle_logs vl
            INNER JOIN vehicles v ON v.id = vl.vehicle_id
            LEFT JOIN drivers d ON d.id = vl.driver_id
            ORDER BY vl.trip_date DESC, vl.id DESC'
        );

        foreach ($statement->fetchAll() as $row) {
            // Convert raw DB rows into the shape already expected by the table markup.
            $logs[] = [
                'id' => $row['id'] ?? null,
                'vehicle_id' => $row['vehicle_id'] ?? null,
                'driver_id' => $row['driver_id'] ?? null,
                'date' => logbookFormatDate($row['trip_date']),
                'vehicle' => $row['registration_no'],
                'driver' => $row['driver_name'],
                'from' => $row['departure_location'],
                'to' => $row['destination'],
                'purpose' => $row['purpose'],
                'odo_start' => $row['odometer_start'] ?? '-',
                'odo_end' => $row['odometer_end'] ?? '-',
                'km' => $row['distance_km'] ?? '-',
                'fuel' => $row['fuel_litres'] ?? '-',
                'cost' => logbookFormatMoney($row['fuel_cost'] !== null ? (float) $row['fuel_cost'] : null),
                'remarks' => $row['remarks'] ?: '-',
            ];

            $totalKm += (int) ($row['distance_km'] ?? 0);
            $totalFuel += (float) ($row['fuel_litres'] ?? 0);
            $totalCostAmount += (float) ($row['fuel_cost'] ?? 0);
        }
    } catch (Throwable $exception) {
        $notification = [
            'type' => 'error',
            'title' => 'Unable to load logbook',
            'message' => 'The logbook records could not be loaded from the database right now.',
        ];
    }

    return [
        'logs' => $logs,
        'hasLogs' => count($logs) > 0,
        'totalKm' => $totalKm,
        'totalFuel' => rtrim(rtrim(number_format($totalFuel, 2, '.', ''), '0'), '.'),
        'totalCost' => logbookFormatMoney($totalCostAmount),
        'logbookNotification' => $notification,
        'logbookFormData' => $formData,
        'shouldOpenLogbookModal' => $openModal,
        'logbookFormMode' => $formMode,
        'logbookFormAction' => logbookHandlerUrl(),
        'logbookVehicleOptions' => $vehicleOptions,
        'logbookDriverOptions' => $driverOptions,
    ];
}

// Shared validation and integrity helpers for create/update
// Validates and normalizes submitted logbook form values.
function logbookValidateInput(array $formData): array
{
    // Shared validation keeps create and update behavior consistent.
    $tripDate = $formData['date'];
    $vehicleId = $formData['vehicle'];
    $driverId = $formData['driver'];
    $departureLocation = $formData['departure_location'];
    $destination = $formData['destination'];
    $purpose = $formData['purpose'];
    $odometerStart = $formData['odometer_start'];
    $odometerEnd = $formData['odometer_end'];
    $fuelLitres = $formData['fuel_litres'];
    $fuelCost = $formData['fuel_cost'];

    if ($tripDate === '' || $vehicleId === '' || $departureLocation === '' || $destination === '' || $purpose === '') {
        throw new RuntimeException('Date, vehicle, departure location, destination, and purpose are required.');
    }

    $vehicleIdValue = filter_var($vehicleId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $driverIdValue = $driverId === 'unassigned' || $driverId === ''
        ? null
        : filter_var($driverId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $odometerStartValue = $odometerStart === ''
        ? null
        : filter_var($odometerStart, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $odometerEndValue = $odometerEnd === ''
        ? null
        : filter_var($odometerEnd, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $fuelLitresValue = $fuelLitres === '' ? null : filter_var($fuelLitres, FILTER_VALIDATE_FLOAT);
    $fuelCostValue = $fuelCost === '' ? null : filter_var($fuelCost, FILTER_VALIDATE_FLOAT);

    if ($vehicleIdValue === false || $driverIdValue === false || $odometerStartValue === false || $odometerEndValue === false || $fuelLitresValue === false || $fuelCostValue === false) {
        throw new RuntimeException('Please review the numeric fields and enter valid values.');
    }

    if ($odometerStartValue !== null && $odometerEndValue !== null && $odometerEndValue < $odometerStartValue) {
        throw new RuntimeException('Odometer end cannot be less than odometer start.');
    }

    return [
        'trip_date' => $tripDate,
        'vehicle_id' => (int) $vehicleIdValue,
        'driver_id' => $driverIdValue === null ? null : (int) $driverIdValue,
        'departure_location' => $departureLocation,
        'destination' => $destination,
        'purpose' => $purpose,
        'odometer_start' => $odometerStartValue === null ? null : (int) $odometerStartValue,
        'odometer_end' => $odometerEndValue === null ? null : (int) $odometerEndValue,
        'fuel_litres' => $fuelLitresValue === null ? null : (float) $fuelLitresValue,
        'fuel_cost' => $fuelCostValue === null ? null : (float) $fuelCostValue,
        'remarks' => $formData['remarks'] === '' ? null : $formData['remarks'],
    ];
}

// Confirms referenced vehicles and drivers still exist before saving.
function logbookAssertForeignKeysExist(PDO $pdo, int $vehicleId, ?int $driverId): void
{
    // Existence checks protect against forged POST values outside the select options.
    $vehicleExists = $pdo->prepare('SELECT COUNT(*) FROM vehicles WHERE id = :id');
    $vehicleExists->execute(['id' => $vehicleId]);

    if ((int) $vehicleExists->fetchColumn() === 0) {
        throw new RuntimeException('Selected vehicle does not exist.');
    }

    if ($driverId !== null) {
        $driverExists = $pdo->prepare('SELECT COUNT(*) FROM drivers WHERE id = :id');
        $driverExists->execute(['id' => $driverId]);

        if ((int) $driverExists->fetchColumn() === 0) {
            throw new RuntimeException('Selected driver does not exist.');
        }
    }
}

// Form normalization helper for POST data
// Collects and trims raw POST values from the logbook form.
function logbookBuildFormDataFromPost(): array
{
    // Normalize incoming form values before validation and database storage.
    return [
        'entry_id' => trim((string) ($_POST['entry_id'] ?? '')),
        'date' => trim((string) ($_POST['date'] ?? '')),
        'vehicle' => trim((string) ($_POST['vehicle'] ?? '')),
        'driver' => trim((string) ($_POST['driver'] ?? '')),
        'departure_location' => trim((string) ($_POST['departure_location'] ?? '')),
        'destination' => trim((string) ($_POST['destination'] ?? '')),
        'purpose' => trim((string) ($_POST['purpose'] ?? '')),
        'odometer_start' => trim((string) ($_POST['odometer_start'] ?? '')),
        'odometer_end' => trim((string) ($_POST['odometer_end'] ?? '')),
        'fuel_litres' => trim((string) ($_POST['fuel_litres'] ?? '')),
        'fuel_cost' => trim((string) ($_POST['fuel_cost'] ?? '')),
        'remarks' => trim((string) ($_POST['remarks'] ?? '')),
    ];
}

// POST handler for create/update actions
// Handles both create and update requests for logbook entries.
function logbookHandleCreateOrUpdate(string $action): void
{
    $formData = logbookBuildFormDataFromPost();

    try {
        $validated = logbookValidateInput($formData);
        $pdo = fleetDb();
        logbookAssertForeignKeysExist($pdo, $validated['vehicle_id'], $validated['driver_id']);

        if ($action === 'update') {
            $entryId = filter_var($formData['entry_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($entryId === false) {
                throw new RuntimeException('The selected log entry could not be identified.');
            }

            $entryExists = $pdo->prepare('SELECT COUNT(*) FROM vehicle_logs WHERE id = :id');
            $entryExists->execute(['id' => $entryId]);

            if ((int) $entryExists->fetchColumn() === 0) {
                throw new RuntimeException('The selected log entry no longer exists.');
            }

            // Updates use the same validated payload as create, but target a single existing row.
            $statement = $pdo->prepare(
                'UPDATE vehicle_logs SET
                    vehicle_id = :vehicle_id,
                    driver_id = :driver_id,
                    trip_date = :trip_date,
                    departure_location = :departure_location,
                    destination = :destination,
                    purpose = :purpose,
                    odometer_start = :odometer_start,
                    odometer_end = :odometer_end,
                    fuel_litres = :fuel_litres,
                    fuel_cost = :fuel_cost,
                    remarks = :remarks
                WHERE id = :entry_id'
            );
            $statement->bindValue(':entry_id', (int) $entryId, PDO::PARAM_INT);
        } else {
            // New trips are inserted once and the database computes distance_km automatically.
            $statement = $pdo->prepare(
                'INSERT INTO vehicle_logs (
                    vehicle_id,
                    driver_id,
                    trip_date,
                    departure_location,
                    destination,
                    purpose,
                    odometer_start,
                    odometer_end,
                    fuel_litres,
                    fuel_cost,
                    remarks
                ) VALUES (
                    :vehicle_id,
                    :driver_id,
                    :trip_date,
                    :departure_location,
                    :destination,
                    :purpose,
                    :odometer_start,
                    :odometer_end,
                    :fuel_litres,
                    :fuel_cost,
                    :remarks
                )'
            );
        }

        $statement->bindValue(':vehicle_id', $validated['vehicle_id'], PDO::PARAM_INT);
        $statement->bindValue(':driver_id', $validated['driver_id'], $validated['driver_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':trip_date', $validated['trip_date']);
        $statement->bindValue(':departure_location', $validated['departure_location']);
        $statement->bindValue(':destination', $validated['destination']);
        $statement->bindValue(':purpose', $validated['purpose']);
        $statement->bindValue(':odometer_start', $validated['odometer_start'], $validated['odometer_start'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':odometer_end', $validated['odometer_end'], $validated['odometer_end'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':fuel_litres', $validated['fuel_litres'], $validated['fuel_litres'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':fuel_cost', $validated['fuel_cost'], $validated['fuel_cost'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':remarks', $validated['remarks'], $validated['remarks'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->execute();

        logbookSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => $action === 'update' ? 'Log entry updated successfully' : 'Log entry created successfully',
                'message' => $action === 'update'
                    ? 'The vehicle trip has been updated in the official logbook.'
                    : 'The vehicle trip has been saved in the official logbook.',
            ],
        ]);
    } catch (RuntimeException $exception) {
        logbookSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Log entry was not updated' : 'Log entry was not created',
                'message' => $exception->getMessage(),
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
    } catch (Throwable $exception) {
        logbookSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Log entry was not updated' : 'Log entry was not created',
                'message' => $action === 'update'
                    ? 'A system error occurred while updating the log entry.'
                    : 'A system error occurred while saving the log entry.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
    }

    header('Location: ' . logbookPageUrl());
    exit;
}

// POST handler for delete actions
// Handles delete requests for logbook entries.
function logbookHandleDelete(): void
{
    // Deletes are handled as simple POST requests from the table row action.
    $entryId = filter_var((string) ($_POST['entry_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($entryId === false) {
        logbookSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Log entry was not deleted',
                'message' => 'The selected log entry could not be identified.',
            ],
        ]);
        header('Location: ' . logbookPageUrl());
        exit;
    }

    try {
        $statement = fleetDb()->prepare('DELETE FROM vehicle_logs WHERE id = :id');
        $statement->execute(['id' => $entryId]);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('The selected log entry no longer exists.');
        }

        logbookSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Log entry deleted successfully',
                'message' => 'The selected trip has been removed from the official logbook.',
            ],
        ]);
    } catch (RuntimeException $exception) {
        logbookSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Log entry was not deleted',
                'message' => $exception->getMessage(),
            ],
        ]);
    } catch (Throwable $exception) {
        logbookSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Log entry was not deleted',
                'message' => 'A system error occurred while deleting the log entry.',
            ],
        ]);
    }

    header('Location: ' . logbookPageUrl());
    exit;
}

// Request dispatcher for the logbook handler endpoint
// Dispatches incoming logbook POST requests by action type.
function logbookHandleRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . logbookPageUrl());
        exit;
    }

    // A single handler file dispatches create, update, and delete actions for logbook rows.
    $action = trim((string) ($_POST['logbook_action'] ?? 'create'));

    if ($action === 'delete') {
        logbookHandleDelete();
    }

    if ($action === 'update') {
        logbookHandleCreateOrUpdate('update');
    }

    logbookHandleCreateOrUpdate('create');
}

if (basename(__FILE__) === basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    // This file can be included for page data or called directly as the POST endpoint.
    logbookHandleRequest();
}
