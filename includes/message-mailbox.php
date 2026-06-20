<?php
// Shared internal mailbox UI used by admin and driver message pages.
$mailboxThreadId = $mailboxActiveThread['thread_id'] ?? null;
$mailboxPageUrl = $mailboxPageUrl ?? '';
$mailboxFolder = $mailboxFolder ?? 'inbox';
$mailboxSearch = $mailboxSearch ?? '';
$mailboxCompose = $mailboxCompose ?? [];
$mailboxRecipientGroups = $mailboxRecipientGroups ?? [];
$mailboxThreadList = $mailboxThreadList ?? [];
$mailboxActiveThreadMessages = $mailboxActiveThreadMessages ?? [];
$mailboxCurrentUserEmail = strtolower((string) ($mailboxCurrentUserEmail ?? ''));
$mailboxFolderCounts = $mailboxFolderCounts ?? [];

$mailboxHasComposeData =
    trim((string) ($mailboxCompose['subject'] ?? '')) !== '' ||
    trim((string) ($mailboxCompose['body'] ?? '')) !== '' ||
    trim((string) ($mailboxCompose['quoted_body'] ?? '')) !== '' ||
    !empty($mailboxCompose['recipient_ids']) ||
    !empty($mailboxCompose['existing_attachments']) ||
    !empty($mailboxCompose['message_id']);
$mailboxIsComposeMode =
    isset($_GET['compose']) ||
    isset($_GET['reply']) ||
    isset($_GET['forward']) ||
    isset($_GET['draft']) ||
    $mailboxHasComposeData;
$mailboxIsThreadMode = !$mailboxIsComposeMode && $mailboxThreadId !== null && $mailboxActiveThreadMessages !== [];
$mailboxRequestedView = strtolower((string) ($_GET['view'] ?? ''));
$mailboxView = in_array($mailboxRequestedView, ['folder', 'thread', 'compose'], true)
    ? $mailboxRequestedView
    : ($mailboxIsComposeMode ? 'compose' : ($mailboxIsThreadMode ? 'thread' : 'folder'));
$mailboxPageQuery = static function (array $overrides = []) use ($mailboxFolder, $mailboxSearch): string {
    $query = [
        'folder' => $mailboxFolder,
    ];

    if ($mailboxSearch !== '') {
        $query['q'] = $mailboxSearch;
    }

    foreach ($overrides as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
            continue;
        }

        $query[$key] = (string) $value;
    }

    return '?' . http_build_query($query);
};
$mailboxSelectedFolderLabel = ucfirst($mailboxFolder);
$mailboxVisibleThreadCount = count($mailboxThreadList);

$allowedRecipientsByEmail = [];
foreach ($mailboxRecipientGroups as $groupRecipients) {
    foreach ($groupRecipients as $recipient) {
        $allowedRecipientsByEmail[strtolower((string) $recipient['email'])] = (int) $recipient['id'];
    }
}

$quickReplyRecipientIds = [];
foreach ($mailboxActiveThreadMessages as $threadMessage) {
    $senderEmail = strtolower((string) ($threadMessage['sender_email'] ?? ''));
    if ($senderEmail !== '' && $senderEmail !== $mailboxCurrentUserEmail && isset($allowedRecipientsByEmail[$senderEmail])) {
        $quickReplyRecipientIds[] = $allowedRecipientsByEmail[$senderEmail];
    }

    foreach (($threadMessage['recipients'] ?? []) as $recipient) {
        $recipientEmail = strtolower((string) ($recipient['email'] ?? ''));
        if ($recipientEmail !== '' && $recipientEmail !== $mailboxCurrentUserEmail && isset($allowedRecipientsByEmail[$recipientEmail])) {
            $quickReplyRecipientIds[] = $allowedRecipientsByEmail[$recipientEmail];
        }
    }
}
$quickReplyRecipientIds = array_values(array_unique($quickReplyRecipientIds));
$latestThreadMessage = $mailboxActiveThreadMessages === [] ? null : $mailboxActiveThreadMessages[count($mailboxActiveThreadMessages) - 1];

$mailboxFolderMeta = [
    'inbox' => ['label' => 'Inbox', 'hint' => 'Messages sent to you'],
    'sent' => ['label' => 'Sent', 'hint' => 'Messages you have sent'],
    'drafts' => ['label' => 'Drafts', 'hint' => 'Saved unfinished messages'],
    'trash' => ['label' => 'Trash', 'hint' => 'Deleted conversations'],
];
$mailboxFolders = [
    'inbox',
    'sent',
    'drafts',
    'trash',
];
$mailboxBackToFolderQuery = $mailboxPageQuery([
    'view' => 'folder',
    'thread' => null,
    'reply' => null,
    'forward' => null,
    'draft' => null,
    'compose' => null,
]);
$mailboxBackFromComposeQuery = !empty($mailboxCompose['thread_id'])
    ? $mailboxPageQuery([
        'view' => 'thread',
        'thread' => (int) $mailboxCompose['thread_id'],
        'reply' => null,
        'forward' => null,
        'draft' => null,
        'compose' => null,
    ])
    : $mailboxBackToFolderQuery;
$mailboxBackFromThreadQuery = $mailboxBackToFolderQuery;
?>
<?php if (!empty($messageNotification)): ?>
    <?php $isMailboxSuccess = ($messageNotification['type'] ?? '') === 'success'; ?>
    <section
        data-flash-notice
        data-flash-type="<?= $isMailboxSuccess ? 'success' : 'error'; ?>"
        class="pointer-events-none fixed left-1/2 top-8 z-[70] hidden w-[min(92vw,34rem)] -translate-x-1/2 overflow-hidden rounded-2xl border bg-white shadow-2xl transition duration-500 <?= $isMailboxSuccess ? 'border-green-200 text-green-900' : 'border-red-200 text-red-900'; ?>"
    >
        <div class="absolute inset-x-0 top-0 h-1.5 <?= $isMailboxSuccess ? 'bg-green-500' : 'bg-red-500'; ?>"></div>
        <div class="flex items-center gap-4 px-5 py-4 sm:px-6">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-sm font-extrabold shadow-lg <?= $isMailboxSuccess ? 'bg-green-600 text-white shadow-green-200' : 'bg-red-600 text-white shadow-red-200'; ?>">
                <?= $isMailboxSuccess ? 'OK' : '!'; ?>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-extrabold uppercase tracking-[0.18em]">
                            <?= htmlspecialchars($messageNotification['title'] ?? 'Mailbox update', ENT_QUOTES, 'UTF-8'); ?>
                        </h2>
                        <p class="mt-1 text-sm leading-6 text-fleet-ink">
                            <?= htmlspecialchars($messageNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    </div>
                    <button type="button" data-dismiss-flash class="pointer-events-auto inline-flex h-9 w-9 items-center justify-center rounded-full border text-base font-bold transition <?= $isMailboxSuccess ? 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'; ?>" aria-label="Dismiss mailbox notification">x</button>
                </div>
                <div class="mt-3 h-1.5 overflow-hidden rounded-full <?= $isMailboxSuccess ? 'bg-green-100' : 'bg-red-100'; ?>">
                    <div data-flash-progress class="h-full w-full origin-left rounded-full <?= $isMailboxSuccess ? 'bg-green-600' : 'bg-red-600'; ?>"></div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="overflow-hidden rounded-[32px] border border-fleet-line bg-[#eef4ff] shadow-[0_26px_80px_rgba(15,23,42,0.08)]">
    <div class="grid xl:grid-cols-[240px_minmax(0,1fr)]">
        <aside class="border-b border-fleet-line bg-[#eaf2ff] p-4 xl:min-h-[820px] xl:border-b-0 xl:border-r">
            <a href="<?= htmlspecialchars($mailboxPageUrl . $mailboxPageQuery(['view' => 'compose', 'compose' => 1, 'thread' => null, 'reply' => null, 'forward' => null, 'draft' => null]), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-14 w-full items-center gap-4 rounded-[20px] bg-[#b9dcff] px-6 text-base font-semibold text-fleet-sidebar shadow-[0_10px_25px_rgba(59,130,246,0.18)] transition hover:bg-[#a7d2ff] xl:w-[180px]">
                <span class="text-xl leading-none">+</span>
                <span>Compose</span>
            </a>

            <nav class="mt-5 space-y-1.5">
                <?php foreach ($mailboxFolders as $folderKey): ?>
                    <?php $isActiveFolder = $mailboxFolder === $folderKey; ?>
                    <a href="<?= htmlspecialchars($mailboxPageUrl . $mailboxPageQuery(['view' => 'folder', 'folder' => $folderKey, 'thread' => null, 'reply' => null, 'forward' => null, 'draft' => null, 'compose' => null]), ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center justify-between rounded-r-full rounded-l-2xl px-5 py-3 text-sm font-semibold transition <?= $isActiveFolder ? 'bg-[#cfe0ff] text-fleet-sidebar' : 'text-fleet-ink hover:bg-white/70'; ?>">
                        <span class="flex items-center gap-3">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full <?= $isActiveFolder ? 'bg-white text-fleet-sidebar' : 'bg-white/80 text-fleet-muted'; ?>">
                                <?php if ($folderKey === 'inbox'): ?>
                                    <span>&#9993;</span>
                                <?php elseif ($folderKey === 'sent'): ?>
                                    <span>&#10148;</span>
                                <?php elseif ($folderKey === 'drafts'): ?>
                                    <span>&#9998;</span>
                                <?php else: ?>
                                    <span>&#128465;</span>
                                <?php endif; ?>
                            </span>
                            <span><?= htmlspecialchars($mailboxFolderMeta[$folderKey]['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </span>
                        <span class="text-xs font-extrabold <?= $isActiveFolder ? 'text-fleet-sidebar' : 'text-fleet-muted'; ?>"><?= (int) ($mailboxFolderCounts[$folderKey] ?? 0); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </aside>

        <section class="bg-white xl:min-h-[820px]">
            <div class="block xl:hidden border-b border-fleet-line px-4 py-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-fleet-muted"><?= htmlspecialchars($mailboxSelectedFolderLabel, ENT_QUOTES, 'UTF-8'); ?></p>
                        <h2 class="mt-1 text-xl font-extrabold text-fleet-ink">Internal messages</h2>
                    </div>
                    <?php if ($mailboxView === 'thread' || $mailboxView === 'compose'): ?>
                        <a href="<?= htmlspecialchars($mailboxPageUrl . ($mailboxView === 'compose' ? $mailboxBackFromComposeQuery : $mailboxBackFromThreadQuery), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 items-center rounded-2xl border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink hover:bg-fleet-surface-muted">
                            Back
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="hidden xl:block">
                <?php if ($mailboxView === 'compose'): ?>
                    <div class="px-6 py-6">
                        <div class="mx-6 mt-6 overflow-hidden rounded-[28px] border border-fleet-line bg-white shadow-[0_20px_48px_rgba(15,23,42,0.08)]">
                            <div class="flex items-center justify-between gap-4 border-b border-fleet-line bg-[#f6f9ff] px-6 py-4">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-fleet-muted">Compose</p>
                                    <h2 class="mt-1 text-xl font-extrabold text-fleet-ink">
                                        <?php if (!empty($mailboxCompose['message_id'])): ?>
                                            Edit draft
                                        <?php elseif (isset($_GET['reply'])): ?>
                                            Reply
                                        <?php elseif (isset($_GET['forward'])): ?>
                                            Forward
                                        <?php else: ?>
                                            New message
                                        <?php endif; ?>
                                    </h2>
                                </div>
                                <a href="<?= htmlspecialchars($mailboxPageUrl . $mailboxBackFromComposeQuery, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 items-center rounded-2xl border border-fleet-line bg-white px-4 text-sm font-semibold text-fleet-ink hover:bg-fleet-surface-muted">Back</a>
                            </div>

                            <div class="max-h-[730px] overflow-y-auto px-6 py-6">
                                <form action="<?= htmlspecialchars($mailboxActionUrl, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data" class="space-y-6" data-fleet-ajax="true">
                                    <input type="hidden" name="folder" value="<?= htmlspecialchars($mailboxFolder, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="search" value="<?= htmlspecialchars($mailboxSearch, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="thread_id" value="<?= htmlspecialchars((string) ($mailboxCompose['thread_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="message_id" value="<?= htmlspecialchars((string) ($mailboxCompose['message_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="parent_message_id" value="<?= htmlspecialchars((string) ($mailboxCompose['parent_message_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <div data-fleet-feedback-host></div>

                                    <div class="rounded-2xl border border-fleet-line bg-[#f8fbff] p-4">
                                        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                            <div>
                                                <p class="text-sm font-bold text-fleet-ink">Recipients</p>
                                                <p class="text-xs text-fleet-muted">Choose users allowed by your role.</p>
                                            </div>
                                            <?php if (($mailboxRecipientGroups['drivers'] ?? []) !== []): ?>
                                                <button type="button" data-select-recipient-group="drivers" class="inline-flex h-9 items-center rounded-xl bg-white px-3 text-xs font-semibold text-fleet-primary shadow-sm hover:bg-blue-50">
                                                    Select all drivers
                                                </button>
                                            <?php endif; ?>
                                        </div>

                                        <div class="space-y-4">
                                            <?php foreach ($mailboxRecipientGroups as $groupKey => $groupRecipients): ?>
                                                <?php if ($groupRecipients === []): ?>
                                                    <?php continue; ?>
                                                <?php endif; ?>
                                                <section>
                                                    <div class="mb-3 flex items-center justify-between gap-3">
                                                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-fleet-muted"><?= htmlspecialchars(str_replace('_', ' ', $groupKey), ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <span class="text-xs text-fleet-muted"><?= count($groupRecipients); ?> available</span>
                                                    </div>
                                                    <div class="grid gap-3">
                                                        <?php foreach ($groupRecipients as $recipient): ?>
                                                            <?php $isChecked = in_array((int) $recipient['id'], $mailboxCompose['recipient_ids'] ?? [], true); ?>
                                                            <label class="flex items-start gap-3 rounded-2xl border border-fleet-line bg-white px-4 py-3 transition hover:border-fleet-primary">
                                                                <input type="checkbox" name="recipient_ids[]" value="<?= (int) $recipient['id']; ?>" class="mt-1 h-4 w-4 rounded border-fleet-line text-fleet-primary focus:ring-fleet-primary" data-recipient-group="<?= htmlspecialchars($groupKey, ENT_QUOTES, 'UTF-8'); ?>" <?= $isChecked ? 'checked' : ''; ?>>
                                                                <span class="min-w-0">
                                                                    <span class="block text-sm font-semibold text-fleet-ink"><?= htmlspecialchars($recipient['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                                    <span class="mt-1 block text-xs text-fleet-muted"><?= htmlspecialchars($recipient['email'], ENT_QUOTES, 'UTF-8'); ?> - <?= htmlspecialchars(str_replace('_', ' ', ucfirst($recipient['role'])), ENT_QUOTES, 'UTF-8'); ?></span>
                                                                </span>
                                                            </label>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </section>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-fleet-ink">Subject</label>
                                        <input type="text" name="subject" class="vehicle-form-control" value="<?= htmlspecialchars($mailboxCompose['subject'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Message subject">
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-semibold text-fleet-ink">Message</label>
                                        <textarea name="body" class="vehicle-form-control min-h-48 resize-y py-3" placeholder="Write your message..."><?= htmlspecialchars(($mailboxCompose['body'] ?? '') . ($mailboxCompose['quoted_body'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>

                                    <div class="rounded-2xl border border-dashed border-fleet-line bg-[#f8fbff] p-4">
                                        <label class="mb-2 block text-sm font-semibold text-fleet-ink">Attachments</label>
                                        <input type="file" name="attachments[]" multiple class="vehicle-form-control h-auto py-2" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx">
                                        <p class="mt-2 text-xs text-fleet-muted">Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG, XLSX. Maximum 5 MB per file.</p>
                                    </div>

                                    <?php if (($mailboxCompose['existing_attachments'] ?? []) !== []): ?>
                                        <div class="rounded-2xl border border-fleet-line bg-white p-4">
                                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-fleet-muted">Existing attachments</p>
                                            <div class="mt-4 space-y-3">
                                                <?php foreach ($mailboxCompose['existing_attachments'] as $attachment): ?>
                                                    <label class="flex items-center justify-between gap-3 rounded-2xl border border-fleet-line-soft px-4 py-3">
                                                        <span class="min-w-0">
                                                            <span class="block truncate text-sm font-semibold text-fleet-ink"><?= htmlspecialchars($attachment['original_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <span class="mt-1 block text-xs text-fleet-muted"><?= htmlspecialchars($attachment['size_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </span>
                                                        <span class="inline-flex items-center gap-2 text-xs font-semibold text-fleet-danger">
                                                            <input type="checkbox" name="remove_attachment_ids[]" value="<?= (int) $attachment['id']; ?>" class="h-4 w-4 rounded border-fleet-line text-fleet-danger focus:ring-fleet-danger">
                                                            Remove
                                                        </span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex flex-wrap gap-3">
                                        <button type="submit" name="message_action" value="send_message" class="inline-flex h-11 items-center rounded-2xl bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active" data-loading-text="Sending...">Send message</button>
                                        <button type="submit" name="message_action" value="save_draft" class="inline-flex h-11 items-center rounded-2xl border border-fleet-line bg-fleet-surface px-5 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted" data-loading-text="Saving...">Save draft</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php elseif ($mailboxView === 'thread' && $mailboxIsThreadMode): ?>
                    <div class="flex min-h-[820px] flex-col">
                        <div class="flex items-center justify-between gap-4 border-b border-fleet-line bg-white px-6 py-5">
                            <div class="flex items-center gap-4">
                                <a href="<?= htmlspecialchars($mailboxPageUrl . $mailboxBackFromThreadQuery, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-11 items-center rounded-2xl border border-fleet-line bg-white px-4 text-sm font-semibold text-fleet-ink hover:bg-fleet-surface-muted">Back</a>
                                <div class="min-w-0">
                                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-fleet-muted"><?= htmlspecialchars($mailboxSelectedFolderLabel, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <h2 class="mt-1 truncate text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($mailboxActiveThread['subject'] ?? 'Conversation', ENT_QUOTES, 'UTF-8'); ?></h2>
                                </div>
                            </div>
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?= htmlspecialchars($mailboxPageUrl . $mailboxPageQuery(['view' => 'compose', 'thread' => $mailboxThreadId, 'reply' => (string) ($mailboxActiveThread['latest_message_id'] ?? $mailboxThreadId), 'forward' => null, 'draft' => null, 'compose' => null]), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 items-center rounded-2xl border border-fleet-line bg-white px-4 text-sm font-semibold text-fleet-ink hover:bg-fleet-surface-muted">Reply</a>
                                    <a href="<?= htmlspecialchars($mailboxPageUrl . $mailboxPageQuery(['view' => 'compose', 'thread' => $mailboxThreadId, 'forward' => (string) ($mailboxActiveThread['latest_message_id'] ?? $mailboxThreadId), 'reply' => null, 'draft' => null, 'compose' => null]), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 items-center rounded-2xl border border-fleet-line bg-white px-4 text-sm font-semibold text-fleet-ink hover:bg-fleet-surface-muted">Forward</a>
                                    <?php if ($mailboxFolder === 'drafts' && !empty($mailboxActiveThread['latest_message_id'])): ?>
                                        <a href="<?= htmlspecialchars($mailboxPageUrl . $mailboxPageQuery(['view' => 'compose', 'draft' => (string) $mailboxActiveThread['latest_message_id'], 'compose' => 1, 'reply' => null, 'forward' => null]), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 items-center rounded-2xl border border-fleet-line bg-white px-4 text-sm font-semibold text-fleet-ink hover:bg-fleet-surface-muted">Edit draft</a>
                                    <?php endif; ?>
                                    <form action="<?= htmlspecialchars($mailboxActionUrl, ENT_QUOTES, 'UTF-8'); ?>" method="post" data-fleet-ajax="true">
                                        <input type="hidden" name="thread_id" value="<?= (int) $mailboxThreadId; ?>">
                                        <input type="hidden" name="folder" value="<?= htmlspecialchars($mailboxFolder, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="search" value="<?= htmlspecialchars($mailboxSearch, ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" name="message_action" value="<?= $mailboxFolder === 'trash' ? 'restore_thread' : 'delete_thread'; ?>" class="inline-flex h-10 items-center rounded-2xl border border-fleet-line bg-white px-4 text-sm font-semibold text-fleet-ink hover:bg-fleet-surface-muted" data-loading-text="Working..."><?= $mailboxFolder === 'trash' ? 'Restore' : 'Trash'; ?></button>
                                    </form>
                                    <form action="<?= htmlspecialchars($mailboxActionUrl, ENT_QUOTES, 'UTF-8'); ?>" method="post" data-fleet-ajax="true">
                                        <input type="hidden" name="thread_id" value="<?= (int) $mailboxThreadId; ?>">
                                        <input type="hidden" name="folder" value="<?= htmlspecialchars($mailboxFolder, ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="search" value="<?= htmlspecialchars($mailboxSearch, ENT_QUOTES, 'UTF-8'); ?>">
                                        <button type="submit" name="message_action" value="<?= (($mailboxActiveThread['unread_count'] ?? 0) > 0) ? 'mark_thread_read' : 'mark_thread_unread'; ?>" class="inline-flex h-10 items-center rounded-2xl border border-fleet-line bg-white px-4 text-sm font-semibold text-fleet-ink hover:bg-fleet-surface-muted" data-loading-text="Working..."><?= (($mailboxActiveThread['unread_count'] ?? 0) > 0) ? 'Mark read' : 'Mark unread'; ?></button>
                                    </form>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto px-6 py-6">
                            <div class="space-y-5">
                                <?php foreach ($mailboxActiveThreadMessages as $threadMessage): ?>
                                    <?php
                                    $recipientLabels = array_map(
                                        static fn(array $recipient): string => $recipient['name'] . ' <' . $recipient['email'] . '>',
                                        $threadMessage['recipients']
                                    );
                                    ?>
                                    <article class="overflow-hidden rounded-[26px] border border-fleet-line bg-white shadow-[0_14px_36px_rgba(15,23,42,0.06)]">
                                        <div class="border-b border-fleet-line bg-[#f8fbff] px-6 py-4">
                                            <div class="flex flex-wrap items-start justify-between gap-4">
                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-3">
                                                        <p class="text-base font-extrabold text-fleet-ink"><?= htmlspecialchars($threadMessage['sender_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <?php if ($threadMessage['is_sent_by_current_user']): ?>
                                                            <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-fleet-primary">You</span>
                                                        <?php endif; ?>
                                                        <?php if ($threadMessage['is_draft']): ?>
                                                            <span class="inline-flex rounded-full bg-fleet-warning-soft px-3 py-1 text-xs font-semibold text-fleet-warning-strong">Draft</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="mt-1 text-sm text-fleet-muted"><?= htmlspecialchars($threadMessage['sender_email'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <p class="mt-2 text-xs text-fleet-muted">To: <?= htmlspecialchars($recipientLabels === [] ? 'No recipients' : implode(', ', $recipientLabels), ENT_QUOTES, 'UTF-8'); ?></p>
                                                </div>
                                                <p class="text-xs font-semibold text-fleet-muted"><?= htmlspecialchars($threadMessage['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                        </div>

                                        <div class="px-6 py-6">
                                            <div class="whitespace-pre-line text-sm leading-7 text-fleet-ink"><?= htmlspecialchars($threadMessage['body'], ENT_QUOTES, 'UTF-8'); ?></div>

                                            <?php if ($threadMessage['attachments'] !== []): ?>
                                                <div class="mt-6 rounded-2xl border border-fleet-line-soft bg-[#f8fbff] p-4">
                                                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-fleet-muted">Attachments</p>
                                                    <div class="mt-3 flex flex-wrap gap-3">
                                                        <?php foreach ($threadMessage['attachments'] as $attachment): ?>
                                                            <a href="<?= htmlspecialchars($attachment['download_url'], ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-2 rounded-2xl border border-fleet-line bg-white px-4 py-2 text-sm font-semibold text-fleet-ink shadow-sm hover:border-fleet-primary hover:text-fleet-primary">
                                                                <span><?= htmlspecialchars($attachment['original_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                                <span class="text-xs text-fleet-muted"><?= htmlspecialchars($attachment['size_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php if ($mailboxFolder !== 'trash'): ?>
                            <div class="border-t border-fleet-line bg-white px-6 py-5">
                                <div class="flex flex-wrap items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-fleet-muted">Quick reply</p>
                                        <p class="mt-1 text-sm text-fleet-muted">Respond directly from this conversation view.</p>
                                    </div>
                                    <a href="<?= htmlspecialchars($mailboxPageUrl . $mailboxPageQuery(['view' => 'compose', 'thread' => $mailboxThreadId, 'reply' => (string) ($latestThreadMessage['id'] ?? $mailboxThreadId), 'compose' => 1, 'forward' => null, 'draft' => null]), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 items-center rounded-2xl border border-fleet-line bg-white px-4 text-sm font-semibold text-fleet-ink hover:bg-fleet-surface-muted">
                                        Open full reply
                                    </a>
                                </div>

                                <form action="<?= htmlspecialchars($mailboxActionUrl, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data" class="mt-4 space-y-4" data-fleet-ajax="true">
                                    <input type="hidden" name="folder" value="<?= htmlspecialchars($mailboxFolder, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="search" value="<?= htmlspecialchars($mailboxSearch, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="thread_id" value="<?= (int) $mailboxThreadId; ?>">
                                    <input type="hidden" name="parent_message_id" value="<?= (int) (($mailboxActiveThread['latest_message_id'] ?? 0)); ?>">
                                    <input type="hidden" name="subject" value="<?= htmlspecialchars((string) ($mailboxActiveThread['subject'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php foreach ($quickReplyRecipientIds as $recipientId): ?>
                                        <input type="hidden" name="recipient_ids[]" value="<?= (int) $recipientId; ?>">
                                    <?php endforeach; ?>
                                    <div data-fleet-feedback-host></div>
                                    <textarea name="body" class="vehicle-form-control min-h-28 resize-y py-3" placeholder="Reply to this thread..."></textarea>
                                    <input type="file" name="attachments[]" multiple class="vehicle-form-control h-auto py-2" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx">
                                    <div class="flex flex-wrap gap-3">
                                        <button type="submit" name="message_action" value="send_message" class="inline-flex h-11 items-center rounded-2xl bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active" data-loading-text="Sending...">Send reply</button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="px-4 py-4 xl:px-6 xl:py-6">
                        <div class="overflow-hidden rounded-[28px] border border-fleet-line bg-white shadow-[0_20px_48px_rgba(15,23,42,0.08)]">
                            <div class="border-b border-fleet-line bg-[#f8fbff] px-6 py-5">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-fleet-muted"><?= htmlspecialchars($mailboxSelectedFolderLabel, ENT_QUOTES, 'UTF-8'); ?></p>
                                        <h2 class="mt-1 text-3xl font-extrabold text-fleet-ink">Internal messages</h2>
                                    </div>
                                    <span class="inline-flex rounded-full bg-[#edf4ff] px-4 py-2 text-sm font-semibold text-fleet-sidebar"><?= $mailboxVisibleThreadCount; ?> conversation(s)</span>
                                </div>

                                <form action="<?= htmlspecialchars($mailboxPageUrl, ENT_QUOTES, 'UTF-8'); ?>" method="get" class="mt-5">
                                    <input type="hidden" name="folder" value="<?= htmlspecialchars($mailboxFolder, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="view" value="folder">
                                    <label class="relative block">
                                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <circle cx="11" cy="11" r="8"></circle>
                                                <path d="m21 21-4.3-4.3"></path>
                                            </svg>
                                        </span>
                                        <input type="search" name="q" class="h-12 w-full rounded-2xl border border-fleet-line bg-[#f7f9ff] py-2 pl-12 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search in mail" value="<?= htmlspecialchars($mailboxSearch, ENT_QUOTES, 'UTF-8'); ?>">
                                    </label>
                                </form>
                            </div>

                            <div class="max-h-[730px] overflow-y-auto">
                                <?php if ($mailboxThreadList === []): ?>
                                    <div class="px-6 py-16 text-center">
                                        <h3 class="text-lg font-extrabold text-fleet-ink">No conversations found</h3>
                                        <p class="mt-2 text-sm text-fleet-muted">This folder has no messages for the current search.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="divide-y divide-fleet-line-soft">
                                        <?php foreach ($mailboxThreadList as $thread): ?>
                                            <?php $isUnreadThread = (int) $thread['unread_count'] > 0; ?>
                                            <a href="<?= htmlspecialchars($mailboxPageUrl . $mailboxPageQuery(['view' => 'thread', 'thread' => (int) $thread['thread_id']]), ENT_QUOTES, 'UTF-8'); ?>" class="grid grid-cols-[1fr_auto] gap-4 px-6 py-4 transition <?= $isUnreadThread ? 'bg-[#f8fbff] hover:bg-[#f1f6ff]' : 'hover:bg-[#f7f9ff]'; ?>">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-3">
                                                        <p class="truncate text-sm <?= $isUnreadThread ? 'font-extrabold text-fleet-ink' : 'font-semibold text-fleet-ink'; ?>"><?= htmlspecialchars($thread['sender_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <?php if ($isUnreadThread): ?>
                                                            <span class="inline-flex min-w-7 items-center justify-center rounded-full bg-fleet-primary px-2 py-0.5 text-[11px] font-extrabold text-white"><?= (int) $thread['unread_count']; ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="mt-1 truncate text-sm font-semibold text-fleet-ink"><?= htmlspecialchars($thread['subject'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <p class="mt-2 truncate text-sm text-fleet-muted"><?= htmlspecialchars($thread['snippet'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-xs font-semibold text-fleet-muted"><?= htmlspecialchars($thread['last_message_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="xl:hidden">
                <?php if ($mailboxIsComposeMode): ?>
                    <div class="px-4 py-4">
                        <div class="overflow-hidden rounded-[28px] border border-fleet-line bg-white shadow-sm">
                            <div class="flex items-center justify-between gap-4 border-b border-fleet-line bg-[#f6f9ff] px-5 py-4">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-fleet-muted">Compose</p>
                                    <h2 class="mt-1 text-xl font-extrabold text-fleet-ink">New message</h2>
                                </div>
                                <a href="<?= htmlspecialchars($mailboxPageUrl . $mailboxBackFromComposeQuery, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 items-center rounded-2xl border border-fleet-line bg-white px-4 text-sm font-semibold text-fleet-ink hover:bg-fleet-surface-muted">Back</a>
                            </div>

                            <div class="max-h-[75vh] overflow-y-auto px-5 py-5">
                                <form action="<?= htmlspecialchars($mailboxActionUrl, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="multipart/form-data" class="space-y-5" data-fleet-ajax="true">
                                    <input type="hidden" name="folder" value="<?= htmlspecialchars($mailboxFolder, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="search" value="<?= htmlspecialchars($mailboxSearch, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="thread_id" value="<?= htmlspecialchars((string) ($mailboxCompose['thread_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="message_id" value="<?= htmlspecialchars((string) ($mailboxCompose['message_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="parent_message_id" value="<?= htmlspecialchars((string) ($mailboxCompose['parent_message_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                    <div data-fleet-feedback-host></div>

                                    <div class="space-y-4">
                                        <?php foreach ($mailboxRecipientGroups as $groupKey => $groupRecipients): ?>
                                            <?php if ($groupRecipients === []) { continue; } ?>
                                            <section class="rounded-2xl border border-fleet-line bg-[#f8fbff] p-4">
                                                <p class="mb-3 text-xs font-extrabold uppercase tracking-[0.2em] text-fleet-muted"><?= htmlspecialchars(str_replace('_', ' ', $groupKey), ENT_QUOTES, 'UTF-8'); ?></p>
                                                <div class="space-y-3">
                                                    <?php foreach ($groupRecipients as $recipient): ?>
                                                        <?php $isChecked = in_array((int) $recipient['id'], $mailboxCompose['recipient_ids'] ?? [], true); ?>
                                                        <label class="flex items-start gap-3 rounded-2xl border border-fleet-line bg-white px-4 py-3">
                                                            <input type="checkbox" name="recipient_ids[]" value="<?= (int) $recipient['id']; ?>" class="mt-1 h-4 w-4 rounded border-fleet-line text-fleet-primary focus:ring-fleet-primary" data-recipient-group="<?= htmlspecialchars($groupKey, ENT_QUOTES, 'UTF-8'); ?>" <?= $isChecked ? 'checked' : ''; ?>>
                                                            <span class="min-w-0">
                                                                <span class="block text-sm font-semibold text-fleet-ink"><?= htmlspecialchars($recipient['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                                <span class="mt-1 block text-xs text-fleet-muted"><?= htmlspecialchars($recipient['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                            </span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </section>
                                        <?php endforeach; ?>
                                    </div>

                                    <input type="text" name="subject" class="vehicle-form-control" value="<?= htmlspecialchars($mailboxCompose['subject'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Message subject">
                                    <textarea name="body" class="vehicle-form-control min-h-40 resize-y py-3" placeholder="Write your message..."><?= htmlspecialchars(($mailboxCompose['body'] ?? '') . ($mailboxCompose['quoted_body'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    <input type="file" name="attachments[]" multiple class="vehicle-form-control h-auto py-2" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx">

                                    <div class="flex flex-wrap gap-3">
                                        <button type="submit" name="message_action" value="send_message" class="inline-flex h-11 items-center rounded-2xl bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active" data-loading-text="Sending...">Send message</button>
                                        <button type="submit" name="message_action" value="save_draft" class="inline-flex h-11 items-center rounded-2xl border border-fleet-line bg-fleet-surface px-5 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted" data-loading-text="Saving...">Save draft</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php elseif ($mailboxIsThreadMode): ?>
                    <div class="px-4 py-4">
                        <div class="overflow-hidden rounded-[28px] border border-fleet-line bg-white shadow-sm">
                            <div class="flex items-center justify-between gap-3 border-b border-fleet-line bg-[#f8fbff] px-5 py-4">
                                <div class="min-w-0">
                                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-fleet-muted"><?= htmlspecialchars($mailboxSelectedFolderLabel, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <h2 class="mt-1 truncate text-xl font-extrabold text-fleet-ink"><?= htmlspecialchars($mailboxActiveThread['subject'] ?? 'Conversation', ENT_QUOTES, 'UTF-8'); ?></h2>
                                </div>
                                <a href="<?= htmlspecialchars($mailboxPageUrl . $mailboxBackFromThreadQuery, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 items-center rounded-2xl border border-fleet-line bg-white px-4 text-sm font-semibold text-fleet-ink hover:bg-fleet-surface-muted">Back</a>
                            </div>

                            <div class="max-h-[75vh] overflow-y-auto px-5 py-5">
                                <div class="space-y-5">
                                    <?php foreach ($mailboxActiveThreadMessages as $threadMessage): ?>
                                        <?php
                                        $recipientLabels = array_map(
                                            static fn(array $recipient): string => $recipient['name'] . ' <' . $recipient['email'] . '>',
                                            $threadMessage['recipients']
                                        );
                                        ?>
                                        <article class="rounded-[24px] border border-fleet-line bg-white shadow-sm">
                                            <div class="border-b border-fleet-line bg-[#f8fbff] px-5 py-4">
                                                <p class="text-base font-extrabold text-fleet-ink"><?= htmlspecialchars($threadMessage['sender_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                <p class="mt-1 text-sm text-fleet-muted"><?= htmlspecialchars($threadMessage['sender_email'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                <p class="mt-2 text-xs text-fleet-muted">To: <?= htmlspecialchars($recipientLabels === [] ? 'No recipients' : implode(', ', $recipientLabels), ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                            <div class="px-5 py-5">
                                                <div class="whitespace-pre-line text-sm leading-7 text-fleet-ink"><?= htmlspecialchars($threadMessage['body'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="px-4 py-4">
                        <div class="overflow-hidden rounded-[28px] border border-fleet-line bg-white shadow-sm">
                            <div class="border-b border-fleet-line px-5 py-4">
                                <form action="<?= htmlspecialchars($mailboxPageUrl, ENT_QUOTES, 'UTF-8'); ?>" method="get">
                                    <input type="hidden" name="folder" value="<?= htmlspecialchars($mailboxFolder, ENT_QUOTES, 'UTF-8'); ?>">
                                    <label class="relative block">
                                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <circle cx="11" cy="11" r="8"></circle>
                                                <path d="m21 21-4.3-4.3"></path>
                                            </svg>
                                        </span>
                                        <input type="search" name="q" class="h-12 w-full rounded-2xl border border-fleet-line bg-[#f7f9ff] py-2 pl-12 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search in mail" value="<?= htmlspecialchars($mailboxSearch, ENT_QUOTES, 'UTF-8'); ?>">
                                    </label>
                                </form>
                            </div>

                            <div class="max-h-[75vh] overflow-y-auto">
                                <?php if ($mailboxThreadList === []): ?>
                                    <div class="px-6 py-16 text-center">
                                        <h3 class="text-lg font-extrabold text-fleet-ink">No conversations found</h3>
                                        <p class="mt-2 text-sm text-fleet-muted">This folder has no messages for the current search.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="divide-y divide-fleet-line-soft">
                                        <?php foreach ($mailboxThreadList as $thread): ?>
                                            <?php $isUnreadThread = (int) $thread['unread_count'] > 0; ?>
                                            <a href="<?= htmlspecialchars($mailboxPageUrl . $mailboxPageQuery(['view' => 'thread', 'thread' => (int) $thread['thread_id']]), ENT_QUOTES, 'UTF-8'); ?>" class="block px-5 py-4 transition <?= $isUnreadThread ? 'bg-[#f8fbff] hover:bg-[#f1f6ff]' : 'hover:bg-[#f7f9ff]'; ?>">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm <?= $isUnreadThread ? 'font-extrabold text-fleet-ink' : 'font-semibold text-fleet-ink'; ?>"><?= htmlspecialchars($thread['sender_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <p class="mt-1 truncate text-sm font-semibold text-fleet-ink"><?= htmlspecialchars($thread['subject'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <p class="mt-2 truncate text-sm text-fleet-muted"><?= htmlspecialchars($thread['snippet'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                    </div>
                                                    <p class="text-xs font-semibold text-fleet-muted"><?= htmlspecialchars($thread['last_message_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</section>

<script>
document.querySelectorAll('[data-select-recipient-group]').forEach((button) => {
    button.addEventListener('click', () => {
        const group = button.getAttribute('data-select-recipient-group');
        document.querySelectorAll(`input[data-recipient-group="${group}"]`).forEach((checkbox) => {
            checkbox.checked = true;
        });
    });
});
</script>
