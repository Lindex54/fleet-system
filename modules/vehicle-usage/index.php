<?php
$activePage = 'vehicle-usage';
require_once __DIR__ . '/../../handlers/vehicle-usage.php';
extract(vehicleUsageFetchPageData());
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';

$vehicleUsagePrintBannerUrl = ($basePath ?: '') . '/assets/images/branding/print_banner.png';

$driverBreakdownShowAll = (($_GET['driver_breakdown'] ?? '') === 'all');
$driverBreakdownTotalEntries = count($vehicleUsageDriverBreakdown);
$driverBreakdownPreviewRows = array_slice($vehicleUsageDriverBreakdown, 0, 5);
$driverBreakdownRowsToRender = $driverBreakdownShowAll ? $vehicleUsageDriverBreakdown : $driverBreakdownPreviewRows;
$driverBreakdownHasMore = $driverBreakdownTotalEntries > 5;

$driverBreakdownShowAllQuery = $_GET;
$driverBreakdownShowAllQuery['driver_breakdown'] = 'all';
$driverBreakdownShowLessQuery = $_GET;
unset($driverBreakdownShowLessQuery['driver_breakdown']);

$vehicleUsageHasMultipleDriverSections = count($vehicleUsageDriverSections) > 1;

$driverBreakdownShowAllUrl = $vehicleUsagePageUrl . '?' . http_build_query($driverBreakdownShowAllQuery) . '#driver-breakdown';
$driverBreakdownShowLessUrl = $vehicleUsagePageUrl . (count($driverBreakdownShowLessQuery) > 0 ? '?' . http_build_query($driverBreakdownShowLessQuery) : '') . '#driver-breakdown';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1536px] px-4 py-8 sm:px-6 lg:px-8 2xl:max-w-[1800px]">
        <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between print:hidden">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Vehicle Usage Register</h1>
                <p class="mt-2 text-sm text-fleet-muted">Filter one vehicle or the whole fleet and print a full usage history by driver, week, or month.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" data-print-page class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-fleet-card hover:bg-fleet-surface-muted">
                    <span class="text-base">P</span>
                    <span>Print Current View</span>
                </button>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>
        </div>

        <?php if (!empty($vehicleUsageNotification)): ?>
            <section class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-900">
                <h2 class="text-sm font-extrabold uppercase tracking-[0.18em]"><?= htmlspecialchars($vehicleUsageNotification['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="mt-2 text-sm"><?= htmlspecialchars($vehicleUsageNotification['message'], ENT_QUOTES, 'UTF-8'); ?></p>
            </section>
        <?php endif; ?>

        <section class="mb-6 rounded-2xl border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card print:hidden">
            <form action="<?= htmlspecialchars($vehicleUsagePageUrl, ENT_QUOTES, 'UTF-8'); ?>" method="get" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" data-vehicle-usage-filter-form>
                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle</span>
                    <select name="vehicle_id" class="vehicle-form-control">
                        <option value="">All vehicles</option>
                        <?php foreach ($vehicleUsageVehicleOptions as $vehicleOption): ?>
                            <option value="<?= htmlspecialchars((string) $vehicleOption['id'], ENT_QUOTES, 'UTF-8'); ?>" <?= ($vehicleUsageFilters['vehicle_id'] ?? '') === (string) $vehicleOption['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($vehicleOption['registration_no'] . ' - ' . $vehicleOption['make_model'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Driver</span>
                    <select name="driver_id" class="vehicle-form-control">
                        <option value="">All drivers</option>
                        <?php foreach ($vehicleUsageDriverOptions as $driverOption): ?>
                            <option value="<?= htmlspecialchars((string) $driverOption['id'], ENT_QUOTES, 'UTF-8'); ?>" <?= ($vehicleUsageFilters['driver_id'] ?? '') === (string) $driverOption['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($driverOption['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Period</span>
                    <select name="period" class="vehicle-form-control" data-vehicle-usage-period-select>
                        <option value="all" <?= ($vehicleUsageFilters['period'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All time</option>
                        <option value="week" <?= ($vehicleUsageFilters['period'] ?? '') === 'week' ? 'selected' : ''; ?>>Particular week</option>
                        <option value="month" <?= ($vehicleUsageFilters['period'] ?? '') === 'month' ? 'selected' : ''; ?>>Particular month</option>
                        <option value="custom" <?= ($vehicleUsageFilters['period'] ?? '') === 'custom' ? 'selected' : ''; ?>>Custom range</option>
                    </select>
                </label>

                <div class="flex items-end gap-3">
                    <button type="submit" class="inline-flex h-10 items-center rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Apply Filters</button>
                    <a href="<?= htmlspecialchars($vehicleUsagePageUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 items-center rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">Reset</a>
                </div>

                <label class="block <?= ($vehicleUsageFilters['period'] ?? '') === 'week' ? '' : 'hidden'; ?>" data-vehicle-usage-week-field>
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Week</span>
                    <input name="week" type="week" class="vehicle-form-control" value="<?= htmlspecialchars($vehicleUsageFilters['week'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>

                <label class="block <?= ($vehicleUsageFilters['period'] ?? '') === 'month' ? '' : 'hidden'; ?>" data-vehicle-usage-month-field>
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Month</span>
                    <input name="month" type="month" class="vehicle-form-control" value="<?= htmlspecialchars($vehicleUsageFilters['month'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>

                <label class="block <?= ($vehicleUsageFilters['period'] ?? '') === 'custom' ? '' : 'hidden'; ?>" data-vehicle-usage-date-from-field>
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Date From</span>
                    <input name="date_from" type="date" class="vehicle-form-control" value="<?= htmlspecialchars($vehicleUsageFilters['date_from'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>

                <label class="block <?= ($vehicleUsageFilters['period'] ?? '') === 'custom' ? '' : 'hidden'; ?>" data-vehicle-usage-date-to-field>
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Date To</span>
                    <input name="date_to" type="date" class="vehicle-form-control" value="<?= htmlspecialchars($vehicleUsageFilters['date_to'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>
            </form>
        </section>

        <section data-print-root data-print-root-custom-header class="rounded-2xl border border-fleet-line bg-white p-6 shadow-fleet-card print:shadow-none">
            <div class="vehicle-usage-print-memo">
                <img src="<?= htmlspecialchars($vehicleUsagePrintBannerUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Busitema University print banner" class="vehicle-usage-print-banner">
                <div class="vehicle-usage-print-unit">ESTATES UNIT</div>
                <dl class="vehicle-usage-print-meta">
                    <div class="vehicle-usage-print-meta-row">
                        <dt>To:</dt>
                        <dd><?= htmlspecialchars($vehicleUsageMemoTo, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div class="vehicle-usage-print-meta-row">
                        <dt>Thru:</dt>
                        <dd><?= htmlspecialchars($vehicleUsageMemoThruOne, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div class="vehicle-usage-print-meta-row">
                        <dt>Thru:</dt>
                        <dd><?= htmlspecialchars($vehicleUsageMemoThruTwo, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div class="vehicle-usage-print-meta-row">
                        <dt>From:</dt>
                        <dd><?= htmlspecialchars($vehicleUsageMemoFrom, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div class="vehicle-usage-print-meta-row">
                        <dt>Date:</dt>
                        <dd><?= htmlspecialchars($vehicleUsageMemoDate, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div class="vehicle-usage-print-meta-row">
                        <dt>For:</dt>
                        <dd><?= htmlspecialchars($vehicleUsageMemoFor, ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                </dl>
                <p class="vehicle-usage-print-subject">SUBJECT: <?= htmlspecialchars($vehicleUsageMemoSubject, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <div class="mt-6 overflow-x-auto rounded-2xl border border-fleet-line bg-white">
                <table class="vehicle-usage-print-table w-full min-w-[720px] border-collapse text-left text-sm">
                    <thead class="bg-fleet-surface-muted text-fleet-muted">
                        <tr>
                            <th class="border border-fleet-line px-4 py-3 font-semibold">Trips in View</th>
                            <th class="border border-fleet-line px-4 py-3 font-semibold">Drivers in View</th>
                            <th class="border border-fleet-line px-4 py-3 font-semibold">Distance Covered</th>
                            <th class="border border-fleet-line px-4 py-3 font-semibold">Fuel Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-fleet-line px-4 py-4 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars((string) $vehicleUsageSummary['trip_count'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="border border-fleet-line px-4 py-4 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars((string) $vehicleUsageSummary['driver_count'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="border border-fleet-line px-4 py-4 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($vehicleUsageSummary['total_distance'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="border border-fleet-line px-4 py-4 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($vehicleUsageSummary['total_cost'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <section class="rounded-2xl border border-fleet-line bg-fleet-surface p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-extrabold text-fleet-ink">Vehicle Detail</h3>
                            <p class="mt-1 text-sm text-fleet-muted">Selected vehicle profile for this usage register.</p>
                        </div>
                        <?php if ($vehicleUsageSelectedVehicle !== null): ?>
                            <span class="inline-flex rounded-lg border border-green-200 bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                                <?= htmlspecialchars($vehicleUsageSelectedVehicle['status'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4 overflow-x-auto rounded-xl border border-fleet-line bg-white">
                        <table class="vehicle-usage-print-table w-full min-w-[560px] border-collapse text-left text-sm">
                            <thead class="bg-fleet-surface-muted text-fleet-muted">
                                <tr>
                                    <th class="border border-fleet-line px-4 py-3 font-semibold">Registration Number</th>
                                    <th class="border border-fleet-line px-4 py-3 font-semibold">Make / Model</th>
                                    <th class="border border-fleet-line px-4 py-3 font-semibold">Vehicle Type</th>
                                    <th class="border border-fleet-line px-4 py-3 font-semibold">Fuel Type</th>
                                    <th class="border border-fleet-line px-4 py-3 font-semibold">Current Mileage</th>
                                    <th class="border border-fleet-line px-4 py-3 font-semibold">Department</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($vehicleUsageSelectedVehicle === null): ?>
                                    <tr>
                                        <td colspan="6" class="border border-fleet-line px-4 py-6 text-center text-sm text-fleet-muted">
                                            No single vehicle is selected. The report currently covers all vehicles that match the chosen filters.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td class="border border-fleet-line px-4 py-4 font-extrabold text-fleet-ink"><?= htmlspecialchars($vehicleUsageSelectedVehicle['registration_no'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($vehicleUsageSelectedVehicle['make_model'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($vehicleUsageSelectedVehicle['vehicle_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($vehicleUsageSelectedVehicle['fuel_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($vehicleUsageSelectedVehicle['current_mileage'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($vehicleUsageSelectedVehicle['department_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section id="driver-breakdown" class="rounded-2xl border border-fleet-line bg-fleet-surface p-5">
                    <h3 class="text-lg font-extrabold text-fleet-ink">Driver Breakdown</h3>
                    <p class="mt-1 text-sm text-fleet-muted">Who used the filtered vehicle scope, how often, and how far.</p>

                    <div class="mt-4 overflow-x-auto rounded-xl border border-fleet-line bg-white">
                        <table class="vehicle-usage-print-table w-full min-w-[520px] border-collapse text-left text-sm">
                            <thead class="bg-fleet-surface-muted text-fleet-muted">
                                <tr>
                                    <th class="border border-fleet-line px-4 py-3 font-semibold">Driver</th>
                                    <th class="border border-fleet-line px-4 py-3 font-semibold">Trips</th>
                                    <th class="border border-fleet-line px-4 py-3 font-semibold">Distance</th>
                                    <th class="border border-fleet-line px-4 py-3 font-semibold">Latest Trip</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($vehicleUsageDriverBreakdown === []): ?>
                                    <tr>
                                        <td colspan="4" class="border border-fleet-line px-4 py-6 text-center text-sm text-fleet-muted">
                                            No driver usage records match the current filters.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($driverBreakdownRowsToRender as $driverRow): ?>
                                        <tr>
                                            <td class="border border-fleet-line px-4 py-4 font-semibold text-fleet-ink"><?= htmlspecialchars($driverRow['driver'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars((string) $driverRow['trips'], ENT_QUOTES, 'UTF-8'); ?> trip(s)</td>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($driverRow['distance'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($driverRow['latest_date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($driverBreakdownHasMore && !$driverBreakdownShowAll): ?>
                        <div class="mt-4 print:hidden">
                            <a href="<?= htmlspecialchars($driverBreakdownShowAllUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 items-center rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">
                                View all <?= htmlspecialchars((string) $driverBreakdownTotalEntries, ENT_QUOTES, 'UTF-8'); ?> entries
                            </a>
                        </div>
                    <?php elseif ($driverBreakdownHasMore && $driverBreakdownShowAll): ?>
                        <div class="mt-4 print:hidden">
                            <a href="<?= htmlspecialchars($driverBreakdownShowLessUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 items-center rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">
                                Show first 5 entries
                            </a>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <section class="mt-6 rounded-2xl border border-fleet-line bg-fleet-surface p-5">
                <div class="flex items-center justify-between gap-4 print:hidden">
                    <div>
                        <h3 class="text-lg font-extrabold text-fleet-ink">Detailed Usage Log</h3>
                        <p class="mt-1 text-sm text-fleet-muted">Full printable history for the current filter selection, grouped by driver for signing.</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-fleet-sidebar"><?= count($vehicleUsageRows); ?> row(s)</span>
                </div>

                <?php if (!$vehicleUsageHasRows): ?>
                    <div class="mt-5 rounded-xl border border-dashed border-fleet-line px-4 py-10 text-center text-sm text-fleet-muted">
                        No vehicle usage records match the current filter selection.
                    </div>
                <?php else: ?>
                    <div class="mt-5 space-y-6">
                        <?php foreach ($vehicleUsageDriverSections as $driverSection): ?>
                            <section class="vehicle-usage-driver-section rounded-2xl border border-fleet-line bg-white p-5 print:break-inside-avoid">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h4 class="text-base font-extrabold text-fleet-ink"><?= htmlspecialchars($driverSection['driver'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                        <p class="mt-1 text-sm text-fleet-muted">Usage log and sign-off section for this driver.</p>
                                    </div>
                                    <div class="text-right text-sm">
                                        <p class="font-semibold text-fleet-ink"><?= htmlspecialchars((string) $driverSection['trip_count'], ENT_QUOTES, 'UTF-8'); ?> trip(s)</p>
                                        <p class="mt-1 text-fleet-muted"><?= htmlspecialchars($driverSection['distance'], ENT_QUOTES, 'UTF-8'); ?> • <?= htmlspecialchars($driverSection['fuel_cost'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    </div>
                                </div>

                                <div class="mt-4 overflow-x-auto">
                                    <table class="w-full min-w-[1200px] border-collapse text-left text-sm" data-vehicle-usage-driver-table>
                                        <thead class="bg-fleet-surface-muted text-fleet-muted">
                                            <tr>
                                                <th class="border border-fleet-line px-3 py-3 font-semibold">Date</th>
                                                <th class="border border-fleet-line px-3 py-3 font-semibold">Vehicle</th>
                                                <th class="border border-fleet-line px-3 py-3 font-semibold">Driver</th>
                                                <th class="border border-fleet-line px-3 py-3 font-semibold">Route</th>
                                                <th class="border border-fleet-line px-3 py-3 font-semibold">Purpose</th>
                                                <th class="border border-fleet-line px-3 py-3 font-semibold">Odo. Start</th>
                                                <th class="border border-fleet-line px-3 py-3 font-semibold">Odo. End</th>
                                                <th class="border border-fleet-line px-3 py-3 font-semibold">Distance</th>
                                                <th class="border border-fleet-line px-3 py-3 font-semibold">Fuel</th>
                                                <th class="border border-fleet-line px-3 py-3 font-semibold">Fuel Cost</th>
                                                <th class="border border-fleet-line px-3 py-3 font-semibold">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($driverSection['rows'] as $row): ?>
                                                <tr class="vehicle-usage-log-row">
                                                    <td class="border border-fleet-line px-3 py-3 text-fleet-ink"><?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="border border-fleet-line px-3 py-3 font-semibold text-fleet-ink"><?= htmlspecialchars($row['vehicle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="border border-fleet-line px-3 py-3 text-fleet-ink"><?= htmlspecialchars($row['driver'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="border border-fleet-line px-3 py-3 text-fleet-muted"><?= htmlspecialchars($row['route'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="border border-fleet-line px-3 py-3 text-fleet-muted"><?= htmlspecialchars($row['purpose'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="border border-fleet-line px-3 py-3 text-fleet-ink"><?= htmlspecialchars($row['odometer_start'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="border border-fleet-line px-3 py-3 text-fleet-ink"><?= htmlspecialchars($row['odometer_end'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="border border-fleet-line px-3 py-3 text-fleet-ink"><?= htmlspecialchars($row['distance'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="border border-fleet-line px-3 py-3 text-fleet-ink"><?= htmlspecialchars($row['fuel_litres'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="border border-fleet-line px-3 py-3 text-fleet-ink"><?= htmlspecialchars($row['fuel_cost'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td class="border border-fleet-line px-3 py-3 text-fleet-muted"><?= htmlspecialchars($row['remarks'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="vehicle-usage-signoff mt-6 gap-4 md:grid-cols-3 print:mt-8 <?= $vehicleUsageHasMultipleDriverSections ? 'vehicle-usage-signoff--print' : ''; ?>">
                                    <div class="rounded-xl border border-dashed border-fleet-line px-4 py-5">
                                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Driver Signature</p>
                                        <div class="mt-10 border-b border-fleet-ink"></div>
                                    </div>
                                    <div class="rounded-xl border border-dashed border-fleet-line px-4 py-5">
                                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Officer Driven</p>
                                        <div class="mt-10 border-b border-fleet-ink"></div>
                                    </div>
                                    <div class="rounded-xl border border-dashed border-fleet-line px-4 py-5">
                                        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Transport Officer</p>
                                        <div class="mt-10 border-b border-fleet-ink"></div>
                                    </div>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    </div>

                    <div class="vehicle-usage-signoff mt-6 gap-4 md:grid-cols-3 print:mt-8 <?= !$vehicleUsageHasMultipleDriverSections ? 'vehicle-usage-signoff--print' : ''; ?>">
                        <div class="rounded-xl border border-dashed border-fleet-line px-4 py-5">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Driver Signature</p>
                            <div class="mt-10 border-b border-fleet-ink"></div>
                        </div>
                        <div class="rounded-xl border border-dashed border-fleet-line px-4 py-5">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Officer Driven</p>
                            <div class="mt-10 border-b border-fleet-ink"></div>
                        </div>
                        <div class="rounded-xl border border-dashed border-fleet-line px-4 py-5">
                            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-fleet-muted">Transport Officer</p>
                            <div class="mt-10 border-b border-fleet-ink"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </section>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodSelect = document.querySelector('[data-vehicle-usage-period-select]');
    const weekField = document.querySelector('[data-vehicle-usage-week-field]');
    const monthField = document.querySelector('[data-vehicle-usage-month-field]');
    const dateFromField = document.querySelector('[data-vehicle-usage-date-from-field]');
    const dateToField = document.querySelector('[data-vehicle-usage-date-to-field]');

    if (!periodSelect) {
        return;
    }

    const syncPeriodFields = function () {
        const period = periodSelect.value;

        weekField?.classList.toggle('hidden', period !== 'week');
        monthField?.classList.toggle('hidden', period !== 'month');
        dateFromField?.classList.toggle('hidden', period !== 'custom');
        dateToField?.classList.toggle('hidden', period !== 'custom');
    };

    periodSelect.addEventListener('change', syncPeriodFields);
    syncPeriodFields();
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
