<?php
$activePage = 'driver-vehicle';
require_once __DIR__ . '/../handlers/driver-panel.php';
extract(driverPanelFetchVehiclePageData());
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="driver-panel-page min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1536px] px-4 py-5 sm:px-6 lg:px-8">
        <div class="dashboard-shell driver-page-shell">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">My Vehicle</h1>
                <p class="mt-1 text-sm text-fleet-muted">Assigned vehicle details for the logged-in driver</p>
            </div>
            <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                <span class="text-xl leading-none">&#9776;</span>
            </button>
        </div>

        <section class="driver-stat-grid grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($vehicleHighlights as $highlight): ?>
                <article class="driver-stat-card rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                    <p class="text-sm font-medium text-fleet-muted"><?= htmlspecialchars($highlight['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($highlight['value'], ENT_QUOTES, 'UTF-8'); ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
            <article class="driver-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-extrabold text-fleet-ink">Vehicle Overview</h2>
                        <p class="mt-1 text-sm text-fleet-muted">Assigned vehicle details relevant to the driver</p>
                    </div>
                    <?php if ($assignedVehicle !== null): ?>
                        <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($assignedVehicle['status_classes'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($assignedVehicle['status_label'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($assignedVehicle === null): ?>
                    <div class="mt-6 rounded-lg border border-dashed border-fleet-line px-5 py-8 text-center text-sm text-fleet-muted">
                        There is no active vehicle assignment for this driver profile.
                    </div>
                <?php else: ?>
                    <div class="mt-6 space-y-5">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="driver-subcard rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                                <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Core Details</p>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Make / Model</dt>
                                        <dd class="text-right font-semibold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['make_model'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Year</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars((string) $assignedVehicle['manufacture_year'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Vehicle Type</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['vehicle_type'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Fuel Type</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['fuel_type'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                </dl>
                            </div>

                            <div class="driver-subcard rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                                <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Operational State</p>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Current Mileage</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['current_mileage'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Department</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['department_name'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Assigned Since</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['assigned_at'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="text-fleet-muted">Insurance Expiry</dt>
                                        <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['insurance_expiry_label'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div class="driver-subcard rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                            <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Driver-Relevant Notes</p>
                            <p class="mt-3 text-sm leading-6 text-fleet-muted"><?= htmlspecialchars($assignedVehicle['notes'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </article>

            <article class="space-y-6">
                <section class="driver-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                    <h2 class="text-lg font-extrabold text-fleet-ink">Quick Access</h2>
                    <p class="mt-1 text-sm text-fleet-muted">Shortcuts tied to this vehicle and its daily operations</p>
                    <div class="mt-5 space-y-3">
                        <a href="<?= htmlspecialchars(($basePath ?: '') . '/driver-panel/pre-trip-inspection', ENT_QUOTES, 'UTF-8'); ?>" class="driver-action-link flex items-center justify-between rounded-lg border border-fleet-line-soft bg-fleet-surface-muted px-4 py-4 text-sm font-semibold text-fleet-ink transition hover:border-fleet-primary hover:bg-blue-50/50">
                            <span>Go to Pre-Trip Inspection</span>
                            <span class="text-fleet-primary">&rarr;</span>
                        </a>
                        <a href="<?= htmlspecialchars(($basePath ?: '') . '/driver-panel/trip-log', ENT_QUOTES, 'UTF-8'); ?>" class="driver-action-link flex items-center justify-between rounded-lg border border-fleet-line-soft bg-fleet-surface-muted px-4 py-4 text-sm font-semibold text-fleet-ink transition hover:border-fleet-primary hover:bg-blue-50/50">
                            <span>Open Trip Log</span>
                            <span class="text-fleet-primary">&rarr;</span>
                        </a>
                        <a href="<?= htmlspecialchars(($basePath ?: '') . '/driver-panel/history', ENT_QUOTES, 'UTF-8'); ?>" class="driver-action-link flex items-center justify-between rounded-lg border border-fleet-line-soft bg-fleet-surface-muted px-4 py-4 text-sm font-semibold text-fleet-ink transition hover:border-fleet-primary hover:bg-blue-50/50">
                            <span>View Driver History</span>
                            <span class="text-fleet-primary">&rarr;</span>
                        </a>
                    </div>
                </section>

                <section class="driver-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-fleet-ink">Other Vehicles</h2>
                            <p class="mt-1 text-sm text-fleet-muted">Additional vehicles this driver can use</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-fleet-sidebar"><?= count($otherVehicles); ?> vehicle(s)</span>
                    </div>

                    <?php if ($otherVehicles === []): ?>
                        <div class="mt-5 rounded-lg border border-dashed border-fleet-line px-4 py-6 text-center text-sm text-fleet-muted">
                            No other vehicles have been linked to this driver.
                        </div>
                    <?php else: ?>
                        <div class="mt-5 space-y-4">
                            <?php foreach ($otherVehicles as $vehicle): ?>
                                <div class="driver-subcard rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-extrabold text-fleet-ink"><?= htmlspecialchars($vehicle['registration_no'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="mt-1 text-sm text-fleet-muted"><?= htmlspecialchars($vehicle['make_model'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                        <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($vehicle['status_classes'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($vehicle['status_label'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <p class="mt-3 text-sm text-fleet-muted">Mileage: <span class="font-semibold text-fleet-ink"><?= htmlspecialchars($vehicle['current_mileage'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="driver-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-fleet-ink">Current Trip State</h2>
                            <p class="mt-1 text-sm text-fleet-muted">Operational status linked to this vehicle</p>
                        </div>
                        <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($tripStatus['classes'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($tripStatus['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-fleet-muted"><?= htmlspecialchars($tripStatus['detail'], ENT_QUOTES, 'UTF-8'); ?></p>

                    <?php if ($latestPreInspection !== null): ?>
                        <div class="driver-subcard mt-5 rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                            <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Latest Pre-Trip Inspection</p>
                            <p class="mt-3 text-sm text-fleet-muted">Date: <span class="font-semibold text-fleet-ink"><?= htmlspecialchars($latestPreInspection['date'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                            <p class="mt-2 text-sm text-fleet-muted">Status: <span class="font-semibold text-fleet-ink"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $latestPreInspection['overall_status'])), ENT_QUOTES, 'UTF-8'); ?></span></p>
                            <p class="mt-2 text-sm leading-6 text-fleet-muted"><?= htmlspecialchars($latestPreInspection['defects'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endif; ?>
                </section>
            </article>
        </section>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
