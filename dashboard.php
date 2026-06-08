<?php
// Main dashboard entry point backed by live database queries.
$activePage = 'dashboard';
require_once __DIR__ . '/handlers/dashboard.php';
extract(dashboardFetchPageData());
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1536px] px-4 py-5 sm:px-6 lg:px-8">
        <?php
        $totalVehicles = max((int) ($metrics[0]['value'] ?? 0), 1);
        $activeVehicles = (int) ($metrics[1]['value'] ?? 0);
        $activePercent = (int) round(($activeVehicles / $totalVehicles) * 100);
        $dashboardGreeting = $_SESSION['user_name'] ?? 'Administrator';
        ?>
        <div class="dashboard-shell">
            <div class="dashboard-topbar">
                <div class="flex items-center gap-3">
                    <button id="sidebar-toggle" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                        <span class="text-xl leading-none">&#9776;</span>
                    </button>
                    <div>
                        <p class="text-sm font-semibold text-fleet-primary">BUESMIS Dashboard</p>
                        <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Good day, <?= htmlspecialchars($dashboardGreeting, ENT_QUOTES, 'UTF-8'); ?></h1>
                        <p class="mt-1 text-sm text-fleet-muted">Busitema University Estates Management Information System</p>
                    </div>
                </div>
                <div class="dashboard-toolbar">
                    <label class="dashboard-search" aria-label="Dashboard search">
                        <span class="dashboard-search-icon">Q</span>
                        <input type="search" placeholder="Search dashboard sections" aria-label="Search dashboard sections">
                    </label>
                    <div class="dashboard-avatar"><?= htmlspecialchars(strtoupper(substr($dashboardGreeting, 0, 1)), ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>

            <section class="dashboard-metrics-grid">
                <?php foreach ($metrics as $index => $metric): ?>
                    <?php
                    $metricTone = match ($index) {
                        0 => 'primary',
                        1 => 'info',
                        2 => 'success',
                        default => 'warning',
                    };
                    ?>
                    <article class="dashboard-kpi-card dashboard-kpi-card-<?= $metricTone; ?>">
                        <div class="dashboard-kpi-copy">
                            <p class="dashboard-kpi-label"><?= htmlspecialchars($metric['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="dashboard-kpi-value"><?= htmlspecialchars($metric['value'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <span class="dashboard-kpi-icon"><?= htmlspecialchars($metric['icon'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(320px,0.95fr)]">
                <div class="space-y-6">
                    <section class="dashboard-notice-row">
                        <?php foreach ($noticeCards as $notice): ?>
                            <article class="dashboard-notice-card <?= $notice['tone'] === 'primary' ? 'dashboard-notice-card-primary' : 'dashboard-notice-card-info'; ?>">
                                <span class="dashboard-notice-icon"><?= htmlspecialchars($notice['icon'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <div>
                                    <p class="text-sm font-extrabold"><?= htmlspecialchars($notice['count'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="text-xs"><?= htmlspecialchars($notice['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </section>

                    <section class="grid gap-6 lg:grid-cols-[minmax(0,1.25fr)_minmax(280px,0.9fr)]">
                        <article class="dashboard-panel dashboard-panel-chart">
                            <div class="dashboard-panel-head">
                                <div>
                                    <p class="dashboard-eyebrow">Fleet Overview</p>
                                    <h2 class="text-xl font-extrabold text-fleet-ink">Vehicle Availability by Department</h2>
                                </div>
                                <span class="dashboard-chip">Live status</span>
                            </div>
                            <div class="relative flex h-56 items-end gap-4 rounded-[1.5rem] border border-fleet-line-soft bg-white/70 px-4 pb-5 pt-8">
                                <?php foreach ($departments as $department): ?>
                                    <div class="chart-bar-group group flex flex-1 items-end gap-1" tabindex="0" aria-label="<?= htmlspecialchars($department['name'] . ': ' . $department['active'] . ' active, ' . $department['maintenance'] . ' maintenance, ' . $department['grounded'] . ' grounded', ENT_QUOTES, 'UTF-8'); ?>">
                                        <div class="chart-tooltip">
                                            <p class="mb-2 font-bold text-fleet-ink"><?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="text-fleet-success">Active : <?= (int) $department['active']; ?></p>
                                            <p class="text-fleet-warning">Maintenance : <?= (int) $department['maintenance']; ?></p>
                                            <p class="text-fleet-danger">Grounded : <?= (int) $department['grounded']; ?></p>
                                        </div>
                                        <?php if ($department['active'] > 0): ?><span class="chart-bar block h-16 flex-1 rounded-t-[1rem] bg-fleet-success"></span><?php endif; ?>
                                        <?php if ($department['maintenance'] > 0): ?><span class="chart-bar block h-16 flex-1 rounded-t-[1rem] bg-fleet-warning"></span><?php endif; ?>
                                        <?php if ($department['grounded'] > 0): ?><span class="chart-bar block h-16 flex-1 rounded-t-[1rem] bg-fleet-danger"></span><?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-5 text-xs text-fleet-muted">
                                <span class="flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-fleet-success"></i> Active</span>
                                <span class="flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-fleet-warning"></i> Maintenance</span>
                                <span class="flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-fleet-danger"></i> Grounded</span>
                            </div>
                        </article>

                        <div class="space-y-6">
                            <article class="dashboard-feature-card dashboard-feature-card-success">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="dashboard-eyebrow text-white/80">Available Fleet</p>
                                        <p class="mt-2 text-4xl font-extrabold text-white"><?= $activeVehicles; ?></p>
                                        <p class="mt-2 text-sm text-white/85">Active &amp; available vehicles right now</p>
                                    </div>
                                    <span class="dashboard-feature-icon">V</span>
                                </div>
                                <div class="mt-6">
                                    <div class="mb-2 flex items-center justify-between text-sm text-white/90">
                                        <span>Fleet readiness</span>
                                        <span><?= $activePercent; ?>%</span>
                                    </div>
                                    <div class="h-3 overflow-hidden rounded-full bg-white/25">
                                        <div class="h-full rounded-full bg-white" style="width: <?= $activePercent; ?>%"></div>
                                    </div>
                                    <p class="mt-3 text-xs text-white/75"><?= $activePercent; ?>% of total fleet (<?= $totalVehicles; ?> vehicles)</p>
                                </div>
                            </article>

                            <article class="dashboard-feature-card dashboard-feature-card-danger">
                                <div class="flex items-start gap-3">
                                    <span class="dashboard-feature-icon">!</span>
                                    <div class="flex-1">
                                        <p class="dashboard-eyebrow text-white/80">Maintenance Focus</p>
                                        <h2 class="mt-2 text-xl font-extrabold text-white">Immediate Maintenance Required</h2>
                                        <?php if (!empty($needsRepairVehicle)): ?>
                                            <p class="mt-3 text-sm text-white/85">Latest unresolved pre-inspection issue</p>
                                            <div class="mt-5 rounded-[1.25rem] bg-white/14 p-4">
                                                <div class="flex items-center justify-between gap-4">
                                                    <div>
                                                        <p class="font-extrabold text-white"><?= htmlspecialchars($needsRepairVehicle['registration_no'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                        <p class="text-xs text-white/80"><?= htmlspecialchars($needsRepairVehicle['vehicle_model'] . ' - ' . $needsRepairVehicle['department_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                    </div>
                                                    <span class="rounded-full bg-white/18 px-3 py-1 text-xs font-bold text-white"><?= htmlspecialchars(ucwords(str_replace('_', ' ', (string) $needsRepairVehicle['overall_status'])), ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                            </div>
                                            <a href="/fleet-system/modules/inspections/index.php" class="mt-5 inline-block text-sm font-semibold text-white underline">View pre-inspection reports</a>
                                        <?php else: ?>
                                            <p class="mt-4 text-sm text-white/85">No unresolved pre-inspection repair issues right now.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </section>

                    <section class="dashboard-panel overflow-hidden">
                        <div class="dashboard-panel-head">
                            <h2 class="text-base font-extrabold text-fleet-ink">Recent Vehicle Logs</h2>
                            <a href="/fleet-system/modules/logbook/index.php" class="text-sm font-semibold text-fleet-sidebar hover:text-fleet-primary">View all &rarr;</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[620px] text-left text-sm">
                                <thead class="bg-fleet-primary-soft text-fleet-muted">
                                    <tr>
                                        <th class="px-5 py-3 font-bold">Date</th>
                                        <th class="px-5 py-3 font-bold">Vehicle</th>
                                        <th class="px-5 py-3 font-bold">Driver</th>
                                        <th class="px-5 py-3 font-bold">Destination</th>
                                        <th class="px-5 py-3 font-bold">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-fleet-line-soft">
                                    <?php if ($vehicleLogs === []): ?>
                                        <tr><td colspan="5" class="px-5 py-4 text-fleet-muted">No recent vehicle logs.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($vehicleLogs as $log): ?>
                                            <tr class="bg-white/70">
                                                <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($log['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="px-5 py-4 font-bold text-fleet-ink"><?= htmlspecialchars($log['vehicle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="px-5 py-4 text-fleet-ink"><?= htmlspecialchars($log['driver'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="px-5 py-4 text-fleet-muted"><?= htmlspecialchars($log['destination'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="px-5 py-4"><span class="rounded-md border border-blue-200 bg-fleet-primary-soft px-3 py-1 text-xs font-semibold text-fleet-primary"><?= htmlspecialchars($log['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <div class="space-y-6">
                    <section class="dashboard-panel dashboard-panel-alerts">
                        <div class="dashboard-panel-head">
                            <h2 class="text-base font-extrabold text-fleet-ink">Service Due Alerts</h2>
                            <span class="rounded-full bg-fleet-badge-red px-3 py-1 text-xs font-bold text-fleet-danger"><?= count($serviceDueAlerts); ?> Alert(s)</span>
                        </div>
                        <div class="dashboard-scroll max-h-[420px] space-y-3 overflow-y-auto">
                            <?php if ($serviceDueAlerts === []): ?>
                                <div class="rounded-[1.25rem] bg-fleet-surface px-5 py-6 text-sm text-fleet-muted">No current service alerts.</div>
                            <?php else: ?>
                                <?php foreach ($serviceDueAlerts as $alert): ?>
                                    <div class="rounded-[1.25rem] border border-red-200 bg-white px-4 py-4 shadow-sm">
                                        <p class="font-extrabold text-fleet-ink">
                                            <?= htmlspecialchars($alert['vehicle'], ENT_QUOTES, 'UTF-8'); ?>
                                            <span class="ml-2 rounded px-2 py-1 text-xs font-bold <?= $alert['typeTone'] === 'purple' ? 'bg-purple-100 text-purple-700' : 'bg-fleet-badge-blue text-fleet-primary'; ?>">
                                                <?= htmlspecialchars($alert['type'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </p>
                                        <p class="mt-2 text-sm text-fleet-muted"><?= htmlspecialchars($alert['model'], ENT_QUOTES, 'UTF-8'); ?> - <?= htmlspecialchars($alert['department'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p class="mt-1 text-xs text-fleet-muted"><?= htmlspecialchars($alert['detail'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="dashboard-panel">
                        <div class="dashboard-panel-head">
                            <h2 class="text-base font-extrabold text-fleet-ink">Active Maintenance</h2>
                            <a href="/fleet-system/modules/maintenance/index.php" class="text-sm font-semibold text-fleet-sidebar hover:text-fleet-primary">View all &rarr;</a>
                        </div>
                        <div>
                            <?php if ($activeMaintenance === []): ?>
                                <div class="flex min-h-28 items-center justify-center text-sm text-fleet-muted">No active maintenance</div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($activeMaintenance as $record): ?>
                                        <div class="rounded-[1.25rem] border border-orange-200 bg-orange-50 px-4 py-4">
                                            <p class="font-extrabold text-fleet-ink"><?= htmlspecialchars($record['vehicle'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="mt-1 text-sm text-fleet-muted"><?= htmlspecialchars($record['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="mt-2 text-xs font-semibold text-fleet-warning-strong"><?= htmlspecialchars($record['status'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="dashboard-panel">
                        <div class="dashboard-panel-head">
                            <h2 class="text-base font-extrabold text-fleet-ink">Active Users</h2>
                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-fleet-success"><?= $onlineCount; ?> active</span>
                        </div>
                        <div>
                            <?php if ($activeUsers === []): ?>
                                <p class="text-sm text-fleet-muted">No active users found.</p>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($activeUsers as $user): ?>
                                        <div class="flex items-center justify-between gap-4 rounded-[1.25rem] border border-fleet-line-soft bg-fleet-surface-muted px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <span class="h-2.5 w-2.5 rounded-full bg-green-300"></span>
                                                <div>
                                                    <p class="text-sm font-semibold text-fleet-ink"><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                    <p class="text-xs text-fleet-muted"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-fleet-sidebar shadow-sm"><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                <p class="mt-2 text-xs text-fleet-muted"><?= htmlspecialchars($user['last_seen'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
