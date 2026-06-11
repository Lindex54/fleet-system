<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

function fleetTrackerEnsureTables(?PDO $pdo = null): void
{
    static $ready = false;

    if ($ready) {
        return;
    }

    $pdo ??= fleetDb();

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS auth_event_logs (
            id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id INT(10) UNSIGNED DEFAULT NULL,
            username VARCHAR(120) DEFAULT NULL,
            name VARCHAR(150) DEFAULT NULL,
            email VARCHAR(150) DEFAULT NULL,
            role VARCHAR(50) DEFAULT NULL,
            event_type VARCHAR(50) NOT NULL,
            event_description VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            metadata_text TEXT DEFAULT NULL,
            occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_auth_event_logs_occurred_at (occurred_at),
            KEY idx_auth_event_logs_user_id (user_id),
            KEY idx_auth_event_logs_event_type (event_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS activity_event_logs (
            id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            actor_user_id INT(10) UNSIGNED DEFAULT NULL,
            actor_name VARCHAR(150) DEFAULT NULL,
            actor_role VARCHAR(50) DEFAULT NULL,
            module_key VARCHAR(80) NOT NULL,
            action_key VARCHAR(80) NOT NULL,
            action_label VARCHAR(150) NOT NULL,
            description VARCHAR(255) NOT NULL,
            target_type VARCHAR(80) DEFAULT NULL,
            target_id INT(10) UNSIGNED DEFAULT NULL,
            target_label VARCHAR(180) DEFAULT NULL,
            metadata_text TEXT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            occurred_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_activity_event_logs_occurred_at (occurred_at),
            KEY idx_activity_event_logs_actor_user_id (actor_user_id),
            KEY idx_activity_event_logs_module_key (module_key),
            KEY idx_activity_event_logs_action_key (action_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $ready = true;
}

function fleetTrackerIpAddress(): ?string
{
    $ipAddress = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));

    return $ipAddress !== '' ? $ipAddress : null;
}

function fleetTrackerUserAgent(): ?string
{
    $userAgent = trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

    return $userAgent !== '' ? substr($userAgent, 0, 255) : null;
}

function fleetTrackerMetadata(?array $metadata): ?string
{
    if ($metadata === null || $metadata === []) {
        return null;
    }

    try {
        return json_encode($metadata, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    } catch (Throwable $exception) {
        return null;
    }
}

function fleetTrackerCurrentActor(): array
{
    fleetAuthStartSession();

    return [
        'user_id' => isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null,
        'name' => isset($_SESSION['user_name']) ? (string) $_SESSION['user_name'] : null,
        'role' => isset($_SESSION['user_role']) ? (string) $_SESSION['user_role'] : null,
    ];
}

function fleetTrackAuthEvent(array $payload, ?PDO $pdo = null): void
{
    try {
        $pdo ??= fleetDb();
        fleetTrackerEnsureTables($pdo);

        $statement = $pdo->prepare(
            'INSERT INTO auth_event_logs (
                user_id,
                username,
                name,
                email,
                role,
                event_type,
                event_description,
                ip_address,
                user_agent,
                metadata_text
            ) VALUES (
                :user_id,
                :username,
                :name,
                :email,
                :role,
                :event_type,
                :event_description,
                :ip_address,
                :user_agent,
                :metadata_text
            )'
        );

        $statement->execute([
            'user_id' => isset($payload['user_id']) ? (int) $payload['user_id'] : null,
            'username' => $payload['username'] ?? null,
            'name' => $payload['name'] ?? null,
            'email' => $payload['email'] ?? null,
            'role' => $payload['role'] ?? null,
            'event_type' => (string) ($payload['event_type'] ?? 'auth_event'),
            'event_description' => (string) ($payload['event_description'] ?? 'Authentication event recorded'),
            'ip_address' => fleetTrackerIpAddress(),
            'user_agent' => fleetTrackerUserAgent(),
            'metadata_text' => fleetTrackerMetadata(isset($payload['metadata']) && is_array($payload['metadata']) ? $payload['metadata'] : null),
        ]);
    } catch (Throwable $exception) {
        error_log('Fleet auth tracker error: ' . $exception->getMessage());
    }
}

function fleetTrackActivity(array $payload, ?PDO $pdo = null): void
{
    try {
        $pdo ??= fleetDb();
        fleetTrackerEnsureTables($pdo);

        $actor = fleetTrackerCurrentActor();
        $statement = $pdo->prepare(
            'INSERT INTO activity_event_logs (
                actor_user_id,
                actor_name,
                actor_role,
                module_key,
                action_key,
                action_label,
                description,
                target_type,
                target_id,
                target_label,
                metadata_text,
                ip_address,
                user_agent
            ) VALUES (
                :actor_user_id,
                :actor_name,
                :actor_role,
                :module_key,
                :action_key,
                :action_label,
                :description,
                :target_type,
                :target_id,
                :target_label,
                :metadata_text,
                :ip_address,
                :user_agent
            )'
        );

        $statement->execute([
            'actor_user_id' => isset($payload['actor_user_id']) ? (int) $payload['actor_user_id'] : $actor['user_id'],
            'actor_name' => $payload['actor_name'] ?? $actor['name'],
            'actor_role' => $payload['actor_role'] ?? $actor['role'],
            'module_key' => (string) ($payload['module_key'] ?? 'system'),
            'action_key' => (string) ($payload['action_key'] ?? 'updated'),
            'action_label' => (string) ($payload['action_label'] ?? 'Updated record'),
            'description' => (string) ($payload['description'] ?? 'Recent activity recorded'),
            'target_type' => $payload['target_type'] ?? null,
            'target_id' => isset($payload['target_id']) ? (int) $payload['target_id'] : null,
            'target_label' => $payload['target_label'] ?? null,
            'metadata_text' => fleetTrackerMetadata(isset($payload['metadata']) && is_array($payload['metadata']) ? $payload['metadata'] : null),
            'ip_address' => fleetTrackerIpAddress(),
            'user_agent' => fleetTrackerUserAgent(),
        ]);
    } catch (Throwable $exception) {
        error_log('Fleet activity tracker error: ' . $exception->getMessage());
    }
}

function fleetTrackerFormatDateTime(?string $value): string
{
    if ($value === null || trim($value) === '') {
        return 'Not recorded';
    }

    $timestamp = strtotime($value);

    return $timestamp === false ? $value : date('d M Y H:i', $timestamp);
}
