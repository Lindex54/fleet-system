<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/internal-messages.php';

$attachmentId = filter_var((string) ($_GET['attachment_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($attachmentId === false) {
    http_response_code(404);
    exit('Attachment not found.');
}

$pdo = fleetDb();
fleetMessageEnsureSchema($pdo);
$context = fleetMessageRequireContext($pdo);
$attachment = fleetMessageFetchAttachmentForDownload($pdo, $context, (int) $attachmentId);

if ($attachment === null) {
    http_response_code(404);
    exit('Attachment not found.');
}

$absolutePath = dirname(__DIR__) . '/' . ltrim((string) $attachment['file_path'], '/');
if (!is_file($absolutePath)) {
    http_response_code(404);
    exit('Attachment file is missing.');
}

header('Content-Type: ' . (string) $attachment['file_type']);
header('Content-Length: ' . (string) filesize($absolutePath));
header('Content-Disposition: attachment; filename="' . rawurlencode((string) $attachment['original_name']) . '"');
header('X-Content-Type-Options: nosniff');
readfile($absolutePath);
exit;
