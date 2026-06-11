<?php
$activePage = 'recent-logs';
require_once __DIR__ . '/../../handlers/recent-logs.php';
extract(recentLogsFetchPageData());
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Recent Logs</h1>
                <p class="mt-2 text-sm text-fleet-muted">Monitor user sign-ins, sign-outs, and recent authentication activity across the system.</p>
            </div>
            <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                <span class="text-xl leading-none">&#9776;</span>
            </button>
        </div>

        <section class="mb-8 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <p class="text-sm font-semibold text-fleet-muted">Total Auth Events</p>
                <p class="mt-3 text-3xl font-extrabold text-fleet-ink"><?= (int) $recentLogsSummary['total']; ?></p>
            </article>
            <article class="rounded-lg border border-green-200 bg-green-50 p-6 shadow-fleet-card">
                <p class="text-sm font-semibold text-green-700">Successful Logins</p>
                <p class="mt-3 text-3xl font-extrabold text-green-800"><?= (int) $recentLogsSummary['logins']; ?></p>
            </article>
            <article class="rounded-lg border border-blue-200 bg-blue-50 p-6 shadow-fleet-card">
                <p class="text-sm font-semibold text-blue-700">Logouts</p>
                <p class="mt-3 text-3xl font-extrabold text-blue-800"><?= (int) $recentLogsSummary['logouts']; ?></p>
            </article>
            <article class="rounded-lg border border-red-200 bg-red-50 p-6 shadow-fleet-card">
                <p class="text-sm font-semibold text-red-700">Failed Logins</p>
                <p class="mt-3 text-3xl font-extrabold text-red-800"><?= (int) $recentLogsSummary['failed']; ?></p>
            </article>
        </section>

        <section class="overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="border-b border-fleet-line p-6">
                <form action="<?= htmlspecialchars($recentLogsPageUrl, ENT_QUOTES, 'UTF-8'); ?>" method="get" class="flex flex-col gap-3 md:flex-row">
                    <label class="relative block flex-1">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                        </span>
                        <input type="search" name="q" value="<?= htmlspecialchars($recentLogsSearch, ENT_QUOTES, 'UTF-8'); ?>" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-12 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search by username, email, role, event or IP">
                    </label>
                    <button type="submit" class="h-11 rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Filter Logs</button>
                </form>
            </div>

            <?php if (!$recentLogsHasRows): ?>
                <div class="flex min-h-[280px] items-center justify-center px-6 py-12">
                    <div class="text-center">
                        <h2 class="text-xl font-extrabold text-fleet-ink">No recent logs found</h2>
                        <p class="mt-2 text-sm text-fleet-muted">Authentication events will appear here once users start logging in and out.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px] text-left text-sm">
                        <thead class="bg-fleet-surface-muted text-fleet-muted">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Date &amp; Time</th>
                                <th class="px-5 py-4 font-semibold">User</th>
                                <th class="px-5 py-4 font-semibold">Role</th>
                                <th class="px-5 py-4 font-semibold">Event</th>
                                <th class="px-5 py-4 font-semibold">Description</th>
                                <th class="px-5 py-4 font-semibold">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-fleet-line-soft">
                            <?php foreach ($recentLogRows as $row): ?>
                                <?php $eventTone = $row['event_type'] === 'login_failed' ? 'border-red-200 bg-red-50 text-red-700' : ($row['event_type'] === 'logout' ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-green-200 bg-green-50 text-green-700'); ?>
                                <tr class="hover:bg-fleet-surface-muted/60">
                                    <td class="px-5 py-4 text-fleet-muted"><?= htmlspecialchars($row['occurred_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-5 py-4">
                                        <p class="font-extrabold text-fleet-ink"><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class="text-xs text-fleet-muted"><?= htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?> . <?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </td>
                                    <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars(ucfirst($row['role']), ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full border px-3 py-1 text-xs font-semibold <?= $eventTone; ?>"><?= htmlspecialchars($row['event_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </td>
                                    <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($row['event_description'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-5 py-4 text-fleet-muted"><?= htmlspecialchars($row['ip_address'], ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
