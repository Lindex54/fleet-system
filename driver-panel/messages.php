<?php
$activePage = 'driver-messages';
require_once __DIR__ . '/../handlers/driver-panel.php';
require_once __DIR__ . '/../includes/internal-messages.php';
extract(driverPanelFetchMessagesPageData());
$mailboxContext = fleetMessageRequireContext();
extract(fleetMessageFetchMailboxPageData($mailboxContext));
$mailboxPageUrl = $mailboxContext['page_url'];
$mailboxCurrentUserEmail = $mailboxContext['email'];
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="driver-panel-page min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1536px] px-4 py-5 sm:px-6 lg:px-8">
        <div class="dashboard-shell driver-page-shell">
            <div class="mb-6 flex items-start justify-between gap-4 border-b border-fleet-line pb-5">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Messages</h1>
                    <p class="mt-1 text-sm text-fleet-muted">Internal conversations with administrators plus your incident reporting tools.</p>
                </div>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>

            <section class="driver-stat-grid grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="driver-stat-card rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                    <p class="text-sm font-medium text-fleet-muted">Unread Messages</p>
                    <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= (int) $mailboxUnreadCount; ?></p>
                </article>
                <article class="driver-stat-card rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                    <p class="text-sm font-medium text-fleet-muted">Inbox Threads</p>
                    <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= (int) ($mailboxFolderCounts['inbox'] ?? 0); ?></p>
                </article>
                <article class="driver-stat-card rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                    <p class="text-sm font-medium text-fleet-muted">Drafts</p>
                    <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= (int) ($mailboxFolderCounts['drafts'] ?? 0); ?></p>
                </article>
                <article class="driver-stat-card rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                    <p class="text-sm font-medium text-fleet-muted">Incident Reports</p>
                    <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= count($incidentHistory); ?></p>
                </article>
            </section>

            <section class="mt-6">
                <?php include __DIR__ . '/../includes/message-mailbox.php'; ?>
            </section>

            <?php if (!empty($messagesNotification)): ?>
                <?php $isIncidentSuccess = ($messagesNotification['type'] ?? '') === 'success'; ?>
                <section
                    data-flash-notice
                    data-flash-type="<?= $isIncidentSuccess ? 'success' : 'error'; ?>"
                    class="pointer-events-none fixed left-1/2 top-8 z-[70] hidden w-[min(92vw,34rem)] -translate-x-1/2 overflow-hidden rounded-2xl border bg-white shadow-2xl transition duration-500 <?= $isIncidentSuccess ? 'border-green-200 text-green-900' : 'border-red-200 text-red-900'; ?>"
                >
                    <div class="absolute inset-x-0 top-0 h-1.5 <?= $isIncidentSuccess ? 'bg-green-500' : 'bg-red-500'; ?>"></div>
                    <div class="flex items-center gap-4 px-5 py-4 sm:px-6">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-sm font-extrabold shadow-lg <?= $isIncidentSuccess ? 'bg-green-600 text-white shadow-green-200' : 'bg-red-600 text-white shadow-red-200'; ?>">
                            <?= $isIncidentSuccess ? 'OK' : '!'; ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-sm font-extrabold uppercase tracking-[0.18em]">
                                        <?= htmlspecialchars($messagesNotification['title'] ?? 'Incident update', ENT_QUOTES, 'UTF-8'); ?>
                                    </h2>
                                    <p class="mt-1 text-sm leading-6 text-fleet-ink">
                                        <?= htmlspecialchars($messagesNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </div>
                                <button type="button" data-dismiss-flash class="pointer-events-auto inline-flex h-9 w-9 items-center justify-center rounded-full border text-base font-bold transition <?= $isIncidentSuccess ? 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'; ?>" aria-label="Dismiss incident notification">x</button>
                            </div>
                            <div class="mt-3 h-1.5 overflow-hidden rounded-full <?= $isIncidentSuccess ? 'bg-green-100' : 'bg-red-100'; ?>">
                                <div data-flash-progress class="h-full w-full origin-left rounded-full <?= $isIncidentSuccess ? 'bg-green-600' : 'bg-red-600'; ?>"></div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <section class="mt-6 grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                <article class="driver-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-fleet-ink">Emergency / Incident Reporting</h2>
                            <p class="mt-1 text-sm text-fleet-muted">Report breakdowns, accidents, and unusual vehicle issues quickly.</p>
                        </div>
                        <span class="inline-flex rounded-lg border border-red-200 bg-fleet-danger-soft px-3 py-1 text-xs font-semibold text-fleet-danger">Action Needed</span>
                    </div>

                    <form action="<?= htmlspecialchars($messagesFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="space-y-5" data-fleet-ajax="true">
                        <input type="hidden" name="driver_panel_action" value="submit_incident">
                        <div data-fleet-feedback-host></div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-fleet-ink">Incident Type *</span>
                                <select name="incident_type" class="vehicle-form-control">
                                    <option value="breakdown" <?= (($incidentFormData['incident_type'] ?? 'breakdown') === 'breakdown') ? 'selected' : ''; ?>>Report Breakdown</option>
                                    <option value="accident" <?= (($incidentFormData['incident_type'] ?? '') === 'accident') ? 'selected' : ''; ?>>Report Accident</option>
                                    <option value="unusual_issue" <?= (($incidentFormData['incident_type'] ?? '') === 'unusual_issue') ? 'selected' : ''; ?>>Report Unusual Vehicle Issue</option>
                                </select>
                            </label>

                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-fleet-ink">Incident Date *</span>
                                <input name="incident_date" type="date" class="vehicle-form-control" value="<?= htmlspecialchars($incidentFormData['incident_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" required>
                            </label>

                            <label class="block md:col-span-2">
                                <span class="mb-2 block text-sm font-semibold text-fleet-ink">Location</span>
                                <input name="location" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($incidentFormData['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Where did the incident happen?">
                            </label>

                            <label class="block md:col-span-2">
                                <span class="mb-2 block text-sm font-semibold text-fleet-ink">Subject *</span>
                                <input name="subject" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($incidentFormData['subject'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Short incident title" required>
                            </label>

                            <label class="block md:col-span-2">
                                <span class="mb-2 block text-sm font-semibold text-fleet-ink">Description *</span>
                                <textarea name="description" class="vehicle-form-control min-h-28 resize-y py-3" placeholder="Describe the breakdown, accident, or unusual issue in detail" required><?= htmlspecialchars($incidentFormData['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </label>

                            <label class="block md:col-span-2">
                                <span class="mb-2 block text-sm font-semibold text-fleet-ink">Urgency *</span>
                                <select name="urgency" class="vehicle-form-control">
                                    <option value="low" <?= (($incidentFormData['urgency'] ?? '') === 'low') ? 'selected' : ''; ?>>Low</option>
                                    <option value="medium" <?= (($incidentFormData['urgency'] ?? 'medium') === 'medium') ? 'selected' : ''; ?>>Medium</option>
                                    <option value="high" <?= (($incidentFormData['urgency'] ?? '') === 'high') ? 'selected' : ''; ?>>High</option>
                                    <option value="critical" <?= (($incidentFormData['urgency'] ?? '') === 'critical') ? 'selected' : ''; ?>>Critical</option>
                                </select>
                            </label>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex h-10 items-center rounded-lg bg-fleet-danger px-5 text-sm font-semibold text-white shadow-sm hover:bg-red-700" data-loading-text="Submitting Incident...">
                                Submit Incident Report
                            </button>
                        </div>
                    </form>
                </article>

                <article class="driver-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-fleet-ink">Incident History</h2>
                            <p class="mt-1 text-sm text-fleet-muted">Recently reported emergency and vehicle incidents.</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-fleet-sidebar"><?= count($incidentHistory); ?> report(s)</span>
                    </div>

                    <div class="mt-5 space-y-4">
                        <?php if ($incidentHistory === []): ?>
                            <div class="rounded-lg border border-dashed border-fleet-line px-4 py-6 text-center text-sm text-fleet-muted">
                                No incident reports have been submitted yet.
                            </div>
                        <?php else: ?>
                            <?php foreach ($incidentHistory as $incident): ?>
                                <div class="driver-subcard rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-extrabold text-fleet-ink"><?= htmlspecialchars($incident['subject'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="mt-1 text-xs text-fleet-muted"><?= htmlspecialchars($incident['date'] . ' • ' . $incident['vehicle'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                        <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($incident['status_classes'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($incident['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <span class="inline-flex rounded-lg border border-blue-200 bg-fleet-primary-soft px-3 py-1 text-xs font-semibold text-fleet-primary"><?= htmlspecialchars($incident['type'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="inline-flex rounded-lg border border-orange-200 bg-fleet-warning-soft px-3 py-1 text-xs font-semibold text-fleet-warning-strong">Urgency: <?= htmlspecialchars($incident['urgency'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <p class="mt-3 text-sm text-fleet-muted">Location: <span class="font-semibold text-fleet-ink"><?= htmlspecialchars($incident['location'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                    <p class="mt-3 text-sm leading-6 text-fleet-muted"><?= htmlspecialchars($incident['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>
            </section>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
