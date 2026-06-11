<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/activity-tracker.php';

// Estate project constants used by validation and display helpers.
const ESTATE_ALLOWED_STATUSES = ['planned', 'approved', 'in_progress', 'on_hold', 'completed', 'cancelled'];
const ESTATE_ALLOWED_PRIORITIES = ['low', 'medium', 'high', 'critical'];

// Starts the session used for estate project flash notifications if it is not already active.
function estateStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

// Returns the estates page URL used after redirects.
function estatePageUrl(): string
{
    return '/fleet-system/modules/estates/';
}

// Returns the POST endpoint URL for estate project form submissions.
function estateHandlerUrl(): string
{
    return '/fleet-system/handlers/estates.php';
}

// Stores one-time estate project feedback in session flash state.
function estateSetFlash(array $payload): void
{
    estateStartSession();
    $_SESSION['estate_flash'] = $payload;
}

// Pulls and clears one-time estate project feedback from session flash state.
function estatePullFlash(): ?array
{
    estateStartSession();

    if (!isset($_SESSION['estate_flash']) || !is_array($_SESSION['estate_flash'])) {
        return null;
    }

    $flash = $_SESSION['estate_flash'];
    unset($_SESSION['estate_flash']);

    return $flash;
}

// Converts stored project status values into display labels.
function estateNormalizeStatus(string $status): string
{
    return match ($status) {
        'in_progress' => 'In Progress',
        'on_hold' => 'On Hold',
        default => ucwords(str_replace('_', ' ', $status)),
    };
}

// Converts stored project priority values into display labels.
function estateNormalizePriority(string $priority): string
{
    return ucwords(str_replace('_', ' ', $priority));
}

// Converts stored project dates into the card-friendly display format.
function estateFormatDisplayDate(?string $date): string
{
    if ($date === null || $date === '') {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d M Y', $timestamp) : $date;
}

// Formats budget and spent amounts using the project cards' money style.
function estateFormatMoney(float $amount): string
{
    return 'UGX ' . number_format($amount, 0);
}

// Converts a category into the icon key already supported by the estate helper file.
function estateResolveIcon(string $category, string $status): string
{
    $category = strtolower($category);

    if (str_contains($category, 'road')) {
        return 'road';
    }

    if (str_contains($category, 'electrical')) {
        return 'bolt';
    }

    if ($status === 'completed') {
        return 'barrier';
    }

    return 'crane';
}

// Returns the status badge classes used across estate project cards and modals.
function estateStatusClasses(): array
{
    return [
        'In Progress' => 'border-fleet-warning bg-fleet-warning-soft text-fleet-warning-strong',
        'Approved' => 'border-blue-300 bg-blue-100 text-fleet-primary',
        'On Hold' => 'border-orange-300 bg-orange-100 text-orange-700',
        'Completed' => 'border-green-300 bg-fleet-success-soft text-fleet-success',
        'Planned' => 'border-slate-300 bg-slate-100 text-slate-700',
        'Cancelled' => 'border-red-300 bg-red-100 text-fleet-danger',
    ];
}

// Returns the priority badge classes used across estate project cards and modals.
function estatePriorityClasses(): array
{
    return [
        'High' => 'border-orange-300 bg-orange-50 text-orange-700',
        'Medium' => 'border-yellow-300 bg-yellow-50 text-yellow-700',
        'Low' => 'border-slate-300 bg-slate-50 text-slate-700',
        'Critical' => 'border-red-300 bg-red-50 text-fleet-danger',
    ];
}

// Loads estate projects with contractor details plus summary totals for the page.
function estateFetchPageData(): array
{
    $flash = estatePullFlash();
    $notification = $flash['notification'] ?? null;
    $formData = $flash['form_data'] ?? [];
    $openModal = (bool) ($flash['open_modal'] ?? false);
    $formMode = $flash['form_mode'] ?? 'create';
    $projects = [];
    $summary = [
        'total_projects' => 0,
        'in_progress' => 0,
        'completed' => 0,
        'overdue' => 0,
        'on_hold' => 0,
        'total_budget' => 0.0,
        'total_spent' => 0.0,
    ];

    try {
        $statement = fleetDb()->query(
            'SELECT
                ep.id,
                ep.contractor_id,
                ep.project_name,
                ep.project_code,
                ep.location,
                ep.category,
                ep.funding_source,
                ep.status,
                ep.priority,
                ep.start_date,
                ep.deadline,
                ep.budget,
                ep.spent,
                ep.progress_percent,
                ep.description,
                c.name AS contractor_name,
                c.phone AS contractor_phone
            FROM estate_projects ep
            LEFT JOIN contractors c ON c.id = ep.contractor_id
            ORDER BY ep.created_at DESC, ep.id DESC'
        );

        foreach ($statement->fetchAll() as $row) {
            $statusLabel = estateNormalizeStatus((string) $row['status']);
            $priorityLabel = estateNormalizePriority((string) $row['priority']);
            $budget = (float) $row['budget'];
            $spent = (float) $row['spent'];
            $remaining = max($budget - $spent, 0);
            $progress = (int) $row['progress_percent'];
            $budgetUsedPercent = $budget > 0 ? (int) round(min(($spent / $budget) * 100, 100)) : 0;

            $projects[] = [
                'id' => (int) $row['id'],
                'contractor_id' => $row['contractor_id'] !== null ? (int) $row['contractor_id'] : null,
                'icon' => estateResolveIcon((string) ($row['category'] ?? ''), (string) $row['status']),
                'name' => $row['project_name'],
                'code' => $row['project_code'],
                'location' => $row['location'] ?: '-',
                'contractor' => $row['contractor_name'] ?: '-',
                'contractor_contact' => $row['contractor_phone'] ?: '-',
                'start' => estateFormatDisplayDate($row['start_date']),
                'start_raw' => $row['start_date'] ?: '',
                'deadline' => estateFormatDisplayDate($row['deadline']),
                'deadline_raw' => $row['deadline'] ?: '',
                'funding' => $row['funding_source'] ?: '-',
                'status' => $statusLabel,
                'status_value' => $row['status'],
                'category' => $row['category'] ?: 'Other',
                'priority' => $priorityLabel,
                'priority_value' => $row['priority'],
                'progress' => $progress,
                'budget_used' => $budgetUsedPercent,
                'spent' => estateFormatMoney($spent),
                'spent_raw' => (string) $spent,
                'budget' => estateFormatMoney($budget),
                'budget_raw' => (string) $budget,
                'remaining' => estateFormatMoney($remaining),
                'description' => $row['description'] ?: 'No description added.',
                'search' => strtolower(implode(' ', [
                    $row['project_name'],
                    $row['project_code'],
                    $row['location'] ?? '',
                    $row['category'] ?? '',
                    $row['funding_source'] ?? '',
                    $row['contractor_name'] ?? '',
                    $statusLabel,
                    $priorityLabel,
                ])),
            ];

            $summary['total_projects']++;
            $summary['total_budget'] += $budget;
            $summary['total_spent'] += $spent;

            if ($row['status'] === 'in_progress') {
                $summary['in_progress']++;
            }

            if ($row['status'] === 'completed') {
                $summary['completed']++;
            }

            if ($row['status'] === 'on_hold') {
                $summary['on_hold']++;
            }

            if (!empty($row['deadline']) && strtotime((string) $row['deadline']) < strtotime(date('Y-m-d')) && !in_array($row['status'], ['completed', 'cancelled'], true)) {
                $summary['overdue']++;
            }
        }
    } catch (Throwable $exception) {
        $notification = [
            'type' => 'error',
            'title' => 'Unable to load estate projects',
            'message' => 'The estate projects could not be loaded from the database right now.',
        ];
    }

    return [
        'projects' => $projects,
        'hasProjects' => count($projects) > 0,
        'estateSummary' => $summary,
        'estateNotification' => $notification,
        'estateFormData' => $formData,
        'shouldOpenEstateModal' => $openModal,
        'estateFormMode' => $formMode,
        'estateFormAction' => estateHandlerUrl(),
        'statusClasses' => estateStatusClasses(),
        'priorityClasses' => estatePriorityClasses(),
    ];
}

// Collects and trims raw POST values from the estate project form.
function estateBuildFormDataFromPost(): array
{
    return [
        'project_id' => trim((string) ($_POST['project_id'] ?? '')),
        'project_name' => trim((string) ($_POST['project_name'] ?? '')),
        'project_code' => trim((string) ($_POST['project_code'] ?? '')),
        'category' => trim((string) ($_POST['category'] ?? '')),
        'location' => trim((string) ($_POST['location'] ?? '')),
        'status' => strtolower(trim((string) ($_POST['status'] ?? 'planned'))),
        'priority' => strtolower(trim((string) ($_POST['priority'] ?? 'medium'))),
        'start_date' => trim((string) ($_POST['start_date'] ?? '')),
        'deadline' => trim((string) ($_POST['deadline'] ?? '')),
        'budget' => trim((string) ($_POST['budget'] ?? '0')),
        'spent' => trim((string) ($_POST['spent'] ?? '0')),
        'progress_percent' => trim((string) ($_POST['progress_percent'] ?? '0')),
        'contractor_name' => trim((string) ($_POST['contractor_name'] ?? '')),
        'contractor_contact' => trim((string) ($_POST['contractor_contact'] ?? '')),
        'funding_source' => trim((string) ($_POST['funding_source'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
    ];
}

// Validates and normalizes submitted estate project form values.
function estateValidateFormData(array $formData): array
{
    if ($formData['project_name'] === '' || $formData['project_code'] === '') {
        throw new RuntimeException('Project title and project code are required.');
    }

    if (!in_array($formData['status'], ESTATE_ALLOWED_STATUSES, true)) {
        throw new RuntimeException('Please select a valid project status.');
    }

    if (!in_array($formData['priority'], ESTATE_ALLOWED_PRIORITIES, true)) {
        throw new RuntimeException('Please select a valid project priority.');
    }

    $budget = filter_var($formData['budget'], FILTER_VALIDATE_FLOAT);
    $spent = filter_var($formData['spent'], FILTER_VALIDATE_FLOAT);
    $progressPercent = filter_var($formData['progress_percent'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);

    if ($budget === false || $budget < 0 || $spent === false || $spent < 0) {
        throw new RuntimeException('Please enter valid project budget and spent amounts.');
    }

    if ($progressPercent === false) {
        throw new RuntimeException('Please enter a valid progress percentage.');
    }

    $startDate = null;
    if ($formData['start_date'] !== '') {
        $start = DateTimeImmutable::createFromFormat('Y-m-d', $formData['start_date']);
        $startErrors = DateTimeImmutable::getLastErrors();
        if (!$start || ($startErrors['warning_count'] ?? 0) > 0 || ($startErrors['error_count'] ?? 0) > 0) {
            throw new RuntimeException('Please enter a valid project start date.');
        }

        $startDate = $start->format('Y-m-d');
    }

    $deadline = null;
    if ($formData['deadline'] !== '') {
        $end = DateTimeImmutable::createFromFormat('Y-m-d', $formData['deadline']);
        $endErrors = DateTimeImmutable::getLastErrors();
        if (!$end || ($endErrors['warning_count'] ?? 0) > 0 || ($endErrors['error_count'] ?? 0) > 0) {
            throw new RuntimeException('Please enter a valid project deadline.');
        }

        $deadline = $end->format('Y-m-d');
        if ($startDate !== null && $deadline < $startDate) {
            throw new RuntimeException('Project deadline cannot be earlier than the start date.');
        }
    }

    return [
        'project_name' => $formData['project_name'],
        'project_code' => $formData['project_code'],
        'category' => $formData['category'] === '' ? null : $formData['category'],
        'location' => $formData['location'] === '' ? null : $formData['location'],
        'status' => $formData['status'],
        'priority' => $formData['priority'],
        'start_date' => $startDate,
        'deadline' => $deadline,
        'budget' => (float) $budget,
        'spent' => (float) $spent,
        'progress_percent' => (int) $progressPercent,
        'contractor_name' => $formData['contractor_name'] === '' ? null : $formData['contractor_name'],
        'contractor_contact' => $formData['contractor_contact'] === '' ? null : $formData['contractor_contact'],
        'funding_source' => $formData['funding_source'] === '' ? null : $formData['funding_source'],
        'description' => $formData['description'] === '' ? null : $formData['description'],
    ];
}

// Finds or creates a contractor row so estate projects can stay linked to the contractors table.
function estateResolveContractorId(PDO $pdo, ?string $name, ?string $contact): ?int
{
    if ($name === null) {
        return null;
    }

    $existing = $pdo->prepare('SELECT id FROM contractors WHERE name = :name');
    $existing->execute(['name' => $name]);
    $contractorId = $existing->fetchColumn();

    if ($contractorId !== false) {
        $update = $pdo->prepare('UPDATE contractors SET phone = :phone WHERE id = :id');
        $update->execute([
            'phone' => $contact,
            'id' => (int) $contractorId,
        ]);

        return (int) $contractorId;
    }

    $insert = $pdo->prepare('INSERT INTO contractors (name, phone) VALUES (:name, :phone)');
    $insert->execute([
        'name' => $name,
        'phone' => $contact,
    ]);

    return (int) $pdo->lastInsertId();
}

// Handles both create and update requests for estate projects.
function estateHandleCreateOrUpdate(string $action): void
{
    $formData = estateBuildFormDataFromPost();

    try {
        $validated = estateValidateFormData($formData);
        $pdo = fleetDb();
        $pdo->beginTransaction();
        $contractorId = estateResolveContractorId($pdo, $validated['contractor_name'], $validated['contractor_contact']);

        if ($action === 'update') {
            $projectId = filter_var($formData['project_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($projectId === false) {
                throw new RuntimeException('The selected estate project could not be identified.');
            }

            $exists = $pdo->prepare('SELECT COUNT(*) FROM estate_projects WHERE id = :id');
            $exists->execute(['id' => $projectId]);
            if ((int) $exists->fetchColumn() === 0) {
                throw new RuntimeException('The selected estate project no longer exists.');
            }

            // Updates keep the same project row while refreshing editable project fields.
            $statement = $pdo->prepare(
                'UPDATE estate_projects SET
                    contractor_id = :contractor_id,
                    project_name = :project_name,
                    project_code = :project_code,
                    location = :location,
                    category = :category,
                    funding_source = :funding_source,
                    status = :status,
                    priority = :priority,
                    start_date = :start_date,
                    deadline = :deadline,
                    budget = :budget,
                    spent = :spent,
                    progress_percent = :progress_percent,
                    description = :description
                WHERE id = :project_id'
            );
            $statement->bindValue(':project_id', (int) $projectId, PDO::PARAM_INT);
        } else {
            // New estate projects are inserted directly with the validated form values.
            $statement = $pdo->prepare(
                'INSERT INTO estate_projects (
                    contractor_id,
                    project_name,
                    project_code,
                    location,
                    category,
                    funding_source,
                    status,
                    priority,
                    start_date,
                    deadline,
                    budget,
                    spent,
                    progress_percent,
                    description
                ) VALUES (
                    :contractor_id,
                    :project_name,
                    :project_code,
                    :location,
                    :category,
                    :funding_source,
                    :status,
                    :priority,
                    :start_date,
                    :deadline,
                    :budget,
                    :spent,
                    :progress_percent,
                    :description
                )'
            );
        }

        $statement->bindValue(':contractor_id', $contractorId, $contractorId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':project_name', $validated['project_name']);
        $statement->bindValue(':project_code', $validated['project_code']);
        $statement->bindValue(':location', $validated['location'], $validated['location'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':category', $validated['category'], $validated['category'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':funding_source', $validated['funding_source'], $validated['funding_source'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':status', $validated['status']);
        $statement->bindValue(':priority', $validated['priority']);
        $statement->bindValue(':start_date', $validated['start_date'], $validated['start_date'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':deadline', $validated['deadline'], $validated['deadline'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':budget', $validated['budget'], PDO::PARAM_STR);
        $statement->bindValue(':spent', $validated['spent'], PDO::PARAM_STR);
        $statement->bindValue(':progress_percent', $validated['progress_percent'], PDO::PARAM_INT);
        $statement->bindValue(':description', $validated['description'], $validated['description'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->execute();
        $targetProjectId = $action === 'update' ? (int) $projectId : (int) $pdo->lastInsertId();
        $pdo->commit();
        fleetTrackActivity([
            'module_key' => 'estates',
            'action_key' => $action === 'update' ? 'updated' : 'created',
            'action_label' => $action === 'update' ? 'Updated project' : 'Created project',
            'description' => $action === 'update'
                ? 'Updated an estate project.'
                : 'Created a new estate project.',
            'target_type' => 'estate_project',
            'target_id' => $targetProjectId,
            'target_label' => $validated['project_name'],
            'metadata' => [
                'status' => $validated['status'],
                'priority' => $validated['priority'],
            ],
        ], $pdo);

        estateSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => $action === 'update' ? 'Estate project updated successfully' : 'Estate project added successfully',
                'message' => $action === 'update'
                    ? 'The estate project has been updated successfully.'
                    : 'The estate project has been saved successfully.',
            ],
        ]);
    } catch (RuntimeException $exception) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        estateSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Estate project was not updated' : 'Estate project was not added',
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

        estateSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => $action === 'update' ? 'Estate project was not updated' : 'Estate project was not added',
                'message' => $action === 'update'
                    ? 'A system error occurred while updating the estate project.'
                    : 'A system error occurred while saving the estate project.',
            ],
            'form_data' => $formData,
            'open_modal' => true,
            'form_mode' => $action,
        ]);
    }

    header('Location: ' . estatePageUrl());
    exit;
}

// Handles delete requests for estate projects.
function estateHandleDelete(): void
{
    $projectId = filter_var((string) ($_POST['project_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($projectId === false) {
        estateSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Estate project was not deleted',
                'message' => 'The selected estate project could not be identified.',
            ],
        ]);
        header('Location: ' . estatePageUrl());
        exit;
    }

    try {
        $pdo = fleetDb();
        $lookup = $pdo->prepare('SELECT project_name FROM estate_projects WHERE id = :id');
        $lookup->execute(['id' => $projectId]);
        $existingProject = $lookup->fetch() ?: null;
        $statement = $pdo->prepare('DELETE FROM estate_projects WHERE id = :id');
        $statement->execute(['id' => $projectId]);

        if ($statement->rowCount() === 0) {
            throw new RuntimeException('The selected estate project no longer exists.');
        }

        fleetTrackActivity([
            'module_key' => 'estates',
            'action_key' => 'deleted',
            'action_label' => 'Deleted project',
            'description' => 'Removed an estate project.',
            'target_type' => 'estate_project',
            'target_id' => (int) $projectId,
            'target_label' => (string) (($existingProject['project_name'] ?? 'Estate project')),
        ], $pdo);

        estateSetFlash([
            'notification' => [
                'type' => 'success',
                'title' => 'Estate project deleted successfully',
                'message' => 'The selected estate project has been removed.',
            ],
        ]);
    } catch (RuntimeException $exception) {
        estateSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Estate project was not deleted',
                'message' => $exception->getMessage(),
            ],
        ]);
    } catch (Throwable $exception) {
        estateSetFlash([
            'notification' => [
                'type' => 'error',
                'title' => 'Estate project was not deleted',
                'message' => 'A system error occurred while deleting the estate project.',
            ],
        ]);
    }

    header('Location: ' . estatePageUrl());
    exit;
}

// Dispatches incoming estate project POST requests by action type.
function estateHandleRequest(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . estatePageUrl());
        exit;
    }

    $action = trim((string) ($_POST['estate_action'] ?? 'create'));

    if ($action === 'delete') {
        estateHandleDelete();
    }

    if ($action === 'update') {
        estateHandleCreateOrUpdate('update');
    }

    estateHandleCreateOrUpdate('create');
}

if (basename(__FILE__) === basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''))) {
    // This file can be included for page data or called directly as the POST endpoint.
    estateHandleRequest();
}
