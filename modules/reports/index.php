<?php
// Live fleet analytics page backed by aggregated database queries.
$activePage = 'reports';
require_once __DIR__ . '/../../handlers/reports.php';
extract(reportsFetchPageData());
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Fleet Reports</h1>
                <p class="mt-2 text-sm text-fleet-muted">Live cost analysis and fleet utilization overview from the database.</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.16em] text-fleet-primary">Scope: <?= htmlspecialchars($reportPeriodLabel, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <form action="<?= htmlspecialchars($reportsPageUrl, ENT_QUOTES, 'UTF-8'); ?>" method="get">
                <select name="period" class="vehicle-form-control h-10 w-40" onchange="this.form.submit()">
                    <option value="all" <?= ($reportFilters['period'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Time</option>
                    <option value="month" <?= ($reportFilters['period'] ?? '') === 'month' ? 'selected' : ''; ?>>This Month</option>
                    <option value="quarter" <?= ($reportFilters['period'] ?? '') === 'quarter' ? 'selected' : ''; ?>>This Quarter</option>
                    <option value="year" <?= ($reportFilters['period'] ?? '') === 'year' ? 'selected' : ''; ?>>This Year</option>
                </select>
            </form>
        </div>

        <?php if (!empty($reportNotification)): ?>
            <section class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-900">
                <h2 class="text-sm font-extrabold uppercase tracking-[0.18em]"><?= htmlspecialchars($reportNotification['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="mt-2 text-sm"><?= htmlspecialchars($reportNotification['message'], ENT_QUOTES, 'UTF-8'); ?></p>
            </section>
        <?php endif; ?>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($summaryCards as $card): ?>
                <?php
                $toneClasses = match ($card['tone']) {
                    'amber' => 'bg-fleet-warning-soft text-fleet-warning-strong',
                    'green' => 'bg-fleet-success-soft text-fleet-success',
                    default => 'bg-fleet-primary-soft text-fleet-primary',
                };
                ?>
                <article class="interactive-card flex min-h-20 items-center gap-4 rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-extrabold <?= $toneClasses; ?>"><?= htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <div>
                        <p class="text-xs text-fleet-muted"><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="mt-1 text-xl font-extrabold text-fleet-ink"><?= htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="mt-6 grid gap-4 md:grid-cols-2">
            <?php foreach ($reportHighlights as $highlight): ?>
                <article class="rounded-2xl border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted"><?= htmlspecialchars($highlight['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mt-3 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($highlight['value'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mt-2 text-sm text-fleet-muted"><?= htmlspecialchars($highlight['detail'], ENT_QUOTES, 'UTF-8'); ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="mt-6">
            <article class="interactive-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-extrabold text-fleet-ink">Maintenance Cost by Vehicle</h2>
                        <p class="mt-1 text-sm text-fleet-muted">Highest maintenance spend for the selected period.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="/fleet-system/modules/maintenance/" class="text-sm font-semibold text-fleet-sidebar hover:text-fleet-primary">View all &rarr;</a>
                        <span class="rounded-full bg-fleet-primary-soft px-3 py-1 text-xs font-semibold text-fleet-primary"><?= count($maintenanceByVehicle); ?> vehicle(s)</span>
                    </div>
                </div>

                <?php if ($maintenanceByVehicle === []): ?>
                    <div class="mt-8 rounded-2xl border border-dashed border-fleet-line px-5 py-10 text-center text-sm text-fleet-muted">
                        No maintenance cost records found for this period.
                    </div>
                <?php else: ?>
                    <div class="mt-8 space-y-5">
                        <?php foreach ($maintenanceByVehicle as $index => $row): ?>
                            <?php
                            $vehicleBarClass = match ($index % 3) {
                                1 => 'bg-fleet-warning',
                                2 => 'bg-fleet-success',
                                default => 'bg-fleet-primary',
                            };
                            ?>
                            <div class="grid gap-3 md:grid-cols-[150px_minmax(0,1fr)_120px] md:items-center">
                                <div>
                                    <p class="text-sm font-extrabold text-fleet-ink"><?= htmlspecialchars($row['vehicle'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="text-xs text-fleet-muted"><?= htmlspecialchars($row['make_model'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <div class="relative h-4 overflow-hidden rounded-full bg-fleet-primary-soft">
                                    <div class="absolute inset-y-0 left-0 rounded-full <?= $vehicleBarClass; ?>" style="width: <?= (int) $row['bar_width']; ?>%"></div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-extrabold text-fleet-ink"><?= htmlspecialchars($row['formatted_cost'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="text-xs text-fleet-muted"><?= (int) $row['record_count']; ?> record(s)</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        </section>

        <section class="interactive-card mt-6 rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-extrabold text-fleet-ink">Trips by Vehicle</h2>
                    <p class="mt-1 text-sm text-fleet-muted">Vehicle activity ranking based on recorded trips.</p>
                </div>
                <span class="rounded-full bg-fleet-success-soft px-3 py-1 text-xs font-semibold text-fleet-success"><?= count($tripsByVehicle); ?> vehicle(s)</span>
            </div>

            <?php if ($tripsByVehicle === []): ?>
                <div class="mt-8 rounded-2xl border border-dashed border-fleet-line px-5 py-10 text-center text-sm text-fleet-muted">
                    No trip history found for this period.
                </div>
            <?php else: ?>
                <div class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
                    <div class="relative h-72 rounded-2xl border border-fleet-line-soft bg-fleet-surface-muted px-5 pb-8 pt-6">
                        <div class="pointer-events-none absolute inset-x-5 top-6 bottom-8 grid grid-rows-4">
                            <span class="border-t border-dashed border-fleet-line"></span>
                            <span class="border-t border-dashed border-fleet-line"></span>
                            <span class="border-t border-dashed border-fleet-line"></span>
                            <span class="border-t border-dashed border-fleet-line"></span>
                        </div>
                        <div class="relative flex h-full items-end gap-4">
                            <?php foreach ($tripsByVehicle as $row): ?>
                                <div class="report-chart-group group flex flex-1 flex-col items-center justify-end gap-3">
                                    <div class="report-tooltip left-1/2 top-0 -translate-x-1/2 text-center">
                                        <p class="font-semibold text-fleet-ink"><?= htmlspecialchars($row['vehicle'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class="mt-2 text-fleet-sidebar"><?= (int) $row['trip_count']; ?> trip(s)</p>
                                        <p class="mt-1 text-fleet-muted"><?= htmlspecialchars($row['formatted_distance'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                    <div class="w-full rounded-t-2xl bg-fleet-warning transition-transform duration-150 group-hover:-translate-y-1" style="height: <?= (int) $row['bar_height']; ?>%"></div>
                                    <div class="w-full text-center">
                                        <p class="truncate text-xs font-extrabold text-fleet-ink"><?= htmlspecialchars($row['vehicle'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class="truncate text-[11px] text-fleet-muted"><?= htmlspecialchars($row['make_model'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <?php foreach ($tripsByVehicle as $row): ?>
                            <article class="rounded-2xl border border-fleet-line bg-fleet-surface-muted p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-extrabold text-fleet-ink"><?= htmlspecialchars($row['vehicle'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class="mt-1 text-xs text-fleet-muted"><?= htmlspecialchars($row['make_model'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-fleet-sidebar shadow-sm"><?= (int) $row['trip_count']; ?> trip(s)</span>
                                </div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-xl bg-white px-3 py-3">
                                        <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-fleet-muted">Distance</p>
                                        <p class="mt-1 text-sm font-extrabold text-fleet-ink"><?= htmlspecialchars($row['formatted_distance'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                    <div class="rounded-xl bg-white px-3 py-3">
                                        <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-fleet-muted">Fuel Cost</p>
                                        <p class="mt-1 text-sm font-extrabold text-fleet-ink"><?= htmlspecialchars($row['formatted_fuel_cost'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
