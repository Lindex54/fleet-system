<?php
$activePage = 'driver-pre-trip';
require_once __DIR__ . '/../handlers/driver-panel.php';
extract(driverPanelFetchPreTripPageData());
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Pre-Trip Inspection</h1>
                <p class="mt-1 text-sm text-fleet-muted">Pre-journey checks and defect reporting for drivers</p>
            </div>
            <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                <span class="text-xl leading-none">&#9776;</span>
            </button>
        </div>

        <?php if (!empty($preTripNotification)): ?>
            <?php $isSuccessNotice = ($preTripNotification['type'] ?? '') === 'success'; ?>
            <section
                data-flash-notice
                data-flash-type="<?= $isSuccessNotice ? 'success' : 'error'; ?>"
                class="pointer-events-none fixed left-1/2 top-8 z-[70] hidden w-[min(92vw,34rem)] -translate-x-1/2 overflow-hidden rounded-2xl border bg-white shadow-2xl transition duration-500 <?= $isSuccessNotice ? 'border-green-200 text-green-900' : 'border-red-200 text-red-900'; ?>"
            >
                <div class="absolute inset-x-0 top-0 h-1.5 <?= $isSuccessNotice ? 'bg-green-500' : 'bg-red-500'; ?>"></div>
                <div class="flex items-center gap-4 px-5 py-4 sm:px-6">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-sm font-extrabold shadow-lg <?= $isSuccessNotice ? 'bg-green-600 text-white shadow-green-200' : 'bg-red-600 text-white shadow-red-200'; ?>">
                        <?= $isSuccessNotice ? 'OK' : '!'; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-sm font-extrabold uppercase tracking-[0.18em]">
                                    <?= htmlspecialchars($preTripNotification['title'] ?? 'Pre-trip update', ENT_QUOTES, 'UTF-8'); ?>
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-fleet-ink">
                                    <?= htmlspecialchars($preTripNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                            <button type="button" data-dismiss-flash class="pointer-events-auto inline-flex h-9 w-9 items-center justify-center rounded-full border text-base font-bold transition <?= $isSuccessNotice ? 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' : 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'; ?>" aria-label="Dismiss notification">x</button>
                        </div>
                        <div class="mt-3 h-1.5 overflow-hidden rounded-full <?= $isSuccessNotice ? 'bg-green-100' : 'bg-red-100'; ?>">
                            <div data-flash-progress class="h-full w-full origin-left rounded-full <?= $isSuccessNotice ? 'bg-green-600' : 'bg-red-600'; ?>"></div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                <p class="text-sm font-medium text-fleet-muted">Assigned Vehicle</p>
                <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['registration_no'] ?? 'Not assigned', ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
            <article class="rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                <p class="text-sm font-medium text-fleet-muted">Driver</p>
                <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($driverProfile['name'] ?? 'Unavailable', ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
            <article class="rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                <p class="text-sm font-medium text-fleet-muted">Current Mileage</p>
                <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($assignedVehicle['current_mileage'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
            <article class="rounded-lg border border-fleet-line bg-fleet-surface p-5 shadow-fleet-card">
                <p class="text-sm font-medium text-fleet-muted">Submission Status</p>
                <p class="mt-2 text-2xl font-extrabold text-fleet-ink"><?= htmlspecialchars($preTripLatestStatus['status'] ?? 'No submission yet', ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-extrabold text-fleet-ink">Start Inspection Action</h2>
                        <p class="mt-1 text-sm text-fleet-muted">Record checklist findings, defects, and vehicle readiness before travel</p>
                    </div>
                    <a href="#pre-trip-form" class="inline-flex h-10 items-center rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Begin</a>
                </div>

                <?php if ($assignedVehicle === null): ?>
                    <div class="rounded-lg border border-dashed border-fleet-line px-5 py-8 text-center text-sm text-fleet-muted">
                        A vehicle must be assigned before a pre-trip inspection can be submitted.
                    </div>
                <?php else: ?>
                    <form id="pre-trip-form" action="<?= htmlspecialchars($preTripFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="space-y-6">
                        <input type="hidden" name="driver_panel_action" value="submit_pre_trip">

                        <div class="grid gap-4 md:grid-cols-3">
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-fleet-ink">Inspection Date *</span>
                                <input name="inspection_date" type="date" class="vehicle-form-control" value="<?= htmlspecialchars($preTripFormData['inspection_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" required>
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle Mileage *</span>
                                <input name="mileage" type="number" min="0" class="vehicle-form-control" value="<?= htmlspecialchars((string) ($preTripFormData['mileage'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-fleet-ink">Overall Status *</span>
                                <select name="overall_status" class="vehicle-form-control">
                                    <option value="good" <?= (($preTripFormData['overall_status'] ?? 'good') === 'good') ? 'selected' : ''; ?>>Good</option>
                                    <option value="fair" <?= (($preTripFormData['overall_status'] ?? '') === 'fair') ? 'selected' : ''; ?>>Fair</option>
                                    <option value="faulty" <?= (($preTripFormData['overall_status'] ?? '') === 'faulty') ? 'selected' : ''; ?>>Faulty</option>
                                    <option value="needs_repair" <?= (($preTripFormData['overall_status'] ?? '') === 'needs_repair') ? 'selected' : ''; ?>>Needs Repair</option>
                                </select>
                            </label>
                        </div>

                        <div>
                            <h3 class="text-sm font-extrabold uppercase tracking-wide text-fleet-sidebar">Inspection Checklist</h3>
                            <div class="mt-4 space-y-4">
                                <?php foreach ($preTripChecklistRows as $index => $row): ?>
                                    <div class="rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                                        <input type="hidden" name="inspection_point[]" value="<?= htmlspecialchars($row['inspection_point'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <div class="grid gap-4 md:grid-cols-[1.2fr_0.8fr_1fr_1fr]">
                                            <div>
                                                <p class="text-sm font-semibold text-fleet-ink"><?= htmlspecialchars($row['inspection_point'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                            <label class="block">
                                                <span class="mb-2 block text-xs font-semibold uppercase tracking-wide text-fleet-muted">Status</span>
                                                <select name="item_status[]" class="vehicle-form-control">
                                                    <option value="good" <?= ($row['item_status'] === 'good') ? 'selected' : ''; ?>>Good</option>
                                                    <option value="fair" <?= ($row['item_status'] === 'fair') ? 'selected' : ''; ?>>Fair</option>
                                                    <option value="faulty" <?= ($row['item_status'] === 'faulty') ? 'selected' : ''; ?>>Faulty</option>
                                                    <option value="needs_repair" <?= ($row['item_status'] === 'needs_repair') ? 'selected' : ''; ?>>Needs Repair</option>
                                                </select>
                                            </label>
                                            <label class="block">
                                                <span class="mb-2 block text-xs font-semibold uppercase tracking-wide text-fleet-muted">Remarks</span>
                                                <input name="item_remarks[]" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($row['item_remarks'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="What did you find?">
                                            </label>
                                            <label class="block">
                                                <span class="mb-2 block text-xs font-semibold uppercase tracking-wide text-fleet-muted">Action</span>
                                                <input name="item_action[]" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($row['item_action'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Recommended action">
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-fleet-ink">Defect Reporting / Summary</span>
                            <textarea name="defects" class="vehicle-form-control min-h-24 resize-y py-3" placeholder="Summarize any issues, faults, or observations"><?= htmlspecialchars($preTripFormData['defects'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </label>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex h-10 items-center rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">
                                Submit Inspection
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </article>

            <article class="space-y-6">
                <section class="rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-fleet-ink">Submission Status</h2>
                            <p class="mt-1 text-sm text-fleet-muted">Most recent inspection outcome for this driver and vehicle</p>
                        </div>
                        <?php if ($preTripLatestStatus !== null): ?>
                            <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($preTripLatestStatus['status_classes'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?= htmlspecialchars($preTripLatestStatus['status'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($preTripLatestStatus === null): ?>
                        <div class="mt-5 rounded-lg border border-dashed border-fleet-line px-4 py-6 text-center text-sm text-fleet-muted">
                            No inspection submission has been recorded yet.
                        </div>
                    <?php else: ?>
                        <div class="mt-5 rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                            <p class="text-sm text-fleet-muted">Date: <span class="font-semibold text-fleet-ink"><?= htmlspecialchars($preTripLatestStatus['date'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                            <p class="mt-2 text-sm text-fleet-muted">Mileage: <span class="font-semibold text-fleet-ink"><?= htmlspecialchars($preTripLatestStatus['mileage'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                            <p class="mt-3 text-sm leading-6 text-fleet-muted"><?= htmlspecialchars($preTripLatestStatus['defects'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-fleet-ink">Recent Inspection Submissions</h2>
                            <p class="mt-1 text-sm text-fleet-muted">Latest pre-trip inspections recorded by this driver</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-fleet-sidebar"><?= count($preTripRecentReports); ?> record(s)</span>
                    </div>

                    <div class="mt-5 space-y-4">
                        <?php if ($preTripRecentReports === []): ?>
                            <div class="rounded-lg border border-dashed border-fleet-line px-4 py-6 text-center text-sm text-fleet-muted">
                                No recent inspection records found.
                            </div>
                        <?php else: ?>
                            <?php foreach ($preTripRecentReports as $report): ?>
                                <div class="rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-extrabold text-fleet-ink"><?= htmlspecialchars($report['invoice'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="mt-1 text-xs text-fleet-muted"><?= htmlspecialchars($report['date'], ENT_QUOTES, 'UTF-8'); ?> • <?= htmlspecialchars($report['mileage'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                        <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($report['status_classes'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($report['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <p class="mt-3 text-sm leading-6 text-fleet-muted"><?= htmlspecialchars($report['defects'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </article>
        </section>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
