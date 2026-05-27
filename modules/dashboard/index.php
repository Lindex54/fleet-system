<?php
// Main dashboard entry point for the fleet management system.
$activePage = 'dashboard';
require_once __DIR__ . '/../../includes/data.php';
extract(fleetData('dashboard'));
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">BUESMIS Dashboard</h1>
                <p class="mt-1 text-sm text-fleet-muted">Busitema University Estates Management Information System</p>
            </div>
            <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                <span class="text-xl leading-none">&#9776;</span>
            </button>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($metrics as $metric): ?>
                <article class="flex min-h-24 items-start justify-between rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                    <div>
                        <p class="text-sm font-medium text-fleet-muted"><?= htmlspecialchars($metric['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($metric['value'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-fleet-surface-muted text-sm font-extrabold text-fleet-sidebar"><?= htmlspecialchars($metric['icon'], ENT_QUOTES, 'UTF-8'); ?></span>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="mt-6 grid gap-4 lg:grid-cols-3">
            <?php foreach ($noticeCards as $notice): ?>
                <article class="flex items-center gap-4 rounded-lg border <?= $notice['tone'] === 'primary' ? 'border-blue-200 bg-fleet-primary-soft text-fleet-primary' : 'border-sky-200 bg-fleet-info-soft text-fleet-info'; ?> p-5 shadow-fleet-card">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-extrabold"><?= htmlspecialchars($notice['icon'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <div>
                        <p class="text-sm font-extrabold"><?= htmlspecialchars($notice['count'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-xs"><?= htmlspecialchars($notice['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="mt-6 grid gap-4 xl:grid-cols-3">
            <article class="min-h-[360px] rounded-lg border border-green-300 bg-fleet-success-soft p-7 text-fleet-success shadow-fleet-card">
                <div class="flex items-center gap-5">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-green-200 text-sm font-extrabold">V</span>
                    <div>
                        <p class="text-4xl font-extrabold text-fleet-success-strong">3</p>
                        <p class="mt-1 text-base font-semibold">Active &amp; Available Vehicles</p>
                        <p class="text-sm">60% of total fleet (5)</p>
                    </div>
                </div>
            </article>

            <article class="min-h-[360px] rounded-lg border border-red-300 bg-fleet-danger-soft p-7 text-fleet-danger shadow-fleet-card">
                <div class="flex items-start gap-3">
                    <span class="text-xl">!</span>
                    <div class="flex-1">
                        <h2 class="text-lg font-extrabold text-fleet-danger-strong">Immediate Maintenance Required</h2>
                        <p class="mt-4 text-sm"><strong>1</strong> vehicle with unresolved pre-inspection issues:</p>
                        <div class="mt-6 flex items-center justify-between gap-4">
                            <div>
                                <p class="font-extrabold text-fleet-danger-strong">UBP 401F</p>
                                <p class="text-xs">TOYOTA LAND CRUISER - Vice Chancellor</p>
                            </div>
                            <span class="rounded-full bg-fleet-badge-red px-3 py-1 text-xs font-bold text-fleet-danger-strong">Needs Repair</span>
                        </div>
                        <a href="#" class="mt-6 inline-block text-sm font-semibold text-fleet-danger">View pre-inspection reports &rarr;</a>
                    </div>
                </div>
            </article>

            <article class="rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                <h2 class="mb-4 text-base font-extrabold text-fleet-ink">Vehicle Availability by Department</h2>
                <div class="relative flex h-40 items-end gap-4 border-b border-l border-fleet-line px-4 pt-6">
                    <?php foreach ($departments as $department): ?>
                        <div class="chart-bar-group group flex flex-1 items-end gap-1" tabindex="0" aria-label="<?= htmlspecialchars($department['name'] . ': ' . $department['active'] . ' active, ' . $department['maintenance'] . ' maintenance, ' . $department['grounded'] . ' grounded', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="chart-tooltip">
                                <p class="mb-2 font-bold text-fleet-ink"><?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-fleet-success">Active : <?= (int) $department['active']; ?></p>
                                <p class="text-fleet-warning">Maintenance : <?= (int) $department['maintenance']; ?></p>
                                <p class="text-fleet-danger">Grounded : <?= (int) $department['grounded']; ?></p>
                            </div>
                            <?php if ($department['active'] > 0): ?>
                                <span class="chart-bar block h-16 flex-1 rounded-t bg-fleet-success"></span>
                            <?php endif; ?>
                            <?php if ($department['maintenance'] > 0): ?>
                                <span class="chart-bar block h-16 flex-1 rounded-t bg-fleet-warning"></span>
                            <?php endif; ?>
                            <?php if ($department['grounded'] > 0): ?>
                                <span class="chart-bar block h-16 flex-1 rounded-t bg-fleet-danger"></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 flex justify-center gap-5 text-xs text-fleet-muted">
                    <span class="flex items-center gap-1"><i class="h-3 w-3 rounded-full bg-fleet-success"></i> Active</span>
                    <span class="flex items-center gap-1"><i class="h-3 w-3 rounded-full bg-fleet-warning"></i> Maintenance</span>
                    <span class="flex items-center gap-1"><i class="h-3 w-3 rounded-full bg-fleet-danger"></i> Grounded</span>
                </div>
                <div class="mt-5 divide-y divide-fleet-line-soft text-sm">
                    <?php foreach ($departments as $department): ?>
                        <div class="flex items-center justify-between py-2">
                            <span class="font-medium text-fleet-ink"><?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="text-xs font-bold">
                                <?php if ($department['active'] > 0): ?>
                                    <span class="text-fleet-success"><?= $department['active']; ?> active</span>
                                <?php endif; ?>
                                <?php if ($department['maintenance'] > 0): ?>
                                    <span class="ml-2 text-fleet-warning"><?= $department['maintenance']; ?> maint.</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
        </section>

        <section class="mt-6 rounded-lg border border-fleet-warning bg-fleet-surface shadow-fleet-card">
            <div class="flex items-center justify-between gap-4 border-b border-fleet-line-soft px-5 py-4">
                <h2 class="text-base font-extrabold text-fleet-ink"><span class="mr-2 text-fleet-warning">S</span>Service Due Alerts</h2>
                <span class="rounded-full bg-fleet-badge-red px-3 py-1 text-xs font-bold text-fleet-danger">8 Overdue</span>
            </div>
            <div class="dashboard-scroll max-h-72 divide-y divide-fleet-line-soft overflow-y-auto">
                <?php foreach ($serviceDueAlerts as $alert): ?>
                    <div class="grid gap-3 bg-fleet-danger-soft px-5 py-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <p class="font-extrabold">
                                <?= htmlspecialchars($alert['vehicle'], ENT_QUOTES, 'UTF-8'); ?>
                                <span class="ml-2 rounded px-2 py-1 text-xs font-bold <?= $alert['typeTone'] === 'purple' ? 'bg-purple-100 text-purple-700' : 'bg-fleet-badge-blue text-fleet-primary'; ?>">
                                    <?= htmlspecialchars($alert['type'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </p>
                            <p class="mt-1 text-sm text-fleet-muted"><?= htmlspecialchars($alert['model'], ENT_QUOTES, 'UTF-8'); ?> - <?= htmlspecialchars($alert['department'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-fleet-muted"><?= htmlspecialchars($alert['detail'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="flex items-center gap-4 text-sm font-extrabold text-fleet-danger">
                            <span>OVERDUE</span>
                            <span>&rsaquo;</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="px-5 py-3">
                <a href="#" class="text-sm font-semibold text-fleet-sidebar hover:text-fleet-primary">Schedule maintenance &rarr;</a>
            </div>
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-2">
            <article class="overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
                <div class="flex items-center justify-between px-5 py-5">
                    <h2 class="text-base font-extrabold text-fleet-ink">Recent Vehicle Logs</h2>
                    <a href="#" class="text-sm font-semibold text-fleet-sidebar hover:text-fleet-primary">View all &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[620px] text-left text-sm">
                        <thead class="bg-fleet-surface-muted text-fleet-muted">
                            <tr>
                                <th class="px-5 py-3 font-bold">Date</th>
                                <th class="px-5 py-3 font-bold">Vehicle</th>
                                <th class="px-5 py-3 font-bold">Driver</th>
                                <th class="px-5 py-3 font-bold">Destination</th>
                                <th class="px-5 py-3 font-bold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-fleet-line-soft">
                            <?php foreach ($vehicleLogs as $log): ?>
                                <tr>
                                    <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($log['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-5 py-4 font-bold text-fleet-ink"><?= htmlspecialchars($log['vehicle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($log['driver'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-5 py-4 text-fleet-muted"><?= htmlspecialchars($log['destination'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-md border border-blue-200 bg-fleet-primary-soft px-3 py-1 text-xs font-semibold text-fleet-primary"><?= htmlspecialchars($log['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
                <div class="flex items-center justify-between px-5 py-5">
                    <h2 class="text-base font-extrabold text-fleet-ink"><span class="mr-2 text-fleet-warning">!</span>Active Maintenance</h2>
                    <a href="#" class="text-sm font-semibold text-fleet-sidebar hover:text-fleet-primary">View all &rarr;</a>
                </div>
                <div class="flex min-h-28 items-center justify-center px-5 pb-8 text-sm text-fleet-muted">
                    No active maintenance
                </div>
            </article>
        </section>

        <section class="mt-6 rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="flex items-center justify-between gap-4 px-5 py-5">
                <h2 class="text-base font-extrabold text-fleet-ink"><span class="mr-2 text-fleet-success">U</span>Active Users</h2>
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-fleet-success">1 online</span>
            </div>
            <div class="flex items-center justify-between gap-4 px-5 pb-5">
                <div class="flex items-center gap-3">
                    <span class="h-2 w-2 rounded-full bg-green-300"></span>
                    <div>
                        <p class="text-sm font-semibold text-fleet-ink">Simali Habert</p>
                        <p class="text-xs text-fleet-muted">simalihabert@gmail.com</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-fleet-sidebar">admin</span>
                    <p class="mt-2 text-xs text-fleet-muted">1 minute ago</p>
                </div>
            </div>
        </section>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
