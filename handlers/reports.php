<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function reportsPageUrl(): string
{
    return '/fleet-system/modules/reports/';
}

function reportsBuildFilterState(): array
{
    $period = strtolower(trim((string) ($_GET['period'] ?? 'all')));

    return [
        'period' => in_array($period, ['all', 'month', 'quarter', 'year'], true) ? $period : 'all',
    ];
}

function reportsResolvePeriodRange(array $filters): array
{
    $today = new DateTimeImmutable('today');

    return match ($filters['period']) {
        'month' => [
            'period' => 'month',
            'date_from' => $today->modify('first day of this month')->format('Y-m-d'),
            'date_to' => $today->modify('last day of this month')->format('Y-m-d'),
            'label' => $today->format('F Y'),
            'select_label' => 'This Month',
        ],
        'quarter' => (function () use ($today): array {
            $month = (int) $today->format('n');
            $quarter = (int) ceil($month / 3);
            $quarterStartMonth = (($quarter - 1) * 3) + 1;
            $quarterStart = $today->setDate((int) $today->format('Y'), $quarterStartMonth, 1);
            $quarterEnd = $quarterStart->modify('+2 months')->modify('last day of this month');

            return [
                'period' => 'quarter',
                'date_from' => $quarterStart->format('Y-m-d'),
                'date_to' => $quarterEnd->format('Y-m-d'),
                'label' => 'Quarter ' . $quarter . ' ' . $today->format('Y'),
                'select_label' => 'This Quarter',
            ];
        })(),
        'year' => [
            'period' => 'year',
            'date_from' => $today->setDate((int) $today->format('Y'), 1, 1)->format('Y-m-d'),
            'date_to' => $today->setDate((int) $today->format('Y'), 12, 31)->format('Y-m-d'),
            'label' => $today->format('Y'),
            'select_label' => 'This Year',
        ],
        default => [
            'period' => 'all',
            'date_from' => null,
            'date_to' => null,
            'label' => 'All recorded dates',
            'select_label' => 'All Time',
        ],
    };
}

function reportsBuildDateFilter(string $column, array $periodRange): array
{
    if (($periodRange['date_from'] ?? null) === null || ($periodRange['date_to'] ?? null) === null) {
        return [
            'sql' => '',
            'params' => [],
        ];
    }

    return [
        'sql' => " WHERE {$column} BETWEEN :date_from AND :date_to",
        'params' => [
            'date_from' => $periodRange['date_from'],
            'date_to' => $periodRange['date_to'],
        ],
    ];
}

function reportsFormatMoney(float $amount): string
{
    return 'UGX ' . number_format($amount, 0);
}

function reportsFormatDistance(int $distance): string
{
    return number_format($distance) . ' km';
}

function reportsNormalizeMaintenanceType(string $type): string
{
    return match ($type) {
        'routine_service' => 'Routine Service',
        'brake_service' => 'Brake Service',
        default => ucwords(str_replace('_', ' ', $type)),
    };
}

function reportsFetchPageData(): array
{
    $filters = reportsBuildFilterState();
    $periodRange = reportsResolvePeriodRange($filters);
    $maintenanceDateFilter = reportsBuildDateFilter('mr.date_reported', $periodRange);
    $tripDateFilter = reportsBuildDateFilter('vl.trip_date', $periodRange);

    $summaryCards = [];
    $maintenanceByVehicle = [];
    $maintenanceByType = [];
    $tripsByVehicle = [];
    $reportHighlights = [];
    $notification = null;

    try {
        $pdo = fleetDb();

        $maintenanceSummaryStatement = $pdo->prepare(
            'SELECT COALESCE(SUM(mr.total_cost), 0) AS total_maintenance_cost
            FROM maintenance_records mr' . $maintenanceDateFilter['sql']
        );
        $maintenanceSummaryStatement->execute($maintenanceDateFilter['params']);
        $maintenanceSummary = $maintenanceSummaryStatement->fetch() ?: ['total_maintenance_cost' => 0];

        $tripSummaryStatement = $pdo->prepare(
            'SELECT
                COUNT(*) AS total_trips,
                COALESCE(SUM(vl.distance_km), 0) AS total_distance,
                COALESCE(SUM(vl.fuel_cost), 0) AS total_fuel_cost
            FROM vehicle_logs vl' . $tripDateFilter['sql']
        );
        $tripSummaryStatement->execute($tripDateFilter['params']);
        $tripSummary = $tripSummaryStatement->fetch() ?: ['total_trips' => 0, 'total_distance' => 0, 'total_fuel_cost' => 0];

        $maintenanceByVehicleStatement = $pdo->prepare(
            'SELECT
                v.registration_no,
                CONCAT(v.make, " ", v.model) AS make_model,
                COUNT(mr.id) AS record_count,
                COALESCE(SUM(mr.total_cost), 0) AS total_cost
            FROM maintenance_records mr
            INNER JOIN vehicles v ON v.id = mr.vehicle_id' . $maintenanceDateFilter['sql'] . '
            GROUP BY mr.vehicle_id, v.registration_no, v.make, v.model
            ORDER BY total_cost DESC, record_count DESC, v.registration_no ASC
            LIMIT 6'
        );
        $maintenanceByVehicleStatement->execute($maintenanceDateFilter['params']);
        $maintenanceByVehicleRows = $maintenanceByVehicleStatement->fetchAll();
        $maxMaintenanceVehicleCost = 0.0;

        foreach ($maintenanceByVehicleRows as $row) {
            $cost = (float) $row['total_cost'];
            $maxMaintenanceVehicleCost = max($maxMaintenanceVehicleCost, $cost);
            $maintenanceByVehicle[] = [
                'vehicle' => (string) $row['registration_no'],
                'make_model' => trim((string) $row['make_model']),
                'record_count' => (int) $row['record_count'],
                'total_cost' => $cost,
                'formatted_cost' => reportsFormatMoney($cost),
            ];
        }

        foreach ($maintenanceByVehicle as $index => $row) {
            $maintenanceByVehicle[$index]['bar_width'] = $maxMaintenanceVehicleCost > 0
                ? max(16, (int) round(($row['total_cost'] / $maxMaintenanceVehicleCost) * 100))
                : 0;
        }

        $maintenanceByTypeStatement = $pdo->prepare(
            'SELECT
                mr.maintenance_type,
                COUNT(mr.id) AS record_count,
                COALESCE(SUM(mr.total_cost), 0) AS total_cost
            FROM maintenance_records mr' . $maintenanceDateFilter['sql'] . '
            GROUP BY mr.maintenance_type
            ORDER BY total_cost DESC, record_count DESC, mr.maintenance_type ASC'
        );
        $maintenanceByTypeStatement->execute($maintenanceDateFilter['params']);
        $maintenanceByTypeRows = $maintenanceByTypeStatement->fetchAll();
        $totalMaintenanceCost = (float) ($maintenanceSummary['total_maintenance_cost'] ?? 0);

        foreach ($maintenanceByTypeRows as $row) {
            $cost = (float) $row['total_cost'];
            $share = $totalMaintenanceCost > 0 ? (int) round(($cost / $totalMaintenanceCost) * 100) : 0;
            $maintenanceByType[] = [
                'type' => reportsNormalizeMaintenanceType((string) $row['maintenance_type']),
                'record_count' => (int) $row['record_count'],
                'total_cost' => $cost,
                'formatted_cost' => reportsFormatMoney($cost),
                'share' => $share,
            ];
        }

        $tripsByVehicleStatement = $pdo->prepare(
            'SELECT
                v.registration_no,
                CONCAT(v.make, " ", v.model) AS make_model,
                COUNT(vl.id) AS trip_count,
                COALESCE(SUM(vl.distance_km), 0) AS total_distance,
                COALESCE(SUM(vl.fuel_cost), 0) AS total_fuel_cost
            FROM vehicle_logs vl
            INNER JOIN vehicles v ON v.id = vl.vehicle_id' . $tripDateFilter['sql'] . '
            GROUP BY vl.vehicle_id, v.registration_no, v.make, v.model
            ORDER BY trip_count DESC, total_distance DESC, v.registration_no ASC
            LIMIT 6'
        );
        $tripsByVehicleStatement->execute($tripDateFilter['params']);
        $tripsByVehicleRows = $tripsByVehicleStatement->fetchAll();
        $maxTripCount = 0;

        foreach ($tripsByVehicleRows as $row) {
            $tripCount = (int) $row['trip_count'];
            $maxTripCount = max($maxTripCount, $tripCount);
            $tripsByVehicle[] = [
                'vehicle' => (string) $row['registration_no'],
                'make_model' => trim((string) $row['make_model']),
                'trip_count' => $tripCount,
                'total_distance' => (int) $row['total_distance'],
                'formatted_distance' => reportsFormatDistance((int) $row['total_distance']),
                'formatted_fuel_cost' => reportsFormatMoney((float) $row['total_fuel_cost']),
            ];
        }

        foreach ($tripsByVehicle as $index => $row) {
            $tripsByVehicle[$index]['bar_height'] = $maxTripCount > 0
                ? max(18, (int) round(($row['trip_count'] / $maxTripCount) * 100))
                : 0;
        }

        $summaryCards = [
            ['label' => 'Total Maintenance Cost', 'value' => reportsFormatMoney((float) ($maintenanceSummary['total_maintenance_cost'] ?? 0)), 'tone' => 'blue', 'icon' => 'W'],
            ['label' => 'Total Fuel Cost', 'value' => reportsFormatMoney((float) ($tripSummary['total_fuel_cost'] ?? 0)), 'tone' => 'amber', 'icon' => 'F'],
            ['label' => 'Total Trips', 'value' => number_format((int) ($tripSummary['total_trips'] ?? 0)), 'tone' => 'green', 'icon' => 'T'],
            ['label' => 'Total Distance', 'value' => reportsFormatDistance((int) ($tripSummary['total_distance'] ?? 0)), 'tone' => 'blue', 'icon' => 'D'],
        ];

        $reportHighlights = [
            [
                'label' => 'Top Maintenance Vehicle',
                'value' => $maintenanceByVehicle[0]['vehicle'] ?? 'No data',
                'detail' => $maintenanceByVehicle[0]['formatted_cost'] ?? 'No maintenance records in this period.',
            ],
            [
                'label' => 'Most Active Vehicle',
                'value' => $tripsByVehicle[0]['vehicle'] ?? 'No data',
                'detail' => isset($tripsByVehicle[0]) ? $tripsByVehicle[0]['trip_count'] . ' trip(s) - ' . $tripsByVehicle[0]['formatted_distance'] : 'No trip records in this period.',
            ],
        ];
    } catch (Throwable $exception) {
        $notification = [
            'type' => 'error',
            'title' => 'Unable to load reports',
            'message' => 'The reports page could not fetch analytics from the database right now.',
        ];
    }

    return [
        'reportFilters' => $filters,
        'reportPeriodLabel' => $periodRange['label'],
        'reportPeriodSelectLabel' => $periodRange['select_label'],
        'reportsPageUrl' => reportsPageUrl(),
        'reportNotification' => $notification,
        'summaryCards' => $summaryCards,
        'maintenanceByVehicle' => $maintenanceByVehicle,
        'maintenanceByType' => $maintenanceByType,
        'tripsByVehicle' => $tripsByVehicle,
        'reportHighlights' => $reportHighlights,
    ];
}
