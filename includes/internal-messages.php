<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

const FLEET_MESSAGE_ALLOWED_EXTENSIONS = [
    'pdf' => 'application/pdf',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
];
const FLEET_MESSAGE_MAX_ATTACHMENT_BYTES = 5 * 1024 * 1024;

function fleetMessageStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function fleetMessageBasePath(): string
{
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $projectRoot = realpath(dirname(__DIR__));

    if ($documentRoot && $projectRoot && substr($projectRoot, 0, strlen($documentRoot)) === $documentRoot) {
        return str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
    }

    return '';
}

function fleetMessageLoginUrl(): string
{
    return (fleetMessageBasePath() ?: '') . '/login';
}

function fleetMessageHandlerUrl(): string
{
    return (fleetMessageBasePath() ?: '') . '/handlers/messages.php';
}

function fleetMessageDownloadUrl(int $attachmentId): string
{
    return (fleetMessageBasePath() ?: '') . '/handlers/message-download.php?attachment_id=' . $attachmentId;
}

function fleetMessagePageUrlForRole(string $role): string
{
    return $role === 'driver'
        ? (fleetMessageBasePath() ?: '') . '/driver-panel/messages'
        : (fleetMessageBasePath() ?: '') . '/modules/communications/';
}

function fleetMessageFlashKeyForRole(string $role): string
{
    return $role === 'driver' ? 'fleet_driver_messages_flash' : 'fleet_admin_messages_flash';
}

function fleetMessageSetFlash(string $role, array $payload): void
{
    fleetMessageStartSession();
    $_SESSION[fleetMessageFlashKeyForRole($role)] = $payload;
}

function fleetMessagePullFlash(string $role): ?array
{
    fleetMessageStartSession();
    $key = fleetMessageFlashKeyForRole($role);

    if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
        return null;
    }

    $flash = $_SESSION[$key];
    unset($_SESSION[$key]);

    return $flash;
}

function fleetMessageUploadDirectory(): string
{
    return dirname(__DIR__) . '/uploads/messages';
}

function fleetMessageEnsureUploadDirectory(): void
{
    $directory = fleetMessageUploadDirectory();

    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $htaccessPath = $directory . '/.htaccess';
    if (!is_file($htaccessPath)) {
        file_put_contents($htaccessPath, "Require all denied\n<FilesMatch \"\\.(php|phtml|phar|pl|py|jsp|asp|sh|cgi)$\">\nRequire all denied\n</FilesMatch>\n");
    }

    $indexPath = $directory . '/index.html';
    if (!is_file($indexPath)) {
        file_put_contents($indexPath, '');
    }
}

function fleetMessageEnsureSchema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS message_threads (
            id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            subject VARCHAR(255) NOT NULL,
            created_by_user_id INT(10) UNSIGNED NULL,
            last_message_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_message_threads_last_message_at (last_message_at),
            CONSTRAINT fk_message_threads_created_by_user
                FOREIGN KEY (created_by_user_id) REFERENCES users(id)
                ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS messages (
            id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            thread_id INT(10) UNSIGNED NOT NULL,
            sender_user_id INT(10) UNSIGNED NOT NULL,
            parent_message_id INT(10) UNSIGNED NULL,
            subject VARCHAR(255) NOT NULL,
            body MEDIUMTEXT NOT NULL,
            is_draft TINYINT(1) NOT NULL DEFAULT 0,
            sender_deleted_at DATETIME NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_messages_thread_id (thread_id),
            KEY idx_messages_sender_user_id (sender_user_id),
            KEY idx_messages_parent_message_id (parent_message_id),
            KEY idx_messages_is_draft (is_draft),
            CONSTRAINT fk_messages_thread
                FOREIGN KEY (thread_id) REFERENCES message_threads(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_messages_sender_user
                FOREIGN KEY (sender_user_id) REFERENCES users(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_messages_parent_message
                FOREIGN KEY (parent_message_id) REFERENCES messages(id)
                ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS message_recipients (
            id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            message_id INT(10) UNSIGNED NOT NULL,
            recipient_user_id INT(10) UNSIGNED NOT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            read_at DATETIME NULL DEFAULT NULL,
            is_deleted TINYINT(1) NOT NULL DEFAULT 0,
            deleted_at DATETIME NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_message_recipient (message_id, recipient_user_id),
            KEY idx_message_recipients_recipient_user_id (recipient_user_id),
            KEY idx_message_recipients_is_read (is_read),
            CONSTRAINT fk_message_recipients_message
                FOREIGN KEY (message_id) REFERENCES messages(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_message_recipients_user
                FOREIGN KEY (recipient_user_id) REFERENCES users(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS message_attachments (
            id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            message_id INT(10) UNSIGNED NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            stored_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            file_type VARCHAR(120) NOT NULL,
            size_bytes BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_message_attachments_message_id (message_id),
            CONSTRAINT fk_message_attachments_message
                FOREIGN KEY (message_id) REFERENCES messages(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function fleetMessageRequireContext(?PDO $pdo = null): array
{
    fleetMessageStartSession();
    $pdo ??= fleetDb();

    $userId = filter_var((string) ($_SESSION['user_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $role = (string) ($_SESSION['user_role'] ?? '');

    if ($userId === false || !in_array($role, ['admin', 'driver'], true)) {
        header('Location: ' . fleetMessageLoginUrl());
        exit;
    }

    $statement = $pdo->prepare(
        "SELECT id, name, email, role, status
         FROM users
         WHERE id = :id
         LIMIT 1"
    );
    $statement->execute(['id' => $userId]);
    $user = $statement->fetch();

    if (!$user || (string) $user['status'] !== 'active') {
        header('Location: ' . fleetMessageLoginUrl());
        exit;
    }

    $driverId = null;
    if ($role === 'driver') {
        $driverId = filter_var((string) ($_SESSION['driver_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($driverId === false) {
            header('Location: ' . fleetMessageLoginUrl());
            exit;
        }
    }

    return [
        'user_id' => (int) $user['id'],
        'driver_id' => $driverId === null ? null : (int) $driverId,
        'name' => (string) $user['name'],
        'email' => (string) $user['email'],
        'role' => $role,
        'page_url' => fleetMessagePageUrlForRole($role),
    ];
}

function fleetMessageFetchAllowedRecipients(PDO $pdo, array $context): array
{
    if ($context['role'] === 'driver') {
        $statement = $pdo->prepare(
            "SELECT id, name, email, role
             FROM users
             WHERE status = 'active'
               AND role = 'admin'
               AND id <> :user_id
             ORDER BY name ASC"
        );
        $statement->execute(['user_id' => $context['user_id']]);

        return [
            'admins' => array_map(static function (array $row): array {
                return [
                    'id' => (int) $row['id'],
                    'name' => (string) $row['name'],
                    'email' => (string) $row['email'],
                    'role' => (string) $row['role'],
                ];
            }, $statement->fetchAll()),
            'drivers' => [],
            'staff' => [],
        ];
    }

    $statement = $pdo->prepare(
        "SELECT
            u.id,
            u.name,
            u.email,
            u.role,
            d.full_name
         FROM users u
         LEFT JOIN drivers d ON d.user_id = u.id
         WHERE u.status = 'active'
           AND u.id <> :user_id
         ORDER BY
            CASE WHEN u.role = 'driver' THEN 0 WHEN u.role = 'admin' THEN 1 ELSE 2 END,
            u.name ASC"
    );
    $statement->execute(['user_id' => $context['user_id']]);

    $groups = [
        'admins' => [],
        'drivers' => [],
        'staff' => [],
    ];

    foreach ($statement->fetchAll() as $row) {
        $entry = [
            'id' => (int) $row['id'],
            'name' => (string) ($row['role'] === 'driver' && $row['full_name'] ? $row['full_name'] : $row['name']),
            'email' => (string) $row['email'],
            'role' => (string) $row['role'],
        ];

        if ($row['role'] === 'driver') {
            $groups['drivers'][] = $entry;
        } elseif ($row['role'] === 'admin') {
            $groups['admins'][] = $entry;
        } else {
            $groups['staff'][] = $entry;
        }
    }

    return $groups;
}

function fleetMessageAllowedRecipientIdMap(array $recipientGroups): array
{
    $map = [];

    foreach ($recipientGroups as $group) {
        foreach ($group as $recipient) {
            $map[(int) $recipient['id']] = $recipient;
        }
    }

    return $map;
}

function fleetMessageNormalizeRecipientIds(array $input): array
{
    $recipientIds = [];

    foreach ($input as $value) {
        $id = filter_var((string) $value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id !== false) {
            $recipientIds[] = (int) $id;
        }
    }

    $recipientIds = array_values(array_unique($recipientIds));
    sort($recipientIds);

    return $recipientIds;
}

function fleetMessageBuildComposeFormDataFromPost(): array
{
    return [
        'subject' => trim((string) ($_POST['subject'] ?? '')),
        'body' => trim((string) ($_POST['body'] ?? '')),
        'thread_id' => trim((string) ($_POST['thread_id'] ?? '')),
        'message_id' => trim((string) ($_POST['message_id'] ?? '')),
        'parent_message_id' => trim((string) ($_POST['parent_message_id'] ?? '')),
        'folder' => trim((string) ($_POST['folder'] ?? 'inbox')),
        'search' => trim((string) ($_POST['search'] ?? '')),
        'recipient_ids' => fleetMessageNormalizeRecipientIds((array) ($_POST['recipient_ids'] ?? [])),
        'remove_attachment_ids' => fleetMessageNormalizeRecipientIds((array) ($_POST['remove_attachment_ids'] ?? [])),
    ];
}

function fleetMessageNormalizeFolder(?string $folder): string
{
    $folder = strtolower(trim((string) $folder));
    return in_array($folder, ['inbox', 'sent', 'drafts', 'trash'], true) ? $folder : 'inbox';
}

function fleetMessageNormalizeUploadedFiles(array $input): array
{
    $files = [];
    $names = $input['name'] ?? [];

    if (!is_array($names)) {
        return $files;
    }

    foreach (array_keys($names) as $index) {
        $error = (int) ($input['error'][$index] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $files[] = [
            'name' => (string) ($input['name'][$index] ?? ''),
            'type' => (string) ($input['type'][$index] ?? ''),
            'tmp_name' => (string) ($input['tmp_name'][$index] ?? ''),
            'error' => $error,
            'size' => (int) ($input['size'][$index] ?? 0),
        ];
    }

    return $files;
}

function fleetMessageValidateAttachments(array $files): array
{
    $validated = [];

    foreach ($files as $file) {
        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('One of the attachments could not be uploaded.');
        }

        $originalName = trim((string) $file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!isset(FLEET_MESSAGE_ALLOWED_EXTENSIONS[$extension])) {
            throw new RuntimeException('Attachments must be PDF, DOC, DOCX, JPG, JPEG, PNG, or XLSX files.');
        }

        if ((int) $file['size'] <= 0 || (int) $file['size'] > FLEET_MESSAGE_MAX_ATTACHMENT_BYTES) {
            throw new RuntimeException('Each attachment must be 5 MB or smaller.');
        }

        $validated[] = [
            'original_name' => $originalName,
            'extension' => $extension,
            'tmp_name' => (string) $file['tmp_name'],
            'size' => (int) $file['size'],
            'type' => (string) ($file['type'] ?: FLEET_MESSAGE_ALLOWED_EXTENSIONS[$extension]),
        ];
    }

    return $validated;
}

function fleetMessageBuildThreadRedirectQuery(string $folder, ?int $threadId = null, string $search = ''): string
{
    $query = ['folder' => fleetMessageNormalizeFolder($folder)];

    if ($threadId !== null && $threadId > 0) {
        $query['thread'] = (string) $threadId;
    }

    if ($search !== '') {
        $query['q'] = $search;
    }

    return '?' . http_build_query($query);
}

function fleetMessageFetchExistingDraft(PDO $pdo, array $context, int $messageId): ?array
{
    $statement = $pdo->prepare(
        "SELECT id, thread_id, parent_message_id, subject, body
         FROM messages
         WHERE id = :id
           AND sender_user_id = :user_id
           AND is_draft = 1
         LIMIT 1"
    );
    $statement->execute([
        'id' => $messageId,
        'user_id' => $context['user_id'],
    ]);

    return $statement->fetch() ?: null;
}

function fleetMessageFetchDraftAttachments(PDO $pdo, int $messageId): array
{
    $statement = $pdo->prepare(
        "SELECT id, original_name, file_type, size_bytes
         FROM message_attachments
         WHERE message_id = :message_id
         ORDER BY id ASC"
    );
    $statement->execute(['message_id' => $messageId]);

    return array_map(static function (array $row): array {
        return [
            'id' => (int) $row['id'],
            'original_name' => (string) $row['original_name'],
            'file_type' => (string) $row['file_type'],
            'size_label' => number_format(((int) $row['size_bytes']) / 1024, 1) . ' KB',
        ];
    }, $statement->fetchAll());
}

function fleetMessageFetchDraftRecipientIds(PDO $pdo, int $messageId): array
{
    $statement = $pdo->prepare(
        "SELECT recipient_user_id
         FROM message_recipients
         WHERE message_id = :message_id
         ORDER BY recipient_user_id ASC"
    );
    $statement->execute(['message_id' => $messageId]);

    return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
}

function fleetMessageFetchReplyDefaults(PDO $pdo, array $context, int $messageId): ?array
{
    $statement = $pdo->prepare(
        "SELECT
            m.id,
            m.thread_id,
            m.subject,
            m.body,
            m.sender_user_id,
            u.name AS sender_name,
            u.email AS sender_email
         FROM messages m
         INNER JOIN users u ON u.id = m.sender_user_id
         WHERE m.id = :id
         LIMIT 1"
    );
    $statement->execute(['id' => $messageId]);
    $message = $statement->fetch();

    if (!$message || !fleetMessageUserCanAccessMessage($pdo, $context, (int) $message['id'])) {
        return null;
    }

    $recipientIds = [];

    if ((int) $message['sender_user_id'] !== (int) $context['user_id']) {
        $recipientIds[] = (int) $message['sender_user_id'];
    }

    $recipientStatement = $pdo->prepare(
        "SELECT DISTINCT mr.recipient_user_id
         FROM messages m
         INNER JOIN message_recipients mr ON mr.message_id = m.id
         WHERE m.thread_id = :thread_id"
    );
    $recipientStatement->execute(['thread_id' => (int) $message['thread_id']]);

    foreach ($recipientStatement->fetchAll(PDO::FETCH_COLUMN) as $recipientId) {
        $recipientId = (int) $recipientId;
        if ($recipientId !== (int) $context['user_id']) {
            $recipientIds[] = $recipientId;
        }
    }

    $subject = (string) $message['subject'];
    if (!preg_match('/^Re:/i', $subject)) {
        $subject = 'Re: ' . $subject;
    }

    $quotedBody = "\n\n--- Original message ---\n"
        . 'From: ' . (string) $message['sender_name'] . ' <' . (string) $message['sender_email'] . ">\n"
        . trim((string) $message['body']);

    return [
        'thread_id' => (int) $message['thread_id'],
        'parent_message_id' => (int) $message['id'],
        'subject' => $subject,
        'body' => '',
        'quoted_body' => $quotedBody,
        'recipient_ids' => array_values(array_unique($recipientIds)),
    ];
}

function fleetMessageFetchForwardDefaults(PDO $pdo, array $context, int $messageId): ?array
{
    $statement = $pdo->prepare(
        "SELECT
            m.subject,
            m.body,
            m.created_at,
            u.name AS sender_name,
            u.email AS sender_email
         FROM messages m
         LEFT JOIN users u ON u.id = m.sender_user_id
         WHERE m.id = :id
         LIMIT 1"
    );
    $statement->execute(['id' => $messageId]);
    $message = $statement->fetch();

    if (!$message || !fleetMessageUserCanAccessMessage($pdo, $context, $messageId)) {
        return null;
    }

    $subject = (string) $message['subject'];
    if (!preg_match('/^Fwd:/i', $subject)) {
        $subject = 'Fwd: ' . $subject;
    }

    $body = "\n\n--- Forwarded message ---\n"
        . 'From: ' . ((string) ($message['sender_name'] ?: 'System')) . ' <' . (string) ($message['sender_email'] ?: '-') . ">\n"
        . 'Sent: ' . date('d M Y H:i', strtotime((string) $message['created_at'])) . "\n\n"
        . trim((string) $message['body']);

    return [
        'thread_id' => null,
        'parent_message_id' => null,
        'subject' => $subject,
        'body' => $body,
        'quoted_body' => '',
        'recipient_ids' => [],
    ];
}

function fleetMessageUserCanAccessMessage(PDO $pdo, array $context, int $messageId): bool
{
    $statement = $pdo->prepare(
        "SELECT 1
         FROM messages m
         LEFT JOIN message_recipients mr
            ON mr.message_id = m.id
           AND mr.recipient_user_id = :recipient_user_id
         WHERE m.id = :message_id
           AND (m.sender_user_id = :sender_user_id OR mr.id IS NOT NULL)
         LIMIT 1"
    );
    $statement->execute([
        'recipient_user_id' => $context['user_id'],
        'sender_user_id' => $context['user_id'],
        'message_id' => $messageId,
    ]);

    return $statement->fetchColumn() !== false;
}

function fleetMessageUserCanAccessThread(PDO $pdo, array $context, int $threadId): bool
{
    $statement = $pdo->prepare(
        "SELECT 1
         FROM messages m
         LEFT JOIN message_recipients mr
            ON mr.message_id = m.id
           AND mr.recipient_user_id = :recipient_user_id
         WHERE m.thread_id = :thread_id
           AND (m.sender_user_id = :sender_user_id OR mr.id IS NOT NULL)
         LIMIT 1"
    );
    $statement->execute([
        'recipient_user_id' => $context['user_id'],
        'sender_user_id' => $context['user_id'],
        'thread_id' => $threadId,
    ]);

    return $statement->fetchColumn() !== false;
}

function fleetMessageStoreUploadedAttachments(PDO $pdo, int $messageId, array $validatedFiles): void
{
    fleetMessageEnsureUploadDirectory();

    $statement = $pdo->prepare(
        "INSERT INTO message_attachments (
            message_id,
            original_name,
            stored_name,
            file_path,
            file_type,
            size_bytes
         ) VALUES (
            :message_id,
            :original_name,
            :stored_name,
            :file_path,
            :file_type,
            :size_bytes
         )"
    );

    foreach ($validatedFiles as $file) {
        $storedName = bin2hex(random_bytes(16)) . '.' . $file['extension'];
        $relativePath = 'uploads/messages/' . $storedName;
        $absolutePath = dirname(__DIR__) . '/' . $relativePath;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            throw new RuntimeException('A message attachment could not be saved.');
        }

        $statement->execute([
            'message_id' => $messageId,
            'original_name' => $file['original_name'],
            'stored_name' => $storedName,
            'file_path' => $relativePath,
            'file_type' => $file['type'],
            'size_bytes' => $file['size'],
        ]);
    }
}

function fleetMessageRemoveDraftAttachments(PDO $pdo, array $context, int $messageId, array $attachmentIds): void
{
    if ($attachmentIds === []) {
        return;
    }

    $placeholders = implode(',', array_fill(0, count($attachmentIds), '?'));
    $statement = $pdo->prepare(
        "SELECT ma.id, ma.file_path
         FROM message_attachments ma
         INNER JOIN messages m ON m.id = ma.message_id
         WHERE ma.message_id = ?
           AND m.sender_user_id = ?
           AND m.is_draft = 1
           AND ma.id IN ($placeholders)"
    );
    $statement->execute(array_merge([$messageId, $context['user_id']], $attachmentIds));

    $attachments = $statement->fetchAll();
    if ($attachments === []) {
        return;
    }

    $deleteStatement = $pdo->prepare("DELETE FROM message_attachments WHERE id = ?");
    foreach ($attachments as $attachment) {
        $deleteStatement->execute([(int) $attachment['id']]);
        $absolutePath = dirname(__DIR__) . '/' . ltrim((string) $attachment['file_path'], '/');
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}

function fleetMessagePersistRecipients(PDO $pdo, int $messageId, array $recipientIds): void
{
    $pdo->prepare('DELETE FROM message_recipients WHERE message_id = :message_id')->execute([
        'message_id' => $messageId,
    ]);

    if ($recipientIds === []) {
        return;
    }

    $statement = $pdo->prepare(
        "INSERT INTO message_recipients (
            message_id,
            recipient_user_id,
            is_read,
            read_at,
            is_deleted,
            deleted_at
         ) VALUES (
            :message_id,
            :recipient_user_id,
            0,
            NULL,
            0,
            NULL
         )"
    );

    foreach ($recipientIds as $recipientId) {
        $statement->execute([
            'message_id' => $messageId,
            'recipient_user_id' => $recipientId,
        ]);
    }
}

function fleetMessageCreateThread(PDO $pdo, array $context, string $subject): int
{
    $statement = $pdo->prepare(
        "INSERT INTO message_threads (
            subject,
            created_by_user_id,
            last_message_at
         ) VALUES (
            :subject,
            :created_by_user_id,
            NOW()
         )"
    );
    $statement->execute([
        'subject' => $subject,
        'created_by_user_id' => $context['user_id'],
    ]);

    return (int) $pdo->lastInsertId();
}

function fleetMessageUpdateThread(PDO $pdo, int $threadId, string $subject): void
{
    $statement = $pdo->prepare(
        "UPDATE message_threads
         SET subject = :subject,
             last_message_at = NOW()
         WHERE id = :id"
    );
    $statement->execute([
        'subject' => $subject,
        'id' => $threadId,
    ]);
}

function fleetMessageResolveThreadId(PDO $pdo, array $context, array $formData, bool $isDraft): int
{
    $messageId = filter_var($formData['message_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($messageId !== false) {
        $draft = fleetMessageFetchExistingDraft($pdo, $context, (int) $messageId);
        if ($draft === null) {
            throw new RuntimeException('The selected draft could not be edited.');
        }

        return (int) $draft['thread_id'];
    }

    $threadId = filter_var($formData['thread_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($threadId !== false) {
        if (!fleetMessageUserCanAccessThread($pdo, $context, (int) $threadId)) {
            throw new RuntimeException('You are not allowed to reply inside that conversation.');
        }

        return (int) $threadId;
    }

    return fleetMessageCreateThread($pdo, $context, $formData['subject'] === '' ? 'Untitled draft' : $formData['subject']);
}

function fleetMessageSaveCompose(PDO $pdo, array $context, array $formData, bool $isDraft, array $validatedFiles): array
{
    $recipientGroups = fleetMessageFetchAllowedRecipients($pdo, $context);
    $allowedRecipientMap = fleetMessageAllowedRecipientIdMap($recipientGroups);

    if (!$isDraft) {
        if ($formData['subject'] === '' || $formData['body'] === '') {
            throw new RuntimeException('Subject and message body are required.');
        }

        if ($formData['recipient_ids'] === []) {
            throw new RuntimeException('Select at least one recipient before sending a message.');
        }
    }

    foreach ($formData['recipient_ids'] as $recipientId) {
        if (!isset($allowedRecipientMap[$recipientId])) {
            throw new RuntimeException('One or more selected recipients are not allowed for your account.');
        }
    }

    $pdo->beginTransaction();

    try {
        $messageId = filter_var($formData['message_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $threadId = fleetMessageResolveThreadId($pdo, $context, $formData, $isDraft);
        $parentMessageId = filter_var($formData['parent_message_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $subject = $formData['subject'] === '' && $isDraft ? 'Untitled draft' : $formData['subject'];

        if ($messageId !== false) {
            $draft = fleetMessageFetchExistingDraft($pdo, $context, (int) $messageId);
            if ($draft === null) {
                throw new RuntimeException('The selected draft could not be found.');
            }

            $update = $pdo->prepare(
                "UPDATE messages
                 SET subject = :subject,
                     body = :body,
                     is_draft = :is_draft,
                     sender_deleted_at = NULL,
                     updated_at = NOW()
                 WHERE id = :id
                   AND sender_user_id = :user_id"
            );
            $update->execute([
                'subject' => $subject,
                'body' => $formData['body'],
                'is_draft' => $isDraft ? 1 : 0,
                'id' => (int) $messageId,
                'user_id' => $context['user_id'],
            ]);
            $savedMessageId = (int) $messageId;
        } else {
            $insert = $pdo->prepare(
                "INSERT INTO messages (
                    thread_id,
                    sender_user_id,
                    parent_message_id,
                    subject,
                    body,
                    is_draft,
                    sender_deleted_at
                 ) VALUES (
                    :thread_id,
                    :sender_user_id,
                    :parent_message_id,
                    :subject,
                    :body,
                    :is_draft,
                    NULL
                 )"
            );
            $insert->execute([
                'thread_id' => $threadId,
                'sender_user_id' => $context['user_id'],
                'parent_message_id' => $parentMessageId === false ? null : (int) $parentMessageId,
                'subject' => $subject,
                'body' => $formData['body'],
                'is_draft' => $isDraft ? 1 : 0,
            ]);
            $savedMessageId = (int) $pdo->lastInsertId();
        }

        fleetMessagePersistRecipients($pdo, $savedMessageId, $formData['recipient_ids']);
        fleetMessageRemoveDraftAttachments($pdo, $context, $savedMessageId, $formData['remove_attachment_ids']);
        fleetMessageStoreUploadedAttachments($pdo, $savedMessageId, $validatedFiles);
        fleetMessageUpdateThread($pdo, $threadId, $subject);

        $pdo->commit();

        return [
            'message_id' => $savedMessageId,
            'thread_id' => $threadId,
        ];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function fleetMessageMarkThreadReadState(PDO $pdo, array $context, int $threadId, bool $isRead): void
{
    $statement = $pdo->prepare(
        "UPDATE message_recipients mr
         INNER JOIN messages m ON m.id = mr.message_id
         SET mr.is_read = :set_is_read,
             mr.read_at = CASE WHEN :case_is_read = 1 THEN NOW() ELSE NULL END
         WHERE m.thread_id = :thread_id
           AND mr.recipient_user_id = :user_id"
    );
    $statement->execute([
        'set_is_read' => $isRead ? 1 : 0,
        'case_is_read' => $isRead ? 1 : 0,
        'thread_id' => $threadId,
        'user_id' => $context['user_id'],
    ]);
}

function fleetMessageMoveThreadToTrash(PDO $pdo, array $context, int $threadId, bool $restore = false): void
{
    $statement = $pdo->prepare(
        "UPDATE messages
         SET sender_deleted_at = " . ($restore ? 'NULL' : 'NOW()') . "
         WHERE thread_id = :thread_id
           AND sender_user_id = :user_id"
    );
    $statement->execute([
        'thread_id' => $threadId,
        'user_id' => $context['user_id'],
    ]);

    $recipientStatement = $pdo->prepare(
        "UPDATE message_recipients mr
         INNER JOIN messages m ON m.id = mr.message_id
         SET mr.is_deleted = :set_is_deleted,
             mr.deleted_at = CASE WHEN :case_is_deleted = 1 THEN NOW() ELSE NULL END
         WHERE m.thread_id = :thread_id
           AND mr.recipient_user_id = :user_id"
    );
    $recipientStatement->execute([
        'set_is_deleted' => $restore ? 0 : 1,
        'case_is_deleted' => $restore ? 0 : 1,
        'thread_id' => $threadId,
        'user_id' => $context['user_id'],
    ]);
}

function fleetMessageBuildFolderSummaryQuery(string $folder): string
{
    return match ($folder) {
        'sent' => "m.sender_user_id = :folder_sender_user_id AND m.is_draft = 0 AND m.sender_deleted_at IS NULL",
        'drafts' => "m.sender_user_id = :folder_sender_user_id AND m.is_draft = 1 AND m.sender_deleted_at IS NULL",
        'trash' => "(
            (m.sender_user_id = :folder_sender_user_id AND m.sender_deleted_at IS NOT NULL)
            OR (
                mr_self.recipient_user_id = :folder_recipient_user_id
                AND m.is_draft = 0
                AND mr_self.is_deleted = 1
            )
        )",
        default => "mr_self.recipient_user_id = :folder_recipient_user_id AND m.is_draft = 0 AND mr_self.is_deleted = 0",
    };
}

function fleetMessageBuildFolderQueryBindings(string $folder, int $userId, array $extra = []): array
{
    $bindings = $extra;

    if (in_array($folder, ['sent', 'drafts', 'trash'], true)) {
        $bindings['folder_sender_user_id'] = $userId;
    }

    if (in_array($folder, ['inbox', 'trash'], true)) {
        $bindings['folder_recipient_user_id'] = $userId;
    }

    return $bindings;
}

function fleetMessageFetchFolderCounts(PDO $pdo, array $context): array
{
    $counts = [];
    foreach (['inbox', 'sent', 'drafts', 'trash'] as $folder) {
        $condition = fleetMessageBuildFolderSummaryQuery($folder);
        $statement = $pdo->prepare(
            "SELECT COUNT(DISTINCT m.thread_id)
             FROM messages m
             LEFT JOIN message_recipients mr_self
                ON mr_self.message_id = m.id
               AND mr_self.recipient_user_id = :join_user_id
             WHERE $condition"
        );
        $statement->execute(fleetMessageBuildFolderQueryBindings($folder, (int) $context['user_id'], [
            'join_user_id' => $context['user_id'],
        ]));
        $counts[$folder] = (int) $statement->fetchColumn();
    }

    $unreadStatement = $pdo->prepare(
        "SELECT COUNT(*)
         FROM message_recipients mr
         INNER JOIN messages m ON m.id = mr.message_id
         WHERE mr.recipient_user_id = :user_id
           AND mr.is_read = 0
           AND mr.is_deleted = 0
           AND m.is_draft = 0"
    );
    $unreadStatement->execute(['user_id' => $context['user_id']]);
    $counts['unread'] = (int) $unreadStatement->fetchColumn();

    return $counts;
}

function fleetMessageFetchThreadList(PDO $pdo, array $context, string $folder, string $search = ''): array
{
    $userId = (int) $context['user_id'];
    $searchSql = '';
    $searchBindings = [];

    if ($search !== '') {
        $searchSql = " AND (m.subject LIKE ? OR m.body LIKE ? OR COALESCE(u.name, '') LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $searchBindings = [$searchTerm, $searchTerm, $searchTerm];
    }

    $conditionSql = match ($folder) {
        'sent' => "m.sender_user_id = ? AND m.is_draft = 0 AND m.sender_deleted_at IS NULL",
        'drafts' => "m.sender_user_id = ? AND m.is_draft = 1 AND m.sender_deleted_at IS NULL",
        'trash' => "(
            (m.sender_user_id = ? AND m.sender_deleted_at IS NOT NULL)
            OR (
                mr_self.recipient_user_id = ?
                AND m.is_draft = 0
                AND mr_self.is_deleted = 1
            )
        )",
        default => "mr_self.recipient_user_id = ? AND m.is_draft = 0 AND mr_self.is_deleted = 0",
    };

    $conditionBindings = match ($folder) {
        'sent', 'drafts' => [$userId],
        'trash' => [$userId, $userId],
        default => [$userId],
    };

    $statement = $pdo->prepare(
        "SELECT
            m.thread_id,
            MAX(m.id) AS latest_message_id,
            MAX(m.created_at) AS last_message_at,
            SUM(
                CASE
                    WHEN mr_self.recipient_user_id = ?
                        AND mr_self.is_read = 0
                        AND mr_self.is_deleted = 0
                        AND m.is_draft = 0
                    THEN 1
                    ELSE 0
                END
            ) AS unread_count
         FROM messages m
         LEFT JOIN users u ON u.id = m.sender_user_id
         LEFT JOIN message_recipients mr_self
            ON mr_self.message_id = m.id
           AND mr_self.recipient_user_id = ?
         WHERE $conditionSql
         $searchSql
         GROUP BY m.thread_id
         ORDER BY MAX(m.created_at) DESC, MAX(m.id) DESC
         LIMIT 50"
    );
    $statement->execute(array_merge([$userId, $userId], $conditionBindings, $searchBindings));
    $rows = $statement->fetchAll();

    if ($rows === []) {
        return [];
    }

    $latestMessageIds = array_map(static fn(array $row): int => (int) $row['latest_message_id'], $rows);
    $placeholders = implode(',', array_fill(0, count($latestMessageIds), '?'));
    $messageStatement = $pdo->prepare(
        "SELECT
            m.id,
            m.thread_id,
            m.subject,
            m.body,
            m.is_draft,
            m.created_at,
            COALESCE(u.name, 'System') AS sender_name
         FROM messages m
         LEFT JOIN users u ON u.id = m.sender_user_id
         WHERE m.id IN ($placeholders)"
    );
    $messageStatement->execute($latestMessageIds);
    $latestMessages = [];
    foreach ($messageStatement->fetchAll() as $message) {
        $latestMessages[(int) $message['id']] = $message;
    }

    $threads = [];
    foreach ($rows as $row) {
        $latest = $latestMessages[(int) $row['latest_message_id']] ?? null;
        if ($latest === null) {
            continue;
        }

        $threads[] = [
            'thread_id' => (int) $row['thread_id'],
            'latest_message_id' => (int) $row['latest_message_id'],
            'subject' => (string) $latest['subject'],
            'snippet' => mb_substr(trim(preg_replace('/\s+/', ' ', (string) $latest['body'])), 0, 120),
            'sender_name' => (string) $latest['sender_name'],
            'last_message_at' => date('d M Y H:i', strtotime((string) $row['last_message_at'])),
            'unread_count' => (int) $row['unread_count'],
            'is_draft' => (int) $latest['is_draft'] === 1,
        ];
    }

    return $threads;
}

function fleetMessageFetchThreadMessages(PDO $pdo, array $context, int $threadId): array
{
    $statement = $pdo->prepare(
        "SELECT
            m.id,
            m.thread_id,
            m.parent_message_id,
            m.sender_user_id,
            m.subject,
            m.body,
            m.is_draft,
            m.created_at,
            mr_self.is_read AS self_is_read,
            mr_self.is_deleted AS self_is_deleted,
            COALESCE(u.name, 'System') AS sender_name,
            u.email AS sender_email
         FROM messages m
         LEFT JOIN users u ON u.id = m.sender_user_id
         LEFT JOIN message_recipients mr_self
            ON mr_self.message_id = m.id
           AND mr_self.recipient_user_id = :join_recipient_user_id
         WHERE m.thread_id = :thread_id
           AND (
                m.sender_user_id = :sender_user_id
                OR mr_self.recipient_user_id = :where_recipient_user_id
           )
         ORDER BY m.created_at ASC, m.id ASC"
    );
    $statement->execute([
        'join_recipient_user_id' => $context['user_id'],
        'sender_user_id' => $context['user_id'],
        'where_recipient_user_id' => $context['user_id'],
        'thread_id' => $threadId,
    ]);
    $messages = $statement->fetchAll();

    if ($messages === []) {
        return [];
    }

    $messageIds = array_map(static fn(array $row): int => (int) $row['id'], $messages);
    $placeholders = implode(',', array_fill(0, count($messageIds), '?'));

    $recipientStatement = $pdo->prepare(
        "SELECT
            mr.message_id,
            u.id AS recipient_user_id,
            u.name,
            u.email
         FROM message_recipients mr
         INNER JOIN users u ON u.id = mr.recipient_user_id
         WHERE mr.message_id IN ($placeholders)
         ORDER BY u.name ASC"
    );
    $recipientStatement->execute($messageIds);
    $recipientsByMessage = [];
    foreach ($recipientStatement->fetchAll() as $recipient) {
        $recipientsByMessage[(int) $recipient['message_id']][] = [
            'id' => (int) $recipient['recipient_user_id'],
            'name' => (string) $recipient['name'],
            'email' => (string) $recipient['email'],
        ];
    }

    $attachmentStatement = $pdo->prepare(
        "SELECT
            id,
            message_id,
            original_name,
            file_type,
            size_bytes
         FROM message_attachments
         WHERE message_id IN ($placeholders)
         ORDER BY id ASC"
    );
    $attachmentStatement->execute($messageIds);
    $attachmentsByMessage = [];
    foreach ($attachmentStatement->fetchAll() as $attachment) {
        $attachmentsByMessage[(int) $attachment['message_id']][] = [
            'id' => (int) $attachment['id'],
            'original_name' => (string) $attachment['original_name'],
            'file_type' => (string) $attachment['file_type'],
            'size_label' => number_format(((int) $attachment['size_bytes']) / 1024, 1) . ' KB',
            'download_url' => fleetMessageDownloadUrl((int) $attachment['id']),
        ];
    }

    return array_map(static function (array $row) use ($context, $recipientsByMessage, $attachmentsByMessage): array {
        $messageId = (int) $row['id'];
        return [
            'id' => $messageId,
            'thread_id' => (int) $row['thread_id'],
            'parent_message_id' => $row['parent_message_id'] === null ? null : (int) $row['parent_message_id'],
            'subject' => (string) $row['subject'],
            'body' => (string) $row['body'],
            'is_draft' => (int) $row['is_draft'] === 1,
            'is_sent_by_current_user' => (int) $row['sender_user_id'] === (int) $context['user_id'],
            'sender_name' => (string) $row['sender_name'],
            'sender_email' => (string) ($row['sender_email'] ?? ''),
            'created_at' => date('d M Y H:i', strtotime((string) $row['created_at'])),
            'recipients' => $recipientsByMessage[$messageId] ?? [],
            'attachments' => $attachmentsByMessage[$messageId] ?? [],
        ];
    }, $messages);
}

function fleetMessageFindThreadSummary(array $threadList, int $threadId): ?array
{
    foreach ($threadList as $thread) {
        if ((int) $thread['thread_id'] === $threadId) {
            return $thread;
        }
    }

    return null;
}

function fleetMessageFetchComposeDefaults(PDO $pdo, array $context, ?array $flashCompose = null): array
{
    if ($flashCompose !== null) {
        $flashCompose['thread_id'] = $flashCompose['thread_id'] !== '' ? (int) $flashCompose['thread_id'] : null;
        $flashCompose['message_id'] = $flashCompose['message_id'] !== '' ? (int) $flashCompose['message_id'] : null;
        $flashCompose['parent_message_id'] = $flashCompose['parent_message_id'] !== '' ? (int) $flashCompose['parent_message_id'] : null;
        $flashCompose['existing_attachments'] = $flashCompose['existing_attachments'] ?? [];

        return $flashCompose;
    }

    $draftId = filter_var((string) ($_GET['draft'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($draftId !== false) {
        $draft = fleetMessageFetchExistingDraft($pdo, $context, (int) $draftId);
        if ($draft !== null) {
            return [
                'subject' => (string) $draft['subject'],
                'body' => (string) $draft['body'],
                'thread_id' => (int) $draft['thread_id'],
                'message_id' => (int) $draft['id'],
                'parent_message_id' => $draft['parent_message_id'] === null ? null : (int) $draft['parent_message_id'],
                'recipient_ids' => fleetMessageFetchDraftRecipientIds($pdo, (int) $draft['id']),
                'existing_attachments' => fleetMessageFetchDraftAttachments($pdo, (int) $draft['id']),
                'quoted_body' => '',
            ];
        }
    }

    $replyId = filter_var((string) ($_GET['reply'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($replyId !== false) {
        $defaults = fleetMessageFetchReplyDefaults($pdo, $context, (int) $replyId);
        if ($defaults !== null) {
            $defaults['message_id'] = null;
            $defaults['existing_attachments'] = [];
            return $defaults;
        }
    }

    $forwardId = filter_var((string) ($_GET['forward'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($forwardId !== false) {
        $defaults = fleetMessageFetchForwardDefaults($pdo, $context, (int) $forwardId);
        if ($defaults !== null) {
            $defaults['message_id'] = null;
            $defaults['existing_attachments'] = [];
            return $defaults;
        }
    }

    return [
        'subject' => '',
        'body' => '',
        'thread_id' => null,
        'message_id' => null,
        'parent_message_id' => null,
        'recipient_ids' => [],
        'existing_attachments' => [],
        'quoted_body' => '',
    ];
}

function fleetMessageFetchMailboxPageData(array $context): array
{
    $pdo = fleetDb();
    fleetMessageEnsureSchema($pdo);

    $selectedFolder = fleetMessageNormalizeFolder((string) ($_GET['folder'] ?? 'inbox'));
    $search = trim((string) ($_GET['q'] ?? ''));
    $flash = fleetMessagePullFlash($context['role']) ?? [];
    $notification = $flash['notification'] ?? null;
    $composeDefaults = fleetMessageFetchComposeDefaults($pdo, $context, $flash['compose_form_data'] ?? null);
    $recipientGroups = fleetMessageFetchAllowedRecipients($pdo, $context);
    $selectedThreadId = filter_var((string) ($_GET['thread'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($selectedThreadId !== false) {
        fleetMessageMarkThreadReadState($pdo, $context, (int) $selectedThreadId, true);
    }

    $folderCounts = fleetMessageFetchFolderCounts($pdo, $context);
    $threadList = fleetMessageFetchThreadList($pdo, $context, $selectedFolder, $search);

    $activeThread = null;
    $activeThreadMessages = [];
    if ($selectedThreadId !== false && $selectedThreadId !== null) {
        $activeThreadMessages = fleetMessageFetchThreadMessages($pdo, $context, (int) $selectedThreadId);
        if ($activeThreadMessages !== []) {
            $activeThread = fleetMessageFindThreadSummary($threadList, (int) $selectedThreadId);
        }
    }

    return [
        'messageNotification' => $notification,
        'mailboxFolder' => $selectedFolder,
        'mailboxSearch' => $search,
        'mailboxFolderCounts' => $folderCounts,
        'mailboxUnreadCount' => (int) ($folderCounts['unread'] ?? 0),
        'mailboxThreadList' => $threadList,
        'mailboxActiveThread' => $activeThread,
        'mailboxActiveThreadMessages' => $activeThreadMessages,
        'mailboxCompose' => $composeDefaults,
        'mailboxRecipientGroups' => $recipientGroups,
        'mailboxActionUrl' => fleetMessageHandlerUrl(),
    ];
}

function fleetMessageFetchUnreadCountForCurrentSession(): int
{
    try {
        $pdo = fleetDb();
        fleetMessageEnsureSchema($pdo);
        $context = fleetMessageRequireContext($pdo);
        $counts = fleetMessageFetchFolderCounts($pdo, $context);

        return (int) ($counts['unread'] ?? 0);
    } catch (Throwable $exception) {
        return 0;
    }
}

function fleetMessageFetchAttachmentForDownload(PDO $pdo, array $context, int $attachmentId): ?array
{
    $statement = $pdo->prepare(
        "SELECT
            ma.id,
            ma.original_name,
            ma.file_path,
            ma.file_type,
            ma.size_bytes,
            m.sender_user_id,
            mr.recipient_user_id
         FROM message_attachments ma
         INNER JOIN messages m ON m.id = ma.message_id
         LEFT JOIN message_recipients mr
            ON mr.message_id = m.id
           AND mr.recipient_user_id = :join_recipient_user_id
         WHERE ma.id = :attachment_id
           AND (m.sender_user_id = :sender_user_id OR mr.recipient_user_id = :where_recipient_user_id)
         LIMIT 1"
    );
    $statement->execute([
        'attachment_id' => $attachmentId,
        'join_recipient_user_id' => $context['user_id'],
        'sender_user_id' => $context['user_id'],
        'where_recipient_user_id' => $context['user_id'],
    ]);

    return $statement->fetch() ?: null;
}
