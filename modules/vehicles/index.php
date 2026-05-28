<?php
// Vehicle registry page backed by the vehicle handler and database.
$activePage = 'vehicles';
require_once __DIR__ . '/../../handlers/vehicle.php';
// Pull both the current vehicle rows and any flash UI state from the handler.
extract(vehicleFetchPageData());
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<main class="min-h-screen lg:pl-64">
    <div class="mx-auto max-w-[1320px] px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-normal text-fleet-ink sm:text-3xl">Fleet Vehicles</h1>
                <p class="mt-2 text-sm text-fleet-muted">University motor vehicle fleet registry - click any cell to edit</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" data-print-page class="inline-flex h-10 items-center gap-2 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-fleet-card hover:bg-fleet-surface-muted">
                    <span class="text-base">P</span>
                    <span>Print</span>
                </button>
                <button type="button" data-open-vehicle-modal class="inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>Add Vehicle</span>
                </button>
                <button id="sidebar-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-fleet-line bg-fleet-surface text-fleet-ink shadow-fleet-card lg:hidden" type="button" aria-label="Open navigation">
                    <span class="text-xl leading-none">&#9776;</span>
                </button>
            </div>
        </div>

        <?php if (!empty($vehicleNotification)): ?>
            <?php $isSuccessNotice = ($vehicleNotification['type'] ?? '') === 'success'; ?>
            <!-- Clear feedback after add-vehicle attempts, whether success or validation/database failure. -->
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
                                    <?= htmlspecialchars($vehicleNotification['title'] ?? 'Vehicle update', ENT_QUOTES, 'UTF-8'); ?>
                                </h2>
                                <p class="mt-1 text-sm leading-6 sm:text-[15px]">
                                    <?= htmlspecialchars($vehicleNotification['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
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
                <input id="vehicle-search" type="search" class="h-11 w-full rounded-lg border border-fleet-line bg-fleet-surface py-2 pl-11 pr-4 text-sm text-fleet-ink shadow-sm outline-none transition placeholder:text-fleet-muted focus:border-fleet-primary focus:ring-4 focus:ring-blue-100" placeholder="Search vehicles...">
            </label>
        </div>

        <section class="<?= $hasVehicles ? 'hidden' : 'flex'; ?> min-h-[420px] items-center justify-center">
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-200 text-fleet-muted">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18.7 8c-.4-.6-1-.9-1.7-.9H7c-.7 0-1.3.3-1.7.9l-1.8 3.1C2.7 11.3 2 12.1 2 13v3c0 .6.4 1 1 1h2"></path>
                        <circle cx="7" cy="17" r="2"></circle>
                        <circle cx="17" cy="17" r="2"></circle>
                        <path d="M5 11h14"></path>
                    </svg>
                </div>
                <h2 class="mt-5 text-lg font-extrabold text-fleet-ink">No vehicles found</h2>
                <p class="mt-2 text-sm text-fleet-muted">Add vehicles to build the university fleet registry.</p>
                <button type="button" data-open-vehicle-modal class="mt-6 inline-flex h-10 items-center gap-2 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-fleet-card hover:bg-fleet-sidebar-active">
                    <span class="text-lg leading-none">+</span>
                    <span>Add Vehicle</span>
                </button>
            </div>
        </section>

        <section class="<?= $hasVehicles ? 'block' : 'hidden'; ?> overflow-hidden rounded-lg border border-fleet-line bg-fleet-surface shadow-fleet-card">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1180px] text-left text-sm" data-vehicle-table>
                    <thead class="bg-fleet-surface-muted text-fleet-muted">
                        <tr>
                            <th class="px-5 py-4 font-semibold">Reg. No.</th>
                            <th class="px-5 py-4 font-semibold">Make / Model</th>
                            <th class="px-5 py-4 font-semibold">Year</th>
                            <th class="px-5 py-4 font-semibold">Type</th>
                            <th class="px-5 py-4 font-semibold">Department</th>
                            <th class="px-5 py-4 font-semibold">Mileage (km)</th>
                            <th class="px-5 py-4 font-semibold">Insurance Expiry</th>
                            <th class="px-5 py-4 font-semibold">Repairs Done</th>
                            <th class="px-5 py-4 font-semibold">Status</th>
                            <th class="px-5 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-fleet-line-soft">
                        <?php foreach ($vehicles as $vehicle): ?>
                            <tr class="vehicle-row hover:bg-fleet-surface-muted/70" data-search="<?= htmlspecialchars(strtolower(implode(' ', $vehicle)), ENT_QUOTES, 'UTF-8'); ?>">
                                <td class="px-5 py-4 font-extrabold text-fleet-ink" contenteditable="true"><?= htmlspecialchars($vehicle['reg'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-ink" contenteditable="true">
                                    <span class="block"><?= htmlspecialchars($vehicle['make'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="mt-2 block"><?= htmlspecialchars($vehicle['model'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td class="px-5 py-4 text-fleet-ink" contenteditable="true"><?= htmlspecialchars($vehicle['year'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4">
                                    <span class="rounded-lg bg-slate-200 px-3 py-1 text-xs font-medium text-slate-600" contenteditable="true"><?= htmlspecialchars($vehicle['type'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <td class="px-5 py-4 text-fleet-ink" contenteditable="true"><?= htmlspecialchars($vehicle['department'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-ink" contenteditable="true"><?= htmlspecialchars($vehicle['mileage'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($vehicle['insurance'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4 text-fleet-muted" contenteditable="true"><?= htmlspecialchars($vehicle['repairs'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="px-5 py-4">
                                    <?php if ($vehicle['status'] === 'Active'): ?>
                                        <span class="rounded-lg border border-green-200 bg-fleet-success-soft px-3 py-1 text-xs font-semibold text-fleet-success">Active</span>
                                    <?php else: ?>
                                        <span class="rounded-lg border border-orange-200 bg-fleet-warning-soft px-3 py-1 text-xs font-semibold text-fleet-warning-strong">Maintenance</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-3">
                                        <button type="button" class="text-fleet-sidebar hover:text-fleet-primary" aria-label="Edit <?= htmlspecialchars($vehicle['reg'], ENT_QUOTES, 'UTF-8'); ?>">Edit</button>
                                        <button type="button" class="text-fleet-danger hover:text-fleet-danger-strong" aria-label="Delete <?= htmlspecialchars($vehicle['reg'], ENT_QUOTES, 'UTF-8'); ?>">Del</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div
        id="vehicle-modal"
        class="fixed inset-0 z-50 <?= $shouldOpenVehicleModal ? 'flex' : 'hidden'; ?> items-center justify-center bg-black/75 px-4 py-6"
        aria-hidden="<?= $shouldOpenVehicleModal ? 'false' : 'true'; ?>"
        data-open-on-load="<?= $shouldOpenVehicleModal ? 'true' : 'false'; ?>"
    >
        <div class="w-full max-w-2xl rounded-lg border border-fleet-line bg-fleet-surface shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="vehicle-modal-title">
            <!-- Failed submissions reopen this modal and refill the fields from flash form data. -->
            <form class="p-6" action="<?= htmlspecialchars($vehicleFormAction, ENT_QUOTES, 'UTF-8'); ?>" method="post">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <h2 id="vehicle-modal-title" class="text-xl font-extrabold text-fleet-ink">Add New Vehicle</h2>
                    <button type="button" data-close-vehicle-modal class="flex h-8 w-8 items-center justify-center rounded-lg text-2xl leading-none text-fleet-muted hover:bg-fleet-surface-muted hover:text-fleet-ink" aria-label="Close add vehicle form">&times;</button>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Registration Number *</span>
                        <input name="registration_number" type="text" required autofocus class="vehicle-form-control" placeholder="e.g. UAX 123A" value="<?= htmlspecialchars($vehicleFormData['registration_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Make *</span>
                        <input name="make" type="text" required class="vehicle-form-control" placeholder="e.g. Toyota" value="<?= htmlspecialchars($vehicleFormData['make'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Model *</span>
                        <input name="model" type="text" required class="vehicle-form-control" placeholder="e.g. Land Cruiser" value="<?= htmlspecialchars($vehicleFormData['model'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Year</span>
                        <input name="year" type="number" min="1980" max="2035" class="vehicle-form-control" placeholder="e.g. 2020" value="<?= htmlspecialchars($vehicleFormData['year'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Vehicle Type</span>
                        <select name="vehicle_type" class="vehicle-form-control">
                            <option value="sedan" <?= (($vehicleFormData['vehicle_type'] ?? 'sedan') === 'sedan') ? 'selected' : ''; ?>>sedan</option>
                            <option value="suv" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'suv') ? 'selected' : ''; ?>>suv</option>
                            <option value="pickup" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'pickup') ? 'selected' : ''; ?>>pickup</option>
                            <option value="truck" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'truck') ? 'selected' : ''; ?>>truck</option>
                            <option value="van" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'van') ? 'selected' : ''; ?>>van</option>
                            <option value="bus" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'bus') ? 'selected' : ''; ?>>bus</option>
                            <option value="motorcycle" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'motorcycle') ? 'selected' : ''; ?>>motorcycle</option>
                            <option value="other" <?= (($vehicleFormData['vehicle_type'] ?? '') === 'other') ? 'selected' : ''; ?>>other</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Fuel Type</span>
                        <select name="fuel_type" class="vehicle-form-control">
                            <option value="diesel" <?= (($vehicleFormData['fuel_type'] ?? 'diesel') === 'diesel') ? 'selected' : ''; ?>>diesel</option>
                            <option value="petrol" <?= (($vehicleFormData['fuel_type'] ?? '') === 'petrol') ? 'selected' : ''; ?>>petrol</option>
                            <option value="hybrid" <?= (($vehicleFormData['fuel_type'] ?? '') === 'hybrid') ? 'selected' : ''; ?>>hybrid</option>
                            <option value="electric" <?= (($vehicleFormData['fuel_type'] ?? '') === 'electric') ? 'selected' : ''; ?>>electric</option>
                            <option value="other" <?= (($vehicleFormData['fuel_type'] ?? '') === 'other') ? 'selected' : ''; ?>>other</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Department</span>
                        <input name="department" type="text" class="vehicle-form-control" placeholder="e.g. Transport" value="<?= htmlspecialchars($vehicleFormData['department'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Current Mileage (km)</span>
                        <input name="current_mileage" type="number" min="0" class="vehicle-form-control" value="<?= htmlspecialchars($vehicleFormData['current_mileage'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>">
                    </label>

                    <label class="block md:col-span-1">
                        <span class="mb-2 block text-sm font-semibold text-fleet-ink">Status</span>
                        <select name="status" class="vehicle-form-control">
                            <option value="active" <?= (($vehicleFormData['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="maintenance" <?= (($vehicleFormData['status'] ?? '') === 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="grounded" <?= (($vehicleFormData['status'] ?? '') === 'grounded') ? 'selected' : ''; ?>>Grounded</option>
                            <option value="disposed" <?= (($vehicleFormData['status'] ?? '') === 'disposed') ? 'selected' : ''; ?>>Disposed</option>
                        </select>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" data-close-vehicle-modal class="h-10 rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted">Cancel</button>
                    <button type="submit" class="h-10 rounded-lg bg-fleet-sidebar px-4 text-sm font-semibold text-white shadow-sm hover:bg-fleet-sidebar-active">Add Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
