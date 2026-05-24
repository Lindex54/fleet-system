<?php
// Static frontend page for email communication archive.
$activePage = 'history';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

$messages = [
    ['datetime' => '18 May 2026, 16:31', 'subject' => 'Inspection', 'sender' => 'Simali Habert', 'drivers' => 1, 'officers' => 0, 'type' => 'Manual', 'message' => 'Inspection reminder sent to the assigned driver.'],
    ['datetime' => '17 May 2026, 12:20', 'subject' => 'Ghhhjj', 'sender' => 'Simali Habert', 'drivers' => 1, 'officers' => 0, 'type' => 'Manual', 'message' => 'Vehicle log follow-up message.'],
];

$hasMessages = count($messages) > 0;
$totalMessages = count($messages);
$driverEmails = array_sum(array_column($messages, 'drivers'));
$officerEmails = array_sum(array_column($messages, 'officers'));
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Communication History</h1>
            <p class="mt-2 text-sm text-fleet-muted">Archive of all emails sent to drivers and officers</p>
        </div>

        <section class="mb-8 grid gap-5 md:grid-cols-3">
            <article class="interactive-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <div class="flex items-center gap-5">
                    <svg class="h-6 w-6 text-fleet-sidebar" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a2 2 0 0 1-2.06 0L2 7"></path>
                    </svg>
                    <div>
                        <p class="text-2xl font-extrabold leading-6 text-fleet-ink"><?= $totalMessages; ?></p>
                        <p class="mt-2 text-sm text-fleet-muted">Total Messages Sent</p>
                    </div>
                </div>
            </article>

            <article class="interactive-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <div class="flex items-center gap-5">
                    <svg class="h-6 w-6 text-fleet-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.9"></path>
                        <path d="M16 3.1a4 4 0 0 1 0 7.8"></path>
                    </svg>
                    <div>
                        <p class="text-2xl font-extrabold leading-6 text-fleet-ink"><?= $driverEmails; ?></p>
                        <p class="mt-2 text-sm text-fleet-muted">Driver Emails Sent</p>
                    </div>
                </div>
            </article>

            <article class="interactive-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <div class="flex items-center gap-5">
                    <svg class="h-6 w-6 text-fleet-success" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.9"></path>
                        <path d="M16 3.1a4 4 0 0 1 0 7.8"></path>
                    </svg>
                    <div>
                        <p class="text-2xl font-extrabold leading-6 text-fleet-ink"><?= $officerEmails; ?></p>
                        <p class="mt-2 text-sm text-fleet-muted">Officer Emails Sent</p>
                    </div>
                </div>
            </article>
        </section>

        <section class="<?= $hasMessages ? 'hidden' : 'flex'; ?> min-h-[340px] items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-200 text-fleet-muted">
                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 12a9 9 0 1 0 3-6.7"></path>
                        <path d="M3 3v6h6"></path>
                        <path d="M12 7v5l3 2"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-xl font-extrabold text-fleet-ink">No communication history</h2>
                <p class="mt-2 text-base text-fleet-muted">Sent emails will appear here.</p>
            </div>
        </section>

        <section class="<?= $hasMessages ? 'block' : 'hidden'; ?> overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="border-b border-fleet-line p-6">
                <label class="relative block">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                    </span>
                    <input id="communication-history-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-12 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search by subject, sender or message...">
                </label>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-fleet-line text-left text-sm" data-communication-history-table>
                    <thead class="bg-slate-50 text-fleet-muted">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Date &amp; Time</th>
                            <th class="px-5 py-4 font-semibold">Subject</th>
                            <th class="px-5 py-4 font-semibold">Sent By</th>
                            <th class="px-5 py-4 font-semibold">Drivers</th>
                            <th class="px-5 py-4 font-semibold">Officers</th>
                            <th class="px-5 py-4 font-semibold">Type</th>
                            <th class="px-5 py-4 text-right font-semibold"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-fleet-line">
                        <?php foreach ($messages as $message): ?>
                            <tr class="communication-history-row transition hover:bg-blue-50/40" data-search="<?= htmlspecialchars(strtolower(implode(' ', $message)), ENT_QUOTES, 'UTF-8'); ?>">
                                <td class="whitespace-nowrap px-5 py-4 text-fleet-muted">
                                    <span class="inline-flex items-center gap-2">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M12 6v6l4 2"></path>
                                        </svg>
                                        <?= htmlspecialchars($message['datetime'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-extrabold text-fleet-ink"><?= htmlspecialchars($message['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-muted"><?= htmlspecialchars($message['sender'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4">
                                    <?php if ($message['drivers'] > 0): ?>
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-fleet-warning px-3 py-1 text-xs font-extrabold text-white">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                            </svg>
                                            <?= (int) $message['drivers']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-fleet-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 text-fleet-muted"><?= $message['officers'] > 0 ? (int) $message['officers'] : '&mdash;'; ?></td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-lg bg-fleet-sidebar px-3 py-1 text-xs font-extrabold text-white shadow-fleet-card"><?= htmlspecialchars($message['type'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-fleet-ink transition hover:bg-blue-50 hover:text-fleet-primary" aria-label="View message">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
