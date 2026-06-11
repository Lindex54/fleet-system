<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/activity-tracker.php';

fleetAuthRequireAdmin();

function recentLogsPageUrl(): string
{
    return '/fleet-system/modules/recent-logs/';
}

function recentLogsFetchPageData(): array
{
    fleetTrackerEnsureTables();

    $search = trim((string) ($_GET['q'] ?? ''));
    $rows = [];
    $summary = [
        'total' => 0,
        'logins' => 0,
        'logouts' => 0,
        'failed' => 0,
    ];

    try {
        $pdo = fleetDb();

        $summaryStatement = $pdo->query(
            "SELECT
                COUNT(*) AS total_count,
                SUM(CASE WHEN event_type = 'login' THEN 1 ELSE 0 END) AS login_count,
                SUM(CASE WHEN event_type = 'logout' THEN 1 ELSE 0 END) AS logout_count,
                SUM(CASE WHEN event_type = 'login_failed' THEN 1 ELSE 0 END) AS failed_count
            FROM auth_event_logs"
        );
        $summaryRow = $summaryStatement->fetch() ?: [];
        $summary = [
            'total' => (int) ($summaryRow['total_count'] ?? 0),
            'logins' => (int) ($summaryRow['login_count'] ?? 0),
            'logouts' => (int) ($summaryRow['logout_count'] ?? 0),
            'failed' => (int) ($summaryRow['failed_count'] ?? 0),
        ];

        $sql = 'SELECT id, username, name, email, role, event_type, event_description, ip_address, occurred_at
            FROM auth_event_logs';
        $params = [];

        if ($search !== '') {
            $sql .= ' WHERE username LIKE :search
                OR name LIKE :search
                OR email LIKE :search
                OR role LIKE :search
                OR event_type LIKE :search
                OR event_description LIKE :search
                OR ip_address LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY occurred_at DESC, id DESC LIMIT 150';

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        foreach ($statement->fetchAll() as $row) {
            $rows[] = [
                'id' => (int) $row['id'],
                'username' => (string) ($row['username'] ?? 'Unknown'),
                'name' => (string) ($row['name'] ?? 'Unknown user'),
                'email' => (string) ($row['email'] ?? 'No email'),
                'role' => (string) ($row['role'] ?? 'unknown'),
                'event_type' => (string) $row['event_type'],
                'event_label' => ucwords(str_replace('_', ' ', (string) $row['event_type'])),
                'event_description' => (string) $row['event_description'],
                'ip_address' => (string) ($row['ip_address'] ?? 'Unknown'),
                'occurred_at' => fleetTrackerFormatDateTime($row['occurred_at'] ?? null),
            ];
        }
    } catch (Throwable $exception) {
        error_log('Recent logs page error: ' . $exception->getMessage());
    }

    return [
        'recentLogRows' => $rows,
        'recentLogsSummary' => $summary,
        'recentLogsSearch' => $search,
        'recentLogsHasRows' => $rows !== [],
        'recentLogsPageUrl' => recentLogsPageUrl(),
    ];
}
