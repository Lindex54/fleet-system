<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/activity-tracker.php';

fleetAuthRequireAdmin();

function recentActivitiesPageUrl(): string
{
    return '/fleet-system/modules/recent-activities/';
}

function recentActivitiesFetchPageData(): array
{
    fleetTrackerEnsureTables();

    $search = trim((string) ($_GET['q'] ?? ''));
    $moduleFilter = trim((string) ($_GET['module'] ?? ''));
    $rows = [];
    $summary = [
        'total' => 0,
        'today' => 0,
        'modules' => 0,
        'actors' => 0,
    ];
    $moduleOptions = [];

    try {
        $pdo = fleetDb();

        $summaryStatement = $pdo->query(
            "SELECT
                COUNT(*) AS total_count,
                SUM(CASE WHEN DATE(occurred_at) = CURDATE() THEN 1 ELSE 0 END) AS today_count,
                COUNT(DISTINCT module_key) AS module_count,
                COUNT(DISTINCT actor_user_id) AS actor_count
            FROM activity_event_logs"
        );
        $summaryRow = $summaryStatement->fetch() ?: [];
        $summary = [
            'total' => (int) ($summaryRow['total_count'] ?? 0),
            'today' => (int) ($summaryRow['today_count'] ?? 0),
            'modules' => (int) ($summaryRow['module_count'] ?? 0),
            'actors' => (int) ($summaryRow['actor_count'] ?? 0),
        ];

        $modulesStatement = $pdo->query(
            'SELECT DISTINCT module_key
            FROM activity_event_logs
            WHERE module_key <> \'\'
            ORDER BY module_key ASC'
        );
        foreach ($modulesStatement->fetchAll() as $row) {
            $moduleOptions[] = (string) $row['module_key'];
        }

        $sql = 'SELECT id, actor_name, actor_role, module_key, action_key, action_label, description, target_type, target_label, occurred_at
            FROM activity_event_logs
            WHERE 1 = 1';
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (
                actor_name LIKE :search
                OR actor_role LIKE :search
                OR module_key LIKE :search
                OR action_label LIKE :search
                OR description LIKE :search
                OR target_type LIKE :search
                OR target_label LIKE :search
            )';
            $params['search'] = '%' . $search . '%';
        }

        if ($moduleFilter !== '') {
            $sql .= ' AND module_key = :module_key';
            $params['module_key'] = $moduleFilter;
        }

        $sql .= ' ORDER BY occurred_at DESC, id DESC LIMIT 150';

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        foreach ($statement->fetchAll() as $row) {
            $rows[] = [
                'id' => (int) $row['id'],
                'actor_name' => (string) ($row['actor_name'] ?? 'System'),
                'actor_role' => (string) ($row['actor_role'] ?? 'system'),
                'module_key' => (string) $row['module_key'],
                'action_key' => (string) $row['action_key'],
                'action_label' => (string) $row['action_label'],
                'description' => (string) $row['description'],
                'target_type' => (string) ($row['target_type'] ?? 'record'),
                'target_label' => (string) ($row['target_label'] ?? 'General update'),
                'occurred_at' => fleetTrackerFormatDateTime($row['occurred_at'] ?? null),
            ];
        }
    } catch (Throwable $exception) {
        error_log('Recent activities page error: ' . $exception->getMessage());
    }

    return [
        'recentActivityRows' => $rows,
        'recentActivitiesSummary' => $summary,
        'recentActivitiesSearch' => $search,
        'recentActivitiesModuleFilter' => $moduleFilter,
        'recentActivitiesModuleOptions' => $moduleOptions,
        'recentActivitiesHasRows' => $rows !== [],
        'recentActivitiesPageUrl' => recentActivitiesPageUrl(),
    ];
}
