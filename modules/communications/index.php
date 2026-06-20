<?php
$activePage = 'communications';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/internal-messages.php';
fleetAuthRequireAdmin();
$mailboxContext = fleetMessageRequireContext();
extract(fleetMessageFetchMailboxPageData($mailboxContext));
$mailboxPageUrl = $mailboxContext['page_url'];
$mailboxCurrentUserEmail = $mailboxContext['email'];
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1536px] px-4 py-5 sm:px-6 lg:px-8">
        <div class="dashboard-shell">
            <div class="mb-7 flex flex-wrap items-start justify-between gap-4 border-b border-fleet-line pb-5">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Internal Communications</h1>
                    <p class="mt-2 text-sm text-fleet-muted">Secure Gmail-like messaging for registered fleet system users only.</p>
                </div>
                <span class="inline-flex rounded-full bg-fleet-primary-soft px-4 py-2 text-sm font-semibold text-fleet-primary">
                    <?= (int) $mailboxUnreadCount; ?> unread
                </span>
            </div>

            <?php include __DIR__ . '/../../includes/message-mailbox.php'; ?>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
