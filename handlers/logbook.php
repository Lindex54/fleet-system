<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function logbookStartSession(): void
{
    // Flash messages for the logbook form are stored in the session across redirects.
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function logbookPageUrl(): string
{
    return '/fleet-system/modules/logbook/index.php';
}

function logbookHandlerUrl(): string
{
    return '/fleet-system/handlers/logbook.php';
}

function logbookSetFlash(array $payload): void
{
    // Save one-time feedback that the page can render after POST redirects.
    logbookStartSession();
    $_SESSION['logbook_flash'] = $payload;
}

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

function logbookFormatMoney(?float $amount): string
{
    if ($amount === null) {
        return '-';
    }

    return 'UGX ' . number_format($amount, 0);
}

function logbookFormatDate(?string $date): string
{
    if ($date === null || $date === '') {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y', $timestamp) : $date;
}

function logbookFetchVehicleOptions(PDO $pdo): array
{
    // The modal uses live vehicle records instead of hard-coded registration numbers.
    $statement = $pdo->query('SELECT id, registration_no FROM vehicles ORDER BY registration_no ASC');

    return $statement->fetchAll();
}

function logbookFetchDriverOptions(PDO $pdo): array
{
    // Drivers are optional in the schema, so the form can still save unassigned trips.
    $statement = $pdo->query("SELECT id, full_name FROM drivers WHERE status = 'active' ORDER BY full_name ASC");

    return $statement->fetchAll();
}

function logbookFetchPageData(): array
{
    // The page needs current logs, select options, totals, and any flash state from the last POST.
    $flash = logbookPullFlash();
    $notification = $flash['notification'] ?? null;
    $formData = $flash['form_data'] ?? [];
    $openModal = (bool) ($flash['open_modal'] ?? false);

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
                vl.trip_date,
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
        'logbookFormAction' => logbookHandlerUrl(),
        'logbookVehicleOptions' => $vehicleOptions,
        'logbookDriverOptions' => $driverOptions,
    ];
}

function logbookHandleCreate(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . logbookPageUrl());
        exit;
    }

    // Normalize incoming form values before validation and database storage.
    $tripDate = trim((string) ($_POST['date'] ?? ''));
    $vehicleId = trim((string) ($_POST['vehicle'] ?? ''));
    $driverId = trim((string) ($_POST['driver'] ?? ''));
    $departureLocation = trim((string) ($_POST['departure_location'] ?? ''));
    $destination = trim((string) ($_POST['destination'] ?? ''));
    $purpose = trim((string) ($_POST['purpose'] ?? ''));
    $odometerStart = trim((string) ($_POST['odometer_start'] ?? ''));
    $odometerEnd = trim((string) ($_POST['odometer_end'] ?? ''));
    $fuelLitres = trim((string) ($_POST['fuel_litres'] ?? ''));
    $fuelCost = trim((string) ($_POST['fuel_cost'] ?? ''));
    $remarks = trim((string) ($_POST['remarks'] ?? ''));

    $formData = [
        'date' => $tripDate,
        'vehicle' => $vehicleId,
        'driver' => $driverId,
        'departure_location' => $departureLocation,
        'destination' => $destination,
        'purpose' => $purpose,
        'odometer_start' => $odometerStart,
        'odometer_end' => $odometerEnd,
        'fuel_litres' => $fuelLitres,
        'fuel_cost' => $fuelCost,
        'remarks' => $remarks,
    ];

    if ($tripDate === '' || $vehicleId === '' || $departureLocation === '' || $destination === '' || $purpose === '') {
        logbookSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Log entry was not created',
                'message' => 'Date, vehicle, departure location, destination, and purpose are required.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
        ]);

        header('Location: ' . logbookPageUrl());
        exit;
    }

    // Validate numeric inputs carefully because the table calculates distance from odometer values.
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
        logbookSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Log entry was not created',
                'message' => 'Please review the numeric fields and enter valid values.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
        ]);

        header('Location: ' . logbookPageUrl());
        exit;
    }

    if ($odometerStartValue !== null && $odometerEndValue !== null && $odometerEndValue < $odometerStartValue) {
        logbookSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Log entry was not created',
                'message' => 'Odometer end cannot be less than odometer start.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
        ]);

        header('Location: ' . logbookPageUrl());
        exit;
    }

    try {
        $pdo = fleetDb();

        // These existence checks keep bad select values from being inserted by manual requests.
        $vehicleExists = $pdo->prepare('SELECT COUNT(*) FROM vehicles WHERE id = :id');
        $vehicleExists->execute(['id' => $vehicleIdValue]);

        if ((int) $vehicleExists->fetchColumn() === 0) {
            throw new RuntimeException('Selected vehicle does not exist.');
        }

        if ($driverIdValue !== null) {
            $driverExists = $pdo->prepare('SELECT COUNT(*) FROM drivers WHERE id = :id');
            $driverExists->execute(['id' => $driverIdValue]);

            if ((int) $driverExists->fetchColumn() === 0) {
                throw new RuntimeException('Selected driver does not exist.');
            }
        }

        // Save the trip exactly once and let MySQL compute the travelled distance automatically.
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

        $statement->bindValue(':vehicle_id', $vehicleIdValue, PDO::PARAM_INT);
        $statement->bindValue(':driver_id', $driverIdValue, $driverIdValue === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':trip_date', $tripDate);
        $statement->bindValue(':departure_location', $departureLocation);
        $statement->bindValue(':destination', $destination);
        $statement->bindValue(':purpose', $purpose);
        $statement->bindValue(':odometer_start', $odometerStartValue, $odometerStartValue === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':odometer_end', $odometerEndValue, $odometerEndValue === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':fuel_litres', $fuelLitresValue, $fuelLitresValue === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':fuel_cost', $fuelCostValue, $fuelCostValue === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':remarks', $remarks === '' ? null : $remarks, $remarks === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->execute();

        logbookSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Log entry created successfully',
                'message' => 'The vehicle trip has been saved in the official logbook.',
            ],
        ]);
    } catch (RuntimeException $exception) {
        logbookSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Log entry was not created',
                'message' => $exception->getMessage(),
            ],
            'form_data' => $formData,
            'open_modal' => true,
        ]);
    } catch (Throwable $exception) {
        logbookSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Log entry was not created',
                'message' => 'A system error occurred while saving the log entry.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
        ]);
    }

    header('Location: ' . logbookPageUrl());
    exit;
}

if (basename(__FILE__) === basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    // This file can be included for page data or called directly as the POST endpoint.
    logbookHandleCreate();
}
