<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function vehicleUsagePageUrl(): string
{
    return '/fleet-system/modules/vehicle-usage/';
}

function vehicleUsageFormatDate(?string $date): string
{
    if ($date === null || $date === '') {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d M Y', $timestamp) : $date;
}

function vehicleUsageFormatMoney(?float $amount): string
{
    if ($amount === null) {
        return '-';
    }

    return 'UGX ' . number_format($amount, 0);
}

function vehicleUsageFormatNumber(?int $value, string $suffix = ''): string
{
    if ($value === null) {
        return '-';
    }

    return number_format($value) . $suffix;
}

function vehicleUsageFetchVehicleOptions(PDO $pdo): array
{
    $statement = $pdo->query(
        "SELECT
            id,
            registration_no,
            CONCAT(make, ' ', model) AS make_model,
            current_mileage,
            status
        FROM vehicles
        WHERE status <> 'disposed'
        ORDER BY registration_no ASC"
    );

    return $statement->fetchAll();
}

function vehicleUsageFetchDriverOptions(PDO $pdo): array
{
    $statement = $pdo->query(
        "SELECT id, full_name, status
        FROM drivers
        ORDER BY full_name ASC"
    );

    return $statement->fetchAll();
}

function vehicleUsageBuildFilterState(): array
{
    return [
        'vehicle_id' => trim((string) ($_GET['vehicle_id'] ?? '')),
        'driver_id' => trim((string) ($_GET['driver_id'] ?? '')),
        'period' => strtolower(trim((string) ($_GET['period'] ?? 'all'))),
        'week' => trim((string) ($_GET['week'] ?? '')),
        'month' => trim((string) ($_GET['month'] ?? '')),
        'date_from' => trim((string) ($_GET['date_from'] ?? '')),
        'date_to' => trim((string) ($_GET['date_to'] ?? '')),
    ];
}

function vehicleUsageResolvePeriodRange(array $filters): array
{
    $period = in_array($filters['period'], ['all', 'week', 'month', 'custom'], true)
        ? $filters['period']
        : 'all';

    if ($period === 'week' && $filters['week'] !== '') {
        $weekStart = DateTimeImmutable::createFromFormat('o-\WW-N', $filters['week'] . '-1');
        $weekEnd = DateTimeImmutable::createFromFormat('o-\WW-N', $filters['week'] . '-7');

        if ($weekStart && $weekEnd) {
            return [
                'period' => 'week',
                'date_from' => $weekStart->format('Y-m-d'),
                'date_to' => $weekEnd->format('Y-m-d'),
                'label' => 'Week of ' . vehicleUsageFormatDate($weekStart->format('Y-m-d')) . ' to ' . vehicleUsageFormatDate($weekEnd->format('Y-m-d')),
            ];
        }
    }

    if ($period === 'month' && preg_match('/^\d{4}-\d{2}$/', $filters['month']) === 1) {
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

    if ($period === 'custom' && $filters['date_from'] !== '' && $filters['date_to'] !== '') {
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
                'label' => vehicleUsageFormatDate($dateFrom->format('Y-m-d')) . ' to ' . vehicleUsageFormatDate($dateTo->format('Y-m-d')),
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

function vehicleUsageBuildQueryFilters(array $filters, array $periodRange): array
{
    $conditions = [];
    $params = [];

    $vehicleId = $filters['vehicle_id'] === ''
        ? null
        : filter_var($filters['vehicle_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($vehicleId === false) {
        $vehicleId = null;
    }

    $driverId = $filters['driver_id'] === '' || $filters['driver_id'] === 'all'
        ? null
        : filter_var($filters['driver_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($driverId === false) {
        $driverId = null;
    }

    if ($vehicleId !== null) {
        $conditions[] = 'vl.vehicle_id = :vehicle_id';
        $params['vehicle_id'] = (int) $vehicleId;
    }

    if ($driverId !== null) {
        $conditions[] = 'vl.driver_id = :driver_id';
        $params['driver_id'] = (int) $driverId;
    }

    if ($periodRange['date_from'] !== null && $periodRange['date_to'] !== null) {
        $conditions[] = 'vl.trip_date BETWEEN :date_from AND :date_to';
        $params['date_from'] = $periodRange['date_from'];
        $params['date_to'] = $periodRange['date_to'];
    }

    return [
        'where_sql' => $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions),
        'params' => $params,
        'vehicle_id' => $vehicleId === null ? null : (int) $vehicleId,
        'driver_id' => $driverId === null ? null : (int) $driverId,
    ];
}

function vehicleUsageFetchSelectedVehicle(PDO $pdo, ?int $vehicleId): ?array
{
    if ($vehicleId === null) {
        return null;
    }

    $statement = $pdo->prepare(
        "SELECT
            v.id,
            v.registration_no,
            v.make,
            v.model,
            v.manufacture_year,
            v.vehicle_type,
            v.fuel_type,
            v.current_mileage,
            v.status,
            COALESCE(d.name, 'Unassigned') AS department_name
        FROM vehicles v
        LEFT JOIN departments d ON d.id = v.department_id
        WHERE v.id = :id
        LIMIT 1"
    );
    $statement->execute(['id' => $vehicleId]);
    $vehicle = $statement->fetch();

    if (!$vehicle) {
        return null;
    }

    return [
        'id' => (int) $vehicle['id'],
        'registration_no' => $vehicle['registration_no'],
        'make_model' => trim($vehicle['make'] . ' ' . $vehicle['model']),
        'manufacture_year' => $vehicle['manufacture_year'] ?: '-',
        'vehicle_type' => ucfirst((string) $vehicle['vehicle_type']),
        'fuel_type' => ucfirst((string) $vehicle['fuel_type']),
        'current_mileage' => vehicleUsageFormatNumber((int) $vehicle['current_mileage'], ' km'),
        'status' => ucfirst((string) $vehicle['status']),
        'department_name' => $vehicle['department_name'],
    ];
}

function vehicleUsageFetchRows(PDO $pdo, string $whereSql, array $params): array
{
    $statement = $pdo->prepare(
        "SELECT
            vl.id,
            vl.trip_date,
            vl.vehicle_id,
            vl.driver_id,
            v.registration_no,
            CONCAT(v.make, ' ', v.model) AS vehicle_name,
            COALESCE(d.full_name, 'Unassigned') AS driver_name,
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
        $whereSql
        ORDER BY vl.trip_date DESC, vl.id DESC"
    );
    $statement->execute($params);

    $rows = [];

    foreach ($statement->fetchAll() as $row) {
        $rows[] = [
            'id' => (int) $row['id'],
            'date' => vehicleUsageFormatDate($row['trip_date']),
            'date_raw' => $row['trip_date'],
            'vehicle_id' => (int) $row['vehicle_id'],
            'vehicle' => $row['registration_no'],
            'vehicle_name' => $row['vehicle_name'],
            'driver_id' => $row['driver_id'] !== null ? (int) $row['driver_id'] : null,
            'driver' => $row['driver_name'],
            'route' => $row['departure_location'] . ' - ' . $row['destination'],
            'from' => $row['departure_location'],
            'to' => $row['destination'],
            'purpose' => $row['purpose'],
            'odometer_start' => $row['odometer_start'] !== null ? number_format((int) $row['odometer_start']) : '-',
            'odometer_end' => $row['odometer_end'] !== null ? number_format((int) $row['odometer_end']) : '-',
            'distance' => $row['distance_km'] !== null ? number_format((int) $row['distance_km']) . ' km' : '-',
            'distance_raw' => $row['distance_km'] !== null ? (int) $row['distance_km'] : 0,
            'fuel_litres' => $row['fuel_litres'] !== null ? rtrim(rtrim(number_format((float) $row['fuel_litres'], 2, '.', ''), '0'), '.') . ' L' : '-',
            'fuel_litres_raw' => $row['fuel_litres'] !== null ? (float) $row['fuel_litres'] : 0.0,
            'fuel_cost' => vehicleUsageFormatMoney($row['fuel_cost'] !== null ? (float) $row['fuel_cost'] : null),
            'fuel_cost_raw' => $row['fuel_cost'] !== null ? (float) $row['fuel_cost'] : 0.0,
            'remarks' => $row['remarks'] ?: '-',
        ];
    }

    return $rows;
}

function vehicleUsageBuildDriverBreakdown(array $rows): array
{
    $breakdown = [];

    foreach ($rows as $row) {
        $driverKey = (string) ($row['driver_id'] ?? 0);

        if (!isset($breakdown[$driverKey])) {
            $breakdown[$driverKey] = [
                'driver' => $row['driver'],
                'trips' => 0,
                'distance_raw' => 0,
                'latest_date_raw' => $row['date_raw'],
            ];
        }

        $breakdown[$driverKey]['trips']++;
        $breakdown[$driverKey]['distance_raw'] += (int) $row['distance_raw'];

        if ($row['date_raw'] > $breakdown[$driverKey]['latest_date_raw']) {
            $breakdown[$driverKey]['latest_date_raw'] = $row['date_raw'];
        }
    }

    usort($breakdown, static function (array $left, array $right): int {
        if ($right['trips'] === $left['trips']) {
            return $right['distance_raw'] <=> $left['distance_raw'];
        }

        return $right['trips'] <=> $left['trips'];
    });

    return array_map(static function (array $row): array {
        return [
            'driver' => $row['driver'],
            'trips' => $row['trips'],
            'distance' => number_format((int) $row['distance_raw']) . ' km',
            'latest_date' => vehicleUsageFormatDate($row['latest_date_raw']),
        ];
    }, $breakdown);
}

function vehicleUsageBuildDriverSections(array $rows): array
{
    $sections = [];

    foreach ($rows as $row) {
        $driverKey = (string) ($row['driver_id'] ?? 0);

        if (!isset($sections[$driverKey])) {
            $sections[$driverKey] = [
                'driver_id' => $row['driver_id'],
                'driver' => $row['driver'],
                'rows' => [],
                'trip_count' => 0,
                'distance_raw' => 0,
                'fuel_cost_raw' => 0.0,
            ];
        }

        $sections[$driverKey]['rows'][] = $row;
        $sections[$driverKey]['trip_count']++;
        $sections[$driverKey]['distance_raw'] += (int) $row['distance_raw'];
        $sections[$driverKey]['fuel_cost_raw'] += (float) $row['fuel_cost_raw'];
    }

    return array_values(array_map(static function (array $section): array {
        $section['distance'] = number_format((int) $section['distance_raw']) . ' km';
        $section['fuel_cost'] = vehicleUsageFormatMoney($section['fuel_cost_raw']);

        return $section;
    }, $sections));
}

function vehicleUsageBuildSummary(array $rows): array
{
    $totalDistance = 0;
    $totalFuel = 0.0;
    $totalCost = 0.0;
    $drivers = [];

    foreach ($rows as $row) {
        $totalDistance += (int) $row['distance_raw'];
        $totalFuel += (float) $row['fuel_litres_raw'];
        $totalCost += (float) $row['fuel_cost_raw'];
        $drivers[$row['driver']] = true;
    }

    return [
        'trip_count' => count($rows),
        'driver_count' => count($drivers),
        'total_distance' => number_format($totalDistance) . ' km',
        'total_fuel' => rtrim(rtrim(number_format($totalFuel, 2, '.', ''), '0'), '.') . ' L',
        'total_cost' => vehicleUsageFormatMoney($totalCost),
    ];
}

function vehicleUsageBuildPrintTitle(?array $selectedVehicle, ?array $selectedDriver, string $periodLabel): string
{
    $parts = ['Vehicle Usage Report'];

    if ($selectedVehicle !== null) {
        $parts[] = $selectedVehicle['registration_no'];
    } else {
        $parts[] = 'All Vehicles';
    }

    if ($selectedDriver !== null) {
        $parts[] = $selectedDriver['full_name'];
    }

    $parts[] = $periodLabel;

    return implode(' - ', $parts);
}

function vehicleUsageFindDriverOption(array $driverOptions, ?int $driverId): ?array
{
    if ($driverId === null) {
        return null;
    }

    foreach ($driverOptions as $driver) {
        if ((int) $driver['id'] === $driverId) {
            return $driver;
        }
    }

    return null;
}

function vehicleUsageBuildVehicleSubjectLabel(array $rows, ?array $selectedVehicle, array $filters): string
{
    if ($selectedVehicle !== null) {
        return (string) $selectedVehicle['registration_no'];
    }

    if (($filters['vehicle_id'] ?? '') === '' && ($filters['driver_id'] ?? '') === '') {
        return 'All Vehicles';
    }

    $registrations = [];
    foreach ($rows as $row) {
        $registration = trim((string) ($row['vehicle'] ?? ''));
        if ($registration !== '') {
            $registrations[$registration] = true;
        }
    }

    if ($registrations === []) {
        return 'All Vehicles';
    }

    return implode(', ', array_keys($registrations));
}

function vehicleUsageFetchPageData(): array
{
    $filters = vehicleUsageBuildFilterState();

    try {
        $pdo = fleetDb();
        $vehicleOptions = vehicleUsageFetchVehicleOptions($pdo);
        $driverOptions = vehicleUsageFetchDriverOptions($pdo);
        $periodRange = vehicleUsageResolvePeriodRange($filters);
        $queryFilters = vehicleUsageBuildQueryFilters($filters, $periodRange);
        $selectedVehicle = vehicleUsageFetchSelectedVehicle($pdo, $queryFilters['vehicle_id']);
        $selectedDriver = vehicleUsageFindDriverOption($driverOptions, $queryFilters['driver_id']);
        $rows = vehicleUsageFetchRows($pdo, $queryFilters['where_sql'], $queryFilters['params']);
        $summary = vehicleUsageBuildSummary($rows);
        $driverBreakdown = vehicleUsageBuildDriverBreakdown($rows);
        $driverSections = vehicleUsageBuildDriverSections($rows);
        $vehicleSubjectLabel = vehicleUsageBuildVehicleSubjectLabel($rows, $selectedVehicle, $filters);

        return [
            'vehicleUsageFilters' => $filters,
            'vehicleUsageVehicleOptions' => $vehicleOptions,
            'vehicleUsageDriverOptions' => $driverOptions,
            'vehicleUsageSelectedVehicle' => $selectedVehicle,
            'vehicleUsageSelectedDriver' => $selectedDriver,
            'vehicleUsageRows' => $rows,
            'vehicleUsageHasRows' => $rows !== [],
            'vehicleUsageSummary' => $summary,
            'vehicleUsageDriverBreakdown' => $driverBreakdown,
            'vehicleUsageDriverSections' => $driverSections,
            'vehicleUsageMemoTo' => 'University Secretary',
            'vehicleUsageMemoThruOne' => 'University Bursar',
            'vehicleUsageMemoThruTwo' => 'Programme Controller',
            'vehicleUsageMemoFrom' => 'Ag. AEO. (Mech.) Simali Habert',
            'vehicleUsageMemoDate' => date('F j, Y'),
            'vehicleUsageMemoSubject' => 'VEHICLE USAGE REPORT FOR MOTOR VEHICLE REG: NO. ' . $vehicleSubjectLabel . '.',
            'vehicleUsageMemoFor' => $vehicleSubjectLabel,
            'vehicleUsagePeriodLabel' => $periodRange['label'],
            'vehicleUsagePrintTitle' => vehicleUsageBuildPrintTitle($selectedVehicle, $selectedDriver, $periodRange['label']),
            'vehicleUsagePageUrl' => vehicleUsagePageUrl(),
            'vehicleUsageNotification' => null,
        ];
    } catch (Throwable $exception) {
        return [
            'vehicleUsageFilters' => $filters,
            'vehicleUsageVehicleOptions' => [],
            'vehicleUsageDriverOptions' => [],
            'vehicleUsageSelectedVehicle' => null,
            'vehicleUsageSelectedDriver' => null,
            'vehicleUsageRows' => [],
            'vehicleUsageHasRows' => false,
            'vehicleUsageSummary' => [
                'trip_count' => 0,
                'driver_count' => 0,
                'total_distance' => '0 km',
                'total_fuel' => '0 L',
                'total_cost' => 'UGX 0',
            ],
            'vehicleUsageDriverBreakdown' => [],
            'vehicleUsageDriverSections' => [],
            'vehicleUsageMemoTo' => 'University Secretary',
            'vehicleUsageMemoThruOne' => 'University Bursar',
            'vehicleUsageMemoThruTwo' => 'Programme Controller',
            'vehicleUsageMemoFrom' => 'Ag. AEO. (Mech.) Simali Habert',
            'vehicleUsageMemoDate' => date('F j, Y'),
            'vehicleUsageMemoSubject' => 'VEHICLE USAGE REPORT FOR MOTOR VEHICLE REG: NO. ALL VEHICLES.',
            'vehicleUsageMemoFor' => 'All Vehicles',
            'vehicleUsagePeriodLabel' => 'All recorded dates',
            'vehicleUsagePrintTitle' => 'Vehicle Usage Report',
            'vehicleUsagePageUrl' => vehicleUsagePageUrl(),
            'vehicleUsageNotification' => [
                'type' => 'error',
                'title' => 'Unable to load vehicle usage',
                'message' => 'Vehicle usage logs could not be loaded right now.',
            ],
        ];
    }
}
