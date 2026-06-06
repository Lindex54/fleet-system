<?php
$activePage = 'driver-history';
require_once __DIR__ . '/../handlers/driver-panel.php';
extract(driverPanelFetchHistoryPageData());
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="driver-panel-page min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1536px] px-4 py-5 sm:px-6 lg:px-8">
        <div class="dashboard-shell driver-page-shell">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">History</h1>
                <p class="mt-1 text-sm text-fleet-muted">Driver trip and inspection history</p>
            </div>
            <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                <span class="text-xl leading-none">&#9776;</span>
            </button>
        </div>

        <section class="driver-stat-grid grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="driver-stat-card rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                <p class="text-sm font-medium text-fleet-muted">Driver</p>
                <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($driverProfile['name'] ?? 'Unavailable', ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
            <article class="driver-stat-card rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                <p class="text-sm font-medium text-fleet-muted">Trip Records</p>
                <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= count($tripHistory); ?></p>
            </article>
            <article class="driver-stat-card rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                <p class="text-sm font-medium text-fleet-muted">Inspection Reports</p>
                <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= count($reportHistory); ?></p>
            </article>
            <article class="driver-stat-card rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                <p class="text-sm font-medium text-fleet-muted">Assigned Vehicle</p>
                <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['registration_no'] ?? 'Not assigned', ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="driver-table-card rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
                <div class="flex items-center justify-between gap-4 border-b border-fleet-line-soft px-5 py-5">
                    <div>
                        <h2 class="text-lg font-extrabold text-fleet-ink">My Trip History</h2>
                        <p class="mt-1 text-sm text-fleet-muted">Trips recorded for this driver only</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-fleet-sidebar"><?= count($tripHistory); ?> trip(s)</span>
                </div>

                <?php if ($tripHistory === []): ?>
                    <div class="px-5 py-8 text-center text-sm text-fleet-muted">No trip history has been recorded yet.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[920px] text-left text-sm" data-driver-history-table>
                            <thead class="bg-fleet-surface-muted text-fleet-muted">
                                <tr>
                                    <th class="px-5 py-4 font-semibold">Date</th>
                                    <th class="px-5 py-4 font-semibold">Vehicle</th>
                                    <th class="px-5 py-4 font-semibold">Route</th>
                                    <th class="px-5 py-4 font-semibold">Purpose</th>
                                    <th class="px-5 py-4 font-semibold">Distance</th>
                                    <th class="px-5 py-4 font-semibold">Status</th>
                                    <th class="px-5 py-4 text-right font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-fleet-line-soft">
                                <?php foreach ($tripHistory as $trip): ?>
                                    <tr class="driver-history-row hover:bg-fleet-surface-muted/70">
                                        <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($trip['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-5 py-4 font-semibold text-fleet-ink"><?= htmlspecialchars($trip['vehicle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($trip['route'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-5 py-4 text-fleet-muted"><?= htmlspecialchars($trip['purpose'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($trip['distance'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($trip['status_classes'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= htmlspecialchars($trip['status'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <a href="<?= htmlspecialchars(($basePath ?: '') . '/driver-panel/history.php?trip_id=' . $trip['id'], ENT_QUOTES, 'UTF-8'); ?>" class="text-sm font-semibold text-fleet-sidebar hover:text-fleet-primary">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </article>

            <article class="driver-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-extrabold text-fleet-ink">Trip Detail</h2>
                        <p class="mt-1 text-sm text-fleet-muted">Selected journey detail view</p>
                    </div>
                    <?php if ($tripDetail !== null): ?>
                        <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($tripDetail['status_classes'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($tripDetail['status'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($tripDetail === null): ?>
                    <div class="mt-5 rounded-lg border border-dashed border-fleet-line px-4 py-6 text-center text-sm text-fleet-muted">
                        Select a trip from the history list to view its details.
                    </div>
                <?php else: ?>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-lg bg-fleet-sidebar px-5 py-5 text-white shadow-fleet-card">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-sidebar-muted">Trip Route</p>
                            <p class="mt-2 text-2xl font-extrabold"><?= htmlspecialchars($tripDetail['route'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mt-2 text-sm text-fleet-sidebar-text"><?= htmlspecialchars($tripDetail['vehicle'] . ' • ' . $tripDetail['date'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="driver-subcard rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                                <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Journey Detail</p>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">From</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($tripDetail['from'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">To</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($tripDetail['to'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Distance</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($tripDetail['distance'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Purpose</dt>
                                        <dd class="text-right font-semibold text-fleet-ink"><?= htmlspecialchars($tripDetail['purpose'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="driver-subcard rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                                <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Operational Detail</p>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Odometer Start</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($tripDetail['odometer_start'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Odometer End</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($tripDetail['odometer_end'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Fuel Used</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($tripDetail['fuel_litres'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Fuel Cost</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($tripDetail['fuel_cost'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div class="driver-subcard rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                            <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Remarks</p>
                            <p class="mt-3 text-sm leading-6 text-fleet-muted"><?= htmlspecialchars($tripDetail['remarks'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </article>
        </section>

        <section class="driver-table-card mt-6 rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="flex items-center justify-between gap-4 border-b border-fleet-line-soft px-5 py-5">
                <div>
                    <h2 class="text-lg font-extrabold text-fleet-ink">Reports History</h2>
                    <p class="mt-1 text-sm text-fleet-muted">Pre-inspection, follow-up reports, and maintenance feedback on reported issues</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-fleet-sidebar"><?= count($reportHistory); ?> report(s)</span>
            </div>

            <?php if ($reportHistory === []): ?>
                <div class="px-5 py-8 text-center text-sm text-fleet-muted">No report history has been recorded yet.</div>
            <?php else: ?>
                <div class="grid gap-4 p-5 xl:grid-cols-2">
                    <?php foreach ($reportHistory as $report): ?>
                        <article class="driver-subcard rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-extrabold text-fleet-ink"><?= htmlspecialchars($report['vehicle'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="mt-1 text-xs text-fleet-muted"><?= htmlspecialchars($report['date'], ENT_QUOTES, 'UTF-8'); ?> • <?= htmlspecialchars($report['reference'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($report['type_classes'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars($report['type'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($report['status_classes'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars($report['status'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 space-y-4 text-sm">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Report Summary</p>
                                    <p class="mt-2 leading-6 text-fleet-muted"><?= htmlspecialchars($report['report_summary'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Maintenance Feedback</p>
                                    <p class="mt-2 text-fleet-muted">Status: <span class="font-semibold text-fleet-ink"><?= htmlspecialchars($report['maintenance_status'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                    <p class="mt-2 leading-6 text-fleet-muted"><?= htmlspecialchars($report['maintenance_feedback'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="mt-2 text-fleet-muted">Completed: <span class="font-semibold text-fleet-ink"><?= htmlspecialchars($report['maintenance_completed_date'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
