<?php
// Static frontend page for composing fleet communication emails.
$activePage = 'communications';
require_once __DIR__ . '/../../includes/data.php';
extract(fleetData('communications'));
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-7">
            <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Communications</h1>
            <p class="mt-2 text-sm text-fleet-muted">Send messages to drivers and officers via email</p>
        </div>

        <section class="mb-8 rounded-lg border border-fleet-warning bg-fleet-warning-soft px-5 py-4 text-fleet-warning-strong shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                    <path d="m22 7-8.97 5.7a2 2 0 0 1-2.06 0L2 7"></path>
                </svg>
                <p class="text-base leading-7">
                    Emails are delivered to recipients who are <strong>registered app users</strong>. Drivers must be invited to the app and have their email linked. Officers added manually must also have app accounts for delivery to succeed.
                </p>
            </div>
        </section>

        <div class="grid gap-7 xl:grid-cols-[1fr_1fr]">
            <section class="interactive-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <div class="mb-7 flex items-center gap-3">
                    <svg class="h-5 w-5 text-fleet-ink" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a2 2 0 0 1-2.06 0L2 7"></path>
                    </svg>
                    <h2 class="text-xl font-extrabold text-fleet-ink">Compose Message</h2>
                </div>

                <form>
                    <div class="space-y-6">
                        <div>
                            <label for="communication-subject" class="mb-2 block text-sm font-semibold text-fleet-ink">Subject *</label>
                            <input id="communication-subject" type="text" class="vehicle-form-control" placeholder="e.g. Vehicle Log Submission Reminder">
                        </div>

                        <div>
                            <label for="communication-message" class="mb-2 block text-sm font-semibold text-fleet-ink">Message *</label>
                            <textarea id="communication-message" rows="9" class="vehicle-form-control min-h-56 resize-y py-3" placeholder="Type your message here..."></textarea>
                        </div>
                    </div>

                    <div class="mt-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-fleet-muted" data-communication-recipient-label>No recipients selected</p>
                        <button type="button" data-communication-send class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-400 px-5 text-sm font-semibold text-white shadow-fleet-card transition hover:bg-fleet-sidebar disabled:cursor-not-allowed disabled:opacity-70" disabled>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m22 2-7 20-4-9-9-4Z"></path>
                                <path d="M22 2 11 13"></path>
                            </svg>
                            <span>Send</span>
                        </button>
                    </div>
                </form>
            </section>

            <div class="space-y-6">
                <section class="interactive-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                    <div class="mb-6 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-fleet-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.9"></path>
                                <path d="M16 3.1a4 4 0 0 1 0 7.8"></path>
                            </svg>
                            <h2 class="text-xl font-extrabold text-fleet-ink">Drivers</h2>
                            <span class="inline-flex h-7 min-w-9 items-center justify-center rounded-lg bg-fleet-warning px-3 text-sm font-extrabold text-white" data-driver-recipient-count>0</span>
                        </div>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-fleet-ink">
                            <input type="checkbox" data-select-all-drivers class="h-4 w-4 rounded border-fleet-line text-fleet-primary focus:ring-fleet-primary" <?= $hasDriverEmails ? '' : 'disabled'; ?>>
                            <span>Select All</span>
                        </label>
                    </div>

                    <?php if ($hasDriverEmails): ?>
                        <div class="space-y-3">
                            <?php foreach ($drivers as $driver): ?>
                                <label class="flex cursor-pointer items-center justify-between gap-4 rounded-lg border border-fleet-line-soft px-4 py-3 transition hover:border-fleet-primary hover:bg-blue-50/40">
                                    <span>
                                        <span class="block text-sm font-semibold text-fleet-ink"><?= htmlspecialchars($driver['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="block text-xs text-fleet-muted"><?= htmlspecialchars($driver['email'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </span>
                                    <input type="checkbox" data-driver-recipient class="h-4 w-4 rounded border-fleet-line text-fleet-primary focus:ring-fleet-primary">
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-base text-fleet-muted">No drivers with email addresses found.</p>
                    <?php endif; ?>
                </section>

                <section class="interactive-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                    <div class="mb-5 flex items-center gap-3">
                        <svg class="h-5 w-5 text-fleet-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.9"></path>
                            <path d="M16 3.1a4 4 0 0 1 0 7.8"></path>
                        </svg>
                        <h2 class="text-xl font-extrabold text-fleet-ink">Officers / Other Recipients</h2>
                        <span class="inline-flex h-7 min-w-9 items-center justify-center rounded-lg bg-fleet-warning px-3 text-sm font-extrabold text-white" data-officer-recipient-count>0</span>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input id="officer-email-input" type="text" class="vehicle-form-control" placeholder="Enter email address(es), comma-separated">
                        <button type="button" data-add-officer-recipient class="inline-flex h-11 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface px-5 text-sm font-semibold text-fleet-ink shadow-sm transition hover:bg-slate-50">Add</button>
                    </div>

                    <div class="mt-4 hidden flex-wrap gap-2" data-officer-recipient-list></div>
                    <p class="mt-4 text-sm text-fleet-muted">Add officer or staff email addresses above.</p>
                </section>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
