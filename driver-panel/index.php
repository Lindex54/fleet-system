<?php
$activePage = 'driver-dashboard';
require_once __DIR__ . '/../handlers/driver-panel.php';
extract(driverPanelFetchDashboardData());
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1536px] px-4 py-5 sm:px-6 lg:px-8">
        <?php $driverName = $driverProfile['name'] ?? 'Driver'; ?>
        <div class="dashboard-shell">
            <div class="dashboard-topbar">
                <div class="flex items-center gap-3">
                    <button id="sidebar-toggle" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                        <span class="text-xl leading-none">&#9776;</span>
                    </button>
                    <div>
                        <p class="text-sm font-semibold text-fleet-primary">Driver Workspace</p>
                        <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Good day, <?= htmlspecialchars($driverName, ENT_QUOTES, 'UTF-8'); ?></h1>
                        <p class="mt-1 text-sm text-fleet-muted">Driver workspace for daily fleet activities</p>
                    </div>
                </div>
                <div class="dashboard-toolbar">
                    <label class="dashboard-search" aria-label="Driver dashboard search">
                        <span class="dashboard-search-icon">Q</span>
                        <input type="search" placeholder="Search driver dashboard" aria-label="Search driver dashboard">
                    </label>
                    <div class="dashboard-avatar"><?= htmlspecialchars($driverProfile['initial'] ?? 'D', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>

            <section class="dashboard-metrics-grid">
                <?php foreach ($overviewCards as $index => $card): ?>
                    <?php
                    $cardTone = match ($index) {
                        0 => 'primary',
                        1 => 'info',
                        2 => 'success',
                        default => 'warning',
                    };
                    ?>
                    <article class="dashboard-kpi-card dashboard-kpi-card-<?= $cardTone; ?>">
                        <div class="dashboard-kpi-copy">
                            <p class="dashboard-kpi-label"><?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="dashboard-kpi-value"><?= htmlspecialchars($card['value'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <span class="dashboard-kpi-icon"><?= htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(320px,0.95fr)]">
                <div class="space-y-6">
                    <section>
                        <article class="dashboard-panel dashboard-panel-chart">
                            <div class="dashboard-panel-head">
                                <div>
                                    <p class="dashboard-eyebrow">Driver Profile</p>
                                    <h2 class="text-xl font-extrabold text-fleet-ink"><?= htmlspecialchars($driverProfile['name'] ?? 'Driver profile unavailable', ENT_QUOTES, 'UTF-8'); ?></h2>
                                    <p class="mt-1 text-sm text-fleet-muted"><?= htmlspecialchars($driverProfile['department'] ?? 'No department available', ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars(($tripStatus['classes'] ?? 'border-slate-200 bg-slate-100 text-slate-600'), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars($tripStatus['label'] ?? 'Unavailable', ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>

                            <div class="flex items-start gap-4">
                                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-fleet-primary-soft text-lg font-extrabold text-fleet-primary">
                                    <?= htmlspecialchars($driverProfile['initial'] ?? 'D', ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div class="rounded-[1.25rem] border border-fleet-line-soft bg-white/70 p-4">
                                            <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Profile Summary</p>
                                            <dl class="mt-3 space-y-2 text-sm">
                                                <div class="flex items-center justify-between gap-4">
                                                    <dt class="text-fleet-muted">Employee ID</dt>
                                                    <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($driverProfile['employee_id'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></dd>
                                                </div>
                                                <div class="flex items-center justify-between gap-4">
                                                    <dt class="text-fleet-muted">License No.</dt>
                                                    <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($driverProfile['license_number'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></dd>
                                                </div>
                                                <div class="flex items-center justify-between gap-4">
                                                    <dt class="text-fleet-muted">License Class</dt>
                                                    <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($driverProfile['license_classes'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></dd>
                                                </div>
                                                <div class="flex items-center justify-between gap-4">
                                                    <dt class="text-fleet-muted">License Expiry</dt>
                                                    <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($driverProfile['license_expiry'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></dd>
                                                </div>
                                            </dl>
                                        </div>

                                        <div class="rounded-[1.25rem] border border-fleet-line-soft bg-white/70 p-4">
                                            <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Contact</p>
                                            <dl class="mt-3 space-y-2 text-sm">
                                                <div class="flex items-center justify-between gap-4">
                                                    <dt class="text-fleet-muted">Phone</dt>
                                                    <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($driverProfile['phone'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></dd>
                                                </div>
                                                <div class="flex items-center justify-between gap-4">
                                                    <dt class="text-fleet-muted">Email</dt>
                                                    <dd class="truncate text-right font-semibold text-fleet-ink"><?= htmlspecialchars($driverProfile['email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></dd>
                                                </div>
                                                <div class="flex items-center justify-between gap-4">
                                                    <dt class="text-fleet-muted">Driver Status</dt>
                                                    <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($driverProfile['status'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></dd>
                                                </div>
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </section>

                    <section class="dashboard-panel overflow-hidden">
                        <div class="dashboard-panel-head">
                            <div>
                                <h2 class="text-lg font-extrabold text-fleet-ink">Current Trip Status</h2>
                                <p class="mt-1 text-sm text-fleet-muted">Latest journey activity for this driver</p>
                            </div>
                            <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($tripStatus['classes'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($tripStatus['label'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>

                        <?php if ($latestTrip === null): ?>
                            <div class="rounded-[1.25rem] border border-dashed border-fleet-line px-5 py-8 text-center text-sm text-fleet-muted">
                                No trip has been recorded yet for this driver.
                            </div>
                        <?php else: ?>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-[1.25rem] border border-fleet-line-soft bg-fleet-primary-soft p-4">
                                    <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Latest Trip</p>
                                    <dl class="mt-3 space-y-2 text-sm">
                                        <div class="flex items-center justify-between gap-4">
                                            <dt class="text-fleet-muted">Date</dt>
                                            <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($latestTrip['date'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <dt class="text-fleet-muted">Vehicle</dt>
                                            <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($latestTrip['vehicle'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <dt class="text-fleet-muted">Route</dt>
                                            <dd class="text-right font-semibold text-fleet-ink"><?= htmlspecialchars($latestTrip['from'] . ' - ' . $latestTrip['to'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-4">
                                            <dt class="text-fleet-muted">Distance</dt>
                                            <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($latestTrip['distance'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                        </div>
                                    </dl>
                                </div>

                                <div class="rounded-[1.25rem] border border-fleet-line-soft bg-white/70 p-4">
                                    <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Trip Detail</p>
                                    <p class="mt-3 text-sm font-semibold text-fleet-ink"><?= htmlspecialchars($latestTrip['purpose'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <div class="mt-4 space-y-2 text-sm">
                                        <p class="text-fleet-muted">Odometer start: <span class="font-semibold text-fleet-ink"><?= htmlspecialchars($latestTrip['odometer_start'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                        <p class="text-fleet-muted">Odometer end: <span class="font-semibold text-fleet-ink"><?= htmlspecialchars($latestTrip['odometer_end'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                        <p class="text-fleet-muted">Record status: <span class="font-semibold text-fleet-ink"><?= htmlspecialchars($latestTrip['status_label'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>

                <div class="space-y-6">
                    <section class="dashboard-panel">
                        <div class="dashboard-panel-head">
                            <div>
                                <h2 class="text-xl font-extrabold text-fleet-ink">Assigned Vehicle</h2>
                                <p class="mt-1 text-sm text-fleet-muted">Current driver allocation and travel readiness</p>
                            </div>
                            <?php if ($assignedVehicle !== null): ?>
                                <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($assignedVehicle['status_classes'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars($assignedVehicle['status_label'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($assignedVehicle === null): ?>
                            <div class="rounded-[1.25rem] border border-dashed border-fleet-line px-5 py-8 text-center text-sm text-fleet-muted">
                                No vehicle is currently assigned to this driver profile.
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <div class="rounded-[1.25rem] bg-fleet-sidebar px-5 py-5 text-white shadow-fleet-card">
                                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-sidebar-muted">Vehicle Registration</p>
                                    <p class="mt-2 text-3xl font-extrabold"><?= htmlspecialchars($assignedVehicle['registration_no'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="mt-2 text-sm text-fleet-sidebar-text"><?= htmlspecialchars($assignedVehicle['make_model'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>

                                <div class="grid gap-4">
                                    <div class="rounded-[1.25rem] border border-fleet-line-soft bg-fleet-surface-muted p-4">
                                        <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Vehicle Summary</p>
                                        <dl class="mt-3 space-y-2 text-sm">
                                            <div class="flex items-center justify-between gap-4">
                                                <dt class="text-fleet-muted">Mileage</dt>
                                                <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['current_mileage'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                            <div class="flex items-center justify-between gap-4">
                                                <dt class="text-fleet-muted">Type</dt>
                                                <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['vehicle_type'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                            <div class="flex items-center justify-between gap-4">
                                                <dt class="text-fleet-muted">Fuel</dt>
                                                <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['fuel_type'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                            <div class="flex items-center justify-between gap-4">
                                                <dt class="text-fleet-muted">Assigned Since</dt>
                                                <dd class="font-semibold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['assigned_at'], ENT_QUOTES, 'UTF-8'); ?></dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="rounded-[1.25rem] border border-fleet-line-soft bg-fleet-surface-muted p-4">
                                        <p class="text-xs font-extrabold uppercase tracking-wide text-fleet-sidebar">Trip Status</p>
                                        <p class="mt-3 inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($tripStatus['classes'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($tripStatus['label'], ENT_QUOTES, 'UTF-8'); ?>
                                        </p>
                                        <p class="mt-3 text-sm leading-6 text-fleet-muted"><?= htmlspecialchars($tripStatus['detail'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section class="dashboard-panel dashboard-panel-alerts">
                        <div class="dashboard-panel-head">
                            <div>
                                <h2 class="text-lg font-extrabold text-fleet-ink">Important Alerts &amp; Reminders</h2>
                                <p class="mt-1 text-sm text-fleet-muted">Operational notices relevant to this driver</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-fleet-sidebar"><?= count($alerts); ?> item(s)</span>
                        </div>

                        <div class="space-y-4">
                            <?php foreach ($alerts as $alert): ?>
                                <?php
                                $alertClasses = match ($alert['tone']) {
                                    'success' => 'border-green-200 bg-fleet-success-soft text-fleet-success',
                                    'warning' => 'border-orange-200 bg-fleet-warning-soft text-fleet-warning-strong',
                                    'danger' => 'border-red-200 bg-fleet-danger-soft text-fleet-danger',
                                    default => 'border-blue-200 bg-fleet-primary-soft text-fleet-primary',
                                };
                                ?>
                                <div class="rounded-[1.25rem] border px-4 py-4 <?= $alertClasses; ?>">
                                    <p class="text-sm font-extrabold"><?= htmlspecialchars($alert['title'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="mt-2 text-sm leading-6"><?= htmlspecialchars($alert['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
