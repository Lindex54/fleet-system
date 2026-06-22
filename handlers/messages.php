<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/ajax.php';
require_once __DIR__ . '/../includes/internal-messages.php';

function fleetMessageRedirectUrl(array $context, string $folder, ?int $threadId = null, string $search = ''): string
{
    return $context['page_url'] . fleetMessageBuildThreadRedirectQuery($folder, $threadId, $search);
}

function fleetMessagePrepareComposeFlash(PDO $pdo, array $context, array $formData): array
{
    $existingAttachments = [];
    $messageId = filter_var($formData['message_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($messageId !== false) {
        $draft = fleetMessageFetchExistingDraft($pdo, $context, (int) $messageId);
        if ($draft !== null) {
            $existingAttachments = fleetMessageFetchDraftAttachments($pdo, (int) $messageId);
        }
    }

    return [
        'subject' => $formData['subject'],
        'body' => $formData['body'],
        'thread_id' => $formData['thread_id'],
        'message_id' => $formData['message_id'],
        'parent_message_id' => $formData['parent_message_id'],
        'recipient_ids' => $formData['recipient_ids'],
        'remove_attachment_ids' => [],
        'existing_attachments' => $existingAttachments,
        'quoted_body' => '',
    ];
}

function fleetMessageHandleCompose(array $context, bool $isDraft): void
{
    $pdo = fleetDb();
    fleetMessageEnsureSchema($pdo);

    $formData = fleetMessageBuildComposeFormDataFromPost();
    $folder = $isDraft ? 'drafts' : $formData['folder'];
    $search = $formData['search'];
    $files = fleetMessageNormalizeUploadedFiles($_FILES['attachments'] ?? []);

    try {
        $validatedFiles = fleetMessageValidateAttachments($files);
        $result = fleetMessageSaveCompose($pdo, $context, $formData, $isDraft, $validatedFiles);
        $redirectUrl = fleetMessageRedirectUrl($context, $isDraft ? 'drafts' : 'sent', (int) $result['thread_id'], $search);
        $emailDelivery = $result['email_delivery'] ?? ['sent' => 0, 'failed' => 0];
        $successMessage = $isDraft
            ? 'Your draft has been saved.'
            : 'Your internal message has been delivered to the selected recipients.';

        if (!$isDraft && (int) ($emailDelivery['sent'] ?? 0) > 0) {
            $successMessage .= ' Email copies were also sent to ' . (int) $emailDelivery['sent'] . ' driver' . ((int) $emailDelivery['sent'] === 1 ? '' : 's') . '.';
        }

        if (!$isDraft && (int) ($emailDelivery['failed'] ?? 0) > 0) {
            $successMessage .= ' Some driver email notifications could not be sent, but the system inbox notification was saved.';
        }

        fleetMessageSetFlash($context['role'], [
            'notification' => [
                'type' => 'success',
                'title' => $isDraft ? 'Draft saved' : 'Message sent',
                'message' => $successMessage,
            ],
        ]);

        fleetFinishResponse($redirectUrl, [
            'success' => true,
            'message' => $successMessage,
            'reload' => true,
        ]);
    } catch (Throwable $exception) {
        $redirectUrl = fleetMessageRedirectUrl(
            $context,
            $formData['folder'] === '' ? 'inbox' : $formData['folder'],
            null,
            $search
        );

        fleetMessageSetFlash($context['role'], [
            'notification' => [
                'type' => 'error',
                'title' => $isDraft ? 'Draft could not be saved' : 'Message could not be sent',
                'message' => $exception->getMessage(),
            ],
            'compose_form_data' => fleetMessagePrepareComposeFlash($pdo, $context, $formData),
        ]);

        fleetFinishResponse($redirectUrl, [
            'success' => false,
            'message' => $exception->getMessage(),
            'reload' => true,
        ], 422);
    }
}

function fleetMessageHandleThreadAction(array $context, string $action): void
{
    $pdo = fleetDb();
    fleetMessageEnsureSchema($pdo);

    $threadId = filter_var((string) ($_POST['thread_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $folder = fleetMessageNormalizeFolder((string) ($_POST['folder'] ?? 'inbox'));
    $search = trim((string) ($_POST['search'] ?? ''));

    if ($threadId === false) {
        fleetFinishResponse(
            fleetMessageRedirectUrl($context, $folder, null, $search),
            [
                'success' => false,
                'message' => 'The selected message thread could not be identified.',
                'reload' => true,
            ],
            422
        );
    }

    try {
        if (!fleetMessageUserCanAccessThread($pdo, $context, (int) $threadId)) {
            throw new RuntimeException('You are not allowed to update that conversation.');
        }

        if ($action === 'delete_thread') {
            fleetMessageMoveThreadToTrash($pdo, $context, (int) $threadId, false);
            $targetFolder = 'trash';
            $title = 'Thread moved to trash';
            $message = 'The selected conversation has been moved to trash.';
            $selectedThread = (int) $threadId;
        } elseif ($action === 'restore_thread') {
            fleetMessageMoveThreadToTrash($pdo, $context, (int) $threadId, true);
            $targetFolder = 'inbox';
            $title = 'Thread restored';
            $message = 'The selected conversation has been restored.';
            $selectedThread = (int) $threadId;
        } elseif ($action === 'mark_thread_unread') {
            fleetMessageMarkThreadReadState($pdo, $context, (int) $threadId, false);
            $targetFolder = $folder;
            $title = 'Marked as unread';
            $message = 'The conversation has been marked as unread.';
            $selectedThread = (int) $threadId;
        } else {
            fleetMessageMarkThreadReadState($pdo, $context, (int) $threadId, true);
            $targetFolder = $folder;
            $title = 'Marked as read';
            $message = 'The conversation has been marked as read.';
            $selectedThread = (int) $threadId;
        }

        fleetMessageSetFlash($context['role'], [
            'notification' => [
                'type' => 'success',
                'title' => $title,
                'message' => $message,
            ],
        ]);

        fleetFinishResponse(
            fleetMessageRedirectUrl($context, $targetFolder, $selectedThread, $search),
            [
                'success' => true,
                'message' => $message,
                'reload' => true,
            ]
        );
    } catch (Throwable $exception) {
        fleetMessageSetFlash($context['role'], [
            'notification' => [
                'type' => 'error',
                'title' => 'Mailbox action failed',
                'message' => $exception->getMessage(),
            ],
        ]);

        fleetFinishResponse(
            fleetMessageRedirectUrl($context, $folder, (int) $threadId, $search),
            [
                'success' => false,
                'message' => $exception->getMessage(),
                'reload' => true,
            ],
            422
        );
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . fleetMessageLoginUrl());
    exit;
}

$context = fleetMessageRequireContext();
$action = trim((string) ($_POST['message_action'] ?? ''));

if ($action === 'send_message') {
    fleetMessageHandleCompose($context, false);
}

if ($action === 'save_draft') {
    fleetMessageHandleCompose($context, true);
}

if (in_array($action, ['delete_thread', 'restore_thread', 'mark_thread_read', 'mark_thread_unread'], true)) {
    fleetMessageHandleThreadAction($context, $action);
}

fleetFinishResponse(
    $context['page_url'],
    [
        'success' => false,
        'message' => 'The requested mailbox action is not supported.',
        'reload' => true,
    ],
    400
);
