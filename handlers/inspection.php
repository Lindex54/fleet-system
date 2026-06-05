<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

// Inspection constants used by validation and display helpers.
const PRE_INSPECTION_ALLOWED_STATUSES = ['good', 'fair', 'faulty', 'needs_repair'];
const POST_INSPECTION_ALLOWED_STATUSES = ['good', 'fair', 'faulty', 'completed'];
const POST_INSPECTION_SYSTEMS = [
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
];

// Starts the session used for pre-inspection flash notifications if it is not already active.
function inspectionStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

// Returns the pre-inspection page URL used after redirects.
function inspectionPageUrl(): string
{
    return '/fleet-system/modules/inspections/index.php';
}

// Returns the post-inspection page URL used after redirects.
function postInspectionPageUrl(): string
{
    return '/fleet-system/modules/post-inspection/index.php';
}

// Returns the POST endpoint URL for pre-inspection form submissions.
function inspectionHandlerUrl(): string
{
    return '/fleet-system/handlers/inspection.php';
}

// Stores one-time pre-inspection feedback in session flash state.
function inspectionSetFlash(array $payload): void
{
    inspectionStartSession();
    $_SESSION['pre_inspection_flash'] = $payload;
}

// Stores one-time post-inspection feedback in session flash state.
function postInspectionSetFlash(array $payload): void
{
    inspectionStartSession();
    $_SESSION['post_inspection_flash'] = $payload;
}

// Pulls and clears one-time pre-inspection feedback from session flash state.
function inspectionPullFlash(): ?array
{
    inspectionStartSession();

    if (!isset($_SESSION['pre_inspection_flash']) || !is_array($_SESSION['pre_inspection_flash'])) {
        return null;
    }

    $flash = $_SESSION['pre_inspection_flash'];
    unset($_SESSION['pre_inspection_flash']);

    return $flash;
}

// Pulls and clears one-time post-inspection feedback from session flash state.
function postInspectionPullFlash(): ?array
{
    inspectionStartSession();

    if (!isset($_SESSION['post_inspection_flash']) || !is_array($_SESSION['post_inspection_flash'])) {
        return null;
    }

    $flash = $_SESSION['post_inspection_flash'];
    unset($_SESSION['post_inspection_flash']);

    return $flash;
}

// Converts stored inspection status values into table-friendly labels.
function inspectionNormalizeStatus(?string $status): string
{
    if ($status === null || $status === '') {
        return '--';
    }

    return match ($status) {
        'needs_repair' => 'Needs Repair',
        default => ucwords(str_replace('_', ' ', $status)),
    };
}

// Formats stored inspection dates for table display.
function inspectionFormatDate(?string $date): string
{
    if ($date === null || $date === '') {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y', $timestamp) : $date;
}

// Loads current vehicle options for the pre-inspection modal dropdown.
function inspectionFetchVehicleOptions(PDO $pdo): array
{
    $statement = $pdo->query(
        "SELECT id, registration_no, make, model, current_mileage
        FROM vehicles
        WHERE status <> 'disposed'
        ORDER BY registration_no ASC"
    );

    return $statement->fetchAll();
}

// Loads current service provider options for the post-inspection modal dropdown.
function postInspectionFetchProviderOptions(PDO $pdo): array
{
    $statement = $pdo->query(
        "SELECT id, name
        FROM service_providers
        WHERE status <> 'inactive'
        ORDER BY name ASC"
    );

    return $statement->fetchAll();
}

// Groups inspection item rows by their parent inspection id for easier page rendering.
function inspectionFetchItemsByInspection(PDO $pdo, array $inspectionIds): array
{
    if ($inspectionIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($inspectionIds), '?'));
    $statement = $pdo->prepare(
        "SELECT inspection_id, inspection_point, findings, action_point
        FROM inspection_items
        WHERE inspection_id IN ($placeholders)
        ORDER BY id ASC"
    );
    $statement->execute($inspectionIds);

    $itemsByInspection = [];

    foreach ($statement->fetchAll() as $row) {
        $inspectionId = (int) $row['inspection_id'];
        $itemsByInspection[$inspectionId][] = [
            'inspection_point' => $row['inspection_point'],
            'inspection_findings' => $row['findings'] ?? '',
            'inspection_action' => $row['action_point'] ?? '',
        ];
    }

    return $itemsByInspection;
}

// Builds one default empty inspection item row so the modal always has editable fields.
function inspectionDefaultItemRows(): array
{
    return [[
        'inspection_point' => '',
        'inspection_findings' => '',
        'inspection_action' => '',
    ]];
}

// Loads pre-inspection rows, vehicle options, and flash state for the page.
function inspectionFetchPageData(): array
{
    $flash = inspectionPullFlash();
    $notification = $flash['notification'] ?? null;
    $formData = $flash['form_data'] ?? [];
    $openModal = (bool) ($flash['open_modal'] ?? false);
    $formMode = $flash['form_mode'] ?? 'create';

    if (!isset($formData['inspection_point']) || !is_array($formData['inspection_point']) || $formData['inspection_point'] === []) {
        $formData['inspection_point'] = [''];
        $formData['inspection_findings'] = [''];
        $formData['inspection_action'] = [''];
    }

    $reports = [];
    $vehicleOptions = [];

    try {
        $pdo = fleetDb();
        $vehicleOptions = inspectionFetchVehicleOptions($pdo);
        $statement = $pdo->query(
            "SELECT
                i.id,
                i.vehicle_id,
                i.invoice_number,
                i.inspection_date,
                i.inspector_name,
                i.inspector_title,
                i.mileage,
                i.overall_status,
                i.defects,
                i.memo_to,
                i.memo_thru_one,
                i.memo_thru_two,
                i.memo_from,
                i.vehicle_description,
                i.closing_note,
                i.cc,
                v.registration_no,
                v.make,
                v.model
            FROM inspections i
            INNER JOIN vehicles v ON v.id = i.vehicle_id
            WHERE i.inspection_type = 'pre'
            ORDER BY i.inspection_date DESC, i.id DESC"
        );

        $rows = $statement->fetchAll();
        $reportIds = array_map(static fn (array $row): int => (int) $row['id'], $rows);
        $itemsByInspection = inspectionFetchItemsByInspection($pdo, $reportIds);

        foreach ($rows as $row) {
            $reportId = (int) $row['id'];
            $items = $itemsByInspection[$reportId] ?? [];
            $defects = $row['defects'] ?: 'None';

            if ($defects === 'None' && $items !== []) {
                $findings = array_filter(array_map(
                    static fn (array $item): string => trim((string) $item['inspection_findings']),
                    $items
                ));
                $defects = $findings !== [] ? implode('; ', $findings) : 'None';
            }

            $reports[] = [
                'id' => $reportId,
                'vehicle_id' => (int) $row['vehicle_id'],
                'invoice_raw' => $row['invoice_number'] ?? '',
                'date_raw' => $row['inspection_date'],
                'inspector_raw' => $row['inspector_name'],
                'inspector_title_raw' => $row['inspector_title'] ?? '',
                'mileage_raw' => $row['mileage'] !== null ? (string) $row['mileage'] : '',
                'overall_raw' => $row['overall_status'] ?? '',
                'defects_raw' => $row['defects'] ?? '',
                'memo_to_raw' => $row['memo_to'] ?? '',
                'memo_thru_one_raw' => $row['memo_thru_one'] ?? '',
                'memo_thru_two_raw' => $row['memo_thru_two'] ?? '',
                'memo_from_raw' => $row['memo_from'] ?? '',
                'vehicle_description_raw' => $row['vehicle_description'] ?? '',
                'closing_note_raw' => $row['closing_note'] ?? '',
                'cc_raw' => $row['cc'] ?? '',
                'items_json' => json_encode($items, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_TAG | JSON_HEX_QUOT) ?: '[]',
                'invoice' => $row['invoice_number'] ?: '-',
                'date' => inspectionFormatDate($row['inspection_date']),
                'vehicle' => $row['registration_no'],
                'make_model' => trim($row['make'] . ' ' . $row['model']),
                'inspector' => $row['inspector_name'],
                'overall' => inspectionNormalizeStatus($row['overall_status']),
                'defects' => $defects,
            ];
        }
    } catch (Throwable $exception) {
        $notification = [
            'type' => 'error',
            'title' => 'Unable to load pre-inspection reports',
            'message' => 'The pre-inspection reports could not be loaded from the database right now.',
        ];
    }

    return [
        'reports' => $reports,
        'hasReports' => count($reports) > 0,
        'preInspectionNotification' => $notification,
        'preInspectionFormData' => $formData,
        'shouldOpenPreInspectionModal' => $openModal,
        'preInspectionFormMode' => $formMode,
        'preInspectionFormAction' => inspectionHandlerUrl(),
        'preInspectionVehicleOptions' => $vehicleOptions,
        'preInspectionItemRows' => inspectionBuildItemRowsFromFormData($formData),
    ];
}

// Rebuilds posted inspection item arrays into aligned rows for modal rendering.
function inspectionBuildItemRowsFromFormData(array $formData): array
{
    $points = isset($formData['inspection_point']) && is_array($formData['inspection_point']) ? $formData['inspection_point'] : [];
    $findings = isset($formData['inspection_findings']) && is_array($formData['inspection_findings']) ? $formData['inspection_findings'] : [];
    $actions = isset($formData['inspection_action']) && is_array($formData['inspection_action']) ? $formData['inspection_action'] : [];
    $rowCount = max(count($points), count($findings), count($actions));

    if ($rowCount === 0) {
        return inspectionDefaultItemRows();
    }

    $rows = [];

    for ($index = 0; $index < $rowCount; $index++) {
        $rows[] = [
            'inspection_point' => trim((string) ($points[$index] ?? '')),
            'inspection_findings' => trim((string) ($findings[$index] ?? '')),
            'inspection_action' => trim((string) ($actions[$index] ?? '')),
        ];
    }

    return $rows;
}

// Loads post-inspection rows, dropdown options, system checks, and flash state for the page.
function postInspectionFetchPageData(): array
{
    $flash = postInspectionPullFlash();
    $notification = $flash['notification'] ?? null;
    $formData = $flash['form_data'] ?? [];
    $openModal = (bool) ($flash['open_modal'] ?? false);
    $formMode = $flash['form_mode'] ?? 'create';

    if (!isset($formData['system_name']) || !is_array($formData['system_name']) || $formData['system_name'] === []) {
        $formData['system_name'] = POST_INSPECTION_SYSTEMS;
    }

    $reports = [];
    $vehicleOptions = [];
    $providerOptions = [];
    $totalRepairCost = 0.0;

    try {
        $pdo = fleetDb();
        $vehicleOptions = inspectionFetchVehicleOptions($pdo);
        $providerOptions = postInspectionFetchProviderOptions($pdo);
        $statement = $pdo->query(
            "SELECT
                i.id,
                i.vehicle_id,
                i.service_provider_id,
                i.invoice_number,
                i.post_invoice_number,
                i.inspection_date,
                i.inspector_name,
                i.inspector_title,
                i.mileage,
                i.overall_status,
                i.works_done,
                i.repair_cost,
                i.recommendation,
                v.registration_no,
                v.make,
                v.model,
                sp.name AS provider_name
            FROM inspections i
            INNER JOIN vehicles v ON v.id = i.vehicle_id
            LEFT JOIN service_providers sp ON sp.id = i.service_provider_id
            WHERE i.inspection_type = 'post'
            ORDER BY i.inspection_date DESC, i.id DESC"
        );

        $rows = $statement->fetchAll();
        $reportIds = array_map(static fn (array $row): int => (int) $row['id'], $rows);
        $systemChecksByInspection = postInspectionFetchSystemChecksByInspection($pdo, $reportIds);

        foreach ($rows as $row) {
            $reportId = (int) $row['id'];
            $repairCost = (float) ($row['repair_cost'] ?? 0);
            $reports[] = [
                'id' => $reportId,
                'vehicle_id' => (int) $row['vehicle_id'],
                'service_provider_id' => $row['service_provider_id'] !== null ? (int) $row['service_provider_id'] : null,
                'invoice_raw' => $row['invoice_number'] ?? '',
                'post_invoice_raw' => $row['post_invoice_number'] ?? '',
                'date_raw' => $row['inspection_date'],
                'inspector_raw' => $row['inspector_name'],
                'inspector_title_raw' => $row['inspector_title'] ?? '',
                'mileage_raw' => $row['mileage'] !== null ? (string) $row['mileage'] : '',
                'overall_raw' => $row['overall_status'] ?? '',
                'works_done_raw' => $row['works_done'] ?? '',
                'repair_cost_raw' => (string) $repairCost,
                'recommendation_raw' => $row['recommendation'] ?? '',
                'system_checks_json' => json_encode($systemChecksByInspection[$reportId] ?? [], JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_TAG | JSON_HEX_QUOT) ?: '[]',
                'invoice' => $row['invoice_number'] ?: '-',
                'date' => inspectionFormatDate($row['inspection_date']),
                'vehicle' => $row['registration_no'],
                'make_model' => trim($row['make'] . ' ' . $row['model']),
                'inspector' => $row['inspector_name'],
                'overall' => inspectionNormalizeStatus($row['overall_status']),
                'post_invoice' => $row['post_invoice_number'] ?: '-',
                'repair_cost' => $repairCost,
                'provider_name' => $row['provider_name'] ?: '',
            ];
            $totalRepairCost += $repairCost;
        }
    } catch (Throwable $exception) {
        $notification = [
            'type' => 'error',
            'title' => 'Unable to load post-inspection reports',
            'message' => 'The post-inspection reports could not be loaded from the database right now.',
        ];
    }

    return [
        'reports' => $reports,
        'hasReports' => count($reports) > 0,
        'totalRepairCost' => $totalRepairCost,
        'postInspectionNotification' => $notification,
        'postInspectionFormData' => $formData,
        'shouldOpenPostInspectionModal' => $openModal,
        'postInspectionFormMode' => $formMode,
        'postInspectionFormAction' => inspectionHandlerUrl(),
        'postInspectionVehicleOptions' => $vehicleOptions,
        'postInspectionProviderOptions' => $providerOptions,
        'postInspectionSystems' => POST_INSPECTION_SYSTEMS,
    ];
}

// Loads saved post-inspection system checks grouped by their parent inspection id.
function postInspectionFetchSystemChecksByInspection(PDO $pdo, array $inspectionIds): array
{
    if ($inspectionIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($inspectionIds), '?'));
    $statement = $pdo->prepare(
        "SELECT inspection_id, system_name, condition_status, remarks
        FROM post_inspection_system_checks
        WHERE inspection_id IN ($placeholders)
        ORDER BY id ASC"
    );
    $statement->execute($inspectionIds);

    $checksByInspection = [];

    foreach ($statement->fetchAll() as $row) {
        $inspectionId = (int) $row['inspection_id'];
        $checksByInspection[$inspectionId][] = [
            'system_name' => $row['system_name'],
            'condition_status' => $row['condition_status'],
            'remarks' => $row['remarks'] ?? '',
        ];
    }

    return $checksByInspection;
}

// Collects and trims raw POST values from the pre-inspection form.
function inspectionBuildFormDataFromPost(): array
{
    $points = $_POST['inspection_point'] ?? [];
    $findings = $_POST['inspection_findings'] ?? [];
    $actions = $_POST['inspection_action'] ?? [];

    return [
        'report_id' => trim((string) ($_POST['report_id'] ?? '')),
        'invoice_number' => trim((string) ($_POST['invoice_number'] ?? '')),
        'inspection_date' => trim((string) ($_POST['inspection_date'] ?? '')),
        'inspector_name' => trim((string) ($_POST['inspector_name'] ?? '')),
        'inspector_title' => trim((string) ($_POST['inspector_title'] ?? '')),
        'vehicle' => trim((string) ($_POST['vehicle'] ?? '')),
        'mileage' => trim((string) ($_POST['mileage'] ?? '')),
        'overall_status' => strtolower(trim((string) ($_POST['overall_status'] ?? ''))),
        'defects' => trim((string) ($_POST['defects'] ?? '')),
        'memo_to' => trim((string) ($_POST['memo_to'] ?? '')),
        'memo_thru_one' => trim((string) ($_POST['memo_thru_one'] ?? '')),
        'memo_thru_two' => trim((string) ($_POST['memo_thru_two'] ?? '')),
        'memo_from' => trim((string) ($_POST['memo_from'] ?? '')),
        'vehicle_description' => trim((string) ($_POST['vehicle_description'] ?? '')),
        'closing_note' => trim((string) ($_POST['closing_note'] ?? '')),
        'cc' => trim((string) ($_POST['cc'] ?? '')),
        'inspection_point' => array_map(static fn ($value): string => trim((string) $value), is_array($points) ? $points : []),
        'inspection_findings' => array_map(static fn ($value): string => trim((string) $value), is_array($findings) ? $findings : []),
        'inspection_action' => array_map(static fn ($value): string => trim((string) $value), is_array($actions) ? $actions : []),
    ];
}

// Collects and trims raw POST values from the post-inspection form.
function postInspectionBuildFormDataFromPost(): array
{
    return [
        'report_id' => trim((string) ($_POST['report_id'] ?? '')),
        'invoice_number' => trim((string) ($_POST['invoice_number'] ?? '')),
        'inspection_date' => trim((string) ($_POST['inspection_date'] ?? '')),
        'inspector_name' => trim((string) ($_POST['inspector_name'] ?? '')),
        'inspector_title' => trim((string) ($_POST['inspector_title'] ?? '')),
        'vehicle' => trim((string) ($_POST['vehicle'] ?? '')),
        'mileage' => trim((string) ($_POST['mileage'] ?? '')),
        'overall_status' => strtolower(trim((string) ($_POST['overall_status'] ?? ''))),
        'works_done' => trim((string) ($_POST['works_done'] ?? '')),
        'post_invoice' => trim((string) ($_POST['post_invoice'] ?? '')),
        'amount_spent' => trim((string) ($_POST['amount_spent'] ?? '')),
        'service_provider' => trim((string) ($_POST['service_provider'] ?? '')),
        'recommendation' => trim((string) ($_POST['recommendation'] ?? '')),
        'system_name' => array_map(static fn ($value): string => trim((string) $value), is_array($_POST['system_name'] ?? null) ? $_POST['system_name'] : []),
        'system_status' => array_map(static fn ($value): string => strtolower(trim((string) $value)), is_array($_POST['system_status'] ?? null) ? $_POST['system_status'] : []),
        'system_remarks' => array_map(static fn ($value): string => trim((string) $value), is_array($_POST['system_remarks'] ?? null) ? $_POST['system_remarks'] : []),
    ];
}

// Validates and normalizes posted inspection item rows before saving them.
function inspectionNormalizeItems(array $formData): array
{
    $rows = inspectionBuildItemRowsFromFormData($formData);
    $items = [];

    foreach ($rows as $row) {
        $point = trim($row['inspection_point']);
        $findings = trim($row['inspection_findings']);
        $action = trim($row['inspection_action']);

        if ($point === '' && $findings === '' && $action === '') {
            continue;
        }

        if ($point === '') {
            throw new RuntimeException('Each inspection item needs an inspection point.');
        }

        $items[] = [
            'inspection_point' => $point,
            'findings' => $findings === '' ? null : $findings,
            'action_point' => $action === '' ? null : $action,
        ];
    }

    return $items;
}

// Rebuilds posted post-inspection system rows into aligned rows for modal rendering.
function postInspectionBuildSystemRowsFromFormData(array $formData): array
{
    $names = isset($formData['system_name']) && is_array($formData['system_name']) ? $formData['system_name'] : [];
    $statuses = isset($formData['system_status']) && is_array($formData['system_status']) ? $formData['system_status'] : [];
    $remarks = isset($formData['system_remarks']) && is_array($formData['system_remarks']) ? $formData['system_remarks'] : [];
    $rowCount = max(count($names), count($statuses), count($remarks));
    $rows = [];

    if ($rowCount === 0) {
        foreach (POST_INSPECTION_SYSTEMS as $systemName) {
            $rows[] = [
                'system_name' => $systemName,
                'condition_status' => '',
                'remarks' => '',
            ];
        }

        return $rows;
    }

    for ($index = 0; $index < $rowCount; $index++) {
        $rows[] = [
            'system_name' => trim((string) ($names[$index] ?? '')),
            'condition_status' => strtolower(trim((string) ($statuses[$index] ?? ''))),
            'remarks' => trim((string) ($remarks[$index] ?? '')),
        ];
    }

    return $rows;
}

// Validates and normalizes posted post-inspection system checks before saving them.
function postInspectionNormalizeSystemChecks(array $formData): array
{
    $rows = postInspectionBuildSystemRowsFromFormData($formData);
    $checks = [];

    foreach ($rows as $row) {
        $systemName = trim($row['system_name']);
        $status = trim($row['condition_status']);
        $remarks = trim($row['remarks']);

        if ($systemName === '') {
            continue;
        }

        if ($remarks !== '' && $status === '') {
            throw new RuntimeException('Please choose a condition for every system check that has remarks.');
        }

        if ($status !== '' && !in_array($status, ['good', 'fair', 'faulty'], true)) {
            throw new RuntimeException('Please select valid system condition values.');
        }

        $checks[] = [
            'system_name' => $systemName,
            'condition_status' => $status === '' ? null : $status,
            'remarks' => $remarks === '' ? null : $remarks,
        ];
    }

    return $checks;
}

// Validates and normalizes submitted pre-inspection form values.
function inspectionValidateFormData(array $formData): array
{
    if ($formData['invoice_number'] === '' || $formData['inspection_date'] === '' || $formData['inspector_name'] === '' || $formData['vehicle'] === '') {
        throw new RuntimeException('Invoice number, inspection date, inspector name, and vehicle are required.');
    }

    $vehicleId = filter_var($formData['vehicle'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($vehicleId === false) {
        throw new RuntimeException('Please select a valid vehicle.');
    }

    $inspectionDate = DateTimeImmutable::createFromFormat('Y-m-d', $formData['inspection_date']);
    $dateErrors = DateTimeImmutable::getLastErrors();
    if (!$inspectionDate || ($dateErrors['warning_count'] ?? 0) > 0 || ($dateErrors['error_count'] ?? 0) > 0) {
        throw new RuntimeException('Please enter a valid inspection date.');
    }

    $mileage = null;
    if ($formData['mileage'] !== '') {
        $mileage = filter_var($formData['mileage'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($mileage === false) {
            throw new RuntimeException('Please enter a valid mileage.');
        }
    }

    if ($formData['overall_status'] !== '' && !in_array($formData['overall_status'], PRE_INSPECTION_ALLOWED_STATUSES, true)) {
        throw new RuntimeException('Please select a valid overall status.');
    }

    return [
        'vehicle_id' => (int) $vehicleId,
        'invoice_number' => $formData['invoice_number'],
        'inspection_date' => $inspectionDate->format('Y-m-d'),
        'inspector_name' => $formData['inspector_name'],
        'inspector_title' => $formData['inspector_title'] === '' ? null : $formData['inspector_title'],
        'mileage' => $mileage === null ? null : (int) $mileage,
        'overall_status' => $formData['overall_status'] === '' ? null : $formData['overall_status'],
        'defects' => $formData['defects'] === '' ? null : $formData['defects'],
        'memo_to' => $formData['memo_to'] === '' ? null : $formData['memo_to'],
        'memo_thru_one' => $formData['memo_thru_one'] === '' ? null : $formData['memo_thru_one'],
        'memo_thru_two' => $formData['memo_thru_two'] === '' ? null : $formData['memo_thru_two'],
        'memo_from' => $formData['memo_from'] === '' ? null : $formData['memo_from'],
        'vehicle_description' => $formData['vehicle_description'] === '' ? null : $formData['vehicle_description'],
        'closing_note' => $formData['closing_note'] === '' ? null : $formData['closing_note'],
        'cc' => $formData['cc'] === '' ? null : $formData['cc'],
        'items' => inspectionNormalizeItems($formData),
    ];
}

// Validates and normalizes submitted post-inspection form values.
function postInspectionValidateFormData(array $formData): array
{
    if ($formData['invoice_number'] === '' || $formData['inspection_date'] === '' || $formData['inspector_name'] === '' || $formData['vehicle'] === '') {
        throw new RuntimeException('Invoice number, inspection date, inspector name, and vehicle are required.');
    }

    $vehicleId = filter_var($formData['vehicle'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($vehicleId === false) {
        throw new RuntimeException('Please select a valid vehicle.');
    }

    $inspectionDate = DateTimeImmutable::createFromFormat('Y-m-d', $formData['inspection_date']);
    $dateErrors = DateTimeImmutable::getLastErrors();
    if (!$inspectionDate || ($dateErrors['warning_count'] ?? 0) > 0 || ($dateErrors['error_count'] ?? 0) > 0) {
        throw new RuntimeException('Please enter a valid inspection date.');
    }

    $mileage = null;
    if ($formData['mileage'] !== '') {
        $mileage = filter_var($formData['mileage'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($mileage === false) {
            throw new RuntimeException('Please enter a valid mileage.');
        }
    }

    $repairCost = 0.0;
    if ($formData['amount_spent'] !== '') {
        $repairCost = filter_var($formData['amount_spent'], FILTER_VALIDATE_FLOAT);
        if ($repairCost === false || $repairCost < 0) {
            throw new RuntimeException('Please enter a valid repair cost.');
        }
    }

    $serviceProviderId = null;
    if ($formData['service_provider'] !== '') {
        $serviceProviderId = filter_var($formData['service_provider'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($serviceProviderId === false) {
            throw new RuntimeException('Please select a valid service provider.');
        }
    }

    if ($formData['overall_status'] !== '' && !in_array($formData['overall_status'], POST_INSPECTION_ALLOWED_STATUSES, true)) {
        throw new RuntimeException('Please select a valid overall status.');
    }

    return [
        'vehicle_id' => (int) $vehicleId,
        'invoice_number' => $formData['invoice_number'],
        'inspection_date' => $inspectionDate->format('Y-m-d'),
        'inspector_name' => $formData['inspector_name'],
        'inspector_title' => $formData['inspector_title'] === '' ? null : $formData['inspector_title'],
        'mileage' => $mileage === null ? null : (int) $mileage,
        'overall_status' => $formData['overall_status'] === '' ? null : $formData['overall_status'],
        'works_done' => $formData['works_done'] === '' ? null : $formData['works_done'],
        'post_invoice_number' => $formData['post_invoice'] === '' ? null : $formData['post_invoice'],
        'repair_cost' => (float) $repairCost,
        'service_provider_id' => $serviceProviderId === null ? null : (int) $serviceProviderId,
        'recommendation' => $formData['recommendation'] === '' ? null : $formData['recommendation'],
        'system_checks' => postInspectionNormalizeSystemChecks($formData),
    ];
}

// Confirms the selected vehicle still exists before saving the inspection.
function inspectionAssertForeignKeysExist(PDO $pdo, int $vehicleId): void
{
    $vehicleExists = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE id = :id AND status <> 'disposed'");
    $vehicleExists->execute(['id' => $vehicleId]);

    if ((int) $vehicleExists->fetchColumn() === 0) {
        throw new RuntimeException('The selected vehicle no longer exists.');
    }
}

// Confirms the selected service provider still exists before saving the post-inspection report.
function postInspectionAssertForeignKeysExist(PDO $pdo, int $vehicleId, ?int $serviceProviderId): void
{
    inspectionAssertForeignKeysExist($pdo, $vehicleId);

    if ($serviceProviderId !== null) {
        $providerExists = $pdo->prepare('SELECT COUNT(*) FROM service_providers WHERE id = :id');
        $providerExists->execute(['id' => $serviceProviderId]);

        if ((int) $providerExists->fetchColumn() === 0) {
            throw new RuntimeException('The selected service provider no longer exists.');
        }
    }
}

// Persists the inspection item list for one parent pre-inspection report.
function inspectionSaveItems(PDO $pdo, int $inspectionId, array $items): void
{
    $pdo->prepare('DELETE FROM inspection_items WHERE inspection_id = :inspection_id')
        ->execute(['inspection_id' => $inspectionId]);

    if ($items === []) {
        return;
    }

    $statement = $pdo->prepare(
        'INSERT INTO inspection_items (inspection_id, inspection_point, findings, action_point)
        VALUES (:inspection_id, :inspection_point, :findings, :action_point)'
    );

    foreach ($items as $item) {
        $statement->bindValue(':inspection_id', $inspectionId, PDO::PARAM_INT);
        $statement->bindValue(':inspection_point', $item['inspection_point']);
        $statement->bindValue(':findings', $item['findings'], $item['findings'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':action_point', $item['action_point'], $item['action_point'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->execute();
    }
}

// Persists the post-inspection system checklist for one parent report.
function postInspectionSaveSystemChecks(PDO $pdo, int $inspectionId, array $systemChecks): void
{
    $pdo->prepare('DELETE FROM post_inspection_system_checks WHERE inspection_id = :inspection_id')
        ->execute(['inspection_id' => $inspectionId]);

    if ($systemChecks === []) {
        return;
    }

    $statement = $pdo->prepare(
        'INSERT INTO post_inspection_system_checks (inspection_id, system_name, condition_status, remarks)
        VALUES (:inspection_id, :system_name, :condition_status, :remarks)'
    );

    foreach ($systemChecks as $check) {
        if ($check['condition_status'] === null && $check['remarks'] === null) {
            continue;
        }

        $statement->bindValue(':inspection_id', $inspectionId, PDO::PARAM_INT);
        $statement->bindValue(':system_name', $check['system_name']);
        $statement->bindValue(':condition_status', $check['condition_status'], $check['condition_status'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':remarks', $check['remarks'], $check['remarks'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->execute();
    }
}

// Handles both create and update requests for pre-inspection reports.
function inspectionHandleCreateOrUpdate(string $action): void
{
    $formData = inspectionBuildFormDataFromPost();

    try {
        $validated = inspectionValidateFormData($formData);
        $pdo = fleetDb();
        inspectionAssertForeignKeysExist($pdo, $validated['vehicle_id']);
        $pdo->beginTransaction();

        if ($action === 'update') {
            $reportId = filter_var($formData['report_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($reportId === false) {
                throw new RuntimeException('The selected pre-inspection report could not be identified.');
            }

            $exists = $pdo->prepare("SELECT COUNT(*) FROM inspections WHERE id = :id AND inspection_type = 'pre'");
            $exists->execute(['id' => $reportId]);
            if ((int) $exists->fetchColumn() === 0) {
                throw new RuntimeException('The selected pre-inspection report no longer exists.');
            }

            // Updates keep the same report row while replacing the latest editable values.
            $statement = $pdo->prepare(
                "UPDATE inspections SET
                    vehicle_id = :vehicle_id,
                    invoice_number = :invoice_number,
                    inspection_date = :inspection_date,
                    inspector_name = :inspector_name,
                    inspector_title = :inspector_title,
                    mileage = :mileage,
                    overall_status = :overall_status,
                    defects = :defects,
                    memo_to = :memo_to,
                    memo_thru_one = :memo_thru_one,
                    memo_thru_two = :memo_thru_two,
                    memo_from = :memo_from,
                    vehicle_description = :vehicle_description,
                    closing_note = :closing_note,
                    cc = :cc
                WHERE id = :report_id AND inspection_type = 'pre'"
            );
            $statement->bindValue(':report_id', (int) $reportId, PDO::PARAM_INT);
        } else {
            // New pre-inspection reports are inserted first, then their item rows are attached.
            $statement = $pdo->prepare(
                "INSERT INTO inspections (
                    vehicle_id,
                    inspection_type,
                    invoice_number,
                    inspection_date,
                    inspector_name,
                    inspector_title,
                    mileage,
                    overall_status,
                    defects,
                    memo_to,
                    memo_thru_one,
                    memo_thru_two,
                    memo_from,
                    vehicle_description,
                    closing_note,
                    cc
                ) VALUES (
                    :vehicle_id,
                    'pre',
                    :invoice_number,
                    :inspection_date,
                    :inspector_name,
                    :inspector_title,
                    :mileage,
                    :overall_status,
                    :defects,
                    :memo_to,
                    :memo_thru_one,
                    :memo_thru_two,
                    :memo_from,
                    :vehicle_description,
                    :closing_note,
                    :cc
                )"
            );
        }

        $statement->bindValue(':vehicle_id', $validated['vehicle_id'], PDO::PARAM_INT);
        $statement->bindValue(':invoice_number', $validated['invoice_number']);
        $statement->bindValue(':inspection_date', $validated['inspection_date']);
        $statement->bindValue(':inspector_name', $validated['inspector_name']);
        $statement->bindValue(':inspector_title', $validated['inspector_title'], $validated['inspector_title'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':mileage', $validated['mileage'], $validated['mileage'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':overall_status', $validated['overall_status'], $validated['overall_status'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':defects', $validated['defects'], $validated['defects'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':memo_to', $validated['memo_to'], $validated['memo_to'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':memo_thru_one', $validated['memo_thru_one'], $validated['memo_thru_one'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':memo_thru_two', $validated['memo_thru_two'], $validated['memo_thru_two'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':memo_from', $validated['memo_from'], $validated['memo_from'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':vehicle_description', $validated['vehicle_description'], $validated['vehicle_description'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':closing_note', $validated['closing_note'], $validated['closing_note'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':cc', $validated['cc'], $validated['cc'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->execute();

        $inspectionId = $action === 'update' ? (int) $reportId : (int) $pdo->lastInsertId();
        inspectionSaveItems($pdo, $inspectionId, $validated['items']);
        $pdo->commit();

        inspectionSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => $action === 'update' ? 'Pre-inspection report updated successfully' : 'Pre-inspection report added successfully',
                'message' => $action === 'update'
                    ? 'The pre-inspection report has been updated successfully.'
                    : 'The pre-inspection report has been saved successfully.',
            ],
        ]);
    } catch (RuntimeException $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        inspectionSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Pre-inspection report was not updated' : 'Pre-inspection report was not added',
                'message' => $exception->getMessage(),
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        inspectionSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Pre-inspection report was not updated' : 'Pre-inspection report was not added',
                'message' => $action === 'update'
                    ? 'A system error occurred while updating the pre-inspection report.'
                    : 'A system error occurred while saving the pre-inspection report.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
    }

    header('Location: ' . inspectionPageUrl());
    exit;
}

// Handles delete requests for pre-inspection reports.
function inspectionHandleDelete(): void
{
    $reportId = filter_var((string) ($_POST['report_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($reportId === false) {
        inspectionSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Pre-inspection report was not deleted',
                'message' => 'The selected pre-inspection report could not be identified.',
            ],
        ]);
        header('Location: ' . inspectionPageUrl());
        exit;
    }

    try {
        $statement = fleetDb()->prepare("DELETE FROM inspections WHERE id = :id AND inspection_type = 'pre'");
        $statement->execute(['id' => $reportId]);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('The selected pre-inspection report no longer exists.');
        }

        inspectionSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Pre-inspection report deleted successfully',
                'message' => 'The selected pre-inspection report has been removed.',
            ],
        ]);
    } catch (RuntimeException $exception) {
        inspectionSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Pre-inspection report was not deleted',
                'message' => $exception->getMessage(),
            ],
        ]);
    } catch (Throwable $exception) {
        inspectionSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Pre-inspection report was not deleted',
                'message' => 'A system error occurred while deleting the pre-inspection report.',
            ],
        ]);
    }

    header('Location: ' . inspectionPageUrl());
    exit;
}

// Handles both create and update requests for post-inspection reports.
function postInspectionHandleCreateOrUpdate(string $action): void
{
    $formData = postInspectionBuildFormDataFromPost();

    try {
        $validated = postInspectionValidateFormData($formData);
        $pdo = fleetDb();
        postInspectionAssertForeignKeysExist($pdo, $validated['vehicle_id'], $validated['service_provider_id']);
        $pdo->beginTransaction();

        if ($action === 'update') {
            $reportId = filter_var($formData['report_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($reportId === false) {
                throw new RuntimeException('The selected post-inspection report could not be identified.');
            }

            $exists = $pdo->prepare("SELECT COUNT(*) FROM inspections WHERE id = :id AND inspection_type = 'post'");
            $exists->execute(['id' => $reportId]);
            if ((int) $exists->fetchColumn() === 0) {
                throw new RuntimeException('The selected post-inspection report no longer exists.');
            }

            // Updates keep the same report row while replacing the latest editable values.
            $statement = $pdo->prepare(
                "UPDATE inspections SET
                    vehicle_id = :vehicle_id,
                    service_provider_id = :service_provider_id,
                    invoice_number = :invoice_number,
                    post_invoice_number = :post_invoice_number,
                    inspection_date = :inspection_date,
                    inspector_name = :inspector_name,
                    inspector_title = :inspector_title,
                    mileage = :mileage,
                    overall_status = :overall_status,
                    works_done = :works_done,
                    repair_cost = :repair_cost,
                    recommendation = :recommendation
                WHERE id = :report_id AND inspection_type = 'post'"
            );
            $statement->bindValue(':report_id', (int) $reportId, PDO::PARAM_INT);
        } else {
            // New post-inspection reports are inserted first, then their system checks are attached.
            $statement = $pdo->prepare(
                "INSERT INTO inspections (
                    vehicle_id,
                    service_provider_id,
                    inspection_type,
                    invoice_number,
                    post_invoice_number,
                    inspection_date,
                    inspector_name,
                    inspector_title,
                    mileage,
                    overall_status,
                    works_done,
                    repair_cost,
                    recommendation
                ) VALUES (
                    :vehicle_id,
                    :service_provider_id,
                    'post',
                    :invoice_number,
                    :post_invoice_number,
                    :inspection_date,
                    :inspector_name,
                    :inspector_title,
                    :mileage,
                    :overall_status,
                    :works_done,
                    :repair_cost,
                    :recommendation
                )"
            );
        }

        $statement->bindValue(':vehicle_id', $validated['vehicle_id'], PDO::PARAM_INT);
        $statement->bindValue(':service_provider_id', $validated['service_provider_id'], $validated['service_provider_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':invoice_number', $validated['invoice_number']);
        $statement->bindValue(':post_invoice_number', $validated['post_invoice_number'], $validated['post_invoice_number'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':inspection_date', $validated['inspection_date']);
        $statement->bindValue(':inspector_name', $validated['inspector_name']);
        $statement->bindValue(':inspector_title', $validated['inspector_title'], $validated['inspector_title'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':mileage', $validated['mileage'], $validated['mileage'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':overall_status', $validated['overall_status'], $validated['overall_status'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':works_done', $validated['works_done'], $validated['works_done'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':repair_cost', $validated['repair_cost'], PDO::PARAM_STR);
        $statement->bindValue(':recommendation', $validated['recommendation'], $validated['recommendation'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->execute();

        $inspectionId = $action === 'update' ? (int) $reportId : (int) $pdo->lastInsertId();
        postInspectionSaveSystemChecks($pdo, $inspectionId, $validated['system_checks']);
        $pdo->commit();

        postInspectionSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => $action === 'update' ? 'Post-inspection report updated successfully' : 'Post-inspection report added successfully',
                'message' => $action === 'update'
                    ? 'The post-inspection report has been updated successfully.'
                    : 'The post-inspection report has been saved successfully.',
            ],
        ]);
    } catch (RuntimeException $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        postInspectionSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Post-inspection report was not updated' : 'Post-inspection report was not added',
                'message' => $exception->getMessage(),
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
    } catch (Throwable $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        postInspectionSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Post-inspection report was not updated' : 'Post-inspection report was not added',
                'message' => $action === 'update'
                    ? 'A system error occurred while updating the post-inspection report.'
                    : 'A system error occurred while saving the post-inspection report.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
    }

    header('Location: ' . postInspectionPageUrl());
    exit;
}

// Handles delete requests for post-inspection reports.
function postInspectionHandleDelete(): void
{
    $reportId = filter_var((string) ($_POST['report_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($reportId === false) {
        postInspectionSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Post-inspection report was not deleted',
                'message' => 'The selected post-inspection report could not be identified.',
            ],
        ]);
        header('Location: ' . postInspectionPageUrl());
        exit;
    }

    try {
        $statement = fleetDb()->prepare("DELETE FROM inspections WHERE id = :id AND inspection_type = 'post'");
        $statement->execute(['id' => $reportId]);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('The selected post-inspection report no longer exists.');
        }

        postInspectionSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Post-inspection report deleted successfully',
                'message' => 'The selected post-inspection report has been removed.',
            ],
        ]);
    } catch (RuntimeException $exception) {
        postInspectionSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Post-inspection report was not deleted',
                'message' => $exception->getMessage(),
            ],
        ]);
    } catch (Throwable $exception) {
        postInspectionSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Post-inspection report was not deleted',
                'message' => 'A system error occurred while deleting the post-inspection report.',
            ],
        ]);
    }

    header('Location: ' . postInspectionPageUrl());
    exit;
}

// Dispatches incoming inspection POST requests by scope and action type.
function inspectionHandleRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . inspectionPageUrl());
        exit;
    }

    $scope = trim((string) ($_POST['inspection_scope'] ?? 'pre'));
    $action = trim((string) ($_POST['inspection_action'] ?? 'create'));

    if ($scope === 'post') {
        if ($action === 'delete') {
            postInspectionHandleDelete();
        }

        if ($action === 'update') {
            postInspectionHandleCreateOrUpdate('update');
        }

        postInspectionHandleCreateOrUpdate('create');
    }

    if ($action === 'delete') {
        inspectionHandleDelete();
    }

    if ($action === 'update') {
        inspectionHandleCreateOrUpdate('update');
    }

    inspectionHandleCreateOrUpdate('create');
}

if (basename(__FILE__) === basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    // This file can be included for page data or called directly as the POST endpoint.
    inspectionHandleRequest();
}
