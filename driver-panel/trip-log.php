<?php
$activePage = 'driver-trip-log';
require_once __DIR__ . '/../handlers/driver-panel.php';
extract(driverPanelFetchTripLogPageData());
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>
<main class="driver-panel-page min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1536px] px-4 py-5 sm:px-6 lg:px-8">
        <div class="dashboard-shell driver-page-shell">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Trip Log</h1>
                <p class="mt-1 text-sm text-fleet-muted">Start, manage, and complete journey logs</p>
            </div>
            <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                <span class="text-xl leading-none">&#9776;</span>
            </button>
        </div>

        <?php if (!empty($tripLogNotification)): ?>
            <?php $isSuccessNotice = ($tripLogNotification['type'] ?? '') === 'success'; ?>
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
                                    <?= htmlspecialchars($tripLogNotification['title'] ?? 'Trip update', ENT_QUOTES, 'UTF-8'); ?>
                                </h2>
                                <p class="mt-1 text-sm leading-6 text-fleet-ink">
                                    <?= htmlspecialchars($tripLogNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
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

        <section class="driver-stat-grid grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="driver-stat-card rounded-lg border border-fleet-line bg-white p-5 shadow-fleet-card">
                <p class="summary-card-label text-slate-800">Assigned Vehicle</p>
                <p class="summary-card-value summary-card-value-text mt-2 text-slate-900"><?= htmlspecialchars($assignedVehicle['registration_no'] ?? 'Not assigned', ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
            <article class="driver-stat-card rounded-lg border border-blue-200 bg-blue-50 p-5 shadow-fleet-card">
                <p class="summary-card-label text-blue-900">Other Vehicles</p>
                <p class="summary-card-value mt-2 text-slate-900"><?= count($otherVehicles); ?></p>
            </article>
            <article class="driver-stat-card rounded-lg border border-amber-200 bg-amber-50 p-5 shadow-fleet-card">
                <p class="summary-card-label text-amber-900">Driver</p>
                <p class="summary-card-value summary-card-value-text mt-2 text-slate-900"><?= htmlspecialchars($driverProfile['name'] ?? 'Unavailable', ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
            <article class="driver-stat-card rounded-lg border border-green-200 bg-green-50 p-5 shadow-fleet-card">
                <p class="summary-card-label text-green-900">Current Trip</p>
                <p class="summary-card-value summary-card-value-text mt-2 text-slate-900"><?= htmlspecialchars($activeTrip !== null ? 'In Progress' : 'No Active Trip', ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
            <article class="driver-stat-card rounded-lg border border-blue-200 bg-blue-50 p-5 shadow-fleet-card">
                <p class="summary-card-label text-blue-900">Recent Trips</p>
                <p class="summary-card-value mt-2 text-slate-900"><?= count($recentTrips); ?></p>
            </article>
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
            <article class="space-y-6">
                <section class="driver-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-extrabold text-fleet-ink">Journey Management</h2>
                            <p class="mt-1 text-sm text-fleet-muted">Start a trip, capture journey details, then end it with mileage and fuel</p>
                        </div>
                        <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($tripStatus['classes'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars($tripStatus['label'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </div>

                    <?php if ($tripVehicleOptions === []): ?>
                        <div class="rounded-lg border border-dashed border-fleet-line px-5 py-8 text-center text-sm text-fleet-muted">
                            No trip vehicle has been enabled for this driver yet.
                        </div>
                    <?php elseif ($activeTrip === null): ?>
                        <!-- jQuery adds inline validation and safe AJAX submission to trip start. -->
                        <form action="<?= htmlspecialchars($tripFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="space-y-5" data-fleet-ajax="true">
                            <input type="hidden" name="driver_panel_action" value="start_trip">
                            <div data-fleet-feedback-host></div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Trip Vehicle *</span>
                                    <select name="vehicle_id" class="vehicle-form-control" required>
                                        <?php foreach ($tripVehicleOptions as $vehicleOption): ?>
                                            <option
                                                value="<?= htmlspecialchars((string) $vehicleOption['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-current-mileage="<?= htmlspecialchars((string) $vehicleOption['current_mileage_raw'], ENT_QUOTES, 'UTF-8'); ?>"
                                                <?= ((string) ($tripStartFormData['vehicle_id'] ?? '') === (string) $vehicleOption['id']) ? 'selected' : ''; ?>
                                            >
                                                <?= htmlspecialchars($vehicleOption['registration_no'] . ' - ' . $vehicleOption['option_label'] . ' - ' . $vehicleOption['current_mileage'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="block">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Trip Date *</span>
                                    <input name="trip_date" type="date" class="vehicle-form-control" value="<?= htmlspecialchars($tripStartFormData['trip_date'] ?? date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>" required>
                                </label>
                                <label class="block">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Odometer Start (km) *</span>
                                    <input name="odometer_start" type="number" min="0" class="vehicle-form-control bg-slate-50 text-fleet-muted" value="<?= htmlspecialchars((string) ($tripStartFormData['odometer_start'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" readonly required aria-describedby="odometer-start-help">
                                    <span id="odometer-start-help" class="mt-2 block text-xs text-fleet-muted">This mileage is filled from the selected vehicle and cannot be edited by the driver.</span>
                                </label>
                                <label class="block">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Departure Location *</span>
                                    <input name="departure_location" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($tripStartFormData['departure_location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Where are you starting from?" required>
                                </label>
                                <label class="block">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Destination *</span>
                                    <input name="destination" type="text" class="vehicle-form-control" value="<?= htmlspecialchars($tripStartFormData['destination'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Where are you going?" required>
                                </label>
                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Purpose of Journey *</span>
                                    <textarea name="purpose" class="vehicle-form-control min-h-24 resize-y py-3" placeholder="Describe the purpose of this trip" required><?= htmlspecialchars($tripStartFormData['purpose'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </label>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex h-10 items-center rounded-lg bg-fleet-sidebar px-5 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active" data-loading-text="Starting Trip...">
                                    Start Trip
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="rounded-lg border border-blue-200 bg-fleet-primary-soft p-4 text-fleet-primary">
                            <p class="text-sm font-extrabold">Trip in progress</p>
                            <p class="mt-2 text-sm leading-6"><?= htmlspecialchars($activeTrip['vehicle'] . ' - ' . $activeTrip['from'] . ' to ' . $activeTrip['to'] . ' on ' . $activeTrip['date'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mt-1 text-sm">Purpose: <span class="font-semibold"><?= htmlspecialchars($activeTrip['purpose'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                            <p class="mt-1 text-sm">Odometer start: <span class="font-semibold"><?= htmlspecialchars($activeTrip['odometer_start_label'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                        </div>

                        <!-- jQuery adds inline validation and safe AJAX submission to trip completion. -->
                        <form action="<?= htmlspecialchars($tripFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post" class="mt-5 space-y-5" data-fleet-ajax="true">
                            <input type="hidden" name="driver_panel_action" value="end_trip">
                            <input type="hidden" name="trip_id" value="<?= htmlspecialchars((string) ($tripEndFormData['trip_id'] ?? $activeTrip['id']), ENT_QUOTES, 'UTF-8'); ?>">
                            <div data-fleet-feedback-host></div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="block">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Last Odometer Stop (km)</span>
                                    <input
                                        type="text"
                                        class="vehicle-form-control bg-slate-50 text-fleet-muted"
                                        value="<?= htmlspecialchars($activeTrip['odometer_start_label'], ENT_QUOTES, 'UTF-8'); ?>"
                                        readonly
                                        aria-describedby="odometer-last-stop-help"
                                    >
                                    <span id="odometer-last-stop-help" class="mt-2 block text-xs text-fleet-muted">This shows where the current trip started from and it cannot be edited.</span>
                                </label>
                                <label class="block">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Odometer End (km) *</span>
                                    <input
                                        name="odometer_end"
                                        type="number"
                                        min="<?= htmlspecialchars((string) (($activeTrip['odometer_start'] ?? 0) + 1), ENT_QUOTES, 'UTF-8'); ?>"
                                        class="vehicle-form-control"
                                        value="<?= htmlspecialchars((string) ($tripEndFormData['odometer_end'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                        required
                                        aria-describedby="odometer-end-help"
                                    >
                                    <span id="odometer-end-help" class="mt-2 block text-xs text-fleet-muted">Enter a value greater than <?= htmlspecialchars($activeTrip['odometer_start_label'], ENT_QUOTES, 'UTF-8'); ?> so the odometer never moves backward.</span>
                                </label>
                                <label class="block">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Fuel Used (L)</span>
                                    <input name="fuel_litres" type="number" min="0" step="0.01" class="vehicle-form-control" value="<?= htmlspecialchars((string) ($tripEndFormData['fuel_litres'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                </label>
                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Fuel Cost</span>
                                    <input name="fuel_cost" type="number" min="0" step="0.01" class="vehicle-form-control" value="<?= htmlspecialchars((string) ($tripEndFormData['fuel_cost'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Enter fuel cost if applicable">
                                </label>
                                <label class="block md:col-span-2">
                                    <span class="mb-2 block text-sm font-semibold text-fleet-ink">Trip Remarks</span>
                                    <textarea name="remarks" class="vehicle-form-control min-h-24 resize-y py-3" placeholder="Add any trip notes or observations"><?= htmlspecialchars($tripEndFormData['remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </label>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex h-10 items-center rounded-lg bg-fleet-success px-5 text-sm font-semibold text-white shadow-sm hover:bg-green-700" data-loading-text="Ending Trip...">
                                    End Trip
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </section>
            </article>

            <article class="space-y-6">
                <section class="driver-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                    <h2 class="text-lg font-extrabold text-fleet-ink">Current Trip Status</h2>
                    <p class="mt-1 text-sm text-fleet-muted">Quick summary of your active or latest journey state</p>

                    <?php if ($activeTrip === null): ?>
                        <div class="mt-5 rounded-lg border border-dashed border-fleet-line px-4 py-6 text-center text-sm text-fleet-muted">
                            No active trip right now. Start one when you are ready to travel.
                        </div>
                    <?php else: ?>
                        <div class="driver-subcard mt-5 rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                            <p class="text-sm font-extrabold text-fleet-ink"><?= htmlspecialchars($activeTrip['vehicle'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mt-2 text-sm text-fleet-muted"><?= htmlspecialchars($activeTrip['date'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mt-2 text-sm leading-6 text-fleet-muted"><?= htmlspecialchars($activeTrip['from'] . ' to ' . $activeTrip['to'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mt-2 text-sm leading-6 text-fleet-muted"><?= htmlspecialchars($activeTrip['purpose'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="driver-card rounded-lg border border-fleet-line bg-fleet-surface p-6 shadow-fleet-card">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-fleet-ink">Recent Trips</h2>
                            <p class="mt-1 text-sm text-fleet-muted">Latest journey records for this driver</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-fleet-sidebar"><?= count($recentTrips); ?> trip(s)</span>
                    </div>

                    <div class="mt-5 space-y-4">
                        <?php if ($recentTrips === []): ?>
                            <div class="rounded-lg border border-dashed border-fleet-line px-4 py-6 text-center text-sm text-fleet-muted">
                                No trip records available yet.
                            </div>
                        <?php else: ?>
                            <?php foreach ($recentTrips as $trip): ?>
                                <div class="driver-subcard rounded-lg border border-fleet-line-soft bg-fleet-surface-muted p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-extrabold text-fleet-ink"><?= htmlspecialchars($trip['vehicle'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="mt-1 text-xs text-fleet-muted"><?= htmlspecialchars($trip['date'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                        <span class="inline-flex rounded-lg border px-3 py-1 text-xs font-semibold <?= htmlspecialchars($trip['status_classes'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($trip['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-fleet-ink"><?= htmlspecialchars($trip['route'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="mt-2 text-sm text-fleet-muted"><?= htmlspecialchars($trip['purpose'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <div class="mt-3 flex flex-wrap gap-4 text-xs text-fleet-muted">
                                        <span>Distance: <?= htmlspecialchars($trip['distance'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span>Fuel: <?= htmlspecialchars($trip['fuel'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <p class="mt-2 text-sm text-fleet-muted"><?= htmlspecialchars($trip['remarks'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </article>
        </section>
        </div>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tripVehicleSelect = document.querySelector('select[name="vehicle_id"]');
    const odometerStartField = document.querySelector('input[name="odometer_start"]');

    if (!tripVehicleSelect || !odometerStartField) {
        return;
    }

    let lastSuggestedMileage = odometerStartField.value;

    const syncSuggestedMileage = function () {
        const selectedOption = tripVehicleSelect.options[tripVehicleSelect.selectedIndex];
        const suggestedMileage = selectedOption ? (selectedOption.dataset.currentMileage || '') : '';

        if (odometerStartField.value === '' || odometerStartField.value === lastSuggestedMileage) {
            odometerStartField.value = suggestedMileage;
        }

        lastSuggestedMileage = suggestedMileage;
    };

    tripVehicleSelect.addEventListener('change', syncSuggestedMileage);
    syncSuggestedMileage();
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
