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

function reportsFormatShortMoney(float $amount): string
{
    if ($amount >= 1000000) {
        return 'UGX ' . rtrim(rtrim(number_format($amount / 1000000, 1, '.', ''), '0'), '.') . 'M';
    }

    if ($amount >= 1000) {
        return 'UGX ' . rtrim(rtrim(number_format($amount / 1000, 1, '.', ''), '0'), '.') . 'K';
    }

    return 'UGX ' . number_format($amount, 0);
}

function reportsNormalizeMaintenanceType(string $type): string
{
    return match ($type) {
        'routine_service' => 'Routine Service',
        'brake_service' => 'Brake Service',
        default => ucwords(str_replace('_', ' ', $type)),
    };
}

function reportsBuildTrendBuckets(array $periodRange): array
{
    $today = new DateTimeImmutable('today');
    $buckets = [];

    if (($periodRange['period'] ?? 'all') === 'month') {
        $start = new DateTimeImmutable((string) $periodRange['date_from']);
        $end = new DateTimeImmutable((string) $periodRange['date_to']);
        $cursor = $start;

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m-d');
            $buckets[$key] = [
                'key' => $key,
                'label' => $cursor->format('d M'),
            ];
            $cursor = $cursor->modify('+1 day');
        }

        return [
            'bucket_type' => 'day',
            'subtitle' => 'Daily trend across the selected month.',
            'buckets' => array_values($buckets),
        ];
    }

    if (($periodRange['period'] ?? 'all') === 'quarter') {
        $start = new DateTimeImmutable((string) $periodRange['date_from']);
        $end = new DateTimeImmutable((string) $periodRange['date_to']);
        $cursor = $start->modify('monday this week');

        while ($cursor <= $end) {
            $weekKey = $cursor->format('o-W');
            $buckets[$weekKey] = [
                'key' => $weekKey,
                'label' => 'Wk ' . $cursor->format('W'),
            ];
            $cursor = $cursor->modify('+1 week');
        }

        return [
            'bucket_type' => 'week',
            'subtitle' => 'Weekly trend across the selected quarter.',
            'buckets' => array_values($buckets),
        ];
    }

    if (($periodRange['period'] ?? 'all') === 'year') {
        $start = new DateTimeImmutable((string) $periodRange['date_from']);
        $end = new DateTimeImmutable((string) $periodRange['date_to']);
        $cursor = $start->modify('first day of this month');

        while ($cursor <= $end) {
            $monthKey = $cursor->format('Y-m');
            $buckets[$monthKey] = [
                'key' => $monthKey,
                'label' => $cursor->format('M'),
            ];
            $cursor = $cursor->modify('+1 month');
        }

        return [
            'bucket_type' => 'month',
            'subtitle' => 'Monthly trend across the selected year.',
            'buckets' => array_values($buckets),
        ];
    }

    $cursor = $today->modify('first day of this month')->modify('-11 months');

    for ($index = 0; $index < 12; $index++) {
        $monthKey = $cursor->format('Y-m');
        $buckets[$monthKey] = [
            'key' => $monthKey,
            'label' => $cursor->format('M y'),
        ];
        $cursor = $cursor->modify('+1 month');
    }

    return [
        'bucket_type' => 'month',
        'subtitle' => 'Monthly trend for the latest 12 months.',
        'buckets' => array_values($buckets),
    ];
}

function reportsBucketKeyForDate(string $date, string $bucketType): string
{
    $dateObject = new DateTimeImmutable($date);

    return match ($bucketType) {
        'day' => $dateObject->format('Y-m-d'),
        'week' => $dateObject->format('o-W'),
        default => $dateObject->format('Y-m'),
    };
}

function reportsBuildLineChart(array $rows): array
{
    if ($rows === []) {
        return [
            'path'       => '',
            'area_path'  => '',
            'points'     => [],
            'max_value'  => 0,
        ];
    }

    $maxValue = max(array_map(static fn (array $row): int => (int) $row['value'], $rows));

    $count       = count($rows);
    $chartLeft   = 4;
    $chartRight  = 96;
    $chartTop    = 4;
    $chartBottom = 96;

    $points    = [];
    $lineParts = [];

    foreach ($rows as $index => $row) {
        $x = $count === 1
            ? ($chartLeft + $chartRight) / 2
            : $chartLeft + (($index / max(1, $count - 1)) * ($chartRight - $chartLeft));

        $y = $maxValue > 0
            ? $chartBottom - (((int) $row['value'] / $maxValue) * ($chartBottom - $chartTop))
            : $chartBottom;

        $points[] = [
            'x'     => round($x, 2),
            'y'     => round($y, 2),
            'label' => $row['label'],
            'value' => (int) $row['value'],
        ];

        $lineParts[] = ($index === 0 ? 'M ' : 'L ') . round($x, 2) . ' ' . round($y, 2);
    }

    $firstPoint = $points[0];
    $lastPoint  = $points[array_key_last($points)];
    $areaPath   = implode(' ', $lineParts)
        . ' L ' . $lastPoint['x'] . ' ' . $chartBottom
        . ' L ' . $firstPoint['x'] . ' ' . $chartBottom
        . ' Z';

    return [
        'path'       => implode(' ', $lineParts),
        'area_path'  => $areaPath,
        'points'     => $points,
        'max_value'  => $maxValue,
    ];
}

function reportsLimitTrendRows(array $rows, int $limit = 8): array
{
    if (count($rows) <= $limit) {
        return $rows;
    }

    return array_slice($rows, -$limit);
}

function reportsFetchPageData(): array
{
    $filters = reportsBuildFilterState();
    $periodRange = reportsResolvePeriodRange($filters);
    $maintenanceDateFilter = reportsBuildDateFilter('mr.date_reported', $periodRange);
    $tripDateFilter = reportsBuildDateFilter('vl.trip_date', $periodRange);
    $trendConfig = reportsBuildTrendBuckets($periodRange);
    $trendBuckets = $trendConfig['buckets'];
    $usageTrendMap = [];
    $maintenanceTrendMap = [];

    foreach ($trendBuckets as $bucket) {
        $usageTrendMap[$bucket['key']] = 0;
        $maintenanceTrendMap[$bucket['key']] = 0.0;
    }

    $summaryCards = [];
    $maintenanceByVehicle = [];
    $maintenanceByType = [];
    $tripsByVehicle = [];
    $reportHighlights = [];
    $tripTrend = [];
    $maintenanceTrend = [];
    $tripTrendChart = ['path' => '', 'area_path' => '', 'points' => [], 'max_value' => 0];
    $maxMaintenanceTrendValue = 0.0;
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

        $usageTrendStatement = $pdo->prepare(
            'SELECT vl.trip_date
            FROM vehicle_logs vl' . $tripDateFilter['sql'] . '
            ORDER BY vl.trip_date ASC, vl.id ASC'
        );
        $usageTrendStatement->execute($tripDateFilter['params']);

        foreach ($usageTrendStatement->fetchAll() as $row) {
            $key = reportsBucketKeyForDate((string) $row['trip_date'], $trendConfig['bucket_type']);
            if (array_key_exists($key, $usageTrendMap)) {
                $usageTrendMap[$key]++;
            }
        }

        $maintenanceTrendStatement = $pdo->prepare(
            'SELECT mr.date_reported, mr.total_cost
            FROM maintenance_records mr' . $maintenanceDateFilter['sql'] . '
            ORDER BY mr.date_reported ASC, mr.id ASC'
        );
        $maintenanceTrendStatement->execute($maintenanceDateFilter['params']);

        foreach ($maintenanceTrendStatement->fetchAll() as $row) {
            $key = reportsBucketKeyForDate((string) $row['date_reported'], $trendConfig['bucket_type']);
            if (array_key_exists($key, $maintenanceTrendMap)) {
                $maintenanceTrendMap[$key] += (float) $row['total_cost'];
            }
        }

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

        foreach ($trendBuckets as $bucket) {
            $tripValue = (int) ($usageTrendMap[$bucket['key']] ?? 0);
            $maintenanceValue = (float) ($maintenanceTrendMap[$bucket['key']] ?? 0.0);
            $tripTrend[] = [
                'label' => $bucket['label'],
                'value' => $tripValue,
            ];
            $maintenanceTrend[] = [
                'label' => $bucket['label'],
                'value' => $maintenanceValue,
                'formatted_value' => reportsFormatMoney($maintenanceValue),
            ];
            $maxMaintenanceTrendValue = max($maxMaintenanceTrendValue, $maintenanceValue);
        }

        $tripTrend = reportsLimitTrendRows($tripTrend, 8);
        $maintenanceTrend = reportsLimitTrendRows($maintenanceTrend, 8);
        $maxMaintenanceTrendValue = 0.0;

        foreach ($maintenanceTrend as $bucket) {
            $maxMaintenanceTrendValue = max($maxMaintenanceTrendValue, (float) $bucket['value']);
        }

        foreach ($maintenanceTrend as $index => $bucket) {
            $maintenanceTrend[$index]['bar_height'] = $maxMaintenanceTrendValue > 0
                ? max(10, (int) round(($bucket['value'] / $maxMaintenanceTrendValue) * 100))
                : 0;
            $maintenanceTrend[$index]['short_value'] = reportsFormatShortMoney((float) $bucket['value']);
        }

        $tripTrendChart = reportsBuildLineChart($tripTrend);

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
        'tripTrend' => $tripTrend,
        'tripTrendChart' => $tripTrendChart,
        'tripTrendSubtitle' => $trendConfig['subtitle'],
        'maintenanceTrend' => $maintenanceTrend,
        'maintenanceTrendSubtitle' => $trendConfig['subtitle'],
    ];
}
