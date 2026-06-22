<?php
$activePage = 'driver-logbook';
require_once __DIR__ . '/../handlers/driver-panel.php';
extract(driverPanelFetchLogbookPageData());
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/includes/sidebar.php';

$driverLogbookPrintBannerUrl = ($basePath ?: '') . '/assets/images/branding/print_banner.png';
?>
<main class="driver-panel-page min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1536px] px-4 py-5 sm:px-6 lg:px-8">
        <div class="dashboard-shell driver-page-shell">
            <div class="mb-6 flex items-start justify-between gap-4 print:hidden">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Vehicle Log Book</h1>
                    <p class="mt-1 text-sm text-fleet-muted">Print-ready log records for this driver only.</p>
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

            <?php if (!empty($driverLogbookNotification)): ?>
                <section class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-900">
                    <h2 class="text-sm font-extrabold uppercase tracking-[0.18em]"><?= htmlspecialchars($driverLogbookNotification['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="mt-2 text-sm"><?= htmlspecialchars($driverLogbookNotification['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                </section>
            <?php endif; ?>

            <section class="mb-6 rounded-2xl border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card print:hidden">
                <form action="<?= htmlspecialchars($driverLogbookPageUrl, ENT_QUOTES, 'UTF-8'); ?>" method="get" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle</span>
                        <select name="vehicle_id" class="vehicle-form-control">
                            <option value="">All my vehicles</option>
                            <?php foreach ($driverLogbookVehicleOptions as $vehicleOption): ?>
                                <option value="<?= htmlspecialchars((string) $vehicleOption['id'], ENT_QUOTES, 'UTF-8'); ?>" <?= ($driverLogbookFilters['vehicle_id'] ?? '') === (string) $vehicleOption['id'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($vehicleOption['registration_no'] . ' - ' . ($vehicleOption['make_model'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Period</span>
                        <select name="period" class="vehicle-form-control" data-driver-logbook-period-select>
                            <option value="all" <?= ($driverLogbookFilters['period'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All time</option>
                            <option value="week" <?= ($driverLogbookFilters['period'] ?? '') === 'week' ? 'selected' : ''; ?>>Particular week</option>
                            <option value="month" <?= ($driverLogbookFilters['period'] ?? '') === 'month' ? 'selected' : ''; ?>>Particular month</option>
                            <option value="custom" <?= ($driverLogbookFilters['period'] ?? '') === 'custom' ? 'selected' : ''; ?>>Custom range</option>
                        </select>
                    </label>

                    <div class="flex items-end gap-3">
                        <button type="submit" class="inline-flex h-10 items-center rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Apply Filters</button>
                        <a href="<?= htmlspecialchars($driverLogbookPageUrl, ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex h-10 items-center rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">Reset</a>
                    </div>

                    <label class="block <?= ($driverLogbookFilters['period'] ?? '') === 'week' ? '' : 'hidden'; ?>" data-driver-logbook-week-field>
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Week</span>
                        <input name="week" type="week" class="vehicle-form-control" value="<?= htmlspecialchars($driverLogbookFilters['week'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block <?= ($driverLogbookFilters['period'] ?? '') === 'month' ? '' : 'hidden'; ?>" data-driver-logbook-month-field>
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Month</span>
                        <input name="month" type="month" class="vehicle-form-control" value="<?= htmlspecialchars($driverLogbookFilters['month'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block <?= ($driverLogbookFilters['period'] ?? '') === 'custom' ? '' : 'hidden'; ?>" data-driver-logbook-date-from-field>
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Date From</span>
                        <input name="date_from" type="date" class="vehicle-form-control" value="<?= htmlspecialchars($driverLogbookFilters['date_from'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block <?= ($driverLogbookFilters['period'] ?? '') === 'custom' ? '' : 'hidden'; ?>" data-driver-logbook-date-to-field>
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Date To</span>
                        <input name="date_to" type="date" class="vehicle-form-control" value="<?= htmlspecialchars($driverLogbookFilters['date_to'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </form>
            </section>

            <section data-print-root data-print-root-custom-header class="rounded-2xl border border-fleet-line bg-white p-6 shadow-fleet-card print:shadow-none">
                <div class="vehicle-usage-print-memo">
                    <img src="<?= htmlspecialchars($driverLogbookPrintBannerUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Busitema University print banner" class="vehicle-usage-print-banner">
                    <div class="vehicle-usage-print-brand">Busitema University Estates MIS</div>
                    <div class="vehicle-usage-print-unit">ESTATES UNIT</div>
                    <dl class="vehicle-usage-print-meta">
                        <div class="vehicle-usage-print-meta-row">
                            <dt>To:</dt>
                            <dd><?= htmlspecialchars($driverLogbookMemoTo, ENT_QUOTES, 'UTF-8'); ?></dd>
                        </div>
                        <div class="vehicle-usage-print-meta-row">
                            <dt>Thru:</dt>
                            <dd><?= htmlspecialchars($driverLogbookMemoThruOne, ENT_QUOTES, 'UTF-8'); ?></dd>
                        </div>
                        <div class="vehicle-usage-print-meta-row">
                            <dt>Thru:</dt>
                            <dd><?= htmlspecialchars($driverLogbookMemoThruTwo, ENT_QUOTES, 'UTF-8'); ?></dd>
                        </div>
                        <div class="vehicle-usage-print-meta-row">
                            <dt>From:</dt>
                            <dd><?= htmlspecialchars($driverLogbookMemoFrom, ENT_QUOTES, 'UTF-8'); ?></dd>
                        </div>
                        <div class="vehicle-usage-print-meta-row">
                            <dt>Date:</dt>
                            <dd><?= htmlspecialchars($driverLogbookMemoDate, ENT_QUOTES, 'UTF-8'); ?></dd>
                        </div>
                        <div class="vehicle-usage-print-meta-row">
                            <dt>For:</dt>
                            <dd><?= htmlspecialchars($driverLogbookMemoFor, ENT_QUOTES, 'UTF-8'); ?></dd>
                        </div>
                    </dl>
                    <p class="vehicle-usage-print-subject">SUBJECT: <?= htmlspecialchars($driverLogbookMemoSubject, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>

                <div class="mt-6 overflow-x-auto rounded-2xl border border-fleet-line bg-white">
                    <table class="vehicle-usage-print-table w-full min-w-[720px] border-collapse text-left text-sm">
                        <thead class="bg-fleet-surface-muted text-fleet-muted">
                            <tr>
                                <th class="border border-fleet-line px-4 py-3 font-semibold">Driver</th>
                                <th class="border border-fleet-line px-4 py-3 font-semibold">Trips in View</th>
                                <th class="border border-fleet-line px-4 py-3 font-semibold">Distance Covered</th>
                                <th class="border border-fleet-line px-4 py-3 font-semibold">Fuel Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-fleet-line px-4 py-4 text-xl font-extrabold text-fleet-ink"><?= htmlspecialchars($driverProfile['name'] ?? 'Unavailable', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars((string) $driverLogbookSummary['trip_count'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($driverLogbookSummary['total_distance'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-4 py-4 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($driverLogbookSummary['total_cost'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if (!$driverLogbookHasRows): ?>
                    <div class="mt-6 rounded-2xl border border-dashed border-fleet-line px-5 py-10 text-center text-sm text-fleet-muted">
                        No driver logbook entries matched the current filters.
                    </div>
                <?php else: ?>
                    <section class="vehicle-usage-driver-section mt-6 rounded-2xl border border-fleet-line bg-white p-5 print:break-inside-avoid">
                        <div class="overflow-x-auto rounded-2xl border border-fleet-line bg-white">
                            <table class="vehicle-usage-print-table w-full min-w-[1200px] border-collapse text-left text-sm">
                                <thead class="bg-fleet-surface-muted text-fleet-muted">
                                    <tr>
                                        <th class="border border-fleet-line px-4 py-3 font-semibold">#</th>
                                        <th class="border border-fleet-line px-4 py-3 font-semibold">Date</th>
                                        <th class="border border-fleet-line px-4 py-3 font-semibold">Vehicle</th>
                                        <th class="border border-fleet-line px-4 py-3 font-semibold">From</th>
                                        <th class="border border-fleet-line px-4 py-3 font-semibold">To</th>
                                        <th class="border border-fleet-line px-4 py-3 font-semibold">Purpose</th>
                                        <th class="border border-fleet-line px-4 py-3 font-semibold">Odo. Start</th>
                                        <th class="border border-fleet-line px-4 py-3 font-semibold">Odo. End</th>
                                        <th class="border border-fleet-line px-4 py-3 font-semibold">Distance</th>
                                        <th class="border border-fleet-line px-4 py-3 font-semibold">Fuel</th>
                                        <th class="border border-fleet-line px-4 py-3 font-semibold">Fuel Cost</th>
                                        <th class="border border-fleet-line px-4 py-3 font-semibold">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($driverLogbookRows as $index => $row): ?>
                                        <tr>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-muted"><?= $index + 1; ?></td>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="border border-fleet-line px-4 py-4 font-semibold text-fleet-ink"><?= htmlspecialchars($row['vehicle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($row['from'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($row['to'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-muted"><?= htmlspecialchars($row['purpose'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($row['odometer_start'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($row['odometer_end'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($row['distance'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($row['fuel_litres'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-ink"><?= htmlspecialchars($row['fuel_cost'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="border border-fleet-line px-4 py-4 text-fleet-muted"><?= htmlspecialchars($row['remarks'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-fleet-surface-muted font-extrabold text-fleet-ink">
                                    <tr>
                                        <td class="border border-fleet-line px-4 py-3" colspan="8">TOTALS</td>
                                        <td class="border border-fleet-line px-4 py-3"><?= htmlspecialchars($driverLogbookSummary['total_distance'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="border border-fleet-line px-4 py-3"><?= htmlspecialchars($driverLogbookSummary['total_fuel'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="border border-fleet-line px-4 py-3"><?= htmlspecialchars($driverLogbookSummary['total_cost'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="border border-fleet-line px-4 py-3"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="vehicle-usage-signoff mt-6 gap-4 md:grid-cols-3 print:mt-8 vehicle-usage-signoff--print">
                            <div>
                                <p class="text-sm font-semibold text-fleet-ink">Prepared By</p>
                                <div class="mt-10 border-b border-fleet-line"></div>
                                <p class="mt-2 text-sm text-fleet-muted"><?= htmlspecialchars($driverProfile['name'] ?? 'Driver', ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-fleet-ink">Checked By</p>
                                <div class="mt-10 border-b border-fleet-line"></div>
                                <p class="mt-2 text-sm text-fleet-muted">Transport Officer</p>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-fleet-ink">Approved By</p>
                                <div class="mt-10 border-b border-fleet-line"></div>
                                <p class="mt-2 text-sm text-fleet-muted">Head of Department</p>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodSelect = document.querySelector('[data-driver-logbook-period-select]');
    const weekField = document.querySelector('[data-driver-logbook-week-field]');
    const monthField = document.querySelector('[data-driver-logbook-month-field]');
    const dateFromField = document.querySelector('[data-driver-logbook-date-from-field]');
    const dateToField = document.querySelector('[data-driver-logbook-date-to-field]');

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
<?php include __DIR__ . '/../includes/footer.php'; ?>
