<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
fleetAuthRequireAdmin();

// Loads dashboard metrics, alerts, recent logs, and other live summary data.
function dashboardFetchPageData(): array
{
    $metrics = [];
    $noticeCards = [];
    $departments = [];
    $serviceDueAlerts = [];
    $vehicleLogs = [];
    $activeMaintenance = [];
    $activeUsers = [];
    $onlineCount = 0;
    $needsRepairVehicle = null;

    try {
        $pdo = fleetDb();

        $vehicleCount = (int) $pdo->query('SELECT COUNT(*) FROM vehicles')->fetchColumn();
        $activeVehicleCount = (int) $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'active'")->fetchColumn();
        $driverCount = (int) $pdo->query('SELECT COUNT(*) FROM drivers')->fetchColumn();
        $maintenanceCost = (float) $pdo->query('SELECT COALESCE(SUM(total_cost), 0) FROM maintenance_records')->fetchColumn();

        $vehiclesUnderMaintenance = (int) $pdo->query("SELECT COUNT(*) FROM vehicles WHERE status = 'maintenance'")->fetchColumn();
        $recentTripEntries = (int) $pdo->query('SELECT COUNT(*) FROM vehicle_logs')->fetchColumn();
        $inspectionIssues = (int) $pdo->query("SELECT COUNT(*) FROM inspections WHERE inspection_type = 'pre' AND overall_status IN ('faulty', 'needs_repair')")->fetchColumn();

        $metrics = [
            ['label' => 'Total Vehicles', 'value' => (string) $vehicleCount, 'icon' => 'V'],
            ['label' => 'Active Vehicles', 'value' => (string) $activeVehicleCount, 'icon' => 'V'],
            ['label' => 'Registered Drivers', 'value' => (string) $driverCount, 'icon' => 'D'],
            ['label' => 'Maintenance Cost', 'value' => 'UGX ' . number_format($maintenanceCost, 0), 'icon' => 'M'],
        ];

        $noticeCards = [
            ['count' => $vehiclesUnderMaintenance . ' Vehicle(s)', 'label' => 'Currently under maintenance', 'tone' => 'info', 'icon' => '!'],
            ['count' => $recentTripEntries . ' Log(s)', 'label' => 'Recent trip entries', 'tone' => 'info', 'icon' => 'L'],
            ['count' => $inspectionIssues . ' Issue(s)', 'label' => 'Pre-inspection repair alerts', 'tone' => 'primary', 'icon' => 'P'],
        ];

        $departmentStatement = $pdo->query(
            "SELECT
                d.name,
                SUM(CASE WHEN v.status = 'active' THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN v.status = 'maintenance' THEN 1 ELSE 0 END) AS maintenance_count,
                SUM(CASE WHEN v.status = 'grounded' THEN 1 ELSE 0 END) AS grounded_count
            FROM departments d
            LEFT JOIN vehicles v ON v.department_id = d.id
            GROUP BY d.id, d.name
            ORDER BY d.name ASC"
        );

        foreach ($departmentStatement->fetchAll() as $row) {
            $departments[] = [
                'name' => $row['name'],
                'active' => (int) $row['active_count'],
                'maintenance' => (int) $row['maintenance_count'],
                'grounded' => (int) $row['grounded_count'],
            ];
        }

        $needsRepairStatement = $pdo->query(
            "SELECT
                v.registration_no,
                CONCAT(v.make, ' ', v.model) AS vehicle_model,
                COALESCE(d.name, 'Unassigned') AS department_name,
                i.overall_status
            FROM inspections i
            INNER JOIN vehicles v ON v.id = i.vehicle_id
            LEFT JOIN departments d ON d.id = v.department_id
            WHERE i.inspection_type = 'pre' AND i.overall_status IN ('faulty', 'needs_repair')
            ORDER BY i.inspection_date DESC, i.id DESC
            LIMIT 1"
        );
        $needsRepairVehicle = $needsRepairStatement->fetch() ?: null;

        $alertStatement = $pdo->query(
            "SELECT
                v.registration_no,
                CONCAT(v.make, ' ', v.model) AS vehicle_model,
                COALESCE(d.name, 'Unassigned') AS department_name,
                i.overall_status,
                COALESCE(i.defects, 'Inspection issue reported') AS defects
            FROM inspections i
            INNER JOIN vehicles v ON v.id = i.vehicle_id
            LEFT JOIN departments d ON d.id = v.department_id
            WHERE i.inspection_type = 'pre' AND i.overall_status IN ('faulty', 'needs_repair')
            ORDER BY i.inspection_date DESC, i.id DESC
            LIMIT 8"
        );

        foreach ($alertStatement->fetchAll() as $row) {
            $type = $row['overall_status'] === 'needs_repair' ? 'Needs Repair' : 'Faulty';
            $serviceDueAlerts[] = [
                'vehicle' => $row['registration_no'],
                'type' => $type,
                'typeTone' => $row['overall_status'] === 'needs_repair' ? 'purple' : 'blue',
                'model' => $row['vehicle_model'],
                'department' => $row['department_name'],
                'detail' => $row['defects'],
            ];
        }

        $logStatement = $pdo->query(
            "SELECT
                vl.trip_date,
                v.registration_no,
                COALESCE(dr.full_name, 'Unassigned') AS driver_name,
                vl.destination
            FROM vehicle_logs vl
            INNER JOIN vehicles v ON v.id = vl.vehicle_id
            LEFT JOIN drivers dr ON dr.id = vl.driver_id
            ORDER BY vl.trip_date DESC, vl.id DESC
            LIMIT 5"
        );

        foreach ($logStatement->fetchAll() as $row) {
            $vehicleLogs[] = [
                'date' => date('d M Y', strtotime((string) $row['trip_date'])),
                'vehicle' => $row['registration_no'],
                'driver' => $row['driver_name'],
                'destination' => $row['destination'],
                'status' => 'Logged',
            ];
        }

        $maintenanceStatement = $pdo->query(
            "SELECT
                mr.status,
                mr.description,
                v.registration_no
            FROM maintenance_records mr
            INNER JOIN vehicles v ON v.id = mr.vehicle_id
            WHERE mr.status IN ('reported', 'in_progress')
            ORDER BY mr.date_reported DESC, mr.id DESC
            LIMIT 5"
        );

        foreach ($maintenanceStatement->fetchAll() as $row) {
            $activeMaintenance[] = [
                'vehicle' => $row['registration_no'],
                'status' => ucwords(str_replace('_', ' ', (string) $row['status'])),
                'description' => $row['description'],
            ];
        }

        $userStatement = $pdo->query(
            "SELECT name, email, role, status, last_login_at
            FROM users
            WHERE status = 'active'
            ORDER BY COALESCE(last_login_at, '1970-01-01 00:00:00') DESC, id DESC
            LIMIT 5"
        );

        foreach ($userStatement->fetchAll() as $row) {
            $activeUsers[] = [
                'name' => $row['name'],
                'email' => $row['email'],
                'role' => $row['role'],
                'last_seen' => $row['last_login_at'] ? date('d M Y H:i', strtotime((string) $row['last_login_at'])) : 'No login recorded',
            ];
        }

        $onlineCount = count($activeUsers);
    } catch (Throwable $exception) {
        $metrics = [
            ['label' => 'Total Vehicles', 'value' => '0', 'icon' => 'V'],
            ['label' => 'Active Vehicles', 'value' => '0', 'icon' => 'V'],
            ['label' => 'Registered Drivers', 'value' => '0', 'icon' => 'D'],
            ['label' => 'Maintenance Cost', 'value' => 'UGX 0', 'icon' => 'M'],
        ];
    }

    return [
        'metrics' => $metrics,
        'noticeCards' => $noticeCards,
        'departments' => $departments,
        'serviceDueAlerts' => $serviceDueAlerts,
        'vehicleLogs' => $vehicleLogs,
        'activeMaintenance' => $activeMaintenance,
        'activeUsers' => $activeUsers,
        'onlineCount' => $onlineCount,
        'needsRepairVehicle' => $needsRepairVehicle,
    ];
}
