<?php
// Central static data store for the frontend prototype.
//
// This file keeps all temporary mock data in one place while the backend is still pending.
// Each page asks for only the dataset it needs through fleetData('key'), which makes the
// page templates cleaner and gives us one future replacement point for database queries.

function fleetData(string $key): array
{
    static $data = null;

    if ($data === null) {
        $data = [
            'dashboard' => buildDashboardData(),
            'vehicles' => buildVehiclesData(),
            'drivers' => buildDriversData(),
            'logbook' => buildLogbookData(),
            'maintenance' => buildMaintenanceData(),
            'reports' => buildReportsData(),
            'pre_inspection' => buildPreInspectionData(),
            'post_inspection' => buildPostInspectionData(),
            'service_providers' => buildServiceProvidersData(),
            'communications' => buildCommunicationsData(),
            'communication_history' => buildCommunicationHistoryData(),
            'estates' => buildEstatesData(),
        ];
    }

    return $data[$key] ?? [];
}

function buildDashboardData(): array
{
    return [
        'metrics' => [
            ['label' => 'Total Vehicles', 'value' => '5', 'icon' => 'V'],
            ['label' => 'Active Vehicles', 'value' => '5', 'icon' => 'V'],
            ['label' => 'Registered Drivers', 'value' => '0', 'icon' => 'D'],
            ['label' => 'Maintenance Cost', 'value' => 'UGX 4,200,000', 'icon' => 'M'],
        ],
        'noticeCards' => [
            ['count' => '2 Vehicle(s)', 'label' => 'Currently under maintenance', 'tone' => 'info', 'icon' => '!'],
            ['count' => '2 Log(s)', 'label' => 'Recent trip entries', 'tone' => 'info', 'icon' => 'L'],
            ['count' => '0 Driver(s)', 'label' => 'Registered in the system', 'tone' => 'primary', 'icon' => 'D'],
        ],
        'departments' => [
            ['name' => 'Unassigned', 'active' => 0, 'maintenance' => 1, 'grounded' => 0],
            ['name' => 'DVD fa', 'active' => 1, 'maintenance' => 0, 'grounded' => 0],
            ['name' => 'University Secretary', 'active' => 1, 'maintenance' => 0, 'grounded' => 0],
            ['name' => 'Estates', 'active' => 1, 'maintenance' => 0, 'grounded' => 0],
            ['name' => 'Vice Chancellor', 'active' => 0, 'maintenance' => 1, 'grounded' => 0],
        ],
        'serviceDueAlerts' => [
            ['vehicle' => 'UBR 123C', 'type' => 'Routine Service', 'typeTone' => 'blue', 'model' => 'TOYOTA Land cruiser', 'department' => 'DVD fa', 'detail' => '852,436 km since last - every 4,000 km'],
            ['vehicle' => 'UBR 123C', 'type' => 'Brake Service', 'typeTone' => 'purple', 'model' => 'TOYOTA Land cruiser', 'department' => 'DVD fa', 'detail' => '852,436 km since last - every 10,000 km'],
            ['vehicle' => 'UBR 402Q', 'type' => 'Routine Service', 'typeTone' => 'blue', 'model' => 'TOYOTA HILLUX PICKUP', 'department' => 'University Secretary', 'detail' => '65,231 km since last - every 4,000 km'],
            ['vehicle' => 'UBR 402Q', 'type' => 'Brake Service', 'typeTone' => 'purple', 'model' => 'TOYOTA HILLUX PICKUP', 'department' => 'University Secretary', 'detail' => '65,231 km since last - every 10,000 km'],
            ['vehicle' => 'UAJ 433X', 'type' => 'Routine Service', 'typeTone' => 'blue', 'model' => 'TOYOTA PRADO', 'department' => 'Estates', 'detail' => '24,120 km since last - every 4,000 km'],
        ],
        'vehicleLogs' => [
            ['date' => '17 May 2026', 'vehicle' => 'UAJ 433X', 'driver' => 'SIMALI HABERT', 'destination' => 'Mbale', 'status' => 'Pending'],
            ['date' => '17 May 2026', 'vehicle' => 'UAJ 433X', 'driver' => 'SIMALI HABERT', 'destination' => 'Kampala', 'status' => 'Pending'],
        ],
    ];
}

function buildVehiclesData(): array
{
    $vehicles = [
        ['reg' => 'UBD 456G', 'make' => 'Toyota', 'model' => 'Prado', 'year' => '2022', 'type' => 'Suv', 'department' => '-', 'mileage' => '0', 'insurance' => '-', 'repairs' => '-', 'status' => 'Maintenance'],
        ['reg' => 'UBR 123C', 'make' => 'TOYOTA', 'model' => 'Land cruiser', 'year' => '2024', 'type' => 'Suv', 'department' => 'DVD fa', 'mileage' => '852436', 'insurance' => '-', 'repairs' => '-', 'status' => 'Active'],
        ['reg' => 'UBR 402Q', 'make' => 'TOYOTA', 'model' => 'HILLUX PICKUP', 'year' => '2024', 'type' => 'Sedan', 'department' => 'University Secretary', 'mileage' => '65231', 'insurance' => '-', 'repairs' => '-', 'status' => 'Active'],
        ['reg' => 'UAJ 433X', 'make' => 'Ford', 'model' => 'Ford ranger', 'year' => '2009', 'type' => 'Pickup', 'department' => 'Estates', 'mileage' => '196002', 'insurance' => '-', 'repairs' => '-', 'status' => 'Active'],
        ['reg' => 'UBP 401F', 'make' => 'TOYOTA', 'model' => 'LAND CRUISER', 'year' => '2022', 'type' => 'Suv', 'department' => 'Vice Chancellor', 'mileage' => '200808', 'insurance' => '-', 'repairs' => '-', 'status' => 'Maintenance'],
    ];

    return ['vehicles' => $vehicles, 'hasVehicles' => count($vehicles) > 0];
}

function buildDriversData(): array
{
    $drivers = [
        ['name' => 'Simali Habert', 'email' => 'simalihabert@gmail.com', 'phone' => '+256 772 123 456', 'license' => 'CM 78452', 'assigned' => 'UAJ 433X', 'status' => 'Active'],
        ['name' => 'Moses Okello', 'email' => 'moses.okello@busitema.ac.ug', 'phone' => '+256 701 450 220', 'license' => 'CM 21984', 'assigned' => 'UBR 123C', 'status' => 'Active'],
        ['name' => 'Grace Namuli', 'email' => 'grace.namuli@busitema.ac.ug', 'phone' => '+256 758 802 114', 'license' => 'CM 66310', 'assigned' => '-', 'status' => 'Inactive'],
    ];

    return ['drivers' => $drivers, 'hasDrivers' => count($drivers) > 0];
}

function buildLogbookData(): array
{
    $logs = [
        ['date' => '17/05/2026', 'vehicle' => 'UAJ 433X', 'driver' => 'SIMALI HABERT', 'from' => 'Kampala', 'to' => 'Mbale', 'purpose' => 'Elgon', 'odo_start' => '196002', 'odo_end' => '196298', 'km' => '296', 'fuel' => '22', 'cost' => 'UGX 132,000', 'remarks' => 'Ok'],
        ['date' => '17/05/2026', 'vehicle' => 'UAJ 433X', 'driver' => 'SIMALI HABERT', 'from' => 'Busitema', 'to' => 'Kampala', 'purpose' => 'National council', 'odo_start' => '196002', 'odo_end' => '196312', 'km' => '310', 'fuel' => '20', 'cost' => 'UGX 125,600', 'remarks' => 'Ok'],
    ];

    return [
        'logs' => $logs,
        'hasLogs' => count($logs) > 0,
        'totalKm' => array_sum(array_map(static fn ($log) => (int) $log['km'], $logs)),
        'totalFuel' => array_sum(array_map(static fn ($log) => (int) $log['fuel'], $logs)),
        'totalCost' => 'UGX 257,600',
    ];
}

function buildMaintenanceData(): array
{
    $records = [
        ['date' => '18/05/2026', 'vehicle' => 'UAJ 433X', 'type' => 'Repair', 'description' => 'Engine over haul Brakes Windscreen ...', 'provider' => '-', 'cost' => 4200000, 'status' => 'Completed'],
    ];

    return [
        'records' => $records,
        'hasRecords' => count($records) > 0,
        'totalCost' => array_sum(array_map(static fn ($record) => (int) $record['cost'], $records)),
    ];
}

function buildReportsData(): array
{
    return [
        'summaryCards' => [
            ['label' => 'Total Maintenance Cost', 'value' => 'UGX 4,200,000', 'tone' => 'blue', 'icon' => 'W'],
            ['label' => 'Total Fuel Cost', 'value' => 'UGX 257,600', 'tone' => 'amber', 'icon' => 'F'],
            ['label' => 'Total Trips', 'value' => '2', 'tone' => 'green', 'icon' => 'T'],
            ['label' => 'Total Distance', 'value' => '606 km', 'tone' => 'blue', 'icon' => 'D'],
        ],
    ];
}

function buildPreInspectionData(): array
{
    $reports = [
        ['invoice' => '234', 'date' => '16/05/2026', 'vehicle' => 'UBP 401F', 'make_model' => 'TOYOTA LAND CRUISER', 'inspector' => 'Simali', 'overall' => '-', 'defects' => 'None'],
        ['invoice' => 'U2344', 'date' => '16/05/2026', 'vehicle' => 'UBP 401F', 'make_model' => 'TOYOTA LAND CRUISER', 'inspector' => 'Sh', 'overall' => 'Good', 'defects' => 'Cooling gfsf'],
        ['invoice' => 'U2344', 'date' => '16/05/2026', 'vehicle' => 'UBP 401F', 'make_model' => 'TOYOTA LAND CRUISER', 'inspector' => 'Sh', 'overall' => 'Good', 'defects' => 'Cooling gfsf'],
    ];

    return ['reports' => $reports, 'hasReports' => count($reports) > 0];
}

function buildPostInspectionData(): array
{
    $reports = [
        ['invoice' => 'I3455', 'date' => '16/05/2026', 'vehicle' => 'UBP 401F', 'make_model' => 'TOYOTA LAND CRUISER', 'inspector' => 'Sh', 'overall' => '-', 'post_invoice' => '-', 'repair_cost' => null],
        ['invoice' => 'U1234', 'date' => '16/05/2026', 'vehicle' => 'UBP 401F', 'make_model' => 'TOYOTA LAND CRUISER', 'inspector' => 'Simali habert', 'overall' => '-', 'post_invoice' => '-', 'repair_cost' => 2563896],
    ];

    return [
        'reports' => $reports,
        'postInspectionSystems' => [
            'Engine & Transmission',
            'Tyres & Wheels',
            'Braking System',
            'Lights (Head/Tail/Indicators)',
            'Body & Bodywork',
            'Fuel System',
            'Engine Oil',
            'Coolant / Radiator',
            'Battery & Electrical',
            'Windscreen & Wipers',
            'Mirrors',
            'Seatbelts & Safety',
        ],
        'hasReports' => count($reports) > 0,
        'totalRepairCost' => array_sum(array_map(static fn ($report) => (int) ($report['repair_cost'] ?? 0), $reports)),
    ];
}

function buildServiceProvidersData(): array
{
    $providers = [
        ['name' => 'Tororo Auto Garage', 'town' => 'Tororo', 'contact' => '+256 701 220 110', 'email' => 'service@tororoauto.ug', 'specialty' => 'General repairs', 'status' => 'Active'],
        ['name' => 'Toyota Uganda Service Centre', 'town' => 'Kampala', 'contact' => '+256 414 339 000', 'email' => 'fleetservice@toyota.co.ug', 'specialty' => 'Toyota service', 'status' => 'Active'],
        ['name' => 'Mbale Fleet Mechanics', 'town' => 'Mbale', 'contact' => '+256 772 431 980', 'email' => 'info@mbalefleet.ug', 'specialty' => 'Brakes and suspension', 'status' => 'Pending'],
    ];

    return ['providers' => $providers, 'hasProviders' => count($providers) > 0];
}

function buildCommunicationsData(): array
{
    $drivers = [];

    return ['drivers' => $drivers, 'hasDriverEmails' => count($drivers) > 0];
}

function buildCommunicationHistoryData(): array
{
    $messages = [
        ['datetime' => '18 May 2026, 16:31', 'subject' => 'Inspection', 'sender' => 'Simali Habert', 'drivers' => 1, 'officers' => 0, 'type' => 'Manual', 'message' => 'Inspection reminder sent to the assigned driver.'],
        ['datetime' => '17 May 2026, 12:20', 'subject' => 'Ghhhjj', 'sender' => 'Simali Habert', 'drivers' => 1, 'officers' => 0, 'type' => 'Manual', 'message' => 'Vehicle log follow-up message.'],
    ];

    return [
        'messages' => $messages,
        'hasMessages' => count($messages) > 0,
        'totalMessages' => count($messages),
        'driverEmails' => array_sum(array_column($messages, 'drivers')),
        'officerEmails' => array_sum(array_column($messages, 'officers')),
    ];
}

function buildEstatesData(): array
{
    $projects = [
        ['icon' => 'crane', 'name' => 'Library Extension Works', 'code' => 'BU-EST-001', 'location' => 'Main Campus - Library Block', 'contractor' => 'Eng. Sarah Namusoke', 'contractor_contact' => '+256 701 654321', 'start' => '01 Jul 2025', 'deadline' => '31 Dec 2025', 'funding' => 'Internal Funds', 'status' => 'In Progress', 'category' => 'Construction', 'priority' => 'High', 'progress' => 38, 'budget_used' => 38, 'spent' => 'UGX 320,000,000', 'budget' => 'UGX 850,000,000', 'remaining' => 'UGX 530,000,000', 'description' => 'Construction and furnishing of additional reading rooms, study spaces, and support offices for the main campus library.'],
        ['icon' => 'road', 'name' => 'Access Road Rehabilitation', 'code' => 'BU-EST-002', 'location' => 'Engineering Faculty Road', 'contractor' => 'Eng. David Ochen', 'contractor_contact' => '+256 701 654321', 'start' => '01 Jun 2025', 'deadline' => '30 Sep 2025', 'funding' => 'Internal Funds', 'status' => 'Approved', 'category' => 'Road Works', 'priority' => 'High', 'progress' => 0, 'budget_used' => 0, 'spent' => 'UGX 0', 'budget' => 'UGX 420,000,000', 'remaining' => 'UGX 420,000,000', 'description' => 'Rehabilitation and tarmacking of the 1.2km access road leading to the Faculty of Engineering and Applied Sciences.'],
        ['icon' => 'crane', 'name' => 'Student Hostel Block D', 'code' => 'BU-EST-003', 'location' => 'Student Village - Block D', 'contractor' => 'Eng. Grace Akello', 'contractor_contact' => '+256 772 888444', 'start' => '15 Oct 2024', 'deadline' => '31 Mar 2025', 'funding' => 'University Development Fund', 'status' => 'On Hold', 'category' => 'Construction', 'priority' => 'Medium', 'progress' => 55, 'budget_used' => 43, 'spent' => 'UGX 280,000,000', 'budget' => 'UGX 650,000,000', 'remaining' => 'UGX 370,000,000', 'description' => 'Development of additional student accommodation facilities at Student Village Block D.'],
        ['icon' => 'barrier', 'name' => 'Campus Perimeter Fence', 'code' => 'BU-EST-004', 'location' => 'Northern Boundary - Main Campus', 'contractor' => 'Mr. James Wafula', 'contractor_contact' => '+256 700 112233', 'start' => '12 May 2024', 'deadline' => '30 Nov 2024', 'funding' => 'Security Improvement Fund', 'status' => 'Completed', 'category' => 'Construction', 'priority' => 'Medium', 'progress' => 100, 'budget_used' => 93, 'spent' => 'UGX 88,000,000', 'budget' => 'UGX 95,000,000', 'remaining' => 'UGX 7,000,000', 'description' => 'Installation of the northern boundary perimeter fence around the main campus.'],
        ['icon' => 'bolt', 'name' => 'Electrical Rewiring Phase II', 'code' => 'BU-EST-005', 'location' => 'Administration Block', 'contractor' => 'Kampala Power Services', 'contractor_contact' => '+256 414 330900', 'start' => '05 May 2026', 'deadline' => '18 Aug 2026', 'funding' => 'Maintenance Vote', 'status' => 'Planned', 'category' => 'Electrical', 'priority' => 'Low', 'progress' => 0, 'budget_used' => 0, 'spent' => 'UGX 0', 'budget' => 'UGX 75,000,000', 'remaining' => 'UGX 75,000,000', 'description' => 'Electrical rewiring and safety upgrades for the Administration Block.'],
    ];

    return [
        'projects' => $projects,
        'statusClasses' => [
            'In Progress' => 'border-fleet-warning bg-fleet-warning-soft text-fleet-warning-strong',
            'Approved' => 'border-blue-300 bg-blue-100 text-fleet-primary',
            'On Hold' => 'border-orange-300 bg-orange-100 text-orange-700',
            'Completed' => 'border-green-300 bg-fleet-success-soft text-fleet-success',
            'Planned' => 'border-slate-300 bg-slate-100 text-slate-700',
        ],
        'priorityClasses' => [
            'High' => 'border-orange-300 bg-orange-50 text-orange-700',
            'Medium' => 'border-yellow-300 bg-yellow-50 text-yellow-700',
            'Low' => 'border-slate-300 bg-slate-50 text-slate-700',
        ],
    ];
}
