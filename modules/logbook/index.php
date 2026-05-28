<?php
// Official vehicle movement log page backed by the logbook handler and database.
$activePage = 'logbook';
require_once __DIR__ . '/../../handlers/logbook.php';
// Load live log records, totals, select options, and any flash UI state from the handler.
extract(logbookFetchPageData());
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Vehicle Log Book</h1>
                <p class="mt-2 text-sm text-fleet-muted">Daily motor vehicle movement records - Official University Log</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" data-print-page class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-fleet-card hover:bg-fleet-surface-muted">
                    <span class="text-base">P</span>
                    <span>Print</span>
                </button>
                <button type="button" data-open-logbook-modal class="inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>New Log Entry</span>
                </button>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>
        </div>

        <section class="mb-6 rounded-lg border border-fleet-line bg-slate-100 px-5 py-4">
            <h2 class="text-sm font-extrabold uppercase tracking-wide text-fleet-sidebar">Busitema University - Official Motor Vehicle Log Book</h2>
            <p class="mt-1 text-xs text-fleet-muted">All vehicle movements must be recorded. Authorized by the University Transport Officer.</p>
        </section>

        <?php if (!empty($logbookNotification)): ?>
            <?php $isSuccessNotice = ($logbookNotification['type'] ?? '') === 'success'; ?>
            <!-- Clear feedback for logbook saves, styled to stand out and then auto-dismiss in app.js. -->
            <section
                data-flash-notice
                class="mb-6 overflow-hidden rounded-2xl border shadow-lg transition duration-500 <?= $isSuccessNotice ? 'border-emerald-200 bg-gradient-to-r from-emerald-50 via-white to-lime-50 text-emerald-950 shadow-emerald-100/80' : 'border-rose-200 bg-gradient-to-r from-rose-50 via-white to-amber-50 text-rose-950 shadow-rose-100/80'; ?>"
            >
                <div class="flex items-start gap-4 px-5 py-4 sm:px-6">
                    <div class="mt-0.5 flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-sm font-extrabold shadow-sm <?= $isSuccessNotice ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-rose-600 text-white ring-4 ring-rose-100'; ?>">
                        <?= $isSuccessNotice ? 'OK' : '!'; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="text-sm font-extrabold uppercase tracking-[0.18em]">
                                    <?= htmlspecialchars($logbookNotification['title'] ?? 'Logbook update', ENT_QUOTES, 'UTF-8'); ?>
                                </h2>
                                <p class="mt-1 text-sm leading-6 sm:text-[15px]">
                                    <?= htmlspecialchars($logbookNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </p>
                            </div>
                            <button
                                type="button"
                                data-dismiss-flash
                                class="inline-flex h-9 w-9 items-center justify-center self-start rounded-full border text-lg font-bold transition hover:scale-105 <?= $isSuccessNotice ? 'border-emerald-200 bg-white/80 text-emerald-700 hover:bg-emerald-100' : 'border-rose-200 bg-white/80 text-rose-700 hover:bg-rose-100'; ?>"
                                aria-label="Dismiss notification"
                            >
                                x
                            </button>
                        </div>
                        <div class="mt-4 h-1.5 overflow-hidden rounded-full <?= $isSuccessNotice ? 'bg-emerald-100' : 'bg-rose-100'; ?>">
                            <div
                                data-flash-progress
                                class="h-full w-full origin-left rounded-full <?= $isSuccessNotice ? 'bg-emerald-500' : 'bg-rose-500'; ?>"
                            ></div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <div class="mb-6 max-w-md">
            <label class="relative block">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-fleet-muted">Q</span>
                <input id="logbook-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-11 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search logs...">
            </label>
        </div>

        <section class="<?= $hasLogs ? 'hidden' : 'flex'; ?> min-h-[420px] items-center justify-center">
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-200 text-fleet-muted">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"></path>
                    </svg>
                </div>
                <h2 class="mt-5 text-lg font-extrabold text-fleet-ink">No log entries found</h2>
                <p class="mt-2 text-sm text-fleet-muted">Create log entries to record vehicle movement.</p>
                <button type="button" data-open-logbook-modal class="mt-6 inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>New Log Entry</span>
                </button>
            </div>
        </section>

        <section class="<?= $hasLogs ? 'block' : 'hidden'; ?> overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1280px] border-collapse text-left text-sm" data-logbook-table>
                    <thead class="bg-fleet-surface-muted text-fleet-muted">
                        <tr>
                            <th class="border border-fleet-line px-3 py-4 font-semibold">#</th>
                            <th class="border border-fleet-line px-3 py-4 font-semibold">Date</th>
                            <th class="border border-fleet-line px-3 py-4 font-semibold">Vehicle Reg.</th>
                            <th class="border border-fleet-line px-3 py-4 font-semibold">Driver</th>
                            <th class="border border-fleet-line px-3 py-4 font-semibold">From</th>
                            <th class="border border-fleet-line px-3 py-4 font-semibold">To</th>
                            <th class="border border-fleet-line px-3 py-4 font-semibold">Purpose</th>
                            <th class="border border-fleet-line px-3 py-4 font-semibold">Odo. Start</th>
                            <th class="border border-fleet-line px-3 py-4 font-semibold">Odo. End</th>
                            <th class="border border-fleet-line px-3 py-4 font-semibold">Km</th>
                            <th class="border border-fleet-line px-3 py-4 font-semibold">Fuel (L)</th>
                            <th class="border border-fleet-line px-3 py-4 font-semibold">Fuel Cost</th>
                            <th class="border border-fleet-line px-3 py-4 font-semibold">Remarks</th>
                            <th class="border border-fleet-line px-3 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $index => $log): ?>
                            <tr class="logbook-row hover:bg-fleet-surface-muted/70" data-search="<?= htmlspecialchars(strtolower(implode(' ', $log)), ENT_QUOTES, 'UTF-8'); ?>">
                                <td class="border border-fleet-line px-3 py-4 text-fleet-muted"><?= $index + 1; ?></td>
                                <td class="border border-fleet-line px-3 py-4 text-fleet-ink" contenteditable="true"><?= htmlspecialchars($log['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-3 py-4 font-extrabold text-fleet-ink" contenteditable="true"><?= htmlspecialchars($log['vehicle'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-3 py-4 text-fleet-ink" contenteditable="true"><?= htmlspecialchars($log['driver'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-3 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($log['from'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-3 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($log['to'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-3 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($log['purpose'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-3 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($log['odo_start'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-3 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($log['odo_end'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-3 py-4 text-fleet-ink" contenteditable="true"><?= htmlspecialchars($log['km'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-3 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($log['fuel'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-3 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($log['cost'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-3 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($log['remarks'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="border border-fleet-line px-3 py-4">
                                    <div class="flex justify-end gap-3">
                                        <button type="button" class="text-fleet-sidebar hover:text-fleet-primary" aria-label="Edit log <?= $index + 1; ?>">Edit</button>
                                        <button type="button" class="text-fleet-danger hover:text-fleet-danger-strong" aria-label="Delete log <?= $index + 1; ?>">Del</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-fleet-surface-muted font-extrabold text-fleet-ink">
                        <tr>
                            <td class="border border-fleet-line px-3 py-3" colspan="9">TOTALS</td>
                            <td class="border border-fleet-line px-3 py-3"><?= $totalKm; ?> km</td>
                            <td class="border border-fleet-line px-3 py-3"><?= $totalFuel; ?> L</td>
                            <td class="border border-fleet-line px-3 py-3"><?= htmlspecialchars($totalCost, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="border border-fleet-line px-3 py-3" colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>

    <div
        id="logbook-modal"
        class="fixed inset-0 z-50 <?= $shouldOpenLogbookModal ? 'flex' : 'hidden'; ?> items-start justify-center overflow-y-auto bg-black/75 px-4 py-5 sm:items-center"
        aria-hidden="<?= $shouldOpenLogbookModal ? 'false' : 'true'; ?>"
        data-open-on-load="<?= $shouldOpenLogbookModal ? 'true' : 'false'; ?>"
    >
        <div class="dashboard-scroll max-h-[calc(100vh-2.5rem)] w-full max-w-2xl overflow-y-auto rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" style="max-width: 680px;" role="dialog" aria-modal="true" aria-labelledby="logbook-modal-title">
            <!-- Failed submissions reopen this modal and keep previously entered values in place. -->
            <form class="p-5" action="<?= htmlspecialchars($logbookFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <h2 id="logbook-modal-title" class="text-lg font-extrabold text-fleet-ink">New Vehicle Log Entry</h2>
                        <p class="mt-1 text-xs text-fleet-muted">Busitema University - Official Motor Vehicle Log</p>
                    </div>
                    <button type="button" data-close-logbook-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close log entry form">&times;</button>
                </div>

                <h3 class="mb-3 text-sm font-extrabold text-fleet-sidebar">Trip Information</h3>
                <div class="grid gap-3 md:grid-cols-3">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Date *</span>
                        <input name="date" type="date" required class="vehicle-form-control" value="<?= htmlspecialchars($logbookFormData['date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" autofocus>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle *</span>
                        <select name="vehicle" required class="vehicle-form-control">
                            <option value="">Select vehicle</option>
                            <?php foreach ($logbookVehicleOptions as $vehicleOption): ?>
                                <option value="<?= htmlspecialchars((string) $vehicleOption['id'], ENT_QUOTES, 'UTF-8'); ?>" <?= (($logbookFormData['vehicle'] ?? '') === (string) $vehicleOption['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($vehicleOption['registration_no'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Driver</span>
                        <select name="driver" class="vehicle-form-control">
                            <option value="unassigned" <?= (($logbookFormData['driver'] ?? 'unassigned') === 'unassigned') ? 'selected' : ''; ?>>Unassigned</option>
                            <?php foreach ($logbookDriverOptions as $driverOption): ?>
                                <option value="<?= htmlspecialchars((string) $driverOption['id'], ENT_QUOTES, 'UTF-8'); ?>" <?= (($logbookFormData['driver'] ?? '') === (string) $driverOption['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($driverOption['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <h3 class="mb-3 mt-5 text-sm font-extrabold text-fleet-sidebar">Journey Details</h3>
                <div class="grid gap-3 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Departure Location *</span>
                        <input name="departure_location" type="text" required class="vehicle-form-control" placeholder="e.g. Main Campus, Tororo" value="<?= htmlspecialchars($logbookFormData['departure_location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Destination *</span>
                        <input name="destination" type="text" required class="vehicle-form-control" placeholder="e.g. Kampala" value="<?= htmlspecialchars($logbookFormData['destination'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block md:col-span-2">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Purpose of Journey *</span>
                        <textarea name="purpose" required class="vehicle-form-control min-h-14 resize-y py-3" placeholder="Describe the purpose of this trip"><?= htmlspecialchars($logbookFormData['purpose'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </label>
                </div>

                <h3 class="mb-3 mt-5 text-sm font-extrabold text-fleet-sidebar">Odometer &amp; Fuel</h3>
                <div class="grid gap-3 md:grid-cols-4">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Odometer Start (km)</span>
                        <input name="odometer_start" type="number" min="0" class="vehicle-form-control" value="<?= htmlspecialchars($logbookFormData['odometer_start'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Odometer End (km)</span>
                        <input name="odometer_end" type="number" min="0" class="vehicle-form-control" value="<?= htmlspecialchars($logbookFormData['odometer_end'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Fuel (litres)</span>
                        <input name="fuel_litres" type="number" min="0" step="0.01" class="vehicle-form-control" value="<?= htmlspecialchars($logbookFormData['fuel_litres'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Fuel Cost (UGX)</span>
                        <input name="fuel_cost" type="number" min="0" class="vehicle-form-control" value="<?= htmlspecialchars($logbookFormData['fuel_cost'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>
                </div>

                <label class="mt-4 block">
                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Remarks</span>
                    <input name="remarks" type="text" class="vehicle-form-control" placeholder="Any notes" value="<?= htmlspecialchars($logbookFormData['remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>

                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" data-close-logbook-modal class="h-10 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">Cancel</button>
                    <button type="submit" class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Create Log Entry</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
